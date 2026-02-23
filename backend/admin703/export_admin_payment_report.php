<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-admin-earning')) {
    $userObj->redirect();
}


$script = 'Admin Payment_Report';
$eSystem = " AND eSystem = 'DeliverAll'";
require('fpdf/fpdf.php');
$date = new DateTime();
$timestamp_filename = $date->getTimestamp();
function cleanNumber($num) {
    return str_replace(',', '', $num);
}

//data for select fields
$ssqlsc = " AND iServiceId IN(".$enablesevicescategory.")";
$sql = "select iCompanyId,vCompany,vEmail from company WHERE eStatus != 'Deleted' $eSystem $ssqlsc order by vCompany";
$db_company = $obj->MySQLSelect($sql);
//data for select fields
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY o.iOrderId DESC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY ru.vName ASC";
    else
        $ord = " ORDER BY ru.vName DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY o.tOrderRequestDate ASC";
    else
        $ord = " ORDER BY o.tOrderRequestDate DESC";
}

if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY o.ePaymentOption ASC";
    else
        $ord = " ORDER BY o.ePaymentOption DESC";
}
//End Sorting
// Start Search Parameters
$ssql = '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$serachOrderNo = isset($_REQUEST['serachOrderNo']) ? $_REQUEST['serachOrderNo'] : '';
$searchRestaurantPayment = isset($_REQUEST['searchRestaurantPayment']) ? $_REQUEST['searchRestaurantPayment'] : '';
$searchPaymentType = isset($_REQUEST['searchPaymentType']) ? $_REQUEST['searchPaymentType'] : '';
$searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';

if ($action == 'search') {
    if ($startDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
    }
    if ($serachOrderNo != '') {
        $ssql .= " AND o.vOrderNo ='" . $serachOrderNo . "'";
    }
    if ($searchCompany != '') {
        $ssql .= " AND c.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchServiceType != '' && !in_array($searchServiceType, ['Genie', 'Runner', 'Anywhere'])) {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "' AND o.eBuyAnyService ='No'";
    }
    if ($searchServiceType == "Genie") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
    }
    if ($searchServiceType == "Runner") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
    }
    if ($searchRestaurantPayment != '') {
        $ssql .= " AND o.eRestaurantPaymentStatus ='" . $searchRestaurantPayment . "'";
    }
    if ($searchPaymentType != '') {
        $ssql .= " AND o.ePaymentOption ='" . $searchPaymentType . "'";
    }
}

$trp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
}
$ssql .= " AND sc.iServiceId IN(".$enablesevicescategory.")";
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql1 = "SELECT o.iOrderId,o.vOrderNo,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fTax,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.fOutStandingAmount,o.iStatusCode,os.vStatus,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, o.fTipAmount, o.eBuyAnyService, o.ePaymentOption,o.eForPickDropGenie FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode = '6' $ssql $trp_ssql";

$totalData = $obj->MySQLSelect($sql1);

$orderIdArr = $companyIdArr =$userIdArr=$driverIdArr=$tripDataArr=$companyDataArr=$userDataArr=$driverDataArr= array();
for($g=0;$g<scount($totalData);$g++){
    $orderIdArr[] = $totalData[$g]['iOrderId'];
    $companyIdArr[] = $totalData[$g]['iCompanyId'];
    $userIdArr[] = $totalData[$g]['iUserId'];
    $driverIdArr[] = $totalData[$g]['iDriverId'];
}
//echo "<pre>";print_r($driverIdArr);die;
if(scount($orderIdArr) > 0){
    $orderIdArr = array_unique($orderIdArr, SORT_REGULAR);
    $implodeOrderIds= implode(",",$orderIdArr);
    $tripData = $obj->MySQLSelect("SELECT fDeliveryCharge,iOrderId FROM trips WHERE iOrderId IN ($implodeOrderIds)");
    for($t=0;$t<scount($tripData);$t++){
        $tripDataArr[$tripData[$t]['iOrderId']] = $tripData[$t];
    }
}
//echo "<pre>";print_r($tripDataArr);die;

