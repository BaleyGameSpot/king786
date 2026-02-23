<?php
include_once('common.php');
$tbl_name = 'trips';
$script = "Trips";
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider,driver,company,organization';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
$APP_DELIVERY_MODE = $CONFIG_OBJ->getConfigurations("configurations","APP_DELIVERY_MODE");
$eUserType = $_SESSION['sess_user'];
if (isset($_REQUEST['iTripId'])){
    $_REQUEST['iTripId'] = base64_decode(base64_decode(trim($_REQUEST['iTripId'])));
}
if (isset($_REQUEST['iJobId'])){
    $_REQUEST['iTripId'] = base64_decode(base64_decode(trim($_REQUEST['iJobId'])));
}
$iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';
if ($iTripId != ""){

    $checkItripId = $obj->MySQLSelect("SELECT iTripId,iUserId,iDriverId,iCompanyId,iOrganizationId FROM trips WHERE iTripId = '".$iTripId."'");
    if ($checkItripId > 0){

        if (strtolower($eUserType) == 'rider' && $checkItripId[0]['iUserId'] != $_SESSION['sess_iUserId']){
            header('Location:mytrip');
            exit();
        }else if (strtolower($eUserType) == 'driver' && $checkItripId[0]['iDriverId'] != $_SESSION['sess_iUserId']){
            header('Location:provider-job');
            exit();
        }else if (strtolower($eUserType) == 'company'){
            if ($checkItripId[0]['iCompanyId'] != 0){
                if ($checkItripId[0]['iCompanyId'] != $_SESSION['sess_iUserId']){
                    header('Location:company-trip');
                    exit();
                }
            }else{
                $checkICompanyId = $obj->MySQLSelect("SELECT iCompanyId FROM register_driver WHERE iDriverId = '".$checkItripId[0]['iDriverId']."'");
                $vDriverCompanyId = $checkICompanyId[0]['iCompanyId'];
                if ($vDriverCompanyId != $_SESSION['sess_iUserId']){
                    header('Location:company-trip');
                    exit();
                }
            }
        }else if (strtolower($eUserType) == 'organization' && $checkItripId[0]['iOrganizationId'] != $_SESSION['sess_iUserId']){
            header('Location:users-trip');
            exit();
        }
    }
    if (scount($checkItripId) == 0 || empty($checkItripId)){
        header('Location:mytrip');
    }
}else{
    header('Location:mytrip');
}
/* Start original route */
$sql = "select tPlatitudes,tPlongitudes from trips_locations where iTripId = '".$iTripId."' AND iTripId != ''";
$data_locations = $obj->MySQLSelect($sql);
$lat_array = $long_array = array();
if (!empty($data_locations) && scount($data_locations) > 0){
    $lat_array = explode(",",str_replace(" ","",$data_locations[0]['tPlatitudes']));
    $long_array = explode(",",str_replace(" ","",$data_locations[0]['tPlongitudes']));
}
/*if(scount($data_locations) == 0)
{
    header('Location:mytrip');
}*/
//Commented By HJ On 17-02-2020 As Per Discuss With KS For Solved Issu Demo Server Mantis #9935 Start
// if(empty($data_locations)) {
if (empty($lat_array[0])){
    $sql = "select tStartLat,tStartLong,tEndLat,tEndLong from trips where iTripId = '".$iTripId."'";
    $data_locations = $obj->MySQLSelect($sql);
    $lat_array[0] = $data_locations[0]['tStartLat'];
    $lat_array[1] = $data_locations[0]['tEndLat'];
    $long_array[0] = $data_locations[0]['tStartLong'];
    $long_array[1] = $data_locations[0]['tEndLong'];
}
//Commented By HJ On 17-02-2020 As Per Discuss With KS For Solved Issu Demo Server Mantis #9935 End
$total_ele = scount($lat_array);
$inc = 1;
if ($total_ele > 200){
    $inc = round($total_ele / 200);
}
// echo $inc=5;
for ($i = 0;$i < $total_ele;$i += $inc){
    $latitudes[] = $lat_array[$i];
    $longitudes[] = $long_array[$i];
}
$orgDataArr = array();
$org_sql = "SELECT vCompany,iOrganizationId FROM organization ORDER BY iOrganizationId ASC";
$orgData = $obj->MySQLSelect($org_sql);

