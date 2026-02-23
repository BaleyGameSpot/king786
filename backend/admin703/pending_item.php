<?php
include_once('../common.php');
$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
if (!$userObj->hasPermission('view-pending-' . strtolower($eMasterType))) {
    $userObj->redirect();
}
$iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'Pending' . $eMasterType;
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$iRentItemId = isset($_REQUEST['iRentItemId']) ? $_REQUEST['iRentItemId'] : '';
$ord = ' ORDER BY r.iRentItemPostId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY riderName ASC"; else
        $ord = " ORDER BY riderName DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY vPlanName ASC"; else
        $ord = " ORDER BY vPlanName DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY vTitleCat ASC"; else
        $ord = " ORDER BY vTitleCat DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY r.dRentItemPostDate ASC"; else
        $ord = " ORDER BY r.dRentItemPostDate DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY r.eStatus ASC"; else
        $ord = " ORDER BY r.eStatus DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchPaymentPlan = isset($_REQUEST['searchPaymentPlan']) ? $_REQUEST['searchPaymentPlan'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$iItemSubCategoryId = isset($_REQUEST['iItemSubCategoryId']) ? $_REQUEST['iItemSubCategoryId'] : '';
if ($startDate != '') {
    $ssql .= " AND Date(r.dRentItemPostDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(r.dRentItemPostDate) <='" . $endDate . "'";
}
if ($searchRider != '') {
    $ssql .= " AND r.iUserId ='" . $searchRider . "'";
}
if ($searchPaymentPlan != "") {
    $ssql .= " AND r.iPaymentPlanId ='" . $searchPaymentPlan . "'";
}
if ($iRentItemId != '') {
    $ssql .= " AND r.iItemCategoryId ='" . $iRentItemId . "'";
}
if ($iItemSubCategoryId != "") {
    $ssql .= " AND r.iItemSubCategoryId ='" . $iItemSubCategoryId . "'";
}
$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";
}
$eTypesql = "";
if ($iMasterServiceCategoryId != "") {
    $eTypesql = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$totalData = $obj->MySQLSelect("SELECT COUNT(r.iRentItemPostId) AS Total,r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 AND r.eStatus = 'Pending' AND r.eStatus!='Deleted' $eTypesql $ssql $trp_ssql"); // New Query By HJ On 09-09-2020
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
if ($page <= 0) $page = 1;
//Pagination End
$sql = "SELECT r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(rc.vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 AND r.eStatus = 'Pending' {$eTypesql} {$ssql} {$trp_ssql}  {$ord} LIMIT {$start}, {$per_page}";
$db_trip = $obj->MySQLSelect($sql);
$endRecord = scount($db_trip);
$driverIdArr = $userIdArr = array();
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
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$Pmonday = date('Y-m-d', strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d', strtotime('sunday this week -1 week'));
$pSql = "";
if ($iMasterServiceCategoryId != "") {
    $pSql = " And iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
}
$rent_item_payment_plan = $RENTITEM_OBJ->getRentItemPaymentPlan("admin", $pSql, 0, 0, $default_lang);
if (isset($_POST['action']) && $_POST['action'] == "statusupdate") {
    $iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : "";
    $eStatus1 = isset($_REQUEST['eStatus1']) ? $_REQUEST['eStatus1'] : "";
    $iRentItemPostId = isset($_REQUEST['iRentItemPostId']) ? $_REQUEST['iRentItemPostId'] : "";
    $vRejectReason = isset($_REQUEST['reject_reason']) ? $_REQUEST['reject_reason'] : "";
    $eMasterType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "RentItem";
    $rsql = "SELECT iPaymentPlanId,iUserId FROM rentitem_post where  iRentItemPostId =  '" . $iRentItemPostId . "'";
    $rentpost_data = $obj->MySQLSelect($rsql);
    $iPaymentPlanId = $rentpost_data[0]['iPaymentPlanId'];
    $vLanguage = $default_lang;
    $getRentItemPaymentPlanAmount = $RENTITEM_OBJ->getRentItemPlan("webservice", $iPaymentPlanId, $vLanguage);
    $rejectreasonsql = "";
    if ($vRejectReason != "") {
        $rejectreasonsql = "  ,`vRejectReason`='" . $vRejectReason . "'";
    }
    $approvalsql = "";
    if ($eStatus1 == "Approved") {
        $iTotalDays = $getRentItemPaymentPlanAmount['iTotalDays'];
        $addeddate = ' +' . $iTotalDays . ' days';
        $renewdate = date('Y-m-d H:i:s', strtotime($addeddate));
        $dApprovedDate = date('Y-m-d H:i:s');
        $approvalsql = "  ,`dApprovedDate`='" . $dApprovedDate . "', `dRenewDate`='" . $renewdate . "'";
    } else {
        $dRejectedDate = date('Y-m-d H:i:s');
        $rejectreasonsql .= "  ,`dRejectedDate`='" . $dRejectedDate . "'";
    }
    $Fsql = "UPDATE `rentitem_post` SET `eStatus`='" . $eStatus1 . "' $approvalsql $rejectreasonsql WHERE iRentItemPostId ='" . $iRentItemPostId . "'";
    $obj->sql_query($Fsql);

    $getRentItemLogData = $RENTITEM_OBJ->createRentItemlog("webservice", $iRentItemPostId, $iUserId, $vLanguage);
    $user_data_order = $obj->MySQLSelect("SELECT ru.iUserId,ru.iGcmRegId,ru.eDeviceType,ru.tSessionId,ru.iAppVersion,ru.vLang,rp.vRentItemPostNo,rp.iRentItemPostId,ru.eAppTerminate,ru.eDebugMode,ru.eHmsDevice FROM rentitem_post as rp LEFT JOIN register_user as ru ON rp.iUserId=ru.iUserId where rp.iRentItemPostId = '" . $iRentItemPostId . "'");
    $vLangCodeuser = $user_data_order[0]['vLang'];
    $iRentItemPostId = $user_data_order[0]['iRentItemPostId'];
    $vRentItemPostNo = $user_data_order[0]['vRentItemPostNo'];
    $iUserIdNew = $user_data_order[0]['iUserId'];
    if ($vLangCodeuser == "" || $vLangCodeuser == NULL) {
        $vLangCodeuser = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
    }
    $reqArr = array(
        'vItemName',
        'vRentItemPostNoMail'
    );
    $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $iRentItemPostId, "", $vLangCodeuser, "", "", "", $reqArr);
    $sql = "SELECT vEmail,vLastName,vName FROM register_user where  iUserId =  '" . $iUserId . "'";
    $data_user = $obj->MySQLSelect($sql);
    $vEmail = $data_user[0]['vEmail'];
    $vName = ucfirst($data_user[0]['vName']);
    $vLastName = $data_user[0]['vLastName'];
    if ($eStatus1 == "Approved") {
        $mailTemplate = "USER_RENT_ITEM_APPROVED";
    } else if ($eStatus1 == "Reject") {
        $mailTemplate = "USER_RENT_ITEM_REJECTED";
    }
    $maildata['EMAIL'] = $vEmail;
    $maildata['NAME'] = ucfirst($vName) . ' ' . $vLastName;
    $maildata['EMAIL_NAME'] = ucfirst($vName) . ' ' . $vLastName;
    $maildata['REJECTREASON'] = $vRejectReason;
    $maildata['RENT_ITEM_NAME'] = $getRentItemPostData['vItemName'];
    $maildata['RENT_POST_NO'] = $getRentItemPostData['vRentItemPostNoMail'];
    $COMM_MEDIA_OBJ->SendMailToMember($mailTemplate, $maildata);
    ## Send Notification To User  ##
    if ($eStatus1 == "Approved") {
        $MessageUser = "PostApprovedByAdmin";
    } else {
        $MessageUser = "PostRejectByAdmin";
    }
    $languageLabelsArrUser = $LANG_OBJ->FetchLanguageLabels($vLangCodeuser, "1");
    $vTitleReasonMessage = ($vRejectReason != "") ? $vRejectReason : '';
    if ($eStatus1 == "Approved") {
        $msg1 = str_replace('#POST_TITLE#', $getRentItemPostData['vItemName'], $languageLabelsArrUser['LBL_APPROVED_RENT_POST_ADMIN_TXT']);
        $alertMsgUser = $msg1;
    } else {
        $alertMsgUser = $languageLabelsArrUser['LBL_REJECT_RENT_POST_ADMIN_TXT'] . " #" . $getRentItemPostData['vItemName'] . "# " . $languageLabelsArrUser['LBL_REASON'] . " " . $vTitleReasonMessage;
    }
    $message_arrUser = array();
    $message_arrUser['Message'] = $MessageUser;
    $message_arrUser['iRentItemPostId'] = $iRentItemPostId;
    $message_arrUser['vRentItemPostNo'] = $vRentItemPostNo;
    $message_arrUser['vTitle'] = $alertMsgUser;
    $message_arrUser['tSessionId'] = $user_data_order[0]['tSessionId'];
    $message_arrUser['eSystem'] = 'General';
    $message_arrUser['vMsgCode'] = strval(time());
    $message_arrUser['MsgType'] = $MessageUser;
    $message_arrUser['tRandomCode'] = time();
    $iAppVersionUser = $user_data_order[0]['iAppVersion'];
    $eDeviceTypeUser = $user_data_order[0]['eDeviceType'];
    $iGcmRegIdUser = $user_data_order[0]['iGcmRegId'];
    $tSessionIdUser = $user_data_order[0]['tSessionId'];
    $eAppTerminateUser = $user_data_order[0]['eAppTerminate'];
    $eDebugModeUser = $user_data_order[0]['eDebugMode'];
    $eHmsDevice = $user_data_order[0]['eHmsDevice'];
    $channelNameUser = "PASSENGER_" . $iUserIdNew;
    $generalDataArr = array();
    $generalDataArr[] = array(
        'eDeviceType' => $eDeviceTypeUser,
        'deviceToken' => $iGcmRegIdUser,
        'alertMsg' => $alertMsgUser,
        'eAppTerminate' => $eAppTerminateUser,
        'eDebugMode' => $eDebugModeUser,
        'eHmsDevice' => $eHmsDevice,
        'message' => $message_arrUser,
        'channelName' => $channelNameUser
    );
    $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_USER);
    ## Send Notification To User ##
    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_Record_Updated_successfully"];
    header("Location:" . $tconfig["tsite_url_main_admin"] . "pending_item.php?eType=" . $eMasterType);
    exit;
}
$ordersql = " ORDER BY iMasterServiceCategoryId,iDisplayOrder";
$rSql = "AND iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "' AND ( estatus = 'Active' || estatus = 'Inactive' )";
$rentitem = $RENTITEM_OBJ->getRentItemMaster('admin', $rSql, 0, 0, $default_lang, $ordersql);
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> |
        <?php if ($eMasterType == "RentItem") { ?>
            Pending Items
        <?php } else if ($eMasterType == "RentEstate") { ?>
            Pending Properties
        <?php } else if ($eMasterType == "RentCars") { ?>
            Pending Cars
        <?php } ?>
    </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style type="text/css">
        .form-group .row {
            padding: 0;
        }

        .pending-trip {
            cursor: pointer;
            position: absolute;
            margin: 2px 0 0 5px;
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
                        <h2>Pending For Approval</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page">
                    <input type="hidden" name="action" value="search"/>
                    <?php if ($eMasterType == "RentItem") { ?>
                        <h3>Search Items ...</h3>
                    <?php } else if ($eMasterType == "RentEstate") { ?>
                        <h3>Search Properties ...</h3>
                    <?php } else if ($eMasterType == "RentCars") { ?>
                        <h3>Search Cars ...</h3>
                    <?php } ?>
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
                </div>
                <div class="Posted-date form-group">
                    <div class="row">
                        <div class="col-lg-3">
                            <input type="text" id="dp4" name="startDate" placeholder="From Date Posted"
                                   class="form-control selectedDate" value="" readonly=""
                                   style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date Posted" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name='searchRider'
                                    data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>"
                                    id="searchRider">
                                <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control filter-by-text" name='searchPaymentPlan'
                                    data-text="Select Payment Plan" id="searchPaymentPlan">
                                <option value="">Select Payment Plan</option>
                                <?php foreach ($rent_item_payment_plan as $k => $PaymentPlanData) { ?>
                                    <option value="<?php echo $PaymentPlanData['iPaymentPlanId']; ?>" <?php if ($PaymentPlanData['iPaymentPlanId'] == $searchPaymentPlan) {
                                        echo 'selected';
                                    } ?> > <?php echo $PaymentPlanData['vPlanName']; ?> </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-lg-3">
                            <select name="iRentItemId" class="form-control filter-by-text" id="iRentItemId"
                                    onChange="getsubcategories(this.value,'<?= $iMasterServiceCategoryId; ?>')">
                                <option value="">Select Category</option>
                                <?php
                                foreach ($rentitem as $rentkey => $rentitemval) { ?>
                                    <option value="<?= $rentitemval['iRentItemId']; ?>" <?= $rentitemval['iRentItemId'] == $iRentItemId ? "selected" : "" ?> ><?= $rentitemval['vTitle'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php if ($eMasterType == "RentItem") { ?>
                            <div class="col-lg-3">
                                <select name="iItemSubCategoryId" class="form-control filter-by-text"
                                        id="iItemSubCategoryId">
                                    <option value="">Select Sub Category</option>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="tripBtns001">
                    <b>
                        <?php $reloadurl = "pending_item.php?eType=" . $_REQUEST['eType']; ?>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = '<?php echo $reloadurl; ?>'"/>
                        <?php if (!empty($db_trip)) { ?>
                        <button type="button" onclick="showExportTypes('pending_item')" class="export-btn001" style="float:none;">Export</button>
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
                                        <th width="8%">Post Number</th>
                                        <th width="12%">
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(1, <?php if ($sortby == '1') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)">User <?php if ($sortby == 1) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php //if ($eMasterType != "RentEstate") { ?>
                                            <th width="8%">Listing Type</th>
                                        <?php //} ?>
                                        <th width="12%" class="align-left">
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(3, <?php if ($sortby == '3') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)">Category <?php if ($sortby == 3) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if ($userObj->hasPermission('view-pending-item-details-' . strtolower($eMasterType))) { ?>
                                        <?php if ($eMasterType == "RentItem") { ?>
                                            <th width="12%" style="text-align: center;"> Item Details</th>
                                        <?php } else if ($eMasterType == "RentEstate") { ?>
                                            <th width="12%" style="text-align: center;"> Property Details</th>
                                        <?php } else if ($eMasterType == "RentCars") { ?>
                                            <th width="12%" style="text-align: center;"> Car Details</th>
                                        <?php } } ?>

                                        <th width="12%">
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(2, <?php if ($sortby == '2') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)">Payment Plan <?php if ($sortby == 2) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="12%" style="text-align: center;">
                                            <a href="javascript:void(0);"
                                               onClick="Redirect(4,  <?php if ($sortby == '4') {
                                                   echo $order;
                                               } else { ?>0<?php } ?>)">Date of Posted <?php if ($sortby == 4) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if ($userObj->hasPermission('update-status-pending-' . strtolower($eMasterType))) { ?>
                                        <th width="8%" style="text-align: center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($db_trip)) {

                                        $systemTimeZone = date_default_timezone_get();
                                        foreach ($db_trip as $k => $value) {
                                            $reqArr = array(
                                                'vCatName',
                                                'eListingTypeWeb'
                                            );
                                            $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value['iRentItemPostId'], "", $default_lang, "", "", "", $reqArr); //
                                            $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
                                            $date_format_data_array = array(
                                                'tdate' => (!empty($value['vTimeZone'])) ? converToTz($value['dRentItemPostDate'], $value['vTimeZone'], $systemTimeZone) : $value['dRentItemPostDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_RentItemPostDate_format = DateformatCls::getNewDateFormat($date_format_data_array); 
                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr>
                                                <td><?php echo $value['vRentItemPostNo'];?></td>
                                                
                                             <td><?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $value['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?> 
                                            <?= clearName($value['riderName']); ?>
                                            <?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?></td>
                                                <?php //if ($eMasterType != "RentEstate") { ?>
                                                    <td><?php echo $getRentItemPostData['eListingTypeWeb']; ?></td>
                                                <?php //} ?>
                                                <td><?php echo $categoryDataArray[0]; ?>
                                                    <br/><?php if (isset($categoryDataArray[1]) && trim($categoryDataArray[1]) != "") {
                                                        echo "(" . $categoryDataArray[1] . ")";
                                                    } ?></td>
                                                <?php if ($userObj->hasPermission('view-pending-item-details-' . strtolower($eMasterType))) { ?>
                                                <td style="text-align: center;">

                                                    <a class="btn btn-success btn-xs"
                                                       href="item-details.php?iItemPostId=<?php echo $value['iRentItemPostId'] ?>"
                                                       target="_blank">View Details 
                                                    </a>

                                                </td>
                                                <?php } ?>
                                                <td><?php echo $value['vPlanName']; ?></td>
                                                <td style="text-align: center;"><?php echo $get_RentItemPostDate_format['tDisplayDate']."<br/>".$get_RentItemPostDate_format['tDisplayTime'].$time_zone_difference_text;//DateTime($value['dRentItemPostDate']); ?></td>
                                                <?php if ($userObj->hasPermission('update-status-pending-' . strtolower($eMasterType))) { ?>
                                                <td style="text-align: center;">

                                                        <a style="background-color:#DDDDDD;color:#000;" class="btn"
                                                           href="javascript:void(0);"
                                                           onClick="approveUser('<?= $value['iRentItemPostId']; ?>','<?= $value['iUserId']; ?>')">
                                                            <i class="fa fa-check-circle" style="color:#000;"></i>
                                                            Approve
                                                        </a>
                                                        <br/>
                                                        <br/>
                                                        <a style="background-color:#DDDDDD;color:#000;" class="btn"
                                                           href="javascript:void(0);"
                                                           onClick="rejectUser('<?= $value['iRentItemPostId']; ?>','<?= $value['iUserId']; ?>')">
                                                            <i class="fa fa-check-circle" style="color:#000;"></i>
                                                            Reject
                                                        </a>
                                                </td>
                                                <?php } ?>
                                                
                                            </tr>
                                        <?php }
                                    } else { ?>
                                        <td colspan="8">No Records Found.</td>
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
    <input type="hidden" name="searchPaymentPlan" value="<?= $searchPaymentPlan; ?>">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="serachTripNo" value="<?= $serachTripNo; ?>">
    <input type="hidden" name="startDate" value="<?= $startDate; ?>">
    <input type="hidden" name="endDate" value="<?= $endDate; ?>">
    <input type="hidden" name="vStatus" value="<?= $vStatus; ?>">
    <input type="hidden" name="eType" value="<?= $_REQUEST['eType']; ?>">
    <input type="hidden" name="iTripId" id="iMainId01" value="">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="iRentItemId" value="<?= $iRentItemId ?>">
</form>
<div class="modal fade " id="approve_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Approve Item?</h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <p id="new-msg-approve" style="display: none;">Are you sure you want to approve this item?</p>
                <div id="imageIconss">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="approveuser"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade " id="reject_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">  <h4>Reject Item?</h4>  </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <p id="new-msg-reject">Are you sure you want to reject this item? </p>
                <div id="imageIconss1">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rejectuser"></div>
            </div>
        </div>
    </div>
</div>
<?php include_once('footer.php'); ?>
<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i><?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details <button type="button" class="close" data-dismiss="modal">x</button>
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
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<?php include_once('searchfunctions.php'); ?>
<script>
    var typeArr = '<?= json_encode($vehilceTypeArr, JSON_HEX_APOS); ?>';

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

            window.location.href = action + "?eType=<?php echo $_REQUEST['eType'];?>&" + formValus;

        }

    });

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

    function approveUser(iRentItemPostId, iUserId) {

        if ("<?php echo SITE_TYPE ?>" == "Demo") {

            window.location.href = "<?php echo $tconfig['tsite_url_main_admin']; ?>" + "pending_item.php?success=2&eType=<?php echo $_REQUEST['eType'];?>";

            return false;

        }

        $("#approveuser").html('');

        $("#imageIconss").show();

        $("#new-msg-approve").hide();

        $("#approve_modal").modal('show');


        if (iUserId != "") {


            var ajaxData = {

                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rentitem_user_details.php',

                'AJAX_DATA': "iUserId=" + iUserId + "&iRentItemPostId=" + iRentItemPostId + "&eStatus=Approved&eType=<?php echo $_REQUEST['eType'];?>",

                'REQUEST_DATA_TYPE': 'html'

            };

            getDataFromAjaxCall(ajaxData, function (response) {

                if (response.action == "1") {

                    var data = response.result;

                    $("#approveuser").html(data);

                    $("#imageIconss").hide();

                    $("#new-msg-approve").show();

                } else {

                    console.log(response.result);

                    $("#imageIconss").hide();

                    $("#new-msg-approve").show();
                }

            });

        }

    }


    function rejectUser(iRentItemPostId, iUserId) {

        if ("<?php echo SITE_TYPE ?>" == "Demo") {

            window.location.href = "<?php echo $tconfig['tsite_url_main_admin']; ?>" + "pending_item.php?success=2&eType=<?php echo $_REQUEST['eType'];?>";

            return false;

        }

        $("#rejectuser").html('');

        $("#imageIconss1").show();

        $("#reject_modal").modal('show');

        $("#new-msg-reject").hide();

        if (iUserId != "") {


            var ajaxData = {

                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rentitem_user_details.php',

                'AJAX_DATA': "iUserId=" + iUserId + "&iRentItemPostId=" + iRentItemPostId + "&eStatus=Reject&eType=<?php echo $_REQUEST['eType'];?>",

                'REQUEST_DATA_TYPE': 'html'

            };

            getDataFromAjaxCall(ajaxData, function (response) {

                if (response.action == "1") {

                    var data = response.result;

                    $("#rejectuser").html(data);

                    $("#imageIconss1").hide();

                    $("#new-msg-reject").show();

                } else {

                    console.log(response.result);

                    $("#imageIconss1").hide();

                    $("#new-msg-reject").show();

                }

            });

        }

    }
    <?php if (!empty($iItemSubCategoryId)) { ?>
    getsubcategories('<?php echo $iRentItemId;?>', '<?php echo $iMasterServiceCategoryId;?>', '<?php echo $iItemSubCategoryId;?>');
    <?php } ?>

    function getsubcategories(iParentId, iMasterServiceCategoryId, iItemSubCategoryId = "") {
        $("#iItemSubCategoryId").html();
        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_bsr_subCateogries.php',

            'AJAX_DATA': "iParentId=" + iParentId + "&iItemSubCategoryId=" + iItemSubCategoryId + "&iMasterServiceCategoryId=" + iMasterServiceCategoryId,

        };

        getDataFromAjaxCall(ajaxData, function (response) {

            if (response.action == "1") {

                var data = response.result;

                $("#iItemSubCategoryId").html(data);

            } else {

                console.log(response.result);

            }

        });
    }
</script>
</body>
<!-- END BODY-->
</html>