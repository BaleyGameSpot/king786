<?php

include_once('../common.php');
if (!$userObj->hasPermission('view-order-invoice')) {
    $userObj->redirect();
}
include_once('../send_invoice_receipt.php');
$iOrderId = isset($_REQUEST['iOrderId']) ? $_REQUEST['iOrderId'] : '';
$script = "All Orders";
$tbl_name = 'orders';
$db_order_data = FetchOrderFareDetailsForWeb($iOrderId, '', '');

if (empty($db_order_data)) {
    header("location: allorders.php?type=allorders");
}
$TripData = $obj->MySQLSelect("SELECT iTripId,vImage FROM trips WHERE iOrderId = '" . $iOrderId . "'");
$getratings = getrating($iOrderId);

// prescription Images displayed by sneha start
$prescriptiondata = '';
$table_pres = 'prescription_images';
$val = $obj->MySQLSelect('select 1 from ' . $table_pres . ' LIMIT 1');
if ($val !== FALSE) {
    $prescriptiondata = $obj->MySQLSelect("SELECT vImage FROM " . $table_pres . " WHERE order_id = '" . $iOrderId . "'");
}
// prescription Images displayed by sneha end
//Added By HJ On 13-02-2020 For Display Paymen Type Start
$paymentType = ucwords($db_order_data['ePaymentOption']);
if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    if ($db_order_data['fNetTotal'] == 0 && $db_order_data['ePayWallet'] == "Yes") {
        $paymentType = ucwords($langage_lbl_admin['LBL_WALLET_TXT']);
    }
    else if ($db_order_data['fNetTotal'] > 0 && $db_order_data['ePayWallet'] == "Yes") {
        $paymentType = ucwords(strtolower($langage_lbl_admin['LBL_CASH_CAPS']));
    }
}
else {
    if (isset($db_order_data['fNetTotal']) > 0 && (strtoupper($db_order_data['ePayWallet']) == "YES" || $db_order_data['fWalletDebit'] > 0)) {
        if (strtoupper($db_order_data['ePaymentOption']) == "CARD") {
            //$paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']);//commented by SP bc of solving issue to be fixed 1312
            $paymentType = ucwords($langage_lbl_admin["LBL_CARD_CAPS"]);
        }
        else if (strtoupper($db_order_data['ePaymentOption']) == "CASH") {
            //$paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"])."-".ucwords($langage_lbl_admin['LBL_WALLET_TXT']);//commented by SP bc of solving issue to be fixed 1312
            $paymentType = ucwords($langage_lbl_admin["LBL_CASH_CAPS"]);
        }
        else {
            $paymentType = ucwords($langage_lbl_admin['LBL_WALLET_TXT']);
        }
    }
}
//Added By HJ On 13-02-2020 For Display Paymen Type End
$takeaway = 'No';
//if($MODULES_OBJ->isTakeAwayEnable()) {
if ($db_order_data['eTakeaway'] == 'Yes') {
    //$prepareTime = $obj->MySQLSelect("select fPrepareTime from company where iCompanyId = ".$user_order_sql_data[0]['iCompanyId']);
    //$preparetimedata = $prepareTime[0]['fPrepareTime']." ".$langage_lbl['LBL_MINUTES_TXT'];
    $takeaway = 'Yes';
}
//}
//Added By HJ On 09-09-2020 For Optimize Order Table Query Start
if (isset($orderDetailsArr['orders_' . $iOrderId])) {
    $selectedPrefData = $orderDetailsArr['orders_' . $iOrderId];
}
else {
    $selectedPrefData = $obj->MySQLSelect("SELECT * FROM orders WHERE iOrderId='" . $iOrderId . "'");
    $orderDetailsArr['orders_' . $iOrderId] = $selectedPrefData;
}
//Added By HJ On 09-09-2020 For Optimize Order Table Query End
// Added by HV for Delivery Preference
if ($MODULES_OBJ->isDeliveryPreferenceEnable()) {
    //$selectedPrefData = $obj->MySQLSelect("SELECT selectedPreferences FROM orders WHERE iOrderId = ".$iOrderId);
    $selectedPrefIds = "";
    if ($selectedPrefData[0]['selectedPreferences'] != "") {
        $selectedPrefIds = $selectedPrefData[0]['selectedPreferences'];
    }
    if ($selectedPrefIds != "") {
        $vLang = "EN";
        $ssql .= " WHERE iPreferenceId IN (" . $selectedPrefIds . ")";
        $deliveryPrefSql = "SELECT iPreferenceId,JSON_UNQUOTE(JSON_VALUE(tTitle, '$.tTitle_" . $default_lang . "')) as tTitle, JSON_UNQUOTE(JSON_VALUE(tDescription, '$.tDescription_" . $default_lang . "')) as tDescription, ePreferenceFor, eImageUpload, iDisplayOrder, eContactLess, eStatus FROM delivery_preferences " . $ssql;
        $deliveryPrefSqlData = $obj->MySQLSelect($deliveryPrefSql);
    }
}
$scSql = "SELECT eShowTerms,eProofUpload, JSON_UNQUOTE(JSON_VALUE(tProofNote, '$.tProofNote_" . $default_lang . "')) as tProofNote FROM service_categories WHERE iServiceId = " . $iServiceId;
$scSqlData = $obj->MySQLSelect($scSql);
$eShowTerms = $scSqlData[0]['eShowTerms'];
$eProofUpload = $scSqlData[0]['eProofUpload'];
$tProofNote = $scSqlData[0]['tProofNote'];
$eShowTermsServiceCategories = "No";
if ($MODULES_OBJ->isEnableTermsServiceCategories() && $eShowTerms == "Yes") {
    $eShowTermsServiceCategories = "Yes";
}
$eProofUploadServiceCategories = "No";
if ($MODULES_OBJ->isEnableProofUploadServiceCategories() && $eProofUpload == "Yes") {
    $eProofUploadServiceCategories = "Yes";
}
$genie_invoice_title = "";
if ($db_order_data['eBuyAnyService'] == "Yes") {
    $genie_invoice_title = "(" . $langage_lbl_admin['LBL_OTHER_DELIVERY'] . ")";
    if ($db_order_data['eForPickDropGenie'] == "Yes") {
        $genie_invoice_title = "(" . $langage_lbl_admin['LBL_RUNNER'] . ")";
    }
}
$vTripAdjusmentId = "";
if (!empty($db_order_data['vTripAdjusmentId'])) {
    $vTripAdjusmentId = get_value('trips', 'vRideNo', 'iTripId', $db_order_data['vTripAdjusmentId'], '', 'true');
}
$DriverFeedbackDetails = array();
if ($MODULES_OBJ->isEnableMultiOptionsToppings()) {
    $rating_feedback_data = $obj->MySQLSelect("SELECT tDriverFeedbackDetails FROM ratings_user_driver WHERE iOrderId = '$iOrderId' AND eFromUserType = 'Passenger' AND eToUserType = 'Driver'");
    if (!empty($rating_feedback_data[0]['tDriverFeedbackDetails'])) {
        $tDriverFeedbackDetails = json_decode($rating_feedback_data[0]['tDriverFeedbackDetails'], true);
        foreach ($tDriverFeedbackDetails as $feedback) {
            $feedback_data = $obj->MySQLSelect("SELECT JSON_UNQUOTE(JSON_VALUE(tQuestion, '$.tQuestion_" . $default_lang . "')) as tQuestion FROM rating_feedback_questions WHERE iFeedbackId = '" . $feedback['iFeedbackId'] . "'");
            $DriverFeedbackDetails[] = array(
                'tQuestion' => $feedback_data[0]['tQuestion'],
                'Answer'    => !empty($feedback['ans']) ? ($feedback['ans'] == "Yes" ? ucfirst(strtolower($langage_lbl['LBL_YES'])) : ucfirst(strtolower($langage_lbl['LBL_NO']))) : "-"
            );
        }
    }
}
// echo '<pre>'; print_r($db_order_data['vTimeZone']); exit();
$systemTimeZone = date_default_timezone_get();
$date_format_data_array = array(
    //'tdate' =>  converToTz($DBProcessingOrders[$i]['tOrderRequestDate'], $vTimeZone, $systemTimeZone),
    'langCode' => $default_lang,
    'DateFormatForWeb' => 1
);
$date_format_data_array['tdate'] = (!empty($db_order_data['vTimeZone'])) ? converToTz($db_order_data['OrderRequestDatenew'], $db_order_data['vTimeZone'], $systemTimeZone) : $db_order_data['OrderRequestDatenew'];
$get_OrderRequestDatenew_format = DateformatCls::getNewDateFormat($date_format_data_array);

