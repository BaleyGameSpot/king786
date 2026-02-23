<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-giftcard-image')) {
    $userObj->redirect();
}



$script = 'GiftCardImages';
$tblname = 'gift_card_images';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$iGiftCardImageId = isset($_REQUEST['iGiftCardImageId']) ? $_REQUEST['iGiftCardImageId'] : "";
$flag = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : "";
$langSearch = $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$vCodeLang = $_REQUEST['vCode'] ?? $default_lang;
if (!empty($_REQUEST['langSearch'])) {

    $langSearch = $vCodeLang = $_REQUEST['langSearch'];
}
$ssql = '';
if ($eStatus != '') {
    $ssql .= " AND eStatus = '" . $eStatus . "'";
}

if ($vCodeLang != '') {
    $ssql .= " AND vCode = '" . $vCodeLang . "'";
}

/*--------------------- ordering ------------------*/

if (!empty($id) && $id != 0) {
    if (SITE_TYPE != 'Demo') {
        if ($flag == 'up') {
            $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tblname . " WHERE iGiftCardImageId ='" . $id . "' AND vCode = '" . $vCodeLang . "'");
            $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
            $val = $order_data - 1;
            if ($val > 0) {
                $obj->sql_query("UPDATE " . $tblname . " SET iDisplayOrder='" . $order_data . "' WHERE iDisplayOrder='" . $val . "' AND vCode = '" . $vCodeLang . "'");
                $obj->sql_query("UPDATE " . $tblname . " SET iDisplayOrder='" . $val . "' WHERE iGiftCardImageId = '" . $id . "' AND vCode = '" . $vCodeLang . "'");
            }
        } else if ($flag == 'down') {
            $sel_order = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tblname . " WHERE iGiftCardImageId ='" . $id . "' AND vCode = '" . $vCodeLang . "'");
            $order_data = isset($sel_order[0]['iDisplayOrder']) ? $sel_order[0]['iDisplayOrder'] : 0;
            $val = $order_data + 1;
            $obj->sql_query("UPDATE " . $tblname . " SET iDisplayOrder='" . $order_data . "' WHERE iDisplayOrder='" . $val . "' AND vCode = '" . $vCodeLang . "' ");
            $obj->sql_query("UPDATE " . $tblname . " SET iDisplayOrder='" . $val . "' WHERE iGiftCardImageId = '" . $id . "' AND vCode = '" . $vCodeLang . "'");
        }

        if(!empty($OPTIMIZE_DATA_OBJ)) {
            $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
        }
        header("Location:gift_card_images.php?langSearch=" . $vCodeLang);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:gift_card_images.php?langSearch=" . $vCodeLang);
        exit();
    }
}

/*--------------------- ordering ------------------*/

/*--------------------- status ------------------*/
if ($iGiftCardImageId != '' && $eStatus != '') {
    if (SITE_TYPE != 'Demo') {
        if($eStatus == "Inactive"){
            $sql1 = "SELECT COUNT(iGiftCardImageId) as totalgiftcards FROM $tblname WHERE  1=1 AND eStatus = 'Active' AND vCode = '" . $vCodeLang . "'";
            $data = $obj->MySQLSelect($sql1);
            $totalgiftcards = $data[0]['totalgiftcards'];
           if($totalgiftcards <= 1){
                $_SESSION['success'] = '2';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_GIFT_CARD_INACTIVE_ERROR_MSG'];
                header("Location:gift_card_images.php");
                exit;
            }
        }
        $query = "UPDATE `" . $tblname . "` SET eStatus = '" . $eStatus . "' WHERE iGiftCardImageId = '" . $iGiftCardImageId . "' AND vCode = '" . $vCodeLang . "'";
        $obj->sql_query($query);
        $_SESSION['success'] = '1';
        if($eStatus == "Inactive"){
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
        }

        if(!empty($OPTIMIZE_DATA_OBJ)) {
            $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
        }

        header("Location:gift_card_images.php?langSearch=" . $vCodeLang);
        exit;
    } else {
        $_SESSION['success'] = '2';
        header("Location:gift_card_images.php?langSearch=" . $vCodeLang);
        exit();
    }
}

