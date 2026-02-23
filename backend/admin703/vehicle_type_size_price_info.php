<?php
include_once('../common.php');

if(!function_exists('updateSizeInfoData')) {
    function updateSizeInfoData($iVehicleTypeId = 0) {
        global $obj, $iDriverVehicleId;

        $iVehicleSizePriceInfo = isset($_POST['iVehicleSizeId']) ? $_POST['iVehicleSizeId'] : '';
        $iVehicleTypeSizePriceInfo = isset($_POST['iVehicleTypeIds']) ? $_POST['iVehicleTypeIds'] : '';

        if(isset($iVehicleSizePriceInfo) && !empty($iVehicleSizePriceInfo)) {
            $sql = "SELECT iVehicleSizeId,iVehicleTypeId FROM vehicle_size_price_info as vspi WHERE vspi.iVehicleTypeid = '".$iVehicleTypeId."' ";
            $vehicle_size_price_info = $obj->MySQLSelect($sql);
            $vehicle_size_price_arr = [];
            foreach($vehicle_size_price_info as $vehicle_size_price) {
                $vehicle_size_price_arr[] =$vehicle_size_price['iVehicleTypeId'].'_'.$vehicle_size_price['iVehicleSizeId'];
            }

            foreach($iVehicleSizePriceInfo as $key => $SizePrice) {

                $vehicle_size_price = $iVehicleTypeId.'_'.$key;
                if(in_array($vehicle_size_price,$vehicle_size_price_arr)) {
                    $data_update = [];
                    $where = " iVehicleSizeId = '" . $key . "'  AND iVehicleTypeId = '".$iVehicleTypeId."' ";
                    if(isset($SizePrice['fPricePerHour'])) {
                        $data_update['fPricePerHour'] = $SizePrice['fPricePerHour'];
                    }

                    if(isset($SizePrice['fMinHour'])) {
                        $data_update['fMinHour'] = $SizePrice['fMinHour'];
                    }

                    if(isset($SizePrice['fFixedFare'])) {
                        $data_update['fFixedFare'] = $SizePrice['fFixedFare'];
                    }

                    if(isset($SizePrice['fPricePerKM']))
                    {
                        $data_update['fPricePerKM'] = $SizePrice['fPricePerKM'];
                    }

                    if(isset($SizePrice['fPricePerMin']))
                    {
                        $data_update['fPricePerMin'] = $SizePrice['fPricePerMin'];
                    }

                    if(isset($SizePrice['iMinFare']))
                    {
                        $data_update['iMinFare'] = $SizePrice['iMinFare'];
                    }

                    if(isset($SizePrice['iBaseFare']))
                    {
                        $data_update['iBaseFare'] = $SizePrice['iBaseFare'];
                    }
                    $obj->MySQLQueryPerform("vehicle_size_price_info", $data_update, 'update', $where);

                }else{

                    $Data_Insert = [];
                    if(isset($SizePrice['fPricePerHour']))
                    {
                        $Data_Insert['fPricePerHour'] = $SizePrice['fPricePerHour'];
                    }

                    if(isset($SizePrice['fMinHour']))
                    {
                        $Data_Insert['fMinHour'] = $SizePrice['fMinHour'];
                    }

                    if(isset($SizePrice['fFixedFare']))
                    {
                        $Data_Insert['fFixedFare'] = $SizePrice['fFixedFare'];
                    }

                    if(isset($SizePrice['fPricePerKM']))
                    {
                        $Data_Insert['fPricePerKM'] = $SizePrice['fPricePerKM'];
                    }

                    if(isset($SizePrice['fPricePerMin']))
                    {
                        $Data_Insert['fPricePerMin'] = $SizePrice['fPricePerMin'];
                    }

                    if(isset($SizePrice['iMinFare']))
                    {
                        $Data_Insert['iMinFare'] = $SizePrice['iMinFare'];
                    }

                    if(isset($SizePrice['iBaseFare']))
                    {
                        $Data_Insert['iBaseFare'] = $SizePrice['iBaseFare'];
                    }

                    $Data_Insert['iVehicleSizeId'] = $key;
                    $Data_Insert['iVehicleTypeId'] = $iVehicleTypeId;

                    $obj->MySQLQueryPerform("vehicle_size_price_info", $Data_Insert, 'insert');
                }
            }
        }

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
    $vLanguage = $default_lang;
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
        $FIXED_FIELD['FIELD_NAME'] = 'Price Per KMs (Price In USD)';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','fPricePerKM');
        $FIXED_FIELD['FIELD_KEY'] = 'fPricePerKM';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Price Per Min (Price In USD)';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','fPricePerMin');
        $FIXED_FIELD['FIELD_KEY'] = 'fPricePerMin';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Minimum Fare (Price In USD) ';
        $FIXED_FIELD['FIELD_VALUE'] = array('amount','iMinFare');
        $FIXED_FIELD['FIELD_KEY'] = 'iMinFare';
        $FIXED_FIELD['FIELD_TYPE'] = 'Input';
        $TABLE_ARRAY['FIELD'][] = $FIXED_FIELD;
        $FIXED_FIELD = [];
        $FIXED_FIELD['FIELD_NAME'] = 'Base Fare (Price In USD)';
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

<table class="table table-striped table-bordered table-hover" id="dataTables-example">
    <thead>
    <?php if (isset($TABLE_ARRAY['FIELD']) && !empty($TABLE_ARRAY['FIELD'])) { ?>
        <tr>
            <?php foreach ($TABLE_ARRAY['FIELD'] as $key => $FIELD) { ?>
                <th><?php echo $FIELD['FIELD_NAME'] ?></th>
            <?php } ?>
        </tr>
    <?php } ?>
    </thead>
    <tbody>
    <?php if (isset($TABLE_ARRAY['VALUE']) && !empty($TABLE_ARRAY['VALUE'])) { ?>
        <?php foreach ($TABLE_ARRAY['VALUE'] as $key => $VALUE) { ?>
            <tr>
                <?php
                foreach ($VALUE['fieldData'] as $key => $v) { ?>
                    <td>
                        <?php if ($v['type'] == "Input") {
                            if(isset($iDriverVehicleId)) { ?>
                            <input class="form-control" name="<?php echo 'iVehicleTypeIds[' . $iVehicleTypeId . ']['.$VALUE['iVehicleSizeId'].']['.$v['key'].']'; ?>" id="" type="text" value='<?php echo $v['value']; ?>' required />
                            <?php } else { ?>

                                    <div style="width: 300px">
                            <input required class="form-control" name="<?php echo 'iVehicleSizeId['.$VALUE['iVehicleSizeId'].']['.$v['key'].']'; ?>" id="" type="text" value='<?php echo $v['value']; ?>' />

                                    </div>


                        <?php }
                        } else if ($v['type'] == "Select"){ ?>

                            <select name="<?php echo 'iVehicleSizeId['.$VALUE['iVehicleSizeId'].']['.$v['key'].']'; ?>" id="eFareType" class="form-control">
                                <option value="Regular" selected="">Regular</option>
                                <option value="Fixed" selected="">Fixed</option>
                                <option value="Hourly" selected="">Hourly</option>
                            </select>
                        <?php } else{ ?>
                            <?php echo $v['value']; ?>
                        <?php } ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    <?php } ?>
    </tbody>
</table>
<?php } ?>