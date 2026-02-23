<?php

include_once('common.php');

$script="Order";
//$tbl_name 	= 'register_driver';
$AUTH_OBJ->checkMemberAuthentication();
 $abc = 'driver';
 $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 setRole($abc,$url);
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$ssql='';
$dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : '';


$DELIVERY_DATE_SQL = '';
if($action!='')
{
	$startDate=$_REQUEST['startDate'];
	$endDate=$_REQUEST['endDate'];

	/*if($startDate!=''){
		$ssql.=" AND Date(ord.tOrderRequestDate) >='".$startDate."'";
	}
	if($endDate!=''){
		$ssql.=" AND Date(ord.tOrderRequestDate) <='".$endDate."'";
	}*/

    if ($_SESSION['sess_user'] == "driver"){
        if ($startDate != ''){
            $ssql .= " AND Date(t.tEndDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(t.tEndDate) <='".$endDate."'";
        }


    }else{
        if ($startDate != ''){
            $ssql .= " AND Date(ord.tOrderRequestDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(ord.tOrderRequestDate) <='".$endDate."'";
        }
    }
}

$sess_user = "user";
if($_SESSION['sess_user'] == "driver"){
	$sess_user = "driver";

    $DELIVERY_DATE_SQL = ' AND t.tEndDate != "0000-00-00 00:00:00" ';
}
if ($_SESSION['sess_user'] == "driver") {
    $sql = "SELECT * FROM register_" . $sess_user . " WHERE iDriverId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyDriver'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
} else {
    $sql = "SELECT * FROM register_" . $sess_user . " WHERE iUserId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);
    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyPassenger'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
}
$currencyName = $db_curr_ratio[0]['vName'];

if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
	  $vLang = $default_lang;
}
$sql = "SELECT t.tEndDate as DeliveryDate,ord.iOrderId,ord.vOrderNo,ord.vTimeZone,ord.tOrderRequestDate,ord.fDriverPaidAmount,ord.iStatusCode,cmp.vCompany,ordst.vStatus,t.fDeliveryCharge,ord.fTipAmount,vt.fDeliveryCharge as fDeliveryChargeVehicle,ord.eBuyAnyService,ord.ePaymentOption From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode JOIN trips as t ON (t.iOrderId=ord.iOrderId AND t.iDriverId=ord.iDriverId  ) LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId = t.iVehicleTypeId WHERE ord.iDriverId = '".$_SESSION['sess_iUserId']."' AND IF(ord.eTakeaway = 'Yes' && ordst.iStatusCode = 6, ordst.eTakeaway='Yes', ordst.eTakeaway != 'Yes') AND IF(ord.eBuyAnyService = 'Yes' && ordst.iStatusCode IN (1,4,13,14), ordst.eBuyAnyService = 'Yes', ordst.eBuyAnyService = 'No') AND ord.iStatusCode NOT IN ('11','12') ".$ssql." ".$DELIVERY_DATE_SQL." AND ord.iDriverId = t.iDriverId ORDER BY ord.tOrderRequestDate DESC ";
// echo $sql; exit;
$db_order_detail = $obj->MySQLSelect($sql);

$Today=Date('Y-m-d');
$tdate=date("d")-1;
$mdate=date("d");
$Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-1,date("Y")));

$curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
$curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
$prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")-1));
$prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")-1));

$currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$tdate,date("Y")));
$currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m")+1,date("d")-$mdate,date("Y")));
$prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d")-$tdate,date("Y")));
$prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-$mdate,date("Y")));

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));
$Pmonday = date('Y-m-d', strtotime('monday this week -1 week'));
$Psunday = date('Y-m-d', strtotime('sunday this week -1 week'));
if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}
$driverOrdArr = array_column($db_order_detail,"iOrderId");

