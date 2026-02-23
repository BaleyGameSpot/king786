<?php
include_once('common.php');
/* 
global $obj; */

     $serviceIds = isset($_REQUEST['serviceIds'])?$_REQUEST['serviceIds']:'';
     $vehicleTypeId = implode(',',$serviceIds);
     $sql = "SELECT iVehicleCategoryId from vehicle_type WHERE iVehicleTypeId IN (".$vehicleTypeId.")";
     $dbGetData = $obj->MySQLSelect($sql);

	if(scount($dbGetData) > 0)
	{		
        $vehicleCategoryIdArray = array();
        $totalCount = scount($dbGetData);
        for($i=0;$i<$totalCount;$i++)
        {

             $db_iVehicleCategoryId   = $dbGetData[$i]['iVehicleCategoryId'];
             $iParentId = get_value('vehicle_category', 'iParentId', 'iVehicleCategoryId', $db_iVehicleCategoryId, '', 'true');
             $iVehicleCategoryId = ($iParentId !=0) ? $iParentId : $db_iVehicleCategoryId;
             array_push($vehicleCategoryIdArray,$iVehicleCategoryId);
        } 
        
        if(scount($vehicleCategoryIdArray) > 0)
        {
            $vehicleCategoryId = implode(',',$vehicleCategoryIdArray);

            $sql = "SELECT * from  document_master  WHERE  iVehicleCategoryId IN (".$vehicleCategoryId.") AND status='Active'";
            $getDocument = $obj->MySQLSelect($sql);
            $getDocumentCount = (scount($getDocument));
            
            $json_data  = array('documentCount'=>$getDocumentCount);
            echo json_encode($json_data);
            exit;
        }
	}
?>