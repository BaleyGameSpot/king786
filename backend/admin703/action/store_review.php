<?php



include_once('../../common.php');


$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?',$reload);

$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$iRatingId = isset($_REQUEST['iRatingId']) ? $_REQUEST['iRatingId'] : '';

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';

$checkbox = isset($_REQUEST['checkbox']) ? implode(',',$_REQUEST['checkbox']) : '';

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

$reviewtype = isset($_REQUEST['reviewtype']) ? $_REQUEST['reviewtype'] : '';

//Start make deleted

if ($method == 'delete'&& $iRatingId != '') {

	if (!$userObj->hasPermission('delete-reviews')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to delete review';

    } else {


		if(SITE_TYPE !='Demo'){

	            $query = "UPDATE ratings_user_driver SET eStatus = 'Deleted' WHERE iRatingId = '".$iRatingId."'";

	            $obj->sql_query($query);

                $iOrderId = get_value('ratings_user_driver', 'iOrderId', 'iRatingId', $iRatingId, '', 'true');

                if ($reviewtype == "Driver") {
                    $iDriverId = get_value('orders', 'iDriverId', 'iOrderId', $iOrderId, '', 'true');
                    $tableName = "register_driver";
                    $where = "iDriverId='" . $iDriverId . "'";
                    $iMemberId = $iDriverId;
                } if ($reviewtype == "Company") {
                    $iCompanyId = get_value('orders', 'iCompanyId', 'iOrderId', $iOrderId, '', 'true');
                    $tableName = "company";
                    $where = "iCompanyId='" . $iCompanyId . "'";
                    $iMemberId = $iCompanyId;
                } else {
                    $iUserId = get_value('orders', 'iUserId', 'iOrderId', $iOrderId, '', 'true');
                    $tableName = "register_user";
                    $where = "iUserId='" . $iUserId . "'";
                    $iMemberId = $iUserId;
                }
               // $Data_update['vAvgRating'] = FetchUserAvgRating($iMemberId, $reviewtype, 'Yes');
                $id1 = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);

	            $_SESSION['success'] = '1';

	            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];

		} else {

	            $_SESSION['success'] = '2';

		}
	}

	header("Location:".$tconfig["tsite_url_main_admin"]."store_review.php?".$parameters); 
	exit;

}

//End make deleted



//Start Change single Status

/*if ($iRatingId != '' && $status != '') {

	if(SITE_TYPE !='Demo'){

            $query = "UPDATE ratings_user_driver SET eStatus = '" . $status . "' WHERE iRatingId = '" . $iRatingId . "'";

            $obj->sql_query($query);

            $_SESSION['success'] = '1';

            if($status == 'Active') {

                   $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];

            } else {

                   $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];

            }

	} else {

            $_SESSION['success']=2;

	}

    header("Location:".$tconfig["tsite_url_main_admin"]."store_review.php?".$parameters);

    exit;

}
*/
//End Change single Status

?>