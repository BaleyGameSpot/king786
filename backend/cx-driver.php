<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$TRACKING_COMPANY = "";
if ($_SESSION['sess_user'] == 'tracking_company') {
    $TRACKING_COMPANY = 1;
}
$abc = 'company';
if ($TRACKING_COMPANY == 1) {
    $abc = 'tracking_company';
    $iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
}
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';

/*$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';*/

$success = '';
if(isset($_SESSION['message_code']) && !empty($_SESSION['message_code']))
{
    $success = $_SESSION['message_code'];
    $_SESSION['message_code'] = '';
}
$var_msg = '';
if(isset($_SESSION['session_msg']) && !empty($_SESSION['session_msg']))
{
    $var_msg = $_SESSION['session_msg'];
    $_SESSION['session_msg'] = '';
}

$Status = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
$iCompanyId = $_SESSION['sess_iUserId'];
$vehicleColumn = 0;
if ($_SESSION['sess_user'] == 'company') {
    $storeData = $obj->MySQLSelect("SELECT eSystem FROM company WHERE iCompanyId = '" . $iCompanyId . "'");
    if (isset($storeData[0]['eSystem']) && $storeData[0]['eSystem'] == "DeliverAll") {
        $vehicleColumn = 1;
    }
}

if($APP_TYPE == "UberX"){
    $driverlisturl = "providerlist";
    $driverdocumentactionurl = "provider_document_add_form";
} else { 
    $driverlisturl = "driverlist";
    $driverdocumentactionurl = "driver_document_add_form";
}

