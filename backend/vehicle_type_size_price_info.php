<?php
if(!function_exists('updateSizeInfoData')) {
    function updateSizeInfoData() {
        global $obj, $iDriverVehicleId;

        $iVehicleTypeSizePriceInfo = isset($_POST['iVehicleTypeIds']) ? $_POST['iVehicleTypeIds'] : '';

        if(!empty($iVehicleTypeSizePriceInfo)) {
            $vehicle_size_price_info_prov = $obj->MySQLSelect("SELECT * FROM service_pro_amount WHERE iDriverVehicleId = '$iDriverVehicleId'");

            $VEHICLE_SIZE_PRICE_PRO_ARR = [];
            foreach ($vehicle_size_price_info_prov as $sizeInfo){
                $VEHICLE_SIZE_PRICE_PRO_ARR[$sizeInfo['iVehicleTypeId']][$sizeInfo['iVehicleSizeId']] = $sizeInfo;
            } 

            foreach ($iVehicleTypeSizePriceInfo as $iVehicleTypeId => $iVehicleSizeData) {
                foreach ($iVehicleSizeData as $iVehicleSizeId => $priceInfo) {
                    $Data_update = array();
                    $Data_update['fAmount'] = $priceInfo['fAmount'];                    

                    if(isset($VEHICLE_SIZE_PRICE_PRO_ARR[$iVehicleTypeId][$iVehicleSizeId])) {
                        $where = " iDriverVehicleId = '$iDriverVehicleId' AND iVehicleTypeId = '$iVehicleTypeId' AND iVehicleSizeId = '$iVehicleSizeId'";
                        $obj->MySQLQueryPerform("service_pro_amount", $Data_update, "update", $where);
                    } else {
                        $Data_update['iDriverVehicleId'] = $iDriverVehicleId;
                        $Data_update['iVehicleTypeId'] = $iVehicleTypeId;                        
                        $Data_update['iVehicleSizeId'] = $iVehicleSizeId;
                        $obj->MySQLQueryPerform("service_pro_amount", $Data_update, "insert");
                    }
                }
            }
        }
    }
}

if(!function_exists('GetCar')) {
    function GetCar($cars,$parents) {
        global $iDriverVehicleId;

        foreach ($parents as $key){
            if(isset($iDriverVehicleId)) {
                if(isset($cars[$key])) {
                    $cars = $cars[$key];
                }    
            } else {
                $cars = $cars[$key];
            }          
        }

        if(is_array($cars)) {
            $cars = "";
        }
        return $cars;
    }
}

/*------------------Request Prams-----------------*/
$eFareType = isset($_POST['eFareType']) ? $_POST['eFareType'] : '';
$iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';
$data_action = isset($_POST['data_action']) ? $_POST['data_action'] : '';

