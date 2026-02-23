<?php
include_once( '../common.php' );
if ( $REFERRAL_SCHEME_ENABLE == "No" ) {
    header( 'Location: dashboard.php' );
    exit;
}
//$script = 'view-referrer';
$script = 'referrer';
$id = $_REQUEST['id'];
$etype = "";
$type = isset( $_REQUEST['eUserType'] ) ? $_REQUEST['eUserType'] : '';
if ( $type == 'Driver' ) {
    $tablename = 'register_driver';
    $iUserId = "iDriverId";
} else {
    $tablename = 'register_user';
    $iUserId = 'iUserId';
}
$query = "SELECT concat(vName, ' ' ,vLastName) as MemberName FROM " . $tablename . " WHERE " . $iUserId . " = '" . $id . "' ";
$result = $obj->MySQLSelect( $query );
$MemberName = clearName( $result[0]['MemberName'] );
if ( $type == 'Driver' ) {
    $q1 = "SELECT 'Driver' as MemberType, rd.vName,rd.vLastName,concat(rd.vName, ' ' ,rd.vLastName) as OrgName,rd.eRefType,rd.iDriverId,rd.iRefUserId,rd.dRefDate FROM register_driver as rd WHERE rd.iRefUserId = '".$id."' AND rd.eRefType = '".$type."'";
    $result_driver = $obj->MySQLSelect( $q1 );
    $q2 = "SELECT 'Rider' as MemberType, ru.vName,ru.vLastName,concat(ru.vName, ' ' ,ru.vLastName) as OrgName,ru.eRefType, ru.iUserId,ru.iRefUserId,ru.dRefDate FROM register_user as ru WHERE ru.iRefUserId = '".$id."' AND ru.eRefType = '".$type."'";
    $result_rider = $obj->MySQLSelect( $q2 );
} else {
    $q3 = "SELECT 'Driver' as MemberType, rd1.vName,rd1.vLastName,concat(rd1.vName, ' ' ,rd1.vLastName) as OrgName,ru.eRefType,rd1.iDriverId,rd1.iRefUserId,rd1.dRefDate FROM register_user as ru LEFT JOIN register_driver as rd1 on rd1.iRefUserId=ru.iUserId WHERE rd1.iRefUserId = '".$id."' AND rd1.eRefType = '".$type."'";
    $result_driver = $obj->MySQLSelect( $q3 );
    $q4 = "SELECT 'Rider' as MemberType, ru.vName,ru.vLastName,concat(ru.vName, ' ' ,ru.vLastName) as OrgName,ru.eRefType, ru.iUserId,ru.iRefUserId,ru.dRefDate FROM register_user as ru LEFT JOIN register_user as ru1 on ru1.iUserId=ru.iRefUserId WHERE ru.iRefUserId = '".$id."' AND ru.eRefType = '".$type."'";
    $result_rider = $obj->MySQLSelect( $q4 );
}
$referrerDataNew = array_merge( $result_driver, $result_rider );
/*if ($type == 'Driver') {
    $q = "SELECT rd.vName,rd.vLastName,concat(rd.vName, ' ' ,rd.vLastName) as OrgName,rd.eRefType,rd.iDriverId,rd.iRefUserId,rd.dRefDate
           FROM register_driver AS rd
           WHERE rd.iRefUserId = '" . $id . "' AND rd.eRefType = '" . $type . "'
           UNION ALL
           SELECT ru.vName,ru.vLastName,concat(ru.vName, ' ' ,ru.vLastName) as OrgName,ru.eRefType, ru.iUserId,ru.iRefUserId,ru.dRefDate
           FROM register_user AS ru
           WHERE ru.iRefUserId = '" . $id . "' AND ru.eRefType = '" . $type . "'";
} else {
    $q = "SELECT rd1.vName,rd1.vLastName,concat(rd1.vName, ' ' ,rd1.vLastName) as OrgName,ru.eRefType,rd1.iDriverId,rd1.iRefUserId,rd1.dRefDate
           FROM register_user AS ru
           LEFT JOIN register_driver AS rd1 ON rd1.iRefUserId=ru.iUserId
           WHERE ru.iRefUserId = '" . $id . "' AND ru.eRefType = '" . $type . "'
           UNION ALL
           SELECT ru.vName,ru.vLastName,concat(ru.vName, ' ' ,ru.vLastName) as OrgName,ru.eRefType, ru.iUserId,ru.iRefUserId,ru.dRefDate
           FROM register_user AS ru
           LEFT JOIN register_user AS ru1 ON ru1.iUserId=ru.iRefUserId
           WHERE ru.iRefUserId = '" . $id . "' AND ru.eRefType = '" . $type . "'";
}*/
//$referrerDataNew = $obj->MySQLSelect($q);
//Pagination Start
//$per_page = 1;
$per_page = $DISPLAY_RECORD_NUMBER;
$referrerSql = "SELECT COUNT(iUserWalletId) as count , SUM(iBalance) as iBalanceTotal FROM user_wallet WHERE iUserId = $id AND eUserType = '" . $type . "' AND eFor = 'Referrer' AND fromUserId > 0";
$referrerData = $obj->MySQLSelect( $referrerSql );
$total_results = $referrerData[0]['count'];
$iBalanceTotal = $referrerData[0]['iBalanceTotal'];
$total_pages = ceil( $total_results / $per_page );
$show_page = 1;
$start = 0;
$end = $per_page;
if ( isset( $_GET['page'] ) ) {
    $show_page = $_GET['page'];
    if ( $show_page > 0 && $show_page <= $total_pages ) {
        $start = ( $show_page - 1 ) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 0;
$tpages = $total_pages;
if ( $page <= 0 ) {
    $page = 1;
}
//Pagination End
/*$referrerSql = "SELECT 

                O.vOrderNo,BP.vBiddingPostNo,RSB.vBookingNo,RIP.vRentItemPostNo,
                TR.vRideNo,UW.iUserId,UW.eUserType,UW.fromUserId,UW.fromUserType,UW.dDate,UW.iBalance,UW.iTripId,UW.iOrderId,UW.iBiddingPostId,UW.iRentItemPostId,
                UW.iBookingId 

                FROM user_wallet as UW
                LEFT JOIN trips as TR ON TR.iTripId = UW.iTripId
                LEFT JOIN orders as O ON O.iOrderId = UW.iOrderId
                LEFT JOIN bidding_post as BP ON BP.iBiddingPostId = UW.iOrderId
                LEFT JOIN ride_share_bookings as RSB ON RSB.iBookingId = UW.iBookingId
                LEFT JOIN rentitem_post as RIP ON RIP.iRentItemPostId = UW.iRentItemPostId
                WHERE UW.iUserId = $id AND UW.eUserType = '" . $type . "' AND UW.eFor = 'Referrer' AND UW.fromUserId > 0 LIMIT $start, $per_page";*/
$tables = array("trips", "orders", "bidding_post", "ride_share_bookings", "rentitem_post");
$existing_tables = array();
foreach ($tables as $table) {
    $result = $obj->MySQLSelect("SHOW TABLES LIKE '$table'");
    if (scount($result) > 0) {
        $existing_tables[] = $table;
    }
}
$selectFields = $leftJoinFields = array();
foreach ($existing_tables as $table) {
    switch ($table) {
        case 'trips':
            $selectFields[] = "trips.vRideNo";
            $LeftJoinFields[$table] = "trips.iTripId = uw.iTripId";
            break;
        case 'orders':
            $selectFields[] = "orders.vOrderNo";
            $LeftJoinFields[$table] = "orders.iOrderId = uw.iOrderId";
            break;
        case 'bidding_post':
            $selectFields[] = "bidding_post.vBiddingPostNo";
            $LeftJoinFields[$table] = "bidding_post.iBiddingPostId = uw.iBiddingPostId";
            break;
        case 'ride_share_bookings':
            $selectFields[] = "ride_share_bookings.vBookingNo";
            $LeftJoinFields[$table] = "ride_share_bookings.iBookingId = uw.iBookingId";
            break;
        case 'rentitem_post':
            $selectFields[] = "rentitem_post.vRentItemPostNo";
            $LeftJoinFields[$table]= "rentitem_post.iRentItemPostId = uw.iRentItemPostId";
            break;
        default:
            // Handle other tables if needed
            break;
    }
}
$referrerSql = "SELECT uw.iUserId, uw.eUserType, uw.fromUserId, uw.fromUserType, uw.dDate, uw.iBalance, uw.iTripId, uw.iOrderId, uw.iBiddingPostId, uw.iRentItemPostId, uw.iBookingId," . implode(",", $selectFields) . " FROM user_wallet as uw";
foreach ($existing_tables as $table_name) {
    $leftJoinCondition = $LeftJoinFields[$table_name];
    $referrerSql .= " LEFT JOIN $table_name ON $leftJoinCondition";
}
$referrerSql .= " WHERE uw.iUserId = $id AND uw.eUserType = '$type' AND uw.eFor = 'Referrer' AND uw.fromUserId > 0 LIMIT $start, $per_page";
$referrerData = $obj->MySQLSelect( $referrerSql );

$all_referrer_data = array();
foreach ( $referrerData as $referrer ) {
    if ( $referrer['fromUserType'] == "Rider" ) {
        $tblname = 'register_user';
        $iMemberId = 'iUserId';
    } else {
        $tblname = 'register_driver';
        $iMemberId = 'iDriverId';
    }
    /*------------------2024 changes-----------------*/
    $REFERRAL_TYPE = $REFERRAL_VNO = $REFERRAL_VID = $REFERRAL_LINK = '';
    if ( !empty( $referrer['vRideNo'] ) ) {

        $REFERRAL_TYPE = "Trip";
        $REFERRAL_VNO = $referrer['vRideNo'];
        $REFERRAL_VID = $referrer['iTripId'];
        $REFERRAL_LINK = "invoice.php?iTripId=" . $REFERRAL_VID;
    } elseif ( $referrer['vBookingNo'] ) {
        $REFERRAL_TYPE = "Booking";
        $REFERRAL_VNO = $referrer['vBookingNo'];
        $REFERRAL_VID = $referrer['iBookingId'];
        $REFERRAL_LINK = "ride_share_bookings_details.php?iBookingId=" . $REFERRAL_VID;
    } elseif ( $referrer['vOrderNo'] ) {
        $REFERRAL_TYPE = "Order";
        $REFERRAL_VNO = $referrer['vOrderNo'];
        $REFERRAL_VID = $referrer['iOrderId'];
        $REFERRAL_LINK = "order_invoice.php?iOrderId=" . $REFERRAL_VID;
    } elseif ( $referrer['vBiddingPostNo'] ) {
        $REFERRAL_TYPE = "Bidding";
        $REFERRAL_VNO = $referrer['vBiddingPostNo'];
        $REFERRAL_VID = $referrer['iBiddingPostId'];
        $REFERRAL_LINK = "invoice_bids.php?iBiddingPostId=" . $REFERRAL_VID;
    }elseif ( $referrer['vRentItemPostNo']) {

        $REFERRAL_TYPE = "BSRItem";
        $REFERRAL_VNO = $referrer['vRentItemPostNo'];
        $REFERRAL_VID = $referrer['iRentItemPostId'];
        $REFERRAL_LINK = "item-details.php?iItemPostId=" . $REFERRAL_VID;
    }
    /*------------------2024 changes-----------------*/
    $refSql = "SELECT ur.tReferrerInfo,ru.$iMemberId,concat(ru.vName, ' ' ,ru.vLastName) as referrer_name FROM user_referrer_transaction as ur LEFT JOIN $tblname as ru ON ur.iMemberId = ru.$iMemberId WHERE iMemberId = " . $referrer['fromUserId'] . " AND eUserType = '" . $referrer['fromUserType'] . "'";
    $refData = $obj->MySQLSelect( $refSql );
    $tReferrerInfo = json_decode( $refData[0]['tReferrerInfo'], true );
    $fromUserId = $referrer['fromUserId'];
    $fromUserType = $referrer['fromUserType'];
    $iBalance = $referrer['iBalance'];
    $fromUserName = $refData[0]['referrer_name'];
    $date_format_data_array = array(
        'tdate' => $referrer['dDate'],
        'langCode' => $default_lang,
        'DateFormatForWeb' => 1
    );
    $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
    $referrerDate = $get_date_format['tDisplayDateTime'];// date( 'jS F Y', strtotime( $referrer['dDate'] ) ) . ' at ' . date( 'h:i A', strtotime( $referrer['dDate'] ) );
    $fromRefData = array();
    foreach ( $tReferrerInfo as $tReferrer ) {
        if ( $tReferrer['eUserType'] == "Rider" ) {
            $tblname1 = 'register_user';
            $iMemberId1 = 'iUserId';
        } else {
            $tblname1 = 'register_driver';
            $iMemberId1 = 'iDriverId';
        }
        $tRefSql = "SELECT concat(vName, ' ' ,vLastName) as referrer_name FROM $tblname1 WHERE $iMemberId1 = " . $tReferrer['iMemberId'];
        $tRefData = $obj->MySQLSelect( $tRefSql );
        $fromRefData[] = array( 'name'      => $tRefData[0]['referrer_name'],
                                'iMemberId' => $tReferrer['iMemberId'],
                                'eUserType' => $tReferrer['eUserType'] );
    }
    $all_referrer_data[] = array( 'fromUserId'    => $fromUserId,
                                  'fromUserType'  => $fromUserType,
                                  'fromUserName'  => $fromUserName,
                                  'referrerDate'  => $referrerDate,
                                  'iBalance'      => $iBalance,
                                  'fromRefData'   => $fromRefData,
                                  'REFERRAL_VNO'  => $REFERRAL_VNO,
                                  'REFERRAL_VID'  => $REFERRAL_VID,
                                  'REFERRAL_LINK' => $REFERRAL_LINK,
                                  'REFERRAL_TYPE' => $REFERRAL_TYPE,

        );
}
// echo"<pre>";print_r($referrerDataNew);die;
$var_filter = "";
foreach ( $_REQUEST as $key => $val ) {
    if ( $key != "tpages" && $key != 'page' ) {
        $var_filter .= "&$key=" . stripslashes( $val );
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
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
    <title>Admin | Referrer</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>

    <? include_once( 'global_files.php' ); ?>
    <style type="text/css">
        .full-width {
            width: 100%
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* Style the tab */
        .tab {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
        }

        /* Style the buttons that are used to open the tab content */
        .tab button {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
        }

        /* Change background color of buttons on hover */
        .tab button:hover {
            background-color: #ddd;
        }

        /* Create an active/current tablink class */
        .tab button.active {
            background-color: #ccc;
        }

        /* Style the tab content */
        /*  .tabcontent {
              display: none;
          }
  */
        div#mCSB_3_container {
            overflow: visible;
        }

        .content_right .mCustomScrollBox {
            overflow: visible;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once( 'header.php' ); ?>
    <? include_once( 'left_menu.php' ); ?>

    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">

            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $MemberName; ?> Referral Details</h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="tab">
                <button class="tablinks referalusertab" onclick="openTabContent(event, 'referalusercontent')"
                        id="defaultOpen"> Referred Members
                </button>
                <button class="tablinks tripcompletedtab" onclick="openTabContent(event, 'tripcompletedcontent')">
                    Referral Earning Details
                </button>
                <button class="tablinks TreeCharttab" onclick="openTabContent(event, 'TreeChart')">
                    Referral Chart
                </button>
            </div>
            <div class="body-div tabcontent" id="referalusercontent">
                <div class="table-list1">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default" style="border: 0;margin-bottom:0">
                                <div class="panel-body" style="padding:0">
                                    <div class="table-responsive1">
                                        <table class="table table-striped table-bordered table-hover"
                                               id="dataTables-example">
                                            <thead>
                                            <tr>
                                                <th width="25%">Referred Member Name</th>
                                                <th width="25%" style="text-align:center;" >Total Members Referred</th>
                                                <th width="25%" style="text-align:center;">Member Type</th>
                                                <th width="12%" style="text-align:center;">Date of Referred</th>
                                                <th width="13%" style="text-align:center;">View Referral Details</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            <?php
                                            $count = scount( $referrerDataNew );
                                            if ( $count > 0 ) {
                                                for ( $i = 0; $i < scount( $referrerDataNew ); $i++ ) { ?>
                                                    <tr class="gradeA">

                                                        <td>
                                                            <?php if (!empty($referrerDataNew[$i]['iDriverId'])){
                                                                $child = scount(getMemberReferUsersub($referrerDataNew[$i]['iDriverId'],$referrerDataNew[$i]['MemberType']));
                                                                ?>
                                                                <?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $referrerDataNew[ $i ]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $referrerDataNew[ $i ]['OrgName'] ); ?><?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?></a> <?php } ?>
                                                            <?php }else{
                                                                $child = scount(getMemberReferUsersub($referrerDataNew[$i]['iUserId'],$referrerDataNew[$i]['MemberType']));
                                                                ?>
                                                                <?php if ( $userObj->hasPermission( 'view-users' ) ) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $referrerDataNew[ $i ]['iUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $referrerDataNew[ $i ]['OrgName'] ); ?><?php if ( $userObj->hasPermission( 'view-users' ) ) { ?></a><?php } ?>
                                                            <?php } ?></td>

                                                        <td align="center" ><?php echo $child; ?></td>
                                                        <?php
                                                        /*$time = strtotime( $referrerDataNew[ $i ]['dRefDate'] );
                                                        $myFormatForView = date( "jS F Y", $time );*/

                                                        $date_format_data_array = array(
                                                            'tdate' => $referrerDataNew[$i]['dRefDate'],
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        ?>
                                                        <td align="center"><?= ( ( $referrerDataNew[ $i ]['iDriverId'] > 0 && !empty( $referrerDataNew[ $i ]['iDriverId'] ) ) ) ? $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] : $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?></td>
                                                        <td align="center"><?= $get_date_format['tDisplayDate']; ?></td>
                                                        <td align="center" >
                                                            <?php if($child > 0){ ?>
                                                            <?php if (!empty($referrerDataNew[$i]['iDriverId'])){ ?>
                                                                <a href="multi_level_referrer_action.php?id=<?php echo $referrerDataNew[$i]['iDriverId']; ?>&eUserType=<?php echo $referrerDataNew[$i]['MemberType']; ?>" data-toggle="tooltip" title="" target="_blank" >
                                                                    <img src="img/view-details.png">
                                                                </a>
                                                            <?php }else{ ?>
                                                                <a href="multi_level_referrer_action.php?id=<?php echo $referrerDataNew[$i]['iUserId']; ?>&eUserType=<?php echo $referrerDataNew[$i]['MemberType']; ?>" data-toggle="tooltip" title="" target="_blank" >
                                                                    <img src="img/view-details.png">
                                                                </a>
                                                            <?php } }else{

                                                                echo "-";
                                                            } ?>

                                                        </td>
                                                    </tr>

                                                <?php }
                                            } else { ?>
                                                <tr class="gradeA">
                                                    <td colspan="3" align="center"> No Details Found</td>
                                                </tr>

                                            <?php } ?>

                                            </tbody>
                                        </table>

                                    </div>

                                </div>

                            </div>
                        </div> <!--TABLE-END-->
                    </div>
                </div>

            </div>

            <div class="body-div tabcontent" id="tripcompletedcontent">
                <div class="table-list1">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default" style="border: 0;margin-bottom: 0">
                                <div class="panel-body" style="padding:0">
                                    <div class="table-responsive1">
                                        <table class="table table-striped table-bordered table-hover"
                                               id="dataTables-example">
                                            <thead>
                                            <tr>
                                                <th width="45%">
                                                    Completed


                                                    <?php
                                                    $addSlash = 0;
                                                    if($MODULES_OBJ->isRideFeatureAvailable('Yes')){
                                                        echo $langage_lbl_admin['LBL_TRIP'];
                                                        $addSlash = 1;
                                                    }

                                                    if($MODULES_OBJ->isUberXFeatureAvailable('Yes')){

                                                        if($addSlash == 1){
                                                            echo "/";
                                                        }
                                                        echo 'Jobs';
                                                        $addSlash = 1;
                                                    }


                                                    if($MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')){
                                                        if($addSlash == 1){
                                                            echo "/";
                                                        }
                                                        echo $langage_lbl_admin['LBL_ORDERS_NAME_ADMIN'];
                                                    }

                                                    if($MODULES_OBJ->isOnlyEnableRideSharingPro()){
                                                        echo "Rides";
                                                    }

                                                    ?>
                                                </th>
                                                <th width="25%" style="text-align:center;">Referral Amount
                                                    Earned/Credited (
                                                    <?php if ( $type == 'Rider' ) { ?>
                                                        <?php if ( $userObj->hasPermission( 'view-users' ) ) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $id; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $MemberName ); ?><?php if ( $userObj->hasPermission( 'view-users' ) ) { ?></a><?php } ?>
                                                    <?php } else { ?>
                                                        <?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $id; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $MemberName ); ?><?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?></a> <?php } ?>
                                                    <?php } ?>

                                                    ) <i class="icon-question-sign" data-placement="auto top"
                                                         data-toggle="tooltip"
                                                         data-original-title="Amount credited to the member's wallet. Please check the wallet report to view all transactions."></i>
                                                </th>
                                                <th width="35%" style="text-align:center;">Date of Received</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $total_ref_amount = 0;
                                            if ( scount( $all_referrer_data ) > 0 ) { ?>
                                                <?php $countRef = 1;
                                                foreach ( $all_referrer_data as $referrer_data ) {

                                                    $total_ref_amount += $referrer_data['iBalance'];
                                                    $Last_Level = $level = scount( $referrer_data['fromRefData'] );
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td>
                                                            <div class="panel-group pull-left full-width"
                                                                 id="accordion<?= $countRef ?>" role="tablist"
                                                                 aria-multiselectable="true">
                                                                <div class="panel panel-default">
                                                                    <div class="panel-heading pull-left full-width cursor-pointer referrer-list collapsed"
                                                                         role="tab" id="heading<?= $countRef ?>"
                                                                         data-toggle="collapse"
                                                                         data-parent="#accordion<?= $countRef ?>"
                                                                         href="#collapse<?= $countRef ?>"
                                                                         aria-expanded="true"
                                                                         aria-controls="collapse<?= $countRef ?>">

                                                                        <a onclick="callInvoice('<?= $referrer_data['REFERRAL_LINK']; ?>')"
                                                                           target="_blank"
                                                                           href="<?= $referrer_data['REFERRAL_LINK']; ?>"
                                                                           role="button"
                                                                           data-toggle="collapse">
                                                                            <?= $referrer_data['REFERRAL_TYPE']; ?> #<?= $referrer_data['REFERRAL_VNO']; ?>
                                                                        </a>

                                                                        <div class="pull-right">
                                                                            <b>View Referral Detail</b>
                                                                            <i class="fa fa-chevron-down pull-right"></i>

                                                                        </div>
                                                                    </div>
                                                                    <div id="collapse<?= $countRef ?>"
                                                                         class="panel-collapse collapse pull-left full-width"
                                                                         role="tabpanel"
                                                                         aria-labelledby="heading<?= $countRef ?>">
                                                                        <div class="panel-body">
                                                                            <ul class="list-group">
                                                                                <!------------------2024 changes----------------->
                                                                                <li style="display: flex;justify-content: space-between;"
                                                                                    class="list-group-item">
                                                                                    <div>
                                                                                        <?php
                                                                                        if ( $referrer_data['fromUserType'] == 'Rider' ) { ?>
                                                                                            <?php if ( $userObj->hasPermission( 'view-users' ) ) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $referrer_data['fromUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $referrer_data['fromUserName'] ); ?><?php if ( $userObj->hasPermission( 'view-users' ) ) { ?></a><?php } ?>
                                                                                        <?php } else { ?>
                                                                                            <?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $referrer_data['fromUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $referrer_data['fromUserName'] ); ?><?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?></a> <?php } ?>
                                                                                        <?php } ?>
                                                                                        <?= ( $referrer_data['fromUserType'] == 'Rider' ) ? '(User)' : '(Provider)' ?>

                                                                                        <!----------------------------------->
                                                                                        [<?= $referrer_data['REFERRAL_TYPE']; ?>
                                                                                        Completed By]
                                                                                        <!----------------------------------->
                                                                                    </div>

                                                                                    <div>
                                                                                        <?php if ( $referrer_data['fromUserId'] != $id ) { ?>
                                                                                            <a target="_blank"
                                                                                               href="multi_level_referrer_action.php?id=<?= $referrer_data['fromUserId']; ?>&eUserType=<?= ( $referrer_data['fromUserType'] == 'Rider' ) ? 'Rider' : 'Driver' ?>">
                                                                                                View Referral Report</a>
                                                                                        <?php } ?>
                                                                                    </div>
                                                                                </li>

                                                                                <!------------------2024 changes----------------->

                                                                                <?php $referrer_data['fromRefData'] = array_reverse( $referrer_data['fromRefData'] ); ?>

                                                                                <?php
                                                                              //  $level = scount($referrer_data['fromRefData'] );
                                                                                $level = 1;
                                                                                ?>
                                                                                <?php foreach ( $referrer_data['fromRefData'] as $fromRef ) { ?>
                                                                                    <li style="display: flex;justify-content: space-between;"
                                                                                        class="list-group-item">
                                                                                        <div>
                                                                                            <?php if ( $fromRef['eUserType'] == 'Rider' ) { ?>
                                                                                                <?php if ( $userObj->hasPermission( 'view-users' ) ) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $fromRef['iMemberId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $fromRef['name'] ); ?><?php if ( $userObj->hasPermission( 'view-users' ) ) { ?></a><?php } ?>
                                                                                            <?php } else { ?>
                                                                                                <?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $fromRef['iMemberId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $fromRef['name'] ); ?><?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?></a> <?php } ?>
                                                                                            <?php } ?>
                                                                                            <?= ( $fromRef['eUserType'] == 'Rider' ) ? '(User)' : '(Provider)' ?>

                                                                                            <!----------------------------------->

                                                                                            <?php echo "[Level: " . $level."]" ;
                                                                                            $EarnAmount = $level;

                                                                                            ?>

                                                                                            <?php $level++; ?>
                                                                                            <!----------------------------------->
                                                                                        </div>

                                                                                        <div>
                                                                                            <?php if ( $fromRef['iMemberId'] != $id ) { ?>
                                                                                                <a target="_blank"
                                                                                                   href="multi_level_referrer_action.php?id=<?= $fromRef['iMemberId']; ?>&eUserType=<?= ( $fromRef['eUserType'] == 'Rider' ) ? 'Rider' : 'Driver' ?>">
                                                                                                    View Referral
                                                                                                    Report</a>
                                                                                            <?php }else{
                                                                                                $Earn_Amount = $EarnAmount;
                                                                                            }?>
                                                                                        </div>
                                                                                    </li>
                                                                                <?php } ?>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td align="center">

                                                            <?= ( $referrer_data['iBalance'] > 0 ) ? formateNumAsPerCurrency( $referrer_data['iBalance'], '' ) : '--'; ?>
                                                            <br>
                                                            (Earned for referral Level <?php echo $Earn_Amount; ?>)
                                                        </td>
                                                        <td align="center"><?= $referrer_data['referrerDate'] ?></td>
                                                    </tr>
                                                    <?php $countRef++;
                                                } ?>

                                            <?php } else { ?>
                                                <tr class="gradeA">
                                                    <td colspan="2"> No Details Found</td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                            <tfoot>
                                            <tr class="gradeA">
                                                <!-- <td style="text-align:right;"><b>Total Amount Earned</b></td>
                                                <td style="text-align:center;"><?php /*echo ($total_ref_amount > 0) ? formateNumAsPerCurrency($total_ref_amount, '') : '--'; */ ?></td>-->
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <?php include( 'pagination_n.php' ); ?>

                                    <!-- ##################### Summary ##################### -->
                                    <div class="row">
                                        <div class="col-lg-6 col-lg-offset-6">
                                            <div class="admin-notes">
                                                <h4>Summary:</h4>
                                                <ul>
                                                    <li><strong>Total Amount
                                                            Earned/Credited: </strong><?= formateNumAsPerCurrency( $iBalanceTotal, '' ); ?>
                                                    </li>
                                                </ul>
                                                <b>
                                                    Please review the
                                                    <a target="_blank"
                                                       href="wallet_report.php?action=search&eUserType=<?= ( $fromRef['eUserType'] == 'Rider' ) ? 'Rider' : 'Driver' ?>&<?= ( $fromRef['eUserType'] == 'Rider' ) ? 'iUserId' : 'iDriverId' ?>=<?= $id; ?>">

                                                        Wallet Report </a>
                                                    to view all transactions
                                                    for
                                                    <?php if ( $type == 'Rider' ) { ?>
                                                        <?php if ( $userObj->hasPermission( 'view-users' ) ) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $id; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $MemberName ); ?><?php if ( $userObj->hasPermission( 'view-users' ) ) { ?></a><?php } ?>
                                                    <?php } else { ?>
                                                        <?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $id; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName( $MemberName ); ?><?php if ( $userObj->hasPermission( 'view-providers' ) ) { ?></a> <?php } ?>
                                                    <?php } ?>

                                                </b>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- ##################### Summary ##################### -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin-notes">
                    <h4>Notes:</h4>
                    <ul>
                        <li>
                            For the manage a referral levels and it's earning percentage please click on
                            <a target="_blank" href="referral_settings.php"> link</a>.

                        </li>
                    </ul>
                </div>
            </div>


            <div class="body-div tabcontent" id="TreeChart">
                <div class="table-list1">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default" style="border: 0;margin-bottom: 0">
                                <div class="panel-body" style="padding:0">
                                    <? include_once('mlm_chart_view.php'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin-notes">
                    <h4>Notes:</h4>
                    <ul>
                        <li>
                            For the manage a referral levels and it's earning percentage please click on
                            <a target="_blank" href="referral_settings.php"> link</a>.

                        </li>
                    </ul>
                </div>
            </div>



        </div>
    </div>

    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<? include_once( 'footer.php' ); ?>

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

    var TSITE_URL_MAIN_ADMIN = "<?php echo $tconfig["tsite_url_main_admin"]; ?>";

    function confirm_delete(action, id) {
        var confirm_ans = confirm("Are You sure You want to Delete this Rider?");
        if (confirm_ans == 'false') {
            return false;
        } else {
            $('#action').val(action);
            $('#iRatingId').val(id);
            document.frmreview.submit();
        }
    }

    function getReview(type) {
        $('#reviewtype').val(type);
        document.frmreview.submit();
    }

    $(document).ready(function () {
        var referrer;
        referrer = document.referrer;
        if (referrer == "") {
            referrer = "referrer.php";
        }
        $(".back_link").attr('href', referrer);
    });
    $('.referrer-list').click(function () {
        var icon = $(this).find('i');
        if (icon.hasClass('fa-chevron-up')) {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });
    // Get the element with id="defaultOpen" and click on it
    if (localStorage.getItem("referrer_tab") === "tripcompletedcontent") {
        document.getElementsByClassName("tripcompletedtab")[0].click();
    }if (localStorage.getItem("referrer_tab") === "TreeChart") {
        document.getElementsByClassName("TreeCharttab")[0].click();
    } else {
        document.getElementById("defaultOpen").click();
    }

    function openTabContent(evt, Pagename) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        document.getElementById(Pagename).style.display = "block";
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        evt.currentTarget.className += " active";
        localStorage.setItem("referrer_tab", Pagename);
    }

    function callInvoice(link) {
        var url = TSITE_URL_MAIN_ADMIN + link;
        window.open(url, '_blank');
    }
</script>
</body>
<!-- END BODY-->
</html>
