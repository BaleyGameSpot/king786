<?php
include_once('common.php');
$tbl_name = 'register_user';
$script = "paymentRequest";
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';

$todayDate = date('Y-m-d H:i:s');
$ssql = $startDate = $endDate = $dateRange = '';
$ord = ' ORDER BY pr.iPublishedRideId  DESC';
$ssql .= "AND  rsb.eStatus = 'Approved' AND pr.iUserId = " . $_SESSION['sess_iUserId'];
$sql = "SELECT  rsb.fWalletDebit , rsb.ePayment_request , CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name,  CONCAT(riderUser.vName,' ',riderUser.vLastName) AS  rider_Name, 
                riderUser.vImgName as rider_ProfileImg, riderUser.iUserId as rider_iUserId, riderDriver.iUserId as driver_iUserId,
                pr.vPublishedRideNo,pr.tStartLocation,pr.tStartLat,pr.tStartLong,pr.tEndLocation,pr.tEndLat,pr.tEndLong,pr.dStartDate,pr.dStartDate,pr.dEndDate,pr.tPriceRatio,
                pr.tEndCity,pr.tStartCity,rsb.vBookingNo,rsb.dBookingDate,
                rsb.iPublishedRideId,rsb.eStatus,rsb.fTotal,rsb.iBookedSeats,pr.tDriverDetails,rsb.iBookingId,rsb.iCancelReasonId,rsb.tCancelReason, rsb.iBookingId , rsb.fBookingFee, rsb.ePaymentOption, rsb.ePaymentStatus,riderUser.vTimeZone
                FROM ride_share_bookings rsb 
                JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId)
                JOIN register_user riderUser  ON (riderUser.iUserId = rsb.iUserId)
                JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)  WHERE 1=1   $ssql AND pr.dEndDate < '$todayDate' $ord";
$db_trip = $obj->MySQLSelect($sql);

$sql = "SELECT * FROM register_user WHERE iUserId ='" . $_SESSION['sess_iUserId'] . "'";
$db_booking = $obj->MySQLSelect($sql);

$sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyPassenger'] . "'";
$db_curr_ratio = $obj->MySQLSelect($sql);

$tripcursymbol = $db_curr_ratio[0]['vSymbol'];
$tripcur = $db_curr_ratio[0]['Ratio'];
$tripcurname = $db_curr_ratio[0]['vName'];
$tripcurthholsamt = $db_curr_ratio[0]['fThresholdAmount'];
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?= $SITE_NAME ?></title>-->
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MY_EARN']; ?></title>
    <meta name="keywords" value=""/>
    <meta name="description" value=""/>
    <!-- Default Top Script and css -->
    <?php
    include_once("top/top_script.php");
    $rtls = "";
    if ($lang_ltr == "yes") {
        $rtls = "dir='rtl'";
    }
    ?>
    <style>
        .card-block {
            min-height: auto;
        }
    </style>
    <!-- End: Default Top Script and css-->
