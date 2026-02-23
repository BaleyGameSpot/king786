<?php
include_once('../common.php');

$iServiceId = isset($_REQUEST['iServiceIdNew']) ? $_REQUEST['iServiceIdNew'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';

$ssql = " FIND_IN_SET('".$iServiceId."', c.iServiceId)";
if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
     $ssql = " (FIND_IN_SET('".$iServiceId."', c.iServiceId) OR (FIND_IN_SET('".$iServiceId."', c.iServiceIdMulti) AND c.iServiceIdMulti != '')) ";
}
if(!empty($iServiceId)){
	$ssql1 = " AND c.eBuyAnyService = 'No' ";
	$sql = "SELECT c.iCompanyId,c.vCompany,c.iServiceId,c.vEmail FROM `company` AS c LEFT JOIN food_menu AS f ON c.iCompanyId = f.iCompanyId WHERE $ssql AND  c.eStatus!='Deleted' $ssql1 GROUP BY c.iCompanyId ORDER BY `vCompany`";
	 //echo $sql; exit;
	$db_company = $obj->MySQLSelect($sql);
	echo "<option value=''>Select ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']."</option>";
	if (scount($db_company) > 0) {
		for($i=0;$i<scount($db_company);$i++){
			$selected='';					
			if($db_company[$i]['iCompanyId'] == $iCompanyId){
				$selected = "selected=selected";

			}
			if($db_company[$i]['vEmail'] != ''){
				echo "<option value=".$db_company[$i]['iCompanyId']." ".$selected.">".clearName($db_company[$i]['vCompany']) ." - ( ". clearEmail($db_company[$i]['vEmail']).") </option>";
			} else {
				echo "<option value=".$db_company[$i]['iCompanyId']." ".$selected.">".clearName($db_company[$i]['vCompany'])." </option>";
			}
		}
		exit;
	}
} else {
	echo "<option value=''>Select ".$langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']."</option>";exit;
}		
?>
