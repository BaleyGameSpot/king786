<?php
include_once('common.php');

$tbl_name = 'trips';
$script = "Trips";
$iTripId = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));
$iTripId = isset($_REQUEST['iTripId']) ? intVal($iTripId) : '';
$status = $_REQUEST['status'];
$resultCheck = $obj->MySQLSelect("SELECT eType,eApproved FROM trips WHERE iTripId='" . $iTripId . "'");
if ($resultCheck[0]['eApproved'] == '') {
    $eType = $resultCheck[0]['eType'];
    if ($status == 'Yes') {
        $updateQuery = "UPDATE trips SET eApproved= 'Yes',eApproveByUser='Yes' WHERE iTripId='" . $iTripId . "' ";
        $obj->sql_query($updateQuery);
    } else {
        $updateQuery = "UPDATE trips SET eApproved= 'No',eApproveByUser='No',vChargesDetailData='' WHERE iTripId='" . $iTripId . "' ";
        $obj->sql_query($updateQuery);
    }
    $result = $obj->MySQLSelect("SELECT vRideNo FROM trips WHERE iTripId='" . $iTripId . "'");
    $vRideNo = $result[0]['vRideNo'];
    if ($status == 'Yes') {
        $statusValue = "Approved";
    } else {
        $statusValue = "Declined";
    }
    if (strtoupper($eType) == "RIDE") {
        $message = "You have " . $statusValue . " for other charges for trip " . $vRideNo . "";
    } else {
        $message = "You have " . $statusValue . " for additional charges for job " . $vRideNo . "";
    }
} else {
    $result = $obj->MySQLSelect("SELECT eType,vRideNo,eApproved FROM trips WHERE iTripId='" . $iTripId . "'");
    $vRideNo = $result[0]['vRideNo'];
    $eApproved = $result[0]['eApproved'];
    $eType = $result[0]['eType'];
    if ($eApproved == "Yes") {
        $returnmsg = "Approved";
    } else if ($eApproved == "No") {
        $returnmsg = "Declined";
    }
    if (strtoupper($eType) == "RIDE") {
        $message = "You can not proceed. Charges " . $returnmsg . " process already done for trip " . $vRideNo . "";
    } else {
        $message = "You can not proceed. Charges " . $returnmsg . " process already done for job " . $vRideNo . "";
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= $SITE_NAME ?> |<?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?> </title>
        <?php include_once("top/top_script.php"); ?>  
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    </head>
    <body id="wrapper">
        <!-- home page -->
        <div id="main-uber-page">
            <?php include_once("top/left_menu.php"); ?>
            <?php include_once("top/header_topbar.php"); ?>
            <?php if ($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
                <div class="gen-cms-page">
                    <div class="gen-cms-page-inner">
                        <div class="static-page">
                            <?= $message; ?>
                        </div>
                    </div>
                </div>
            <? } else { ?>
                <div class="page-contant">
                    <div class="page-contant-inner page-trip-detail clearfix">
                        <h2 class="header-page trip-detail">
                            <?= $message; ?>                      
                        </h2>

                        <div class="trip-detail-page">
                            <div class="trip-detail-page-inner clearfix">       
                                <div class="trip-detail-page-right1" style="font-weight: 400;font-size: 15px">
                                    <?= $message; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <? } ?>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <!-- home page end-->
        <!-- Footer Script -->
        <?php include_once('top/footer_script.php'); ?>
        <script src="assets/js/gmap3.js"></script>
        <!-- End: Footer Script -->
    </body>
</html>