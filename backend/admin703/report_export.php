<?php
include_once('../common.php');
$section = isset($_REQUEST['section'])?$_REQUEST['section']:'';
$sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
$order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
$startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
$endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
$iCompanyId = isset($_REQUEST['searchCompany'])?$_REQUEST['searchCompany']:'';
$iDriverId = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
$iUserId = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
$serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
$vTripPaymentMode = isset($_REQUEST['searchPaymentType'])?$_REQUEST['searchPaymentType']:'';
$eDriverPaymentStatus = isset($_REQUEST['searchDriverPayment'])?$_REQUEST['searchDriverPayment']:'';
$promocode = isset($_REQUEST['promocode'])?$_REQUEST['promocode']:'';
$ssql = $header = "";
$time = time();
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');
// require('fpdf/fpdf.php');
$date = new DateTime();
$timestamp_filename = $date->getTimestamp();
function cleanData(&$str)
{
    $str = preg_replace("/\t/","\\t",$str);
    $str = preg_replace("/\r?\n/","\\n",$str);
    if (strstr($str,'"')){
        $str = '"'.str_replace('"','""',$str).'"';
    }
}

if ($section == 'outstanding_amount'){

    function cleanNumber($num)
    {
        return str_replace(',','',$num);
    }

    //data for select fields
    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY tr.iTripId DESC';
    if ($sortby == 1){

        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 2){

        if ($order == 0){
            $ord = " ORDER BY tr.iFare ASC, o.fSubTotal ASC";
        }else{
            $ord = " ORDER BY tr.iFare DESC, o.fSubTotal DESC";
        }
    }
    if ($sortby == 3){

        if ($order == 0){
            $ord = " ORDER BY toa.fPendingAmount ASC";
        }else{
            $ord = " ORDER BY toa.fPendingAmount DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $ssqlsearchSettle = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    $searchSettleUnsettle = isset($_REQUEST['searchSettleUnsettle'])?$_REQUEST['searchSettleUnsettle']:'';
    $searchSettleUnsettlePagination = $searchSettleUnsettle;
    $ssql = '';
    if ($searchSettleUnsettle == '1'){
        $ssql1 = " AND toa.ePaidByPassenger ='No' ";
    }else if ($searchSettleUnsettle == '0'){
        $ssql1 = " AND toa.ePaidByPassenger ='Yes' ";
    }
    if ($action == 'search'){
        if ($searchRider != ''){
            $ssql .= " AND toa.iUserId ='".$searchRider."'";
        }
    }
    $trp_ssql = " ORDER BY riderName ASC";
    if ($searchSettleUnsettle == '1'){
        $sql = "SELECT toa.*,concat(ru.vName,' ',ru.vLastName) as riderName,ru.vEmail,CONCAT('(+',ru.vPhoneCode,')',ru.vPhone) as userphone,ru.vPhoneCode, ru.vPhone, ru.vCurrencyPassenger as userCurrency, cur.Ratio as currencyRatio, toa.iUserId, SUM(toa.fPendingAmount) as allSum, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId AND toa1.vTripPaymentMode != 'Organization' AND toa1.ePaidByPassenger ='No' AND toa1.iUserId != '') as Remaining, concat(ru.vName,' ',ru.vLastName) as riderName from trip_outstanding_amount AS toa LEFT JOIN register_user AS ru ON toa.iUserId = ru.iUserId LEFT JOIN currency as cur ON cur.vName=ru.vCurrencyPassenger WHERE toa.iUserId > 0 AND toa.iUserId != '' AND toa.vTripPaymentMode != 'Organization' AND ru.vName!='NULL' $ssql GROUP BY toa.iUserId   HAVING remaining >0 $trp_ssql";
    }else if ($searchSettleUnsettle == '0'){
        $sql = "SELECT toa.*,concat(ru.vName,' ',ru.vLastName) as riderName,ru.vEmail,CONCAT('(+',ru.vPhoneCode,')',ru.vPhone) as userphone,ru.vPhoneCode, ru.vPhone, ru.vCurrencyPassenger as userCurrency,cur.Ratio as currencyRatio, SUM(toa.fPendingAmount) AS allSum, (SUM(toa.fPendingAmount)-(SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId AND toa1.vTripPaymentMode != 'Organization' AND toa1.ePaidByPassenger ='Yes' AND toa1.iUserId != ''))as Remaining, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId AND toa1.vTripPaymentMode != 'Organization' AND toa1.ePaidByPassenger ='Yes' AND toa1.iUserId != '') as PaidData,concat(ru.vName,' ',ru.vLastName) as riderName from trip_outstanding_amount AS toa LEFT JOIN register_user AS ru ON toa.iUserId = ru.iUserId LEFT JOIN currency as cur ON cur.vName=ru.vCurrencyPassenger WHERE toa.iUserId > 0 AND toa.iUserId != '' AND toa.vTripPaymentMode != 'Organization' AND ru.vName!='NULL' $ssql GROUP BY toa.iUserId HAVING allSum=PaidData $trp_ssql";
    }else{
        $sql = "SELECT toa.iUserId,COUNT(toa.iTripOutstandId) AS Total,ru.vEmail,CONCAT('(+',ru.vPhoneCode,')',ru.vPhone) as userphone,ru.vPhoneCode, ru.vPhone, ru.vCurrencyPassenger as userCurrency,cur.Ratio as currencyRatio, SUM(toa.fPendingAmount) AS allSum,(SUM(toa.fPendingAmount)-(SELECT (CASE WHEN ISNULL(SUM(toa1.fPendingAmount)) THEN 0 ELSE SUM(toa1.fPendingAmount) END) FROM trip_outstanding_amount as toa1 WHERE toa1.iUserId=toa.iUserId AND toa1.vTripPaymentMode != 'Organization' AND toa1.ePaidByPassenger ='Yes' AND toa1.iUserId != '')) as Remaining, concat(ru.vName,' ',ru.vLastName) as riderName from trip_outstanding_amount AS toa LEFT JOIN register_user AS ru ON toa.iUserId = ru.iUserId LEFT JOIN orders AS o ON o.iOrderId = toa.iOrderId  LEFT JOIN trips AS tr ON tr.iTripId = toa.iTripId LEFT JOIN currency as cur ON cur.vName=ru.vCurrencyPassenger WHERE toa.iUserId > 0 AND toa.iUserId != '' AND ru.vName != '' AND ru.vName != 'NULL' AND toa.vTripPaymentMode != 'Organization' $ssql1 $ssql GROUP BY toa.iUserId  $ssql  $trp_ssql";
    }
    $db_trip = $obj->MySQLSelect($sql) or die('Query failed!');
    $var_filter = "";
    foreach ($_REQUEST as $key => $val){

        if (($key != "tpages") && ($key != 'page') && ($key == 'searchSettleUnsettle') || ($key == 'action')){
            $var_filter .= "&$key=".stripslashes($val);
        }
    }
    $header .= "User Name"."\t";
    //$header .= "Total Amount" . "\t";
    //$header .= "Total Outstanding Amount" . "\t";
    $header .= "Outstanding Amount"."\t";
    $flag = false;
    $filename = "user_outstanding_report.xls";
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',"Contact Details");
    $sheet->setCellValue('B1',"Contact Number");
    $sheet->setCellValue('C1',"Outstanding Amount");
    $sheet->setCellValue('D1',"Total");
    $j = 2;
    if (scount($db_trip) > 0){

        $AllTotalprice = 0;
        $AllTotalPending = $AllTotalRemainingPending = $AllTotalRemainingPendingAll = 0;
        for ($i = 0;$i < scount($db_trip);$i++){

            // $fPendingAmount = $db_trip[$i]['allSum'];
            $fPendingAmount = ($db_trip[$i]['allSum'] / $db_trip[$i]['currencyRatio']);
            $remainingPendingAmount = ($db_trip[$i]['Remaining'] != '')?($db_trip[$i]['Remaining'] / $db_trip[$i]['currencyRatio']):'0';
            $userCurrency = $db_trip[$i]['userCurrency'];
            $AllTotalPending += $fPendingAmount;
            $AllTotalRemainingPending += $remainingPendingAmount;
            $vEmail = !empty($db_trip[$i]['vEmail'])?" Email:".clearEmail($db_trip[$i]['vEmail']):'';
            $userphone = !empty(clearPhone($db_trip[$i]['vPhone']))?"(+".($db_trip[$i]["vPhoneCode"]).") ".clearPhone($db_trip[$i]["vPhone"]):'';
            $data['riderName'] = $db_trip[$i]['riderName'].$vEmail;
            //$data .= formateNumAsPerCurrency(cleanNumber($fPendingAmount),'') . "\t";
            $data['Outstanding'] = formateNumAsPerCurrency(cleanNumber($remainingPendingAmount),'');
            $data['Total'] = formateNumAsPerCurrency(cleanNumber($AllTotalRemainingPending),'');
            $fPendingAmountAll = $db_trip[$i]['allSum'];
            //$remainingPendingAmount = ($db_trip[$i]['Remaining'] != '') ? ($db_trip[$i]['Remaining'] / $db_trip[$i]['currencyRatio'] ) :'0';
            $remainingPendingAmountAll = $db_trip[$i]['Remaining'];
            $AllTotalPendingAll += $fPendingAmountAll;
            $AllTotalRemainingPendingAll += $remainingPendingAmountAll;
            $sheet->setCellValue('A'.$j,$data['riderName']);
            $sheet->setCellValue('B'.$j,$userphone);
            $sheet->setCellValue('C'.$j,$data['Outstanding']);
            $sheet->setCellValue('D'.$j,$data['Total']);
            $j++;
        }
        //$data .= formateNumAsPerCurrency(cleanNumber($totalAllFare),'') . "\t";
        //$data .= formateNumAsPerCurrency(cleanNumber($AllTotalPending),'') . "\t";
    }
    $j += 1;
    $Summary_array = array("Outstanding Amount " => formateNumAsPerCurrency($AllTotalRemainingPendingAll,''));
    foreach ($Summary_array as $key => $value){
        $sheet->setCellValue('C'.$j,$key);
        $sheet->setCellValue('D'.$j,$value);
        $j++;
    }
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'org_outstanding_amount'){

    function cleanNumber($num)
    {
        return str_replace(',','',$num);
    }

    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $ssqlsearchSettle = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $searchOrganization = isset($_REQUEST['searchOrganization'])?$_REQUEST['searchOrganization']:'';
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    $searchSettleUnsettle = isset($_REQUEST['searchSettleUnsettle'])?$_REQUEST['searchSettleUnsettle']:'1';
    //$searchPaidby = $_REQUEST['searchPaidby'] ?? '';
    $searchPaidby = 'org';
    //echo $searchPaidby;exit;
    $searchSettleUnsettlePagination = $searchSettleUnsettle;
    $ssql = '';
    if ($searchPaidby == 'org'){
        if ($searchSettleUnsettle == '1'){
            //$ssql1 = " AND toa1.ePaidByOrganization ='No' ";
            $ssql1 = "AND (toa1.eBillGenerated ='No' AND toa1.ePaidByOrganization ='No')";
        }else if ($searchSettleUnsettle == '0'){
            //$ssql1 = " AND toa1.ePaidByOrganization ='Yes' ";
            $ssql1 = "AND (toa1.eBillGenerated ='Yes' OR toa1.ePaidByOrganization ='Yes')";
        }else if ($searchSettleUnsettle == '-1'){
            //$ssql1 = " AND toa1.ePaidByOrganization ='Yes' ";
            $ssql1 = "AND (toa1.eBillGenerated ='Yes' OR toa1.ePaidByOrganization ='Yes') ";
        }
    }else{
        if ($searchSettleUnsettle == '1'){
            $ssql1 = "AND toa1.ePaidByPassenger ='No' ";
        }else if ($searchSettleUnsettle == '0'){
            $ssql1 = "AND toa1.ePaidByPassenger ='Yes' ";
        }else if ($searchSettleUnsettle == '-1'){
            $ssql1 = "AND toa1.ePaidByPassenger ='Yes' ";
        }
    }
    if ($searchOrganization != ''){
        $ssql .= "AND toa.iOrganizationId ='".$searchOrganization."'";
    }
    $sqlPaidby = $sqlPaidbysub = '';
    if ($searchPaidby == 'org'){
        $sqlPaidbysub = "AND toa1.vTripPaymentMode = 'Organization'";
        $sqlPaidby = "AND toa.vTripPaymentMode = 'Organization'";
    }else{
        $sqlPaidbysub = " AND toa1.vTripPaymentMode != 'Organization'";
        $sqlPaidby = "AND toa.vTripPaymentMode != 'Organization'";
    }
    $trp_ssql = "ORDER BY org.vCompany ASC";
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    if ($searchSettleUnsettle == '1'){
        $sql = "SELECT org.vCompany, toa.iOrganizationId,COUNT(toa.iTripOutstandId) AS Total, SUM(toa.fPendingAmount) as allSum, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '') as Remaining from trip_outstanding_amount AS toa LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId HAVING remaining>0 $trp_ssql";
    }else if ($searchSettleUnsettle == '0'){
        $sql = "SELECT org.vCompany, toa.iOrganizationId,COUNT(toa.iTripOutstandId) AS Total,SUM(toa.fPendingAmount) AS allSum, (SUM(toa.fPendingAmount)-(SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != ''))as Remaining, (SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '') as PaidData from trip_outstanding_amount AS toa LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId HAVING allSum=PaidData $trp_ssql";
    }else{
        $sql = "SELECT org.vCompany, toa.iOrganizationId,COUNT(toa.iTripOutstandId) AS Total,SUM(toa.fPendingAmount) AS allSum, (SUM(toa.fPendingAmount)-(SELECT (CASE WHEN ISNULL(SUM(toa1.fPendingAmount)) THEN 0 ELSE SUM(toa1.fPendingAmount) END) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '')) as Remaining from trip_outstanding_amount AS toa LEFT JOIN trips AS tr ON tr.iTripId = toa.iTripId LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby GROUP BY toa.iOrganizationId ORDER BY org.vCompany ASC";
    }
    $db_trip = $obj->MySQLSelect($sql);
    $var_filter = "";
    foreach ($_REQUEST as $key => $val){

        if (($key != "tpages") && ($key != 'page') && ($key == 'searchSettleUnsettle') || ($key == 'action')){
            $var_filter .= "&$key=".stripslashes($val);
        }
    }
    $header .= "Organization Name"."\t";
    //$header .= "Total Amount" . "\t";
    //$header .= "Total Outstanding Amount" . "\t";
    $header .= "Outstanding Amount"."\t";
    if (scount($db_trip) > 0){
        $AllTotalprice = 0;
        $AllTotalPending = $AllTotalRemainingPending = 0;
        for ($i = 0;$i < scount($db_trip);$i++){

            $fPendingAmount = $db_trip[$i]['allSum'];
            //$fPendingAmount = ($db_trip[$i]['allSum'] / $db_trip[$i]['currencyRatio']);
            //$remainingPendingAmount = ($db_trip[$i]['Remaining'] != '') ? ($db_trip[$i]['Remaining'] / $db_trip[$i]['currencyRatio'] ) :'0';
            $remainingPendingAmount = $db_trip[$i]['Remaining'] != ''?$db_trip[$i]['Remaining']:'0';
            $AllTotalPending += $fPendingAmount;
            $AllTotalRemainingPending += $remainingPendingAmount;
            $data .= $db_trip[$i]['vCompany']."\t";
            //$data .= formateNumAsPerCurrency(cleanNumber($fPendingAmount),'') . "\t";
            $data .= formateNumAsPerCurrency(cleanNumber($remainingPendingAmount),'')."\n";
        }
        $data .= "\n";
        $data .= "TOTAL"."\t";
        //$data .= formateNumAsPerCurrency(cleanNumber($totalAllFare),'') . "\t";
        //$data .= formateNumAsPerCurrency(cleanNumber($AllTotalPending),'') . "\t";
        $data .= formateNumAsPerCurrency(cleanNumber($AllTotalRemainingPending),'')."\t";
    }
    $data = str_replace("\r","",$data);
    ob_clean();
    header("Content-type: application/octet-stream; charset=utf-8");
    header("Content-Disposition: attachment; filename=outstanding_amount_reports.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
function makeCell(array $CELL_NAME_ARR,$sheet,$cellRowNumber = 1)
:array
{
    $columnIndex = 'A';
    foreach ($CELL_NAME_ARR as $key => $value){
        $sheet->setCellValue($columnIndex.$cellRowNumber,$value);
        $columnIndex++;
    }
    return array($key,$value);
}

if ($section == 'driver_payment'){
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    $ord = ' ORDER BY tr.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY tr.tTripRequestDate ASC";
        }else{
            $ord = " ORDER BY tr.tTripRequestDate DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY u.vName ASC";
        }else{
            $ord = " ORDER BY u.vName DESC";
        }
    }
    if ($sortby == 6){
        if ($order == 0){
            $ord = " ORDER BY tr.eType ASC";
        }else{
            $ord = " ORDER BY tr.eType DESC";
        }
    }
    $ssql = "";
    if ($startDate != ''){
        $ssql .= " AND Date(tTripRequestDate) >='".$startDate."'";
    }
    if ($endDate != ''){
        $ssql .= " AND Date(tTripRequestDate) <='".$endDate."'";
    }
    if ($serachTripNo != ''){
        $ssql .= " AND tr.vRideNo ='".$serachTripNo."'";
    }
    if ($iCompanyId != ''){
        $ssql .= " AND rd.iCompanyId = '".$iCompanyId."'";
    }
    if ($iDriverId != ''){
        $ssql .= " AND tr.iDriverId = '".$iDriverId."'";
    }
    if ($iUserId != ''){
        $ssql .= " AND tr.iUserId = '".$iUserId."'";
    }
    if ($vTripPaymentMode != ''){
        $ssql .= " AND tr.vTripPaymentMode = '".$vTripPaymentMode."'";
    }
    if ($eDriverPaymentStatus != ''){
        $ssql .= " AND tr.eDriverPaymentStatus = '".$eDriverPaymentStatus."'";
    }
    if ($eType != ''){
        if ($eType == 'Fly'){
            $ssql .= " AND tr.iFromStationId > 0 AND tr.iToStationId > 0";
        }else if ($eType == 'Ride'){
            $ssql .= " AND tr.eType ='".$eType."' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' AND  tr.iFromStationId = 0 AND tr.iToStationId = 0 ";
        }elseif ($eType == 'InterCity'){
            $ssql .= " AND tr.eType ='Ride' AND tr.eIsInterCity = 'Yes'";
        }elseif ($eType == 'RentalRide'){
            $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
        }elseif ($eType == 'HailRide'){
            $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
        }else if ($eType == "Pool"){
            $ssql .= " AND tr.eType ='Ride' AND tr.ePoolRide = 'Yes'";
        }else{
            $ssql .= " AND tr.eType ='".$eType."' ";
        }
    }
    $ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable()?"Yes":"No"; //add function to modules availibility
    $rideEnable = $MODULES_OBJ->isRideFeatureAvailable()?"Yes":"No";
    $deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable()?"Yes":"No";
    $deliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable()?"Yes":"No";
    if ($ufxEnable != "Yes"){
        $ssql .= " AND tr.eType != 'UberX'";
    }
    if (!$MODULES_OBJ->isAirFlightModuleAvailable()){
        $ssql .= " AND tr.iFromStationId = '0' AND tr.iToStationId = '0'";
    }
    if ($rideEnable != "Yes" && $deliverallEnable != "Yes"){
        $ssql .= " AND tr.eType != 'Ride'";
    }
    if ($deliveryEnable != "Yes"){
        $ssql .= " AND tr.eType != 'Deliver' AND tr.eType != 'Multi-Delivery'";
    }
    //global $userObj;
    $locations_where = "";
    if (scount($userObj->locations) > 0){
        $locations = implode(', ',$userObj->locations);
        $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE tr.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And tr.tTripRequestDate > '".WEEK_DATE."'";
    }
    $db_organization = $obj->MySQLSelect("SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany");
    $orgNameArr = array();
    for ($g = 0;$g < scount($db_organization);$g++){
        $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];
    }
    $etypeSql = " AND tr.eSystem = 'General'";
    if ($deliverallEnable == "Yes"){
        $etypeSql = " AND (tr.eSystem = 'General' OR tr.eSystem = 'DeliverAll') AND tr.iServiceId = '0'";
    }
    $sql = "SELECT tr.fCancellationFare,tr.eIsInterCity,tr.tStartDate,tr.tEndDate,tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.iFare, tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eDriverPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE (tr.iActive ='Finished' OR (tr.iActive ='Canceled' AND tr.iFare > 0) OR (tr.iActive ='Canceled' AND tr.fWalletDebit > 0 AND tr.iFare = 0)) $etypeSql $ssql $trp_ssql $ord";
    //echo $sql;die;
    $db_trip = $obj->MySQLSelect($sql);
    $driver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_ifare = $tot_tax = 0.00;
    //Added By HJ On 08-08-2019 For Get Driver Wallet Debit Amount Start As Per Discuss With KS Sir
    $tripWalletArr = array();
    $getWalletData = $obj->MySQLSelect("SELECT iBalance,iTripId FROM user_wallet WHERE eType ='Debit' AND iTripId > 0");
    for ($d = 0;$d < scount($getWalletData);$d++){
        $tripWalletArr[$getWalletData[$d]['iTripId']] = $getWalletData[$d]['iBalance'];
    }
    //Added By HJ On 08-08-2019 For Get Driver Wallet Debit Amount End As Per Discuss With KS Sir
    //echo "<pre>";print_r($tripWalletArr);die;
    $enableCashReceivedCol = $enableTipCol = array();
    foreach ($db_trip as $dtps){
        $fTipPrice = $dtps['fTipPrice'];
        //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start
        if ($dtps['vTripPaymentMode'] == "Cash"){
            $enableCashReceivedCol[] = 1;
        }
        //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start
        if ($fTipPrice > 0){
            $enableTipCol[] = 1;
        }
    }
    $endRecord = scount($db_trip);
    $var_filter = "";
    foreach ($_REQUEST as $key => $val){
        if ($key != "tpages" && $key != 'page'){
            $var_filter .= "&$key=".stripslashes($val);
        }
    }
    $reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
    $Today = Date('Y-m-d');
    $tdate = date("d") - 1;
    $mdate = date("d");
    $Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - 1,date("Y")));
    $curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
    $curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
    $prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y") - 1));
    $prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y") - 1));
    $currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $tdate,date("Y")));
    $currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m") + 1,date("d") - $mdate,date("Y")));
    $prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m") - 1,date("d") - $tdate,date("Y")));
    $prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $mdate,date("Y")));
    $monday = date('Y-m-d',strtotime('sunday this week -1 week'));
    $sunday = date('Y-m-d',strtotime('saturday this week'));
    $Pmonday = date('Y-m-d',strtotime('sunday this week -2 week'));
    $Psunday = date('Y-m-d',strtotime('saturday this week -1 week'));
    $generalConfigPaymentArr = $CONFIG_OBJ->getGeneralVarAll_Payment_Array();
    //echo "<pre>";print_r($generalConfigPaymentArr);die;
    $SYSTEM_PAYMENT_FLOW = "Method-1";
    if (isset($generalConfigPaymentArr['SYSTEM_PAYMENT_FLOW'])){
        $SYSTEM_PAYMENT_FLOW = $generalConfigPaymentArr['SYSTEM_PAYMENT_FLOW'];
    }
    $ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
    $hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
    $kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');
    /*if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
        $header .= $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN'] . "\t";
    }*/
    $nxtChar = "B";
    if (in_array(1,$enableCashReceivedCol)){
        $lbl['Cash_Received'] = $nxtChar."=Cash Received";
        $nxtChar = "C";
    }
    $lbl['Commission_Amount'] = $nxtChar."=Commission Amount";
    $nxtChar = "C";
    if ($nxtChar == "C"){
        $nxtChar = "D";
    }
    $lbl['Total_Tax'] = $nxtChar."=Total Tax";
    $nxtChar = "D";
    if ($nxtChar == "D"){
        $nxtChar = "E";
    }
    if (in_array(1,$enableTipCol)){
        $tipAmt = $nxtChar;
        $lbl['Tip'] = $nxtChar."=Tip";
    }
    $nxtChar = "E";
    if ($nxtChar == "E"){
        $nxtChar = "F";
    }
    $outAmt = $nxtChar;
    $lbl['Outstanding_Amount'] = $nxtChar."=".$langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Outstanding Amount";
    $nxtChar = "F";
    if ($nxtChar == "F"){
        $nxtChar = "G";
    }
    $bookAmt = "";
    if ($hotelPanel > 0 || $kioskPanel > 0){
        $bookAmt = $nxtChar;
    }
    $lbl['Booking_Fees'] = $nxtChar."=Booking Fees";
    $nxtChar = "G";
    if ($nxtChar == "G"){
        $nxtChar = "H";
    }
    $ppAmt = $nxtChar;
    //added by SP for changes as per the report on 28-06-2019 end
    $flag = false;
    $db_trip = $obj->MySQLSelect($sql) or die('Query failed!');
    $filename = "trips_payment_report_".$timestamp_filename.'.xls';
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    /*$sheet->setCellValue('A1',$langage_lbl_admin['LBL_RIDE_NO_ADMIN']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']);
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'] . " Date");
        $sheet->setCellValue('F1', 'A=Total Fare');
        $sheet->setCellValue('G1', $lbl['Cash_Received']);
        $sheet->setCellValue('H1', $lbl['Commission_Amount']);
        $sheet->setCellValue('I1', $lbl['Total_Tax']);
        $sheet->setCellValue('J1', $lbl['Tip']);
        $sheet->setCellValue('K1', $lbl['Outstanding_Amount']);
        $sheet->setCellValue('L1', $lbl['Booking_Fees']);
        $sheet->setCellValue('M1', $nxtChar . "=" . $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " pay / Take Amount");
        $sheet->setCellValue('N1', 'Site Earning');
        $sheet->setCellValue('O1', $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . " Status");
        $sheet->setCellValue('P1', 'Payment method');
    $sheet->setCellValue('Q1',$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Payment Status");*/
    /*------------------make dynamic cell from array-----------------*/
    $CELL_NAME_ARR = [];
    $CELL_NAME_ARR['RIDE_NO'] = $langage_lbl_admin['LBL_RIDE_NO_ADMIN'];
    $CELL_NAME_ARR['TRIP_TYPE'] = $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN'];
    $CELL_NAME_ARR['DRIVER'] = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
    $CELL_NAME_ARR['RIDER'] = $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'];
    $CELL_NAME_ARR['TRIP_DATE'] = $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Date";
    $CELL_NAME_ARR['TOTAL_FARE'] = "A=Total Fare";
    $CELL_NAME_ARR['CASH_RECEIVED'] = $lbl['Cash_Received'];
    $CELL_NAME_ARR['COMMISSION_AMOUNT'] = $lbl['Commission_Amount'];
    $CELL_NAME_ARR['TOTAL_TAX'] = $lbl['Total_Tax'];
    if (in_array(1,$enableTipCol)){
        $CELL_NAME_ARR['TIP'] = $lbl['Tip'];
    }
    $CELL_NAME_ARR['OUTSTANDING_AMOUNT'] = $lbl['Outstanding_Amount'];
    $CELL_NAME_ARR['BOOKING_FEES'] = $lbl['Booking_Fees'];
    $CELL_NAME_ARR['DRIVER_PAY_AMOUNT'] = $nxtChar."=".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." pay / Take Amount";
    $CELL_NAME_ARR['SITE_EARNING'] = 'Site Earning';
    $CELL_NAME_ARR['RIDE_STATUS'] = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN']." Status";
    $CELL_NAME_ARR['PAYMENT_METHOD'] = 'Payment method';
    $CELL_NAME_ARR['DRIVER_PAYMENT_STATUS'] = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Payment Status";
    makeCell($CELL_NAME_ARR,$sheet);
    /*------------------make dynamic cell from array-----------------*/
    $j = 2;
    $driver_payment = $total_tip = $tot_fare = $tot_site_commission = $tot_hotel_commision = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_ifare = $tot_tax = $totSiteEarning = $TotaliNewFare = 0.00;
    for ($i = 0;$i < scount($db_trip);$i++){
        if ($db_trip[$i]['eDriverPaymentStatus'] == 'Unsettelled'){
            $db_trip[$i]['eDriverPaymentStatus'] = 'Unsettled';
        }else if ($db_trip[$i]['eDriverPaymentStatus'] == 'Settelled'){
            $db_trip[$i]['eDriverPaymentStatus'] = 'Settled';
        }
        $iTripId = $db_trip[$i]['iTripId'];
        //echo "<pre>";print_r($db_trip);die;
        $iFare = $iFareOrg = setTwoDecimalPoint($db_trip[$i]['iFare']);
        $totTax = setTwoDecimalPoint($db_trip[$i]['fTax1'] + $db_trip[$i]['fTax2']);
        $orgName = "";
        if (isset($orgNameArr[$db_trip[$i]['iOrganizationId']]) && $orgNameArr[$db_trip[$i]['iOrganizationId']] != ""){
            $orgName = "(".$orgNameArr[$db_trip[$i]['iOrganizationId']].")";
            $iFare = 0;
        }
        $poolTxt = "";
        if ($db_trip[$i]['ePoolRide'] == "Yes"){
            $poolTxt = " (Pool)";
        }
        $totalfare = setTwoDecimalPoint($db_trip[$i]['fTripGenerateFare']);
        $site_commission = setTwoDecimalPoint($db_trip[$i]['fCommision']);
        $promocodediscount = setTwoDecimalPoint($db_trip[$i]['fDiscount']);
        $wallentPayment = setTwoDecimalPoint($db_trip[$i]['fWalletDebit']);
        $fOutStandingAmount = setTwoDecimalPoint($db_trip[$i]['fOutStandingAmount']);
        $fHotelCommision = setTwoDecimalPoint($db_trip[$i]['fHotelCommision']);
        $fTipPrice = setTwoDecimalPoint($db_trip[$i]['fTipPrice']);
        $tipPayment = 0;
        $siteEarning = $site_commission + $totTax + $fOutStandingAmount + $fHotelCommision; // Added By HJ On 26-09-2020 As Per Discuss With KS Sir = Total = C+D+F+G
        //echo $iTripId."===>"."(" . $totalfare . "+" . $fTipPrice . ")-(" . $site_commission . "+" . $totTax . "+" . $fOutStandingAmount . "+" . $fHotelCommision ."+".$iFare. ")<br>";
        //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start
        $driver_payment = $dispay_driver_payment = setTwoDecimalPoint(($totalfare + $fTipPrice) - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision));
        if ($db_trip[$i]['vTripPaymentMode'] == "Cash"){
            $driver_payment = $dispay_driver_payment = setTwoDecimalPoint($driver_payment - $iFare);
            $tot_ifare += $iFare;
        }
        //echo "<pre>";print_r($db_trip);die;
        //Added By HJ On 26-09-2020 For Display Canceled Trip Calculation As Per Discuss With KS sir Start
        if ($db_trip[$i]['iActive'] == "Canceled"){
            $iFare = $db_trip[$i]['fCancellationFare'] - $iFare;
            $driver_payment = $iFareOrg - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision);
            if ($db_trip[$i]['vTripPaymentMode'] == "Cash" || $db_trip[$i]['vTripPaymentMode'] == "Organization"){
                $iFare = 0;
                $driver_payment = ($iFareOrg + $wallentPayment) - ($site_commission + $totTax + $fOutStandingAmount + $fHotelCommision);
                $driver_payment = $dispay_driver_payment = setTwoDecimalPoint($driver_payment - $iFare);
            }
        }
        //Added By HJ On 26-09-2020 For Display Canceled Trip Calculation As Per Discuss With KS sir End
        //echo $iTripId . "===>" .$driver_payment."<br>";
        //Added By HJ On 08-08-2019 For Check Driver Wallet Debit Amount Start As Per Discuss With KS Sir
        $driverDebitAmt = 0;
        if (isset($tripWalletArr[$iTripId]) && $tripWalletArr[$iTripId] > 0){
            $driverDebitAmt = setTwoDecimalPoint($tripWalletArr[$iTripId]);
            //echo $driverDebitAmt."+".$driver_payment."<br>";die;
            //$driver_payment = $dispay_driver_payment = setTwoDecimalPoint($driverDebitAmt + $driver_payment);
            ///echo setTwoDecimalPoint($driver_payment);die;
        }
        //echo $iTripId . "===>" .$driver_payment."<br>";
        //Added By HJ On 08-08-2019 For Check Driver Wallet Debit Amount End As Per Discuss With KS End
        //Added By HJ On 25-05-2019 As Per Discuss With KS Also Given Confirmation After Checked By Her Start
        $set_unsetarray[] = $db_trip[$i]['eDriverPaymentStatus'];
        $eTypenew = $db_trip[$i]['eType'];
        if ($eTypenew == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eTypenew == 'UberX'){
            $trip_type = 'Other Services';
        }else{
            $trip_type = 'Delivery';
        }
        if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])){
            $trip_type = 'Fly';
        }
        $trip_type .= $poolTxt;
        if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0){
            $tripTypeTxt = "Rental ".$trip_type."<br/> ( Hail )";
        }else if ($db_trip[$i]['eIsInterCity'] == 'Yes'){
            $tripTypeTxt = "InterCity ".$trip_type;
        }else if ($db_trip[$i]['iRentalPackageId'] > 0){
            $tripTypeTxt = "Rental ".$trip_type;
        }else if ($db_trip[$i]['eHailTrip'] == "Yes"){
            $tripTypeTxt = "Hail ".$trip_type;
        }else{
            $tripTypeTxt = $trip_type;
        }
        $tot_fare += $totalfare;
        $tot_site_commission += $site_commission;
        $tot_hotel_commision += $fHotelCommision;
        $tot_promo_discount += $promocodediscount;
        $tot_wallentPayment += $wallentPayment;
        $tot_tax += $totTax;
        $total_tip += $fTipPrice;
        $tot_driver_refund += $dispay_driver_payment;
        $totSiteEarning += $siteEarning; // Added By HJ On 26-09-2020 As Per Discuss With KS Sir = Total = C+D+F+G
        //echo $iTripId . "===>" .$driver_payment."<br>";
        $tot_outstandingAmount += $fOutStandingAmount;
        $data .= $db_trip[$i]['vRideNo']."\t".$tripTypeTxt."\t";
        $vRideNo_text = $db_trip[$i]['vRideNo']." (".$tripTypeTxt.")";
        //echo $tot_fare;
        //if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
        //    if ($db_trip[$j]['eHailTrip'] == "Yes" && $db_trip[$j]['iRentalPackageId'] > 0) {
        //        //$data .= "Rental " . $trip_type . " ( Hail )" . "\t";
        //        $data .= $db_trip[$j]['vRideNo'] . "  " . "Rental " . $trip_type . " ( Hail )" . "\t";
        //    } else if ($db_trip[$j]['iRentalPackageId'] > 0) {
        //        //$data .= "Rental " . $trip_type . "\t";
        //        $data .= $db_trip[$j]['vRideNo'] . "  " . "Rental " . $trip_type . "\t";
        //    } else if ($db_trip[$j]['eHailTrip'] == "Yes") {
        //        //$data .= "Hail " . $trip_type . "\t";
        //        $data .= $db_trip[$j]['vRideNo'] . "  " . "Hail " . $trip_type . "\t";
        //    } else {
        //        $data .= $db_trip[$j]['vRideNo'] . "  " . $trip_type . "\t";
        //    }
        //} else {
        //    $data .= $db_trip[$j]['vRideNo'] . "  " . $trip_type . "\t";
        //}
        //$data .= $db_trip[$j]['vRideNo'] . "\t";
        //$data .= clearName($db_trip[$i]['drivername']) . "\t";
        //$data .= clearName($db_trip[$i]['riderName']) . "\t";
        $systemTimeZone = date_default_timezone_get();
        //$db_trip[$i]['tTripRequestDate'] = converToTz($db_trip[$i]['tTripRequestDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone);
        if ($db_trip[$i]['fCancellationFare'] > 0 && $db_trip[$i]['vTimeZone'] != ""){
            $dBookingDate = converToTz($db_trip[$i]['tEndDate'],$db_trip[$i]['vTimeZone'],$systemTimeZone);
        }else if ($db_trip[$i]['tStartDate'] != "" && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00" && $db_trip[$i]['vTimeZone'] != ""){
            $dBookingDate = $db_trip[$i]['tStartDate'];
        }else{
            if (!empty($db_trip[$i]['tStartDate']) && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00"){
                $dBookingDate = $db_trip[$i]['tStartDate'];
            }else{
                $dBookingDate = $db_trip[$i]['tTripRequestDate'];
            }
        }
        $date_format_data_array = array(
            'langCode' => $default_lang,
            'DateFormatForWeb' => 1
        );
        $date_format_data_array['tdate'] = (!empty($db_trip[$i]['vTimeZone']))?converToTz($dBookingDate,$db_trip[$i]['vTimeZone'],$systemTimeZone):$dBookingDate;
        $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
        $result['date'] = $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($db_trip[$i]['tTripRequestDate']) . "\t";
        // $data .= ($totalfare > 0) ? trip_currency($totalfare) . "\t" : "- \t";
        if ($db_trip[$i]['fTripGenerateFare'] != "" && $db_trip[$i]['fTripGenerateFare'] != 0){
            $totFareHtml = formateNumAsPerCurrency($db_trip[$i]['fTripGenerateFare'],'');
        }else{
            $totFareHtml = "-";
        }
        $result['tot_fare'] = $totFareHtml;
        if (in_array(1,$enableCashReceivedCol)){
            if ($db_trip[$i]['vTripPaymentMode'] != "Card"){
                //$data .= ($iFare > 0) ? trip_currency($iFare) . "\t" : "".trip_currency(0)." \t";
                $result['receivec'] = ($iFare > 0)?formateNumAsPerCurrency($iFare,'')."\t":"".trip_currency(0)." \t";
                //$TotaliNewFare = $TotaliNewFare + $totalfare;
            }else{
                $result['receivec'] = "$0.00";
            }
        }
        if ($db_trip[$i]['vTripPaymentMode'] == "Cash"){
            $TotaliNewFare = $TotaliNewFare + $totalfare;
        }
        //$data .= ($site_commission > 0) ? trip_currency($site_commission) . "\t" : "- \t";
        if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0){
            $result['fCommision'] = formateNumAsPerCurrency($db_trip[$i]['fCommision'],'');
        }else{
            $result['fCommision'] = "-";
        }
        //$data .= ($site_commission > 0) ? formateNumAsPerCurrency($site_commission,'') . "\t" : "- \t";
        //$data .= ($totTax > 0) ? trip_currency($totTax) . "\t" : "- \t";
        $result['tot_tax'] = ($totTax > 0)?formateNumAsPerCurrency($totTax,''):"0";
        //added by SP for changes as per the report on 28-06-2019 start
        //$data .= ($promocodediscount > 0) ? trip_currency($promocodediscount) . "\t" : "- \t";
        //$data .= ($wallentPayment > 0) ? trip_currency($wallentPayment) . "\t" : "- \t";
        //added by SP for changes as per the report on 28-06-2019 end
        if (in_array(1,$enableTipCol)){
            $result['total_tip'] = ($db_trip[$i]['fTipPrice'] != "0")?formateNumAsPerCurrency($db_trip[$i]['fTipPrice'],'')."\t":"0";
            //$data .= ($fTipPrice > 0) ? trip_currency($fTipPrice) . "\t" : "- \t";
        }
        //$data .= ($fOutStandingAmount > 0) ? trip_currency($fOutStandingAmount) . "\t" : "- \t";
        $result['fOutStandingAmount'] = ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0)?formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'],'')."\t":"0";
        /* if ($hotelPanel > 0 || $kioskPanel > 0) {
            echo $db_trip[$i]['fHotelCommision'];
            $result['total_tip'] = ($db_trip[$i]['fHotelCommision'] != "" && $db_trip[$i]['fHotelCommision'] != 0 && $db_trip[$i]['fTipPrice'] != "0") ? formateNumAsPerCurrency($db_trip[$i]['fHotelCommision'], '') : "0";
        } */
        $result['payment'] = ($driver_payment != "" && $driver_payment != 0)?formateNumAsPerCurrency($driver_payment,'')."\t":"0";
        $result['Site_Earning'] = ($siteEarning != "" && $siteEarning != 0)?formateNumAsPerCurrency($siteEarning,'')."\t":"0";
        //$data .= $db_trip[$i]['iActive'] . "\t";
        if ($db_trip[$i]['vTripPaymentMode'] == "Card" && $db_trip[$i]['ePayWallet'] == 'Yes'){
            $result['vTripPaymentMode'] = $langage_lbl_admin['LBL_WALLET_TXT'];
        }else{
            $result['vTripPaymentMode'] = $db_trip[$i]['vTripPaymentMode'].$orgName;
        }
        //$data .= $paymentmode . "\t";
        if ($db_trip[$i]['eDriverPaymentStatus'] == "Settelled"){
            $result['eDriverPaymentStatus'] = "Settled";
        }else if ($db_trip[$i]['eDriverPaymentStatus'] == "Unsettelled"){
            $result['eDriverPaymentStatus'] = "Unsettled";
        }else{
            $result['eDriverPaymentStatus'] = $db_trip[$i]['eDriverPaymentStatus'];
        }
        /*       $sheet->setCellValue('A'.$j,$vRideNo_text);
            $sheet->setCellValue('B' . $j, $tripTypeTxt);
            $sheet->setCellValue('C' . $j, clearName($db_trip[$i]['drivername']));
            $sheet->setCellValue('D' . $j, clearName($db_trip[$i]['riderName']));
            $sheet->setCellValue('E' . $j, $result['date']);
            $sheet->setCellValue('F' . $j, $result['tot_fare']);
            $sheet->setCellValue('G' . $j, $result['receivec']);
            $sheet->setCellValue('H' . $j, $result['fCommision']);
            $sheet->setCellValue('I' . $j, $result['tot_tax']);
            $sheet->setCellValue('J' . $j, $result['total_tip']);
            $sheet->setCellValue('K' . $j, $result['fOutStandingAmount']);
            $sheet->setCellValue('L' . $j, ($fHotelCommision > 0) ? formateNumAsPerCurrency($fHotelCommision, '') : 0);
            $sheet->setCellValue('M'.$j, ($dispay_driver_payment > 0)?formateNumAsPerCurrency($dispay_driver_payment,''):0 );
            $sheet->setCellValue('N' . $j, $result['Site_Earning']);
            $sheet->setCellValue('O' . $j, $db_trip[$i]['iActive']);
            $sheet->setCellValue('P' . $j, $result['vTripPaymentMode']);
        $sheet->setCellValue('Q'.$j,$result['eDriverPaymentStatus']);*/
        /*------------------make dynamic cell from array-----------------*/
        $cellValues['RIDE_NO'] = $vRideNo_text;
        $cellValues['TRIP_TYPE'] = $tripTypeTxt;
        $cellValues['DRIVER'] = clearName($db_trip[$i]['drivername']);
        $cellValues['RIDER'] = clearName($db_trip[$i]['riderName']);
        $cellValues['TRIP_DATE'] = $result['date'];
        $cellValues['TOTAL_FARE'] = $result['tot_fare'];
        $cellValues['CASH_RECEIVED'] = $result['receivec'];
        $cellValues['COMMISSION_AMOUNT'] = $result['fCommision'];
        $cellValues['TOTAL_TAX'] = $result['tot_tax'];
        if (in_array(1,$enableTipCol)){
            $cellValues['TIP'] = $result['total_tip'];
        }
        $cellValues['OUTSTANDING_AMOUNT'] = $result['fOutStandingAmount'];
        $cellValues['BOOKING_FEES'] = ($fHotelCommision > 0)?formateNumAsPerCurrency($fHotelCommision,''):0;
        $cellValues['DRIVER_PAY_AMOUNT'] = ($dispay_driver_payment > 0)?formateNumAsPerCurrency($dispay_driver_payment,''):0;
        $cellValues['SITE_EARNING'] = $result['Site_Earning'];
        $cellValues['RIDE_STATUS'] = $db_trip[$i]['iActive'];
        $cellValues['PAYMENT_METHOD'] = $result['vTripPaymentMode'];
        $cellValues['DRIVER_PAYMENT_STATUS'] = $result['eDriverPaymentStatus'];
        makeCell($cellValues,$sheet,$j);
        /*------------------make dynamic cell from array-----------------*/
        $j++;
    }
    $j += 1;
    $Summary_array = array("Total Fare "                              => formateNumAsPerCurrency($tot_fare,''),
                           "Total Commission Amount (Total Earning) " => formateNumAsPerCurrency($tot_site_commission,''),
                           "Total Tax "                               => formateNumAsPerCurrency($tot_tax,''),
                           "Total Trip/Job Outstanding Amount "       => formateNumAsPerCurrency($tot_outstandingAmount,''),
                           "Total Driver pay / Take Amount "          => formateNumAsPerCurrency($tot_driver_refund,''),
                           "Total Site Earning "                      => formateNumAsPerCurrency($totSiteEarning,''));
    if (in_array(1,$enableCashReceivedCol)){
        //$Summary_array["Total Cash Received "] = formateNumAsPerCurrency($TotaliNewFare, '');
        $Summary_array = array_merge(["Total Fare " => formateNumAsPerCurrency($tot_fare,'')],["Total Cash Received " => formateNumAsPerCurrency($TotaliNewFare,'')],$Summary_array);
    }
    if ($hotelPanel > 0 || $kioskPanel > 0){
        $search_key = array_search("Total Trip/Job Outstanding Amount ",array_keys($Summary_array));
        $Summary_array = array_slice($Summary_array,0,$search_key + 1) + ["Total Booking Fees" => formateNumAsPerCurrency($tot_hotel_commision,'')] + array_slice($Summary_array,$search_key + 1);
    }
    if (in_array(1,$enableTipCol)){
        $Summary_array["Total Tip"] = formateNumAsPerCurrency($total_tip,'');
    }
    foreach ($Summary_array as $key => $value){
        $sheet->setCellValue('P'.$j,$key);
        $sheet->setCellValue('Q'.$j,$value);
        $j++;
    }
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'cancellation_driver_payment' || $section == "cancellation_org_driver_payment"){

    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    $searchPaymentByUser = isset($_REQUEST['searchPaymentByUser'])?$_REQUEST['searchPaymentByUser']:'';
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And trp.tTripRequestDate > '".WEEK_DATE."'";
    }
    $ord = ' ORDER BY tr.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY trp.tTripRequestDate ASC";
        }else{
            $ord = " ORDER BY trp.tTripRequestDate DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY u.vName ASC";
        }else{
            $ord = " ORDER BY u.vName DESC";
        }
    }
    if ($sortby == 6){
        if ($order == 0){
            $ord = " ORDER BY trp.eType ASC";
        }else{
            $ord = " ORDER BY trp.eType DESC";
        }
    }
    $ssql = "";
    $reportName = "cancellation_payment_report";
    if ($section == "cancellation_org_driver_payment"){
        $ssql .= " AND tr.ePaymentBy='Organization'";
        $reportName = "org_cancellation_payment_report";
    }
    if ($startDate != ''){
        $ssql .= " AND Date(trp.tTripRequestDate) >='".$startDate."'";
    }
    if ($endDate != ''){
        $ssql .= " AND Date(trp.tTripRequestDate) <='".$endDate."'";
    }
    if ($serachTripNo != ''){
        $ssql .= " AND trp.vRideNo ='".$serachTripNo."'";
    }
    if ($iCompanyId != ''){
        $ssql .= " AND rd.iCompanyId ='".$iCompanyId."'";
    }
    if ($iDriverId != ''){
        $ssql .= " AND tr.iDriverId ='".$iDriverId."'";
    }
    if ($iUserId != ''){
        $ssql .= " AND tr.iUserId ='".$iUserId."'";
    }
    if ($eDriverPaymentStatus != ''){
        $ssql .= " AND tr.ePaidToDriver ='".$eDriverPaymentStatus."'";
    }
    if ($vTripPaymentMode != ''){
        $ssql .= " AND tr.vTripPaymentMode ='".$vTripPaymentMode."'";
    }
    if ($eType != ''){
        if ($eType == 'Ride'){
            $ssql .= " AND trp.eType ='".$eType."' AND trp.iRentalPackageId = 0 AND trp.eHailTrip = 'No' ";
        }elseif ($eType == 'RentalRide'){
            $ssql .= " AND trp.eType ='Ride' AND trp.iRentalPackageId > 0";
        }elseif ($eType == 'HailRide'){
            $ssql .= " AND trp.eType ='Ride' AND trp.eHailTrip = 'Yes'";
        }else{
            $ssql .= " AND trp.eType ='".$eType."' ";
        }
    }
    if ($searchPaymentByUser != ''){
        $ssql .= " AND tr.ePaidByPassenger ='".$searchPaymentByUser."'";
    }
    $locations_where = "";
    if (scount($userObj->locations) > 0){
        $locations = implode(', ',$userObj->locations);
        $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE trp.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
    }
    $sql_admin = "SELECT org.vCompany AS Organization, tr.iTripId,tr.iTripOutstandId,tr.fPendingAmount,tr.iDriverId,tr.iUserId, tr.fCommision, tr.fDriverPendingAmount, tr.fWalletDebit,tr.ePaidByPassenger,tr.ePaidToDriver,tr.vTripPaymentMode,trp.iRentalPackageId,trp.eType,trp.vRideNo,trp.tTripRequestDate, tr.vTripAdjusmentId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,trp.vTimeZone FROM trip_outstanding_amount AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN trips AS trp ON trp.iTripId = tr.iTripId  LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId INNER JOIN organization org ON org.iOrganizationId=tr.iOrganizationId WHERE  1 = 1 AND tr.fDriverPendingAmount > 0 AND trp.eSystem = 'General' AND tr.ePaymentBy='Organization'$ssql $trp_ssql $ord";
    //$db_trip = $obj->MySQLSelect($sql_admin);
    /* echo "<pre>";
    print_r($db_trip);
    exit; */
    //echo $sql_admin;die;
    $filename = $reportName.".xls";
    $db_trip = $obj->MySQLSelect($sql_admin) or die('Query Failed!');
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']);
    $sheet->setCellValue('B1','Cancelled '.$langage_lbl_admin['LBL_RIDE_NO_ADMIN']);
    $sheet->setCellValue('C1',"Organization");
    $sheet->setCellValue('D1',$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
    $sheet->setCellValue('E1',$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']);
    $sheet->setCellValue('F1',$langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Date");
    $sheet->setCellValue('G1','Total Cancellation Fees');
    /* $sheet->setCellValue('G1', "Platform Fees");
    $sheet->setCellValue('H1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . " Pay Amount"); */
    $sheet->setCellValue('H1',"Organization Payment Status");
    $sheet->setCellValue('I1',"Provider Payment Status");
    $i = 2;
    $driver_payment = $tot_site_commission = 0.00;
    $serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($db_trip);$j++){

        $site_commission = $db_trip[$j]['fCommision'];
        $driver_payment = $db_trip[$j]['fDriverPendingAmount'];
        $tot_site_commission = $tot_site_commission + $site_commission;
        $tot_driver_refund = $tot_driver_refund + $driver_payment;
        $paymentmode = $db_trip[$j]['vTripPaymentMode'];
        $eType = $db_trip[$j]['eType'];
        if ($eType == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eType == 'UberX'){
            $trip_type = 'Other Services';
        }else if ($eType == 'Deliver' || $eType == 'Multi-Delivery'){
            $trip_type = 'Delivery';
        }
        $q = "SELECT vRideNo FROM trips WHERE iTripId = '".$db_trip[$j]['vTripAdjusmentId']."'";
        $db_bookingno = $obj->MySQLSelect($q);
        if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery'){
            if ($db_trip[$j]['eHailTrip'] == "Yes" && $db_trip[$j]['iRentalPackageId'] > 0){
                $result['type'] = "Rental ".$trip_type." ( Hail )"."\t";
            }else if ($db_trip[$j]['iRentalPackageId'] > 0){
                $result['type'] = "Rental ".$trip_type."\t";
            }else if ($db_trip[$j]['eHailTrip'] == "Yes"){
                $result['type'] = "Hail ".$trip_type."\t";
            }else{
                $result['type'] = $trip_type."\t";
            }
        }
        if ($db_bookingno[0]['vRideNo'] != "" && $db_bookingno[0]['vRideNo'] != 0){
            $paymentstatus = "Paid in Trip# ".$db_bookingno[0]['vRideNo'];
        }else if ($db_trip[$j]['ePaidByPassenger'] == 'No'){
            $paymentstatus = "Not Paid";
        }else{
            $paymentstatus = "Paid By Card";
        }
        $TotalCancelledprice = $db_trip[$j]['fPendingAmount'] > $db_trip[$j]['fWalletDebit']?$db_trip[$j]['fPendingAmount']:$db_trip[$j]['fWalletDebit'];
        if ($db_trip[$j]['ePaidToDriver'] == 'No'){
            $providerPaymentStatus = "Unsettled";
        }else{
            $providerPaymentStatus = "Settled";
        }
        $result['ride_no'] = $db_trip[$j]['vRideNo']."\t";
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['tTripRequestDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['tTripRequestDate'];
        $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$j]['vTimeZone'],$date_format_data_array['tdate']).")";
        $result['date'] = $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text."\t";//date('d-m-Y', strtotime($db_trip[$j]['tTripRequestDate'])) . "\t";
        $result['total_cancel'] = ($TotalCancelledprice != "" && $TotalCancelledprice != 0)?formateNumAsPerCurrency($TotalCancelledprice,'')."\t":"- \t";
        $result['fees'] = ($db_trip[$j]['fCommision'] != "" && $db_trip[$j]['fCommision'] != 0)?formateNumAsPerCurrency($db_trip[$j]['fCommision'],'')."\t":"- \t";
        $result['driver_payment'] = ($driver_payment != "" && $driver_payment != 0)?formateNumAsPerCurrency($driver_payment,'')."\t":"- \t";
        //$data .= ($db_bookingno[0]['vRideNo'] != "" && $db_bookingno[0]['vRideNo'] != 0) ? $db_bookingno[0]['vRideNo'] . "\n" : "- \n";
        $result['Adjsment'] = $paymentstatus."\t";
        $result['status'] = $providerPaymentStatus."\n";
        $sheet->setCellValue('A'.$i,$result['type']);
        $sheet->setCellValue('B'.$i,$result['ride_no']);
        $sheet->setCellValue('C'.$i,$db_trip[$j]['Organization']);
        $sheet->setCellValue('D'.$i,clearName($db_trip[$j]['drivername']));
        $sheet->setCellValue('E'.$i,clearName($db_trip[$j]['riderName']));
        $sheet->setCellValue('F'.$i,$result['date']);
        $sheet->setCellValue('G'.$i,$result['total_cancel']);
        $sheet->setCellValue('H'.$i,$result["Adjsment"]);
        $sheet->setCellValue('I'.$i,$result['status']);
        $i++;
    }
    $i += 1;
    $summary_array = array('Total Platform Fees '            => formateNumAsPerCurrency($tot_site_commission,''),
                           'Total Service Provider Payment ' => formateNumAsPerCurrency($tot_driver_refund,''));
    foreach ($summary_array as $key => $value){
        $sheet->setCellValue('H'.$i,$key);
        $sheet->setCellValue('I'.$i,$value);
        $i++;
    }
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
    exit;
}
/* if ($section == 'driver_payment') {

  $trp_ssql = "";
  if (SITE_TYPE == 'Demo') {
  $trp_ssql = " And tr.tTripRequestDate > '" . WEEK_DATE . "'";
  }

  $ord = ' ORDER BY tr.iTripId DESC';

  if ($sortby == 1) {
  if ($order == 0)
  $ord = " ORDER BY rd.vName ASC";
  else
  $ord = " ORDER BY rd.vName DESC";
  }

  if ($sortby == 2) {
  if ($order == 0)
  $ord = " ORDER BY ru.vName ASC";
  else
  $ord = " ORDER BY ru.vName DESC";
  }

  if ($sortby == 3) {
  if ($order == 0)
  $ord = " ORDER BY tr.tStartDate ASC";
  else
  $ord = " ORDER BY tr.tStartDate DESC";
  }

  if ($sortby == 4) {
  if ($order == 0)
  $ord = " ORDER BY d.vName ASC";
  else
  $ord = " ORDER BY d.vName DESC";
  }

  if ($sortby == 5) {
  if ($order == 0)
  $ord = " ORDER BY u.vName ASC";
  else
  $ord = " ORDER BY u.vName DESC";
  }

  $ssql = "";
  if ($startDate != '') {
  $ssql .= " AND Date(tTripRequestDate) >='" . $startDate . "'";
  }
  if ($endDate != '') {
  $ssql .= " AND Date(tTripRequestDate) <='" . $endDate . "'";
  }
  if ($iCompanyId != '') {
  $ssql .= " AND rd.iCompanyId = '" . $iCompanyId . "'";
  }
  if ($iDriverId != '') {
  $ssql .= " AND tr.iDriverId = '" . $iDriverId . "'";
  }

  if ($iUserId != '') {
  $ssql .= " AND tr.iUserId = '" . $iUserId . "'";
  }
  if ($serachTripNo != '') {
  $ssql .= " AND tr.vRideNo ='" . $serachTripNo . "'";
  }

  if ($vTripPaymentMode != '') {
  $ssql .= " AND tr.vTripPaymentMode = '" . $vTripPaymentMode . "'";
  }
  if ($eDriverPaymentStatus != '') {
  $ssql .= " AND tr.eDriverPaymentStatus = '" . $eDriverPaymentStatus . "'";
  }
  //$sql_admin = "SELECT * from trips WHERE 1=1 ".$ssql." ORDER BY iTripId DESC";
  $sql_admin = "SELECT tr.iTripId,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eDriverPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName FROM trips AS tr
  LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId
  LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId
  LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId
  WHERE 1=1 $ssql $trp_ssql $ord";
  $db_trip = $obj->MySQLSelect($sql_admin);
  //    echo "<pre>";print_r($db_trip); exit;

  $header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." No." . "\t";
  $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name" . "\t";
  $header .= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']." Name" . "\t";
  $header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Date" . "\t";
  $header .= "Total Fare" . "\t";
  $header .= "Platform Fees" . "\t";
  $header .= "Promo Code Discount" . "\t";
  $header .= "Wallet Debit" . "\t";
  if ($ENABLE_TIP_MODULE == "Yes") {
  $header .= "Tip" . "\t";
  }
  $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." pay Amount" . "\t";
  $header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Status" . "\t";
  $header .= "Payment method" . "\t";
  $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Payment Status";

  $tot_fare = 0.00;
  $tot_site_commission = 0.00;
  $tot_promo_discount = 0.00;
  $tot_driver_refund = 0.00;
  $tot_wallentPayment = 0.00;
  $total_tip = 0.00;

  for ($j = 0; $j < scount($db_trip); $j++) {
  $driver_payment = 0.00;

  $totalfare = $db_trip[$j]['fTripGenerateFare'];
  $site_commission = $db_trip[$j]['fCommision'];
  $promocodediscount = $db_trip[$j]['fDiscount'];
  $wallentPayment = $db_trip[$j]['fWalletDebit'];
  $fTipPrice = $db_trip[$j]['fTipPrice'];
  $driver_payment = $totalfare - $site_commission;

  $tot_fare = $tot_fare + $totalfare;
  $tot_site_commission = $tot_site_commission + $site_commission;
  $tot_promo_discount = $tot_promo_discount + $promocodediscount;
  $tot_wallentPayment = $tot_wallentPayment + $wallentPayment;
  $total_tip = $total_tip + $fTipPrice;
  $tot_driver_refund = $tot_driver_refund + $driver_payment;

  if ($db_trip[$j]['eMBirr'] == "Yes") {
  $paymentmode = "M-birr";
  } else {
  $paymentmode = $db_trip[$j]['vTripPaymentMode'];
  }

  $data .= $db_trip[$j]['vRideNo'] . "\t";
  $data .= clearName($db_trip[$j]['drivername']) . "\t";
  $data .= clearName($db_trip[$j]['riderName']) . "\t";
  $data .= date('d-m-Y', strtotime($db_trip[$j]['tTripRequestDate'])) . "\t";
  $data .= ($db_trip[$j]['fTripGenerateFare'] != "" && $db_trip[$j]['fTripGenerateFare'] != 0) ? trip_currency($db_trip[$j]['fTripGenerateFare']) . "\t" : "- \t";
  $data .= ($db_trip[$j]['fCommision'] != "" && $db_trip[$j]['fCommision'] != 0) ? trip_currency($db_trip[$j]['fCommision']) . "\t" : "- \t";
  $data .= ($db_trip[$j]['fDiscount'] != "" && $db_trip[$j]['fDiscount'] != 0) ? trip_currency($db_trip[$j]['fDiscount']) . "\t" : "- \t";
  $data .= ($db_trip[$j]['fWalletDebit'] != "" && $db_trip[$j]['fWalletDebit'] != 0) ? trip_currency($db_trip[$j]['fWalletDebit']) . "\t" : "- \t";
  if ($ENABLE_TIP_MODULE == "Yes") {
  $data .= ($db_trip[$j]['fTipPrice'] != "" && $db_trip[$j]['fTipPrice'] != 0) ? trip_currency($db_trip[$j]['fTipPrice']) . "\t" : "- \t";
  }
  $data .= ($driver_payment != "" && $driver_payment != 0) ? trip_currency($driver_payment) . "\t" : "- \t";
  $data .= $db_trip[$j]['iActive'] . "\t";
  $data .= $paymentmode . "\t";
  $data .= $db_trip[$j]['eDriverPaymentStatus'] . "\n";
  }
  $data .= "\n\t\t\t\t\t\t\t\t\tTotal Fare\t" . trip_currency($tot_fare) . "\n";
  $data .= "\t\t\t\t\t\t\t\t\tTotal Platform Fees\t" . trip_currency($tot_site_commission) . "\n";
  $data .= "\t\t\t\t\t\t\t\t\tTotal Promo Discount\t" . trip_currency($tot_promo_discount) . "\n";
  $data .= "\t\t\t\t\t\t\t\t\tTotal Wallet Debit\t" . trip_currency($tot_wallentPayment) . "\n";
  if ($ENABLE_TIP_MODULE == "Yes") {
  $data .= "\t\t\t\t\t\t\t\t\tTotal ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Payment\t" . trip_currency($total_tip) . "\n";
  $data .= "\t\t\t\t\t\t\t\t\tTotal ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Payment\t" . trip_currency($tot_driver_refund+$total_tip) . "\n";
  }else {
  $data .= "\t\t\t\t\t\t\t\t\tTotal ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Payment\t" . trip_currency($tot_driver_refund) . "\n";
  }
  $data = str_replace("\r", "", $data);
  #echo "<br>".$data; exit;
  ob_clean();
  header("Content-type: application/octet-stream; charset=utf-8");
  header("Content-Disposition: attachment; filename=payment_reports.xls");
  header("Pragma: no-cache");
  header("Expires: 0");
  print "$header\n$data";
  exit;
  } */