$db_country = $obj->MySQLSelect("SELECT * FROM country WHERE eStatus = 'Active'");
$db_lang = $obj->MySQLSelect("SELECT * FROM language_master WHERE eStatus = 'Active'");
$script = 'Driver';
if ($action == 'delete') {
    if (SITE_TYPE != '') { // updated by Nmodi on 11-12-20
        $query = "UPDATE register_driver SET eStatus = 'Deleted' WHERE iDriverId = '" . $hdn_del_id . "'";
        $obj->sql_query($query);
        $var_msg = $langage_lbl['LBL_COMPNAY_FRONT_DELETE_TEXT'];

        $_SESSION['message_code'] = "1";
        $_SESSION['session_msg'] = $var_msg;
        //header("Location:".$driverlisturl."?success=1&var_msg=" . $var_msg);
        header("Location:".$driverlisturl);
        exit();
    } else {
        $_SESSION['message_code'] = "2";
        header("Location:".$driverlisturl);
       // header("Location:".$driverlisturl."?success=2");
        exit();
    }
}
if ($Status != '') {
    $driverIds = $hdn_del_id;
    $sql = "SELECT register_driver.iDriverId from register_driver LEFT JOIN driver_vehicle on driver_vehicle.iDriverId=register_driver.iDriverId WHERE driver_vehicle.eStatus='Active' AND eType = 'TrackService'  AND  register_driver.iDriverId IN (" . $driverIds . ") GROUP BY register_driver.iDriverId";
    $Data = $obj->MySQLSelect($sql);
    if ($Status == 'active') {
        if (scount($Data) <= 0) {
            $var_msg = $langage_lbl["LBL_DRIVER_TXT_ADMIN"] . ' status can not be activated because either ' . $langage_lbl["LBL_DRIVER_TXT_ADMIN"] . ' has not added any vehicle or his added vehicle is not activated yet. Please try again after adding and activating the vehicle.';
            $_SESSION['message_code'] = "3";
            $_SESSION['session_msg'] = $var_msg;
            header("Location:".$driverlisturl);
           // header("Location:".$driverlisturl."?success=3&var_msg=" . $var_msg);
            exit;
        }
    }
    $query = "UPDATE register_driver SET eStatus = '" . $Status . "' WHERE iDriverId = '" . $hdn_del_id . "'";
    $id = $obj->sql_query($query);
    if ($Status == 'active') {
        $var_msg = $langage_lbl["LBL_RECORD_ACTIVATE_MSG"];
    } else {
        $var_msg = $langage_lbl["LBL_RECORD_INACTIVATE_MSG"];
    }
    $_SESSION['message_code'] = "1";
    $_SESSION['session_msg'] = $var_msg;
    header("Location:".$driverlisturl);
    //header("Location:".$driverlisturl."?success=1&var_msg=" . $var_msg);
    exit();
}
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLname = isset($_POST['vLname']) ? $_POST['vLname'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = encrypt($vPassword);
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$tbl_name = "register_driver";
if (isset($_POST['submit'])) {
    $q = "INSERT INTO ";
    $where = '';
    if ($action == 'Edit') {
        $eStatus = ", eStatus = 'Inactive' ";
    } else {
        $eStatus = '';
    }
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDriverId` = '" . $id . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
    `vName` = '" . $vName . "',
    `vLastName` = '" . $vLname . "',
    `vCountry` = '" . $vCountry . "',
    `vCode` = '" . $vCode . "',
    `vEmail` = '" . $vEmail . "',
    `vLoginId` = '" . $vEmail . "',
    `vPassword` = '" . $vPass . "',
    `vPhone` = '" . $vPhone . "',
    `vLang` = '" . $vLang . "',
    `eStatus` = '" . $eStatus . "',
    `iCompanyId` = '" . $iCompanyId . "'" . $where;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    if (SITE_TYPE != 'Demo') {
        if ($action == 'Edit') {
            $var_msg = $langage_lbl['LBL_COMPNAY_FRONT_UPDATE_DRIVER_TEXT'];
            $_SESSION['message_code'] = "1";
            $_SESSION['session_msg'] = $var_msg;
            header("Location:".$driverlisturl);
           // header("Location:".$driverlisturl."?id=" . $id . "&success=1&var_msg=" . $var_msg);
            exit;
        } else {
            $var_msg = $langage_lbl['LBL_COMPNAY_FRONT_ADD_DRIVER_TEXT'];
            $_SESSION['message_code'] = "1";
            $_SESSION['session_msg'] = $var_msg;
            header("Location:".$driverlisturl);
            //header("Location:".$driverlisturl."?id=" . $id . "&success=1&var_msg=" . $var_msg);
            exit;
        }
    } else {
        $_SESSION['message_code'] = "2";
        header("Location:".$driverlisturl);
        exit;
    }
}
$dri_ssql = "";
if (SITE_TYPE == 'Demo' && $vehicleColumn == 0) {
    $dri_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
if ($action == 'view') {
    $sql = "SELECT * FROM register_driver where iCompanyId = '" . $iCompanyId . "' and eStatus != 'Deleted' $dri_ssql order by tRegistrationDate DESC";
    if ($TRACKING_COMPANY == 1) {
        $sql = "SELECT * FROM register_driver where iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' and eStatus != 'Deleted' $dri_ssql order by tRegistrationDate DESC";
    }
    $data_drv = $obj->MySQLSelect($sql);
    if ($APP_TYPE == 'Ride-Delivery') {
        $eTypeQuery = " AND (eType='Ride' OR eType='Delivery')";
    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {
        $eTypeQuery = " AND (eType='Ride' OR eType='Delivery' OR eType='UberX')";
    } else {
        $eTypeQuery = " AND eType='" . $APP_TYPE . "'";
    }
    $sql1 = "SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='driver' AND status = 'Active' $eTypeQuery";
    $doc_count_query = $obj->MySQLSelect($sql1);
    $doc_count = scount($doc_count_query);
}
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?php if ($TRACKING_COMPANY == 1) { ?>
        <title><?= $SITE_NAME ?> | Driver</title>
    <?php } else { ?>
        <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_VEHICLE_DRIVER_TXT_ADMIN']; ?></title>
    <?php } ?>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <style type="text/css"></style>
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- Driver page-->
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_DRIVER_COMPANY_TXT']; ?></h1>
                </div>
                <div class="button-block end">
                    <?php if ($TRACKING_COMPANY == 1) { ?>
                        <a href="javascript:void(0);" onClick="add_driver_form();"
                           class="gen-btn"><?= $langage_lbl['LBL_ADD_DRIVER_TRACKING_COMPANY_TXT']; ?></a>
                    <?php } else { ?>
                        <a href="javascript:void(0);" onClick="add_driver_form();"
                           class="gen-btn"><?= $langage_lbl['LBL_ADD_DRIVER_COMPANY_TXT']; ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="table-holder">
                <div class="page-contant">
                    <div class="page-contant-inner">
                        <!-- driver list page -->
                        <div class="trips-page trips-page1">
                            <? if (isset($success) && $success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x
                                    </button>
                                    <?= $var_msg ?>
                                </div>
                            <? } else if (isset($success) && $success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                    </button>
                                    <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                                </div>
                            <?php } else if (isset($success) && $success == 3) {
                                ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                    </button>
                                    <?= $var_msg ?>
                                </div>
                            <? }
                            ?>
                            <div class="trips-table trips-table-driver trips-table-driver-res">
                                <div class="trips-table-inner">
                                    <div class="driver-trip-table">
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0"
                                               id="dataTables-example"
                                               class="ui celled table custom-table dataTable no-footer no-footer-new">
                                            <thead>
                                            <tr>
                                                <?php
                                                /* We has conditon commented because Issues To Be Fixed - #201
                                                if ($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX") { */ ?>
                                                <th><?= $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?></th>
                                                <?php /*}*/ ?>
                                                <th><?= $langage_lbl['LBL_DRIVER_EMAIL_LBL_TXT']; ?></th>
                                                <!--<th>Service Location</th>-->
                                                <th><?= $langage_lbl['LBL_MOBILE_NUMBER_HEADER_TXT']; ?></th>
                                                <?php if ($MODULES_OBJ->isUfxFeatureAvailable() != 'No' && $_SESSION['sess_eSystem'] == "General") { ?>
                                                    <th>
                                                        <?
                                                        if ($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX") {
                                                            echo $langage_lbl['LBL_SHORT_LANG_TXT'];
                                                        } else {
                                                            echo $langage_lbl['LBL_SERVICES_WEB'];
                                                        }
                                                        ?>
                                                    </th>
                                                    <?php if ($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") { ?>
                                                        <th>
                                                            <?php echo $langage_lbl['LBL_AVAILABILITY']; ?>
                                                        </th>
                                                    <?php }
                                                } ?>
                                                <?php if ($APP_TYPE != 'UberX' && $RideDeliveryBothFeatureDisable == 'No' && $isStoreDriver > 0 && $vehicleColumn > 0) { ?>
                                                    <th>
                                                        <?= $langage_lbl['LBL_VEHICLE_TITLE']; ?>
                                                    </th>
                                                <?php } ?>
                                                <?php if ($doc_count != 0) { ?>
                                                    <th><?php echo $langage_lbl['LBL_EDIT_DOCUMENTS_TXT']; ?></th>
                                                <?php } ?>
                                                <?php if ($TRACKING_COMPANY == 1) { ?>
                                                    <!--  <th width="15%"><? /*= $langage_lbl['LBL_Status']; */ ?></th>-->
                                                <?php } ?>
                                                <th><?= $langage_lbl['LBL_DRIVER_EDIT']; ?></th>
                                                <th><?= $langage_lbl['LBL_DRIVER_DELETE']; ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $i < scount($data_drv); $i++) { ?>
                                                <tr class="gradeA">
                                                    <?php /* We has conditon commented because Issues To Be Fixed - #201
                                                    if ($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX") {*/ ?>
                                                    <td><?= clearName($data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']); ?></td>
                                                    <?php /*}*/ ?>
                                                    <td><?= clearEmail($data_drv[$i]['vEmail']); ?></td>
                                                    <td>
                                                        <?php if (!empty($data_drv[$i]['vPhone'])) { ?>
                                                            (+<?= $data_drv[$i]['vCode']; ?>)
                                                            <?= clearMobile($data_drv[$i]['vPhone']); ?><?php } ?>
                                                    </td>
                                                    <?php if ($MODULES_OBJ->isUfxFeatureAvailable() != 'No' && $_SESSION['sess_eSystem'] == "General") { ?>
                                                        <td valign="top">
                                                            <?
                                                            if ($APP_TYPE != "UberX" && $APP_TYPE != "Ride-Delivery-UberX") {
                                                                echo $data_drv[$i]['vLang'];
                                                            } else {
                                                                ?>
                                                                <a href="add_services.php?iProviderId=<?= base64_encode(base64_encode($data_drv[$i]['iDriverId'])); ?>"
                                                                   class="gen-btn small-btn">
                                                                    <?= $langage_lbl['LBL_SERVICES_WEB']; ?>
                                                                </a>
                                                                <?php if (checkServicesIsSelectedByProvider($data_drv[$i]['iDriverId']) > 0 && $SHOW_PROVIDER_FILL_DETAILS_TICK_MARK_TO_ADMIN == "Yes") { ?>
                                                                    <img src="<?= $tconfig["tsite_url_main_admin"] ?>img/active-icon-c.png"
                                                                         alt="">
                                                                <?php } ?><? } ?>
                                                        </td>
                                                        <?php if ($APP_TYPE == "UberX" || $APP_TYPE == "Ride-Delivery-UberX") { ?>
                                                            <td valign="top">
                                                                <a href="add_availability.php?iProviderId=<?= $data_drv[$i]['iDriverId']; ?>"
                                                                   class="gen-btn small-btn">
                                                                    <?= $langage_lbl['LBL_AVAILABILITY']; ?>
                                                                </a>
                                                                <?php if (checkTimeAvailabilityIsSelectedByProvider($data_drv[$i]['iDriverId']) > 0 && $SHOW_PROVIDER_FILL_DETAILS_TICK_MARK_TO_ADMIN == "Yes") { ?>
                                                                    <img src="<?= $tconfig["tsite_url_main_admin"] ?>img/active-icon-c.png"
                                                                         alt="">
                                                                <?php } ?>
                                                            </td>
                                                        <?php }
                                                    } ?>
                                                    <?php if ($APP_TYPE != 'UberX' && $RideDeliveryBothFeatureDisable == 'No' && $isStoreDriver > 0 && $vehicleColumn > 0) { ?>
                                                        <td valign="top" class="providerdocClass">
                                                            <a href="vehicle?driverid=<?= $data_drv[$i]['iDriverId']; ?>&action=edit&vehicle=store"
                                                               class="gen-btn small-btn">
                                                                <?= $langage_lbl['LBL_ADD'] . "/" . $langage_lbl['LBL_DRIVER_EDIT']; ?>
                                                            </a>
                                                        </td>
                                                    <?php } ?>
                                                    <?php if ($doc_count != 0) { ?>
                                                        <td valign="top" class="providerdocClass">
                                                            <a href="<?php echo $driverdocumentactionurl;?>?id=<?= $data_drv[$i]['iDriverId']; ?>&action=edit"
                                                               class="gen-btn small-btn">
                                                                <?= $langage_lbl['LBL_EDIT_DOCUMENTS_TXT']; ?>
                                                            </a>
                                                            <?php if (checkDocumentIsUploadedByProvider($data_drv[$i]['iDriverId'], $data_drv[$i]['vCountry']) > 0 && $SHOW_PROVIDER_FILL_DETAILS_TICK_MARK_TO_ADMIN == "Yes") { ?>
                                                                <span class="rightBtn">
                                                                    <img src="<?= $tconfig["tsite_url_main_admin"] ?>img/active-icon-c.png">
                                                                </span>
                                                            <?php } ?>
                                                        </td>
                                                    <?php } ?>

                                                    <?php if ($TRACKING_COMPANY == 1) { ?>
                                                        <!--<td width="10%" align="center">
                                                            <a href="providerlist?hdn_del_id=<? /*= $data_drv[$i]['iDriverId']; */ ?>&Status=<? /*= ($data_drv[$i]['eStatus'] == "active") ? 'inactive' : 'active' */ ?>" class="gen-btn small-btn">
                                                                <?php /*if (strtolower($data_drv[$i]['eStatus']) == "active") {
                                                                    $statusLabel = $langage_lbl['LBL_ACTIVE'];
                                                                }
                                                                else {
                                                                    $statusLabel = $langage_lbl['LBL_INACTIVE'];
                                                                } */ ?>
                                                                <? /*= $statusLabel; */ ?></a>
                                                        </td>-->
                                                    <?php } ?>
                                                    <td valign="top">
                                                        <?php if ($APP_TYPE == "UberX"){ ?>
                                                            <a href="provider_add_form?id=<?= $data_drv[$i]['iDriverId']; ?>&action=edit"
                                                           class="gen-btn small-btn"
                                                           onclick="<?php if (getEditDriverProfileStatus($data_drv[$i]['eStatus']) == "No") { ?> alert('<?php echo $langage_lbl['LBL_PROFILE_EDIT_BLOCK_TXT']; ?>') <?php } ?>">
                                                        <?php } else { ?>
                                                           <a href="driver_add_form?id=<?= $data_drv[$i]['iDriverId']; ?>&action=edit"
                                                           class="gen-btn small-btn"
                                                           onclick="<?php if (getEditDriverProfileStatus($data_drv[$i]['eStatus']) == "No") { ?> alert('<?php echo $langage_lbl['LBL_PROFILE_EDIT_BLOCK_TXT']; ?>') <?php } ?>">
                                                        <?php } ?>
                                                            <?= $langage_lbl['LBL_DRIVER_EDIT']; ?>
                                                        </a>
                                                    </td>
                                                    <td valign="top">
                                                        <form name="delete_form_<?= $data_drv[$i]['iDriverId']; ?>"
                                                              id="delete_form_<?= $data_drv[$i]['iDriverId']; ?>"
                                                              method="post" action="" class="margin0">
                                                            <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                                                   value="<?= $data_drv[$i]['iDriverId']; ?>">
                                                            <input type="hidden" name="action" id="action"
                                                                   value="delete">
                                                            <button type="button" class="gen-btn small-btn del_btn"
                                                                    onClick="confirm_delete('<?= $data_drv[$i]['iDriverId']; ?>');">
                                                                <?= $langage_lbl['LBL_DRIVER_DELETE']; ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="assets/js/modal_alert.js"></script>
    <!-- footer part -->
    <!-- Powered by V3Cube.com -->
    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
</div>
<?php include_once('top/footer_script.php'); ?>
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#dataTables-example').dataTable({
            "oLanguage": langData,
            "aaSorting": [],
            "aoColumnDefs": [
                {
                    "bSortable": false, "aTargets": [-1, -2, -3, -4, -5],
                    "searchable": false, "aTargets": [-1, -2, -3, -4, -5]
                }
            ]
        });
    });

    function confirm_delete(id) {
        $(".del_btn").attr('disabled', 'disabled');
        $("#hdn_del_id").val(id);
        show_alert("<?= addslashes($langage_lbl['LBL_DELETE']); ?>", "<?= addslashes($langage_lbl['LBL_DELETE_DRIVER_CONFIRM_MSG']); ?>", "<?= addslashes($langage_lbl['LBL_CONFIRM_TXT']); ?>", "<?= addslashes($langage_lbl['LBL_CANCEL_TXT']); ?>", "", function (btn_id) {
            if (btn_id == 0) {
                id = $("#hdn_del_id").val();
                ShpSq6fAm7($("#delete_form_" + id));
                document.getElementById("delete_form_" + id).submit();
            }
            $(".del_btn").removeAttr("disabled", "disabled");
        });
        $(".del_btn").removeAttr("disabled", "disabled");
    }

    function changeCode(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            } else {
                console.log(response.result);
            }
        });
    }

    function add_driver_form() {
        <?php if ($APP_TYPE == "UberX"){ ?>
            window.location.href = "provider_add_form";
        <?php } else { ?>
            window.location.href = "driver_add_form";
        <?php } ?>
    }

    $(document).ready(function () {
        $("[name='dataTables-example_length']").each(function () {
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $("[name='dataTables-example_length']").change(function () {
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    })
</script>
</body>
</html>