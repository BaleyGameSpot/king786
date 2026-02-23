<?php
include_once("../common.php");

$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';

$vCountryCode = isset($_REQUEST['vCountryCode'])?$_REQUEST['vCountryCode']:'';
$userType = isset($_REQUEST['userType'])?$_REQUEST['userType']:'';
$checkusedata = isset($_REQUEST['checkusedata'])?$_REQUEST['checkusedata']:'';
//$action = isset($_REQUEST['action'])?$_REQUEST['action']:'Add';

$delsql = " AND eStatus != 'Deleted'";

$alluserdata = array();
$sql = "select concat(vName,' ',vLastName , ' (+' ,vCode, ' ' ,vPhone , ')') as DriverName,iDriverId,eDeviceType,eDebugMode from register_driver where (vEmail != '' OR vPhone != '')  AND vCountry='".$vCountryCode."' $delsql order by vName";
$db_drvlist = $obj->MySQLSelect($sql);
$db_drv_list = array();

for ($i = 0; $i < scount($db_drvlist); $i++) {
    $data = array();
    $data['DriverName'] = mb_convert_encoding(clearName(ucfirst($db_drvlist[$i]['DriverName'])), 'utf-8', 'auto');
    $data['iDriverId'] = $db_drvlist[$i]['iDriverId'];
    $data['eDeviceType'] = $db_drvlist[$i]['eDeviceType'];
    $data['eDebugMode'] = $db_drvlist[$i]['eDebugMode'];
    array_push($db_drv_list, $data);
}

$sql = "select concat(vName,' ',vLastName, ' (+' ,vPhoneCode,' ',vPhone , ')' ) as riderName,iUserId,eDeviceType from register_user where (vEmail != '' OR vName != '' OR vPhone != '') AND vCountry='".$vCountryCode."' $delsql order by vName";

$db_rdrlist = $obj->MySQLSelect($sql);
$db_rdr_list = array();
for ($ii = 0; $ii < scount($db_rdrlist); $ii++) {
    $data = array();
    $data['riderName'] = mb_convert_encoding(clearName(ucfirst($db_rdrlist[$ii]['riderName'])), 'utf-8', 'auto');
    $data['iUserId'] = $db_rdrlist[$ii]['iUserId'];
    $data['eDeviceType'] = $db_rdrlist[$ii]['eDeviceType'];
    array_push($db_rdr_list, $data);
}

$sql_drv = "select concat(vName,' ',vLastName , ' (+' ,vCode, ' ' ,vPhone , ')' ) as DriverName,iDriverId,eDeviceType from register_driver where `eLogout` = 'No' AND (vEmail != '' OR vPhone != '') AND vCountry='".$vCountryCode."' $delsql order by vName";
$db_login_drvlist = $obj->MySQLSelect($sql_drv);
$db_login_drv_list = array();
for ($iii = 0; $iii < scount($db_login_drvlist); $iii++) {
    $data = array();
    $data['DriverName'] = mb_convert_encoding(clearName(ucfirst($db_login_drvlist[$iii]['DriverName'])), 'utf-8', 'auto');
    $data['iDriverId'] = $db_login_drvlist[$iii]['iDriverId'];
    $data['eDeviceType'] = $db_login_drvlist[$iii]['eDeviceType'];
    array_push($db_login_drv_list, $data);
}

$sql_rdr = "select concat(vName,' ',vLastName , ' (+' ,vPhoneCode,' ',vPhone , ')' ) as riderName,iUserId,eDeviceType from register_user where `eLogout` = 'No'  AND (vEmail != '' OR vPhone != '') AND vCountry='".$vCountryCode."' $delsql order by vName";
$db_login_rdrlist = $obj->MySQLSelect($sql_rdr);
$db_login_rdr_list = array();
for ($iv = 0; $iv < scount($db_login_rdrlist); $iv++) {
    $data = array();
    $data['riderName'] = mb_convert_encoding(clearName(ucfirst($db_login_rdrlist[$iv]['riderName'])), 'utf-8', 'auto');
    $data['iUserId'] = $db_login_rdrlist[$iv]['iUserId'];
    $data['eDeviceType'] = $db_login_rdrlist[$iv]['eDeviceType'];
    array_push($db_login_rdr_list, $data);
}