if ($section == 'driver_payment_report'){
    $script = 'Deliverall Driver Payment Report';
    function cleanNumber($num)
    {
        return str_replace(',','',$num);
    }

    //data for select fields
    $sql = "select iUserId,CONCAT(vName,' ',vLastName) AS riderName,vEmail from register_user WHERE eStatus != 'Deleted' order by vName";
    $db_rider = $obj->MySQLSelect($sql);
    //data for select fields
    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY tr.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY o.tOrderRequestDate ASC";
        }else{
            $ord = " ORDER BY o.tOrderRequestDate DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY u.vName ASC";
        }else{
            $ord = " ORDER BY u.vName DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $searchCompany = isset($_REQUEST['searchCompany'])?$_REQUEST['searchCompany']:'';
    $searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
    $searchDriverPayment = isset($_REQUEST['searchDriverPayment'])?$_REQUEST['searchDriverPayment']:'';
    $searchPaymentType = isset($_REQUEST['searchPaymentType'])?$_REQUEST['searchPaymentType']:'';
    $searchServiceType = isset($_REQUEST['searchServiceType'])?$_REQUEST['searchServiceType']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    if ($action == 'search'){
        if ($startDate != ''){
            $ssql .= " AND Date(o.tOrderRequestDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(o.tOrderRequestDate) <='".$endDate."'";
        }
        if ($serachTripNo != ''){
            $ssql .= " AND o.vOrderNo ='".$serachTripNo."'";
        }
        if ($searchCompany != ''){
            $ssql .= " AND rd.iCompanyId ='".$searchCompany."'";
        }
        if ($searchDriver != ''){
            $ssql .= " AND o.iDriverId ='".$searchDriver."'";
        }
        if ($searchRider != ''){
            $ssql .= " AND tr.iUserId ='".$searchRider."'";
        }
        if ($searchServiceType != '' && !in_array($searchServiceType,['Genie','Runner','Anywhere'])){
            $ssql .= " AND sc.iServiceId ='".$searchServiceType."' AND o.eBuyAnyService ='No'";
        }
        if ($searchServiceType == "Genie"){
            $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
        }
        if ($searchServiceType == "Runner"){
            $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
        }
        if ($searchDriverPayment != ''){
            $ssql .= " AND tr.eDriverPaymentStatus ='".$searchDriverPayment."'";
        }
        if ($searchPaymentType != ''){
            $ssql .= " AND tr.vTripPaymentMode ='".$searchPaymentType."'";
        }
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And o.tOrderRequestDate > '".WEEK_DATE."'";
    }
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    $sql = "SELECT tr.fDeliveryCharge,o.fTipAmount,tr.vTripPaymentMode,sc.vServiceName_".$default_lang." as vServiceName,o.fDriverPaidAmount, o.iStatusCode, odcd.fDeliveryCharge as fCustomDeliveryCharge,vt.fDeliveryCharge as fDeliveryChargeVehicle, o.fTipAmount, o.eBuyAnyService,o.ePaymentOption, o.iOrderId, ( SELECT COUNT(tr.iTripId) FROM trips AS tr LEFT JOIN orders as o on o.iOrderId=tr.iOrderId LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE 1=1 AND o.iStatusCode = 6 AND tr.eSystem = 'Deliverall' $ssql $trp_ssql) AS Total FROM trips AS tr LEFT JOIN orders as o on o.iOrderId=tr.iOrderId LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid LEFT JOIN order_delivery_charge_details as odcd ON odcd.iOrderId = tr.iOrderId LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId = tr.iVehicleTypeId WHERE  1 = 1 AND o.iStatusCode = 6 AND tr.eSystem = 'Deliverall' $ssql $trp_ssql";
    $totalData = $obj->MySQLSelect($sql);
    $tot_driver_payment = 0.00;
    $total_driver_earning = 0.00;
    foreach ($totalData as $dtps){
        $site_commission = $dtps['fDeliveryCharge'];
        $subtotal = 0;
        if ($dtps['eBuyAnyService'] == "Yes" && $dtps['ePaymentOption'] == "Card"){
            $order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId = '".$dtps['iOrderId']."'");
            if (scount($order_buy_anything) > 0){
                foreach ($order_buy_anything as $oItem){
                    if ($oItem['eConfirm'] == "Yes"){
                        $subtotal += $oItem['fItemPrice'];
                    }
                }
            }
        }
        if ($dtps['iStatusCode'] == '7' || $dtps['iStatusCode'] == '8'){
            $fDriverPaidAmount = $dtps['fDriverPaidAmount'];
        }else{
            $fDriverPaidAmount = $dtps['fDeliveryCharge'];
            // $fDriverPaidAmount = $fDriverPaidAmount - ($fDriverPaidAmount - ($dtps['fCustomDeliveryCharge'] + $dtps['fDeliveryChargeVehicle']));
            $fDriverPaidAmount = $fDriverPaidAmount + $dtps['fTipAmount'] + $subtotal;
        }
        $tot_driver_payment = $tot_driver_payment + cleanNumber($site_commission);
        $total_driver_earning = $total_driver_earning + cleanNumber($fDriverPaidAmount);
    }
    $total_results = $totalData[0]['Total'];
    $total_pages = ceil($total_results / $per_page); //total pages we going to have
    $show_page = 1;
    //-------------if page is setcheck------------------//
    if (isset($_GET['page'])){
        $show_page = $_GET['page'];             //it will telles the current page
        if ($show_page > 0 && $show_page <= $total_pages){
            $start = ($show_page - 1) * $per_page;
            $end = $start + $per_page;
        }else{
            // error - show first set of results
            $start = 0;
            $end = $per_page;
        }
    }else{
        // if page isn't set, show first set of results
        $start = 0;
        $end = $per_page;
    }
    // display pagination
    $page = isset($_GET['page'])?intval($_GET['page']):0;
    $tpages = $total_pages;
    if ($page <= 0){
        $page = 1;
    }
    //Pagination End
    $sql = "SELECT tr.iTripId,o.fTipAmount,o.iOrderId,o.vOrderNo,sc.vServiceName_".$default_lang." as vServiceName,o.iCompanyId,o.iDriverId,o.fDriverPaidAmount,o.iStatusCode,o.iUserId,o.tOrderRequestDate,tr.fDeliveryCharge, tr.eDriverPaymentStatus,tr.vTripPaymentMode,os.vStatus,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, odcd.fDeliveryCharge as fCustomDeliveryCharge,vt.fDeliveryCharge as fDeliveryChargeVehicle, o.fTipAmount,o.eBuyAnyService,o.ePaymentOption,o.eForPickDropGenie,o.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN orders as o on o.iOrderId=tr.iOrderId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid LEFT JOIN order_delivery_charge_details as odcd ON odcd.iOrderId = tr.iOrderId LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId = tr.iVehicleTypeId  WHERE 1 = 1 AND o.iStatusCode = 6 AND tr.eSystem = 'Deliverall' $ssql $trp_ssql GROUP BY tr.iTripId $ord ";
    $db_trip = $obj->MySQLSelect($sql);
    $endRecord = scount($db_trip);
    $var_filter = "";
    foreach ($_REQUEST as $key => $val){
        if ($key != "tpages" && $key != 'page'){
            $var_filter .= "&$key=".stripslashes($val);
        }
    }
    $reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
    $Today = Date('Y-m-d');
    $tdate = date("d") - 1;
    $mdate = date("d");
    $Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - 1,date("Y")));
    $curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
    $curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
    $prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y") - 1));
    $prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y") - 1));
    $currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $tdate,date("Y")));
    $currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m") + 1,date("d") - $mdate,date("Y")));
    $prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m") - 1,date("d") - $tdate,date("Y")));
    $prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $mdate,date("Y")));
    $monday = date('Y-m-d',strtotime('sunday this week -1 week'));
    $sunday = date('Y-m-d',strtotime('saturday this week'));
    $Pmonday = date('Y-m-d',strtotime('sunday this week -2 week'));
    $Psunday = date('Y-m-d',strtotime('saturday this week -1 week'));
    $sql = "select iDriverId,CONCAT(vName,' ',vLastName) AS driverName,vEmail from register_driver WHERE iDriverId ='".$searchDriver."'  order by vName";
    $db_drivers = $obj->MySQLSelect($sql);
    $catdata = serviceCategories;
    $allservice_cat_data = json_decode($catdata,true);
    //Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
    $cardText = "Card";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3'){
        $cardText = "Wallet";
    }
    $header = $data = "";
    if (scount($allservice_cat_data) > 1){
        $header .= "Service type"."\t";
    }
    $header .= "Order No#"."\t";
    $header .= "Order Date"."\t";
    $header .= "Driver"."\t";
    $header .= "User"."\t";
    $header .= "Restaurant Name"."\t";
    $header .= "Driver Pay Amount"."\t";
    $header .= "Order Status"."\t";
    $header .= "Payment method"."\t";
    $header .= "Driver Payment Status"."\t";
    if (scount($db_trip) > 0){
        $serverTimeZone = date_default_timezone_get();
        for ($i = 0;$i < scount($db_trip);$i++){
            $subtotal = 0;
            if ($db_trip[$i]['eBuyAnyService'] == "Yes"){
                $db_trip[$i]['vServiceName'] = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
                if ($db_trip[$i]['eForPickDropGenie'] == "Yes"){
                    $db_trip[$i]['vServiceName'] = $langage_lbl_admin['LBL_RUNNER'];
                }
                if ($db_trip[$i]['ePaymentOption'] == "Card"){
                    $order_buy_anything = $obj->MySQLSelect("SELECT * FROM order_items_buy_anything WHERE iOrderId = '".$db_trip[$i]['iOrderId']."'");
                    if (scount($order_buy_anything) > 0){
                        foreach ($order_buy_anything as $oItem){
                            if ($oItem['eConfirm'] == "Yes"){
                                $subtotal += $oItem['fItemPrice'];
                            }
                        }
                    }
                }
            }
            $class_setteled = "";
            if ($db_trip[$i]['eDriverPaymentStatus'] == 'Settelled'){
                $class_setteled = "setteled-class";
            }
            $site_commission = $db_trip[$i]['fDeliveryCharge'];
            $set_unsetarray[] = $db_trip[$i]['eDriverPaymentStatus'];
            if ($db_trip[$i]['iStatusCode'] == '7' || $db_trip[$i]['iStatusCode'] == '8'){
                $driverEarning = $db_trip[$i]['fDriverPaidAmount'];
            }else{
                $driverEarning = $db_trip[$i]['fDeliveryCharge'];
                // $driverEarning = $driverEarning - ($driverEarning - ($db_trip[$i]['fCustomDeliveryCharge'] + $db_trip[$i]['fDeliveryChargeVehicle']));
                $driverEarning = $driverEarning + $db_trip[$i]['fTipAmount'] + $subtotal;
            }
            if ($db_trip[$i]['driver_phone'] != ''){
                $vdrivername = clearName($db_trip[$i]['drivername']);
                $vdrivername .= '   ';
                $vdrivername .= '  Phone: +'.clearPhone($db_trip[$i]['driver_phone']);
            }else{
                $vdrivername .= clearName($db_trip[$i]['drivername']);
            }
            if ($db_trip[$i]['user_phone'] != ''){
                $vRiderName = clearName($db_trip[$i]['riderName']);
                $vRiderName .= '   ';
                $vRiderName .= '  Phone: +'.clearPhone($db_trip[$i]['user_phone']);
            }else{
                $vRiderName = clearName($db_trip[$i]['riderName']);
            }
            if ($db_trip[$i]['resturant_phone'] != ''){
                $vCompany = clearName($db_trip[$i]['vCompany']);
                $vCompany .= '    ';
                $vCompany .= '  Phone: +'.clearPhone($db_trip[$i]['resturant_phone']);
            }else{
                $vCompany = clearName($db_trip[$i]['vCompany']);
            }
            $vTripPaymentMode = $db_trip[$i]['vTripPaymentMode'];
            if ($db_trip[$i]['vTripPaymentMode'] == 'Card'){
                $vTripPaymentMode = $cardText;
            }
            if (scount($allservice_cat_data) > 1){
                $data .= $db_trip[$i]['vServiceName']."\t";
            }
            $data .= $db_trip[$i]['vOrderNo']."\t";
            $date_format_data_array = array('tdate'            => (!empty($db_trip[$i]['vTimeZone']))?converToTz($db_trip[$i]['tOrderRequestDate'],$db_trip[$i]['vTimeZone'],$serverTimeZone):$db_trip[$i]['tOrderRequestDate'],
                                            'langCode'         => $default_lang,
                                            'DateFormatForWeb' => 1);
            $get_tOrderRequestDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
            $data .= $get_tOrderRequestDate_date_format['tDisplayDate'].$time_zone_difference_text."\t";//DateTime($db_trip[$i]['tOrderRequestDate']) . "\t";
            $data .= $vdrivername."\t";
            $data .= $vRiderName."\t";
            $data .= $vCompany."\t";
            if ($db_trip[$i]['fTipAmount'] > 0){
                $data .= formateNumAsPerCurrency($driverEarning,'')." (Including Driver Tip: ".formateNumAsPerCurrency($db_trip[$i]['fTipAmount'],'').")\t";
            }else{
                $data .= formateNumAsPerCurrency($driverEarning,'')."\t";
            }
            $data .= $db_trip[$i]['vStatus']."\t";
            $data .= $vTripPaymentMode."\t";
            $data .= $db_trip[$i]['eDriverPaymentStatus']."\n";
            // $data .= $db_trip[$i]['eRestaurantPaymentStatus'] . "\n";
        }
        $data .= "\n\n\n";
        $data .= "Total Driver Payment : ".formateNumAsPerCurrency($total_driver_earning,'')."\n";
        $data = str_replace("\r","",$data);
    }
    $timenow = time();
    $filename = "driver_payment_report_".$timenow.".xls";
    ob_clean();
    header("Content-type: application/octet-stream; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == "store_payment_report"){
    $script = 'Restaurant Payment Report';
    $eSystem = " AND eSystem = 'DeliverAll'";
    function cleanNumber($num)
    {
        return str_replace(',','',$num);
    }

    $catdata = serviceCategories;
    $allservice_cat_data = json_decode($catdata,true);
    //data for select fields
    $sql = "select iCompanyId,vCompany,vEmail from company WHERE eStatus != 'Deleted' $eSystem order by vCompany";
    $db_company = $obj->MySQLSelect($sql);
    //data for select fields
    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY o.iOrderId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY c.vCompany ASC";
        }else{
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY o.tOrderRequestDate ASC";
        }else{
            $ord = " ORDER BY o.tOrderRequestDate DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY o.ePaymentOption ASC";
        }else{
            $ord = " ORDER BY o.ePaymentOption DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $searchCompany = isset($_REQUEST['searchCompany'])?$_REQUEST['searchCompany']:'';
    $serachOrderNo = isset($_REQUEST['serachOrderNo'])?$_REQUEST['serachOrderNo']:'';
    $searchRestaurantPayment = isset($_REQUEST['searchRestaurantPayment'])?$_REQUEST['searchRestaurantPayment']:'';
    $searchPaymentType = isset($_REQUEST['searchPaymentType'])?$_REQUEST['searchPaymentType']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $searchServiceType = isset($_REQUEST['searchServiceType'])?$_REQUEST['searchServiceType']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    if ($action == 'search'){
        if ($startDate != ''){
            $ssql .= " AND Date(o.tOrderRequestDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(o.tOrderRequestDate) <='".$endDate."'";
        }
        if ($serachOrderNo != ''){
            $ssql .= " AND o.vOrderNo ='".$serachOrderNo."'";
        }
        if ($searchCompany != ''){
            $ssql .= " AND c.iCompanyId ='".$searchCompany."'";
        }
        if ($searchRestaurantPayment != ''){
            $ssql .= " AND o.eRestaurantPaymentStatus ='".$searchRestaurantPayment."'";
        }
        if ($searchServiceType != ''){
            $ssql .= " AND sc.iServiceId ='".$searchServiceType."'";
        }
        if ($searchPaymentType != ''){
            $ssql .= " AND o.ePaymentOption ='".$searchPaymentType."'";
        }
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And o.tOrderRequestDate > '".WEEK_DATE."'";
    }
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    $sql = "SELECT o.iOrderId,o.vOrderNo,o.fTipAmount,sc.vServiceName_".$default_lang." as vServiceName,o.iCompanyId,o.iDriverId,o.iUserId,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fOutStandingAmount,o.tOrderRequestDate,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.iStatusCode,os.vStatus ,( SELECT COUNT(o.iOrderId) FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId WHERE 1=1  AND (o.iStatusCode = '6' OR o.fRestaurantPayAmount > 0) $ssql $trp_ssql) AS Total FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company AS c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status AS os ON os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND (o.iStatusCode = '6') $ssql $trp_ssql";
    //OR o.fRestaurantPayAmount > 0
    $totalData = $obj->MySQLSelect($sql);
    $tot_order_amount = 0.00;
    $tot_site_commission = 0.00;
    $tot_delivery_charges = 0.00;
    $tot_offer_discount = 0.00;
    $tot_restaurant_payment = 0.00;
    $expected_rest_payment = 0.00;
    $tot_outstanding_amount = 0.00;
    foreach ($totalData as $dtps){
        $totalfare = $dtps['fTotalGenerateFare'];
        $fOffersDiscount = $dtps['fOffersDiscount'];
        $fDeliveryCharge = $dtps['fDeliveryCharge'];
        $site_commission = $dtps['fCommision'];
        $totaltipamount = $dtps['fTipAmount'];
        $fRestaurantPayAmount = $dtps['fRestaurantPayAmount'];
        $fRestaurantPaidAmount = $dtps['fRestaurantPaidAmount'];
        $fOutStandingAmount = $dtps['fOutStandingAmount'];
        if ($dtps['iStatusCode'] == '7' || $dtps['iStatusCode'] == '8'){
            $fRestexpectedearning = $fRestaurantPayAmount;
        }else{
            $fRestexpectedearning = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount);
        }
        if ($dtps['iStatusCode'] == '7' || $dtps['iStatusCode'] == '8'){
            $restaurant_payment = $fRestaurantPaidAmount;
        }else{
            $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount) - cleanNumber($totaltipamount);
        }
        $tot_order_amount = $tot_order_amount + cleanNumber($totalfare);
        $tot_offer_discount = $tot_offer_discount + cleanNumber($fOffersDiscount);
        $tot_delivery_charges = $tot_delivery_charges + cleanNumber($fDeliveryCharge);
        $tot_site_commission = $tot_site_commission + cleanNumber($site_commission);
        $expected_rest_payment = $expected_rest_payment + cleanNumber($fRestexpectedearning);
        $tot_restaurant_payment = $tot_restaurant_payment + cleanNumber($restaurant_payment);
        $tot_outstanding_amount = $tot_outstanding_amount + cleanNumber($fOutStandingAmount);
        $tot_tip_amount = $totaltipamount + cleanNumber($totaltipamount);
    }
    $total_results = $totalData[0]['Total'];
    $total_pages = ceil($total_results / $per_page);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                //total pages we going to have
    $show_page = 1;
    //-------------if page is setcheck------------------//
    if (isset($_GET['page'])){
        $show_page = $_GET['page'];             //it will telles the current page
        if ($show_page > 0 && $show_page <= $total_pages){
            $start = ($show_page - 1) * $per_page;
            $end = $start + $per_page;
        }else{
            // error - show first set of results
            $start = 0;
            $end = $per_page;
        }
    }else{
        // if page isn't set, show first set of results
        $start = 0;
        $end = $per_page;
    }
    // display pagination
    $page = isset($_GET['page'])?intval($_GET['page']):0;
    $tpages = $total_pages;
    if ($page <= 0){
        $page = 1;
    }
    //Pagination End
    $sql = "SELECT o.iOrderId,o.vOrderNo,o.fTipAmount,o.iCompanyId,sc.vServiceName_".$default_lang." as vServiceName,o.iDriverId,o.iUserId,o.tOrderRequestDate,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.fOutStandingAmount,o.fTotalGenerateFare,o.fDeliveryCharge,o.fOffersDiscount,o.fCommision,o.eRestaurantPaymentStatus,o.ePaymentOption,o.iStatusCode,os.vStatus,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,CONCAT(ru.vPhoneCode,' ',ru.vPhone)  as user_phone,CONCAT(rd.vCode,' ',rd.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone,o.vTimeZone FROM orders AS o LEFT JOIN register_driver AS rd ON o.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON o.iUserId = ru.iUserId LEFT JOIN company as c ON o.iCompanyId = c.iCompanyId LEFT JOIN order_status as os on os.iStatusCode=o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND (o.iStatusCode = '6') $ssql $trp_ssql $ord "; //LIMIT $start, $per_page
    //OR o.fRestaurantPayAmount > 0
    $db_trip = $obj->MySQLSelect($sql);
    $endRecord = scount($db_trip);
    $var_filter = "";
    foreach ($_REQUEST as $key => $val){
        if ($key != "tpages" && $key != 'page'){
            $var_filter .= "&$key=".stripslashes($val);
        }
    }
    $reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
    $Today = Date('Y-m-d');
    $tdate = date("d") - 1;
    $mdate = date("d");
    $Yesterday = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - 1,date("Y")));
    $curryearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y")));
    $curryearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y")));
    $prevyearFDate = date("Y-m-d",mktime(0,0,0,'1','1',date("Y") - 1));
    $prevyearTDate = date("Y-m-d",mktime(0,0,0,"12","31",date("Y") - 1));
    $currmonthFDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $tdate,date("Y")));
    $currmonthTDate = date("Y-m-d",mktime(0,0,0,date("m") + 1,date("d") - $mdate,date("Y")));
    $prevmonthFDate = date("Y-m-d",mktime(0,0,0,date("m") - 1,date("d") - $tdate,date("Y")));
    $prevmonthTDate = date("Y-m-d",mktime(0,0,0,date("m"),date("d") - $mdate,date("Y")));
    $monday = date('Y-m-d',strtotime('sunday this week -1 week'));
    $sunday = date('Y-m-d',strtotime('saturday this week'));
    $Pmonday = date('Y-m-d',strtotime('sunday this week -2 week'));
    $Psunday = date('Y-m-d',strtotime('saturday this week -1 week'));
    $catdata = serviceCategories;
    $allservice_cat_data = json_decode($catdata,true);
    //Added By HJ On 26-08-2019 For Changed Word Of Card As Per Bug - 225 server 6736 Start
    $cardText = "Card";
    if ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3'){
        $cardText = "Wallet";
    }
    if (scount($allservice_cat_data) > 1){
        $header .= "Service Type"."\t";
    }
    $header .= "Order No#"."\t";
    $header .= "Restaurant"."\t";
    $header .= "Driver"."\t";
    $header .= "User"."\t";
    $header .= "Order Date"."\t";
    $header .= "A=Total Order Amount"."\t";
    $header .= "B=Site Commission"."\t";
    $header .= "C=Delivery Charges"."\t";
    $header .= "D=Offer Amount"."\t";
    $header .= "E=Outstanding Amount"."\t";
    $header .= "F=Tip Amount"."\t";
    $header .= "G=A-B-C-D-E-F Final Restaurant Pay Amount"."\t";
    $header .= "Order Status"."\t";
    $header .= "Payment method"."\t";
    $header .= "Restaurant Payment Status"."\t";
    if (scount($db_trip) > 0){
        $systemTimeZone = date_default_timezone_get();
        for ($i = 0;$i < scount($db_trip);$i++){

            $class_setteled = "";
            if ($db_trip[$i]['eRestaurantPaymentStatus'] == 'Settled'){
                $class_setteled = "setteled-class";
            }
            $totalfare = $db_trip[$i]['fTotalGenerateFare'];
            $site_commission = $db_trip[$i]['fCommision'];
            $fOffersDiscount = $db_trip[$i]['fOffersDiscount'];
            $fDeliveryCharge = $db_trip[$i]['fDeliveryCharge'];
            $fOutStandingAmount = $db_trip[$i]['fOutStandingAmount'];
            $fTipAmount = $db_trip[$i]['fTipAmount'];
            //     /* if($db_trip[$i]['iStatusCode'] == '7' || $db_trip[$i]['iStatusCode'] == '8') {
            //       $expectedpaymentamount  = $db_trip[$i]['fRestaurantPayAmount'];
            //       } else {
            //       $expectedpaymentamount = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge);
            //       } */
            if ($db_trip[$i]['iStatusCode'] == '7' || $db_trip[$i]['iStatusCode'] == '8'){
                $restaurant_payment = $db_trip[$i]['fRestaurantPaidAmount'];
            }else{
                $restaurant_payment = $totalfare - cleanNumber($site_commission) - cleanNumber($fOffersDiscount) - cleanNumber($fDeliveryCharge) - cleanNumber($fOutStandingAmount) - cleanNumber($fTipAmount);
            }
            $set_unsetarray[] = $db_trip[$i]['eRestaurantPaymentStatus'];
            if (!empty($db_trip[$i]['drivername'])){
                $drivername = $db_trip[$i]['drivername'];
            }else{
                $drivername = '--';
            }
            if ($db_trip[$i]['resturant_phone'] != ''){
                $vCompany = clearCmpName($db_trip[$i]['vCompany']);
                $vCompany .= '    '.'  ';
                $vCompany .= '   Phone: +'.clearPhone($db_trip[$i]['resturant_phone']);
            }else{
                $vCompany = clearCmpName($db_trip[$i]['vCompany']);
            }
            if ($db_trip[$i]['driver_phone'] != ''){
                $vDriverName = clearName($drivername);
                $vDriverName .= "   ";
                $vDriverName .= ' Phone: +'.clearPhone($db_trip[$i]['driver_phone']);
            }else{
                $vDriverName = clearName($drivername);
            }
            if ($db_trip[$i]['user_phone'] != ''){
                $vRiderName = clearName($db_trip[$i]['riderName']);
                $vRiderName .= "    ";
                $vRiderName .= ' Phone: +'.clearPhone($db_trip[$i]['user_phone']);
            }else{
                $vRiderName = clearName($db_trip[$i]['riderName']);
            }
            if ($db_trip[$i]['fTotalGenerateFare'] != "" && $db_trip[$i]['fTotalGenerateFare'] != 0){
                $vfTotalGenerateFare = formateNumAsPerCurrency($db_trip[$i]['fTotalGenerateFare'],'');
            }else{
                $vfTotalGenerateFare = '-';
            }
            if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0){
                $vfCommision = formateNumAsPerCurrency($db_trip[$i]['fCommision'],'');
            }else{
                $vfCommision = '-';
            }
            if ($db_trip[$i]['fDeliveryCharge'] != "" && $db_trip[$i]['fDeliveryCharge'] != 0){
                $vfDeliveryCharge = formateNumAsPerCurrency($db_trip[$i]['fDeliveryCharge'],'');
            }else{
                $vfDeliveryCharge = '-';
            }
            if ($db_trip[$i]['fOffersDiscount'] != "" && $db_trip[$i]['fOffersDiscount'] != 0){
                $vfOffersDiscount = formateNumAsPerCurrency($db_trip[$i]['fOffersDiscount'],'');
            }else{
                $vfOffersDiscount = '-';
            }
            if ($db_trip[$i]['fOutStandingAmount'] != "" && $db_trip[$i]['fOutStandingAmount'] != 0){
                $vfOutStandingAmount = formateNumAsPerCurrency($db_trip[$i]['fOutStandingAmount'],'');
            }else{
                $vfOutStandingAmount = '-';
            }
            if ($db_trip[$i]['fTipAmount'] != "" && $db_trip[$i]['fTipAmount'] != 0){
                $vfTipAmount = formateNumAsPerCurrency($db_trip[$i]['fTipAmount'],'');
            }else{
                $vfTipAmount = '-';
            }
            if ($restaurant_payment != "" && $restaurant_payment != 0){
                $vrestaurant_payment = formateNumAsPerCurrency($restaurant_payment,'');
            }else{
                $vrestaurant_payment = '-';
            }
            $ePaymentOption = $db_trip[$i]['ePaymentOption'];
            if ($db_trip[$i]['ePaymentOption'] == 'Card'){
                $ePaymentOption = $cardText;
            }
            if (scount($allservice_cat_data) > 1){
                $data .= $db_trip[$i]['vServiceName']."\t";
            }
            $data .= $db_trip[$i]['vOrderNo']."\t";
            $data .= $vCompany."\t";
            $data .= $vDriverName."\t";
            $data .= $vRiderName."\t";
            $date_format_data_array = array('tdate'            => (!empty($db_trip[$i]['vTimeZone']))?converToTz($db_trip[$i]['tOrderRequestDate'],$db_trip[$i]['vTimeZone'],$systemTimeZone):$db_trip[$i]['tOrderRequestDate'],
                                            'langCode'         => $default_lang,
                                            'DateFormatForWeb' => 1);
            $get_tOrderRequestDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
            $data .= $get_tOrderRequestDate_date_format['tDisplayDate'].$time_zone_difference_text."\t";//DateTime($db_trip[$i]['tOrderRequestDate']) . "\t";
            $data .= $vfTotalGenerateFare."\t";
            $data .= $vfCommision."\t";
            $data .= $vfDeliveryCharge."\t";
            $data .= $vfOffersDiscount."\t";
            $data .= $vfOutStandingAmount."\t";
            $data .= $vfTipAmount."\t";
            $data .= $vrestaurant_payment."\t";
            $data .= $db_trip[$i]['vStatus']."\t";
            $data .= $ePaymentOption."\t";
            $data .= $db_trip[$i]['eRestaurantPaymentStatus']."\n";
        }
        $data .= "\n\n\n";
        $data .= "Total Fare : ".formateNumAsPerCurrency($tot_order_amount,'')."\n";
        $data .= "Total Site Commission : ".formateNumAsPerCurrency($tot_site_commission,'')."\n";
        $data .= "Total Delivery Charges : ".formateNumAsPerCurrency($tot_delivery_charges,'')."\n";
        $data .= "Total Offer Amount : ".formateNumAsPerCurrency($tot_offer_discount,'')."\n";
        $data .= "Total Outstanding Amount : ".formateNumAsPerCurrency($tot_outstanding_amount,'')."\n";
        $data .= "Total ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." Payment: ".formateNumAsPerCurrency($tot_restaurant_payment,'')."\n";
        $data = str_replace("\r","",$data);
    }
    $timenow = time();
    $filename = "restaurant_payment_report_".$timenow.".xls";
    ob_clean();
    header("Content-type: application/octet-stream; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == 'driver_log_report'){

    $dlp_ssql = "";
    $ord = ' ORDER BY dlr.iDriverLogId DESC';
    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY dlr.iDriverLogId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY rd.vEmail ASC";
        }else{
            $ord = " ORDER BY rd.vEmail DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY dlr.dLoginDateTime ASC";
        }else{
            $ord = " ORDER BY dlr.dLoginDateTime DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY dlr.dLogoutDateTime ASC";
        }else{
            $ord = " ORDER BY dlr.dLogoutDateTime DESC";
        }
    }
    // Start Search Parameters
    $ssql = '';
    $iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $vEmail = isset($_REQUEST['vEmail'])?$_REQUEST['vEmail']:'';
    if ($startDate != '' && $endDate != ''){
        $search_startDate = $startDate.' 00:00:00';
        $search_endDate = $endDate.' 23:59:00';
        $ssql .= " AND dlr.dLoginDateTime BETWEEN '".$search_startDate."' AND '".$search_endDate."'";
    }
    if ($iDriverId != ''){
        $ssql .= " AND rd.iDriverId = '".$iDriverId."'";
    }
    if ($vEmail != ''){
        $ssql .= " AND rd.vEmail = '".$vEmail."'";
    }
    //$sql_admin = "SELECT * from dlips WHERE 1=1 ".$ssql." ORDER BY iDriverLogId DESC";
    $sql = "SELECT rd.vName, rd.vLastName, rd.vEmail, dlr.dLoginDateTime, dlr.dLogoutDateTime
                        FROM driver_log_report AS dlr
                        LEFT JOIN register_driver AS rd ON rd.iDriverId = dlr.iDriverId where 1=1 AND rd.eStatus != 'Deleted' $ssql $ord";
    $db_dlip = $obj->MySQLSelect($sql);
    #echo "<pre>";print_r($db_dlip); exit;
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name"."\t";
    $header .= "Email"."\t";
    $header .= "Log DateTime"."\t";
    $header .= "Logout TimeDate"."\t";
    $header .= "Total Hours Login"."\t";
    for ($j = 0;$j < scount($db_dlip);$j++){

        $dstart = $db_dlip[$j]['dLoginDateTime'];
        if ($db_dlip[$j]['dLogoutDateTime'] == '0000-00-00 00:00:00' || $db_dlip[$j]['dLogoutDateTime'] == ''){
            $dLogoutDateTime = '--';
            $totalTimecount = '--';
        }else{

            $dLogoutDateTime = $db_dlip[$j]['dLogoutDateTime'];
            $totalhours = get_left_days_jobsave($dLogoutDateTime,$dstart);
            $totalTimecount = mediaTimeDeFormater($totalhours);
        }
        $data .= clearName($db_dlip[$j]['vName'].'  '.$db_dlip[$j]['vLastName'])."\t";
        $data .= clearEmail($db_dlip[$j]['vEmail'])."\t";
        $data .= DateTime($db_dlip[$j]['dLoginDateTime'])."\t";
        $data .= DateTime($db_dlip[$j]['dLogoutDateTime'])."\t";
        $data .= $totalTimecount."\n";
    }
    ob_clean();
    header("Content-type: application/octet-sdleam; charset=utf-8");
    header("Content-Disposition: attachment; filename= driver_log_report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == 'user_reward'){

    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY tr.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $searchCompany = isset($_REQUEST['searchCompany'])?$_REQUEST['searchCompany']:'';
    $searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
    $searchDriverPayment = isset($_REQUEST['searchDriverPayment'])?$_REQUEST['searchDriverPayment']:'';
    $searchPaymentType = isset($_REQUEST['searchPaymentType'])?$_REQUEST['searchPaymentType']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    if ($action == 'search'){
        if ($startDate != ''){
            $ssql .= " AND Date(tr.tTripRequestDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(tr.tTripRequestDate) <='".$endDate."'";
        }
        if ($serachTripNo != ''){
            if (strpos($serachTripNo,',') !== false){
                $serachTripNoArr = str_replace(",","','",$serachTripNo);
                $ssql .= " AND tr.vRideNo IN ('".$serachTripNoArr."')";
            }else{
                $ssql .= " AND tr.vRideNo ='".$serachTripNo."'";
            }
        }
        if ($searchRider != ''){
            $ssql .= " AND tr.iUserId ='".$searchRider."'";
        }
    }
    $ssql .= " AND tr.eType = 'Ride'";
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And tr.tTripRequestDate > '".WEEK_DATE."'";
    }
    $etypeSql = " AND tr.eSystem = 'General'";
    $sql = "SELECT tr.fUserRewardsCoins,tr.fCancellationFare,tr.iFromStationId,tr.iToStationId,tr.ePayWallet,tr.iFare, tr.fTax1,tr.fTax2,tr.iOrganizationId,tr.ePoolRide,tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iUserId,tr.tTripRequestDate,tr.tStartDate,tr.tEndDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eDriverPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,u.iBalance,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN user_wallet AS u ON u.iTripId = tr.iTripId WHERE (tr.iActive ='Finished' OR (tr.iActive ='Canceled' AND tr.iFare > 0) OR (tr.iActive ='Canceled' AND tr.fWalletDebit > 0 AND tr.iFare = 0)) AND (tr.fUserRewardsCoins > 0 AND u.tDescription LIKE '%LBL_REWARD_AMOUNT_CREDITED_USER%') $etypeSql $ssql $trp_ssql $ord "; //OR u.tDescription LIKE '%LBL_REWARD_AMOUNT_CREDITED_USER%'
    $db_trip = $obj->MySQLSelect($sql) or die('Query failed!');
    $totalCoins = $tot_fare = 0;
    $set_unsetarray = array();
    /* $header .= $langage_lbl_admin['LBL_RIDE_NO_ADMIN'] . "\t";
                                    $header .= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] . "\t";
                                    $header .= "Coin Earned" . "\t";
                                    $header .= "Amount Transferred to Wallet" . "\t";
                                    $header .= " Date of Coins Earned" . "\t"; */
    $flag = false;
    $filename = "user_reward_report.xls";
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$langage_lbl_admin['LBL_RIDE_NO_ADMIN']);
    $sheet->setCellValue('B1',$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']);
    $sheet->setCellValue('C1','Coin Earned');
    $sheet->setCellValue('D1','Amount Transferred to Wallet');
    $sheet->setCellValue('E1','Date of Coins Earned');
    $j = 2;
    if (scount($db_trip) > 0){
        for ($i = 0;$i < scount($db_trip);$i++){
            $iTripId = $db_trip[$i]['iTripId'];
            $eTypenew = $db_trip[$i]['eType'];
            $systemTimeZone = date_default_timezone_get();
            if ($db_trip[$i]['fCancellationFare'] > 0 && $db_trip[$i]['vTimeZone'] != ""){
                $dBookingDate = converToTz($db_trip[$i]['tEndDate'],$db_trip[$i]['vTimeZone'],$systemTimeZone);
            }else if ($db_trip[$i]['tStartDate'] != "" && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00" && $db_trip[$i]['vTimeZone'] != ""){
                $dBookingDate = $db_trip[$i]['tStartDate'];
            }else{
                if (!empty($db_trip[$i]['tStartDate']) && $db_trip[$i]['tStartDate'] != "0000-00-00 00:00:00"){
                    $dBookingDate = $db_trip[$i]['tStartDate'];
                }else{
                    $dBookingDate = $db_trip[$i]['tTripRequestDate'];
                }
            }
            $tot_fare += $db_trip[$i]['iBalance'];
            $totalCoins += $db_trip[$i]['fUserRewardsCoins'];
            if ($db_trip[$i]['iBalance'] != "" && $db_trip[$i]['iBalance'] != 0){
                $totFareHtml = formateNumAsPerCurrency($db_trip[$i]['iBalance'],'');
            }else{
                $totFareHtml = "-";
            }
            $result['no'] = $db_trip[$i]['vRideNo'];
            $result['name'] = $db_trip[$i]['riderName'];
            $result['reward'] = $db_trip[$i]['fUserRewardsCoins'];
            $result['tot_fare'] = $totFareHtml;
            $date_format_data_array = array('langCode'         => $default_lang,
                                            'DateFormatForWeb' => 1);
            $date_format_data_array['tdate'] = $dBookingDate;
            $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $get_utc_time = DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']);
            $time_zone_difference_text = !empty($get_utc_time)?" (UTC:".$get_utc_time.")":"";
            $result['date'] = $get_dBookingDate_format['tDisplayDateTime'].$time_zone_difference_text;
            // $data .= DateTime($dBookingDate, '7') . "\t";
            $sheet->setCellValue('A'.$j,$result['no']);
            $sheet->setCellValue('B'.$j,$result['name']);
            $sheet->setCellValue('C'.$j,$result['reward']);
            $sheet->setCellValue('D'.$j,$result['tot_fare']);
            $sheet->setCellValue('E'.$j,$result['date']);
            $j++;
        }
    }
    $j += 1;
    $summary_array = array('Total Reward Amount ' => formateNumAsPerCurrency($tot_fare,''),
                           'Total Coins '         => $totalCoins);
    foreach ($summary_array as $key => $value){
        $sheet->setCellValue('D'.$j,$key);
        $sheet->setCellValue('E'.$j,$value);
        $j++;
    }
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'cancelled_trip'){

    $dlp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $dlp_ssql = " And dl.dLoginDateTime > '".WEEK_DATE."'";
    }
    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY t.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY t.tTripRequestDate ASC";
        }else{
            $ord = " ORDER BY t.tTripRequestDate DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY t.eCancelledBy ASC";
        }else{
            $ord = " ORDER BY t.eCancelledBy DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY t.vCancelReason ASC";
        }else{
            $ord = " ORDER BY t.vCancelReason DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY t.eType ASC";
        }else{
            $ord = " ORDER BY t.eType DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $vStatus = isset($_REQUEST['vStatus'])?$_REQUEST['vStatus']:'';
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    if ($action == 'search'){
        if ($startDate != ''){
            $ssql .= " AND Date(t.tTripRequestDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(t.tTripRequestDate) <='".$endDate."'";
        }
        if ($iDriverId != ''){
            $ssql .= " AND t.iDriverId ='".$iDriverId."'";
        }
        if ($serachTripNo != ''){
            $ssql .= " AND t.vRideNo ='".$serachTripNo."'";
        }
        if ($eType != ''){
            $ssql .= " AND t.eType ='".$eType."'";
        }
    }
    $locations_where = "";
    if (scount($userObj->locations) > 0){
        $locations = implode(', ',$userObj->locations);
        $ssql .= " AND EXISTS(SELECT * FROM vehicle_type WHERE trips.iVehicleTypeId = vehicle_type.iVehicleTypeId AND vehicle_type.iLocationid IN(-1, {$locations}))";
    }
    //     $sql_admin = "SELECT t.tTripRequestDate,t.tStartDate ,t.tEndDate,t.eHailTrip,t.eCancelled,t.vCancelReason,t.vCancelComment,d.iDriverId, t.tSaddress,t.vRideNo,t.eType,t.eCancelledBy, t.tDaddress, t.fWalletDebit,t.eCarType,t.iTripId,t.iActive ,CONCAT(d.vName,' ',d.vLastName) AS dName FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId
    // WHERE 1=1 And t.iActive='Canceled' $ssql $trp_ssql $ord ";
    $sql_admin = "SELECT t.ePoolRide,t.tTripRequestDate ,t.tEndDate,t.eCancelled,t.vCancelReason,t.vCancelComment,t.eHailTrip,d.iDriverId, t.tSaddress,t.vRideNo,t.eCancelledBy,t.tDaddress, t.fWalletDebit,t.eCarType,t.iTripId,t.iActive, t.eType ,CONCAT(d.vName,' ',d.vLastName) AS dName,t.fCancellationFare,cr.vTitle_EN as cancel_reason_title,t.vTimeZone FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId LEFT JOIN cancel_reason cr ON cr.iCancelReasonId = t.iCancelReasonId WHERE 1=1 AND (t.iActive='Canceled' OR t.eCancelled='yes') AND t.eSystem = 'General' $ssql $trp_ssql $ord";
    $db_dlip = $obj->MySQLSelect($sql_admin);
    // echo "<pre>";print_r($db_dlip); exit;
    $filename = "cancelled_trip_".$timestamp_filename.".xls";
    //$result = $obj->MySQLSelect($sql) or die('Query Failed!');
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']);
    $sheet->setCellValue('B1',$langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Date");
    $sheet->setCellValue('C1',"Cancel By");
    $sheet->setCellValue('D1',"Cancel Reason");
    $sheet->setCellValue('E1',$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name");
    $sheet->setCellValue('F1',$langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." No");
    $sheet->setCellValue('G1','Address');
    $i = 2;
    $serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($db_dlip);$j++){

        $eTypenew = $db_dlip[$j]['eType'];
        if ($eTypenew == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eTypenew == 'UberX'){
            $trip_type = 'Other Services';
        }else if ($eTypenew == 'Multi-Delivery'){
            $trip_type = 'Multi-Delivery';
        }else{
            $trip_type = 'Delivery';
        }
        if ($eTypenew == 'Multi-Delivery' && $ENABLE_MULTI_VIEW_IN_SINGLE_DELIVERY == 'Yes'){
            $db_deliveryloc = $obj->MySQLSelect("SELECT * FROM `trips_delivery_locations` WHERE `iTripId` = ".$db_dlip[$i]['iTripId']);
            if (scount($db_deliveryloc) == 1){
                $trip_type = 'Delivery';
            }
        }
        $poolTxt = "";
        if ($db_dlip[$j]['ePoolRide'] == "Yes"){
            $poolTxt = " (Pool)";
        }
        $trip_type .= $poolTxt;
        $vCancelReason = $db_dlip[$j]['cancel_reason_title'];
        $trip_cancel = ($vCancelReason != '')?$vCancelReason:$db_dlip[$j]['vCancelReason'];
        $trip_cancel = ($trip_cancel != '')?$trip_cancel:'---';
        $eCancelled = $db_dlip[$j]['eCancelled'];
        //$CanceledBy = ($eCancelled == 'Yes' && $vCancelReason != '' ) ? $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] : $langage_lbl_admin['LBL_RIDER'];
        //$CanceledBy = $db_dlip[$j]['eCancelledBy']; //added by SP on 28-06-2019
        $CanceledBy = !empty($db_dlip[$j]['eCancelledBy'])?$db_dlip[$j]['eCancelledBy']:$langage_lbl_admin['LBL_ADMIN'];
        if ($db_dlip[$j]['eCancelledBy'] == "Passenger"){
            $CanceledBy = $langage_lbl_admin['LBL_RIDER'];
        }else if ($db_dlip[$j]['eCancelledBy'] == "Driver"){
            $CanceledBy = $langage_lbl_admin['LBL_DRIVER'];
        }
        if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery'){
            if ($db_dlip[$j]['eHailTrip'] != "Yes"){
                $result['trip_type'] = $trip_type;
            }else{
                $result['trip_type'] = $trip_type." ( Hail )";
            }
        }
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $date_format_data_array['tdate'] = (!empty($db_dlip[$j]['vTimeZone']) && $db_dlip[$j]['tTripRequestDate'] != "0000-00-00 00:00:00")?converToTz($db_dlip[$j]['tTripRequestDate'],$db_dlip[$j]['vTimeZone'],$serverTimeZone):$db_dlip[$j]['tTripRequestDate'];
        $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_dlip[$j]['vTimeZone'],$date_format_data_array['tdate']).")";
        $result['date'] = $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text."\t";
        //$data .= DateTime($db_dlip[$j]['tTripRequestDate'], 'no') . "\t";
        $result['CanceledBy'] = $CanceledBy."\t";
        $result['CANCEL'] = $trip_cancel."\t";
        $result['name'] = clearName($db_dlip[$j]['dName'])."\t";
        $result['no'] = $db_dlip[$j]['vRideNo']."\t";
        $str = "";
        if ($db_dlip[$j]['tDaddress'] != ""){
            $str = ' -> '.$db_dlip[$j]['tDaddress'];
        }
        // $data .= $db_dlip[$j]['tSaddress'].$str;
        $string = $db_dlip[$j]['tSaddress'].$str;
        $result['address'] = str_replace(array("\n","\r","\r\n","\n\r"),' ',$string);
        $sheet->setCellValue('A'.$i,$result['trip_type']);
        $sheet->setCellValue('B'.$i,$result['date']);
        $sheet->setCellValue('C'.$i,$result['CanceledBy']);
        $sheet->setCellValue('D'.$i,$result['CANCEL']);
        $sheet->setCellValue('E'.$i,$result['name']);
        $sheet->setCellValue('F'.$i,$result['no']);
        $sheet->setCellValue('G'.$i,$result['address']);
        $i++;
    }
    // Auto-size columns
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'ride_acceptance_report'){

    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY rs.iDriverRequestId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $date1 = $startDate.' '."00:00:00";
    $date2 = $endDate.' '."23:59:59";
    if ($startDate != '' && $endDate != ''){
        $ssql .= " AND rs.tDate between '$date1' and '$date2'";
    }
    if ($iDriverId != ''){
        $ssql .= " AND rd.iDriverId = '".$iDriverId."'";
    }
    $chk_str_date = @date('Y-m-d H:i:s',strtotime('-'.$RIDER_REQUEST_ACCEPT_TIME.' second'));
    $sql_admin = "SELECT rd.iDriverId , rd.vLastName ,rd.vName ,
        COUNT(case when rs.eStatus = 'Accept' then 1 else NULL end) `Accept` ,
        COUNT(case when rs.eStatus != '' then 1 else NULL  end) `Total Request` ,
        COUNT(case when (rs.eStatus  = 'Decline' AND rs.eAcceptAttempted  = 'No') then 1 else NULL end) `Decline` ,
        COUNT(case when rs.eAcceptAttempted  = 'Yes' then 1 else NULL end) `Missed` ,
        COUNT(case when ((rs.eStatus  = 'Timeout' OR rs.eStatus  = 'Received') AND rs.eAcceptAttempted  = 'No' AND  rs.dAddedDate < '".$chk_str_date."')  then 1 else NULL end) `Timeout`,
        COUNT(case when ((rs.eStatus  = 'Timeout' OR rs.eStatus  = 'Received') AND rs.eAcceptAttempted  = 'No' AND rs.dAddedDate > '".$chk_str_date."' ) then 1 else NULL end) `inprocess`
        FROM driver_request rs left join register_driver rd on rd.iDriverId=rs.iDriverId  
        WHERE 1=1 $ssql GROUP by rs.iDriverId $ord ";
    /*
      $sql_admin = "SELECT rd.iDriverId , rd.vLastName ,rd.vName ,
      COUNT(case when rs.eStatus = 'Accept' then 1 else NULL end) `Accept` ,
      COUNT(case when rs.eStatus != '' then 1 else NULL  end) `Total Request` ,
      COUNT(case when rs.eStatus  = 'Decline' then 1 else NULL end) `Decline` ,
      COUNT(case when rs.eStatus  = 'Timeout' then 1 else NULL end) `Timeout`
      FROM register_driver rd
      left join driver_request rs on rd.iDriverId=rs.iDriverId
      WHERE 1=1 $ssql GROUP by rs.iDriverId $ord "; */
    $db_dlip = $obj->MySQLSelect($sql_admin);
    #echo "<pre>";print_r($db_dlip); exit;
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name"."\t";
    $header .= "Total ".$langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Requests"."\t";
    $header .= "Requests Accepted"."\t";
    $header .= "Requests Decline"."\t";
    $header .= "Requests Timeout"."\t";
    $header .= "Missed Attempts"."\t";
    $header .= "In Process Request"."\t";
    $header .= "Acceptance Percentage"."\t";
    $total_trip_req = "";
    $total_trip_acce_req = "";
    $total_trip_dec_req = "";
    for ($j = 0;$j < scount($db_dlip);$j++){

        $sql_acp = "SELECT COUNT(case when t.eCancelled = 'Yes' then 1 else NULL end) `Cancel` , COUNT(case when t.eCancelled != '' then 1 else NULL  end) `Finish` FROM trips t  where t.iDriverId='".$db_dlip[$j]['iDriverId']."'";
        $db_acp = $obj->MySQLSelect($sql_acp);
        $Accept = $db_dlip[$j]['Accept'];
        $tAccept = $tAccept + $Accept;
        $Request = $db_dlip[$j]['Total Request'];
        $tRequest = $tRequest + $Request;
        $Decline = $db_dlip[$j]['Decline'];
        $tDecline = $tDecline + $Decline;
        $Timeout = $db_dlip[$j]['Timeout'];
        $tTimeout = $tTimeout + $Timeout;
        $Cancel = $db_acp[0]['Cancel'];
        $tCancel = $tCancel + $Cancel;
        $missed = $db_dlip[$j]['Missed'];
        $tmissed = $tmissed + $missed;
        $inprocess = $db_dlip[$j]['inprocess'];
        $tinprocess = $tinprocess + $inprocess;
        $Finish = $db_acp[0]['Finish'];
        $tFinish = $tFinish + $Finish;
        $aceptance_percentage = (100 * ($Accept)) / $Request;
        $data .= clearName($db_dlip[$j]['vName'].' '.$db_dlip[$j]['vLastName'])."\t";
        $data .= $Request."\t";
        $data .= $Accept."\t";
        $data .= $Decline."\t";
        $data .= $Timeout."\t";
        $data .= $missed."\t";
        $data .= $inprocess."\t";
        $data .= round($aceptance_percentage,2).' %'."\n";
    }
    ob_clean();
    header("Content-type: application/octet-sdleam; charset=utf-8");
    header("Content-Disposition: attachment; filename=ride_acceptance_report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == 'driver_trip_detail'){

    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $ord = ' ORDER BY t.tStartdate DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY t.tStartDate ASC";
        }else{
            $ord = " ORDER BY t.tStartDate DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    //End Sorting
    $cmp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $cmp_ssql = " And t.tStartDate > '".WEEK_DATE."'";
    }
    // Start Search Parameters
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $iDriverId = isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $date1 = $startDate.' '."00:00:00";
    $date2 = $endDate.' '."23:59:59";
    if ($startDate != ''){
        $ssql .= " AND Date(t.tStartDate) >='".$startDate."'";
    }
    if ($endDate != ''){
        $ssql .= " AND Date(t.tStartDate) <='".$endDate."'";
    }
    if ($iDriverId != ''){
        $ssql .= " AND d.iDriverId = '".$iDriverId."'";
    }
    if ($serachTripNo != ''){
        $ssql .= " AND t.vRideNo ='".$serachTripNo."'";
    }
    $locations_where = "";
    if (scount($userObj->locations) > 0){
        $locations = implode(', ',$userObj->locations);
        $ssql .= " AND vt.iLocationid IN(-1, {$locations})";
    }
    $sql_admin = "SELECT u.vName, u.vLastName, d.vAvgRating,t.fGDtime,t.tStartdate,t.tEndDate, t.tTripRequestDate, t.iFare, d.iDriverId, t.tSaddress,t.vRideNo, t.tDaddress, d.vName AS name,c.vName AS comp,c.vCompany, d.vLastName AS lname,t.eCarType,t.iTripId,vt.vVehicleType,t.iActive FROM register_driver d RIGHT JOIN trips t ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId
                 WHERE 1=1 AND t.iActive = 'Finished' AND t.eCancelled='No' $ssql $cmp_ssql $ord ";
    $db_dlip = $obj->MySQLSelect($sql_admin);
    #echo "<pre>";print_r($db_dlip); exit;
    $header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']."  No"."\t";
    $header .= "Address"."\t";
    $header .= $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']."  Date"."\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."\t";
    $header .= "Estimated Time"."\t";
    $header .= "Actual Time"."\t";
    $header .= "Variance"."\t";
    for ($j = 0;$j < scount($db_dlip);$j++){

        $data .= $db_dlip[$j]['vRideNo']."\t";
        $data .= $db_dlip[$j]['tSaddress'].' -> '.$db_dlip[$j]['tDaddress']."\t";
        $data .= DateTime($db_dlip[$j]['tStartdate'])."\t";
        $data .= clearName($db_dlip[$j]['name']." ".$db_dlip[$j]['lname'])."\t";
        $ans = set_hour_min($db_dlip[$j]['fGDtime']);
        if ($ans['hour'] != 0){
            $ans1 = $ans['hour']." Hours ".$ans['minute']." Minutes";
        }else{
            $ans1 = '';
            if ($ans['minute'] != 0){
                $ans1 .= $ans['minute']." Minutes ";
            }
            $ans1 .= $ans['second']." Seconds";
        }
        $data .= $ans1."\t";
        $a = strtotime($db_dlip[$j]['tStartdate']);
        $b = strtotime($db_dlip[$j]['tEndDate']);
        $diff_time = ($b - $a);
        //$diff_time=$diff_time*1000;
        $ans_diff = set_hour_min($diff_time);
        //print_r($ans);exit;
        if ($ans_diff['hour'] != 0){
            $ans_diff12 = $ans_diff['hour']." Hours ".$ans_diff['minute']." Minutes";
        }else{
            $ans_diff12 = '';
            if ($ans_diff['minute'] != 0){
                $ans_diff12 .= $ans_diff['minute']." Minutes ";
            }
            $ans_diff12 .= $ans_diff['second']." Seconds";
        }
        $data .= $ans_diff12."\t";
        $ori_time = $db_dlip[$j]['fGDtime'];
        $tak_time = $diff_time;
        $ori_diff = $ori_time - $tak_time;
        echo $ans_ori = set_hour_min(abs($ori_diff));
        if ($ans_ori['hour'] != 0){
            $ans2 .= $ans_ori['hour']." Hours ".$ans_ori['minute']." Minutes";
            if ($ori_diff < 0){
                $ans2 .= " Late";
            }else{

                $ans2 .= " Early";
            }
        }else{
            $ans2 = '';
            if ($ans_ori['minute'] != 0){
                $ans2 .= $ans_ori['minute']." Minutes ";
            }
            $ans2 .= $ans_ori['second']." Seconds";
            if ($ori_diff < 0){
                $ans2 .= " Late";
            }else{
                $ans2 .= " Early";
            }
        }
        $data .= $ans2."\n";
    }
    ob_clean();
    header("Content-type: application/octet-sdleam; charset=utf-8");
    header("Content-Disposition: attachment; filename=driver_trip_detail.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == 'wallet_report'){

    $action = (isset($_REQUEST['action'])?$_REQUEST['action']:'');
    $ssql = '';
    if ($action != ''){

        $startDate = $_REQUEST['startDate'];
        $endDate = $_REQUEST['endDate'];
        $eUserType = $_REQUEST['eUserType'];
        $eFor = $_REQUEST['searchBalanceType'];
        $Payment_type = $_REQUEST['searchPaymentType'];
        if ($eUserType == "Driver"){

            $iDriverId = $_REQUEST['iDriverId'];
            $iUserId = "";
            $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($iDriverId,$eUserType);
        }
        if ($eUserType == "Rider"){

            $iUserId = $_REQUEST['iUserId'];
            $iDriverId = "";
            $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($iUserId,$eUserType);
        }
        if ($iDriverId != ''){
            $ssql .= " AND iUserId = '".$iDriverId."'";
        }
        if ($iUserId != ''){
            $ssql .= " AND iUserId = '".$iUserId."'";
        }
        if ($startDate != ''){
            $ssql .= " AND Date(dDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(dDate) <='".$endDate."'";
        }
        if ($eUserType){
            $ssql .= " AND eUserType = '".$eUserType."'";
        }
        if ($eFor != ''){
            $ssql .= " AND eFor = '".$eFor."'";
        }
        if ($Payment_type != ''){
            $ssql .= " AND eType = '".$Payment_type."'";
        }
    }
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    /*          $ord = ' ORDER BY iUserWalletId DESC'; */
    $ord = ' ORDER BY dDate ASC';
    $sql_admin = "SELECT * From user_wallet where 1=1 $ssql $ord ";
    $db_dlip = $obj->MySQLSelect($sql_admin) or die('Query failed!');
    $flag = false;
    $filename = $timestamp_filename.'.xls';
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1','Transaction Date');
    $sheet->setCellValue('B1','Description');
    $sheet->setCellValue('C1','Transaction ID');
    $sheet->setCellValue('D1',$langage_lbl_admin['LBL_TRIP_NO_ADMIN']);
    $sheet->setCellValue('E1','Amount');
    $sheet->setCellValue('F1','Purpose');
    $sheet->setCellValue('G1','Balance Type');
    $sheet->setCellValue('H1','Balance');
    $sheet->setCellValue('I1','Total Balance');
    $i = 2;
    //$serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($db_dlip);$j++){

        if ($db_dlip[$j]['eType'] == "Credit"){
            $db_dlip[$j]['currentbal'] = $prevbalance + $db_dlip[$j]['iBalance'];
        }else{
            $db_dlip[$j]['currentbal'] = $prevbalance - $db_dlip[$j]['iBalance'];
        }
        $prevbalance = $db_dlip[$j]['currentbal'];
        if ($db_dlip[$j]['iTripId'] > 0){
            $sql_query = "SELECT * FROM `trips` WHERE iTripId =".$db_dlip[$j]['iTripId'];
            $db_result_trips = $obj->MySQLSelect($sql_query);
            $ride_number = $db_result_trips[0]['vRideNo'];
        }else{
            $ride_number = '--';
        }
        if ($eUserType == 'Driver'){
            $sql_user_timezone = "SELECT vTimeZone From register_driver where iDriverId = '".$iDriverId."' ";
        }else{
            $sql_user_timezone = "SELECT vTimeZone From register_user where iUserId = '".$iUserId."' ";
        }
        //$sql_user_timezone = "SELECT vTimeZone From register_user where iUserId = ".$db_dlip[$j]['iUserId']." ";
        $db_utimezone = $obj->MySQLSelect($sql_user_timezone);
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        // $date_format_data_array['tdate'] = (!empty($db_dlip[$j]['vTimeZone'])) ? converToTz($db_dlip[$j]['dDate'],$db_dlip[$j]['vTimeZone'],$serverTimeZone) : $db_dlip[$j]['dDate'];
        $date_format_data_array['tdate'] = $db_dlip[$j]['dDate'];
        $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_utimezone[0]['vTimeZone'],$date_format_data_array['tdate']).")";
        $result['date'] = $get_date_format['tDisplayDateTime'].$time_zone_difference_text;
        //$data .= DateTime($db_dlip[$j]['dDate']) . "\t";
        $pat = '/\#([^\"]*?)\#/';
        preg_match($pat,$db_dlip[$j]['tDescription'],$tDescription_value);
        $tDescription_translate = $langage_lbl_admin[$tDescription_value[1]];
        $row_tDescription = str_replace($tDescription_value[0],$tDescription_translate,$db_dlip[$j]['tDescription']);
        if ($db_dlip[$j]['eFor'] == "Transfer"){
            if (preg_match($pat,$row_tDescription,$tDescription_value_new)){
                $tDescription_translate_second = $langage_lbl_admin[$tDescription_value_new[1]];
                $row_tDescription1 = str_replace($tDescription_value_new[0],$tDescription_translate_second,$row_tDescription);
            }else{
                $row_tDescription1 = $row_tDescription;
            }
            if (preg_match($pat,$row_tDescription1,$tDescription_value_other)){
                $tDescription_translate_last = $langage_lbl_admin[$tDescription_value_other[1]];
                $row_tDescriptionNew = str_replace($tDescription_value_other[0],$tDescription_translate_last,$row_tDescription1);
            }else{
                $row_tDescriptionNew = $row_tDescription1;
            }
        }
        if ($db_dlip[$j]['eFor'] == "Transfer"){
            $result['desc'] = $row_tDescriptionNew;
        }else{
            $result['desc'] = $row_tDescription;
        }
        $result['tPaymentUserID'] = $db_dlip[$j]['tPaymentUserID'];
        $result['ride_number'] = $ride_number;
        //$data .= formateNumAsPerCurrency($db_dlip[$j]['iBalance'],'') . "\t";
        $result['iBalance'] = formateNumAsPerCurrency($db_dlip[$j]['iBalance'],'');
        $result['eFor'] = $db_dlip[$j]['eFor'];
        $result['eType'] = $db_dlip[$j]['eType'];
        //$data .= formateNumAsPerCurrency($db_dlip[$j]['currentbal'],'') . "\n";
        $result['currentbal'] = formateNumAsPerCurrency($db_dlip[$j]['currentbal'],'');
        $sheet->setCellValue('A'.$i,$result['date']);
        $sheet->setCellValue('B'.$i,$result['desc']);
        $sheet->setCellValue('C'.$i,$result['tPaymentUserID']);
        $sheet->setCellValue('D'.$i,$result['ride_number']);
        $sheet->setCellValue('E'.$i,$result['iBalance']);
        $sheet->setCellValue('F'.$i,$result['eFor']);
        $sheet->setCellValue('G'.$i,$result['eType']);
        $sheet->setCellValue('H'.$i,$result['currentbal']);
        $i++;
    }
    $result['total'] = formateNumAsPerCurrency($user_available_balance,'');
    $sheet->setCellValue('I'.$i,$result['total']);
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'cab_booking'){
    $action = (isset($_REQUEST['action'])?$_REQUEST['action']:'');
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
    $keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:"";
    $eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
    $ord = ' ORDER BY cb.iCabBookingId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY cb.dBooking_date ASC";
        }else{
            $ord = " ORDER BY cb.dBooking_date DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY cb.vSourceAddresss ASC";
        }else{
            $ord = " ORDER BY cb.vSourceAddresss DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY cb.tDestAddress ASC";
        }else{
            $ord = " ORDER BY cb.tDestAddress DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY cb.eStatus ASC";
        }else{
            $ord = " ORDER BY cb.eStatus DESC";
        }
    }
    if ($sortby == 6){
        if ($order == 0){
            $ord = " ORDER BY cb.vBookingNo ASC";
        }else{
            $ord = " ORDER BY cb.vBookingNo DESC";
        }
    }
    if ($sortby == 7){
        if ($order == 0){
            $ord = " ORDER BY cb.eType ASC";
        }else{
            $ord = " ORDER BY cb.eType DESC";
        }
    }
    $adm_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $adm_ssql = " And cb.dAddredDate > '".WEEK_DATE."'";
    }
    if ($eType == 'RentalRide'){
        $eType_new = 'Ride';
        $sql11 = " AND cb.iRentalPackageId > 0";
    }else{
        $eType_new = $eType;
        $sql11 = 'AND cb.iRentalPackageId = 0';
    }
    $ssql = '';
    if ($keyword != ''){
        if ($option != ''){
            if ($eType_new != ''){
                $ssql .= " AND ".stripslashes($option)." LIKE '%".clean($keyword)."%' AND cb.eType = '".clean($eType_new)."' $sql11";
            }else{
                $ssql .= " AND ".stripslashes($option)." LIKE '%".clean($keyword)."%' $sql11";
            }
        }else{
            if ($eType_new != ''){
                $ssql .= " AND (CONCAT(ru.vName,' ',ru.vLastName) LIKE '%".clean($keyword)."%' OR cb.tDestAddress LIKE '%".clean($keyword)."%' OR cb.vSourceAddresss  LIKE '%".clean($keyword)."%' OR cb.vBookingNo LIKE '".clean($keyword)."' OR cb.eStatus LIKE '%".clean($keyword)."%') AND cb.eType = '".clean($eType_new)."' $sql11";
            }else{
                $ssql .= " AND (CONCAT(ru.vName,' ',ru.vLastName) LIKE '%".clean($keyword)."%' OR cb.tDestAddress LIKE '%".clean($keyword)."%' OR cb.vSourceAddresss  LIKE '%".clean($keyword)."%' OR cb.vBookingNo LIKE '".clean($keyword)."' OR cb.eStatus LIKE '%".clean($keyword)."%') $sql11";
            }
        }
    }else if ($eType_new != '' && $keyword == ''){
        $ssql .= " AND cb.eType = '".clean($eType_new)."' $sql11";
    }elseif ($option == 'cb.eStatus' && !empty($eStatus)){

        if ($eStatus == 'Expired'){ //changed by me
            $ssql .= " AND ((cb.eStatus LIKE '%Pending%' or cb.eStatus LIKE '%Accepted%') AND DATE( NOW( ) ) >= DATE_ADD( DATE( cb.dBooking_date ) , INTERVAL 10 MINUTE )) ".$sql11;
        }else if ($eStatus == 'Completed'){
            $ssql .= " AND ((cb.eStatus LIKE '%Completed%') AND DATE( NOW( ) ) >= DATE_ADD( DATE( cb.dBooking_date ) , INTERVAL 10 MINUTE )) ".$sql11;
        }else{
            $ssql .= " AND cb.eStatus LIKE '%".clean($eStatus)."%' ".$sql11;
        }
    }
    $hotelQuery = "";
    if ($_SESSION['SessionUserType'] == 'hotel'){
        $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
        $hotelQuery = " And cb.eBookingFrom = 'Hotel' AND cb.iHotelBookingId = '".$iHotelBookingId."'";
    }
    $locations_where = "";
    if (scount($userObj->locations) > 0){
        $locations = implode(', ',$userObj->locations);
        $ssql .= " AND vt.iLocationid IN(-1, {$locations})";
    }
    $sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType,vt.vRentalAlias_".$default_lang." as vRentalVehicleTypeName FROM cab_booking as cb LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 $ssql $adm_ssql $hotelQuery $ord";
    $data_drv = $obj->MySQLSelect($sql);
    ///changed by me start
    if ($eStatus == 'Completed'){
        foreach ($data_drv as $key_com => $val_com){
            $sql_trip = "select iActive, eCancelledBy from trips where iTripId=".$data_drv[$key_com]['iTripId'];
            $data_trip = $obj->MySQLSelect($sql_trip);
            if (!empty($data_trip)){
                if ($data_trip[0]['iActive'] == "Canceled" && $data_trip[0]['eCancelledBy'] == "Driver"){
                }else{
                    $cabbookingid[] = $val_com['iCabBookingId'];
                }
            }
        }
        $cabbookingid_implode = implode(",",$cabbookingid);
        $ssql .= " AND cb.iCabBookingId IN($cabbookingid_implode)";
        $sql = "SELECT cb.*,CONCAT(ru.vName,' ',ru.vLastName) as rider,CONCAT(rd.vName,' ',rd.vLastName) as driver,vt.vVehicleType,vt.vRentalAlias_".$default_lang." as vRentalVehicleTypeName FROM cab_booking as cb LEFT JOIN register_user as ru on ru.iUserId=cb.iUserId LEFT JOIN register_driver as rd on rd.iDriverId=cb.iDriverId LEFT JOIN vehicle_type as vt on vt.iVehicleTypeId=cb.iVehicleTypeId WHERE 1=1 $ssql $adm_ssql $hotelQuery $ord";
        $data_drv = $obj->MySQLSelect($sql);
    }
    ///changed by me end
    if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX'){
        $header .= $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']."\t";
    }
    if ($hotelPanel > 0 || $kioskPanel > 0){
        $header .= "Booked By\t";
    }
    $header .= $langage_lbl_admin['LBL_MYTRIP_RIDE_NO']."\t";
    $header .= $langage_lbl_admin['LBL_RIDERS_ADMIN']."\t";
    $header .= "Date"."\t";
    $header .= "Expected Source Location"."\t";
    if ($APP_TYPE != "UberX"){
        $header .= "Expected Destination Location"."\t";
    }
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."\t";
    $header .= "Status"."\t";
    for ($j = 0;$j < scount($data_drv);$j++){
        $eType = $data_drv[$j]['eType'];
        if ($eType_new == 'Ride' && $data_drv[$j]['iRentalPackageId'] > 0){
            $trip_type = 'Rental Ride';
        }else if ($eType == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eType == 'UberX'){
            $trip_type = 'Other Services';
        }else if ($eType == 'Deliver'){
            $trip_type = 'Delivery';
        }
        if ($data_drv[$j]['eBookingFrom'] != ''){
            $eBookingFrom = $data_drv[$j]['eBookingFrom'];
        }else{
            $eBookingFrom = $langage_lbl_admin['LBL_RIDER'];
        }
        $systemTimeZone = date_default_timezone_get();
        if ($data_drv[$j]['dBooking_date'] != "" && $data_drv[$j]['vTimeZone'] != ""){
            $dBookingDate = converToTz($data_drv[$j]['dBooking_date'],$data_drv[$j]['vTimeZone'],$systemTimeZone);
        }else{
            $dBookingDate = $data_drv[$j]['dBooking_date'];
        }
        if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX'){
            $data .= $trip_type."\t";
        }
        if ($hotelPanel > 0 || $kioskPanel > 0){
            $data .= $eBookingFrom."\t";
        }
        $data .= clearName($data_drv[$j]['vBookingNo'])."\t";
        $data .= clearName($data_drv[$j]['rider'])."\t";
        $data .= DateTime($dBookingDate)."\t";
        $string = $data_drv[$j]['vSourceAddresss'];
        $data .= str_replace(array("\n","\r","\r\n","\n\r"),' ',$string)."\t";
        if ($APP_TYPE != "UberX"){
            $string1 = $data_drv[$j]['tDestAddress'];
            $data .= str_replace(array("\n","\r","\r\n","\n\r"),' ',$string1)."\t";
        }
        /* Driver Details */
        if ($data_drv[$j]['eAutoAssign'] == "Yes" && $data_drv[$j]['iRentalPackageId'] > 0){
            $data .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].": Auto Assign ( Vehicle Type : ".$data_drv[$j]['vRentalVehicleTypeName']." )"."\t";
        }else if ($data_drv[$j]['eAutoAssign'] == "Yes" && $data_drv[$j]['eType'] == "Deliver" && $data_drv[$j]['iDriverId'] == 0 && $data_drv[$j]['eStatus'] != 'Cancel' && $APP_DELIVERY_MODE == "Multi"){
            $data .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].": Auto Assign ( Vehicle Type : ".$data_drv[$j]['vVehicleType']." )"."\t";
        }else if ($data_drv[$j]['eAutoAssign'] == "Yes" && $data_drv[$j]['iDriverId'] == 0){
            $data .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." : Auto Assign ( Car Type : ".$data_drv[$j]['vVehicleType']." )"."\t";
        }else if ($data_drv[$j]['eStatus'] == "Pending" && (strtotime($data_drv[$j]['dBooking_date']) > strtotime(date('Y-m-d'))) && $data_drv[$j]['iDriverId'] == 0){
            $data .= "( ".$langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']." : ".$data_drv[$j]['vVehicleType']." )"."\t";
        }else if ($data_drv[$j]['eCancelBy'] == "Driver" && $data_drv[$j]['eStatus'] == "Cancel" && $data_drv[$j]['iDriverId'] == 0){
            $data .= "( ".$langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']." : ".$data_drv[$j]['vVehicleType'].")"."\t";
        }else if ($data_drv[$j]['driver'] != "" && $data_drv[$j]['driver'] != "0"){
            $data .= clearName($data_drv[$j]['driver'])."( ".$langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']." :".$data_drv[$j]['vVehicleType'].")"."\t";
        }else{
            $data .= "( ".$langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']." : ".$data_drv[$j]['vVehicleType'].")"."\t";
        }
        /* Status */
        $setcurrentTime = strtotime(date('Y-m-d H:i:s'));
        $bookingdate = date("Y-m-d H:i",strtotime('+30 minutes',strtotime($data_drv[$j]['dBooking_date'])));
        $bookingdatecmp = strtotime($bookingdate);
        if ($data_drv[$j]['eStatus'] == "Assign" && $bookingdatecmp > $setcurrentTime){
            $data .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Assigned"."\n";
        }else if ($data_drv[$j]['eStatus'] == 'Accepted'){
            $data .= $data_drv[$j]['eStatus']."\n";
        }else if ($data_drv[$j]['eStatus'] == 'Declined'){
            $data .= $data_drv[$j]['eStatus']."\n";
        }else{
            $sql = "select iActive, eCancelledBy from trips where iTripId=".$data_drv[$j]['iTripId'];
            $data_stat = $obj->MySQLSelect($sql);
            if ($data_stat){
                for ($d = 0;$d < scount($data_stat);$d++){
                    if ($data_stat[$d]['iActive'] == "Canceled"){
                        $eCancelledBy = ($data_stat[$d]['eCancelledBy'] == 'Passenger')?$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']:$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];
                        $data .= "Canceled By ".$eCancelledBy."\n";
                    }else if ($data_stat[$d]['iActive'] == "Finished" && $data_stat[$d]['eCancelledBy'] == "Driver"){
                        $data .= "Canceled By ".$eCancelledBy."\n";
                    }else{
                        $data .= $data_stat[$d]['iActive']."\n";
                    }
                }
            }else{
                if ($data_drv[$j]['eStatus'] == "Cancel"){
                    if ($data_drv[$j]['eCancelBy'] == "Driver"){
                        $data .= "Canceled By ".$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."\n";
                    }else if ($data_drv[$j]['eCancelBy'] == "Rider"){
                        $data .= "Canceled By ".$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']."\n";
                    }else{
                        $data .= "Canceled By Admin"."\n";
                    }
                }else{
                    if ($data_drv[$j]['eStatus'] == 'Pending' && $bookingdatecmp > $setcurrentTime){
                        $data .= $data_drv[$j]['eStatus']."\n";
                    }else{
                        $data .= 'Expired'."\n";
                    }
                }
            }
        }
    }
    ob_clean();
    header("Content-type: application/octet-sdleam; charset=utf-8");
    header("Content-Disposition: attachment; filename=ScheduledBookings.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == 'triplist'){
    $action = (isset($_REQUEST['action'])?$_REQUEST['action']:'');
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
    $keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:"";
    $searchCompany = isset($_REQUEST['searchCompany'])?$_REQUEST['searchCompany']:'';
    $searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $vStatus = isset($_REQUEST['vStatus'])?$_REQUEST['vStatus']:'';
    $method = isset($_REQUEST['method'])?$_REQUEST['method']:'';
    $iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';
    $searchKiosk = isset($_REQUEST['searchKiosk'])?$_REQUEST['searchKiosk']:'';
    $subType = isset($_REQUEST['subType']) ? $_REQUEST['subType'] : '';
    $vehilceTypeArr = array();
    $getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_".$default_lang." AS vehicleType FROM vehicle_type WHERE 1=1");
    for ($r = 0;$r < scount($getVehicleTypes);$r++){
        $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
    }
    $ssql = '';
    $ord = ' ORDER BY t.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY t.eType ASC";
        }else{
            $ord = " ORDER BY t.eType DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY t.tTripRequestDate ASC";
        }else{
            $ord = " ORDER BY t.tTripRequestDate DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY c.vCompany ASC";
        }else{
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY u.vName ASC";
        }else{
            $ord = " ORDER BY u.vName DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    if ($startDate != ''){
        $ssql .= " AND Date(t.tTripRequestDate) >='".$startDate."'";
    }
    if ($endDate != ''){
        $ssql .= " AND Date(t.tTripRequestDate) <='".$endDate."'";
    }
    if ($serachTripNo != ''){
        $ssql .= " AND t.vRideNo ='".$serachTripNo."'";
    }
    if ($searchCompany != ''){
        $ssql .= " AND d.iCompanyId ='".$searchCompany."'";
    }
    if ($searchDriver != ''){
        $ssql .= " AND t.iDriverId ='".$searchDriver."'";
    }
    if ($searchRider != ''){
        $ssql .= " AND t.iUserId ='".$searchRider."'";
    }
    if ($searchKiosk != ''){
        $ssql .= " AND t.iHotelId ='".$searchKiosk."'";
    }
    if ($vStatus == "onRide"){
        $ssql .= " AND (t.iActive = 'On Going Trip' OR t.iActive = 'Active') AND t.eCancelled='No'";
    }else if ($vStatus == "cancel"){
        $ssql .= " AND (t.iActive = 'Canceled' OR t.eCancelled='yes')";
    }else if ($vStatus == "complete"){
        $ssql .= " AND t.iActive = 'Finished' AND t.eCancelled='No'";
    }
    if (trim($promocode) != ""){
        $ssql .= " AND t.vCouponCode LIKE '".$promocode."' AND t.iActive !='Canceled'";
    }
    if (scount($userObj->locations) > 0){
        $locations = implode(', ',$userObj->locations);
        $ssql .= " AND vt.iLocationid IN(-1, {$locations}) ";
    }
    if ($eType != ''){
        if ($eType == 'Ride'){
            $ssql .= " AND t.eType ='".$eType."' AND t.iRentalPackageId = 0 AND t.eHailTrip = 'No' ";
            $ssql .= " AND  t.iFromStationId = 0 AND t.iToStationId = 0 ";
        }elseif ($eType == 'RentalRide'){
            $ssql .= " AND t.eType ='Ride' AND t.iRentalPackageId > 0";
        }elseif ($eType == 'HailRide'){
            $ssql .= " AND t.eType ='Ride' AND t.eHailTrip = 'Yes'";
        }else if ($eType == "Pool"){
            $ssql .= " AND t.eType ='Ride' AND t.ePoolRide = 'Yes'";
        }else if ($eType == "Fly"){
            $ssql .= " AND t.eType ='Ride' AND t.iFromStationId != 0 AND t.iToStationId != 0 ";
        }else if($eType == "Deliver" && empty($subType)) {
            $ssql .= " AND t.eType ='Multi-Delivery'";
        } else if ($eType == "Deliver"  && $subType == "Single-Delivery"){
            $ssql .= " AND t.eType ='Multi-Delivery' HAVING totalDeliveryTrips = 1";
        }else if ($eType == "Multi-Delivery"  && $subType == "Multi-Delivery"){
            $ssql .= " AND t.eType ='".$eType."' HAVING totalDeliveryTrips > 1";
        }else{
            $ssql .= " AND t.eType ='".$eType."' ";
        }
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){

        $trp_ssql = " And t.tTripRequestDate > '".WEEK_DATE."'";
    }
    $hotelQuery = "";
    if ($_SESSION['SessionUserType'] == 'hotel'){

        /* $sql1 = "SELECT * FROM hotel where iAdminId = '".$_SESSION['sess_iAdminUserId']."'";

          $hoteldata = $obj->MySQLSelect($sql1); */
        $iHotelBookingId = $_SESSION['sess_iAdminUserId'];
        $hotelQuery = " AND (t.eBookingFrom = 'Hotel' || t.eBookingFrom = 'Kiosk') AND t.iHotelBookingId = '".$iHotelBookingId."'";
    }
    $sql = "SELECT t.iFromStationId, t.iToStationId,t.ePoolRide,t.tStartDate,t.tEndDate, t.tTripRequestDate,t.eBookingFrom,t.vCancelReason,t.vCancelComment,t.iCancelReasonId, t.eHailTrip, t.iUserId, t.iFare, t.eType, d.iDriverId, t.tSaddress, t.vRideNo, t.tDaddress,  t.fWalletDebit, t.eCarType, t.iTripId, t.iActive, t.fCancellationFare, t.eCancelledBy, t.eCancelled, t.iHotelBookingId, t.iRentalPackageId , CONCAT(u.vName,' ',u.vLastName) AS riderName, CONCAT(d.vName,' ',d.vLastName) AS driverName, d.vAvgRating,t.vDeliveryConfirmCode, c.vCompany, vt.vVehicleType_{$default_lang} as vVehicleType, vt.vRentalAlias_{$default_lang} as vRentalVehicleTypeName,t.tVehicleTypeData, t.fTax1, t.fTax2, (SELECT COUNT(tl.iTripDeliveryLocationId) AS Total FROM trips_delivery_locations as tl WHERE 1=1 AND tl.iActive='Finished' AND t.iTripId=tl.iTripId) as totalDeliveryTrips,t.vTimeZone FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId LEFT JOIN company c ON c.iCompanyId=d.iCompanyId WHERE 1=1 AND t.eSystem = 'General' {$ssql} {$trp_ssql} {$hotelQuery} {$ord} ";
    $db_trip = $obj->MySQLSelect($sql) or die('Query failed!');
    $filename = "trips_".$timestamp_filename.'.xls';

    if ($APP_TYPE == 'UberX')
    {
        $filename = "Jobs_".$timestamp_filename.'.xls';
    }
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // if ($hotelPanel > 0 || $kioskPanel > 0) {
    $kioskupdatedAry = array();
    $kioskAry = $obj->MySQLSelect("SELECT iAdminId, concat(vFirstName,' ',vLastName) as kioskname FROM `administrators` WHERE iGroupId='4'");
    foreach ($kioskAry as $key => $value){
        $kioskupdatedAry[$value['iAdminId']] = $value['kioskname'];
    }
    // }
    // $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // // Get the active sheet
    // $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    // if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery') {
    //      if ($_SESSION['SessionUserType'] != 'hotel') {
    //         $sheet->setCellValue('A1',  $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']);
    //         $is_TRIP_TYPE = 1;
    //      }
    // }
    // if ($hotelPanel > 0 || $kioskPanel > 0) {
    //     $sheet->setCellValue(($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery' && $_SESSION['SessionUserType'] != 'hotel') ?  'B1' : 'A1',   "Booked By");
    //     $is_bookby = 1;
    // }
    // $header_nu = "A1";
    // if($is_TRIP_TYPE == 1 || $is_bookby == 1)
    // {
    //     $header_nu = "B1";
    //     if($is_TRIP_TYPE == 1 && $is_bookby == 1)
    //     {
    //         $header_nu = "C1";
    //     }
    // }
    // $sheet->setCellValue($header_nu, $langage_lbl_admin['LBL_TRIP_NO_ADMIN']);
    // $sheet->setCellValue(($header_nu == "A1") ? 'B1' : 'C1' ,  "Address");
    // $sheet->setCellValue(($header_nu == "A1") ? 'C1' : 'D1' ,  $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']);
    // if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel') {
    //     $sheet->setCellValue(($header_nu == "A1") ? 'D1' : 'E1' , "Company");
    // }
    // $sheet->setCellValue('E1', "Ride End Time");
    // $sheet->setCellValue('F1', $langage_lbl_admin['LBL_RIDE_SHARE_DURATION'] );
    // $sheet->setCellValue('G1',  $langage_lbl_admin['LBL_TRACK_SERVICE_START_LOC_TXT']);
    // $sheet->setCellValue('H1', $langage_lbl_admin['LBL_TRACK_SERVICE_END_LOC_TXT']);
    // $sheet->setCellValue('I1', "Published Date");
    // $sheet->setCellValue('J1', $langage_lbl_admin['LBL_RIDE_SHARE_PRICE_PER_SEAT_TOTAL_TXT']);
    // $sheet->setCellValue('K1', "Total seats");
    // $sheet->setCellValue('L1', "Occupied Seats");
    // $sheet->setCellValue('M1', $langage_lbl_admin['LBL_Status']);
    if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery'){
        if ($_SESSION['SessionUserType'] != 'hotel'){
            $trip_type = $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN'];
        }
    }
    if ($hotelPanel > 0 || $kioskPanel > 0){
        $book_by = "Booked By";
    }
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$trip_type);
    $sheet->setCellValue('B1',$book_by);
    $sheet->setCellValue('C1',$langage_lbl_admin['LBL_TRIP_NO_ADMIN']);
    $sheet->setCellValue('D1','Address');
    $sheet->setCellValue('E1',$langage_lbl_admin['LBL_TRIP_DATE_ADMIN']);
    $sheet->setCellValue('F1',$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
    $sheet->setCellValue('G1',$langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']);
    $sheet->setCellValue('H1',$langage_lbl_admin['LBL_DRIVER_TRIP_FARE_TXT']);
    $sheet->setCellValue('I1','Type');
    $sheet->setCellValue('J1','Status');
    $j = 2;
    $serverTimeZone = date_default_timezone_get();
    //print_r($db_trip); die;
    for ($i = 0;$i < scount($db_trip);$i++){
        $poolTxt = "";
        if ($db_trip[$i]['ePoolRide'] == "Yes"){
            $poolTxt = " (Pool)";
        }
        $eTypenew = $db_trip[$i]['eType'];
        $link_page = "invoice.php";
        if ($eTypenew == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eTypenew == 'UberX'){
            $trip_type = 'Other Services';
        }else if ($eTypenew == 'Multi-Delivery' && $db_trip[$i]['totalDeliveryTrips'] > 1){
            $trip_type = 'Multi-Delivery';
            $link_page = "invoice_multi_delivery.php";
        }else{
            $trip_type = 'Delivery';
        }
        $trip_type .= $poolTxt;
        if ($db_trip[$i]['eBookingFrom'] != ''){
            $iHotelBooking = "";
            if ($db_trip[$i]['iHotelBookingId'] != "" && $db_trip[$i]['iHotelBookingId'] > 0 && $kioskupdatedAry[$db_trip[$i]['iHotelBookingId']] != ""){
                $iHotelBooking = " ".$kioskupdatedAry[$db_trip[$i]['iHotelBookingId']];
            }
            $eBookingFrom = $db_trip[$i]['eBookingFrom'].$iHotelBooking;
        }else{
            $eBookingFrom = $langage_lbl_admin['LBL_RIDER'];
        }
        if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery'){
            if ($_SESSION['SessionUserType'] != 'hotel'){
                if ($db_trip[$i]['eHailTrip'] == "Yes" && $db_trip[$i]['iRentalPackageId'] > 0){
                    $data['trip_type'] = "Rental ".$trip_type." ( Hail )";
                }else if ($db_trip[$i]['iRentalPackageId'] > 0){
                    $data['trip_type'] = "Rental ".$trip_type;
                }else if ($db_trip[$i]['eHailTrip'] == "Yes"){
                    $data['trip_type'] = "Hail ".$trip_type;
                }else{
                    if (!empty($db_trip[$i]['iFromStationId']) && !empty($db_trip[$i]['iToStationId'])){
                        $trip_type = 'Fly';
                    }
                    $data['trip_type'] = $trip_type;
                }
            }
        }
        if ($hotelPanel > 0 || $kioskPanel > 0){
            $data['eBookingFrom'] = $eBookingFrom;
        }
        $data["booking_no"] = $db_trip[$i]['vRideNo'];
        $string = $db_trip[$i]['tSaddress'];
        if ($APP_TYPE != "UberX" && !empty($db_trip[$i]['tDaddress'])){
            $string .= ' -> '.$db_trip[$i]['tDaddress'];
        }
        $data['address'] = str_replace(array("\n","\r","\r\n","\n\r"),' ',$string);
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $date_format_data_array['tdate'] = (!empty($db_trip[$i]['vTimeZone']))?converToTz($db_trip[$i]['tTripRequestDate'],$db_trip[$i]['vTimeZone'],$serverTimeZone):$db_trip[$i]['tTripRequestDate'];
        $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
        $data['date'] = $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);
        //$data .= date('d-F-Y', strtotime($db_trip[$i]['tTripRequestDate'])) . "\t";
        if (isset($_SESSION['SessionUserType']) && $_SESSION['SessionUserType'] != 'hotel'){
            $driverName = clearCmpName($db_trip[$i]['vCompany']);
        }
        $data['driverName'] = clearName($db_trip[$i]['driverName']);
        $data['riderName'] = clearName($db_trip[$i]['riderName']);
        if ($db_trip[$i]['fCancellationFare'] > 0){
            $db_trip[$i]['fCancellationFare'] = $db_trip[$i]['fCancellationFare'] + $db_trip[$i]['fTax1'] + $db_trip[$i]['fTax2'];
            $data['fare'] = formateNumAsPerCurrency($db_trip[$i]['fCancellationFare'],'');
        }else{
            $data['fare'] = formateNumAsPerCurrency($db_trip[$i]['iFare'] + $db_trip[$i]['fWalletDebit'],'');
        }
        if (isset($db_trip[$i]['tVehicleTypeData']) && $db_trip[$i]['tVehicleTypeData'] != "" && $vehicleTypeName == ""){
            $viewService = 1;
            $seriveJson = json_decode($db_trip[$i]['tVehicleTypeData'],true);
            $service_name = "";
            $c = 1;
            foreach ($seriveJson as $servc){
                if ($c < scount($seriveJson)){
                    $new_line = "\n";
                }else{
                    $new_line = "";
                }
                $service_name .= $vehilceTypeArr[$servc['iVehicleTypeId']].$new_line;
                $c++;
            }
            $data['type'] = '"'.$service_name.'"';
        }else{
            if ($db_trip[$i]['iRentalPackageId'] > 0){
                $data['type'] = $db_trip[$i]['vRentalVehicleTypeName'];
            }else{
                $data['type'] = $db_trip[$i]['vVehicleType'];
            }
        }
        if (($db_trip[$i]['iActive'] == 'Finished' && $db_trip[$i]['eCancelled'] == "Yes") || ($db_trip[$i]['fCancellationFare'] > 0) || ($db_trip[$i]['iActive'] == 'Canceled' && $db_trip[$i]['fWalletDebit'] > 0)){
            $data['status'] = "Cancelled";
        }else if ($db_trip[$i]['iActive'] == 'Finished'){
            $data['status'] = "Finished";
        }else{
            if ($db_trip[$i]['iActive'] == "Active" or $db_trip[$i]['iActive'] == "On Going Trip"){
                if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX'){
                    $data['status'] = "On Job";
                }else{
                    $data['status'] = "On Ride";
                }
                if (!empty($db_trip[$i]['vDeliveryConfirmCode'])){
                    $data['status'] = '<div style="margin-top:15px;">Delivery Confirmation Code: '.$db_trip[$i]['vDeliveryConfirmCode'].'</div>';
                }
            }else if ($db_trip[$i]['iActive'] == "Canceled" && ($db_trip[$i]['iCancelReasonId'] > 0 || $db_trip[$i]['vCancelReason'] != '')){
                if ($db_trip[$i]['iCancelReasonId'] > 0){
                    $cancelreasonarray = getCancelReason($db_trip[$i]['iCancelReasonId'],$default_lang);
                    $db_trip[$i]['vCancelReason'] = $cancelreasonarray['vCancelReason'];
                }else{
                    $db_trip[$i]['vCancelReason'] = $db_trip[$i]['vCancelReason'];
                }
                $stringReason = stripcslashes($db_trip[$i]['vCancelReason']." ".$db_trip[$i]['vCancelComment'])."\n Cancel By: ".stripcslashes($db_trip[$i]['eCancelledBy']);
                $data['status'] = "Cancel Reason: ".str_replace(array("\n","\r","\r\n","\n\r"),' ',$stringReason);
            }else if ($db_trip[$i]['iActive'] == "Canceled" && $db_trip[$i]['fWalletDebit'] < 0){
                $data['status'] = "Cancelled";
            }else{
                $data['status'] = $db_trip[$i]['iActive'];
            }
        }
        $sheet->setCellValue('A'.$j,$data['trip_type']);
        $sheet->setCellValue('B'.$j,$data['eBookingFrom']);
        $sheet->setCellValue('C'.$j,$data["booking_no"]);
        $sheet->setCellValue('D'.$j,$data["address"]);
        $sheet->setCellValue('E'.$j,$data["date"]);
        $sheet->setCellValue('F'.$j,$data["driverName"]);
        $sheet->setCellValue('G'.$j,$data["riderName"]);
        $sheet->setCellValue('H'.$j,$data["fare"]);
        $sheet->setCellValue('I'.$j,$data["type"]);
        $sheet->setCellValue('J'.$j,$data['status']);
        $j++;
    }
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
//Added By Hasmukh On 10-10-2018 For Export Organization Report Data csv from Screen Start
if ($section == "organization_payment"){
    //Start Sorting
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    $searchOrganization = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $action = (isset($_REQUEST['action'])?$_REQUEST['action']:'');
    $ord = ' ORDER BY tr.iTripId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY ru.vName ASC";
        }else{
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY tr.tTripRequestDate ASC";
        }else{
            $ord = " ORDER BY tr.tTripRequestDate DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY u.vName ASC";
        }else{
            $ord = " ORDER BY u.vName DESC";
        }
    }
    if ($sortby == 6){
        if ($order == 0){
            $ord = " ORDER BY tr.eType ASC";
        }else{
            $ord = " ORDER BY tr.eType DESC";
        }
    }
    //End Sorting
    if ($action == 'search'){
        if ($startDate != ''){
            $ssql .= " AND Date(tr.tTripRequestDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(tr.tTripRequestDate) <='".$endDate."'";
        }
        if ($serachTripNo != ''){
            $ssql .= " AND tr.vRideNo ='".$serachTripNo."'";
        }
        if ($searchOrganization != ''){
            $ssql .= " AND tr.iOrganizationId ='".$searchOrganization."'";
        }
        if ($searchUser != ''){
            $ssql .= " AND tr.iUserId ='".$iUserId."'";
        }
        if ($searchDriverPayment != ''){
            $ssql .= " AND tr.eOrganizationPaymentStatus ='".$eDriverPaymentStatus."'";
        }
        if ($searchPaymentType != ''){
            $ssql .= " AND tr.vTripPaymentMode ='".$vTripPaymentMode."'";
        }
        if ($eType != ''){
            if ($eType == 'Ride'){
                $ssql .= " AND tr.eType ='".$eType."' AND tr.iRentalPackageId = 0 AND tr.eHailTrip = 'No' ";
            }elseif ($eType == 'RentalRide'){
                $ssql .= " AND tr.eType ='Ride' AND tr.iRentalPackageId > 0";
            }elseif ($eType == 'HailRide'){
                $ssql .= " AND tr.eType ='Ride' AND tr.eHailTrip = 'Yes'";
            }else{
                $ssql .= " AND tr.eType ='".$eType."' ";
            }
        }
    }
    $trp_ssql = $header = $data = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And tr.tTripRequestDate > '".WEEK_DATE."'";
    }
    //Pagination Start
    $org_sql = "SELECT iOrganizationId,vCompany AS driverName,vEmail FROM organization order by vCompany";
    $db_organization = $obj->MySQLSelect($org_sql);
    $orgNameArr = array();
    for ($g = 0;$g < scount($db_organization);$g++){
        $orgNameArr[$db_organization[$g]['iOrganizationId']] = $db_organization[$g]['driverName'];
    }
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    $sql = "SELECT tr.iTripId,tr.fHotelCommision,tr.vRideNo,tr.iDriverId,tr.iOrganizationId,tr.iUserId,tr.tTripRequestDate, tr.eType, tr.eHailTrip,tr.fTripGenerateFare,tr.fCommision, tr.fDiscount, tr.fWalletDebit, tr.fTipPrice,tr.eOrganizationPaymentStatus,tr.ePaymentCollect,tr.vTripPaymentMode,tr.iActive,tr.fOutStandingAmount, tr.iRentalPackageId,c.vCompany,concat(rd.vName,' ',rd.vLastName) as drivername,concat(ru.vName,' ',ru.vLastName) as riderName,tr.vTimeZone FROM trips AS tr LEFT JOIN register_driver AS rd ON tr.iDriverId = rd.iDriverId LEFT JOIN register_user AS ru ON tr.iUserId = ru.iUserId LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE  if(tr.iActive ='Canceled',if(tr.vTripPaymentMode='Card',1=1,0),1=1) AND tr.iActive ='Finished' AND tr.iOrganizationId >0 AND tr.eSystem='General' $ssql $trp_ssql $ord";
    //echo "<pre>";
    $totalData = $obj->MySQLSelect($sql);
    //echo "<pre>";
    $flag = false;
    $filename = "organization_payment_report_".$timestamp_filename.'.xls';
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']);
    $sheet->setCellValue('B1',$langage_lbl_admin['LBL_RIDE_NO_ADMIN']);
    $sheet->setCellValue('C1',"User Name");
    $sheet->setCellValue('D1',$langage_lbl_admin['LBL_TRIP_TXT_ADMIN']." Date");
    $sheet->setCellValue('E1',"A=Total Fare");
    $sheet->setCellValue('F1',"B=Platform Fees");
    $sheet->setCellValue('G1',"C= Promo Code Discount");
    $sheet->setCellValue('H1',"D = Wallet Debit");
    $sheet->setCellValue('I1',"E = Tip");
    $sheet->setCellValue('J1',"F = Trip Outstanding Amount");
    $sheet->setCellValue('K1',"G = Booking Fees  ");
    $sheet->setCellValue('L1',$langage_lbl_admin['LBL_ORGANIZATION']." pay Amount");
    $sheet->setCellValue('M1',$langage_lbl_admin['LBL_RIDE_TXT_ADMIN']." Status");
    $sheet->setCellValue('N1',"Payment method");
    $sheet->setCellValue('O1',$langage_lbl_admin['LBL_ORGANIZATION']." Payment Status");
    $i = 2;
    $total_tip = $tot_fare = $tot_site_commission = $tot_promo_discount = $tot_driver_refund = $tot_wallentPayment = $tot_outstandingAmount = $tot_hotel_commision = 0.00;
    $serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($totalData);$j++){
        $orfName = "";
        if (isset($orgNameArr[$totalData[$j]['iOrganizationId']]) && $orgNameArr[$totalData[$j]['iOrganizationId']] != ""){
            $orfName = "(".$orgNameArr[$totalData[$j]['iOrganizationId']].")";
        }
        $totalfare = trip_currency_payment($totalData[$j]['fTripGenerateFare']);
        $site_commission = trip_currency_payment($totalData[$j]['fCommision']);
        $promocodediscount = trip_currency_payment($totalData[$j]['fDiscount']);
        $wallentPayment = trip_currency_payment($totalData[$j]['fWalletDebit']);
        $fTipPrice = trip_currency_payment($totalData[$j]['fTipPrice']);
        $fOutStandingAmount = trip_currency_payment($totalData[$j]['fOutStandingAmount']);
        $hotel_commision = trip_currency_payment($totalData[$j]['fHotelCommision']);
        if ($totalData[$j]['vTripPaymentMode'] == "Cash"){
            //$driver_payment = ($promocodediscount+$wallentPayment)-($site_commission+$fOutStandingAmount+$hotel_commision);
        }else{
            //$driver_payment = ($fTipPrice+$totalfare)-($site_commission+$fOutStandingAmount+$hotel_commision);
            //$driver_payment = $totalfare - $site_commission + $fTipPrice - $fOutStandingAmount - $hotel_commision;
        }
        $driver_payment = ($fTipPrice + $totalfare) - ($site_commission + $fOutStandingAmount + $hotel_commision);
        $class_setteled = "";
        if ($totalData[$j]['eOrganizationPaymentStatus'] == 'Settelled'){
            $class_setteled = "setteled-class";
        }
        $tot_fare += $totalfare;
        $tot_site_commission += $site_commission;
        $tot_hotel_commision += $hotel_commision;
        $tot_promo_discount += $promocodediscount;
        $tot_wallentPayment += $wallentPayment;
        $total_tip += $fTipPrice;
        $tot_driver_refund += $driver_payment;
        $cashPayment = $site_commission;
        $cardPayment = $totalfare - $site_commission;
        $tot_outstandingAmount += $fOutStandingAmount;
        $eType = $totalData[$j]['eType'];
        if ($eType == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eType == 'UberX'){
            $trip_type = 'Other Services';
        }else if ($eType == 'Deliver'){
            $trip_type = 'Delivery';
        }
        if ($APP_TYPE != 'UberX' && $APP_TYPE != 'Delivery'){
            if ($totalData[$j]['eHailTrip'] == "Yes" && $totalData[$j]['iRentalPackageId'] > 0){
                $result['ride_type'] = "Rental ".$trip_type." ( Hail )";
            }else if ($totalData[$j]['iRentalPackageId'] > 0){
                $result['ride_type'] = "Rental ".$trip_type;
            }else if ($totalData[$j]['eHailTrip'] == "Yes"){
                $result['ride_type'] = "Hail ".$trip_type;
            }else{
                $result['ride_type'] = $trip_type."\t";
            }
        }
        $result['ride'] = $totalData[$j]['vRideNo']."\t";
        //$data .= clearName($totalData[$j]['drivername']) . "\t";
        $result['name'] = clearName($totalData[$j]['riderName']);
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $date_format_data_array['tdate'] = (!empty($totalData[$j]['vTimeZone']))?converToTz($totalData[$j]['tTripRequestDate'],$totalData[$j]['vTimeZone'],$serverTimeZone):$totalData[$j]['tTripRequestDate'];
        $get_tTripRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($totalData[$j]['vTimeZone'],$date_format_data_array['tdate']).")";
        $result['date'] = $get_tTripRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);
        //$data .= DateTime($totalData[$j]['tTripRequestDate']) . "\t";
        $result['total_far'] = ($totalfare != "" && $totalfare != 0)?$totalfare:"$0.00";
        $result['site_commition'] = ($site_commission != "" && $site_commission != 0)?$site_commission:"$0.00";
        $result['promocode_disc'] = ($promocodediscount != "" && $promocodediscount != 0)?$promocodediscount:"$0.00";
        $result['wallet_ammount'] = ($wallentPayment != "" && $wallentPayment != 0)?$wallentPayment:"$0.00";
        if ($ENABLE_TIP_MODULE == "Yes"){
            $result['tip'] = ($fTipPrice != "" && $fTipPrice != 0)?$fTipPrice:"$0.00";
        }
        $result['outstanding'] = ($fOutStandingAmount != "" && $fOutStandingAmount != 0)?$fOutStandingAmount:"$0.00";
        $result['booking_fees'] = ($hotel_commision != "" && $hotel_commision != 0)?$hotel_commision:"$0.00";
        $result['org_pay'] = ($totalfare != "" && $totalfare != 0)?$totalfare:"$0.00";
        $result['ride_status'] = $totalData[$j]['iActive'];
        $result['method'] = $totalData[$j]['vTripPaymentMode']." ".$orfName;
        $result['status'] = $totalData[$j]['eOrganizationPaymentStatus'];
        $sheet->setCellValue('A'.$i,$result['ride_type']);
        $sheet->setCellValue('B'.$i,$result['ride']);
        $sheet->setCellValue('C'.$i,$result['name']);
        $sheet->setCellValue('D'.$i,$result['date']);
        $sheet->setCellValue('E'.$i,($result['total_far'] > 0)?formateNumAsPerCurrency($result['total_far'],""):$result['total_far']);
        $sheet->setCellValue('F'.$i,($result['site_commition'] > 0)?formateNumAsPerCurrency($result['site_commition'],""):$result['site_commition']);
        $sheet->setCellValue('G'.$i,($result['promocode_disc'] > 0)?formateNumAsPerCurrency($result['promocode_disc'],""):$result['promocode_disc']);
        $sheet->setCellValue('H'.$i,($result['wallet_ammount'] > 0)?formateNumAsPerCurrency($result['wallet_ammount'],""):$result['wallet_ammount']);
        $sheet->setCellValue('I'.$i,($result['tip'] > 0)?formateNumAsPerCurrency($result['tip'],""):$result['tip']);
        $sheet->setCellValue('J'.$i,($result['outstanding'] > 0)?formateNumAsPerCurrency($result['outstanding'],""):$result['outstanding']);
        $sheet->setCellValue('K'.$i,($result['booking_fees'] > 0)?formateNumAsPerCurrency($result['booking_fees'],""):$result['booking_fees']);
        $sheet->setCellValue('L'.$i,($result['org_pay'] > 0)?formateNumAsPerCurrency($result['org_pay'],""):$result['org_pay']);
        $sheet->setCellValue('M'.$i,$result['ride_status']);
        $sheet->setCellValue('N'.$i,$result['method']);
        $sheet->setCellValue('O'.$i,$result['status']);
        $i++;
    }
    $amount_array = array('Total Fare'           => setTwoDecimalValue($tot_fare),
                          'Total Platform Fees'  => setTwoDecimalValue($tot_site_commission),
                          'Total Promo Discount' => setTwoDecimalValue($tot_promo_discount),
                          'Total Wallet Debit'   => setTwoDecimalValue($tot_wallentPayment));
    //$data .= "\n\t\t\t\t\t\t\t\t\t\t\tTotal Fare\t" . setTwoDecimalValue($tot_fare) . "\n";
    //$data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Platform Fees\t" . setTwoDecimalValue($tot_site_commission) . "\n";
    // $data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Promo Discount\t" . setTwoDecimalValue($tot_promo_discount) . "\n";
    //$data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Wallet Debit\t" . setTwoDecimalValue($tot_wallentPayment) . "\n";
    if ($ENABLE_TIP_MODULE == "Yes"){
        $amount_array['Total Tip Amount'] = setTwoDecimalValue($total_tip);
        $amount_array['Total Trip Outstanding Amount'] = setTwoDecimalValue($tot_outstandingAmount);
        $amount_array['Total Booking Fees'] = setTwoDecimalValue($tot_hotel_commision);
        $amount_array['Total Total Payment Amount'] = setTwoDecimalValue($tot_driver_refund);
        //$data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Tip Amount\t" . setTwoDecimalValue($total_tip) . "\n";
        //$data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Trip Outstanding Amount\t" . setTwoDecimalValue($tot_outstandingAmount) . "\n";
        //$data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Booking Fees\t" . setTwoDecimalValue($tot_hotel_commision) . "\n";
        //$data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Total Payment Amount\t" . setTwoDecimalValue($tot_driver_refund) . "\n";
    }else{
        $amount_array['Total Trip Outstanding Amount'] = setTwoDecimalValue($tot_outstandingAmount);
        $amount_array['Total Total Payment Amount Payment'] = setTwoDecimalValue($tot_driver_refund);
        // $data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Trip Outstanding Amount\t" . setTwoDecimalValue($tot_outstandingAmount) . "\n";
        // $data .= "\t\t\t\t\t\t\t\t\t\t\tTotal Total Payment Amount Payment\t" . setTwoDecimalValue($tot_driver_refund) . "\n";
    }
    foreach ($amount_array as $key => $value){
        $sheet->setCellValue('N'.$i,$key);
        $sheet->setCellValue('O'.$i,formateNumAsPerCurrency($value,''));
        $i++;
    }
    //$data = str_replace("\r", "", $data);
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
        //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == "trips_statistics_report"){
    //echo "<pre>";
    $date1 = $startDate.' '."00:00:00";
    $date2 = $endDate.' '."23:59:59";
    if ($startDate != '' && $endDate != ''){
        $ssql .= "TR.tTripRequestDate between '$date1' and '$date2'";
    }
    $totalData = $obj->MySQLSelect("SELECT iActive,DATE_FORMAT(TR.tTripRequestDate, '%Y-%m-%d') AS REQUEST_DATE FROM trips TR WHERE $ssql ORDER BY tTripRequestDate DESC");
    $finalTripArr = array();
    for ($r = 0;$r < scount($totalData);$r++){
        $date = $totalData[$r]['REQUEST_DATE'];
        $tripStatus = $totalData[$r]['iActive'];
        $finalTripArr[$date]['date'] = $date;
        if (isset($finalTripArr[$date]['total'])){
            $finalTripArr[$date]['total'] += 1;
        }else{
            $finalTripArr[$date]['total'] = 1;
        }
        if (isset($finalTripArr[$date][$tripStatus])){
            $finalTripArr[$date][$tripStatus] += 1;
        }else{
            $finalTripArr[$date][$tripStatus] = 1;
        }
    }
    $trp_ssql = $header = $data = "";
    $header .= "Trip Date.\t";
    $header .= "Total Trips\t";
    $header .= "Active Trips\t";
    $header .= "Ongoing Trips\t";
    $header .= "Completed Trips\t";
    $header .= "Cancelled Trips\t";
    //echo "<pre>";
    $totTrips = $totCompleted = $totCancelled = $totOngoing = $totActive = 0;
    foreach ($finalTripArr as $key => $val){
        $totalTrips = $cancelledTrips = $completedTrips = $ongoingTrips = $activeTrips = 0;
        $tripDate = $val['date'];
        if (isset($val['total']) && $val['total'] > 0){
            $totalTrips = $val['total'];
        }
        $totTrips += $totalTrips;
        if (isset($val['Active']) && $val['Active'] > 0){
            $activeTrips = $val['Active'];
        }
        $totActive += $activeTrips;
        if (isset($val['Finished']) && $val['Finished'] > 0){
            $completedTrips = $val['Finished'];
        }
        $totCompleted += $completedTrips;
        if (isset($val['Canceled']) && $val['Canceled'] > 0){
            $cancelledTrips = $val['Canceled'];
        }
        $totCancelled += $cancelledTrips;
        if (isset($val['On Going Trip']) && $val['On Going Trip'] > 0){
            $ongoingTrips = $val['On Going Trip'];
        }
        $totOngoing += $ongoingTrips;
        $data .= $tripDate."\t";
        $data .= $totalTrips."\t";
        $data .= $activeTrips."\t";
        $data .= $ongoingTrips."\t";
        $data .= $completedTrips."\t";
        $data .= $cancelledTrips."\n";
    }
    $data .= "Total\t";
    $data .= $totTrips."\t";
    $data .= $totActive."\t";
    $data .= $totOngoing."\t";
    $data .= $totCompleted."\t";
    $data .= $totCancelled."\n";
    $data = str_replace("\r","",$data);
    ob_clean();
    header("Content-type: application/octet-stream; charset=utf-8");
    header("Content-Disposition: attachment; filename=".$time."_trips_statistics_report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
//Added By Hasmukh On 10-10-2018 For Export Organization Report Data csv from Screen End
function setTwoDecimalValue($amount)
{
    $amount = number_format($amount,2);
    return $amount;
}

if ($section == 'insurance_report'){

    $eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:'';
    $eAddedFor = isset($_REQUEST['eAddedFor'])?$_REQUEST['eAddedFor']:'Available';
    $export_file_name = !empty($_REQUEST['export_file_name'])?$_REQUEST['export_file_name']:"";
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And dir.dStartDate > '".WEEK_DATE."'";
    }
    $ord = ' ORDER BY dir.iInsuranceReportId DESC';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY tr.vRideNo ASC";
        }else{
            $ord = " ORDER BY tr.vRideNo DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY rd.vName ASC";
        }else{
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY rd.vEmail ASC";
        }else{
            $ord = " ORDER BY rd.vEmail DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY dir.dStartDate ASC";
        }else{
            $ord = " ORDER BY dir.dStartDate DESC";
        }
    }
    if ($sortby == 6){
        if ($order == 0){
            $ord = " ORDER BY dir.dEndDate ASC";
        }else{
            $ord = " ORDER BY dir.dEndDate DESC";
        }
    }
    $ssql = "";
    if ($startDate != ''){
        $ssql .= " AND Date(dir.dStartDate) >='".$startDate."'";
    }
    if ($endDate != ''){
        $ssql .= " AND Date(dir.dStartDate) <='".$endDate."'";
    }
    if ($iDriverId != ''){
        $ssql .= " AND dir.iDriverId = '".$iDriverId."'";
    }
    if ($serachTripNo != ''){
        $ssql .= " AND tr.vRideNo ='".$serachTripNo."'";
    }
    $sql = "SELECT dir.`iInsuranceReportId`, dir.`iDriverId`, dir.`iTripId`,dir.vDistance, dir.`dStartDate`, dir.`dEndDate`, dir.`tStartLat`, dir.`tStartLong`, dir.`tStartLocation`, dir.`tEndLat`, dir.`tEndLong`, dir.`tEndLocation`, dir.`eAddedFor`,tr.vRideNo,tr.eType,tr.fDistance, concat(rd.vName,' ',rd.vLastName) as drivername,rd.vEmail as driveremail,concat('+',rd.vCode,rd.vPhone) as driverphone,rd.vCode as vPhoneCode,rd.vPhone as vPhone, rd.vTimeZone FROM driver_insurance_report AS dir 
    LEFT JOIN trips AS tr ON tr.iTripId = dir.iTripId 
    LEFT JOIN register_driver AS rd ON rd.iDriverId = dir.iDriverId where 1=1 and eAddedFor='$eAddedFor' $ssql $trp_ssql $ord";

    $flag = false;
    $db_trip = $obj->MySQLSelect($sql);

    if(!empty($ENABLE_PROVIDER_INSURANCE_LOCATIONS) && strtoupper($ENABLE_PROVIDER_INSURANCE_LOCATIONS) == "YES") {
        if($eAddedFor == "Available") {
            $tbl_driver_locations = "driver_locations";

            $systemTimeZone = date_default_timezone_get();

            date_default_timezone_set('UTC');

            $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
            $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
            $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "0";
            $serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : "";

            $searchQuery = array();

            if(!empty($startDate) && !empty($endDate)) {
                $startDateString = DateTime::createFromFormat('Y-m-d', $startDate);
                $startDateString = $startDateString->format('Y-m-d\T00:00:00\Z');

                $endDateString = DateTime::createFromFormat('Y-m-d', $endDate);
                $endDateString = str_replace('T00:00:00Z', 'T23:59:59Z', $endDateString->format('Y-m-d\T00:00:00\Z'));

                $dates['$gte'] = $startDateString;
                $dates['$lte'] = $endDateString;
                $searchQuery['OnlineDateTime'] = $dates;
            }

            if($searchDriver > 0) {
                $searchQuery['iDriverId'] = $searchDriver;
            }

            if(!empty($serachTripNo)) {
                $tripData = $obj->MySQLSelect("SELECT iTripId FROM trips WHERE vRideNo = '$serachTripNo'");
                if(!empty($tripData)) {
                    $searchQuery['iTripId'] = $tripData[0]['iTripId'];
                } else {
                    $searchQuery['iTripId'] = "";
                }
            }

            $options['OnlineDateTime'] = -1;
            $driver_locations = $obj->fetchAllRecordsFromMongoDBWithSortParams(TSITE_DB, $tbl_driver_locations, $searchQuery, $options);
            $driver_locations = json_decode(json_encode((array) json_decode(\MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($driver_locations)))), true);

            $driverDataArr = $tripDataArr = array();
            if(!empty($driver_locations) && scount($driver_locations) > 0) {
                $iDriverIdArr = array_values(array_unique(array_column($driver_locations, 'iDriverId')));
                $iDriverIds = implode(",", $iDriverIdArr);

                $driverData = $obj->MySQLSelect("SELECT iDriverId, vName, vLastName, vPhone, vCode FROM register_driver WHERE iDriverId IN ($iDriverIds)");
                foreach ($driverData as $drv) {
                    $driverDataArr[$drv['iDriverId']] = $drv;
                }

                $iTripIdArr = array_values(array_unique(array_column($driver_locations, 'iTripId')));
                $iTripIds = implode(",", $iTripIdArr);

                if(!empty($iTripIds)) {
                    $tripsData = $obj->MySQLSelect("SELECT iTripId, vRideNo, iActive, eType, iOrderId FROM trips WHERE iTripId IN ($iTripIds)");
                    foreach ($tripsData as $tr) {
                        $tripDataArr[$tr['iTripId']] = $tr;
                    }
                }
            }

        } else {
            $tripsLocArr = array();
            if(!empty($db_trip) && scount($db_trip) > 0) {
                $iTripIds = array_column($db_trip, 'iTripId');
                $searchQuery = array('iTripId' => ['$in' => $iTripIds]);
                $db_trips_locations = $obj->fetchAllRecordsFromMongoDB("trips_locations", $searchQuery);
                $db_trips_locations = json_decode(json_encode((array) json_decode(\MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($db_trips_locations)))), true);

                foreach ($db_trips_locations as $trip_loc) {
                    $tripsLocArr[$trip_loc['iTripId']] = $trip_loc;
                }
            }
        }        
    }

    $filename = $export_file_name."_".$timestamp_filename.'.xls';
    // Get the active sheet
    if ($eAddedFor == "Accept"){
        $accept_time = $langage_lbl_admin['LBL_TRIP_TXT']." Accepted Time";
        $cancel_time = $langage_lbl_admin['LBL_TRIP_TXT']." Start/Cancel Time";
        $distance_time = "Approx Distance Travelled";
    }else if ($eAddedFor == "Trip"){
        $accept_time = $langage_lbl_admin['LBL_TRIP_TXT']." Start Time";
        $cancel_time = $langage_lbl_admin['LBL_TRIP_TXT']." End Time";
        $distance_time = "Distance Travelled";
    }else{
        $accept_time = "Online Time";
        $cancel_time = $langage_lbl_admin['LBL_TRIP_TXT']." Accepted/Offline Time";
        $distance_time = "Approx Distance Travelled";
    }
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $name_lable = "";
    if ($eAddedFor == "Available"){
        $name_lable = "Trip Number";
    } elseif ($eAddedFor == "Accept") {
        $name_lable = "Ride Number";
    } else {
        $name_lable = "Trip/Job Number";
    }

    if(!empty($ENABLE_PROVIDER_INSURANCE_LOCATIONS) && strtoupper($ENABLE_PROVIDER_INSURANCE_LOCATIONS) == "YES") {
        $name_lable = $langage_lbl_admin['LBL_TRIP_TXT_ADMIN'];
    }

    $sheet->setCellValue('A1',$name_lable);
    $sheet->setCellValue('B1',$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Name");
    $sheet->setCellValue('C1',$langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']." Phone");
    $sheet->setCellValue('D1',$accept_time);
    $sheet->setCellValue('E1',$cancel_time);
    $sheet->setCellValue('F1',$distance_time);
    $sheet->setCellValue('G1','Time Taken to Distance Travelled');
    $i = 2;
    $serverTimeZone = date_default_timezone_get();

    if(!empty($ENABLE_PROVIDER_INSURANCE_LOCATIONS) && strtoupper($ENABLE_PROVIDER_INSURANCE_LOCATIONS) == "YES" && $eAddedFor == "Available") {

        foreach ($driver_locations as $drv_loc) {
            if (isset($drv_loc['iTripId']) && isset($tripDataArr[$drv_loc['iTripId']])) {
                if ($tripDataArr[$drv_loc['iTripId']]['iActive'] == "Canceled") {
                    $vRideNo = $tripDataArr[$drv_loc['iTripId']]['vRideNo'] . ' (Canceled)';
                } else {
                    $vRideNo = $tripDataArr[$drv_loc['iTripId']]['vRideNo'];
                }
            } else {
                $vRideNo = "--";
            }

            $drv_loc['ride_no'] = $vRideNo;
            $drv_loc['name'] = clearName($driverDataArr[$drv_loc['iDriverId']]['vName'] . ' ' . $driverDataArr[$drv_loc['iDriverId']]['vLastName']);
            $drv_loc['phone'] = '(+' . $driverDataArr[$drv_loc['iDriverId']]['vCode'] . ') ' . clearPhone($driverDataArr[$drv_loc['iDriverId']]['vPhone']);

            $date_format_data_array = array(
                'langCode'         => $default_lang,
                'DateFormatForWeb' => 1
            );
            
            $OnlineDateTime = date('Y-m-d H:i:s', strtotime($drv_loc['OnlineDateTime']));
            $OnlineDateTime = converToTz($OnlineDateTime, $systemTimeZone, "UTC");

            $date_format_data_array['tdate'] = $OnlineDateTime; 
            $get_OnlineDateTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $Start_time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($systemTimeZone, $date_format_data_array['tdate']).")";
            $drv_loc['date'] = $get_OnlineDateTime_format['tDisplayDateTime'].$Start_time_zone_difference_text;

            if(isset($drv_loc['OfflineDateTime'])) {
                $OfflineDateTime = date('Y-m-d H:i:s', strtotime($drv_loc['OfflineDateTime']));
                $OfflineDateTime = converToTz($OfflineDateTime, $systemTimeZone, "UTC");

                $date_format_data_array['tdate'] = $OfflineDateTime; 
                $get_OfflineDateTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($systemTimeZone, $date_format_data_array['tdate']).")";

                $drv_loc['edate'] = $get_OfflineDateTime_format['tDisplayDateTime'].$time_zone_difference_text;

            } else {
                $drv_loc['edate'] = "--";
            }

            $totalDistance = 0;
            if(isset($drv_loc['Locations'])) {
                $totalDistance = calculateTotalDistance($drv_loc['Locations']);
            }

            $vDistance = number_format($totalDistance, 2);
            if ($DEFAULT_DISTANCE_UNIT == "Miles"){
                $vDistance1 = str_replace(",","",$vDistance);
                $vDistance = number_format($vDistance1 * KM_TO_MILES_RATIO, 2);
            }
            $drv_loc['distance'] = $vDistance." ".$DEFAULT_DISTANCE_UNIT;

            $a = strtotime($drv_loc['OnlineDateTime']);
            $b = strtotime($drv_loc['OfflineDateTime']);
            $diff_time = ($b - $a);
            $ans_diff = set_hour_min($diff_time);

            $data_time_txt = "";
            if ($ans_diff['hour'] != 0){
                $data_time_txt = $ans_diff['hour']." Hours ".$ans_diff['minute']." Minutes"."\t";
            }else{
                if ($ans_diff['minute'] != 0){
                    $data_time_txt .= $ans_diff['minute']." Minutes ";
                }
                if ($ans_diff['second'] < 0){
                    $data_time_txt .= "-"."\t";
                }else{
                    $data_time_txt .= $ans_diff['second']." Seconds"."\t";
                }
            }
            $drv_loc['time'] = $data_time_txt;
            $sheet->setCellValue('A'.$i,$drv_loc['ride_no']);
            $sheet->setCellValue('B'.$i,$drv_loc['name']);
            $sheet->setCellValue('C'.$i,$drv_loc['phone']);
            $sheet->setCellValue('D'.$i,$drv_loc['date']);
            $sheet->setCellValue('E'.$i,$drv_loc['edate']);
            $sheet->setCellValue('F'.$i,$drv_loc['distance']);
            $sheet->setCellValue('G'.$i,$drv_loc['time']);
            $i++;
        }

    } else {
        foreach ($db_trip as $value){
            $vRideNo = ($value['vRideNo'] != "")?$value['vRideNo']:"-";
            $value['ride_no'] = $vRideNo;
            $value['name'] = clearName($value['drivername']);
            $value['phone'] = "(+".($value["vPhoneCode"]).") ".clearPhone($value["vPhone"]);

            $date_format_data_array = array(
                'langCode'         => $default_lang,
                'DateFormatForWeb' => 1
            );
            $time_zone_difference_text = "";
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $value['dStartDate'] != "0000-00-00 00:00:00")?converToTz($value['dStartDate'],$value['vTimeZone'],$serverTimeZone):$value['dStartDate'];
            $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            if ($value['tStartDate'] != "0000-00-00 00:00:00"){
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
            }
            $value['date'] = $get_dStartDate_format['tDisplayDateTime'].$time_zone_difference_text;

            $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $value['dEndDate'] != "0000-00-00 00:00:00")?converToTz($value['dEndDate'],$value['vTimeZone'],$serverTimeZone):$value['dEndDate'];
            if ($value['dEndDate'] != "0000-00-00 00:00:00"){
                $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
            }else{
                $get_dEndDate_format['tDisplayDateTime'] = "-";
                $time_zone_difference_text = "";
            }
            $value['edate'] = $get_dEndDate_format['tDisplayDateTime'].$time_zone_difference_text;

            $distance_tot = ($eAddedFor == "Trip")?$value['fDistance']:$value['vDistance'];
            $distance_tot = ($distance_tot == "")?"0":$distance_tot;

            if(!empty($ENABLE_PROVIDER_INSURANCE_LOCATIONS) && strtoupper($ENABLE_PROVIDER_INSURANCE_LOCATIONS) == "YES") {
                date_default_timezone_set('UTC');

                if($eAddedFor == "Accept") {
                    $AcceptDateTime = date('Y-m-d H:i:s', strtotime($tripsLocArr[$value['iTripId']]['Accept']['dDateTime']));
                    $AcceptDateTime = converToTz($AcceptDateTime, $serverTimeZone, "UTC");

                    $date_format_data_array['tdate'] = $AcceptDateTime; 
                    $get_AcceptDateTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($serverTimeZone, $date_format_data_array['tdate']).")";
                    $value['date'] = $get_AcceptDateTime_format['tDisplayDateTime'].$time_zone_difference_text;

                    if(isset($tripsLocArr[$value['iTripId']]['Begin'])) {
                        $StartDateTime = date('Y-m-d H:i:s', strtotime($tripsLocArr[$value['iTripId']]['Begin']['dDateTime']));
                        $StartDateTime = converToTz($StartDateTime, $serverTimeZone, "UTC");

                        $date_format_data_array['tdate'] = $StartDateTime; 
                        $get_StartDateTime_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $get_utc_time = DateformatCls::getUTCDiff($serverTimeZone, $date_format_data_array['tdate']);
                        $time_zone_difference_text = (!empty($get_utc_time)) ? " (UTC:".$get_utc_time.")" : "";
                        $value['edate'] = $get_StartDateTime_format['tDisplayDateTime'].$time_zone_difference_text;

                    } else {
                        $value['edate'] = "--";
                    }

                    $totalDistance = 0;
                    if(isset($tripsLocArr[$value['iTripId']]['Accept']['Locations'])) {
                        $totalDistance = calculateTotalDistance($tripsLocArr[$value['iTripId']]['Accept']['Locations']);
                    }

                    $value['dStartDate'] = $tripsLocArr[$value['iTripId']]['Accept']['dDateTime'];

                    if(isset($tripsLocArr[$value['iTripId']]['Arrived']['Locations'])) {
                        $totalDistance += calculateTotalDistance($tripsLocArr[$value['iTripId']]['Accept']['Arrived']);

                        $value['dEndDate'] = $tripsLocArr[$value['iTripId']]['Arrived']['dDateTime'];
                    }

                    if(isset($tripsLocArr[$value['iTripId']]['Begin']['dDateTime'])) {
                        $value['dEndDate'] = $tripsLocArr[$value['iTripId']]['Begin']['dDateTime'];

                    } elseif(isset($tripsLocArr[$value['iTripId']]['Arrived']['dDateTime'])) {
                        $value['dEndDate'] = $tripsLocArr[$value['iTripId']]['Arrived']['dDateTime'];

                    } elseif (isset($tripsLocArr[$value['iTripId']]['Accept']['dEndDateTime'])) {
                        $value['dEndDate'] = $tripsLocArr[$value['iTripId']]['Accept']['dEndDateTime'];
                    }

                    $distance_tot = $totalDistance;

                } else {
                    $StartDateTime = date('Y-m-d H:i:s', strtotime($tripsLocArr[$value['iTripId']]['Begin']['dDateTime']));
                    $StartDateTime = converToTz($StartDateTime, $serverTimeZone, "UTC");

                    $date_format_data_array['tdate'] = $StartDateTime; 
                    $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $Start_time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($serverTimeZone, $date_format_data_array['tdate']).")";
                    $value['date'] = $get_dStartDate_format['tDisplayDateTime'].$Start_time_zone_difference_text;

                    $value['dStartDate'] = $tripsLocArr[$value['iTripId']]['Begin']['dDateTime'];

                    if($tripsLocArr[$value['iTripId']]['Begin']['dEndDateTime']) {
                        $EndDateTime = date('Y-m-d H:i:s', strtotime($tripsLocArr[$value['iTripId']]['Begin']['dEndDateTime']));       
                        $EndDateTime = converToTz($EndDateTime, $serverTimeZone, "UTC");

                        $date_format_data_array['tdate'] = $EndDateTime; 
                        $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($serverTimeZone, $date_format_data_array['tdate']).")";
                        $value['edate'] = $get_dEndDate_format['tDisplayDateTime'].$time_zone_difference_text;

                        $value['dEndDate'] = $tripsLocArr[$value['iTripId']]['Begin']['dEndDateTime'];

                    } elseif (isset($tripsLocArr[$value['iTripId']]['Accept']['dEndDateTime'])) {
                        $EndDateTime = date('Y-m-d H:i:s', strtotime($tripsLocArr[$value['iTripId']]['Accept']['dEndDateTime']));       
                        $EndDateTime = converToTz($EndDateTime, $serverTimeZone, "UTC");

                        $date_format_data_array['tdate'] = $EndDateTime; 
                        $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($serverTimeZone, $date_format_data_array['tdate']).")";
                        $value['edate'] = $get_dEndDate_format['tDisplayDateTime'].$time_zone_difference_text;

                        $value['dEndDate'] = $tripsLocArr[$value['iTripId']]['Accept']['dEndDateTime'];
                    } else {
                        $get_dEndDate_format['tDisplayDateTime'] = "--";
                        $time_zone_difference_text = "";
                    }

                    $totalDistance = 0;
                    if(isset($tripsLocArr[$value['iTripId']]['Begin']['Locations'])) {
                        $totalDistance = calculateTotalDistance($tripsLocArr[$value['iTripId']]['Begin']['Locations']);
                    }

                    $distance_tot = $totalDistance;
                }
                
            }

            $vDistance = number_format($distance_tot,2);
            if ($DEFAULT_DISTANCE_UNIT == "Miles"){
                $vDistance1 = str_replace(",","",$vDistance);
                $vDistance = number_format($vDistance1 * KM_TO_MILES_RATIO, 2);
            }
            $value['distance'] = $vDistance." ".$DEFAULT_DISTANCE_UNIT;

            $a = strtotime($value['dStartDate']);
            $b = strtotime($value['dEndDate']);
            $diff_time = ($b - $a);
            $ans_diff = set_hour_min($diff_time);

            $data_time_txt = "";
            if ($ans_diff['hour'] != 0){
                $data_time_txt = $ans_diff['hour']." Hours ".$ans_diff['minute']." Minutes"."\t";
            }else{
                if ($ans_diff['minute'] != 0){
                    $data_time_txt .= $ans_diff['minute']." Minutes ";
                }
                if ($ans_diff['second'] < 0){
                    $data_time_txt .= "-"."\t";
                }else{
                    $data_time_txt .= $ans_diff['second']." Seconds"."\t";
                }
            }
            $value['time'] = $data_time_txt;
            $sheet->setCellValue('A'.$i,$value['ride_no']);
            $sheet->setCellValue('B'.$i,$value['name']);
            $sheet->setCellValue('C'.$i,$value['phone']);
            $sheet->setCellValue('D'.$i,$value['date']);
            $sheet->setCellValue('E'.$i,$value['edate']);
            $sheet->setCellValue('F'.$i,$value['distance']);
            $sheet->setCellValue('G'.$i,$value['time']);
            $i++;
        }
    }
    
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'trackingTripList'){

    $startDate = (isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'');
    $endDate = (isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'');
    $ord = ' ORDER BY t.iTrackServiceTripId DESC';
    if ($startDate != ''){
        $ssql .= " AND Date(t.dStartDate) >='".$startDate."'";
    }
    if ($endDate != ''){
        $ssql .= " AND Date(t.dStartDate) <='".$endDate."'";
    }
    $sql = "SELECT t.iTrackServiceTripId,d.vName,d.vLastName,t.tStartLocation,t.tEndLocation,t.dStartDate,t.eTripStatus,t.eTripType
        FROM track_service_trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId 
        WHERE 1=1  {$ssql}  {$ord}";
    $db_trip = $obj->MySQLSelect($sql);
    $header = $data = "";
    $header .= "Pickup Location"."\t";
    $header .= "Dropoff  Location"."\t";
    $header .= "Provider"."\t";
    $header .= "Trip Date"."\t";
    $header .= "Trip Type"."\t";
    $header .= "Status"."\t";
    for ($j = 0;$j < scount($db_trip);$j++){
        $data .= $db_trip[$j]['tStartLocation']."\t";
        $data .= $db_trip[$j]['tEndLocation']."\t";
        $data .= $db_trip[$j]['vName'].' '.$db_trip[$j]['vLastName']."\t";
        $data .= DateTime($db_trip[$j]['dStartDate'],'21')."\t";
        $data .= $db_trip[$j]['eTripType']."\t";
        $data .= $db_trip[$j]['eTripStatus']."\t";
        $data .= "\n";
    }
    ob_clean();
    header("Content-type: application/octet-sdleam; charset=utf-8");
    header("Content-Disposition: attachment; filename=Tracking company list.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
if ($section == 'PublishedRides'){
    $eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $searchRideNo = isset($_REQUEST['searchRideNo'])?$_REQUEST['searchRideNo']:'';
    $ord = ' ORDER BY pr.iPublishedRideId  DESC';
    $ssql_date = "";
    if ($searchRider != ''){
        $ssql .= " AND pr.iUserId = {$searchRider} ";
    }
    if ($eStatus != ''){
        if ($eStatus == "PastRides"){
            $ssql_date = " AND pr.dStartDate < '".date('Y-m-d H:i:s')."' AND pr.eStatus = 'Active' ";
        }else if ($eStatus == "Active"){
            $ssql_date = " AND pr.dStartDate >= '".date('Y-m-d H:i:s')."' AND pr.eStatus = 'Active'";
        }else{
            $ssql .= "AND pr.eStatus = '{$eStatus}' ";
        }
    }
    if ($searchRideNo != ''){
        $ssql .= " AND pr.vPublishedRideNo = {$searchRideNo} ";
    }
    /*if ($startDate != '') {

        $ssql .= " AND Date(pr.dStartDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {

        $ssql .= " AND Date(pr.dStartDate) <='" . $endDate . "'";
    }*/
    if ($startDate != ''){

        $ssql .= " AND Date(pr.dAddedDate) >='".$startDate."'";
    }
    if ($endDate != ''){

        $ssql .= " AND Date(pr.dAddedDate) <='".$endDate."'";
    }
    $TimeZoneOffset = date('P');
    $EXPIRED = 2;
    $EXPIRED_M = $EXPIRED * 60;
    $isExpired = '';
    $isExpired .= "CASE WHEN pr.eTrackingStatus ='Pending' THEN pr.dStartDate < ( (CONVERT_TZ(NOW(), 'SYSTEM', '".$TimeZoneOffset."')) - INTERVAL $EXPIRED_M MINUTE )  ELSE  '0' END  as isExpired,";
    $sql = "SELECT pr.*, $isExpired pr.iUserId AS driver_Id , CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name,riderDriver.vTimeZone FROM published_rides pr 
         JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)
         WHERE 1=1 $ssql $ssql_date $ord";
    $db_trip = $obj->MySQLSelect($sql);
    $filename = "PublishedRides".$eAddedFor."_report.xls";
    $iPublishedRideIdARR = array_column($db_trip,'iPublishedRideId');
    $iPublishedRideIdARR = implode(',',$iPublishedRideIdARR);
    $sql = "SELECT * FROM `published_rides_waypoints` WHERE iPublishedRideId IN ($iPublishedRideIdARR) ";
    $waypoints_data = $obj->MySQLSelect($sql);
    $RIDE_WAYPOINTS = [];
    $RIDE_WAYPOINTS_SUM = [];
    foreach ($waypoints_data as $w){
        $RIDE_WAYPOINTS[$w['iPublishedRideId']][] = $w;
        $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] = $RIDE_WAYPOINTS_SUM[$w['iPublishedRideId']] + $w['iBookedSeats'];
    }
    $RIDE_WAYPOINTS_ARR = [];
    if (isset($RIDE_WAYPOINTS) && !empty($RIDE_WAYPOINTS)){
        foreach ($RIDE_WAYPOINTS as $key => $waypoint){
            $wayPoints = $RIDE_SHARE_OBJ->wayPointDBToArray($waypoint);
            $wayPoints['iBookedSeats'] = $waypoint['iBookedSeats'];
            $RIDE_WAYPOINTS_ARR[$key] = $wayPoints['waypoint_data'];
        }
    }
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',"Ride No");
    $sheet->setCellValue('B1',$langage_lbl_admin['LBL_RIDE_SHARE_PUBLISHED_BY']);
    $sheet->setCellValue('C1',"Published Date");
    $sheet->setCellValue('D1',"Ride Start Time");
    $sheet->setCellValue('E1',"Ride End Time");
    $sheet->setCellValue('F1',$langage_lbl_admin['LBL_RIDE_SHARE_DURATION']);
    $sheet->setCellValue('G1',$langage_lbl_admin['LBL_TRACK_SERVICE_START_LOC_TXT']);
    $sheet->setCellValue('H1',$langage_lbl_admin['LBL_TRACK_SERVICE_END_LOC_TXT']);
    $sheet->setCellValue('I1',$langage_lbl_admin['LBL_RIDE_SHARE_PRICE_PER_SEAT_TOTAL_TXT']);
    $sheet->setCellValue('J1',"Total seats");
    $sheet->setCellValue('K1',"Occupied Seats");
    $sheet->setCellValue('L1',$langage_lbl_admin['LBL_Status']);
    $i = 2;
    $serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($db_trip);$j++){
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['dStartDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['dStartDate'];
        $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dStartDate = $get_dStartDate_format['tDisplayDateTime'];
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['dEndDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['dEndDate'];
        $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dEndDate = $get_dEndDate_format['tDisplayDateTime'];
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['dAddedDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['dAddedDate'];
        $get_dAddedDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dAddedDate = $get_dAddedDate_format['tDisplayDateTime'];
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$j]['vTimeZone'],$date_format_data_array['tdate']).")";
        if ($db_trip[$j]['iBookedSeats'] > 0 && $db_trip[$j]['eStatus'] != "Cancelled"){
            $iBookedSeats = $db_trip[$j]['iBookedSeats'];
        }else{
            $iBookedSeats = '-';
        }
        if (strtotime($db_trip[$j]['dStartDate']) > strtotime(date("Y-m-d H:i:s")) || $db_trip[$j]['eStatus'] == "Cancelled"){
            $eStatus = $db_trip[$j]['eStatus'];
        }else{
            $eStatus = '-';
        }
        if ($db_trip[$j]['isExpired'] == "1"){
            $eStatus = $langage_lbl_admin['LBL_EXPIRED_TXT'];
        }
        $time = $RIDE_SHARE_OBJ->convertSecToMin(floor((int)$db_trip[$j]['fDuration']));
        if (isset($RIDE_WAYPOINTS_ARR[$db_trip[$j]['iPublishedRideId']]) && !empty($RIDE_WAYPOINTS_ARR[$db_trip[$j]['iPublishedRideId']])){
            $RIDE_WAYPOINTS = @$RIDE_WAYPOINTS_ARR[$db_trip[$j]['iPublishedRideId']];
            $v = 1;
            foreach ($RIDE_WAYPOINTS as $key => $w){
                $db_trip[$j]['tStartLocation'] .= PHP_EOL.' STOP '.$v.': '.$w['address'];
                $v++;
            }
        }
        $sheet->setCellValue('A'.$i,$db_trip[$j]['vPublishedRideNo']);
        $sheet->setCellValue('B'.$i,$db_trip[$j]['driver_Name']);
        $sheet->setCellValue('C'.$i,$dAddedDate.$time_zone_difference_text);
        $sheet->setCellValue('D'.$i,$dStartDate);
        $sheet->setCellValue('E'.$i,$dEndDate);
        $sheet->setCellValue('F'.$i,$time);
        $sheet->setCellValue('G'.$i,$db_trip[$j]['tStartLocation']);
        $sheet->setCellValue('H'.$i,$db_trip[$j]['tEndLocation']);
        $sheet->setCellValue('I'.$i,formateNumAsPerCurrency($db_trip[$j]['fPrice'],''));
        $sheet->setCellValue('J'.$i,$db_trip[$j]['iAvailableSeats']);
        $sheet->setCellValue('K'.$i,$iBookedSeats);
        $sheet->setCellValue('L'.$i,$eStatus);
        $i++;
    }
    // Auto-size columns
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == 'PublishedRidesBooking'){

    $option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
    $keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
    $searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
    $eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $searchRideNo = isset($_REQUEST['searchRideNo'])?$_REQUEST['searchRideNo']:'';
    $searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $isRideShare = isset($_REQUEST['rideShare'])?$_REQUEST['rideShare']:'';
    $ord = ' ORDER BY pr.iPublishedRideId  DESC';
    $ssql = '';
    if ($searchRider != ''){
        $ssql .= " AND rsb.iUserId = {$searchRider} ";
    }
    if ($searchDriver != ''){
        $ssql .= " AND pr.iUserId = {$searchDriver} ";
    }
    if ($eStatus != ''){
        $ssql .= " AND rsb.eStatus = '{$eStatus}' ";
    }
    if (isset($iPublishedRideId) && !empty($iPublishedRideId)){
        $ssql .= " AND rsb.iPublishedRideId = ".$iPublishedRideId." AND rsb.eStatus = 'Approved'";
    }
    if ($startDate != ''){

        $ssql .= " AND Date(rsb.dBookingDate) >='".$startDate."'";
    }
    if ($endDate != ''){

        $ssql .= " AND Date(rsb.dBookingDate) <='".$endDate."'";
    }
    if ($searchRideNo != ''){
        $ssql .= " AND pr.vPublishedRideNo = '{$searchRideNo}' ";
    }
    $sql = "SELECT  CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name,  CONCAT(riderUser.vName,' ',riderUser.vLastName) AS  rider_Name, 
                riderUser.vImgName as rider_ProfileImg, riderUser.iUserId as rider_iUserId, riderDriver.iUserId as driver_iUserId,
                pr.tStartLocation,pr.tStartLat,pr.tStartLong,pr.tEndLocation,pr.tEndLat,pr.tEndLong,pr.dStartDate,pr.dStartDate,pr.dEndDate,pr.tPriceRatio,
                pr.tEndCity,pr.tStartCity,rsb.dBookingDate,pr.fDuration,pr.vPublishedRideNo,pr.eTrackingStatus,
                
                rsb.iPublishedRideId,rsb.eStatus,rsb.fTotal,rsb.iBookedSeats,pr.tDriverDetails,rsb.iBookingId,rsb.iCancelReasonId,rsb.tCancelReason,rsb.vBookingNo,riderUser.vTimeZone
                FROM ride_share_bookings rsb 
                JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId)
                JOIN register_user riderUser  ON (riderUser.iUserId = rsb.iUserId)
                JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)  WHERE 1=1 $ssql $ord";
    $db_trip = $obj->MySQLSelect($sql);
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$langage_lbl_admin["LBL_TRIP_NO_ADMIN"]);
    $sheet->setCellValue('B1',"Publish Ride No");
    $sheet->setCellValue('C1',"Booked By");
    $sheet->setCellValue('D1',$langage_lbl_admin['LBL_RIDE_SHARE_PUBLISHED_BY']);
    $sheet->setCellValue('E1',"Ride Start Time");
    $sheet->setCellValue('F1',"Ride End Time");
    $sheet->setCellValue('G1',$langage_lbl_admin['LBL_TRACK_SERVICE_START_LOC_TXT']);
    $sheet->setCellValue('H1',$langage_lbl_admin['LBL_RIDE_SHARE_DETAILS_END_LOC_TXT']);
    $sheet->setCellValue('I1',$langage_lbl_admin['LBL_RIDE_SHARE_BOOKED_SEATS']);
    $sheet->setCellValue('J1',$langage_lbl_admin['LBL_BOOKING_DATE']);
    $sheet->setCellValue('K1',"Booking Status");
    $i = 2;
    $serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($db_trip);$j++){
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['dStartDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['dStartDate'];
        $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dStartDate = $get_dStartDate_format['tDisplayDateTime'];
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['dEndDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['dEndDate'];
        $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dEndDate = $get_dEndDate_format['tDisplayDateTime'];
        $date_format_data_array['tdate'] = (!empty($db_trip[$j]['vTimeZone']))?converToTz($db_trip[$j]['dBookingDate'],$db_trip[$j]['vTimeZone'],$serverTimeZone):$db_trip[$j]['dBookingDate'];
        $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dBookingDate = $get_dBookingDate_format['tDisplayDateTime'];
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($db_trip[$j]['vTimeZone'],$date_format_data_array['tdate']).")";
        $time = $RIDE_SHARE_OBJ->convertSecToMin(floor((int)$db_trip[$j]['fDuration']));
        $sheet->setCellValue('A'.$i,$db_trip[$j]['vBookingNo']);
        $sheet->setCellValue('B'.$i,$db_trip[$j]['vPublishedRideNo']);
        $sheet->setCellValue('C'.$i,$db_trip[$j]['rider_Name']);
        $sheet->setCellValue('D'.$i,$db_trip[$j]['driver_Name']);
        $sheet->setCellValue('E'.$i,$dStartDate);
        $sheet->setCellValue('F'.$i,$dEndDate);
        $sheet->setCellValue('G'.$i,$db_trip[$j]['tStartLocation']);
        $sheet->setCellValue('H'.$i,$db_trip[$j]['tEndLocation']);
        $sheet->setCellValue('I'.$i,$db_trip[$j]['iBookedSeats']);
        $sheet->setCellValue('J'.$i,$dBookingDate.$time_zone_difference_text);
        $sheet->setCellValue('K'.$i,$RIDE_SHARE_OBJ->getDisplayStatusForAdmin($db_trip[$j]['eTrackingStatus'],$db_trip[$j]['eStatus'])['status']);
        $i++;
    }
    // Auto-size columns
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $filename = "PublishedRidesBooking_report.xls";
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
}
if ($section == "RideSharePaymentReport"){
    $option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
    $keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
    $searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
    $eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $searchBookingNo = isset($_REQUEST['searchBookingNo'])?$_REQUEST['searchBookingNo']:'';
    $searchRideNo = isset($_REQUEST['searchRideNo'])?$_REQUEST['searchRideNo']:'';
    $searchPaymentStatus = isset($_REQUEST['searchPaymentStatus'])?$_REQUEST['searchPaymentStatus']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $endDate = isset($_REQUEST['rideShare'])?$_REQUEST['endDate']:'';
    $ord = ' ORDER BY pr.iPublishedRideId  DESC';
    if ($searchRider != ''){
        $ssql .= " AND rsb.iUserId = {$searchRider} ";
    }
    if ($searchDriver != ''){
        $ssql .= " AND pr.iUserId = {$searchDriver} ";
    }
    if ($searchBookingNo != ''){

        $ssql .= " AND rsb.vBookingNo IN ($searchBookingNo) ";
    }
    if ($searchRideNo != ''){
        $ssql .= " AND pr.vPublishedRideNo = {$searchRideNo} ";
    }
    if ($searchPaymentStatus != ''){
        $ssql .= " AND rsb.ePaymentStatus = '{$searchPaymentStatus}' ";
    }
    /*if ($startDate != '') {
        $ssql .= " AND Date(pr.dStartDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(pr.dStartDate) <='" . $endDate . "'";
    }*/
    if ($startDate != ''){

        $ssql .= " AND Date(pr.dAddedDate) >='".$startDate."'";
    }
    if ($endDate != ''){

        $ssql .= " AND Date(pr.dAddedDate) <='".$endDate."'";
    }
    if (isset($iPublishedRideId) && !empty($iPublishedRideId)){
        $ssql .= " AND rsb.iPublishedRideId = ".$iPublishedRideId;
    }
    $ssql .= "AND  (rsb.eStatus = 'Approved' OR (rsb.eStatus = 'Cancelled' AND rsb.eCommissionDeduct = 'Yes') )";
    $sql = "SELECT  CONCAT(riderDriver.vName,' ',riderDriver.vLastName) AS driver_Name,  CONCAT(riderUser.vName,' ',riderUser.vLastName) AS  rider_Name, 
                riderUser.vImgName as rider_ProfileImg, riderUser.iUserId as rider_iUserId, riderDriver.iUserId as driver_iUserId,
                rsb.fTax1,rsb.fTax2,pr.vPublishedRideNo,pr.tStartLocation,pr.tStartLat,pr.tStartLong,pr.tEndLocation,pr.tEndLat,pr.tEndLong,pr.dStartDate,pr.dStartDate,pr.dEndDate,pr.tPriceRatio,
                pr.tEndCity,pr.tStartCity,rsb.vBookingNo,rsb.dBookingDate,
                rsb.iPublishedRideId,rsb.eStatus,rsb.fTotal,rsb.iBookedSeats,pr.tDriverDetails,rsb.iBookingId,rsb.iCancelReasonId,rsb.tCancelReason, rsb.iBookingId , rsb.fBookingFee, rsb.ePaymentOption, rsb.ePaymentStatus,riderUser.vTimeZone
                FROM ride_share_bookings rsb 
                JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId)
                JOIN register_user riderUser  ON (riderUser.iUserId = rsb.iUserId)
                JOIN register_user riderDriver  ON (riderDriver.iUserId = pr.iUserId)  WHERE 1=1   $ssql $ord";
    $data_drv = $obj->MySQLSelect($sql);
    $SPREADSHEET_OBJ->setActiveSheetIndex(0);
    // Get the active sheet
    $sheet = $SPREADSHEET_OBJ->getActiveSheet();
    $sheet->setCellValue('A1',$langage_lbl_admin['LBL_TRIP_NO_ADMIN']);
    $sheet->setCellValue('B1',$langage_lbl_admin['LBL_RIDE_SHARE_PUBLISH_NO']);
    $sheet->setCellValue('C1',$langage_lbl_admin['LBL_RIDE_SHARE_PUBLISHED_BY']);
    $sheet->setCellValue('D1',"Booked By");
    $sheet->setCellValue('E1',$langage_lbl_admin['LBL_BOOKING_DATE']);
    $sheet->setCellValue('F1',"Ride Start Time");
    $sheet->setCellValue('G1',"Ride End Time");
    $sheet->setCellValue('H1',$langage_lbl_admin['LBL_RIDE_SHARE_BOOKED_SEATS']);
    $sheet->setCellValue('I1',"Booking Fee / Site Earning");
    /* $sheet->setCellValue('J1', "Tax 1");
    $sheet->setCellValue('K1', "Tax 2"); */
    $sheet->setCellValue('J1',$langage_lbl_admin['LBL_TOTAL_TXT']);
    $sheet->setCellValue('K1',"Total Tax");
    $sheet->setCellValue('L1',"Publisher payout / take amount Payment");
    $sheet->setCellValue('M1',$langage_lbl_admin['LBL_PAYMENT_METHOD']);
    $sheet->setCellValue('N1',$langage_lbl_admin['LBL_Status']);
    $sheet->setCellValue('O1',"Settle");
    $i = 2;
    $serverTimeZone = date_default_timezone_get();
    for ($j = 0;$j < scount($data_drv);$j++){
        $Refunded = '';
        if ($data_drv[$j]['eStatus'] == 'Cancelled'){
            $Refunded = "(Refunded)";
        }
        $time = $RIDE_SHARE_OBJ->convertSecToMin(floor($data_drv[$j]['fDuration']));
        $class_setteled = "";
        if ($data_drv[$j]['ePaymentStatus'] == "Settled"){
            $class_setteled = "setteled-class";
        }
        $set_unsetarray[] = $data_drv[$j]['ePaymentStatus'];
        $driverAmount = $data_drv[$j]['fTotal'] - ($data_drv[$j]['fBookingFee'] + $data_drv[$j]['fTax1'] + $data_drv[$j]['fTax2']);
        if ($data_drv[$j]['ePaymentOption'] == "Cash"){
            $driverAmount = $driverAmount - $data_drv[$j]['fTotal'];
        }
        $date_format_data_array = array('langCode'         => $default_lang,
                                        'DateFormatForWeb' => 1);
        $date_format_data_array['tdate'] = (!empty($data_drv[$j]['vTimeZone']))?converToTz($data_drv[$j]['dBookingDate'],$data_drv[$j]['vTimeZone'],$serverTimeZone):$data_drv[$j]['dBookingDate'];
        $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dBookingDate = $get_dBookingDate_format['tDisplayDateTime'];
        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($data_drv[$j]['vTimeZone'],$date_format_data_array['tdate']).")";
        $date_format_data_array['tdate'] = (!empty($data_drv[$j]['vTimeZone']))?converToTz($data_drv[$j]['dStartDate'],$data_drv[$j]['vTimeZone'],$serverTimeZone):$data_drv[$j]['dStartDate'];
        $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dStartDate = $get_dStartDate_format['tDisplayDateTime'];
        $date_format_data_array['tdate'] = (!empty($data_drv[$j]['vTimeZone']))?converToTz($data_drv[$j]['dEndDate'],$data_drv[$j]['vTimeZone'],$serverTimeZone):$data_drv[$j]['dEndDate'];
        $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        $dEndDate = $get_dEndDate_format['tDisplayDateTime'];
        $fBookingFee = $data_drv[$j]['fBookingFee'];
        $fTax1 = $data_drv[$j]['fTax1'];
        $fTax2 = $data_drv[$j]['fTax2'];
        $fTotal = $data_drv[$j]['fTotal'];
        $driverAmount = $fTotal - ($fBookingFee + $fTax1 + $fTax2);
        $driverEarning = $driverAmount;
        if ($data_drv[$j]['ePaymentOption'] == "Cash"){
            $driverAmount = $driverAmount - $fTotal;
        }
        $driverTotalAmount += $driverAmount;
        $driverTotalEarning += $driverEarning;
        $totalBookingFee += $fBookingFee;
        $totalTax1 += $fTax1;
        $totalTax2 += $fTax2;
        $totalFare += $fTotal;
        $sheet->setCellValue('A'.$i,$data_drv[$j]['vBookingNo']);
        $sheet->setCellValue('B'.$i,$data_drv[$j]['vPublishedRideNo']);
        $sheet->setCellValue('C'.$i,$data_drv[$j]['driver_Name']);
        $sheet->setCellValue('D'.$i,$data_drv[$j]['rider_Name']);
        $sheet->setCellValue('E'.$i,$dBookingDate.$time_zone_difference_text);
        $sheet->setCellValue('F'.$i,$dStartDate);
        $sheet->setCellValue('G'.$i,$dEndDate);
        $sheet->setCellValue('H'.$i,$data_drv[$j]['iBookedSeats']);
        $sheet->setCellValue('I'.$i,formateNumAsPerCurrency($data_drv[$j]['fBookingFee'],''));
        /* $sheet->setCellValue('J' . $i, formateNumAsPerCurrency($data_drv[$j]['fTax1'], ''));
        $sheet->setCellValue('K' . $i, formateNumAsPerCurrency($data_drv[$j]['fTax2'], '')); */
        $sheet->setCellValue('J'.$i,formateNumAsPerCurrency($data_drv[$j]['fTotal'],''));
        $sheet->setCellValue('K'.$i,formateNumAsPerCurrency(($data_drv[$j]['fTax1'] + $data_drv[$j]['fTax2']),''));
        $sheet->setCellValue('L'.$i,($data_drv[$j]['eStatus'] == 'Cancelled')?"-":formateNumAsPerCurrency($driverAmount,''));
        $sheet->setCellValue('M'.$i,$data_drv[$j]['ePaymentOption']);
        $sheet->setCellValue('N'.$i,$data_drv[$j]['eStatus'].$Refunded);
        $sheet->setCellValue('O'.$i,$data_drv[$j]['ePaymentStatus']);
        $i++;
    }
    $totalTax = $totalTax1 + $totalTax2;
    $i += 1;
    $Summary_array = array("Total Fare "                                   => formateNumAsPerCurrency($totalFare,''),
                           "Total Booking Fee / Site Earning "             => formateNumAsPerCurrency($totalBookingFee,''),
                           "Total Tax "                                    => formateNumAsPerCurrency($totalTax,''),
                           "Total Earning amount Publisher "               => formateNumAsPerCurrency($driverTotalEarning,''),
                           "Total Publisher payout / take amount Payment " => formateNumAsPerCurrency($driverTotalAmount,''));
    foreach ($Summary_array as $key => $value){
        $sheet->setCellValue('N'.$i,$key);
        $sheet->setCellValue('O'.$i,$value);
        $i++;
    }
    // Auto-size columns
    foreach (range('A',$sheet->getHighestDataColumn()) as $columnID){
        $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    $filename = "ride_share_payment_report.xls";
    $SPREADSHEET_WRITER_OBJ->save('php://output');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');
    // $header = "Booking No." . "\t";
    // $header .= "Ride No.". "\t";
    // $header .= "Published By" . "\t";
    // $header .= "Booked By" . "\t";
    // $header .= "Booking Date" . "\t";
    // $header .= "Ride Start Time" . "\t";
    // $header .= "Ride End Time" . "\t";
    // $header .= "Booked Seats" . "\t";
    // $header .= "Booking Fee / Site Earning" . "\t";
    // $header .= "Tax 1" . "\t";
    // $header .= "Tax 2" . "\t";
    // $header .= "Total" . "\t";
    // $header .= "Publisher payout / take amount Payment" . "\t";
    // $header .= "Payment Method" . "\t";
    // $header .= "Status" . "\t";
    // $header .= "Settle" . "\t";
    // $serverTimeZone = date_default_timezone_get();
    // for ($i = 0; $i < scount($data_drv); $i++) {
    //     $Refunded = '';
    //     if ($data_drv[$i]['eStatus'] == 'Cancelled') {
    //         $Refunded = "(Refunded)";
    //     }
    //     $time = $RIDE_SHARE_OBJ->convertSecToMin(floor($data_drv[$i]['fDuration']));
    //     $class_setteled = "";
    //     if ($data_drv[$i]['ePaymentStatus'] == "Settled") {
    //         $class_setteled = "setteled-class";
    //     }
    //     $set_unsetarray[] = $data_drv[$i]['ePaymentStatus'];
    //     $driverAmount = $data_drv[$i]['fTotal'] - ($data_drv[$i]['fBookingFee'] + $data_drv[$i]['fTax1'] + $data_drv[$i]['fTax2'] );
    //     if ($data_drv[$i]['ePaymentOption'] == "Cash") {
    //         $driverAmount = $driverAmount - ($data_drv[$i]['fBookingFee'] + $data_drv[$i]['fTax1'] + $data_drv[$i]['fTax2'] );
    //     }
    //     $date_format_data_array = array(
    //         'langCode' => $default_lang,
    //         'DateFormatForWeb' => 1
    //     );
    //     $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dBookingDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['dBookingDate'];
    //     $get_dBookingDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
    //     $dBookingDate = $get_dBookingDate_format['tDisplayDateTime'];
    //     $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($data_drv[$i]['vTimeZone'],$date_format_data_array['tdate']).")";
    //     $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dStartDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['dStartDate'];
    //     $get_dStartDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
    //     $dStartDate = $get_dStartDate_format['tDisplayDateTime'];
    //     $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['dEndDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['dEndDate'];
    //     $get_dEndDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
    //     $dEndDate = $get_dEndDate_format['tDisplayDateTime'];
    //     $data .= $data_drv[$i]['vBookingNo'] . "\t";
    //     $data .= $data_drv[$i]['vPublishedRideNo'] . "\t";
    //     $data .= $data_drv[$i]['driver_Name'] . "\t";
    //     $data .= $data_drv[$i]['rider_Name'] . "\t";
    //     $data .= $dBookingDate. $time_zone_difference_text."\t";//date('M d, Y  h:i A', strtotime($data_drv[$i]['dBookingDate'])) . "\t";
    //     $data .= $dStartDate. "\t";//date('M d, Y  h:i A', strtotime($data_drv[$i]['dStartDate'])) . "\t";
    //     $data .= $dEndDate. "\t";//date('M d, Y  h:i A', strtotime($data_drv[$i]['dEndDate'])) . "\t";
    //     $data .= $db_trip[$i]['iBookedSeats'] . "\t";
    //     $data .= formateNumAsPerCurrency($data_drv[$i]['fBookingFee'], '') . "\t";
    //     $data .= formateNumAsPerCurrency($data_drv[$i]['fTax1'], '') . "\t";
    //     $data .= formateNumAsPerCurrency($data_drv[$i]['fTax2'], '') . "\t";
    //     $data .= formateNumAsPerCurrency($data_drv[$i]['fTotal'], '') . "\t";
    //     if ($data_drv[$i]['eStatus'] == 'Cancelled') {
    //         $data .= '-' . "\t";
    //     }else {
    //         $data .= formateNumAsPerCurrency($driverAmount, '') . "\t";
    //     }
    //     $data .= $data_drv[$i]['ePaymentOption'] .  "\t";
    //     $data .= $data_drv[$i]['eStatus'].$Refunded .  "\t";
    //     $data .= $data_drv[$i]['ePaymentStatus'] .  "\n";
    // }
    // $data = str_replace("\r", "", $data);
    // // echo "<pre>".$data;print_r($data);exit;
    // $filename = "PublishedRideReport_report.xls";
    // ob_clean();
    // header("Content-type: application/octet-stream; charset=utf-8");
    // header("Content-Disposition: attachment; filename=$filename");
    // header("Pragma: no-cache");
    // header("Expires: 0");
    // print "$header\n$data";
    // exit;
}
if ($section == "total_trip_details"){

    $ord = ' ORDER BY t.iTripId DESC';
    $sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
    $order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
    if ($sortby == 1){
        if ($order == 0){
            $ord = " ORDER BY t.eType ASC";
        }else{
            $ord = " ORDER BY t.eType DESC";
        }
    }
    if ($sortby == 2){
        if ($order == 0){
            $ord = " ORDER BY t.tStartDate ASC";
        }else{
            $ord = " ORDER BY t.tStartDate DESC";
        }
    }
    if ($sortby == 3){
        if ($order == 0){
            $ord = " ORDER BY c.vCompany ASC";
        }else{
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 4){
        if ($order == 0){
            $ord = " ORDER BY d.vName ASC";
        }else{
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5){
        if ($order == 0){
            $ord = " ORDER BY u.vName ASC";
        }else{
            $ord = " ORDER BY u.vName DESC";
        }
    }
    $ssql = '';
    $action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
    $searchCompany = isset($_REQUEST['searchCompany'])?$_REQUEST['searchCompany']:'';
    $searchDriver = isset($_REQUEST['searchDriver'])?$_REQUEST['searchDriver']:'';
    $searchRider = isset($_REQUEST['searchRider'])?$_REQUEST['searchRider']:'';
    $serachTripNo = isset($_REQUEST['serachTripNo'])?$_REQUEST['serachTripNo']:'';
    $startDate = isset($_REQUEST['startDate'])?$_REQUEST['startDate']:'';
    $endDate = isset($_REQUEST['endDate'])?$_REQUEST['endDate']:'';
    $vStatus = isset($_REQUEST['vStatus'])?$_REQUEST['vStatus']:'';
    if ($action == 'search'){
        if ($startDate != ''){
            $ssql .= " AND Date(t.tStartDate) >='".$startDate."'";
        }
        if ($endDate != ''){
            $ssql .= " AND Date(t.tStartDate) <='".$endDate."'";
        }
        if ($serachTripNo != ''){
            $ssql .= " AND t.vRideNo ='".$serachTripNo."'";
        }
        if ($searchCompany != ''){
            $ssql .= " AND d.iCompanyId ='".$searchCompany."'";
        }
        if ($searchDriver != ''){
            $ssql .= " AND t.iDriverId ='".$searchDriver."'";
        }
        if ($searchRider != ''){
            $ssql .= " AND t.iUserId ='".$searchRider."'";
        }
        if ($vStatus == "onRide"){
            $ssql .= " AND (t.iActive = 'On Going Trip' OR t.iActive = 'Active') AND t.eCancelled='No'";
        }else if ($vStatus == "cancel"){
            $ssql .= " AND (t.iActive = 'Canceled' OR t.eCancelled='yes')";
        }else if ($vStatus == "complete"){
            $ssql .= " AND t.iActive = 'Finished' AND t.eCancelled='No'";
        }
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo'){
        $trp_ssql = " And t.tStartDate > '".WEEK_DATE."'";
    }
    $sql = "SELECT t.fTax1,t.fTax2,t.tStartDate ,t.tEndDate, t.tTripRequestDate,t.vCancelReason,t.vCancelComment, t.iFare,t.eType,d.iDriverId, t.tSaddress,t.vRideNo, t.tDaddress, t.fTripGenerateFare,t.fCommision, t.fDiscount, t.fWalletDebit, t.fTipPrice,t.vTripPaymentMode, t.eCarType,t.iTripId,t.iActive ,CONCAT(u.vName,' ',u.vLastName) AS riderName, CONCAT(d.vName,' ',d.vLastName) AS driverName, d.vAvgRating, c.vCompany, vt.vVehicleType FROM trips t LEFT JOIN register_driver d ON d.iDriverId = t.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId LEFT JOIN  register_user u ON t.iUserId = u.iUserId JOIN company c ON c.iCompanyId=d.iCompanyId WHERE 1=1 AND if(t.iActive ='Canceled',if(t.vTripPaymentMode='Card',1=1,0),1=1) AND t.iActive ='Finished' $ssql $trp_ssql $ord";
    $db_trip = $obj->MySQLSelect($sql);
    if ($APP_TYPE != 'UberX'){
        $header = $langage_lbl_admin['LBL_TRIP_TYPE_TXT_ADMIN']."\t";
    }
    $header .= $langage_lbl_admin['LBL_TRIP_NO']."\t";
    $header .= $langage_lbl_admin['LBL_TRIP_DATE_ADMIN']."\t";
    $header .= "Company"."\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']."\t";
    $header .= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']."\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TRIP_FARE_TXT']."\t";
    $header .= "Platform Fees"."\t";
    $header .= "Total Tax"."\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TRIP_DISCOUNT']."\t";
    $header .= $langage_lbl_admin['LBL_DRIVER_TRIP_WALLET']."\t";
    $header .= $langage_lbl_admin['LBL_TRIP_CASH_PAYMENT']."\t";
    $header .= $langage_lbl_admin['LBL_TRIP_CARD_PAYMENT']."\t";
    for ($i = 0;$i < scount($db_trip);$i++){

        $poolTxt = "";
        if ($db_trip[$i]['ePoolRide'] == "Yes"){
            $poolTxt = " (Pool)";
        }
        $eTypenew = $db_trip[$i]['eType'];
        if ($eTypenew == 'Ride'){
            $trip_type = 'Ride';
        }else if ($eTypenew == 'UberX'){
            $trip_type = 'Other Services';
        }else if ($eTypenew == 'Multi-Delivery'){
            $trip_type = 'Multi-Delivery';
            $link_page = "invoice_multi_delivery.php";
        }else{
            $trip_type = 'Delivery';
        }
        $trip_type .= $poolTxt;
        $totTax = $db_trip[$i]['fTax1'] + $db_trip[$i]['fTax2'];
        if ($APP_TYPE != 'UberX'){
            $data .= $trip_type."\t";
        }
        $data .= $db_trip[$i]['vRideNo']."\t";
        $data .= DateTime($db_trip[$i]['tStartDate'])."\t";
        $data .= clearCmpName($db_trip[$i]['vCompany'])."\t";
        $data .= clearName($db_trip[$i]['driverName'])."\t";
        $data .= $db_trip[$i]['riderName']."\t";
        if ($db_trip[$i]['fTripGenerateFare'] != "" && $db_trip[$i]['fTripGenerateFare'] != 0){
            $data .= trip_currency($db_trip[$i]['fTripGenerateFare'])."\t";
        }else{
            $data .= '-'."\t";
        }
        if ($db_trip[$i]['fCommision'] != "" && $db_trip[$i]['fCommision'] != 0){
            $data .= trip_currency($db_trip[$i]['fCommision'])."\t";
        }else{
            $data .= '-'."\t";
        }
        $data .= trip_currency($totTax)."\t";
        if ($db_trip[$i]['fDiscount'] != "" && $db_trip[$i]['fDiscount'] != 0){
            $data .= trip_currency($db_trip[$i]['fDiscount'])."\t";
        }else{
            $data .= '-'."\t";
        }
        if ($db_trip[$i]['fWalletDebit'] != "" && $db_trip[$i]['fWalletDebit'] != 0){
            $data .= trip_currency($db_trip[$i]['fWalletDebit'])."\t";
        }else{
            $data .= '-'."\t";
        }
        if ($db_trip[$i]['vTripPaymentMode'] == 'Cash'){
            if ($db_trip[$i]['iFare'] != "" && $db_trip[$i]['iFare'] != 0){
                $data .= trip_currency($db_trip[$i]['iFare'])."\t";
            }else{
                $data .= '-'."\t";
            }
        }else{
            $data .= '-'."\t";
        }
        if ($db_trip[$i]['vTripPaymentMode'] == 'Card'){
            if ($db_trip[$i]['iFare'] != "" && $db_trip[$i]['iFare'] != 0){
                $data .= trip_currency($db_trip[$i]['iFare'])."\n";
            }else{
                $data .= '-'."\n";
            }
        }else{
            $data .= '-'."\n";
        }
    }
    $data = str_replace("\r","",$data);
    //echo "<pre>";print_r($data);exit;
    $filename = "total_trip_detail_report.xls";
    //ob_clean();
    header("Content-type: application/octet-stream; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");
    print "$header\n$data";
    exit;
}
?>