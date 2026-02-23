<?php
include_once('common.php');

$script = "Trips";
$tbl_name = 'register_driver';
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = '';
$deafultLang = $_SESSION['sess_lang'];
$dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : ''; 
if ($action != '') {
    $startDate = $_REQUEST['startDate'];
    $endDate = $_REQUEST['endDate'];
      

    if ($startDate != '') {
        $ssql .= " AND Date(t.tTripRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(t.tTripRequestDate) <='" . $endDate . "'";
    }
}

$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = $MODULES_OBJ->isRideFeatureAvailable() ? "Yes" : "No";
$deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable() ? "Yes" : "No";
$deliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable() ? "Yes" : "No";

if($ufxEnable != "Yes") {
    $ssql .= " AND t.eType != 'UberX'";
}
if(!$MODULES_OBJ->isAirFlightModuleAvailable()) {
    $ssql.= " AND t.iFromStationId = '0' AND t.iToStationId = '0'";
}
if($rideEnable != "Yes") {
    $ssql .= " AND t.eType != 'Ride'";
}
if($deliveryEnable != "Yes") {
    $ssql .= " AND t.eType != 'Deliver' AND t.eType != 'Multi-Delivery'";
}

$sql = "SELECT t.*,c.vCurrencyCompany,u.vName, u.vLastName,d.vAvgRating, d.iDriverId, d.vName AS name, d.vLastName AS lname,vt.vVehicleType_" . $deafultLang . " as vVehicleType,vt.vRentalAlias_" . $deafultLang . " as vRentalVehicleTypeName FROM register_driver d RIGHT JOIN trips t ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId LEFT JOIN company as c on c.iCompanyId= d.iCompanyId WHERE d.iCompanyId = '" . $_SESSION['sess_iUserId'] . "'" . $ssql . " AND t.eSystem = 'General' ORDER BY t.iTripId DESC";
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

if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}
if(file_exists($logogpath."canceled-invoice.png")){
 $canceled_icon = $logogpath."canceled-invoice.png";   
}else{
 $canceled_icon = "assets/img/canceled-invoice.png";   
}
// added by sunita
$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_" . $deafultLang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_TRIPS_TXT']; ?></title>

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
    <?php if($template!='taxishark'){?>
    <div id="main-uber-page">
    <?php } ?>
        <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- First Section -->
        <?php include_once("top/header.php");?>
        <!-- End: First Section -->