$sql_inactive_drv = "select concat(vName,' ',vLastName, ' (+' ,vCode, ' ' ,vPhone , ')' ) as DriverName,iDriverId,eDeviceType from register_driver where eStatus = 'Inactive' AND (vEmail != '' OR vPhone != '') AND vCountry='".$vCountryCode."' order by vName";
$db_inactive_drvlist = $obj->MySQLSelect($sql_inactive_drv);

$db_inactive_drv_list = array();
for ($v = 0; $v < scount($db_inactive_drvlist); $v++) {
    $data = array();
    $data['DriverName'] = mb_convert_encoding(clearName(ucfirst($db_inactive_drvlist[$v]['DriverName'])), 'utf-8', 'auto');
    $data['iDriverId'] = $db_inactive_drvlist[$v]['iDriverId'];
    $data['eDeviceType'] = $db_inactive_drvlist[$v]['eDeviceType'];
    array_push($db_inactive_drv_list, $data);
}

$sql_inactive_rdr = "select concat(vName,' ',vLastName, ' (+' ,vPhoneCode,' ',vPhone , ')' ) as riderName,iUserId,eDeviceType from register_user where eStatus = 'Inactive' AND (vEmail != '' OR vPhone != '') AND vCountry='".$vCountryCode."' order by vName";
$db_inactive_rdrlist = $obj->MySQLSelect($sql_inactive_rdr);
$db_inactive_rdr_list = array();
for ($vi = 0; $vi < scount($db_inactive_rdrlist); $vi++) {
    $data = array();
    $data['riderName'] = mb_convert_encoding(clearName(ucfirst($db_inactive_rdrlist[$vi]['riderName'])), 'utf-8', 'auto');
    $data['iUserId'] = $db_inactive_rdrlist[$vi]['iUserId'];
    $data['eDeviceType'] = $db_inactive_rdrlist[$vi]['eDeviceType'];
    array_push($db_inactive_rdr_list, $data);
}

$sql = "SELECT concat(vCompany, ' (+' ,vCode,' ',vPhone , ')' ) AS CompnayName, c.iCompanyId, c.vCompany,c.eDeviceType FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE  c.eStatus = 'Active' AND sc.eStatus='Active' AND  c.iServiceId > 0  AND c.vCountry='".$vCountryCode."' order by c.vCompany";
$db_storelist = $obj->MySQLSelect($sql);
$db_store_list = array();
for ($vii = 0; $vii < scount($db_storelist); $vii++) {
    $data = array();
    $data['vCompany'] = mb_convert_encoding(clearCmpName(ucfirst($db_storelist[$vii]['CompnayName'])), 'utf-8', 'auto');
    $data['iCompanyId'] = $db_storelist[$vii]['iCompanyId'];
    $data['eDeviceType'] = $db_storelist[$vii]['eDeviceType'];
    array_push($db_store_list, $data);
}

$sql = "SELECT concat(vCompany, ' (+' ,vCode,' ',vPhone , ')' ) AS CompnayName, c.iCompanyId, c.vCompany,c.eDeviceType FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE  c.eStatus = 'Active' AND sc.eStatus='Active' AND c.eLogout = 'No'AND  c.iServiceId>0  AND c.vCountry='".$vCountryCode."' order by c.vCompany";
$db_login_rstlist = $obj->MySQLSelect($sql);
$db_login_rst_list = array();
for ($ix = 0; $ix < scount($db_login_rstlist); $ix++) {
    $data = array();
    $data['vCompany'] = mb_convert_encoding(clearCmpName(ucfirst($db_login_rstlist[$ix]['CompnayName'])), 'utf-8', 'auto');
    $data['iCompanyId'] = $db_login_rstlist[$ix]['iCompanyId'];
    $data['eDeviceType'] = $db_login_rstlist[$ix]['eDeviceType'];
    array_push($db_login_rst_list, $data);
}

