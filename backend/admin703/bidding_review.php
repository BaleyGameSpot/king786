<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-bidding-review')) {
    $userObj->redirect();
}
$script = 'BidReviews';
$type = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Driver';
$reviewtype = $type;
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iRatingId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY p.vBiddingPostNo ASC";
    else
        $ord = " ORDER BY p.vBiddingPostNo DESC";
}
if ($sortby == 2) {
    if ($reviewtype == 'Driver') {
        if ($order == 0) $ord = " ORDER BY rd.vName ASC";
        else
            $ord = " ORDER BY rd.vName DESC";
    }
    else {
        if ($order == 0) $ord = " ORDER BY ru.vName ASC";
        else
            $ord = " ORDER BY ru.vName DESC";
    }
}
if ($sortby == 6) {
    if ($reviewtype == 'Driver') {
        if ($order == 0) $ord = " ORDER BY ru.vName ASC";
        else
            $ord = " ORDER BY ru.vName DESC";
    }
    else {
        if ($order == 0) $ord = " ORDER BY rd.vName ASC";
        else
            $ord = " ORDER BY rd.vName DESC";
    }
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY r.fRating ASC";
    else
        $ord = " ORDER BY r.fRating DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY r.tDate ASC";
    else
        $ord = " ORDER BY r.tDate DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY r.tMessage ASC";
    else
        $ord = " ORDER BY r.tMessage DESC";
}
//End Sorting
$adm_ssql = "";
if (SITE_TYPE == 'Demo') {
    $adm_ssql = " And ru.tRegistrationDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        $option_new = $option;
        if ($option == 'drivername') {
            $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
        }
        if ($option == 'ridername') {
            $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";
        }
        if ($eStatus != "") {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%' AND r.eStatus = '" . clean($eStatus) . "'";
        }
        else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%'";
        }
    }
    else {
        $ssql .= " AND (p.vBiddingPostNo LIKE '%" . clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . clean($keyword) . "%' OR r.fRating LIKE '%" . clean($keyword) . "%' )";
    }
}
else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND r.eStatus = '" . clean($eStatus) . "'";
}
if ($eStatus != '') {
    $estatusquery = "";
}
else {
    $estatusquery = " AND r.eStatus != 'Deleted'";
}
// End Search Parameters
//Pagination Start
$chkusertype = "";
if ($type == "Driver") {
    $chkusertype = "Passenger";
}
else {
    $chkusertype = "Driver";
}
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT count(r.iRatingId) as Total FROM bidding_service_ratings as r LEFT JOIN bidding_post as p ON p.iBiddingPostId=r.iBiddingPostId LEFT JOIN register_driver as rd ON rd.iDriverId=p.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=p.iUserId WHERE eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' AND r.fRating != '' $estatusquery $ssql $adm_ssql";
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
    }
    else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
}
else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
$chkusertype = "";
if ($type == "Driver") {
    $chkusertype = "Passenger";
}
else {
    $chkusertype = "Driver";
}
$sql = "SELECT r.iRatingId,r.iBiddingPostId,r.fRating,r.tDate,r.eUserType,r.tDate,r.tMessage,CONCAT(rd.vName,' ',rd.vLastName) as driverName ,rd.vAvgRating,CONCAT(ru.vName,' ',ru.vLastName) as passangerName,ru.vAvgRating as passangerrate,p.iDriverId,p.iUserId,p.vBiddingPostNo,ru.vTimeZone FROM bidding_service_ratings as r LEFT JOIN bidding_post as p ON p.iBiddingPostId=r.iBiddingPostId LEFT JOIN register_driver as rd ON rd.iDriverId=p.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=p.iUserId WHERE 1=1 AND r.eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted'  AND r.fRating != '' $estatusquery $ssql $adm_ssql $ord LIMIT $start, $per_page";
//$ssql $adm_ssql $ord
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
if (isset($_POST['btnsubmitnew'])) {
    $iRatingId = isset($_POST['iRatingId']) ? $_POST['iRatingId'] : '';
    $tMessage = isset($_POST['tMessage']) ? $_POST['tMessage'] : '';
    $q = "INSERT INTO ";
    $where = '';
    if ($iRatingId != '') {
        $q = "UPDATE ";
        $where = " WHERE `iRatingId` = '" . $iRatingId . "'";
    }
    $query = $q . " `bidding_service_ratings` SET
            `tMessage` = '" . $tMessage . "'" . $where;
    $obj->sql_query($query);
    $var_msg = "Comment upadted.";
    header("Location:" . $reload);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Bidding Reviews</title>
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
                        <h2>Bidding Reviews</h2>

                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <div class="panel-heading">


                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                        <tr>
                            <td width="2%">
                                <label for="textfield">
                                    <strong>Search:</strong>
                                </label>
                            </td>
                            <td width="5%" class="padding-right10">
                                <select name="option" id="option" class="form-control">
                                    <option value="">All</option>
                                    <option value="p.vBiddingPostNo" <?php
                                    if ($option == "p.vBiddingPostNo") {
                                        echo "selected";
                                    }
                                    ?> >Bidding Number
                                    </option>
                                    <option value="drivername" <?php
                                    if ($option == "drivername") {
                                        echo "selected";
                                    }
                                    ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name
                                    </option>
                                    <option value="ridername" <?php
                                    if ($option == "ridername") {
                                        echo "selected";
                                    }
                                    ?> ><?php echo $langage_lbl_admin['LBL_RIDER']; ?> Name
                                    </option>
                                    <option value="r.fRating" <?php
                                    if ($option == 'r.fRating') {
                                        echo "selected";
                                    }
                                    ?> >Rate
                                    </option>
                                </select>
                            </td>
                            <td width="15%">
                                <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                       class="form-control"/>
                            </td>
                            <input type="hidden" name="reviewtype" value="<?= $reviewtype ?>">

                            <td width="13%" class="estatus_options" id="eStatus_options">

                                <select name="eStatus" id="eStatus" class="form-control">

                                    <option value="">Select Status</option>

                                    <option value='Active' <?php
                                    if ($eStatus == 'Active') {

                                        echo "selected";

                                    }
                                    ?> >Active

                                    </option>

                                    <option value="Deleted" <?php
                                    if ($eStatus == 'Deleted') {

                                        echo "selected";

                                    }
                                    ?> >Delete

                                    </option>

                                </select>
                            </td>
                            <td width="12%">
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                       title="Search"/>
                                <input type="button" value="Reset" class="btnalt button11"
                                       onClick="window.location.href = 'bidding_review.php'"/>
                                <?php if (!empty($data_drv)) { ?>
                                <button type="button" onclick="showExportTypes('bidding_review')" class="btnalt button11">Export</button>
                                <?php } ?>
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </form>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="clear:both;"></div>
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <div class="panel panel-default">
                                    <div class="panel-heading referrer-page-tab">
                                        <ul class="nav nav-tabs">
                                            <li <?php if ($reviewtype == 'Driver') { ?> class="active" <?php } ?>>
                                                <a data-toggle="tab" onclick="getReview('Driver')"
                                                   href="#home"><?= $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']; ?></a>
                                            </li>
                                            <li <?php if ($reviewtype == 'Passenger') { ?> class="active" <?php } ?>>
                                                <a data-toggle="tab" onClick="getReview('Passenger')"
                                                   href="#menu1"><?= $langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN']; ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover"
                                                   id="dataTables-example">
                                                <thead>
                                                <tr>
                                                    <th>
                                                        <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        }
                                                        else {
                                                            ?>0<?php } ?>)">Bidding Number <?php
                                                            if ($sortby == 1) {
                                                                if ($order == 0) {
                                                                    ?>
                                                                    <i class="fa fa-sort-amount-asc"
                                                                       aria-hidden="true"></i> <?php } else { ?>
                                                                    <i class="fa fa-sort-amount-desc"
                                                                       aria-hidden="true"></i><?php
                                                                }
                                                            }
                                                            else {
                                                                ?>
                                                                <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                        </a>
                                                    </th>
                                                    <?php if ($reviewtype == 'Driver') { ?>
                                                        <th width="15%">
                                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                            if ($sortby == '2') {
                                                                echo $order;
                                                            }
                                                            else {
                                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                                                Name <?php
                                                                if ($sortby == 2) {
                                                                    if ($order == 0) {
                                                                        ?>
                                                                        <i class="fa fa-sort-amount-asc"
                                                                           aria-hidden="true"></i> <?php } else { ?>
                                                                        <i class="fa fa-sort-amount-desc"
                                                                           aria-hidden="true"></i><?php
                                                                    }
                                                                }
                                                                else {
                                                                    ?>
                                                                    <i class="fa fa-sort"
                                                                       aria-hidden="true"></i> <?php } ?></a>
                                                        </th>

                                                        <th width="15%">
                                                            <a href="javascript:void(0);"
                                                               onClick="Redirect(6,<?php if ($sortby == '6') {
                                                                   echo $order;
                                                               }
                                                               else {
                                                                   ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>
                                                                Name <?php
                                                                if ($sortby == 6) {
                                                                    if ($order == 0) {
                                                                        ?>
                                                                        <i class="fa fa-sort-amount-asc"
                                                                           aria-hidden="true"></i> <?php } else { ?>
                                                                        <i class="fa fa-sort-amount-desc"
                                                                           aria-hidden="true"></i><?php
                                                                    }
                                                                }
                                                                else {
                                                                    ?>
                                                                    <i class="fa fa-sort"
                                                                       aria-hidden="true"></i> <?php } ?></a>
                                                        </th>
                                                    <?php } else { ?>
                                                        <th width="15%">
                                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                            if ($sortby == '2') {
                                                                echo $order;
                                                            }
                                                            else {
                                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>
                                                                Name<?php
                                                                if ($sortby == 2) {
                                                                    if ($order == 0) {
                                                                        ?>
                                                                        <i class="fa fa-sort-amount-asc"
                                                                           aria-hidden="true"></i> <?php } else { ?>
                                                                        <i class="fa fa-sort-amount-desc"
                                                                           aria-hidden="true"></i><?php
                                                                    }
                                                                }
                                                                else {
                                                                    ?>
                                                                    <i class="fa fa-sort"
                                                                       aria-hidden="true"></i> <?php } ?></a>
                                                        </th>
                                                        <th width="15%">
                                                            <a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                            if ($sortby == '6') {
                                                                echo $order;
                                                            }
                                                            else {
                                                                ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                                                Name <?php
                                                                if ($sortby == 6) {
                                                                    if ($order == 0) {
                                                                        ?>
                                                                        <i class="fa fa-sort-amount-asc"
                                                                           aria-hidden="true"></i> <?php } else { ?>
                                                                        <i class="fa fa-sort-amount-desc"
                                                                           aria-hidden="true"></i><?php }
                                                                }
                                                                else { ?>
                                                                    <i class="fa fa-sort"
                                                                       aria-hidden="true"></i> <?php } ?></a>
                                                        </th>
                                                    <? } ?>
                                                    <th width="8%" class="align-center">
                                                        <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        }
                                                        else {
                                                            ?>0<?php } ?>)">Rate <?php if ($sortby == 3) {
                                                                if ($order == 0) { ?>
                                                                    <i class="fa fa-sort-amount-asc"
                                                                       aria-hidden="true"></i> <?php } else { ?>
                                                                    <i class="fa fa-sort-amount-desc"
                                                                       aria-hidden="true"></i><?php
                                                                }
                                                            }
                                                            else {
                                                                ?>
                                                                <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                        </a>
                                                    </th>
                                                    <th width="15%" class="align-center">
                                                        <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        }
                                                        else {
                                                            ?>0<?php } ?>)">Date <?php
                                                            if ($sortby == 4) {
                                                                if ($order == 0) {
                                                                    ?>
                                                                    <i class="fa fa-sort-amount-asc"
                                                                       aria-hidden="true"></i> <?php } else { ?>
                                                                    <i class="fa fa-sort-amount-desc"
                                                                       aria-hidden="true"></i><?php
                                                                }
                                                            }
                                                            else {
                                                                ?>
                                                                <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                        </a>
                                                    </th>
                                                    <th width="15%">
                                                        <a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        }
                                                        else {
                                                            ?>0<?php } ?>)">Comment <?php
                                                            if ($sortby == 5) {
                                                                if ($order == 0) {
                                                                    ?>
                                                                    <i class="fa fa-sort-amount-asc"
                                                                       aria-hidden="true"></i> <?php } else { ?>
                                                                    <i class="fa fa-sort-amount-desc"
                                                                       aria-hidden="true"></i><?php
                                                                }
                                                            }
                                                            else {
                                                                ?>
                                                                <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                        </a>
                                                    </th>
                                                    <?php if ($userObj->hasPermission(['delete-reviews',
                                                            'edit-bidding-review']) && $eStatus != 'Deleted' && SITE_TYPE == 'Live'
                                                    ) { ?>
                                                        <th width="3%">Action</th> <?php } ?>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    $systemTimeZone = date_default_timezone_get();
                                                    for ($i = 0; $i < scount($data_drv); $i++) {
                                                        /*$default = '';
                                                        if ($data_drv[$i]['eDefault'] == 'Yes') {
                                                            $default = 'disabled';
                                                        }*/
                                                        $date_format_data_array = array(
                                                            'tdate' => (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tDate'], $data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['tDate'],
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td width="10%"><?php echo $data_drv[$i]['vBiddingPostNo']; ?></td>
                                                            <?php $vAvgRating = $passangerrate = "0.0";
                                                            if ($reviewtype == 'Driver') { ?>
                                                                <td>
                                                                    <?php if ($userObj->hasPermission('view-providers')) { ?>
                                                                    <a href="javascript:void(0);"
                                                                       onClick="show_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')"
                                                                       style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['driverName']); ?>
                                                                        <?php if ($userObj->hasPermission('view-providers')) { ?></a><?php } ?>
                                                                    <? if (isset($data_drv[$i]['vAvgRating']) && $data_drv[$i]['vAvgRating'] > 0) {
                                                                        $vAvgRating = $data_drv[$i]['vAvgRating'];
                                                                    } ?></td>

                                                                <td><?php if ($userObj->hasPermission('view-users')) { ?>
                                                                    <a href="javascript:void(0);"
                                                                       onClick="show_rider_details('<?= $data_drv[$i]['iUserId']; ?>')"
                                                                       style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['passangerName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
                                                                </td>

                                                            <?php } else { ?>
                                                                <td><?php if ($userObj->hasPermission('view-users')) { ?>
                                                                    <a href="javascript:void(0);"
                                                                       onClick="show_rider_details('<?= $data_drv[$i]['iUserId']; ?>')"
                                                                       style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['passangerName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
                                                                </td>

                                                                <td><?php if ($userObj->hasPermission('view-providers')) { ?>
                                                                    <a href="javascript:void(0);"
                                                                       onClick="show_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')"
                                                                       style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['driverName']); ?>
                                                                        <?php if ($userObj->hasPermission('view-providers')) { ?></a><?php } ?>
                                                                </td>
                                                            <? } ?>

                                                            <td align="center"> <?= $data_drv[$i]['fRating']; ?> </td>

                                                            <td align="center"><?= $get_tDate_format['tDisplayDate'];//DateTime($data_drv[$i]['tDate']); ?></td>

                                                            <td> <?= clearGeneralText($data_drv[$i]['tMessage']); ?></td>
                                                            <?php if ($userObj->hasPermission('edit-bidding-review') && SITE_TYPE == 'Live'  && $eStatus != 'Deleted') { ?>
                                                                <td align="center" style="text-align:center;"
                                                                    class="action-btn001">
                                                                    <?php if ($eStatus != 'Deleted') { /* ?>
                                                                        <div class="share-button openHoverAction-class" style="display: block;">
                                                                            <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                            <div class="social show-moreOptions for-two openPops_<?= $data_drv[$i]['iRatingId']; ?>" style="top: -15px">
                                                                                <ul>
                                                                                    <?php if($userObj->hasPermission('edit-review')){ ?>
                                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                    <a href="javascript:void(0);" onClick="show_review_detail('<?= $data_drv[$i]['iRatingId']; ?>','Edit')" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                                        </li>
                                                                                    <?php } ?>
                                                                                    <?php if($userObj->hasPermission('delete-reviews')){ ?>
                                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                                            <a href="javascript:void(0);"  onClick="changeStatusDelete('<?php echo $data_drv[$i]['iRatingId']; ?>')"  data-toggle="tooltip" title="Delete"> <img src="img/delete-icon.png" alt="Delete"> </a>
                                                                                        </li>
                                                                                    <?php } ?>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                        <?php */ ?>
                                                                        <a href="javascript:void(0);"
                                                                           onClick="show_review_detail('<?= $data_drv[$i]['iRatingId']; ?>','Edit')"
                                                                           data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a>
                                                                        <div class="modal fade"
                                                                             id="review_package_<?= $data_drv[$i]['iRatingId']; ?>"
                                                                             tabindex="-1" role="dialog"
                                                                             aria-labelledby="myModalLabel"
                                                                             aria-hidden="true">
                                                                            <div class="modal-dialog">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h4> Edit Review
                                                                                            <button type="button"
                                                                                                    class="close"
                                                                                                    data-dismiss="modal">
                                                                                                x
                                                                                            </button>
                                                                                        </h4>
                                                                                    </div>
                                                                                    <div class="modal-body"
                                                                                         style="max-height: 450px;overflow: auto;">
                                                                                        <div class="form-group">
                                                                                            <form id="review_package"
                                                                                                  name="review_package"
                                                                                                  method="post"
                                                                                                  action="review.php?id=<?php echo $data_drv[$i]['iRatingId']; ?>"
                                                                                                  enctype="multipart/form-data">
                                                                                                <input type="hidden"
                                                                                                       id="iRatingId"
                                                                                                       name="iRatingId"
                                                                                                       value="<?php echo $data_drv[$i]['iRatingId']; ?>">
                                                                                                <input type="hidden"
                                                                                                       name="frm_action"
                                                                                                       value="<?php echo $frm_action ?>">
                                                                                                <div class="row">
                                                                                                    <div class="col-lg-12">
                                                                                                        <label>Comment </label>
                                                                                                    </div>
                                                                                                    <div class="col-lg-12">
                                                                                                        <textarea
                                                                                                                class="form-control"
                                                                                                                name="tMessage"
                                                                                                                id="tMessage"
                                                                                                                required="required"
                                                                                                                style="height: 200px;"><?= $data_drv[$i]['tMessage']; ?></textarea>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="col-lg-12">
                                                                                                    <input type="submit"
                                                                                                           class="btn btn-default"
                                                                                                           name="btnsubmitnew"
                                                                                                           id="btnsubmit"
                                                                                                           value="Edit Review">
                                                                                                    <?php // } ?>
                                                                                                </div>
                                                                                            </form>
                                                                                        </div>
                                                                                        <div class="row loding-action"
                                                                                             id="loaderIcon"
                                                                                             style="display:none;">
                                                                                            <div align="center">
                                                                                                <img src="default.gif">
                                                                                                <span>Language Translation is in Process. Please Wait...</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>
                                                                </td>

                                                            <?php } ?>
                                                        </tr>
                                                        <?php
                                                    }
                                                }
                                                else { ?>
                                                    <tr class="gradeA">
                                                        <td colspan="7"> No Records Found.</td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>
                        Review module will list all reviews on this page.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/bidding_review.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iRatingId" id="iMainId01" value="">
    <input type="hidden" name="reviewtype" id="reviewtype" value="<?php echo $reviewtype; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<?php
include_once('footer.php');
?>

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


<div class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
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
                <div id="imageIcons1" style="display:none">
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

<script>
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

    function getReview(type) {
        $('#reviewtype').val(type);
        var action = $("#_list_form").attr('action');
        var formValus = $("#pageForm").serialize();
        window.location.href = action + "?" + formValus;
    }

    function show_review_detail(id, action) {
        $('#review_package_' + id).modal({
            show: 'true'
        });
        $("#review_package_" + id).submit();

    }
</script>
</body>
<!-- END BODY-->
</html>