<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'driver,company,rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//setRole($abc, $url);
$currency = '';
if(isset($_SESSION['sess_currency']) && !empty($_SESSION['sess_currency'])){
    $currency = $_SESSION['sess_currency'];
}

if($_SESSION['sess_user'] == "driver")
{
    $Settlement_log_sql = " AND iDriverId = '".$_SESSION['sess_iUserId']."' ";
} else if($_SESSION['SessionUserType'] == "hotel")
{
    $iAdminId = $_SESSION['sess_iAdminUserId'];
    $sql = "SELECT A.iAdminId,H.iHotelId,CONCAT(vFirstName, ' ', vLastName) AS 'Name',vEmail FROM hotel H JOIN administrators A ON H.iAdminId = A.iAdminId WHERE A.iAdminId =  {$iAdminId}";
    $relatedData = $obj->MySQLSelect($sql);
    $Settlement_log_sql = " AND iHotelId = '".$relatedData[0]['iHotelId']."' ";
}
else{
    $Settlement_log_sql = " AND iCompanyId = '".$_SESSION['sess_iCompanyId']."' ";
}


$type = "DayWise";
$iSettlementId = '';
if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    $iSettlementId = $_REQUEST['id'];
} else {
    exit;
}
$get_data = $obj->MySQLSelect("SELECT iHotelId,iSettlementId, iDriverId, iTripId, dDate, iAdminId, eType, iUniqueId, iCompanyId, iOrderId, iBiddingPostId, iUserId, iBookingId FROM `trips_driver_settlement_log` WHERE iUniqueId = " . $iSettlementId . $Settlement_log_sql ." " );

function biddingWiseCal($BiddingData){

    $fBiddingAmount = $BiddingData['fBiddingAmount'];
    $fCommission_percentage = $BiddingData['fCommission'];
    $BiddingData['fCommissionAmount'] = $fCommission_percentage = ($fBiddingAmount * $fCommission_percentage) / 100;
    $BiddingData['driverPayment'] = $BiddingData['takeFromDriver'] =  $BiddingData['payToDriver'] = 0;
    if ($BiddingData['ePaymentOption'] == 'Cash') {
        $BiddingData['takeFromDriver'] = $fCommission_percentage;
        $BiddingData['driverPayment'] = ($fBiddingAmount - $fCommission_percentage) - $fBiddingAmount;
    } else if ($BiddingData['ePaymentOption'] == 'Card') {
        $BiddingData['driverPayment'] = $BiddingData['payToDriver'] += ($fBiddingAmount - $fCommission_percentage);
    } else if ($BiddingData['ePaymentOption'] == 'Wallet') {
        $BiddingData['driverPayment'] = $BiddingData['payToDriver'] += ($fBiddingAmount - $fCommission_percentage);
    }
    return $BiddingData;
}
function calculateTripPayments($trips) {
    $totalCashFareCollected = 0;

    foreach ($trips as &$trip) {
        $originalFare = $trip['iFare']; // Clearer than `iFareOrg`
        $currentFare = $originalFare;
        $totalTax = $trip['fTax1'] + $trip['fTax2'];
        $generatedFare = $trip['fTripGenerateFare'];
        $commission = $trip['fCommision'];
        $discount = $trip['fDiscount'];
        $walletDebit = $trip['fWalletDebit'];
        $outstandingAmount = $trip['fOutStandingAmount'];
        $hotelCommission = $trip['fHotelCommision'];
        $tipAmount = $trip['fTipPrice'];

        // Site earnings calculation
        $siteEarnings = $commission + $totalTax + $outstandingAmount + $hotelCommission;

        // Driver payment calculation based on payment mode and status
        $driverPayment = $displayedDriverPayment = number_format($generatedFare + $tipAmount - $siteEarnings, 2);

        if ($trip['vTripPaymentMode'] === "Cash") {

            $driverPayment = $displayedDriverPayment = number_format($driverPayment - $currentFare, 2);
            $totalCashFareCollected += $currentFare;
        } else if ($trip['isActive'] === "Canceled" && ($trip['vTripPaymentMode'] === "Cash" || $trip['vTripPaymentMode'] === "Organization")) {
            $currentFare = 0;
            $driverPayment = $displayedDriverPayment = number_format($originalFare + $walletDebit - $siteEarnings, 2);
        }

        $trip['driverPayment'] = $displayedDriverPayment;
    }

    return [$trips, $totalCashFareCollected];
}

