<?php
include_once('../common.php');
if (!$MODULES_OBJ->isEnableRideDeliveryV1() || !$userObj->hasPermission('manage-app-banner-info')) {
    $userObj->redirect();
}
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
//Delete
$hdn_del_id = isset($_POST['hdn_del_id']) ? $_POST['hdn_del_id'] : '';
// Update eStatus
$iBannerId = $id = isset($_GET['id']) ? $_GET['id'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
//sort order
$flag = isset($_GET['flag']) ? $_GET['flag'] : '';
$tbl_name = 'app_banner_info';
$script = 'app_banner_info';
$ssql_deliverall = "";
//delete record
if ($hdn_del_id != '') {
    $vImage = isset($_POST['vImage']) ? $_POST['vImage'] : '';
    if (SITE_TYPE != 'Demo') {
        $data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `" . $tbl_name . "` WHERE iBannerId = '$hdn_del_id'" . $ssql_deliverall;
        $data_rec = $obj->MySQLSelect($data_q);
        //echo '<pre>'; print_r($data_rec); echo '</pre>';
        $order = isset($data_rec[0]['iDisplayOrder']) ? $data_rec[0]['iDisplayOrder'] : 0;
        $data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iBannerId = '" . $hdn_del_id . "'" . $ssql_deliverall);
        if (scount($data_logo) > 0) {
            $iDisplayOrder = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
            $obj->sql_query("DELETE FROM `" . $tbl_name . "` WHERE iBannerId = '" . $hdn_del_id . "'" . $ssql_deliverall);
            if (file_exists($tconfig['tsite_upload_app_launch_images_path'] . $vImage)) {
                unlink($tconfig['tsite_upload_app_launch_images_path'] . $vImage);
            }
            if ($iDisplayOrder < $order) for ($i = $iDisplayOrder + 1; $i <= $order; $i++) $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i . " " . $ssql_deliverall);
        }
    } else {
        $_SESSION['success'] = '2';
        header("Location:app_banner_info.php");
        exit();
    }
}
if ($iBannerId != 0) {
    if ($flag == 'up') {
        $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iBannerId ='" . $iBannerId . "'" . $ssql_deliverall);
        $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
        $val = $order_data - 1;
        if ($val > 0) {
            $obj->MySQLSelect("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $order_data . "' WHERE iDisplayOrder='" . $val . "'" . $ssql_deliverall);
            $obj->MySQLSelect("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $val . "' WHERE iBannerId = '" . $iBannerId . "'" . $ssql_deliverall);
        }
    } else if ($flag == 'down') {
        $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iBannerId ='" . $iBannerId . "'" . $ssql_deliverall);
        $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
        $val = $order_data + 1;
        $obj->MySQLSelect("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $order_data . "' WHERE iDisplayOrder='" . $val . "'" . $ssql_deliverall);
        $obj->MySQLSelect("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $val . "' WHERE iBannerId = '" . $iBannerId . "'" . $ssql_deliverall);
    }
    header("Location:app_banner_info.php");
}
if ($iBannerId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE `" . $tbl_name . "` SET eStatus = '" . $status . "' WHERE iBannerId = '" . $iBannerId . "'" . $ssql_deliverall;
        $obj->sql_query($query);
        header("Location:app_banner_info.php");
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:app_banner_info.php");
        exit();
    }
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$sql = "SELECT b.*, JSON_UNQUOTE(JSON_VALUE(b.tTitle, '$.tTitle_" . $default_lang . "')) as tTitle, JSON_UNQUOTE(JSON_VALUE(b.tSubtitle, '$.tSubtitle_" . $default_lang . "')) as tSubtitle, vc.vCategory_" . $default_lang . " as vCategory FROM " . $tbl_name . " as b LEFT JOIN " . $sql_vehicle_category_table_name . " as vc ON vc.iVehicleCategoryId = b.iVehicleCategoryId WHERE 1 = 1 " . $ssql_deliverall . " ORDER BY iBannerId";
$db_data = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8">
<![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9">
<![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | App Banner Info</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <script type="text/javascript">
        function confirm_delete() {
            var confirm_ans = confirm("Are You sure You want to Delete Banner?");
            return confirm_ans;
            //document.getElementById(id).submit();
        }
    </script>
    <style type="text/css">
        #dataTables-example td {
            vertical-align: middle;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>App Banner Info</h2>
                    <?php if ($userObj->hasPermission('create-app-banner-info')) { ?>
                        <a href="app_banner_info_action.php">
                            <input type="button" value="Add Banner" class="add-btn">
                        </a>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <?php include('valid_msg.php'); ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                App Banner Info
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover"
                                           id="dataTables-example" align="center">
                                        <thead>
                                        <tr>
                                            <th class="text-center">Image</th>
                                            <th class="text-center">Title</th>
                                            <th class="text-center">Subtitle</th>
                                            <th class="text-center">Service Category</th>

                                            <?php if ($userObj->hasPermission('update-status-app-banner-info')) { ?>


                                            <th class="text-center">Status</th>
                                            <?php  } ?>
                                            <?php if ($userObj->hasPermission('edit-app-banner-info')) { ?>
                                                <th class="text-center">Edit</th>
                                            <?php } ?>
                                            <?php if ($userObj->hasPermission('delete-app-banner-info')) { ?>
                                                <th class="text-center">Delete</th>
                                            <?php } ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < scount($db_data); $i++) {
                                            $iBannerId = $db_data[$i]['iBannerId'];
                                            $vImage = $db_data[$i]['vImage'];
                                            $tTitle = $db_data[$i]['tTitle'];
                                            $tSubtitle = $db_data[$i]['tSubtitle'];
                                            $vCategory = $db_data[$i]['vCategory'];
                                            $iDisplayOrder = $db_data[$i]['iDisplayOrder'];
                                            $eStatus = $db_data[$i]['eStatus'];
                                            $checked = ($eStatus == "Active") ? 'checked' : '';
                                            ?>
                                            <tr class="gradeA">
                                                <td width="10%" align="center">
                                                    <?php if ($vImage != '' && file_exists($tconfig['tsite_upload_app_banner_images_path'] . $vImage)) { ?>
                                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=150&src=' . $tconfig['tsite_upload_app_banner_images'] . $vImage; ?>"
                                                             width="50">
                                                    <?php } else echo $vImage; ?>
                                                </td>
                                                <td width="20%" align="center"><?= $tTitle; ?></td>
                                                <td align="center">
                                                    <?php if (str_word_count($tSubtitle) <= 20) {
                                                        echo $tSubtitle;
                                                    } else {
                                                        echo implode(' ', array_slice(explode(' ', $tSubtitle), 0, 20)) . " ...";
                                                    } ?>
                                                </td>
                                                <?php php/*<td width="10%" align="center">
                                                                    <?php if ($iDisplayOrder != 1) { ?>
                                                                        <a href="store_images.php?id=<?= $iUniqueId; ?>&flag=up<?= ($sid != "") ? '&'.$sid : '' ?>">
                                                                            <button class="btn btn-warning"><i class="icon-arrow-up"></i></button>
                                                                        </a>
                                                                    <?php } if ($iDisplayOrder != $count_all) { ?>
                                                                        <a href="store_images.php?id=<?= $iUniqueId; ?>&flag=down<?= ($sid != "") ? '&'.$sid : '' ?>">
                                                                            <button class="btn btn-warning"><i class="icon-arrow-down"></i></button>
                                                                        </a>
                                                                    <?php } ?>
                                                                </td>*/ ?>
                                                <td width="20%" align="center"><?= $vCategory; ?></td>
                                                <?php if ($userObj->hasPermission('update-status-app-banner-info')) { ?>
                                                <td width="10%" align="center">
                                                    <a href="app_banner_info.php?id=<?= $iBannerId; ?>&status=<?= ($eStatus == "Active") ? 'Inactive' : 'Active' ?>">
                                                        <button class="btn">
                                                            <i class="<?= ($eStatus == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?= $eStatus; ?>
                                                        </button>
                                                    </a>
                                                </td>
                                                <?php } ?>
                                                <?php if ($userObj->hasPermission('edit-app-banner-info')) { ?>
                                                <td width="10%" align="center">
                                                    <a href="app_banner_info_action.php?id=<?= $iBannerId; ?>">
                                                        <button class="btn btn-primary">
                                                            <i class="icon-pencil icon-white"></i>
                                                            Edit
                                                        </button>
                                                    </a>
                                                </td>
                                                <?php } ?>
                                                <?php if ($userObj->hasPermission('delete-app-banner-info')) { ?>
                                                <td width="10%" align="center">
                                                    <!-- <a href="languages.php?id=<?= $id; ?>&action=delete"><i class="icon-trash"></i> Delete</a>-->
                                                    <form name="delete_form" id="delete_form" method="post" action=""
                                                          onsubmit="return confirm_delete()" class="margin0">
                                                        <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                                               value="<?= $iBannerId; ?>">
                                                        <input type="hidden" name="vImage" id="vImage"
                                                               value="<?= $vImage; ?>">
                                                        <button class="btn btn-danger">
                                                            <i class="icon-remove icon-white"></i>
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                                <?php } ?>

                                            </tr>
                                        <?php }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script>
    $(document).ready(function () {
        $('#dataTables-example').dataTable({"bSort": false});
    });
</script>
</body>
<!-- END BODY-->
</html>