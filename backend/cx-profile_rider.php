<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$script = 'Profile';
//$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
//$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';

$success = '';
if(isset($_SESSION['message_code']) && !empty($_SESSION['message_code']))
{
    $success = $_SESSION['message_code'];
    $_SESSION['message_code'] = '';
}


$var_msg = '';
if(isset($_SESSION['session_msg']) && !empty($_SESSION['session_msg']))
{
    $var_msg = $_SESSION['session_msg'];
    $_SESSION['session_msg'] = '';
}


$abc = 'rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$user = $_SESSION["sess_user"];
$db_user = $obj->MySQLSelect("select * from register_user where iUserId = '" . $_SESSION['sess_iUserId'] . "'");
$db_lang = $db_country = $db_currency = array();
for ($la = 0; $la < scount($Data_ALL_langArr); $la++) {
    if (strtoupper($Data_ALL_langArr[$la]['eStatus']) == "ACTIVE") {
        $db_lang[] = $Data_ALL_langArr[$la];
    }
}

for ($ca = 0; $ca < scount($country_data_retrieve); $ca++) {
    if (strtoupper($country_data_retrieve[$ca]['eStatus']) == "ACTIVE") {
        $db_country[] = $country_data_retrieve[$ca];
    }
}

for ($cu = 0; $cu < scount($Data_ALL_currency_Arr); $cu++) {
    if (strtoupper($Data_ALL_currency_Arr[$cu]['eStatus']) == "ACTIVE") {
        $db_currency[] = $Data_ALL_currency_Arr[$cu];
    }
}

$db_currency = $obj->MySQLSelect("select * from currency where eStatus = 'Active' ORDER BY vName ASC");
for ($i = 0; $i < scount($db_lang); $i++) {
    if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) {
        $lang = $db_lang[$i]['vTitle'];
    }
}
for ($i = 0; $i < scount($db_country); $i++) {
    if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) {
        $country = $db_country[$i]['vCountry'];
    }
}
$user_available_balance = $WALLET_OBJ->FetchMemberWalletBalanceApp($_SESSION['sess_iUserId'], 'Rider');
$sql = "SELECT DISTINCT ord.iOrderId,(select count(orddetail.iOrderId) from order_details as orddetail where orddetail.iOrderId = ord.iOrderId) as TotalItem From orders as ord LEFT JOIN company as cmp ON cmp.iCompanyId = ord.iCompanyId LEFT JOIN order_status as ordst ON ordst.iStatusCode = ord.iStatusCode LEFT JOIN register_user as u ON u.iUserId = ord.iUserId LEFT JOIN service_categories as sc on sc.iServiceId=ord.iServiceId WHERE ord.iUserId = '" . $_SESSION['sess_iUserId'] . "' AND ord.iStatusCode NOT IN ('11','12') ORDER BY ord.iOrderId DESC ";
$db_order_detail = $obj->MySQLSelect($sql);
$order_delivered = scount($db_order_detail);
$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = $MODULES_OBJ->isRideFeatureAvailable() ? "Yes" : "No";
$deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable() ? "Yes" : "No";
$serviceTheme = 0;
if ($THEME_OBJ->isServiceXThemeActive() == 'Yes' || $THEME_OBJ->isServiceXv2ThemeActive() == 'Yes') {
    $serviceTheme = 1;
}
$ssql = "";
if ($ufxEnable != "Yes") {
    $ssql .= " AND t.eType != 'UberX'";
}
if (!$MODULES_OBJ->isAirFlightModuleAvailable()) {
    $ssql .= " AND t.iFromStationId = '0' AND t.iToStationId = '0'";
}
if ($rideEnable != "Yes") {
    $ssql .= " AND t.eType != 'Ride'";
}
if ($deliveryEnable != "Yes") {
    $ssql .= " AND t.eType != 'Deliver' AND t.eType != 'Multi-Delivery'";
}
$sql = "SELECT count(t.iTripId) as cnt FROM trips as t LEFT JOIN  register_user u ON t.iUserId = u.iUserId LEFT JOIN register_driver d ON t.iDriverId = d.iDriverId LEFT JOIN vehicle_type vt ON vt.iVehicleTypeId = t.iVehicleTypeId WHERE t.eSystem = 'General' AND u.iUserId = '" . $_SESSION['sess_iUserId'] . "' $ssql ORDER BY t.iTripId DESC";
$db_trip_detail = $obj->MySQLSelect($sql);
$trip_finished = $db_trip_detail[0]['cnt'];

//$ufxEnable = 'No';
//if ($MODULES_OBJ->isUfxFeatureAvailable() == 'Yes') {
//    $ufxEnable = 'Yes';
//}
$db_setup_info = $obj->MySQLSelect("SELECT lAddOnConfiguration FROM setup_info WHERE iSetupId=1");
$addOnData = json_decode($db_setup_info[0]['lAddOnConfiguration'], true);

$DONATION = $DRIVER_DESTINATION = $FAVOURITE_DRIVER = $FAVOURITE_STORE = $DRIVER_SUBSCRIPTION = $GOJEK_GOPAY = $MULTI_STOPOVER_POINTS = $MANUAL_STORE_ORDER_WEBSITE = $MANUAL_STORE_ORDER_STORE_PANEL = $MANUAL_STORE_ORDER_ADMIN_PANEL = $Deliverall = $UberX = $Fly = "No"; // Added By HJ On 12-07-2019
foreach ($addOnData as $addOnKey => $addOnVal) {
    $$addOnKey = $addOnVal;
}
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem();
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
$deliverallModule = strtoupper(DELIVERALL);
if ($cubeDeliverallOnly > 0) {
    $onlyDeliverallModule = "YES";
}
$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');
$become_restaurant = '';
if ($deliverallModule == "YES") {
    if (scount($serviceIdArray) == 1 && $serviceIdArray[0] == 1) {
        $imgname1 = "my-orders.svg";
        $imgname2 = "burger.svg";
    }
    else {
        $imgname1 = "my-orders.svg";
        $imgname2 = "burger.svg";
    }
}
$manual_booking_enable = $MODULES_OBJ->isManualBookingAvailable() ? 'Yes' : 'No';
if(!($MODULES_OBJ->isRideFeatureAvailable() || ($MODULES_OBJ->isDeliveryFeatureAvailable() && strtoupper(ENABLE_MANUAL_BOOKING_DELIVERY) == "YES") || ($ufxEnable == 'Yes' && strtoupper(ENABLE_MANUAL_BOOKING_UBERX) == 'YES'))) {
    $manual_booking_enable = "No";
}

