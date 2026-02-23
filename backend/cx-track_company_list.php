<?php

include_once('common.php');

$script = "Trips";

$tbl_name = 'trips';

$AUTH_OBJ->checkMemberAuthentication();


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

$monday = date('Y-m-d', strtotime('sunday this week -1 week'));

$sunday = date('Y-m-d', strtotime('saturday this week'));

$Pmonday = date('Y-m-d', strtotime('sunday this week -2 week'));

$Psunday = date('Y-m-d', strtotime('saturday this week -1 week'));


$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';

$iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];

$abc = 'tracking_company';

$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

setRole($abc, $url);

$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');

$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';

$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';



$sql1 - '';

if(!empty($searchRider)){

   $sql1 =  " AND FIND_IN_SET(".$searchRider.",iUserIds)";

}

if ($startDate != '') {

    $sql1 .= " AND Date(tst.dAddedDate) >='" . $startDate . "'";

}

if ($endDate != '') {

    $sql1 .= " AND Date(tst.dAddedDate) <='" . $endDate . "'";

}



$sql = "SELECT GROUP_CONCAT(DISTINCT(iDriverId)) as driverId FROM register_driver where iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' and eStatus != 'Deleted'";

$db_driver = $obj->MySQLSelect($sql);



$driverId = $db_driver[0]['driverId'];

$sql = "SELECT tst.iTrackServiceTripId,rd.vName,rd.vLastName,tst.tStartLocation,tst.tEndLocation,tst.dStartDate FROM `track_service_trips` as tst JOIN register_driver rd ON (rd.iDriverId = tst.iDriverId) 

        WHERE  1 = 1 AND tst.iDriverId IN ($driverId) $sql1 order by tst.dStartDate DESC";

$db_trip = $obj->MySQLSelect($sql);


$sql = "SELECT vEmail,vName,vLastName,iTrackServiceUserId FROM track_service_users where iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' and eStatus != 'Deleted' order by dAddedDate DESC";

$db_rider = $obj->MySQLSelect($sql);

?>

<!DOCTYPE html>

<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_TRIP_REPORT_WEB']; ?></title>

    <!-- Default Top Script and css -->

    <?php include_once("top/top_script.php"); ?>

    <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->

    <!-- End: Default Top Script and css-->

</head>

<body id="wrapper">

<!-- home page -->

<!-- home page -->

