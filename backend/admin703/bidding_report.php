<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-bids-report')) {
    $userObj->redirect();
}
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'Bids';
$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And bid.dBiddingDate > '" . WEEK_DATE . "'";
}
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY bid.iBiddingPostId DESC';
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY bid.dBiddingDate ASC";
    } else {
        $ord = " ORDER BY bid.dBiddingDate DESC";
    }
}
if ($sortby == 4) {
    if ($order == 0) {
        $ord = " ORDER BY d.vName ASC";
    } else {
        $ord = " ORDER BY d.vName DESC";
    }
}
if ($sortby == 5) {
    if ($order == 0) {
        $ord = " ORDER BY ru.vName ASC";
    } else {
        $ord = " ORDER BY ru.vName DESC";
    }
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$vBiddingPostNo = isset($_REQUEST['vBiddingPostNo']) ? $_REQUEST['vBiddingPostNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$iBiddingPostId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
if ($method == 'reset' && !empty($iBiddingPostId)) {
    $userDeviceData = 1;
    $getBiddingPostData = $BIDDING_OBJ->getBiddingPost('webservice', $iBiddingPostId)[0];
    $drvdata = $obj->MySQLSelect("SELECT iTripId,vTripStatus,tSessionId,iGcmRegId,eDeviceType,CONCAT(vName,' ',vLastName) AS driverName,eAppTerminate,eDebugMode,eHmsDevice FROM register_driver WHERE iDriverId = '" . $getBiddingPostData['iDriverId'] . "'");
    $tSessionId = $drvdata[0]['tSessionId'];
    $iGcmRegId = $drvdata[0]['iGcmRegId'];
    $eDeviceType = $drvdata[0]['eDeviceType'];
    $eAppTerminate = $drvdata[0]['eAppTerminate'];
    $eDebugMode = $drvdata[0]['eDebugMode'];
    $eHmsDevice = $drvdata[0]['eHmsDevice'];
    $userdata = $obj->MySQLSelect("SELECT tSessionId,iGcmRegId,eDeviceType,vLang,eAppTerminate,eDebugMode,eHmsDevice FROM register_user WHERE iUserId = '" . $getBiddingPostData['iUserId'] . "'");
    $iGcmRegIdUser = $userdata[0]['iGcmRegId'];
    $eDeviceTypeUser = $userdata[0]['eDeviceType'];
    $eAppTerminateUser = $userdata[0]['eAppTerminate'];
    $eDebugModeUser = $userdata[0]['eDebugMode'];
    $eHmsDeviceUser = $userdata[0]['eHmsDevice'];
    $vTripStatus = "NONE";
    $where = " iBiddingPostId = '$iBiddingPostId'";
    $data_update_booking['eStatus'] = "Cancelled";
    $data_update_booking['vTaskStatus'] = "vTaskStatus";
    $data_update_booking['iCancelReasonId'] = '';
    $data_update_booking['vCancelReason'] = 'Status Reset By Admin';
    $data_update_booking['iCancelByUserId'] = 0;
    $data_update_booking['dCancelDate'] = @date("Y-m-d H:i:s");
    $data_update_booking['eCancelBy'] = '';
    $data_update_booking['iBiddingPostId'] = $iBiddingPostId;
    $id = $BIDDING_OBJ->updateBiddingPost('webservice', $data_update_booking, $where);
    $obj->sql_query("UPDATE register_driver SET vTaskStatus='" . $vTripStatus . "',iBiddingPostId=0 WHERE iDriverId = '" . $getBiddingPostData['iDriverId'] . "'  AND iBiddingPostId = '" . $iBiddingPostId . "'");
    $obj->sql_query("UPDATE register_user SET vTaskStatus='" . $vTripStatus . "',iBiddingPostId=0 WHERE iUserId = '" . $getBiddingPostData['iUserId'] . "'  AND iTripId = '" . $iBiddingPostId . "'");
    $final_message['Message'] = "BiddingTaskCancelled";
    $final_message['MsgType'] = "BiddingTaskCancelled";
    $final_message['iBiddingPostId'] = $iBiddingPostId;
    $final_message['time'] = time();
    $final_message['eType'] = "Bidding";
    $alertMsg_db = str_replace("#TASK_NO#", $getBiddingPostData['vBiddingPostNo'], $langage_lbl_admin['LBL_BIDDING_TASK_CANCELLED_ADMIN_MSG']);
    $notifiactondata[] = array(
        'eDeviceType' => $eDeviceType,
        'deviceToken' => $iGcmRegId,
        'alertMsg' => $alertMsg_db,
        'eAppTerminate' => $eAppTerminate,
        'eDebugMode' => $eDebugMode,
        'eHmsDevice' => $eHmsDevice,
        'message' => $final_message,
        'channelName' => "CAB_REQUEST_DRIVER_" . $getBiddingPostData['iDriverId']
    );
    $data = $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $notifiactondata), RN_PROVIDER);
    $final_message['time'] = time();
    $alertMsg_db = str_replace("#TASK_NO#", $getBiddingPostData['vBiddingPostNo'], $langage_lbl_admin['LBL_BIDDING_TASK_CANCELLED_ADMIN_MSG']);
    $notifiactondataUser[] = array(
        'eDeviceType' => $eDeviceTypeUser,
        'deviceToken' => $iGcmRegIdUser,
        'alertMsg' => $alertMsg_db,
        'eAppTerminate' => $eAppTerminateUser,
        'eDebugMode' => $eDebugModeUser,
        'eHmsDevice' => $eHmsDeviceUser,
        'message' => $final_message,
        'channelName' => "PASSENGER_" . $getBiddingPostData['iUserId'],
    );
    $data = $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $notifiactondataUser), RN_USER);
    header("Location:" . $tconfig["tsite_url_main_admin"] . "bidding_report.php");
    exit;
}
if ($startDate != '') {
    $ssql .= " AND Date(bid.dBiddingDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(bid.dBiddingDate) <='" . $endDate . "'";
}
if ($vBiddingPostNo != '') {
    $ssql .= " AND bid.vBiddingPostNo ='" . $vBiddingPostNo . "'";
}
if ($searchCompany != '') {
    $ssql .= " AND bid.iCompanyId ='" . $searchCompany . "'";
}
if ($searchDriver != '') {
    $ssql .= " AND bid.iDriverId ='" . $searchDriver . "'";
}
if ($searchRider != '') {
    $ssql .= " AND bid.iUserId ='" . $searchRider . "'";
}
if ($eStatus == "Accepted") {
    $ssql .= " AND (bid.eStatus = 'Accepted')";
} else if ($eStatus == "Pending") {
    $ssql .= " AND (bid.eStatus = 'Pending')";
} else if ($eStatus == "Cancelled") {
    $ssql .= " AND (bid.eStatus = 'Cancelled')";
} else if ($eStatus == "Completed") {
    $ssql .= " AND bid.eStatus = 'Completed'";
} else if ($eStatus == "Expired") {
    $ssql .= " AND bid.eStatus  IN ('Pending')  AND bid.dBiddingDate < (NOW()) - INTERVAL 30 MINUTE";
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And bid.dBiddingDate > '" . WEEK_DATE . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$totalData = $obj->MySQLSelect("SELECT COUNT(bid.iBiddingPostId) AS Total FROM bidding_post bid WHERE 1=1  $ssql $trp_ssql"); // New Query By HJ On 09-09-2020
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
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
$TimeZoneOffset = date("P");
//Pagination End
$sql = "SELECT CASE WHEN bid.eStatus ='Pending' THEN bid.dBiddingDate < ((CONVERT_TZ(NOW(), 'SYSTEM', '" . $TimeZoneOffset . "')) - INTERVAL 30 MINUTE) ELSE  '0' END  as isExpired, bid.vTaskStatus, bid.iUserId, bid.vBiddingPostNo, bid.eStatus,bid.eCancelBy,bid.iCancelReasonId,bid.vCancelReason, bid.fBiddingAmount, bid.ePaid, bid.dBiddingDate, bid.iBiddingId,bid.iBiddingPostId, CONCAT(ru.vName,' ',ru.vLastName) AS riderName, ru.iUserId, d.iDriverId, ru.iGcmRegId, ru.vPhone, ru.vPhoneCode, ru.vImgName, ua.vLatitude, ua.vLongitude, ua.vAddressType, ua.vServiceAddress,CONCAT(d.vName,' ',d.vLastName) AS driverName, ru.vTimeZone,JSON_UNQUOTE(JSON_VALUE(bs.vTitle, '$.vTitle_" . $default_lang . "')) as vTitle FROM bidding_post as bid LEFT JOIN register_user as ru ON ru.iUserId = bid.iUserId LEFT JOIN user_address as ua ON ua.iUserAddressId = bid.iAddressId LEFT JOIN register_driver d ON d.iDriverId = bid.iDriverId LEFT JOIN bidding_service as bs ON bs.iBiddingId = bid.iBiddingId WHERE 1=1 {$ssql} {$trp_ssql} {$ord} LIMIT {$start}, {$per_page}";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = scount($db_trip);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
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

    <meta charset="UTF-8"/>

    <title><?= $SITE_NAME ?> | Bidding Report</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <?php include_once('global_files.php'); ?>

    <style type="text/css">

        .form-group .row {


            padding: 0;


        }

    </style>

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

                        <h2>Bidding Report</h2>

                    </div>

                </div>

                <hr/>

            </div>

            <?php include('valid_msg.php'); ?>

            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">

                <div class="Posted-date mytrip-page">

                    <input type="hidden" name="action" value="search"/>

                    <h3>Search Bidding ...</h3>

                    <span>
                        <a style="cursor:pointer" onClick="return todayDate('dp4', 'dp5');"><?= $langage_lbl_admin['LBL_MYTRIP_Today']; ?></a>

                        <a style="cursor:pointer" onClick="return yesterdayDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Yesterday']; ?></a>

                        <a style="cursor:pointer" onClick="return currentweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Week']; ?></a>

                        <a style="cursor:pointer" onClick="return previousweekDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Week']; ?></a>

                        <a style="cursor:pointer" onClick="return currentmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Month']; ?></a>

                        <a style="cursor:pointer" onClick="return previousmonthDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous Month']; ?></a>

                        <a style="cursor:pointer" onClick="return currentyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Current_Year']; ?></a>

                        <a style="cursor:pointer" onClick="return previousyearDate('dFDate', 'dTDate');"><?= $langage_lbl_admin['LBL_MYTRIP_Previous_Year']; ?></a>

                        </span>

                </div>

                <div class="Posted-date form-group">

                    <div class="row">

                        <div class="col-lg-3">

                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control selectedDate"

                                   value="" readonly="" style="cursor:default; background-color: #fff"/>

                        </div>

                        <div class="col-lg-3">

                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control selectedDate"

                                   value="" readonly="" style="cursor:default; background-color: #fff"/>

                        </div>

                        <div class="col-lg-3">

                            <select class="form-control" name='eStatus'>

                                <option value="">All Status</option>

                                <option value="Pending" <?php if ($eStatus == "Pending") {
                                    echo "selected";
                                } ?>>Pending

                                </option>

                                <option value="Accepted" <?php
                                if ($eStatus == "Accepted") {
                                    echo "selected";
                                }
                                ?>>Accepted

                                </option>

                                <option value="Completed" <?php
                                if ($eStatus == "Completed") {
                                    echo "selected";
                                }
                                ?>>Completed

                                </option>

                                <option value="Cancelled" <?php
                                if ($eStatus == "Cancelled") {
                                    echo "selected";
                                }
                                ?>>Cancelled

                                </option>

                                <option value="Expired" <?php
                                if ($eStatus == "Expired") {
                                    echo "selected";
                                }
                                ?>>Expired

                                </option>

                            </select>

                        </div>

                        <div class="col-lg-3">

                            <input type="text" id="vBiddingPostNo" name="vBiddingPostNo"

                                   placeholder="<?= $langage_lbl_admin['LBL_BIDDING_TXT']; ?> Number"

                                   class="form-control search-trip001" value="<?= $vBiddingPostNo; ?>"/>

                        </div>

                    </div>

                </div>

                <div class="row">

                    <?php if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') { ?>

                        <div class="col-lg-3">

                            <select class="form-control filter-by-text" name='searchCompany' id="searchCompany"

                                    data-text="Select Company">

                                <option value="">Select Company</option>

                            </select>

                        </div>

                    <?php } ?>

                    <div class="col-lg-3">

                        <select class="form-control filter-by-text driver_container" name='searchDriver'

                                data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">

                            <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>

                        </select>

                    </div>

                    <div class="col-lg-3">

                        <select class="form-control filter-by-text" name='searchRider'

                                data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>"

                                id="searchRider">

                            <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>

                        </select>

                    </div>

                </div>

                <div class="tripBtns001">

                    <b>

                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'bidding_report.php'"/>
                        <?php if(!empty($db_trip)) { ?>
                        <button type="button" onclick="showExportTypes('bidding_report')" class="export-btn001">Export</button>
                        <?php } ?>

                    </b>

                </div>

            </form>

            <div class="table-list">

                <div class="row">

                    <div class="col-lg-12">

                        <div class="table-responsive">

                            <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">

                                <table class="table table-striped table-bordered table-hover">

                                    <thead>

                                    <tr>

                                        <th><?= $langage_lbl_admin['LBL_TRIP_NO_ADMIN']; ?></th>

                                        <th>Type</th>

                                        <th>Address</th>

                                        <th width="8%">

                                            <a href="javascript:void(0);"

                                               onClick="Redirect(2, <?php if ($sortby == '2') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_BIDDING_TXT'] . ' Date'; ?>



                                                <?php if ($sortby == 2) {
                                                    if ($order == 0) { ?>

                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>

                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>

                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                        </th>

                                        <th width="12%">

                                            <a href="javascript:void(0);"

                                               onClick="Redirect(4, <?php if ($sortby == '4') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>



                                                <?php if ($sortby == 4) {
                                                    if ($order == 0) { ?>

                                                        <i class="fa fa-sort-amount-asc"

                                                           aria-hidden="true"></i> <?php } else { ?>

                                                        <i class="fa fa-sort-amount-desc"

                                                                aria-hidden="true"></i><?php }
                                                } else { ?>

                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                        </th>

                                        <th width="12%">

                                            <a href="javascript:void(0);" onClick="Redirect(5,



                                            <?php if ($sortby == '5') {
                                                echo $order;
                                            } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>



                                                <?php if ($sortby == 5) {
                                                    if ($order == 0) { ?>

                                                        <i class="fa fa-sort-amount-asc"

                                                           aria-hidden="true"></i> <?php } else { ?>

                                                        <i

                                                                class="fa fa-sort-amount-desc"

                                                                aria-hidden="true"></i><?php }
                                                } else { ?>

                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                        </th>

                                        <th width="8%">Fare</th>

                                        <th width="8%">Status</th>

                                        <?php if ($userObj->hasPermission('view-bids-invoice')) { ?>

                                            <th class="align-center">View Invoice</th>

                                        <?php } ?>

                                    </tr>

                                    </thead>

                                    <tbody>

                                    <?php
                                    if (!empty($db_trip)) {
                                        $systemTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($db_trip); $i++) {
                                            $viewService = 0;
                                            $getBiddingPost = $BIDDING_OBJ->getBiddingPost('webservice', $db_trip[$i]['iBiddingPostId']);
                                            $sql = "SELECT amount FROM  `bidding_offer`  WHERE 1 = 1 AND eStatus = 'Accepted' AND iBiddingPostId = " . $db_trip[$i]['iBiddingPostId'] . "   ORDER BY `IOfferId` DESC LIMIT 1";
                                            $bidding_final_offer = $obj->MySQLSelect($sql);
                                            if (empty($bidding_final_offer)) {
                                                $bidding_final_offer[0]['amount'] = $getBiddingPost[0]['fBiddingAmount'];
                                            }
                                            $fOutStandingAmount = round($getBiddingPost[0]['fOutStandingAmount'], 2);
                                            $fWalletDebit = round($getBiddingPost[0]['fWalletDebit'], 2);

                                            $total_Fare = $bidding_final_offer[0]['amount'] + $getBiddingPost[0]['fTax1'] + $getBiddingPost[0]['fTax2'];
                                            $total_Fare = round($total_Fare, 2);


                                            $fareAmount = $total_Fare - $fWalletDebit + $fOutStandingAmount;
                                            //$fareAmount =  $BIDDING_OBJ->getbiddingFinalAmount($db_trip[$i]['iBiddingPostId']);
                                            $date_format_data_array = array(
                                                'tdate' =>  (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['dBiddingDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone) : $db_trip[$i]['dBiddingDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_BiddingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>

                                            <tr class="gradeA">

                                                <td>

                                                    <?= $db_trip[$i]['vBiddingPostNo']; ?>

                                                </td>

                                                <td>

                                                    <?= $db_trip[$i]['vTitle']; ?>

                                                </td>

                                                <td width="30%"><?php if (!empty($db_trip[$i]['vAddressType'])) {
                                                        echo $db_trip[$i]['vAddressType'] . "\n" . $db_trip[$i]['vServiceAddress'];
                                                    } else {
                                                        echo $db_trip[$i]['vServiceAddress'];
                                                    } ?>

                                                </td>

                                                <td>

                                                    <?= $get_BiddingDate_format['tDisplayDateTime'].$time_zone_difference_text;//date('d-F-Y', strtotime($db_trip[$i]['dBiddingDate'])); ?></td>

                                                <td>

                                                    <?php if (!empty($db_trip[$i]['driverName'])) {
                                                        if ($userObj->hasPermission('view-providers')) { ?>

                                                            <a href="javascript:void(0);" onClick="show_driver_details('<?= $db_trip[$i]['iDriverId']; ?>')" style="text-decoration: underline;">

                                                        <?php } ?>



                                                        <?= clearName($db_trip[$i]['driverName']); ?>

                                                        <?php if ($userObj->hasPermission('view-providers')) { ?>

                                                            </a>

                                                        <?php }
                                                    } else {
                                                        echo '-';
                                                    } ?>

                                                </td>

                                                <td>

                                                    <?php if ($userObj->hasPermission('view-users')) { ?>

                                                    <a href="javascript:void(0);"
                                                       onClick="show_rider_details('<?= $db_trip[$i]['iUserId']; ?>')"
                                                       style="text-decoration: underline;">

                                                        <?php } ?>

                                                        <?= clearName($db_trip[$i]['riderName']); ?>

                                                        <?php if ($userObj->hasPermission('view-users')) { ?>

                                                    </a>

                                                <?php } ?>

                                                </td>

                                                <td><?= formateNumAsPerCurrency($fareAmount, ''); ?></td>

                                                <td>

                                                    <?php if ($db_trip[$i]['isExpired'] == 1) {
                                                        echo $langage_lbl_admin["LBL_EXPIRED_TXT"];
                                                    } else {
                                                        echo $db_trip[$i]['eStatus'];
                                                    } ?>

                                                </td>

                                                <?php if ($userObj->hasPermission('view-bids-invoice')) { ?>

                                                    <td align="center" width="10%">

                                                        <?php if ($db_trip[$i]['eStatus'] == 'Completed') { ?>

                                                            <button class="btn btn-primary"

                                                                    onclick='return !window.open(" invoice_bids.php?iBiddingPostId=<?= $db_trip[$i]['iBiddingPostId'] ?>", "_blank")'

                                                                    ;">

                                                            <i class="icon-th-list icon-white">

                                                                <b>View Invoice</b>

                                                            </i>

                                                            </button>

                                                        <? } else if ($db_trip[$i]['eStatus'] == "Cancelled" && ($db_trip[$i]['iCancelReasonId'] > 0 || $db_trip[$i]['vCancelReason'] != '')) { ?>

                                                            <a href="javascript:void(0);" class="btn btn-info"

                                                               data-toggle="modal"

                                                               data-target="#uiModal1_<?= $db_trip[$i]['iBiddingPostId']; ?>">

                                                                Cancel

                                                                Reason

                                                            </a>

                                                        <? } else if ($db_trip[$i]['eStatus'] == "Accepted" && in_array($db_trip[$i]['vTaskStatus'], ['Ongoing'])) {
                                                            ?>

                                                            <a href="javascript:void(0);"

                                                               onClick="resetOnlyBiddingStatus('<?= $db_trip[$i]['iBiddingPostId']; ?>')"

                                                               data-toggle="tooltip" title="Reset">

                                                                Reset Bidding

                                                            </a>

                                                            <?php
                                                        } else {
                                                            echo '--';
                                                        } ?>

                                                    </td>

                                                <? } ?>

                                            </tr>

                                            <div class="modal fade" id="uiModal1_<?= $db_trip[$i]['iBiddingPostId']; ?>"

                                                 tabindex="-1" role="dialog" aria-labelledby="myModalLabel"

                                                 aria-hidden="true">

                                                <div class="modal-content image-upload-1" style="width:400px;">

                                                    <div class="upload-content" style="width:350px;">

                                                        <h3><?= $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']; ?> Cancel

                                                            Reason

                                                        </h3>

                                                        <h4>Cancel Reason:

                                                            <b

                                                                    style="font-size: 15px;font-weight: normal;">

                                                                <?php
                                                                if ($db_trip[$i]['iCancelReasonId'] > 0) {
                                                                    $cancelreasonarray = $BIDDING_OBJ->getCancelReason($db_trip[$i]['iCancelReasonId'], $default_lang);
                                                                    $db_trip[$i]['vCancelReason'] = $cancelreasonarray;
                                                                }
                                                                ?>







                                                                <?= stripcslashes($db_trip[$i]['vCancelReason']); ?></b>

                                                        </h4>

                                                        <?php if (!empty($db_trip[$i]['eCancelBy'])) {
                                                            $eCancelBy = $langage_lbl_admin['LBL_ADMIN'];
                                                            if ($db_trip[$i]['eCancelBy'] == "User") {
                                                                $eCancelBy = $langage_lbl_admin['LBL_RIDER'];
                                                            } else if ($db_trip[$i]['eCancelBy'] == "Driver") {
                                                                $eCancelBy = $langage_lbl_admin['LBL_DRIVER'];
                                                            } ?>

                                                            <h4>Cancel By:

                                                                <b

                                                                        style="font-size: 15px;font-weight: normal;"><?= stripcslashes($eCancelBy); ?></b>

                                                            </h4>

                                                        <?php } else { ?>

                                                            <h4>

                                                                <b style="font-size: 15px;font-weight: normal;"></b>

                                                            </h4>

                                                        <?php } ?>

                                                        <input type="button" class="save" data-dismiss="modal"

                                                               name="cancel" value="Close">

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="clear"></div>

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

                    </div>

                </div>

            </div>

            <div class="clear"></div>

        </div>

    </div>

    <!--END PAGE CONTENT -->

</div>

<!--END MAIN WRAPPER -->

<form name="pageForm" id="pageForm" action="" method="post">

    <input type="hidden" name="page" id="page" value="<?= $page; ?>">

    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">

    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">

    <input type="hidden" name="order" id="order" value="<?= $order; ?>">

    <input type="hidden" name="action" value="<?= $action; ?>">

    <input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>">

    <input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>">

    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">

    <input type="hidden" name="vBiddingPostNo" value="<?= $vBiddingPostNo; ?>">

    <input type="hidden" name="startDate" value="<?= $startDate; ?>">

    <input type="hidden" name="endDate" value="<?= $endDate; ?>">

    <input type="hidden" name="eStatus" value="<?= $eStatus; ?>">

    <input type="hidden" name="eType" value="<?= $eType; ?>">

    <input type="hidden" name="iTripId" id="iMainId01" value="">

    <input type="hidden" name="method" id="method" value="">

</form>

<div data-backdrop="static" data-keyboard="false" class="modal fade" id="is_resetTrip_modal_trip" tabindex="-1"

     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4>Reset Record(s) ?</h4>

            </div>

            <div class="modal-body">

                <p>Resetting <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> will end

                    the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> and release

                    the <?= strtolower($langage_lbl_admin['LBL_RIDER']); ?>

                    and <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>. Confirm to

                    reset <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?>?

                </p>

                <br/>

                <?php /* 1. <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> will be marked as cancelled and fare of <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> will set to 0.Please use this feature only when rider & driver are stuck in a trip. */ ?>

                <p>Note:

                    <br/>

                    <br/>

                    1. <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> status will be marked as Cancelled and

                    Fare of <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> will set to 0. Please use this feature only

                    when <?= $langage_lbl_admin['LBL_RIDER'] ?> & <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> are

                    stuck in the application and under any circumstances, they want to end

                    the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?>.

                    <br/>

                    <br/>

                    2. Please restart the <?= $langage_lbl_admin['LBL_RIDER'] ?>

                    and <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> application once

                    the <?= strtolower($langage_lbl_admin['LBL_TRIP_TXT_ADMIN']); ?> is reset.

                </p>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">Not Now</button>

                <a class="btn btn-success btn-ok action_modal_submit">Yes</a></div>

        </div>

    </div>

</div>

<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png"
                                                          alt=""></i><?= $langage_lbl_admin['LBL_RIDER']; ?> Details
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

                <div id="rider_detail"></div>

            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="driver_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4>

                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png"
                                                          alt=""></i><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?>
                    Details
                    <button type="button" class="close" data-dismiss="modal">x</button>

                </h4>

            </div>

            <div class="modal-body" style="max-height: 450px;overflow: auto;">

                <div id="driver_imageIcons" style="display:none">

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

<? include_once('footer.php'); ?>

<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>

<script src="../assets/js/jquery-ui.min.js"></script>

<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>

<? include_once('searchfunctions.php'); ?>

<script>

    $('#dp4').datepicker().on('changeDate', function (ev) {
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
    $('#dp5').datepicker().on('changeDate', function (ev) {
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

    function reset() {
        location.reload();
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

    function resetOnlyBiddingStatus(iAdminId) {
        $('#is_resetTrip_modal_trip').modal('show');
        $(".action_modal_submit").unbind().click(function () {
            var action = $("#pageForm").attr('action');
            var page = $("#pageId").val();
            $("#pageId01").val(page);
            $("#iMainId01").val(iAdminId);
            $("#method").val('reset');
            var formValus = $("#pageForm").serialize();
            window.location.href = action + "?" + formValus;
        });
    }

    function show_rider_details(userid) {
        $("#rider_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');
        if (userid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': "iUserId=" + userid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#rider_detail").html(data);
                    $("#imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#detail_modal").modal('hide');
                }
            });
        }
    }

    function show_driver_details(driverid) {
        $("#driver_detail").html('');
        $("#driver_imageIcons").show();
        $("#driver_detail_modal").modal('show');
        if (driverid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': "iDriverId=" + driverid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#driver_imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#driver_imageIcons").hide();
                }
            });
        }
    }

</script>

</body>

<!-- END BODY-->

</html>