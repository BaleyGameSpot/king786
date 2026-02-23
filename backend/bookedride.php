<?php
include_once('common.php');

$script = "BookedRide";
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$RIDE_SHARE_OBJ->WebCommonParam();
$PublishedRides_DATA = $RIDE_SHARE_OBJ->fetchBookings();

$rideData = [];
if (isset($PublishedRides_DATA['message']) && !empty($PublishedRides_DATA['message'])) {
    $rideData = $PublishedRides_DATA['message'];
}

if (file_exists($logogpath . "driver-view-icon.png")) {
    $invoice_icon = $logogpath . "driver-view-icon.png";
}
else {
    $invoice_icon = "assets/img/driver-view-icon.png";
}
$dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';

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
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?= $SITE_NAME ?></title>-->
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_RIDE_SHARE_BOOKED_RIDE']; ?></title>
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
                    <h1><?= $langage_lbl['LBL_RIDE_SHARE_BOOKED_RIDE'] ?></h1>
                </div>

                <form class="tabledata-filter-block filter-form" name="search" method="post"
                      onSubmit="return checkvalid()">
                    <input type="hidden" name="action" value="search"/>
                    <div class="filters-column mobile-full">
                        <label><?= $langage_lbl['LBL_SEARCH_RIDES_POSTED_BY_DATE']; ?></label>
                        <select id="timeSelect" name="dateRange">
                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                            <option value="today" <?php
                            if ($dateRange == 'today') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Today']; ?></option>
                            <option value="yesterday" <?php
                            if ($dateRange == 'yesterday') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Yesterday']; ?></option>
                            <option value="currentWeek" <?php
                            if ($dateRange == 'currentWeek') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Current_Week']; ?></option>
                            <option value="previousWeek" <?php
                            if ($dateRange == 'previousWeek') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Previous_Week']; ?></option>
                            <option value="currentMonth" <?php
                            if ($dateRange == 'currentMonth') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Current_Month']; ?></option>
                            <option value="previousMonth" <?php
                            if ($dateRange == 'previousMonth') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_PREVIOUS'] . ' ' . $langage_lbl['LBL_MONTH_TXT']; ?></option>
                            <option value="currentYear" <?php
                            if ($dateRange == 'currentYear') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Current_Year']; ?></option>
                            <option value="previousYear" <?php
                            if ($dateRange == 'previousYear') {
                                echo 'selected';
                            }
                            ?>><?= $langage_lbl['LBL_Previous_Year']; ?></option>

                        </select>
                    </div>
                    <div class="filters-column mobile-half">
                        <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?></label>
                        <input type="text" id="dp4" name="startDate"
                               placeholder="<?= $langage_lbl['LBL_MYTRIP_FROM_DATE']; ?>" class="form-control" value=""
                               readonly style="cursor:default; background-color: #fff"/>
                        <i class="icon-cal" id="from-date"></i>
                    </div>
                    <div class="filters-column mobile-half">
                        <label><?= $langage_lbl['LBL_MYTRIP_TO_DATE']; ?></label>
                        <input type="text" id="dp5" name="endDate"
                               placeholder="<?= $langage_lbl['LBL_MYTRIP_TO_DATE']; ?>" class="form-control" value=""
                               readonly style="cursor:default; background-color: #fff"/>
                        <i class="icon-cal" id="to-date"></i>
                    </div>
                    <div class="filters-column mobile-full">
                        <button class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_Search']; ?></button>
                        <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                        <a href="BookedRide" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>

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
                        <th width="10%"><?= $langage_lbl['LBL_RIDE_SHARE_BOOKING_NO']; ?></th>
                        <th width="15%"><?= $langage_lbl['LBL_RIDE_SHARE_PUBLISHED_BY']; ?></th>
                        <th width="30%"><?= $langage_lbl['LBL_RIDE_SHARE_START_DATE']; ?></th>
                        <th width="15%"><?= $langage_lbl['LBL_RIDE_SHARE_DETAILS_START_LOC_TXT']; ?></th>
                        <th width="15%"><?= $langage_lbl['LBL_RIDE_SHARE_DETAILS_END_LOC_TXT']; ?></th>
                        <th width="10%"><?= $langage_lbl['LBL_RIDE_SHARE_BOOKED_SEATS']; ?></th>
                        <th width="16%"><?= $langage_lbl['LBL_RIDE_SHARE_BOOKING_STATUS']; ?>Booking Status</th>
                        <th width="16%"><?= $langage_lbl['LBL_RIDE_SHARE_BOOKING_STATUS']; ?>Details</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($rideData) && !empty($rideData)) {
                        foreach ($rideData as $ride) { ?>
                            <tr>
                                <td>  <?php echo $ride['vBookingNo']; ?> </td>
                                <td>  <?php echo $ride['DriverName']; ?> </td>
                                <td>  <?php echo $ride['StartDate']; ?> <?php echo $ride['StartTime']; ?> </td>
                                <td style="text-align: left;">  <?php echo $ride['tStartLocation']; ?></td>
                                <td style="text-align: left;"> <?php echo $ride['tEndLocation']; ?></td>
                                <td>  <?php echo $ride['vNoOfPassengerText']; ?> </td>
                                <td>  <?php echo $ride['eStatusText']; ?> </td>
                                <td>
                                    <?php $link_page = "booked_ride_details.php" ?>
                                    <a target="_blank"
                                       href="<?= $link_page ?>?iBookingId=<?= base64_encode(base64_encode($ride['iBookingId'])) ?>"><strong><img
                                                src="<?php echo $invoice_icon; ?>"></strong></a>
                                </td>
                            </tr>
                        <?php }
                    }
                    ?>

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
<div class="custom-modal-main" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
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
        $('#my-trips-data').dataTable();
    }
    $(document).on('change', '#timeSelect', function (e) {
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
    var typeArr = '<?= getJsonFromAnArr($vehilceTypeArr); ?>';
    $(document).ready(function () {
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