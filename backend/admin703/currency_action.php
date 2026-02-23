<?
include_once('../common.php');

unset($_POST['dataTables-example_length']);
unset($_POST['submit']);
$ratio = $_REQUEST['Ratio'];
$thresholdamount = $_REQUEST['fThresholdAmount'];
$vSymbol = $_REQUEST['vSymbol'];
$iCurrencyId = $_REQUEST['iCurrencyId'];
$eDefault = $_REQUEST['eDefault'];
$eStatus = $_REQUEST['eStatus'];
$iDispOrder = $_REQUEST['iDispOrder'];
$eRoundingOffEnable = $_REQUEST['eRoundingOffEnable'];
$eReverseformattingEnable = $_REQUEST['eReverseformattingEnable'];
$eReverseSymbolEnable = $_REQUEST['eReverseSymbolEnable'];
$oCache->flushData();


$sql = "select * from currency WHERE 1 order by iCurrencyId";
$db_sq = $obj->MySQLSelect($sql);

/*------------------check currency status change-----------------*/

function updateMemberCurrency($changeTheCurrency)
{
    global $obj,$vSystemDefaultCurrencyName;
    $dataUpdateCurrency=$dataUpdateCurrency_d = [];
    $quotedCurrencies = array_map(function($currency) {
        return "'$currency'";
    }, $changeTheCurrency);

    $changeTheCurrency =  implode(',',$quotedCurrencies);
    $where = " vCurrencyPassenger IN (" . $changeTheCurrency . ")";
    $dataUpdateCurrency['vCurrencyPassenger'] = $vSystemDefaultCurrencyName;
    $dataUpdateCurrency['tSessionId'] = '';
    $obj->MySQLQueryPerform("register_user", $dataUpdateCurrency, 'update', $where);

    $where = " vCurrencyDriver IN (" . $changeTheCurrency . ")";
    $dataUpdateCurrency_d['vCurrencyDriver'] = $vSystemDefaultCurrencyName;
    $dataUpdateCurrency_d['tSessionId'] = '';
    $obj->MySQLQueryPerform("register_driver", $dataUpdateCurrency_d, 'update', $where);
}
$dbCurrency = [];
$dbNewCurrency = [];
$changeTheCurrency = [];
if(isset($db_sq) && !empty($db_sq))
{
    foreach ($db_sq as $c){
        $dbCurrency[$c['iCurrencyId']] = $c['vName'];
    }
    $ci = 0;
    foreach($iCurrencyId as $key => $c){
        if($eStatus[$key] == 'Inactive')
        {
            $changeTheCurrency[] = $dbCurrency[$c];
        }
    }
}
/*------------------check currency status change-----------------*/
if (SITE_TYPE == 'Demo'){
    header("location:currency.php?success=2");
    exit;
}
else{
    for ($i = 0;$i < scount($db_sq);$i++){
        $name = $db_sq[$i]["vName"];
        $j = 0;
        $str = "UPDATE currency SET ";
        foreach ($db_sq as $arr){
            if ($eRoundingOffEnable[$iCurrencyId[$i]] == 'on'){
                $eRoundingOffEnable1[$i] = 'Yes';
                $fMiddleRangeValue[$i] = '0.5';
                $fFirstRangeValue[$i] = '0';
                $fSecRangeValue[$i] = '1';
            }else{
                $eRoundingOffEnable1[$i] = 'No';
                $fMiddleRangeValue[$i] = '0';
                $fFirstRangeValue[$i] = '0';
                $fSecRangeValue[$i] = '0';
            }
            if ($eReverseformattingEnable[$iCurrencyId[$i]] == 'on'){
                $eReverseformattingEnable1[$i] = 'Yes';
            }else{
                $eReverseformattingEnable1[$i] = 'No';
            }
            if ($eReverseSymbolEnable[$iCurrencyId[$i]] == 'on'){
                $eReverseSymbolEnable1[$i] = 'Yes';
            }else{
                $eReverseSymbolEnable1[$i] = 'No';
            }
            $str .= "vSymbol"."='".$vSymbol[$i]."',";
            $str .= "Ratio"."='".$ratio[$i]."',";
            $str .= "eRoundingOffEnable"."='".$eRoundingOffEnable1[$i]."',";
            $str .= "eReverseformattingEnable"."='".$eReverseformattingEnable1[$i]."',";
            $str .= "eReverseSymbolEnable"."='".$eReverseSymbolEnable1[$i]."',";
            $str .= "fMiddleRangeValue"."='".$fMiddleRangeValue[$i]."',";
            $str .= "fFirstRangeValue"."='".$fFirstRangeValue[$i]."',";
            $str .= "fSecRangeValue"."='".$fSecRangeValue[$i]."',";
            $str .= "fThresholdAmount"."='".$thresholdamount[$i]."',";
            // $str .= "iDispOrder" . "='" . $iDispOrder[$i] . "',";
            $str .= "eStatus"."='".$eStatus[$i]."',";
        }
        $str = substr_replace($str," ",-1);
        $id = $db_sq[$i]['iCurrencyId'];
        $str .= "where iCurrencyId=".$iCurrencyId[$i];
        $db_update = $obj->sql_query($str);

    }

    if(isset($changeTheCurrency) && !empty($changeTheCurrency)){
        updateMemberCurrency($changeTheCurrency);
    }
    $query = "UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query);
    $query1 = "UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query1);
    $query1 = "UPDATE company SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query1);
    if (!empty($OPTIMIZE_DATA_OBJ)){
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
    }
    updateSystemData();
    $siteUrl = $tconfig['tsite_url']."".SITE_ADMIN_URL."/currency.php?success=1&reload";
    ?>
    <script>window.location.replace("<?php echo $siteUrl; ?>"); </script>
<?php } ?>