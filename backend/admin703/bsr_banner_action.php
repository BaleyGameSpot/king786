<?php
include_once('../common.php');
require_once(TPATH_CLASS . "Imagecrop.class.php");
$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
$permission_name = strtolower($eMasterType);
$create = "create-banner-".$permission_name;
$view = "view-banner-".$permission_name;
$edit = "edit-banner-".$permission_name;
$delete = "delete-banner-".$permission_name;
$updateStatus = "update-status-banner-".$permission_name;
$iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
$script = $eMasterType . 'Banner';
$tbl_name = 'banners';
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
$action = ($id != '') ? 'Edit' : 'Add';
$count_all = 1;
$vImage = isset($_POST['vImage_old']) ? $_POST['vImage_old'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$thumb = new thumbnail();
$whereserviceId = " AND iMasterServiceCategoryId = " . $iMasterServiceCategoryId;
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name . " WHERE 1 $whereserviceId AND vCode = '" . $vCodeLang . "'");
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder = $nxtDispNo = $iDisplayOrder + 1; // Maximum order number
$getLangData = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active' ORDER BY iDispOrder");
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : 0;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";
$eType = isset($_POST['eType']) ? $_POST['eType'] : $eMasterType;
$eForService = isset($_POST['eForService']) ? $_POST['eForService'] : "";
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '-1';
$vStatusBarColor = isset($_POST['vStatusBarColor']) ? $_POST['vStatusBarColor'] : '';
$searchvCode = "";
$iCopyForOther = isset($_POST['iCopyForOther']) ? $_POST['iCopyForOther'] : 'off';
if ($_REQUEST['vCode'] != "") {
    $searchvCode = "&selectlang=" . $_REQUEST['vCode'];
}
if (isset($_POST['submit'])) { //form submit
    $vCodeLang = isset($_POST['vCode']) ? $_POST['vCode'] : 0;
    if ($action == "Add" && !$userObj->hasPermission($create)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create banner.';
        header("Location:bsr_banner.php?eType=" . $_REQUEST['eType'].$searchvCode);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update banner.';
        header("Location:bsr_banner.php?eType=" . $_REQUEST['eType'].$searchvCode);
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:bsr_banner.php?eType=" . $_REQUEST['eType'].$searchvCode);
        exit;
    }
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE eType = '" . $eMasterType . "' AND iDisplayOrder = " . $i . " AND vCode = '" . $vCodeLang . "' ");
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {
            $setOrder = $i - 1;
            if ($i == 1) {
                $setOrder = $nxtDispNo;
            }
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . $setOrder . " WHERE eType = '" . $eMasterType . "' AND iDisplayOrder = " . $i . " AND vCode = '" . $vCodeLang . "' ");
        }
        
    }
    $select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM " . $tbl_name . " WHERE vCode = '" . $vCodeLang . "'");
    $iUniqueId = isset($select_order[0]['iUniqueId']) ? $select_order[0]['iUniqueId'] : 0;
    $iUniqueId = $iUniqueId + 1; // Maximum order number
    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $q = "INSERT INTO ";
            $where = '';
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iUniqueId` = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
                $iUniqueId = $id;
            }
            if (!empty($id) && !empty($vCodeLang)) {
                $sqlrecord = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode,eBuyAnyService,iMasterServiceCategoryId FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' AND vCode = '" . $vCodeLang . "'";
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
                $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
                $image_width = $image_info[0];
                $image_height = $image_info[1];
                if ($error) {
                    $_SESSION['success'] = '3';
                    $_SESSION['var_msg'] = $error;
                    header("Location:bsr_banner.php?eType=" . $_REQUEST['eType'].$searchvCode);
                    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_images_panel"] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                    $vImage = $img[0];
                }
            }
            $query = $q . " `" . $tbl_name . "` SET 	
					`vTitle` = '" . $vTitle . "',
					`vImage` = '" . $vImage . "',
					`eStatus` = '" . $eStatus . "',
					`iUniqueId` = '" . $iUniqueId . "',
					`iDisplayOrder` = '" . $iDisplayOrder . "',
					`iServiceId` = '" . $iServiceId . "',
                    `vCode` = '" . $vCodeLang . "',
                    `iLocationid` = '" . $iLocationId . "',
                    `eBuyAnyService` = '" . $eBuyAnyService . "',
                    `eType` = '" . $eMasterType . "',
                    `iMasterServiceCategoryId` = '" . $iMasterServiceCategoryId . "',
					`vStatusBarColor` = '" . $vStatusBarColor . "'" 
					
					. $where;
            $obj->sql_query($query);
            if ($iCopyForOther == "on" && $action == "Add") {
                //echo"<pre>"; print_r($getLangData);
                foreach ($getLangData as $lk => $lvalue) {
                    if ($vCodeLang != $lvalue['vCode']) {
                        $iUniqueId = $iUniqueId + 1;
                        $iDisplayOrder = $iDisplayOrder + 1;
                        $subquery = $q . " `" . $tbl_name . "` SET 	
						`vTitle` = '" . $vTitle . "',
						`vImage` = '" . $vImage . "',
						`eStatus` = '" . $eStatus . "',
						`iUniqueId` = '" . $iUniqueId . "',
						`iDisplayOrder` = '" . $iDisplayOrder . "',
						`iServiceId` = '" . $iServiceId . "',
	                    `vCode` = '" . $lvalue['vCode'] . "',
	                    `iLocationid` = '" . $iLocationId . "',
	                    `eBuyAnyService` = '" . $eBuyAnyService . "',
	                    `eType` = '" . $eMasterType . "',
	                    `iMasterServiceCategoryId` = '" . $iMasterServiceCategoryId . "',
						`vStatusBarColor` = '" . $vStatusBarColor . "'" 
						
						. $where;
                        $obj->sql_query($subquery);
                    }
                }
            }
            if ($id != '') {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            } else {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            }
        }
        header("Location:bsr_banner.php?eType=" . $_REQUEST['eType'].$searchvCode);
        exit();
    }
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode,eBuyAnyService,iLocationid,eType,vStatusBarColor,iMasterServiceCategoryId FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '" . $vCodeLang . "'";
    $db_data = $obj->MySQLSelect($sql);
    $iUniqueId = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vTitle = $value['vTitle'];
            $eStatus = $value['eStatus'];
            $vImage = $value['vImage'];
            $iDisplayOrder = $value['iDisplayOrder'];
           // echo  $iDisplayOrder;die;
            $iServiceId = $value['iServiceId'];
            $vCodeLang = $value['vCode'];
            $iLocationId = $value['iLocationid'];
            $eBuyAnyService = $value['eBuyAnyService'];
            $vStatusBarColor = $value['vStatusBarColor'];
            $eType = $value['eType'];
            $iMasterServiceCategoryId = $value['iMasterServiceCategoryId'];
        }
    }
}
$sql_location = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'Banner' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sql_location);
?>
<!DOCTYPE html>
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
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
                    <h2><?= $action; ?> Banner</h2>
                    <a href="bsr_banner.php?eType=<?= $_REQUEST['eType'] ?>">
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
                            <?php echo $_REQUEST['var_msg']; ?>
                        </div> <br/>
                    <?php } ?>
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div> <br/>
                    <?php } ?>
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>  <br/>
                    <?php } ?>
                    <form method="post" name="bsr_banner" id="bsr_banner" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="vImage_old" value="<?= $vImage ?>">
                        <input type="hidden" name="iMasterServiceCategoryId" value="<?= $iMasterServiceCategoryId ?>">
                        <input type="hidden" name="eType" value="<?= $_REQUEST['eType'] ?>">
                        <!-- <div class="row">
                            <?php if ($action == "Add") { ?>
                                <div class="col-lg-12">
                                    <label>Select Language</label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name='vCode' id='vCode'  onchange="bannerdata(this.value)">
                                        <?php for ($l = 0; $l < scount($getLangData); $l++) { ?>
					                       <option <?php if ($vCodeLang == $getLangData[$l]['vCode']) { ?>selected=""<?php } ?>   value="<?= $getLangData[$l]['vCode']; ?>"><?= $getLangData[$l]['vTitle'] . ' (' . $getLangData[$l]['vCode'] . ')'; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <div class="col-lg-12">
                                    <label>Language: <?= $vCodeLang ?></label>
                                </div>
                                <input type="hidden" name="vCode" value="<?= $vCodeLang ?>">
                            <?php } ?>
                        </div> -->
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Select Language</label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='vCode' id='vCode'  onchange="bannerdata(this.value)">
                                    <?php for ($l = 0; $l < scount($getLangData); $l++) { ?>
                                       <option <?php if ($vCodeLang == $getLangData[$l]['vCode']) { ?>selected=""<?php } ?>   value="<?= $getLangData[$l]['vCode']; ?>"><?= $getLangData[$l]['vTitle'] . ' (' . $getLangData[$l]['vCode'] . ')'; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="bannerlang">
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Image<?= ($vImage == '') ? '<span class="red"> *</span>' : ''; ?></label>
                                </div>
                                <div class="col-lg-6">
                                    <?php if ($vImage != '') { ?>
                                        <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?w=300&src=' . $tconfig['tsite_upload_images'] . $vImage; ?>">
                                        <input type="file" name="vImage" id="vImage" value="<?= $vImage; ?>"/>
                                    <?php } else { ?>
                                        <input type="file" name="vImage" id="vImage" value="<?= $vImage; ?>" required/>
                                    <?php } ?>
                                    <br/>
                                    [Note: Recommended dimension for banner image is 2880 * 1620. <br> <?= IMAGE_INSTRUCTION_NOTES ?>]
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Title</label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" name="vTitle" id="vTitle" value="<?= $vTitle ?>"  class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <?php if ($MODULES_OBJ->isEnableLocationwiseBanner()) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    
				    <label>Select Location  <span class="red"> *</span>  <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Select the location in which you would like to appear this banner. For example banner to appear for any specific city or state or may be for whole country. You can define these locations from "Manage Locations >> Geo Fence Location" section'></i></label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name='iLocationId' id="iLocationId" required="">
                                        <option value="">Select Location</option>
                                        <option value="-1" <?php if ($iLocationId == "-1") { ?>selected<?php } ?>>All</option>
                                        <?php
                                        foreach ($db_location as $i => $row) {
                                            ?>
                                            
					    <option value="<?= $row['iLocationId'] ?>"     <?php if ($iLocationId == $row['iLocationId']) { ?>selected<?php } ?>><?= $row['vLocationName'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-lg-6">
                                    <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location  </a>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($MODULES_OBJ->isEnableAppHomeScreenLayoutV2()) { ?>
                            <div style="display: none" class="row">
                                <div class="col-lg-12">
                                    <label>App Status Bar Color</label>
                                </div>
                                <div class="col-md-1 col-sm-1">
                                    <input type="color" id="StatusBarColor" class="form-control"   value="<?= $vStatusBarColor ?>"/>
                                    <input type="hidden" name="vStatusBarColor" id="vStatusBarColor"  value="<?= $vStatusBarColor ?>">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Order</label>
                            </div>
                            <div class="col-lg-6">
                                        <span id="orderdiv">
                                        <?php
                                        $temp = 1;
                                        $dataArray = array();
                                        $query1 = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE 1=1 $whereserviceId AND vCode = '$vCodeLang' ORDER BY iDisplayOrder";
                                        $data_order = $obj->MySQLSelect($query1);
                                        
                                        foreach ($data_order as $k=>$val) {
                                            $dataArray[] = $k+1;
                                            $temp = $iDisplayOrder;
                                        }
                                        ?>
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp; ?>">
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php foreach ($dataArray as $arr): ?>
                                                <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >
                                                    -- <?= $arr ?> --
                                                </option>
                                            <?php endforeach; ?>
                                            <?php if ($action == "Add") { ?>
                                                <option value="<?= $temp; ?>"  selected="selected"> -- <?= $temp ?> --  </option>
                                            <?php } ?>
                                            <!-- <?php for ($i = 1; $i <= $maxDisplayOrder; $i++) { ?>
                                                <option value="<?= $i ?>" <?php if ($action == "Add") { ?><?= $maxDisplayOrder == $i ? "selected" : "" ?><?php } else { ?> <?= $iDisplayOrder == $i ? "selected" : "" ?><?php } ?>><?= $i ?></option>
                                            <?php } ?> -->
                                        </select>
                                        </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    
				    <input type="checkbox"  name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                </div>
                            </div>
                        </div>
                        <?php if ($action == "Add" && scount($getLangData) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label> Do you want to copy same banner for other languages also?</label>
                                </div>
                                <div class="col-lg-6">
                                    <div class="make-switch" data-on="success" data-off="warning" data-on-label="Yes"  data-off-label="No">
                                        <input type="checkbox" name="iCopyForOther"/>
                                    </div>
	                                </div>
                            	<?php // } ?>
                                <!--<div class="row">
                                    <?php /*if (($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' && $userObj->hasPermission($create))) { */?>
                                        <div class="col-lg-12">
                                            <input type="submit" class="save btn-info" name="submit" id="submit" value="<?php /*= $action; */?> Banner">
                                            <a href="bsr_banner.php?eType=<?php /*= $_REQUEST['eType']*/?>" class="btn btn-default back_link">Cancel</a>
                                        </div>
                                    <?php /*} */?>
                                </div>-->
                            </div>
                        <?php } ?>
                        <div class="row">
                            <?php if (($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' && $userObj->hasPermission($create))) { ?>
                                <div class="col-lg-12">
                                    <input type="submit" class="save btn-info" name="submit" id="submit"   value="<?= $action; ?> Banner">
                                    <a href="bsr_banner.php?eType=<?= $_REQUEST['eType'] ?>"  class="btn btn-default back_link">Cancel   </a>
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
            
	    'AJAX_DATA': { vCode: val, id: '<?= $_REQUEST['id']; ?>', order: 'Yes',eBuyAnyService: '<?= $eBuyAnyService ?>', eForService: '<?= $eForService ?>', iMasterServiceCategoryId: '<?= $iMasterServiceCategoryId ?>'  },
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
                console.log(response.result);
            }
        });
    }
    $("#StatusBarColor").on("input", function () {
        var color = $(this).val();
        $('#vStatusBarColor').val(color);
    });
    if ($('#bsr_banner').length !== 0) {
        $("#bsr_banner").validate({
        rules: {
            vImage: {
                     extension: imageUploadingExtenstionjsrule
                }
        },
        messages: {
                vImage:{
                    required: requiredFieldMsg,
                    extension: imageUploadingExtenstionMsg
                }
            },
        });
    }
</script>
</body>
<!-- END BODY-->
</html>