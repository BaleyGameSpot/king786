<?php
include_once('common.php');
$tbl_name = 'register_driver';
$script = "payment_request";
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'admin,driver,company,rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$vHolderName = (isset($_REQUEST['vHolderName1']) ? $_REQUEST['vHolderName1'] : '');
$vBankName = (isset($_REQUEST['vBankName1']) ? $_REQUEST['vBankName1'] : '');
$iBankAccountNo = (isset($_REQUEST['iBankAccountNo1']) ? $_REQUEST['iBankAccountNo1'] : '');
$BICSWIFTCode = (isset($_REQUEST['BICSWIFTCode1']) ? $_REQUEST['BICSWIFTCode1'] : '');
$vBankBranch = (isset($_REQUEST['vBankBranch1']) ? $_REQUEST['vBankBranch1'] : '');

if ($_SESSION['sess_user'] == "driver") {
    $sql = "SELECT * FROM register_" . $_SESSION['sess_user'] . " WHERE iDriverId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);
    
    $csql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyDriver'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($csql);
}

if ($_SESSION['sess_user'] == "rider") {
    $sql = "SELECT * FROM register_user WHERE iUserId='" . $_SESSION['sess_iUserId'] . "'";
    $db_booking = $obj->MySQLSelect($sql);

    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='" . $db_booking[0]['vCurrencyPassenger'] . "'";
    $db_curr_ratio = $obj->MySQLSelect($sql);

}

$tripcursymbol = $db_curr_ratio[0]['vSymbol'];
$tripcur = $db_curr_ratio[0]['Ratio'];
$tripcurname = $db_curr_ratio[0]['vName'];
$tripcurthholsamt = $db_curr_ratio[0]['fThresholdAmount'];

