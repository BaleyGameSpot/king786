<?php
	include_once('../common.php');

	$iFoodMenuId = isset($_POST['iFoodMenuId']) ? $_POST['iFoodMenuId'] : '0';
	$iMenuItemId = isset($_POST['iMenuItemId']) ? $_POST['iMenuItemId'] : '0';
	$vSKU = isset($_POST['vSKU']) ? $_POST['vSKU'] : '';
	$ssql="";
	if(!empty($iMenuItemId)){
		$ssql=  " AND iMenuItemId != '".$iMenuItemId."'";
	}
	if(isset($_REQUEST['iFoodMenuId']))
	{
        $sql1 = "SELECT fm.iFoodMenuId,count('vSKU') as Total FROM menu_items as mi JOIN food_menu as fm ON (fm.iFoodMenuId = mi.iFoodMenuId) WHERE vSKU LIKE '".$vSKU."' {$ssql} AND fm.iCompanyId = (SELECT iCompanyId FROM food_menu WHERE iFoodMenuId = '".$iFoodMenuId."' )";
        //$sql1 = "SELECT count('vSKU') as Total FROM menu_items WHERE vSKU LIKE '".$vSKU."' $ssql";
		$db_item = $obj->MySQLSelect($sql1);
		
		if($db_item[0]['Total'] > 0 ) {
			echo 'false';
		} else {
			echo 'true';
		}
	}
?>