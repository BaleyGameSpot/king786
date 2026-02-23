<?php
include_once 'common.php';

if ($WALLET_ENABLE == "No") {
    header('Location: index.php');
    exit;
}

$tbl_name = 'user_wallet';
$script = "Rider Wallet";
$AUTH_OBJ->checkMemberAuthentication();

// $abc = 'admin,rider';
$abc = 'driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//setRole($abc,$url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$type = (isset($_REQUEST['type']) ? $_REQUEST['type'] : '');
if (ucfirst($_SESSION['sess_user']) != $type) {
    header('Location: profile');
    exit;
}
$ssql = '';
$dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : '';
if ($action != '') {
    $startDate = $_REQUEST['startDate'];
    $endDate = $_REQUEST['endDate'];

    if ($startDate != '') {
        $ssql .= " AND DATE(u.dDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND DATE(u.dDate) <='" . $endDate . "'";
    }
}
/* for Withdrawal Money Bank Details */
#echo "type = ".$type;
if ($type == 'Driver') {
    $sql = "SELECT * from register_driver where iDriverId='" . $_SESSION['sess_iUserId'] . "'";
    $db_driver = $obj->MySQLSelect($sql);

   $sql = "SELECT u.*,ru.vTimeZone,ru.vCurrencyDriver as vCurrencyPassenger from user_wallet as u LEFT JOIN register_driver as ru on ru.iDriverId=u.iUserId where u.iUserId='" . $_SESSION['sess_iUserId'] . "' AND u.eUserType = '" . $type . "' " . $ssql . " ORDER BY u.iUserWalletId ASC";
} else {
    $sql = "SELECT u.*,ru.vTimeZone,ru.vCurrencyPassenger from user_wallet as u LEFT JOIN register_user as ru on ru.iUserId=u.iUserId where u.iUserId='" . $_SESSION['sess_iUserId'] . "' AND u.eUserType = '" . $type . "' " . $ssql . " ORDER BY u.iUserWalletId ASC";
}
$db_trip = $obj->MySQLSelect($sql);
/* for Withdrawal Money Bank Details end */