if ($data_action == "GET") {
    if(empty($db_currency[0]['vName'])) {
        $db_currency = $obj->MySQLSelect("SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'");
    }

    $vehicle_size_price_info = $obj->MySQLSelect("SELECT * FROM vehicle_size_price_info WHERE iVehicleTypeid = '$iVehicleTypeId' ");    

    $VEHICLE_SIZE_PRICE_ARR = [];
    foreach ($vehicle_size_price_info as $sizeInfo){
        $VEHICLE_SIZE_PRICE_ARR[$sizeInfo['iVehicleSizeId']] = $sizeInfo;
    }

    $vehicle_size_price_info_prov = $obj->MySQLSelect("SELECT * FROM service_pro_amount WHERE iVehicleTypeid = '$iVehicleTypeId' AND iDriverVehicleId = '$iDriverVehicleId'"); 

    $VEHICLE_SIZE_PRICE_PRO_ARR = [];
    foreach ($vehicle_size_price_info_prov as $sizeInfo){
        $VEHICLE_SIZE_PRICE_PRO_ARR[$sizeInfo['iVehicleSizeId']] = $sizeInfo;
    }

    /*------------------get vehicle_size_price_info-----------------*/
    /*------------------get vehicle_size_info-----------------*/
    $vLanguage = $_SESSION['sess_lang'];
    $sql = "SELECT JSON_UNQUOTE(JSON_VALUE(vSizeName, '$.vSizeName_".$vLanguage."')) as vSizeName , iVehicleSizeId FROM vehicle_size_info as vsi 
                WHERE vsi.eStatus = 'Active' ORDER BY iDisplayOrder ";
    $vehicle_size_info = $obj->MySQLSelect($sql);
    $VEHICLE_SIZE_ARR = [];
    foreach ($vehicle_size_info as $sizeInfo){
        $sizeInfo['amount'] = $VEHICLE_SIZE_PRICE_ARR[$sizeInfo['iVehicleSizeId']];
        if(isset($iDriverVehicleId) && isset($VEHICLE_SIZE_PRICE_PRO_ARR[$sizeInfo['iVehicleSizeId']])) {
            $sizeInfo['amount'] = $VEHICLE_SIZE_PRICE_PRO_ARR[$sizeInfo['iVehicleSizeId']];
        }
        $VEHICLE_SIZE_ARR[] = $sizeInfo;
    }

    /*------------------get vehicle_size_info-----------------*/
    $TABLE_ARRAY = [];
    if ($eFareType == "Fixed"){

        /*------------------set Field-----------------*/
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Vehicle Size Name';
        $FIXED_FIELD['FIELD_VALUE'] = array('vSizeName');
        $FIXED_FIELD['FIELD_KEY'] = 'vSizeName';
        $FIXED_FIELD['FIELD_TYPE'] = 'Display';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;

        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Service Charge - Fixed (Price In ' . $db_currency[0]['vName'] . ')';
        if(isset($iDriverVehicleId)) {        
            $FIXED_FIELD['FIELD_KEY'] = 'fAmount';
            $FIXED_FIELD['FIELD_VALUE'] = array('amount','fAmount','fFixedFare');
        } else {
            $FIXED_FIELD['FIELD_KEY'] = 'fFixedFare';
            $FIXED_FIELD['FIELD_VALUE'] = array('amount','fFixedFare');
        }
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        /*------------------set Field-----------------*/
    } else if ($eFareType == "Hourly"){

        /*------------------set Field-----------------*/
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Vehicle Size Name';
        $FIXED_FIELD['FIELD_VALUE'] = array('vSizeName');
        $FIXED_FIELD['FIELD_KEY'] = 'vSizeName';
        $FIXED_FIELD['FIELD_TYPE'] = 'Display';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;

        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Service Charge Per Hour (Price In ' . $db_currency[0]['vName'] . ')';
        
        if(isset($iDriverVehicleId)) {
            $FIXED_FIELD['FIELD_KEY'] = 'fAmount';
            $FIXED_FIELD['FIELD_VALUE'] = array('amount','fAmount','fPricePerHour');
        } else {
            $FIXED_FIELD['FIELD_KEY'] = 'fPricePerHour';
            $FIXED_FIELD['FIELD_VALUE'] = array('amount','fPricePerHour');
        }
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;

        if(!isset($iDriverVehicleId)) {
            $FIXED_FIELD = [];
            $FIXED_FIELD['FIELD_NAME'] = 'Minimum Hour';
            $FIXED_FIELD['FIELD_VALUE'] = array('amount','fMinHour');
            $FIXED_FIELD['FIELD_KEY'] = 'fMinHour';
            $FIXED_FIELD['FIELD_TYPE'] = 'Input';
            $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        }
        
        /*------------------set Field-----------------*/
    } else if ($eFareType == "Regular"){

        /*------------------set Field-----------------*/
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Vehicle Size Name';
        $FIXED_FIELD['FIELD_VALUE'] = array('vSizeName');
        $FIXED_FIELD['FIELD_KEY'] = 'vSizeName';
        $FIXED_FIELD['FIELD_TYPE'] = 'Display';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;

        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Price Per KMs (Price In ' . $db_currency[0]['vName'] . ')';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','fPricePerKM');
        $FIXED_FIELD['FIELD_KEY'] = 'fPricePerKM';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Price Per Min (Price In ' . $db_currency[0]['vName'] . ')';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','fPricePerMin');
        $FIXED_FIELD['FIELD_KEY'] = 'fPricePerMin';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Minimum Fare (Price In ' . $db_currency[0]['vName'] . ')';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','iMinFare');
        $FIXED_FIELD['FIELD_KEY'] = 'iMinFare';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Base Fare (Price In ' . $db_currency[0]['vName'] . ')';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','iBaseFare');
        $FIXED_FIELD['FIELD_KEY'] = 'iBaseFare';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        /*------------------set Field-----------------*/
    }


    $TABLE_ARRAY['VALUE'] = [];
    if (isset($VEHICLE_SIZE_ARR) && !empty($VEHICLE_SIZE_ARR)){

        foreach ($VEHICLE_SIZE_ARR as $DATA){

            $arr1 = [];
            foreach ($TABLE_ARRAY['FIELD'] as $FIELD){
                $arr = [];
                $arr['key'] = $FIELD['FIELD_KEY'];
                $arr['value'] = GetCar($DATA,$FIELD['FIELD_VALUE']);
                $arr['type'] = $FIELD['FIELD_TYPE'];
                $arr1[] = $arr;
            }
            $DATA['fieldData'] = $arr1;
            $TABLE_ARRAY['VALUE'][] = $DATA;
        }
    }
?>

<ul>
    <?php foreach ($TABLE_ARRAY['VALUE'] as $key => $VALUE) { ?>
        <li>
            <?php foreach ($VALUE['fieldData'] as $key => $v) { ?>
                <?php if ($v['type'] == "Input") { ?>
                <div class="price-input">
                    <span><?= $vSymbol; ?></span>
                    <input class="form-control" name="<?php echo 'iVehicleTypeIds[' . $iVehicleTypeId . ']['.$VALUE['iVehicleSizeId'].']['.$v['key'].']'; ?>" id="" type="text" value='<?php echo $v['value']; ?>' required />
                    <span><?= $eFareType == "Fixed" ? $langage_lbl['LBL_FARE_TYPE_FIXED_TXT'] : $langage_lbl['LBL_FARE_TYPE_HOURLY_TXT']; ?></span>
                </div>
                <?php } else{ ?>
                <div class="price-title"><?php echo $v['value']; ?></div>
                <?php } ?>            
            <?php } ?>
        </li>
    <?php } ?>
</ul>
<?php } ?>
