<?php
include_once('../common.php');

$iCompanyId=isset($_REQUEST['iCompanyId'])?$_REQUEST['iCompanyId']:'';
$iOrganizationId=isset($_REQUEST['iOrganizationId'])?$_REQUEST['iOrganizationId']:'';
$iAdminId=isset($_REQUEST['iAdminId'])?$_REQUEST['iAdminId']:'';
$iDriverId=isset($_REQUEST['iDriverId'])?$_REQUEST['iDriverId']:'';
$iUserId=isset($_REQUEST['iUserId'])?$_REQUEST['iUserId']:'';
$usertype = isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : '';
$iGroupId = isset($_REQUEST['iGroupId']) ? $_REQUEST['iGroupId'] : '';
if($iCompanyId !='') {
	$ssql=" AND iCompanyId !='".$iCompanyId."'";
}else if($iOrganizationId !='') {
	$ssql=" AND iOrganizationId !='".$iOrganizationId."'";
}else if($iAdminId != "") {
	$ssql=" AND iAdminId !='".$iAdminId."'";
}else if($iDriverId != "") {
	$ssql=" AND iDriverId !='".$iDriverId."'";
}else if($iUserId != "" && $usertype != 'TrackingUser') {
	$ssql=" AND iUserId !='".$iUserId."'";
}else if($iUserId != "" && $usertype == 'TrackingUser') {
	$ssql=" AND iTrackServiceUserId  !='".$iUserId."'";
}else {
	$ssql=" ";
}
	
if(isset($_REQUEST['iAdminId']) && isset($_REQUEST['vEmail']))
{
	$email=$_REQUEST['vEmail'];
	if($iGroupId != "4"){
		$Sql1 = ' AND iGroupId != "4"';
	}
	$sql1 = 'SELECT count(vEmail) as Total,eStatus FROM administrators WHERE vEmail = "'.$email.'" '. $Sql1 . $ssql;
	$db_adm = $obj->MySQLSelect($sql1);
	
	if($db_adm[0]['Total'] > 0) {
		if((ucfirst($db_adm[0]['eStatus'])=='Deleted') || (ucfirst($db_adm[0]['eStatus'])=='Inactive')){ 
			echo 'deleted';
		} else {
			echo 'false';
		}
	} else {
		echo 'true';
	}
}


/*Use For Organization Module */

	if(isset($_REQUEST['iOrganizationId']) && isset($_REQUEST['vEmail']))
	{
		$email=$_REQUEST['vEmail'];
		
		$sql1 = "SELECT count('vEmail') as Total,eStatus FROM organization WHERE vEmail = '".$email."'".$ssql;
		$db_comp = $obj->MySQLSelect($sql1);
		
		if($db_comp[0]['Total'] > 0) {
			if((ucfirst($db_comp[0]['eStatus'])=='Deleted')  || (ucfirst($db_comp[0]['eStatus'])=='Inactive')){ 
				echo 'deleted';
			} else {
				echo 'false';
			}
		} else {
			echo 'true';
		}
	}

/*Use For Organization Module */

if(isset($_REQUEST['iCompanyId']) && isset($_REQUEST['vEmail']))
{
	$email=$_REQUEST['vEmail'];
	
	$sql1 = "SELECT count('vEmail') as Total,eStatus FROM company WHERE vEmail = '".$email."'".$ssql;
	$db_comp = $obj->MySQLSelect($sql1);
	
	if($db_comp[0]['Total'] > 0) {
		if((ucfirst($db_comp[0]['eStatus'])=='Deleted')  || (ucfirst($db_comp[0]['eStatus'])=='Inactive')){ 
			echo 'deleted';
		} else {
			echo 'false';
		}
	} else {
		echo 'true';
	}
}
	
if(isset($_REQUEST['iDriverId']) && isset($_REQUEST['vEmail']))
{
	$email=$_REQUEST['vEmail'];
	
	/*$sql1 = "SELECT count('vEmail') as Total,eStatus FROM administrators WHERE vEmail = '".$email."'";
	$db_adm = $obj->MySQLSelect($sql1);*/
	
	$sql2 = "SELECT count('vEmail') as Total,eStatus FROM register_driver WHERE vEmail = '".$email."'".$ssql;
	$db_driver = $obj->MySQLSelect($sql2);
	
	/*$sql2 = "SELECT count('vEmail') as Total,eStatus FROM company WHERE vEmail = '".$email."'";
	$db_comp = $obj->MySQLSelect($sql2);*/
	//if($db_adm[0]['Total'] > 0 || $db_driver[0]['Total'] > 0 || $db_comp[0]['Total'] > 0)
	if($db_driver[0]['Total'] > 0) {
		if((ucfirst($db_driver[0]['eStatus'])=='Deleted') || (ucfirst($db_driver[0]['eStatus'])=='Inactive')){ 
			echo 'deleted';
		} else {
			echo 'false';
		}
	} else {
		echo 'true';
	}
}
	
if(isset($_REQUEST['iUserId']) && isset($_REQUEST['vEmail']) && $usertype != 'TrackingUser')
{
	$email=$_REQUEST['vEmail'];
	
	/*$sql1 = "SELECT count('vEmail') as Total,eStatus FROM administrators WHERE vEmail = '".$email."'";
	$db_adm = $obj->MySQLSelect($sql1);*/
	
	$sql2 = "SELECT count('vEmail') as Total,eStatus FROM register_user WHERE vEmail = '".$email."'".$ssql;
	$db_user = $obj->MySQLSelect($sql2);
	
	/*$sql2 = "SELECT count('vEmail') as Total,eStatus FROM company WHERE vEmail = '".$email."'";
	$db_comp = $obj->MySQLSelect($sql2);*/
	//if($db_adm[0]['Total'] > 0 || $db_user[0]['Total'] > 0 || $db_comp[0]['Total'] > 0) 
	if($db_user[0]['Total'] > 0) {
		if((ucfirst($db_user[0]['eStatus'])=='Deleted') || (ucfirst($db_user[0]['eStatus'])=='Inactive')){ 
			echo 'deleted';
		} else {
			echo 'false';
		}
	} else {
		echo 'true';
	}
}

if(isset($_REQUEST['iUserId']) && isset($_REQUEST['vEmail']) && $usertype == 'TrackingUser')
{
	$email=$_REQUEST['vEmail'];
	
	$sql2 = "SELECT count('vEmail') as Total,eStatus FROM track_service_users WHERE vEmail = '".$email."'".$ssql;
	$db_user = $obj->MySQLSelect($sql2);

	if($db_user[0]['Total'] > 0) {
		if((ucfirst($db_user[0]['eStatus'])=='Deleted') || (ucfirst($db_user[0]['eStatus'])=='Inactive')){ 
			echo 'deleted';
		} else {
			echo 'false';
		}
	} else {
		echo 'true';
	}
}
?>