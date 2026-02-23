<?php
include_once('../common.php');
require_once(TPATH_CLASS . "Imagecrop.class.php");

$tbl_name = 'gift_card_images';
$script = 'GiftCardImages';

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = $_REQUEST['id'] ?? '';

$action = ($id != '') ? 'Edit' : 'Add';

$success = $_REQUEST['success'] ?? '';
$vCodeLang = $_REQUEST['vCode'] ?? $default_lang;

$count_all = 1;
$vImage = $_POST['vImage_old'] ?? '';
$eStatus_check = $_POST['eStatus'] ?? 'off';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();
$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name . " WHERE 1 AND vCode = '" . $vCodeLang . "'");

$iDisplayOrder = $select_order[0]['iDisplayOrder'] ?? 0;
$iDisplayOrder = $nxtDispNo = $iDisplayOrder + 1; // Maximum order number
$serviceCatArr = json_decode(serviceCategories, true);
$getLangData = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active'");
$iDisplayOrder = $_POST['iDisplayOrder'] ?? $iDisplayOrder;
$temp_order = $_POST['temp_order'] ?? "";
$iLocationId = $_POST['iLocationId'] ?? '-1';

/**
 * @param string $action
 * @param $userObj
 * @param string $eBuyAnyServiceReq
 * @return void
 */
