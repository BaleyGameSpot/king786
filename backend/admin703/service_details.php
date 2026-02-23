<?php
include_once '../common.php';
$getDeliveryDetails = isset($_REQUEST['getDeliveryDetails']) ? $_REQUEST['getDeliveryDetails'] : 0;
$iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : 0;
$iCabBookingId = isset($_REQUEST['iCabBookingId']) ? $_REQUEST['iCabBookingId'] : 0;
if($getDeliveryDetails == 1)
{
    $db_reci_data = FetchDeliveryRecepientDetails($iTripId, '', '', '','',$iCabBookingId);
    echo json_encode($db_reci_data);
    exit;
}

$getServiceDetails = isset($_REQUEST['getServiceDetails']) ? $_REQUEST['getServiceDetails'] : 0;

if($getServiceDetails == 1)
{
    if(isset($iTripId) && !empty($iTripId) && $iTripId != 0){

        $sql = "SELECT iVehicleTypeId,isVideoCall,iVehicleSizeId,tVehicleTypeData,vRideNo as rideNo FROM `trips` WHERE 1=1 AND iTripId = '".$iTripId."' ";
        $Service_details = $obj->MySQLSelect($sql);
    }

    if(isset($iCabBookingId) && !empty($iCabBookingId) && $iCabBookingId != 0){

        $sql = "SELECT iVehicleTypeId,isVideoCall,iVehicleSizeId,tVehicleTypeData,vBookingNo as rideNo FROM `cab_booking` WHERE 1=1 AND iCabBookingId = '".$iCabBookingId."' ";
        $Service_details = $obj->MySQLSelect($sql);
    }
    echo json_encode($Service_details[0]);
    exit;
}



/*------------------Get parent Category -----------------*/


$sql_vehicle_category_table_name = getVehicleCategoryTblName();


$getVehicleCategory = $obj->MySQLSelect("SELECT iVehicleCategoryId,vCategory_" . $default_lang . " as vCategory, iParentId FROM $sql_vehicle_category_table_name WHERE 1=1 ");

$getVehicleCategoryArr = [];
array_filter($getVehicleCategory, function ($data){
    global $getVehicleCategoryArr;
    $getVehicleCategoryArr[$data['iVehicleCategoryId']] = $data;
});


$VehicleCategoryArr = [];
array_filter($getVehicleCategory, function ($data){
    global $VehicleCategoryArr,$getVehicleCategoryArr;
    $data['vCategory_video_1'] = $getVehicleCategoryArr[$data['iParentId']]['vCategory'] ?? null;
    $data['vCategory_video_2'] = $data['vCategory'];

    if (isset($getVehicleCategoryArr[$data['iParentId']]['vCategory'])) {
        $data['vCategory'] = $getVehicleCategoryArr[$data['iParentId']]['vCategory'] . ' - ' . $data['vCategory'];
    } else {
        $data['vCategory'] = $data['vCategory']; // Or handle it in a different way, like assigning a default value.
    }

    if(isset($getVehicleCategoryArr[$data['iParentId']]['vCategory']) && !empty($getVehicleCategoryArr[$data['iParentId']]['vCategory'])){
        $VehicleCategoryArr[$data['iVehicleCategoryId']] = $data;
    }
});

/*------------------Get parent Category -----------------*/

/*------------------car Size-----------------*/
$SizeText = '';
$vehicleSizeIdInfoData = [];
if ($MODULES_OBJ->isEnableCarSizeServiceTypeAmount())
{
    $vehicleSizeIdInfoData = allVehicleSizeIdInfo($default_lang,'iVehicleSizeId');
}
/*------------------car Size-----------------*/



$vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT  iVehicleCategoryId,iVehicleTypeId,vVehicleType_" . $default_lang . " AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0; $r < scount($getVehicleTypes); $r++) {
   // $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r];
}

?>


<style>
    .modal {
        text-align: center;
    }

    @media screen and (min-width: 768px) {
        .modal:before {
            display: inline-block;
            vertical-align: middle;
            content: " ";
            height: 100%;
        }
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
    }

    .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        margin-right: 0;
        overflow-x: hidden;
    }

    .modal-body .form-group:last-child {
        margin-bottom: 0
    }

    @media (min-width: 992px) {
        .modal-lg {
            width: 900px;
        }
    }

    .modal-header h4 {
        font-weight: 600;
        font-size: 18px;
    }
</style>