if($MODULES_OBJ->isOnlyEnableRideSharingPro()){
    $iUserId = $_SESSION['sess_iUserId'];

    $sql = "SELECT count(*) as count FROM ride_share_bookings rsb 
                LEFT JOIN published_rides pr ON (pr.iPublishedRideId = rsb.iPublishedRideId)
                WHERE rsb.iUserId = " . $iUserId . "  AND pr.eStatus != 'Cancelled' AND  pr.eTrackingStatus IN ('Start','MarkAsPickupALL','End')";

    $sql = "SELECT count(*) as count FROM ride_share_bookings rsb WHERE rsb.iUserId = " . $iUserId . "";
    $ride_share_bookings_count = $obj->MySQLSelect($sql)[0]['count'];

    $sql = "SELECT count(*) as count FROM published_rides pr WHERE pr.iUserId = " . $iUserId . "";
    $published_rides_count = $obj->MySQLSelect($sql)[0]['count'];
}

if($MODULES_OBJ->isOnlyEnableBuySellRentPro())
{
    $sql = "SELECT GROUP_CONCAT(DISTINCT iRentItemId) as categoryId, iMasterServiceCategoryId FROM rent_items_category WHERE iMasterServiceCategoryId IN (8, 9 ,10) GROUP BY iMasterServiceCategoryId";
    $Master_Category_Wise_Category = $obj->MySQLSelect($sql);
    $iUserId = $_SESSION['sess_iUserId'];
    if(isset($Master_Category_Wise_Category) && !empty($Master_Category_Wise_Category)) {

        $MCategory_category = [];
        foreach ($Master_Category_Wise_Category as $category)
        {
            $categoryId  = $category['categoryId'];
            $getItemTotalQuery = "SELECT count(iRentItemPostId) as count FROM `rentitem_post` WHERE  iUserId = '" . $iUserId . "' AND iItemCategoryId IN ($categoryId) ";
            $getItemTotal = $obj->MySQLSelect($getItemTotalQuery);

            $MCategory_category[$category['iMasterServiceCategoryId']] = $getItemTotal[0]['count'];
        }

    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_PROFILE_TXT']; ?></title>
    <!--<link rel="stylesheet" href="templates/Cubex/assets/plugins/bootstrap/css/bootstrap.css" />-->
    <!--<link rel="stylesheet" href="assets/css/bootstrap-front.css" />-->
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css">
    <link rel="stylesheet" href="assets/validation/validatrix.css"/>
    <!-- End: Default Top Script and css-->
    <link rel="stylesheet" href="assets/css/modal_alert.css" />
    <style type="text/css">
        .help-block {
            color: red;
            font-size: 12px;
            text-align: center;
            margin-top: 8px;
            text-wrap: wrap;
            width: 250px;
        }
        span#photo-error{
             text-wrap: wrap;
            width: 250px;
        }
    </style>
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
    <section class="profile-section">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_PROFILE_TITLE_TXT']; ?></h1>
                </div>
                <div style="width:100%; margin: 10px 0 30px 0">
                    <?php

                    if ($success == 1) { ?>
                        <div class="alert alert-success">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= $langage_lbl['LBL_PROFILE_UPDATED']; ?>
                        </div>
                    <?php } else if ($success == 2) { ?>
                        <div class="alert alert-success">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <?php
                    }else if ($success == 3) { ?>
                        <div class="alert alert-success">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl[$_REQUEST['message']]; ?>
                        </div>
                        <?php
                    }
                    else if ($success == 0 && $var_msg != "") {
                        ?>
                        <div class="alert alert-success msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $var_msg; ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="profile-image">
                    <?php if (($db_user[0]['vImgName'] != '') && file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'])) { ?>
                        <img src="<?= $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'] ?>"
                             style="height:150px;"/>
                    <?php } else { ?>
                        <img src="assets/img/profile-user-img.png" alt="">
                    <?php } ?>

                    <a data-toggle="modal" data-target="#uiModal_4"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                </div>
                <div class="profile-block">
                    <div class="profile-caption-header">
                        <label><?= $langage_lbl['LBL_HELLO'] . ", " . cleanall(htmlspecialchars($db_user[0]['vName'])) . ' ' . cleanall(htmlspecialchars($db_user[0]['vLastName'])); ?></label>
                        <div>
                        <button class="profile_edit_btn"><?= $langage_lbl['LBL_EDIT_PROFILE_TXT']; ?></button>

                        <!------------------for delete account----------------->
                       <!--  <button id = "profile_Delete_btn" class="profile_Delete_btn"><?= $langage_lbl['LBL_DELETE_ACCOUNT_TXT']; ?></button> -->
                        <!------------------for delete account----------------->
                        </div>
                    </div>
                    <div class="profile-detail">
                        <?php if ($db_user[0]['vEmail'] != "") { ?>
                            <div class="profile-column">
                                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                <div class="data_info">
                                    <strong><?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?></strong>
                                    <span><?= $db_user[0]['vEmail']; ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="profile-column">
                            <i class="icon-call" aria-hidden="true"></i>
                            <div class="data_info">
                                <strong><?= $langage_lbl['LBL_PHONE']; ?></strong>
                                <span dir="ltr"><?php if (!empty($db_user[0]['vPhone'])) { ?>(+<?= $db_user[0]['vPhoneCode'] ?>) <?= $db_user[0]['vPhone'] ?><?php } ?></span>
                            </div>
                        </div>
                        <div class="profile-column">
                            <i class="icon-location" aria-hidden="true"></i>
                            <div class="data_info">
                                <strong><?= $langage_lbl['LBL_COUNTRY_TXT']; ?></strong>
                                <span><?php if ($country != "") { ?><?= $country ?><?php } ?></span>
                            </div>
                        </div>
                        <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                            <div class="profile-column">
                                <i class="icon-refer" aria-hidden="true"></i>
                                <div class="data_info">
                                    <strong><?php echo $langage_lbl['LBL_REFERAL_CODE_SIGNUP']; ?></strong>
                                    <span><?= $db_user[0]['vRefCode'] ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="custom-modal-main" id="uiModal_4" style="display:none">
                    <div class="custom-modal">
                        <div class="model-header">
                            <h4><?= $langage_lbl['LBL_PROFILE_PICTURE']; ?></h4>
                            <i class="icon-close" data-dismiss="modal"></i>
                        </div>
                        <form class="form-horizontal frm9" id="frm9" method="post" enctype="multipart/form-data"
                              action="upload_pic.php" name="frm9">
                            <div class="model-body">
                                <input type="hidden" name="action" value="photo"/>
                                <input type="hidden" name="img_path"
                                       value="<?= $tconfig["tsite_upload_images_passenger_path"]; ?>"/>
                                <div class="form-group">
                                    <div class="col-lg-12">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-preview thumbnail" id="fileupload-preview">
                                                <?php if ($db_user[0]['vImgName'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'])) { ?>
                                                    <img class="imagename"
                                                         src="<?= $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'] ?>"
                                                         alt="<?= $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'] ?>"/>
                                                <?php } else { ?>
                                                    <img class="imagename" src="assets/img/profile-user-img.png" alt="">
                                                <?php } ?>
                                            </div>
                                            <div>
                                                    <span class="btn btn-file btn-success gen-btn"><span
                                                                class="fileupload-new"><?= $langage_lbl['LBL_UPLOAD_PHOTO']; ?></span>
                                                        <span class="fileupload-exists"><?= $langage_lbl['LBL_Driver_document_CHANGE']; ?></span>
                                                        <input type="file" class="gen-btn" name="photo"/></span>
                                                <!-- input type="hidden" name="photo_hidden"  id="photo" value="<?php echo ($db_user[0]['vImgName'] != "") ? $db_user[0]['vImgName'] : $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName']; ?> " / -->
                                                <input type="hidden" name="photo_hidden" id="photo"
                                                       value="<?php echo ($db_user[0]['vImgName'] != "") ? $db_user[0]['vImgName'] : ''; ?>"/>
                                                <?php if ($db_user[0]['vImgName'] == '') {
                                                    $vImgNamepreview = "assets/img/profile-user-img.png";
                                                }
                                                else {
                                                    $vImgNamepreview = $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'];
                                                } ?>
                                                <a href="#" class="gen-btn fileupload-exists" data-dismiss="fileupload"
                                                   onclick="change_img('<?= $vImgNamepreview; ?>')"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                                            </div>
                                            <div class="upload-error"><span class="file_error"></span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="model-footer">
                                <div class="button-block">
                                    <input type="submit" class="gen-btn" name="save"
                                           value="<?= $langage_lbl['LBL_RIDER_Save']; ?>">
                                    <input type="button" data-form-type="profile_image" class="gen-btn" data-dismiss="modal" name="cancel"
                                           value="<?= $langage_lbl['LBL_BTN_PROFILE_RIDER_CANCEL_TRIP_TXT']; ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="custom-modal-main in" id="uiModal_4123" tabindex="-1" role="dialog"
                     aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-content image-upload-1 popup-box3">
                        <div class="upload-content">
                            <h4><?= $langage_lbl['LBL_RIDER_PROFILE_PICTURE']; ?></h4>
                            <form class="form-horizontal frm9" id="frm9" method="post" enctype="multipart/form-data"
                                  action="upload_pic.php" name="frm9">
                                <input type="hidden" name="action" value="photo"/>
                                <input type="hidden" name="img_path"
                                       value="<?= $tconfig["tsite_upload_images_passenger_path"]; ?>"/>
                                <div class="form-group">
                                    <div class="col-lg-12">
                                        <div class="fileupload fileupload-new" data-provides="fileupload">
                                            <div class="fileupload-preview thumbnail" id="fileupload-preview">
                                                <?php if ($db_user[0]['vImgName'] == '' || !file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'])) { ?>
                                                    <img class="imagename" src="assets/img/profile-user-img.png" alt="">
                                                <?php } else { ?>
                                                    <img class="imagename"
                                                         src="<?= $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'] ?>"
                                                         alt="<?= $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'] ?>"/>
                                                <?php } ?>

                                            </div>
                                            <div>
                                                    <span class="btn btn-file btn-success"><span
                                                                class="fileupload-new"><?= $langage_lbl['LBL_UPLOAD_PHOTO']; ?></span><span
                                                                class="fileupload-exists">
                                                            <?= $langage_lbl['LBL_Driver_document_CHANGE']; ?></span>
                                                        <input type="file" name="photo"/></span>
                                                <!-- input type="hidden" name="photo_hidden"  id="photo" value="<?php echo ($db_user[0]['vImgName'] != "") ? $db_user[0]['vImgName'] : $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName']; ?> " / -->
                                                <input type="hidden" name="photo_hidden" id="photo"
                                                       value="<?php echo ($db_user[0]['vImgName'] != "") ? $db_user[0]['vImgName'] : ''; ?>"/>
                                                <?php if ($db_user[0]['vImgName'] == '') {
                                                    $vImgNamepreview = "assets/img/profile-user-img.png";
                                                }
                                                else {
                                                    $vImgNamepreview = $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_user[0]['vImgName'];
                                                } ?>
                                                <a href="#" class="gen-btn fileupload-exists" data-dismiss="fileupload"
                                                   onclick="change_img('<?= $vImgNamepreview; ?>')"><?= $langage_lbl['LBL_CLOSE_TXT']; ?></a>
                                            </div>
                                            <div class="upload-error"><span class="file_error"></span></div>
                                        </div>
                                    </div>
                                </div>
                                <input type="submit" class="save" name="save"
                                       value="<?= $langage_lbl['LBL_RIDER_Save']; ?>">
                                <input type="button" class="cancel" data-dismiss="modal" name="cancel"
                                       value="<?= $langage_lbl['LBL_BTN_PROFILE_RIDER_CANCEL_TRIP_TXT']; ?>">
                            </form>

                            <div style="clear:both;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--<form action="index" class="general-form profile_edit">-->
            <form id="frm1" method="post" action="javascript:void(0);" class="general-form profile_edit">
                <input type="hidden" class="edit" name="action" value="all">
                <div class="partation">
                    <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?><span class="red">*</span></label>
                            <input type="hidden" name="uid" id="u_id1" value="<?= $_SESSION['sess_iUserId']; ?>">
                            <input type="email" id="in_email" class="edit-profile-detail-form-input"
                                   value="<?= $db_user[0]['vEmail'] ?>"
                                   name="email" <?= isset($db_user[0]['vEmail']) ? '' : ''; ?> required
                                   title="Please enter valid email address">
                            <div class="required-label" id="emailCheck"></div>
                        </div>
                        <div class="form-group half phone-column newrow">
                            <label><?= $langage_lbl['LBL_RIDER_Phone_Number']; ?><span class="red">*</span></label>
                            <select name="vPhoneCode" id="code" onChange="changeCode(this.value, 'phone');" dir="ltr" readonly style="pointer-events:none;background-image:none;">
                                <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                    <option value="<?= $db_country[$i]['vPhoneCode'] ?>"
                                            <?php if ($db_user[0]['vPhoneCode'] == $db_country[$i]['vPhoneCode']) { ?>selected<?php } ?>>
                                        <?= $db_country[$i]['vPhoneCode']; ?></option>
                                <?php } ?>
                            </select>
                            <input name="phone" type="text" id="phone" required value="<?= $db_user[0]['vPhone'] ?>"
                                   maxlength="15" title="<?= $langage_lbl['LBL_PHONE_VALID_MSG']; ?>"
                                   onkeypress="return isNumberKey(event);" onblur="return isNumberKey(event);"
                                   onKeyUp="return isNumberKey(event);"/>
                        </div>
                    <?php } else { ?>
                        <div class="form-group half phone-column newrow">
                            <label><?= $langage_lbl['LBL_RIDER_Phone_Number']; ?><span class="red">*</span></label>
                            <select name="vPhoneCode" id="code" onChange="changeCode(this.value, 'phone');" dir="ltr" readonly style="pointer-events:none;background-image:none;">
                                <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                    <option value="<?= $db_country[$i]['vPhoneCode'] ?>"
                                            <?php if ($db_user[0]['vPhoneCode'] == $db_country[$i]['vPhoneCode']) { ?>selected<?php } ?>>
                                        <?= $db_country[$i]['vPhoneCode']; ?></option>
                                <?php } ?>
                            </select>
                            <input name="phone" type="text" id="phone" required value="<?= $db_user[0]['vPhone'] ?>"
                                   maxlength="15" title="<?= $langage_lbl['LBL_PHONE_VALID_MSG']; ?>"
                                   onkeypress="return isNumberKey(event);" onblur="return isNumberKey(event);"
                                   onKeyUp="return isNumberKey(event);"/>
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?></label>
                            <input type="hidden" name="uid" id="u_id1" value="<?= $_SESSION['sess_iUserId']; ?>">
                            <input type="email" id="in_email" class="edit-profile-detail-form-input"
                                   value="<?= $db_user[0]['vEmail'] ?>"
                                   name="email" <?= isset($db_user[0]['vEmail']) ? '' : ''; ?>  <?php if ($RIDER_EMAIL_VERIFICATION == 'Yes') { ?>  title="<?= $langage_lbl['LBL_EMAIL_VERIFICATION_TITLE']; ?>"<?php } ?>>
                            <div class="required-label" id="emailCheck"></div>
                        </div>
                    <?php } ?>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_RIDER_YOUR_FIRST_NAME']; ?><span class="red">*</span></label>
                        <input type="text" value="<?= cleanall(htmlspecialchars($db_user[0]['vName'])) ?>" name="fname"
                               required>

                    </div>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_RIDER_YOUR_LAST_NAME']; ?><span class="red">*</span></label>
                        <input type="text" value="<?= cleanall(htmlspecialchars($db_user[0]['vLastName'])); ?>"
                               name="lname" required>
                    </div>
                    <div class="form-group half newrow floating">
                        <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?><span class="red">*</span></label>
                        <?php
                        if (scount($db_country) > 1) {
                            $style = "";
                        }
                        else {
                            $style = " disabled=disabled";
                        } ?>
                        <select <?= $style ?> name='country' id="country" required
                                              onChange="changeCode(this.value, 'country');">
                            <!-- <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option> -->
                            <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                <option value="<?= $db_country[$i]['vCountryCode'] ?>"
                                        <?php if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="required-label" id="countryCheck"></div>
                    </div>

                    <?php if (scount($db_lang) <= 1) { ?>
                        <input name="lang1" type="hidden" class="create-account-input"
                               value="<?php echo $db_lang[0]['vCode']; ?>"/>
                    <?php } else { ?>
                        <div class="form-group half newrow floating">
                            <label><?= $langage_lbl['LBL_PROFILE_SELECT_LANGUAGE']; ?><span class="red">*</span></label>
                            <select name="lang1" required>
                                <!-- <option value=""><?= $langage_lbl['LBL_PROFILE_SELECT_LANGUAGE']; ?></option> -->
                                <?php for ($i = 0; $i < scount($db_lang); $i++) { ?>
                                    <option value="<?= $db_lang[$i]['vCode'] ?>" <?php if ($db_lang[$i]['vCode'] == $db_user[0]['vLang']) { ?> selected <?php } ?>><?= $db_lang[$i]['vTitle'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="required-label" id="lang1"></div>
                    <?php }
                    if (scount($db_currency) <= 1) { ?>
                        <input name="vCurrencyPassenger" type="hidden" class="create-account-input"
                               value="<?php echo $db_currency[0]['vName']; ?>"/>
                    <?php } else { ?>
                        <div class="form-group half newrow floating">
                            <label><?= $langage_lbl['LBL_PROFILE_SELECT_CURRENCY']; ?><span class="red">*</span></label>
                            <select name='vCurrencyPassenger' required>
                                <!-- <option value=""><?= $langage_lbl['LBL_PROFILE_SELECT_CURRENCY']; ?></option> -->
                                <?php for ($i = 0; $i < scount($db_currency); $i++) { ?>
                                    <option value="<?= $db_currency[$i]['vName'] ?>"
                                            <?php if ($db_user[0]['vCurrencyPassenger'] == $db_currency[$i]['vName']) { ?>selected<?php } ?>><?= $db_currency[$i]['vName'] ?></option>
                                <?php } ?>
                            </select>
                            <div class="required-label" id="vCurrencyPassengerCheck"></div>
                        </div>
                    <?php }
                    if ($db_user[0]['vFbId'] >= 0 && $db_user[0]['vPassword'] != "") { ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_RIDER_CURR_PASS_HEADER']; ?></label>
                            <input type="password" class="input-box" name="cpass" id="cpass" onkeyup="nospaces(this)">
                        </div>
                    <?php } ?>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_RIDER_UPDATE_PASSWORD_HEADER_TXT']; ?></label>
                        <input type="password" class="input-box" name="npass" id="npass" onkeyup="nospaces(this)">
                    </div>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_RIDER_Confirm_New_Password']; ?></label>
                        <input type="password" class="input-box" name="ncpass" id="ncpass" onkeyup="nospaces(this)">
                    </div>
                </div>
                <div class="button-block">
                    <div class="btn-hold">
                        <input type="submit" value="<?= $langage_lbl['LBL_RIDER_Save']; ?>" style="padding: 15px 35px;">
                    </div>
                    <div class="btn-hold">
                        <input type="button" class="gen-btn cancel_btn"
                               value="<?= $langage_lbl['LBL_BTN_PROFILE_RIDER_CANCEL_TRIP_TXT']; ?>" style="padding: 15px 35px;">
                    </div>
                </div>
            </form>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <ul>
                <li>
                    <div class="earning-block" onclick="window.location.href='user_wallet'" style="cursor: pointer;">
                        <strong><?= $langage_lbl['LBL_MY_WALLET'] ?></strong>
                        <div class="count"><?= $user_available_balance ?></div>
                        <a class="go-icon ri-arrow-right-line" href="user_wallet"></a>
                    </div>
                </li>
                <?php if (($MANUAL_STORE_ORDER_ADMIN_PANEL == "Yes" || $MANUAL_STORE_ORDER_WEBSITE == "Yes" || $MANUAL_STORE_ORDER_STORE_PANEL == "Yes") && $deliverallModule == 'YES') { ?>
                    <li>
                        <div class="earning-block order-ico" onclick="window.location.href='myorder'"
                             style="cursor: pointer;">
                            <strong><?= $langage_lbl['LBL_MY_ORDERS'] ?></strong>
                            <div class="count"><?= $order_delivered ?></div>
                            <a class="go-icon ri-arrow-right-line" href="myorder"></a>
                        </div>
                    </li>
                <?php }
                if ($onlyDeliverallModule != 'YES' && !$MODULES_OBJ->isOnlyEnableRideSharingPro()  && !$MODULES_OBJ->isOnlyEnableBuySellRentPro()) { ?>
                    <li>
                        <?php PHP
                        if ($APP_TYPE == 'UberX') {
                            $link_pro = 'myJobs';
                        }
                        else {
                            $link_pro = 'mytrip';
                        }
                        ?>
                        <div class="earning-block trips-ico" onclick="window.location.href='<?php echo $link_pro; ?>'"
                             style="cursor: pointer;">
                            <strong><?= $langage_lbl['LBL_HEADER_TOPBAR_TRIPS_TEXT'] ?></strong>
                            <div class="count"><?= $trip_finished ?></div>
                            <a class="go-icon ri-arrow-right-line" href="<?php echo $link_pro; ?>"></a>
                        </div>
                    </li>

                    <?php if ($APP_TYPE != "UberX" && $serviceTheme == 0 && strtoupper($manual_booking_enable) == "YES" && $THEME_OBJ->isMedicalServicev2ThemeActive() == 'No') { //added by SP bcoz serivcex theme then booking and service booking open same page so dont show it
                        ?>
                        <li>
                            <div class="earning-block booking-ico" onclick="window.location.href='userbooking'"
                                 style="cursor: pointer;">
                                <?php /*<i class="place-holder-icon"><img src="assets/img/apptype/<?= $template ?>/trips.svg" alt=""></i>*/ ?>
                                <strong> <?= $langage_lbl['LBL_MANUAL_TAXI_DISPATCH'] ?></strong>
                                <div class="count"></div>
                                <a class="go-icon ri-arrow-right-line" href="userbooking"></a>
                            </div>
                        </li>
                    <?php }
                }

                if($MODULES_OBJ->isOnlyEnableBuySellRentPro()){ ?>
                    <li>
                        <div class="earning-block booking-ico" onclick="window.location.href='<?php echo $tconfig['tsite_url']; ?>property-listing'" style="cursor: pointer;">
                            <strong> <?= $langage_lbl['LBL_MYRENT_PROPERTY_LISTING'] ?></strong>
                            <div class="count"><?php echo $MCategory_category['9']; ?></div>
                            <a class="go-icon ri-arrow-right-line" href="userbooking"></a>
                        </div>
                    </li>
                    <li>
                        <div class="earning-block booking-ico" onclick="window.location.href='<?php echo $tconfig['tsite_url']; ?>car-listing'" style="cursor: pointer;">
                            <strong> <?= $langage_lbl['LBL_MYRENT_CARS_LISTING'] ?></strong>
                            <div class="count"><?php echo $MCategory_category['10']; ?></div>
                            <a class="go-icon ri-arrow-right-line" href="userbooking"></a>
                        </div>
                    </li>
                     <li>
                        <div class="earning-block booking-ico" onclick="window.location.href='<?php echo $tconfig['tsite_url']; ?>item-listing'" style="cursor: pointer;">
                            <strong> <?= $langage_lbl['LBL_MYRENT_GENERAL_ITEM_LISTING'] ?></strong>
                            <div class="count"><?php echo $MCategory_category['8']; ?></div>
                            <a class="go-icon ri-arrow-right-line" href="userbooking"></a>
                        </div>
                    </li>
                <?php }

                if($MODULES_OBJ->isOnlyEnableRideSharingPro()){ ?>
                    <li>
                        <div class="earning-block booking-ico" onclick="window.location.href='PublishedRide'"
                             style="cursor: pointer;">
                            <?php /*<i class="place-holder-icon"><img src="assets/img/apptype/<?= $template ?>/trips.svg" alt=""></i>*/ ?>
                            <strong> <?= $langage_lbl['LBL_RIDE_SHARE_PUBLISHED_RIDES_TXT'] ?></strong>
                            <div class="count"><?php echo $published_rides_count; ?></div>
                            <a class="go-icon ri-arrow-right-line" href="userbooking"></a>
                        </div>
                    </li>
                    <li>
                        <div class="earning-block booking-ico" onclick="window.location.href='BookedRide'"
                             style="cursor: pointer;">
                            <?php /*<i class="place-holder-icon"><img src="assets/img/apptype/<?= $template ?>/trips.svg" alt=""></i>*/ ?>
                            <strong> <?= $langage_lbl['LBL_RIDE_SHARE_BOOKED_RIDE'] ?></strong>
                            <div class="count"><?php echo $ride_share_bookings_count; ?></div>
                            <a class="go-icon ri-arrow-right-line" href="userbooking"></a>
                        </div>
                    </li>
                <?php }
                if ($MANUAL_STORE_ORDER_WEBSITE == "Yes" && ($deliverallModule == 'YES')) { ?>
                    <li>
                        <div class="earning-block food-ico" onclick="window.location.href='order-items?order=user'"
                             style="cursor: pointer;">
                            <?php /*<i class="place-holder-icon"><img src="assets/img/apptype/<?= $template ?>/<?= $imgname2 ?>" alt=""></i>*/ ?>
                            <strong><?= $langage_lbl['LBL_MANUAL_STORE_ORDER_TXT'] ?></strong>
                            <div class="count"></div>
                            <a class="go-icon ri-arrow-right-line" href="order-items?order=user"></a>
                        </div>
                    </li>
                <?php }
                if ($ufxEnable == 'Yes' && ENABLE_MANUAL_BOOKING_UBERX == 'Yes') { ?>
                    <li>
                        <div class="earning-block food-ico"
                             onclick="window.location.href='userbooking.php?userType1=rider&navigatedPage=UberX'"
                             style="cursor: pointer;">
                            <?php /*<i class="place-holder-icon"><img src="assets/img/apptype/<?= $template ?>/trips.svg" alt=""></i>*/ ?>
                            <strong><?= $langage_lbl['LBL_SERVICE_BOOKING'] ?></strong>
                            <div class="count"></div>
                            <a class="go-icon ri-arrow-right-line"
                               href="userbooking.php?userType1=rider&navigatedPage=UberX"></a>
                        </div>
                    </li>
                <?php } ?>
            </ul>


            <?php

          /*  if($MODULES_OBJ->isOnlyEnableRideSharingPro()){

                include_once('cx-profile_rider_document.php');
            }*/


            include_once('cx-profile_rider_document.php');


            ?>
        </div>
    </section>

    <?php

        include_once('account_delete_process_web.php');

    ?>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <div class="clearfix"></div>

</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
?>
<style>
    .upload-error .help-block {
        color: #b94a48;
    }
</style>
<script type="text/javascript" src="assets/js/moment.min.js"></script>
<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js"></script>
<?php if ($lang != 'en') { ?>
    <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
    <?php include_once('otherlang_validation.php'); ?>
<?php } ?>

<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<!--<link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css"/>
<script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>-->
<!--<script type="text/javascript" src="assets/js/moment.min.js"></script>-->
<!--<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.min.js"></script>-->
 <script type="text/javascript" src="assets/js/modal_alert.js" ></script>
<!-- End: Footer Script -->
<script type="text/javascript">
    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 35 || charCode > 57)) {
            return false;
        } else {
            return true;
        }
    }
    $(document).ready(function () {
        $('.frm9').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, element) {
                if (element.attr("name") == "photo") {
                    error.insertAfter("span.file_error");
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                photo: {
                    required: {
                        depends: function (element) {
                            if ($("#photo").val() == "NONE" || $("#photo").val() == "") {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    },
                     extension: imageUploadingExtenstionjsrule
                    // accept: "jpg|jpeg|png|bmp|gif|heic"
                },
            },
            messages: {
                photo: {
                    required: '<?php echo addslashes($langage_lbl['LBL_UPLOAD_IMG']); ?>',
                    // extension: '<?php echo addslashes($langage_lbl['LBL_UPLOAD_IMG_ERROR']); ?>'
                    // accept: imageUploadingExtenstionMsg
                    extension: imageUploadingExtenstionMsg
                }
            }
        });
        $("#show-edit-profile-div").click(function () {
            $("#hide-profile-div").hide();
            $("#show-edit-profile").show();
        });
        $("#hide-edit-profile-div").click(function () {
            $("#show-edit-profile").hide();
            $("#hide-profile-div").show();
            $("#frm1")[0].reset();
            var selectedOption = $('.custom-select-new.country').find(":selected").text();
            var selectedOption1 = $('.custom-select-new.lang1').find(":selected").text();
            var selectedOption2 = $('.custom-select-new.vCurrencyPassenger').find(":selected").text();
            if (selectedOption != "" || selectedOption1 != "" || selectedOption2 != "") {
                $('.custom-select-new.country').next(".holder").text(selectedOption);
                $('.custom-select-new.lang1').next(".holder").text(selectedOption1);
                $('.custom-select-new.vCurrencyPassenger').next(".holder").text(selectedOption2);
            }
        });
    });