$anythingDataArr = array();
if(scount($driverOrdArr) > 0){
    $implodeOrdIds = implode(",",$driverOrdArr);
    $order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId IN ($implodeOrdIds)");
    for($an=0;$an<scount($order_buy_anything);$an++){
        $anythingDataArr[$order_buy_anything[$an]['iOrderId']][] = $order_buy_anything[$an];
    }
}
$TotalDrvEarning =$TotalDrvExpectedEarning= 0;
for($i=0;$i<scount($db_order_detail);$i++){
	$iOrderIdnew = $db_order_detail[$i]['iOrderId'];
	$getUserCurrencyLanguageDetails = getDriverCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$iOrderIdnew);
        $currencycode = $getUserCurrencyLanguageDetails['currencycode'];
        $currencySymbol = $getUserCurrencyLanguageDetails['currencySymbol'];
        $Ratio = $getUserCurrencyLanguageDetails['Ratio'];
        $db_order_detail[$i]['currencycode'] = $currencycode;
        $db_order_detail[$i]['currencySymbol'] = $currencySymbol;
        $db_order_detail[$i]['Ratio'] = $Ratio;

	// $driverExpectedEarn = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;
	$driverExpectedEarn = $db_order_detail[$i]['fDeliveryCharge'] + $db_order_detail[$i]['fTipAmount'];
	// $driverExpectedEarn = $driverExpectedEarn - ($driverExpectedEarn - ($db_order_detail[$i]['fCustomDeliveryCharge'] + $db_order_detail[$i]['fDeliveryChargeVehicle']));
	$driverExpectedEarn = $driverExpectedEarn * $Ratio;

	if($db_order_detail[$i]['iStatusCode'] == '7' || $db_order_detail[$i]['iStatusCode'] == '8'){
		// $driverEarn = $db_order_detail[$i]['fDriverPaidAmount'] * $Ratio;
		$driverEarn = $db_order_detail[$i]['fDriverPaidAmount'];

	} else {
		// $driverEarn = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;
		$driverEarn = $db_order_detail[$i]['fDeliveryCharge'];
		// $driverEarn = $driverEarn - ($driverEarn - ($db_order_detail[$i]['fCustomDeliveryCharge'] + $db_order_detail[$i]['fDeliveryChargeVehicle']));
		$driverEarn = $driverEarn + $db_order_detail[$i]['fTipAmount'];

		$item_subtotal = 0;
        if($db_order_detail[$i]['eBuyAnyService'] == "Yes" && $db_order_detail[$i]['ePaymentOption'] == "Card")
        {
            //$order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId = '" . $iOrderIdnew . "'");
            $order_buy_anything = array();
            if(isset($anythingDataArr[$iOrderIdnew])){
                $order_buy_anything = $anythingDataArr[$iOrderIdnew];
            }
            if(scount($order_buy_anything) > 0)
            {
                foreach ($order_buy_anything as $oItem) {
                    if($oItem['eConfirm'] == "Yes")
                    {
                        $item_subtotal += $oItem['fItemPrice'];
                    }
                }
            }
        }

        $db_order_detail[$i]['item_subtotal'] = $item_subtotal;
        $driverEarn += $item_subtotal;

        $driverExpectedEarn += $item_subtotal;
	}

	$driverEarn = $driverEarn * $Ratio;

	$TotalDrvExpectedEarning += $driverExpectedEarn;
	$TotalDrvEarning += $driverEarn;

    $db_order_detail[$i]['driverEarn'] = $driverEarn;
    $db_order_detail[$i]['driverExpectedEarn'] = $driverExpectedEarn;
}

foreach ($db_order_detail as $okey => $oData) {
    if($oData['eBuyAnyService'] == "No")
    {
        $orderStatusAll = $obj->MySQLSelect("SELECT * FROM order_status_logs WHERE iOrderId = ".$oData['iOrderId']." AND iStatusCode = 2");
        if(scount($orderStatusAll) == 0)
        {
            $TotalDrvExpectedEarning -= $db_order_detail[$okey]['driverExpectedEarn'];
            $TotalDrvEarning -= $db_order_detail[$okey]['driverEarn'];
            unset($db_order_detail[$okey]);
        }
    }

    $driver_log = $obj->MySQLSelect("SELECT * FROM order_driver_log WHERE iOrderId = ".$oData['iOrderId']." AND iDriverId = " . $_SESSION['sess_iUserId']);
    if(!empty($driver_log) && scount($driver_log) > 0)
    {
        $TotalDrvExpectedEarning -= $db_order_detail[$okey]['driverExpectedEarn'];
        $TotalDrvEarning -= $db_order_detail[$okey]['driverEarn'];
        unset($db_order_detail[$okey]);
    }
}

