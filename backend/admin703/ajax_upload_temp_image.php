<?php
include '../common.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
$image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';

if ($image_name != "") {
    $Photo_Gallery_folder = $tconfig['tpanel_path'] . 'webimages/temp_item_option_images/';

    if (!is_dir($Photo_Gallery_folder)) {
        mkdir($Photo_Gallery_folder, 0777);
        chmod($Photo_Gallery_folder, 0777);
    }
    $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "bmp,jpg,jpeg,gif,png");
    $vImageName = $vFile[0];

    $returnArr['Action'] = "1";
    $returnArr['message'] = $vImageName;
    echo json_encode($returnArr);
    exit;
}

$returnArr['Action'] = "0";
$returnArr['message'] = "";
echo json_encode($returnArr);
exit;
?>