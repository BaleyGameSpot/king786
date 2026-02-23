<?php

include_once('../common.php');

require_once(TPATH_CLASS . "Imagecrop.class.php");

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : ''; // iUniqueId

$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : ''; // iUniqueId

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';

$iMasterServiceCategoryId = isset($_REQUEST['iMasterServiceCategoryId']) ? $_REQUEST['iMasterServiceCategoryId'] : '';

$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'banners';

$script = 'Banner';

// fetch all lang from language_master table 

//$sql = "SELECT vCode FROM `language_master` ORDER BY `iDispOrder`";

//$db_master = $obj->MySQLSelect($sql);

//$count_all = scount($db_master);

$count_all = 1;

$vImage = isset($_REQUEST['vImage_old']) ? $_REQUEST['vImage_old'] : '';

$eStatus_check = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : 'off';

$vTitle = isset($_REQUEST['vTitle']) ? $_REQUEST['vTitle'] : '';

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$eForhotel = isset($_REQUEST['eForhotel']) ? $_REQUEST['eForhotel'] : '';
if($eForhotel == 'Yes'){
    $temp = 1;

    $action = 'Add';

    $dataArray = array();


    $select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM hotel_banners WHERE 1 $whereserviceId AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");

    $iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;

    $iDisplayOrder = $nxtDispNo = $iDisplayOrder + 1; // Maximum order number

    

    $query1 = "SELECT iDisplayOrder FROM hotel_banners WHERE 1 = 1 AND vCode = '".$vCodeLang."' ORDER BY iDisplayOrder";

    $data_order = $obj->MySQLSelect($query1);
    
    foreach ($data_order as $value) {

        $dataArray[] = $value['iDisplayOrder'];

        $temp = $iDisplayOrder;

    } ?>

    <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp ?>">

    <select name="iDisplayOrder" class="form-control">

        <?php foreach ($dataArray as $arr): ?>

            <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >

                -- <?= $arr ?> --

            </option>

        <?php endforeach; ?>

        <?php if ($action == "Add") { ?>

            <option value="<?= $temp; ?>" selected="selected">

                -- <?= $temp ?> --

            </option>

        <?php } ?>

    </select>
<?php  exit; }


if($order == 'Yes') {

    $temp = 1;

    $action = 'Add';

    $dataArray = array();
   

    $serviceid = isset($_REQUEST['serviceid']) ? $_REQUEST['serviceid'] : '';
   
    if(!empty($serviceid)) {

        $whereserviceId = " AND iServiceId != 0";    

    } else {

        $whereserviceId = " AND iServiceId = 0";    

    }

    if(!empty($iMasterServiceCategoryId)){
        $whereserviceId = " AND iMasterServiceCategoryId = '".$iMasterServiceCategoryId."'";    
    }

    $eBuyAnyService = (isset($_REQUEST['eBuyAnyService']) && in_array($_REQUEST['eBuyAnyService'], ['Genie', 'Runner', 'Anywhere'])) ? 'eBuyAnyService='.$_REQUEST['eBuyAnyService'] : '';

    $ssqlbuyanyservice = "";

    if(isset($_REQUEST['eBuyAnyService']) && in_array($_REQUEST['eBuyAnyService'], ['Genie', 'Runner', 'Anywhere']) && $MODULES_OBJ->isEnableAnywhereDeliveryFeature())

    {

        $ssqlbuyanyservice = " AND eBuyAnyService = '".$_REQUEST['eBuyAnyService']."' ";

        if($_REQUEST['eBuyAnyService'] == "Genie" || $_REQUEST['eBuyAnyService'] == "Anywhere")

        {

            $ssqlbuyanyservice = " AND eBuyAnyService = 'Genie' ";

        }

    } else if (isset($_REQUEST['eForService']) && in_array($_REQUEST['eForService'], ['MoreDelivery']) && $MODULES_OBJ->isDeliveryFeatureAvailable()) {

        $ssqlbuyanyservice = " AND eType = 'Deliver' ";

    } else {
        if(!empty($iMasterServiceCategoryId)){
            $ssqlbuyanyservice = " AND eBuyAnyService = ''";
        } else {
            $ssqlbuyanyservice = " AND eBuyAnyService = '' AND eType = ''";
        }

    }

    

    $select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name . " WHERE 1 $whereserviceId AND vCode = '" . $vCodeLang . "' $ssqlbuyanyservice");

    $iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;

    $iDisplayOrder = $nxtDispNo = $iDisplayOrder + 1; // Maximum order number

    

    $query1 = "SELECT iDisplayOrder FROM " . $tbl_name . " WHERE 1 = 1 $whereserviceId AND vCode = '$vCodeLang' $ssqlbuyanyservice ORDER BY iDisplayOrder";

    $data_order = $obj->MySQLSelect($query1);
    
    foreach ($data_order as $value) {

        $dataArray[] = $value['iDisplayOrder'];

        $temp = $iDisplayOrder;

    }

    ?>

    <input type="hidden" name="temp_order" id="temp_order" value="<?= $temp ?>">

    <select name="iDisplayOrder" class="form-control">

        <?php foreach ($dataArray as $arr): ?>

            <option <?= $arr == $temp ? ' selected="selected"' : '' ?> value="<?= $arr; ?>" >

                -- <?= $arr ?> --

            </option>

        <?php endforeach; ?>

        <?php if ($action == "Add") { ?>

            <option value="<?= $temp; ?>" selected="selected">

                -- <?= $temp ?> --

            </option>

        <?php } ?>

    </select>

