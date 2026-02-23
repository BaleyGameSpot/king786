<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-sos-request-report')) {
    $userObj->redirect();
}
$script = 'emergency_contact_data';
$tableName = "emergency_contact_data";
//Start Sorting
$isAjax = isset($_REQUEST['isAjax']) ? $_REQUEST['isAjax'] : "No";
$usertype = isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : "";
$tripid = isset($_REQUEST['tripid']) ? $_REQUEST['tripid'] : "";
$message = isset($_REQUEST['message']) ? $_REQUEST['message'] : "";
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
if($isAjax=="Yes" && !empty($usertype) && !empty($tripid)) {
    if ($eType == "PublishRide") {
        $selectcontactData = $obj->MySQLSelect("SELECT vContactName as vName, vContactPhone as vPhone, tMessage FROM emergency_contact_data WHERE (iPublishedRideId = $tripid) and vFromUserType = '$usertype' group by iEmergencyId");
    } else {
        $selectcontactData = $obj->MySQLSelect("SELECT vContactName as vName, vContactPhone as vPhone, tMessage FROM emergency_contact_data WHERE (iTripId = $tripid) and vFromUserType = '$usertype' group by iEmergencyId");
    }
    if(!empty($message)) {
        echo clearGeneralText($selectcontactData[0]['tMessage']);
        exit;
    }
    $data = "<table class='table table-striped table-bordered table-hover'>";
    if(!empty($selectcontactData)) {
        $data .= "<tr><th>Name</th><th>Phone</th></tr>";
        foreach($selectcontactData as $key=>$value) {
            $data .= "<tr><td>".clearName($value['vName'])."</td><td>".clearPhone($value['vPhone'])."</td></tr>";
        }
    } else {
            $data .= "<tr><td colspan='2'>No Records Found</td></tr>";
        }
        $data .= "</table>";
        echo $data;
        exit;
    }

    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY iEmergencyContactId DESC';

    if ($sortby == 1) {
    if ($order == 0) {
            $ord = " ORDER BY vFirstname ASC";
    } else {
            $ord = " ORDER BY vFirstname DESC";
    }
}

    if ($sortby == 2) {

    if ($order == 0) {
            $ord = " ORDER BY vEmail ASC";
    } else {
            $ord = " ORDER BY vEmail DESC";
    }
}
//End Sorting
$cmp_ssql = "";
// Start Search Parameters
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$ssql = $ssql1 = '';
    if ($startDate != '') {
        $ssql .= " AND Date(ecd.tRequestDate) >='" . $startDate . "'";
     $ssql1 .= " AND Date(ecd.tRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(ecd.tRequestDate) <='" . $endDate . "'";
    $ssql1 .= " AND Date(ecd.tRequestDate) <='" . $endDate . "'";
    }
    if ($serachTripNo != '') {
        $ssql .= " AND t.vRideNo ='" . $serachTripNo . "'";
        $ssql1 .= " AND t.vPublishedRideNo ='" . $serachTripNo . "'";
    }
    if ($searchRider != '' && $searchDriver != '') {
        $ssql .= " AND ((ecd.iUserId ='" . $searchRider . "' AND ecd.vFromUserType = 'Passenger') OR (ecd.iDriverId ='" . $searchDriver . "' AND ecd.vFromUserType = 'Driver'))";
    $ssql1 .= " AND ((ecd.iUserId ='" . $searchRider . "' AND ecd.vFromUserType = 'Passenger') OR (ecd.iDriverId ='" . $searchDriver . "' AND ecd.vFromUserType = 'Driver'))";
    } else if ($searchDriver != '') {
        $ssql .= " AND (ecd.iDriverId ='" . $searchDriver . "' AND ecd.vFromUserType = 'Driver')";
    $ssql1 .= " AND (ecd.iDriverId ='" . $searchDriver . "' AND ecd.vFromUserType = 'Driver')";
    } else if ($searchRider != '') {
    $ssql .= " AND (ecd.iUserId ='" . $searchRider . "' AND ecd.vFromUserType = 'Passenger')";
        $ssql .= " AND (ecd.iUserId ='" . $searchRider . "' AND ecd.vFromUserType = 'Passenger')";
    }

// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql = "SELECT ecd.iEmergencyContactId,t.vRideNo FROM `emergency_contact_data` ecd 
LEFT JOIN trips t ON t.iTripId = ecd.`iTripId`
LEFT JOIN register_user u ON u.iUserId = ecd.`iUserId`
LEFT JOIN register_driver d ON d.iDriverId = ecd.`iDriverId`
WHERE 1 = 1 AND ecd.iTripId > 0 $ssql GROUP BY ecd.iTripId,ecd.vFromUserType
UNION ALL
SELECT ecd.iEmergencyContactId,t.vPublishedRideNo FROM `emergency_contact_data` ecd 
LEFT JOIN published_rides t ON t.iPublishedRideId = ecd.iPublishedRideId
LEFT JOIN register_user u ON u.iUserId = ecd.iUserId
LEFT JOIN register_driver d ON d.iDriverId = ecd.iDriverId
WHERE 1 = 1 AND ecd.iPublishedRideId > 0 $ssql1 GROUP BY ecd.iPublishedRideId,ecd.vFromUserType ";
$totalData = $obj->MySQLSelect($sql);
$totalData[0]['Total'] = scount($totalData);
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
if ($page <= 0) {
    $page = 1;
}
//Pagination End
$sql = "SELECT 
ecd.iEmergencyContactId, 
CONCAT(u.vName,' ',u.vLastName) as userName, 
CONCAT(d.vName,' ',d.vLastName) as driverName, u.vEmail as useremail, d.vEmail as driveremail,CONCAT('(+',u.vPhoneCode,') ',u.vPhone) as userphone, CONCAT('(+',d.vCode,')',d.vPhone) as driverphone, ecd.iEmergencyId,ecd.vFromUserType,ecd.iTripId,t.vRideNo as vRideNo,t.eType,ecd.tRequestDate,t.vTimeZone as vTimeZone,ecd.iUserId,ecd.iDriverId,ecd.iPublishedRideId FROM `emergency_contact_data` ecd 
LEFT JOIN trips t ON t.iTripId = ecd.`iTripId`
LEFT JOIN register_user u ON u.iUserId = ecd.`iUserId`
LEFT JOIN register_driver d ON d.iDriverId = ecd.`iDriverId`
WHERE 1 = 1 AND ecd.iTripId > 0 $ssql GROUP BY ecd.iTripId,ecd.vFromUserType 
UNION ALL
SELECT ecd.iEmergencyContactId, CONCAT(u.vName,' ',u.vLastName) as userName, CONCAT(d.vName,' ',d.vLastName) as driverName, u.vEmail as useremail, d.vEmail as driveremail,CONCAT('(+',u.vPhoneCode,') ',u.vPhone) as userphone, CONCAT('(+',d.vCode,')',d.vPhone) as driverphone, ecd.iEmergencyId,ecd.vFromUserType,ecd.iTripId,
t.vPublishedRideNo as vRideNo,'' as eType,ecd.tRequestDate,u.vTimeZone as vTimeZone,ecd.iUserId,ecd.iDriverId,ecd.iPublishedRideId FROM `emergency_contact_data` ecd 
LEFT JOIN published_rides t ON t.iPublishedRideId = ecd.iPublishedRideId
LEFT JOIN register_user u ON u.iUserId = ecd.iUserId
LEFT JOIN register_driver d ON d.iDriverId = ecd.iDriverId
WHERE 1 = 1 AND ecd.iPublishedRideId > 0 $ssql1 GROUP BY ecd.iPublishedRideId,ecd.vFromUserType 
$ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$systemTimeZone = date_default_timezone_get();
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
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title><?= $SITE_NAME ?> | SOS Requests</title>
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
                            <h2>SOS Requests</h2>
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
                                <select class="form-control filter-by-text" name='searchRider'
                                        data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>"
                                        id="searchRider">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                </select>
                            </div>
                            <?php if($onlyRideShareEnable != 'Yes')  { ?>
                                <div class="col-lg-3 select001">
                                    <select class="form-control filter-by-text driver_container" name='searchDriver'
                                            data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>"
                                            id="searchDriver">
                                        <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                    </select>
                                </div> 
                            <?php } ?>
                            <?php if($onlyRideShareEnable == 'Yes')  { ?>
                                <div class="col-lg-2">            
                                    <input type="text" id="serachTripNo" name="serachTripNo"
                                           placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number"
                                           class="form-control search-trip001" value="<?= $serachTripNo; ?>"/>
                                </div>
                            <?php } ?>
                        </span>
                    </div>
                    <?php if($onlyRideShareEnable != 'Yes')  { ?>
                        <div class="row payment-report payment-report1 payment-report2">
                            <div class="col-lg-2">            
                            <input type="text" id="serachTripNo" name="serachTripNo"
                                   placeholder="<?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> Number"
                                   class="form-control search-trip001" value="<?= $serachTripNo; ?>"/>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="tripBtns001"><b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'emergency_contact_data.php'"/>
                    </b></div>
                </form>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="clear:both;"></div>
                            <div class="table-responsive">
                                <form class="_list_form" id="_list_form" method="post" 
				action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="10%" style="text-align: center;">Booking No</th>
                                                <th width="30%">Contact Details</th>
                                                <th width="10%" style="text-align: center;">Emergency Contacts</th>  
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
                                            $isPublishRideId = 0;
                                            if ($data_drv[$i]['iTripId'] == 0 && $data_drv[$i]['iPublishedRideId'] > 0) {
                                                $isPublishRideId = 1;
                                                $link_page = "prdetails.php";
                                                    }
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td align="center">
                                                    <?php if ($isPublishRideId == 1) { ?>
                                                        <a href="<?= $link_page ?>?iPublishedRideId=<?= $data_drv[$i]['iPublishedRideId'] ?>"
                                                           target="_blank"><?= $data_drv[$i]['vRideNo']; ?></a>
                                                    <?php } else { ?>
                                                    	<a href="<?= $link_page ?>?iTripId=<?= $data_drv[$i]['iTripId'] ?>"
                                                       target="_blank"><?= $data_drv[$i]['vRideNo']; ?></a>
                                                    <?php } ?>
                                                        </td>
                                                        <td>
                                                            <? if($data_drv[$i]['vFromUserType']=='Passenger') {
                                                                if ($userObj->hasPermission('view-users')) { ?>
                                                            <a href="javascript:void(0);"
                                                               onClick="show_rider_details('<?= $data_drv[$i]['iUserId']; ?>')"
                                                               style="text-decoration: underline;"><?= clearName($data_drv[$i]['userName']); ?></a>
                                                                    <? echo " (".$langage_lbl_admin['LBL_RIDER'].")";
                                                                } else {
                                                                    echo clearName($data_drv[$i]['userName'])." (".$langage_lbl_admin['LBL_RIDER'].")";
                                                                }
                                                                echo "<br>".clearEmail($data_drv[$i]['useremail'])."<br>".clearPhone($data_drv[$i]['userphone']);
                                                            } else if($data_drv[$i]['vFromUserType']=='Driver') {
                                                                if ($userObj->hasPermission('view-providers')) { ?>
                                                            <a href="javascript:void(0);"
                                                               onClick="show_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')"
                                                               style="text-decoration: underline;"><?= clearName($data_drv[$i]['driverName']); ?></a><? echo " (" . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ")";
                                                                } else {
                                                                    echo clearName($data_drv[$i]['driverName'])." (".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].")";
                                                                }
                                                                echo "<br>".clearEmail($data_drv[$i]['driveremail'])."<br>".clearPhone($data_drv[$i]['driverphone']);
                                                            } ?>
                                                        </td>                                                    
                                                <td align="center">
                                                    <a href="javascript:void(0);"
                                                        <?php if ($isPublishRideId == 1) { ?>
                                                            onClick="show_details('<?= $data_drv[$i]['vFromUserType'] ?>','<?= $data_drv[$i]['iPublishedRideId']; ?>','','PublishRide');"
                                                        <?php } else { ?>
                                                            onClick="show_details('<?= $data_drv[$i]['vFromUserType'] ?>','<?= $data_drv[$i]['iTripId']; ?>');"
                                                        <?php } ?> style="text-decoration: underline;">View
                                                    </a>
                                                </td>
                                                <td align="center">
                                                    <a href="javascript:void(0);"
                                                        <?php if ($isPublishRideId == 1) { ?>
                                                            onClick="show_details('<?= $data_drv[$i]['vFromUserType'] ?>','<?= $data_drv[$i]['iPublishedRideId']; ?>','message','PublishRide');"
                                                        <?php } else { ?>
                                                            onClick="show_details('<?= $data_drv[$i]['vFromUserType'] ?>','<?= $data_drv[$i]['iTripId']; ?>','message');"
                                                        <?php } ?>
                                                       style="text-decoration: underline;">View
                                                        Message
                                                    </a>
                                                </td>
                                                <td align="center">
                                                    <?php 
                                                        if(!empty($data_drv[$i]['vTimeZone'])){
                                                            $tRequestDate = converToTz($data_drv[$i]['tRequestDate'], $data_drv[$i]['vTimeZone'], $systemTimeZone);
                                                            $date_format_data_array = array(
                                                            'tdate' => $tRequestDate,
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                            );
                                                            $get_tRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                                            echo $get_tRequestDate_format['tDisplayDate']."<br/>".$get_tRequestDate_format['tDisplayTime'].$time_zone_difference_text;
                                                        }
                                                    ?>
                                                </td>
                                                </tr>
                                        <?php }
                                    } else { ?>
                                                <tr class="gradeA">
                                                    <td colspan="8"> No Records Found.</td>
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
                        <li>This will list all kind of emergency/SOS requests sent from User to their emergency contact list 
			during <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?>.
			</li>
                    <li>Users may ask for help in case of emergency
                        while <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> is in progress.
                    </li>
                        <!-- <li>You can communicate with the users via mentioned contact details externally.</li> -->
                    </ul>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
<div class="modal fade " id="detail_modal_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
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
                <div id="imageIcons_user">
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
<div class="modal fade" id="detail_modal_driver" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
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
<form name="pageForm" id="pageForm" action="action/emergency_contact_data.php" method="post" >
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iCompanyId" id="iMainId01" value="" >
    <input type="hidden" name="status" id="status01" value="" >
    <input type="hidden" name="statusVal" id="statusVal" value="" >
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
    <input type="hidden" name="method" id="method" value="" >
</form>
<?php include_once('footer.php'); ?>
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/css/modal_alert.css" />
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<script src="../assets/js/jquery-ui.min.js"></script>
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

    function show_details(usertype, tripid, message = "", eType = "") {
        if (message!="") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>emergency_contact_data.php',
                'AJAX_DATA': {
                    "isAjax": 'Yes',
                    "usertype": usertype,
                    "tripid": tripid,
                    "message": message,
                    "eType": eType
                },
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    show_alert("Message", data, "Ok", "", "", function (btn_id) {
                    }, true, true, true);
                } else {
                    console.log(response.result);
                }
            });
        } else {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>emergency_contact_data.php',
                'AJAX_DATA': {"isAjax": 'Yes', "usertype": usertype, "tripid": tripid, "eType": eType},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    show_alert("Contact Details", data, "Ok", "", "", function (btn_id) {
                    }, true, true, true);
                } else {
                    console.log(response.result);
                }
            });
        }
    }
    function show_driver_details(driverid) {
        $("#driver_detail").html('');
        $("#imageIcons_driver").show();
        $("#detail_modal_driver").modal('show');
        if (driverid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': {"iDriverId": driverid,"editTrip":"No"},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
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
    function show_rider_details(userid) {
        $("#rider_detail").html('');
        $("#imageIcons_user").show();
        $("#detail_modal_user").modal('show');
        if (userid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': {"iUserId": userid,"editTrip":"No"},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    $("#rider_detail").html(data);
                    $("#imageIcons_user").hide();
                } else {
                    $("#imageIcons_user").hide();
                }
            });
        }
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