</script>
<script>


    $(function () {


        $("#uploadDocExDate").datepicker({
            dateFormat: "yy-mm-dd",
            changeYear: true,
            changeMonth: true,
            yearRange: "-100:+10"
        });

    });
    function nospaces(t) {
        if (t.value.match(/\s/g)) {
            alert('Password should not contain whitespace.');
            //t.value=t.value.replace(/\s/g,'');
            t.value = '';
        }
    }
    function validate_password() {
        var cpass = document.getElementById('cpass').value;
        var npass = document.getElementById('npass').value;
        var ncpass = document.getElementById('ncpass').value;
        var err = '';
        //alert("here");
        // if (pass == '') {
        // err += "Something went wrong in Password.<BR>";
        // }
        /*if (cpass == '') {
         err += "<?php echo addslashes($langage_lbl['LBL_CURRENT_PASS_MSG']) ?><br/>";
             }
             if (npass == '') {
             err += "<?php echo addslashes($langage_lbl['LBL_NEW_PASS_MSG']) ?><br/>";
             }*/
        if (npass.length < 6 && npass != '') {
            err += "<?php echo addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']) ?>";
        }
        if (npass.length > 16 && npass != '') {
            err += "<?= addslashes($langage_lbl['LBL_PASS__MAX_LENGTH_MSG']); ?>";
        }
        if (ncpass == '' && npass != '') {
            err += "<?php echo addslashes($langage_lbl['LBL_REPASS_MSG']) ?>";
        }
        if (err == "") {
            // if (pass != cpass)
            // err += "Current password is incorrect.<br />";
            if (npass != ncpass)
                err += "<?php echo addslashes($langage_lbl['LBL_PASS_NOT_MATCH']) ?>";
        }
        if (err == "") {
            
        } else {
            //$('#cpass,#npass,#ncpass').val('');
            if ($('#npass').val() != "") {
                /*bootbox.dialog({
                    title: "&nbsp;",
                    message: "<h3>" + err + "</h3>",
                    buttons: {
                        danger: {
                            label: "Ok",
                            className: "btn-danger",
                        },
                    }
                });*/
                show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>",err, "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                    }
                }, true, true, true);
                //$('#cpass,#npass,#ncpass').val('');
            }

            return false;
        }
    }
    function validate_password_fb() {
        var npass = document.getElementById('npass').value;
        var ncpass = document.getElementById('ncpass').value;
        var err = '';
        if (npass == '') {
            err += "<?php echo addslashes($langage_lbl['LBL_NEW_PASS_MSG']) ?><br/>";
        }
        if (npass.length < 6) {
            err += "<?php echo addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']) ?><br/>";
        }
        if (ncpass == '') {
            err += "<?php echo addslashes($langage_lbl['LBL_REPASS_MSG']) ?><br/>";
        }
        if (err == "") {
            if (npass != ncpass)
                err += "<?php echo addslashes($langage_lbl['LBL_PASS_NOT_MATCH']) ?><br/>";
        }
        if (err == "") {
            //editProfile('pass');
            //return false;
        } else {
            //$('#npass').val('');
            //$('#ncpass').val('');
            // alert(err);
           /* bootbox.dialog({
                title: "&nbsp;",
                message: "<h3>" + err + "</h3>",
                buttons: {
                    danger: {
                        label: "Ok",
                        className: "btn-danger",
                    },
                }
            });*/
             show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>",err, "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
             }
                }, true, true, true);
            //document.getElementById("err_password").innerHTML = '<div class="alert alert-danger">' + err + '</div>';
            return false;
        }
    }
    /* Email */
    function change_img(action) {
        $('#fileupload-preview').html('<img src="' + action + '" />');
        $(".imagename").fadeIn();
    }
    $("#in_email").bind("keypress click", function () {
        $('#emailCheck').html('');
        $("#in_email").removeClass('required-active');
    });
    h = window.innerHeight;
    function changeCode(id, type) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_phonecountry.php',
            'AJAX_DATA': {
                id: id,
                type: type
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHTML = response.result;
                if (type == 'phone') {
                    $('select[name^="country"] option:selected').attr("selected", false);
                    $("#country option[value='" + dataHTML + "']").attr("selected", true);
                } else {
                    $('select[name^="vPhoneCode"] option:selected').prop("selected", false);
                    $("#code option[value='" + dataHTML + "']").prop("selected", true);
                   /* $('select[name^="vPhoneCode"] option:selected').attr("selected", null);
                    $("#code option[value='" + dataHTML + "']").attr("selected", "selected");*/
                }
            } else {
                console.log(response.result);
            }
        });
    }
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    var errormessage;
    $('#frm1').validate({
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
        //errorPlacement: function(error, element) {
        //    if(element.attr("name") == "vCurrencyPassenger")
        //      error.appendTo('#vCurrencyPassengerCheck');
        //    else if(element.attr("name") == "lang1")
        //      error.appendTo('#lang1');
        //    else if(element.attr("name") == "country")
        //      error.appendTo('#countryCheck');
        //    else
        //        error.insertAfter(element);
        //},
        onkeyup: function (element, event) {
            if (event.which === 9 && this.elementValue(element) === "") {
                return;
            } else {
                this.element(element);
            }
        },
        rules: {
            email: {
                <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                required: true,
                <?php } ?>
                email: true,
                remote: {
                    url: 'ajax_validate_email.php',
                    type: "post",
                    cache: false,
                    data: {
                        id: function (e) {
                            return $('#in_email').val();
                        },
                        action:'Edit-Profile',
                        usr: 'rider',
                        uid: function (e) {
                            return $("#u_id1").val();
                        }
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                    async: false
                }
            },
            phone: {
                required: true,
                digits: true,
                remote: {
                    url: 'ajax_rider_mobile_new.php',
                    type: "post",
                    cache: false,
                    data: {
                        vPhone: function (e) {
                            return $('#phone').val();
                        },
                        vPhoneCode: function (e) {
                            return $('#country option:selected').val();
                        },
                        usr: 'rider',
                        iUserId: function (e) {
                            return $("#u_id1").val();
                        }
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                    async: false
                }
            },
            fname: {
                // pattern: /^[a-zA-Z'.\s]{1,40}$/,
                required: true,
                minlength: 1,
                maxlength: 30
            },
            cpass: {
                required: function () {
                    return !!$('#npass').val();
                }
            },
            npass: {
                minlength: 6,
            },
            ncpass: {
                minlength: 6,
                equalTo: "#npass"
            },
            lname: {
                // pattern: /^[a-zA-Z'.\s]{1,40}$/,
                required: true,
                minlength: 1,
                maxlength: 30
            }
        },
        messages: {
            email: {
                remote: function () {
                    return errormessage;
                }
            },
            phone: {
                required: requiredFieldMsg,
                minlength: phoneMinLengthMsg,
                digits: phoneDigitMsg,
                remote: function () {
                    return errormessage;
                }
                // remote: function () {
                //     return errormessage;
                // }
            },
            fname: {
                required: requiredFieldMsg,
                minlength: NameMinLengthMsg,
                maxlength: NameMaxLengthMsg
            },
            lname: {
                required: requiredFieldMsg,
                minlength: NameMinLengthMsg,
                maxlength: NameMaxLengthMsg
            },
        },
        submitHandler: function (form) {
            if ($("#npass").val() != '' && $("#ncpass").val() == '') {
                alert('<?php echo addslashes($langage_lbl['LBL_CONFIRM_PWD_REQ']) ?>');
                return false;
            }
            if ($("#ncpass").val() == '') {
                valid = true;
            } else {
            <?php if ($db_user[0]['vFbId'] >= 0 && $db_user[0]['vPassword'] != "") { ?>
            valid = validate_password();
            <?php } else {
                if (empty($db_user[0]['vPassword']) && empty($db_user[0]['vPassword_token'])) { ?>
            valid = validate_password_fb();
            <?php } else { ?>
            valid = true;
            <?php }
            } ?>
            }

            ShpSq6fAm7(form);

            if ($("#frm1").valid() && valid != false) {
                $("#vCountry").prop('disabled', false);
                data = $("#frm1").serialize();
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_profile_rider_a.php',
                    'AJAX_DATA': data,
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var data = response.result;
                        if (data == '0' || data == 0) {
                            err = "<?php echo addslashes($langage_lbl['LBL_INCCORECT_CURRENT_PASS_ERROR_MSG']) ?>";
                            /*bootbox.dialog({
                                message: "<h3>" + err + "</h3>",
                                buttons: {
                                    danger: {
                                        label: "Ok",
                                        className: "btn-danger",
                                    },
                                }
                            });*/
                             show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>",err, "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                    }
                }, true, true, true);
                            $('#npass').val('');
                            $('#ncpass').val('');
                            $('#cpass').val('');
                            return false;
                        } else if (data == '2' || data == 2) {
                            window.location = "profile-rider?success=2&var_msg=" + data;
                            return false;
                        } else {
                            window.location = 'profile-rider?success=1';
                            return false;
                        }
                    } else {
                        console.log(response.result);
                    }
                });
            }
        },
        onfocusout: injectTrim($.validator.defaults.onfocusout)
    });
    function injectTrim(handler) {
        return function (element, event) {
            if (element.tagName === "TEXTAREA" || (element.tagName === "INPUT" && element.type !== "password")) {
                element.value = $.trim(element.value);
            }
            return handler.call(this, element, event);
        };
    }
    $(document).on('click', '[data-dismiss="modal"]', function (e) {
        var myValue = $(this).attr('data-form-type');
        if(myValue == "profile_image"){
            $("#frm9")[0].reset();
        }
    });




</script>
</body>

</html>