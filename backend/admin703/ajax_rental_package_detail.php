<?php
include_once("../common.php");

$iRentalPackageId = isset($_REQUEST['iRentalPackageId'])?$_REQUEST['iRentalPackageId']:''; 
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId'])?$_REQUEST['iVehicleTypeId']:''; 
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$fPrice = isset($_POST['fPrice']) ? $_POST['fPrice'] : '';
$fKiloMeter = isset($_POST['fKiloMeter']) ? $_POST['fKiloMeter'] : '';
$fHour = isset($_POST['fHour']) ? $_POST['fHour'] : '';
$fPricePerKM = isset($_POST['fPricePerKM']) ? $_POST['fPricePerKM'] : '';
$fPricePerHour = isset($_POST['fPricePerHour']) ? $_POST['fPricePerHour'] : '';

$tbl_name= 'rental_package';
$sql = "SELECT * FROM " . $tbl_name . " WHERE iRentalPackageId = '" . $iRentalPackageId . "' AND iVehicleTypeId='".$iVehicleTypeId."'";
$db_data = $obj->MySQLSelect($sql);

if(scount($db_data) > 0){
	echo json_encode($db_data[0]); exit;
}else{
	echo 0; exit;
}

	
	



 

 

       

  

       
         