//Added By HJ On 28-02-2020 For Solved 141 Mantis Bug #3804 Start
if(scount($db_trip) > 0){
    $_SESSION["sess_vCurrency"] = $db_trip[0]['vCurrencyPassenger'];
}
//Added By HJ On 28-02-2020 For Solved 141 Mantis Bug #3804 End
$user_available_balance_without_sign = $WALLET_OBJ->FetchMemberWalletBalance($_SESSION['sess_iUserId'], $type);
//$user_available_balance = FetchMemberWalletBalance($_SESSION['sess_iUserId'],$type);

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
$sess_user = "user";
if($_SESSION['sess_user'] == "driver"){
	$sess_user = "driver";
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

$withdraw_request_restrict = $MODULES_OBJ->isEnableWalletWithdrawRequestRestriction() ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_WALLET_RIDER_WALLET'];?></title>

    <!-- Default Top Script and css -->
    <?php
include_once "top/top_script.php";
$rtls = "";
if ($lang_ltr == "yes") {
    $rtls = "dir='rtl'";
    //$direction_lng = "dir='rtl'";
}
?>
<style type="text/css">.bootbox button.close{display: none;}</style>
    <!-- End: Default Top Script and css-->

</head>
<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <?php if ($template != 'taxishark') {?>
    <div id="main-uber-page">
    <?php }?>
        <!-- Left Menu -->
    <?php include_once "top/left_menu.php";?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once "top/header_topbar.php";?>
        <!-- End: Top Menu-->
        <!-- First Section -->
        <?php include_once "top/header.php";?>
        <!-- End: First Section -->
<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption">
            <div class="page-heading">
                <h1><?=$langage_lbl['LBL_WALLET_RIDER_WALLET'];?></h1>
            </div>

            <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                <input type="hidden" name="action" value="search" />
                <div class="filters-column mobile-full">
                    <label><?=$langage_lbl['LBL_SEARCH_TRANSACTION_BY_DATE'];?></label>
                    <select id="timeSelect" name="dateRange">
                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                    <option value="today" <?php if ($dateRange == 'today') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Today'];?></option>
                                    <option value="yesterday" <?php if ($dateRange == 'yesterday') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Yesterday'];?></option>
                                    <option value="currentWeek" <?php if ($dateRange == 'currentWeek') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Current_Week'];?></option>
                                    <option value="previousWeek" <?php if ($dateRange == 'previousWeek') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Previous_Week'];?></option>
                                    <option value="currentMonth" <?php if ($dateRange == 'currentMonth') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Current_Month'];?></option>
                                    <option value="previousMonth" <?php if ($dateRange == 'previousMonth') {echo 'selected';}?> ><?=$langage_lbl['LBL_PREVIOUS'].' '.$langage_lbl['LBL_MONTH_TXT'];?></option>
                                    <option value="currentYear" <?php if ($dateRange == 'currentYear') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Current_Year'];?></option>
                                    <option value="previousYear" <?php if ($dateRange == 'previousYear') {echo 'selected';}?> ><?=$langage_lbl['LBL_Wallet_Previous_Year'];?></option>

                    </select>
                </div>
                <div class="filters-column mobile-half">
                    <label><?= $langage_lbl['LBL_MYTRIP_FROM_DATE'] ?></label>
                    <input type="text" id="dp4" name="startDate" placeholder="<?=$langage_lbl['LBL_MYTRIP_FROM_DATE'];?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                    <i class="icon-cal" id="from-date"></i>
                </div>
                <div class="filters-column mobile-half">
                    <label><?= $langage_lbl['LBL_MYTRIP_TO_DATE'] ?></label>
                    <input type="text" id="dp5" name="endDate" placeholder="<?=$langage_lbl['LBL_MYTRIP_TO_DATE'];?>" class="form-control" value="" readonly style="cursor:default; background-color: #fff"/>
                    <i class="icon-cal" id="to-date"></i>
                </div>
                <div class="filters-column mobile-full">
                    <button class="driver-trip-btn"><?=$langage_lbl['LBL_Wallet_Search'];?></button>
                    <!-- <button onClick="reset();" class="driver-trip-btn"><?=$langage_lbl['LBL_MYTRIP_RESET'];?></button> -->
                    <?php $link = $type == 'Driver' ? 'provider_wallet' : 'user_wallet';?>
                    <a href="<?php echo $link; ?>" class="gen-btn"><?=$langage_lbl['LBL_MYTRIP_RESET'];?></a>
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
                    <td style="display: none"></td>
                    <th width="15%"><?=$langage_lbl['LBL_TRANSACTION_DATE'];?></th>
                    <th width="20%"><?=$langage_lbl['LBL_DESCRIPTION'];?></th>
                    <th width="15%"><?=$langage_lbl['LBL_AMOUNT'];?></th>
                    <!-- <th width="15%"><?=$langage_lbl['LBL_WALLET_TRIP_NO'];?></th> -->
                    <th width="10%"><?=$langage_lbl['LBL_BALANCE_FOR'];?></th>
                    
                    <th width="20%"><?=$langage_lbl['LBL_BALANCE_TYPE'];?></th>
                    <th width="10%"><?=$langage_lbl['LBL_BALANCE'];?></th>
                </tr>
            </thead>
                <tbody>
                    <?

                    // ==================================
                         
                            $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
                            $iUserId = isset($_SESSION['sess_iUserId']) ? $_SESSION['sess_iUserId'] : '';
                            $UserType = isset($type) ? $type : '';
                            $tripTimeZone = isset($db_trip[0]['vTimeZone']) ? $db_trip[0]['vTimeZone'] : '';
                            $ListType = isset($_REQUEST["ListType"]) ? $_REQUEST["ListType"] : 'All';
                            if ($page == "0" || $page == 0) {
                                $page = 1;
                            }
                            if ($UserType == "Passenger") {
                                $UserType = "Rider";
                            }
                            $ssql = '';
                            if ($ListType != "All") {
                                $ssql .= " AND eType ='" . $ListType . "'";
                            }
                            $per_page = 10;
                            $sql_all = "SELECT COUNT(iUserWalletId) As TotalIds FROM user_wallet WHERE  iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ";
                            $data_count_all = $obj->MySQLSelect($sql_all);
                            $TotalPages = ceil($data_count_all[0]['TotalIds'] / $per_page);
                            $start_limit = ($page - 1) * $per_page;
                            $limit = " LIMIT " . $start_limit . ", " . $per_page;
                            $sql = "SELECT * from user_wallet where iUserId='" . $iUserId . "' AND eUserType = '" . $UserType . "' " . $ssql . " ORDER BY iUserWalletId DESC";
                            $Data = $obj->MySQLSelect($sql);
                            $totalNum = scount($Data);
                            $vSymbol = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
                            if ($UserType == 'Driver') {
                                $UserData = get_value('register_driver', 'vCurrencyDriver,vLang', 'iDriverId', $iUserId);
                                $uservSymbol = $UserData[0]['vCurrencyDriver'];
                                $vLangCode = $UserData[0]['vLang'];
                            }
                            else {
                                $UserData = get_value('register_user', 'vCurrencyPassenger,vLang', 'iUserId', $iUserId);
                                $uservSymbol = $UserData[0]['vCurrencyPassenger'];
                                $vLangCode = $UserData[0]['vLang'];
                            }
                            $userCurrencySymbol = get_value('currency', 'vSymbol', 'vName', $uservSymbol, '', 'true');
                            if ($vLangCode == "" || $vLangCode == NULL) {
                                $vLangCode = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
                            }
                            
                            $languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLangCode, "1", $iServiceId);
                            
                            $i = 0;
                            $returnData['WITHDRAWABLE_AMOUNT'] = "";
                            if (scount($Data) > 0) {
                                $row = $Data;
                                $prevbalance = 0;
                                while (scount($row) > $i) {
                                    if (!empty($row[$i]['tDescription'])) {
                                        $pat = '/\#([^\"]*?)\#/';
                                        preg_match($pat, $row[$i]['tDescription'], $tDescription_value);
                                        $tDescription_translate = $languageLabelsArr[$tDescription_value[1]];
                                        $row[$i]['tDescription'] = str_replace($tDescription_value[0], $tDescription_translate, $row[$i]['tDescription']);
                                    }
                                    // Convert Into Timezone
                                    if ($tripTimeZone != "") {
                                        $serverTimeZone = date_default_timezone_get();
                                        $row[$i]['dDate'] = converToTz($row[$i]['dDate'], $tripTimeZone, $serverTimeZone);
                                    }
                                    // Convert Into Timezone
                                    if ($row[$i]['eType'] == "Credit") {
                                        $row[$i]['currentbal'] = $prevbalance + $row[$i]['iBalance'];
                                    }
                                    else {
                                        $row[$i]['currentbal'] = $prevbalance - $row[$i]['iBalance'];
                                    }
                                    $prevbalance = $row[$i]['currentbal'];
                                    $row[$i]['dDateOrig'] = $row[$i]['dDate'];
                                    $row[$i]['dDate'] = date('d-M-Y', strtotime($row[$i]['dDate']));
                                    $row[$i]['currentbal'] = $WALLET_OBJ->MemberCurrencyWalletBalance($row[$i]['fRatio_' . $uservSymbol], $row[$i]['currentbal'], $uservSymbol);
                                    $row[$i]['iBalance'] = $WALLET_OBJ->MemberCurrencyWalletBalance($row[$i]['fRatio_' . $uservSymbol], $row[$i]['iBalance'], $uservSymbol);
                                    $i++;
                                }
                                $returnData['message'] = $row;

                                $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalanceApp($iUserId, $UserType, '', 'Yes');
                                // echo "<pre>"; print_r($user_available_balance);exit;
                                $returnData['user_available_balance_default'] = $user_available_balance['DISPLAY_AMOUNT'];
                                $returnData['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);
                                $returnData["MemberBalance"] = strval($user_available_balance['DISPLAY_AMOUNT']);
                                $returnData['user_available_balance'] = strval($user_available_balance['DISPLAY_AMOUNT']);
                                $returnData['user_available_balance_amount'] = strval($user_available_balance['ORIG_AMOUNT']);
                                $returnData['Action'] = "1";
                                if ($UserType == 'Driver') {
                                        $walletSql = "SELECT iBalance,eType,dDate,eFor FROM user_wallet WHERE iUserId = '" . $iUserId . "' AND eUserType = 'Driver'";
                                    $walletSqlData = $obj->MySQLSelect($walletSql);

                                    $totalCredit = $totalDebit = $totalReferrer = $lastRefBalance = 0;
                                    $lastRefDate = $lastDebitDate = "";
                                    foreach ($walletSqlData as $transaction) {
                                        if($transaction['eFor'] == "Referrer") 
                                        {
                                            $totalReferrer += $transaction['iBalance'];
                                            $lastRefDate = $transaction['dDate'];
                                            $lastRefBalance = $transaction['iBalance'];
                                        }

                                        if($transaction['eType'] == "Credit")
                                        {
                                            $totalCredit += $transaction['iBalance'];
                                        }
                                        elseif ($transaction['eType'] == "Debit") {
                                            $totalDebit += $transaction['iBalance'];
                                            $lastDebitDate = $transaction['date'];

                                            if($lastRefDate != "") {
                                                if(strtotime($lastRefDate) < strtotime($lastDebitDate)) {
                                                    $totalReferrer -= $lastRefBalance;
                                                    $totalCredit -= $lastRefBalance;
                                                }
                                            }
                                        }
                                    }
                                    $non_withdrawable_amount = ($totalReferrer < 0) ? 0 : $totalReferrer;
                                    $non_withdrawable_amount = number_format($non_withdrawable_amount, 2, '.', '');

                                    $withdrawable_amount = $totalCredit - ($totalDebit + $non_withdrawable_amount);
                                    $withdrawable_amount = number_format($withdrawable_amount, 2, '.', '');
                                    
                                    //$ref_deb_diff = $walletSqlData[0]['REFERRAL_AMOUNT'] - $walletSqlData[0]['DEBIT_AMOUNT'];
                                   // $non_withdrawable_amount = ($ref_deb_diff < 0) ? 0 : $ref_deb_diff;
                                   // $non_withdrawable_amount = number_format($non_withdrawable_amount, 2, '.', '');
                                   // $withdrawable_amount = $walletSqlData[0]['CREDIT_AMOUNT'] + ($walletSqlData[0]['REFERRAL_AMOUNT'] - $walletSqlData[0]['DEBIT_AMOUNT']);
                                   // $withdrawable_amount = ($ref_deb_diff > 0) ? ($withdrawable_amount - $ref_deb_diff) : $withdrawable_amount;
                                   // $withdrawable_amount = number_format($withdrawable_amount, 2, '.', '');
                                    $sqld = "SELECT rd.vCurrencyDriver as vCurrency,cu.vSymbol,cu.Ratio FROM register_driver as rd LEFT JOIN currency as cu ON rd.vCurrencyDriver = cu.vName WHERE iDriverId = '" . $iUserId . "'";
                                    $db_currency = $obj->MySQLSelect($sqld);

                                    $vCurrency = $db_currency[0]['vCurrency'];
                                    if ($vCurrency == "" || $vCurrency == null) {
                                        $sql = "SELECT vName as vCurrency,vSymbol,Ratio from currency WHERE eDefault = 'Yes'";
                                        $db_currency = $obj->MySQLSelect($sql);
                                    }
                                    $vCurrency = $db_currency[0]['vCurrency'];
                                    $vSymbol = $db_currency[0]['vSymbol'];
                                    $Ratio = $db_currency[0]['Ratio'];
                                   
                                    $returnData['WITHDRAWABLE_AMOUNT'] = formateNumAsPerCurrency(($withdrawable_amount * $Ratio),$vCurrency);
                                    $returnData['ORIG_WITHDRAWABLE_AMOUNT'] = $user_available_balance_without_sign = ($withdrawable_amount * $Ratio);
                                    $returnData['NON_WITHDRAWABLE_AMOUNT'] = formateNumAsPerCurrency(($non_withdrawable_amount * $Ratio),$vCurrency);
                                    $returnData['ORIG_NON_WITHDRAWABLE_AMOUNT'] = ($withdrawable_amount * $Ratio);
                                    $vAccountNumber = get_value('register_driver', 'vAccountNumber', 'iDriverId', $iUserId);
                                    $returnData['vAccountNumber'] = ($vAccountNumber[0]['vAccountNumber'] != "") ? 'Yes' : 'No';
                                    $returnData['ACCOUNT_NO'] = ($vAccountNumber[0]['vAccountNumber'] != "") ? $vAccountNumber[0]['vAccountNumber'] : 'XXXXXXX';
                                }
                                // setDataResponse($returnData);
                            }
                            else {
                                $returnData['Action'] = "1";
                                $returnData['message'] = "LBL_NO_TRANSACTION_AVAIL";
                                //$returnData['user_available_balance'] = $returnData['MemberBalance'] = $userCurrencySymbol . "0.00";
                                $returnData['user_available_balance'] = formateNumAsPerCurrency(0.00,$uservSymbol);
                                $returnData['WITHDRAWABLE_AMOUNT'] = formateNumAsPerCurrency(0.00,$uservSymbol);
                                $returnData['NON_WITHDRAWABLE_AMOUNT'] = formateNumAsPerCurrency(0.00,$uservSymbol);
                                
                                // setDataResponse($returnData);
                            }
                            // echo "<pre>";
                            // print_r($returnData);exit;
                            if ($UserType == 'Driver') {

                                $NoteWithdrable = str_replace(array('#drivername#','#withdrawableamount#','#nonwithdrawableamount#'),array($_SESSION['sess_vName'] . ' '. $_SESSION['sess_vLastName'],$returnData['WITHDRAWABLE_AMOUNT'],$returnData['NON_WITHDRAWABLE_AMOUNT']), $langage_lbl['LBL_DRIVER_WALLET_NOTE_TXT']);
                                // $NoteWithdrable = $_SESSION['sess_vName'] . ' '. $_SESSION['sess_vLastName'] . " have total withdrawable amount is " . $returnData['WITHDRAWABLE_AMOUNT'] . " and non withdrawable amount is ".$returnData['NON_WITHDRAWABLE_AMOUNT'];
                            } else {
                                $NoteWithdrable = '';
                            }

                            if($withdraw_request_restrict == "No") {
                                $returnData['WITHDRAWABLE_AMOUNT'] = $returnData['user_available_balance_amount'];
                                $NoteWithdrable = "";
                                $user_available_balance_without_sign = $WALLET_OBJ->FetchMemberWalletBalance($_SESSION['sess_iUserId'], $type);
                            }
                            //  echo "<pre>";
                            // print_r($_SESSION);exit;
                    // ==================================



                    if (scount($db_trip) > 0) {
                        $prevbalance = 0;
                        for ($i = 0; $i < scount($db_trip); $i++) {
                            $tDescription = $db_trip[$i]['tDescription'];
                            $iBalance = $db_trip[$i]['iBalance'] * $db_trip[$i]['fRatio_' . $_SESSION["sess_vCurrency"]];
                            $iTripId = $db_trip[$i]['iTripId'];
                            if(!empty($iTripId) && $iTripId > 0){
                                $db_result_trips = $obj->MySQLSelect("SELECT vRideNo,iTripId FROM `trips` WHERE 1=1 AND iTripId = '".$iTripId."'");
                                
                                $vRideNo =!empty($db_result_trips[0]['vRideNo']) ? $db_result_trips[0]['vRideNo'] : '';
                            }
                            if ($iTripId == "0")
                                $iTripId = "-";


                            if ($db_trip[$i]['eFor'] == "Deposit") {
                                $eFor = $langage_lbl['LBL_DEPOSIT'];
                            } else if ($db_trip[$i]['eFor'] == "Booking") {
                                $eFor = $langage_lbl['LBL_BOOKING'];
                            } else if ($db_trip[$i]['eFor'] == "Refund") {
                                $eFor = $langage_lbl['LBL_REFUND'];
                            } else if ($db_trip[$i]['eFor'] == "Withdrawl") {
                                $eFor = $langage_lbl['LBL_WITHDRAWL'];
                            } else if ($db_trip[$i]['eFor'] == "Charges") {
                                $eFor = $langage_lbl['LBL_CHARGES_TXT'];
                            } else if ($db_trip[$i]['eFor'] == "Referrer") {
                                $eFor = $langage_lbl['LBL_DEPOSIT'];
                            } else if ($db_trip[$i]['eFor'] == "Transfer") {
                                $eFor = $langage_lbl['LBL_TRANSFER'];
                            } else if ($db_trip[$i]['eFor'] == "Subscription") {
                                $eFor = $langage_lbl['LBL_SUBSCRIPTION'];
                            }

                            if ($db_trip[$i]['eType'] == "Credit") {
                                $eType = $langage_lbl['LBL_CREDIT'];
                            } else if ($db_trip[$i]['eType'] == "Debit") {
                                $eType = $langage_lbl['LBL_DEBIT'];
                            }

                            $systemTimeZone = date_default_timezone_get();
                            if ($db_trip[$i]['dDate'] != "" && $db_trip[$i]['vTimeZone'] != "") {
                                $dBookingDate = converToTz($db_trip[$i]['dDate'], $db_trip[$i]['vTimeZone'], $systemTimeZone);
                            } else {
                                $dBookingDate = $db_trip[$i]['dDate'];
                            }
                            //  if($direction_lng != ''){
                            //$dDate = date('M-d-Y',strtotime($db_trip[$i]['dDate']));
                            //}else{
                            $date_format_data_array = array(
                                'tdate' => $dBookingDate,
                                'langCode' => $vLangCode,
                                'DateFormatForWeb' => 1
                            );
                            $get_booking_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                            $dDate = $get_booking_date_format['tDisplayDate']."<br/>".$get_booking_date_format['tDisplayTime'];//DateTime1($dBookingDate, 'no');
                            // }

                            if ($db_trip[$i]['eType'] == "Credit") {
                                $db_trip[$i]['currentbal'] = $prevbalance + ($iBalance);
                            } else {
                                $db_trip[$i]['currentbal'] = $prevbalance - ($iBalance);
                            }
                            $prevbalance = $db_trip[$i]['currentbal'];
                            ?>
                                <tr class="gradeA">
                                    <td style="display: none" data-order="<?= intVal($db_trip[$i]['iUserWalletId']) ?>"><?php echo intVal($db_trip[$i]['iUserWalletId']) ?></td>
                                    <td align="right" class="center"><?=$dDate;?></td>
                                    <td align="center" data-order="<?=$db_trip[$i]['iUserWalletId']?>">
                                    
                                                                        <?php
                                            $pat = '/\#([^\"]*?)\#/';
                                            preg_match($pat, $db_trip[$i]['tDescription'], $tDescription_value);
                                            $tDescription_translate = $langage_lbl[$tDescription_value[1]];
                                            //echo $tDescription_value[0]."==============".$tDescription_translate."=============".$db_trip[$i]['tDescription']."========";
                                            $row_tDescription = str_replace($tDescription_value[0], $tDescription_translate, $db_trip[$i]['tDescription']);

                                            if($db_trip[$i]['eFor'] == "Deposit" && strpos($row_tDescription, '#TRIP_NUMBER#') !== false){
                                                $row_tDescription = str_replace('#TRIP_NUMBER#', $vRideNo, $row_tDescription);
                                            }

                                            //added by SP on 12-11-2020 for transfer description converted from label.
                                            if($db_trip[$i]['eFor'] == "Transfer") {
                                               if ( preg_match($pat, $row_tDescription, $tDescription_value_new)) {
                                                  $tDescription_translate_second = $langage_lbl[$tDescription_value_new[1]];
                                                  $row_tDescription_final = str_replace($tDescription_value_new[0], $tDescription_translate_second, $row_tDescription);
                                                  if(preg_match($pat, $row_tDescription_final, $tDescription_value_second)){
                                                       $tDescription_translate_third = $langage_lbl[$tDescription_value_second[1]];
                                                      $row_tDescription_final_new = str_replace($tDescription_value_second[0], $tDescription_translate_third, $row_tDescription_final);
                                                      echo $row_tDescription_final_new;
                                                  } else {
                                                      echo $row_tDescription_final;
                                                  }
                                               } else {
                                                  echo $row_tDescription;
                                               }
                                            } else {
                                               echo $row_tDescription;
                                            }
                                            ?>
                                    </td>
                                    <!--<td align="right" class="center"><?=$WALLET_OBJ->MemberCurrencyWalletBalanceFront(0, $iBalance, $_SESSION["sess_vCurrency"]);?></td> -->
									<td align="right" class="center"><?=$WALLET_OBJ->MemberCurrencyWalletBalanceFront(0, $iBalance, $currencyName);?></td>
                                    <!-- <td align="right" class="center"><?=$iTripId;?></td> -->
                                    <td align="right" class="center"><?=$eFor;?></td>
                                    
                                    <td align="right" class="center"><?=$eType;?></td>
                                    <!--<td class="center"><?=$final = $WALLET_OBJ->MemberCurrencyWalletBalanceFront(0, $db_trip[$i]['currentbal'], $_SESSION["sess_vCurrency"]);?>
                                    </td> -->
									<td class="center"><?=$final = $WALLET_OBJ->MemberCurrencyWalletBalanceFront(0, ($db_trip[$i]['currentbal'] < 0 ? 0 : $db_trip[$i]['currentbal']), $currencyName);?>
                                    </td> 
                                </tr>
<? } ?>
                        </tbody>
                        <tfoot>
                            <tr class="gradeA odd total_balance">
                                <td class="last_record_row" style="border-right:0px;"></td>
                                <td></td>
                                <td></td>
                                 <!-- <td></td> -->
                                <!-- <td></td> -->
                                <td rowspan="1" colspan="2" align="right" style="font-weight:bold;text-align:right;"><?=$langage_lbl['LBL_WALLET_TOTAL_BALANCE'];?></td>
                                <td rowspan="1" colspan="1" align="center" class="center"><?=$final;?></td>
                            </tr>
                        </tfoot>
<? } else { ?>
                           <!--  <tr class="odd">
                                <td class="center" align="center" colspan="7">No Details found</td>
                            </tr>    -->
                    <? } ?>
                </tbody>
        </table>
    </div>
    <?php if($_SESSION['sess_user'] == "driver") { ?>
        <div class="singlerow-login-log button-block">
            <a href="#" class="gen-btn" data-toggle="modal" data-target="#uiModal"><?=$langage_lbl['LBL_WITHDRAW_REQUEST'];?></a>
        </div>
    <?php } ?>
    </div>
</section>


<div class="col-lg-12">
        <div class="custom-modal-main" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="custom-modal">
            <div class="modal-content image-upload-1 popup-box1">
                <div class="upload-content">
                            <div class="model-header">
                                <h4><?=$langage_lbl['LBL_WITHDRAW_REQUEST'];?></h4>
                                <i class="icon-close" data-dismiss="modal"></i>
                            </div>
                    <div class="model-body">
                        <form class="form-horizontal general-form" id="frm6" method="post" enctype="multipart/form-data" name="frm6">
                            <input type="hidden" id="action" name="action" value="send_equest">
                            <input type="hidden" name="eTransRequest" id="eTransRequest" value="">
                            <input type="hidden" name="iUserId" id="iUserId" value="<?=$_SESSION['sess_iUserId'];?>">
                            <input type="hidden" name="eUserType" id="eUserType" value="<?=$type;?>">
                            <input type="hidden" name="User_Available_Balance" id="User_Available_Balance" value="<?=$user_available_balance_without_sign;?>">
                            <input type="hidden" name="User_Available_Balance_symbole" id="User_Available_Balance_symbole" value="<?=$returnData['WITHDRAWABLE_AMOUNT'];?>">

                            <div class="col-lg-13">

                                <div class="input-append">
                                
                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_PAYMENT_EMAIL_TXT'];?></label>
                                    <input type="text" name="vPaymentEmail" id="vPaymentEmail" class="form-control vHolderName"  <? if ($type == 'Driver') { ?>value="<?=$db_driver[0]['vPaymentEmail'];?>"<? } ?>>
                                </div>

                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_WALLET_ACCOUNT_HOLDER_NAME'];?></label>
                                    <input type="text" name="vHolderName" id="vHolderName" class="form-control vHolderName"  <? if ($type == 'Driver') { ?>value="<?=$db_driver[0]['vBankAccountHolderName'];?>"<? } ?>>
                                </div>

                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_WALLET_NAME_OF_BANK'];?></label>
                                    <input type="text" name="vBankName" id="vBankName" class="form-control vBankName" <? if ($type == 'Driver') { ?>value="<?=$db_driver[0]['vBankName'];?>"<? } ?>>
                                </div>

                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_WALLET_ACCOUNT_NUMBER'];?></label>
                                    <input type="text" name="iBankAccountNo" id="iBankAccountNo" class="form-control iBankAccountNo" <? if ($type == 'Driver') { ?>value="<?=$db_driver[0]['vAccountNumber'];?>"<? } ?>>
                                </div>

                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_WALLET_BIC_SWIFT_CODE'];?></label>
                                    <input type="text" name="BICSWIFTCode" id="BICSWIFTCode" class="form-control BICSWIFTCode" <? if ($type == 'Driver') { ?>value="<?=$db_driver[0]['vBIC_SWIFT_Code'];?>"<? } ?>>
                                </div>

                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_WALLET_BANK_LOCATION'];?></label>
                                    <input type="text" name="vBankBranch" id="vBankBranch" class="form-control vBankBranch" <? if ($type == 'Driver') { ?>value="<?=$db_driver[0]['vBankLocation'];?>"<? } ?>>
                                </div>

                                <div class="form-group newrow">
                                    <label><?=$langage_lbl['LBL_ENTER_AMOUNT'];?></label>
                                    <input type="text" name="fAmount" id="fAmount" class="form-control fAmount" value="">
                                </div>
                                <?php echo $NoteWithdrable; ?>
                                    <!-- <span class="input-group-addon add-on"><i class="icon-calendar"></i></span> -->
                                </div>
                            </div>
                            </div>
                            <div class="model-footer">
                                <div class="button-block">
                                    <input type="button" onClick="check_login_small_v2();" id="withdrawal_request" class="save gen-btn" name="<?=$langage_lbl['LBL_WALLET_save'];?>" value="<?=$langage_lbl['LBL_BTN_SEND_TXT'];?>">
                                    <input type="button" class="gen-btn" data-dismiss="modal" name="<?=$langage_lbl['LBL_WALLET_BTN_PROFILE_CANCEL_TRIP_TXT'];?>" value="<?=$langage_lbl['LBL_WALLET_BTN_PROFILE_CANCEL_TRIP_TXT'];?>">
                                </div>
                            </div>
                        </form>

                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- home page end-->
    <!-- footer part -->
    <?php include_once 'footer/footer_home.php';?>

    <div style="clear:both;"></div>
     <?php if ($template != 'taxishark') {?>
     </div>
     <?php }?>
    <!-- footer part end -->
<!-- Footer Script -->
<?php include_once 'top/footer_script.php';?>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>


<script type="text/javascript">
    if($('#my-trips-data').length > 0) {
        // $('#my-trips-data').DataTable({"oLanguage": langData,
        //     "order": [[0, "desc"]],
        // });
        var table = $('#my-trips-data').DataTable({"oLanguage": langData,
            "order": [[0, "desc"]],
         });
        function getRecordCount() {
            var filteredRecords = table.rows({ filter: 'applied' }).data().length;
            if (filteredRecords == 0) {
                $('.total_balance').hide();
            } else {
                $('.total_balance').show();
            }
        }
        getRecordCount();
        table.on('search.dt', function() {
            getRecordCount();
        });
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

    $(document).ready(function () {
        $(".fAmount").keydown(function (e) {

            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    // Allow: Ctrl+A
                (e.keyCode == 65 && e.ctrlKey === true) ||
                // Allow: Ctrl+C
                (e.keyCode == 67 && e.ctrlKey === true) ||
                // Allow: Ctrl+X
                        (e.keyCode == 88 && e.ctrlKey === true) ||
                        // Allow: home, end, left, right
                                (e.keyCode >= 35 && e.keyCode <= 39)) {
                    // let it happen, don't do anything
                    return;
                }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

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
        /* $('#dataTables-example').dataTable({
         "order": [[ 0, "desc" ]]
         });*/

        $('#dataTables-example').dataTable({
            fixedHeader: {
                footer: true
            },
            "oLanguage": langData,
            "order": [[0, "desc"]],
        // "order": [[3, "asc"]],
        });
        // formInit();
    });

    function todayDate()
    {
        $("#dp4").val('<?=$Today;?>');
        $("#dp5").val('<?=$Today;?>');
    }
    function reset() {
        location.reload();
    }
    function yesterdayDate()
    {
        $("#dp4").val('<?=$Yesterday;?>');
        $("#dp5").val('<?=$Yesterday;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }
    function currentweekDate(dt, df)
    {
        $("#dp4").val('<?=$monday;?>');
        $("#dp5").val('<?=$sunday;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }
    function previousweekDate(dt, df)
    {
        $("#dp4").val('<?=$Pmonday;?>');
        $("#dp5").val('<?=$Psunday;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }
    function currentmonthDate(dt, df)
    {
        $("#dp4").val('<?=$currmonthFDate;?>');
        $("#dp5").val('<?=$currmonthTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }
    function previousmonthDate(dt, df)
    {
        $("#dp4").val('<?=$prevmonthFDate;?>');
        $("#dp5").val('<?=$prevmonthTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }
    function currentyearDate(dt, df)
    {
        $("#dp4").val('<?=$curryearFDate;?>');
        $("#dp5").val('<?=$curryearTDate;?>');
        $("#dp4").datepicker('refresh');
        $("#dp5").datepicker('refresh');
    }
    function previousyearDate(dt, df)
    {
        $("#dp4").val('<?=$prevyearFDate;?>');
        $("#dp5").val('<?=$prevyearTDate;?>');
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
    function check_skills_edit() {
        y = getCheckCount(document.frmbooking);
        if (y > 0)
        {
            $("#eTransRequest").val('Yes');
            document.frmbooking.submit();
        } else {
            alert("<?php echo addslashes($langage_lbl['LBL_SELECT_RIDE_FOR_TRANSFER_MSG']); ?>")
            return false;
        }
    }

    function check_login_small_v2(){

        $('#frm6').submit();
        if(!$('#frm6').valid())
        {
            return false;
        }
    }
    function check_login_small() {


        var maxamount = document.getElementById("User_Available_Balance").value;
        var requestamount = document.getElementById("fAmount").value;
        var vHolderName = document.getElementById("vHolderName").value;
        var vBankName = document.getElementById("vBankName").value;
        var iBankAccountNo = document.getElementById("iBankAccountNo").value;
        var BICSWIFTCode = document.getElementById("BICSWIFTCode").value;
        var vBankBranch = document.getElementById("vBankBranch").value;
        var vPaymentEmail = document.getElementById("vPaymentEmail").value;
        var maxamount_symbol = document.getElementById("User_Available_Balance_symbole").value;



        if (vPaymentEmail == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_PAYMENT_EMAIL_TXT']); ?>");
            return false;
        }
        if (vHolderName == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_ACCOUNT_HOLDER_NAME_MSG']); ?>");
            return false;
        }
        if (vBankName == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_BANK_MSG']); ?>");
            return false;
        }
        if (iBankAccountNo == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_ACCOUNT_NUM_MSG']); ?>");
            return false;
        }
        if (BICSWIFTCode == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_BIC_SWIFT_CODE_MSG']); ?>");
            return false;
        }
        if (vBankBranch == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_BANK_BRANCH_MSG']); ?>");
            return false;
        }

        if (requestamount == '' || $.isNumeric(requestamount)==false) {
           if($.isNumeric(requestamount)==false) {
              $("#fAmount").val('');
           }
            alert("<?php echo addslashes($langage_lbl['LBL_WITHDRAW_AMT_MSG']); ?>");
            return false;
        }

        if (requestamount == 0) {
            alert("<?php echo addslashes($langage_lbl['LBL_WITHDRAW_AMT_ERROR']); ?>");
            return false;
        }

        if(parseFloat(requestamount) > parseFloat(maxamount)){
            // alert("Please Enter Withdraw Amount Less Than " + maxamount_symbol );
            alert("<?php echo addslashes($langage_lbl['LBL_RESTRICT_WITHDRAW_AMT_NOTE']); ?>");
            return false;
         }

        $("#eTransRequest").val('Yes');
        //document.frm6.submit();
        if (vPaymentEmail != "" && vHolderName != "" && vBankName != "" && iBankAccountNo != "" && BICSWIFTCode != "" && vBankBranch != "" && requestamount != "") {
            $("#withdrawal_request").val('Please wait ...').attr('disabled', 'disabled');

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>user_withdraw_request.php',
                'AJAX_DATA': $("#frm6").serialize(),
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    if (data == 0)
                    {
                       $('#uiModal').modal('hide');
                        var err = "<?php echo addslashes($langage_lbl['LBL_WITHDRAW_AMT_VALIDATION_MSG']); ?>";
                        bootbox.dialog({
                            message: "<h3>" + err + "</h3>",
                            buttons: {
                                danger: {
                                    label: "Ok",
                                    className: "btn-danger",
                                    callback: function () {
                                        $("#withdrawal_request").val('Send').removeAttr('disabled');
                                    }
                                },
                            }
                        });

                        return false;
                    } else if (data == 1)
                    {
                        $('#uiModal').modal('hide');
                        var err = "<?php echo addslashes($langage_lbl['LBL_WITHDRAW_AMT_SUCCESS_MSG']); ?>";
                        bootbox.dialog({
                            message: "<h3>" + err + "</h3>",
                            buttons: {
                                danger: {
                                    label: "Ok",
                                    className: "btn-danger",
                                    callback: function () {
                                        $("#withdrawal_request").val('Send').removeAttr('disabled');
                                        $('#uiModal #frm6')[0].reset();
                                    }
                                },
                            }
                        });

                        return true;
                    }
                }
                else {
                    console.log(response.result);
                }
            });
        }
    }
    function add_money_to_wallet() {
        var priceamount = document.getElementById("fAmountprice").value;
        //alert(priceamount );
        if (priceamount == '') {
            alert("<?php echo addslashes($langage_lbl['LBL_WITHDRAW_AMT_MSG']); ?>");
            return false;
        }

        document.addmoney.submit();
    }
    
    $(document).ready(function () {
        $('[data-dismiss=modal]').on('click', function (e) {
            $('#uiModal #frm6')[0].reset();
        });
        $("[name='dataTables-example_length']").each(function () {
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $("[name='dataTables-example_length']").change(function () {
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    });




    /*-----------------------------------*/

    $('#frm6').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            vPaymentEmail: {required: true},
            vHolderName: {required: true},
            vBankName: {required: true},
            iBankAccountNo: {required: true},
            BICSWIFTCode: {required: true},
            vBankBranch: {required: true},
            fAmount: {required: true},
        },
        submitHandler: function (form,event) {

            check_login_small();
            event.preventDefault();
        }
    });


    /*-----------------------------------*/


</script>

<!-- End: Footer Script -->
</body>
</html>
