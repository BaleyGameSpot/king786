<?php
include_once('../../common.php');


$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iFeedbackId = isset($_REQUEST['iFeedbackId']) ? $_REQUEST['iFeedbackId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';

$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

if ($method == 'delete' && $iFeedbackId != '') {

	if(!$userObj->hasPermission('delete-rating-feedback-ques')) {
				$_SESSION['success'] = 3;
				$_SESSION['var_msg'] = 'You do not have permission to delete feedback questions';   
	}else{
		if(SITE_TYPE !='Demo'){
			$query = "UPDATE rating_feedback_questions SET eStatus = 'Deleted' WHERE iFeedbackId = '" . $iFeedbackId . "'";
			$obj->sql_query($query);
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];   
		}
		else{
			$_SESSION['success'] = '2';
		}
	}

	if(!empty($OPTIMIZE_DATA_OBJ)) {
	    $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
	}

	updateSystemData();
	header("Location:".$tconfig["tsite_url_main_admin"]."rating_feedback_ques.php?".$parameters); exit;
}


//Start Change single Status
if ($iFeedbackId != '' && $status != '') {
	if(!$userObj->hasPermission('update-status-rating-feedback-ques')) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to change status of feedback questions';   
	}else{
		if(SITE_TYPE !='Demo'){
			$query = "UPDATE rating_feedback_questions SET eStatus = '" . $status . "' WHERE iFeedbackId = '" . $iFeedbackId . "'";
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
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
    }

    updateSystemData();
	header("Location:".$tconfig["tsite_url_main_admin"]."rating_feedback_ques.php?".$parameters);
	exit;
}


if ($checkbox != ''  && $statusVal == 'Deleted') { 
	if(!$userObj->hasPermission('delete-rating-feedback-ques')) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to delete feedback questions';   
	}else{
		if (SITE_TYPE != 'Demo') {
			$query = "UPDATE rating_feedback_questions SET eStatus = 'Deleted' WHERE iFeedbackId IN (" . $checkbox . ")"; //die;    

			$obj->sql_query($query);
			$status='deleted';
			$_SESSION['success'] = '1';
			$_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
		} else {
			$_SESSION['success']=2;
		}
	}

	if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
    }

    updateSystemData();
	header("Location:".$tconfig["tsite_url_main_admin"]."rating_feedback_ques.php?".$parameters);
	exit;
}
//End Change All Deleted Selected Status


//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") { 
	if(!$userObj->hasPermission(['update-status-rating-feedback-ques', 'delete-rating-feedback-ques'])) {
		$_SESSION['success'] = 3;
		$_SESSION['var_msg'] = 'You do not have permission to change status of feedback questions';   
	}else{
		if(SITE_TYPE !='Demo'){
			 $query = "UPDATE rating_feedback_questions SET eStatus = '" . $statusVal . "' WHERE iFeedbackId IN (" . $checkbox . ")";
			 $obj->sql_query($query);
			 $_SESSION['success'] = '1';
			 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
		}
		else{
			$_SESSION['success']=2;
		}
	}

	if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
    }

    updateSystemData();
	header("Location:".$tconfig["tsite_url_main_admin"]."rating_feedback_ques.php?".$parameters);
	exit;
}