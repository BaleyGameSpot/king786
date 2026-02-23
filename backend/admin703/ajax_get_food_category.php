<?php
include_once('../common.php');

$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$iFoodMenuId = isset($_REQUEST['iFoodMenuId']) ? $_REQUEST['iFoodMenuId'] : '';
$iServiceId = isset($_REQUEST['iServiceId']) ? $_REQUEST['iServiceId'] : '';

$ssql = "";
if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
	$ssql = " AND fm.iServiceId = '$iServiceId'";
}
$sql = "SELECT fm.iFoodMenuId,fm.vMenu_".$default_lang." as menuTitle,c.vCompany,c.iCompanyId FROM  food_menu AS fm LEFT JOIN `company` as c on c.iCompanyId=fm.iCompanyId WHERE fm.iCompanyId='".$iCompanyId."' AND fm.eStatus != 'Deleted' $ssql";
$db_menu = $obj->MySQLSelect($sql);

echo "<option value=''>--select--</option>";
if (scount($db_menu) > 0) {
	for($i=0;$i<scount($db_menu);$i++){
		$selected='';					
		if($db_menu[$i]['iFoodMenuId'] == $iFoodMenuId){
			$selected = "selected=selected";						
		}
		echo "<option value=".$db_menu[$i]['iFoodMenuId']." ".$selected.">".clearName($db_menu[$i]['menuTitle'])."</option>";	
	}
	 exit;
}
?>