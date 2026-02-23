<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$sql = "SELECT * FROM country WHERE eStatus='Active' ORDER BY vCountry ASC";
$db_country = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$script = 'TrackServiceUser';
$tbl_name = 'track_service_users';
$sql = "SELECT * FROM language_master WHERE eStatus = 'Active' ORDER BY vTitle ASC ";
$db_lang = $obj->MySQLSelect($sql);
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? strtolower($_POST['vEmail']) : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$oldImage = isset($_POST['oldImage']) ? $_POST['oldImage'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = ($vPassword != "") ? encrypt_bycrypt($vPassword) : '';
$eGender = isset($_POST['eGender']) ? $_POST['eGender'] : '';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vLocation = isset($_POST['vLocation']) ? $_POST['vLocation'] : '';
$vLongitude = isset($_POST['vLongitude']) ? $_POST['vLongitude'] : '';
$vLatitude = isset($_POST['vLatitude']) ? $_POST['vLatitude'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
$iTrackServiceCompanyId = isset($_POST['iTrackServiceCompanyId']) ? $_POST['iTrackServiceCompanyId'] : '';
$eReftype = "Rider";
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-users-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']) . '.';
        header("Location:track_service_user.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-users-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']) . '.';
        header("Location:track_service_user.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:track_service_user.php?id=" . $id);
        exit;
    }
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vName'], 'req', ' Name is required.');
    $validobj->add_fields($_POST['vLastName'], 'req', 'Last name is required.');
    if ($ENABLE_EMAIL_OPTIONAL != "Yes") {
        $validobj->add_fields(strtolower($_POST['vEmail']), 'req', 'Email address is required.');
    }
    $validobj->add_fields(strtolower($_POST['vEmail']), 'email', '* Please enter valid Email Address.');
    $validobj->add_fields($_POST['vPhone'], 'req', 'Phone number is required.');
    $validobj->add_fields($_POST['vCountry'], 'req', 'Country is required.');
    $error = $validobj->validate();
    $eSystem = "";
    $CountryData = $obj->MySQLSelect("SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $vPhoneCode . "'");
    /*$eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    if ($eZeroAllowed == 'Yes') {
        $vPhone = $vPhone;
    }
    else {
        $first = substr($vPhone, 0, 1);
        if ($first == "0") {
            $vPhone = substr($vPhone, 1);
        }
    }*/
    $error .= $validobj->validateFileType($_FILES['vImage'], 'jpg,jpeg,png,gif,bmp', '* Image file is not valid.');
    if ($error) {
        $success = 3;
        $newError = $error;
    }
    else {
        $vRefCodePara = '';
        $strng = '';
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iTrackServiceUserId` = '" . $id . "'";
        }
        if ($action == "Add") {
            //$InviteCode = $vInviteCode = RandomString('10', 'Yes');
            $InviteCode = $TRACK_SERVICE_OBJ->GenerateInviteCode();
            $vInviteCode = "`vInviteCode` = '" . $InviteCode . "',";
            $dAddedDate = "`dAddedDate` = '" . date('Y-m-d H:i:s') . "',";
        }
        $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $vName . "',
			`vLastName` = '" . $vLastName . "',
			`vEmail` = '" . $vEmail . "',
			$vInviteCode
			$dAddedDate
			`vPhone` = '" . $vPhone . "',	
			`iDriverId` = '" . $iDriverId . "',	
			`vCountry` = '" . $vCountry . "',
			`vPhoneCode` = '" . $vPhoneCode . "',
			`eStatus` = '" . $eStatus . "',
			`vLocation` = '" . $vLocation . "',
			`vLatitude` = '" . $vLatitude . "',
			`vLongitude` = '" . $vLongitude . "',
			`vAddress` = '" . $vAddress . "',
			`iTrackServiceCompanyId` = '" . $iTrackServiceCompanyId . "'
			" . $where;
        $obj->sql_query($query);
        if ($id == "") {
            $id = $obj->GetInsertId();

            if (!empty($vEmail)) {
                $maildata['vEmail'] = $vEmail;
                $maildata['NAME'] = $vName . ' ' . $vLastName;
                $maildata['INVITECODE'] = $InviteCode;
                $COMM_MEDIA_OBJ->SendMailToMember("TRACK_COMPANY_USER_INVITECODE_SEND", $maildata);
            }
            $vLangCode = $LANG_OBJ->FetchDefaultLangData("vCode");
            $dataArraySMSNew['NAME'] = $vName . ' ' . $vLastName;
            $dataArraySMSNew['INVITECODE'] = $InviteCode;
            $message = $COMM_MEDIA_OBJ->GetSMSTemplate('TRACK_COMPANY_USER_INVITECODE_SEND', $dataArraySMSNew, "", $vLangCode);
            $result = $COMM_MEDIA_OBJ->SendSystemSMS($vPhone, $CountryData[0]['vPhoneCode'], $message);
        }
        if ($_FILES['vImage']['name'] != '') {
            $img_path = $tconfig["tsite_upload_images_track_company_user_path"];
            $temp_gallery = $img_path . '/';
            $image_object = $_FILES['vImage']['tmp_name'];
            $image_name = $_FILES['vImage']['name'];
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
                    $final_width = $height;
                    if ($width < $height) {
                        $final_width = $width;
                    }
                    $img->load($Photo_Gallery_folder . $img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder . $img1);
                    $img1 = $UPLOAD_OBJ->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], "");
                }
            }
            $vImage = $img1;
            $sql = "UPDATE " . $tbl_name . " SET `vImage` = '" . $vImage . "' WHERE `iTrackServiceUserId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        }
        else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }
        header("Location:" . $backlink);
        exit;
    }
}
if ($action == 'Edit') {
    $sql = "SELECT iDriverId,iTrackServiceCompanyId,vImage,vAddress,vLongitude,vLatitude,vLocation,iUserId,vName,vLastName,vEmail,vPhone,vPhoneCode,vCountry,vInviteCode,eStatus FROM " . $tbl_name . " WHERE iTrackServiceUserId  = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vName = htmlentities(clearName(" " . $value['vName']));
            $vLastName = clearName(" " . $value['vLastName']);
            $vEmail = clearEmail($value['vEmail']);
            $vPhone = clearPhone($value['vPhone']);
            $vPhoneCode = clearPhone($value['vPhoneCode']);
            $vCountry = $value['vCountry'];
            $vInviteCode = $value['vInviteCode'];
            $eStatus = $value['eStatus'];
            $vLongitude = $value['vLongitude'];
            $vLatitude = $value['vLatitude'];
            $vLocation = $value['vLocation'];
            $vAddress = $value['vAddress'];
            $oldImage = $value['vImage'];
            $iDriverId = $value['iDriverId'];
            $iTrackServiceCompanyId = $value['iTrackServiceCompanyId'];
        }
    }
}
$sql = "SELECT  iTrackServiceCompanyId,vCompany FROM `track_service_company` WHERE eStatus != 'Deleted'";
$trackServiceCompany = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?>  <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END HEAD-->
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
                    <h2><?= $action; ?> <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> <?= $vName; ?> <?= $vLastName; ?></h2>
                    <a class="back_link" href="track_service_user.php">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php print_r($error); ?>
                        </div><br/>
                    <?php } ?>
                    <form method="post" action="" enctype="multipart/form-data" id="_rider_form" name="_rider_form">
                        <input type="hidden" name="actionOf" id="actionOf" value="<?php echo $action; ?>"/>
                        <input type="hidden" name="id" id="iUserId" value="<?= $id; ?>"/>
                        <input type="hidden" name="oldImage" value="<?= $oldImage; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="track_service_user.php"/>
                        <?php if ($id) { ?>
                            <div class="row" id="hide-profile-div">
                                <div class="col-lg-4">
                                    <b>
                                        <?php if ($oldImage == 'NONE' || $oldImage == '') { ?>
                                            <img src="../assets/img/profile-user-img.png" alt="212121">
                                            <?php
                                        }
                                        else {
                                            if (file_exists($tconfig["tsite_upload_images_track_company_user_path"] . '/' . $id . '/3_' . $oldImage)) {
                                                ?>
                                                <img src="<?= $tconfig["tsite_url"] . 'resizeImg.php?h=150&src=' . $tconfig["tsite_upload_images_track_company_user"] . '/' . $id . '/3_' . $oldImage; ?>" style="height:150px;"/>
                                            <? } else { ?>
                                                <img src="../assets/img/profile-user-img.png" alt="ereerr">
                                                <?php
                                            }
                                        }
                                        ?>
                                    </b>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Company <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select onchange="changeCompnay(this.value)" class="form-control" id='iTrackServiceCompanyId' name='iTrackServiceCompanyId'>
                                    <option value="">Select Company</option>
                                    <?php foreach ($trackServiceCompany as $company) { ?><?php
                                        $selected = '';
                                        if ($company['iTrackServiceCompanyId'] == $iTrackServiceCompanyId) {
                                            $selected = "selected";
                                        }
                                        ?>
                                        <option <?php echo $selected; ?> value="<?php echo $company['iTrackServiceCompanyId']; ?>"><?php echo $company['vCompany']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <?php /*<div class="row">
                            <div class="col-lg-12">
                                <label>Driver</label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='iDriverId' id="iDriverId"> </select>
                            </div>
                        </div> */?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>First Name <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vName" id="vName" value="<?= $vName; ?>" placeholder="First Name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Last Name <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vLastName" id="vLastName" value="<?= $vLastName; ?>" placeholder="Last Name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Email <? if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?><span class="red">
                                        *</span> <? } ?></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vEmail" id="vEmail" value="<?= $vEmail; ?>" placeholder="Email"/>
                            </div>
                            <label id="emailCheck">
                                <label>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Vehicle</label>
                            </div>
                            <div class="col-lg-6">
                                <select name="iDriverId" id="iDriverId" class="form-control"></select>
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
                                <select <?= $style ?> class="form-control" id='vCountry' name='vCountry' onChange="changeCode(this.value);">
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
                            <div class="col-lg-12" style="width:30%">
                                <label>Phone<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6" style="width:50%">
                                <input type="text" class="form-select-2 form-select-21" id="code" readonly name="vPhoneCode" value="<?= $vPhoneCode ?>">
                                <input type="text" class="mobile-text form-control form-select-3" name="vPhone" id="vPhone" value="<?= $vPhone; ?>" placeholder="Phone">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Location <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vLocation" id="vLocation" value="<?php echo $vLocation; ?>" placeholder="Location">
                            </div>
                            <input type="hidden" name="vLatitude" id="vLatitude" value="<?php echo $vLatitude; ?>">
                            <input type="hidden" name="vLongitude" id="vLongitude" value="<?php echo $vLongitude; ?>">
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Address <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vAddress" id="vAddress" value="<?php echo $vAddress; ?>" placeholder="Address">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Profile Picture</label>
                            </div>
                            <div class="col-lg-6">
                                <input type="file" class="form-control" name="vImage" id="vImage" placeholder="Name Label" accept='image/*'>
                            </div>
                        </div>
                        <?php if ($eStatus != 'Deleted') { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Status</label>
                                </div>
                                <div class="col-lg-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> value="1"/>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="eStatus" id="eStatus" value="Deleted"/>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-users-trackservice')) || ($action == 'Add' && $userObj->hasPermission('create-users-trackservice'))) { ?>
                                    <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?><?php } else { ?>Update<?php } ?>">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <!-- <a href="javascript:void(0);" onClick="reset_form('_rider_form');" class="btn btn-default">Reset</a> -->
                                <a href="track_service_user.php" class="btn btn-default back_link">Cancel</a>
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
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>

    $('#_rider_form').validate({
        rules: {
            vName: {
                required: true
            },
            vLastName: {
                required: true
            },
            vEmail: {
                <? if($ENABLE_EMAIL_OPTIONAL != "Yes") {?>
                required: true,
                <? } ?>
                email: true
            },
            <?php if ($id == '') { ?>vPassword: {required: true, noSpace: true, minlength: 6, maxlength: 16},<?php } ?>
            vCountry: {
                required: true
            },
            vPhone: {
                required: true, minlength: 3, digits: true
            },
            vLang: {
                required: true
            },
            iTrackServiceCompanyId: {
                required: true
            },
            vLocation: {
                required: true
            },
            vZip: {
                required: true
            },
            vCaddress: {
                required: true
            },
            vAddress: {
                required: true
            },
            iDriverId: {
                required: true
            },
        },
        submitHandler: function (form) {
            $("#vCountry").prop('disabled', false);
            if ($(form).valid())
                form.submit();
            return false;
        }
    });
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        }
        else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "vehicles.php";
        }
        else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
        var iCompanyId = '<?php echo $iTrackServiceCompanyId; ?>';
        changeCompnay(iCompanyId);
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
            }
            else {
                console.log(response.result);
            }
        });
    }

    changeCode('<?php echo $vCountry; ?>');
    /*--------------------- autoCompleteAddress location ------------------*/
    var selected_u = false;
    $(function () {
        $('#vLocation').keyup(function (e) {
            selected_u = false;
            buildAutoComplete("vLocation", e, "<?= $MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE; ?>", "<?= $_SESSION['sess_lang']; ?>", function (latitude, longitude, address) {
                $("#vLatitude").val(latitude);
                $("#vLongitude").val(longitude);
                selected_u = true;
            });
        });
    });
    $('#vLocation').on('focus', function () {
        if ($('#vLatitude').val() == "" || $('#vLongitude').val() == "") {
            selected_u = false;
        }
    }).on('blur', function () {
        setTimeout(function () {
            if (!selected_u) {
                $('#vLocation').val('');
                $('#vLatitude').val('');
                $('#vLongitude').val('');
            }
        }, 500);
    });
    /*--------------------- autoCompleteAddress location ------------------*/

    /*--------------------- company wise driver get ------------------*/
    function changeCompnay(iCompanyId) {
        var iDriverId = "<?php echo $iDriverId; ?>";
        if (iCompanyId > 0) {
            if (iCompanyId > 0) {
                $(".slotTime , .TimeField").val('')
                $(".slotTime , .TimeField").prop('disabled', false);
            }
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_track_service_company_driver.php',
                'AJAX_DATA': {
                    module: 'track_service',
                    iCompanyId: iCompanyId,
                    iDriverId: iDriverId,
                },
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    try {
                        var returnedData = JSON.parse(data);
                        console.log(returnedData);
                        $("#iDriverId").html('');
                        $("#iDriverId").html(returnedData.company_option_html);
                    } catch (error) {
                        console.log(error);
                        //handle error -> visual feedback for user
                    }
                }
                else {
                    // console.log(response.result);
                }
            });
        }
    }

    /*--------------------- company wise driver get ------------------*/
</script>
</body>
<!-- END BODY-->
</html>
