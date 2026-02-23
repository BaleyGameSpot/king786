<?php
include 'common.php';

// Define que a resposta será sempre JSON
header('Content-Type: application/json');

// Pega parâmetros e limpa (supondo que $obj->Escape faz escape seguro)
$id = isset($_REQUEST['id']) ? $obj->Escape($_REQUEST['id']) : '';
$eUnit = isset($_REQUEST['eUnit']) ? $_REQUEST['eUnit'] : '';

if (empty($id)) {
    echo json_encode(['error' => 'Missing parameter: id']);
    exit;
}

if ($eUnit === 'yes') {
    $sql = "SELECT vPhoneCode, eUnit, vCountryCode, vTimeZone 
            FROM country 
            WHERE vCountry = '$id' OR iCountryId = '$id'";
    $db_data = $obj->MySQLSelect($sql);
    if (!empty($db_data)) {
        echo json_encode($db_data[0]);
    } else {
        echo json_encode(['error' => 'Country not found']);
    }
} else {
    $sql = "SELECT vPhoneCode 
            FROM country 
            WHERE vCountry = '$id'";
    $db_data = $obj->MySQLSelect($sql);
    if (!empty($db_data)) {
        echo json_encode(['vPhoneCode' => $db_data[0]['vPhoneCode']]);
    } else {
        echo json_encode(['error' => 'Country not found']);
    }
}
?>
