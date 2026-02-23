<?php
include_once('../../common.php');


$AUTH_OBJ->checkMemberAuthentication();

$reload = $_SERVER['REQUEST_URI']; 

$urlparts = explode('?',$reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iGiftCardId = isset($_REQUEST['iGiftCardId']) ? $_REQUEST['iGiftCardId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start make deleted
if ($method == 'delete' && $iGiftCardId != '') {


	if(!$userObj->hasPermission('delete-giftcard')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete gift card.';   
    }else{

		if(SITE_TYPE !='Demo'){
	            $query = "UPDATE gift_cards SET eStatus = 'Deleted' WHERE iGiftCardId = '" . $iGiftCardId . "'";
	            $obj->sql_query($query);

            
	            $_SESSION['success'] = '1';
	            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];   
		}
		else{
	            $_SESSION['success'] = '2';
		}
	}
	header("Location:".$tconfig["tsite_url_main_admin"]."gift_card.php?".$parameters); exit;
}
//End make deleted


//Start Change single Status
if ($iGiftCardId != '' && $status != '') {
	if(!$userObj->hasPermission('update-status-giftcard')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of gift card.';   
    }else{
		if(SITE_TYPE !='Demo'){
	            $query = "UPDATE gift_cards SET eStatus = '" . $status . "' WHERE iGiftCardId = '" . $iGiftCardId . "'";
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
    header("Location:".$tconfig["tsite_url_main_admin"]."gift_card.php?".$parameters);
    exit;
}
//End Change single Status

//Start Change All Selected Status
if($checkbox != "" && $statusVal != "") {
	if(!$userObj->hasPermission(['update-status-giftcard', 'delete-giftcard'])) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to change status of gift card';   
    }else{
		if(SITE_TYPE !='Demo'){
			 $query = "UPDATE gift_cards SET eStatus = '" . $statusVal . "' WHERE iGiftCardId IN (" . $checkbox . ")";
			 $obj->sql_query($query);
			 $_SESSION['success'] = '1';
			 $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
		}
		else{
			$_SESSION['success']=2;
		}
	}
    header("Location:".$tconfig["tsite_url_main_admin"]."gift_card.php?".$parameters);
    exit;
}
//End Change All Selected Status

?>