for ($g = 0;$g < scount($orgData);$g++){
    $orgDataArr[$orgData[$g]['iOrganizationId']] = $orgData[$g]['vCompany'];
}
array_push($latitudes,$lat_array[$total_ele - 1]);
array_push($longitudes,$long_array[$total_ele - 1]);
// ---------- Added By NModi -- on 331-dec-20 START--------------
$multi_location = 0;
$getAllTrip = $data_locations = $obj->MySQLSelect("SELECT tStartLat,tStartLong,tEndLat,tEndLong,iTripId,iPoolParentId FROM trips WHERE iTripId=".$iTripId."");
if (empty($latitudes[0]) && strtoupper($ENABLE_STOPOVER_POINT) == 'YES'){
    $latitudes = $longitudes = array();
    //$data_locations = $obj->MySQLSelect("select tStartLat,tStartLong,tEndLat,tEndLong from trips where iTripId = '" . $iTripId . "'");
    $latitudes[] = $data_locations[0]['tStartLat'];
    $longitudes[] = $data_locations[0]['tStartLong'];
    $sql = "select tActualDestLatitude,tActualDestLongitude,tDestLatitude,tDestLongitude from trips_stopoverpoint_location where iTripId = '".$iTripId."'";
    $data_locations_mul = $obj->MySQLSelect($sql);
    foreach ($data_locations_mul as $key => $value){
        $latitudes[] = (!empty($value['tActualDestLatitude']))?$value['tActualDestLatitude']:$value['tDestLatitude'];
        $longitudes[] = (!empty($value['tActualDestLongitude']))?$value['tActualDestLongitude']:$value['tDestLongitude'];
    }
    $total_ele = scount($latitudes);
    $multi_location = 1;
}
// ---------- Added By NModi -- on 331-dec-20 END--------------
/* End original route */
$getAllTrip = $vehilceTypeArr = array();
$getVehicleTypes = $obj->MySQLSelect("SELECT iVehicleTypeId,vVehicleType_".$default_lang." AS vehicleType FROM vehicle_type WHERE 1=1");
for ($r = 0;$r < scount($getVehicleTypes);$r++){
    $vehilceTypeArr[$getVehicleTypes[$r]['iVehicleTypeId']] = $getVehicleTypes[$r]['vehicleType'];
}
$tsite_sc_host = $tconfig['tsite_sc_host'];
$tsite_host_sc_port = $tconfig['tsite_host_sc_port'];
?>
<?php
$ssql_trip = "";
if ($_SESSION['sess_user'] == "rider"){
    $ssql_trip = " where iTripId = '".$iTripId."' AND iUserId = '".$_SESSION['sess_iUserId']."'";
}else if ($_SESSION['sess_user'] == "driver"){
    $ssql_trip = " where iTripId = '".$iTripId."' AND iDriverId = '".$_SESSION['sess_iUserId']."'";
}else if ($_SESSION['sess_user'] == "company"){
    $ssql_trip = " t LEFT JOIN register_driver d ON t.iDriverId = d.iDriverId where t.iTripId = '".$iTripId."' AND d.iCompanyId = '".$_SESSION['sess_iUserId']."'";
}else{
    $ssql_trip = " where iTripId = '".$iTripId."' AND iOrganizationId = '".$_SESSION['sess_iUserId']."'";
}
$user_trip_sql = "select * from trips ".$ssql_trip;
$user_trip_sql_data = $obj->MySQLSelect($user_trip_sql);
if (!empty($user_trip_sql_data)){
    if ($_SESSION['sess_user'] == 'driver'){
        $db_trip_data = FetchTripFareDetailsWeb($iTripId,$_SESSION['sess_iUserId'],'Driver');
    }else if ($_SESSION['sess_user'] == 'rider'){
        $db_trip_data = FetchTripFareDetailsWeb($iTripId,$_SESSION['sess_iUserId'],'Passenger');
    }else if ($_SESSION['sess_user'] == 'organization'){
        $db_trip_data = FetchTripFareDetailsWeb($iTripId,$_SESSION['sess_iUserId'],'Passenger','','Yes');
    }else{
        $db_trip_data = FetchTripFareDetailsWeb($iTripId,'','Driver');
    }
}
$organizationName = "";
//Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected Start
if (isset($db_trip_data['tVehicleTypeFareData']) && $db_trip_data['tVehicleTypeFareData'] != ""){
    $decodeTypeData = json_decode($db_trip_data['tVehicleTypeFareData'],true);
    $decodeTypeData = $decodeTypeData['FareData'];
    $db_trip_data['videoCategory'] = $db_trip_data['vCategory'];
    $db_trip_data['vCategory'] = $db_trip_data['vVehicleCategory'] = isset($decodeTypeData[0]['vVehicleCategory'])?$decodeTypeData[0]['vVehicleCategory']:'';
}
//Added By HJ On 08-02-2019 For Get Main Category Name When Multiple Service Selected End
if (isset($orgDataArr[$db_trip_data['iOrganizationId']]) && $orgDataArr[$db_trip_data['iOrganizationId']] != "" && $db_trip_data['ePaymentBy'] == "Organization" && $eUserType == "rider"){
    $organizationName = $orgDataArr[$db_trip_data['iOrganizationId']];
}
if ($db_trip_data['eType'] == 'UberX'){
    $langage_lbl['LBL_PICKUP_LOCATION_TXT'] = $langage_lbl['LBL_JOB_LOCATION_TXT'];
    $langage_lbl['LBL_TRIP_DATE_TXT'] = $langage_lbl['LBL_TRIP_DATE_TXT_DRDL'];
    $langage_lbl['LBL_PICKUP_TIME'] = $langage_lbl['LBL_RIDER_Trip_time'];
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <!--<title><?=$SITE_NAME?></title>-->
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_MYEARNING_INVOICE'];?></title>
    <meta name="keywords" value=""/>
    <meta name="description" value=""/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->

    <style type="text/css">
        #map-canvas ul[role="menu"],
        #map-canvas li[role="menuitemcheckbox"] {
            width: auto;
        }

        #map-canvas li[role="menuitemcheckbox"] {
            margin-bottom: 0;
        }
    </style>
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
                    <h1><?=$langage_lbl['LBL_Invoice'];?></h1>
                </div>
                <ul class="overview-detail">
                    <li>
                        <div class="overview-data">
                            <strong><?=$langage_lbl['LBL_RIDE_NO'];?></strong>
                            <span><?=!empty($db_trip_data['vRideNo'])?$db_trip_data['vRideNo']:"&nbsp;";?></span>
                        </div>
                    </li>
                    <li>
                        <?php
                        if (!empty($db_trip_data['vVehicleCategory'])){
                            $printCategory = $db_trip_data['vVehicleCategory'];
                            if ($db_trip_data['vVehicleType'] != ""){
                                $printCategory .= "-".$db_trip_data['vVehicleType'];
                            }
                        }else{
                            $printCategory = $db_trip_data['carTypeName'];
                        }
                        $subclass = ($printCategory == "")?'subdata':'';
                        ?>
                        <div class="overview-data <? echo $subclass; ?> ">
                            <? if ($db_trip_data['eType'] == 'UberX'){ ?>
                                <strong><?=$langage_lbl['LBL_MYTRIP_TRIP_TYPE'];?></strong>
                            <? }else{ ?>
                                <strong><?=$langage_lbl['LBL_VEHICLE_TITLE'];?></strong>
                            <? } ?>
                            <span>
                                    <?php
                                    if ($MODULES_OBJ->isEnableVideoConsultingService() && $db_trip_data['isVideoCall'] == 'Yes'){
                                        echo $db_trip_data['videoCategory'].'('.$langage_lbl['LBL_VIDEO_CONSULTATION_TXT'].')';
                                    }else{
                                        $seriveJson = "";
                                        //if (isset($db_trip_data['tVehicleTypeData']) && $db_trip_data['tVehicleTypeData'] != "" && $printCategory == "") {
                                        //$seriveJson = $db_trip_data['tVehicleTypeData'];
                                        if (isset($db_trip_data['tVehicleTypeFareData']) && $db_trip_data['tVehicleTypeFareData'] != "" && $printCategory == ""){
                                            $seriveJson = json_decode($db_trip_data['tVehicleTypeFareData']);
                                            $seriveJson = json_encode($seriveJson->FareData);
                                            ?>
                                            <button class="btn btn-success gen-btn" data-trip="<?=$db_trip_data['vRideNo'];?>" data-json='<?=$seriveJson;?>' data-jsonVehicleTypeData='<?=$db_trip_data['tVehicleTypeData'];?>' onclick="return showServiceModal(this);">
                                                <i class="fa fa-certificate"><b><?php echo $langage_lbl['LBL_VIEW_SERVICE']; ?></b></i>
                                            </button>
                                            <textarea style="display: none;"><?=$seriveJson;?></textarea>
                                            <?php
                                        }else{
                                            echo !empty($printCategory)?$printCategory:'&nbsp;';
                                        }
                                    }
                                    ?>
                                </span>
                        </div>
                    </li>
                    <?php if ($db_trip_data['eType'] != 'UberX'){ ?>
                        <li>
                            <div class="overview-data">
                                <strong><?=$langage_lbl['LBL_Trip_time'];?></strong>
                                <span><?=!empty($db_trip_data['TripTimeInMinutes'])?$db_trip_data['TripTimeInMinutes']:"&nbsp;";?></span>
                            </div>
                        </li>
                        <li>
                            <div class="overview-data">
                                <strong><?=$langage_lbl['LBL_DISTANCE_TXT'];?></strong>
                                <span><?=!empty($db_trip_data['fDistance'])?$db_trip_data['fDistance']." ".$db_trip_data['DisplayDistanceTxt']:"&nbsp;";?></span>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="left-block">
                <div class="inv-block-inner">
                    <?php if ($db_trip_data['eType'] != 'UberX'){ ?>
                        <div class="trip-detail-map" id="invoice_map">
                            <div id="map-canvas" class="gmap3" style="width:100%;height:300px;margin-bottom:10px;"></div>
                        </div>
                    <?php } ?>
                    <!--<div id="invoice_map" style="background-image:url(images/map-back.jpg)"></div>-->
                    <strong class="sub-block-title"><?php echo $langage_lbl['LBL_FARE_BREAKDOWN_TXT']; ?></strong>
                    <div class="invoice-data-holder">
                        <div>
                            <?php if ($_SESSION['sess_user'] == "company") { ?>

                            <div class="inv-data profile-image-blank">
                                <strong><?=clearName($db_trip_data['PassengerDetails']['vName'].' '.$db_trip_data['PassengerDetails']['vLastName']);?> <?=$langage_lbl['LBL_RIDE_TXT_ADMIN'];?> <?=$langage_lbl['LBL_WITH_TXT'];?> <?=clearName($db_trip_data['DriverDetails']['vName'].' '.$db_trip_data['DriverDetails']['vLastName']);?>

                                    <?php } else if ($_SESSION['sess_user'] == "driver") { ?>
                                    <div class="profile-image">
                                        <? if ($db_trip_data['PassengerDetails']['vImgName'] != '' && file_exists($tconfig["tsite_upload_images_passenger_path"].'/'.$db_trip_data['PassengerDetails']['iUserId'].'/2_'.$db_trip_data['PassengerDetails']['vImgName'])){
                                            ?>
                                            <img src="<?=$tconfig["tsite_upload_images_passenger"].'/'.$db_trip_data['PassengerDetails']['iUserId'].'/2_'.$db_trip_data['PassengerDetails']['vImgName']?>"/><!-- style="height:150px;" -->
                                        <? }else{ ?>
                                            <img src="assets/img/profile-user-img.png" alt="">
                                        <? } ?>
                                    </div>
                                    <div class="inv-data">
                                        <strong><?=$langage_lbl['LBL_You_ride_with'];?> <?=clearName($db_trip_data['PassengerDetails']['vName'].' '.$db_trip_data['PassengerDetails']['vLastName']);?>
                                            <?php } else { ?>
                                            <div class="profile-image">
                                                <? if ($db_trip_data['DriverDetails']['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"].'/'.$db_trip_data['DriverDetails']['iDriverId'].'/2_'.$db_trip_data['DriverDetails']['vImage'])){ ?>
                                                    <img src="<?=$tconfig["tsite_upload_images_driver"].'/'.$db_trip_data['DriverDetails']['iDriverId'].'/2_'.$db_trip_data['DriverDetails']['vImage']?>" style="height:150px;"/>
                                                <? }else{ ?>
                                                    <img src="assets/img/profile-user-img.png" alt="">
                                                <? } ?>
                                            </div>
                                            <div class="inv-data">
                                                <strong><?=$langage_lbl['LBL_You_ride_with'];?> <?=clearName($db_trip_data['DriverDetails']['vName'].' '.$db_trip_data['DriverDetails']['vLastName']);?>
                                                    <?php } ?>
                                                </strong>
                                                <ul>
                                                    <?
                                                    //added by SP for rounding off currency wise on 26-8-2019 start
                                                    $roundoff = 0;
                                                    if (array_key_exists($langage_lbl['LBL_ROUNDING_DIFF_TXT'],$db_trip_data['FareDetailsArr']) && !empty($db_trip_data['FareDetailsArr'][$langage_lbl['LBL_ROUNDING_DIFF_TXT']])){
                                                        $roundoff = 1;
                                                    }
                                                    //added by SP for rounding off currency wise on 26-8-2019 end
                                                    foreach ($db_trip_data['HistoryFareDetailsNewArr'] as $key => $value){
                                                        foreach ($value as $k => $val){
                                                            if ($k == $langage_lbl['LBL_EARNED_AMOUNT']){
                                                                continue;
                                                            }else if ($k == $langage_lbl['LBL_SUBTOTAL_TXT'] && $roundoff == 0){ //added by SP for rounding off currency wise on 26-8-2019
                                                                continue;
                                                            }else if ($k == $langage_lbl['LBL_ROUNDING_DIFF_TXT'] && $roundoff == 0){ //added by SP for rounding off currency wise on 26-8-2019
                                                                continue;
                                                            }else if ($k == $langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT'] && $roundoff == 1){
                                                                continue;
                                                            }else if ($k == "eDisplaySeperator"){
                                                                //echo '<li class="eDisplaySeperator"><hr/></li>';
                                                            }else if ($k == $langage_lbl['LBL_NORMAL_FARE']){ ?>
                                                                <li style="border-top: 1px dashed #000">
                                                                    <span><?=$k;?></span><b><?php echo $val; ?></b></li>
                                                            <? }else{ ?>
                                                                <li><span><?=$k;?></span><b><?php echo $val; ?></b></li>
                                                                <?
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                    <?php if ($_SESSION['sess_user'] == "driver" || $_SESSION['sess_user'] == "company"){ ?>
                                                        <li>
                                                            <strong><?=$langage_lbl['LBL_TOTAL_EARNINGS_FRONT'];?></strong>
                                                            <b><?=$db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_EARNED_AMOUNT']];?></b>
                                                        </li>
                                                    <?php }else{ ?>
                                                        <li><strong><?=$langage_lbl['LBL_Total_Fare'];?></strong>
                                                            <b><?=($roundoff == 1)?$db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_ROUNDING_NET_TOTAL_TXT']]:$db_trip_data['HistoryFareDetailsArr'][$langage_lbl['LBL_SUBTOTAL_TXT']];?></b>
                                                        </li><!-- //added by SP for rounding off currency wise on 26-8-2019  -->
                                                    <?php } ?>
                                                </ul>

                                                <?php if (($db_trip_data['iActive'] == 'Finished' && $db_trip_data['eCancelled'] == "Yes") || ($db_trip_data['fCancellationFare'] > 0) || ($db_trip_data['iActive'] == 'Canceled' && $db_trip_data['fWalletDebit'] > 0)){
                                                    ?>
                                                    <ul style="margin-top: 10px;">
                                                        <li style="border:dotted 2px #000000;background: none;">
                                                            <strong style="font-weight: bold;padding: 2px;">
                                                                <?
                                                                if ($db_trip_data['eCancelledBy'] == 'Driver'){
                                                                    echo $langage_lbl['LBL_TRIP_CANCELLED_BY_DRIVER_ADMIN'];
                                                                    echo '<br/>';
                                                                    if (!empty($db_trip_data['vCancelReason'])){
                                                                        echo $langage_lbl['LBL_CANCEL_REASON'].': '.$db_trip_data['vCancelReason'];
                                                                    }
                                                                }else if ($db_trip_data['eCancelledBy'] == 'Passenger'){
                                                                    echo $langage_lbl['LBL_TRIP_CANCELLED_BY_PASSANGER_ADMIN'];
                                                                    echo '<br/>';
                                                                    if (!empty($db_trip_data['vCancelReason'])){
                                                                        echo $langage_lbl['LBL_CANCEL_REASON'].': '.$db_trip_data['vCancelReason'];
                                                                    }
                                                                }else{
                                                                    echo $langage_lbl['LBL_CANCELED_TRIP_ADMIN_TXT'];
                                                                }
                                                                ?>
                                                            </strong>
                                                        </li>
                                                    </ul>
                                                    <div style="clear:both;"></div>
                                                <? } ?>
                                                <?php
                                                if ($db_trip_data['fTipPrice'] != "" && $db_trip_data['fTipPrice'] != "0" && $db_trip_data['fTipPrice'] != "0.00"){
                                                    ?>
                                                    <ul>
                                                        <li>
                                                            <strong><?=$langage_lbl['LBL_TIP_RS_TXT'];?></strong><b> <?=$db_trip_data['fTipPrice'];?></b>
                                                        </li>
                                                    </ul>
                                                    <?
                                                } ?>
                                                <div style="clear:both;"></div>
                                                <?php
                                                if (scount($getAllTrip) > 0){
                                                    $tableHaed = "Trips connected with this Pool Trip";
                                                    if (scount($getAllTrip) == 1){
                                                        $tableHaed = "Trip connected with this Pool Trip";
                                                    }
                                                    echo "<h4>".$tableHaed."</h4>";
                                                    ?>
                                                    <table border="1" width="100%">
                                                        <tr>
                                                            <th style="border: 1px solid #000000; width:20%;">#Sr No.</th>
                                                            <th style="border: 1px solid #000000; width:20%;">Booking No</th>
                                                            <th style="border: 1px solid #000000; width:20%;">Status</th>
                                                        </tr>
                                                        <?php
                                                        for ($t = 0;$t < scount($getAllTrip);$t++){
                                                            $link_page = "cx-invoice.php";
                                                            $srNO = $t + 1;
                                                            ?>
                                                            <tr>
                                                                <td width="10%" style="border: 1px solid #000000;"><?=$srNO;?></td>
                                                                <td width="45%" style="border: 1px solid #000000;">
                                                                    <a target="_blank" href="<?=$link_page?>?iTripId=<?=base64_encode(base64_encode($getAllTrip[$t]['iTripId']))?>"><strong><?=$getAllTrip[$t]['vRideNo'];?>
                                                                    </a></td>
                                                                <td width="45%" style="border: 1px solid #000000;"><?=$getAllTrip[$t]['iActive'];?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                <?php } ?>

                                            </div>
                                    </div>
                                    <?php if ($db_trip_data['TripRating'] > 0){ ?>
                                        <div class="inv-rating">
                                            <?php if ($_SESSION['sess_user'] == "company"){ ?>
                                            <?php }else if ($_SESSION['sess_user'] == "driver"){ ?>
                                                <strong><?=$langage_lbl['LBL_TRIP_RATING_TXT'];?>:</strong>
                                                <?php
                                                $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                $db_trip_data['TripRating'] = '<span class="rating_img" style="width: 68px; height: 13px; background-image: url('.$tconfig['tsite_upload_apptype_images'].$template.'/rating-stripe.svg);">
    <span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background-image: url('.$tconfig['tsite_upload_apptype_images'].$template.'/rating-stripe.svg);"></span>
    </span>';
                                                ?>
                                                <?=$db_trip_data['TripRating'];?>
                                            <?php }else{ ?>
                                                <strong><?=$langage_lbl['LBL_TRIP_RATING_TXT'];?>:</strong>
                                                <?php
                                                $rating_width = ($db_trip_data['TripRating'] * 100) / 5;
                                                $db_trip_data['TripRating'] = '<span class="rating_img" style="display: block; width: 68px; height: 13px; background-image: url('.$tconfig['tsite_upload_apptype_images'].$template.'/rating-stripe.svg);">
									<span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background-image: url('.$tconfig['tsite_upload_apptype_images'].$template.'/rating-stripe.svg);"></span>
									</span>';
                                                ?>
                                                <?=$db_trip_data['TripRating'];?>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                            </div>
                            <?php if ($db_trip_data['eType'] == 'UberX' && ($db_trip_data['vBeforeImage'] != '' || $db_trip_data['vAfterImage'] != '')){ ?>
                                <div>&nbsp;</div>
                                <div class="invoice-data-holder">
                                    <?php $img_path = $tconfig["tsite_upload_trip_images"]; ?>
                                    <h3><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT']; ?></b></h3>
                                    <div class="invoice-right-bottom-img">
                                        <?php if ($db_trip_data['vBeforeImage'] != ''){ ?>
                                            <div class="beforeafterimage">
                                                <h4> <?php echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN']; ?></h4>
                                                <b><a href="<?=$db_trip_data['vBeforeImage']?>" target="_blank"><img src="<?=$db_trip_data['vBeforeImage']?>" style="width:200px;" alt="Before Images"/></a></b>
                                            </div>
                                            <?php
                                        }
                                        if ($db_trip_data['vAfterImage'] != ''){
                                            ?>
                                            <div class="beforeafterimage">
                                                <h4><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN']; ?></h4>
                                                <b><a href="<?=$db_trip_data['vAfterImage']?>" target="_blank"><img src="<?=$db_trip_data['vAfterImage']?>" style="width:200px;" alt="After Images"/></a></b>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="left-right">
                        <div class="inv-destination-data">
                            <div>
                                <ul>
                                    <?php
                                    $systemTimeZone = date_default_timezone_get();
                                    if ($db_trip_data['fCancellationFare'] > 0 && $db_trip_data['vTimeZone'] != ""){
                                        $dBookingDate = $endDate = converToTz($db_trip_data['tEndDateOrig'],$db_trip_data['vTimeZone'],$systemTimeZone);
                                    }else if ($db_trip_data['tTripRequestDateOrig'] != "" && $db_trip_data['vTimeZone'] != ""){
                                        $dBookingDate = converToTz($db_trip_data['tTripRequestDateOrig'],$db_trip_data['vTimeZone'],$systemTimeZone);
                                        $endDate = converToTz($db_trip_data['tEndDateOrig'],$db_trip_data['vTimeZone'],$systemTimeZone);
                                    }else{
                                        $dBookingDate = $db_trip_data['tTripRequestDateOrig'];
                                        $endDate = $db_trip_data['tEndDateOrig'];
                                    }
                                    // New Date Format
                                    $date_format_data_array = array('langCode'         => $default_lang,
                                                                    'DateFormatForWeb' => 1);
                                    $date_format_data_array['tdate'] = $dBookingDate;
                                    $get_invoice_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                    $date_format_data_array['tdate'] = $endDate;
                                    $get_end_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                    ?>
                                    <?php if ($_SESSION['sess_user'] != "driver"){ ?>
                                        <li>
                                            <i class="fa fa-user"></i>
                                            <div class="destination_data_right">
                                                <strong><?=$langage_lbl['LBL_DRIVER_NAME'];?>:</strong>
                                                <span><?=clearName($db_trip_data['DriverDetails']['vName'].' '.$db_trip_data['DriverDetails']['vLastName']);?></span>
                                            </div>
                                        </li>
                                    <?php } ?>
                                    <?php if ($_SESSION['sess_user'] == "driver"){ ?>
                                        <li>
                                            <i class="fa fa-user"></i>
                                            <div class="destination_data_right">
                                                <strong><?=$langage_lbl['LBL_USER_NAME_LBL_TXT'];?>:</strong>
                                                <span><?=clearName($db_trip_data['PassengerDetails']['vName'].' '.$db_trip_data['PassengerDetails']['vLastName']);?></span>
                                            </div>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <i class="fa fa-calendar"></i>
                                        <div class="destination_data_right">
                                            <strong><?=$langage_lbl['LBL_TRIP_DATE_TXT'];?>:</strong>
                                            <?php if ($db_trip_data['eType'] != 'UberX'){ ?>
                                                <span><?=$get_invoice_date_format['tDisplayDate'];?></span>
                                            <?php }else{ ?>
                                                <span><?=$get_invoice_date_format['tDisplayDateTime'];//@date('d M Y h:i A', @strtotime($dBookingDate)); ?></span>
                                            <?php } ?>
                                        </div>
                                    </li>
                                    <?php if ($MODULES_OBJ->isEnableVideoConsultingService() && $db_trip_data['isVideoCall'] == 'No'){ ?>
                                        <li>
                                            <i class="fa fa-map-marker"></i>
                                            <div class="destination_data_right">
                                                <strong><?=$langage_lbl['LBL_PICKUP_LOCATION_TXT'];?>:</strong>
                                                <span><?=$db_trip_data['tSaddress'];?></span>
                                            </div>
                                        </li>
                                    <?php } ?>
                                    <?php if ($db_trip_data['eType'] != 'UberX'){ ?>
                                        <li>
                                            <i class="fa fa-clock-o"></i>
                                            <div class="destination_data_right">
                                                <strong><?=$langage_lbl['LBL_PICKUP_TIME'];?>:</strong>
                                                <span><?=$get_invoice_date_format['tDisplayTimeAbbr'];//@date('d M Y h:i A', @strtotime($dBookingDate)); ?></span>
                                            </div>
                                        </li>
                                        <?php if ($MODULES_OBJ->isInterCityFeatureAvailable() && $db_trip_data['eType'] == 'Ride' && $db_trip_data['eIsInterCity'] == "Yes" && $db_trip_data['eRoundTrip'] == "Yes" && $db_trip_data['tReachedDateTime'] != ""){
                                            $date_format_data_array['tdate'] = converToTz($db_trip_data['tReachedDateTime'],$db_trip_data['vTimeZone'],$systemTimeZone);
                                            $get_return_data_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            ?>
                                            <li>
                                                <i class="fa fa-map-marker"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_INTERCITY_RETURN_LOCATION'];?>:</strong>
                                                    <span><?=$db_trip_data['tReturnAddress'];?></span>
                                                </div>
                                            </li>
                                            <li>
                                                <i class="fa fa-clock-o"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_INTERCITY_RETURN'];?> :</strong>
                                                    <span><?=$get_return_data_format['tDisplayDateTime'];//@date('d M Y h:i A', @strtotime(converToTz($db_trip_data['tReachedDateTime'], $db_trip_data['vTimeZone'], $systemTimeZone))); ?></span>
                                                </div>
                                            </li>
                                            <li>
                                                <i class="fa fa-map-marker"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_DROPOFF_LOCATION'];?>:</strong>
                                                    <span><?=$db_trip_data['tDaddress'];?></span>
                                                </div>
                                            </li>
                                            <li>
                                                <i class="fa fa-clock-o"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_DROPOFF_TIME'];?>:</strong>
                                                    <span><?=$get_end_date_format['tDisplayTimeAbbr'];//@date('d M Y h:i A', @strtotime($endDate)); ?></span>
                                                </div>
                                            </li>
                                        <?php }else{ ?>
                                            <li>
                                                <i class="fa fa-map-marker"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_DROPOFF_LOCATION'];?>:</strong>
                                                    <span><?=$db_trip_data['tDaddress'];?></span>
                                                </div>
                                            </li>
                                            <li>
                                                <i class="fa fa-clock-o"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_DROPOFF_TIME'];?>:</strong>
                                                    <span><?=$get_end_date_format['tDisplayTimeAbbr'];//@date('d M Y h:i A', @strtotime($endDate)); ?></span>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    <?php } ?>

                                    <? $orgReason = "";
                                    if ($db_trip_data['eTripReason'] == 'Yes' && $db_trip_data['iTripReasonId'] > 0 && ($_SESSION['sess_user'] == "rider" || $_SESSION['sess_user'] == "organization")){
                                        $tripreason = "SELECT if(vReasonTitle != '',JSON_UNQUOTE(JSON_VALUE(`vReasonTitle`, '$.vReasonTitle_EN')),'') AS vReasonTitle FROM `trip_reason` where iTripReasonId = '".$db_trip_data['iTripReasonId']."'";
                                        $tripreasonData = $obj->MySQLSelect($tripreason);
                                        if (!empty($tripreasonData[0]['vReasonTitle'])){
                                            $orgReason = $tripreasonData[0]['vReasonTitle'];
                                        }
                                    }
                                    if (($_SESSION['sess_user'] == "driver" || $_SESSION['sess_user'] == "rider" || $_SESSION['sess_user'] == "organization") && ($db_trip_data['eTripReason'] == 'Yes' && $db_trip_data['vReasonTitle'] != '')){
                                        $orgReason = $db_trip_data['vReasonTitle'];
                                    }
                                    if (!empty($orgReason)){ ?>
                                        <li>
                                            <i class="fa fa-list-alt"></i>
                                            <div class="destination_data_right">
                                                <strong><?=$langage_lbl['LBL_ORGANIZATION_TRIP_REASON'];?>:</strong>
                                                <span><?=$orgReason;?></span>
                                            </div>
                                        </li>
                                    <? }
                                    if ($db_trip_data['eType'] == 'UberX'){
                                        if (!empty($printCategory) && !empty($db_trip_data['tUserCommentNew'])){ ?>
                                            <li>
                                                <i class="fa fa-list-alt"></i>
                                                <div class="destination_data_right">
                                                    <strong><?=$langage_lbl['LBL_SPECIAL_INSTRUCTION_TXT'];?>:</strong>
                                                    <span><?=$db_trip_data['tUserCommentNew'];?></span>
                                                </div>
                                            </li>
                                        <?php }
                                    } ?>
                                </ul>
                                <?php if ($db_trip_data['eType'] == 'Deliver'){ ?>
                                    <div class="extra-details">
                                        <h3><?=$langage_lbl['LBL_DELIVERY_DETAILS'];?></h3>
                                        <ul class="no-icons">
                                            <li>
                                                <strong><?=$langage_lbl['LBL_RECEIVER_NAME'];?> </strong><b><?=$db_trip_data['vReceiverName'];?></b>
                                            </li>
                                            <li>
                                                <strong><?=$langage_lbl['LBL_RECEIVER_MOBILE'];?> </strong><b><?=$db_trip_data['vReceiverMobile'];?></b>
                                            </li>
                                            <li>
                                                <strong><?=$langage_lbl['LBL_PICK_UP_INS'];?> </strong><b><?=$db_trip_data['tPickUpIns'];?></b>
                                            </li>
                                            <li>
                                                <strong><?=$langage_lbl['LBL_DELIVERY_INS'];?> </strong><b><?=$db_trip_data['tDeliveryIns'];?></b>
                                            </li>
                                            <li>
                                                <strong><?=$langage_lbl['LBL_PACKAGE_DETAILS'];?></strong><b><?=$db_trip_data['tPackageDetails'];?></b>
                                            </li>
                                            <li>
                                                <strong><?=$langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT'];?> </strong><b><?=$db_trip_data['vDeliveryConfirmCode'];?></b>
                                            </li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                            <? if ($APP_DELIVERY_MODE == "Multi" && $db_trip_data['eType'] == 'Deliver'){ ?>
                                <div class="invoice-part-bottom invoice-part-bottom1">
                                    <?php
                                    $sql1 = "SELECT * FROM trips_delivery_locations AS tdl WHERE iTripId = '".$iTripId."'";
                                    $db_trips_locations = $obj->MySQLSelect($sql1);
                                    $i = 1;
                                    if (!empty($db_trips_locations)){
                                        foreach ($db_trips_locations as $dtls){
                                            $class = (!empty($dtls['vSignImage']))?'sign-img':'';
                                            ?>
                                            <div class="col-sm-6 <?php echo $class; ?>">
                                                <h3><?=$langage_lbl['LBL_RECIPIENT_LIST_TXT'].'&nbsp;'.$i;?></h3>
                                                <table style="width:100%" class="deliverytable" cellpadding="5" cellspacing="0" border="0">
                                                    <tr>
                                                        <td style="min-width: 150px;">
                                                            <b><?=$langage_lbl['LBL_RECIPIENT_NAME_HEADER_TXT'];?> </b>
                                                        </td>
                                                        <td><?=$dtls['vReceiverName'];?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="min-width: 150px;">
                                                            <b><?=$langage_lbl['LBL_DROP_OFF_LOCATION_RIDE_DETAIL'];?> </b>
                                                        </td>
                                                        <td><?=$dtls['tPickUpIns'].",".$dtls['tDaddress'];?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="min-width: 150px;">
                                                            <b><?=$langage_lbl['LBL_DELIVERY_INS'];?></b></td>
                                                        <td><?=$dtls['tDeliveryIns'];?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="min-width: 150px;">
                                                            <b><?=$langage_lbl['LBL_PACKAGE_DETAILS'];?></b></td>
                                                        <td><?=$dtls['tPackageDetails'];?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="min-width: 150px;">
                                                            <b><?=$langage_lbl['LBL_DELIVERY_STATUS_TXT'];?></b></td>
                                                        <td><?=$dtls['iActive'];?></td>
                                                    </tr>
                                                    <?php if (!empty($dtls['vSignImage'])){ ?>
                                                        <tr>
                                                            <td class="label_left">
                                                                <b><?=$langage_lbl['LBL_RECEIVER_SIGN'];?></b></td>
                                                            <td class="detail_right">
                                                                <?php
                                                                if (file_exists($tconfig["tsite_upload_trip_signature_images_path"].'/'.$dtls['vSignImage'])){
                                                                    $img1 = $tconfig["tsite_upload_trip_signature_images"].'/'.$dtls['vSignImage'];
                                                                }
                                                                ?>
                                                                <img src="<?php echo $img1; ?>" align="left" style="width:150px;">
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                            </div>
                                            <?php
                                            $i++;
                                        }
                                    }
                                    ?>
                                </div>
                            <? } ?>
                            <div class="invoice-pay-type">
                                <strong><?=$langage_lbl['LBL_PAYMENT_TYPE_CAPS'];?> :</strong>
                                <strong><?php
                                    if (strtoupper($db_trip_data['ePayWallet']) == 'YES' || $db_trip_data['fWalletDebit'] > 0){
                                        if (strtoupper($db_trip_data['vTripPaymentMode']) == "CARD"){
                                            //$paymentMode = ucwords($langage_lbl['LBL_PAY_BY_CARD_TXT']) . "-" . ucwords($langage_lbl['LBL_HEADER_RDU_WALLET']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
                                            $paymentMode = ucwords($langage_lbl['LBL_CARD_CAPS']);
                                            if (strtoupper($db_trip_data['vTripPaymentMode']) == 'CARD' && ($SYSTEM_PAYMENT_FLOW == 'Method-2' || $SYSTEM_PAYMENT_FLOW == 'Method-3')){
                                                $paymentMode = ucwords($langage_lbl['LBL_HEADER_RDU_WALLET']);
                                            }
                                        }else if (strtoupper($db_trip_data['vTripPaymentMode']) == "CASH"){
                                            //$paymentMode = ucwords($langage_lbl['LBL_PAY_BY_CASH_TXT']) . "-" . ucwords($langage_lbl['LBL_HEADER_RDU_WALLET']); // Commented BY HJ On 24-02-2020 As Per Discuss with KS Sir
                                            $paymentMode = ucwords($langage_lbl['LBL_CASH_CAPS']);
                                        }else{
                                            $paymentMode = ucwords($langage_lbl['LBL_HEADER_RDU_WALLET']);
                                        }
                                    }else{
                                        if (strtoupper($db_trip_data['vTripPaymentMode']) == 'CASH'){
                                            $paymentMode = ucwords($langage_lbl['LBL_CASH_CAPS']);
                                        }else if (strtoupper($db_trip_data['vTripPaymentMode']) == 'CARD'){
                                            $paymentMode = ucwords($langage_lbl['LBL_CARD_CAPS']);
                                        }else if (strtoupper($db_trip_data['vTripPaymentMode']) == 'ORGANIZATION'){
                                            $paymentMode = ucwords($langage_lbl['LBL_ORGANIZATION_CAPS']);
                                        }
                                    }
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
<?php if ($lang != 'en'){ ?>
    <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?=$lang;?>.js" ></script> -->
    <? include_once('otherlang_validation.php'); ?>
<?php } ?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<!-- home page end-->
<div class="custom-modal-main" id="service_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="custom-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="upload-content">
                    <div class="model-header">
                        <h4 id="servicetitle">
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                            Service Details
                            <!-- <button type="button" class="close" data-dismiss="modal">x</button> -->
                        </h4>
                        <i class="icon-close" data-dismiss="modal"></i>
                    </div>
                    <div class="model-body" style="max-height: 450px;overflow: auto;">
                        <div id="service_detail"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Footer Script -->
<?php //include_once('top/footer_script.php');
?>
<script src="https://maps.google.com/maps/api/js?key=<?=$GOOGLE_SEVER_API_KEY_WEB?>&libraries=places" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script type="text/javascript" src="<?=$siteUrl;?>assets/js/jquery_easing.js"></script>
<script type="text/javascript" src="<?=$siteUrl;?>assets/js/markerAnimate.js" defer></script>
<script type="text/javascript">
    var typeArr = '<?= json_encode($vehilceTypeArr,JSON_HEX_APOS); ?>';
    h = window.innerHeight;
    $("#page_height").css('min-height', Math.round(h - 99) + 'px');
    var arr1 = [];
    var lats = [];
    var longs = [];
    var markers = [];
    var map;

    function initialize() {
        GOOGLE_MAP_OBJ.options.center = new google.maps.LatLng(parseFloat('<?= $db_trip_data['tStartLat']; ?>'), parseFloat('<?= $db_trip_data['tStartLong']; ?>'));
        map = GOOGLE_MAP_OBJ.init('map-canvas');
        from_to_polyline();
    }

    var tPlatitudes = '<?= json_encode($latitudes) ?>';
    lats = JSON.parse(tPlatitudes);
    var tPlongitudes = '<?= json_encode($longitudes) ?>';
    longs = JSON.parse(tPlongitudes);
    var multi_loc = '<?= $multi_location; ?>';
    var pts = [];
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < lats.length; i++) {
        if ((lats[i] != "") && (longs[i] != "")) {
            var latlongs = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
            pts.push(latlongs);
            var point = latlongs;
            bounds.extend(point);
            if (i == 0) {
                var start = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
            } else if (i == lats.length - 1) {
                var end = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
            } else {
                if (multi_loc == 1) {
                    var middle = new google.maps.LatLng(parseFloat(lats[i]), parseFloat(longs[i]));
                }
            }
        }
    }
    var directionsService = new google.maps.DirectionsService();
    var directionsOptions = { // For Polyline Route line options on map
        polylineOptions: {
            path: pts,
            strokeColor: '#f35e2f',
            strokeOpacity: 3.0,
            strokeWeight: 6
        }
    };
    var directionsDisplay = new google.maps.DirectionsRenderer(directionsOptions);

    function from_to() {
        var request = {
            origin: start, // From locations latlongs
            destination: end, // To locations latlongs
            travelMode: google.maps.TravelMode.DRIVING // Set the Path of Driving
        };
        directionsService.route(request, function (response, status) {
            directionsDisplay.setMap(map);
            directionsDisplay.setDirections(response);
        });
    }
    <?php if (!empty($iTripId) || !empty($db_trip_data)) { ?>
    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);
    });
    <?php } ?>

    function from_to_polyline() {
        DeleteMarkers('from_loc');
        DeleteMarkers('to_loc');
        setMarker(start, 'from_loc');
        setMarker(end, 'to_loc');
        var flightPath = '';
        var flightPath = new google.maps.Polyline({
            path: pts,
            geodesic: true,
            strokeColor: '#f35e2f',
            strokeOpacity: 1.0,
            strokeWeight: 4
        });
        map.fitBounds(bounds);
        flightPath.setMap(map);
    }

    function setMarker(postitions, valIcon) {
        var newIcon;
        if (valIcon == 'from_loc') {
            newIcon = 'webimages/upload/mapmarker/PinFrom.png';
        } else if (valIcon == 'to_loc') {
            newIcon = 'webimages/upload/mapmarker/PinTo.png';
        } else {
            newIcon = 'webimages/upload/mapmarker/PinTo.png';
        }
        marker = new google.maps.Marker({
            map: map,
            animation: google.maps.Animation.DROP,
            position: postitions,
            icon: newIcon
        });
        marker.id = valIcon;
        markers.push(marker);
    }

    function DeleteMarkers(newId) {
        for (var i = 0; i < markers.length; i++) {
            if (newId != '') {
                if (markers[i].id == newId) {
                    markers[i].setMap(null);
                }
            } else {
                markers[i].setMap(null);
                markers = [];
            }
        }
    }

    function showServiceModal(elem) {
        // var tripJson = JSON.parse($(elem).attr("data-json"));
        var tripJson = JSON.parse($(elem).next("textarea").val().replace(/\s\s+/g, ' '));
        var tripJsonVehicleTypeData = JSON.parse($(elem).attr("data-jsonVehicleTypeData"));
        var rideNo = $(elem).attr("data-trip");
        var typeNameArr = JSON.parse(typeArr)
        var serviceHtml = "";
        var srno = 1;
        $.each(tripJson, function (item, value) {
            //serviceHtml += "<p>" + srno + ") " + typeNameArr[value['iVehicleTypeId']] + "&nbsp;&nbsp;&nbsp;&nbsp;  <?= $langage_lbl['LBL_QTY_TXT'] ?>: <b>" + [value['fVehicleTypeQty']] + "</b></p>";
            serviceHtml += "<p>" + srno + ") " + typeNameArr[value['id']] + "&nbsp;&nbsp;&nbsp;&nbsp;  ";
            if (value['eAllowQty'] == 'Yes') {
                serviceHtml += "<?= $langage_lbl['LBL_QTY_TXT'] ?>: <b>" + [value['qty']] + "</b>";
            }
            serviceHtml += "</p>";
            if (tripJsonVehicleTypeData[item]['tUserComment'] != "" && tripJsonVehicleTypeData[item]['tUserComment'] != "undefined") {
                serviceHtml += "<p><?= $langage_lbl['LBL_SPECIAL_INSTRUCTION_TXT'] ?> : " + tripJsonVehicleTypeData[item]['tUserComment'] + "</p>";
            }
            srno++;
        });
        $("#service_detail").html(serviceHtml);
        $("#servicetitle").text("Service Details : " + rideNo);
        //$("#service_modal").modal('show');
        $("#service_modal").addClass('active');
        return false;
    }
</script>
<!-- End: Footer Script -->
</body>

</html>