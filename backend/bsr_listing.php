<?php
include_once('common.php');

$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
$script = "bsrListing".$eMasterType;

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$iUserId = $_SESSION['sess_iUserId'];
$lang = $_SESSION['sess_lang'];

$RENTITEM_OBJ->WebCommonParam();
$getAll_DATA = $RENTITEM_OBJ->getRentItemPostFinal("Web","",$iUserId,$lang,"","",$eMasterType);

$startDate = $_REQUEST['startDate'];
$endDate = $_REQUEST['endDate'];
$dateRange = $_REQUEST['dateRange'];
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
if($eMasterType == "RentItem"){            
    $pagetitle = $langage_lbl['LBL_RENT_ALL_GENERALITEMS_TXT'];
} else if($eMasterType == "RentEstate") { 
    $pagetitle = $langage_lbl['LBL_RENT_ALL_REALESTATE_TXT'];
} else if($eMasterType == "RentCars") { 
    $pagetitle = $langage_lbl['LBL_RENT_ALL_CARS_TXT'];
} 
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">

    <title><?= $SITE_NAME ?> | <?= $pagetitle; ?></title>
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
                    <h1><?= $pagetitle; ?></h1>
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
                        <a href="bsr_listing.php?eType=<?php echo $_REQUEST['eType'];?>" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>

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
                        <th width="6%"><?= $langage_lbl['LBL_RENT_POST_NUMBER']; ?></th>
                        <th width="8%"><?= $langage_lbl['LBL_RENT_LISTING_TYPE']; ?></th>
                        <th width="10%"><?= $langage_lbl['LBL_RENT_CATEGORY']; ?></th>
                        <th width="10%"><?= $langage_lbl['LBL_RENT_PAYMENT_PLAN']; ?></th>
                        <th width="10%"><?= $langage_lbl['LBL_RENT_DATE_POSTED']; ?></th>
                        <th width="10%"><?= $langage_lbl['LBL_RENT_APPROVED_AT']; ?></th>
                        <th width="14%"><?= $langage_lbl['LBL_RENT_RENEWAL_DATE']; ?></th>
                        <th width="10%"><?= $langage_lbl['LBL_RENT_STATUS']; ?></th>
                        <?php if($eMasterType == "RentItem"){ ?>             
                            <th width="7%"> <?= $langage_lbl['LBL_RENT_ITEM_DETAILS']; ?></th>
                        <?php } else if($eMasterType == "RentEstate") { ?>
                            <th width="7%"> <?= $langage_lbl['LBL_RENT_PROPERTY_DETAIL']; ?></th>
                        <?php } else if($eMasterType == "RentCars") { ?>
                            <th width="7%"> <?= $langage_lbl['LBL_RENT_CAR_DETAILS']; ?></th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($getAll_DATA) && !empty($getAll_DATA)) {
                        foreach ($getAll_DATA as $postdata) { ?>
                            <tr>
                                <td><?php echo $postdata['vRentItemPostNoMail']; ?> </td>
                                <td><?php echo $postdata['eListingTypeWeb']; ?> </td>
                                <td><?php echo $postdata['vCatName']; ?></td>
                                <td><?php echo $postdata['RentItemPlanData']['vPlanName'];?> </td>
                                <td><?php echo DateTime($postdata['dRentItemPostDate']);?></td>
                                <td><?php echo  DateTime($postdata['dApprovedDate']);?></td>
                                <td><?php echo  DateTime($postdata['dRenewDate']); 
                                $dRenewDate = strtotime($postdata['dRenewDate']);
                                $dApprovedDate = strtotime(date('Y-m-d H:i:s'));
                                $datediff = $dRenewDate - $dApprovedDate;
                                if($postdata['eStatus'] == "Approved" && $datediff > 0){
                                    echo "(".round($datediff / (60 * 60 * 24)) ." days left)";
                                } ?> </td>
                                <td><?php echo $postdata['eStatus'];?></td>
                                <td><a class="btn btn-success btn-xs" href="cx-item-details.php?iItemPostId=<?php echo base64_encode(base64_encode($postdata['iRentItemPostId']))?>" target="_blank"><button class="gen-btn" style="font-size: 15px;padding: 10px;">View</button></a></td>
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

        console.log(timeSelect);

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