function calculateRestaurantPayment($order)
{
    $taxAndPacking = $order['fSubTotal'] + $order['fPackingCharge'] + $order['fTax'];
    $commissionAndDiscount = $order['fCommision'] + $order['fOffersDiscount'];

    if ($order['iStatusCode'] < 7) {
        $restaurantPayment = $taxAndPacking - $commissionAndDiscount;
        if (strtolower($order['eOrderplaced_by']) === 'kiosk') {
            $restaurantPayment = -cleanNumber($order['fCommision']);
        }
    } else {
        $restaurantPayment = $order['fRestaurantPaidAmount'];
    }
    return $restaurantPayment;
}
function getDriverPaymentForStoreDelivery($order, $order_buy_anything)
{

    $driverEarning = $order['fDeliveryCharge'];
    $set_unsetarray[] = $order['eDriverPaymentStatus'];
    // Combine conditions for driver earning calculation
    if ($order['iStatusCode'] === 7 || $order['iStatusCode'] === 8) {
        $driverEarning = $order['fDriverPaidAmount'];
    } else {
        $driverEarning += $order['fTipAmount'];
        $subtotal = 0;
        if ($order['ePaymentOption'] === "Card" && scount($order_buy_anything)) {
            /*$subtotal = array_sum(
                array_column(
                    array_filter($order_buy_anything, function ($item) { return $item['eConfirm'] === "Yes"; }),
                    'fItemPrice'
                )
            );*/
        }
        $driverEarning += $subtotal;
    }
    return $driverEarning;
}

function sumByKey($arr, $key) {
    $sum = 0;
    foreach ($arr as $subArr) {
        if (isset($subArr[$key])) {
            $sum += floatval($subArr[$key]);
        }
    }
    return $sum;
}

$TRIP_TABLE_ARRAY = [];

/*------------------Service Wise Group -----------------*/


