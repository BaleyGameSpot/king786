rp.php<?php
include_once('../common.php');
$userObj->redirect();
if (!$userObj->hasPermission('manage-trip-job-time-variance-report')) {
    $userObj->redirect();
}
$script = "Driver Trip Detail";
/*$sql = "select iDriverId, CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);*/
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY t.tStartdate DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY t.tStartDate ASC"; else
        $ord = " ORDER BY t.tStartDate DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY d.vName ASC"; else
        $ord = " ORDER BY d.vName DESC";
}
//End Sorting
$cmp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $cmp_ssql = " And t.tStartDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$date1 = $startDate . ' ' . "00:00:00";
$date2 = $endDate . ' ' . "23:59:59";
if ($startDate != '') {
    $ssql .= " AND Date(t.tStartDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(t.tStartDate) <='" . $endDate . "'";
}
if ($iDriverId != '') {
    $ssql .= " AND d.iDriverId = '" . $iDriverId . "'";
}
if ($serachTripNo != '') {
    $ssql .= " AND t.vRideNo ='" . $serachTripNo . "'";
}
$locations_where = "";
if (scount($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE t.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(d.iDriverId) AS Total
FROM register_driver d
RIGHT JOIN trips t ON d.iDriverId = t.iDriverId
LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId
LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId
WHERE 1=1 AND t.iActive = 'Finished' AND t.eCancelled='No' $ssql $cmp_ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
$sql = "SELECT u.vName, u.vLastName, d.vAvgRating,t.fGDtime,t.tStartdate,t.tEndDate, t.tTripRequestDate, t.iFare, d.iDriverId, t.tSaddress,t.vRideNo, t.iOrderId, t.eSystem, t.tDaddress, d.vName AS name,c.vName AS comp,c.vCompany, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType,t.iActive FROM register_driver d RIGHT JOIN trips t ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId WHERE 1=1 AND t.iActive = 'Finished' AND t.eCancelled='No' $ssql $cmp_ssql $ord LIMIT $start, $per_page";
$db_trip = $obj->MySQLSelect($sql);

$endRecord = scount($db_trip);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
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
$monday = date('Y-m-d', strtotime('sunday this week -1 week'));
$sunday = date('Y-m-d', strtotime('saturday this week'));
$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));
$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Time Variance</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Time Variance</h2>
                </div>
            </div>
            <hr/>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                            <div class="Posted-date mytrip-page mytrip-page-select payment-report">
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
													<input type="text" id="dp5" name="endDate" placeholder="To Date"
                                                           class="form-control" value="" readonly=""
                                                           style="cursor:default; background-color: #fff"/>
													<div class="col-lg-3 select001">
														<select class="form-control filter-by-text" name='iDriverId'
                                                                data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN']; ?>"
                                                                id="searchDriver">
														   <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN']; ?></option>
                                                            <!-- <?php foreach ($db_drivers as $dbd) { ?>
														   <option value="<?php echo $dbd['iDriverId']; ?>" <?php if ($iDriverId == $dbd['iDriverId']) {
                                                                echo "selected";
                                                            } ?>><?php echo clearName($dbd['driverName']); ?> - ( <?php echo clearEmail($dbd['vEmail']); ?> )</option>
														   <?php } ?> -->
														</select>
													</div>
													<div class="col-lg-2">
													  <input type="text" id="serachTripNo" name="serachTripNo"
                                                             placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number"
                                                             class="form-control search-trip001"
                                                             value="<?php echo $serachTripNo; ?>"/>
													</div>
													</span>
                            </div>
                            <div class="tripBtns001">
                                <b>
                                    <input type="submit" value="Search" class="btnalt button11" id="Search"
                                           name="Search" title="Search"/>
                                    <input type="button" value="Reset" class="btnalt button11"
                                           onClick="window.location.href='driver_trip_detail.php'"/>
                                    <?php if (scount($db_trip) > 0) { ?>
                                    <button type="button" onClick="reportExportTypes('driver_trip_detail')"
                                            class="export-btn001">Export
                                    </button>
                                </b>
                                <?php } ?>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <form name="_list_form" id="_list_form" class="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                    <tr>
                                        <th><?php echo $langage_lbl_admin['LBL_TRIP_NO_ADMIN']; ?></th>
                                        <th>Address</th>
                                        <th>
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(1,<?php if ($sortby == '1') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']; ?> <?php if ($sortby == 1) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th>
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(2,<?php if ($sortby == '2') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php if ($sortby == 2) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th>Estimated Time</th>
                                        <th>Actual Time</th>
                                        <th>Variance</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (scount($db_trip) > 0) {
                                        for ($i = 0; $i < scount($db_trip); $i++) {
                                            ?>
                                            <tr class="gradeA">
                                                <td>
                                                    <?= $db_trip[$i]['vRideNo']; ?>
                                                    <br>
                                                    <?php if ($userObj->hasPermission('view-invoice')) { ?>
                                                        <?php if ($db_trip[$i]['eSystem'] == 'DeliverAll') { ?>
                                                            <a href="order_invoice.php?iOrderId=<?= $db_trip[$i]['iOrderId'] ?>"
                                                               target="_blank">View
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="invoice.php?iTripId=<?= $db_trip[$i]['iTripId'] ?>"
                                                               target="_blank">View
                                                            </a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                                <td width="30%"
                                                    data-order="<?= $db_trip[$i]['iTripId'] ?>"><?= $db_trip[$i]['tSaddress'] . ' -> ' . $db_trip[$i]['tDaddress']; ?></td>
                                                <td><?= DateTime($db_trip[$i]['tStartdate']); ?></td>
                                                <td>
                                                    <a href="javascript:void(0);"
                                                       onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')"
                                                       style="text-decoration: underline;"><?php echo clearName($db_trip[$i]['name'] . " " . $db_trip[$i]['lname']); ?></a>
                                                </td>
                                                <!--<td width="8%">
																	<?= $db_trip[$i]['vCompany']; ?>
																</td>
																<td>
																	<?= clearName($db_trip[$i]['vName'] . " " . $db_trip[$i]['vLastName']); ?>
																</td> -->
                                                <td align="left">
                                                    <?php
                                                    $ans = set_hour_min($db_trip[$i]['fGDtime']);
                                                    if ($ans['hour'] != 0) {
                                                        echo $ans['hour'] . " Hours " . $ans['minute'] . " Minutes";
                                                    } else {
                                                        if ($ans['minute'] != 0) {
                                                            echo $ans['minute'] . " Minutes ";
                                                        }
                                                        echo $ans['second'] . " Seconds";
                                                    }
                                                    ?>
                                                </td>
                                                <td align="left">
                                                    <?php
                                                    $a = strtotime($db_trip[$i]['tStartdate']);
                                                    $b = strtotime($db_trip[$i]['tEndDate']);
                                                    $diff_time = ($b - $a);
                                                    //$diff_time=$diff_time*1000;
                                                    $ans_diff = set_hour_min($diff_time);
                                                    //print_r($ans);exit;
                                                    if ($ans_diff['hour'] != 0) {
                                                        echo $ans_diff['hour'] . " Hours " . $ans_diff['minute'] . " Minutes";
                                                    } else {
                                                        if ($ans_diff['minute'] != 0) {
                                                            echo $ans_diff['minute'] . " Minutes ";
                                                        }
                                                        echo $ans_diff['second'] . " Seconds";
                                                    }
                                                    ?>
                                                </td>
                                                <td align="left">
                                                    <?php
                                                    $ori_time = $db_trip[$i]['fGDtime'];
                                                    $tak_time = $diff_time;
                                                    $ori_diff = $ori_time - $tak_time;
                                                    $ans_ori = set_hour_min(abs($ori_diff));
                                                    if ($ans_ori['hour'] != 0) {
                                                        echo $ans_ori['hour'] . " Hours " . $ans_ori['minute'] . " Minutes";
                                                        if ($ori_diff < 0) {
                                                            echo " Late";
                                                        } else {
                                                            echo " Early";
                                                        }
                                                    } else {
                                                        if ($ans_ori['minute'] != 0) {
                                                            echo $ans_ori['minute'] . " Minutes ";
                                                        }
                                                        echo $ans_ori['second'] . " Seconds";
                                                        if ($ori_diff < 0) {
                                                            echo " Late";
                                                        } else {
                                                            echo " Early";
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <!--<td align="center">
																<?php //=trip_currency($db_trip[$i]['iFare']);
                                                ?>
																</td>
																<td align="center">
																	<?php //=$db_trip[$i]['vVehicleType'];
                                                ?>
																</td>
																<td align="center" width="10%">
																
																<?php //if($db_trip[$i]['iFare']!=0){
                                                ?>
																  <a href="invoice.php?iTripId=<?php //=$db_trip[$i]['iTripId']
                                                ?>">
																	<button class="btn btn-primary">
																		<i class="icon-th-list  icon-white"> View Invoice</i>
																	</button>
																 </a>
																<?php /*}else
																{
																	if($db_trip[$i]['iActive']== "Active" OR $db_trip[$i]['iActive']== "On Going Trip")
																	{
																		echo "On Ride";
																	}
																	else if($db_trip[$i]['iActive']== "Canceled")
																	{
																		echo "Cancelled";
																	}
																	else
																	{
																		echo "Cancelled";
																	}
																	
																}*/
                                                ?>
																</td>-->
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <tr class="gradeA">
                                            <td colspan="7" style="text-align:center;"> No Records Found.</td>
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
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="iDriverId" value="<?php echo $iDriverId; ?>">
    <input type="hidden" name="serachTripNo" value="<?php echo $serachTripNo; ?>">
    <input type="hidden" name="startDate" value="<?php echo $startDate; ?>">
    <input type="hidden" name="endDate" value="<?php echo $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?php echo $vStatus; ?>">
    <input type="hidden" name="method" id="method" value="">
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

        /*$("select.filter-by-text").each(function(){
         $(this).select2({
               placeholder: $(this).attr('data-text'),
               allowClear: true
         }); //theme: 'classic'
       });*/
    });

    function setRideStatus(actionStatus) {
        window.location.href = "trip.php?type=" + actionStatus;
    }

    function todayDate() {
        //alert('sa');
        $("#dp4").val('<?= $Today;?>');
        $("#dp5").val('<?= $Today;?>');
    }

    function resetform() {
        //location.reload();
        document.search.reset();
        document.getElementById("iDriverId").value = " ";
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

    function checkvalid() {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        }
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
        //console.log(item.text);
        /*if(item.text == 'Searching…'){
            console.log('item1');
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }*/
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
    var sId = '<?= $iDriverId;?>';
    var sSelect = $('select.filter-by-text#searchDriver');
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
                console.log(response.result);
            }
        });
    }
</script>
</body>
</html>