function extracted(string $action, $userObj)
{
    if ($action == "Add" && !$userObj->hasPermission('create-giftcard-image')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create banner.';
        header("Location:gift_card_images.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-giftcard-image')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update banner.';
        header("Location:gift_card_images.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:gift_card_images.php");
        exit;
    }
}

if (isset($_POST['submit'])) {
    extracted($action, $userObj);

    if($eStatus == "Inactive"){
        $sql1 = "SELECT COUNT(iGiftCardImageId) as totalgiftcards FROM $tbl_name WHERE  1=1 AND eStatus = 'Active' AND vCode = '" . $vCodeLang . "'";
        $data = $obj->MySQLSelect($sql1);
        $totalgiftcards = $data[0]['totalgiftcards'];

        if($totalgiftcards <= 1){
            $_SESSION['success'] = '2';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_GIFT_CARD_INACTIVE_ERROR_MSG'];
            header("Location:gift_card_images.php");
            exit;
        }
    }

    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE iDisplayOrder = " . $i . " AND vCode = '" . $vCodeLang . "'");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $setOrder = $i - 1;
            if ($i == 1) {
                $setOrder = $nxtDispNo;
            }
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . $setOrder . " WHERE iDisplayOrder = " . $i . " AND vCode = '" . $vCodeLang . "'");
        }
    }

    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $q = "INSERT INTO ";
            $where = '';
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iGiftCardImageId` = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
            }
            if (!empty($id) && !empty($vCodeLang)) {
                $sqlrecord = "SELECT eStatus,vImage,iDisplayOrder,vCode FROM " . $tbl_name . " WHERE iGiftCardImageId = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
                $db_records = $obj->MySQLSelect($sqlrecord);
                if (empty($db_records)) {
                    $q = "INSERT INTO ";
                    $where = '';
                }
            }
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            if ($image_name != "") {
                // $filecheck = basename($_FILES['vImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
                // }

                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                if($error){
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = $error;
                    header("Location:gift_card_images.php");
                    exit;
                }

                $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
                $image_width = $image_info[0];
                $image_height = $image_info[1];
                if ($flag_error == 1) {
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = $var_msg;
                    header("Location:gift_card_images.php");
                    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_images_gift_card_path"] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                    $vImage = $img[0];
                }
            }

            $dAddedDate = date('Y-m-d H:i:s');
            $query = $q . " `" . $tbl_name . "` SET 	
					`vImage` = '" . $vImage . "',
					`eStatus` = '" . $eStatus . "',
					`iDisplayOrder` = '" . $iDisplayOrder . "',
                    `vCode` = '" . $vCodeLang . "',
                    `dAddedDate` = '" . $dAddedDate . "',
                    `iLocationid` = '" . $iLocationId . "'" . $where;
            $obj->sql_query($query);
            if ($id != '') {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            }
        }

        if(!empty($OPTIMIZE_DATA_OBJ)) {
            $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
        }

        updateSystemData();
        header("Location:gift_card_images.php");
        exit();
    }
}

// for Edit
if ($action == 'Edit') {
    //$vCodeLang = !empty($vCodeLang) ? $vCodeLang : $default_lang;
    $sql = "SELECT eStatus,vImage,iDisplayOrder,vCode,iLocationid FROM " . $tbl_name . " WHERE iGiftCardImageId = '" . $id . "' and vCode = '" . $vCodeLang . "'";

    $db_data = $obj->MySQLSelect($sql);


    $iGiftCardImageId = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $eStatus = $value['eStatus'];
            $vImage = $value['vImage'];
            $iDisplayOrder = $value['iDisplayOrder'];
            $vCodeLang = $value['vCode'];
            $iLocationId = $value['iLocationid'];
        }
    }
}

$sql_location = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'Banner' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sql_location);
?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title>Admin | Banner <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
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
                    <h2><?= $action; ?> EGV Design Theme</h2>
                    <a href="gift_card_images.php">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 0 && $_REQUEST['var_msg'] != "") { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <? echo $_REQUEST['var_msg']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <form method="post" id="GiftCardImages" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                        <div class="row">
                            <?php if ($action == "Add") { ?>
                                <div class="col-lg-12">
                                    <label>Select Language</label>
                                </div>
                                <div class="col-lg-6">
                                    <select  class="form-control" name = 'vCode'  id= 'vCode' onchange="bannerdata(this.value)">
                                        <?php for ($l = 0; $l < scount($getLangData); $l++) { ?>
                                            <option <?php if ($vCodeLang == $getLangData[$l]['vCode']) { ?>selected=""<?php } ?> value = "<?= $getLangData[$l]['vCode']; ?>"><?= $getLangData[$l]['vTitle']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <? } else { ?>
                                <div class="col-lg-12">
                                    <label>Language: <?= $vCodeLang ?></label>
                                </div>
                                <input type="hidden" name="vCode" value="<?= $vCodeLang ?>">
                            <? } ?>
                        </div>
                        <div class="bannerlang">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Image<?= ($vImage == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                </div>
                                <div class="col-lg-6">
                                    <?php if ($vImage != '') { ?>
                                        <img id="bannerlang" src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=300&src=' . $tconfig['tsite_upload_images_gift_card'] .'/'. $vImage; ?>">
                                        <input type="file" class="form-control" name="vImage" id="vImage" value="<?= $vImage; ?>"/>
                                    <?php } else { ?>
                                        <input type="file" class="form-control" name="vImage" id="vImage" value="<?= $vImage; ?>"/>
                                    <?php } ?>

                                    <?php if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV3()) { ?>
                                        <span class="notes">[Note: Recommended dimension for banner image is 1050px X 450px.]</span>
                                    <?php } else { ?>
                                       <span class="notes"> [Note: Recommended dimension for banner image is 2880px X 1620px.]</span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox"
                                           name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Order</label>
                            </div>
                            <div class="col-lg-6">
                                        <span id="orderdiv">
                                        <?php
                                        $temp = 1;
                                        $dataArray = array();
                                        $query1 = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE 1  AND vCode = '$vCodeLang' ORDER BY iDisplayOrder";

                                        $data_order = $obj->MySQLSelect($query1);
                                        foreach ($data_order as $value) {
                                            $dataArray[] = $value['iDisplayOrder'];
                                            $temp = $iDisplayOrder;
                                        }
                                        ?>
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp ?>">
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php foreach ($dataArray as $arr): ?>
                                                <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>">
                                                    -- <?= $arr ?> --
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if ($action == "Add") { ?>
                                                <option value="<?= $temp; ?>">
                                                    -- <?= $temp ?> --
                                                </option>
                                            <?php } ?>
                                        </select>
                                        </span>
                            </div>
                        </div>
                        <div class="row">
                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-giftcard-image')) || ($action == 'Add' && $userObj->hasPermission('create-giftcard-image'))) { ?>
                                <div class="col-lg-12">
                                    <input type="submit" class="save btn-info" name="submit" id="submit"
                                           value="<?= $action; ?> Image">
                                    <a href="gift_card_images.php" class="btn btn-default back_link">Cancel</a>
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    function bannerdata(val) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>banner_lang.php',
            'AJAX_DATA': {
                vCode: val,
                id: '<?= isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; ?>',
                order: 'Yes',
                eBuyAnyService: '<?= isset($eBuyAnyService) ?  $eBuyAnyService : ''?>',
                eForService: '<?= isset($eForService) ?  $eBuyAnyService : '' ?>'
            },
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml2 = response.result;
                if (dataHtml2 != "") {
                    $('#orderdiv').html(dataHtml2);
                    //$('.bannerlang').html(dataHtml2);
                }
            } else {
                //console.log(response.result);
            }
        });
    }
    //console.log(_system_script);

    if (_system_script == 'GiftCardImages') {
        if ($('#GiftCardImages').length !== 0) {
            $('#GiftCardImages').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {required: true},
                    vImage: {
                        required: function(element) {
                            if($("#bannerlang") && $("#bannerlang").attr('src'))
                            {
                                return false;
                            } else {
                                return true;
                            }
                        },
                        extension: imageUploadingExtenstionjsrule
                    }
                },
                messages: {
                    vTitle_Default: { required: 'This field is required.'},
                    vImage: {extension: imageUploadingExtenstionMsg}
                    
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
</script>
</body>
<!-- END BODY-->
</html>