</head>
<body id="wrapper">
<!-- home page -->
<!-- home page -->
<?php if ($template != 'taxishark') { ?>
<div id="main-uber-page">
    <?php } ?>
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- First Section -->
    <?php include_once("top/header.php"); ?>
    <!-- End: First Section -->
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_MY_EARN'] ?></h1>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="table-holder">
                <form name="frmbooking" id="frmbooking" method="post"
                      action="<?php echo $tconfig['tsite_url']; ?>payment_request_a.php">
                    <input type="hidden" id="type" name="type" value="<?= $type; ?>">
                    <input type="hidden" id="action" name="action" value="send_equest_for_ride_share">
                    <input type="hidden" name="eTransRequest" id="eTransRequest" value="">
                    <input type="hidden" name="iBookingId" id="iBookingId" value="">
                    <input type="hidden" name="vHolderName1" id="vHolderName1" value="">
                    <input type="hidden" name="vBankName1" id="vBankName1" value="">
                    <input type="hidden" name="iBankAccountNo1" id="iBankAccountNo1" value="">
                    <input type="hidden" name="BICSWIFTCode1" id="BICSWIFTCode1" value="">
                    <input type="hidden" name="vBankBranch1" id="vBankBranch1" value="">
                    <?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close"
                                    type="button">×
                            </button>
                            <?= $var_msg ?>
                        </div>
                    <?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close"
                                    type="button">×
                            </button>
                            <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                    <?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 0) {
                        ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close"
                                    type="button">×
                            </button>
                            <?= $var_msg ?>
                        </div>
                    <?php }
                    ?>
                    <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
                        <thead>
                        <tr>
                            <th width="17%"><?php echo $langage_lbl['LBL_RIDE_SHARE_BOOKING_NO'] ?? 'Booking No..';?></th>
                            <th width="18%"><?php echo $langage_lbl['LBL_RIDE_SHARE_RIDE_NO'] ?? 'Ride No.';?></th>
                            <th width="15%"><?php echo $langage_lbl['LBL_RIDE_SHARE_RIDE_START_END_TIME'] ?? 'Ride Start & End Time';?></th>
                            <th width="15%" style="text-align: right"><?php echo $langage_lbl['LBL_RIDE_SHARE_BOOKING_FEE'] ?? 'Booking Fee';?></th>
                            <th width="15%" style="text-align: right"><?php echo $langage_lbl['LBL_RIDE_SHARE_TOTAL'] ?? 'Booking FeeTotal';?></th>
                            <th width="16%" style="text-align: right"><?php echo $langage_lbl['LBL_RIDE_SHARE_PAYMENT'] ?? 'Payment';?></th>
                            <th width="16%"><?php echo $langage_lbl['LBL_RIDE_SHARE_PAYMENT_METHOD'] ?? 'Payment <br>Method';?></th>
                            <th width="16%"><?php echo $langage_lbl['LBL_MYEARNING_REQUEST_PAYMENT'] ?? 'Request Payment For';?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $fBookingFee = $fTotal = $cardNo = 0;
                        if (isset($db_trip) && !empty($db_trip)) {
                            $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');

                            $systemTimeZone = date_default_timezone_get();
                            foreach ($db_trip as $trip) { 
                                // New Date Format
                                $date_format_data_array = array(
                                    'langCode' => $vLangCode,
                                    'DateFormatForWeb' => 1
                                );
                                $date_format_data_array['tdate'] = (!empty($trip['vTimeZone'])) ? converToTz($trip['dStartDate'], $trip['vTimeZone'], $systemTimeZone) :  $trip['dStartDate'];
                                $get_start_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                               

                                $date_format_data_array['tdate'] =  (!empty($trip['vTimeZone']) && $trip['dEndDate'] != "0000-00-00 00:00:00") ? converToTz($trip['dEndDate'], $trip['vTimeZone'], $systemTimeZone) : $trip['dEndDate'];
                                $get_end_date_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                ?>
                                <tr>
                                    <td><?= $trip['vBookingNo'] ?></td>
                                    <td><?= $trip['vPublishedRideNo'] ?></td>
                                    <td>
                                        <div class="lableCombineData">
                                            <label>Start Time</label>
                                            <span><?= $get_start_date_format['tDisplayDateTime'];//date('M d, Y  h:i A', strtotime($trip['dStartDate'])); ?> </span>
                                            <label>End Time</label>
                                            <span><?= $get_end_date_format['tDisplayDateTime'];//date('M d, Y  h:i A', strtotime($trip['dEndDate'])); ?></span>
                                        </div>
                                    </td>
                                    <td style="text-align: right"><?php echo formateNumAsPerCurrency(($trip['fBookingFee'] * $tripcur), $tripcurname);
                                        $fBookingFee += $trip['fBookingFee']; ?></td>
                                    <td><?=   formateNumAsPerCurrency($trip['fTotal'], $tripcurname);  ?></td>
                                    <td style="text-align: right">
                                        <?php
                                        $driverPay = $trip['fTotal'] - $trip['fBookingFee'];

                                        if($trip['ePaymentOption'] == 'Cash' && $trip['fWalletDebit'] > 0 ){
                                            $driverPay = $trip['fWalletDebit'];
                                        }
                                        echo formateNumAsPerCurrency(($driverPay * $tripcur), $tripcurname);
                                        $fTotal += $driverPay; ?>
                                    </td>
                                    <td><?= $trip['ePaymentOption'] ?></td>

                                    <td>
                                    <?php
                                       if ($trip['ePaymentStatus'] == "Unsettled")  {
                                            $cardNo++;
                                            ?>
                                            <?php if ($trip['ePaymentStatus'] == "Unsettled") { ?>
                                                <div class="check-main">
                                                    <label class="check-hold">
                                                        <input id="iBookingId_<?= $trip['iBookingId']; ?>" name="iBookingId[]" value="<?= $trip['iBookingId'] ?>" type="checkbox" <?php if ($trip['ePayment_request'] == 'Yes') { ?> checked="checked" disabled <?php } ?> >
                                                        <span class="check-button"></span>
                                                    </label>
                                                </div>
                                            <?php } else { ?>
                                                --
                                            <?php } ?>
                                            <?php
                                        } else {
                                            echo '---';
                                        }
                                        ?></td>
                                </tr>
                            <?php }
                        } ?>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="card-block">
                <div class="button-block">
                    <?php if ($cardNo != 0) { ?>
                        <div class="singlerow-login-log">
                            <a href="javascript:void(0);" onClick="javascript:check_skills_edit(); return false;"
                               class="gen-btn"><?= $langage_lbl['LBL_Send_transfer_Request']; ?></a>
                        </div>
                    <?php } //added by SP if anyone request is of the card then only this btn shown on 31-07-2019    ?>
                    <div class="your-requestd">
                        <b><?= $langage_lbl['LBL_THRESHOLDAMOUNT_NOTE1']; ?></b> <?= $langage_lbl['LBL_THRESHOLDAMOUNT_NOTE2']; ?><?= '  ' . formateNumAsPerCurrency(number_format($tripcurthholsamt, 2, '.', ''), $tripcurname); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="col-lg-12">
        <?php $type = $_SESSION['sess_user']; ?>
        <div class="custom-modal-main in" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="custom-modal">
                <div class="modal-content image-upload-1 popup-box1">
                    <div class="upload-content">
                        <div class="model-header">
                            <h4><?= $langage_lbl['LBL_WITHDRAW_REQUEST']; ?></h4>
                        </div>
                        <div class="model-body">
                            <form class="form-horizontal general-form" id="frm6" method="post"
                                  enctype="multipart/form-data" name="frm6">
                                <input type="hidden" id="action" name="action" value="send_equest">
                                <input type="hidden" name="iUserId" id="iUserId"
                                       value="<?= $_SESSION['sess_iUserId']; ?>">
                                <input type="hidden" name="eUserType" id="eUserType" value="<?= $type; ?>">
                                <div class="col-lg-13">
                                    <div class="input-group input-append">
                                        <div class="form-group newrow">
                                            <label><?= $langage_lbl['LBL_WALLET_ACCOUNT_HOLDER_NAME']; ?>*</label>
                                            <input type="text" name="vHolderName" id="vHolderName"
                                                   class="form-control vHolderName"
                                                   <?php if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBankAccountHolderName']; ?>"<?php } ?>>
                                        </div>
                                        <div class="form-group newrow">
                                            <label><?= $langage_lbl['LBL_WALLET_NAME_OF_BANK']; ?>*</label>
                                            <input type="text" name="vBankName" id="vBankName"
                                                   class="form-control vBankName"
                                                   <?php if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBankName']; ?>"<?php } ?>>
                                        </div>
                                        <div class="form-group newrow">
                                            <label><?= $langage_lbl['LBL_WALLET_ACCOUNT_NUMBER']; ?>*</label>
                                            <input type="text" name="iBankAccountNo" id="iBankAccountNo"
                                                   class="form-control iBankAccountNo"
                                                   <?php if ($type == 'driver') { ?>value="<?= $db_booking[0]['vAccountNumber']; ?>"<?php } ?>>
                                        </div>
                                        <div class="form-group newrow">
                                            <label><?= $langage_lbl['LBL_WALLET_BIC_SWIFT_CODE']; ?>*</label>
                                            <input type="text" name="BICSWIFTCode" id="BICSWIFTCode"
                                                   class="form-control BICSWIFTCode"
                                                   <?php if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBIC_SWIFT_Code']; ?>"<?php } ?>>
                                        </div>
                                        <div class="form-group newrow">
                                            <label><?= $langage_lbl['LBL_WALLET_BANK_LOCATION']; ?>*</label>
                                            <input type="text" name="vBankBranch" id="vBankBranch"
                                                   class="form-control vBankBranch"
                                                   <?php if ($type == 'driver') { ?>value="<?= $db_booking[0]['vBankLocation']; ?>"<?php } ?>>
                                        </div>
                                    </div>
                                </div>
                                <div class="model-footer">
                                    <div class="button-block">
                                        <input type="button" onClick="check_login_small();" id="withdrawal_request"
                                               class="save gen-btn" name="<?= $langage_lbl['LBL_WALLET_save']; ?>"
                                               value="<?= $langage_lbl['LBL_BTN_SEND_TXT']; ?>">
                                        <input type="button" class="gen-btn" data-dismiss="modal"
                                               name="<?= $langage_lbl['LBL_WALLET_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>"
                                               value="<?= $langage_lbl['LBL_WALLET_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- home page end-->
        <!-- footer part -->
    </div>
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
    <?php if ($template != 'taxishark') { ?>
</div>
<?php } ?>

