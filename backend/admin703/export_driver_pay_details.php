<?php
include_once('../common.php');

$tbl_name = 'trips';

// require('fpdf/fpdf.php');
// require('TCPDF-master/tcpdf.php'); // Added By Hasmukh
$date = new DateTime();
$timestamp_filename = $date->getTimestamp();

$abc = 'admin,company';

$action = $_REQUEST['action'];
//added by SP on 28-06-2019 start
$searchCompany = isset($_REQUEST['prevsearchCompany']) ? $_REQUEST['prevsearchCompany'] : '';
$searchDriver = isset($_REQUEST['prevsearchDriver']) ? $_REQUEST['prevsearchDriver'] : '';
$startDate = isset($_REQUEST['prev_start']) ? $_REQUEST['prev_start'] : '';
$endDate = isset($_REQUEST['prev_end']) ? $_REQUEST['prev_end'] : '';
$searchDriverPayment = isset($_REQUEST['searchDriverPayment']) ? $_REQUEST['searchDriverPayment'] : 'Unsettelled';
//data for select fields
$sql = "SELECT iCompanyId,vCompany,vEmail FROM company WHERE eStatus != 'Deleted' AND eSystem = 'General' order by vCompany";
$db_company = $obj->MySQLSelect($sql);
$sql = "SELECT iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail FROM register_driver WHERE eStatus != 'Deleted' order by vName";
$db_drivers = $obj->MySQLSelect($sql);
//Start Sorting
$sortby = isset($_REQUEST['prev_sortby']) ? $_REQUEST['prev_sortby'] : 0;
$order = isset($_REQUEST['prev_order']) ? $_REQUEST['prev_order'] : '';
$ord = ' ORDER BY rd.iDriverId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.iDriverId ASC";
    else
        $ord = " ORDER BY rd.iDriverId DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY rd.vBankAccountHolderName ASC";
    else
        $ord = " ORDER BY rd.vBankAccountHolderName DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY rd.vBankName ASC";
    else
        $ord = " ORDER BY rd.vBankName DESC";
}
//End Sorting
// Start Search Parameters
//$ssql='';
$ssql = " AND tr.iActive = 'Finished' ";
$ssql1 = $whereDriverId = '';

if ($startDate != '') {
    //$ssql.=" AND Date(tr.tEndDate) >='".$startDate."'";
    $ssql .= " AND Date(tr.tTripRequestDate) >='" . $startDate . "'";
    $whereDriverId .= " AND Date(tTripRequestDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    //$ssql.=" AND Date(tr.tEndDate) <='".$endDate."'";
    $ssql .= " AND Date(tr.tTripRequestDate) <='" . $endDate . "'";
    $whereDriverId .= " AND Date(tTripRequestDate) <='" . $endDate . "'";
}
if ($searchCompany != '') {
    $ssql1 .= " AND rd.iCompanyId ='" . $searchCompany . "'";
}
if ($searchDriver != '') {
    $ssql .= " AND tr.iDriverId ='" . $searchDriver . "'";
    $whereDriverId .= " AND iDriverId ='" . $searchDriver . "'";
}

$locations_where = "";
if (scount($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE tr.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
}
//Select dates
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

$per_page = $DISPLAY_RECORD_NUMBER;

$etypeSql = " AND tr.eSystem = 'General'";
$etypeSql1 = " AND eSystem = 'General'";
if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) {
    $etypeSql = " AND (tr.eSystem = 'General' OR tr.eSystem = 'DeliverAll')";
    $etypeSql1 = " AND (eSystem = 'General' OR eSystem = 'DeliverAll') AND iServiceId = '0'";
}

/*$sql = "SELECT COUNT( DISTINCT rd.iDriverId ) AS Total FROM register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='Unsettelled' AND tr.eSystem = 'General' $ssql $ssql1";*/
$sql = "SELECT COUNT( DISTINCT rd.iDriverId ) AS Total FROM register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='$searchDriverPayment' $etypeSql $ssql $ssql1";

$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End


$sql = "SELECT rd.iDriverId,tr.eDriverPaymentStatus,concat(rd.vName,' ',rd.vLastName) as dname,rd.vCountry,rd.vBankAccountHolderName,rd.vAccountNumber,rd.vBankLocation,rd.vBankName,rd.vBIC_SWIFT_Code FROM register_driver AS rd LEFT JOIN trips AS tr ON tr.iDriverId=rd.iDriverId WHERE tr.eDriverPaymentStatus='$searchDriverPayment' $etypeSql AND tr.iActive = 'Finished'  $ssql $ssql1 GROUP BY rd.iDriverId $ord";
$db_payment = $obj->MySQLSelect($sql);


