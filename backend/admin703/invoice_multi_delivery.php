<?php

include_once('../common.php');
$tbl_name = 'trips';
//$ENABLE_TIP_MODULE = $CONFIG_OBJ->getConfigurations("configurations", "ENABLE_TIP_MODULE");
// $APP_DELIVERY_MODE = $CONFIG_OBJ->getConfigurations("configurations","APP_DELIVERY_MODE");
include_once('../send_invoice_receipt.php');
if (!$userObj->hasPermission('view-invoice')) {
    $userObj->redirect();
}
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
$script = "Trips";
$sql = "SELECT vName,vSymbol from currency WHERE eDefault = 'Yes'";
$currencyData = $obj->MySQLSelect($sql);
$currencycode = $currencyData[0]['vName'];
$db_trip_data = FetchTripFareDetailsWeb($iTripId, '', '');
if (!isset($db_trip_data['iTripId'])) {
    header("location: trip.php");
}
$db_reci_data = FetchDeliveryRecepientDetails($iTripId, '', '');
if (file_exists($tconfig["tsite_upload_images_driver_path"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'])) {
    $img = $tconfig["tsite_upload_images_driver"] . '/' . $db_trip_data['DriverDetails']['iDriverId'] . '/2_' . $db_trip_data['DriverDetails']['vImage'];
} else {
    $img = $tconfig["tsite_url"] . "webimages/icons/help/driver.png";
}
if (file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
    $img1 = $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'];
} else {
    $img1 = $tconfig["tsite_url"] . "webimages/icons/help/taxi_passanger.png";
}
function getUserOutstandingAmountweb($iUserId, $tripId = 0)
{
    global $obj, $data_trips;
    $whereCondi = "AND eAuthoriseIdName='No' AND iAuthoriseId=0";
    $sql = "SELECT iTripOutstandId,fPendingAmount FROM trip_outstanding_amount WHERE iUserId='" . $iUserId . "' AND iTripId='" . $tripId . "' AND ePaidByPassenger = 'No' AND ePaymentBy = 'Passenger' AND fPendingAmount >0 $whereCondi";
    $getOutStandingAmt = $obj->MySQLSelect($sql);
    $returnArr['iTripOutstandId'] = $getOutStandingAmt[0]['iTripOutstandId'];
    $returnArr['fPendingAmount'] = $getOutStandingAmt[0]['fPendingAmount'];
    return $returnArr;
}

$outstandingamount = getUserOutstandingAmountweb($db_trip_data['iUserId'], $db_trip_data['iTripId']);
/* Start original route */
$sql = "SELECT vReceiverLatitude,vReceiverLongitude FROM trips_delivery_locations WHERE iTripId = '" . $iTripId . "' ORDER BY iTripDeliveryLocationId ASC";
$data_locations = $obj->MySQLSelect($sql);
$fromarray = array('vReceiverLatitude'  => $db_trip_data['tStartLat'],
                   'vReceiverLongitude' => $db_trip_data['tStartLong']
);
array_unshift($data_locations, $fromarray);
$jsLocations = '[';
$jsLocationsNew = '[';
foreach ($data_locations as $k => $location) {
    $number = $k;
    $jsLocations .= "{lat: {$location['vReceiverLatitude']}, lng: {$location['vReceiverLongitude']}, label: '{$number}'},";
    $jsLocationsNew .= "{lat: {$location['vReceiverLatitude']}, lng: {$location['vReceiverLongitude']}},";
}
$jsLocations = rtrim($jsLocations, ',') . ']';
$jsLocationsNew = rtrim($jsLocationsNew, ',') . ']';
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
    <title>Admin | Invoice</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <?php include_once('global_files.php'); ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <?php $APP_DELIVERY_MODE = "Multi"; ?>
    <!--PAGE CONTENT -->
    <div id="content">

        <div class="inner" id="page_height" style="">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Invoice</h2>
                    <!-- <a href="mytrip.php">-->
                    <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                    <!-- </a> -->
                    <div style="clear:both;"></div>
                </div>
            </div>
            <hr/>
            <?php if ($_REQUEST['success'] == 1) { ?>
                <div class="alert alert-success paddiing-10">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    Email send successfully.
                </div>
            <?php } ?>
            <?php
            $systemTimeZone = date_default_timezone_get();
            if ($db_trip_data['fCancellationFare'] > 0 && $db_trip_data['vTimeZone'] != "") {
                $dBookingDate = $endDate = converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
            } else if ($db_trip_data['tTripRequestDateOrig'] != "" && $db_trip_data['vTimeZone'] != "") {
                $dBookingDate = converToTz($db_trip_data['tTripRequestDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
                $endDate = converToTz($db_trip_data['tEndDateOrig'], $db_trip_data['vTimeZone'], $systemTimeZone);
            } else {
                $dBookingDate = $db_trip_data['tTripRequestDateOrig'];
                $endDate = $db_trip_data['tEndDateOrig'];
            }
            ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <b>Your <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> </b>
                                <?php
                                if (($db_trip_data['tTripRequestDateOrig'] == "0000-00-00 00:00:00")) {
                                    echo "Was Cancelled.";
                                } else {
                                    echo @date('h:i A', @strtotime($dBookingDate));
                                    ?> on <?= @date('d M Y', @strtotime($dBookingDate));
                                }
                                ?>
                            </div>
                            <div class="panel-body rider-invoice-new">
                                <div class="row">

                                    <div class="col-sm-6 rider-invoice-new-left">
                                        <?php //if ($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?>
                                        <div id="map-canvas" class="gmap3"
                                             style="width:100%;height:200px;margin-bottom:10px;"></div>
                                        <?php //} ?>
                                        <span class="location-from"><i class="icon-map-marker"></i>
                                                <b><?= @date('h:i A', @strtotime($dBookingDate)); ?><p><?= $db_trip_data['tSaddress']; ?></p></b></span>
                                        <?php if ($db_trip_data['eType'] != 'UberX' && ($APP_DELIVERY_MODE != "Multi" || $db_trip_data['eType'] == "Ride")) { ?>
                                            <span class="location-to"><i
                                                        class="icon-map-marker"></i> <b><?= @date('h:i A', @strtotime($endDate)); ?><p><?= $db_trip_data['tDaddress']; ?></p></b></span>
                                        <?php } ?>

                                        <?php
                                        if ($db_trip_data['eType'] == 'UberX') {
                                            $class_name = 'col-sm-6';
                                            $style = "style='text-align:center;width:100%;'";
                                        } else {
                                            $class_name = 'col-sm-4';
                                            $style = '';
                                        }
                                        ?>
                                        <div class="rider-invoice-bottom">
                                            <div class="<?php echo $class_name; ?>" <?= $style; ?> >
                                                <?php if ($db_trip_data['eType'] == 'UberX') { ?> Service Type
                                                    <?php
                                                } else {
                                                    echo $langage_lbl_admin['LBL_CAR_TXT_ADMIN'];
                                                }
                                                ?> <br/>
                                                <b>
                                                    <?php
                                                    if (!empty($db_trip_data['vVehicleCategory'])) {
                                                        echo $db_trip_data['vVehicleCategory'] . "-" . $db_trip_data['vVehicleType'];
                                                    } else {
                                                        echo $db_trip_data['carTypeName'];
                                                    }
                                                    ?>
                                                </b><br/>
                                            </div>

                                            <?php if ($db_trip_data['eType'] != 'UberX') { ?>

                                                <div class="<?php echo $class_name; ?>">
                                                    Distance<br/>
                                                    <b><?= $db_trip_data['fDistance'] . $db_trip_data['DisplayDistanceTxt']; ?></b>
                                                    <br/>
                                                </div>
                                                <div class="<?php echo $class_name; ?>">
                                                    <?php echo $langage_lbl_admin['LBL_DELIVERY']; ?> time<br/>
                                                    <b><? echo $db_trip_data['TripTimeInMinutes']; ?></b>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <?php if ((!empty($db_trip_data['vSignImage'])) && $APP_DELIVERY_MODE == 'Multi') { ?>
                                            <div class="rider-invoice-bottom">
                                                <div class="col-sm-6">
                                                    <b><?php echo $langage_lbl_admin['LBL_SENDER_SIGN']; ?></b>
                                                </div>
                                                <?php
                                                if (file_exists($tconfig["tsite_upload_trip_signature_images_path"] . '/' . $db_trip_data['vSignImage'])) {
                                                    $img123 = $tconfig["tsite_upload_trip_signature_images"] . '/' . $db_trip_data['vSignImage'];
                                                }
                                                ?>
                                                <div class="col-sm-6">
                                                    <img src="<?php echo $img123; ?>" align="left"
                                                         style="width: 100px;">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <? if ($APP_DELIVERY_MODE != 'Multi') { ?>
                                            <div class="rider-invoice-bottom">
                                                <div class="col-sm-6">
                                                    <div class="left col-sm-3">
                                                        <img src="<?php echo $img; ?>"
                                                             style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                             align="left" height="45" width="45" class="CToWUd">
                                                    </div>
                                                    <div class="right col-sm-9" style="word-wrap: break-word;">
                                                        <div>
                                                            <b><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b>
                                                        </div>
                                                        <!--<div><?php echo clearName($db_trip_data['DriverDetails']['vName']) . "&nbsp;" . clearName($db_trip_data['DriverDetails']['vLastName']); ?></div>-->
                                                        <div><?php echo clearName($db_trip_data['DriverDetails']['vName'] . " " . $db_trip_data['DriverDetails']['vLastName']); ?></div>
                                                        <div><?php echo clearEmail($db_trip_data['DriverDetails']['vEmail']); ?></div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="left col-sm-3">
                                                        <img src="<?php echo $img1; ?>"
                                                             style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                             align="left" height="45" width="45" class="CToWUd">
                                                    </div>
                                                    <div class="right col-sm-9" style="word-wrap: break-word;">
                                                        <div><b><?php echo $langage_lbl_admin['LBL_RIDER']; ?></b></div>
                                                        <!--<div><?php echo clearName($db_trip_data['PassengerDetails']['vName']) . "&nbsp;" . clearName($db_trip_data['PassengerDetails']['vLastName']); ?></div>-->
                                                        <div><?php echo clearName($db_trip_data['PassengerDetails']['vName'] . " " . $db_trip_data['PassengerDetails']['vLastName']); ?></div>
                                                        <div><?php echo clearEmail($db_trip_data['PassengerDetails']['vEmail']); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <? } ?>
                                    </div>

                                    <div class="col-sm-6 rider-invoice-new-right">
                                        <h4 style="text-align:center;">    <?php echo $langage_lbl_admin['LBL_FARE_BREAKDOWN_DELIVERY_NO_TXT']; ?>
                                            :<?= $db_trip_data['vRideNo']; ?></h4>
                                        <hr/>
                                        <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                            <tbody>
                                            <?
                                            foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value) {
                                                foreach ($value as $k => $val) {
                                                    if ($k == $langage_lbl_admin['LBL_EARNED_AMOUNT'] || $k == $langage_lbl['LBL_EARNED_AMOUNT']) {
                                                        continue;
                                                    } else if ($k == $langage_lbl_admin['LBL_SUBTOTAL_TXT'] || $k == $langage_lbl['LBL_SUBTOTAL_TXT']) {
                                                        continue;
                                                    } else if ($k == "eDisplaySeperator") {
                                                        echo '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';
                                                    } else {
                                                        ?>
                                                        <tr>
                                                            <td><?= $k; ?></td>
                                                            <td align="right"><?php echo $val; ?></td>
                                                        </tr>
                                                        <?
                                                    }
                                                }
                                            }
                                            ?>
                                            <tr>
                                                <td colspan="2">
                                                    <hr style="margin-bottom:0px"/>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><b><?php echo $langage_lbl_admin['LBL_Total_Fare_TXT']; ?> (Via <?
                                                        if ($db_trip_data['ePayWallet'] == 'Yes') {
                                                            echo $langage_lbl_admin['LBL_WALLET_TXT'];
                                                        } else {
                                                            echo $db_trip_data['vTripPaymentMode'];
                                                        }
                                                        ?>)</b></td>
                                                <td align="right">
                                                    <b>
                                                        <?= $db_trip_data['FareSubTotal']; ?>
                                                    </b>
                                                </td>
                                            </tr>
                                            <?
                                            if ($APP_DELIVERY_MODE == 'Multi') {
                                                foreach ($db_reci_data as $key1 => $value1) {
                                                    foreach ($value1 as $key2 => $value2) {
                                                        //if(!empty($value2['ePaymentBy'])){ //Commented By HJ On 04-06-2019 As Per Changed By PM
                                                        if ($value2['ePaymentBy'] == "Sender" || $value2['ePaymentBy'] == "Individual" || ($value2['ePaymentBy'] == "Receiver" && $value2['ePaymentByReceiver'] == "Yes")) { //Added By HJ On 04-06-2019 As Per Changed By PM
                                                            ?>
                                                            <tr>
                                                                <td><b>Payment By</b></td>
                                                                <?
                                                                if ($value2['ePaymentBy'] == "Sender") {
                                                                    ?>
                                                                    <td align="right"><?= $value2['ePaymentBy']; ?></td><?
                                                                } else if ($value2['ePaymentBy'] == "Receiver") {
                                                                    ?>
                                                                    <td align="right"><?= $value2['PaymentPerson']; ?></td><?
                                                                } else if ($value2['ePaymentBy'] == "Individual") {
                                                                    ?>
                                                                    <td align="right"><?= $langage_lbl_admin['LBL_EACH_RECIPIENT']; ?></td><?
                                                                }
                                                                ?>
                                                            </tr>
                                                            <?
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                            if (isset($db_trip_data['fCommision']) && $db_trip_data['fCommision'] > 0 && $comminsionDisp == 0) {
                                                ?>
                                                <table style="border:dotted 2px #000000;" cellpadding="5px"
                                                       cellspacing="2px" width="100%">
                                                    <tr>
                                                        <td><b><?php echo $langage_lbl_admin['LBL_Commision']; ?></b>
                                                        </td>
                                                        <td align="right">
                                                            <b><?= formateNumAsPerCurrency($db_trip_data['fCommision'], ''); ?></b>
                                                        </td>
                                                    </tr>
                                                </table><br>
                                            <?php }
                                            ?>
                                            </tbody>
                                        </table>
                                        <br><br><br>

                                        <? if (isset($outstandingamount['fPendingAmount']) && $outstandingamount['fPendingAmount'] > 0) { ?>
                                            <table style="border:dotted 2px #000000;" cellpadding="5px"
                                                   cellspacing="2px" width="100%">

                                                <tr>

                                                    <td>
                                                        <b><?php echo $langage_lbl_admin['LBL_OUTSTANDING_AMOUNT_TXT']; ?></b>
                                                    </td>

                                                    <td align="right">
                                                        <b><?= $db_trip_data['CurrencySymbol'] . " " . $outstandingamount['fPendingAmount']; ?></b>
                                                    </td>

                                                </tr>

                                            </table><br>
                                        <? }
                                        ?>

                                        <?php if (($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)) {
                                            ?>
                                            <table style="border:dotted 2px #000000;" cellpadding="5px" cellspacing="0"
                                                   width="100%">
                                                <tr>
                                                    <td>
                                                        <b>
                                                            <?php
                                                            if ($db_trip_data['eCancelledBy'] == 'Driver') {
                                                                echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                if (!empty($db_trip_data['vCancelReason'])) {
                                                                    echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                }
                                                            } else if ($db_trip_data['eCancelledBy'] == 'Passenger') {
                                                                echo $langage_lbl_admin['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
                                                                if (!empty($db_trip_data['vCancelReason'])) {
                                                                    echo 'Reason: ' . $db_trip_data['vCancelReason'];
                                                                }
                                                            } else {
                                                                echo $langage_lbl_admin['LBL_CANCELED_TRIP_ADMIN_TXT'];
                                                            }
                                                            ?>
                                                        </b></td>
                                                </tr>
                                            </table><br>
                                        <? } ?>

                                        <?php
                                        if ($db_trip_data['fTipPrice'] != "" && $db_trip_data['fTipPrice'] != "0" && $db_trip_data['fTipPrice'] != "0.00") {
                                            if ($ENABLE_TIP_MODULE == "Yes") {
                                                ?>
                                                <table style="border:dotted 2px #000000;" cellpadding="5px"
                                                       cellspacing="2px" width="100%">
                                                    <tr>
                                                        <td><b>Tip given
                                                                to <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></b>
                                                        </td>
                                                        <td align="right"><b><?= $db_trip_data['fTipPrice']; ?></b></td>
                                                    </tr>
                                                </table><br>
                                                <?
                                            }
                                        }
                                        ?>

                                        <?php if ($db_trip_data['eType'] == 'Deliver' && $APP_DELIVERY_MODE != 'Multi') { ?>

                                            <h4 style="text-align:center;"><?php echo $langage_lbl_admin['LBL_DELIVERY_DETAILS_TXT_ADMIN']; ?></h4>
                                            <hr/>

                                            <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                <tr>
                                                    <td><?php echo $langage_lbl_admin['LBL_RECEIVER_NAME']; ?></td>
                                                    <td><?= clearName($db_trip_data['vReceiverName']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $langage_lbl_admin['LBL_RECEIVER_MOBILE']; ?></td>
                                                    <td><?= clearPhone($db_trip_data['vReceiverMobile']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $langage_lbl_admin['LBL_PICK_UP_INS']; ?></td>
                                                    <td><?= $db_trip_data['tPickUpIns']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $langage_lbl_admin['LBL_DELIVERY_INS']; ?></td>
                                                    <td><?= $db_trip_data['tDeliveryIns']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $langage_lbl_admin['LBL_PACKAGE_DETAILS']; ?></td>
                                                    <td><?= $db_trip_data['tPackageDetails']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo $langage_lbl_admin['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></td>
                                                    <td><?= $db_trip_data['vDeliveryConfirmCode']; ?></td>
                                                </tr>
                                            </table>

                                        <?php } ?>


                                        <?php
                                        if ($db_trip_data['eType'] == 'UberX' && ($db_trip_data['vBeforeImage'] != '' || $db_trip_data['vAfterImage'] != '')) {
                                            $img_path = $tconfig["tsite_upload_trip_images"];
                                            ?>
                                            <h4 style="text-align:center;"><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT']; ?></h4>
                                            <hr/>
                                            <div class="invoice-right-bottom-img">
                                                <?php if ($db_trip_data['vBeforeImage'] != '') { ?>
                                                    <div class="col-sm-6">
                                                        <h3><?php echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN']; ?></h3>
                                                        <b><a href="<?= $db_trip_data['vBeforeImage']; ?>"
                                                              target="_blank"><img
                                                                        src="<?= $db_trip_data['vBeforeImage'] ?>"
                                                                        style="width:200px;"
                                                                        alt="Before Images"/></a></b>
                                                    </div>
                                                <?php } ?>
                                                <?php if ($db_trip_data['vAfterImage'] != '') { ?>
                                                    <div class="col-sm-6">
                                                        <h3><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN']; ?></h3>
                                                        <b><a href="<?= $db_trip_data['vAfterImage']; ?>"
                                                              target="_blank"><img
                                                                        src="<?= $db_trip_data['vAfterImage']; ?>"
                                                                        style="width:200px;"
                                                                        alt="After Images"/></a></b>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <div class="clear"></div>

                                    <? if ($APP_DELIVERY_MODE == "Multi") { ?>
                                        <div class="invoice-table">
                                            <?php
                                            if (!empty($db_reci_data)) {
                                                foreach ($db_reci_data as $key1 => $value1) {
                                                    $no = $key1 + 1;
                                                    $class = (!empty($dtls['vSignImage'])) ? 'sign-img' : '';
                                                    ?>
                                                    <div class="col-sm-6 <?php echo $class; ?>">
                                                        <h4><?php echo $langage_lbl_admin['LBL_RECIPIENT_LIST_TXT'] . '&nbsp;' . $no; ?></h4>
                                                        <hr/>
                                                        <table style="width:100%" cellpadding="5" cellspacing="0"
                                                               border="0">

                                                            <? foreach ($value1 as $key2 => $value2) { ?>

                                                                <tr>
                                                                    <td class="label_left"><?php echo $value2['vFieldName']; ?></td>
                                                                    <?php if ($value2['vFieldName'] == 'Receiver Name') { ?>
                                                                        <td class="detail_right"><?= clearName($value2['vValue']); ?></td>
                                                                    <?php } else if ($value2['vFieldName'] == 'Receiver Mobile') { ?>
                                                                        <td class="detail_right"><?= clearPhone($value2['vValue']); ?></td>

                                                                    <?php } else { ?>
                                                                        <td class="detail_right"><?= clearName($value2['vValue']); ?></td>
                                                                    <?php } ?>
                                                                </tr>
                                                            <? } ?>
                                                            <? if (!empty($value2['ePaymentBy']) && $value2['ePaymentBy'] == "Individual") { ?>
                                                                <tr>
                                                                    <td class="label_left"><?= $langage_lbl_admin['LBL_AMOUNT_PAID_TXT']; ?></td>
                                                                    <td class="detail_right">
                                                                        <?= $value2['PaymentAmount']; ?>
                                                                    </td>
                                                                </tr>
                                                            <? } ?>
                                                            <?php if (!empty($value2['Receipent_Signature']) && $db_trip_data['vVerificationMethod'] == 'Signature') { ?>
                                                                <tr>
                                                                    <td class="label_left"><?= $langage_lbl_admin['LBL_RECEIVER_SIGN']; ?></td>
                                                                    <td class="detail_right">
                                                                        <img width="100px"
                                                                             src="<?php echo $value2['Receipent_Signature']; ?>"
                                                                             align="left">
                                                                    </td>
                                                                </tr>
                                                            <?php } else if ($value2['vDeliveryConfirmCode'] != "" && $db_trip_data['vVerificationMethod'] == 'Code') { ?>
                                                                <tr>
                                                                    <td class="label_left"><?= $langage_lbl_admin['LBL_DELIVERY_CONFIRMATION_CODE_TXT']; ?></td>
                                                                    <td class="detail_right">
                                                                        <?= $value2['vDeliveryConfirmCode'] ?>
                                                                    </td>
                                                                </tr>
                                                            <? } ?>
                                                        </table>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>

                                    <? } ?>

                                    <div class="row invoice-email-but">
                                                <span>
                                                    <a href="../send_invoice_receipt_multi.php?action_from=mail&iTripId=<?= $db_trip_data['iTripId'] ?>&multidelivery=1"><button
                                                                class="btn btn-primary ">E-mail</button></a>
                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>

<!--END MAIN WRAPPER -->

<? include_once('footer.php'); ?>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places,geometry" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script>
    h = window.innerHeight;
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    const markers = [];
    let map;
    let locationNew = <?php echo $jsLocationsNew; ?>;

    function initializeMap() {

        GOOGLE_MAP_OBJ.options.center = new google.maps.LatLng(parseFloat('<?= $db_trip_data['tStartLat']; ?>'), parseFloat('<?= $db_trip_data['tStartLong']; ?>'));
        GOOGLE_MAP_OBJ.options.zoom = 12;
        map = GOOGLE_MAP_OBJ.init('map-canvas');

        const bounds = new google.maps.LatLngBounds();
        const locations = <?php echo $jsLocations; ?>;
        iteratePairs(locationNew);
        locations.forEach((location, index) => {
            let iconUrl, labelText, labelColor;
            if (index === locations.length - 1) {
                iconUrl = '../webimages/upload/mapmarker/mapicon4.svg';
                labelColor = 'black';
                labelText = (locations.length === 2) ? 'To' : location.label;
            } else if (index === 0) {
                iconUrl = '../webimages/upload/mapmarker/mapicon1.svg';
                labelColor = 'black';
                labelText = 'Fr';
            } else {
                iconUrl = '../webimages/upload/mapmarker/mapicon2.svg';
                labelColor = 'black';
                labelText = location.label;
            }
            const marker = new google.maps.Marker({
                position: location,
                map: map,
                icon: {
                    url: iconUrl,
                    labelOrigin: new google.maps.Point(15, 12)
                },
                label: {
                    text: labelText,
                    color: labelColor,
                    fontWeight: 'bold',
                    fontSize: '11px'
                }
            });
            bounds.extend(location);
        });
        /*const lineSymbol = {
            path: 'M 0,-1 0,1',
            strokeOpacity: 0.7,
            scale: 3
        };

        const path = new google.maps.Polyline({
            path: locations,
            icons: [{
                icon: lineSymbol,
                offset: '0',
                repeat: '20px'
            }],
            geodesic: true,
            strokeColor: '#000000',
            strokeOpacity: 0,
            map: map
        });*/
        map.fitBounds(bounds);
    }

    function iteratePairs(arr) {
        for (let i = 0; i < locationNew.length - 1; i++) {
            drawCurve(arr[i], arr[i + 1], map);
        }
    }

    function drawCurve(P1, P2, map) {
        const lineLength = google.maps.geometry.spherical.computeDistanceBetween(P1, P2);
        const lineHeading = google.maps.geometry.spherical.computeHeading(P1, P2);
        const lineHeading1 = lineHeading < 0 ? lineHeading + 45 : lineHeading + -45;
        const lineHeading2 = lineHeading < 0 ? lineHeading + 135 : lineHeading + -135;
        const pA = google.maps.geometry.spherical.computeOffset(P1, lineLength / 3, lineHeading1);
        const pB = google.maps.geometry.spherical.computeOffset(P2, lineLength / 3, lineHeading2);
        const curvedLine = new GmapsCubicBezier(P1, pA, pB, P2, 0.01, map);
    }

    function GmapsCubicBezier(latlong1, latlong2, latlong3, latlong4, resolution, map) {
        const latlong1Obj = new google.maps.LatLng(latlong1.lat, latlong1.lng);
        const latlong4Obj = new google.maps.LatLng(latlong4.lat, latlong4.lng);
        const lat1 = latlong1Obj.lat();
        const long1 = latlong1Obj.lng();
        const lat4 = latlong4Obj.lat();
        const long4 = latlong4Obj.lng();
        const lat2 = latlong2.lat();
        const long2 = latlong2.lng();
        const lat3 = latlong3.lat();
        const long3 = latlong3.lng();
        const points = [];
        for (let it = 0; it <= 1; it += resolution) {
            points.push(this.getBezier({
                x: lat1,
                y: long1
            }, {
                x: lat2,
                y: long2
            }, {
                x: lat3,
                y: long3
            }, {
                x: lat4,
                y: long4
            }, it));
        }
        const path = [];
        for (let i = 0; i < points.length - 1; i++) {
            path.push(new google.maps.LatLng(points[i].x, points[i].y));
            path.push(new google.maps.LatLng(points[i + 1].x, points[i + 1].y, false));
        }
        const Line = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeOpacity: 0,
            icons: [{
                icon: {
                    path: 'M 0,-1 0,1',
                    strokeOpacity: 1,
                    scale: 3
                },
                offset: '0',
                repeat: '20px'
            }],
        });
        Line.setMap(map);
        return Line;
    }

    GmapsCubicBezier.prototype = {
        B1: function (t) {
            return t * t * t;
        },
        B2: function (t) {
            return 3 * t * t * (1 - t);
        },
        B3: function (t) {
            return 3 * t * (1 - t) * (1 - t);
        },
        B4: function (t) {
            return (1 - t) * (1 - t) * (1 - t);
        },
        getBezier: function (C1, C2, C3, C4, percent) {
            const pos = {};
            pos.x = C1.x * this.B1(percent) + C2.x * this.B2(percent) + C3.x * this.B3(percent) + C4.x * this.B4(percent);
            pos.y = C1.y * this.B1(percent) + C2.y * this.B2(percent) + C3.y * this.B3(percent) + C4.y * this.B4(percent);
            return pos;
        }
    }
    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initializeMap);
    });
</script>
</body>
<!-- END BODY-->
</html>
