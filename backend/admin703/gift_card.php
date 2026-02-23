<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-giftcard')) {
    $userObj->redirect();
}
$script = 'GiftCard';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iGiftCardId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY vGiftCardCode ASC"; else
        $ord = " ORDER BY vGiftCardCode DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY JSON_VALUE(tDescription,'$.tDescription_$default_lang') ASC"; else
        $ord = " ORDER BY JSON_VALUE(tDescription,'$.tDescription_$default_lang') DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY fAmount ASC"; else
        $ord = " ORDER BY fAmount DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY eStatus ASC"; else
        $ord = " ORDER BY eStatus DESC";
}
//End Sorting
//For Currency
$sql = "select vSymbol,vName from  currency where eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$GiftCardCode = isset($_REQUEST['GiftCardCode']) ? stripslashes($_REQUEST['GiftCardCode']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eRedeemed = isset($_REQUEST['eRedeemed']) ? $_REQUEST['eRedeemed'] : "No";
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$CreatedBy = isset($_REQUEST['CreatedBy']) ? $_REQUEST['CreatedBy'] : '';
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : '';
$ssql = '';
if ($searchRider != '' and $CreatedBy == 'User') {
    $ssql .= " AND iCreatedById ='" . $searchRider . "' AND eCreatedBy='User'";
} else if ($searchDriver != '' and $CreatedBy == 'Driver') {
    $ssql .= " AND iCreatedById ='" . $searchDriver . "' AND eCreatedBy='Driver'";
} else if ($CreatedBy == 'Admin') {
    $ssql .= "  AND eCreatedBy='Admin'";
}
if ($startDate != '') {
    $ssql .= " AND Date(dAddedDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(dAddedDate) <='" . $endDate . "'";
}
if ($GiftCardCode != '') {
    $ssql .= " AND (vGiftCardCode LIKE '%" . clean($GiftCardCode) . "%')";
}
if ($eStatus != '' && empty($keyword)) {
    $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
} else if ($eStatus != '') {
    $ssql .= " AND eStatus = '" . $eStatus . "'";
} else {
    $ssql .= " AND eStatus != 'Deleted'";
}
if ($eRedeemed == 'Yes') {
    $ssql .= " AND eRedeemed = 'Yes'";
} else if ($eRedeemed == '') {
    $ssql .= "";
} else {
    $ssql .= " AND eRedeemed = 'No'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iGiftCardId) AS Total FROM gift_cards WHERE 1 =1 $ssql ";
$totalData = $obj->MySQLSelect($sql);
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
$sql = "SELECT *, 
        CASE WHEN eCreatedBy='User' THEN (select CONCAT(vName ,' ',vLastName) as name FROM register_user WHERE iUserId = iCreatedById) 
         WHEN eCreatedBy='Driver' THEN (select CONCAT(vName ,' ',vLastName) as name FROM register_driver WHERE iDriverId  = iCreatedById) 
            ELSE 'Admin'
            END userName,
        JSON_UNQUOTE(JSON_VALUE(tDescription, '$.tDescription_" . $default_lang . "')) as tDescription 
        FROM gift_cards 
        WHERE 1=1 $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$driverId = $userId = [];
foreach ($data_drv as $data) {
    if ($data["eCreatedBy"] == 'Admin' || $data["eRedeemed"] == 'Yes') {
        if ($data['eUserType'] == 'DriverSpecific') {
            $driverId[] = $data['iMemberId'];
        }
        if ($data['eUserType'] == 'UserSpecific') {
            $userId[] = $data['iMemberId'];
        }
        if ($data['eUserType'] == 'Anyone' || $data['eRedeemed'] == 'Yes') {
            if ($data['eReceiverUserType'] == 'Driver') {
                $driverId[] = $data['eReceiverId'];
            }
            if ($data['eReceiverUserType'] == 'Passenger') {
                $userId[] = $data['eReceiverId'];
            }
        }
    }
}
$driverId = implode(',', $driverId);
$userId = implode(',', $userId);
if(!empty($driverId)){
    $sql = "SELECT iDriverId,concat(vName,' ',vLastName) as tReceiverName,vEmail AS tReceiverEmail,vCode AS vReceiverPhoneCode ,vPhone AS vReceiverPhone from  register_driver WHERE iDriverId IN ($driverId)";
    $registerDriver = $obj->MySQLSelect($sql);
}
if(!empty($userId)){
    $sql = "SELECT iUserId, concat(vName,' ',vLastName) as tReceiverName,vEmail AS tReceiverEmail,vPhoneCode AS vReceiverPhoneCode ,vPhone AS vReceiverPhone from  register_user WHERE iUserId IN ($userId)";
    $registerUser = $obj->MySQLSelect($sql);
}

$registerDriverArr = [];
if (isset($registerDriver) && !empty($registerDriver)) {
    foreach ($registerDriver as $Driver) {
        $registerDriverArr[$Driver['iDriverId']] = $Driver;
    }
}
$registerUserArr = [];
if (isset($registerUser) && !empty($registerUser)) {
    foreach ($registerUser as $User) {
        $registerUserArr[$User['iUserId']] = $User;
    }
}
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
    <title><?= $SITE_NAME ?> | Gift Cards</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
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
                        <h2>Gift Cards</h2>
                        <a class="add-btn" href="gift_card_action.php" style="text-align: center;">Add GIFT CARD
                        </a>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page">
                    <input type="hidden" name="action" value="search"/>
                    <h3>Search Gift Card code ...</h3>
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
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control selectedDate"
                                   value="" readonly="" style="cursor:default; background-color: #fff"/>
                        </div>
                        <div class="col-lg-3">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?> >Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?> >Inactive
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <select name="eRedeemed" id="eRedeemed" class="form-control">
                                <option value="">Select Redeem Status</option>
                                <option value='Yes' <?php
                                if ($eRedeemed == 'Yes') {
                                    echo "selected";
                                }
                                ?> >Redeemed
                                </option>
                                <option value="No" <?php
                                if ($eRedeemed == 'No') {
                                    echo "selected";
                                }
                                ?> >Not Redeemed
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <td width="15%">
                            <input placeholder="Gift Card Code" type="Text" id="GiftCardCode" name="GiftCardCode"
                                   value="<?php echo $GiftCardCode; ?>"
                                   class="form-control"/>
                        </td>
                    </div>
                    <div class="col-lg-3">
                        <select onchange="chnageUserType(this)" name="CreatedBy" id="CreatedBy" class="form-control">
                            <option value="">Created By</option>
                            <option value='User' <?php
                            if ($CreatedBy == 'User') {
                                echo "selected";
                            }
                            ?> ><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>
                            </option>
                            <option value="Driver" <?php
                            if ($CreatedBy == 'Driver') {
                                echo "selected";
                            }
                            ?> > <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                            </option>
                            <option value="Admin" <?php
                            if ($CreatedBy == 'Admin') {
                                echo "selected";
                            }
                            ?> >Admin
                            </option>
                        </select>
                    </div>
                    <div class="col-lg-3 searchDriver_div">
                        <select class="form-control filter-by-text driver_container" name='searchDriver'
                                data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>"
                                id="searchDriver">
                            <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                        </select>
                    </div>
                    <div class="col-lg-3 searchRider_div">
                        <select class="form-control filter-by-text" name='searchRider'
                                data-text="Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>"
                                id="searchRider">
                            <option value="">Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></option>
                        </select>
                    </div>
                </div>
                <div class="tripBtns001">
                    <b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                               title="Search"/>
                        <input type="button" value="Reset" class="btnalt button11"
                               onClick="window.location.href = 'gift_card.php'"/>
                    </b>
                </div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export">
                            <div class="changeStatus col-lg-12 option-box-left">
                                <span class="col-lg-2 new-select001">
                                    <?php if ($userObj->hasPermission(['delete-giftcard', 'update-status-giftcard']) && $eRedeemed != 'Yes') { ?>
                                        <select name="changeStatus" id="changeStatus" class="form-control"
                                                onchange="ChangeStatusAll(this.value);">
                                            <option value="">Select Action</option>
                                            <?php if ($userObj->hasPermission('update-status-giftcard')) { ?>
                                                <option value='Active' <?php
                                                if ($option == 'Active') {
                                                    echo "selected";
                                                }
                                                ?> >Activate
                                                </option>
                                                <option value="Inactive" <?php
                                                if ($option == 'Inactive') {
                                                    echo "selected";
                                                }
                                                ?> >Deactivate
                                                </option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </span>
                            </div>
                            <?php if (!empty($data_drv)) { ?>
                                <!--<div style = "disply:none" class="panel-heading">
                                    <form name="_export_form" id="_export_form" method="post">
                                        <button type="button" onclick="reportExportTypes('gift_card')">Export</button>
                                    </form>
                                </div>-->
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if ($userObj->hasPermission(['delete-giftcard', 'update-status-giftcard']) && $eRedeemed != 'Yes') { ?>
                                            <th align="center" width="" style="text-align:center;">
                                                <input type="checkbox" id="setAllCheck">
                                            </th>
                                        <?php } ?>
                                        <th width="">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Gift Card Code <?php
                                                if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php /* ?><th width=""><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == 2) {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Gift Card Name <?php
                                                        if ($sortby == 2) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th> <?php */ ?>
                                        <th width="">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Amount <?php
                                                if ($sortby == 3) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="">Created By</th>
                                        <th width="">Receiver Details</th>
                                        <th width="">Is Redeemed?</th>
                                        <th width="">Redeemed By</th>
                                        <th width="">Send Gift Card
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip"
                                               data-original-title="System will send an email & sms to the Receiver by pressing 'Send' button."></i>
                                        </th>
                                        <th width="" align="center" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 4) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th style="display: none">Preview</th>
                                        <th>Created Date</th>
                                        <?php if ($userObj->hasPermission(["edit-giftcard", "update-status-giftcard", "delete-giftcard"])) { ?>
                                            <th width="" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            $tReceiverDetails_ORG = $tReceiverDetails = json_decode($data_drv[$i]['tReceiverDetails'], true);
                                            if ($data_drv[$i]['eCreatedBy'] == 'Admin' || $data_drv[$i]['eRedeemed'] == 'Yes') {
                                                if ($data_drv[$i]['eUserType'] == 'DriverSpecific') {
                                                    $tReceiverDetails = isset($registerDriverArr[$data_drv[$i]['iMemberId']]) ? $registerDriverArr[$data_drv[$i]['iMemberId']] : array();
                                                    $tReceiverDetails['usertype'] = 'Driver';
                                                }
                                                if ($data_drv[$i]['eUserType'] == 'UserSpecific') {
                                                    $tReceiverDetails = isset($registerUserArr[$data_drv[$i]['iMemberId']]) ? $registerUserArr[$data_drv[$i]['iMemberId']] : array();
                                                    $tReceiverDetails['usertype'] = 'User';
                                                }
                                                if ($data_drv[$i]['eUserType'] == 'Anyone' || $data_drv[$i]['eRedeemed'] == 'Yes') {
                                                    if ($data_drv[$i]['eReceiverUserType'] == 'Driver') {
                                                        $tReceiverDetails = isset($registerDriverArr[$data_drv[$i]['eReceiverId']]) ? $registerDriverArr[$data_drv[$i]['eReceiverId']] : array();
                                                        $tReceiverDetails['usertype'] = 'Driver';
                                                    }
                                                    if ($data_drv[$i]['eReceiverUserType'] == 'Passenger') {
                                                        $tReceiverDetails = isset($registerUserArr[$data_drv[$i]['eReceiverId']]) ? $registerUserArr[$data_drv[$i]['eReceiverId']] : array();
                                                        $tReceiverDetails['usertype'] = 'User';
                                                    }
                                                }
                                            }
                                            $date_format_data_array = array(
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $date_format_data_array['tdate'] = $data_drv[$i]['dAddedDate'];
                                            $get_dAddedDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            ?>
                                            <tr class="gradeA">
                                                <?php if ($userObj->hasPermission(['delete-giftcard', 'update-status-giftcard']) && $eRedeemed != 'Yes') { ?>
                                                <td align="center" style="text-align:center;">
                                                    <?php if ($data_drv[$i]['eRedeemed'] != 'Yes') { ?>
                                                        <input type="checkbox" id="checkbox"
                                                               name="checkbox[]" <?php //echo $default; ?>
                                                               value="<?php echo $data_drv[$i]['iGiftCardId']; ?>"/>&nbsp;
                                                    <?php } ?>
                                                </td>
                                                <?php } ?>
                                                <td style="text-transform: uppercase;"><?= $data_drv[$i]['vGiftCardCode']; ?></td>
                                                <td><?= formateNumAsPerCurrency($data_drv[$i]['fAmount'], $db_currency[0]['vName']); ?></td>
                                                <td><?php
                                                    if (isset($data_drv[$i]['eCreatedBy']) && strtoupper($data_drv[$i]['eCreatedBy']) == strtoupper('admin')) {
                                                        echo $data_drv[$i]['eCreatedBy'];
                                                    } else {
                                                        if ($data_drv[$i]['eCreatedBy'] == 'User') {
                                                            ?>
                                                            <?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $data_drv[$i]['iCreatedById']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['userName']) . ' (' . $data_drv[$i]['eCreatedBy'] . ')'; ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $data_drv[$i]['iCreatedById']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['userName']) . ' (' . $data_drv[$i]['eCreatedBy'] . ')'; ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php if ($data_drv[$i]['eCreatedBy'] != 'Admin') { ?>
                                                        <a href="javascript:void(0);"
                                                           onclick="viewReceiverDetails(this)" class="btn btn-info"
                                                           data-vReceiverPhoneCode="<?= $tReceiverDetails_ORG['vReceiverPhoneCode']; ?>"
                                                           data-vReceiverPhone="<?= clearPhone($tReceiverDetails_ORG['vReceiverPhone']); ?>"
                                                           data-tReceiverMessage="<?= $tReceiverDetails_ORG['tReceiverMessage']; ?>"
                                                           data-tReceiverEmail="<?= clearEmail($tReceiverDetails_ORG['tReceiverEmail']); ?>"
                                                           data-tReceiverName="<?= clearName($tReceiverDetails_ORG['tReceiverName']); ?>"
                                                           data-code="<?= $data_drv[$i]['vGiftCardCode']; ?>"> Receiver
                                                            Details
                                                        </a>
                                                    <?php } ?></td>
                                                <td><?php echo $data_drv[$i]['eRedeemed']; ?></td>
                                                <td>
                                                    <?php if ($data_drv[$i]['eRedeemed'] == 'Yes' || ($data_drv[$i]['eCreatedBy'] == 'Admin' && in_array($data_drv[$i]['eUserType'], ['DriverSpecific', 'UserSpecific']))) { ?>
                                                        <!-- <a href="javascript:void(0);"
                                                           onclick="viewReceiverDetails(this)" class="btn btn-info"
                                                           data-vReceiverPhoneCode="<? /*= $tReceiverDetails['vReceiverPhoneCode']; */ ?>"
                                                           data-vReceiverPhone="<? /*= $tReceiverDetails['vReceiverPhone']; */ ?>"
                                                           data-tReceiverMessage="<? /*= $tReceiverDetails['tReceiverMessage']; */ ?>"
                                                           data-tReceiverEmail="<? /*= $tReceiverDetails['tReceiverEmail']; */ ?>"
                                                           data-tReceiverName="<? /*= $tReceiverDetails['tReceiverName']; */ ?>">
                                                            Redeemed By
                                                        </a>-->
                                                        <?php if ($tReceiverDetails['usertype'] == 'User') {
                                                            ?>
                                                            <?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $tReceiverDetails['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($tReceiverDetails['tReceiverName']) . ' (' . $tReceiverDetails['usertype']. ')'; ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
                                                             <?php
                                                        } else {
                                                            ?>
                                                            <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $tReceiverDetails['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($tReceiverDetails['tReceiverName']). ' (' . $tReceiverDetails['usertype'] . ')'; ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?>
                                                         <?php
                                                        }
                                                        ?>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($data_drv[$i]['eRedeemed'] == 'No' && $data_drv[$i]['eUserType'] != 'Anyone') { ?>
                                                        <a href="javascript:void(0);" onclick="sendTheInfo(this)"
                                                           class="btn btn-info"
                                                           data-tReceiverEmail="<?= $tReceiverDetails['tReceiverEmail']; ?>"
                                                           data-iGiftCardId="<?= $data_drv[$i]['iGiftCardId']; ?>"
                                                           data-toggle="modal" data-target="#uiModal1_13043">
                                                            Send
                                                        </a>
                                                    <?php } else {
                                                        echo "--";
                                                    } ?>
                                                </td>
                                                <td width="10%" align="center">
                                                    <?php
                                                    if ($data_drv[$i]['eRedeemed'] == 'No') {
                                                        if ($data_drv[$i]['eStatus'] == 'Active') {
                                                            $dis_img = "img/active-icon.png";
                                                        } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                            $dis_img = "img/inactive-icon.png";
                                                        } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                            $dis_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $dis_img; ?>"
                                                             alt="<?= $data_drv[$i]['eStatus'] ?>" data-toggle="tooltip"
                                                             title="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                    <?php } else {
                                                        echo "--";
                                                    } ?>
                                                </td>
                                                <td style="display: none">
                                                    <a class="btn btn-info" target="_blank"
                                                       href="<?php echo $tconfig['tsite_url']; ?>preview_gift_card.php?adminPreview=1&GiftCardImageId=<?= $data_drv[$i]['iGiftCardImageId']; ?>&GeneralMemberId=<?= $data_drv[$i]['iCreatedById']; ?>&GeneralUserType=<?= $data_drv[$i]['eCreatedBy']; ?>&SenderMsg=<?= $tReceiverDetails_ORG['tReceiverMessage']; ?>&Amount=<?= $data_drv[$i]['fAmount']; ?>">
                                                        Preview
                                                    </a>
                                                </td>
                                                <td width="10%" align="center">
                                                    <?= $get_dAddedDate_format['tDisplayDate'];//date('M d, Y', strtotime($data_drv[$i]['dAddedDate'])); ?>
                                                </td>
                                                <?php if ($userObj->hasPermission(["edit-giftcard", "update-status-giftcard", "delete-giftcard"])) { ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <?php if ($data_drv[$i]['eRedeemed'] == 'No') { ?>
                                                            <div class="share-button openHoverAction-class"
                                                                 style="display: block;">
                                                                <label class="entypo-export">
                                                                    <span><img src="images/settings-icon.png"
                                                                               alt=""></span>
                                                                </label>
                                                                <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iGiftCardId']; ?>">
                                                                    <ul>
                                                                        <?php if ($data_drv[$i]['eCreatedBy'] == 'Admin') { ?>
                                                                            <li class="entypo-twitter"
                                                                                data-network="twitter">
                                                                                <a href="gift_card_action.php?iGiftCardId=<?= $data_drv[$i]['iGiftCardId']; ?>"
                                                                                   data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png"
                                                                                         alt="Edit">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                      
                                                                        <?php if ($userObj->hasPermission('update-status-giftcard')) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatus('<?php echo $data_drv[$i]['iGiftCardId']; ?>', 'Inactive')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatus('<?php echo $data_drv[$i]['iGiftCardId']; ?>', 'Active')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Deactivate">
                                                                                    <img src="img/inactive-icon.png"
                                                                                         alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php if ($userObj->hasPermission('delete-giftcard')) { ?>
                                                                        <?php if ($data_drv[$i]['eCreatedBy'] == 'Admin') { ?>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatusDelete('<?php echo $data_drv[$i]['iGiftCardId']; ?>')"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        <?php } else {
                                                            echo "--";
                                                        } ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td style="text-align: center;" colspan="11"> No Records Found.</td>
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
                <h4>Notes:</h4>
                <ul>
                    <li> Gift Card module will list all gift card on this page.</li>
                    <li> Administrator can Activate / Deactivate any gift card.</li>
                    <li> Administrator can delete any gift card.</li>
                    <?php if ($CONFIG_OBJ->isOnlyCashPaymentModeAvailable()) { ?>
                        <li>
                            <strong>Gift Card</strong>
                            feature is not available in the applications as only
                            <strong>Cash</strong>
                            payment option is available in the system.
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/gift_card.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iGiftCardId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>">
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
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
<?php
include_once('footer.php');
?>
<? include_once('searchfunctions.php'); ?>
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script>
    $('.searchRider_div , .searchDriver_div').hide();
    if ("<?php echo $CreatedBy ?>" == 'User') {
        $('.searchRider_div').show();
    } else if ("<?php echo $CreatedBy ?>" == 'Driver') {
        $('.searchDriver_div').show();
    }

    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        } else {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                this.checked = '';
            });
        }
    });
    $("#Search").on('click', function () {
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });
    $('.entypo-export').click(function (e) {

        console.log('hhhhhh');
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

    function viewReceiverDetails(elem) {
        var driverList = '';
        driverList += "<table class='table table-bordered' width='100%' align='center'>";
        driverList += "<tr>";
        driverList += "<td> Name</td><td>" + $(elem).data('treceivername') + "</td>";
        driverList += "</tr>";
        driverList += "<tr>";
        driverList += "<td> Phone No.</td><td>+" + $(elem).data('vreceiverphonecode') + " " + $(elem).data('vreceiverphone') + "</td>";
        driverList += "</tr>";
        driverList += "<tr>";
        driverList += "<td> Email</td><td>" + $(elem).data('treceiveremail') + "</td>";
        driverList += "</tr>";
        driverList += "</table>";
        show_alert("Receiver Details( Gift Card: " + $(elem).data('code') + ")", driverList, "", "", "<?= $langage_lbl_admin['LBL_BTN_OK_TXT'] ?>", undefined, true, true, true);
    }

    function sendTheInfo(elem) {
        <?php if (SITE_TYPE == 'Demo') { ?>
        setTimeout(function () {
            show_alert("", "This Feature has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.", "", "", "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']) ?>");
        }, 500);

        <?php } else { ?>
        $('#loaderIcon').show();
        var tReceiverEmail = $(elem).data('treceiveremail');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_gift_card.php',
            'AJAX_DATA': {type: 'sendInfo', iGiftCardId: $(elem).data('igiftcardid')},
            'REQUEST_DATA_TYPE': 'json'
        };

        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').hide();
            var dataHtml2 = response.result;

            var mes = '';
            if (typeof dataHtml2.mail != 'undefined') {
                mes += "<?= addslashes($langage_lbl['LBL_EMAIL_SENT_TO']) ?>  " + dataHtml2.mailSend + "<br>";
            }
            if (typeof dataHtml2.sms != 'undefined') {
                mes += "<?= addslashes($langage_lbl['LBL_SMS_SENT_TO']) ?>  " + dataHtml2.smsSend;
            }

            show_alert("", mes, "", "", "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']) ?>");
        });
        <?php } ?>
    }

    function show_rider_details(userid) {
        $("#detail_modal1").modal('hide');
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
        $("#detail_modal").modal('hide');
        $("#driver_detail").html('');
        $("#imageIcons1").show();
        $("#detail_modal1").modal('show');

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
                    $("#imageIcons1").hide();
                } else {
                    $("#imageIcons1").hide();
                }
            });
        }
    }


    /* ----------------------date filter --------------------*/
    /*var startDate;
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
        if ('<?= $startDate ?>' != '') {
            $("#dp4").val('<?= $startDate ?>');
            $("#dp4").datepicker('update', '<?= $startDate ?>');
        }
        if ('<?= $endDate ?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate; ?>');
            $("#dp5").val('<?= $endDate; ?>');
        }
    });
*/

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

    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        } else {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                this.checked = '';
            });
        }
    });
    $("#Search").on('click', function () {
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });

    /* ----------------------date filter --------------------*/


    function chnageUserType(e) {
        $('.searchRider_div , .searchDriver_div').hide();
        if (e.value == 'User') {
            $('.searchRider_div').show();
        } else if (e.value == 'Driver') {
            $('.searchDriver_div').show();
        }
    }
</script>
</body>
<!-- END BODY-->
</html>