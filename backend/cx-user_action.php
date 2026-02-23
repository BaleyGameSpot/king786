<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();

$abc = "tracking_company";
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$script = 'user';
$iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vLocation = isset($_POST['vLocation']) ? $_POST['vLocation'] : '';
$vLatitude = isset($_POST['vLatitude']) ? $_POST['vLatitude'] : '';
$vLongitude = isset($_POST['vLongitude']) ? $_POST['vLongitude'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
$action = ($id != '') ? 'Edit' : 'Add';
if (isset($_POST['btn_submit'])) {
    if (isset($_FILES['vImage'])) {
        $img_path = $tconfig["tsite_upload_images_track_company_user_path"];
        $temp_gallery = $img_path . '/';
        $image_object = $_FILES['vImage']['tmp_name'];
        $image_name = $_FILES['vImage']['name'];
        if (!empty($id)) {
            $check_file_query = "SELECT iTrackServiceUserId, vImage from track_service_users where iTrackServiceUserId = " . $id;
            $check_file = $obj->sql_query($check_file_query);
            $vImage = $check_file[0]['vImage'];
        }
        if ($image_name != "") {
            if (!empty($id)) {
                $check_file['vImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vImage'];
                if ($check_file['vImage'] != '' && file_exists($check_file['vImage'])) {
                    unlink($img_path . '/' . $id . '/' . $check_file[0]['vImage']);
                    unlink($img_path . '/' . $id . '/1_' . $check_file[0]['vImage']);
                    unlink($img_path . '/' . $id . '/2_' . $check_file[0]['vImage']);
                    unlink($img_path . '/' . $id . '/3_' . $check_file[0]['vImage']);
                }
            }
            $filecheck = basename($_FILES['vImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $flag_error = 0;
            if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                $flag_error = 1;
                $var_msg = $langage_lbl['LBL_UPLOAD_IMG_ERROR'];
            }
            if ($flag_error == 1) {
                getPostForm($_POST, $var_msg, "trackinguseraction?success=0&var_msg=" . $var_msg);
                exit;
            } else {
                $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                    chmod($Photo_Gallery_folder, 0777);
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
                $vImage = $img1;
            }
        }
    }
    $sql = "SELECT vPhoneCode FROM `country` WHERE vCountryCode = '" . $vCode . "'";
    $CountryData = $obj->MySQLSelect($sql);
    $data['vName'] = $vName;
    $data['vLastName'] = $vLastName;
    $data['vEmail'] = $vEmail;
    $data['vPhoneCode'] = $CountryData[0]['vPhoneCode'];
    $data['vCountry'] = $vCode;
    $data['vPhone'] = $vPhone;
    $data['vAddress'] = $vAddress;
    $data['vLocation'] = $vLocation;
    $data['vLatitude'] = $vLatitude;
    $data['eStatus'] = $eStatus;
    $data['vLongitude'] = $vLongitude;
    $data['iDriverId'] = $iDriverId;
    $data['vImage'] = $vImage;
    if (!empty($id)) {
        $where = " iTrackServiceUserId  = '" . $id . "'";
        $id = $obj->MySQLQueryPerform("track_service_users", $data, 'update', $where);
    } else {
        //$data['vInviteCode'] = RandomString('10', 'Yes');
        $data['vInviteCode'] = $TRACK_SERVICE_OBJ->GenerateInviteCode();
        $data['iTrackServiceCompanyId'] = $iTrackServiceCompanyId;
        $data['dAddedDate'] = date('Y-m-d H:i:s');
        $id = $obj->MySQLQueryPerform("track_service_users", $data, 'insert');
        
        if (!empty($vEmail)) {
            $maildata['vEmail'] = $vEmail;
            $maildata['NAME'] = $vName . ' ' . $vLastName;
            $maildata['EMAIL_NAME'] = $vName . ' ' . $vLastName;
            $maildata['INVITECODE'] = $data['vInviteCode'];
            $COMM_MEDIA_OBJ->SendMailToMember("TRACK_COMPANY_USER_INVITECODE_SEND", $maildata);
        }
        $vLangCode = $LANG_OBJ->FetchDefaultLangData("vCode");
        $dataArraySMSNew['NAME'] = $vName . ' ' . $vLastName;
        $dataArraySMSNew['INVITECODE'] = $data['vInviteCode'];
        $message = $COMM_MEDIA_OBJ->GetSMSTemplate('TRACK_COMPANY_USER_INVITECODE_SEND', $dataArraySMSNew, "", $vLangCode);
        $result = $COMM_MEDIA_OBJ->SendSystemSMS($vPhone, $CountryData[0]['vPhoneCode'], $message);
    }
    if ($action == 'Edit') {
        $var_msg = $langage_lbl['LBL_Record_Updated_successfully'];
    } else {
        $var_msg = $langage_lbl['LBL_RECORD_INSERT_MSG'];
    }
    header("Location:trackinguserlist?id=" . $id . '&success=1&var_msg=' . $var_msg);
    exit;
}
if ($action == 'Edit') {
    $sql = "SELECT * from track_service_users where iTrackServiceUserId='" . $id . "'";
    $User = $obj->MySQLSelect($sql);
    $User = $User[0];
    $vName = $User['vName'];
    $vEmail = $User['vEmail'];
    $vLastName = $User['vLastName'];
    $vPhoneCode = $User['vPhoneCode'];
    $vPhone = $User['vPhone'];
    $vAddress = $User['vAddress'];
    $vLocation = $User['vLocation'];
    $vLatitude = $User['vLatitude'];
    $eStatus = $User['eStatus'];
    $vLongitude = $User['vLongitude'];
    $vCountry = $User['vCountry'];
    $iDriverId = $User['iDriverId'];
    $vImage = $User['vImage'];
    $vCode = $User['vPhoneCode'];
}
$sqlC = "SELECT vCountryCode,vPhoneCode,vCountry from country where eStatus='Active'";
$AllcountryArry = $obj->MySQLSelect($sqlC);

$db_drvr = $obj->MySQLSelect("SELECT * FROM register_driver WHERE iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' AND eStatus !='Deleted' order by vName ASC");

$db_vehicles = $obj->MySQLSelect("SELECT dv.iDriverVehicleId, dv.vLicencePlate, dv.iDriverId, m.vMake, md.vTitle, CONCAT(rd.vName,' ',rd.vLastName) AS driverName FROM driver_vehicle as dv LEFT JOIN register_driver as rd ON rd.iDriverVehicleId = dv.iDriverVehicleId LEFT JOIN make as m ON m.iMakeId = dv.iMakeId LEFT JOIN model as md ON md.iModelId = dv.iModelId WHERE rd.iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' AND dv.eStatus = 'Active' AND rd.eStatus = 'active' ");

if ($action == 'Add') {
    $action_lbl = $langage_lbl['LBL_ACTION_ADD'];
} elseif ($action == 'Edit') {
    $action_lbl = $langage_lbl['LBL_ACTION_EDIT'];
}
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER']; ?> <?= $action; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= $action_lbl; ?>  <?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER']; ?>

                        <?= $vName; ?></h1>
                </div>
                <div class="button-block end">
                    <? if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') { ?>
                        <a href="trackinguserlist" class="gen-btn">
                            <?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?>
                        </a>
                    <? } else { ?>
                        <a href="driverlist" class="gen-btn">
                            <?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?>
                        </a>
                    <? } ?>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="table-holder">
                <div class="page-contant">
                    <div class="page-contant addVehicleCX  ">
                        <div class="addDriverform">
                            <!-- login in page -->
                            <div class="driver-action-page">
                                <? if ($success == 1) { ?>
                                    <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                        </button>
                                        <?php echo $langage_lbl['LBL_Record_Updated_successfully']; ?>
                                    </div>
                                <? } else if ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                        </button>
                                        <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                                    </div>
                                    <?php
                                }
                                ?>
                                <form id="frm1" name="frm1" method="post"
                                      class="trackingCompanyUserAction general-form profile_edit profile-caption active"
                                      enctype="multipart/form-data" action="">
                                    <!-- onSubmit="return editPro('login')" -->
                                    <input type="hidden" class="edit" name="action" value="<?= $action ?>">
                                    <input type="hidden" class="edit" name="id" value="<?= $id ?>">
                                    <?php if (!empty($id)) { ?>
                                        <div id="hide-profile-div" class="profile-image-hold">
                                            <?php if ($vImage != '' && file_exists($tconfig["tsite_upload_images_track_company_user_path"] . '/' . $id . '/3_' . $vImage)) { ?>
                                                <div class="col-lg-2">
                                                    <b class="img-b">
                                                        <img class="img-ipm1"
                                                             src="<?php echo $tconfig["tsite_upload_images_track_company_user"] . '/' . $id . '/3_' . $vImage ?>"/>
                                                    </b>
                                                </div>
                                            <?php } else { ?>
                                                <img src="assets/img/profile-user-img.png" alt="">
                                            <?php } ?>
                                        </div>
                                    <? } ?>
                                    <div class="">
                                        <div class="grpDriver">
                                            <div class="action-driv">
                                                <?php
                                                ?>
                                                <div class="partation">
                                                    <h1><?= $langage_lbl['LBL_PERSONAL_INFO_TXT'] ?></h1>
                                                    <div class="form-group half">
                                                        <label><?= $langage_lbl['LBL_YOUR_FIRST_NAME']; ?>
                                                            <span class="red">*</span>
                                                        </label>
                                                        <input type="text" class="driver-action-page-input" name="vName"
                                                               id="vName"
                                                               value="<?= clearName(cleanall(htmlspecialchars($vName))); ?>"
                                                               required>
                                                        <div id="vName_validate"></div>
                                                    </div>
                                                    <div class="form-group half">
                                                        <label><?= $langage_lbl['LBL_YOUR_LAST_NAME']; ?>
                                                            <span class="red">*</span>
                                                        </label>
                                                        <input type="text" class="driver-action-page-input"
                                                               name="vLastName" id="vLastName"
                                                               value="<?= clearName(cleanall(htmlspecialchars($vLastName))); ?>"
                                                               required>
                                                        <div id="vLastName_validate"></div>
                                                    </div>
                                                    <div class="form-group half">
                                                        <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?>

                                                            <?php if ($ENABLE_EMAIL_OPTIONAL == 'No') { ?>
                                                                <span class="red">*</span>
                                                            <?php } ?>
                                                        </label>
                                                        <input type="email" class="driver-action-page-input "
                                                               autocomplete="new-email" name="vEmail" id="vEmail"
                                                               value="<?= clearEmail($vEmail); ?>"
                                                            <?php if ($ENABLE_EMAIL_OPTIONAL == 'No') {
                                                                echo "required";
                                                            } ?>
                                                        >
                                                        <div id="vEmail_validate"></div>
                                                    </div>
                                                    <div class="form-group half phone-column">
                                                        <label><?= $langage_lbl['LBL_Phone_Number']; ?> <?= $vCode ?>
                                                            <span class="red">*</span>
                                                        </label>
                                                        <input type="text" class="input-phNumber1 phonecode" id="code"
                                                               name="vCode" value="<?= $vCode ?>" readonly>
                                                        <input name="vPhone" type="text" value="<?= clearPhone($vPhone); ?>"
                                                               class="driver-action-page-input input-phNumber2"
                                                               required/>
                                                        <div id="vPhone_validate"></div>
                                                    </div>
                                                    <div class="form-group half">
                                                        <?php
                                                        if (scount($AllcountryArry) > 1) {
                                                            $style = "";
                                                        } else {
                                                            $style = " disabled=disabled";
                                                        } ?>
                                                        <select <?= $style ?> class="custom-select-new" name='vCode'
                                                                              onChange="changeCode(this.value);"
                                                                              required id="vCountry">
                                                            <?php if (scount($AllcountryArry) > 1) { ?>
                                                                <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option>
                                                            <?php } ?>

                                                            <? for ($i = 0; $i < scount($AllcountryArry); $i++) { ?>
                                                                <option
                                                                        value="<?= $AllcountryArry[$i]['vCountryCode'] ?>"
                                                                    <? if ($DEFAULT_COUNTRY_CODE_WEB == $AllcountryArry[$i]['vCountryCode'] && $action == 'Add') { ?> selected <?php } else if ($vCountry == $AllcountryArry[$i]['vCountryCode']) { ?>selected<? } ?>><?= $AllcountryArry[$i]['vCountry'] ?></option>
                                                            <? } ?>
                                                        </select>
                                                        <div id="vCountry_validate"></div>
                                                    </div>

                                                    <?php /*
                                                    <div class="form-group half">
                                                        <select name="iDriverId" id="iDriverId" class="custom-select-new" required>
                                                            <option value=""><?= $langage_lbl['LBL_CHOOSE_DRIVER']; ?></option>
                                                            <?php for ($j = 0; $j < scount($db_drvr); $j++) { ?>
                                                                <option value="<?= $db_drvr[$j]['iDriverId'] ?>" <? if ($db_drvr[$j]['iDriverId'] == $iDriverId) { ?> selected <? } ?>><?= clearName($db_drvr[$j]['vName'] . ' ' . $db_drvr[$j]['vLastName']); ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    */ ?>

                                                    <div class="form-group half">
                                                        <select name="iDriverId" id="iDriverId" class="custom-select-new">
                                                            <option value=""><?= $langage_lbl['LBL_CHOOSE_CAR']; ?></option>
                                                            <?php for ($i = 0; $i < scount($db_vehicles); $i++) { ?>
                                                                <option value="<?= $db_vehicles[$i]['iDriverId'] ?>" <?php if ($db_vehicles[$i]['iDriverId'] == $iDriverId) { ?> selected <? } ?>><?= $db_vehicles[$i]['vMake'] . ' ' . $db_vehicles[$i]['vTitle'] . ' (' . $db_vehicles[$i]['vLicencePlate'] . ')' ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group half">
                                                        <div class="relation-parent fileUploading"
                                                             filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                            <input type="file" class="driver-action-page-input"
                                                                   name="vImage" id="vImage" placeholder="Name Label"
                                                                   accept="image/*"
                                                                   onChange="validate_fileextension(this.value);">
                                                        </div>
                                                        <div class="fileerror error"></div>
                                                    </div>
                                                    <div class="track-user-status">
                                                        <label>Status</label>
                                                        <div class="toggle-combo">

                                                            <span class="toggle-switch">

                                                                <input type="checkbox" value="Active" name="eStatus"
                                                                       id="eStatus" <?= ($eStatus == 'Inactive') ? '' : 'checked'; ?> />

                                                                <span class="toggle-base"></span> </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="partation">
                                                    <h1><?= $langage_lbl['LBL_ADDRESS_INFORMATION'] ?></h1>
                                                    <div class="form-group half">
                                                        <label><?= $langage_lbl['LBL_LOCATION_FOR_FRONT']; ?>
                                                            <span class="red">*</span>
                                                        </label>
                                                        </label>
                                                        <input type="text" class="driver-action-page-input"
                                                               name="vLocation" id="vLocation"
                                                               value="<?= $vLocation; ?>" required>
                                                        <input type="hidden" name="vLatitude" id="vLatitude"
                                                               value="<?php echo $vLatitude; ?>">
                                                        <input type="hidden" name="vLongitude" id="vLongitude"
                                                               value="<?php echo $vLongitude; ?>">
                                                        <div id="vLocation_validate"></div>
                                                    </div>
                                                    <div class="form-group half">
                                                        <label><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?>
                                                            <span class="red">*</span>
                                                        </label>
                                                        </label>
                                                        <input type="text" class="driver-action-page-input"
                                                               name="vAddress" id="vAddress" value="<?= $vAddress; ?>"
                                                               required>
                                                        <div id="vZip_validate"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="button-block">
                                            <div class="btn-hold">
                                                <input type="submit" class="save-but gen-btn" name="btn_submit"
                                                       id="btn_submit"
                                                       value="<?= $action_lbl; ?> <?= $langage_lbl['LBL_TRACK_SERVICE_COMPANY_USER']; ?>">
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </form>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
?>
<script type="text/javascript"
        src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script>
    function changeCode(id) {
        if(id == '')
        {
            id = "<?= $vCountry?>";
        }
        var ajaxData = {

            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
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

    $('.trackingCompanyUserAction').validate({

        ignore: 'input[type=hidden]',

        errorClass: 'help-block error',

        onkeypress: true,

        errorElement: 'span',

        errorPlacement: function (error, e) {

            e.parents('.half').append(error);

        },

        highlight: function (e) {

            $(e).closest('.half').removeClass('has-success has-error').addClass('has-error');

            $(e).closest('.half input').addClass('has-shadow-error');

            $(e).closest('.help-block').remove();

        },

        success: function (e) {

            e.prev('input').removeClass('has-shadow-error');

            e.closest('.half').removeClass('has-success has-error');

            e.closest('.help-block').remove();

            e.closest('.help-inline').remove();

        },

        rules: {

            vName: {required: true, minlength: 1, maxlength: 30},

        },

        messages: {},

        submitHandler: function (form) {

            if ($(form).valid())

                form.submit();

            return false; // prevent normal form posting

        }

    });


    function validate_fileextension(filename) {

        var fileExtension = ['jpeg', 'jpg', 'png', 'gif', 'bmp'];

        if ($.inArray(filename.split('.').pop().toLowerCase(), fileExtension) == -1) {

            $(".fileerror").html("Only formats are allowed : " + fileExtension.join(', '));

            $('.save-but').prop("disabled", true);

            return false;

        } else {

            $('.save-but').prop("disabled", false);

            $(".fileerror").html("");

        }

    }
</script>
<!-- End: Footer Script -->
</body>
</html>