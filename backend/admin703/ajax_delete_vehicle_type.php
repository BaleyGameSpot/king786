<?php
include_once("../common.php");




$iVehicleTypeId = isset($_REQUEST['id'])?$_REQUEST['id']:'';
if($iVehicleTypeId != '')
{
	$sql = "select vCarType from driver_vehicle";  
	$db_model = $obj->MySQLSelect($sql); 
	
	$store = array();
	for($i=0;$i<scount($db_model);$i++){
		$abc= explode(",", $db_model[$i]['vCarType']);
		$flag = true;
		if(in_array($iVehicleTypeId,$abc)){		
            $flag = true;
            echo $flag;
		}else{
            $flag = false; 
            echo $flag;
            exit;
		}
	}
}
?>