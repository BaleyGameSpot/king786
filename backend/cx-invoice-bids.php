<?php
include_once('common.php');

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider,driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$script = "Bids";

$eUserType = $_SESSION['sess_user'];
$_REQUEST['iBiddingPostId'] = base64_decode(base64_decode(trim($_REQUEST['iBiddingPostId'])));
$iBiddingPostId = isset($_REQUEST['iBiddingPostId']) ? $_REQUEST['iBiddingPostId'] : '';

if ($iBiddingPostId != "") {
    $checkItripId = $obj->MySQLSelect("SELECT iBiddingPostId,iUserId,iDriverId,vDriverCompanyId FROM bidding_post WHERE iBiddingPostId LIKE '" . $iBiddingPostId . "'");
    $eUserType = $_SESSION['sess_user'];

    if($checkItripId > 0){

        if (strtolower($eUserType) == 'rider' && $checkItripId[0]['iUserId'] != $_SESSION['sess_iUserId']) {
            header('Location:mybids');
            exit();
        } else if(strtolower($eUserType) == 'driver' && $checkItripId[0]['iDriverId'] != $_SESSION['sess_iUserId']) {
            header('Location:provider-bid');
            exit();
        } else if(strtolower($eUserType) == 'company') {
            if($checkItripId[0]['vDriverCompanyId'] != 0){
                if($checkItripId[0]['vDriverCompanyId'] != $_SESSION['sess_iUserId']){
                    header('Location:company-bid');
                    exit();
                }
            } else {
                $checkICompanyId = $obj->MySQLSelect("SELECT iCompanyId FROM register_driver WHERE iDriverId = '" . $checkItripId[0]['iDriverId'] . "'");
                $vDriverCompanyId = $checkICompanyId[0]['iCompanyId'];

                if($vDriverCompanyId != $_SESSION['sess_iUserId']){
                    header('Location:company-bid');
                    exit();
                }
            }
        }
    }
    if (scount($checkItripId) == 0) {
        header('Location:mybids');
    }
} else {
    header('Location:mybids');
}

$biddingData = $BIDDING_OBJ->getBiddingPost('webservice', $iBiddingPostId);

if($eUserType == "driver" || ( isset($_SESSION['sess_eSystem']) && $_SESSION['sess_eSystem'] == 'General' && $_SESSION['sess_user'] == 'company')){
    $UserType = 'Driver';
    $iMemberId = $biddingData[0]['iDriverId'];
} else {
    $UserType = 'Passenger';
    $iMemberId = $biddingData[0]['iUserId'];
}

$db_trip_data =  $BIDDING_OBJ->getFareDetails($iBiddingPostId, $iMemberId, $UserType , "user");

if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
    $vLang = $default_lang;
}