if(isset($get_data) && !empty($get_data)){

    $TRIPS_DRIVER_SETTLEMENT_LOG = [];
    foreach ($get_data as $data) {
        if ($data['eType'] == "DeliverAll" && $data['iTripId'] > 0) {
            $TRIPS_DRIVER_SETTLEMENT_LOG['orderDelivery'][] = $data;
        } else if ($data['eType'] == "Ride" && $data['iHotelId'] > 0) {
            $TRIPS_DRIVER_SETTLEMENT_LOG['RideHotel'][] = $data;
        } else {
            $TRIPS_DRIVER_SETTLEMENT_LOG[$data['eType']][] = $data;
        }
    }
    foreach($TRIPS_DRIVER_SETTLEMENT_LOG as $key => $SETTLEMENT_LOG)
    {
        if($key == "ServiceBid")
        {
            $ssql = ' AND bp.eStatus = "Completed" ';
            $iBiddingPostIdIn = array_column($SETTLEMENT_LOG, "iBiddingPostId");

            $sql = "SELECT 
                    bp.fOutStandingAmount,bp.iBiddingPostId,bp.eDriverPaymentStatus,bp.vTaskStatus,bp.ePaymentOption, bp.dBiddingDate,bp.fCommission,bp.fBiddingAmount,bp.iBiddingPostId,bp.vBiddingPostNo,  CONCAT(ru.vName ,' ',ru.vLastName)  AS user_name,
                    rd.iDriverId,ru.iUserId,
                    CONCAT(rd.vName ,' ',rd.vLastName)  AS driver_name
                    FROM bidding_post AS bp 
                    JOIN register_user AS ru ON bp.iUserId = ru.iUserId
                    LEFT JOIN register_driver AS rd ON bp.iDriverId=rd.iDriverId WHERE  bp.iBiddingPostId IN (" . implode(', ', $iBiddingPostIdIn) . ") $ssql";
            $bidding_post_data = $obj->MySQLSelect($sql);

            $BiddingPostData = [];
            foreach ($bidding_post_data as $BiddingData) {
                $BiddingData = biddingWiseCal($BiddingData);
                $BiddingPostData[$BiddingData['iBiddingPostId']] = $BiddingData;
            }


            if (isset($BiddingPostData) && !empty($BiddingPostData)) {
                $iBiddingPostIds = array_column($BiddingPostData, "iBiddingPostId");
                $iBiddingPostId = implode(',', $iBiddingPostIds);

                $query = "SELECT amount,iBiddingPostId FROM bidding_offer WHERE `eStatus` = 'Accepted' AND iBiddingPostId IN (" . $iBiddingPostId . ") ORDER BY `IOfferId`";
                $bidding_final_offer = $obj->MySQLSelect($query);
                if (isset($bidding_final_offer) && !empty($bidding_final_offer)) {
                    foreach ($bidding_final_offer as $offer) {
                        $BiddingPostData[$offer['iBiddingPostId']]['fBiddingAmount'] = $offer['amount'];
                        $BiddingData = biddingWiseCal($BiddingPostData[$offer['iBiddingPostId']]);
                        $BiddingPostData[$offer['iBiddingPostId']] = $BiddingData;
                    }
                }
            }

           /* echo "<pre>";
            print_r($BiddingPostData);
            exit;*/

            if(isset($BiddingPostData) && !empty($BiddingPostData))
            {
                foreach ($BiddingPostData as $biddingData) {
                    $TRIP_ARRAY['TYPE'] = "Bidding";
                    $TRIP_ARRAY['RIDE_NO'] = $biddingData['vBiddingPostNo'];
                    $TRIP_ARRAY['DATE'] = $biddingData['dBiddingDate'];
                    $TRIP_ARRAY['STATUS'] = $biddingData['vTaskStatus'];
                    $TRIP_ARRAY['DRIVER_PAY'] = $biddingData['driverPayment'];
                    $TRIP_ARRAY['PAYMENT_MODE'] = $biddingData['ePaymentOption'];
                    $TRIP_TABLE_ARRAY[] = $TRIP_ARRAY;
                }
            }
        }
        elseif ($key == "Ride"){
            $iTripId = array_column($SETTLEMENT_LOG, "iTripId");
            $iTripIds = implode(',', $iTripId);

            $sqlWhere = " AND tr.iTripId IN ($iTripIds)  ";
            $sql1 ="SELECT tr.fCancellationFare,
                  tr.iFare,
                  tr.vRideNo,
                  tr.fTax1,
                  tr.fTax2,
                  tr.iOrganizationId,
                  tr.iTripId,
                  tr.fHotelCommision,
                  tr.fTripGenerateFare,
                  tr.fCommision,
                  tr.fDiscount,
                  tr.fWalletDebit,
                  tr.fTipPrice,
                  tr.vTripPaymentMode,
                  tr.iActive,
                  tr.fOutStandingAmount,
                  tr.tTripRequestDate
                FROM
                  trips AS tr 
                  LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId
                  LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId
                  LEFT JOIN company AS c ON rd.iCompanyId = c.iCompanyId
                WHERE
                  (
                    tr.iActive = 'Finished'
                    OR (
                      tr.iActive = 'Canceled'
                      AND tr.iFare > 0
                    )
                    OR (
                      tr.iActive = 'Canceled'
                      AND tr.fWalletDebit > 0
                      AND tr.iFare = 0
                    )
                  ) $sqlWhere" ;
            $totaltrips = $obj->MySQLSelect($sql1);

            list($totaltrips, $totalCashFare) = calculateTripPayments($totaltrips);

            if(isset($totaltrips) && !empty($totaltrips)){

                foreach ($totaltrips as $trips) {

                    /*echo $trips['iTripId']." : " .$trips['driverPayment'];
                    echo "";
                    echo "<br>";
                    echo "";*/


                    $TRIP_ARRAY['TYPE'] = "Ride";
                    $TRIP_ARRAY['RIDE_NO'] = $trips['vRideNo'];
                    $TRIP_ARRAY['DATE'] = $trips['tTripRequestDate'];
                    $TRIP_ARRAY['STATUS'] = $trips['iActive'];
                    $TRIP_ARRAY['DRIVER_PAY'] = $trips['driverPayment'];
                    $TRIP_ARRAY['PAYMENT_MODE'] = $trips['vTripPaymentMode'];
                    $TRIP_TABLE_ARRAY[] = $TRIP_ARRAY;
                }
            }

           /*exit;*/
        }
        elseif($key == "DeliverAll")
        {
            $iOrderIds = implode(',', array_column($SETTLEMENT_LOG, "iOrderId"));
            $ssql = " AND o.iOrderId IN ($iOrderIds)";

            $sql = "SELECT o.iOrderId,o.vOrderNo,o.fSubTotal,o.fTax,o.fPackingCharge,o.iCompanyId,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fOutStandingAmount,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.iStatusCode,os.vStatus,o.eOrderplaced_by,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, o.fTipAmount FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND (o.iStatusCode = '6') $ssql";

            $db_order = $obj->MySQLSelect($sql);

            $updated_db_order = [];
            foreach ($db_order as &$order)
            {
                $totalFare = $order['fTotalGenerateFare'];
                $order['restaurantPayment'] =  calculateRestaurantPayment($order);
               /* $updated_db_order[] = array_merge($order, ['restaurantPayment' => $restaurantPayment]);*/
            }

            if(isset($db_order) && !empty($db_order)){
                foreach ($db_order as $order) {
                    $TRIP_ARRAY['TYPE'] = "DeliverAll";
                    $TRIP_ARRAY['RIDE_NO'] = $order['vOrderNo'];
                    $TRIP_ARRAY['DATE'] = $order['tOrderRequestDate'];
                    $TRIP_ARRAY['STATUS'] = $order['vStatus'];
                    $TRIP_ARRAY['DRIVER_PAY'] = $order['restaurantPayment'];
                    $TRIP_ARRAY['PAYMENT_MODE'] = $order['ePaymentOption'];
                    $TRIP_TABLE_ARRAY[] = $TRIP_ARRAY;
                }
            }
        }
        elseif ($key == "orderDelivery")
        {
            $iTripId = array_column($SETTLEMENT_LOG, "iTripId");
            $iTripIds = implode(',', $iTripId);

            $sqlWhere = " AND tr.iTripId IN ($iTripIds)  ";
            $ssql = '';


            /*------------------order delivery()-----------------*/
            $sql = "SELECT tr.iTripId,o.tOrderRequestDate,o.iOrderId,o.vOrderNo,o.iCompanyId,o.iDriverId,o.fDriverPaidAmount,o.iStatusCode,o.iUserId,tr.fDeliveryCharge, tr.eDriverPaymentStatus,tr.vTripPaymentMode,os.vStatus, odcd.fDeliveryCharge as fCustomDeliveryCharge,vt.fDeliveryCharge as fDeliveryChargeVehicle, o.fTipAmount,o.eBuyAnyService,o.ePaymentOption, o.eForPickDropGenie FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN orders as o on o.iOrderId=tr.iOrderId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid LEFT JOIN order_delivery_charge_details as odcd ON odcd.iOrderId = tr.iOrderId LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId = tr.iVehicleTypeId  WHERE 1 = 1 AND o.iStatusCode = 6 AND tr.eSystem = 'Deliverall' AND tr.iActive != 'Canceled' $ssql $sqlWhere GROUP by tr.iTripId";


            $db_order = $obj->MySQLSelect($sql);

            /*------------------order delivery-----------------*/

            /*-----------------------------------*/
            $iOrderId = array_column($db_order,"iOrderId");
            $iOrderId = implode(',', $iOrderId);

            $order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId IN ('" . $iOrderId . "') ");


            /*-----------------------------------*/
            foreach ($db_order as &$order){
                $order['driverPayment'] = getDriverPaymentForStoreDelivery($order, $order_buy_anything);
            }
            if(isset($db_order) && !empty($db_order)){
                foreach ($db_order as $O) {
                    $TRIP_ARRAY['TYPE'] = "DeliverAll";
                    $TRIP_ARRAY['RIDE_NO'] = $O['vOrderNo'];
                    $TRIP_ARRAY['DATE'] = $O['tOrderRequestDate'];
                    $TRIP_ARRAY['STATUS'] = $O['vStatus'];
                    $TRIP_ARRAY['DRIVER_PAY'] = $O['driverPayment'];
                    $TRIP_ARRAY['PAYMENT_MODE'] = $O['ePaymentOption'];
                    $TRIP_TABLE_ARRAY[] = $TRIP_ARRAY;
                }
            }
        }

        elseif ($key == "RideShare")
        {

            $iBookingId = implode(',', array_column($SETTLEMENT_LOG, "iBookingId"));
            $ssql = " AND rsb.iBookingId IN ($iBookingId)";
            $ssql .= "AND  (rsb.eStatus = 'Approved' OR (rsb.eStatus = 'Cancelled' AND rsb.eCommissionDeduct = 'Yes')  )";

            $sql = "SELECT  rsb.fTax1,rsb.fTax2,rsb.fTax1Percentage,rsb.fTax2Percentage, CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name,  CONCAT(riderUser.vName,' ',riderUser.vLastName) AS  rider_Name,  riderUser.vImgName as rider_ProfileImg, riderUser.iUserId as rider_iUserId, riderDriver.iUserId as driver_iUserId,  pr.vPublishedRideNo,pr.tStartLocation,pr.tStartLat,pr.tStartLong,pr.tEndLocation,pr.tEndLat,pr.tEndLong,pr.dStartDate,pr.dStartDate,pr.dEndDate,pr.tPriceRatio,  pr.tEndCity,pr.tStartCity,rsb.vBookingNo,rsb.dBookingDate,rsb.fTax1,rsb.fTax2,rsb.fTax1Percentage,rsb.fTax2Percentage,  rsb.iPublishedRideId,rsb.eStatus,rsb.fTotal,rsb.iBookedSeats,pr.tDriverDetails,rsb.iBookingId,rsb.iCancelReasonId,rsb.tCancelReason, rsb.iBookingId , rsb.fBookingFee, rsb.ePaymentOption, rsb.ePaymentStatus
                FROM ride_share_bookings rsb 
                JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId)
                JOIN register_user riderUser  ON (riderUser.iUserId = rsb.iUserId)
                JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)  WHERE 1=1   $ssql";
            $data_drv = $obj->MySQLSelect($sql);


            foreach ($data_drv as &$data) {
                $data['DriverPayment'] = $driverAmount = $data['fTotal'] - ($data['fBookingFee'] + $data['fTax1'] + $data['fTax2']);
                if ($data['ePaymentOption'] == "Cash") {
                    $driverAmount = $driverAmount - $data['fTotal'];
                    $data['DriverPayment'] = $driverAmount;
                }
            }

            if(isset($data_drv) && !empty($data_drv)){
                foreach ($data_drv as $rideShareData) {
                    $TRIP_ARRAY['TYPE'] = "RideShare";
                    $TRIP_ARRAY['RIDE_NO'] = $rideShareData['vBookingNo'];
                    $TRIP_ARRAY['DATE'] = $rideShareData['dBookingDate'];
                    $TRIP_ARRAY['STATUS'] = $rideShareData['eStatus'];
                    $TRIP_ARRAY['DRIVER_PAY'] = $rideShareData['DriverPayment'];
                    $TRIP_ARRAY['PAYMENT_MODE'] = $rideShareData['ePaymentOption'];
                    $TRIP_TABLE_ARRAY[] = $TRIP_ARRAY;
                }
            }
        }
        elseif ($key == "RideHotel")
        {
            $iTripId = array_column($SETTLEMENT_LOG, "iTripId");
            $iTripIds = implode(',', $iTripId);

            $sqlWhere = " AND tr.iTripId IN ($iTripIds)  ";
            $sql1 ="SELECT 
                    tr.fCancellationFare,
                  tr.iFare,
                  tr.vRideNo,
                  tr.fTax1,
                  tr.fTax2,
                  tr.iOrganizationId,
                  tr.iTripId,
                  tr.fHotelCommision,
                  tr.fTripGenerateFare,
                  tr.fCommision,
                  tr.fDiscount,
                  tr.fWalletDebit,
                  tr.fTipPrice,
                  tr.vTripPaymentMode,
                  tr.iActive,
                  tr.fOutStandingAmount,
                  tr.tTripRequestDate
                FROM
                  trips AS tr 
                  LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId
                  LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId
                  LEFT JOIN company AS c ON rd.iCompanyId = c.iCompanyId
                WHERE
                  (
                    tr.iActive = 'Finished'
                    OR (
                      tr.iActive = 'Canceled'
                      AND tr.iFare > 0
                    )
                    OR (
                      tr.iActive = 'Canceled'
                      AND tr.fWalletDebit > 0
                      AND tr.iFare = 0
                    )
                  ) $sqlWhere" ;
            $totaltrips = $obj->MySQLSelect($sql1);
            if(isset($totaltrips) && !empty($totaltrips))
            {
                foreach ($totaltrips as $trips) {

                    $TRIP_ARRAY['TYPE'] = "Ride";
                    $TRIP_ARRAY['RIDE_NO'] = $trips['vRideNo'];
                    $TRIP_ARRAY['DATE'] = $trips['tTripRequestDate'];
                    $TRIP_ARRAY['STATUS'] = $trips['iActive'];
                    $TRIP_ARRAY['DRIVER_PAY'] = $trips['fHotelCommision'];
                    $TRIP_ARRAY['PAYMENT_MODE'] = $trips['vTripPaymentMode'];
                    $TRIP_TABLE_ARRAY[] = $TRIP_ARRAY;
                }
            }

        }



    }
}

