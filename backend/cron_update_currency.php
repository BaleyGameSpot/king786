<?php

/*
  This api run only 2 times per a day
 */
include_once('common.php');

$script_file_cron_job = isset($_REQUEST['SCRIPT_FILE']) ? $_REQUEST['SCRIPT_FILE'] : '';
$session_cron_job = isset($_REQUEST['SESSION_CRON_JOB']) ? $_REQUEST['SESSION_CRON_JOB'] : '';
CheckCronJobSession($script_file_cron_job, $session_cron_job);

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_update_currency_status.txt", "running");
/* Cron Log Update End */



$app_id = $EXCHANGE_CURRENCY_RATES_APP_ID;
$sql = "SELECT vName FROM currency WHERE eDefault='Yes'";
$db_currency = $obj->MySQLSelect($sql);
$default_currency = $db_currency[0]['vName'];
$defaultcurrency = 'USD';

$returnArr = array();
$ch = curl_init();
$oxr_url = "https://openexchangerates.org/api/latest.json?app_id=" . $app_id . '&base=' . $defaultcurrency;

// Open CURL session:
$ch = curl_init($oxr_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// Get the data:
$json = curl_exec($ch);
curl_close($ch);


$exchangeRates = json_decode($json, TRUE);

function getDefaultCurrenyRate($default_db_currency, $exchangeRates) {
    if (isset($exchangeRates['rates']) && !empty($exchangeRates['rates']) && scount($exchangeRates['rates']) > 0) {
        if ($default_db_currency == "ZWD") {
            $default_db_currency = "ZWL";
        }
        if (isset($exchangeRates['rates'][$default_db_currency])) {
            return $exchangeRates['rates'][$default_db_currency];
        }
    }
    return 1;
}

if (isset($exchangeRates['rates']) && !empty($exchangeRates['rates']) && scount($exchangeRates['rates']) > 0) {
    $vSymbol = "";
    foreach ($exchangeRates['rates'] as $key => $data) {

        $locale = 'en-US';
        $currency = $key;

        // $fmt = new NumberFormatter( $locale."@currency=$currency", NumberFormatter::CURRENCY );
        // $vSymbol = $fmt->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
        header("Content-Type: text/html; charset=UTF-8;");

        $data = $data / getDefaultCurrenyRate($default_currency, $exchangeRates);

        $eDefault = "No";
        $ThresholdAmount = 50;
        $fThresholdAmount = $ThresholdAmount * $data;
        
        if ($key == "ZWL") {
            $key = "ZWD";
        } elseif ($key == "GHS") {
            $key = "GHC";
        } elseif ($key == "XOF") {
            $key = "CFA";
        }
        
        $sql = "select  vName FROM currency  where vName='" . $key . "'";
        $db_select_currency = $obj->MySQLSelect($sql);
        $counter = scount($db_select_currency);
        
        if ($counter > 0) {
            $notkey = $db_select_currency[0]['vName'];
			if($default_db_currency == $key){
				$data = "1";
			}
            /* if ($notkey != "USD") { */
                $Table = "currency";
                $updateData['Ratio'] = trim($data);
                $updateData['fThresholdAmount'] = trim($fThresholdAmount);

                $where = "vName='" . $key . "'";
                $res = $obj->MySQLQueryPerform($Table, $updateData, 'update', $where);

                $Ratio = trim($data);
                $subquery = "update `master_currency`  SET `Ratio` = '" . $Ratio . "' where `vName` = '" . $key . "'";
                $obj->sql_query($subquery);
            /* }else{
				
			} */
        } else {

            $query = "Insert into  master_currency (vName,vSymbol,eDefault,Ratio) values ('" . $key . "','" . $vSymbol . "','" . $eDefault . "','" . $data . "')";
            $obj->sql_query($query);
            $id = $obj->GetInsertId();
        }
    }


    $returnArr['Action'] = "1";
    $returnArr['message'] = "Record Updated successfully.";
    echo json_encode($returnArr);
} else {
    $returnArr['Action'] = "0";
    $returnArr['message'] = "Can't communicate with server. Please check your internet connection or try again later.";
    echo json_encode($returnArr);
}

/* Update Default Currency Ration for payment gateway */
$currency_data = $obj->MySQLSelect("SELECT vName, Ratio FROM currency");
$currencyArr = array();
foreach ($currency_data as $currency) {
    $currencyArr[$currency['vName']] = $currency['Ratio'];
}

$pg_currency_data = $obj->MySQLSelect("SELECT vName, vValue FROM configurations_payment WHERE vName LIKE '%DEFAULT_CURRENCY_CONVERATION%'");
$pg_currency_arr = array();
foreach ($pg_currency_data as $pg_currency) {
    $pg_name = "DEFAULT";
    if(!startsWith($pg_currency['vName'], 'DEFAULT_CURRENCY_CONVERATION')) {
        $pg_name = explode("_", $pg_currency['vName'])[0];
    }
    $pg_currency_arr[$pg_name][$pg_currency['vName']] = $pg_currency['vValue'];
}

foreach ($pg_currency_arr as $key => $pg_currency) {
    $pg_key = "DEFAULT_CURRENCY_CONVERATION_ENABLE";
    $pg_code = strtoupper($pg_currency['DEFAULT_CURRENCY_CONVERATION_CODE']);
    $pg_ratio_key = 'DEFAULT_CURRENCY_CONVERATION_CODE_RATIO';

    if($key != "DEFAULT") {
        $pg_key = $key . '_DEFAULT_CURRENCY_CONVERATION_ENABLE';
        $pg_code = strtoupper($pg_currency[$key . '_DEFAULT_CURRENCY_CONVERATION_CODE']);
        $pg_ratio_key = $key . '_DEFAULT_CURRENCY_CONVERATION_CODE_RATIO';
    }

    if(strtoupper($pg_currency[$pg_key]) == "YES") {
        $pg_ratio = $currencyArr[$pg_code];

        $obj->sql_query("UPDATE configurations_payment SET vValue = '$pg_ratio' WHERE vName = '$pg_ratio_key' ");
    }
}
$oCache->flushData();
/* Update Default Currency Ration for payment gateway */

/* Cron Log Update */
WriteToFile($tconfig['tsite_script_file_path'] . "cron_update_currency_status.txt", "executed");

$cron_logs = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_logs');
$cron_logs = json_decode($cron_logs, true); 

foreach ($cron_logs as $ckey => $cfile) 
{
    if($cfile['filename'] == "cron_update_currency.php")
    {
        $cron_logs[$ckey]['last_executed'] = date('Y-m-d H:i:s');
    }
}

WriteToFile($tconfig['tsite_script_file_path'] . "system_cron_logs", json_encode($cron_logs));
/* Cron Log Update End */
exit;
?>