/*--------------------- status ------------------*/
/*--------------------- delete ------------------*/
$hdn_del_id = $_REQUEST['hdn_del_id'] ?? '';
$vCodeDlt = $_REQUEST['vCode'] ?? '';

if ($hdn_del_id != '') {
    if (SITE_TYPE != 'Demo') {

        $sql1 = "SELECT COUNT(iGiftCardImageId) as totalgiftcards FROM $tblname WHERE  1=1 AND eStatus != 'Deleted' AND vCode = '" . $vCodeLang . "'";
        $data = $obj->MySQLSelect($sql1);
        $totalgiftcards = $data[0]['totalgiftcards'];
        if($totalgiftcards <= 1){
            $_SESSION['success'] = '2';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_GIFT_CARD_DELETE_ERROR_MSG'];
            header("Location:gift_card_images.php");
            exit;
        }
        
        $data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `" . $tblname . "` WHERE 1=1 AND vCode = '" . $vCodeLang . "'";
        $data_rec = $obj->MySQLSelect($data_q);
        $order = $data_rec[0]['iDisplayOrder'] ?? 0;
        $data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tblname . " WHERE iGiftCardImageId = '" . $hdn_del_id . "' AND vCode = '" . $vCodeLang . "'");

        if (scount($data_logo) > 0) {
            $iDisplayOrder = $data_logo[0]['iDisplayOrder'] ?? '';
            //$obj->sql_query("DELETE FROM `" . $tblname . "` WHERE iGiftCardImageId = '" . $hdn_del_id . "' AND vCode = '" . $vCodeLang . "'");
            $obj->sql_query("UPDATE `gift_card_images` SET `eStatus` = 'Deleted' WHERE iGiftCardImageId = '" . $hdn_del_id . "' AND vCode = '" . $vCodeLang . "' ");
            if ($iDisplayOrder < $order) {
                for ($i = $iDisplayOrder + 1; $i <= $order; $i++) {

                    $obj->sql_query("UPDATE " . $tblname . " SET iDisplayOrder = " . ($i - 1) . " WHERE eStatus != 'Deleted' AND iDisplayOrder = " . $i . " AND vCode = '" . $vCodeLang . "'");
                }
            }
        }

        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];

        if(!empty($OPTIMIZE_DATA_OBJ)) {
            $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
        }
    
        header("Location:gift_card_images.php?langSearch=" . $vCodeLang);
        exit();

    } else {
        $_SESSION['success'] = '2';
        header("Location:gift_card_images.php?langSearch=" . $vCodeLang);
        exit();
    }
}
/*--------------------- delete ------------------*/

$whereArray =  array(
        'vCode' => array( '=' , $vCodeLang ),
        'eStatus' => array( 'IN' , "('Active' , 'inactive')" ),
);
correctGiftCardImagesDisplayOrder('gift_card_images' , 'iDisplayOrder' ,$whereArray);

$sql = "SELECT * FROM $tblname WHERE  1=1  {$ssql} AND eStatus != 'Deleted' ORDER BY iDisplayOrder = 0 ASC ,iDisplayOrder ASC";