function remote_file_exists($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if( $httpCode == 200 ){return true;}
    return false;
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MYEARNING_INVOICE']; ?></title>
    <meta name="keywords" value="" />
    <meta name="description" value="" />
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
</head>

<body id="wrapper">
    <!-- home page -->
    <!-- home page -->
    <div id="main-uber-page">
        <!-- Left Menu -->
        <?php include_once("top/left_menu.php"); ?>
        <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php"); ?>
        <!-- End: Top Menu-->
        <!-- First Section -->
        <?php include_once("top/header.php"); ?>
        <!-- End: First Section -->
        <section class="profile-section">
            <div class="profile-section-inner">
                <div class="profile-caption _MB0_">
                    <div class="page-heading">
                        <h1><?= $langage_lbl['LBL_Invoice']; ?></h1>
                    </div>
                    <ul class="overview-detail">
                        <li>
                            <div class="overview-data">
                                <strong><?= $langage_lbl['LBL_RIDE_NO']; ?></strong>
                                <span><?= !empty($db_trip_data['vBiddingPostNo']) ? $db_trip_data['vBiddingPostNo'] : "&nbsp;"; ?></span>
                            </div>
                        </li>
                        <li>
                            <?php
                            $printCategory = $db_trip_data['vServiceDetailTitle'];
                            $subclass = ($printCategory == "") ? 'subdata' : '';
                            ?>
                            <div class="overview-data <? echo $subclass; ?> ">
                                <strong><?= $langage_lbl['LBL_MYTRIP_TRIP_TYPE']; ?></strong>
                                <span><?php echo !empty($printCategory) ? $printCategory : '&nbsp;';?>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <section class="profile-earning">
            <div class="profile-earning-inner">
                <div class="left-block">
                    <div class="inv-block-inner">
                        <strong class="sub-block-title"><?php echo $langage_lbl['LBL_FARE_BREAKDOWN_TXT']; ?></strong>
                        <div class="invoice-data-holder">
                            <div>
                                <?php if ($_SESSION['sess_user'] == "driver") { ?>
                                        <div class="profile-image">
                                            <? if ($db_trip_data['PassengerDetails']['vImgName'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'])) {
                                            ?>
                                                <img src="<?= $tconfig["tsite_upload_images_passenger"] . '/' . $db_trip_data['PassengerDetails']['iUserId'] . '/2_' . $db_trip_data['PassengerDetails']['vImgName'] ?>" /><!-- style="height:150px;" -->
                                            <? } else { ?>
                                                <img src="assets/img/profile-user-img.png" alt="">
                                            <? } ?>
                                        </div>
                                            <div class="inv-data">
                                                <strong><?= $langage_lbl['LBL_You_ride_with']; ?> <?= clearName($db_trip_data['userName']); ?>
                                        <?php } else { ?>
                                            <div class="profile-image">
                                                <? if (isset($db_trip_data['driverImage']) && $db_trip_data['driverImage'] != '' && remote_file_exists($db_trip_data['driverImage'])){ ?>
                                                    <img src="<?= $db_trip_data['driverImage']; ?>" style="height:150px;" />
                                                <? } else { ?>
                                                    <img src="assets/img/profile-user-img.png" alt="">
                                                <? } ?>
                                            </div>
                                            <div class="inv-data">
                                                <?php if (isset($db_trip_data['driverName'])) { ?>
                                                    <strong><?= $langage_lbl['LBL_You_ride_with']; ?> <?= clearName($db_trip_data['driverName']); ?>
                                                <?php } ?>
                                        <?php } ?>
                                            </strong>
                                                <ul>
                                                    <?
                                                    //added by SP for rounding off currency wise on 26-8-2019 start
                                                    $roundoff = 0;
                                                    if (array_key_exists($langage_lbl['LBL_ROUNDING_DIFF_TXT'], $db_trip_data['FareDetailsNewArr']) && !empty($db_trip_data['FareDetailsNewArr'][$langage_lbl['LBL_ROUNDING_DIFF_TXT']])) {
                                                        $roundoff = 1;
                                                    }
                                                    //added by SP for rounding off currency wise on 26-8-2019 end
                                                    foreach ($db_trip_data['FareDetailsNewArr'] as $key => $value) {
                                                        foreach ($value as $k => $val) {
                                                            if ($k == $langage_lbl['LBL_EARNED_AMOUNT']) {
                                                                continue;
                                                            } else if ($k == $langage_lbl['LBL_SUBTOTAL_TXT'] && $roundoff == 0) { //added by SP for rounding off currency wise on 26-8-2019
                                                                continue;
                                                            } else if ($k == $langage_lbl['LBL_ROUNDING_DIFF_TXT'] && $roundoff == 0) { //added by SP for rounding off currency wise on 26-8-2019
                                                                continue;
                                                            } else if ($k == $langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT'] && $roundoff == 1) {
                                                                continue;
                                                            } else if ($k == "eDisplaySeperator") {
                                                                //echo '<li class="eDisplaySeperator"><hr/></li>';
                                                            } else {
                                                            ?>
                                                                <li><span><?= $k; ?></span><b><?php echo $val; ?></b></li>
                                                            <?
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <?php if ($_SESSION['sess_user'] == "driver") { ?>
                                                        <li><strong><?= $langage_lbl['LBL_TOTAL_EARNINGS_FRONT']; ?></strong>
                                                            <b><?= $db_trip_data['FareSubTotal']; ?></b>
                                                        </li>
                                                    <?php } else { ?>
                                                        <li><strong><?= $langage_lbl['LBL_Total_Fare']; ?></strong>
                                                            <b><?= ($roundoff == 1) ? $db_trip_data['FareDetailsNewArr'][$langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT']] : $db_trip_data['FareSubTotal']; ?></b>
                                                        </li><!-- //added by SP for rounding off currency wise on 26-8-2019  -->
                                                    <?php } ?>
                                                </ul>

                                                <div style="clear:both;"></div>

                                            </div>
                                            </div>
                                            <?php if ($db_trip_data['is_rating'] == "Yes") { ?>
                                                <div class="inv-rating">
                                                    <?php if ($_SESSION['sess_user'] == "company") { ?>
                                                    <?php } else if ($_SESSION['sess_user'] == "driver") { ?>
                                                        <strong><?= $langage_lbl['LBL_TRIP_RATING_TXT']; ?>:</strong>
                                                        <?php
                                                        $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                        $db_trip_data['TripRating'] = '<span class="rating_img" style="width: 68px; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);"><span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);"></span></span>';
                                                        ?>
                                                        <?= $db_trip_data['TripRating']; ?>
                                                    <?php } else { ?>
                                                        <strong><?= $langage_lbl['LBL_TRIP_RATING_TXT']; ?>:</strong>
                                                        <?php
                                                        $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                        $db_trip_data['TripRating'] = '<span class="rating_img" style="display: block; width: 68px; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);">
									<span style="margin: 0;float:left;display: block; width: ' . $rating_width . '%; height: 13px; background-image: url(' . $tconfig['tsite_upload_apptype_images'] . $template . '/rating-stripe.svg);"></span>
									</span>'; ?>
                                                        <?= $db_trip_data['TripRating']; ?>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                    </div>
                            </div>
                        </div>
                        <div class="left-right">
                            <div class="inv-destination-data">
                                <div>
                                    <ul>
                                        <?php
                                        $systemTimeZone = date_default_timezone_get();

                                        if ($db_trip_data['dBiddingDate'] != "" && $biddingData[0]['vTimeZone'] != "") {

                                            $dBookingDate = converToTz($db_trip_data['dBiddingDate'], $biddingData[0]['vTimeZone'], $systemTimeZone);

                                        } else {
                                            $dBookingDate = $db_trip_data['dBiddingDate'];
                                        }
                                        $date_format_data_array = array(
                                            'tdate' => $dBookingDate,
                                            'langCode' => $vLang,
                                            'DateFormatForWeb' => 1
                                        );
                                        $get_dBookingDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                        ?>
                                        <?php if ($_SESSION['sess_user'] != "driver") { 
                                            ?>
                                            <li>
                                                <i class="fa fa-user"></i>
                                                <div class="destination_data_right">
                                                <strong><?= $langage_lbl['LBL_DRIVER_NAME']; ?>:</strong>
                                                <?php if(!empty($db_trip_data['driverName'])){ ?>
                                                    <p><?= clearName($db_trip_data['driverName']); ?></p>
                                                <?php } ?>
                                                </div>
                                            </li>
                                        <?php } ?>
                                        <li>
                                            <i class="fa fa-calendar"></i>
                                            <div class="destination_data_right">
                                            <strong><?= $langage_lbl['LBL_TRIP_DATE_TXT_DRDL']; ?>:</strong>
                                            <p><?= $get_dBookingDate_date_format['tDisplayDateTime'];//@date('d M Y', @strtotime($dBookingDate)); ?></p>
                                            </div>
                                        </li>

                                        <li>
                                            <i class="fa fa-map-marker"></i>
                                            <div class="destination_data_right">
                                            <strong><?= $langage_lbl['LBL_PICKUP_LOCATION_TXT']; ?>:</strong>
                                            <p><?= $db_trip_data['tSaddress']; ?></p>
                                            </div>
                                        </li>

                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <div class="destination_data_right">
                                            <strong><?= $langage_lbl['LBL_PICKUP_TIME']; ?>:</strong>
                                            <p><?= $get_dBookingDate_date_format['tDisplayTimeAbbr'];//@date('h:i A', @strtotime($dBookingDate)); ?></p>
                                            </div>
                                        </li>

                                    </ul>

                                </div>

                                <div class="invoice-pay-type">
                                    <strong><?= $langage_lbl['LBL_PAYMENT_TYPE_CAPS']; ?> :</strong>
                                    <strong><?php
                                            if (strtoupper($db_trip_data['vBiddingPaymentMode']) == 'CASH')
                                                $paymentMode = ucwords($langage_lbl['LBL_CASH_CAPS']);
                                            else if (strtoupper($db_trip_data['vBiddingPaymentMode']) == 'CARD')
                                                $paymentMode = ucwords($langage_lbl['LBL_CARD_CAPS']);
                                            else if (strtoupper($db_trip_data['ePayWallet']) == 'YES' || strtoupper($db_trip_data['vBiddingPaymentMode']) == 'WALLET')
                                                $paymentMode = ucwords($langage_lbl['LBL_HEADER_RDU_WALLET']);
                                            echo $paymentMode;
                                            ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
        </section>

        <?php include_once('footer/footer_home.php'); ?>
        <div style="clear:both;"></div>
    </div>
    <!-- footer part end -->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php'); ?>
    <!-- End: Footer Script -->
    <?php
    $lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
    ?>
    <?php if ($lang != 'en') { ?>
        <? include_once('otherlang_validation.php'); ?>
    <?php } ?>
    <!-- home page end-->
</body>

</html>