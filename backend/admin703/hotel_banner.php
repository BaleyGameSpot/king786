<?
include_once '../common.php';
$permission_banner = "hotel-banner";

$permission_banner_view = "view-".$permission_banner;
$permission_banner_create = "create-".$permission_banner;
$permission_banner_edit = "edit-".$permission_banner;
$permission_banner_delete = "delete-".$permission_banner;
$permission_banner_update_status = "update-status-".$permission_banner;

if (!$userObj->hasPermission($permission_banner_view)) {
    $userObj->redirect();
}
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
//Delete
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
// Update eStatus
$iUniqueId = isset($_GET['iUniqueId']) ? $_GET['iUniqueId'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$select_lang = isset($_REQUEST['selectlang']) ? stripslashes($_REQUEST['selectlang']) : '';
//sort order
$flag = isset($_GET['flag']) ? $_GET['flag'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$languages = $obj->MySQLSelect("SELECT * FROM language_master WHERE eStatus='Active' ORDER BY iDispOrder");
$tbl_name = 'hotel_banners';
$script = 'hotel_banners';
//delete record
$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
$ParameterUrl =  'selectlang=' . $vCodeLang;
$wherelang = '';
if($select_lang != "") {
    $wherelang .= " AND vCode = '".$select_lang."'";    
} else {
    $select_lang = $vCodeLang;
    $wherelang .= " AND vCode = '".$select_lang."'";    
}
if ($hdn_del_id != '') {
    if (SITE_TYPE != 'Demo') {
        $data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `" . $tbl_name . "` WHERE 1 = 1 $wherelang";
        $data_rec = $obj->MySQLSelect($data_q);
        $order = isset($data_rec[0]['iDisplayOrder']) ? $data_rec[0]['iDisplayOrder'] : 0;
        $data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId = '" . $hdn_del_id . "' $wherelang");
        if (scount($data_logo) > 0) {
            $iDisplayOrder = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
            $obj->sql_query("DELETE FROM `" . $tbl_name . "` WHERE iUniqueId = '" . $hdn_del_id . "' $wherelang");
            if ($iDisplayOrder < $order) {
                for ($i = $iDisplayOrder + 1; $i <= $order; $i++) {
                    $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i . " $wherelang ");
                }
            }
        }
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        header("Location:hotel_banner.php?".$ParameterUrl);
        exit();
    } else {
        $_SESSION['success'] = '2';
        header("Location:hotel_banner.php?".$ParameterUrl);
        exit();
    }
}
if (!empty($id) && $id != 0) {
    if ($flag == 'up') {
        $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId ='" . $id . "' $wherelang");
        $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
        $val = $order_data - 1;
        if ($val > 0) {
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $order_data . "' WHERE iDisplayOrder='" . $val . "' $wherelang");
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $val . "' WHERE iUniqueId = '" . $id . "' $wherelang");
        }
    } else if ($flag == 'down') {
        $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId ='" . $id . "' $wherelang");
        $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
        $val = $order_data + 1;
        $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $order_data . "' WHERE iDisplayOrder='" . $val . "' $wherelang");
        $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder='" . $val . "' WHERE iUniqueId = '" . $id . "' $wherelang");
    }
    header("Location:hotel_banner.php?".$ParameterUrl);
    exit();
}
if ($iUniqueId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE `" . $tbl_name . "` SET eStatus = '" . $status . "' WHERE iUniqueId = '" . $iUniqueId . "'";
        $obj->sql_query($query);
    } else {
        $_SESSION['success'] = '2';
        header("Location:hotel_banner.php?".$ParameterUrl);
        exit();
    }
}
$sql = "SELECT * FROM " . $tbl_name . " WHERE 1 = 1 $wherelang ORDER BY iDisplayOrder";
$db_data = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Hotel Banners</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <? include_once 'global_files.php'; ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once 'header.php'; ?>

    <? include_once 'left_menu.php'; ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Hotel Banner</h2>
                    <?php if ($userObj->hasPermission($permission_banner_create)) { ?>
                        <a href="hotel_banner_action.php?vCode=<?php echo $select_lang;?>">
                            <input type="button" value="Add Hotel Banner" class="add-btn">
                        </a>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <?php include 'valid_msg.php'; ?>
            <?php if (scount($languages) > 1) { ?>
                <form name="frmsearch" id="frmsearch" action="" >
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                            <tr>
                                <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                               
                                <td width="200px" class="estatus_options">
                                    <select name="selectlang" id="selectlang" class="form-control">
                                        <option value="" disabled>Select Language</option>
                                        <?php foreach ($languages as $lang) { ?>
                                        <option value="<?= $lang['vCode'] ?>" <?php
                                            if ($select_lang == $lang['vCode']) {
                                                echo "selected";
                                            }
                                            ?> > <?= $lang['vTitle'] . ' (' . $lang['vCode'] . ')'; ?></option>
                                        <?php } ?>
                                    </select>
                                </td>
                        
                                <td>
                                  <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                  <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='hotel_banner.php'"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                  </form>
            <?php } ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Hotel Banner
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover"  id="dataTables-example" style="width: 100%;">
                                        <thead>
                                        <tr>
                                            <th class="text-center"  width="15%">Image</th>
                                            <th width="25%">Title</th>
                                            <th width="15%" class="text-center" >Language</th>
                                            <th width="15%" class="text-center">Order</th>
                                            <!-- <?php if ($userObj->hasPermission('update-status-hotel-banner')) { ?>
                                                <th>Status</th>
                                            <?php } ?>

                                            <?php if ($userObj->hasPermission('edit-hotel-banner')) { ?>
                                                <th>Edit</th>
                                            <?php } ?>

                                            <?php if ($userObj->hasPermission('delete-hotel-banner')) { ?>
                                                <th>Delete</th>
                                            <?php } ?> -->
                                            <th width="15%" class="text-center">Status</th>
                                            <th width="15%" class="text-center">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?
                                        $count_all = scount($db_data);
                                        if ($count_all > 0) {
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vTitle = $db_data[$i]['vTitle'];
                                                $vImage = $db_data[$i]['vImage'];
                                                $vCode = $db_data[$i]['vCode'];
                                                $iDisplayOrder = $db_data[$i]['iDisplayOrder'];
                                                $eStatus = $db_data[$i]['eStatus'];
                                                $iUniqueId = $db_data[$i]['iUniqueId'];
                                                $checked = ($eStatus == "Active") ? 'checked' : '';
                                                ?>
                                                <tr class="gradeA">
                                                    <td align="center">
                                                        <? if ($vImage != '' && file_exists($tconfig['tsite_upload_images_hotel_banner_path'] . '/' . $vImage)) { ?>
                                                            <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=50&src=' . $tconfig['tsite_upload_images_hotel_banner'] . '/' . $vImage ?>">
                                                        <? } else {
                                                            echo $vImage;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?= $vTitle; ?></td>
                                                    <td class="text-center" ><?= $vCode; ?></td>
                                                    <td align="center">
                                                        <? if ($iDisplayOrder != 1) { ?>
                                                            <a href="hotel_banner.php?id=<?= $iUniqueId; ?>&flag=up&vCode=<?= $vCode ?>">
                                                                <button class="btn btn-warning">
                                                                    <i class="icon-arrow-up"></i>
                                                                </button>
                                                            </a>
                                                        <?
                                                        }
                                                        if ($iDisplayOrder != $count_all) { ?>
                                                            <a href="hotel_banner.php?id=<?= $iUniqueId; ?>&flag=down&vCode=<?= $vCode ?>">
                                                                <button class="btn btn-warning">
                                                                    <i class="icon-arrow-down"></i>
                                                                </button>
                                                            </a>
                                                        <?
                                                        } ?>
                                                    </td>
                                                    <!-- <?php if ($userObj->hasPermission('update-status-hotel-banner')) { ?>
                                                        <td width="10%" align="center">
                                                            <a href="hotel_banner.php?iUniqueId=<?= $iUniqueId; ?>&status=<?= ($eStatus == "Active") ? 'Inactive' : 'Active' ?>">
                                                                <button class="btn">
                                                                    <i class="<?= ($eStatus == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?= $eStatus; ?>
                                                                </button>
                                                            </a>
                                                        </td>
                                                    <?php } ?>

                                                    <?php if ($userObj->hasPermission('edit-hotel-banner')) { ?>
                                                        <td width="10%" align="center">
                                                            <a href="hotel_banner_action.php?id=<?= $iUniqueId; ?>">
                                                                <button class="btn btn-primary">
                                                                    <i class="icon-pencil icon-white"></i>
                                                                    Edit
                                                                </button>
                                                            </a>
                                                        </td>
                                                    <?php } ?>

                                                    <?php if ($userObj->hasPermission('delete-hotel-banner')) { ?>
                                                        <td width="10%" align="center">
                                                           
                                                            <form name="delete_form" id="delete_form" method="post"
                                                                  action="" onsubmit="return confirm_delete()"
                                                                  class="margin0">
                                                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                                                       value="<?= $iUniqueId; ?>">
                                                                <button class="btn btn-danger">
                                                                    <i class="icon-remove icon-white"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </td>
                                                    <?php } ?> -->
                                                    <td align="center">
                                                        <?
                                                        if ($eStatus == 'Active') {
                                                            $dis_img = "img/active-icon.png";
                                                        } else if ($eStatus == 'Inactive') {
                                                            $dis_img = "img/inactive-icon.png";
                                                        } else if ($eStatus == 'Deleted') {
                                                            $dis_img = "img/delete-icon.png";
                                                        }
                                                        ?>
                                                        <img src="<?= $dis_img; ?>" alt="<?= $eStatus; ?>" data-toggle="tooltip" title="<?= $eStatus; ?>">
                                                    </td>
                                                     <td align="center" style="text-align:center;" class="action-btn001">
                                                            <div class="share-button openHoverAction-class" style="display: block;">
                                                                <label class="entypo-export"><span><img src="images/settings-icon.png"  alt=""></span></label>
                                                                <div class="social show-moreOptions openPops_<?= $iUniqueId; ?>">
                                                                    <ul>
                                                                        <?php if ($userObj->hasPermission($permission_banner_edit)) { ?>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a href="hotel_banner_action.php?id=<?= $iUniqueId; ?>&vCode=<?= $vCode ?>" data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                            </a></li>
                                                                        <?php }  ?>
                                                                        <?php if ($userObj->hasPermission($permission_banner_update_status)) { ?>
                                                                            <li class="entypo-facebook" data-network="facebook">
                                                                                <a href="javascript:void(0);" onClick='window.location.href="hotel_banner.php?iUniqueId=<?= $iUniqueId; ?>&status=Active&vCode=<?= $vCode ?>"' data-toggle="tooltip" title="Activate">
                                                                                    <img src="img/active-icon.png" alt="<?php echo $eStatus; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus" data-network="gplus">
                                                                                <a href="javascript:void(0);" onClick='window.location.href="hotel_banner.php?iUniqueId=<?= $iUniqueId; ?>&status=Inactive&vCode=<?= $vCode ?>"' data-toggle="tooltip" title="Deactivate">
                                                                                    <img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>">
                                                                                </a>

                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission($permission_banner_delete)) {  ?>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="confirm_delete('<?= $iUniqueId; ?>','<?= $vCode ?>');" data-toggle="tooltip"  title="Delete">
                                                                                    <img src="img/delete-icon.png" alt="Delete">
                                                                                </a></li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                </tr>
                                            <?
                                            }
                                        } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<? include_once 'footer.php'; ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script>
    /*$(document).ready(function () {

        $('#dataTables-example').dataTable({"bSort": false,autoWidth: true});

	});*/
    $('.entypo-export').click(function (e) {
        e.stopPropagation();
        var $this = $(this).parent().find('div');
        $(".openHoverAction-class div").not($this).removeClass('active');
        $this.toggleClass('active');
    });
    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
        }
    });
    function confirm_delete(iUniqueId,vCode) {

        var confirm_ans = confirm("Are You sure You want to Delete Banner?");

        if (confirm_ans == true) {
            window.location.href = 'hotel_banner.php?hdn_del_id='+iUniqueId+'&vCode='+vCode;
        }
    }
	</script>

</body>

<!-- END BODY-->

</html>