$data = $obj->MySQLSelect($sql);
$maxDisplayOrderData = $obj->MySQLSelect("SELECT max(iDisplayOrder) as maxDisplayOrder FROM $tblname");
$maxDisplayOrder = $maxDisplayOrderData[0]['maxDisplayOrder'] + 1;
$db_langdata = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active' ORDER BY iDispOrder");
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | EGV Design Theme</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>EGV Design Theme</h2>
                        <?php if ($userObj->hasPermission(['create-giftcard-image', 'edit-giftcard-image'])) {

                            if (!empty($langSearch)) {

                                if (!empty($eBuyAnyService)) {

                                    $add_banner = "&vCode=" . $langSearch;
                                } else {

                                    $add_banner = "?vCode=" . $langSearch;
                                }
                            }

                            ?>
                            <a href="gift_card_image_action.php<?= $add_banner ?>">
                                <input type="button" value="ADD Image" class="add-btn">
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="" method="GET">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <?php if ($userObj->hasPermission('update-status-giftcard-image')) { ?>
                        <td width="12%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?> >Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?> >Inactive
                                </option>
                                <?php /*<option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?> >Delete
                                </option>*/ ?>
                            </select>
                        </td>
                        <?php } ?>
                        <td>
                            <select name="langSearch" class="form-control">
                                <? foreach ($db_langdata as $key => $value) { ?>
                                    <option value="<?= $value['vCode'] ?>" <? if ($value['vCode'] == $langSearch) echo "selected"; ?>><?= $value['vTitle'] ?></option>
                                <? } ?>
                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'gift_card_images.php'"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                EGV Design
                            </div>
                            <div class="panel-body">
                                <div>
                                    <table class="table responsive table-striped table-bordered table-hover" id="dataTables-example">
                                        <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th style="text-align: center; width: 10%">Language</th>
                                            <th style="text-align: center; width: 10%" >Order</th>
                                            <!-- <?php if ($userObj->hasPermission('update-status-giftcard-image')) { ?>
                                            <th style="text-align: center; width: 10%" >Status</th>
                                            <?php } if ($userObj->hasPermission('edit-giftcard-image')) { ?>
                                            <th style="text-align: center; width: 10%" >Edit</th>
                                            <?php } if ($userObj->hasPermission('delete-giftcard-image')) { ?>
                                            <th style="text-align: center; width: 10%" >Delete</th>
                                            <?php } ?> -->
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $count_all = scount($data);

                                        if ($count_all > 0) {
                                            $langsql = " AND vCode = '" . $langSearch . "'";

                                            $db_dataCnt = $obj->MySQLSelect("SELECT * FROM " . $tblname . " WHERE 1=1 $langsql AND eStatus != 'Deleted' ");
                                            $countData = scount($db_dataCnt);

                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vImage = $data[$i]['vImage'];
                                                $vCode = $data[$i]['vCode'];
                                                $iDisplayOrder = $data[$i]['iDisplayOrder'];
                                                $eStatus = $data[$i]['eStatus'];
                                                $iUniqueId = $data[$i]['iGiftCardImageId'];
                                                $checked = ($eStatus == "Active") ? 'checked' : ''; ?>
                                                <tr class="gradeA">
                                                    <td width="10%" align="center">
                                                        <?php if ($vImage != '' && file_exists($tconfig['tsite_upload_images_gift_card_path'] . '/' . $vImage)) { ?>
                                                            <img src="<?php echo $tconfig["tsite_url"] . 'resizeImg.php?w=100&src=' . $tconfig["tsite_upload_images_gift_card"] . '/' . $vImage; ?>">
                                                            <?php
                                                        } else {
                                                            echo $vImage;
                                                        } ?>
                                                    </td>
                                                    <td style="text-align: center; width: 10%" ><?= $vCode; ?></td>
                                                    <td align="center">
                                                        <?php
                                                        if ($iDisplayOrder != 1) { ?>
                                                            <a href="gift_card_images.php?id=<?= $iUniqueId; ?>&flag=up&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?>">
                                                                <button class="btn btn-warning">
                                                                    <i class="icon-arrow-up"></i>
                                                                </button>
                                                            </a>
                                                        <?php }
                                                        if ($iDisplayOrder != $countData) { ?>
                                                            <a href="gift_card_images.php?id=<?= $iUniqueId; ?>&flag=down&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?>">
                                                                <button class="btn btn-warning">
                                                                    <i class="icon-arrow-down"></i>
                                                                </button>
                                                            </a>
                                                        <?php } ?>
                                                    </td>
                                                    <!-- <?php if ($userObj->hasPermission('update-status-giftcard-image')) { ?>
                                                        <td width="10%" align="center">
                                                            <a href="gift_card_images.php?iGiftCardImageId=<?= $iUniqueId; ?>&eStatus=<?= ($eStatus == "Active") ? 'Inactive' : 'Active' ?>&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?>">
                                                                <button class="btn">
                                                                    <i class="<?= ($eStatus == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?= $eStatus; ?>
                                                                </button>
                                                            </a>
                                                        </td>
                                                    <?php } ?>

                                                    <?php if ($userObj->hasPermission('edit-giftcard-image')) { ?>
                                                        <td width="10%" align="center">
                                                            <a href="gift_card_image_action.php?id=<?= $iUniqueId; ?>&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?>">
                                                                <button class="btn btn-primary">
                                                                    <i class="icon-pencil icon-white"></i>
                                                                    Edit
                                                                </button>
                                                            </a>
                                                        </td>
                                                    <?php } ?>

                                                    <?php if ($userObj->hasPermission('delete-giftcard-image')) { ?>
                                                        <td width="10%" align="center">
                                                            <form name="delete_form" id="delete_form" method="POST"
                                                                  action="" onsubmit="return confirm_delete()"
                                                                  class="margin0">
                                                                <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                                                       value="<?= $iUniqueId; ?>">
                                                                <input type="hidden" name="vCode" id="vCode"
                                                                       value="<?= $vCode; ?>">
                                                                <button class="btn btn-danger">
                                                                    <i class="icon-remove icon-white"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        </td>
                                                    <?php } ?> -->
                                                    <td width="10%"  align="center">
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
                                                    <td width="10%" align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class" style="display: block;">
                                                            <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                            <div class="social show-moreOptions openPops_<?= $iUniqueId; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission('edit-giftcard-image')) { ?>
                                                                    <li class="entypo-twitter" data-network="twitter"> 
                                                                        <a href="gift_card_image_action.php?id=<?= $iUniqueId; ?>&vCode=<?= $vCode ?>&langSearch=<?= $vCode ?>" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                        </a></li>
                                                                    <?php }  ?>
                                                                    <?php if ($userObj->hasPermission('update-status-giftcard-image')) { ?>

                                                                        <li class="entypo-facebook" data-network="facebook">

                                                                            <a href="javascript:void(0);" onClick='window.location.href="gift_card_images.php?iGiftCardImageId=<?= $iUniqueId; ?>&eStatus=Active&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?>"' data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png" alt="<?php echo $eStatus; ?>">
                                                                            </a>
                                                                        </li>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);" onClick='window.location.href="gift_card_images.php?iGiftCardImageId=<?= $iUniqueId; ?>&eStatus=Inactive&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?>"' data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>">
                                                                            </a>

                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($userObj->hasPermission('delete-giftcard-image')) {  ?>
                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="confirm_delete('<?= $iUniqueId; ?>','<?= $vCode; ?>');" data-toggle="tooltip"  title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete">
                                                                            </a></li>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php }
                                        } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php //include('pagination_n.php'); ?>
                            </div>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li> Administrator can Activate / Deactivate / Delete any EGV Design Theme.</li>
                </ul>
            </div>
            <div class="modal fade" id="add_edit_gift_image_modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content nimot-class">
                        <div class="modal-header">
                            <h4>
                                <span id="action"></span>
                                Image</span>
                                <button type="button" class="close" data-dismiss="modal">x</button>
                            </h4>
                        </div>
                        <form class="form-horizontal" id="add_edit_gift_card_image_form" method="POST"
                              enctype="multipart/form-data" action="">
                            <input type="hidden" id="iGiftCardImagesId" name="iGiftCardImagesId" value="">
                            <div class="col-lg-12">
                                <img width="300px" src="" id="giftCardImage">
                            </div>
                            <div class="col-lg-12">
                                <div class="input-group input-append">
                                    <div class="ddtt" style="margin-top: 10px">
                                        <h4>Image Upload</h4>
                                        <input required onchange="previewFile(this);" type="file" name="vImage"
                                               id="vImage" class="form-control iAmount" style="margin-top:5px">
                                        <input type="Hidden" name="vOldImage" id="vOldImage"
                                               class="form-control iAmount" style="margin-top:5px">
                                    </div>
                                    <div id="iLimitmsg" style="margin-bottom: 10px"></div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="input-group input-append">
                                    <div class="ddtt" style="margin-top: 10px">
                                        <h4>Display Order</h4>
                                        <select name="iDisplayOrder" id="iDisplayOrder" class="form-control">
                                            <!-- <option value="0">0</option>-->
                                            <?php for ($i = 1; $i <= $maxDisplayOrder; $i++) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                        <input type="hidden" name="oldDisplayOrder" id="oldDisplayOrder"
                                               value="">
                                    </div>
                                    <div id="iLimitmsg" style="margin-bottom: 10px"></div>
                                </div>
                            </div>
                            <div class="nimot-class-but" style="margin-bottom: 20px">
                                <button type="button" onClick="check_Image();" class="save" id="setting_btn"
                                        style="margin-left: 15px !important"></button>
                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<!-- <form name="pageForm" id="pageForm" action="action/gift_card_images.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iGiftCardImageId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form> -->
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#dataTables-example').dataTable({
            "order": [],
            "aoColumns": [
                {"bSortable": false},
                {"bSortable": false},
                {"bSortable": false},
                {"bSortable": false},
                {"bSortable": false}
            ],
            columnDefs: [{
                "defaultContent": "-",
                "targets": "_all"
            }]
        });
    });
    $('#add_gift_card_image, .edit-gift_card_image').click(function () {
        $("#loaderIcon").show();
        $('#previewImg').hide();
        $('#vImage').val('');
        $('#giftCardImage').attr('src', '');
        $('#previewImg').attr('src', '');
        var action = $(this).data('action');
        var iGiftCardImagesId = $(this).data('id');
        var image = $(this).data('image');
        var iDisplayOrder = $(this).data('idisplayorder');
        $('#action').text(action);
        $('#setting_btn').text(action);
        $('#giftCardImage').attr('src', image);
        $('#vOldImage').val(image);
        $('#iGiftCardImagesId').val(iGiftCardImagesId);
        $('#oldDisplayOrder').val(iDisplayOrder);
        setTimeout(function () {
            $('#add_edit_gift_image_modal').modal('show');
            console.log(image);
            if (image == '' || image == undefined) {
                $('#giftCardImage').hide();
            } else {
                $('#giftCardImage').show();
            }
            if (action == 'Add') {
                $('#iDisplayOrder').val(<?php echo $maxDisplayOrder; ?>);
            } else {
                $('#iDisplayOrder').val(iDisplayOrder);
            }
            $("#loaderIcon").hide();
        }, 500);
    });
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

    function check_Image() {
        var vImage = $("#vImage").val();
        var vOldImage = $("#vOldImage").val();
        if (vImage == '' && vOldImage == '') {
            alert("Please Upload Image");
            return false;
        } else {
            $('#add_edit_gift_card_image_form').submit();
        }
    }

    function previewFile(input) {
        var file = $("input[type=file]").get(0).files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function () {
                $("#giftCardImage").show();
                $("#giftCardImage").attr("src", reader.result);
            }
            reader.readAsDataURL(file);
        }
    }

    $("#Search").on('click', function () {
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });

    function confirm_delete(iUniqueId,vCode) {
        var confirm_ans = confirm("Are You sure You want to Delete Banner?");

        if (confirm_ans == true) {
            window.location.href = 'gift_card_images.php?hdn_del_id='+iUniqueId+'&vCode='+vCode;
        }
    }
</script>
</body>
<!-- END BODY-->
</html>