//Added By HJ On 21-09-2020 For Optimize loop Query Start
$OrderItemBuyArr = array();
$order_buy_anything = $obj->MySQLSelect("SELECT eConfirm,fItemPrice,iOrderId FROM order_items_buy_anything");
for($b=0;$b<scount($order_buy_anything);$b++){
    $OrderItemBuyArr[$order_buy_anything[$b]['iOrderId']][]  = $order_buy_anything[$b];
}
//echo "<pre>";print_r($OrderItemBuyArr);die;
//echo "<pre>";print_r($driverDataArr);die;
$tot_order_amount = 0.00;
$tot_site_commission = 0.00;
$tot_delivery_charges = 0.00;
$tot_offer_discount = 0.00;
$tot_admin_payment = 0.00;
$tot_outstanding_amount = 0.00;
$tot_admin_tax = 0.00;
foreach ($totalData as $dtps) {
    $orderId = $dtps['iOrderId'];
    $totalfare = $dtps['fTotalGenerateFare'];
    $fOffersDiscount = $dtps['fOffersDiscount'];
    $fDeliveryCharge = $dtps['fDeliveryCharge'];
    $site_commission = $dtps['fCommision'];
    $fOutStandingAmount = $dtps['fOutStandingAmount'];
    $fTipAmount = $dtps['fTipAmount'];
    $fTax = $dtps['fTax'];

    $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount)- cleanNumber($fTax);
    $tripDelCharge = 0;
    if(isset($tripDataArr[$orderId])){
        $tripDelCharge = $tripDataArr[$orderId]['fDeliveryCharge'];
    }

    $subtotal = 0;
    if($dtps['eBuyAnyService'] == "Yes" && $dtps['ePaymentOption'] == "Card")
    {
        //$order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId = '" . $orderId . "'");
        $order_buy_anything = array();
        if(isset($OrderItemBuyArr[$orderId])){
            $order_buy_anything = $OrderItemBuyArr[$orderId];
        }

        if(scount($order_buy_anything) > 0)
        {
            foreach ($order_buy_anything as $oItem) {
                if($oItem['eConfirm'] == "Yes")
                {
                    $subtotal += $oItem['fItemPrice'];    
                }
            }
        }
    }

    $siteearnig = cleanNumber($site_commission) + cleanNumber($fDeliveryCharge) + cleanNumber($fOutStandingAmount)+ cleanNumber($fTipAmount)- cleanNumber($fTax);
    $driverearning = $tripDelCharge + $dtps['fTipAmount'] + $subtotal;
    $adminearning = $siteearnig - cleanNumber($driverearning) ;
    
    if($dtps['eBuyAnyService'] == "Yes")
    {
        $adminearning = cleanNumber($site_commission) + cleanNumber($fOutStandingAmount);
    }

    $tot_order_amount = $tot_order_amount + cleanNumber($totalfare);
    $tot_offer_discount = $tot_offer_discount + cleanNumber($fOffersDiscount);
    $tot_delivery_charges = $tot_delivery_charges + cleanNumber($fDeliveryCharge);
    $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
    $tot_outstanding_amount = $tot_outstanding_amount + cleanNumber($fOutStandingAmount);
    $tot_admin_payment = $tot_admin_payment + cleanNumber($adminearning);
     $tot_admin_tax = $tot_admin_tax + cleanNumber($fTax);
}

$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}

// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT o.iOrderId,o.vOrderNo,o.iCompanyId,sc.vServiceName_" . $default_lang . " as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.fTax,o.eRestaurantPaymentStatus,o.ePaymentOption,o.fOutStandingAmount,o.iStatusCode,os.vStatus,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, o.fTipAmount, o.eBuyAnyService, o.eForPickDropGenie,o.vTimeZone FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.iStatusCode = '6' $ssql $trp_ssql $ord";
$db_trip = $obj->MySQLSelect($sql) or die('Query failed!');

$endRecord = scount($db_trip);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
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

$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
$cardText = "Card";
if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3') {
    $cardText = "Wallet";
}
//Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 End

