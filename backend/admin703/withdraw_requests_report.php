<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-withdraw-request-report')){
    $userObj->redirect();
}
$script = 'withdraw_requests';
$tableName = "withdraw_requests";
//Start Sorting
$sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
$order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
$ord = ' ORDER BY wr.iWithdrawRequestsId DESC';
if ($sortby == 1){
    if ($order == 0){
        $ord = " ORDER BY wr.vName ASC";
    }else{
        $ord = " ORDER BY wr.vName DESC";
    }
}
//End Sorting
$systemTimeZone = date_default_timezone_get();
$cmp_ssql = "";
$isAjax = isset($_REQUEST['isAjax'])?$_REQUEST['isAjax']:"No";
$iWithdrawRequestsId = isset($_REQUEST['iWithdrawRequestsId'])?$_REQUEST['iWithdrawRequestsId']:"";
if ($isAjax == "Yes" && !empty($iWithdrawRequestsId)){

    if (!empty($_REQUEST['eMarkAsDone'])){
        $obj->sql_query("UPDATE $tableName SET eMarkAsDone='".$_REQUEST['eMarkAsDone']."',tRequestDate = '".date("Y-m-d H:i:s")."' WHERE iWithdrawRequestsId = $iWithdrawRequestsId");
        echo "1";
        exit;
    }
}
// Start Search Parameters
$option = isset($_REQUEST['option'])?$_REQUEST['option']:"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
$searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
$searchRequestStatus = isset($_REQUEST['searchRequestStatus'])?$_REQUEST['searchRequestStatus']:'';
$startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:""; //date('Y-m-d', strtotime('-2 week'));
$endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:"";//date('Y-m-d');
$ssql = '';
if ($searchRequestStatus != ''){
    $ssql .= " AND wr.eMarkAsDone = '".$searchRequestStatus."'";
}else{
    $ssql .= " AND wr.eMarkAsDone = ''";
}
if ($startDate != ''){
    $ssql .= " AND Date(wr.tRequestDate) >='".$startDate."'";
}
if ($endDate != ''){
    $ssql .= " AND Date(wr.tRequestDate) <='".$endDate."'";
}
if ($searchDriver != ''){
    $ssql .= " AND wr.iDriverId ='".$searchDriver."'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$totalData = $obj->MySQLSelect("SELECT count(wr.iWithdrawRequestsId) as Total FROM $tableName as wr WHERE 1 = 1 $ssql $ord");
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])){
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages){
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page'])?intval($_GET['page']):0;
$tpages = $total_pages;
if ($page <= 0){
    $page = 1;
}
//Pagination End
$sql = "SELECT wr.*,rd.vTimeZone FROM $tableName as wr LEFT JOIN register_driver as rd on wr.iDriverId=rd.iDriverId WHERE 1 = 1 $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$iDriverIds = array_unique(array_column($data_drv,'iDriverId','iWithdrawRequestsId'));
$currentbal = array();
foreach ($iDriverIds as $key => $value){
    $currentbal[$value] = $WALLET_OBJ->FetchMemberWalletBalance($value,"Driver");
}
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val){
    if ($key != "tpages" && $key != 'page'){
        $var_filter .= "&$key=".stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
$Today = Date('Y-m-d');
$tdate = date("d") - 1;
$mdate = date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - 1,date("Y")));
$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y") - 1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y") - 1));
$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m") + 1,date("d") - $mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m") - 1,date("d") - $tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $mdate,date("Y")));
$monday = date('Y-m-d',strtotime('monday this week'));
$sunday = date('Y-m-d',strtotime('sunday this week'));
$Pmonday = date('Y-m-d',strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d',strtotime('sunday this week -1 week'));
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?=$SITE_NAME?> | Withdraw Requests</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->

<!-- BEGIN BODY-->
<body class="padTop53 ">
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
                        <h2>Withdraw Requests</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search">
                    <h3>Search...</h3>
                    <span>
                            <a style="cursor:pointer"
                                    onClick="return todayDate('dp4', 'dp5');"><?=$langage_lbl_admin['LBL_MYTRIP_Today'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return yesterdayDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Yesterday'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return currentweekDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Week'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return previousweekDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous_Week'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return currentmonthDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Month'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return previousmonthDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous Month'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return currentyearDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Current_Year'];?></a>
                            <a style="cursor:pointer"
                                    onClick="return previousyearDate('dFDate', 'dTDate');"><?=$langage_lbl_admin['LBL_MYTRIP_Previous_Year'];?></a>
                        </span>
                    <span>
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff">
                            <div class="col-lg-3 select001">
                                <select class="form-control filter-by-text driver_container" name='searchDriver' data-text="Select <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>" id="searchDriver">
                                    <option value="">Select <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <select class="form-control filter-by-text driver_container" name='searchRequestStatus' data-text="Select Request Status" id="searchRequestStatus">
                                    <option value="">Pending Requests</option>
                                    <option value="Yes" <? if (!empty($searchRequestStatus) && $searchRequestStatus == "Yes"){
                                        echo "selected";
                                    } ?>>Completed Request</option>
                                    <option value="No" <? if (!empty($searchRequestStatus) && $searchRequestStatus == "No"){
                                        echo "selected";
                                    } ?>>Declined Request</option>
                                </select>
                            </div>
                        </span>
                </div>
                <div class="tripBtns001"><b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'withdraw_requests_report.php'"/>
                    </b></div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th width="25%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1'){
                                                echo $order;
                                            }else{
                                                ?>0<?php } ?>)">Contact Details<?php
                                                if ($sortby == 1){
                                                    if ($order == 0){
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                }else{
                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="10%" style="text-align: center">Requested Amount to Withdraw</th>
                                        <th width="15%" style="text-align: center">Wallet Balance</th>
                                        <th width="8%" style="text-align: center">Bank Details</th>
                                        <th width="10%" style="text-align: center">Date</th>
                                        <th width="8%" style="text-align: center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)){
                                        for ($i = 0;$i < count($data_drv);$i++){ ?>
                                            <tr class="gradeA">
                                                <td>
                                                    <? if ($userObj->hasPermission('view-providers')){ ?>
                                                        <a href="javascript:void(0);" onClick="show_driver_details('<?=$data_drv[$i]['iDriverId'];?>')" style="text-decoration: underline;"><?=clearName($data_drv[$i]['vName']." ".$data_drv[$i]['vLastName']);?></a>
                                                    <? }else{ ?>
                                                        <?=clearName($data_drv[$i]['vName']." ".$data_drv[$i]['vLastName']);?>
                                                    <? } ?>
                                                    <br><?=clearEmail($data_drv[$i]['vEmail']);?><br>
                                                    <?="(+".clearPhone($data_drv[$i]['vCode']).") ".clearPhone($data_drv[$i]['vPhone']);?>

                                                </td>
                                                <td align="center"><?=formateNumAsPerCurrency($data_drv[$i]['fAmount'],'')?></td>
                                                <td align="center">
                                                    Current Balance:
                                                    <a href="wallet_report.php?action=search&eUserType=Driver&iDriverId=<?=$data_drv[$i]['iDriverId']?>"><?=formateNumAsPerCurrency($currentbal[$data_drv[$i]['iDriverId']],'')?></a><br>
                                                    Balance when requested: <? if ($data_drv[$i]['fAccountBalance'] > 0){ ?>
                                                        <a href="wallet_report.php?action=search&eUserType=Driver&iDriverId=<?=$data_drv[$i]['iDriverId']?>"><?=formateNumAsPerCurrency($data_drv[$i]['fAccountBalance'],'');?></a><? }else{ ?>--<? } ?>
                                                </td>
                                                <td align="center">
                                                    <a href="javascript:void(0);" onClick="show_driver_bankdetails('<?=$data_drv[$i]['iDriverId'];?>', '<?=clearCmpName($data_drv[$i]['vName']." ".$data_drv[$i]['vLastName']);?>', '<?=clearCmpName($data_drv[$i]['vBankAccountHolderName']);?>', '<?=$data_drv[$i]['vBankName'];?>', '<?=clearCmpName($data_drv[$i]['vAccountNumber']);?>', '<?=clearCmpName($data_drv[$i]['vBIC_SWIFT_Code']);?>','<?=clearCmpName($data_drv[$i]['vBankLocation']);?>')" style="text-decoration: underline;">View Details</a>
                                                </td>
                                                <td align="center"><?php

                                                    if (empty($data_drv[$i]['vTimeZone'])){
                                                        $tRequestDate = $data_drv[$i]['tRequestDate'];
                                                    }else{
                                                        $tRequestDate = converToTz($data_drv[$i]['tRequestDate'],$data_drv[$i]['vTimeZone'],$systemTimeZone);
                                                    }
                                                    $date_format_data_array = array('tdate'            => $tRequestDate,
                                                                                    'langCode'         => $default_lang,
                                                                                    'DateFormatForWeb' => 1);
                                                    $get_tRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                    $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                    echo $get_tRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//date('d M, Y h:i A', strtotime($tRequestDate));
                                                    ?></td>
                                                <!--<td style="text-align: center"><a class="btn btn-primary" onclick="Recordcheckedbyadmin('<?=$data_drv[$i]['iWithdrawRequestsId'];?>')">Mark As Done</a>
                                                    <input type="hidden" name="iWithdrawRequestsId" id="iWithdrawRequestsId" value="<?=$data_drv[$i]['iWithdrawRequestsId'];?>">
                                                    </td>-->
                                                <td align="center" class="action-btn001">
                                                    <? if ($data_drv[$i]['eMarkAsDone'] != ""){
                                                        echo "--";
                                                    }else{ ?>
                                                        <div class="share-button openHoverAction-class" style="display: block;">
                                                            <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                            <div class="social show-moreOptions for-five openPops_<?=$driverId;?>">
                                                                <ul>
                                                                    <li class="entypo-facebook" data-network="facebook">
                                                                        <a href="javascript:void(0);" onClick="Recordcheckedbyadmin('<?=$data_drv[$i]['iWithdrawRequestsId'];?>','Yes')" data-toggle="tooltip" title="Mark as Completed">
                                                                            <img src="img/active-icon.png" alt="Mark as Completed">
                                                                        </a></li>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);" onClick="Recordcheckedbyadmin('<?=$data_drv[$i]['iWithdrawRequestsId'];?>','No')" data-toggle="tooltip" title="Mark as Declined">
                                                                            <img src="img/delete-icon.png" alt="Mark as Declined">
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    <? } ?>
                                                    <input type="hidden" name="iWithdrawRequestsId" id="iWithdrawRequestsId" value="<?=$data_drv[$i]['iWithdrawRequestsId'];?>">
                                                    <input type="hidden" name="eMarkAsDone" id="eMarkAsDone" value="<?=$data_drv[$i]['eMarkAsDone'];?>">
                                                </td>
                                            </tr>
                                        <?php }
                                    }else{ ?>
                                        <tr class="gradeA">
                                            <td colspan="8" align="center"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Note:</h4>
                <ul>
                    <li>This will list all the requests for withdrawing money from the wallet.</li>
                    <li>You can mark requests as completed OR declined. This will not send any kind of emails OR notification to the <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']?>. By doing this, system will not show particular request in this list by default. You can view these requests by changing the status of requests in search facility.</li>
                    <!-- <li>You can communicate with the member via mentioned contact details externally.</li> -->
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="modal fade" id="detail_modal_driver" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                    <?=$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons_driver" style="display:none">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                    <span id="provideName"></span> Bank Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <table border="1" class="table table-bordered" width="100%" align="center" cellspacing="5" cellpadding="10px">
                    <tbody>
                    <tr>
                        <td class="text_design">Account Holder Name</td>
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
                    <tr>
                        <td class="text_design">Bank Location</td>
                        <td id="banklocation"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/withdraw_requests_report.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iCompanyId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="startDate" value="<?=$startDate;?>">
    <input type="hidden" name="endDate" value="<?=$endDate;?>">
    <input type="hidden" name="searchDriver" value="<?=$searchDriver;?>">
    <input type="hidden" name="searchRequestStatus" value="<?=$searchRequestStatus;?>">
</form>
<?php
include_once('footer.php');
?>
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/css/modal_alert.css"/>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<!--<script src="../assets/js/jquery-ui.min.js"></script> commented bcoz in activate and delete icon tooltip is not shown.-->
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<? include_once('searchfunctions.php'); ?>
<script>
    var startDate;
    var endDate;
    $('#dp4').datepicker()
        .on('changeDate', function (ev) {
            startDate = new Date(ev.date);
            if (endDate != null) {
                if (ev.date.valueOf() < endDate.valueOf()) {
                    $('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    $('#startDate').text($('#dp4').data('date'));
                }
            }
            $('#dp4').datepicker('hide');
        });
    $('#dp5').datepicker()
        .on('changeDate', function (ev) {
            endDate = new Date(ev.date);
            if (startDate != null) {
                if (ev.date.valueOf() < startDate.valueOf()) {
                    $('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    $('#endDate').text($('#dp5').data('date'));
                }
            }
            $('#dp5').datepicker('hide');
        });
    $(document).ready(function () {
        $('#usertype_options').hide();
        $('#option').each(function () {
            if (this.value == 'eUserType') {
                $('#usertype_options').show();
                $('.searchform').hide();
            }
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
    $('#option').change(function () {
        if ($('#option').val() == 'eUserType') {
            $('#usertype_options').show();
            $("input[name=keyword]").val("");
            $('.searchform').hide();
        } else {
            $('#usertype_options').hide();
            $("#estatus_value").val("");
            $('.searchform').show();
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

    function show_driver_bankdetails(driverid, provideName, acName, bankName, acNumber, sortCode, banklocation) {
        $("#provideName").text("");
        $("#pacName,#pbankName,#pacNumber,#psortcode,#banklocation").html("");
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
            $("#banklocation").html(banklocation);
        }
    }

    function show_driver_details(driverid) {
        $("#driver_detail").html('');
        $("#imageIcons_driver").show();
        $("#detail_modal_driver").modal('show');
        if (driverid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': {"iDriverId": driverid, "editTrip": "No"},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#imageIcons_driver").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons_driver").hide();
                }
            });
        }
    }

    function show_details(iWithdrawRequestsId) {
        if (iWithdrawRequestsId != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>withdraw_requests_report.php',
                'AJAX_DATA': {"isAjax": 'Yes', "iWithdrawRequestsId": iWithdrawRequestsId},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    show_alert("Booking Detail(s)", data, "Ok", "", "", function (btn_id) {
                    }, true, true, true);
                } else {
                    console.log(response.result);
                }
            });
        }
    }

    function Recordcheckedbyadmin(iWithdrawRequestsId, eMarkAsDone) {
        //var iWithdrawRequestsId = $("#iWithdrawRequestsId").val();
        $("#iWithdrawRequestsId").val(iWithdrawRequestsId);
        $("#eMarkAsDone").val(eMarkAsDone);
        if (eMarkAsDone == 'Yes') {
            var alert = "Once you have completed this withdraw request, you will set mark as done, do you want to set mark as done this record?";
        } else if (eMarkAsDone == 'No') {
            var alert = "Once you have declined this withdraw request, you will set mark as declined, do you want to set mark as declined this record?";
        } else {
            var alert = "Pending Request";
        }
        if (iWithdrawRequestsId != "") {
            show_alert("Note", alert, "Ok", "Cancel", "", function (btn_id) {
                if (btn_id == 0) {
                    withdrawrequest();
                } else {
                    return false;
                }
            }, true, true, true);
        }
    }

    function withdrawrequest() {
        var iWithdrawRequestsId = $("#iWithdrawRequestsId").val();
        var eMarkAsDone = $("#eMarkAsDone").val();
        var ajaxData = {
            'URL': 'withdraw_requests_report.php',
            'AJAX_DATA': {"isAjax": "Yes", "iWithdrawRequestsId": iWithdrawRequestsId, "eMarkAsDone": eMarkAsDone},
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (res) {
            if (res.action == "1") {
                location.reload();
            } else {
            }
        });
    }

    function todayDate() {
        $("#dp4").val('<?= $Today; ?>');
        $("#dp5").val('<?= $Today; ?>');
    }

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
</script>
</body>
<!-- END BODY-->
</html>