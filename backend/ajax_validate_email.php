<?php
include_once('common.php');
$ssql = "";
$ssql1 = "";
$usertype = $_SESSION['sess_user'];
$type = $_REQUEST['usr'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if ($_REQUEST['uid'] != "" && $usertype == 'company' && $action != 'Edit-Profile') {
    $ssql = "and iCompanyId != '" . $_REQUEST['uid'] . "' and eStatus != 'Deleted'";
} else if ($_REQUEST['uid'] != "" && $usertype == 'company' && $action == 'Edit-Profile') {
    $ssql = "and iCompanyId = '" . $_REQUEST['uid'] . "' and eStatus != 'Deleted'";
}
/* Use For Organization Module */
if ($_REQUEST['uid'] != "" && $usertype == 'organization' && $action != 'Edit-Profile') {
    $ssql = "and iOrganizationId != '" . $_REQUEST['uid'] . "'";
} else if ($_REQUEST['uid'] != "" && $usertype == 'organization' && $action == 'Edit-Profile'){
    $ssql = "and iOrganizationId = '" . $_REQUEST['uid'] . "'";
}
/* Use For Organization Module */
if ($_REQUEST['uid'] != "" && $usertype == 'driver' && $action != 'Edit-Profile') {
    $ssql1 = "and iDriverId != '" . $_REQUEST['uid'] . "'";
} else if ($_REQUEST['uid'] != "" && $usertype == 'driver' && $action == 'Edit-Profile'){
    $ssql1 = "and iDriverId = '" . $_REQUEST['uid'] . "'";
}
if ($_REQUEST['uid'] != "" && $usertype == 'rider' && $action != 'Edit-Profile') {
    $ssql2 = "and iUserId != '" . $_REQUEST['uid'] . "'";
} else if ($_REQUEST['uid'] != "" && $usertype == 'rider' && $action == 'Edit-Profile') {
    $ssql2 = "and iUserId = '" . $_REQUEST['uid'] . "'";
}
if ($_REQUEST['uid'] != "" && $usertype == 'company' && $type == 'driver') {
    $ssql1 = "and iDriverId != '" . $_REQUEST['uid'] . "'";
}
if (isset($_REQUEST['id']) && $usertype == 'company') {
    $email = strtolower($_REQUEST['id']);
    if ($usertype == 'company' && $type == 'company') {
        $sql = "SELECT vEmail,eStatus FROM company WHERE vEmail = '" . $email . "' $ssql";
        $db_user = $obj->MySQLSelect($sql);
    }
    if ($usertype == 'company' && $type == 'driver') {
        $sql = "SELECT vEmail,eStatus FROM register_driver WHERE vEmail = '" . $email . "' $ssql1";
        $db_user = $obj->MySQLSelect($sql);
    }
    if($action != 'Edit-Profile'){
    if (scount($db_user) > 0) {
        if ((ucfirst($db_user[0]['eStatus']) == 'Deleted') || (ucfirst($db_user[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
    } else {
        echo 'true';
    }
    } else {
        if (scount($db_user) > 0) {
            if ((ucfirst($db_user[0]['eStatus']) == 'Deleted')) {
                echo 'deleted';
            } else {
                echo 'true';
            }
        } else {
            echo 'true';
        }
    }
}
if (isset($_REQUEST['id']) && $usertype == 'organization') {
    $email = strtolower($_REQUEST['id']);
    $sql = "SELECT vEmail,eStatus FROM organization WHERE vEmail = '" . $email . "' $ssql";
    $db_user = $obj->MySQLSelect($sql);

    if($action != 'Edit-Profile'){
    if (scount($db_user) > 0) {
        if ((ucfirst($db_user[0]['eStatus']) == 'Deleted') || (ucfirst($db_user[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
    } else {
        echo 'true';
    }
    } else {
        if (scount($db_user) > 0) {
            if ((ucfirst($db_user[0]['eStatus']) == 'Deleted')) {
                echo 'deleted';
            } else {
                echo 'true';
            }
        } else {
            echo 'true';
        }
    }
}
if (isset($_REQUEST['id']) && $usertype == 'driver') {
    $email = strtolower($_REQUEST['id']);
    if ($usertype == 'driver' || ($usertype == 'company' && $type == 'company')) {
        $sql = "SELECT vEmail,eStatus FROM register_driver WHERE vEmail = '" . $email . "' $ssql1";
        $db_user = $obj->MySQLSelect($sql);
    }
    if($action != 'Edit-Profile'){
    if (scount($db_user) > 0) {
        if ((ucfirst($db_user[0]['eStatus']) == 'Deleted') || (ucfirst($db_user[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
    } else {
        echo 'true';
    }
    } else {
        if (scount($db_user) > 0) {
            if ((ucfirst($db_user[0]['eStatus']) == 'Deleted')) {
                echo 'deleted';
            } else {
                echo 'true';
            }
        } else {
            echo 'true';
        }
    }
}
if (isset($_REQUEST['id']) && $usertype == 'rider') {
    $email = strtolower($_REQUEST['id']);
    if ($usertype == 'rider') {
        $sql4 = "SELECT vEmail,eStatus FROM register_user WHERE vEmail = '" . $email . "'" . $ssql2; //exit;
        $db_user = $obj->MySQLSelect($sql4);
    }
    if($action != 'Edit-Profile'){
    if (scount($db_user) > 0) {
        if ((ucfirst($db_user[0]['eStatus']) == 'Deleted') || (ucfirst($db_user[0]['eStatus']) == 'Inactive')) {
            echo 'deleted';
        } else {
            echo 'false';
        }
    } else {
        echo 'true';
    }
    } else {
        if (scount($db_user) > 0) {
            if ((ucfirst($db_user[0]['eStatus']) == 'Deleted')) {
                echo 'deleted';
            } else {
                echo 'true';
            }
        } else {
            echo 'true';
        }
    }
}
?>