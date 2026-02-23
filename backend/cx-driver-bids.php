<?php
include_once('common.php');

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'driver';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$script = "Bids";

$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = $startDate = $endDate = $dateRange = '';
if (isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != "") {
    $startDate = $_REQUEST['startDate'];
}
if (isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != "") {
    $endDate = $_REQUEST['endDate'];
}
if (isset($_REQUEST['dateRange']) && $_REQUEST['dateRange'] != "") {
    $dateRange = $_REQUEST['dateRange'];
}
if ($action != '') {
    $dateRange = $_REQUEST['dateRange'];
    if ($startDate != '') {
        $ssql .= " AND Date(bid.dBiddingDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(bid.dBiddingDate) <='" . $endDate . "'";
    }
}

if ($_SESSION['sess_user'] == "driver") {
    $sql = "SELECT * FROM register_" . $_SESSION['sess_user'] . " WHERE iDriverId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);

    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyDriver'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
} else {
    $sql = "SELECT * FROM register_user WHERE iUserId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyPassenger'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
}

$tripcur = $db_curr_ratio[0]['Ratio'];
$tripcurname = $db_curr_ratio[0]['vName'];
$deafultLang = $_SESSION['sess_lang'];

 $sql = "SELECT bid.*, CONCAT(ru.vName,' ',ru.vLastName) AS riderName, ru.iUserId, d.iDriverId, ru.iGcmRegId, ru.vPhone, ru.vPhoneCode, ru.vImgName, ua.vLatitude, ua.vLongitude, ua.vAddressType, ua.vServiceAddress,CONCAT(d.vName,' ',d.vLastName) AS driverName, JSON_UNQUOTE(JSON_VALUE(bs.vTitle, '$.vTitle_" . $deafultLang . "')) as vTitle FROM bidding_post as bid LEFT JOIN register_user as ru ON ru.iUserId = bid.iUserId LEFT JOIN user_address as ua ON ua.iUserAddressId = bid.iAddressId LEFT JOIN register_driver d ON d.iDriverId = bid.iDriverId LEFT JOIN bidding_service as bs ON bs.iBiddingId = bid.iBiddingId WHERE 1=1 AND bid.iDriverId= '" . $_SESSION['sess_iUserId'] . "'  {$ssql} ";

$db_trip = $obj->MySQLSelect($sql);


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

if (file_exists($logogpath . "driver-view-icon.png")) {
    $invoice_icon = $logogpath . "driver-view-icon.png";
} else {
    $invoice_icon = "assets/img/driver-view-icon.png";
}

if (file_exists($logogpath . "canceled-invoice.png")) {
    $canceled_icon = $logogpath . "canceled-invoice.png";
} else {
    $canceled_icon = "assets/img/canceled-invoice.png";
}


