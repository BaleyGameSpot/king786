<?php
include 'common.php';

header('Content-Type: application/json');

$id = isset($_REQUEST['id']) ? $obj->Escape($_REQUEST['id']) : '';

if (empty($id)) {
    echo json_encode(['error' => 'Missing parameter: id']);
    exit;
}

$sql = "SELECT vPhoneCode FROM country WHERE vCountryCode = '$id'";
$db_data = $obj->MySQLSelect($sql);

if (empty($db_data)) {
    echo json_encode(['error' => 'Country not found']);
    exit;
}

$vPhoneCode = $db_data[0]['vPhoneCode'];

// Check if driver is logged in
if (isset($_SESSION['sess_iUserId'])) {
    $iDriverId = $obj->Escape($_SESSION['sess_iUserId']);
    $sql = "SELECT vCountry FROM register_driver WHERE iDriverId = '$iDriverId'";
    $edit_data = $obj->MySQLSelect($sql);

    if (!empty($edit_data) && $id !== $edit_data[0]['vCountry']) {
        $update_sql = "UPDATE register_driver SET ePhoneVerified = 'No' WHERE iDriverId = '$iDriverId'";
        $obj->sql_query($update_sql);
    }
}

echo json_encode(['vPhoneCode' => $vPhoneCode]);
exit;
?>
