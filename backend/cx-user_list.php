<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = "tracking_company";
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$hdn_del_id = isset($_REQUEST["hdn_del_id"]) ? $_REQUEST["hdn_del_id"] : '';
$iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
if ($action == 'delete') {
    if (SITE_TYPE != '') {
        $query = "UPDATE track_service_users SET eStatus = 'Deleted' WHERE iTrackServiceUserId = '" . $hdn_del_id . "'";
        $obj->sql_query($query);
        $var_msg = $langage_lbl['LBL_COMPNAY_FRONT_DELETE_TEXT'];
        header("Location:trackinguserlist?success=1&var_msg=" . $var_msg);
        exit();
    }
    else {
        header("Location:trackinguserlist?success=2");
        exit();
    }
}
if ($action == 'view') {
    $sql = "SELECT tsu.*, CONCAT(rd.vName , ' ',rd.vLastName) as vProviderName ,(SELECT COUNT(tst.iTrackServiceTripId) FROM track_service_trips as tst WHERE FIND_IN_SET(tsu.iTrackServiceUserId, iUserIds) AND tst.eTripStatus IN ('Finished')) as ToTalTrips 
                FROM track_service_users as tsu 
                JOIN register_driver as rd ON (rd.iDriverId = tsu.iDriverId)
                where tsu.iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' and tsu.eStatus != 'Deleted' order by dAddedDate DESC";
    $data_drv = $obj->MySQLSelect($sql);
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER']; ?></title>
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
                    <h1><?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER']; ?></h1>
                </div>
                <div class="button-block end">
                    <a href="javascript:void(0);" onClick="add_driver_form();" class="gen-btn"><?= $langage_lbl['LBL_TRACK_SERVICE_ADD_USER_COMPANY_TXT']; ?></a>
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
                            <?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x
                                    </button>
                                    <?= $var_msg ?>
                                </div>
                            <?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                    </button>
                                    <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                                </div>
                            <?php }
                            else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 0) {
                                ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                    </button>
                                    <?= $var_msg ?>
                                </div>
                            <?php }
                            ?>
                            <div class="trips-table trips-table-driver trips-table-driver-res">
                                <div class="trips-table-inner">
                                    <div class="driver-trip-table">
                                        <table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example" class="ui celled table custom-table dataTable no-footer no-footer-new">
                                            <thead>
                                            <tr>
                                                <th><?= $langage_lbl['LBL_USER_NAME_HEADER_SLIDE_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_DRIVER_EMAIL_LBL_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_MOBILE_NUMBER_HEADER_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_TRACK_SERVICE_ASSIGNED_DRIVER_TXT']; ?></th>
                                                <th><?= $langage_lbl['LBL_Status']; ?></th>
                                                <th><?= $langage_lbl['LBL_TOTAL_TRIPS_WEB']; ?></th>
                                                <th><?= $langage_lbl['LBL_TRACK_SERVICE_SEND_INVITED_CODE']; ?></th>
                                                <th><?= $langage_lbl['LBL_DRIVER_EDIT']; ?></th>
                                                <th><?= $langage_lbl['LBL_DRIVER_DELETE']; ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $i < scount($data_drv); $i++) { ?>
                                                <tr class="gradeA">
                                                    <td><?= clearName($data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']); ?></td>
                                                    <td><?= clearEmail($data_drv[$i]['vEmail']); ?></td>
                                                    <td>
                                                        <?php if (!empty($data_drv[$i]['vPhone'])) { ?>
                                                            (+<?= $data_drv[$i]['vPhoneCode']; ?>)
                                                            <?= clearMobile($data_drv[$i]['vPhone']); ?><?php } ?>
                                                    </td>
                                                    <td><?= $data_drv[$i]['vProviderName']; ?></td>
                                                    <td><?= clearEmail($data_drv[$i]['eStatus']); ?></td>
                                                    <td><?= $data_drv[$i]['ToTalTrips'] ?></td>
                                                    <td>
                                                        <?php if ($data_drv[$i]['iUserId'] == 0) { ?>
                                                            <a onClick="sendInviteCode('<?php echo $data_drv[$i]['iTrackServiceUserId']; ?>')" class="gen-btn small-btn" href="javascript:void(0);" style="text-align: center;">
                                                                <?= $langage_lbl['LBL_TRACK_SERVICE_SEND_INVITED_CODE']; ?>
                                                            </a>
                                                        <?php }
                                                        else {
                                                            echo '--'; ?><?php } ?>
                                                    </td>
                                                    <td valign="top">
                                                        <a href="trackinguseraction?id=<?= $data_drv[$i]['iTrackServiceUserId']; ?>&action=edit" class="gen-btn small-btn">
                                                            <?= $langage_lbl['LBL_DRIVER_EDIT']; ?>
                                                        </a>
                                                    </td>
                                                    <td valign="top">
                                                        <form name="delete_form_<?= $data_drv[$i]['iTrackServiceUserId']; ?>" id="delete_form_<?= $data_drv[$i]['iTrackServiceUserId']; ?>" method="post" action="" class="margin0">
                                                            <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $data_drv[$i]['iTrackServiceUserId']; ?>">
                                                            <input type="hidden" name="action" id="action" value="delete">
                                                            <button type="submit" class="gen-btn small-btn del_btn" onClick="confirm_delete('<?= $data_drv[$i]['iTrackServiceUserId']; ?>');">
                                                                <?= $langage_lbl['LBL_DRIVER_DELETE']; ?>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php } ?>
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
                document.getElementById("delete_form_" + id).submit();
            }
            $(".del_btn").removeAttr("disabled", "disabled");
        });
        $(".del_btn").removeAttr("disabled", "disabled");
        return;
    }

    function add_driver_form() {
        window.location.href = "trackinguseraction";
    }

    function sendInviteCode(iTrackServiceUserId) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_track_service_company_driver.php',
            'AJAX_DATA': {
                module: 'track_service_send_invite_code',
                iTrackServiceUserId: iTrackServiceUserId,
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            show_alert("", "Invite Code Sent.", "", "", "<?= $langage_lbl['LBL_BTN_OK_TXT'] ?>");
        });
    }
</script>
<script type="text/javascript">
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