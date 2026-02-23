<?php
include_once('../common.php');

if (!$userObj->hasPermission('view-trip-job-help-request-report')) {
    $userObj->redirect();
}

$script = 'trip_help_details';
$tableName = "trip_help_detail";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iTripHelpDetailId DESC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vName ASC";
    else
        $ord = " ORDER BY vName DESC";
}
//End Sorting

$cmp_ssql = "";

// Start Search Parameters
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';//date('Y-m-d', strtotime('-2 week'))
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';//date('Y-m-d');
$ssql = '';
if ($startDate != '') {
    $ssql .= " AND Date(tDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(tDate) <='" . $endDate . "'";
}
$ssql .= " AND th.iTripId != ''";
if ($serachTripNo != '') {
    $ssql .= " AND t.vRideNo ='" . $serachTripNo . "'";
}
if ($searchRider != '') {
    $ssql .= " AND th.iUserId ='" . $searchRider . "'";
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$totalData = $obj->MySQLSelect("SELECT count(iTripHelpDetailId) as Total FROM $tableName th
LEFT JOIN register_user ru ON th.iUserId = ru.iUserId
LEFT JOIN help_detail hd ON th.iHelpDetailId = hd.iHelpDetailId
LEFT JOIN trips t ON t.iTripId = th.iTripId
WHERE 1 = 1 $ssql $ord");
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
if ($page <= 0)
    $page = 1;
//Pagination End

$sql = "SELECT th.iTripHelpDetailId,ru.vName,ru.vLastName,ru.vEmail,ru.vPhone,ru.vPhoneCode,ru.vTimeZone,hd.vTitle_$default_lang,th.vComment,th.tDate,th.iTripId,th.iUserId,t.vRideNo,t.eType FROM $tableName th
LEFT JOIN register_user ru ON th.iUserId = ru.iUserId
LEFT JOIN help_detail hd ON th.iHelpDetailId = hd.iHelpDetailId
LEFT JOIN trips t ON t.iTripId = th.iTripId
WHERE 1 = 1 $ssql $ord LIMIT $start, $per_page";
                                           
$data_drv = $obj->MySQLSelect($sql);

$endRecord = scount($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

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

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Help Requests</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->

    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                                <h2><?= $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN'] ?> Help Requests</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <div class="Posted-date mytrip-page payment-report">
                        <input type="hidden" name="action" value="search">
                        <h3>Search...</h3>
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
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff">
                            <div class="col-lg-3 select001">
                                <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>" id="searchRider">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                </select>
                            </div>
                            <div class="col-lg-2">            
                                <input type="text" id="serachTripNo" name="serachTripNo" placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number" class="form-control search-trip001" value="<?= $serachTripNo; ?>"/>
                            </div>
                             
                        </span>
                    </div>
                    <div class="tripBtns001"><b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'trip_help_details.php'"/>
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
                                                    <th width="10%" style="text-align: center;">Booking No</th>
                                                    <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                            if ($sortby == '1') {
                                                                echo $order;
                                                            } else {
                                                            ?>0<?php } ?>)">Contact Details<?php
                                                            if ($sortby == 1) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                    }
                                                                } else {
                                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                    </th>
                                                    <th width="35%" >Subject</th>     
                                                    <th width="10%" style="text-align: center;">Message</th>     
                                                    <th width="15%" style="text-align: center;">Date</th>     
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < scount($data_drv); $i++) {
                                                        $link_page = "invoice.php";
                                                        if ($data_drv[$i]['eType'] == 'Multi-Delivery') {
                                                            $link_page = "invoice_multi_delivery.php";
                                                        }
                                                    ?>
                                                <tr class="gradeA">
                                                    <td align="center">

                                                        <?php
                                                        if ($APP_TYPE == 'UberX') {
                                                            $id_pro = 'iJobId';
                                                        }
                                                        else {
                                                            $id_pro = 'iTripId';
                                                        }
                                                        ?>

                                                        <a href="<?= $link_page ?>?<?= $id_pro; ?>=<?= $data_drv[$i]['iTripId'] ?>" target="_blank"><?= $data_drv[$i]['vRideNo']; ?></a>
                                                    </td>
                                                    <td>
                                                        <?php if ($userObj->hasPermission('view-users')) { ?>
                                                        <a href="javascript:void(0);" onClick="show_rider_details('<?= $data_drv[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?= clearName($data_drv[$i]['vName']." ".$data_drv[$i]['vLastName']); ?></a>
                                                        <?php } else { ?>
                                                        <?= clearName($data_drv[$i]['vName']." ".$data_drv[$i]['vLastName']); ?>
                                                        <?php } ?>
                                                        <br><?= clearEmail($data_drv[$i]['vEmail']); ?><br>
                                                    <?= clearPhone("(+".$data_drv[$i]['vPhoneCode'].") ".$data_drv[$i]['vPhone']); ?></td>
                                                    <td><?= clearName($data_drv[$i]['vTitle_'.$default_lang]); ?></td>
                                                    <td align="center"><a href="javascript:void(0);" onClick="show_details('<?= $data_drv[$i]['iTripHelpDetailId']; ?>')" style="text-decoration: underline;">View Message</a></td>
                                                    <div style="display:none" id="condetails_<?= $data_drv[$i]['iTripHelpDetailId']; ?>"><?= clearGeneralText($data_drv[$i]['vComment']); ?></div>
                                                    <td align="center"><?php $systemTimeZone = date_default_timezone_get();
                                                    $tRequestDate = converToTz($data_drv[$i]['tDate'], $data_drv[$i]['vTimeZone'], $systemTimeZone);

                                                    $date_format_data_array = array(
                                                        'tdate' => $tRequestDate,
                                                        'langCode' => $default_lang,
                                                        'DateFormatForWeb' => 1
                                                    );
                                                    $get_tRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                    $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                    echo $get_tRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//date('d M, Y h:i A', strtotime($tRequestDate));
                                                    ?></td>
                                                </tr>
                                                <?php } } else { ?>
                                                <tr class="gradeA">
                                                    <td colspan="8"  align="center"> No Records Found.</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                            <?php include('pagination_n.php'); ?>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                        <div class="admin-notes">
                            <h4>Note:</h4>
                            <ul>
                                <li>This will list all kind of help requests which is raised by the member of your site.</li>
                                <li>Users may ask for the help if they face any problems after Trip OR Job.</li>
                                <!-- <li>You can communicate with the users via mentioned contact details externally.</li> -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                        <!--<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>-->
                            <i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>
                            <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="imageIcons">
                            <div align="center">                                                                       
                                <img src="default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="rider_detail" ></div>
                    </div>
                </div>
            </div>
        </div>
<form name="pageForm" id="pageForm" action="action/trip_help_details.php" method="post" >
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iCompanyId" id="iMainId01" value="" >
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
    <input type="hidden" name="status" id="status01" value="" >
    <input type="hidden" name="statusVal" id="statusVal" value="" >
    <input type="hidden" name="option" value="<?php echo $option; ?>" >
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
    <input type="hidden" name="method" id="method" value="" >
    <input type="hidden" name="startDate" value="<?= $startDate; ?>" >
    <input type="hidden" name="endDate" value="<?= $endDate; ?>" >
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>" >
    <input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>" >
</form>
<?php include_once('footer.php'); ?>
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/css/modal_alert.css" />
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<?php include_once('searchfunctions.php'); ?>
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

function show_details(contactid) {
    //$("#rider_detail").html($("#condetails_" +contactid).html());
    //$("#detail_modal").modal('show');
    message = $("#condetails_" +contactid).html();
    show_alert("Message",message,"ok","","",function (btn_id) {}, true,true,true); 

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