$sql = "select vName, Ratio from currency where eDefault = 'Yes'";
$db_currency_admin = $obj->MySQLSelect($sql);
$sql_fThresholdAmount_default = "SELECT fThresholdAmount FROM currency WHERE eDefault='Yes'";
$currency_fThresholdAmount_default = $obj->MySQLSelect($sql_fThresholdAmount_default)[0]['fThresholdAmount'];
if ($action == "send_equest") {
    $iTripId = $_REQUEST['iTripId'];
    if (is_array($iTripId)) {
        $iTripId = implode(",", $iTripId);
    }
    $sql = "SELECT * From trips WHERE iTripId IN($iTripId)";
    $db_dtrip = $obj->MySQLSelect($sql);
    $tot_records = scount($db_dtrip);
    $payout_limit = 0;
    if (scount($db_dtrip) > 0) {
        for ($i = 0; $i < scount($db_dtrip); $i++) {
            $fare = trip_currency_payment($db_dtrip[$i]['fTripGenerateFare'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
            $comission = trip_currency_payment($db_dtrip[$i]['fCommision'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
            //added by SP on 24-08-2020 for checking condition with the same amount which is shown in total of field 'payment' start
            $tax = $db_dtrip[$i]['fTax1'] + $db_dtrip[$i]['fTax2'];
            $totTax = trip_currency_payment($tax, $db_dtrip[$i]['fRatio_' . $tripcurname]);
            $fOutStandingAmount = trip_currency_payment($db_dtrip[$i]['fOutStandingAmount'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
            $hotel_commision = trip_currency_payment($db_dtrip[$i]['fHotelCommision'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
            $fTipPrice = trip_currency_payment($db_dtrip[$i]['fTipPrice'], $db_dtrip[$i]['fRatio_' . $tripcurname]);
            $payment = $fare - $comission - $totTax - $fOutStandingAmount - $hotel_commision + $fTipPrice;
            $payment_admin = $db_dtrip[$i]['fTripGenerateFare'] - $db_dtrip[$i]['fCommision'] - $tax - $db_dtrip[$i]['fOutStandingAmount'] - $db_dtrip[$i]['fHotelCommision'] + $db_dtrip[$i]['fTipPrice'];
            //added by SP on 24-08-2020 for checking condition with the same amount which is shown in total of field 'payment' end
            $total += $payment;
            $total_admin += $payment_admin;
            if ($tot_records == ($i + 1)) {
                $seperator = "";
            } else {
                $seperator = ",";
            }
            $maildata['TripIds'] .= $db_dtrip[$i]['vRideNo'] . "" . $seperator;
        }
    }
    $currency_fThresholdAmount_default = number_format($tripcurthholsamt, 2, '.', ''); // Added By NM for resolve mantis

    if ($total > $currency_fThresholdAmount_default) {
        $data = array('ePayment_request' => 'Yes');
        $where = " iTripId IN (" . $iTripId . ")";
        $res = $obj->MySQLQueryPerform("trips", $data, 'update', $where);
        if ($res) {
            #echo "<script>alert('Request Send Successfully');document.location='payment_request.php'; </script>";
            $maildata['Name'] = $db_booking[0]['vName'] . ' ' . $db_booking[0]['vLastName'];
            $maildata['vEmail'] = $db_booking[0]['vEmail'];
            $maildata['Total_Amount'] = formateNumAsPerCurrency($total, $tripcurname);
            $maildata['Account_Name'] = $vHolderName;
            $maildata['Bank_Name'] = $vBankName;
            $maildata['Account_Number'] = $iBankAccountNo;
            $maildata['BIC/SWIFT_Code'] = $BICSWIFTCode;
            $maildata['Bank_Branch'] = $vBankBranch;
            //added by SP on 17-03-2021 to save data of payment requests
            $datainsert = array();
            //$datainsert['vTripIds'] = $iTripId;
            $datainsert['vRideNo'] = $maildata['TripIds'];
            $datainsert['iDriverId'] = $_SESSION['sess_iUserId'];
            $datainsert['vName'] = $db_booking[0]['vName'];
            $datainsert['vLastName'] = $db_booking[0]['vLastName'];
            $datainsert['vEmail'] = $db_booking[0]['vEmail'];
            $datainsert['vCode'] = $db_booking[0]['vCode'];
            $datainsert['vPhone'] = $db_booking[0]['vPhone'];
            $datainsert['fAmount'] = $total_admin;
            $datainsert['vCurrency'] = $db_currency_admin[0]['vName'];
            $datainsert['vBankAccountHolderName'] = $vHolderName;
            $datainsert['vBankName'] = $vBankName;
            $datainsert['vAccountNumber	'] = $iBankAccountNo;
            $datainsert['vBIC_SWIFT_Code'] = $BICSWIFTCode;
            $datainsert['vBankLocation'] = $vBankBranch;
            $datainsert["tRequestDate"] = @date("Y-m-d H:i:s");
            $insid = $obj->MySQLQueryPerform("payment_requests", $datainsert, 'insert');
            //to send email
            $COMM_MEDIA_OBJ->SendMailToMember("PAYMENT_REQUEST_ADMIN", $maildata);
            $_SESSION['message_code'] = '1';
            $_SESSION['session_msg'] = $langage_lbl['LBL_SEND_REQUEST_SUCCESSFULLY'];
            header("Location:payment_request.php");
            //header("Location:payment_request.php?success=1&var_msg=" . $langage_lbl['LBL_SEND_REQUEST_SUCCESSFULLY'] . "");
            exit;
        }
    } else {
        $var_msg = $langage_lbl['LBL_THRESHOLDAMOUNT_NOTE2'] . " " . formateNumAsPerCurrency($tripcurthholsamt, $tripcurname);
        $_SESSION['message_code'] = '3';
        $_SESSION['session_msg'] = $var_msg;
        header("Location:payment_request.php");
        //header("Location:payment_request.php?success=0&var_msg=" . $var_msg . "");
        exit;
    }
}
if ($action = "send_equest_for_ride_share") {
    $iBookingId = $_REQUEST['iBookingId'];
    if (is_array($iBookingId)) {
        $iBookingId = implode(",", $iBookingId);
    }
    $sql = "SELECT fBookingFee,vBookingNo, fTotal,ePaymentOption,fWalletDebit From ride_share_bookings WHERE iBookingId IN($iBookingId)";
    $db_Booking = $obj->MySQLSelect($sql);

    $tot_records = scount($db_Booking);

    if (scount($tot_records) > 0) {
        $i = $fBookingFee = 0;
        foreach ($db_Booking as $tot_record) {

            if($tot_record['ePaymentOption'] == 'Cash' && $tot_record['fWalletDebit'] > 0 ){
                $fBookingFee += $tot_record['fWalletDebit'];
            }else {
                $fBookingFee += ($tot_record['fTotal'] - $tot_record['fBookingFee']);
            }
            $i++;
            if ($tot_records == $i) {
                $seperator = "";
            } else {
                $seperator = ",";
            }
            $maildata['vBookingNo'] .= $tot_record['vBookingNo'] . "" . $seperator;

        }
    }
    $currency_fThresholdAmount_default = number_format($tripcurthholsamt, 2, '.', '');
   
    if ($fBookingFee > $currency_fThresholdAmount_default) {

        $data = array('ePayment_request' => 'Yes');
        $where = " iBookingId IN (" . $iBookingId . ")";
        $res = $obj->MySQLQueryPerform("ride_share_bookings", $data, 'update', $where);
        if ($res) {
            #echo "<script>alert('Request Send Successfully');document.location='payment_request.php'; </script>";
            $maildata['Name'] = $db_booking[0]['vName'] . ' ' . $db_booking[0]['vLastName'];
            $maildata['vEmail'] = $db_booking[0]['vEmail'];

            if(empty($maildata['vEmail']))
            {
                $maildata['vEmail'] = "-";
            }

            $maildata['Total_Amount'] = formateNumAsPerCurrency($fBookingFee, $tripcurname);
            $maildata['Account_Name'] = $vHolderName;
            $maildata['Bank_Name'] = $vBankName;
            $maildata['Account_Number'] = $iBankAccountNo;
            $maildata['BIC/SWIFT_Code'] = $BICSWIFTCode;
            $maildata['Bank_Branch'] = $vBankBranch;
            //added by SP on 17-03-2021 to save data of payment requests
            $datainsert = array();
            //$datainsert['vTripIds'] = $iTripId;
            $datainsert['vBookingNo'] = $maildata['vBookingNo'];
            $datainsert['iUserId'] = $_SESSION['sess_iUserId'];
            $datainsert['vName'] = $db_booking[0]['vName'];
            $datainsert['vLastName'] = $db_booking[0]['vLastName'];
            $datainsert['vEmail'] = $db_booking[0]['vEmail'];
            $datainsert['vCode'] = $db_booking[0]['vPhoneCode'];
            $datainsert['vPhone'] = $db_booking[0]['vPhone'];
            $datainsert['fAmount'] = $fBookingFee;
            $datainsert['vCurrency'] = $db_currency_admin[0]['vName'];
            $datainsert['vBankAccountHolderName'] = $vHolderName;
            $datainsert['vBankName'] = $vBankName;
            $datainsert['vAccountNumber	'] = $iBankAccountNo;
            $datainsert['vBIC_SWIFT_Code'] = $BICSWIFTCode;
            $datainsert['vBankLocation'] = $vBankBranch;
            $datainsert["tRequestDate"] = @date("Y-m-d H:i:s");
            $insid = $obj->MySQLQueryPerform("payment_requests", $datainsert, 'insert');

            $COMM_MEDIA_OBJ->SendMailToMember("PAYMENT_REQUEST_FOR_RIDE_SHARE_ADMIN", $maildata);
            $_SESSION['message_code'] = '1';
            $_SESSION['session_msg'] = $langage_lbl['LBL_SEND_REQUEST_SUCCESSFULLY'];
            header("Location:ride_share_payment_request.php?success=1&var_msg=" . $langage_lbl['LBL_SEND_REQUEST_SUCCESSFULLY'] . "");
        }
    } else {
        $var_msg = $langage_lbl['LBL_THRESHOLDAMOUNT_NOTE2'] . " " . formateNumAsPerCurrency($tripcurthholsamt, $tripcurname);
        $_SESSION['message_code'] = '0';
        $_SESSION['session_msg'] = $var_msg;
       // header("Location:ride_share_payment_request.php?success=0&var_msg=" . $var_msg . "");
        header("Location:ride_share_payment_request.php");
        exit;
    }
}
?>