<div class="modal fade" id="services_modal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4 id="service_title"></h4>
                <button type="button" class="close" data-dismiss="modal">x</button>
            </div>
            <div class="modal-body">
                <div id="services_detail"></div>
            </div>
            <div class="modal-footer" style="text-align: left">
            </div>
        </div>
    </div>
</div>

<div class="row loding-action" id="loaderIcon1" style="display:none;">
    <div align="center">
        <img src="default.gif">

    </div>
</div>


<script>

    var typeArr = '<?= getJsonFromAnArr($vehilceTypeArr); ?>';
    var VehicleCategoryArr = '<?= getJsonFromAnArr($VehicleCategoryArr); ?>';
    var vehicleSizeIdInfoData = '<?= getJsonFromAnArr($vehicleSizeIdInfoData); ?>';

    var typeNameArr = JSON.parse(typeArr);
    var VehicleCategoryArr = JSON.parse(VehicleCategoryArr);
    var vehicleSizeIdInfoData = JSON.parse(vehicleSizeIdInfoData);


    function showServiceModalV2(tripJson,rideNo,iVehicleSizeId,isVideoCall,iVehicleTypeId) {


        console.log(tripJson);
        var rideNo = rideNo;
        var serviceHtml = "";


        if(isVideoCall == "Yes")
        {
            iVehicleTypeId = tripJson[0]['iVehicleTypeId'];
            if(VehicleCategoryArr[iVehicleTypeId]) {
                var srno = 1;
                serviceHtml += "<p><b>" + VehicleCategoryArr[iVehicleTypeId]['vCategory_video_1'] + "</b> (Video Consulting)</p>";
                serviceHtml += "<p>" + srno + ") " + VehicleCategoryArr[iVehicleTypeId]['vCategory_video_2'] + "</p>";
                srno++;
            }
        }else{

            var srno = 1;
            var tripJsonArr = [];
            for (var g = 0; g < tripJson.length; g++) {
                var typeNameArrP = typeNameArr[tripJson[g]['iVehicleTypeId']];
                if (!tripJsonArr[typeNameArrP['iVehicleCategoryId']]) {
                    tripJsonArr[typeNameArrP['iVehicleCategoryId']] = [];
                }
                tripJsonArr[typeNameArrP['iVehicleCategoryId']].push(tripJson[g]);
            }
            if (vehicleSizeIdInfoData[iVehicleSizeId] && vehicleSizeIdInfoData[iVehicleSizeId]['vSizeName']) {
                serviceHtml += "<b> Vehicle Size: </b> <span>" + vehicleSizeIdInfoData[iVehicleSizeId]['vSizeName'] + "</span><br> <br>";
            }
            for (i in tripJsonArr) {
                var key = i
                var value = tripJsonArr[i];
                serviceHtml += "<p><b>" + VehicleCategoryArr[key]['vCategory'] + "</b></p>";
                for (var g = 0; g < value.length; g++) {
                    serviceHtml += "<p>" + srno + ") " + typeNameArr[value[g]['iVehicleTypeId']]['vehicleType'] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?=$langage_lbl_admin['LBL_QTY_TXT']?>: <b>" + [tripJson[g]['fVehicleTypeQty']] + "</b></p>";
                    srno++;
                }
            }
        }
        $("#services_detail").html(serviceHtml);
        $("#service_title").text("Job Details of Booking #" + rideNo);
        $("#services_modal").modal('show');
        $("#loaderIcon1").hide();
    }
    function getServiceDetails(iTripId,iCabBookingId,callback)
    {
        $("#loaderIcon1").show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>service_details.php',
            'AJAX_DATA': {'iTripId': iTripId, 'iCabBookingId': iCabBookingId , 'getServiceDetails' : 1 },
        };
        getDataFromAjaxCall(ajaxData, function (response)
        {
            if (response.action == "1")
            {
                var data = response.result;
                var json_data = JSON.parse(data);
                var rideNo = json_data.rideNo;
                var iVehicleSizeId = json_data.iVehicleSizeId;
                var isVideoCall = json_data.isVideoCall;
                var iVehicleTypeId = json_data.iVehicleTypeId;

                if(json_data.tVehicleTypeData)
                {
                    tVehicleTypeData = JSON.parse(json_data.tVehicleTypeData)
                    window[callback](tVehicleTypeData,rideNo,iVehicleSizeId,isVideoCall,iVehicleTypeId);
                }

            }
        });

    }


</script>