<?php
include_once('../common.php');
global $userObj;
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$sql = "SELECT vCountryCode,vCountry FROM country WHERE eStatus='Active' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);
$sql = "SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active' ORDER BY vTitle ASC";
$db_lang = $obj->MySQLSelect($sql);
$sql = "SELECT  iTrackServiceCompanyId,vCompany FROM `track_service_company` WHERE eStatus != 'Deleted'";
$trackServiceCompany = $obj->MySQLSelect($sql);
$sql = "SELECT vName,eDefault FROM currency WHERE eStatus='Active' ORDER BY vName ASC";
$db_currency = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : ''; // Added By HJ On 12-08-2019 For Edit eEnableDemoLocDispatch Value If QA User as Per Disucss WIth KS
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'register_driver';
$script = 'TrackServiceDriver';
// set all variables with either post (when submit) either blank (when insert)
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vUserName = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCaddress = isset($_POST['vCaddress']) ? $_POST['vCaddress'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vZip = isset($_POST['vZip']) ? $_POST['vZip'] : '';
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '0';
$iDriverVehicleId = isset($_POST['iDriverVehicleId']) ? $_POST['iDriverVehicleId'] : '';

$eReftype = "Driver";
$onlyDeliverallModule = ONLYDELIVERALL;
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem();
if ($cubeDeliverallOnly > 0) {
    $onlyDeliverallModule = "Yes";
}
if (isset($_POST['btnsubmit'])) {
    if ($SITE_VERSION == "v5") {
        $data_driver_pref = Update_User_Preferences($id, $_REQUEST);
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Preferences updated successfully.';
        header("Location:driver_action.php?id=" . $id);
        exit;
    }
}
if (isset($_POST['submit'])) {
    if ($action == 'Add' && !$userObj->hasPermission('create-driver-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:driver.php");
        exit;
    }
    if ($action == 'Edit' && !$userObj->hasPermission('edit-driver-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:driver.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') { // Added By NModi on 10-12-20
        // if (!empty($id) && SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:driver.php?id=" . $id);
        exit;
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vName'], 'req', ' Name is required');
    $validobj->add_fields($_POST['vLastName'], 'req', 'Last Name is required');
    if ($ENABLE_EMAIL_OPTIONAL != "Yes") {
        $validobj->add_fields(strtolower($_POST['vEmail']), 'req', 'Email Address is required.');
    }
    $validobj->add_fields(strtolower($_POST['vEmail']), 'email', 'Please enter valid Email Address.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
        if ($onlyDeliverallModule == 'Yes') {
            $validobj->add_fields($_POST['iCompanyId'], 'req', 'Company is required.');
        }
    }
    $validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
    $validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');
    $validobj->add_fields($_POST['vLang'], 'req', 'Language is required.');
    $error = $validobj->validate();
    $checPhoneExist = checkMemberDataInfo($vPhone, "", 'DRIVER', $vCountry, $id, $eSystem);
    if ($checPhoneExist['status'] == 0) {
        $error .= '* Phone number already exists.<br>';
    }
    else if ($checPhoneExist['status'] == 2) {
        $error .= $langage_lbl_admin['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
    }
    $error .= $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');
    if ($error) {
        $success = 3;
        $newError = $error;
    }
    else {
        $vRefCodePara = '';
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Edit') {
            $str = " ";
        }
        else {
            $str = " , eStatus = '$eStatus' ";
        }
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverId` = '" . $id . "'";
        }
        $str2 = $passPara = $str1 = $companyid = '';
        if ($action == 'Add') {
            $str1 = "`tRegistrationDate` = '" . date("Y-m-d H:i:s") . "',";
            $companyid = "`iTrackServiceCompanyId` = '" . $iCompanyId . "',";
        }
        if ($vPassword != "") {
            $passPara = "`vPassword` = '" . encrypt_bycrypt($vPassword) . "',";
        }
        if ($id != '') {
            $companyid = "`iTrackServiceCompanyId` = '" . $iCompanyId . "',";
            $q = "UPDATE ";
            $where = " WHERE `iDriverId` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET
                `vName` = '" . $vName . "',
                `vLastName` = '" . $vLastName . "',
                `vCountry` = '" . $vCountry . "',
                `vCaddress` = '" . $vCaddress . "',
                `vCity` = '" . $vCity . "',
                `vZip` = '" . $vZip . "',
                `vState` = '" . $vState . "',
                `vCode` = '" . $vCode . "',
                `vEmail` = '" . $vEmail . "',
                `vLoginId` = '" . $vEmail . "',
                `iCompanyId` = '1',
                 $passPara      
                $companyid
                `vPhone` = '" . $vPhone . "',
                `vImage` = '" . $oldImage . "',
                `iDriverVehicleId` = '" . $iDriverVehicleId . "',
                `vLang` = '" . $vLang . "' $str" . $where;
        $obj->sql_query($query);
        if ($id == "") {
            $id = $obj->GetInsertId();
        }
        if ($_FILES['vImage']['name'] != "") {
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $img_path = $tconfig["tsite_upload_images_driver_path"];
            $temp_gallery = $img_path . '/';
            $check_file = $img_path . '/' . $id . '/' . $oldImage;
            if ($oldImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $id . '/' . $oldImage);
                @unlink($img_path . '/' . $id . '/1_' . $oldImage);
                @unlink($img_path . '/' . $id . '/2_' . $oldImage);
                @unlink($img_path . '/' . $id . '/3_' . $oldImage);
            }
            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            if ($img1 != '') {
                if (is_file($Photo_Gallery_folder . $img1)) {
                    include_once(TPATH_CLASS . "/SimpleImage.class.php");
                    $img = new SimpleImage();
                    list($width, $height, $type, $attr) = getimagesize($Photo_Gallery_folder . $img1);
                    if ($width < $height) {
                        $final_width = $width;
                    }
                    else {
                        $final_width = $height;
                    }
                    $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                    $img1 = $UPLOAD_OBJ->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImgName = $img1;
            $sql = "UPDATE " . $tbl_name . " SET `vImage` = '" . $vImgName . "' WHERE `iDriverId` = '" . $id . "'";
            $obj->sql_query($sql);
        }

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        header("Location:" . $backlink);
        exit;
    }
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iDriverId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    // $vPass = decrypt($db_data[0]['vPassword']);
    if ($db_data[0]['eStatus'] == "active") {
        $actionType = "approve";
    }
    else {
        $actionType = "pending";
    }
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vName = clearName(" " . $value['vName']);;
            $iCompanyId = $value['iCompanyId'];
            $vLastName = clearName(" " . $value['vLastName']);
            $vCaddress = $value['vCaddress'];
            $vCountry = $value['vCountry'];
            $vCity = $value['vCity'];
            $vZip = $value['vZip'];
            $vState = $value['vState'];
            $vCode = $value['vCode'];
            $vEmail = clearEmail($value['vEmail']);
            $vUserName = $value['vLoginId'];
            $vPassword = $value['vPassword'];
            $eGender = $value['eGender'];
            $vPhone = clearPhone($value['vPhone']);
            $vLang = $value['vLang'];
            $oldImage = $value['vImage'];
            $vCurrencyDriver = $value['vCurrencyDriver'];
            $vPaymentEmail = $value['vPaymentEmail'];
            $vBankAccountHolderName = $value['vBankAccountHolderName'];
            $vAccountNumber = $value['vAccountNumber'];
            $vBankLocation = $value['vBankLocation'];
            $vBankName = $value['vBankName'];
            $vBIC_SWIFT_Code = $value['vBIC_SWIFT_Code'];
            $tProfileDescription = $value['tProfileDescription'];
            $eEnableDemoLocDispatch = $value['eEnableDemoLocDispatch'];
            $iTrackServiceCompanyId = $value['iTrackServiceCompanyId'];
            $iDriverVehicleId = $value['iDriverVehicleId'];
        }

        $db_vehicles = $obj->MySQLSelect("SELECT dv.iDriverVehicleId, dv.vLicencePlate, dv.iDriverId, m.vMake, md.vTitle FROM driver_vehicle as dv LEFT JOIN register_driver as rd ON rd.iDriverId = dv.iDriverId LEFT JOIN make as m ON m.iMakeId = dv.iMakeId LEFT JOIN model as md ON md.iModelId = dv.iModelId WHERE rd.iDriverId = '" . $id . "' AND dv.eType = 'TrackService' AND dv.eStatus = 'Active' ");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>  <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
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
                    <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>  <?= $vName; ?></h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <?php } ?>
                    <?php if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php print_r($error); ?>
                        </div><br/>
                    <?php } ?>
                    <form id="_driver_form" name="_driver_form" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                        <input type="hidden" name="id" id="iDriverId" value="<?= $id; ?>"/>
                        <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="driver.php"/>
                        <?php if ($id) { ?>
                            <div class="row col-md-12" id="hide-profile-div">
                                <?php $class = ($SITE_VERSION == "v5") ? "col-lg-3" : "col-lg-4"; ?>
                                <div class="<?= $class ?>">
                                    <b>
                                        <?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                            <img src="../assets/img/profile-user-img.png" alt="">
                                            <?php
                                        }
                                        else {
                                            if (file_exists('../webimages/upload/Driver/' . $id . '/3_' . $oldImage)) {
                                                ?>
                                                <!--  <img src = "<?php echo $tconfig["tsite_upload_images_driver"] . '/' . $id . '/3_' . $oldImage ?>" class="img-ipm" /> -->
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=170&src=' . $tconfig["tsite_upload_images_driver"] . '/' . $id . '/3_' . $oldImage ?>" class="img-ipm"/>
                                            <?php } else { ?>
                                                <img src="../assets/img/profile-user-img.png" alt="">
                                                <?php
                                            }
                                        }
                                        ?>
                                    </b>
                                </div>
                                <?php if ($SITE_VERSION == "v5") { ?>
                                    <div class="col-lg-4">
                                        <fieldset class="col-md-12 field">
                                            <legend class="lable">
                                                <h4 class="headind1"> Preferences: </h4>
                                            </legend>
                                            <p>
                                            <div class=""> <?php foreach ($data_driver_pref as $val) { ?>
                                                    <img data-toggle="tooltip" class="borderClass-aa1 border_class-bb1" title="<?= $val['pref_Title'] ?>" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image'] ?>">
                                                <?php } ?>
                                            </div>
                                            <span class="col-md-12">
                                                <a href="" data-toggle="modal" data-target="#myModal" id="show-edit-language-div" class="hide-language1">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i> Manage Preferences
                                                </a>
                                            </span></p>
                                        </fieldset>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>First Name<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vName" id="vName" value="<?= $vName; ?>" placeholder="First Name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Last Name<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vLastName" id="vLastName" value="<?= $vLastName; ?>" placeholder="Last Name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Email <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?><span class="red">
                                        *</span> <?php } ?></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="Email">
                            </div>
                            <div id="emailCheck"></div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Password <span class="red"> *</span>
                                    <?php if ($action == 'Edit') { ?>
                                        <span>&nbsp;[Leave blank to retain assigned password.]</span>
                                    <?php } ?>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="password" class="form-control" name="vPassword" id="vPassword" value="" placeholder="Password" autocomplete="new-password">
                            </div>
                        </div>
                        <?php /*
                                <!--<div class="row">
                                    <div class="col-lg-12">
                                      <label>Birth date <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                      <input type="text" id="dp5" name="dBirthDate" placeholder="From Date"  readonly class="form-control" value="<?= $dBirthDate ?>" style="cursor:default; background-color: #fff" required />
                                    </div>
                                    </div>-->
                                <!--                                    <div class="row">
                                    <div class="col-md-6">
                                            <label class="date-birth">
                                    <?= $langage_lbl_admin['LBL_Date_of_Birth']; ?><label>
                                            <select name="vDay" Id="vDay" data="DD" class="custom-select-new required">
                                                    <option value="">Date</option>
                                    <?php for ($i = 1; $i <= 31; $i++) { ?>
                                                                                <option value="<?= $i ?>" <?= ($i == $dBirthDay ) ? 'Selected' : ''; ?>>
                                    <?= $i ?>
                                                                                </option>
                                    <?php } ?>
                                            </select>
                                            <select data="MM" Id="vMonth" name="vMonth" class="custom-select-new required" >
                                                    <option value="">Month</option>
                                    <?php for ($i = 1; $i <= 12; $i++) { ?>
                                                                                <option value="<?= $i ?>" <?= ($i == $dBirthMonth ) ? 'Selected' : ''; ?>>
                                    <?= $i ?>
                                                                                </option>
                                    <?php } ?>
                                            </select>
                                            <select data="YYYY" Id="vYear" name="vYear" class="custom-select-new required">
                                                    <option value="">Year</option>
                                    <?php for ($i = (date("Y") - $START_BIRTH_YEAR_DIFFERENCE); $i >= (date("Y") - $BIRTH_YEAR_DIFFERENCE); $i--) { ?>
                                                                                <option value="<?= $i ?>" <?= ($i == $dBirthYear ) ? 'Selected' : ''; ?>>
                                    <?= $i ?>
                                                                                </option>
                                    <?php } ?>
                                            </select>

                                    </div>
                                    </div> -->  */ ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Profile Picture</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="file" class="form-control" name="vImage" id="vImage" placeholder="Name Label" style="padding-bottom: 39px;">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Country <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <?php
                                if (scount($db_country) > 1) {
                                    $style = "";
                                }
                                else {
                                    $style = " disabled=disabled";
                                } ?>
                                <select <?= $style ?> class="form-control" name='vCountry' id="vCountry" onChange="setState(this.value, ''), changeCode(this.value);">
                                    <?php
                                    if (scount($db_country) > 1) { ?>
                                        <option value="">Select</option>
                                    <?php } ?>
                                    <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                        <option value="<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>State<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='vState' id="vState" onChange="setCity(this.value, '');">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                        <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>City</label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name='vCity' id="vCity">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Address<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vCaddress" id="vCaddress" value="<?= $vCaddress ?>" placeholder="Address">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl_admin['LBL_ZIP_CODE_SIGNUP']; ?><span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vZip" id="vZip" value="<?= $vZip; ?>" placeholder="<?= $langage_lbl['LBL_ZIP_CODE_SIGNUP']; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Phone<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>" readonly style="width: 10%;height: 36px;text-align: center;"
                                / >
                                <input type="text" class="form-control" style="margin-top: 5px; width:90%;" name="vPhone" id="vPhone" value="<?= $vPhone; ?>" placeholder="Phone">
                            </div>
                        </div>
                        <div class="row" id="companylisthtml" <?= $company_select_disable ?>>
                            <div class="col-lg-12">
                                <label>Company<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='iCompanyId' id='iCompanyId' required="required">
                                    <option value="">--select--</option>
                                    <?php
                                    for ($i = 0; $i < scount($trackServiceCompany); $i++) {
                                        $status_cmp = ($trackServiceCompany[$i]['eStatus'] == "Inactive") ? " (Inactive)" : "";
                                        ?>
                                        <option value="<?= $trackServiceCompany[$i]['iTrackServiceCompanyId'] ?>" <?= ($trackServiceCompany[$i]['iTrackServiceCompanyId'] == $iTrackServiceCompanyId) ? 'selected' : ''; ?>>
                                            <?= clearCmpName($trackServiceCompany[$i]['vCompany'] . $status_cmp); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <?php if($action == "Edit") { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Vehicle<span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name="iDriverVehicleId" id="iDriverVehicleId" required>
                                        <option value="">--Select--</option>
                                        <?php for ($i = 0; $i < scount($db_vehicles); $i++) { ?>
                                            <option value="<?= $db_vehicles[$i]['iDriverVehicleId'] ?>" <?php if ($iDriverVehicleId == $db_vehicles[$i]['iDriverVehicleId']) { ?> selected <?php } ?>>
                                                <?= $db_vehicles[$i]['vMake'] . ' ' . $db_vehicles[$i]['vTitle'] . ' (' . $db_vehicles[$i]['vLicencePlate'] . ')' ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (scount($db_lang) <= 1) { ?>
                            <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Language<span class="red"> *</span></label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control" name='vLang' id='vLang'>
                                        <option value="">--select--</option>
                                        <?php for ($i = 0; $i < scount($db_lang); $i++) { ?>
                                            <option value="<?= $db_lang[$i]['vCode'] ?>" <?= ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>>
                                                <?= $db_lang[$i]['vTitle'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        

                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Add' && $userObj->hasPermission('create-driver-trackservice')) || ($action == 'Edit' && $userObj->hasPermission('edit-driver-trackservice'))) { ?>
                                    <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?><?php } else { ?>Update<?php } ?>">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <!-- <a href="javascript:void(0);" onClick="reset_form('_driver_form');" class="btn btn-default">Reset</a> -->
                                <a href="driver.php" class="btn btn-default back_link">Cancel</a>
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
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-medium">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title " id="myModalLabel">
                    Manage <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Preferences</h4>
            </div>
            <div class="modal-body">
                <span>
                    <form name="frm112" action="" method="POST">
                        <?php foreach ($data_preference as $value) { ?>
                            <div class="preferences-chat">
                                <b class="car-preferences-right-part1"><?= $value['vName'] ?></b>
                                <b class="car-preferences-right-part-a">
                                    <span data-toggle="tooltip" title="<?= $value['vYes_Title'] ?>">
                                        <a href="#">
                                            <img class="borderClass-aa1 borderClass-aa2" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $value['vPreferenceImage_Yes'] ?>" alt="" id="img_Yes_<?= $value['iPreferenceId'] ?>" onClick="checked_val('<?= $value['iPreferenceId'] ?>', 'Yes')"/>
                                        </a>
                                    </span></b>
                                <b class="car-preferences-right-part-a"><span data-toggle="tooltip" title="<?= $value['vNo_Title'] ?>">
                                        <a href="#">
                                            <img class="borderClass-aa1 borderClass-aa2" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $value['vPreferenceImage_No'] ?>" alt="" id="img_No_<?= $value['iPreferenceId'] ?>" onClick="checked_val('<?= $value['iPreferenceId'] ?>', 'No')"/>
                                        </a>
                                    </span></b>
                            </div><span style="display:none;">
                                <input type="radio" name="vChecked_<?= $value['iPreferenceId'] ?>" id="Yes_<?= $value['iPreferenceId'] ?>" value="Yes">
                                <input type="radio" name="vChecked_<?= $value['iPreferenceId'] ?>" id="No_<?= $value['iPreferenceId'] ?>" value="No">
                            </span>
                        <?php } ?>
                        <p class="car-preferences-right-part-b">
                            <input name="btnsubmit" type="submit" value="<?= $langage_lbl_admin['LBL_Save']; ?>" class="save-but1">
                        </p>
                    </form>
                </span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php include_once('footer.php'); ?>
<script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    $('#_driver_form').validate({
        rules: {
            vName: {
                required: true,
                minlength: 1,
                maxlength: 30
            },
            vLastName: {
                required: true,
                minlength: 1,
                maxlength: 30
            },
            vEmail: {
                <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                required: true,
                <?php } ?>
                email: true
            },
            <?php if ($id == '') { ?>
            vPassword: {
                required: true,
                noSpace: true,
                minlength: 6,
                maxlength: 16
            },
            <?php } ?>
            vCountry: {
                required: true
            },
            vPhone: {
                required: true,
                minlength: 3,
                digits: true
            },
            vLang: {
                required: true
            },
            <?php
            if ($onlyDeliverallModule == 'No') { ?>
            iCompanyId: {
                required: true
            },
            <?php } ?>
            vState: {
                required: true
            },
            vCaddress: {
                required: true
            },
            vZip: {
                required: true
            },
            vCurrencyDriver: {
                required: true
            }
        },
        submitHandler: function (form) {
            $("#vCountry").prop('disabled', false);
            if ($(form).valid())
                form.submit();
            return false; // prevent normal form posting
        }
    });

    var selCompanyId = '<?= $iCompanyId; ?>';
    

    function changeCode(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            }
            else {
                console.log(response.result);
            }
        });
    }

    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        }
        else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "driver.php";
        }
        else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
        var date = new Date();
        var currentMonth = date.getMonth();
        var currentDate = date.getDate();
        var currentYear = date.getFullYear();
    });

    function setCity(id, selected) {
        var fromMod = 'driver';

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCity").html(dataHtml);
            }
            else {
                console.log(response.result);
            }
        });
    }

    function setState(id, selected) {
        var fromMod = 'driver';

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vState").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            }
            else {
                console.log(response.result);
            }
        });
    }

    $('#dp5').datepicker({
        maxDate: 0,
        onRender: function (date) {
            return date.valueOf() > new Date().valueOf() ? 'disabled' : '';
        }
    });
    setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
    changeCode('<?php echo $vCountry; ?>');
    setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');

    function checked_val(id, value) {
        $("#img_Yes_" + id).removeClass('border_class-aa1');
        $("#img_No_" + id).removeClass('border_class-aa1');
        $("#img_" + value + "_" + id).addClass('border_class-aa1');
        $("#Yes_" + id).prop("checked", false);
        $("#No_" + id).prop("checked", false);
        $("#" + value + "_" + id).prop("checked", true);
        return false;
    }

    $(window).on("load", function () {
        <?php
        if (scount($data_driver_pref) > 0) {
        ?>
        var dataarr = '<?= json_encode($data_driver_pref) ?>';
        var arr1 = JSON.parse(dataarr);
        for (var i = 0; i < arr1.length; i++) {
            checked_val(arr1[i].pref_Id, arr1[i].pref_Type)
        } <?php
        } ?>
    });
</script>
</body>
<!-- END BODY-->
</html>