$flag = false;
$filename ="store_deliveries_earning_report_".$timestamp_filename.'.xls';
$SPREADSHEET_OBJ->setActiveSheetIndex(0);
// Get the active sheet
$sheet = $SPREADSHEET_OBJ->getActiveSheet();
$sheet->setCellValue('A1', 'Service type');
$sheet->setCellValue('B1', $langage_lbl_admin['LBL_RIDE_NO_ADMIN_DL']);
$sheet->setCellValue('C1', $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']." Date");
$sheet->setCellValue('D1', "A=Total Order Amount");       
$sheet->setCellValue('E1', "B=Site Commision");
$sheet->setCellValue('F1', "C=Delivery Charges");
$sheet->setCellValue('G1', "D=OutStanding Amount");
$sheet->setCellValue('H1', "E=Delivery Tip");
$sheet->setCellValue('I1', "F=Tax");
$sheet->setCellValue('J1', "G=Driver Pay Amount");
$sheet->setCellValue('K1', "H=Admin Earning Amount");
$sheet->setCellValue('L1', $langage_lbl_admin['LBL_RIDE_TXT_ADMIN_DL']." Status");
$sheet->setCellValue('M1', "Payment method");
$j = 2;

$serverTimeZone = date_default_timezone_get();
if(scount($db_trip) > 0){
    for($i=0;$i<scount($db_trip);$i++) {
        $iOrderId = $db_trip[$i]['iOrderId'];
        $totalfare = $db_trip[$i]['fTotalGenerateFare'];
        $site_commission = $db_trip[$i]['fCommision'];
        $fOffersDiscount = $db_trip[$i]['fOffersDiscount'];
        $fDeliveryCharge = $db_trip[$i]['fDeliveryCharge'];
        $fOutStandingAmount = $db_trip[$i]['fOutStandingAmount'];
        $fTipAmount = $db_trip[$i]['fTipAmount'];
         $fTax = $db_trip[$i]['fTax'];

        $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);

        $set_unsetarray[] = $db_trip[$i]['eRestaurantPaymentStatus'];

        if (!empty($db_trip[$i]['drivername'])) {
            $drivername = $db_trip[$i]['drivername'];
        } else {
            $drivername = '--';
        }

        $subtotal = 0;
        if($db_trip[$i]['eBuyAnyService'] == "Yes" && $db_trip[$i]['ePaymentOption'] == "Card")
        {
            //$order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId = '" . $db_trip[$i]['iOrderId'] . "'");
            $order_buy_anything = array();
            if(isset($OrderItemBuyArr[$iOrderId])){
                $order_buy_anything = $OrderItemBuyArr[$iOrderId];
            }

            if(scount($order_buy_anything) > 0)
            {
                foreach ($order_buy_anything as $oItem) {
                    if($oItem['eConfirm'] == "Yes")
                    {
                        $subtotal += $oItem['fItemPrice'];    
                    }
                }
            }
        }
        $db_trip[$i]['item_subtotal'] = $subtotal;
        $driverearningnew = 0;
        if(isset($tripDataArr[$iOrderId])){
            $driverearningnew = $tripDataArr[$iOrderId]['fDeliveryCharge'];
        }

        $siteearnig = cleanNumber($site_commission) + cleanNumber($fDeliveryCharge) + cleanNumber($fOutStandingAmount) + cleanNumber($fTipAmount)- cleanNumber($fTax);
        $driverearning = $driverearningnew + cleanNumber($fTipAmount) + $subtotal;
        $adminearning = $siteearnig - cleanNumber($driverearning);
        if($db_trip[$i]['eBuyAnyService'] == "Yes")
        {
            $db_trip[$i]['vServiceName'] = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
            if($db_trip[$i]['eForPickDropGenie'] == "Yes")
            {
                $db_trip[$i]['vServiceName'] = $langage_lbl_admin['LBL_RUNNER'];
            }
            $adminearning = cleanNumber($site_commission) + cleanNumber($fOutStandingAmount);
        }
      
        if(scount($allservice_cat_data) > 1) {
          $result['service_type'] = $db_trip[$i]['vServiceName'];
        }
        
        $result['ride_no'] = $db_trip[$i]['vOrderNo'];

        $date_format_data_array = array(
            'langCode' => $default_lang,
            'DateFormatForWeb' => 1
        );
        $date_format_data_array['tdate'] = (!empty($db_trip[$i]['vTimeZone'])) ? converToTz($db_trip[$i]['tOrderRequestDate'],$db_trip[$i]['vTimeZone'],$serverTimeZone) : $db_trip[$i]['tOrderRequestDate'];
        $get_tOrderRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
        $result['date'] = $get_tOrderRequestDate_format['tDisplayDateTime'].str_replace("<br>"," ",$time_zone_difference_text)."\t";//DateTime($db_trip[$i]['tOrderRequestDate'])."\t";
        
        $result['total_amount'] = ($db_trip[$i]['fTotalGenerateFare'] != "" && $db_trip[$i]['fTotalGenerateFare'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fTotalGenerateFare'], ''):'---';
        $data .= "\t";
        
        $result['commision'] = ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fCommision'], '') : '---';
        $data .= "\t";
        
        $result['charge'] = ($db_trip[$i]['fDeliveryCharge'] != "" && $db_trip[$i]['fDeliveryCharge'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fDeliveryCharge'], '') : '---';
        $data .= "\t";
        
        //$data .= ($db_trip[$i]['fOffersDiscount'] != "" && $db_trip[$i]['fOffersDiscount'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fOffersDiscount'], '') : '---'."\t";
        //$data .= "\t";
        
        $result['OutStandingAmount'] = ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'], '') : '---';
        $data .= "\t";
		
        $result['tip'] = ($db_trip[$i]['fTipAmount'] != "" && $db_trip[$i]['fTipAmount'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fTipAmount'], '') : '---';
        $data .= "\t";
		
        $result['tax'] = ($db_trip[$i]['fTax'] != "" && $db_trip[$i]['fTax'] != 0) ? formateNumAsPerCurrency($db_trip[$i]['fTax'], '') : '---';
        $data .= "\t";
        
        //$data .= ($restaurant_payment != "" && $restaurant_payment != 0) ? formateNumAsPerCurrency($restaurant_payment, '') : '---'."\t";
        //$data .= "\t";

        $result['driver_pay'] = ($driverearningnew != "" && $driverearningnew != 0) ? formateNumAsPerCurrency($driverearning, '') : '---';
        $data .= "\t";
        
        $result['earning_ammount'] = ($adminearning != "" && $adminearning != 0) ? formateNumAsPerCurrency($adminearning, '') : '---';
        $data .= "\t";
        
        $result['status'] = $db_trip[$i]['vStatus'];
        
        $ePaymentOption = $db_trip[$i]['ePaymentOption'];
        if ($db_trip[$i]['ePaymentOption'] == 'Card') {
            $ePaymentOption = $cardText;
        }
        
        $result['payment'] = $ePaymentOption;
      
		
			$sheet->setCellValue('A' . $j, $result['service_type']);
            $sheet->setCellValue('B' . $j, $result['ride_no']);
            $sheet->setCellValue('C' . $j, $result['date']);
            $sheet->setCellValue('D' . $j, $result['total_amount']);
            $sheet->setCellValue('E' . $j, $result['commision']);
            $sheet->setCellValue('F' . $j, $result['charge']);
            $sheet->setCellValue('G' . $j, $result['OutStandingAmount']);
            $sheet->setCellValue('H' . $j, $result['tip']);
            $sheet->setCellValue('I' . $j, $result['tax']);
            $sheet->setCellValue('J' . $j, $result['driver_pay']);
            $sheet->setCellValue('K' . $j, $result['earning_ammount']);
            $sheet->setCellValue('L' . $j, $result['status']);
            $sheet->setCellValue('M' . $j, $result['payment']);            
            $j++;  
    }
}

	$j +=1;
	$Summary_array = array(
        "Total Fare " => formateNumAsPerCurrency($tot_order_amount, ''),			
        "Total Site Commission " =>  formateNumAsPerCurrency($tot_site_commission, ''),
        "Total Tax " => formateNumAsPerCurrency($tot_admin_tax, ''),		
        "Total Delivery Charges " => formateNumAsPerCurrency($tot_delivery_charges, ''),			
        "Total Outstanding Amount " => formateNumAsPerCurrency($tot_outstanding_amount, ''),
        "Total Admin Earning " => formateNumAsPerCurrency($tot_admin_payment, '')      
    );

	foreach ($Summary_array as $key => $value) {
        $sheet->setCellValue('L' . $j, $key);
        $sheet->setCellValue('M' . $j, $value);
        $j++;
    }

    foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );            
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
//added by SP on 28-06-2019 end
?>

