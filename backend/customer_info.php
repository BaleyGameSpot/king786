<?php
include_once("common.php");
GetPagewiseSessionMemberType('customer_info');
$fromOrder = "guest";

if($_REQUEST['order'] == "restaurant"){
    $_REQUEST['order'] = "store";
}

if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
    $fromOrder = $_REQUEST['order'];
}
$_SESSION['MANUAL_ORDER_USER'] = $fromOrder;
$db_code = $obj->MySQLSelect("SELECT vCountryCode,vCountry,vPhoneCode,vTimeZone from country where eStatus = 'Active'");
$customer_info_vPhoneCode = "1";
$vDistance = 0;
$vCountryCode = "US";
$address = $vRideCountry = "USA";
$vPhoneCode = $vDuration = 1;
$eStatus = "Active";
$vTimeZone = "Asia/Kolkata";
for ($g = 0; $g < scount($db_code); $g++) {
    if ($db_code[$g]['vCountryCode'] == $DEFAULT_COUNTRY_CODE_WEB) {
        $vCountryCode = $db_code[$g]['vCountryCode'];
        $customer_info_vPhoneCode = $db_code[$g]['vPhoneCode'];
        $address = $db_code[$g]['vCountry'];
        $vTimeZone = $db_code[$g]['vTimeZone'];
    }
}
if ($fromOrder == "store" && $_SESSION['sess_user'] == "company" && !empty($_SESSION['sess_iCompanyId'])) {
    $db_company = $obj->MySQLSelect("SELECT vCountry,vCode,iServiceIdMulti,iServiceId from company where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'");
    $vCountryCode = $db_company[0]['vCountry'];
    $db_countrydata = $obj->MySQLSelect("SELECT vCountryCode,vCountry,vPhoneCode,vTimeZone from country where vCountryCode = '" . $vCountryCode . "'");
    $customer_info_vPhoneCode = $db_countrydata[0]['vPhoneCode'];
    $address = $db_countrydata[0]['vCountry'];
    $vTimeZone = $db_countrydata[0]['vTimeZone'];
}
$customer_info_vPhone = $customer_info_vName = $customer_info_vLastName = $customer_info_vEmail = "";
$prepAddr = str_replace(' ', '+', $address);
$script = "Customer info";
$meta_arr = $STATIC_PAGE_OBJ->FetchStaticPage(1, $_SESSION['sess_lang']);
$meta_keyword = $meta_desc = "Manual Store Order Screen";
if (isset($meta_arr['meta_keyword'])) {
    $meta_keyword = $meta_arr['meta_keyword'];
}
if (isset($meta_arr['meta_desc'])) {
    $meta_desc = $meta_arr['meta_desc'];
}
$sqlcont = "select vCountry,tLatitude,tLongitude from country where vCountryCode='" . $vCountryCode . "'";
$db_contry = $obj->MySQLSelect($sqlcont);
$latitude = $db_contry[0]['tLatitude'];
$longitude = $db_contry[0]['tLongitude'];
$_REQUEST["vLang"] = $_SESSION['sess_lang'];
if(!empty($db_company)){
    if (!empty($db_company[0]['iServiceIdMulti'])) {
        $iServiceIdMulti = $db_company[0]['iServiceIdMulti'];
    } else {
        $iServiceIdMulti = $db_company[0]['iServiceId'];
    }
}
if (!empty($iServiceIdMulti)) {
    $serviceidarr = explode(",", $iServiceIdMulti);
}
if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)) {
    $service_categories = $serviceCategoriesTmp;
} else {
    $service_categories = array();
}
$checkUser = GetSessionMemberType();
$emailExistsMsg = $langage_lbl['LBL_EMAIL_EXISTS_MSG'];
$pageHead = $SITE_NAME . " | " . $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'];
$overlay_class = "";
if (SITE_TYPE == 'Demo' && isset($_SESSION['sess_iAdminUserId'])) {
    $overlay_class = "overlay-helper";
}
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $pageHead; ?></title>
    <meta name="keywords" value="<?= $meta_keyword; ?>"/>
    <meta name="description" value="<?= $meta_desc; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <?php include_once("top/validation.php"); ?>
    <!-- End: Default Top Script and css-->
    <script type='text/javascript' src='assets/js/jquery-ui.min.js'></script>
    <script type='text/javascript' src='assets/js/bootbox.min.js'></script>
    <link href="assets/css/radio.css" rel="stylesheet" type="text/css"/>
    <?php include_once("store_css_include.php"); ?>
    <style type="text/css">
        .overlay-helper {
            position: relative;
            padding: 30px;
        }

        .overlay-custom {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            cursor: pointer;
        }

        .overlay-custom-content {
            position: absolute;
            width: 80%;
            top: 50%;
            left: 50%;
            font-size: 28px;
            color: #ffffff;
            transform: translate(-50%, -50%);
            -webkit-transform: translate(-50%, -50%);
            -o-transform: translate(-50%, -50%);
            -moz-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
        }
    </style>
