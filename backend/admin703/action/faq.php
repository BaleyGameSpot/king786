<?php
include_once('../../common.php');

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iFaqId = isset($_REQUEST['iFaqId']) ? $_REQUEST['iFaqId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';

$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//echo '<pre>'; print_r($_REQUEST); echo '</pre>'; die;
//Start faqs deleted
if ($method == 'delete' && $iFaqId != '') {

	if(!$userObj->hasPermission('delete-faq')) {
				$_SESSION['success'] = 3;
				$_SESSION['var_msg'] = 'You do not have permission to delete FAQ';   
	}else{
		if(SITE_TYPE !='Demo'){
			$data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM faqs";
			$data_rec = $obj->MySQLSelect($data_q);
	
			$order = isset($data_rec[0]['iDisplayOrder']) ? $data_rec[0]['iDisplayOrder'] : 0;
	
			$data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM faqs WHERE iFaqId = '" . $iFaqId . "'");
	
			if (scount($data_logo) > 0) {
				$iDisplayOrder = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
				$obj->sql_query("DELETE FROM faqs WHERE iFaqId = '" . $iFaqId . "'");
				
				if ($iDisplayOrder < $order) {
					for ($i = $iDisplayOrder + 1; $i <= $order; $i++) {
						$obj->sql_query("UPDATE faqs SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i);
					}
				}
			}
			// $query = "UPDATE faqs SET eStatus = 'Deleted' WHERE iFaqId = '" . $iFaqId . "'";
			//$query = "DELETE FROM faqs WHERE iFaqId = '" . $iFaqId . "'";
			//$obj->sql_query($query);
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];   
		}
		else{
			$_SESSION['success'] = '2';
		}
	}
	if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('SetFaqs');
    }
    updateSystemData();

	header("Location:".$tconfig["tsite_url_main_admin"]."faq.php?".$parameters); exit;
}
//End faqs deleted

//Start Change single Status
if ($iFaqId != '' && $status != '') {
	if(!$userObj->hasPermission('update-status-faq')) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to change status of FAQ';   
	}else{
		if(SITE_TYPE !='Demo'){
			$query = "UPDATE faqs SET eStatus = '" . $status . "' WHERE iFaqId = '" . $iFaqId . "'";
			$obj->sql_query($query);
			$_SESSION['success'] = '1';
			if($status == 'Active') {
				$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
			}else {
				$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
			}
		}
		else{
			$_SESSION['success']=2;
		}
	}
	if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('SetFaqs');
    }
    updateSystemData();

	header("Location:".$tconfig["tsite_url_main_admin"]."faq.php?".$parameters);
	exit;
}
//End Change single Status

//Start Change All Deleted Selected Status
//echo '<pre>'; print_r($_REQUEST); echo '</pre>'; die;

if ($checkbox != ''  && $statusVal == 'Deleted') { 
	if(!$userObj->hasPermission('delete-faq')) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to delete FAQ';   
	}else{
		if (SITE_TYPE != 'Demo') {
		 //echo '<pre>'; print_r($status); echo '</pre>';die;
			 //$query = "UPDATE faqs SET eStatus = '" . $status . "' WHERE iFaqId = '" . $iFaqId . "'";
			
			$checkboxArr = explode(',',$checkbox);
			foreach($checkboxArr as $key => $iFaqId) {	
				$data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM faqs";
				$data_rec = $obj->MySQLSelect($data_q);
		
				$order = isset($data_rec[0]['iDisplayOrder']) ? $data_rec[0]['iDisplayOrder'] : 0;
		
				$data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM faqs WHERE iFaqId = '" . $iFaqId . "'");
		
				if (scount($data_logo) > 0) {
					$iDisplayOrder = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
					$obj->sql_query("DELETE FROM faqs WHERE iFaqId = '" . $iFaqId . "'");
					
					if ($iDisplayOrder < $order) {
						for ($i = $iDisplayOrder + 1; $i <= $order; $i++) {
							$obj->sql_query("UPDATE faqs SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i);
						}
					}
				}
			}
			
			//$query = "DELETE FROM faqs WHERE iFaqId IN (" . $checkbox . ")"; //die;    
			//$obj->sql_query($query);
			$status='deleted';
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
		} else {
			$_SESSION['success']=2;
		}
	}
	if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('SetFaqs');
    }
    updateSystemData();

	header("Location:".$tconfig["tsite_url_main_admin"]."faq.php?".$parameters); exit;
}
//End Change All Deleted Selected Status


//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") { 
	if(!$userObj->hasPermission(['update-status-faq', 'delete-faq'])) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to change status of FAQ';   
	}else{
		if(SITE_TYPE !='Demo'){
			 $query = "UPDATE faqs SET eStatus = '" . $statusVal . "' WHERE iFaqId IN (" . $checkbox . ")";
			 $obj->sql_query($query);
			 $_SESSION['success'] = '1';
			 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
		}
		else{
			$_SESSION['success']=2;
		}
	}
	if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('SetFaqs');
    }
    updateSystemData();
    
	header("Location:".$tconfig["tsite_url_main_admin"]."faq.php?".$parameters);
	exit;
}