<?php if ($template != 'taxishark'){ ?>

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

                    <h1><?php echo $langage_lbl['LBL_TRACK_SERVICE_COMPANY_TRIP_REPORT_WEB']; ?></h1>

                </div>

                <form class="tabledata-filter-block filter-form" name="search" method="post" onSubmit="return checkvalid()">

                    <input type="hidden" name="action" value="search"/>

                    <div class="filters-column mobile-full">

                        <label><?= $langage_lbl['LBL_COMPANY_TRIP_SEARCH_RIDES_POSTED_BY_TIME_PERIOD']; ?></label>

                        <select id="timeSelect" name="dateRange">

                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>

                            <option value="today" <?php if ($dateRange == 'today') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></option>

                            <option value="yesterday" <?php if ($dateRange == 'yesterday') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></option>

                            <option value="currentWeek" <?php if ($dateRange == 'currentWeek') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></option>

                            <option value="previousWeek" <?php if ($dateRange == 'previousWeek') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></option>

                            <option value="currentMonth" <?php if ($dateRange == 'currentMonth') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></option>

                            <option value="previousMonth" <?php if ($dateRange == 'previousMonth') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></option>

                            <option value="currentYear" <?php if ($dateRange == 'currentYear') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></option>

                            <option value="previousYear" <?php if ($dateRange == 'previousYear') {

                                echo 'selected';

                            } ?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></option>

                        </select>

                    </div>

                    <div class="filters-column mobile-half">

                        <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE'] ?></label>

                        <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>

                        <i class="icon-cal" id="from-date"></i>

                    </div>

                    <div class="filters-column mobile-half">

                        <label><?= $langage_lbl['LBL_MYTRIP_TO_DATE'] ?></label>

                        <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>

                        <i class="icon-cal" id="to-date"></i>

                    </div>



                    <div class="filters-column mobile-full">

                        <select class="form-control filter-by-text" name='searchRider' data-text="Select <?php echo $langage_lbl['LBL_PASSANGER_TXT_ADMIN']; ?>">

                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_PASSANGER_TXT_ADMIN']; ?></option>

                            <?php foreach ($db_rider as $dbr) { ?>

                                <option value="<?php echo $dbr['iTrackServiceUserId']; ?>" <?php

                                if ($searchRider == $dbr['iTrackServiceUserId']) {

                                    echo "selected";

                                }

                                ?>><?php echo clearName($dbr['vName'] . $dbr['vLastName'] ); ?> -

                                    ( <?php echo clearEmail($dbr['vEmail']); ?> )

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="filters-column mobile-full">

                        <button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>

                        <a href="trackingtriplist" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>

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

                        <th></th>

                        <th><?= $langage_lbl['LBL_Pick_Up']; ?></th>

                        <th><?= $langage_lbl['LBL_DRIVER_COMPANY_TXT']; ?></th>

                        <th><?= $langage_lbl['LBL_TRIP_DATE_TXT']; ?></th>



                        <th><?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER_LIST_WEB']; ?></th>



                    </tr>

                    </thead>

                    <tbody>

                    <?php
                    if(!empty($db_trip)){

                        $fareTotal = 0;

                        for ($i = 0;  $i < scount($db_trip);   $i++) { ?>

                            <tr role="row">

                                <td></td>

                                <td>

                                    <div class="lableCombineData">

                                        <label><?= $langage_lbl['LBL_Pick_Up'] ?></label>

                                        <span><?= $db_trip[$i]['tStartLocation'] ?> </span>

                                        <label><?= $langage_lbl['LBL_DROP_AT'] ?></label>

                                        <span> <?= $db_trip[$i]['tEndLocation']; ?></span></div>

                                </td>

                                <td>

                                    <span><?= clearName($db_trip[$i]['vName']); ?> <?= clearName($db_trip[$i]['vLastName']); ?></span>

                                </td>

                                <td>

                                    <span><?= DateTime($db_trip[$i]['dStartDate'], '21'); ?></span>

                                </td>

                                <td>

                                    <button class="gen-btn" href="#" onclick="viewCancelOrderDrivers(this);" class="btn btn-info" data-id="<?= $db_trip[$i]['iTrackServiceTripId']; ?>" type="button">

                                        <?= $langage_lbl['LBL_VIEW_USER_DETAIL']; ?></button>

                                </td>

                            </tr>

                        <? } 
                    }  ?>

                    </tbody>

                    <tfoot></tfoot>

                </table>

            </div>

        </div>

    </section>

    <!-- home page end-->

    <!-- footer part -->

    <?php include_once('footer/footer_home.php'); ?>

    <div style="clear:both;"></div>

    <?php if ($template != 'taxishark'){ ?>

</div>

<?php } ?>

<!-- footer part end -->

<!-- Footer Script -->

<?php include_once('top/footer_script.php'); ?>

<script src="assets/js/jquery-ui.min.js"></script>

<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>

<script src="assets/js/modal_alert.js"></script>

<script type="text/javascript">

    if ($('#my-trips-data').length > 0) {

        $('#my-trips-data').DataTable({"oLanguage": langData});

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



    });

    if ('<?= $startDate ?>' != '') {

        $("#dp4").val('<?= $startDate ?>');

        $("#dp4").datepicker('refresh');

    }

    if ('<?= $endDate ?>' != '') {

        $("#dp5").val('<?= $endDate; ?>');

        $("#dp5").datepicker('refresh');

    }



    function todayDate() {

        $("#dp4").val('<?= $Today; ?>');

        $("#dp5").val('<?= $Today; ?>');

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

                        label: "<?= $langage_lbl['LBL_OK'] ?>",

                        className: "btn-danger"

                    }

                }

            });

            return false;

        }

    }



    function viewCancelOrderDrivers(elem) {

        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url'] ?>ajax-track_compnay_list.php',

            'AJAX_DATA': {tracking_company_trip_user_list: 1, tripId: $(elem).data('id')},

            'REQUEST_DATA_TYPE': 'json'

        };

        getDataFromAjaxCall(ajaxData, function (response) {

            if (response.action == "1") {

                var dataHtml2 = response.result;

                if (dataHtml2.Action == 1) {

                    if (dataHtml2.message != "") {

                        show_alert("<?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER'] ?> Details", dataHtml2.message, "", "", "<?= $langage_lbl['LBL_BTN_OK_TXT'] ?>", undefined, true, true, true);

                    }

                }

                else {

                    show_alert("", dataHtml2.message, "", "", "<?= $langage_lbl['LBL_BTN_OK_TXT'] ?>");

                }

            }

            else {

                console.log(response.result);

            }

        });

    }

</script>

<!-- End: Footer Script -->

</body>

</html>