$sql = "SELECT concat(vCompany, ' (+' ,vCode,' ',vPhone , ')' ) AS CompnayName, c.iCompanyId, c.vCompany,c.eDeviceType FROM company AS c  left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE  c.eStatus = 'Inactive' AND sc.eStatus='Active' AND  c.eStatus = 'Inactive' AND  c.iServiceId>0  AND c.vCountry='".$vCountryCode."' order by c.vCompany";
$db_inactive_rstlist = $obj->MySQLSelect($sql);
$db_inactive_rst_list = array();
for ($x = 0; $x < scount($db_inactive_rstlist); $x++) {
    $data = array();
    $data['vCompany'] = mb_convert_encoding(clearCmpName(ucfirst($db_inactive_rstlist[$x]['CompnayName'])), 'utf-8', 'auto');
    $data['iCompanyId'] = $db_inactive_rstlist[$x]['iCompanyId'];
    $data['eDeviceType'] = $db_inactive_rstlist[$x]['eDeviceType'];
    array_push($db_inactive_rst_list, $data);
}

if($checkusedata != 'Yes'){
	if($userType == 'driver'){

		$alluserdata['driverlist'] = $db_drv_list;

	} else if ($userType == 'rider'){

		$alluserdata['userlist'] = $db_rdr_list;

	} else if ($userType == 'logged_driver'){

		$alluserdata['loggedindriverlist'] = $db_login_drv_list;

	} else if ($userType == 'logged_rider'){

		$alluserdata['loggedinriderlist'] = $db_login_rdr_list;

	} else if ($userType == 'inactive_driver'){

		$alluserdata['inactivedriverlist'] = $db_inactive_drv_list;

	} else if ($userType == 'inactive_rider'){ 

		$alluserdata['inactiveuserlist'] = $db_inactive_rdr_list;

	} else if ($userType == 'store'){

	    $alluserdata['storelist'] = $db_store_list;

	} else if ($userType == 'logged_store'){

	     $alluserdata['loginstorelist'] = $db_login_rst_list;

	} else if ($userType == 'inactive_store'){

	    $alluserdata['inactivestorelist'] = $db_inactive_rst_list;

	}
	//returns data as JSON format
	echo json_encode($alluserdata,JSON_UNESCAPED_UNICODE);
	exit;
}
?>
<?

if($checkusedata == 'Yes'){
	//if(empty($db_drv_list) && empty($db_rdr_list) && empty($db_login_drv_list) && empty($db_login_rdr_list) && empty($db_inactive_drv_list) && empty($db_inactive_rdr_list) && empty($db_store_list) && empty($db_login_rst_list) && empty($db_inactive_rst_list)){
		echo '<option value="">Select Type</option>';
	//} 
	if (!empty($db_drv_list) && $onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') { 
		echo '<option value="driver">All '.$langage_lbl_admin["LBL_DRIVERS_NAME_ADMIN"].'</option>';
	} 
	if (!empty($db_rdr_list)) { 
		echo '<option value="rider">All '.$langage_lbl_admin["LBL_RIDERS_ADMIN"].'</option>';
	}
	if (!empty($db_login_drv_list) && $onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') {
		echo '<option value="logged_driver">All Logged in '.$langage_lbl_admin["LBL_DRIVERS_NAME_ADMIN"].'</option>';
	} 
	if (!empty($db_login_rdr_list)) { 
		echo '<option value="logged_rider">All Logged in '.$langage_lbl_admin["LBL_RIDERS_ADMIN"].'</option>';
	} 
	if (!empty($db_inactive_drv_list) && $onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') { 
		echo '<option value="inactive_driver">All Inactive '.$langage_lbl_admin["LBL_DRIVERS_NAME_ADMIN"].'</option>';
	} 
	if (!empty($db_inactive_rdr_list)) {
		echo '<option value="inactive_rider">All Inactive '.$langage_lbl_admin["LBL_RIDERS_ADMIN"].'</option>';
	} 
	if (DELIVERALL == 'Yes') { 
	 	if (!empty($db_store_list)) { 
		    echo '<option value="store">All '.$langage_lbl_admin["LBL_RESTAURANT_TXT_ADMIN"].'</option>';
		} 
		if (!empty($db_login_rst_list)) { 
		    echo '<option value="logged_store">All Logged in '.$langage_lbl_admin["LBL_RESTAURANT_TXT_ADMIN"].'</option>';
		} 
		if (!empty($db_inactive_rst_list)) {
		    echo '<option value="inactive_store">All Inactive '.$langage_lbl_admin["LBL_RESTAURANT_TXT_ADMIN"].'</option>';
		} 
	}
	exit;
}
?>