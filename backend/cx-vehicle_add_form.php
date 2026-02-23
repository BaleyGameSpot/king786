<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$TRACKING_COMPANY = 0;
if ($_SESSION['sess_user'] == 'tracking_company') {
    $TRACKING_COMPANY = 1;
}
$abc = 'driver,company';
$TRACKING_COMPANY_STYLE_DISPLAY = '';
if ($TRACKING_COMPANY == 1) {
    $abc = 'tracking_company';
    $TRACKING_COMPANY_STYLE_DISPLAY = 'style="Display:none"';
    $iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
    $langage_lbl['LBL_CHOOSE_DRIVER'] = $langage_lbl['LBL_SELECT_TXT'] . ' ' . $langage_lbl['LBL_DRIVER_TXT'];
}
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$fly = 'No';
if ($MODULES_OBJ->isAirFlightModuleAvailable()) {
    $fly = 'Yes';
}
/*$abc = 'admin,driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);*/
$start = @date("Y");
$end = '1970';
$script = "Vehicle";
$tbl_name = 'driver_vehicle';
if (isset($_REQUEST['id'])) {
    $_REQUEST['id'] = base64_decode(base64_decode(trim($_REQUEST['id'])));
}
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$vehicleStore = isset($_REQUEST['vehicle']) ? $_REQUEST['vehicle'] : '';
$driverIdVehicle = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
if ($driverIdVehicle == "") {
    $driverIdVehicle = isset($_REQUEST['driverid']) ? $_REQUEST['driverid'] : '';
}
$action = ($id != '') ? 'Edit' : 'Add';
$action_show = ($id != '') ? $langage_lbl['LBL_VEHICLE_EDIT'] : $langage_lbl['LBL_VEHICLE_ACTION_ADD'];
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$vehicleDriverId = $_SESSION['sess_iUserId'];
$storeDriver = 0;
if (strtoupper($vehicleStore) == "STORE" && $driverIdVehicle > 0) {
    //echo $driverIdVehicle;die;  
    $vehicleDriverId = $driverIdVehicle;
    $storeDriver = 1;
}
if ($_SESSION['sess_user'] == 'driver') {
    $sql = "SELECT c.eSystem FROM `register_driver` as rd LEFT JOIN company as c ON rd.iCompanyId = c.iCompanyId WHERE rd.iDriverId = $vehicleDriverId";
    $db_storedriver = $obj->MySQLSelect($sql);
    if ($db_storedriver[0]['eSystem'] == 'DeliverAll') {
        $storeDriver = 1;
    }
}
if ($_SESSION['sess_user'] == 'driver' || $storeDriver > 0) {
    $sql = "SELECT rd.iCompanyId,c.iCountryId FROM `register_driver` AS rd LEFT JOIN country AS c ON c.vCountryCode=rd.vCountry WHERE rd.iDriverId = '" . $vehicleDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    $iCountryId = $db_usr[0]['iCountryId'];
    $iDriverId = $vehicleDriverId;
    if ($storeDriver > 0) {
        $sql = "SELECT * FROM register_driver WHERE iCompanyId = '" . $iCompanyId . "' AND iDriverId='" . $iDriverId . "' AND eStatus !='Deleted' order by vName ASC";
        $db_drvr = $obj->MySQLSelect($sql);
    }
}
if ($_SESSION['sess_user'] == 'company' && $storeDriver == 0) {
    $iCompanyId = $_SESSION['sess_iCompanyId'];
    $sql = "SELECT cn.iCountryId FROM `company` AS c LEFT JOIN country AS cn ON cn.vCountryCode=c.vCountry WHERE c.iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCountryId = $db_usr[0]['iCountryId'];
    $iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
    $sql = "SELECT * FROM register_driver WHERE iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "' AND eStatus !='Deleted' order by vName ASC";
    $db_drvr = $obj->MySQLSelect($sql);
}
if ($TRACKING_COMPANY == 1) {
    $iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
    $sql = "SELECT * FROM register_driver WHERE iTrackServiceCompanyId = '" . $iTrackServiceCompanyId . "' AND eStatus !='Deleted' order by vName ASC";
    $db_drvr = $obj->MySQLSelect($sql);
}
$sql = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId = '" . $id . "' ";
$db_mdl = $obj->MySQLSelect($sql);
// set all variables with either post (when submit) either blank (when insert)
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : (isset($_REQUEST['vLicencePlate']) ? $_REQUEST['vLicencePlate'] : "");
$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : (isset($_REQUEST['iMakeId']) ? $_REQUEST['iMakeId'] : "");
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : (isset($_REQUEST['iModelId']) ? $_REQUEST['iModelId'] : "");
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : (isset($_REQUEST['iYear']) ? $_REQUEST['iYear'] : "");
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eHandiCapAccessibility_check = isset($_POST['eHandiCapAccessibility']) ? $_POST['eHandiCapAccessibility'] : 'off';
$eChildSeatAvailable_check = isset($_POST['eChildSeatAvailable']) ? $_POST['eChildSeatAvailable'] : 'off';
$eWheelChairAvailable_check = isset($_POST['eWheelChairAvailable']) ? $_POST['eWheelChairAvailable'] : 'off';
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$vRentalCarType = isset($_POST['vRentalCarType']) ? $_POST['vRentalCarType'] : '';
$vColour = isset($_POST['vColour']) ? $_POST['vColour'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eHandiCapAccessibility = ($eHandiCapAccessibility_check == 'on') ? 'Yes' : 'No';
$eChildSeatAvailable = ($eChildSeatAvailable_check == 'on') ? 'Yes' : 'No';
$eWheelChairAvailable = ($eWheelChairAvailable_check == 'on') ? 'Yes' : 'No';
$eType = isset($_POST['eType']) ? $_POST['eType'] : 'Ride';
//$sql = "SELECT * FROM make WHERE eStatus='Active' ORDER BY vMake ASC";
$sql = "SELECT ma.* FROM make AS ma JOIN model as mo ON ma.iMakeId=mo.iMakeId WHERE ma.eStatus='Active' AND mo.eStatus='Active' GROUP BY ma.iMakeId ORDER By ma.vMake ASC";
$db_make = $obj->MySQLSelect($sql);
$storeDriverUrl = $storeUrlDriver = "";
if ($storeDriver > 0) {
    $storeDriverUrl = "&driverid=" . $iDriverId . "&vehicle=store";
    $storeUrlDriver = "?driverid=" . $iDriverId . "&vehicle=store";
}
if (isset($_POST['form_submit']) && $_POST['form_submit'] != "") {
    //echo "<pre>";print_r($iDriverId);die;
    if ($ENABLE_EDIT_DRIVER_VEHICLE == "No" && $action == 'Edit') {
        $error_msg = $langage_lbl['LBL_EDIT_VEHICLE_DISABLED'];
        $_SESSION['message_code'] = 0;
        $_SESSION['var_msg'] = $error_msg;
        header("Location:cx-vehicle.php?success=0&var_msg=" . $error_msg . $storeDriverUrl);
        exit;
    }
    if (!isset($_REQUEST['vCarType']) && $TRACKING_COMPANY == 0) {
        $error_msg = $langage_lbl['LBL_SELECT_CAR_TYPE'];
        $_SESSION['message_code'] = 2;
        $_SESSION['var_msg'] = $error_msg;
        header("Location:cx-vehicle_add_form.php?id=" . base64_encode(base64_encode($id)) . $storeDriverUrl . "&error_msg=" . $error_msg . "&success=2&iMakeId=" . $iMakeId . "&iModelId=" . $iModelId . "&iYear=" . $iYear . "&vLicencePlate=" . $vLicencePlate);
        exit;
    }
    if ($APP_TYPE == 'UberX') {
        $vLicencePlate = 'My Services';
    } else {
        $vLicencePlate = $vLicencePlate;
    }
    $dsql = "";
    if ($id != '') {
        $dsql = " and iDriverVehicleId != '$id'";
    }
    $deliverAllArr = $eAddedDeliverVehicleArr = array();
    if (isset($_POST['deliverall']) && $_POST['deliverall'] != "") {
        $deliverAllArr = explode(",", $_POST['deliverall']);
        for ($f = 0; $f < scount($deliverAllArr); $f++) {
            if (in_array($deliverAllArr[$f], $_REQUEST['vCarType'])) {
                $eAddedDeliverVehicleArr[] = 1;
            }
        }
    }
    $eAddedDeliverVehicle = "No";
    if (in_array(1, $eAddedDeliverVehicleArr)) {
        $eAddedDeliverVehicle = "Yes";
    }
    $TRACKING_COMPANY_SQL = '';
    if ($TRACKING_COMPANY == 1) {
        $TRACKING_COMPANY_SQL = "`iTrackServiceCompanyId` = '" . $iTrackServiceCompanyId . "',";
        $eType = 'TrackService';
    }
    //echo $eAddedDeliverVehicle;die;
    //Added By Hasmukh On 30-10-2018 For Check eAddedDeliverVehicle Value End
    $q = "INSERT INTO ";
    $where = '';
    if ($action == 'Edit') {
        $str = ' ';
    } else {
        if (SITE_TYPE == 'Demo') $str = ", eStatus = 'Active' "; else
            $str = ", eStatus = 'Inactive' ";
    }
    $cartype = implode(",", $_REQUEST['vCarType']);
    $vRentalCarType = '';
    if (!empty($_REQUEST['vRentalCarType'])) {
        $vRentalCarType = implode(",", $_REQUEST['vRentalCarType']);
    }

    if(!empty($cartype) && $MODULES_OBJ->isInterCityFeatureAvailable()){
        $sql = "SELECT iVehicleTypeId,eInterCityStatus FROM `vehicle_type` WHERE iVehicleTypeId IN ($cartype) AND eInterCityStatus = 'Yes'";
        $VehiclesArr = $obj->MySQLSelect($sql);
        $IntercityArr = array();
        foreach($VehiclesArr as $Intercityk=>$Intercityval){
            $IntercityArr[] = $Intercityval['iVehicleTypeId'];
        }
        $IntercityRentalCarType = implode(",",$IntercityArr);
        
        if (empty($vRentalCarType)) {
            $vRentalCarType .= $IntercityRentalCarType;
        } else if (strpos($vRentalCarType, $IntercityRentalCarType) === false ){
           $vRentalCarType .= "," .$IntercityRentalCarType;
        }
    }

    $rental_query = " `vRentalCarType` = '" . $vRentalCarType . "', ";
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDriverVehicleId` = '" . $id . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET
		`iModelId` = '" . $iModelId . "',
		`vLicencePlate` = '" . $vLicencePlate . "',
		$TRACKING_COMPANY_SQL
		`iYear` = '" . $iYear . "',
		`iMakeId` = '" . $iMakeId . "',
		`iCompanyId` = '" . $iCompanyId . "',
		`iDriverId` = '" . $iDriverId . "',
		`vColour` = '" . $vColour . "',
		`eType`  = '" . $eType . "',
        `eAddedDeliverVehicle` = '" . $eAddedDeliverVehicle . "',
		`eHandiCapAccessibility` = '" . $eHandiCapAccessibility . "',
        `eChildSeatAvailable` = '" . $eChildSeatAvailable . "',
		`eWheelChairAvailable` = '" . $eWheelChairAvailable . "',
		$rental_query
		`vCarType` = '" . $cartype . "' $str" . $where;
    // echo $query;die;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();
    if ($action == "Add") {
        $sql = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $db_compny = $obj->MySQLSelect($sql);
        $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
        $db_status = $obj->MySQLSelect($sql);

        $maildata['EMAIL'] = ' - ';
        if(isset($db_status[0]['vEmail']) && !empty($db_status[0]['vEmail']))
        {
            $maildata['EMAIL'] = $db_status[0]['vEmail'];
        }

        $maildata['NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
        $maildata['DETAIL'] = "Thanks for adding your vehicle.<br />We will soon verify and check it's documentation and proceed ahead with activating your account.<br />We will notify you once your account become active and you can then take rides with passengers.";
        //$COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING",$maildata);
        //$maildata['DETAIL']="Vehicle is Added For ".$db_compny[0]['vCompany']." . Below is link to activate.<br>
        $sql1 = "SELECT mo.vTitle,m.vMake from make as m LEFT JOIN model as mo on mo.iMakeId = m.iMakeId where m.iMakeId = '" . $iMakeId . "'";
        $db_make_data = $obj->MySQLSelect($sql1);
        $maildata['MAKE'] = $db_make_data[0]['vMake'];
        $maildata['MODEL'] = $db_make_data[0]['vTitle'];
        $maildata['DETAIL'] = "You can active this Vehicle by clicking below link<br><p><a href='" . $tconfig["tsite_url_main_admin"] . "vehicle_add_form.php?id=$id'>Active this Vehicle</a></p>";
        $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING_ADMIN", $maildata);
        $var_msg = $langage_lbl['LBL_RECORD_INSERT_MSG'];
    } else {
        $var_msg = $langage_lbl['LBL_Record_Updated_successfully'];
    }

    $_SESSION['message_code'] = 1;
    $_SESSION['session_msg'] = $var_msg;

    header("Location:cx-vehicle.php?success=1&var_msg=" . $var_msg . $storeDriverUrl);
    exit;
    //}
}
// for Edit
if ($action == 'Edit') {
    if ($_SESSION['sess_user'] == 'driver' || $storeDriver > 0) {
        $ssql = "and iDriverId = '" . $vehicleDriverId . "'";
    } else {
        $ssql = "and iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
    }
    $sql = "SELECT * from  $tbl_name where iDriverVehicleId = '" . $id . "' " . $ssql . " ";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iMakeId = $value['iMakeId'];
            $iModelId = $value['iModelId'];
            $vLicencePlate = $value['vLicencePlate'];
            $iYear = $value['iYear'];
            $eCarX = $value['eCarX'];
            $eCarGo = $value['eCarGo'];
            $eType = $value['eType'];
            $iDriverId = $value['iDriverId'];
            $eHandiCapAccessibility = $value['eHandiCapAccessibility'];
            $eChildSeatAvailable = $value['eChildSeatAvailable'];
            $eWheelChairAvailable = $value['eWheelChairAvailable'];
            $vCarType = $value['vCarType'];
            $vRentalCarType = $value['vRentalCarType'];
            $vColour = $value['vColour'];
        }
    } else {
        header("location:cx-vehicle.php" . $storeDriverUrl);
    }
}
$vCarTyp = explode(",", $vCarType);
$vRentalCarTyp = explode(",", $vRentalCarType);
##################### new added ########################
$RideDeliveryIconArrStatus = CheckRideDeliveryFeatureDisableWeb();
$eShowRideVehicles = $RideDeliveryIconArrStatus['eShowRideVehicles'];
$eShowDeliveryVehicles = $RideDeliveryIconArrStatus['eShowDeliveryVehicles'];
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_Vehicle']; ?> <?= $action_show; ?></title>
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
    <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="assets/css/modal_alert.css"/>
    <style type="text/css">
        .delivery-helper-info ul {
            text-align: left;
            list-style-type: disc;
            padding: 0 30px;
        }

        .custom-admin-info-popup .popup-img {
            width: 150px;
        }


    </style>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <!-- home page -->
    <div id="main-uber-page" class="addVehicleCX">
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
                        <h1><?= $action_show; ?> <?= $langage_lbl['LBL_Vehicle']; ?></h1>
                    </div>
                    <div class="button-block end">
                        <?php if ($storeDriver > 0) { ?>
                            <a href="cx-vehicle.php?driverid=<?= $vehicleDriverId; ?>&action=edit&vehicle=store"
                               class="gen-btn"><?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?></a>
                        <?php } else if ($TRACKING_COMPANY == 1) { ?>
                            <a href="cx-vehicle.php" class="gen-btn"><?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?></a>
                        <?php } else { ?>
                            <a href="cx-vehicle.php"
                               class="gen-btn"><?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>
        <section class="profile-earning">
            <form method="post" action="" id="vehicle_form" name="vehicle_form">
                <input type="hidden" name="id" value="<?= base64_encode(base64_encode($id)); ?>"/>
                <?php if ($APP_TYPE != 'UberX') { ?>
                    <div class="profile-earning-inner">
                        <div class="general-form">
                            <div class="card-block">
                                <?php if ($success == 1) { ?>
                                    <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                        </button>
                                        <?= $langage_lbl['LBL_Record_Updated_successfully']; ?>
                                    </div>
                                <?php } else if ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×
                                        </button>
                                        <?= isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : ' '; ?>
                                    </div>
                                <?php } ?>
                                <h1><?= $langage_lbl['LBL_VEHICLE_DETAILS_TXT']; ?></h1>
                                <div class="partation">
                                    <div class="form-group half newrow">
                                        <strong><?= $langage_lbl['LBL_CHOOSE_MAKE']; ?>
                                            <span class="red">*</span>
                                        </strong>
                                        <select name="iMakeId" id="iMakeId" class="custom-select-new"
                                                data-key="<?= $langage_lbl['LBL_CHOOSE_MAKE']; ?>"
                                                onChange="get_model(this.value, <?php echo $iModelId; ?>)">
                                            <option value=""><?= $langage_lbl['LBL_CHOOSE_MAKE']; ?></option>
                                            <?php for ($j = 0; $j < scount($db_make); $j++) { ?>
                                                <option value="<?= $db_make[$j]['iMakeId'] ?>" <?php if ($iMakeId == $db_make[$j]['iMakeId']) { ?> selected <?php } ?>><?= $db_make[$j]['vMake'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span id="" style="color: Red; font-size: 11px;"></span>
                                    </div>
                                    <div class="form-group half newrow">
                                        <strong><?= $langage_lbl['LBL_CHOOSE_VEHICLE_MODEL']; ?>
                                            <span class="red">*</span>
                                        </strong>
                                        <select name="iModelId" id="iModelId"
                                                data-key="<?= $langage_lbl['LBL_CHOOSE_VEHICLE_MODEL']; ?>"
                                                class="custom-select-new validate[required]">
                                            <option value=""><?= $langage_lbl['LBL_CHOOSE_VEHICLE_MODEL']; ?> </option>
                                            <?php if (!empty($db_model)) {
                                                for ($j = 0; $j < scount($db_model); $j++) { ?>
                                                    <option value="<?= $db_model[$j]['iModelId'] ?>" <?php if ($iModelId == $db_model[$j]['iModelId']) { ?> selected <?php } ?>><?= $db_model[$j]['vModel'] ?></option>
                                                <?php }
                                            } ?>
                                        </select>
                                        <span id="" style="color: Red; font-size: 11px;"></span>
                                    </div>
                                    <div class="form-group half newrow">
                                        <strong><?= $langage_lbl['LBL_CHOOSE_YEAR']; ?>
                                            <span class="red">*</span>
                                        </strong>
                                        <select name="iYear" data-key="<?= $langage_lbl['LBL_CHOOSE_YEAR']; ?>"
                                                id="iYear" class="custom-select-new">
                                            <option value=""><?= $langage_lbl['LBL_CHOOSE_YEAR']; ?> </option>
                                            <?php for ($j = $start; $j >= $end; $j--) { ?>
                                                <option value="<?= $j ?>" <?php if ($iYear == $j) { ?> selected <?php } ?>><?= $j ?></option>
                                            <?php } ?>
                                        </select>
                                        <span id="" style="color: Red; font-size: 11px;"></span>
                                    </div>
                                    <div class="form-group half newrow">
                                        <strong>&nbsp;</strong>
                                        <div class="relation-parent">
                                            <label><?= $langage_lbl['LBL_LICENCE_PLATE_TXT']; ?>
                                                <span class="red">*</span>
                                            </label>
                                            <input type="text" class="form-control" name="vLicencePlate"
                                                   id="vLicencePlate" value="<?= $vLicencePlate; ?>">
                                            <!-- onBlur="check_licence_plate(this.value,'<?= $id ?>')"  -->
                                            <span id="plate_warning" class="error"></span>
                                            <span id="" style="color: Red; font-size: 11px;"></span>
                                        </div>
                                    </div>
                                    <?php if ($_SESSION['sess_user'] == 'company' || $TRACKING_COMPANY == 1) { ?>
                                        <div class="form-group half">
                                            <?php if (strtoupper($vehicleStore) == "STORE" && $driverIdVehicle > 0) { ?>
                                                <select name="iDriverId1" id="iDriverId1" class="custom-select-new"
                                                        disabled="disabled">
                                                    <?php if ($storeDriver == 0) { ?>
                                                        <option value=""><?= $langage_lbl['LBL_CHOOSE_DRIVER']; ?></option>
                                                    <?php } ?>
                                                    <?php for ($j = 0; $j < scount($db_drvr); $j++) { ?>
                                                        <option value="<?= $db_drvr[$j]['iDriverId'] ?>" <?php if ($db_drvr[$j]['iDriverId'] == $iDriverId) { ?> selected <?php } ?>><?= clearName($db_drvr[$j]['vName'] . ' ' . $db_drvr[$j]['vLastName']); ?></option>
                                                    <?php } ?>
                                                </select>
                                                <input type="hidden" name="iDriverId" id="iDriverId"
                                                       value="<?= $iDriverId; ?>">
                                            <?php } else { ?>
                                                <select name="iDriverId" id="iDriverId" class="custom-select-new"
                                                        required>
                                                    <?php if ($storeDriver == 0) { ?>
                                                        <option value=""><?= $langage_lbl['LBL_CHOOSE_DRIVER']; ?></option>
                                                    <?php } ?>
                                                    <?php for ($j = 0; $j < scount($db_drvr); $j++) { ?>
                                                        <option value="<?= $db_drvr[$j]['iDriverId'] ?>" <?php if ($db_drvr[$j]['iDriverId'] == $iDriverId) { ?> selected <?php } ?>><?= clearName($db_drvr[$j]['vName'] . ' ' . $db_drvr[$j]['vLastName']); ?></option>
                                                    <?php } ?>
                                                </select>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                    <div class="form-group half">
                                        <label><?= $langage_lbl['LBL_VEHICLE_TITLE'] . " " . $langage_lbl['LBL_COLOR_ADD_VEHICLES']; ?></label>
                                        <input type="text" class="form-control" name="vColour" id="vColour"
                                               value="<?= $vColour; ?>">
                                    </div>
                                </div>
                            </div>
                            <?php
                            if ($APP_TYPE != 'Delivery' && $storeDriver == 0 && ONLYDELIVERALL != 'Yes' && ((isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") || (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") || (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes"))) {
                                ?>
                                <div <?php echo $TRACKING_COMPANY_STYLE_DISPLAY; ?> class="card-block">
                                    <h1><?= $langage_lbl['LBL_SETTINGS']; ?></h1>
                                    <div class="partation">
                                        <ul class="setings-list">
                                            <?php if (isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") { ?>
                                                <li>
                                                    <div class="toggle-combo">
                                                        <label><?= $langage_lbl['LBL_HANDICAP_QUESTION_ADD_VEHICLES']; ?></label>
                                                        <span class="toggle-switch">
                                                            <!-- <div class="make-switch" data-on="success" data-off="warning" data-on-label='Yes' data-off-label='No'> -->
                                                            <input type="checkbox" name="eHandiCapAccessibility"
                                                                   id="eHandiCapAccessibility" <?= ($eHandiCapAccessibility == 'No') ? '' : 'checked'; ?> />
                                                            <span class="toggle-base"></span>
                                                            <!-- </div> -->
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php }
                                            if (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") { ?>
                                                <li>
                                                    <div class="toggle-combo">
                                                        <label><?= $langage_lbl['LBL_CHILD_SEAT_ADD_VEHICLES']; ?></label>
                                                        <span class="toggle-switch">
                                                            <!-- <div class="make-switch" data-on="success" data-off="warning" data-on-label='Yes' data-off-label='No'> -->
                                                            <input type="checkbox" name="eChildSeatAvailable"
                                                                   id="eChildSeatAvailable" <?= ($eChildSeatAvailable == 'No') ? '' : 'checked'; ?> />
                                                            <span class="toggle-base"></span>
                                                            <!-- </div> -->
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php }
                                            if (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes") { ?>
                                                <li>
                                                    <div class="toggle-combo">
                                                        <label><?= $langage_lbl['LBL_WHEEL_CHAIR_ADD_VEHICLES']; ?></label>
                                                        <span class="toggle-switch">
                                                            <!-- <div class="make-switch" data-on="success" data-off="warning" data-on-label='Yes' data-off-label='No'> -->
                                                            <input type="checkbox" name="eWheelChairAvailable"
                                                                   id="eWheelChairAvailable" <?= ($eWheelChairAvailable == 'No') ? '' : 'checked'; ?> />
                                                            <span class="toggle-base"></span>
                                                            <!-- </div> -->
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php } ?>
                            <div <?php echo $TRACKING_COMPANY_STYLE_DISPLAY; ?> class="card-block">
                                <!--<h1><?= $langage_lbl['LBL_Car_Type']; ?></h1>-->
                                <div id="vehicleTypes001"></div>
                            </div>
                            <div class="button-block justify-left">
                                <input type="submit" class="save-vehicle gen-btn" name="form_submit" id="form_submit"
                                       value="<?= $action_show; ?> <?= $langage_lbl['LBL_Vehicle']; ?>">
                                <a href="cx-vehicle.php<?= $storeUrlDriver; ?>"
                                   class="gen-btn"><?= $langage_lbl['LBL_CANCEL_TXT'] ?></a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </form>
        </section>

        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">
                <img src="default.gif">
            </div>
        </div>
        <!-- footer part -->
    </div>
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- -->
    <div class="clearfix"></div>
</div>


<!-- home page end-->
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="assets/js/modal_alert.js"></script>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script>
    $('#vehicle_form').validate({
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
            e.parent().find('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        rules: {
            iMakeId: {
                required: true,
            },
            iModelId: {
                required: true,
            },
            iYear: {
                required: true,
            },
            vLicencePlate: {
                required: true,
                notEmpty: true
            }
        },
        messages: {
            iMakeId:{
                required: 'This field is required.'
            },
            iModelId:{
                required: 'This field is required.'
            },
            iYear:{
                required: 'This field is required.'
            },
            vLicencePlate:{
                required: 'This field is required.',
                notEmpty: 'This field is required.'
            }
        },
        submitHandler: function (form) {

            if ($(form).valid()) {

                $("#loaderIcon").show();

                ShpSq6fAm7(form);
                form.submit();
            }
            return false;
        }
    });
    $.validator.addMethod("notEmpty", function(value, element) {
        return value.trim() !== '';
    }, "This field is required.");
</script>
<?php if ($action == 'Edit') { ?>
    <script>
        $(document).ready(function () {
            //window.onload = function () {
            //  get_model('<?php echo $db_mdl[0]['iMakeId']; ?>', '<?php echo $db_mdl[0]['iModelId']; ?>');
            get_vehicleType('<?= $iDriverId; ?>', '<?= $vCarType; ?>', '<?= $eType ?>', '<?= $vRentalCarType ?>');
        });
    </script><?php } else { ?>
    <script>
        //window.onload = function () {
        $(document).ready(function () {
            var appType = '<?= $APP_TYPE ?>';
            if (appType == 'Ride-Delivery-UberX' || appType == 'Ride-Delivery') {
                SelectedAppType = 'Ride';
            } else if (appType == 'Delivery') {
                SelectedAppType = 'Deliver';
            } else {
                SelectedAppType = appType;
            }
            get_vehicleType('<?= $iDriverId; ?>', '', SelectedAppType);
        });
    </script>
<?php } ?>
<?php if (!empty($error_msg)) { ?>
    <script>
        window.onload = function () {
            //   get_model('<?php echo $iMakeId; ?>', '<?php echo $iModelId; ?>');
        };
    </script>
<?php } ?>
<script>
    $('#etypedelivery').on('change', function () {
        get_vehicleType('<?= $iDriverId; ?>', '<?php echo $vCarType; ?>', this.value, '<?= $vRentalCarType ?>');
        if (this.value == 'Delivery') {
            $("#handicapaccess,#childseataccess,#wheelchairaccess").hide();
        } else {
            $("#handicapaccess,#childseataccess,#wheelchairaccess").show();
        }
    });
    <?php if ($_SESSION['sess_user'] == 'company') { ?>
    $('#iDriverId').on('change', function () {
        get_vehicleType(this.value, '<?php echo $vCarType; ?>', $("#etypedelivery").val(), '<?= $vRentalCarType ?>');
    });
    <?php
    } else {
    if ($APP_TYPE == 'Ride') {
    ?>
    get_vehicleType('<?= $iDriverId; ?>', '<?= $vCarType; ?>', '<?= $eType ?>', '<?= $vRentalCarType ?>');
    <?php
    }
    }
    ?>

    <?php if ($TRACKING_COMPANY != 1) { ?>
    function get_vehicleType(iDriverId, selected = '', eType = '', rentalselected = '') {
        if (eType == 'Delivery') {
            var eType = 'Deliver';
        } else {
            var eType = eType;
        }
        var fly = '<?php echo $fly; ?>';
        var storeDriver = '<?php echo $storeDriver; ?>';
        $("#vehicleTypes001").html('Wait...');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_cx_find_vehicleType.php',
            'AJAX_DATA': "iDriverId=" + iDriverId + "&selected=" + selected + "&eType=" + eType + "&Front=front&rentalselected=" + rentalselected + "&fly=" + fly + "&store=" + storeDriver,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#vehicleTypes001").html(data);
            } else {
                console.log(response.result);
            }
        });
    }
    <?php } ?>
    function get_model(model, modelid) {


        if (modelid != '' && model != '') {
            $("#loaderIcon").show();
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_find_model_new.php',
                'AJAX_DATA': "action=get_model&model=" + model + "&iModelId=" + modelid,
            };
            getDataFromAjaxCall(ajaxData, function (response) {

                if (response.action == "1") {


                    var data = response.result;
                    $("#iModelId").empty().append(data);
                    var selectedOption = $('#iModelId').find(":selected").text();
                    if (selectedOption != "") {
                        $('#iModelId').next(".holder").text(selectedOption);
                    }

                   // $("#loaderIcon").hide();

                    console.log('done');
                } else {
                    console.log(response.result);
                }

                $("#loaderIcon").hide();
            });
        }
    }
</script>
<!-- End: Footer Script -->
</body>
</html>
