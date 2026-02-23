<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-review-rideshare')) {
    $userObj->redirect();
}
$script = 'ride-share-review';
$FromUserType = $reviewtype = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Passenger';
$ord = ' ORDER BY iRideShareRatingId DESC';
$keyword = (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') ? $_REQUEST['keyword'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$isRideShare = isset($_REQUEST['rideShare']) ? $_REQUEST['rideShare'] : '';
if (isset($_POST['btnsubmitnew'])) {
    $reload1 = $_SERVER['HTTP_REFERER'];
    $urlparts = explode('?', $reload1);
    $parameters = $urlparts[1];

    $iRatingId = isset($_REQUEST['iRatingId']) ? $_REQUEST['iRatingId'] : '';
    $vMessage = isset($_REQUEST['vMessage']) ? $_REQUEST['vMessage'] : '';
    $q = "INSERT INTO ";
    $where = '';
    if ($iRatingId != '') {
        $q = "UPDATE ";
        $where = " WHERE `iRideShareRatingId` = '" . $iRatingId . "'";
    }
    $query = $q . " `ride_share_ratings` SET
                    `tMessage` = '" . $vMessage . "'" . $where;
    $obj->sql_query($query);
    $var_msg = "Comment upadted.";
    if (!empty($parameters)) {
        header("Location:ride_share_reviews.php?" . $parameters);
    } else {
        header("Location:ride_share_reviews.php");
    }
    exit;
}


//search
$ssql = '';
if ($keyword != '') {
    $ssql .= " AND (pr.vPublishedRideNo LIKE '%" . clean($keyword) . "%' OR rsb.vBookingNo LIKE '%" . clean($keyword) . "%') ";
}

if ($searchRider != '') {
    $ssql .= " AND rsb.iUserId = {$searchRider} ";
}
if ($searchDriver != '') {
    $ssql .= " AND pr.iUserId = {$searchDriver} ";
}
//search
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER;
$sql = "SELECT COUNT(rsr.iPublishedRideId) as Total FROM `ride_share_ratings` as rsr 
        LEFT JOIN published_rides as pr ON pr.iPublishedRideId=rsr.iPublishedRideId 
        LEFT JOIN ride_share_bookings as rsb ON rsb.iBookingId=rsr.iBookingId WHERE 1=1 AND rsr.eFromUserType =  '" . $FromUserType . "'   $ssql ";
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
        $start = 0;
        $end = $per_page;
    }
} else {
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) {
    $page = 1;
}
//Pagination End
$sql = "SELECT 
        rsr.tDate,
        rsr.iRideShareRatingId,
        rsr.tMessage,rsr.iPublishedRideId , rsr.iBookingId , pr.vPublishedRideNo,rsr.fRating,
         CONCAT(rd.vName,' ',rd.vLastName) AS DriverName,
         rd.iUserId as driverId,
        ru.iUserId as iUserId,
        rsb.vBookingNo,
        CONCAT(ru.vName,' ',ru.vLastName) AS UserName,ru.vTimeZone
        FROM `ride_share_ratings` as rsr  
        LEFT JOIN published_rides as pr ON pr.iPublishedRideId=rsr.iPublishedRideId 
        LEFT JOIN ride_share_bookings as rsb ON rsb.iBookingId=rsr.iBookingId 
        LEFT JOIN register_user as rd ON rd.iUserId=pr.iUserId
        LEFT JOIN register_user as ru ON ru.iUserId=rsb.iUserId
        WHERE rsr.eFromUserType =  '" . $FromUserType . "' $ssql LIMIT $start, $per_page ";
$data_drv = $obj->MySQLSelect($sql);

$review_data = $data_drv;

