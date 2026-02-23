<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$admin = isset($_REQUEST['admin']) ? $_REQUEST['admin'] : '';
$create = "create-admin";
$edit = "edit-admin";
$delete = "delete-admin";
$updateStatus = "update-status-admin";
if ($admin == "hotels") {
    $create = "create-hotel";
    $edit = "edit-hotel";
    $delete = "delete-hotel";
    $updateStatus = "update-status-hotel";
}
if ((!$userObj->hasRole(1) && !$userObj->hasPermission($edit)) && !(isset($_SESSION['sess_iAdminUserId']) && $_SESSION['sess_iAdminUserId'] == $_REQUEST['id'] && $action == "Edit")) {
    //$userObj->redirect();
}
$tbl_name = 'administrators';
if ($admin == "hotels") {
    $script = 'Hotels';
} else {
    $script = 'Admin';
}
$sql = "SELECT vCountryCode,vCountry FROM country WHERE eStatus='Active' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);
if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $CONFIG_OBJ->getConfigurations("configurations", "SHOW_CITY_FIELD");
}
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');
$kioskPanel = $MODULES_OBJ->isEnableKioskPanel('Yes');
if (!empty($admin)) {
    $sql1 = "SELECT iGroupId,vGroup FROM admin_groups WHERE eStatus = 'Active' AND iGroupId = '4'";
} else {
    $sql1 = "SELECT iGroupId,vGroup FROM admin_groups WHERE eStatus = 'Active' AND iGroupId != '4'";
}
$db_group = $obj->MySQLSelect($sql1);
// set all variables with either post (when submit) either blank (when insert)
$vFirstName = isset($_POST['vFirstName']) ? $_POST['vFirstName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
$iGroupId = isset($_POST['iGroupId']) ? $_POST['iGroupId'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vPass = ($vPassword != "") ? encrypt_bycrypt($vPassword) : '';
$fHotelServiceCharge = isset($_POST['fHotelServiceCharge']) ? $_POST['fHotelServiceCharge'] : '';
$vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
$vBankAccountHolderName = isset($_POST['vBankAccountHolderName']) ? $_POST['vBankAccountHolderName'] : '';
$vAccountNumber = isset($_POST['vAccountNumber']) ? $_POST['vAccountNumber'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vBIC_SWIFT_Code = isset($_POST['vBIC_SWIFT_Code']) ? $_POST['vBIC_SWIFT_Code'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vContactNo = isset($_POST['vContactNo']) ? $_POST['vContactNo'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vAddressLat = isset($_POST['vAddressLat']) ? $_POST['vAddressLat'] : '';
$vAddressLong = isset($_POST['vAddressLong']) ? $_POST['vAddressLong'] : '';
$vPickupFrom = isset($_POST['vPickupFrom']) ? $_POST['vPickupFrom'] : '';
if (isset($_POST['submitBtn'])) {
    //print_r($_POST);die;
    if (!$userObj->hasRole(1) && ($action == "Add" && !$userObj->hasPermission($create))) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create admin.';
        header("Location:admin.php");
        exit;
    }
    if (!$userObj->hasRole(1) && ($action == "Edit" && !$userObj->hasPermission($edit)) && !(isset($_SESSION['sess_iAdminUserId']) && $_SESSION['sess_iAdminUserId'] == $_REQUEST['id'] && $action == "Edit")) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update admin.';
        header("Location:admin.php");
        exit;
    }
    if (($id != "" && SITE_TYPE == 'Demo') || ($id == "" && SITE_TYPE == 'Demo')) { // Added By NModi on 10-12-20
        // header("Location:admin_action.php?id=" . $id . '&success=2'); // commneted by NModi on on 10-12-20
        $_SESSION['success'] = '2';
        header("location:" . $backlink);
        exit;
    }
    //Add Custom validation
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vFirstName'], 'req', 'First Name is required');
    if ($iGroupId != 4) {
        $validobj->add_fields($_POST['vLastName'], 'req', 'Last Name is required');
    }
    $validobj->add_fields($_POST['vEmail'], 'req', 'Email Address is required.');
    $validobj->add_fields($_POST['vEmail'], 'email', 'Please enter valid Email Address.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
    }
    //$validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
    if ($_SESSION['sess_iGroupId'] == 1) {
        $validobj->add_fields($_POST['iGroupId'], 'req', 'Group is required.');
    }
    $error = $validobj->validate();
    //Other Validations
    if (isset($_POST['iGroupId']) && $_POST['iGroupId'] == 4) {
        $eSystem = "";
        $checEmailExist = checkMemberDataInfo($vEmail, "", 'ADMIN', $vCountry, $id, $eSystem);
        if ($checEmailExist['status'] == 0) {
            $error .= '* Email Address is already exists.<br>';
        } else if ($checEmailExist['status'] == 2) {
            $error .= $langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        }
    } else {
        if ($vEmail != "") {
            if ($id != "") {
                $msg1 = checkDuplicateAdminNew('iAdminId', 'administrators', Array('vEmail'), $id, "");
            } else {
                $msg1 = checkDuplicateAdminNew('vEmail', 'administrators', array('vEmail'), "", "");
            }
            if ($msg1 == 1) {
                $error .= '* Email Address is already exists.<br>';
            }
        }
    }
    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        $passPara = '';
        if ($vPass != "") {
            $passPara = "`vPassword` = '" . $vPass . "',";
        }
        $groupSave = "";
        if ($_SESSION['sess_iGroupId'] == 1) {
            $groupSave = "`iGroupId` = '" . $iGroupId . "'";
        } else {
            $groupSave = "`iGroupId` = '" . $userObj->role_id . "'";
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Edit') {
            $str = ", eStatus = 'Inactive' ";
        } else {
            $str = '';
        }
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iAdminId` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET

			`vFirstName` = '" . $vFirstName . "',

			`vLastName` = '" . $vLastName . "',

			`vEmail` = '" . $vEmail . "',

            `fHotelServiceCharge`= '" . $fHotelServiceCharge . "',

            `vCode` = '" . $vCode . "',

            `vContactNo` = '" . $vContactNo . "',

            `vCountry` = '" . $vCountry . "',

            `vState` = '" . $vState . "',

            `vCity` = '" . $vCity . "',

            `vAddress` = '" . $vAddress . "',

            `vAddressLat` = '" . $vAddressLat . "',

            `vAddressLong` = '" . $vAddressLong . "',

            `vPaymentEmail`= '" . $vPaymentEmail . "',

            `vBankAccountHolderName`= '" . $vBankAccountHolderName . "',

            `vAccountNumber`= '" . $vAccountNumber . "',

            `vBankName`= '" . $vBankName . "',

            `vBankLocation`= '" . $vBankLocation . "',

            `vBIC_SWIFT_Code`= '" . $vBIC_SWIFT_Code . "',

			$passPara

			$groupSave

			 " . $where;
             
$obj->sql_query($query); // salva ou atualiza o administrador
$id = ($id != '') ? $id : $obj->GetInsertId(); // pega o ID correto (insert ou edit)

// Agora salva a localização se for um Franchise Admin
if (!empty($iLocationId) && !empty($id) && $iGroupId == 6) {
    echo "Salvando em admin_locations: $id → $iLocationId";
    $obj->sql_query("DELETE FROM admin_locations WHERE admin_id = '$id'");
    $obj->sql_query("INSERT INTO admin_locations (admin_id, location_id) VALUES ('$id', '$iLocationId')");
    exit; // só para testar
}








        // new add
        if ($iGroupId == 4) {
            $hsql = "SELECT * FROM hotel WHERE iAdminId = '" . $id . "'";
            $htotalData = $obj->MySQLSelect($hsql);
            if (scount($htotalData) == 0) {
                $q = "INSERT INTO ";
                $where = '';
            }
            $subquery = $q . " `hotel` 

            SET `iAdminId` = '" . $id . "',

            vLang ='" . $_SESSION['sess_lang'] . "',

            vPickupFrom = '" . $vPickupFrom . "',

            vCurrencyPassenger ='" . $_SESSION['sess_currency'] . "'

             " . $where;
            $obj->sql_query($subquery);
        }
        $sql1 = "SELECT iHotelId FROM  `hotel` WHERE `iAdminId` = '" . $id . "'";
        $db_hoteldata = $obj->MySQLSelect($sql1);
        $hotelid = $db_hoteldata[0]['iHotelId'];
        if ($_FILES['vImgName']['name'] != '') {

            require_once("library/validation.class.php");
            $validobj = new validation();

            $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
            $error = $validobj->validateFileType($_FILES['vImgName'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

            if($error){
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $error;
                header("Location:admin.php");
                exit;
            }

            $img_path = $tconfig["tsite_upload_images_hotel_passenger_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImgName']['tmp_name'];
            $image_name = $_FILES['vImgName']['name'];
            $check_file = $img_path . '/' . $hotelid . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $hotelid . '/' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/1_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/2_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/3_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/4_' . $oldImage);
            }
            $Photo_Gallery_folder = $img_path . '/' . $hotelid . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_hotel_passenger_size1"], $tconfig["tsite_upload_images_hotel_passenger_size2"], $tconfig["tsite_upload_images_hotel_passenger_size3"], $tconfig["tsite_upload_images_hotel_passenger_size4"], '', '', 'Y', '', $Photo_Gallery_folder);
            $vImgName = $img1;
            $sql1 = "UPDATE hotel SET `vImgName` = '" . $vImgName . "' WHERE `iAdminId` = '" . $id . "'";
            $obj->sql_query($sql1);
        }
        if ($_FILES['vVehicleTypeImg']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_hotel_passenger_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vVehicleTypeImg']['tmp_name'];
            $image_name = $_FILES['vVehicleTypeImg']['name'];
            $filecheck = basename($_FILES['vVehicleTypeImg']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $flag_error = 0;
            // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            //     $flag_error = 1;
            //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
            // }

            require_once("library/validation.class.php");
            $validobj = new validation();

            $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
            $error = $validobj->validateFileType($_FILES['vVehicleTypeImg'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

            if($error){
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $error;
                header("Location:admin.php");
                exit;
            }
            $dataimg = getimagesize($_FILES['vVehicleTypeImg']['tmp_name']);
            $imgwidth = $dataimg[0];
            $imgheight = $dataimg[1];
            if ($imgwidth < 1024) {
                echo "<script>alert('Your Image upload size is less than recommended. Image will look stretched.');</script>";
            }
            /* if ($flag_error == 1) {

              if ($action == "Add") {

              header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");

              exit;

              } else {

              header("Location:vehicle_category_action.php?id=" . $id . "&var_msg=" . $var_msg . "&goback=1");

              exit;

              }

              } */
            $check_file = $img_path . '/' . $hotelid . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $hotelid . '/' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/1_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/2_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/3_' . $oldImage);
                @unlink($img_path . '/' . $hotelid . '/4_' . $oldImage);
            }
            if ($flag_error == 1) {
                $_SESSION['success'] = '3';
                $_SESSION['var_msg'] = $var_msg;
                header("Location:admin.php");
            } else {
                $Photo_Gallery_folder = $img_path . '/' . $hotelid . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img1 = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg');
                $vVehicleTypeImg = $img1[0];
                $sql1 = "UPDATE hotel SET `vVehicleTypeImg` = '" . $vVehicleTypeImg . "' WHERE `iAdminId` = '" . $id . "'";
                $obj->sql_query($sql1);
            }
        }
        $locations_ids = isset($_POST['locations_ids']) ? $_POST['locations_ids'] : [];
        $user = Models\Administrator::find($id);
        if ($user) {
            $user->locations()->sync($locations_ids);
        }
        if (isset($_SESSION['sess_iAdminUserId']) && $_SESSION['sess_iAdminUserId'] == $_REQUEST['id'] && $action == "Edit") {
            $_SESSION['sess_vAdminFirstName'] = $vFirstName;
            $_SESSION['sess_vAdminLastName'] = $vLastName;
        }
        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        header("location:" . $backlink);
        exit;
    }
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iAdminId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $sql1 = "SELECT * FROM hotel WHERE iAdminId = '" . $id . "'";
    $db_hoteldata = $obj->MySQLSelect($sql1);
    // $vPass = decrypt($db_data[0]['vPassword']);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vFirstName = $value['vFirstName'];
            $vLastName = clearName($value['vLastName']);
            //$vLastName = clearName(" " . $value['vLastName']);
            $vEmail = clearEmail($value['vEmail']);
            // $vUserName = $value['vUserName'];
            $vPassword = $value['vPassword'];
            $iGroupId = $value['iGroupId'];
            $hotel_booking_service_charge = $value['fHotelServiceCharge'];
            $vPaymentEmail = $value['vPaymentEmail'];
            $vBankAccountHolderName = $value['vBankAccountHolderName'];
            $vAccountNumber = $value['vAccountNumber'];
            $vBankName = $value['vBankName'];
            $vBankLocation = $value['vBankLocation'];
            $vBIC_SWIFT_Code = $value['vBIC_SWIFT_Code'];
            $vCode = $value['vCode'];
            $vContactNo = isset($value['vContactNo']) ? $value['vContactNo'] : '';
            $vCountry = isset($value['vCountry']) ? $value['vCountry'] : '';
            $vState = isset($value['vState']) ? $value['vState'] : '';
            $vCity = isset($value['vCity']) ? $value['vCity'] : '';
            $vAddress = isset($value['vAddress']) ? $value['vAddress'] : '';
            $vAddressLat = isset($value['vAddressLat']) ? $value['vAddressLat'] : '';
            $vAddressLong = isset($value['vAddressLong']) ? $value['vAddressLong'] : '';

            $vImgName = $vVehicleTypeImg = $vPickupFrom = $hotelid = '';
            if(!empty($db_hoteldata)) {
                $vImgName = $db_hoteldata[0]['vImgName'];
                $vVehicleTypeImg = $db_hoteldata[0]['vVehicleTypeImg'];
                $vPickupFrom = $db_hoteldata[0]['vPickupFrom'];
                $hotelid = $db_hoteldata[0]['iHotelId'];    
            }
            
        }
    }
}
$locations = Models\LocationMaster::adminLocations()->get()->pluck('vLocationName', 'iLocationId')->toArray();
//$selected_locations = Models\Administrator::find($id)->locations;
$selected_locations = array();
$administratorfind = Models\Administrator::find($id);
if ($administratorfind) {
    $selected_locations = $administratorfind->locations;
}
if ($selected_locations && $selected_locations->count() > 0) {
    $selected_location_ids = $selected_locations->pluck(['iLocationId'])->toArray();
}
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
    <title><?= $SITE_NAME ?> | Admin <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="<?php echo $tconfig['tsite_url']; ?>assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="<?php echo $tconfig['tsite_url']; ?>assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $tconfig['tsite_url']; ?>assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="css/select2/select2.min.css"></link>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $action; ?> Admin <?= $vFirstName; ?></h2>
                    <a class="back_link" href="company.php">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <?php } ?>

                    <?php if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php print_r($error); ?>
                        </div>
                        <br/>
                    <?php } ?>
                    <form name="_admin_form" id="_admin_form" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                        <input type="hidden" name="id" id="iAdminId" value="<?php echo $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="admin.php"/>
                        <?php if ($_SESSION['sess_iGroupId'] != 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Group
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <input type="hidden" value="4" id="iGroupId" name="iGroupId">
                                <div class="col-lg-6">
                                    <div class="form-control disabled"><?php
                                        for ($i = 0; $i < scount($db_group); $i++) {
                                            echo $db_group[$i]['vGroup'];

                                            if ($userObj->hasRole($db_group[$i]['iGroupId'])) {
                                                echo $db_group[$i]['vGroup'];
                                                break;
                                            }
                                        }
                                        ?></div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($_SESSION['sess_iGroupId'] == 1 && empty($admin)) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Group
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name="iGroupId" id="iGroupId">
                                        <option value="">--select--</option>
                                        <?php for ($i = 0; $i < scount($db_group); $i++) {
                                            ?>
                                            <option value="<?= $db_group[$i]['iGroupId'] ?>" <?= ($db_group[$i]['iGroupId'] == $iGroupId) ? 'selected' : ''; ?> ><?= $db_group[$i]['vGroup'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php }else{ ?>
                            <input type="hidden" value="4" id="iGroupId" name="iGroupId">

                       <?php  } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label class="admin">First Name
                                    <span class="red"> *</span>
                                </label>
                                <label class="hoteladmin" style="display: none;">Hotel Name
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vFirstName" id="vName"
                                       value="<?= clearName($vFirstName); ?>" placeholder="First Name">
                            </div>
                        </div>
                        <?php // if ($iGroupId != 4) { ?>
                        <div class="row vLastName">
                            <div class="col-lg-12">
                                <label>Last Name
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vLastName" id="vLastName"
                                       value="<?= $vLastName; ?>" placeholder="Last Name">
                            </div>
                        </div>
                        <?php //} ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Email
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vEmail" id="vEmail"
                                       value="<?= $vEmail; ?>" placeholder="Email">
                            </div>
                            <div id="emailCheck"></div>
                        </div>
                        
                            <div class="row">
    <div class="col-lg-12">
        <label for="iLocationId">Franchise Location</label>
    </div>
    <div class="col-lg-6">
        <?php
        $locations = $obj->MySQLSelect("SELECT iLocationId, vLocationName FROM location_master WHERE eStatus = 'Active' AND eFor = 'Franchise'");
        ?>
        <select class="form-control" name="iLocationId" id="iLocationId">
            <option value="">-- Select Location --</option>
            <?php foreach ($locations as $loc) { ?>
                <option value="<?= $loc['iLocationId'] ?>" <?= ($loc['iLocationId'] == $iLocationId) ? 'selected' : '' ?>>
                    <?= $loc['vLocationName'] ?>
                </option>
            <?php } ?>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <label>Password
            <span class="red"> *</span>
            <?php if ($action == 'Edit') { ?>
                <span>&nbsp;[Leave blank to retain assigned password.]</span>
            <?php } ?>
        </label>
    </div>
    <div class="col-lg-6">
        <input type="password" class="form-control" name="vPassword" id="vPassword" value=""
               placeholder="Password" autocomplete="new-password">
    </div>
</div>


                        <?php if ($_SESSION['sess_iGroupId'] == 1) { ?>
                            <div id="hotel_bookingField" style="display: none;">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl['LBL_COUNTRY_TXT']; ?>
                                            <span class="red">*</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <?php
                                        if (scount($db_country) > 1) {
                                            $style = "";
                                        } else {
                                            $style = " disabled=disabled";
                                        } ?>
                                        <select <?= $style ?> class="form-control valid" name='vCountry' id='vCountry'
                                                              onChange="changeCode(this.value);setState(this.value, '<?= $vState ?>');">
                                            <?php
                                            if (scount($db_country) > 1) { ?>
                                                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                            <?php } ?>

                                            <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                                <option value="<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl['LBL_STATE_TXT']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name='vState' id="vState"
                                                onChange="setCity(this.value, '<?= $vCity ?>');">
                                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                        </select>
                                    </div>
                                </div>
                                <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?= $langage_lbl['LBL_CITY_TXT']; ?></label>
                                        </div>
                                        <div class="col-lg-6">
                                            <select class="form-control" name='vCity' id="vCity">
                                                <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                <?php for ($i = 0; $i < scount($db_city); $i++) { ?>
                                                    <option value="<?= $db_city[$i]['iCityId'] ?>" <?php if ($vCity == $db_city[$i]['iCityId']) { ?> selected <?php } ?>><?= $db_city[$i]['vcity'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row" id="hasherror">
                                    <div class="col-lg-12">
                                        <label>Contact No
                                            <span class="red">*</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-select-2" id="code" name="vCode"
                                               value="<?= $vCode ?>" readonly
                                               style="width: 10%;height: 36px;text-align: center;"
                                        / >
                                        <input type="text" class="form-control numericInputA" style="margin-top: 5px; width:90%;"
                                               name="vContactNo" id="vContactNo" value="<?= $vContactNo; ?>"
                                               placeholder="Contact No">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Address
                                            <span class="red">*</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" id="vAddress" class="form-control" name="vAddress"
                                               id="vAddress" value="<?= $vAddress; ?>" placeholder=" Location">
                                    </div>
                                    <input type="hidden" name="vAddressLat" id="vAddressLat"
                                           value="<?= $vAddressLat ?>">
                                    <input type="hidden" name="vAddressLong" id="vAddressLong"
                                           value="<?= $vAddressLong ?>">
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div id="map" style="width:100%;height:200px;"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Hotel Booking Service Charge (In %)
                                            <span class="red">*</span>
                                        </label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control numericInputA" name="fHotelServiceCharge"
                                               id='fHotelServiceCharge' value="<?= isset($hotel_booking_service_charge) ? $hotel_booking_service_charge : ''; ?>"/>
                                        [Note : Booking service charge will apply on Base Fare.]
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Payment Email</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vPaymentEmail"
                                               value="<?= $vPaymentEmail; ?>" placeholder="Payment Email"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Account Holder name</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vBankAccountHolderName"
                                               value="<?= $vBankAccountHolderName; ?>"
                                               placeholder="Account Holder name"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Account Number</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vAccountNumber"
                                               value="<?= $vAccountNumber; ?>" placeholder="Account Number"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Name of Bank</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vBankName"
                                               value="<?= $vBankName; ?>" placeholder="Name of Bank"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Bank Location</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vBankLocation"
                                               value="<?= $vBankLocation; ?>" placeholder="Bank Location"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>BIC/SWIFT Code</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vBIC_SWIFT_Code"
                                               value="<?= $vBIC_SWIFT_Code; ?>" placeholder="BIC/SWIFT Code"/>
                                    </div>
                                </div>
                                <?php if (ENABLEKIOSKPANEL == 'Yes') { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Logo</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php if (isset($vImgName) && $vImgName != '') { ?>
                                                <img src="<?= $tconfig['tsite_url'] . 'resizeImg.php?w=250&src=' . $tconfig['tsite_upload_images_hotel_passenger'] . "/" . $hotelid . "/" . $vImgName; ?>">
                                            <?php } ?>
                                            <!-- <input type="file" class="form-control" name="vImgName" id="vImgName"
                                                   placeholder="Name Label" accept='image/*'> -->
                                                   <input type="file" class="form-control" name="vImgName" id="vImgName"
                                                   placeholder="Name Label">
                                            [Note: Please Upload image size of 280px*280px.]
                                        </div>
                                    </div>
                                    <div class="row" style="display: none">
                                        <div class="col-lg-12">
                                            <label>VehicleType Screen bg Image (Kiosk)</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <?php if (isset($vVehicleTypeImg) && $vVehicleTypeImg != '') { ?>
                                                <img src="<?= $tconfig['tsite_upload_images_hotel_passenger'] . "/" . $hotelid . "/" . $vVehicleTypeImg; ?>"
                                                     style="width:100px;height:100px;">
                                            <?php } ?>
                                            <input type="file" class="form-control" name="vVehicleTypeImg"
                                                   id="vVehicleTypeImg" placeholder="Name Label" accept='image/*'>
                                            [Note: Please Upload image size of 1024px*680px for better resolution.]
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Pickup From</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control" name="vPickupFrom"
                                                   value="<?= $vPickupFrom; ?>" placeholder="Pickup From"/>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php
                                if ($userObj->hasRole(1) || ($action == "Edit" && $userObj->hasPermission($edit)) || ($action == "Add" && $userObj->hasPermission($create)) || (isset($_SESSION['sess_iAdminUserId']) && $_SESSION['sess_iAdminUserId'] == $_REQUEST['id'] && $action == "Edit")) {
                                    if ($action == "Edit") {
                                        $actionTxt = 'Update';
                                    } else {
                                        $actionTxt = 'Add Admin';
                                    }
                                    ?>
                                    <input type="submit" class="btn btn-default" name="submitBtn" id="submitBtn"
                                           value="<?= $actionTxt; ?>">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="admin.php" class="btn btn-default back_link">Cancel</a>
                            </div>
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
</body>
<script type="text/javascript" src="js/plugins/select2.min.js"></script>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places&callback=initMap" type="text/javascript" async></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script>

    var markers = [];

    $(document).ready(function () {

        $('.select2').select2({

            allowClear: true,

        });


        var referrer;

        if ($("#previousLink").val() == "") {

            referrer = document.referrer;

            //alert(referrer);

        } else {

            referrer = $("#previousLink").val();

        }

        if (referrer == "") {

            referrer = "admin.php";

        } else {

            $("#backlink").val(referrer);

        }

        $(".back_link").attr('href', referrer);

    });

    $(document).ready(function () {

        var iGroupId = $('#iGroupId').val();

        if (iGroupId == '4') {

            $("#hotel_bookingField").css("display", "block");

            $('#vCountry').attr('required', 'required');

            $('#vAddress').attr('required', 'required');

            $('#vContactNo').attr('required', 'required');

            $('#fHotelServiceCharge').attr('required', 'required');

            $('.vLastName').hide();

            $('#vLastName').removeAttr('required');

            $('.hoteladmin').show();

            $('.admin').hide();

        } else {

            $("#hotel_bookingField").css("display", "none");

            $('#vCountry').removeAttr('required');

            $('#vAddress').removeAttr('required');

            $('#vContactNo').removeAttr('required');

            $('#fHotelServiceCharge').removeAttr('required');

            $('.vLastName').show();

            $('#vLastName').attr('required', 'required');

            $('.hoteladmin').hide();

            $('.admin').show();

        }

    });


    $('#iGroupId').on('change', function () {

        if (this.value == '4') {
            $("#hotel_bookingField").css("display", "block");
            $('#vCountry').attr('required', 'required');
            $('#vAddress').attr('required', 'required');
            $('#vContactNo').attr('required', 'required');
            $('#fHotelServiceCharge').attr('required', 'required');
            $('.vLastName').hide();
            $('#vLastName').removeAttr('required');
            $('.hoteladmin').show();
            $('.admin').hide();

        } else {

            $("#hotel_bookingField").css("display", "none");

            $('#vCountry').removeAttr('required');

            $('#vAddress').removeAttr('required');

            $('#vContactNo').removeAttr('required');

            $('#fHotelServiceCharge').removeAttr('required');

            $('.vLastName').show();

            $('#vLastName').attr('required', 'required');

            $('.hoteladmin').hide();

            $('.admin').show();

        }

    });


    function changeCode(id) {

        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_code.php',

            'AJAX_DATA': 'id=' + id,

        };

        getDataFromAjaxCall(ajaxData, function (response) {

            if (response.action == "1") {

                var data = response.result;

                document.getElementById("code").value = data;

                $("#vContactNo-error").hide();

            } else {

                console.log(response.result);

            }

        });

    }

    function setState(id, selected) {


        $("#vState + em").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) ?>');

        $("#vCity + em").html('<?= addslashes($langage_lbl['LBL_SELECT_TXT']) ?>');


        var fromMod = 'profile';

        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',

            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},

        };

        getDataFromAjaxCall(ajaxData, function (response) {

            if (response.action == "1") {

                var dataHtml = response.result;

                $("#vCity").html('<option value=""><?= addslashes($langage_lbl['LBL_SELECT_TXT']) ?></option>');

                $("#vState").html(dataHtml);

                if (selected == '')

                    setCity('', selected);

            } else {

                console.log(response.result);

            }

        });

    }


    function setCity(id, selected) {

        var fromMod = 'profile';

        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',

            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},

        };

        getDataFromAjaxCall(ajaxData, function (response) {

            if (response.action == "1") {

                var dataHtml = response.result;

                $("#vCity").html(dataHtml);

            } else {

                console.log(response.result);

            }

        });

    }


    setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');

    changeCode('<?php echo $vCountry; ?>');

    setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');

    var map;

    function initMap() {

        map = GOOGLE_MAP_OBJ.init('map');

        $('#vAddress').keyup(function (e) {

            buildAutoComplete("vAddress", e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>", "<?=$_SESSION['sess_lang'];?>", function (latitude, longitude, address) {

                // show_locations();

                var marker = new google.maps.Marker({

                    map: map,

                    anchorPoint: new google.maps.Point(0, -29)

                });

                var location = new google.maps.LatLng(latitude, longitude);

                map.setCenter(location);

                map.setZoom(17);

                marker.setVisible(false);

                marker.setPosition(location);

                marker.setVisible(true);

                $("#vAddressLat").val(latitude);

                $("#vAddressLong").val(longitude);

            }); // (orignal function)

        });

        // var input = document.getElementById('vAddress');

        // // map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);


        // var autocomplete = new google.maps.places.Autocomplete(input);

        // autocomplete.bindTo('bounds', map);


        var marker = new google.maps.Marker({

            map: map,

            anchorPoint: new google.maps.Point(0, -29)

        });


        if ($("#vAddress").val() != "") {

            var myLatLng = new google.maps.LatLng($("#vAddressLat").val(), $("#vAddressLong").val());

            marker.setPosition(myLatLng);

            map.setCenter(myLatLng);

            map.setZoom(17);

            marker.setVisible(true);

        }

        var input = document.getElementById('vAddress');

        google.maps.event.addDomListener(input, 'keydown', function (event) {

            if (event.keyCode === 13) {

                event.preventDefault();

            }

        });
    }

    

    function DeleteMarkers(newId) {

        // Loop through all the markers and remove

        for (var i = 0; i < markers.length; i++) {

            if (newId != '') {

                if (markers[i].id == newId) {

                    markers[i].setMap(null);

                }

            } else {

                markers[i].setMap(null);

            }

        }

        if (newId == '') {

            markers = [];

        }

    }

    function setMarker(postitions, valIcon) {

        var marker = new google.maps.Marker({

            map: map,

            draggable: true,

            animation: google.maps.Animation.DROP,

            position: postitions,

        });

        marker.id = valIcon;

        markers.push(marker);

        map.setCenter(marker.getPosition());

        map.setZoom(15);

    }
</script>



<!-- END BODY-->
</html>
