<?php

include_once("common.php");


$eFavStore = isset($_REQUEST['eFavStore']) ? clean($_REQUEST['eFavStore']) : ''; // No=> 'Not Favorite','Yes'=> 'Favorite'
$iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : 0;
$iCompanyId = isset($_REQUEST['iCompanyId']) ? clean($_REQUEST['iCompanyId']) : 0;
$iServiceId = isset($_REQUEST['iServiceId']) ? clean($_REQUEST['iServiceId']) : 0;
$message = "fail";
if ($MODULES_OBJ->isFavouriteStoreModuleAvailable() && !empty($eFavStore) && !empty($iUserId) && !empty($iCompanyId) && !empty($iServiceId)) {
    include_once "include/features/include_fav_store.php";
    $returnArr = addUpdateFavStore();
    if ($returnArr['Action'] == '1') {
        if ($eFavStore == 'Yes') {
            $message = "sucess";
        }
    }
}
echo $message;
?>