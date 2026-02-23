<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess = isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'track_service_company';
$script = 'TrackServiceCompany';
if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $CONFIG_OBJ->getConfigurations("configurations", "SHOW_CITY_FIELD");
}
$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active' ORDER BY  vCountry ASC ";
$db_country = $obj->MySQLSelect($sql);
$sql = "SELECT vCode,vTitle FROM language_master WHERE eStatus = 'Active' order by vTitle asc";
$db_lang = $obj->MySQLSelect($sql);
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCaddress = isset($_POST['vCaddress']) ? $_POST['vCaddress'] : '';
$vZip = isset($_POST['vZip']) ? $_POST['vZip'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$vPass = encrypt_bycrypt($vPassword);
$vVatNum = isset($_POST['vVatNum']) ? $_POST['vVatNum'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vPass = ($vPassword != "") ? encrypt_bycrypt($vPassword) : '';

if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-company-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Company.';
        header("Location:track_service_company_action.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-company-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Company.';
        header("Location:track_service_company_action.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:track_service_company_action.php?id=" . $id . '&success=2');
        exit;
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vCompany'], 'req', 'Company Name is required');
    if ($ENABLE_EMAIL_OPTIONAL != "Yes") {
        $validobj->add_fields($_POST['vEmail'], 'req', 'Email Address is required.');
    }
    $validobj->add_fields($_POST['vEmail'], 'email', 'Please enter valid Email Address.');
    if ($action == "Add") {
        $validobj->add_fields($_POST['vPassword'], 'req', 'Password is required.');
    }
    $validobj->add_fields($_POST['vPhone'], 'req', 'Phone Number is required.');
    $validobj->add_fields($_POST['vCaddress'], 'req', 'Address is required.');
    $validobj->add_fields($_POST['vZip'], 'req', 'Zip Code is required.');
    $validobj->add_fields($_POST['vLang'], 'req', 'Language is required.');
    $validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');
    $error = $validobj->validate();
    $eSystem = "General";
    if ($vEmail != "") {
        $checEmailExist = checkMemberDataInfo($vEmail, "", 'TRACKING_COMPANY', $vCountry, $id, $eSystem);
        if ($checEmailExist['status'] == 0) {
            $error .= 'Email Address is already exists.<br>';
        } else if ($checEmailExist['status'] == 2) {
            $error .= $langage_lbl_admin['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
        }
    }
    $error .= $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');
    $eSystem = "";
    $checPhoneExist = checkMemberDataInfo($vPhone, "", 'TRACKING_COMPANY', $vCountry, $id, $eSystem);
    if ($checPhoneExist['status'] == 0) {
        $error .= 'Phone number already exists.<br>';
    } else if ($checPhoneExist['status'] == 2) {
        $error .= $langage_lbl_admin['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT'];
    }
    if ($error) {
        $success = 3;
        $newError = $error;
    } else {

        $sql = "select vPhoneCode from country where vCountryCode = '$vCountry'";
        $db_country_data = $obj->MySQLSelect($sql);
        if ($vCode == "") {
            $vCode = $db_country_data[0]['vPhoneCode'];
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Add') {
            $str = "`tRegistrationDate` = '" . date("Y-m-d H:i:s") . "', `eStatus` = 'Active',";
        } else {
            $str = '';
        }
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iTrackServiceCompanyId` = '" . $id . "'";
        }
        $passPara = '';
        if ($vPass != "") {
            $passPara = "`vPassword` = '" . $vPass . "',";
        }

        $query = "SELECT vImage FROM `track_service_company` {$where}";
        $track_service_company_Data = $obj->MySQLSelect($query);
        $oldImage = $track_service_company_Data[0]['vImage'];
        $csql = "SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vCode . "'";
        $CountryData = $obj->MySQLSelect($csql);
        /*$eZeroAllowed = $CountryData[0]['eZeroAllowed'];
        if ($eZeroAllowed == 'Yes') {
            $vPhone = $vPhone;
        } else {
            $first = substr($vPhone, 0, 1);
            if ($first == "0") {
                $vPhone = substr($vPhone, 1);
            }
        }*/

        $query = $q . " `" . $tbl_name . "` SET
			`vEmail` = '" . $vEmail . "',
			`vLocation` = '" . $vCaddress . "',
            `vZip` = '" . $vZip . "',
            $passPara
            `vPhone` = '" . $vPhone . "',
            `vLang` = '" . $vLang . "',
            `vState` = '" . $vState . "',
            `vCompany` = '" . $vCompany . "',
            `vVat` = '" . $vVatNum . "',
			`vCode` = '" . $vCode . "',
			
            $str
           `vCountry` = '" . $vCountry . "'"
            . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
        if ($_FILES['vImage']['name'] != "") {
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
            $img_path = $tconfig["tsite_upload_images_track_company_path"];
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
                    } else {
                        $final_width = $height;
                    }
                    $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                    $img1 = $UPLOAD_OBJ->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImgName = $img1;


            $sql = "UPDATE " . $tbl_name . " SET `vImage` = '" . $vImgName . "' WHERE `iTrackServiceCompanyId` = '" . $id . "'";

            $obj->sql_query($sql);
        }
        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }

      
        header("location:" . $backlink);
    }
}
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iTrackServiceCompanyId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vEmail = clearEmail($value['vEmail']);
            $vCompany = clearCmpName($value['vCompany']);
            $vCaddress = $value['vLocation'];
            $vZip = $value['vZip'];
            $vPassword = $value['vPassword'];
            $vCode = $value['vCode'];
            $vPhone = clearPhone($value['vPhone']);
            $oldImage = $value['vImage'];
            $vLang = $value['vLang'];
            $vCity = $value['vCity'];
            $vState = $value['vState'];
            $vInviteCode = $value['vInviteCode'];
            $vVatNum = $value['vVat'];
            $vCountry = $value['vCountry'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Company <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php
    include_once('global_files.php');
    ?>
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<body class="padTop53 ">
<div id="wrap">
    <?php
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $action; ?> Company <?= $vCompany; ?></h2>
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
                    <form autocomplete="off" name="_company_form" id="_company_form" method="post" action=""
                          enctype="multipart/form-data">
                        <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                        <input type="hidden" name="id" id="iCompanyId" value="<?php echo $id; ?>"/>
                        <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="company.php"/>
                        <?php if ($id) { ?>
                            <div class="row col-md-12" id="hide-profile-div">
                                <?php $class = ($SITE_VERSION == "v5") ? "col-lg-3" : "col-lg-4"; ?>
                                <div class="<?= $class ?>">
                                    <b>
                                        <?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                            <img src="../assets/img/profile-user-img.png" alt="">
                                            <?php
                                        } else {

                                            ;
                                            if (file_exists($tconfig["tsite_upload_images_track_company_path"] . '/' . $id . '/3_' . $oldImage)) {
                                                ?>
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=170&src=' . $tconfig["tsite_upload_images_track_company"] . '/' . $id . '/3_' . $oldImage ?>"
                                                     class="img-ipm"/>
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
                                                <h4 class="headind1"> Preferences:</h4>
                                            </legend>
                                            <p>
                                            <div class=""> <?php foreach ($data_driver_pref as $val) { ?>
                                                    <img data-toggle="tooltip" class="borderClass-aa1 border_class-bb1"
                                                         title="<?= $val['pref_Title'] ?>"
                                                         src="<?= $tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image'] ?>">
                                                <?php } ?>
                                            </div>
                                            <span class="col-md-12">
                                                <a href="" data-toggle="modal" data-target="#myModal"
                                                   id="show-edit-language-div" class="hide-language1">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i> Manage Preferences
                                                </a>
                                            </span>
                                            </p>
                                        </fieldset>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Company Name
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vCompany" id="vCompany"
                                       value="<?= $vCompany; ?>" placeholder="Company Name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Email<?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                                        <span class="red">
                                        *</span><?php } ?></label>
                            </div>
                            <div class="col-lg-6">
                                <input autocomplete="new-email" type="text" class="form-control" name="vEmail"
                                       id="vEmail" value="<?= $vEmail; ?>" placeholder="Email">
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
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Profile Picture</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="file" class="form-control" name="vImage" id="vImage"
                                       placeholder="Name Label" style="padding-bottom: 39px;">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Country
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <?php
                            if (scount($db_country) > 1) {
                                $style = "";
                            } else {
                                $style = " disabled=disabled";
                            } ?>
                            <div class="col-lg-6">
                                <select <?= $style ?> class="form-control" name='vCountry' id="vCountry"
                                                      onChange="setState(this.value, '');changeCode(this.value);"
                                                      required>
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
                                <label>State</label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='vState' id="vState"
                                        onChange="setCity(this.value, '');">
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
                                <label>Address
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vCaddress" id="vCaddress"
                                       value="<?= $vCaddress; ?>" placeholder="Address">
                                <input type="hidden" name="vLatitude" id="vLatitude"
                                       value="<?php echo $db_user[0]['vLatitude']; ?>">
                                <input type="hidden" name="vLongitude" id="vLongitude"
                                       value="<?php echo $db_user[0]['vLongitude']; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Zip Code
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vZip" id="vZip" value="<?= $vZip; ?>"
                                       placeholder="Zip Code">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Phone
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-select-2" id="code" name="vCode" value="<?= $vCode ?>"
                                       readonly style="width: 10%;height: 36px;text-align: center;"
                                / >
                                <input type="text" class="form-control" name="vPhone" id="vPhone"
                                       value="<?= $vPhone; ?>" placeholder="Phone" style="margin-top: 5px; width:90%;">
                            </div>
                        </div>
                        <?php if (scount($db_lang) <= 1) { ?>
                            <input name="vLang" type="hidden" class="create-account-input"
                                   value="<?php echo $db_lang[0]['vCode']; ?>"/>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Language
                                        <span class="red"> *</span>
                                    </label>
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
                                <label>VAT Number</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vVatNum" id="vVatNum"
                                       value="<?= $vVatNum; ?>" placeholder="VAT Number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-company-trackservice')) || ($action == 'Add' && $userObj->hasPermission('create-company-trackservice'))) { ?>
                                    <input type="submit" class="btn btn-default" name="submit" id="submit"
                                           value="<?php if ($action == 'Add') { ?><?= $action; ?> Company<?php } else { ?>Update<?php } ?>">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="company.php" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include_once('footer.php');
?>
</body>
</html>
<script>
    $('#_company_form').validate({
        rules: {
            vCompany: {
                required: true
            },
            vEmail: {
                <?php if ($ENABLE_EMAIL_OPTIONAL != 'Yes') { ?>
                required: true,
                <?php } ?>
                email: true
            },
            <?php if ($id == '') { ?>vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},<?php } ?>
            vCountry: {
                required: true
            },
            vState: {
                required: true
            },
            vZip: {
                required: true
            },
            vCaddress: {
                required: true
            },
            vPhone: {
                required: true, minlength: 3, digits: true
            },
            vLang: {
                required: true
            }
        },
        submitHandler: function (form) {
            $("#vCountry").prop('disabled', false);
            $("#vLang").prop('disabled', false);
            if ($(form).valid())
                form.submit();
            return false;
        }
    });
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "company.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });

    function setCity(id, selected) {
        var fromMod = 'company';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
            'REQUEST_DATA_TYPE': 'html'
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

    function setState(id, selected) {
        var fromMod = 'company';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vState").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                console.log(response.result);
            }
        });
    }

    setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
    setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');

    function changeCode(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id
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

    changeCode('<?php echo $vCountry; ?>');
    /*--------------------- autoCompleteAddress location ------------------*/
    var selected_u = false;
    $(function () {
        $('#vCaddress').keyup(function (e) {
            selected_u = false;
            buildAutoComplete("vCaddress", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>", function (latitude, longitude, address) {
                $("#vLatitude").val(latitude);
                $("#vLongitude").val(longitude);
                selected_u = true;
            });
        });
    });
    $('#vCaddress').on('focus', function () {
        if ($('#vLatitude').val() == "" || $('#vLongitude').val() == "") {
            selected_u = false;
        }
    }).on('blur', function () {
        setTimeout(function () {
            if (!selected_u) {
                $('#vCaddress').val('');
                $('#vLatitude').val('');
                $('#vLongitude').val('');
            }
        }, 500);
    });
    /*--------------------- autoCompleteAddress location ------------------*/
</script>