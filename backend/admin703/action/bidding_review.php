<?php

include_once('../../common.php');

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iRatingId = isset($_REQUEST['iRatingId']) ? $_REQUEST['iRatingId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//Start make deleted
if ($method == 'delete' && $iRatingId != '') {
    if (!$userObj->hasPermission('delete-reviews')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete review.';
    } else {

        if (SITE_TYPE != 'Demo') {
            $query = "UPDATE bidding_service_ratings SET eStatus = 'Deleted' WHERE iRatingId = '".$iRatingId."'";
            $obj->sql_query($query);

            $iBiddingPostId = get_value('ratings_user_driver', 'iBiddingPostId', 'iRatingId', $iRatingId, '', 'true');

            if ($reviewtype == "Driver") {
                $iDriverId = get_value('bidding_post', 'iDriverId', 'iBiddingPostId', $iBiddingPostId, '', 'true');
                $tableName = "register_driver";
                $where = "iDriverId='" . $iDriverId . "'";
                $iMemberId = $iDriverId;
                $eFromUserType = "Passenger";
            } else {
                $iUserId = get_value('bidding_post', 'iUserId', 'iBiddingPostId', $iBiddingPostId, '', 'true');
                $tableName = "register_user";
                $where = "iUserId='" . $iUserId . "'";
                $iMemberId = $iUserId;
                $eFromUserType = "Driver";
            }
            $Data_update['vAvgRating'] = FetchUserAvgRating($iMemberId, $eFromUserType, 'Yes');
            $id1 = $obj->MySQLQueryPerform($tableName, $Data_update, 'update', $where);

            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "bidding_review.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
/*if ($iRatingId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE bidding_service_ratings SET eStatus = '" . $status . "' WHERE iRatingId = '" . $iRatingId . "'";
        $obj->sql_query($query);
        $_SESSION['success'] = '1';
        if ($status == 'Active') {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
        }
    } else {
        $_SESSION['success'] = 2;
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "bidding_review.php?" . $parameters);
    exit;
}*/
//End Change single Status

?>