$getDriverTripData = $obj->MySQLSelect("SELECT iActive,fCancellationFare,iOrganizationId,iTripId,iDriverId,SUM(fTipPrice) AS fTipPrice,SUM(fTripGenerateFare) AS fTripGenerateFare,SUM(fCommision) AS fCommision,SUM(fTax1) AS fTax1,SUM(fTax2) AS fTax2,SUM(fOutStandingAmount) AS fOutStandingAmount,SUM(fHotelCommision) AS fHotelCommision,SUM(fDiscount) AS fDiscount,SUM(fWalletDebit) AS fWalletDebit,SUM(iFare) AS iFare,SUM(iBaseFare) AS iBaseFare,SUM(fPricePerKM) AS fPricePerKM,SUM(fPricePerMin) AS fPricePerMin,vTripPaymentMode FROM trips WHERE (iActive ='Finished' OR (iActive ='Canceled' AND iFare > 0) OR (iActive ='Canceled' AND fWalletDebit > 0 AND iFare = 0)) AND iDriverId >0 $etypeSql1 

AND iFromStationId = '0' 
AND iToStationId = '0' 
AND eDriverPaymentStatus='$searchDriverPayment' $whereDriverId GROUP BY iDriverId,iTripId,vTripPaymentMode ORDER BY iTripId ASC");

$driverArr = array();
for ($r = 0; $r < scount($getDriverTripData); $r++) {
    $driverArr[$getDriverTripData[$r]['iDriverId']][$getDriverTripData[$r]['iTripId']] = $getDriverTripData[$r];
}

$enableCashReceivedCol = $enableTipCol = array();
for ($i = 0; $i < scount($db_payment); $i++) {
    $cashPayment = $cardPayment = $transferAmount = $walletPayment = $promocodePayment = $tripoutstandingAmount = $bookingfees = $totTaxAmt = $totalCashReceived = $tot_fare = $providerAmtCard = $providerAmtCash =$providerAmtOrg = $tipPayment = 0;
    $iDriverId = $db_payment[$i]['iDriverId'];
    if (isset($driverArr[$iDriverId])) {
        $driverData = $driverArr[$iDriverId];

        // Added By HJ On 10-05-2019 For Provide Payment Data Start
        foreach ($driverData as $key => $val) {
            $providerAmtCard = $providerAmtCash =$providerAmtOrg= 0;
            $iFare = setTwoDecimalPoint($val['iFare']);
            $fTipPrice = setTwoDecimalPoint($val['fTipPrice']);
            if ($fTipPrice > 0) {
                $enableTipCol[] = 1;
            }
            $totalfare = setTwoDecimalPoint($val['fTripGenerateFare']);
            $site_commission = setTwoDecimalPoint($val['fCommision']);
            $hotel_commision = setTwoDecimalPoint($val['fHotelCommision']);
            $fOutStandingAmount = setTwoDecimalPoint($val['fOutStandingAmount']);
            $fWalletDebit = setTwoDecimalPoint($val['fWalletDebit']);
            $totTax = setTwoDecimalPoint($val['fTax1'] + $val['fTax2']);
            $fDiscount = setTwoDecimalPoint($val['fDiscount']);
            $tipPayment += $fTipPrice;
            $cashPayment += $site_commission;
            $totTaxAmt += $totTax;
            if (strtoupper($val['vTripPaymentMode']) == "CASH") {
                
                $walletPayment += $fWalletDebit;
                $promocodePayment += $fDiscount;
                $tripoutstandingAmount += $fOutStandingAmount;
                $bookingfees += $hotel_commision;
                //$totalCashReceived += $iFare;
                $enableCashReceivedCol[] = 1;

                 if ($val['iActive'] == "Canceled") {
                    $providerAmtCash = $iFare - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision);
                    $totalCashReceived += 0;
                } else {
                    $providerAmtCash = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision + $iFare);
                    $totalCashReceived += $iFare;
                }
            } else if (strtoupper($val['vTripPaymentMode']) == "CARD") {

               $providerAmtCard = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision);
                $cardPayment += $providerAmtCard;
            }else if (strtoupper($val['vTripPaymentMode']) == "ORGANIZATION") {
                $providerAmtOrg = ($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $hotel_commision);
                $organizationPayment += $providerAmtOrg;
            }
            $tot_fare += $totalfare;
            $transferAmount += $providerAmtCash + $providerAmtCard+$providerAmtOrg;
            //echo $transferAmount."<br>";
        }
        // Added By HJ On 10-05-2019 For Provide Payment Data End
    }
    $db_payment[$i]['transferAmount'] = setTwoDecimalPoint($transferAmount); // Added By HJ On 10-05-2019
    //$db_payment[$i]['cashPayment'] = getAllCashCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['cashPayment'] = setTwoDecimalPoint($cashPayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['cardPayment'] = getAllCardCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['cardPayment'] = setTwoDecimalPoint($cardPayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['walletPayment'] = getAllWalletCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['walletPayment'] = setTwoDecimalPoint($walletPayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['promocodePayment'] = getAllPromocodeCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['promocodePayment'] = setTwoDecimalPoint($promocodePayment); // Added By HJ On 10-05-2019
    //$db_payment[$i]['tripoutstandingAmount'] = getAllOutstandingAmountCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['tripoutstandingAmount'] = setTwoDecimalPoint($tripoutstandingAmount); // Added By HJ On 10-05-2019
    //$db_payment[$i]['bookingfees'] = getAllBookingAmountCountbyDriverId($db_payment[$i]['iDriverId'], $ssql); // Commented By HJ On 10-05-2019
    $db_payment[$i]['bookingfees'] = setTwoDecimalPoint($bookingfees); // Added By HJ On 10-05-2019
    $db_payment[$i]['tipPayment'] = setTwoDecimalPoint($tipPayment); // Added By HJ On 10-05-2019
    $db_payment[$i]['totalTaxAmt'] = setTwoDecimalPoint($totTaxAmt); // Added By HJ On 10-05-2019
    $db_payment[$i]['totalCashReceived'] = setTwoDecimalPoint($totalCashReceived); // Added By HJ On 10-05-2019
    $db_payment[$i]['totalFare'] = setTwoDecimalPoint($tot_fare); // Added By HJ On 10-05-2019

    $tot_fareAllTemp = $tot_fareAllTemp + setTwoDecimalPoint($tot_fare);
    $cashPaymentAllTemp = $cashPaymentAllTemp + setTwoDecimalPoint($cashPayment);
    $cardPaymentAllTemp = $cardPaymentAllTemp + setTwoDecimalPoint($cardPayment);
    $walletPaymentAllTemp = $walletPaymentAllTemp + setTwoDecimalPoint($walletPayment);
    $promocodePaymentAllTemp = $promocodePaymentAllTemp + setTwoDecimalPoint($promocodePayment);
    $tripoutstandingAmountAllTemp = $tripoutstandingAmountAllTemp + setTwoDecimalPoint($tripoutstandingAmount);
    $bookingfeesAllTemp = $bookingfeesAllTemp + setTwoDecimalPoint($bookingfees);
    $tipPaymentAllTemp = $tipPaymentAllTemp + setTwoDecimalPoint($tipPayment);
    $totTaxAmtAllTemp = $totTaxAmtAllTemp + setTwoDecimalPoint($totTaxAmt);
    $totalCashReceivedAllTemp = $totalCashReceivedAllTemp + setTwoDecimalPoint($totalCashReceived);

     if($transferAmount > 0) {
            $transferAmountAllTemp = $transferAmountAllTemp + $transferAmount;
    }
    
    if ($transferAmount >= 0) {
            
    } else {
            $transferAmountTakeBackAllTemp = $transferAmountTakeBackAllTemp + abs($transferAmount);
    }
}
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');
//echo "<pre>";print_r($db_payment);die;
$flag = false;
        $filename ="service_provider_jobs_payment_report_.xls";
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Name");
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Bank Details");
        $sheet->setCellValue('C1', "Bank Name");
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Bank Account Number");
        $sheet->setCellValue('E1', "Sort Code");
        $sheet->setCellValue('F1', "Total Fare");
        $sheet->setCellValue('G1', "Total Cash Received");
        $sheet->setCellValue('H1', "Total Tip Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
        $sheet->setCellValue('I1', "Total " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Commission Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']);
        $sheet->setCellValue('J1', "Total " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Card " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']);
        $sheet->setCellValue('K1', "Total Tax Amount Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']);
        $sheet->setCellValue('L1', "Total  " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Outstanding Amount Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']);
        $sheet->setCellValue('M1', "Total " . $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Booking Fee Take From " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " For Cash  " . $langage_lbl_admin['LBL_TRIPS_TXT_ADMIN']);
        $sheet->setCellValue('N1', "Final Amount Pay to " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
        $sheet->setCellValue('O1', "Final Amount to take back from " . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
        $sheet->setCellValue('P1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Payment Status");
        $i = 2;

        for ($j = 0; $j < scount($db_payment); $j++) {
            $result['Name'] = clearCmpName($db_payment[$j]['dname']) ;
            $result['no'] = ($db_payment[$j]['vBankAccountHolderName'] != "") ? clearCmpName($db_payment[$j]['vBankAccountHolderName']) : '---';
            $result['bank_name'] = ($db_payment[$j]['vBankName'] != "") ? $db_payment[$j]['vBankName'] : '---';
            $result['AccountNumber'] = ($db_payment[$j]['vAccountNumber'] != "") ? clearCmpName(" ".$db_payment[$j]['vAccountNumber']) : '---';
            $result['code'] = ($db_payment[$j]['vBIC_SWIFT_Code'] != "") ? clearCmpName($db_payment[$j]['vBIC_SWIFT_Code']) : '---';
            $result['fare'] = formateNumAsPerCurrency($db_payment[$j]['totalFare'],'');
            if (in_array(1, $enableCashReceivedCol)) {
                $result['cash_received'] = formateNumAsPerCurrency($db_payment[$j]['totalCashReceived'],'');
            } if (in_array(1, $enableTipCol)) {
                $result['tip_payment'] = formateNumAsPerCurrency($db_payment[$j]['tipPayment'],'');
            } if (in_array(1, $enableCashReceivedCol)) {
                $result['cash_payment'] = formateNumAsPerCurrency($db_payment[$j]['cashPayment'],'');
            }
            $result['job_amount'] = formateNumAsPerCurrency($db_payment[$j]['cardPayment'],'');
            $result['tax_amount'] = formateNumAsPerCurrency($db_payment[$j]['totalTaxAmt'],'');
            $result['outstanding'] = formateNumAsPerCurrency($db_payment[$j]['tripoutstandingAmount'],'');

            if ($hotelPanel > 0 || $kioskPanel > 0) {
                $result['bookingfees'] = formateNumAsPerCurrency($db_payment[$j]['bookingfees'],'');
            }
            // if ($db_payment[$j]['transferAmount'] > 0) {
                $result['transferAmount'] = formateNumAsPerCurrency($db_payment[$j]['transferAmount'],'');
            // } else {
            //     $result['transferAmount'] = "---" . "\t";
            // }

            // if ($db_payment[$j]['transferAmount'] >= 0) {
            //     $result['amount_back_from_service_provider'] = "---" . "\t";
            // } else {
                $result['amount_back_from_service_provider'] = formateNumAsPerCurrency(abs($db_payment[$j]['transferAmount']),'') ;
            // }

            if ($db_payment[$j]['eDriverPaymentStatus'] == "Unsettelled") {
                $result['status'] = "Unsettled";
            } else {
                $result['status'] = $db_trip[$j]['eDriverPaymentStatus'] ;
            }	
        			$sheet->setCellValue('A' . $i, $result['Name']);
                    $sheet->setCellValue('B' . $i, $result['no']);
                    $sheet->setCellValue('C' . $i, $result['bank_name']);
                    $sheet->setCellValue('D' . $i, $result['AccountNumber']);
                    $sheet->setCellValue('E' . $i, $result['code']);
                    $sheet->setCellValue('F' . $i, $result['fare']);
                    $sheet->setCellValue('G' . $i, $result['cash_received']);
                    $sheet->setCellValue('H' . $i, $result['tip_payment']);
                    $sheet->setCellValue('I' . $i, $result['cash_payment']);
                    $sheet->setCellValue('J' . $i, $result['job_amount']);
                    $sheet->setCellValue('K' . $i, $result['tax_amount']);
                    $sheet->setCellValue('L' . $i, $result['outstanding']);
                    $sheet->setCellValue('M' . $i, $result['bookingfees']);
                    $sheet->setCellValue('N' . $i, $result['transferAmount']);
                    $sheet->setCellValue('O' . $i, $result['amount_back_from_service_provider']);
                    $sheet->setCellValue('P' . $i, $result['status']);
                    $i++;   
        }
		$i +=1;
        $summary_array = array(
            'Total Fare' => formateNumAsPerCurrency($tot_fareAllTemp, ''),
            'Total Cash Received' => formateNumAsPerCurrency($totalCashReceivedAllTemp, ''),
            'Total Tip Amount Pay to Provider' => formateNumAsPerCurrency($tipPaymentAllTemp, ''),
            'Total Trip/Job Commission Take From Provider For Cash Trips/Jobs' => formateNumAsPerCurrency($cashPaymentAllTemp, ''),
            'Total Trip/Job Amount Pay to Provider For Card Trips/Jobs' =>formateNumAsPerCurrency($cardPaymentAllTemp, ''),
            'Total Tax Amount Take From Provider For Cash Trips/Jobs' => formateNumAsPerCurrency($totTaxAmtAllTemp, ''),
            'Total Trip/Job Outstanding Amount Take From Provider For Cash Trips/Jobs' => formateNumAsPerCurrency($tripoutstandingAmountAllTemp, ''),
            'Total Trip/Job Booking Fee Take From Provider For Cash Trips/Jobs' => formateNumAsPerCurrency($bookingfeesAllTemp, ''),
            'Final Amount Pay to Provider' => formateNumAsPerCurrency($transferAmountAllTemp, ''),
            'Final Amount to take back from Provider' => formateNumAsPerCurrency($transferAmountTakeBackAllTemp, '')
        );
        foreach ($summary_array as $key => $value) {
            $sheet->setCellValue('O' . $i, $key);
            $sheet->setCellValue('P' . $i, $value);
            $i++;
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