?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_BIDS_TXT']; ?></title>
    <meta name="keywords" value="" />
    <meta name="description" value="" />
    <!-- Default Top Script and css -->
    <?php
    include_once("top/top_script.php");
    $rtls = "";
    if ($lang_ltr == "yes") {
        $rtls = "dir='rtl'";
    }
    ?>
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
                        <h1><?= $langage_lbl['LBL_HEADER_TOPBAR_BIDS_TEXT'] ?></h1>
                    </div>
                    <form class="tabledata-filter-block filter-form" name="search" method="post" onSubmit="return checkvalid()">
                        <input type="hidden" name="action" value="search" />
                        <div class="filters-column mobile-full">
                            <label><?= $langage_lbl['LBL_SEARCH_RIDES_POSTED_BY_DATE']; ?></label>
                            <select id="timeSelect" name="dateRange">
                                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                <option value="today" <?php if ($dateRange == 'today') { echo 'selected';  } ?>><?= $langage_lbl['LBL_Today']; ?></option>
                                <option value="yesterday" <?php if ($dateRange == 'yesterday') { echo 'selected'; }?>><?= $langage_lbl['LBL_Yesterday']; ?></option>

                                <option value="currentWeek" <?php  if ($dateRange == 'currentWeek') { echo 'selected';}?>><?= $langage_lbl['LBL_Current_Week']; ?></option>

                                <option value="previousWeek" <?php if ($dateRange == 'previousWeek') { echo 'selected'; } ?>><?= $langage_lbl['LBL_Previous_Week']; ?></option>

                                <option value="currentMonth" <?php if ($dateRange == 'currentMonth') { echo 'selected'; } ?>><?= $langage_lbl['LBL_Current_Month']; ?></option>

                                <option value="previousMonth" <?php if ($dateRange == 'previousMonth') { echo 'selected';} ?>><?= $langage_lbl['LBL_Previous Month']; ?></option>

                                <option value="currentYear" <?php if ($dateRange == 'currentYear') { echo 'selected'; } ?>><?= $langage_lbl['LBL_Current_Year']; ?></option>

                                <option value="previousYear" <?php if ($dateRange == 'previousYear') { echo 'selected'; } ?>><?= $langage_lbl['LBL_Previous_Year']; ?></option>

                            </select>
                        </div>
                        <div class="filters-column mobile-half">
                            <label><?= $langage_lbl['LBL_MYBIDS_FROM_DATE']; ?></label>
                            <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_MYBIDS_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff" />
                            <i class="icon-cal" id="from-date"></i>
                        </div>
                        <div class="filters-column mobile-half">
                            <label><?= $langage_lbl['LBL_MYBIDS_TO_DATE']; ?></label>
                            <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_MYBIDS_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff" />
                            <i class="icon-cal" id="to-date"></i>
                        </div>
                        <div class="filters-column mobile-full">
                            <button class="driver-trip-btn"><?= $langage_lbl['LBL_MYBID_SEARCH']; ?></button>
                            <a href="provider-bid" class="gen-btn"><?= $langage_lbl['LBL_MYBID_RESET']; ?></a>

                        </div>
                    </form>
                </div>
            </div>
        </section>
        <section class="profile-earning">
            <div class="profile-earning-inner">
                <div class="table-holder">
                    <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="17%"><?= $langage_lbl['LBL_MYBID_RIDE_NO']; ?></th>
                                <th width="18%"><?= $langage_lbl['LBL_MYTRIP_TRIP_RIDER']; ?></th>
                                <th width="15%"><?= $langage_lbl['LBL_MYBID_TRIPDATE']; ?></th>
                                <th width="15%"><?= $langage_lbl['LBL_MYBID_CAR']; ?></th>
                                <th width="15%"><?= $langage_lbl['LBL_Your_Fare']; ?></th>
                                <th width="15%"><?= $langage_lbl['LBL_MYBID_STATUS']; ?></th>
                                <th width="16%"><?= $langage_lbl['LBL_MYBID_VIEW_INVOICE']; ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 0; $i < scount($db_trip); $i++) {
                                
                                $car = $db_trip[$i]['vTitle'];
                                $pickup = isset($db_trip[$i]['tSaddress']) ? $db_trip[$i]['tSaddress'] : '';
                                $riderName = clearName($db_trip[$i]['riderName']);
                                $fareAmount =  $BIDDING_OBJ->getbiddingFinalAmount($db_trip[$i]['iBiddingPostId']);
                                $fCommission = round(($fareAmount*$db_trip[$i]['fCommission'])/100,2);
                                $fOutStandingAmount =  $db_trip[$i]['fOutStandingAmount'];
                                $Subtotal = $fareAmount - $fCommission + $fOutStandingAmount;
                                $fare = trip_currency_payment($Subtotal , $db_trip[$i]['fRatio_' . $tripcurname]);
                                
                         
                                $link_page = "cx-invoice-bids.php";
                                
                                $systemTimeZone = date_default_timezone_get();
                                if ($db_trip[$i]['dBiddingDate'] != "" && $db_trip[$i]['vTimeZone'] != "") {
                                    if ($db_trip[$i]['dStartDate'] == "0000-00-00 00:00:00") {
                                        $db_trip[$i]['dBiddingDate'] = $db_trip[$i]['dBiddingDate'];
                                    } else {
                                        $db_trip[$i]['dBiddingDate'] = $db_trip[$i]['dStartDate'];
                                    }
                                    $dBookingDate = converToTz($db_trip[$i]['dBiddingDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone);
                                } else {
                                    $dBookingDate = $db_trip[$i]['tTripRequestDate'];
                                }
                                $date_format_data_array = array(
                                    'tdate' => $dBookingDate,
                                    'langCode' => $deafultLang,
                                    'DateFormatForWeb' => 1
                                );
                                $get_request_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                
                            ?>
                                <tr class="gradeA">
                                    <td align="center" data-order="<?= $db_trip[$i]['iBiddingPostId'] ?>"><?= $db_trip[$i]['vBiddingPostNo'] ?></td>
                                    <td>
                                        <?php
                                        if ($riderName == '') {
                                            echo '--';
                                        } else {
                                            echo $riderName;
                                        }
                                        ?>
                                    </td>
                                    <td data-order="<?= $db_trip[$i]['iBiddingPostId'] ?>" align="center"><span style="display:none;"><?= strtotime($dBookingDate) ?></span><?= $get_request_date_format['tDisplayDateTime'];//DateTime1($dBookingDate, 'no'); ?></td>
                                    <td align="center" class="center">
                                        <?php echo $car;?>
                                    </td>
                                    <td align="right" class="center">
                                        <?php
                                            if ($db_trip[$i]['eStatus'] == 'Cancelled') {
                                                echo formateNumAsPerCurrency($fare, $tripcurname);
                                            } else {
                                                echo formateNumAsPerCurrency($fare, $tripcurname);
                                            }
                                        ?>
                                    </td>
                                  
                                    <td align="center" class="center">
                                        <?php echo $db_trip[$i]['eStatus'];?>
                                    </td>
                                   <? if ($db_trip[$i]['eStatus'] == 'Completed') { ?>
                                        <td class="center">
                                            <a target="_blank" href="<?= $link_page ?>?iBiddingPostId=<?= base64_encode(base64_encode($db_trip[$i]['iBiddingPostId'])) ?>"><strong><img src="<?php echo $invoice_icon; ?>"></strong></a>

                                        </td>
                                        <? } else if ($db_trip[$i]['eStatus'] == 'Cancelled' && $db_trip[$i]['iCancelReasonId'] > 0) {
                                        ?>
                                            <td class="center">
                                                <a href="#" data-toggle="modal" data-target="#uiModal1_<?= $db_trip[$i]['iBiddingPostId']; ?>">
                                                    <img src="<?php echo $canceled_icon; ?>" title="<?= $langage_lbl['LBL_MYBID_CANCELED_TXT']; ?>">
                                                </a>
                                            </td>
                                    

                                        <div class="custom-modal-main" id="uiModal1_<?= $db_trip[$i]['iBiddingPostId']; ?>">
                                            <div class="custom-modal">
                                                <div class="model-header">
                                                    <h4><?= $langage_lbl['LBL_RIDE_TXT_ADMIN'] . " " . $langage_lbl['LBL_CANCEL_REASON']; ?></h4>
                                                    <i class="icon-close" data-dismiss="modal"></i>
                                                </div>
                                            <div class="model-body">
                                            <ul class="value-listing">
                                            <li><b><?= $langage_lbl['LBL_CANCEL_REASON']; ?> : </b><span> <?
                                                if ($db_trip[$i]['iCancelReasonId'] > 0) {
                                                    $cancelreasonarray = getCancelReason($db_trip[$i]['iCancelReasonId'], $deafultLang);
                                                    $db_trip[$i]['vCancelReason'] = $cancelreasonarray['vCancelReason'];
                                                }
                                               
                                                if ($db_trip[$i]['eCancelBy'] == "User") {
                                                    $eCancelBy = $langage_lbl['LBL_RIDER'];
                                                } else if ($db_trip[$i]['eCancelBy'] == "Driver") {
                                                    $eCancelBy = $langage_lbl['LBL_DRIVER'];
                                                }
                                                echo stripcslashes($db_trip[$i]['vCancelReason']);
                                                ?></span></li>
                                            <li><b><?= $langage_lbl['LBL_CANCEL_BY']; ?>:</b> <span><?= stripcslashes($eCancelBy); ?></span></li>
                                            </ul>
                                            </div>
                                                <div class="model-footer">
                                                    <div class="button-block">
                                                        <button type="button" class="gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <? } else { ?>
                                        <td class="center">  -- </td>
                                    <?php } ?>
                                </tr>
                            <? } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- home page end-->
        <!-- footer part -->
        <?php include_once('footer/footer_home.php'); ?>

        <div style="clear:both;"></div>
        <?php if ($template != 'taxishark') { ?>
        </div>
    <?php } ?>
    <!-- footer part end -->
    <div class="custom-modal-main" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="custom-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="upload-content">
                        <div class="model-header">
                            <h4 id="servicetitle">
                                <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                                Service Details
                            </h4>
                            <i class="icon-close" data-dismiss="modal"></i>
                        </div>
                        <div class="model-body" style="max-height: 450px;overflow: auto;">
                            <div id="service_detail"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php'); ?>
    <script src="assets/js/jquery-ui.min.js"></script>
    <script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


    <script type="text/javascript">
        if ($('#my-trips-data').length > 0) {
            $('#my-trips-data').dataTable({
                "oLanguage": langData,
                "order": [
                    [2, "desc"]
                ],
                "aoColumns": [
                    null,
                    null,
                    null,
                    null,
                    null,
                    {
                        "bSortable": false
                    },
                    null
                ]
            });
        }
        $(document).on('change', '#timeSelect', function(e) {
            e.preventDefault();

            var timeSelect = $(this).val();

            if (timeSelect == 'today') {
                todayDate('dp4', 'dp5')
            }
            if (timeSelect == 'yesterday') {
                yesterdayDate('dFDate', 'dTDate')
            }
            if (timeSelect == 'currentWeek') {
                currentweekDate('dFDate', 'dTDate')
            }
            if (timeSelect == 'previousWeek') {
                previousweekDate('dFDate', 'dTDate')
            }
            if (timeSelect == 'currentMonth') {
                currentmonthDate('dFDate', 'dTDate')
            }
            if (timeSelect == 'previousMonth') {
                previousmonthDate('dFDate', 'dTDate')
            }
            if (timeSelect == 'currentYear') {
                currentyearDate('dFDate', 'dTDate')
            }
            if (timeSelect == 'previousYear') {
                previousyearDate('dFDate', 'dTDate')
            }

        });
    </script>

    <script type="text/javascript">
        var typeArr = '<?= json_encode($vehilceTypeArr); ?>';
        $(document).ready(function() {
            $("#dp4").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true,
                yearRange: "-100:+10"
            });
            $("#dp5").datepicker({
                dateFormat: "yy-mm-dd",
                changeYear: true,
                changeMonth: true,
                yearRange: "-100:+10"
            });
            if ('<?= $startDate ?>' != '') {
                $("#dp4").val('<?= $startDate ?>');
                $("#dp4").datepicker('refresh');
            }
            if ('<?= $endDate ?>' != '') {
                $("#dp5").val('<?= $endDate; ?>');
                $("#dp5").datepicker('refresh');
            }
            // formInit();
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
            $("#dp5").val('<?= $Yesterday; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function currentweekDate(dt, df) {
            $("#dp4").val('<?= $monday; ?>');
            $("#dp5").val('<?= $sunday; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function previousweekDate(dt, df) {
            $("#dp4").val('<?= $Pmonday; ?>');
            $("#dp5").val('<?= $Psunday; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function currentmonthDate(dt, df) {
            $("#dp4").val('<?= $currmonthFDate; ?>');
            $("#dp5").val('<?= $currmonthTDate; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function previousmonthDate(dt, df) {
            $("#dp4").val('<?= $prevmonthFDate; ?>');
            $("#dp5").val('<?= $prevmonthTDate; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function currentyearDate(dt, df) {
            $("#dp4").val('<?= $curryearFDate; ?>');
            $("#dp5").val('<?= $curryearTDate; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function previousyearDate(dt, df) {
            $("#dp4").val('<?= $prevyearFDate; ?>');
            $("#dp5").val('<?= $prevyearTDate; ?>');
            $("#dp4").datepicker('refresh');
            $("#dp5").datepicker('refresh');
        }

        function checkvalid() {
            if ($("#dp5").val() < $("#dp4").val()) {
                //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
                bootbox.dialog({
                    message: "<h4><?php echo addslashes($langage_lbl['LBL_FROM_TO_DATE_ERROR_MSG']); ?></h4>",
                    buttons: {
                        danger: {
                            label: "OK",
                            className: "btn-danger"
                        }
                    }
                });
                return false;
            }
        }

    </script>

    <!-- End: Footer Script -->
</body>

</html>