<?php include_once('top/footer_script.php'); ?>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script type="text/javascript">
    if ($('#my-trips-data').length > 0) {
        $('#my-trips-data').dataTable({
            "oLanguage": langData,
            "order": [
                [2, "desc"]
            ]
        });
    }

    function getCheckCount(frmbooking) {
        var x = 0;
        var threasold_value = 0;
        for (var i = 0; i < frmbooking.length; i++) {
            if (frmbooking[i].checked == true && frmbooking[i].disabled == false) {
                x++;
            }
        }
        return x;
    }

    function check_skills_edit() {
        y = getCheckCount(document.getElementsByName('iBookingId[]'));

        if (y > 0) {
            $("#eTransRequest").val('Yes');
            $('#myModal').addClass('active');
        } else {
            alert("<?php echo addslashes($langage_lbl['LBL_SELECT_RIDE_FOR_TRANSFER_MSG']) ?>")
            return false;
        }
    }

    function check_login_small() {
        var vHolderName = document.getElementById("vHolderName").value;
        var vBankName = document.getElementById("vBankName").value;
        var iBankAccountNo = document.getElementById("iBankAccountNo").value;
        var BICSWIFTCode = document.getElementById("BICSWIFTCode").value;
        var vBankBranch = document.getElementById("vBankBranch").value;

        if (vHolderName == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_ACCOUNT_HOLDER_NAME_MSG']) ?>");
            return false;
        }
        if (vBankName == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_BANK_MSG']) ?>");
            return false;
        }
        if (iBankAccountNo == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_ACCOUNT_NUM_MSG']) ?>");
            return false;
        }
        if (BICSWIFTCode == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_BIC_SWIFT_CODE_MSG']) ?>");
            return false;
        }
        if (vBankBranch == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_BANK_BRANCH_MSG']) ?>");
            return false;
        }

        if (vHolderName != "" && vBankName != "" && iBankAccountNo != "" && BICSWIFTCode != "" && vBankBranch != "") {
            ShpSq6fAm7(frmbooking);
            document.getElementById("vHolderName1").value = vHolderName;
            document.getElementById("vBankName1").value = vBankName;
            document.getElementById("iBankAccountNo1").value = iBankAccountNo;
            document.getElementById("BICSWIFTCode1").value = BICSWIFTCode;
            document.getElementById("vBankBranch1").value = vBankBranch;
            document.getElementById("withdrawal_request").value = 'Please wait ...';
            document.getElementById("withdrawal_request").setAttribute('disabled', 'disabled');
            document.frmbooking.submit();
        }
    }
</script>
</body>
</html>