<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption">
            <div class="page-heading">

                <?php if($APP_TYPE == "Ride-Delivery"){ ?>
                    <h1><?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS_DELIVERIES_TEXT']; ?></h1>
                <?php }else{ ?>
                    <h1><?= $langage_lbl['LBL_COMPANY_TRIP_HEADER_TRIPS_TXT']; ?></h1>
                <?php }?>
            </div>
            
            <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                <input type="hidden" name="action" value="search" />
                <div class="filters-column mobile-full">
                    <label><?= $langage_lbl['LBL_SEARCH_RIDES_POSTED_BY_DATE']; ?></label>
                    <select id="timeSelect" name="dateRange">
                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option>
                                    <option value="today" <?php if($dateRange == 'today'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></option>
                                    <option value="yesterday" <?php if($dateRange == 'yesterday'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></option>
                                    <option value="currentWeek" <?php if($dateRange == 'currentWeek'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></option>
                                    <option value="previousWeek" <?php if($dateRange == 'previousWeek'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></option>
                                    <option value="currentMonth" <?php if($dateRange == 'currentMonth'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></option>
                                    <option value="previousMonth" <?php if($dateRange == 'previousMonth'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous Month']; ?></option>
                                    <option value="currentYear" <?php if($dateRange == 'currentYear'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></option>
                                    <option value="previousYear" <?php if($dateRange == 'previousYear'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></option>

                    </select>
                </div>
                <div class="filters-column mobile-half">
                    <label><?= $langage_lbl['LBL_WALLET_FROM_DATE']; ?></label>
                    <input type="text" id="dp4" name="startDate" placeholder="<?= $langage_lbl['LBL_WALLET_FROM_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                    <i class="icon-cal" id="from-date"></i>
                </div>
                <div class="filters-column mobile-half">
                    <label><?= $langage_lbl['LBL_WALLET_TO_DATE']; ?></label>
                    <input type="text" id="dp5" name="endDate" placeholder="<?= $langage_lbl['LBL_WALLET_TO_DATE']; ?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                    <i class="icon-cal" id="to-date"></i>
                </div>
                <div class="filters-column mobile-full">
                    <button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                    <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                    <a href="company-trip" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>
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
                    <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                        <th><?= $langage_lbl['LBL_TRIP_JOB_TYPE_FRONT']; ?></th>
                    <?php } ?>
                    <th width="17%"><?= $langage_lbl['LBL_MYTRIP_RIDE_NO_TXT']; ?></th>
                    <th><?= $langage_lbl['LBL_LOCATION_FOR_FRONT']; ?></th>
                    <th><?= $langage_lbl['LBL_COMPANY_TRIP_DRIVER']; ?></th>
                    <th><?= $langage_lbl['LBL_COMPANY_TRIP_RIDER']; ?></th>
                    <th><?= $langage_lbl['LBL_COMPANY_TRIP_Trip_Date']; ?></th>
                    <th><?= $langage_lbl['LBL_COMPANY_TRIP_FARE_TXT']; ?></th>
                    <th><?= $langage_lbl['LBL_COMPANY_TRIP_Car_Type']; ?></th>
                    <th><?= $langage_lbl['LBL_COMPANY_TRIP_View_Invoice']; ?></th>
                </tr>
            </thead>
    <tbody>
                                            <?php
                                            for ($i = 0; $i < scount($db_trip); $i++) {
                                                if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                    if(!empty($db_trip[$i]['vRentalVehicleTypeName'])){
                                                        $car = $db_trip[$i]['vRentalVehicleTypeName'];
                                                    } else {
                                                        $car = $db_trip[$i]['vVehicleType'];
                                                    }
                                                } else {
                                                    $car = $db_trip[$i]['vVehicleType'];
                                                }

                                                $viewService = 0;
                                                $seriveJson = "";
                                                //if (isset($db_trip[$i]['tVehicleTypeData']) && $db_trip[$i]['tVehicleTypeData'] != "" && $car == "") {
                                                //    $viewService = 1;
                                                //    $seriveJson = $db_trip[$i]['tVehicleTypeData'];
                                                //}
                                                if (isset($db_trip[$i]['tVehicleTypeFareData']) && $db_trip[$i]['tVehicleTypeFareData'] != "" && $car == "") {
                                                   $viewService = 1;
                                                   $seriveJson = json_decode($db_trip[$i]['tVehicleTypeFareData']);
                                                   $seriveJson = json_encode($seriveJson->FareData);
                                                }

                                                $eType = $db_trip[$i]['eType'];
                                                $link_page = "invoice.php";
                                                if ($eType == 'Ride') {
                                                    $trip_type = $langage_lbl["LBL_RIDE_TXT"];
                                                } else if ($eType == 'UberX') {
                                                    $trip_type = $langage_lbl["LBL_OTHER"];
                                                } else if ($eType == 'Multi-Delivery') {
                                                    $trip_type = $langage_lbl["LBL_MULTI_DELIVERY"] ;
                                                    $link_page = "invoice_multi_delivery.php";
                                                } else {
                                                    $trip_type = $langage_lbl["LBL_DELIVERY_TXT"];
                                                }
                                                if ($eType == 'Multi-Delivery' && $ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY=='Yes') {
                                                    $db_deliveryloc = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE `iTripId` = ".$db_trip[$i]['iTripId']);
                                                    if(scount($db_deliveryloc)==1) {
                                                        $trip_type = $langage_lbl["LBL_DELIVERY_TXT"];
                                                    }
                                                }
                                                $systemTimeZone = date_default_timezone_get();
                                                if ($db_trip[$i]['tTripRequestDate'] != "" && $db_trip[$i]['vTimeZone'] != "") {
                                                    $dBookingDate = converToTz($db_trip[$i]['tTripRequestDate'], trim($db_trip[$i]['vTimeZone']), $systemTimeZone);
                                                } else {
                                                    $dBookingDate = $db_trip[$i]['tTripRequestDate'];
                                                }
                                                /*$date_format_data_array = array(
                                                    'tdate' => $dBookingDate,
                                                    'langCode' => $deafultLang,
                                                    'DateFormatForWeb' => 1
                                                );
                                                $get_booking_date_format = DateformatCls::getNewDateFormat($date_format_data_array);*/
                                                ?>
                                                <tr class="gradeA">
                                                <?php if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') { ?>
                                                        <td data-order="<?= $db_trip[$i]['iTripId'] ?>"><?php
                                                        if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0) {
                                                            echo "Rental " . $trip_type . "<br/> ( Hail )";
                                                        } else if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                            echo "Rental " . $trip_type;
                                                        } else if ($db_trip[$i]['eHailTrip'] == "Yes") {
                                                            echo "Hail " . $trip_type;
                                                        } else {
                                                            echo $trip_type;
                                                        }
                                                        ?></td>
                                                        <?php } ?>
                                                    <td align="center"><?= $db_trip[$i]['vRideNo']; ?></td>
                                                    <?php if ($APP_TYPE == 'UberX' || $db_trip[$i]['eType'] == 'UberX') { ?>
                                                         <?php if ($MODULES_OBJ->isEnableVideoConsultingService() && $db_trip[$i]['isVideoCall'] == 'No') { ?>
                                                         <td width="25%"><div class="lableCombineData"><label><?= $langage_lbl['LBL_JOB_LOCATION_TXT']; ?></label><span><?= $db_trip[$i]['tSaddress']; ?></span></div></td>
                                                     <?php } else {
                                                        echo '<td width="25%">-</td>';
                                                     } ?>
                                                    <?php } else {
                                                        if (!empty($db_trip[$i]['tDaddress'])) {
                                                            ?>
                                                            <td width="25%"><div class="lableCombineData">
                                                                <label><?= $langage_lbl['LBL_Pick_Up']; ?></label><span><?= $db_trip[$i]['tSaddress'] ?> </span>
                                                                <label><?= $langage_lbl['LBL_DROP_AT']; ?></label><span> <?= $db_trip[$i]['tDaddress']; ?></span></div>
                                                            </td>
                                                        <?php } else { ?>
                                                            <td width="25%"><div class="lableCombineData"> <label><?= ($data_drv[$i]['eType']=='UberX') ? $langage_lbl['LBL_JOB_LOCATION_TXT'] : $langage_lbl['LBL_Pick_Up'] ?></label><span><?= $db_trip[$i]['tSaddress']; ?></span></div></td>
                                                        <?php }
                                                    }
                                                    ?> 
                                                    <td>
                                                        <?= clearName($db_trip[$i]['name'] . " " . $db_trip[$i]['lname']); ?>
                                                    </td>
                                                    <td>
                                                        <?= clearName($db_trip[$i]['vName'] . " " . $db_trip[$i]['vLastName']); ?>
                                                    </td>
                                                    <td data-order="<?= $db_trip[$i]['iTripId'] ?>"><?= DateTime($dBookingDate, 26);//date('d-M-Y', strtotime($dBookingDate));//$get_booking_date_format['tDisplayDateTime']; ?></td>
                                                    <td align="center">

                                                        <?php
                                                        $fareAmount =$total_main_price=0;
                                                        if($db_trip[$i]['fTripGenerateFare']){
                                                            $fareAmount += $db_trip[$i]['fTripGenerateFare'];
                                                        }else if($db_trip[$i]['fCancellationFare']){
                                                            $fareAmount += $db_trip[$i]['fCancellationFare'];
                                                        }
                                                        if($fareAmount > 0){
                                                            $total_main_price = ($fareAmount + $db_trip[$i]['fTipPrice']) - ($db_trip[$i]['fCommision'] + $db_trip[$i]['fTax2'] + $db_trip[$i]['fTax1'] + $db_trip[$i]['fOutStandingAmount'] + $db_trip[$i]['fAddedOutstandingamt'] + $db_trip[$i]['fHotelCommision']);
                                                        }
                                                        $tripcurname = $db_trip[$i]['vCurrencyCompany'];
                                                        $total_main_price = trip_currency_payment($total_main_price, $db_trip[$i]['fRatio_' . $tripcurname]);
                                                        ?>
    <?= formateNumAsPerCurrency($total_main_price,$tripcurname); //trip_currency($total_main_price); ?>
                                                    </td>
                                                    <td align="center">
                                                         <?php if ($viewService == 1) { ?>
                                                            <button class="btn btn-success gen-btn" data-trip="<?= $db_trip[$i]['vRideNo']; ?>" data-json='<?= $seriveJson; ?>' onclick="return showServiceModal(this);">
                                                                <i class="fa fa-certificate certi"><b> View Service</b></i>
                                                            </button>
                                                            <?php
                                                            } else {

                                                            echo $car;
                                                            /*if ($db_trip[$i]['iRentalPackageId'] > 0) {
                                                                echo $db_trip[$i]['vRentalVehicleTypeName'];
                                                            } else {
                                                                echo $db_trip[$i]['vVehicleType'];
                                                            }*/
                                                        }
                                                        ?>
                                                        <!-- <?= $db_trip[$i]['vVehicleType']; ?> -->
                                                    </td>
    <?php if ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fTripGenerateFare'] <= 0) {
        if ($db_trip[$i]['iCancelReasonId'] > 0 || $db_trip[$i]['vCancelReason'] != '') {
            ?>
                                                            <td class="center">
                                                                <a href="#" data-toggle="modal" data-target="#uiModal1_<?= $db_trip[$i]['iTripId']; ?>"> <!-- class="btn btn-info"  -->
                                                                    <img src="<?php echo $canceled_icon; ?>" title="<?= $langage_lbl['LBL_MYTRIP_CANCELED_TXT']; ?>">
                                                                </a>
                                                            </td> 
                                                        <?php } else { ?>
                                                            <td class="center">
                                                                <img src="<?php echo $canceled_icon; ?>" title="<?= $langage_lbl['LBL_MYTRIP_CANCELED_TXT']; ?>">
                                                            </td>
        <?php } ?>

                                                <div class="custom-modal-main in fade" role="dialog" id="uiModal1_<?= $db_trip[$i]['iTripId']; ?>" >
                                                    <div class="custom-modal">
                                                    <div class="modal-dialog">
                                                        <!-- Modal content-->
                                                        <div class="modal-content">
                                                            <div class="model-header">
                                                                <h4 class="modal-title"><?= $langage_lbl['LBL_RIDE_TXT'] . " " . $langage_lbl['LBL_CANCEL_REASON']; ?> </h4>
                                                            </div>
                                                            <div class="model-body">
                                                                <ul class="value-listing">
                                                                <li><b><?= $langage_lbl['LBL_CANCEL_REASON']; ?>  </b><span> 
                                                                    <?php
                                                                    if ($db_trip[$i]['iCancelReasonId'] > 0) {
                                                                        $cancelreasonarray = getCancelReason($db_trip[$i]['iCancelReasonId'], $_SESSION['sess_lang']);
                                                                        $db_trip[$i]['vCancelReason'] = $cancelreasonarray['vCancelReason'];
                                                                    } else {
                                                                        $db_trip[$i]['vCancelReason'] = $db_trip[$i]['vCancelReason'];
                                                                    }
                                                                    if(empty($db_trip[$i]['eCancelledBy'])) {
                                                                      $db_trip[$i]['eCancelledBy'] = $langage_lbl['LBL_ADMIN'];
                                                                    }
                                                                    ?> 
                                                                    <?= stripcslashes($db_trip[$i]['vCancelReason']); ?></span></li>
                                                                <li><b><?= $langage_lbl['LBL_CANCEL_BY']; ?> </b><span> <?= stripcslashes($db_trip[$i]['eCancelledBy']); ?></span></li>
                                                            </ul>
                                                            </div>
                                                            <div class="model-footer">
                                                                <button type="button" class="gen-btn" data-dismiss="modal"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                </div>
    <?php } else if (($db_trip[$i]['iActive'] == 'Finished' && $db_trip[$i]['eCancelled'] == "Yes") || ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fTripGenerateFare'] > 0)) { ?>
                                                <td align="center" width="10%">
                                                    <?php if($APP_TYPE == "UberX"){ ?>
                                                        <a target = "_blank" href="<?= $link_page ?>?iJobId=<?= base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>">
                                                    <?php } else { ?>
                                                        <a target = "_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>">    
                                                    <?php } ?>
                                                        <img alt="" src="<?php echo $invoice_icon; ?>">
                                                    </a>
                                                    <div style="font-size: 12px;">Cancelled</div>
                                                </td>
    <?php } else if ($db_trip[$i]['iActive'] == 'Active' || $db_trip[$i]['iActive'] == 'On Going Trip') { ?>
                                                <td class="center">
        <?= $langage_lbl['LBL_ON_RIDES_ADMIN']; ?>
                                                </td>
                                            <?php } else { ?>  
                                                <td align="center" width="10%">
                                                    <?php if($APP_TYPE == "UberX"){ ?>
                                                        <a target = "_blank" href="<?= $link_page ?>?iJobId=<?= base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>">
                                                    <?php } else { ?>
                                                        <a target = "_blank" href="<?= $link_page ?>?iTripId=<?= base64_encode(base64_encode($db_trip[$i]['iTripId'])) ?>">
                                                    <?php } ?>
                                                    <img alt="" src="<?php echo $invoice_icon; ?>">
                                                    </a>
                                                </td>
    <?php } ?>
                                            </tr>
                        <?php } ?>     
                                        </tbody>
        </table>
    </div>
    </div>
</section>
    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>

    <div style="clear:both;"></div>
     <?php if($template!='taxishark'){?>
     </div>
     <?php } ?>
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');?>
<div  class="custom-modal-main" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="custom-modal">
        <div class="modal-dialog" >
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

<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


<script type="text/javascript">
      if($('#my-trips-data').length > 0) {
        $('#my-trips-data').DataTable({"order": [[4, "desc"]],"oLanguage": langData});
    }
    



    $(document).on('change','#timeSelect',function(e){
        e.preventDefault();
        
        var timeSelect = $(this).val();
        
        if(timeSelect == 'today'){ todayDate('dp4', 'dp5') }
        if(timeSelect == 'yesterday'){yesterdayDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentWeek'){currentweekDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousWeek'){previousweekDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentMonth'){currentmonthDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousMonth'){previousmonthDate('dFDate', 'dTDate')}
        if(timeSelect == 'currentYear'){currentyearDate('dFDate', 'dTDate')}
        if(timeSelect == 'previousYear'){previousyearDate('dFDate', 'dTDate')}

    });




</script>

        <script type="text/javascript">
            var typeArr = '<?= $obj->SqlEscapeString(json_encode($vehilceTypeArr)); ?>';
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

                var startDate = '<?= !empty($startDate) ? $startDate : '' ?>';
                var endDate = '<?= !empty($endDate) ? $endDate : '' ?>';
                if (startDate != '') {
                    $("#dp4").val(startDate);
                    $("#dp4").datepicker('refresh');
                }
                if (endDate != '') {
                    $("#dp5").val(endDate);
                    $("#dp5").datepicker('refresh');
                }
            <?php if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Delivery') { ?>
                    $('#dataTables-example').DataTable({
                     "oLanguage": langData,
                        "order": [[4, "desc"]]
                    });
            <?php } else { ?>
                    $('#dataTables-example').DataTable({
                     "oLanguage": langData,
                        "order": [[5, "desc"]]
                    });
            <?php } ?>
                //$('#dataTables-example').dataTable();
                // formInit();
            });
            function reset() {
                location.reload();
            }
            function todayDate()
            {
                $("#dp4").val('<?= $Today; ?>');
                $("#dp5").val('<?= $Today; ?>');
            }
            function yesterdayDate()
            {
                $("#dp4").val('<?= $Yesterday; ?>');
                $("#dp5").val('<?= $Yesterday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentweekDate(dt, df)
            {
                $("#dp4").val('<?= $monday; ?>');
                $("#dp5").val('<?= $sunday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousweekDate(dt, df)
            {
                $("#dp4").val('<?= $Pmonday; ?>');
                $("#dp5").val('<?= $Psunday; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentmonthDate(dt, df)
            {
                $("#dp4").val('<?= $currmonthFDate; ?>');
                $("#dp5").val('<?= $currmonthTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousmonthDate(dt, df)
            {
                $("#dp4").val('<?= $prevmonthFDate; ?>');
                $("#dp5").val('<?= $prevmonthTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function currentyearDate(dt, df)
            {
                $("#dp4").val('<?= $curryearFDate; ?>');
                $("#dp5").val('<?= $curryearTDate; ?>');
                $("#dp4").datepicker('refresh');
                $("#dp5").datepicker('refresh');
            }
            function previousyearDate(dt, df)
            {
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
             function showServiceModal(elem) {
                var tripJson = JSON.parse($(elem).attr("data-json"));
                var rideNo = $(elem).attr("data-trip");
                var typeNameArr = JSON.parse(typeArr)
                var serviceHtml = "";
                var srno = 1;
                // added by sunita
                for (var g = 0; g < tripJson.length; g++) {
                    //serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl['LBL_QTY_TXT']?>: <b>"+ [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
                    serviceHtml += "<p>" + srno + ") " + typeNameArr[tripJson[g]['id']] + "&nbsp;&nbsp;&nbsp;&nbsp;  ";
                    if(tripJson[g]['eAllowQty']=='Yes') {
                      serviceHtml += "<?=$langage_lbl['LBL_QTY_TXT']?>: <b>"+ [tripJson[g]['qty']] + "</b>";
                    }
                    serviceHtml += "</p>";
                    srno++;
                }
                $("#service_detail").html(serviceHtml);
                $("#servicetitle").text("Service Details : " + rideNo);
                //$("#service_modal").modal('show');
                $("#service_modal").addClass('active');
                return false;
            }
        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                $("[name='dataTables-example_length']").each(function () {
                    $(this).wrap("<em class='select-wrapper'></em>");
                    $(this).after("<em class='holder'></em>");
                });
                $("[name='dataTables-example_length']").change(function () {
                    var selectedOption = $(this).find(":selected").text();
                    $(this).next(".holder").text(selectedOption);
                }).trigger('change');
            })
        </script>

<!-- End: Footer Script -->
</body>
</html>