$date_format_data_array['tdate'] = (!empty($db_order_data['vTimeZone'])) ? converToTz($db_order_data['DeliveryDate'], $db_order_data['vTimeZone'], $systemTimeZone) : $db_order_data['DeliveryDate'];
$get_DeliveryDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
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
    <? include_once('global_files.php'); ?>

    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <style>
        .location-to b span, .location-from b span, .location-username span {
            margin: 0px;
            padding: 0px;
            display: inline-block;
            width: auto;
            font-weight: 600;
            vertical-align: middle;
        }

    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>

    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner" id="page_height" style="">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Invoice <?= $genie_invoice_title ?></h2>
                    <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                    <div style="clear:both;"></div>
                </div>
            </div>
            <hr/>
            <?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1) { ?>
                <div class="alert alert-success paddiing-10">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    Email send successfully.
                </div>
            <?php } ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <b>Your <?= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN_DL']; ?> </b> <?
                                if ($db_order_data['DeliveryDate'] == "0000-00-00 00:00:00") {
                                    echo "Was Cancelled.";
                                }
                                else {
                                    /*echo @date('h:i A', @strtotime($db_order_data['OrderRequestDatenew']));
                                    ?> on <?
                                    echo @date('d M Y', @strtotime($db_order_data['OrderRequestDatenew']));*/
                                    echo $get_OrderRequestDatenew_format['tDisplayDateTime'];
                                }
                                ?>
                            </div>
                            <div class="panel-body rider-invoice-new">
                                <div class="row">
                                    <div class="col-sm-6 rider-invoice-new-left">
                                        <div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div>
                                        <span class="location-from">
                                                    <?php if ($db_order_data['eOrderplaced_by'] == "Kiosk") { ?>
                                                        <i class="icon-map-marker"></i>
                                                    <?php } else { ?>
                                                        <i class="icon-map-marker"></i>
                                                    <?php } ?>
                                                    <b><?= $get_OrderRequestDatenew_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?></b>
                                                    <b><span><?= clearName($db_order_data['CompanyName']); ?></span>
                                                        <? if (!empty($getratings['CompanyRate'])) { ?>
                                                            <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>)</span>
                                                        <? } ?>
                                                    </b>

                                                    <b><p><?= $db_order_data['vRestuarantLocation']; ?></p></b>

                                                </span>
                                        <!--    <span class="location-from"><i class="icon-map-marker"></i>
                                                    <b>
                                                <?= $get_OrderRequestDatenew_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($db_order_data['OrderRequestDatenew'])); ?>
                                                        <p><?= clearName($db_order_data['CompanyName']); ?>
                                                <? if (!empty($getratings['CompanyRate'])) { ?>
                                                                        (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['CompanyRate'] ?>) 
                                                <? } ?>
                                                        </p>
                                                        <p><?= $db_order_data['vRestuarantLocation']; ?></p>
                                                    </b>
                                                </span> -->
                                        <!-- <span class="location-to"><i class="icon-map-marker"></i>
                                                    <b><?= $get_DeliveryDate_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?>
                                                        <p>
                                                <?= clearName($db_order_data['UserName']); ?> 
                                                <? if (!empty($getratings['UserRate'])) { ?>
                                                                        (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>) 
                                                <? } ?>
                                                        </p>
                                                        <p><?= $db_order_data['DeliveryAddress']; ?></p>
                                                    </b>
                                                </span>
                                                -->

                                        <span class="location-to vtest">
                                                    <?php if ($db_order_data['eOrderplaced_by'] == "Kiosk") { ?>
                                                        <i class="icon-user"></i>
                                                    <?php } else { ?>
                                                        <i class="icon-map-marker"></i>
                                                    <?php } ?>
                                                    <b><?= $get_DeliveryDate_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($db_order_data['DeliveryDate'])); ?></b>
                                                    <b><span>   

                                                            <?= clearName($db_order_data['UserName']); ?> 

                                                        </span>         

                                                        <? if (!empty($getratings['UserRate'])) { ?>
                                                            <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>)
                                                            </span><? } ?>

                                                    </b>
                                                    <b> <p><?= $db_order_data['DeliveryAddress']; ?></p>
                                                    </b>
                                                </span>


                                        <div class="rider-invoice-bottom">
                                            <div class="col-sm-4">
                                                <?= $langage_lbl_admin['LBL_ORDER_NO_TXT']; ?> <br/>
                                                <b>
                                                    <?= $db_order_data['vOrderNo']; ?>
                                                </b><br/>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= $langage_lbl_admin['LBL_ORDER_STATUS_TXT']; ?><br/>
                                                <b><?= $db_order_data['vStatus']; ?> </b> <br/>
                                            </div>
                                            <div class="col-sm-4">
                                                <?= $langage_lbl_admin['LBL_PAYMENT_TYPE_TXT']; ?><br/>
                                                <b><?= $paymentType; ?></b>
                                            </div>

                                            <br><br><br>
                                            <?php /* if (isset($db_order_data['DriverName']) && $db_order_data['DriverName'] != '') { ?>
                                                      <div class="col-sm-4">
                                                      <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name<br />
                                                      <b>   <?= $db_order_data['DriverName']; ?>
                                                      <? if (!empty($getratings['DriverRate'])) { ?>
                                                      (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>)
                                                      <? } ?>
                                                      </b><br/>
                                                      </div>
                                                      <?php } ?>
                                                      <?php if (isset($db_order_data['DriverVehicle']) && $db_order_data['DriverVehicle'] != '') { ?>
                                                      <div class="col-sm-4">
                                                      <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Vehicle<br />
                                                      <b><?= $db_order_data['DriverVehicle']; ?> </b> <br/>
                                                      </div>
                                                      <?php } ?>
                                                      <?php if ($db_order_data['UserName'] != '') { ?>
                                                      <div class="col-sm-4">
                                                      Username<br />
                                                      <b><?= clearName($db_order_data['UserName']); ?>
                                                      <? if (!empty($getratings['UserRate'])) { ?>
                                                      (<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>)
                                                      <? } ?>
                                                      </b>
                                                      </div>
                                                      <?php } */ ?>
                                            <?php if (isset($db_order_data['DriverName']) && $db_order_data['DriverName'] != '') { ?>
                                                <div class="col-sm-4">
                                                    Driver Name<br/>
                                                    <b class="location-username"><span> <?= clearName($db_order_data['DriverName']); ?></span>
                                                        <? if (!empty($getratings['DriverRate'])) { ?>
                                                            <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['DriverRate'] ?>)</span>
                                                        <? } ?>
                                                    </b><br/>
                                                </div>
                                            <?php } ?>
                                            <?php if (isset($db_order_data['DriverVehicle']) && $db_order_data['DriverVehicle'] != '') { ?>
                                                <div class="col-sm-4">
                                                    Driver Vehicle<br/>
                                                    <b><?= $db_order_data['DriverVehicle']; ?> </b> <br/>
                                                </div>
                                            <?php } ?>
                                            <?php if ($db_order_data['UserName'] != '') { ?>
                                                <div class="col-sm-4">
                                                    Username<br/>
                                                    <b class="location-username"><span><?= clearName($db_order_data['UserName']); ?></span>
                                                        <? if (!empty($getratings['UserRate'])) { ?>
                                                            <span>(<img src="../assets/img/star.jpg" alt=""> <?= $getratings['UserRate'] ?>)</span>
                                                        <? } ?>
                                                    </b>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <?php
                                        // $db_order_data['vInstruction'];die;
                                        if (!empty(trim($db_order_data['vInstruction']))) { ?>
                                            <div class="rider-invoice-bottom row">
                                                <!-- 
                                                    Addon to show Order Instruction as per discuss with CD BY TP
                                                -->
                                                <div class="col-sm-12" style="margin-top: 12px;">
                                                    Instruction<br/>
                                                    <b class="location-username">
                                                        <?php $ins_style = '';
                                                        if (SITE_TYPE == "Demo") {
                                                            $ins_style = 'style="word-break: break-word"';
                                                        } ?>
                                                        <span <?= $ins_style ?>><?= !empty($db_order_data['vInstruction']) ? clearName($db_order_data['vInstruction']) : '<code>No Instruction Added</code>'; ?></span>
                                                    </b>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <!--
                                            Addon to show Delivery Preference Instruction as per discuss with KS BY HV
                                        -->
                                        <?php if ($MODULES_OBJ->isDeliveryPreferenceEnable() && $selectedPrefIds != "") { ?>
                                            <div class="rider-invoice-bottom">
                                                <div class="col-sm-4" style="margin-top: 12px;">
                                                    <span><?= $langage_lbl_admin['LBL_DELIVERY_PREF']; ?></span>

                                                    <br/>
                                                    <?php //if($selectedPrefIds != "") { ?>
                                                    <?php foreach ($deliveryPrefSqlData as $delivery_pref) { ?>
                                                        <b class="location-username">
                                                            <span>- <?= $delivery_pref['tTitle'] ?></span><br>
                                                        </b>
                                                        <?php if ($delivery_pref['eContactLess'] == 'Yes' && $db_order_data['vImageDeliveryPref'] != "") { ?>
                                                            <span><a href="<?= $tconfig['tsite_upload_order_delivery_pref_images'] . $db_order_data['vImageDeliveryPref']; ?>" class="btn btn-sm btn-info" style="margin-left: 10px; line-height: 1.1" target="_blank">View Image</a></span>
                                                            <br>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <?php //} else { ?>
                                                    <!--<b class="location-username">
                                                        <span><code>No Delivery Preference Instruction(s) Added</code></span>
                                                    </b>-->
                                                    <?php //} ?>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <? if ($takeaway == 'Yes' && $db_order_data['iStatusCode'] == 6) { ?>
                                            <div class="rider-invoice-bottom">
                                                    <span>
                                                    <b><?= $langage_lbl_admin['LBL_TAKE_AWAY']; ?></b>
                                                    <p><?= str_replace('#RESTAURANT_NAME#', $db_order_data['CompanyName'], $langage_lbl_admin['LBL_TAKE_AWAY_ORDER_NOTE']); ?></p>
                                                   </span>
                                            </div>
                                        <? } ?>

                                        <?php if ($MODULES_OBJ->isEnableMultiOptionsToppings() && !empty($DriverFeedbackDetails)) { ?>
                                            <div class="rider-invoice-bottom row">
                                                <div class="col-sm-12">
                                                    <div style="margin-bottom: 5px;">
                                                        <b><?= $langage_lbl_admin['LBL_DRIVER_RATING_FEEDBACK_TITLE']; ?>:</b>
                                                    </div>

                                                    <table class="table table-striped table-bordered">
                                                        <tbody>
                                                        <?php foreach ($DriverFeedbackDetails as $Feedback_Data) { ?>
                                                            <tr>
                                                                <td><?= $Feedback_Data['tQuestion'] ?></td>
                                                                <td><?= $Feedback_Data['Answer'] ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        <?php } ?>

                                    </div>

                                    <div class="col-sm-6 rider-invoice-new-right">
                                        <h4 style="text-align:center;"> <?= $langage_lbl_admin['LBL_ORDER_DETAIL_TXT']; ?> </h4>
                                        <hr/>

                                        <div class="fare-breakdown">
                                            <div class="fare-breakdown-inner">
                                                <? $db_menu_item_list = $db_order_data['itemlist']; ?>
                                                <h5><?= $langage_lbl_admin['LBL_TOTAL_ITEM_TXT']; ?> :
                                                    <b><?= $db_order_data['TotalItems']; ?></b></h5>
                                                <?php if (!empty($db_menu_item_list)) { ?>
                                                    <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                        <tbody>
                                                        <? foreach ($db_menu_item_list as $key => $val) { ?>
                                                            <tr>
                                                                <?php if ($db_order_data['eBuyAnyService'] == "Yes") { ?>
                                                                    <td><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?>
                                                                        <? if (trim($val['SubTitle'], "/") != '') { ?>
                                                                            <br/>
                                                                            <small style="font-size: 12px;">(<?= trim($val['SubTitle'], "/"); ?>)</small>
                                                                        <? } ?>
                                                                        <? if ($val['eDecline'] == "Yes") { ?>
                                                                            <br/>
                                                                            <small style="font-size: 12px;">(<?= $langage_lbl_admin['LBL_USER_DECLINED'] ?>)</small>
                                                                        <? } ?>
                                                                        <? if ($val['eExtraPayment'] == "No" && $val['eItemAvailable'] == "Yes") { ?>
                                                                            <br/>
                                                                            <small style="font-size: 12px;">(<?= $langage_lbl_admin['LBL_PAYMENT_NOT_REQUIRED'] ?>)</small>
                                                                        <? } elseif ($val['eItemAvailable'] == "No") { ?>
                                                                            <? if ($val['eExtraPayment'] == "No") { ?>
                                                                                <br/>
                                                                                <small style="font-size: 12px;">(<?= $langage_lbl_admin['LBL_ITEM_NO_PAYMENT_UNAVAILABLE'] ?>)</small>
                                                                            <?php } else { ?>
                                                                                <br/>
                                                                                <small style="font-size: 12px;">(<?= $langage_lbl_admin['LBL_ITEM_NOT_AVAILABLE'] ?>)</small>
                                                                            <? } ?>
                                                                        <?php } ?>
                                                                    </td>

                                                                    <td align="right"><?= $val['fTotPrice'] ?></td>
                                                                <?php } else { ?>
                                                                    <td><?= $val['MenuItem']; ?> X <?= $val['iQty']; ?>
                                                                        <? if (trim($val['SubTitle'], "/") != '') { ?>
                                                                            <br/>
                                                                            <small style="font-size: 12px;">(<?= trim($val['SubTitle'], "/"); ?>)</small>
                                                                        <? } ?>
                                                                    </td>
                                                                    <td align="right"><?= $val['fTotPrice'] ?></td>
                                                                <?php } ?>
                                                            </tr>
                                                        <?php } ?>
                                                        <tr>
                                                            <td colspan="2">
                                                                <hr style="margin-bottom:0px;border-style: dotted;"/>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>

                                                <?php } ?>
                                                <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                    <tbody>
                                                    <?
                                                    foreach ($db_order_data['History_Arr'] as $key => $value) {
                                                        if ($key == $langage_lbl_admin['LBL_BILL_SUB_TOTAL']) {
                                                            ?>
                                                            <tr>
                                                                <td style="font-weight: bold;"><?= $key; ?></td>
                                                                <td align="right"><?= $value; ?></td>
                                                            </tr>
                                                        <?php } else { ?>
                                                            <tr>
                                                                <td><?= $key; ?></td>
                                                                <td align="right"><?= $value; ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <tr>
                                                        <td colspan="2">
                                                            <hr style="margin-bottom:0px;border-style: dotted;"/>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>


                                                <?php foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                    if ($key == $langage_lbl_admin['LBL_TOTAL_BILL_AMOUNT_TXT']) { ?>
                                                        <table style="width:100%" cellpadding="5" cellspacing="0" border="0">
                                                            <tbody>
                                                            <tr>
                                                                <td style="font-weight: bold;"><?= $key; ?></td>
                                                                <td align="right" style="font-weight: bold;"><?= $value; ?></td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    <?php }
                                                }
                                                ?>

                                                <table style="width:100%;border:dotted 2px #000000;" cellpadding="5" cellspacing="0" border="0">
                                                    <tbody>
                                                    <?php foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                        if ($key != $langage_lbl_admin['LBL_TOTAL_BILL_AMOUNT_TXT']) {
                                                            ?>
                                                            <tr>
                                                                <td style="font-weight: bold;"><?= $key; ?></td>
                                                                <td align="right" style="font-weight: bold;"><?= $value; ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>


                                                <?php if (isset($db_order_data['vCouponCode']) && !empty($db_order_data['vCouponCode'])) {
                                                    $checkPromocode = $obj->MySQLSelect("SELECT * FROM coupon  WHERE vCouponCode='" . $db_order_data['vCouponCode'] . "' ");
                                                    ?>
                                                    <table style="margin-top:20px;width:100%;border:dotted 2px #000000;" cellpadding="5" cellspacing="0" border="0">
                                                        <tbody>
                                                        <?php foreach ($db_order_data['History_Arr_first'] as $key => $value) {
                                                            if ($key != $langage_lbl_admin['LBL_TOTAL_BILL_AMOUNT_TXT']) {
                                                                ?>
                                                                <tr>
                                                                    <td style="font-weight: bold;"><?= $langage_lbl_admin['LBL_APPLIED_PROMO_CODE']; ?></td>
                                                                    <td align="right" style="font-weight: bold;"><?= $db_order_data['vCouponCode']; ?></td>
                                                                </tr>
                                                                <?php if ($checkPromocode[0]['eFreeDelivery'] == 'Yes') { ?>
                                                                    <tr>
                                                                        <td style="font-weight: bold;">Discount Type</td>

                                                                        <td align="right" style="font-weight: bold;">Free Delivery</td>

                                                                    </tr>
                                                                <?php } ?>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <?php
                                        if (isset($TripData[0]['vImage']) && $TripData[0]['vImage'] != '') {
                                            $img_path = $tconfig["tsite_upload_order_images"];
                                            ?>
                                            <br/><br/><br/>
                                            <div class="invoice-right-bottom-img">
                                                <div class="col-sm-6">
                                                    <b><a href="<?= $img_path . $TripData[0]['vImage']; ?>" target="_blank"><img src="<?= $img_path . $TripData[0]['vImage']; ?>" style="width:200px;" alt="Order Images"/></a></b>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <br/>

                                        <?php if ($eProofUploadServiceCategories == "Yes" && !empty($db_order_data['vIdProofImg'])) { ?>
                                            <div style="clear:both;"></div>
                                            <hr style="margin: 20px 0 10px 0">
                                            <h4><?= $langage_lbl_admin['LBL_IDENTIFICATION'] ?></h4>
                                            <hr>
                                            <div class="invoice-right-bottom-img">
                                                <div class="col-sm-6">
                                                    <a href="<?= $tconfig['tsite_upload_id_proof_service_categories_images'] . "Orders/" . $db_order_data['vIdProofImg'] ?>" target="_blank"><img src="<?= $tconfig['tsite_upload_id_proof_service_categories_images'] . "Orders/" . $db_order_data['vIdProofImg'] ?>" style="width:200px; margin-bottom: 20px" alt="Order Images"/></a>
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <?php if ($db_order_data['iStatusCode'] == '8') { ?>
                                            <div class="panel panel-warning">
                                                <div class="panel-heading">
                                                    <p><?= $langage_lbl_admin["LBL_ORDER_CANCEL_WEB_TEXT"]; ?></p>
                                                    <? if ($db_order_data['eCancelledBy'] != '') { 
                                                        $cancelledBy = $db_order_data['eCancelledBy'] == 'Passenger' ? 'User' : $db_order_data['eCancelledBy'];
                                                    ?>
                                                        <p>Cancelled By : <?= $cancelledBy; ?></p>
                                                    <? }
                                                    if ($db_order_data['vCancelReason'] != '') { ?>
                                                        <p>Cancellation Reason : <?= $db_order_data['vCancelReason']; ?></p>
                                                    <? } ?>
                                                    <p><?= $langage_lbl_admin["LBL_CANCELLATION_CHARGE_WEB"] ?> For
                                                        <?= $langage_lbl_admin['LBL_RIDER']; ?> :
                                                        <?php
                                                        if ($db_order_data['eBuyAnyService'] == "No" || $db_order_data['eCancelledBy'] == "Admin") {
                                                            echo formateNumAsPerCurrency($db_order_data['fCancellationCharge'], '');
                                                        }
                                                        else {
                                                            echo formateNumAsPerCurrency($db_order_data['fDeliveryChargeCancelled'], '');
                                                        }
                                                        ?>
                                                        <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            <?php if (!empty($db_order_data['vOrderAdjusmentId'])) { ?>
                                                                ( <?= $langage_lbl_admin["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                            <?php } else { ?>
                                                                ( <?= $langage_lbl_admin["LBL_PAID_IN_TRIP_NO_TXT"] ?># : <?= $vTripAdjusmentId ?>)
                                                            <?php } ?>
                                                        <?php } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl_admin["LBL_UNPAID_WEB_TXT"] ?> )
                                                            <?
                                                        }
                                                        else if ($db_order_data['ePaymentOption'] == 'Card') {
                                                            if ($db_order_data['eCancelledBy'] != "Admin") {
                                                                $ePaymentOption = $langage_lbl_admin["LBL_PAID_BY_CARD_WEB_TXT"];
                                                                if ($db_order_data['ePayWallet'] == "Yes") {
                                                                    $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                                                                }
                                                            }
                                                            else {
                                                                if (!empty($db_order_data['vOrderAdjusmentId'])) {
                                                                    $ePaymentOption = $langage_lbl_admin["LBL_PAID_IN_ORDER_NO_TXT"] . '# : ' . $db_order_data['vOrderAdjusmentId'];
                                                                }
                                                                elseif (!empty($db_order_data['vTripAdjusmentId'])) {
                                                                    $ePaymentOption = $langage_lbl_admin["LBL_PAID_IN_TRIP_NO_TXT"] . '# : ' . $vTripAdjusmentId;
                                                                }
                                                                else {
                                                                    $ePaymentOption = $langage_lbl_admin["LBL_UNPAID_WEB_TXT"];
                                                                }
                                                            }
                                                            ?>
                                                            ( <?= $ePaymentOption; ?> )
                                                        <? } ?>
                                                    </p>
                                                    <?php if ($db_order_data['eBuyAnyService'] == "No") { ?>
                                                        <p><?= $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To <?= $langage_lbl_admin["LBL_RESTAURANT_TXT_ADMIN"] ?> : <?= formateNumAsPerCurrency($db_order_data['fRestaurantPaidAmount'], ''); ?></p>
                                                    <?php } ?>
                                                    <p><?= $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>: <?= formateNumAsPerCurrency($db_order_data['fDriverPaidAmount'], ''); ?></p>
                                                </div>
                                            </div>
                                        <?php } else if ($db_order_data['iStatusCode'] == '7') { ?>
                                            <div class="panel panel-warning">
                                                <div class="panel-heading">
                                                    <p><?= $langage_lbl_admin["LBL_ORDER_REFUND_WEB_TEXT"]; ?></p>
                                                    <? if ($db_order_data['eCancelledBy'] != '') { ?>
                                                        <p>Cancelled By : <?= $db_order_data['eCancelledBy']; ?></p>
                                                    <? }
                                                    if ($db_order_data['vCancelReason'] != '') { ?>
                                                        <p>Cancellation Reason : <?= $db_order_data['vCancelReason']; ?></p>
                                                    <? } ?>
                                                    <p><?= $langage_lbl_admin["LBL_CANCELLATION_CHARGE_WEB"] ?> : <?= formateNumAsPerCurrency($db_order_data['fCancellationCharge'], ''); ?>
                                                        <? if ($db_order_data['ePaymentOption'] == 'Cash' && $db_order_data['ePaidByPassenger'] == 'Yes') { ?>
                                                            ( <?= $langage_lbl_admin["LBL_PAID_IN_ORDER_NO_TXT"] ?># : <?= $db_order_data['vOrderAdjusmentId'] ?>)
                                                        <? } else if ($db_order_data['ePaymentOption'] == 'Cash') { ?>
                                                            ( <?= $langage_lbl_admin["LBL_UNPAID_WEB_TXT"] ?> )
                                                            <?
                                                        }
                                                        else if ($db_order_data['ePaymentOption'] == 'Card') {
                                                            $ePaymentOption = $langage_lbl_admin["LBL_PAID_BY_CARD_WEB_TXT"];
                                                            if ($db_order_data['ePayWallet'] == "Yes") {
                                                                $ePaymentOption = $langage_lbl_admin['LBL_WALLET_TXT'];
                                                            }
                                                            ?>
                                                            ( <?= $ePaymentOption; ?> )
                                                        <? } ?>
                                                    </p>
                                                    <p>Refunded Amount To <?= $langage_lbl_admin['LBL_RIDER']; ?> : <?= formateNumAsPerCurrency($db_order_data['fRefundAmount'], ''); ?>
                                                    <p><?= $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To Restaurant: <?= formateNumAsPerCurrency($db_order_data['fRestaurantPaidAmount'], ''); ?></p>
                                                    <p><?= $langage_lbl_admin["LBL_ADJUSTMENT_AMOUNT_MESSAGE"] ?> To <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>: <?= formateNumAsPerCurrency($db_order_data['fDriverPaidAmount'], ''); ?></p>
                                                </div>
                                            </div>
                                        <?php } ?>


                                        <?php
                                        // prescription Images displayed by sneha start
                                        $img_url = $tconfig["tsite_upload_prescription_image"];
                                        if (!empty($prescriptiondata)) {
                                            ?>
                                            <h4 style="text-align:center;"><?= "Prescription Images"; ?></h4>
                                            <hr/>
                                            <div class="invoice-right-bottom-img">
                                                <?php foreach ($prescriptiondata as $key => $val) { ?>
                                                    <div class="col-sm-3 new-id-work">
                                                        <b class="img-thumbnail"><a href="<?= $img_url . "/" . $val['vImage'] ?>" target="_blank"><img src="<?= $img_url . "/" . $val['vImage'] ?>" alt="Prescription Images"/></a></b>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        <?php }  // prescription Images displayed by sneha end   ?>

                                    </div>


                                    <div class="clear"></div>
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
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places,geometry" type="text/javascript" async></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script>
    h = window.innerHeight;
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    var map;
    var markers = [];

    function initialize() {
        map = GOOGLE_MAP_OBJ.init('map-canvas');

        <?php if($db_order_data['eOrderplaced_by'] == "Kiosk") { ?>
            from_to_kiosk();
        <?php } else { ?>
            from_to();
        <?php } ?>
    };

    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);
    });
    function from_to() {
        var vRestuarantLocationLat = '<?= $db_order_data['vRestuarantLocationLat'];?>';
        var vRestuarantLocationLong = '<?= $db_order_data['vRestuarantLocationLong'];?>';
        var vLatitude = '<?= $db_order_data['vLatitude'];?>';
        var vLongitude = '<?= $db_order_data['vLongitude'];?>';

        var newFrom = vRestuarantLocationLat + ", " + vRestuarantLocationLong;
        var newTo = vLatitude + ", " + vLongitude;
        var source_latitude = vRestuarantLocationLat;
        var source_longitude = vRestuarantLocationLong;
        var dest_latitude = vLatitude;
        var dest_longitude = vLongitude;
        var waypoint0 = newFrom;
        var waypoint1 = newTo;

        var latlng = [
            new google.maps.LatLng(source_latitude, source_longitude),
            new google.maps.LatLng(dest_latitude, dest_longitude)
        ];
        var latlngbounds = new google.maps.LatLngBounds();
        for (var i = 0; i < latlng.length; i++) {
            latlngbounds.extend(latlng[i]);
        }
        if ($(window).width() >= 768) {
            var elmnt = $(".rider-invoice-new-left").offset().right + $(".rider-invoice-new-left").outerWidth();
            map.fitBounds(latlngbounds, {top: 50, right: elmnt, left: 50, bottom: 50});
        } else {
            map.fitBounds(latlngbounds);
        }

        var trafalgar = new google.maps.LatLng(source_latitude, source_longitude);
        var rcbc = new google.maps.LatLng(dest_latitude, dest_longitude);
        drawCurve(trafalgar, rcbc, map);

        DeleteMarkers('from_loc');
        var latlng = new google.maps.LatLng(source_latitude, source_longitude);
        setMarker(latlng, 'from_loc');
        DeleteMarkers('to_loc');
        var latlng_to = new google.maps.LatLng(dest_latitude, dest_longitude);
        setMarker(latlng_to, 'to_loc');

        /*$("#map-canvas").gmap3({
            getroute: {
                options: {
                    origin: '<?= $db_order_data['vRestuarantLocationLat'] . "," . $db_order_data['vRestuarantLocationLong'] ?>',
                    destination: '<?= $db_order_data['vLatitude'] . "," . $db_order_data['vLongitude'] ?>',
                    travelMode: google.maps.DirectionsTravelMode.DRIVING
                },
                callback: function (results) {
                    if (!results)
                        return;
                    $(this).gmap3({
                        map: {
                            options: {
                                zoom: 13,
                                center: [-33.879, 151.235]
                            }
                        },
                        directionsrenderer: {
                            options: {
                                directions: results
                            }
                        }
                    });
                }
            }
        });*/
    }

    function from_to_kiosk() {
        var center = [<?= $db_order_data['vRestuarantLocationLat'] ?>, <?= $db_order_data['vRestuarantLocationLong'] ?>];
        $("#map-canvas").gmap3({
            map: {
                options: {
                    center: center,
                    zoom: 13,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
            },
            marker: {
                options: {
                    position: center,
                    icon: '<?= $tconfig['tsite_url'] ?>webimages/upload/mapmarker/source_marker_store.png'
                }
            }
        });
    }

    function setMarker(postitions, valIcon) {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinFrom.png';
        } else if (valIcon == 'to_loc') {
            newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinTo.png';
        } else {
            newIcon = '<?= $tconfig["tsite_url"] ?>/webimages/upload/mapmarker/PinTo.png';
        }

        var marker = new google.maps.Marker({
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon
        });
        marker.id = valIcon;
        markers.push(marker);
        map.setCenter(marker.getPosition());
        map.setZoom(11);
    }

    function DeleteMarkers(newId) {
        // Loop through all the markers and remove
        for (var i = 0; i < markers.length; i++) {
            if (newId != '') {
                if (markers[i].id == newId) {
                    markers[i].setMap(null);
                }
            } else {
                markers[i].setMap(null);
            }
        }
        if (newId == '') {
            markers = [];
        }
    }

    function drawCurve(P1, P2, map) {
        var lineLength = google.maps.geometry.spherical.computeDistanceBetween(P1, P2);
        var lineHeading = google.maps.geometry.spherical.computeHeading(P1, P2);
        if (lineHeading < 0) {
            var lineHeading1 = lineHeading + 45;
            var lineHeading2 = lineHeading + 135;
        } else {
            var lineHeading1 = lineHeading + -45;
            var lineHeading2 = lineHeading + -135;
        }
        var pA = google.maps.geometry.spherical.computeOffset(P1, lineLength / 4.0, lineHeading1);
        var pB = google.maps.geometry.spherical.computeOffset(P2, lineLength / 4.0, lineHeading2);


        var curvedLine = new GmapsCubicBezier(P1, pA, pB, P2, 0.01, map);
    }

    var GmapsCubicBezier = function(latlong1, latlong2, latlong3, latlong4, resolution, map) {
        var lat1 = latlong1.lat();
        var long1 = latlong1.lng();
        var lat2 = latlong2.lat();
        var long2 = latlong2.lng();
        var lat3 = latlong3.lat();
        var long3 = latlong3.lng();
        var lat4 = latlong4.lat();
        var long4 = latlong4.lng();

        var points = [];

        for (it = 0; it <= 1; it += resolution) {
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
        var path = [];
        for (var i = 0; i < points.length - 1; i++) {
            path.push(new google.maps.LatLng(points[i].x, points[i].y));
            path.push(new google.maps.LatLng(points[i + 1].x, points[i + 1].y, false));
        }

        var Line = new google.maps.Polyline({
            path: path,
            /*geodesic: true,
            strokeColor: "##35495e",
            strokeOpacity: 0.8,
            strokeWeight: 3,*/
            strokeOpacity: 0,
                icons: [{
                  icon: {
                    path: 'M 0,-1 0,1',
                    strokeOpacity: 1,
                    scale: 4
                  },
                  offset: '0',
                  repeat: '20px'
                }],
        });

        Line.setMap(map);
        return Line;
    };

    GmapsCubicBezier.prototype = {

        B1: function(t) {
            return t * t * t;
        },
        B2: function(t) {
            return 3 * t * t * (1 - t);
        },
        B3: function(t) {
            return 3 * t * (1 - t) * (1 - t);
        },
        B4: function(t) {
            return (1 - t) * (1 - t) * (1 - t);
        },
        getBezier: function(C1, C2, C3, C4, percent) {
            var pos = {};
            pos.x = C1.x * this.B1(percent) + C2.x * this.B2(percent) + C3.x * this.B3(percent) + C4.x * this.B4(percent);
            pos.y = C1.y * this.B1(percent) + C2.y * this.B2(percent) + C3.y * this.B3(percent) + C4.y * this.B4(percent);
            return pos;
        }
    };

</script>
</body>
<!-- END BODY-->
</html>