$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | RideShare Reviews</title>
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
                        <h2>RideShare Reviews</h2>
                        <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <div class="panel-heading">
                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                    <input type="hidden" name="rideShare" id="rideShare" value="1"/>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                        <tr>
                            <td style="padding-left: 10px;" width="2%">
                                <label for="textfield">
                                    <strong>Search:</strong>
                                </label>
                            </td>
                            <td style="padding-left: 10px;" width="15%">
                                <input placeholder="Booking No. OR Publish No." type="Text" id="keyword" name="keyword"
                                       value="<?php echo $keyword; ?>" class="form-control"/>
                            </td>
                            <td style="padding-left: 10px;" width="15%">
                                <select class="form-control filter-by-text" name="searchRider"
                                        data-text="Users (Booked By)" id="searchRider">
                                    <option value="">Users (Booked By)</option>
                                </select>
                            </td>
                            <td  style="padding-left: 10px;" width="15%">
                                <select class="form-control filter-by-text driver_container" name="searchDriver"
                                        data-text="Users (Published By)" id="searchDriver">
                                    <option value="">Users (Published By)</option>
                                </select>
                            </td>

                            <input type="hidden" name="reviewtype" value="<?= $reviewtype ?>">
                            <td style="padding-left: 10px;" width="30%">
                                <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                       title="Search"/>
                                <input type="button" value="Reset" class="btnalt button11"
                                       onClick="window.location.href = 'ride_share_reviews.php'"/>
                                <?php if (!empty($review_data)) { ?>
                                <button type="button" onclick="showExportTypes('ride_share_reviews')" class="btnalt button11">Export</button>
                                <?php } ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>


                <!--<form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-lg-1">
                                <label for="textfield">
                                    <strong>Search:</strong>
                                </label>
                            </div>
                            <div class="col-lg-2">
                                <input type="text" id="searchRideNo" name="searchRideNo" placeholder="Ride Number" class="form-control search-trip001" value="<?php /*= $searchRideNo */?>">
                            </div>
                            <div class="col-lg-2">

                            </div>
                            <div class="col-lg-2">
                                <select class="form-control filter-by-text driver_container" name="searchDriver"
                                        data-text="Published By(Select User)" id="searchDriver">
                                    <option value="">Published By(Select User)</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <b>
                                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                           title="Search"/>
                                    <input type="button" value="Reset" class="btnalt button11"
                                           onClick="window.location.href = 'ride_share_reviews.php'"/>
                                </b>
                            </div>
                        </div>
                    </div>
                </form>-->
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="clear:both;"></div>
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <div class="panel panel-default">
                                    <div class="panel-heading referrer-page-tab">
                                        <ul class="nav nav-tabs">
                                            <li <?php if ($reviewtype == 'Passenger') { ?> class="active" <?php } ?>>
                                                <a data-toggle="tab" onClick="getReview('Passenger')"
                                                   href="#menu1"><?= $langage_lbl_admin['LBL_DASHBOARD_USERS_ADMIN']; ?> (Booked By)</a>
                                            </li>

                                            <li <?php if ($reviewtype == 'Driver') { ?> class="active" <?php } ?>>
                                                <a data-toggle="tab" onclick="getReview('Driver')"
                                                   href="#home">Users (Published By)</a>
                                            </li>

                                        </ul>
                                    </div>
                                    <div class="panel-body">
                                        <style>

                                            .row01 {
                                                display: block;
                                                border-top: 1px solid #c1bebe;
                                                padding-top: 5px;
                                            }
                                        </style>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover"
                                                   id="dataTables-example">

                                                <?php if ($FromUserType == "Driver") { ?>
                                                    <thead>
                                                    <tr>
                                                        <th>Booking No.</th>
                                                        <th>Publish No.</th>
                                                        <th>User Name (Published By)</th>
                                                        <th>Rating By (User Name - Booked By)</th>
                                                        <th class="align-center" >Rating</th>
                                                        <th>Comment</th>
                                                        <th class="align-center">Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    if (!empty($review_data)) {
                                                        $serverTimeZone = date_default_timezone_get();
                                                        foreach ($review_data as $data) {

                                                            $date_format_data_array = array(
                                                                'langCode' => $default_lang,
                                                                'DateFormatForWeb' => 1
                                                            );
                                                            $date_format_data_array['tdate'] = (!empty($data['vTimeZone']) && $data['tDate'] != '0000-00-00 00:00:00') ? converToTz($data['tDate'],$data['vTimeZone'],$serverTimeZone) : $data['tDate'];
                                                            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                            $tDate = $get_date_format['tDisplayDate'];//DateTime($val);
                                                            ?>
                                                            <tr class="gradeA">
                                                                <td><a style="text-decoration: underline;" target="_blank" href="ride_share_bookings_details.php?iBookingId=<?php echo $data['iBookingId']; ?>" ><?php echo $data['vBookingNo']; ?></a></td>
                                                                <td><a style="text-decoration: underline;" target="_blank" href="prdetails.php?iPublishedRideId=<?php echo $data['iPublishedRideId']; ?>" ><?php echo $data['vPublishedRideNo']; ?></a></td>

                                                                <td>
                                                                    <a style="text-decoration: underline;"  href="javascript:void(0);" onClick="show_rider_details('<?= $data['driverId']; ?>')" > <?php echo clearName($data['DriverName']); ?> </a>
                                                                </td>
                                                                <td><a style="text-decoration: underline;" href="javascript:void(0);" onClick="show_rider_details('<?= $data['iUserId']; ?>')"  ><?php echo clearName($data['UserName']); ?> </a> </td>
                                                                <td align="center">
                                                                    <?php echo $data['fRating']; ?>
                                                                </td>
                                                                <td><?php echo $data['tMessage']; ?></td>
                                                                <td align="center"><?= $tDate;//DateTime($data['tDate']); ?></td>
                                                                <td><a href="javascript:void(0);"
                                                                       onClick="show_review_detail('<?= $data['iRideShareRatingId']; ?>','Edit')"
                                                                       data-toggle="tooltip"
                                                                       title="Edit">
                                                                        <img src="img/edit-icon.png"
                                                                             alt="Edit">
                                                                    </a>
                                                                    <div class="modal fade"
                                                                         id="review_package_<?= $data['iRideShareRatingId']; ?>"
                                                                         tabindex="-1" role="dialog"
                                                                         aria-labelledby="myModalLabel"
                                                                         aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h4> Edit Review
                                                                                        <button type="button"
                                                                                                class="close"
                                                                                                data-dismiss="modal">x
                                                                                        </button>
                                                                                    </h4>
                                                                                </div>
                                                                                <div class="modal-body"
                                                                                     style="max-height: 450px;overflow: auto;">
                                                                                    <div class="form-group">
                                                                                        <form id="review_package"
                                                                                              name="review_package"
                                                                                              method="post"
                                                                                              action="ride_share_reviews.php?id=<?php echo $data['iRideShareRatingId']; ?>"
                                                                                              enctype="multipart/form-data">
                                                                                            <input type="hidden"
                                                                                                   id="iRatingId"
                                                                                                   name="iRatingId"
                                                                                                   value="<?php echo $data['iRideShareRatingId']; ?>">

                                                                                            <div class="row">
                                                                                                <div class="col-lg-12">
                                                                                                    <label>Comment
                                                                                                    </label>
                                                                                                </div>
                                                                                                <div class="col-lg-12">
                                                                                                    <textarea
                                                                                                            class="form-control"
                                                                                                            name="vMessage"
                                                                                                            id="vMessage"
                                                                                                            required="required"
                                                                                                            style="height: 200px;"><?= $data['tMessage']; ?></textarea>
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

                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td colspan="7"> No Records Found.</td>
                                                        </tr>
                                                    <?php } ?>
                                                    </tbody>

                                                <?php } ?>

                                                <?php if ($FromUserType == "Passenger") { ?>
                                                    <thead>
                                                    <tr>
                                                        <th>Booking No.</th>
                                                        <th>Publish No.</th>
                                                        <th>User Name (Booked By)</th>
                                                        <th>Rating By (User Name - Published By)</th>
                                                        <th class="align-center" >Rating</th>
                                                        <th>Comment</th>
                                                        <th class="align-center" >Date</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    if (!empty($review_data)) {
                                                        $serverTimeZone = date_default_timezone_get();
                                                        foreach ($review_data as $data) {
                                                            $date_format_data_array = array(
                                                                'langCode' => $default_lang,
                                                                'DateFormatForWeb' => 1
                                                            );
                                                            $date_format_data_array['tdate'] = (!empty($data['vTimeZone']) && $data['tDate'] != '0000-00-00 00:00:00') ? converToTz($data['tDate'],$data['vTimeZone'],$serverTimeZone) : $data['tDate'];
                                                            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                            $tDate = $get_date_format['tDisplayDate'];//DateTime($val);
                                                            ?>
                                                            <tr class="gradeA">
                                                                <td><a style="text-decoration: underline;" target="_blank" href="ride_share_bookings_details.php?iBookingId=<?php echo $data['iBookingId']; ?>"><?php echo $data['vBookingNo']; ?></a></td>
                                                                <td><a style="text-decoration: underline;" target="_blank" href="prdetails.php?iPublishedRideId=<?php echo $data['iPublishedRideId']; ?>"><?php echo $data['vPublishedRideNo']; ?></a></td>
                                                                <td><a style="text-decoration: underline;" href="javascript:void(0);" onClick="show_rider_details('<?= $data['iUserId']; ?>')"  > <?php echo clearName($data['UserName']); ?> </a> </td>
                                                                <td>
                                                                    <a style="text-decoration: underline;"  href="javascript:void(0);" onClick="show_rider_details('<?= $data['driverId']; ?>')" >  <?php echo clearName($data['DriverName']); ?> </a>
                                                                </td>
                                                                <td align="center">
                                                                    <?php echo $data['fRating']; ?>
                                                                </td>
                                                                <td><?php echo $data['tMessage']; ?></td>
                                                                <td align="center"> <?= $tDate;//DateTime($data['tDate']); ?></td>
                                                                <td><a href="javascript:void(0);"
                                                                       onClick="show_review_detail('<?= $data['iRideShareRatingId']; ?>','Edit')"
                                                                       data-toggle="tooltip"
                                                                       title="Edit">
                                                                        <img src="img/edit-icon.png"
                                                                             alt="Edit">
                                                                    </a>
                                                                    <div class="modal fade"
                                                                         id="review_package_<?= $data['iRideShareRatingId']; ?>"
                                                                         tabindex="-1" role="dialog"
                                                                         aria-labelledby="myModalLabel"
                                                                         aria-hidden="true">
                                                                        <div class="modal-dialog">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h4> Edit Review
                                                                                        <button type="button"
                                                                                                class="close"
                                                                                                data-dismiss="modal">x
                                                                                        </button>
                                                                                    </h4>
                                                                                </div>
                                                                                <div class="modal-body"
                                                                                     style="max-height: 450px;overflow: auto;">
                                                                                    <div class="form-group">
                                                                                        <form id="review_package"
                                                                                              name="review_package"
                                                                                              method="post"
                                                                                              action="ride_share_reviews.php?id=<?php echo $data['iRideShareRatingId']; ?>"
                                                                                              enctype="multipart/form-data">
                                                                                            <input type="hidden"
                                                                                                   id="iRatingId"
                                                                                                   name="iRatingId"
                                                                                                   value="<?php echo $data['iRideShareRatingId']; ?>">

                                                                                            <div class="row">
                                                                                                <div class="col-lg-12">
                                                                                                    <label>Comment
                                                                                                    </label>
                                                                                                </div>
                                                                                                <div class="col-lg-12">

                                                                                                    <textarea
                                                                                                            class="form-control"
                                                                                                            name="vMessage"
                                                                                                            id="vMessage"
                                                                                                            required="required"
                                                                                                            style="height: 200px;"><?= $data['tMessage']; ?></textarea>
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
                                                                </td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <tr class="gradeA">
                                                            <td colspan="7"> No Records Found.</td>
                                                        </tr>
                                                    <?php } ?>
                                                    </tbody>

                                                <?php } ?>

                                            </table>
                                        </div>
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
<form name="pageForm" id="pageForm" action="action/review.php" method="post">
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
    <input type="hidden" name="searchRider" value="<?= $searchRider?>">
    <input type="hidden" name="searchDriver" value="<?= $searchDriver?>">
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
include_once('searchfunctions.php');
?>
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