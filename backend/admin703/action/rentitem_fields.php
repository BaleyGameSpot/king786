<?php
include_once('../../common.php');


$reload = $_SERVER['REQUEST_URI'];

$urlparts = explode('?', $reload);

$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$iRentFieldId = isset($_REQUEST['iRentFieldId']) ? $_REQUEST['iRentFieldId'] : '';

$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';

$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';

$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';

$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';

//echo "<pre>"; print_r($_REQUEST);die;

//Start make deleted

if (($statusVal == 'Deleted' || $method == 'delete') && ($iRentFieldId != '' || $checkbox != "")) {

    if (!$userObj->hasPermission('delete-rentitem-fields')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to delete package type';

    } else {

        //Added By Hasmukh On 05-10-2018 For Solved Bug Start

        if ($iRentFieldId != "") {

            $fieldIds = $iRentFieldId;

        } else {

            $fieldIds = $checkbox;

        }

        //Added By Hasmukh On 05-10-2018 For Solved Bug End

        if (SITE_TYPE != 'Demo') {

            $query = "UPDATE rentitem_fields SET eStatus = 'Deleted' WHERE iRentFieldId IN (" . $fieldIds . ")";

            $obj->sql_query($query);

            $_SESSION['success'] = '1';

            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];

        } else {

            $_SESSION['success'] = '2';

        }

    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "rentitem_fields.php?" . $parameters);

    exit;

}

//End make deleted

//Start Change single Status

if ($iRentFieldId != '' && $status != '') {

    if (!$userObj->hasPermission('update-status-rentitem-fields')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to change status of rent item fields';

    } else {

        if (SITE_TYPE != 'Demo') {

            $query = "UPDATE rentitem_fields SET eStatus = '" . $status . "' WHERE iRentFieldId = '" . $iRentFieldId . "'";

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

    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "rentitem_fields.php?" . $parameters);

    exit;

}

//End Change single Status

//Start Change All Selected Status

if ($checkbox != "" && $statusVal != "") {

    if (!$userObj->hasPermission('update-status-rentitem-fields')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to change status of package type';

    } else {

        if (SITE_TYPE != 'Demo') {

            $query = "UPDATE rentitem_fields SET eStatus = '" . $statusVal . "' WHERE iRentFieldId IN (" . $checkbox . ")";

            $obj->sql_query($query);

            $_SESSION['success'] = '1';

            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

        } else {

            $_SESSION['success'] = 2;

        }

    }

    header("Location:" . $tconfig["tsite_url_main_admin"] . "rentitem_fields.php?" . $parameters);

    exit;

}

//End Change All Selected Status

?>