$db_order_detail = array_values($db_order_detail);

$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');

$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (scount($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $restaurant_text = $langage_lbl['LBL_RESTAURANT_TXT'];
    } else {
        $restaurant_text = $langage_lbl['LBL_STORE'];
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_DRIVER_ORDERS_TXT']; ?></title>
        <?php include_once("top/top_script.php"); ?>
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
                <h1><?= $langage_lbl['LBL_DRIVER_ORDERS_TXT']; ?></h1>
            </div>

            <div class="button-block oppData">
            <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                <input type="hidden" name="action" value="search" />
                <div class="filters-column mobile-full">
                    <label><?= $langage_lbl['LBL_ORDER_SEARCH_BY_DATE']; ?></label>
                    <select id="timeSelect" name="dateRange">
                        <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                        <option value="today" <?php if($dateRange == 'today'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Today']; ?></option>
                        <option value="yesterday" <?php if($dateRange == 'yesterday'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Yesterday']; ?></option>
                        <option value="currentWeek" <?php if($dateRange == 'currentWeek'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Week']; ?></option>
                        <option value="previousWeek" <?php if($dateRange == 'previousWeek'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Week']; ?></option>
                        <option value="currentMonth" <?php if($dateRange == 'currentMonth'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Current_Month']; ?></option>
                        <option value="previousMonth" <?php if($dateRange == 'previousMonth'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Month']; ?></option>
                        <option value="currentYear" <?php if($dateRange == 'currentYear'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMAPNY_TRIP_Current_Year']; ?></option>
                        <option value="previousYear" <?php if($dateRange == 'previousYear'){echo 'selected'; }?> ><?= $langage_lbl['LBL_COMPANY_TRIP_Previous_Year']; ?></option>
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
                    <button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                    <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                    <a href="driver-order" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>

                </div>
            </form>
			<ul class="value-listing">
					<li><b><?=$langage_lbl['LBL_TOTAL_ORDERS_DRIVER']; ?> :</b> <span><?= scount($db_order_detail);?></span></li>
					<li><b><?=$langage_lbl['LBL_TOTAL_ORDERS_EARNING_DRIVER']; ?> :</b>
					<!-- <span><?= $currencySymbol;?> <?= formateNumAsPerCurrency(trip_currency_payment($TotalDrvEarning),$currencycode); ?></span> -->
					<span><?= formateNumAsPerCurrency((trip_currency_payment($TotalDrvEarning)),$currencyName);?></span>
					</li>
			</ul>
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
					<th style="text-align: center"><?=$langage_lbl['LBL_ORDER_NO_TXT'];?></th>
					<th width="17%" style="text-align: center"><?=$langage_lbl['LBL_DATE_TXT']; ?></th>
					<th style="text-align: center"><?=$restaurant_text; ?></th>
					<th style="text-align: center"><?=$langage_lbl['LBL_ORDER_EXPECTED_EARNING_TXT']; ?></th>
					<th style="text-align: center"><?=$langage_lbl['LBL_EARNING_AMOUNT_DRIVER']; ?></th>
					<th style="text-align: center"><?=$langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
					<th style="text-align: center"><?=$langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
                </tr>
            </thead>
			<tbody>
				<?php
					for($i=0;$i<scount($db_order_detail);$i++)
					{
						$iOrderIdnew = $db_order_detail[$i]['iOrderId'];
						//$getUserCurrencyLanguageDetails = getDriverCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$iOrderIdnew);
						//$currencycode = $getUserCurrencyLanguageDetails['currencycode'];
						//$currencySymbol = $getUserCurrencyLanguageDetails['currencySymbol'];
						//$Ratio = $getUserCurrencyLanguageDetails['Ratio'];
                                                //echo "<pre>";print_r($db_order_detail[$i]);die;
						$currencycode = $db_order_detail[$i]['currencycode'];
						$currencySymbol = $db_order_detail[$i]['currencySymbol'];
						$Ratio = $db_order_detail[$i]['Ratio'];

						// $expectedearning = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;
						$expectedearning = $db_order_detail[$i]['fDeliveryCharge'];
						// $expectedearning = $expectedearning - ($expectedearning - ($db_order_detail[$i]['fCustomDeliveryCharge'] + $db_order_detail[$i]['fDeliveryChargeVehicle']));
                        if(!isset($db_order_detail[$i]['item_subtotal'])) {
                            $db_order_detail[$i]['item_subtotal'] = 0;
                        }
						$expectedearning = $expectedearning + $db_order_detail[$i]['fTipAmount'] + $db_order_detail[$i]['item_subtotal'];
						$expectedearning = $expectedearning * $Ratio;

						if($db_order_detail[$i]['iStatusCode'] == '7' || $db_order_detail[$i]['iStatusCode'] == '8'){
							// $driverEarning = $db_order_detail[$i]['fDriverPaidAmount'] * $Ratio;
							$driverEarning = $db_order_detail[$i]['fDriverPaidAmount'];
						} else {
							// $driverEarning = $db_order_detail[$i]['fDeliveryCharge'] * $Ratio;
							$driverEarning = $db_order_detail[$i]['fDeliveryCharge'];
							// $driverEarning = $driverEarning - ($driverEarning - ($db_order_detail[$i]['fCustomDeliveryCharge'] + $db_order_detail[$i]['fDeliveryChargeVehicle']));
							$driverEarning = $driverEarning + $db_order_detail[$i]['fTipAmount'] + $db_order_detail[$i]['item_subtotal'];
						}

						$driverEarning = $driverEarning * $Ratio;
                        $pickupDateTime = "";
                        $pickupTime = $obj->MySQLSelect("SELECT dDate from order_status_logs WHERE iStatusCode = 5 AND iOrderId = '" . $iOrderIdnew . "'");
                        if (scount($pickupTime) > 0) {
                            $pickupDateTime = $pickupTime[0]['dDate'];
                        } else {
                            $pickupTime = $obj->MySQLSelect("SELECT dDate from order_status_logs WHERE iStatusCode = 6 AND iOrderId = '" . $iOrderIdnew . "'");
                            if (scount($pickupTime) > 0) {
                                $pickupDateTime = $pickupTime[0]['dDate'];
                            }
                        }
                        $pickupDateTime = $db_order_detail[$i]['DeliveryDate'];
						$systemTimeZone = date_default_timezone_get();
						/*if($db_order_detail[$i]['tOrderRequestDate']!= "" && $db_order_detail[$i]['vTimeZone'] != "")  {
							$tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'],$db_order_detail[$i]['vTimeZone'],$systemTimeZone);
						} else {
							$tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
						}*/
                        if(!empty($pickupDateTime) && $db_order_detail[$i]['vTimeZone'] != "")  {
                            $tOrderRequestDate = converToTz($pickupDateTime,$db_order_detail[$i]['vTimeZone'],$systemTimeZone);
                        } else {
                            $tOrderRequestDate = $pickupDateTime;
                        }
                        $date_format_data_array = array(
                            'tdate' => $tOrderRequestDate,
                            'langCode' => $vLang,
                            'DateFormatForWeb' => 1
                        );
                        $get_request_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
					?>
					<tr class="gradeA">
						<td align="center"><?=$db_order_detail[$i]['vOrderNo'];?></td>
						<td data-order="<?php echo $tOrderRequestDate; ?>"><?= $get_request_date_format['tDisplayDateTime'];//DateTime1($tOrderRequestDate,'yes');?></td>
						<td align="center"><?= clearName($db_order_detail[$i]['vCompany']);?></td>
						<td align="center"><?= formateNumAsPerCurrency(trip_currency_payment($expectedearning),$currencyName);     //$currencySymbol." ".trip_currency_payment($expectedearning);?></td>
						<td align="center">
							<?php if(($driverEarning == 0 && $db_order_detail[$i]['iStatusCode'] == '7') || $driverEarning > 0 || $db_order_detail[$i]['eBuyAnyService'] == "Yes") {
								// echo $currencySymbol." ".trip_currency_payment($driverEarning);
								echo formateNumAsPerCurrency(trip_currency_payment($driverEarning),$currencyName);
								 } else {
								echo $langage_lbl['LBL_PENDING_WEB'];
							 } ?>
						</td>
						<td align="center"><?=$db_order_detail[$i]['vStatus'];?></td>
						<td align="center" width="10%">
						  <a target = "_blank" href="cx-invoice_deliverall.php?iOrderId=<?=base64_encode(base64_encode($db_order_detail[$i]['iOrderId']))?>">
								<img alt="" src="<?php echo $invoice_icon;?>">
						 </a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
			<tfoot>
            <?php if(scount($db_order_detail) > 0) { ?>
				<tr class="last_row_record">
					<td></td>
					<td></td>
					<td></td>
					<!--<td class="last_record_row"><?= $currencySymbol;?> <?=trip_currency_payment($TotalDrvExpectedEarning);?></td>
					<td class="last_record_row"><?= $currencySymbol;?> <?=trip_currency_payment($TotalDrvEarning);?></td> -->
					<td class="last_record_row"><?=formateNumAsPerCurrency(trip_currency_payment($TotalDrvExpectedEarning),$currencyName);?></td>
					<td class="last_record_row"><?=formateNumAsPerCurrency(trip_currency_payment($TotalDrvEarning),$currencyName);?></td>
					<td></td>
					<td></td>
				</tr>
            <?php } ?>
			</tfoot>
        </table>
    </div>
    </div>
</section>


    <!-- add money-->

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
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


<script type="text/javascript">
      if($('#my-trips-data').length > 0) {
        $('#my-trips-data').DataTable({"oLanguage": langData, "order": [[1, 'desc']]});
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
         $(document).ready(function () {
         	$( "#dp4" ).datepicker({
         		dateFormat: "yy-mm-dd",
         		changeYear: true,
     		  	changeMonth: true,
     		  	yearRange: "-100:+10"
         	});
         	$( "#dp5" ).datepicker({
         		dateFormat: "yy-mm-dd",
         		changeYear: true,
     		  	changeMonth: true,
     		  	yearRange: "-100:+10"
         	});

            var startDate = '<?= !empty($startDate) ? $startDate : '' ?>';
            var endDate = '<?= !empty($endDate) ? $endDate : '' ?>';
			 if(startDate !=''){
				 $("#dp4").val(startDate);
				 $("#dp4").datepicker('refresh');
			 }
			 if(endDate !=''){
				 $("#dp5").val(endDate);
				 $("#dp5").datepicker('refresh');
			 }

			$('#dataTables-example').DataTable( {
    "oLanguage": langData,
			  "order": [[ 1, "desc" ]]
			} );
         });
        function reset() {
			location.reload();
		}
		 function todayDate()
		 {
			 $("#dp4").val('<?= $Today;?>');
			 $("#dp5").val('<?= $Today;?>');
		 }
		 function yesterdayDate()
		 {
			 $("#dp4").val('<?= $Yesterday;?>');
			 $("#dp5").val('<?= $Yesterday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $monday;?>');
			 $("#dp5").val('<?= $sunday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousweekDate(dt,df)
		 {
			 $("#dp4").val('<?= $Pmonday;?>');
			 $("#dp5").val('<?= $Psunday;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $currmonthFDate;?>');
			 $("#dp5").val('<?= $currmonthTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousmonthDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevmonthFDate;?>');
			 $("#dp5").val('<?= $prevmonthTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function currentyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $curryearFDate;?>');
			 $("#dp5").val('<?= $curryearTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
		 function previousyearDate(dt,df)
		 {
			 $("#dp4").val('<?= $prevyearFDate;?>');
			 $("#dp5").val('<?= $prevyearTDate;?>');
			 $("#dp4").datepicker('refresh');
			 $("#dp5").datepicker('refresh');
		 }
	 	function checkvalid(){
			 if($("#dp5").val() < $("#dp4").val()){
				 //bootbox.alert("<h4>From date should be lesser than To date.</h4>");
			 	bootbox.dialog({
				 	message: "<h4><?php echo addslashes($langage_lbl['LBL_FROM_TO_DATE_ERROR_MSG']);?></h4>",
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

    <script type="text/javascript">
    $(document).ready(function(){
        $("[name='dataTables-example_length']").each(function(){
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $("[name='dataTables-example_length']").change(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    })
</script>
<!-- End: Footer Script -->
</body>
</html>
