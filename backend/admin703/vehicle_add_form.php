<?php
include_once('../common.php');
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$commonTxt = '';
if ($eType == 'Ride') {
    $commonTxt = 'taxi-service';
}
if ($eType == 'Deliver') {
    $commonTxt = 'parcel-delivery';
}
if ($eType == 'Ambulance') {
    $commonTxt = 'medical';
}
$view = "view-provider-vehicles-" . $commonTxt;
$create = "create-provider-vehicles-" . $commonTxt;
$edit = "edit-provider-vehicles-" . $commonTxt;
$delete = "delete-provider-vehicles-" . $commonTxt;
$updateStatus = "update-status-provider-vehicles-" . $commonTxt;
if (empty($eType)) {
    $view = ['view-provider-vehicles-taxi-service', 'view-provider-vehicles-parcel-delivery', 'view-provider-vehicles-medical', 'view-provider-vehicles'];
    $create = ['create-provider-vehicles-taxi-service', 'create-provider-vehicles-parcel-delivery', 'create-provider-vehicles-medical', 'create-provider-vehicles'];
    $edit = ['edit-provider-vehicles-taxi-service', 'edit-provider-vehicles-parcel-delivery', 'edit-provider-vehicles-medical', 'edit-provider-vehicles'];
    $delete = ['delete-provider-vehicles-taxi-service', 'delete-provider-vehicles-parcel-delivery', 'delete-provider-vehicles-medical', 'delete-provider-vehicles'];
    $updateStatus = ['update-status-provider-vehicles-taxi-service', 'update-status-provider-vehicles-parcel-delivery', 'update-status-provider-vehicles-medical', 'update-status-provider-vehicles'];
}
if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}
if ($MODULES_OBJ->isAirFlightModuleAvailable(1)) {
    $fly = 'Yes';
} else {
    $fly = 'No';
}
$start = @date("Y");
$end = '1970';
$tbl_name = 'driver_vehicle';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eQuery = '';
if (ONLYDELIVERALL == 'Yes') {
    $eQuery = " AND eSystem='DeliverAll'";
} else if (DELIVERALL == 'No' && $APP_TYPE == 'Ride-Delivery-UberX') {
    $eQuery = " AND eSystem!='DeliverAll'";
} else {
    $eQuery = "";
}
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$db_driver_detail_sql = "SELECT iDriverId,concat(vName,' ',vLastName) AS DriverName FROM register_driver WHERE eStatus!='Deleted' ORDER By iDriverId ASC";
$db_driver_detail = $obj->MySQLSelect($db_driver_detail_sql);
$sql = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId = '" . $id . "' ";
$db_mdl = $obj->MySQLSelect($sql);
$sql = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId = '" . $id . "' ";
$db_driver = $obj->MySQLSelect($sql);
// set all variables with either post (when submit) either blank (when insert)
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';
$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '';
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$eHandiCapAccessibility_check = isset($_POST['eHandiCapAccessibility']) ? $_POST['eHandiCapAccessibility'] : 'off';
$eChildSeatAvailable_check = isset($_POST['eChildSeatAvailable']) ? $_POST['eChildSeatAvailable'] : 'off';
$eWheelChairAvailable_check = isset($_POST['eWheelChairAvailable']) ? $_POST['eWheelChairAvailable'] : 'off';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
$vColour = isset($_POST['vColour']) ? $_POST['vColour'] : '';
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$vRentalCarType = isset($_POST['vRentalCarType']) ? $_POST['vRentalCarType'] : '';
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '0';
if ($iServiceId > 0) {
    $_POST['iCompanyId'] = isset($_POST['storeId']) ? $_POST['storeId'] : '1';
}
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eHandiCapAccessibility = ($eHandiCapAccessibility_check == 'on') ? 'Yes' : 'No';
$eChildSeatAvailable = ($eChildSeatAvailable_check == 'on') ? 'Yes' : 'No';
$eWheelChairAvailable = ($eWheelChairAvailable_check == 'on') ? 'Yes' : 'No';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$script = "Vehicle";
$queryString = '';
if (isset($eType) && !empty($eType)) {
    $script .= "_" . $eType;
    $queryString = 'eType=' . $eType;
}
//$sql = "SELECT * FROM make WHERE eStatus='Active' ORDER BY vMake ASC";
$sql = "SELECT ma.* FROM make AS ma JOIN model as mo ON ma.iMakeId=mo.iMakeId WHERE ma.eStatus='Active' AND mo.eStatus='Active' GROUP BY ma.iMakeId ORDER By ma.vMake ASC";
$db_make = $obj->MySQLSelect($sql);
$sql = "SELECT * from company WHERE eStatus='Active'  AND eSystem ='General'  ORDER By iCompanyId ASC";
$db_company = $obj->MySQLSelect($sql);
$defaultCompany = 1;
if (scount($db_company) > 0) {
    $defaultCompany = $db_company[0]['iCompanyId'];
}
if (trim($iCompanyId) == "") {
    $iCompanyId = $defaultCompany;
}
//echo $iCompanyId;die;
if (isset($_POST['submit'])) {
    //echo "<pre>";print_r($_POST);die;
    if ($action == "Add" && !$userObj->hasPermission($create)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin["LBL_TEXI_ADMIN"]);
        header("Location:vehicles.php?" . $queryString);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin["LBL_TEXI_ADMIN"]);
        header("Location:vehicles.php?" . $queryString);
        exit;
    }
    if (SITE_TYPE == 'Demo') { // Added By NModi on 10-12-20
        // if (SITE_TYPE == 'Demo' && $id != '') { // commneted by NModi on on 10-12-20
        $_SESSION['success'] = 2;
        header("Location:vehicles.php?id=" . $id . "&" . $queryString);
        exit;
    }
    //Added By Hasmukh On 30-10-2018 For Check eAddedDeliverVehicle Value Start
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
    //Added By Hasmukh On 30-10-2018 For Check eAddedDeliverVehicle Value End
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['iMakeId'], 'req', 'Make is required.');
    $validobj->add_fields($_POST['iModelId'], 'req', 'Model is required.');
    $validobj->add_fields($_POST['iYear'], 'req', 'Year is required.');
    $validobj->add_fields($_POST['vLicencePlate'], 'req', 'Licence plate Id is required.');
    if (ONLYDELIVERALL == 'No') {
        $validobj->add_fields($_POST['iCompanyId'], 'req', 'Company is required.');
    }
    $validobj->add_fields($_POST['iDriverId'], 'req', $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' is required.');
    if (empty($_REQUEST['vCarType'])) {
        $validobj->add_fields($_POST['vCarType'], 'req', 'You must select at least one car type!');
    }
    $error = $validobj->validate();
    if ($error) {
        /* $success = 3;
          $newError = $error; */
        $_SESSION['success'] = '3';
        $_SESSION['var_msg'] = $error;
        header("location:vehicle_add_form.php?" . $queryString);
        exit();
    } else {
        if ($APP_TYPE == 'UberX') {
            $vLicencePlate = 'My Services';
        } else {
            $vLicencePlate = $vLicencePlate;
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($action == 'Edit') {
            $str = ' ';
        } else {
            // $eStatus = 'Active'; // comment  issue to fix 212
        }
        $cartype = implode(",", $_REQUEST['vCarType']);
        $vRentalCarType = '';
        if (!empty($_REQUEST['vRentalCarType'])) {
            $vRentalCarType = implode(",", $_REQUEST['vRentalCarType']);
        }
        if (!empty($cartype) && $MODULES_OBJ->isInterCityFeatureAvailable()) {
            $sql = "SELECT iVehicleTypeId,eInterCityStatus FROM `vehicle_type` WHERE iVehicleTypeId IN ($cartype) AND eInterCityStatus = 'Yes'";
            $VehiclesArr = $obj->MySQLSelect($sql);
            $IntercityArr = array();
            foreach ($VehiclesArr as $Intercityk => $Intercityval) {
                $IntercityArr[] = $Intercityval['iVehicleTypeId'];
            }
            $IntercityRentalCarType = implode(",", $IntercityArr);
            if (empty($vRentalCarType)) {
                $vRentalCarType .= $IntercityRentalCarType;
            } else if (strpos($vRentalCarType, $IntercityRentalCarType) === false) {
                $vRentalCarType .= "," . $IntercityRentalCarType;
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
			`iYear` = '" . $iYear . "',
			`iMakeId` = '" . $iMakeId . "',
			`iCompanyId` = '" . $iCompanyId . "',
			`iDriverId` = '" . $iDriverId . "',
			`vColour` = '" . $vColour . "',
			`eStatus` = '" . $eStatus . "',
			`eType` = '" . $eType . "',
			`eAddedDeliverVehicle` = '" . $eAddedDeliverVehicle . "',
			`eHandiCapAccessibility` = '" . $eHandiCapAccessibility . "',
			`eChildSeatAvailable` = '" . $eChildSeatAvailable . "',
			`eWheelChairAvailable` = '" . $eWheelChairAvailable . "',
			$rental_query
			`vCarType` = '" . $cartype . "' $str" . $where;
        $obj->sql_query($query);
        if ($id != "" && $db_mdl[0]['eStatus'] != $eStatus) {
            if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
                $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vCompany as companyFirstName
						FROM driver_vehicle dv, register_driver rd, make m, model md, company c WHERE dv.eStatus != 'Deleted' AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $id . "'";
                $data_email_drv = $obj->MySQLSelect($sql23);
                $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
                $maildata['NAME'] = $data_email_drv[0]['vName'];
                $maildata['EMAIL_NAME'] = $data_email_drv[0]['vName'];
                //$maildata['LAST_NAME'] = $data_drv[0]['companyFirstName'];
                $maildata['DETAIL'] = "Your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . " " . $data_email_drv[0]['vMake'] . " - " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarly " . $eStatus;
                $COMM_MEDIA_OBJ->SendMailToMember("ACCOUNT_STATUS", $maildata);
            }
        }
        $id = ($id != '') ? $id : $obj->GetInsertId();
        if ($action == "Add") {
            $sql = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
            $db_compny = $obj->MySQLSelect($sql);
            $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
            $db_status = $obj->MySQLSelect($sql);
            if(empty($db_status[0]['iDriverVehicleId']))
            {
                $updateData['iDriverVehicleId'] = $id;
                $where = " iDriverId = '" . $iDriverId . "'";
                $obj->MySQLQueryPerform("register_driver", $updateData, 'update', $where);
            }
            $maildata['EMAIL'] = $db_status[0]['vEmail'];
            $maildata['NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
            $maildata['EMAIL_NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
            //$maildata['DETAIL'] = "Thanks for adding your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . ".<br />We will soon verify and check it's documentation and proceed ahead with activating your account.<br />We will notify you once your account become active and you can then take " . $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . " with " . $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] . ".";
            $maildata['DETAIL'] = '<br />' . str_replace(['#VEHICLE_TXT#',
                                                          '#JOB_TXT#',
                                                          '#USER#'], [$langage_lbl_admin['LBL_TEXI_ADMIN'],
                                                                      $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'],
                                                                      $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']], $langage_lbl_admin['LBL_VEHICLE_ADDED_ADMIN_EMAIL']);
            $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING", $maildata);
        } else {
            assignVehicleToDriver($iDriverId);
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
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * from  $tbl_name where iDriverVehicleId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iMakeId = $value['iMakeId'];
            $iModelId = $value['iModelId'];
            $vLicencePlate = $value['vLicencePlate'];
            $iYear = $value['iYear'];
            $eCarX = $value['eCarX'];
            $eType = $value['eType'];
            $eCarGo = $value['eCarGo'];
            $iDriverId = $value['iDriverId'];
            $vCarType = $value['vCarType'];
            $vRentalCarType = $value['vRentalCarType'];
            $iCompanyId = $value['iCompanyId'];
            $eHandiCapAccessibility = $value['eHandiCapAccessibility'];
            $eChildSeatAvailable = $value['eChildSeatAvailable'];
            $eWheelChairAvailable = $value['eWheelChairAvailable'];
            $eStatus = $value['eStatus'];
            $vColour = $value['vColour'];
        }
    }
}
$vCarTyp = explode(",", $vCarType);
$vRentalCarTyp = explode(",", $vRentalCarType);
/* if($APP_TYPE == 'Delivery'){
  $Vehicle_type_name = 'Deliver';
  } else if($APP_TYPE == 'Ride-Delivery-UberX') {
  $Vehicle_type_name = 'Ride-Delivery';
  } else {
  $Vehicle_type_name = $APP_TYPE;
  }

  //$Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;
  if($Vehicle_type_name == "Ride-Delivery"){
  $vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver') AND iLocationId = '-1'";
  $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
  } else {
  if($Vehicle_type_name == 'UberX'){
  $vehicle_type_sql = "SELECT vt.*,vc.iVehicleCategoryId,vc.vCategory_".$default_lang." FROM vehicle_type as vt  left join ".$sql_vehicle_category_table_name." as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='".$Vehicle_type_name."' AND vt.iLocationId = '-1'";
  $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
  } else {
  $vehicle_type_sql = "SELECT * FROM vehicle_type WHERE eType='".$Vehicle_type_name."' AND iLocationId = '-1'";
  $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
  }
  } */
$isStoreDriverOption = $MODULES_OBJ->isStorePersonalDriverAvailable();
//$isStoreDriverOption = 0;
$serviceStoreArr = $serviceArr = array();
$selectedServiceId = 0;
if ($isStoreDriverOption > 0) {
    $serviceArr = json_decode(serviceCategories, true);
    $getStoreList = $obj->MySQLSelect("SELECT iServiceId,iCompanyId,vCompany,eStatus,vEmail FROM company WHERE eStatus = 'Active' AND vCompany != '' AND iServiceId > 0 ORDER BY vCompany ASC");
    for ($g = 0; $g < scount($getStoreList); $g++) {
        if ($iCompanyId == $getStoreList[$g]['iCompanyId']) {
            $selectedServiceId = $getStoreList[$g]['iServiceId'];
        }
        $serviceStoreArr[$getStoreList[$g]['iServiceId']][] = $getStoreList[$g];
    }
}
//echo "<pre>";print_r($serviceArr);die;
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_VEHICLE_TXT_ADMIN']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="../assets/validation/validatrix.css"/>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <style type="text/css">
        .delivery-helper-info ul {
            text-align: left;
            list-style-type: disc;
        }
    </style>
</head>
<!-- END  HEAD-->
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
                    <h2><?= $action . " " . $langage_lbl_admin['LBL_VEHICLE_TITLE']; ?></h2>
                    <a href="vehicles.php?<?php echo $queryString; ?>" class="back_link">
                        <input type="button" value="<?= $langage_lbl_admin['LBL_RIDER_back_to_listing']; ?>"
                               class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <!-- <? if ($success == 3) { ?>
                                                        <div class="alert alert-danger alert-dismissable">
                                                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php print_r($error); ?>
                                                        </div><br/>
                            <? } ?> -->
                    <?php include('valid_msg.php'); ?>
                    <form name="_vehicle_form" id="_vehicle_form" method="post" action="">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink"
                               value="vehicles.php?<?php echo $queryString; ?>"/>
                        <?php if ($APP_TYPE != 'UberX') { ?>
                            <!--<?php if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                <label>Service Type<span class="red">*</span></label>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <select  class="form-control" name = 'eType' required id='etypedelivery'>
                                                                        <option value="Ride" <?php if ($eType == "Ride") {
                                echo 'selected="selected"';
                            } ?> >Ride</option>
                                                                        <option value="Delivery"<?php if ($eType == "Delivery") {
                                echo 'selected="selected"';
                            } ?>>Delivery</option>
                                                                    </select>
                                                                </div>
                                                            </div>            
                                    <?php } else { ?>
                                                                <input type="hidden" name="eType" value="<?= $APP_TYPE ?>" id='etypedelivery'>
                                    <?php } ?> -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Make
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select name="iMakeId" id="iMakeId" class="form-control"
                                            onChange="get_model(this.value, '')">
                                        <option value="">CHOOSE MAKE</option>
                                        <?php for ($j = 0; $j < scount($db_make); $j++) { ?>
                                            <option value="<?= $db_make[$j]['iMakeId'] ?>" <?php if ($iMakeId == $db_make[$j]['iMakeId']) { ?> selected <?php } ?>><?= $db_make[$j]['vMake'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Model
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <div id="carmdl">
                                        <select name="iModelId" id="iModelId" class="form-control">
                                            <option value="">
                                                CHOOSE <?php //echo $langage_lbl_admin['LBL_VEHICLE_CAPITAL_TXT_ADMIN'];      ?>
                                                MODEL
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Year
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select name="iYear" id="iYear" class="form-control">
                                        <option value="">CHOOSE YEAR</option>
                                        <?php for ($j = $start; $j >= $end; $j--) { ?>
                                            <option value="<?= $j ?>" <? if ($iYear == $j) { ?> selected <? } ?>><?= $j ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>License Plate
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="vLicencePlate" id="vLicencePlate"
                                           value="<?= $vLicencePlate; ?>" placeholder="Licence Plate">
                                    <!-- onblur="check_licence_plate(this.value,'<?= $id ?>')" -->
                                    <b>
                                        <span id="plate_warning" class="error"></span>
                                    </b>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($isStoreDriverOption > 0) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Service Category
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select onchange="displayStoreList(this.value);" class="form-control"
                                            name='iServiceId' id='iServiceId' required="required">
                                        <option value="0">General</option>
                                        <?php for ($s = 0; $s < scount($serviceArr); $s++) { ?>
                                            <option value="<?= $serviceArr[$s]['iServiceId'] ?>" <?= ($serviceArr[$s]['iServiceId'] == $selectedServiceId) ? 'selected' : ''; ?>>
                                                <?= clearCmpName($serviceArr[$s]['vServiceName']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" id="storelisthtml">
                                <div class="col-lg-12">
                                    <label>Store Selection
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select class="form-control filter-by-text" name='storeId' id='iCompanyIdhtml'
                                            required="required" data-text="CHOOSE STORE">
                                    </select>
                                </div>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name='iServiceId' id='iServiceId' value="0">
                        <?php }
                        if (ONLYDELIVERALL == 'No') { ?>
                            <div class="row" id="companylisthtml">
                                <div class="col-lg-12">
                                    <label>Company
                                        <span class="red"> *</span>
                                    </label>
                                </div>
                                <div class="col-lg-6">
                                    <select name="iCompanyId" id="iCompanyId" class="form-control filter-by-text"
                                            data-text="CHOOSE COMPANY">
                                        <option value="">CHOOSE COMPANY</option>
                                    </select>
                                </div>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="iCompanyId" id="iCompanyIdHidden"
                                   value="<?= $defaultCompany; ?>">
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label><?php echo $langage_lbl_admin['LBL_VEHICLE_DRIVER_TXT_ADMIN']; ?>
                                    <span class="red"> *</span>
                                </label>
                            </div>
                            <div class="col-lg-6">
                                <select name="iDriverId"
                                        id="driverNo" <?php if ($isStoreDriverOption > 0 && $action == "Add") { ?> onchange="getVehicleType();" <?php } ?>
                                        class="form-control filter-by-text"
                                        data-text="<?= $langage_lbl_admin['LBL_CHOOSE_DRIVER_ADMIN']; ?>">
                                    <!--<option value=""> CHOOSE <?php echo strtoupper($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> </option>-->
                                    <option value=""><?php echo $langage_lbl_admin['LBL_CHOOSE_DRIVER_ADMIN']; ?> </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Vehicle <?php echo $langage_lbl_admin['LBL_COLOR_ADD_VEHICLES']; ?></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="vColour" id="vColour"
                                       value="<?= $vColour; ?>" placeholder="Vehicle Color">
                            </div>
                        </div>
                        <?php if ($APP_TYPE != 'Delivery' && ONLYDELIVERALL != 'Yes') { ?>
                            <?php if (isset($HANDICAP_ACCESSIBILITY_OPTION) && $HANDICAP_ACCESSIBILITY_OPTION == "Yes") { ?>
                                <div class="row" id="handicapaccess">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_HANDICAP_QUESTION_ADD_VEHICLES']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning"
                                             data-on-label='Yes' data-off-label='No'>
                                            <input type="checkbox" name="eHandiCapAccessibility"
                                                   id="eHandiCapAccessibility" <?= ($eHandiCapAccessibility == 'No') ? '' : 'checked'; ?> />
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            if (isset($CHILD_SEAT_ACCESSIBILITY_OPTION) && $CHILD_SEAT_ACCESSIBILITY_OPTION == "Yes") { ?>
                                <div class="row" id="childseataccess">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_CHILD_SEAT_ADD_VEHICLES']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning"
                                             data-on-label='Yes' data-off-label='No'>
                                            <input type="checkbox" name="eChildSeatAvailable"
                                                   id="eChildSeatAvailable" <?= ($eChildSeatAvailable == 'No') ? '' : 'checked'; ?> />
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            if (isset($WHEEL_CHAIR_ACCESSIBILITY_OPTION) && $WHEEL_CHAIR_ACCESSIBILITY_OPTION == "Yes") { ?>
                                <div class="row" id="wheelchairaccess">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_WHEEL_CHAIR_ADD_VEHICLES']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="make-switch" data-on="success" data-off="warning"
                                             data-on-label='Yes' data-off-label='No'>
                                            <input type="checkbox" name="eWheelChairAvailable"
                                                   id="eWheelChairAvailable" <?= ($eWheelChairAvailable == 'No') ? '' : 'checked'; ?> />
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>



                        <?php if(ONLYDELIVERALL != "Yes") { ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label><?= $langage_lbl_admin['LBL_VEHICLE_TITLE']; ?> Type
                                    <span class="red">*</span>
                                </label>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="checkbox-group required">
                            <div id="vehicleTypes001">
                            </div>
                        </div>
                        <?php if ($eStatus != 'Deleted') { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Status</label>
                                </div>
                                <div class="col-lg-6">
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" name="eStatus"
                                               id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' && $userObj->hasPermission($create))) { ?>
                                    <input type="submit" class="btn btn-default" name="submit" id="submit"
                                           value="<?php if ($action == 'Add') { ?><?= $action; ?> <?php echo $langage_lbl_admin['LBL_Vehicle']; ?><?php } else { ?>Update<?php } ?>">
                                    <input type="reset" value="Reset" class="btn btn-default">
                                <?php } ?>
                                <a href="vehicles.php?<?php echo $queryString; ?>" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>
                        Please close the application and open it again to see the reflected changes after saving the
                        values above.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<? include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="../assets/js/modal_alert.js"></script>
</body>
<!-- END BODY-->
</html>
<?php if ($action == 'Edit') { ?>
    <script>
        window.onload = function () {
            get_model('<?php echo $db_mdl[0]['iMakeId']; ?>', '<?php echo $db_mdl[0]['iModelId']; ?>');
            //get_driver('<?php echo $iCompanyId; ?>', '<?php echo $iDriverId; ?>');
            // get_vehicleType('<?= $iDriverId; ?>', '<?= $vCarType; ?>', '<?= $eType ?>', '<?= $vRentalCarType ?>');
        };
    </script>
<? } else { ?>
    <script>
        $(document).ready(function () {
            var appType = '<?= $APP_TYPE ?>';
            if (appType == 'Ride-Delivery-UberX' || appType == 'Ride-Delivery') {
                SelectedAppType = 'Ride';
            } else if (appType == 'Delivery') {
                SelectedAppType = 'Deliver';
            } else {
                SelectedAppType = appType;
            }

            <?php if($action == "Add") { ?>
            get_vehicleType('', '', SelectedAppType, '');
            <?php } ?>

        });
    </script>
<?php } ?>
<script>

    $('input:reset').click(function () {
        $('input:text').attr('value', '');
        $("#driverNo").val('').trigger('change');
        $("#iMakeId").val('').trigger('change');
    });
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "vehicles.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
        $("#vehicleTypes001").html('Loading Vehicle Types...');
    });
    $('#driverNo').on('change', function () {
        get_vehicleType(this.value, '<?php echo $vCarType; ?>', $("#etypedelivery").val(), '<?= $vRentalCarType ?>');
    });
    $('#etypedelivery').on('change', function () {
        get_vehicleType($("#driver").val(), '<?php echo $vCarType; ?>', this.value, '<?= $vRentalCarType ?>');
        if (this.value == 'Delivery') {
            $("#handicapaccess,#childseataccess,#wheelchairaccess").hide();
        } else {
            $("#handicapaccess,#childseataccess,#wheelchairaccess").show();
        }
    });

    function getVehicleType() {
        var driverId = $("#driverNo").val();
        var serviceId = $("#iServiceId").val();
        var appType = '<?= $APP_TYPE ?>';
        if (appType == 'Ride-Delivery-UberX' || appType == 'Ride-Delivery') {
            SelectedAppType = 'Ride';
        } else if (appType == 'Delivery') {
            SelectedAppType = 'Deliver';
        } else {
            SelectedAppType = appType;
        }
        $("#handicapaccess,#childseataccess,#wheelchairaccess").show();
        if (serviceId != "0") {
            SelectedAppType = "DeliverAll";
            $("#handicapaccess,#childseataccess,#wheelchairaccess").hide();
        }
        //$("#_vehicle_form").valid();

        get_vehicleType(driverId, '', SelectedAppType, '');
    }

    function get_model(model, modelid) {
        $("#carmdl").html('Wait...');
        if (model != '') {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_find_model.php',
                'AJAX_DATA': "action=get_model&model=" + model + "&iModelId=" + modelid,
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#carmdl").html(data);
                    $("#_vehicle_form").valid();
                } else {
                    console.log(response.result);
                }
            });
        } else {
            $("#carmdl").html('');
            /*------------------this code used when click on rest button-----------------*/
            var container = document.getElementById("carmdl");
            var selectElement = document.createElement("select");
            selectElement.name = "iModelId";
            selectElement.id = "iModelId";
            selectElement.className = "form-control";
            var options = [
                {value: "", label: "CHOOSE MODEL"},
            ];
            for (var i = 0; i < options.length; i++) {
                var option = document.createElement("option");
                option.value = options[i].value;
                option.textContent = options[i].label;
                selectElement.appendChild(option);
            }
            container.appendChild(selectElement);
            /*------------------this code used when click on rest button-----------------*/
        }
    }

    var serviceStoreArr = <?= json_encode($serviceStoreArr); ?>;
    var selCompanyId = '<?= $iCompanyId; ?>'
    var selServiceId = '<?= $selectedServiceId; ?>'
    <?php if($isStoreDriverOption > 0){ ?>
    displayStoreList(selServiceId);
    <?php } ?>
    function displayStoreList(serviceId) {
        $("#iServiceId").val(serviceId);
        getVehicleType();
        if (serviceId > 0) {
            $("#storelisthtml").show();
            $("#iCompanyIdhtml").attr("required", "required");
            $("#companylisthtml").hide();
            var optionhtml = "";
            var serviceData = serviceStoreArr[serviceId];
            $('select.filter-by-text#driverNo').val(null).trigger('change');
            $('select.filter-by-text#iCompanyId').val(null).trigger('change');
        } else {
            $("#storelisthtml").hide();
            $("#iCompanyIdhtml").removeAttr("required");
            $("#companylisthtml").show();
            $('select.filter-by-text#driverNo').val(null).trigger('change');
            $('select.filter-by-text#iCompanyIdhtml').val(null).trigger('change');
        }
        $("#iCompanyIdhtml").html(optionhtml);
        //console.log(serviceStoreArr);
    }

    // function get_vehicleType(iDriverId = '', selected = '', eType = '', rentalselected = '') {
    function get_vehicleType(iDriverId, selected, eType, rentalselected) {
        iDriverId = iDriverId || '';
        selected = selected || '';
        eType = eType || '';
        rentalselected = rentalselected || '';
        if (eType == 'Delivery') {
            var eType = 'Deliver';
        } else {
            var eType = eType;
        }
        var fly = '<?php echo $fly; ?>';
        var serviceId = $("#iServiceId").val();
        $("#vehicleTypes001").html('Loading Vehicle Types...');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_find_vehicleType.php',
            'AJAX_DATA': "iDriverId=" + iDriverId + "&selected=" + selected + "&eType=" + eType + "&rentalselected=" + rentalselected + "&fly=" + fly + "&serviceId=" + serviceId,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#vehicleTypes001").html(data);
            }
        });
    }

</script>
<link rel="stylesheet" href="css/select2/select2.min.css"/>
<script src="js/plugins/select2.min.js"></script>
<script>
    $('body').on('keyup', '.select2-search__field', function () {

        $("#_vehicle_form").valid();

        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ($(".select2-results__options").is(".select2-results__message")) {
            $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });

    $('body').on('keyup', '.select2-search__field', function () {

        $("#_vehicle_form").valid();
    });


    function formatDesign(item) {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        if (selectionText[2] != null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2] + '</span>');
        } else if (selectionText[2] == null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if (selectionText[2] != null && selectionText[1] == null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }
        return $returnString;
    }

    function formatDesignnew(item) {
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#driverNo").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var serviceIdnew = $('#iServiceId option:selected').val();
                        if (typeof serviceIdnew == 'undefined' || serviceIdnew == 0) {
                            <? if (ONLYDELIVERALL == 'No') { ?>
                            var company_id = $('#iCompanyId option:selected').val();
                            <? } else { ?>
                            var companyidselected = $("#iCompanyIdHidden").val();
                            var company_id = $('#iCompanyIdHidden').val();
                            <? } ?>
                        } else {
                            var company_id = $('#iCompanyIdhtml option:selected').val();
                        }
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Driver',
                            company_id: company_id,
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;
                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }
                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        return {
                            results: $.map(data, function (item) {
                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };
                    },
                    transport: function (params, success, failure) {
                        params.beforeSend = function (request) {
                            mO4u1yc3dx(request);
                        };
                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }).on('select2:select', function (e) {
                $("#_vehicle_form").valid();
            });
        });
    });
    $(function () {
        $("select.filter-by-text#iCompanyId").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Company',
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;
                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }
                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        return {
                            results: $.map(data, function (item) {
                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };
                    },
                    transport: function (params, success, failure) {
                        params.beforeSend = function (request) {
                            mO4u1yc3dx(request);
                        };
                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });
    $(function () {
        $("select.filter-by-text#iCompanyIdhtml").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Store',
                            selectedserviceId: $('#iServiceId option:selected').val(),
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;
                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }
                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        return {
                            results: $.map(data, function (item) {
                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };
                    },
                    transport: function (params, success, failure) {
                        params.beforeSend = function (request) {
                            mO4u1yc3dx(request);
                        };
                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });
    // Fetch the preselected item, and add to the control
    var sId = '<?= $iDriverId;?>';
    var sSelect = $('select.filter-by-text#driverNo');
    var sIdCompany = '<?= $iCompanyId;?>';
    var sSelectCompany = $('select.filter-by-text#iCompanyId');
    var sIdStore = '<?= $iCompanyId;?>';
    var sSelectStore = $('select.filter-by-text#iCompanyIdhtml');
    var itemname;
    var itemid;
    if (sIdStore != '') {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdStore + '&usertype=Store',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectStore.append(option).trigger('change');
            } else {
            }
        });
    }
    if (sIdCompany != '') {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompany + '&usertype=Company',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectCompany.append(option);
            } else {
            }
        });
    }
    if (sId != '') {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Driver',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                //sSelect.append(option).trigger('change');
                sSelect.append(option);
                get_vehicleType('<?= $iDriverId; ?>', '<?= $vCarType; ?>', '<?= $eType ?>', '<?= $vRentalCarType ?>');
            } else {
            }
        });
    }
    var $eventSelect = $("select.filter-by-text#iCompanyId");
    $eventSelect.on("change", function (e) {
        $('select.filter-by-text#driverNo').val(null).trigger('change');
    });
    var $eventstoreSelect = $("select.filter-by-text#iCompanyIdhtml");
    $eventstoreSelect.on("change", function (e) {
        $('select.filter-by-text#driverNo').val(null).trigger('change');
    });
</script>