</head>
<body>
<div id="main-uber-page">
    <?php include_once("top/left_menu.php"); ?>
    <?php include_once("top/header_topbar.php"); ?>
    <?php if ($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
    <div class="profile-section">

        <div class="profile-section-inner">
            <?php if (SITE_TYPE == 'Demo' && isset($_SESSION['sess_iAdminUserId'])) { ?>
                <div class="alert alert-danger" style="width: 100%">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php echo $langage_lbl['LBL_DEMO_CREATE_ORDER_MSG']; ?>
                </div>
            <?php } ?>
            <div class="profile-caption">
                <div class="page-heading">
                    <h1>
                        <?php } else { ?>
                        <div class="page-contant">
                            <div class="page-contant-inner clearfix">
                                <h2 class="header-page trip-detail"><?php } ?><?php echo $langage_lbl['LBL_MANUAL_STORE_ADD_USER_INFO']; ?>
                        <?php if ($THEME_OBJ->isXThemeActive() == 'Yes') { ?></h1>
                </div>
            </div><?php } else { ?>
            </h2>
        <?php } ?>
            <div class="static-page static-page-new <?= $overlay_class ?>">
                <?php if (SITE_TYPE == 'Demo' && isset($_SESSION['sess_iAdminUserId'])) { ?>
                    <div class="overlay-custom">
                        <div class="overlay-custom-content"><?= $langage_lbl['LBL_DEMO_CREATE_ORDER_MSG'] ?></div>
                    </div>
                <?php } ?>

                <?php if (isset($_REQUEST['error']) && $_REQUEST['error'] != "") { ?>
                    <div class="row Syserror">
                        <div class="col-sm-12 alert alert-danger">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= $_REQUEST['var_msg']; ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="row msgforcustomer" style="display:none">
                    <div class="col-sm-12 alert alert-danger msgforcustomerdisplay">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= $_REQUEST['var_msg']; ?>
                    </div>
                </div>
         
                <form action="customer_info_action.php" class="general-form-new" name="CustomerForm" id="CustomerForm" method="post" class="clearfix">
                    <input type="hidden" name="previousLink" id="previousLink" value=""/>
                    <input type="hidden" name="ENABLE_DEBUG" id="ENABLE_DEBUG" value="1"/>
                    <input type="hidden" name="backlink" id="backlink" value="user-order-information"/>
                    <input type="hidden" name="distance" id="distance" value="<?= $vDistance; ?>">
                    <input type="hidden" name="duration" id="duration" value="<?= $vDuration; ?>">
                    <input type="hidden" name="from_lat_long" id="from_lat_long" value="<?= $from_lat_long; ?>">
                    <input type="hidden" name="from_lat" id="from_lat" value="<?= $latitude; ?>">
                    <input type="hidden" name="from_long" id="from_long" value="<?= $longitude; ?>">
                    <input type="hidden" value="1" id="location_found" name="location_found">
                    <input type="hidden" value="" id="user_type" name="user_type">
                    <input type="hidden" value="0" id="areatext" name="areatext" onChange="changearea()">
                    <input type="hidden" value="<?= $eStatus; ?>" id="eStatus" name="eStatus">
                    <input type="hidden" value="" id="serviceAdd" name="serviceAdd">
                    <input type="hidden" value="<?= $vTimeZone; ?>" id="vTimeZone" name="vTimeZone">
                    <input type="hidden" value="<?= $vRideCountry; ?>" id="vRideCountry" name="vRideCountry">
                    <input type="hidden" value="<?= $GOOGLE_SEVER_API_KEY_WEB; ?>" id="google_server_key"
                           name="google_server_key">
                    <input type="hidden" value="" id="getradius" name="getradius">
                    <input type="hidden" value="KMs" id="eUnit" name="eUnit">
                    <input type="hidden" name="fromOrder" id="fromOrder" value="<?= $fromOrder; ?>">
                    <?php if ($APP_TYPE != 'Ride-Delivery' && $APP_TYPE != 'Ride-Delivery-UberX' || ($APP_TYPE == 'Ride-Delivery' && $APP_DELIVERY_MODE == "Multi")) { ?>
                        <input type="hidden" value="<?= $etype ?>" id="eType" name="eType"/>
                    <?php } ?>
                    <div class="form-column newrow newrow-new">
                        <?php if (scount($db_code) > 1) {
                            $style = "";
                        } else {
                            $style = " disabled=disabled ";
                        } ?>
                        <select <?= $style ?> name="customer_info_vCountry" id="customer_info_vCountry cos-in-v"
                                              onChange="changeCode(this.value);" required>
                            <?php for ($i = 0; $i < scount($db_code); $i++) { ?>
                                <option value="<?= $db_code[$i]['vCountry'] ?>"
                                    <?php if ($db_code[$i]['vCountryCode'] == $vCountryCode) {
                                        echo "selected";
                                    } ?> >
                                    <?= $db_code[$i]['vCountry']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-column newrow country-code">
                        <label><?= $langage_lbl['LBL_777-777-7777']; ?> <span>*</span></label>
                        <input type="text" name="customer_info_vPhoneCode" id="customer_info_vPhoneCode"
                               value="<?= $customer_info_vPhoneCode; ?>" readonly/>
                        <input type="text" pattern="[0-9]{1,}" name="customer_info_vPhone" clear
                               id="customer_info_vPhone" value="<?= $customer_info_vPhone; ?>" autocomplete="off"
                               onblur="showPhoneDetail();"/>
                    </div>
                    <div class="form-column newrow mail-sec">
                        <label><?= $langage_lbl['LBL_ENTER_EMAIL_HINT']; ?></label>
                        <input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"
                               name="customer_info_vEmail" id="customer_info_vEmail"
                               value="<?= $customer_info_vEmail; ?>"
                               />
                    </div>
                    <div class="form-column half newrow">
                        <label><?= $langage_lbl['LBL_FIRST_NAME_HEADER_TXT']; ?><span>*</span></label>
                        <input type="text" name="customer_info_vName" id="customer_info_vName"
                               value="<?= $customer_info_vName; ?>"
                                required/>
                    </div>
                    <div class="form-column half newrow">
                        <label><?= $langage_lbl['LBL_LAST_NAME_HEADER_TXT']; ?><span>*</span></label>
                        <input type="text" name="customer_info_vLastName" id="customer_info_vLastName"
                               value="<?= $customer_info_vLastName; ?>"
                                required/>
                    </div>

                    <?php if (strtolower($fromOrder) == 'store' && $MODULES_OBJ->isEnableStoreMultiServiceCategories()) { ?>
                        <div class="form-column half">
                            <?php if (scount($service_categories) == 1) { ?>
                                <input type="hidden" name="serviceid" id="serviceid" value="<?= $db_company[0]['iServiceId']; ?>">
                            <?php } else { ?>
                                <select name="serviceid">
                                    <?php if (scount($service_categories) > 0) {
                                        for ($i = 0; $i < scount($service_categories); $i++) {
                                            $iServiceId = $service_categories[$i]['iServiceId'];
                                            if ($service_categories[$i]['vImage'] == "") {
                                                $service_categories[$i]['vImage'] = $tconfig['tsite_url'] . '/assets/img/burger.jpg';
                                            }
                                            if (in_array($iServiceId, $serviceidarr) && strtolower($fromOrder) == 'store') {
                                                ?>
                                                <option value="<?php echo $iServiceId; ?>"><?php echo clearName(ucfirst($service_categories[$i]['vServiceName'])); ?> </option>
                                            <?php }
                                        }
                                    } ?>
                                </select>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                            <input type="hidden" name="serviceid" id="serviceid" value="<?= $db_company[0]['iServiceId']; ?>">
                    <?php } ?>
                    <?php if (strtolower($fromOrder) == 'admin') { ?>
                        <div class="form-column half">
                            <?php if (scount($service_categories) == 1) { ?>
                                <input type="hidden" name="serviceid" id="serviceid"
                                       value="<?= $service_categories[0]['iServiceId']; ?>">
                            <?php } else { ?>
                                <select name="serviceid">
                                    <?php if (scount($service_categories) > 0) {
                                        for ($i = 0; $i < scount($service_categories); $i++) {
                                            $iServiceId = $service_categories[$i]['iServiceId'];
                                            if ($service_categories[$i]['vImage'] == "") {
                                                $service_categories[$i]['vImage'] = $tconfig['tsite_url'] . '/assets/img/burger.jpg';
                                            } ?>
                                            <option value="<?php echo $iServiceId; ?>"><?php echo clearName(ucfirst($service_categories[$i]['vServiceName'])); ?> </option>
                                            <?php
                                        }
                                    } ?>
                                </select>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <?php if (scount($service_categories) == 1) { ?>
                            <input type="hidden" name="serviceid" id="serviceid"
                                   value="<?= $service_categories[0]['iServiceId']; ?>">
                        <?php } ?>
                    <?php } ?>
                    <div style="clear:both; width:100%;"></div>
                    <div class="form-column half newrow" style="margin-bottom:20px;" id="DeliveryAddress">
                        <label><?= $langage_lbl['LBL_DELIVERY_ADDRESS']; ?><span> * </span></label>
                        <input type="text" name="vServiceAddress" id="vServiceAddress" autocomplete="off">
                    </div>
                    <div class="form-column half newrow" style="margin-bottom:20px;" id="BuildingNo">
                        <label><?php echo $langage_lbl['LBL_JOB_LOCATION_HINT_INFO']; ?><span> * </span></label>        
                        <input class="vBuildingNo" type="text" name="vBuildingNo" id="vBuildingNo" autocomplete="off">
                    </div>
                    <div class="form-column half newrow" style="margin-bottom:20px;" id="Landmark">
                            <label><?php echo $langage_lbl['LBL_LANDMARK_HINT_INFO']; ?><span> * </span></label>
                            <input class="vLandmark" type="text" name="vLandmark" id="vLandmark" autocomplete="off">
                    </div>
                    <div class="form-column half" style="margin-bottom:20p  x;" id="AddressType">
                                <label><?php echo $langage_lbl['LBL_ADDRESSTYPE_HINT_INFO']; ?></label>
                                <input class="vAddressType" type="text" name="vAddressType" id="vAddressType" autocomplete="off">
                               
                    </div>
                    <div class="form-column-full" id="riderAddresses"></div>
                    <div align="left" id="new_oneadd" class="form-button-block" style="display:none;">
                        <input type="button" class="btn-submit" id="new_one"
                               value="<?php echo $langage_lbl['LBL_ADD_NEW_ADDRESS_TXT']; ?>" onclick="chacknew()">
                    </div>
                    <div align="left" id="old_oneadd" class="form-button-block" style="display:none;">
                        <input type="button" class="btn-submit" id="old_one"
                               value="<?php echo $langage_lbl['LBL_OTHER_TXT'] . " " . $langage_lbl['LBL_PROFILE_ADDRESS']; ?>"
                               onclick="displayOtherAddress()">
                    </div>
                    <div class="form-column-full">
                        <div class="map-page">
                            <div class="panel-heading location-map" style="background:none;">
                                <div class="google-map-wrap">
                                    <div id="map-canvas" class="google-map" style="width:100%; height:550px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-button-block" style="margin-top:10px;">
                        <button type="submit" name="SUBMIT"><?= $langage_lbl['LBL_BTN_SUBMIT_TXT']; ?></button>
                        <input type="button" name="reset" id="reset" class="cancel-btn"
                               style="background-color:#000000;" value="<?= $langage_lbl['LBL_RESET']; ?>"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if (empty($_SESSION['sess_iAdminUserId']) && strpos($_SERVER['SCRIPT_FILENAME'], 'customer_info.php') !== false) {
        include_once('footer/footer_home.php');
    } ?>
    <div style="clear:both;"></div>
</div>
<?php include_once('top/footer_script.php'); ?>
<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"] ?>js/moment.min.js"></script>
<script type="text/javascript" src="<?= $tconfig["tsite_url_main_admin"] ?>js/plugins/select2.min.js"></script>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places&callback=initMap" type="text/javascript" async></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<!-- End: Footer Script -->
<script>
    $(document).ready(function () {
        $('#vServiceAddress').keyup(function (e) {
            buildAutoComplete("vServiceAddress", e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>", "<?=$_SESSION['sess_lang'];?>", function (latitude, longitude, address) {
                show_locations();
            }); // (orignal function)
        });
    });
    $("#reset").on("click", function () {
        $("#customer_info_vPhone,#customer_info_vName,#customer_info_vLastName,#customer_info_vEmail,.vBuildingNo,.vLandmark,#DeliveryAddress,.vAddressType").val('');
        $('#serviceAdd').val('');
        showPhoneDetail();
    });

    function chkvalidation() {
        var from_lat = $('#from_lat').val();
        var from_long = $('#from_long').val();
        var iUserAddressId = $('#iUserAddressId:checked').val();
        var vBuildingNo = $('#vBuildingNo').val();
        var Landmark = $('#vLandmark').val();
        if ($.trim(from_lat) != "" && $.trim(from_long) != "" && $.trim(Landmark) != "" && $.trim(vBuildingNo) != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_check_address_store.php',
                'AJAX_DATA': {from_lat: from_lat, from_long: from_long, iUserAddressId: iUserAddressId},
                'REQUEST_DATA_TYPE': 'json'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var dataHTML = response.result;
                    if (dataHTML.Action == 0) {
                        $('.msgforcustomerdisplay').html('<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>' + dataHTML.message);
                        $('.msgforcustomer').show();
                        if ($('.Syserror').length > 0) {
                            $('.Syserror').hide();
                            $('#vServiceAddress').focus();
                        }
                        return false;
                    } else {
                        $('.msgforcustomer').hide();
                        $('.msgforcustomerdisplay').html('');
                        if ($('.Syserror').length > 0) {
                            $('.Syserror').show();
                        }
                        $('#CustomerForm').submit();
                        return true;
                    }
                }
                else {
                    console.log(response.result);
                }
            });
            return false;
        } else {
            $('.msgforcustomer').hide();
            $('.msgforcustomerdisplay').html('');
            if ($('.Syserror').length > 0) {
                $('.Syserror').show();
            }
            return true;
        }
    }

    var errormessage;
    $('#CustomerForm').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            customer_info_vCountry: {required: true},
            customer_info_vPhone: {
                required: true, minlength: 3, digits: true,
                remote: {
                    url: 'ajax_rider_mobile_new.php',
                    type: "post",
                    data: {iUserId: ''},
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?=addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']);?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?=addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']);?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            // vServiceAddress:{alphanumericspace:true},
            customer_info_vName: {required: true, minlength: 1, maxlength: 30, alphanumericspace: true, notEmpty: true},
            customer_info_vLastName: {required: true, minlength: 1, maxlength: 30, alphanumericspace: true, notEmpty: true},
            customer_info_vEmail: {<?php if($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>required: true, <?php }?>email: true},
            
        },
        messages: {
            customer_info_vPhone: {
                required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>',
                minlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_AT_LEAST']) . ' 3 ' . addslashes($langage_lbl['LBL_MANUAL_STORE_NUMBER']) . '.'; ?>',
                digits: '<?php echo addslashes($langage_lbl['LBL_PHONE_VALID_MSG']); ?>',
                remote: function () {
                    return errormessage;
                }
            },
            customer_info_vName: {
                required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>',
                minlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_AT_LEAST']) . ' 2 ' . addslashes($langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>',
                maxlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_NO_MORE_THAN'] . ' 30 ' . $langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>'
            },
            customer_info_vLastName: {
                required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>',
                minlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_AT_LEAST'] . ' 2 ' . $langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>',
                maxlength: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_PLEASE_ENTER_NO_MORE_THAN'] . ' 30 ' . $langage_lbl['LBL_MANUAL_STORE_CHARACTERS']) . '.'; ?>'
            },
            customer_info_vEmail: {
                required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>',
                email: '<?php echo addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']) . '.'; ?>.'
            },
            vServiceAddress: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>'},
            vBuildingNo: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>'},
            vLandmark: {required: '<?php echo addslashes($langage_lbl['LBL_MANUAL_STORE_REQUIRED_FIELD']) . '.'; ?>'}
        },
        submitHandler: function (form) {
            $("#customer_info_vCountry").prop('disabled', false);
            return true;
        }
    });
    
    $.validator.addMethod("notEmpty", function(value, element) {
        return value.trim() !== '';
    }, "This field is required.");
    
    function chacknew() {
        $("#old_oneadd").show();
        $('input[name=iUserAddressId]').attr('checked', false);
        $('#new_oneadd,#riderAddresses').hide();
        $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();
        $('#areatext').val(0);
        document.getElementById("vBuildingNo").required = true;
        document.getElementById("vLandmark").required = true;
        document.getElementById("vServiceAddress").required = true;
        return false;
    }

    function displayOtherAddress() {
        showPhoneDetail();
        $("#old_oneadd").hide();
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 35 || charCode > 57)) {
            return false;
        } else {
            return true;
        }
    }

    function changeCode(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_check_code.php',
            'AJAX_DATA': {id: id, eUnit: 'yes'},
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHTML = response.result;
                document.getElementById("customer_info_vPhoneCode").value = dataHTML.vPhoneCode;
                document.getElementById("eUnit").value = dataHTML.eUnit;
                document.getElementById("vRideCountry").value = dataHTML.vCountryCode;
                document.getElementById("vTimeZone").value = dataHTML.vTimeZone;
                showPhoneDetail();
            }
            else {
                console.log(response.result);
            }
        });
    }

    function checkCustomerEmail() {
        var custEmail = $("#customer_info_vEmail").val();
        var phone = $('#customer_info_vPhone').val();
        var phoneCode = $('#customer_info_vPhoneCode').val();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_rider_mobile_new.php',
            'AJAX_DATA': {type: "email", custEmail: custEmail, phone: phone, phoneCode: phoneCode},
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
           
            if (response.action == "1") {
                var dataHTML = response.result;
                if (dataHTML > 0) {
                    alert("<?=$emailExistsMsg;?>");
                    $("#customer_info_vEmail").val("");
                }
            }
            else {
                console.log(response.result);
            }
        });
    }

    function showPhoneDetail() {
        var phone = $('#customer_info_vPhone').val();
        var phoneCode = $('#customer_info_vPhoneCode').val();

        $("#vServiceAddress").val("");
        if (phone != "" && phoneCode != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_find_rider_by_number.php',
                'AJAX_DATA': {phone: phone, phoneCode: phoneCode},
                'REQUEST_DATA_TYPE': 'json'
            };
            getDataFromAjaxCall(ajaxData, function (response) {

                if (response.action == "1") {
                    var dataHtml = response.result;
                    if (dataHtml.cont != "" && dataHtml.count > 0) {
                        $('#riderAddresses,#new_oneadd').show();
                        var result = dataHtml.cont.split(':');
                        $('#customer_info_vName').val(result[0]);
                        $('#customer_info_vLastName').val(result[1]);
                        $('#customer_info_vEmail').val(result[2]);
                        $('#iUserId').val(result[3]);
                        $('#eStatus').val(result[4]);
                        $('#areatext').val(1);
                        $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').hide();
                        if (dataHtml.workAddress != "" && dataHtml.vLatitude != "" && dataHtml.vLongitude != "") {
                            var results = dataHtml.workAddress;
                            document.getElementById("vBuildingNo").required = false;
                            document.getElementById("vLandmark").required = false;
                            document.getElementById("vServiceAddress").required = false;
                            $('#riderAddresses').html(results);
                            var vUserAddressLatitude = dataHtml.vLatitude;
                            var vUserAddressLongitude = dataHtml.vLongitude;
                            var myLatlongs = new google.maps.LatLng(vUserAddressLatitude, vUserAddressLongitude);
                            var showsurgemodal = "No";
                            $("#from_lat").val(vUserAddressLatitude);
                            $("#from_long").val(vUserAddressLongitude);
                            $("#from_lat_long").val(myLatlongs);
                            getAddress(vUserAddressLatitude, vUserAddressLongitude, 'serviceAdd', 'from');
                            show_locationsper();
                        } else {
                            var result = dataHtml.cont.split(':');
                            var workAddressNew = dataHtml.workAddress;
                            $('#customer_info_vName').val(result[0]);
                            $('#customer_info_vLastName').val(result[1]);
                            $('#customer_info_vEmail').val(result[2]);
                            $('#iUserId').val(result[3]);
                            $('#eStatus').val(result[4]);
                            $('#areatext').val(1);
                            $('#riderAddresses').html(workAddressNew);
                            document.getElementById("vBuildingNo").required = true;
                            document.getElementById("vLandmark").required = true;
                            document.getElementById("vServiceAddress").required = true;
                            $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();
                            $('#new_oneadd,#riderAddresses').hide();
                        }
                    } else {
                        $('#customer_info_vName,#customer_info_vLastName,#customer_info_vEmail,#iUserId,#eStatus').val('');
                        $('#areatext').val(0);
                        document.getElementById("vBuildingNo").required = true;
                        document.getElementById("vLandmark").required = true;
                        document.getElementById("vServiceAddress").required = true;
                        $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();
                        $('#new_oneadd,#riderAddresses').hide();
                    }
                }
                else {
                    console.log(response.result);
                }
            });
        } else {
            document.getElementById("vBuildingNo").required = true;
            document.getElementById("vLandmark").required = true;
            document.getElementById("vServiceAddress").required = true;
            $('#areatext').val(0);
            $('#DeliveryAddress,#BuildingNo,#Landmark,#AddressType').show();
            $('#new_oneadd,#riderAddresses').hide();
            $("#user_type,#customer_info_vName,#customer_info_vLastName,#customer_info_vEmail,#iUserId").val('');
            var serviceAdd = $("#serviceAdd").val();
            if (serviceAdd != "") {
                $("#vServiceAddress").val(serviceAdd);
                if ($("#vServiceAddress").val() != "") {
                    DeleteMarkers('from_loc');
                    var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
                    setMarker(latlng, 'from_loc');
                }
            }
        }
    }

    function changelocation(idss, vLatitude, vLongitude) {
        var vUserAddressLatitude = vLatitude;
        var vUserAddressLongitude = vLongitude;
        var myLatlongs = new google.maps.LatLng(vUserAddressLatitude, vUserAddressLongitude);
        var showsurgemodal = "No";
        var from_latold = $("#from_lat").val();
        var from_longold = $("#from_long").val();
        var from_lat_longold = $("#from_lat_long").val();
        var from_addressold = $("#vServiceAddress").val();
        $("#from_lat").val(vUserAddressLatitude);
        $("#from_long").val(vUserAddressLongitude);
        $("#from_lat_long").val(myLatlongs);
        getAddress(vUserAddressLatitude, vUserAddressLongitude, 'serviceAdd', 'from', from_latold, from_longold, from_lat_longold, from_addressold);
        show_locationsper();
    }

    var map;
    var markers = [];
    var bounds = [];

    function initMap() {
        var bounds = new google.maps.LatLngBounds();
        var from_lat = $('#from_lat').val();
        var from_lng = $('#from_long').val();
        var thePoint = new google.maps.LatLng(from_lat, from_lng);
        bounds.extend(thePoint);

        GOOGLE_MAP_OBJ.options.center = new google.maps.LatLng(parseFloat(from_lat), parseFloat(from_lng));
        map = GOOGLE_MAP_OBJ.init('map-canvas');
        map.fitBounds(bounds);

        zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {
            if (this.getZoom()) {
                this.setZoom(12);
            }
        });

        callEditFundtion()
    }

    $(document).ready(function () {

        var from_latold = $("#from_lat").val();
        var from_longold = $("#from_long").val();
        var from_lat_longold = $("#from_lat_long").val();
        var from_addressold = $("#vServiceAddress").val();
        getAddress($("#from_lat").val(), $("#from_long").val(), 'vServiceAddress', 'from', from_latold, from_longold, from_lat_longold, from_addressold);
    });

    function getAddress(mDlatitude, mDlongitude, addId, setLatLongField, oldlat, oldlong, oldlatlong, oldAddress) {
        setTimeout(function() {
            var mylatlang = new google.maps.LatLng(mDlatitude, mDlongitude);
            getReverseGeoCode(addId, setLatLongField, sess_lang, mDlatitude, mDlongitude, oldlat, oldlong, oldlatlong, oldAddress, function (latitude, longitude, address) {
                show_locationsper();
            });
        }, 1000);        
    }

    function show_locationsper() {
        DeleteMarkers('from_loc');
        var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
        setMarker(latlng, 'from_loc');
    }

    function setMarkers(postitions, valIcon) {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = '<?php echo $tconfig["tsite_url"] ?>webimages/upload/mapmarker/source_marker.png';
        }
        var marker = new google.maps.Marker({
            map: map,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon
        });
        marker.id = valIcon;
        markers.push(marker);
        map.setCenter(marker.getPosition());
        map.setZoom(15);
    }

    function setMarker(postitions, valIcon) {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = '<?php echo $tconfig["tsite_url"] ?>webimages/upload/mapmarker/source_marker.png';
        }
        var marker = new google.maps.Marker({
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon
        });
        marker.id = valIcon;
        markers.push(marker);
        map.setCenter(marker.getPosition());
        map.setZoom(12);
        if (valIcon == "from_loc") {
            marker.addListener('dragend', function (event) {
                var lat = event.latLng.lat();
                var lng = event.latLng.lng();
                var myLatlongs = new google.maps.LatLng(lat, lng);
                showsurgemodal = "No";
                var from_latold = $("#from_lat").val();
                var from_longold = $("#from_long").val();
                var from_lat_longold = $("#from_lat_long").val();
                var from_addressold = $("#vServiceAddress").val();
                $("#from_lat").val(lat);
                $("#from_long").val(lng);
                $("#from_lat_long").val(myLatlongs);
                getAddress(lat, lng, 'vServiceAddress', 'from', from_latold, from_longold, from_lat_longold, from_addressold);
                // console.log($("#from_lat").val() +"=="+ $("#from_long").val());
            });
        }
    }

    function show_locations() {
        if ($("#vServiceAddress").val() != "") {
            DeleteMarkers('from_loc');
            var latlng = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
            setMarker(latlng, 'from_loc');
        }
    }

    function DeleteMarkers(newId) {
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

    function callEditFundtion() {
        var from_lat = $('#from_lat').val();
        var from_lng = $('#from_long').val();
        var from = new google.maps.LatLng(from_lat, from_lng);
        if (from != '') {
            setMarker(from, 'from_loc');
        }
    }

    $country = $('#customer_info_vCountry').val()
    var tlat = $("#from_lat").val();
    var tlong = $("#from_long").val();
    var from_longold = '';
    var from_lat_longold = '';
    var from_addressold = '';
    getAddress(tlat, tlong, 'vServiceAddress', 'from', from_longold, from_lat_longold, from_addressold);

    function play(tlat, tlong) {
        var bounds = new google.maps.LatLngBounds();
        var pt = new google.maps.LatLng($("#from_lat").val(), $("#from_long").val());
        bounds.extend(pt);
        map.setCenter(pt);
        map.fitBounds(bounds);
        zoomChangeBoundsListener = google.maps.event.addListenerOnce(map, 'bounds_changed', function (event) {
            if (this.getZoom()) {
                this.setZoom(12);
            }
        });
        DeleteMarkers('from_loc');
        callEditFundtion();
    }

    $(document).ready(function () {

        var from_latold = $("#from_lat").val();
        var from_longold = $("#from_long").val();
        var from_lat_longold = $("#from_lat_long").val();
        var from_addressold = $("#vServiceAddress").val();
        getAddress($("#from_lat").val(), $("#from_long").val(), 'vServiceAddress', 'from', from_latold, from_longold, from_lat_longold, from_addressold);
    });

    function change_lang(lang) {
        document.location = 'common.php?lang=' + lang;
    }
</script>
</body>
</html>