/*------------------Service Wise Group -----------------*/

$SUM_OF_DRIVER_PAY = sumByKey($TRIP_TABLE_ARRAY, 'DRIVER_PAY');

?>

<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?= $SITE_NAME ?></title>-->
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_RIDE_SHARE_PUBLISHED_RIDES_TXT']; ?></title>
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
                    <h1>Payment Report(settlement)</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="table-holder">
                <table id="my-trips-data" class="ui celled table custom-table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="10%">Type</th>
                        <th width="10%">Ride/Job No	</th>
                        <th width="10%">Trip/Job Date </th>
                        <th width="10%">Ride/Job Status</th>
                        <th width="10%">Payment Mode</th>

                        <th width="20%">Provider pay</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (isset($TRIP_TABLE_ARRAY) && !empty($TRIP_TABLE_ARRAY)) {
                        foreach ($TRIP_TABLE_ARRAY as $ride) { ?>
                            <tr>
                                <td><?php echo $ride['TYPE']; ?></td>
                                <td><?php echo $ride['RIDE_NO']; ?></td>
                                <td>
                                    <?= DateTime($ride['DATE'], '7'); ?>
                                </td>
                                <td><?php echo $ride['STATUS']; ?></td>
                                <td><?php echo $ride['PAYMENT_MODE']; ?></td>
                                <td><?php echo formateNumAsPerCurrency($ride['DRIVER_PAY'],$currency); ?></td>
                            </tr>
                        <?php } ?>

                    <?php }
                    ?>
                    </tbody>

                    <tfoot>
                    <tr>
                        <td>Total Settlement Amount:</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo formateNumAsPerCurrency($SUM_OF_DRIVER_PAY,$currency); ?></td>
                    </tr>
                    </tfoot>
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
        $('#my-trips-data').dataTable({
            "ordering": false
        });
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