<?php exit; } 

$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

$thumb = new thumbnail();

/* to fetch max iDisplayOrder from table for insert */

$select_order = $obj->MySQLSelect("SELECT MAX(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "'");

$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;

$iDisplayOrder = $nxtDispNo=$iDisplayOrder + 1; // Maximum order number

$serviceCatArr = json_decode(serviceCategories, true);

$getLangData = $obj->MySQLSelect("SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active'");

//echo "<pre>";print_r($getLangData);die;

$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;

$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : 0;

//$vCodeLang = isset($_POST['vCode']) ? $_POST['vCode'] : 0;

$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";

if (isset($_POST['submit'])) { //form submit



    if ($action == "Add" && !$userObj->hasPermission('create-banner')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to create banner.';

        header("Location:cancel_reason.php");

        exit;

    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-banner')) {

        $_SESSION['success'] = 3;

        $_SESSION['var_msg'] = 'You do not have permission to update banner.';

        header("Location:cancel_reason.php");

        exit;

    }

    if (!empty($id) && SITE_TYPE == 'Demo') {

        $_SESSION['success'] = 2;

        header("Location:banner.php");

        exit;

    }

    //echo "<pre>";print_r($_REQUEST);exit;

    if ($temp_order > $iDisplayOrder) {

        for ($i = $temp_order; $i >= $iDisplayOrder; $i--) {

            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i + 1) . " WHERE iDisplayOrder = " . $i);

        }

    } else if ($temp_order < $iDisplayOrder) {

        for ($i = $temp_order; $i <= $iDisplayOrder; $i++) {

            $setOrder = $i - 1;

            if ($i == 1) {

                $setOrder = $nxtDispNo;

            }

            $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . $setOrder . " WHERE iDisplayOrder = " . $i);

        }

    }

    $select_order = $obj->MySQLSelect("SELECT MAX(iUniqueId) AS iUniqueId FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "'");

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

            $image_object = $_FILES['vImage']['tmp_name'];

            $image_name = $_FILES['vImage']['name'];

            if ($image_name != "") {

                $filecheck = basename($_FILES['vImage']['name']);

                $fileextarr = explode(".", $filecheck);

                $ext = strtolower($fileextarr[scount($fileextarr) - 1]);

                $flag_error = 0;

                if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {

                    $flag_error = 1;

                    $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";

                }

                $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);

                $image_width = $image_info[0];

                $image_height = $image_info[1];

                if ($flag_error == 1) {

                    $_SESSION['success'] = '3';

                    $_SESSION['var_msg'] = $var_msg;

                    header("Location:banner.php");

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

					`vCode` = '" . $vCodeLang . "'"

                    . $where;

            $obj->sql_query($query);

            if ($id != '') {

                $_SESSION['success'] = '1';

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];

            } else {

                $_SESSION['success'] = '1';

                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];

            }

        }

        header("Location:banner.php");

        exit();

    }

}

// for Edit

if ($action == 'Edit') {

    $sql = "SELECT vTitle,eStatus,vImage,iDisplayOrder,iServiceId,vCode FROM " . $tbl_name . " WHERE iUniqueId = '" . $id . "' and vCode = '".$vCodeLang."'";

    $db_data = $obj->MySQLSelect($sql);

    $iUniqueId = $id;

    if (scount($db_data) > 0) {

        foreach ($db_data as $key => $value) {

            //$vTitle 			= 'vTitle_'.$value['vCode'];

            $vTitle = $value['vTitle'];

            $eStatus = $value['eStatus'];

            $vImage = $value['vImage'];

            $iDisplayOrder = $value['iDisplayOrder'];

            $iServiceId = $value['iServiceId'];

            $vCodeLang = $value['vCode'];

        }

    }

}

?>

<input type="hidden" name="vImage_old" value="<?= $vImage ?>">

<div class="row">

    <div class="col-lg-12">

        <label>Image<?= ($vImage == '') ? '<span class="red"> *</span>' : ''; ?></label>

    </div>

    <div class="col-lg-6">

        <?php if ($vImage != '') { ?>

            <!-- <img src="<?= $tconfig['tsite_upload_images'] . $vImage; ?>" style="width:200px;height:100px;"> -->



            <img src="<?= $tconfig["tsite_url"].'resizeImg.php?w=400&h=400&src='.$tconfig['tsite_upload_images'] . $vImage;   ?>" style="width:200px;height:200px;">



            <input type="file" name="vImage" id="vImage" value="<?= $vImage; ?>"/>

        <?php } else { ?>

            <input type="file" name="vImage" id="vImage" value="<?= $vImage; ?>" required/>

        <?php } ?>

        <br/>

        [Note: Recommended dimension for banner image is 2880 * 1620.]

    </div>

</div>

<div class="row">

    <div class="col-lg-12">

        <label>Title</label>

    </div>

    <div class="col-lg-6">

        <input type="text" name="vTitle" id="vTitle" value="<?= $vTitle ?>" class="form-control" />

    </div>

</div>