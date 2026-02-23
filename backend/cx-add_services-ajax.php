<?php
include_once('common.php');

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$start = @date("Y");
$end = '1970';
$script = "My Availability";
$tbl_name = 'driver_vehicle';
$tbl_name1 = 'service_pro_amount';
$tbl_dsr = 'driver_service_request';

$iDriverId = isset($_REQUEST['iDriverId']) ? (trim($_REQUEST['iDriverId'])) : '';

$sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '" . $iDriverId . "' AND eType='UberX'";
$db_drv_veh = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : $db_drv_veh[0]['iDriverVehicleId'];
$action = ($id != '') ? 'Edit' : 'Add';

$SearchService = isset($_REQUEST['SearchService']) ? $_REQUEST['SearchService'] : 0;
$isAjax = isset($_REQUEST['isAjax']) ? $_REQUEST['isAjax'] : 'No';
$sqlSearch = '';
if($isAjax=='Yes') {
   $search_keyword = $SearchService;
   $sqlSearch = " AND vt.vVehicleType LIKE '%".$SearchService."%'";
   $sqlSearchCat = " AND vc.vCategory_" . $_SESSION['sess_lang']." LIKE '%".$SearchService."%'";
}
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';
$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '3';
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '1';
$fAmount = isset($_POST['fAmount']) ? $_POST['fAmount'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : Date('Y');
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : 'UberX';

if ($_SESSION['sess_user'] == 'driver') {
    $sql = "select iCompanyId,vCurrencyDriver from `register_driver` where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $iCompanyId = $db_usr[0]['iCompanyId'];
    $vCurrencyDriver = $db_usr[0]['vCurrencyDriver'];
    if ($vCurrencyDriver != '') {
        $sql1 = "select Ratio,vSymbol from `currency` where vName = '" . $vCurrencyDriver . "'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    } else {
        $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }
    $vSymbol = $db_usrcurrency[0]['vSymbol'];
    $ration = $db_usrcurrency[0]['Ratio'];
}
if ($_SESSION['sess_user'] == 'company') {
    $iCompanyId = $_SESSION['sess_iCompanyId'];
    $sql = "select * from register_driver where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
    $db_drvr = $obj->MySQLSelect($sql);

    $sql = "select iCompanyId,vCurrencyDriver from `register_driver` where iDriverId = '" . $iDriverId . "'";
    $db_usr = $obj->MySQLSelect($sql);
    $vCurrencyDriver = $db_usr[0]['vCurrencyDriver'];
    if ($vCurrencyDriver != '') {
        $sql1 = "select Ratio,vSymbol from `currency` where vName = '" . $vCurrencyDriver . "'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    } else {
        $sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
        $db_usrcurrency = $obj->MySQLSelect($sql1);
    }
    $vSymbol = $db_usrcurrency[0]['vSymbol'];
    $ration = $db_usrcurrency[0]['Ratio'];
}

/* Replace with ePricetype */
$chngamt = "Disabled";
if ($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes") {
    $chngamt = "Enabled";
}

if (isset($_POST['submit1'])) {

    if (SITE_TYPE == 'Demo' && $action == 'Edit') {
        $error_msg = $langage_lbl['LBL_EDIT_DELETE_RECORD'];
        header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }
    if ($ENABLE_EDIT_DRIVER_SERVICE == "No") {
        $error_msg = $langage_lbl['LBL_EDIT_SERVICE_DISABLED'];
        header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }

    if (!isset($_REQUEST['vCarType'])) {
        $error_msg = $langage_lbl['LBL_SELECT_CAR_TYPE'];
        header("Location:cx-add_services.php?iDriverId=" . base64_encode(base64_encode($iDriverId)) . "&error_msg=" . $error_msg . "&success=2");
        exit;
    }

    if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
        $vLicencePlate = 'My Services';
    } else {
        $vLicencePlate = $vLicencePlate;
    }

    if (SITE_TYPE == 'Demo') {
        $str = ", eStatus = 'Active' ";
    } else {
        $str = ", eStatus = 'Active' ";
    }

    $cartype = implode(",", $_REQUEST['vCarType']);

    $driverstatusQuery = "SELECT eStatus FROM register_driver WHERE iDriverId = '".$iDriverId."'";
    $iDriverStatus = $obj->MySQLSelect($driverstatusQuery);
    $eStatus = $iDriverStatus[0]['eStatus'];

    /* ------------------------------ */
    /* Request Service for Activation */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') {

        if($eStatus != 'inactive'){
                
                // $sql = "SELECT vCarType from driver_vehicle where iDriverId = '" . $iDriverId . "' AND vLicencePlate = 'My Services'";
                $sql  = 'SELECT dv.vCarType, rd.vEmail, rd.vName ,rd.vLastName ,rd.vCode ,rd.vPhone FROM driver_vehicle AS dv JOIN register_driver AS rd ON rd.iDriverId = dv.iDriverId WHERE dv.iDriverId = ' .$iDriverId.' AND dv.vLicencePlate = "My Services"' ;
                $existRequestdb = $obj->MySQLSelect($sql);

                $existServices = explode(',',$existRequestdb[0]['vCarType']);

                $remainingCats = array_diff($_REQUEST['vCarType'],$existServices);
                
                foreach ($remainingCats as $key => $catVal) {

                    if(!empty($catVal)){
                        $sql = "SELECT iDriverId from driver_service_request where iDriverId = '" . $iDriverId . "' AND iVehicleCategoryId = '" . $catVal . "'";
                        $existRequest = $obj->MySQLSelect($sql);

                        if (scount($existRequest) == 0) {
                            $q = "INSERT INTO ";
                            $wheredrs = '';
                        
                            $query = $q . " `" . $tbl_dsr . "` SET		
                                `iVehicleCategoryId` = '" . $catVal . "',
                                `iDriverId` = '" . $iDriverId . "',
                                `cRequestStatus` = 'Pending'"
                                    . $wheredrs;
                                    
                            $obj->sql_query($query);        
                        }
                    }
                }

                if(!empty($remainingCats)){

                    /* Send Email to Driver */  
                    $getMaildata['name'] = $existRequestdb[0]['vName']." ".$existRequestdb[0]['vLastName'];

                    if(isset($existRequestdb[0]['vEmail']) && !empty($existRequestdb[0]['vEmail']))
                    {
                        $getMaildata['email'] = $langage_lbl['LBL_EMAIL_TEXT'].': '.$existRequestdb[0]['vEmail'];
                    }
                    if(isset($existRequestdb[0]['vPhone']) && !empty($existRequestdb[0]['vPhone'])){

                        $getMaildata['phone'] = $langage_lbl['LBL_PHONE'].': '."+".$existRequestdb[0]['vCode']." ".$existRequestdb[0]['vPhone'];
                    }
                    $mail = $COMM_MEDIA_OBJ->SendMailToMember('SERVICE_REQUEST_FROM_PROVIDER',$getMaildata);
                    
                }

            }
    }
    /* End Request Service for Activation */            
    /* ------------------------------ */

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iDriverId` = '" . $iDriverId . "' AND `iDriverVehicleId` = '" . $id . "' ";
    }

    $query = $q . " `" . $tbl_name . "` SET		
		`vLicencePlate` = '" . $vLicencePlate . "',
		`iYear` = '" . $iYear . "',		
		`iCompanyId` = '" . $iCompanyId . "',
		`iDriverId` = '" . $iDriverId . "',
		`eType` = '" . $eType . "',
		`vCarType` = '" . $cartype . "' $str"
            . $where;

    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();

    /* --------------------------------------- */
    /* This is for Reverse operation for new added services as it should be approve first */
    if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $eType == "UberX" && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') {
        if($eStatus != 'inactive'){

            $sql = "SELECT vCarType from driver_vehicle where iDriverId = '" . $iDriverId . "' AND vLicencePlate = 'My Services'";
            $existRequest = $obj->MySQLSelect($sql);
            $existServices = explode(',',$existRequest[0]['vCarType']);
            
            $existServices = implode(',',array_diff($existServices,$remainingCats));
            $sqlu  = 'UPDATE driver_vehicle SET vCarType = "'.$existServices.'" WHERE iDriverId = "' .$iDriverId.'" AND vLicencePlate = "My Services"' ;
            $existingServices = $obj->sql_query($sqlu);
        }
    }
    /* End for Reverse operation for new added services */
    /* --------------------------------------- */

    if (!empty($fAmount)) {
        //$amt_man=$fAmount;
        $amt_man = array();
        foreach ($fAmount as $key => $value) {
            $amt_man[$key] = $value / $ration;
        }
        $sql = "select iServProAmntId,iDriverVehicleId from " . $tbl_name1 . " where iDriverVehicleId = '" . $id . "' ";
        $db_drv_price = $obj->MySQLSelect($sql);
        if (scount($db_drv_price) > 0) {
            $sql = "delete from " . $tbl_name1 . " where iDriverVehicleId='" . $db_drv_price[0]['iDriverVehicleId'] . "'";
            $obj->sql_query($sql);
        }

        foreach ($amt_man as $key => $value) {
            if ($value != "") {
                $q = "Insert Into ";
                $query = $q . " `" . $tbl_name1 . "` SET
					`iDriverVehicleId` = '" . $id . "',
					`iVehicleTypeId` = '" . $key . "',
					`fAmount` = '" . $value . "'";
                $db_parti_price = $obj->sql_query($query);
            }
        }
    }

    if ($action == "Add") {
        $sql = "SELECT * FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
        $db_compny = $obj->MySQLSelect($sql);

        $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
        $db_status = $obj->MySQLSelect($sql);

        $maildata['EMAIL'] = $db_status[0]['vEmail'];
        $maildata['NAME'] = $db_status[0]['vName'];
        $maildata['EMAIL_NAME'] = $db_status[0]['vName'];
        $maildata['DETAIL'] = "Your Services is Added For " . $db_compny[0]['vName'] . " and will process your document and activate your account ";
        $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING", $maildata);
    }

    $var_msg = $langage_lbl['LBL_SERVICE_UPDATE_SUCCESS'];
    header("Location:cx-add_services.php?success=1&var_msg=" . $var_msg . "&iDriverId=" . base64_encode(base64_encode($iDriverId)));
}

// for Edit

$sql = "SELECT t.*,t1.fAmount,t1.iServProAmntId,t1.iVehicleTypeId AS `VehicleId`,t1.iDriverVehicleId AS `DriverVehilceId` from  $tbl_name as t left join $tbl_name1 t1 on t.iDriverVehicleId=t1.iDriverVehicleId where t.iDriverId = '" . $iDriverId . "' AND t.iDriverVehicleId = '" . $db_drv_veh[0]['iDriverVehicleId'] . "' ";
$db_data = $obj->MySQLSelect($sql);
$vLabel = $id;
$fAmount = array();
if (scount($db_data) > 0) {
    foreach ($db_data as $key => $value) {
        $vLicencePlate = $value['vLicencePlate'];
        $iYear = $value['iYear'];
        $eCarX = $value['eCarX'];
        $eType = $value['eType'];
        $eCarGo = $value['eCarGo'];
        $iDriverId = $value['iDriverId'];
        $vCarType = $value['vCarType'];
        $iCompanyId = $value['iCompanyId'];
        $eStatus = $value['eStatus'];
        $iDriverVehicleId = $value['iDriverVehicleId'];
        $amt = $value['fAmount'] * $ration;
        $fAmount[$value['VehicleId']] = $amt;
    }
}

$vCarTyp = explode(",", $vCarType);

if ($APP_TYPE == 'Delivery') {
    $Vehicle_type_name = 'Deliver';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $Vehicle_type_name = 'UberX';
} else {
    $Vehicle_type_name = $APP_TYPE;
}

if ($Vehicle_type_name == "Ride-Delivery") {
    $vehicle_type_sql = "SELECT * from  vehicle_type where(eType ='Ride' or eType ='Deliver')";
    $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
} else {
    if ($Vehicle_type_name == 'UberX') {
        $userSQL = "SELECT c.iCountryId from register_driver AS rd LEFT JOIN country AS c ON c.vCountryCode=rd.vCountry where rd.iDriverId='" . $iDriverId . "'";
        $drivers = $obj->MySQLSelect($userSQL);
        $iCountryId = $drivers[0]['iCountryId'];
        $whereParentId = "";
        if ($parent_ufx_catid > 0) {
            $whereParentId = " AND vc.iVehicleCategoryId='" . $parent_ufx_catid . "'";
        }
        $getvehiclecat = "SELECT vc.iVehicleCategoryId, vc.vCategory_" . $_SESSION['sess_lang'] . " as main_cat FROM vehicle_category as vc WHERE vc.eStatus='Active' AND vc.iParentId='0' $whereParentId order by vc.vCategory_" . $_SESSION['sess_lang'];
        $vehicle_type_data = $obj->MySQLSelect($getvehiclecat);
        $i = 0;
        foreach ($vehicle_type_data as $key => $val) {
            $vehicle_type_sql = "SELECT vc.eVideoConsultEnable,vt.vVehicleType,vc.iParentId,vc.vCategory_" . $_SESSION['sess_lang'] . ",vc.iVehicleCategoryId from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' AND vc.iParentId ='" . $val['iVehicleCategoryId'] . "'  AND vc.eStatus='Active' GROUP BY vc.iVehicleCategoryId";
            $vehicle_type_dataOld = $obj->MySQLSelect($vehicle_type_sql);
            
            $vehicle_type_data[$i]['SubCategory'] = $vehicle_type_dataOld;
            $j = 0;
            foreach ($vehicle_type_dataOld as $subkey => $subvalue) {
                $vehicle_type_sql1 = "SELECT vt.*,vc.*,lm.vLocationName from  vehicle_type as vt  left join vehicle_category as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' and vc.iVehicleCategoryId = '" . $subvalue['iVehicleCategoryId'] . "' AND (lm.iCountryId='" . $iCountryId . "' || vt.iLocationid='-1') AND vt.eStatus='Active'";
                $vehicle_type_dataNew = $obj->MySQLSelect($vehicle_type_sql1);
                $vehicle_type_data[$i]['SubCategory'][$j]['VehicleType'] = $vehicle_type_dataNew;
                $j++;
            }

            $i++;
        }
    } else {
        $vehicle_type_sql = "SELECT * from  vehicle_type  where eType='" . $Vehicle_type_name . "' ";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    }
}
//echo "<PRE>";print_R($vehicle_type_data); exit;
if(!empty($search_keyword) && $MODULES_OBJ->isEnableSearchUfxServices()) {
   $vehicleCategoryData = $vehicle_type_data;
   //print_R($vehicleCategoryData);
   foreach ($vehicleCategoryData as $key => $value) {
         $main_cat = $subcat = $subcattype = $mainsubcat = 0;
         //print_R($value); exit;
         if(stripos($value['main_cat'], $search_keyword) !== false) {
             $main_cat = 1;
         }
     //print_R($value['SubCategory']); exit;
         if(isset($value['SubCategory']) && $main_cat == 0) {
            
            foreach ($value['SubCategory'] as $skey => $sCategory) {
               
               if(stripos($sCategory['vCategory_' . $_SESSION['sess_lang']], $search_keyword) !== false) {
                   $subcat = 1;
               }
               else {
                  $subcattype = 0;
               
                  foreach ($sCategory['VehicleType'] as $skeyType => $sCategoryType) {                    
                      if(stripos($sCategoryType['vVehicleType_' . $_SESSION['sess_lang']], $search_keyword) !== false) {
                           $subcattype = 1;$mainsubcat=1;
                      } else {
                           unset($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType'][$skeyType]);
                      }
                  }
           
                          
                  if(!empty($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType'])) {
                      $vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType'] = array_values($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType']);
                  }
               
                  if(empty($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType']) && $subcattype==0) {
                     unset($vehicleCategoryData[$key]['SubCategory'][$skey]);
                  }
               
               }
            }
     
             if(!empty($vehicleCategoryData[$key]['SubCategory'])) {
                 $vehicleCategoryData[$key]['SubCategory'] = array_values($vehicleCategoryData[$key]['SubCategory']);
             }
         }
         
         if(($main_cat == 0 && $subcat == 0 && $mainsubcat==0) || empty($vehicleCategoryData[$key]['SubCategory'])) {
            
             unset($vehicleCategoryData[$key]);
         }
   }
   $vehicle_type_data = $vehicleCategoryData;
}
$sql  = 'SELECT iVehicleCategoryId FROM driver_service_request WHERE iDriverId = "' .$iDriverId.'" ' ;
$ReqServices = $obj->MySQLSelect($sql);
$requestedServices = [];
foreach ($ReqServices as $key => $ReqService) {
    $requestedServices[] = $ReqService['iVehicleCategoryId'] ;
}
?>
<!--<section class="profile-earning cx-addServices">-->
<form name="frm1" method="post" action="" id="frm1">

   <div class="general-form">
      <!-- Service detail page -->
      <div class="driver-add-vehicle"> 
      <?php if ($success == 1) { ?>
      <div class="form-err">
      <span class="msg_close">✕</span>
      <p style="background-color: #14b368;" class="btn-block btn btn-rect btn-success error-login-v" id="success"> <?= $langage_lbl['LBL_SERVICE_UPDATE_SUCCESS']; ?></p>
      </div>
      <?php } else if ($success == 2) { ?>
      <div class="form-err">
      <span class="msg_close">✕</span>
      <p id="errmsg" class="text-muted btn-block btn btn-danger btn-rect error-login-v"> <?= isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : ' '; ?></p>
      </div>
      <?php } ?>
      <?php if (!empty($vehicle_type_data)) { ?>
     <!--  <form name="frm1" method="post" action="" > -->
         <input type="hidden" name="iDriverIdNew"  value="<?= $iDriverId ?>"/>
         <input type="hidden" name="iCompanyId"  value="<?= $iCompanyId ?>"/>
         <input type="hidden" name="id" value="<?= $iDriverVehicleId; ?>"/>
         <input type="hidden" name="vLicencePlate"  value="<?= $vLicencePlate; ?>"/>
         <input type="hidden" name="eType"  value="<?= $eType; ?>"/>
         <div class="add-car-services-hatch add-services-hatch add-services-taxi">                        
         <div class="card-block no-services-available">
         <?php
         $emptySubCatData = $emptyVehicleType = '0';
         
         foreach ($vehicle_type_data as $value1) {
            if(scount($value1['SubCategory']) > 0 ){ // condition added NM "no service available issue"
            $emptySubCatData = empty($value1['SubCategory']) ? '0' : '1 ' ; 
            }
            
            foreach ($value1['SubCategory'] as $Vehicle_Type) {
               //print_R($Vehicle_Type);exit;
               if (!empty($Vehicle_Type['VehicleType'])) {
                  $emptyVehicleType = 1;
                  if ($Vehicle_type_name == 'UberX') {
                     $vName = 'vCategory_' . $_SESSION['sess_lang'];
                     $vehicleName = $Vehicle_Type[$vName];
                  } else {
                     $vehicle_typeName = $Vehicle_Type['vVehicleType'];
                  }
                  $iParentcatId = $Vehicle_Type['iParentId'];
                  $sql_query = "SELECT ePriceType FROM vehicle_category WHERE iVehicleCategoryId = '" . $iParentcatId . "' ";
                  $ePricetype_data = $obj->MySQLSelect($sql_query);
                  $ePricetype = $ePricetype_data[0]['ePriceType'];
                  ?>

                  <div class="main-cat">
                    <span><?= $value1['main_cat'] . " - " . $vehicleName; ?></span>
                    <?php if($MODULES_OBJ->isEnableVideoConsultingService() && $Vehicle_Type['eVideoConsultEnable'] == 'Yes' ) { ?>
                    <a href="javascript:void(0);" class="gen-btn" onclick="manageVideoConsultService('<?= $Vehicle_Type['iVehicleCategoryId'] ?>', '<?= $value1['main_cat'] . ' - ' . $vehicleName ?>')"><?= $langage_lbl['LBL_MANAGE_VIDEO_CONSULT_SERVICE_TXT'] ?></a>
                    <?php } ?>
                </div>
                  <div class="partation">
                  <ul class="setings-list">
                  
                  <?php
                  //print_R($Vehicle_Type['VehicleType']);
                  foreach ($Vehicle_Type['VehicleType'] as $val) {
                     $VehicleName1 = 'vVehicleType_' . $_SESSION['sess_lang'];
                     if ($val['eFareType'] == 'Fixed') {
                        $eFareType = 'Fixed';
                        $amt_old = $val['fFixedFare'] * $ration;
                        $fAmount_old = $amt_old;
                     } else if ($val['eFareType'] == 'Hourly') {
                        $eFareType = 'Per hour';
                        $amt_old1 = $val['fPricePerHour'] * $ration;
                        $fAmount_old = $amt_old1;
                     } else {
                        $eFareType = '';
                        $amt_old2 = $val['fFixedFare'] * $ration;
                        $fAmount_old = $amt_old2;
                     }
                     $vehicle_typeName = $val[$VehicleName1];
                     //echo $vehicle_typeName;exit;
                     if (!empty($val['vLocationName'])) {
                        $localization = '(Location : ' . $val["vLocationName"] . ')';
                     } else {
                        $localization = '';
                     }
                     $disStat = '';
                     if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {   
                        if(in_array($val['iVehicleTypeId'],$requestedServices)){
                           $disStat = 'disabled';
                        }
                     }
                     ?>
                     
                     <li>
                        <div class="toggle-list-inner">
                           <div class="toggle-combo">
                              <label><?php echo $vehicle_typeName; ?>
                                 <div style="font-size: 12px;"><?php echo $localization; ?></div>
                              </label>
                              <span class="toggle-switch">
                                 <!-- <div class="make-switch" data-on="success" data-off="warning" data-on-label='Yes' data-off-label='No'> -->
                                 <input type="checkbox" <?php if ($ePricetype == "Provider") { ?>onchange="check_box_value(this.value);" <?php }else{ ?>onchange="cTrig('vCarType1_<?= $val['iVehicleTypeId'] ?>')"  <?php } ?> id="vCarType1_<?= $val['iVehicleTypeId'] ?>" class="chk" name="vCarType[]" <?php if (in_array($val['iVehicleTypeId'], $vCarTyp)) { ?>checked<?php } ?> value="<?= $val['iVehicleTypeId'] ?>" <?=$disStat?> />
                                 <span class="toggle-base"></span>
                                 <!-- </div> -->
                              </span>
                           </div>
                           <div class="check-combo">
                              <?php if(!empty($disStat) && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') { ?>
                              <br><br><br>
                              <small><?= $langage_lbl['LBL_SERVICE_REQUEST_PENDING']; ?></small><br>
                              <?php } ?>    
                              <?php
                              if ($ePricetype == "Provider") {
                              
                                 $p001 = "style='display:none;'";
                                 $p001Lbl = "style='display:block;'";
                              
                                 if (in_array($val['iVehicleTypeId'], $vCarTyp)) {
                                    $p001 = "style='display:block;'";
                                    $p001Lbl = "style='display:none;'";
                                 }
                              
                                 $fAmount_new = $fAmount[$val['iVehicleTypeId']];
                                 $famount_val = (empty($fAmount_new)) ? round($fAmount_old, 2) : round($fAmount_new, 2);
                                 ?>
                                 <div class="hatchback-search" id="amt1_<?= $val['iVehicleTypeId'] ?>" <?php echo $p001; ?>>
                                    <input type="hidden" name="desc" id="desc_<?= $val['iVehicleTypeId'] ?>" value="<?= $val[$VehicleName1] ?>">
                                    <?php if ($val['eFareType'] != 'Regular') { ?>
                                    <span><?php echo $vSymbol; ?></span>
                                    <input class="form-control" type="text" name="fAmount[<?= $val['iVehicleTypeId'] ?>]" value="<?= $famount_val; ?>" placeholder="Enter Amount for <?= $val[$VehicleName1] ?>" id="fAmount_<?= $val['iVehicleTypeId'] ?>" maxlength="10">
                                    <span><?php echo $eFareType; ?></span>
                                 </div>
                              
                                 <label id="ServiceText_<?= $val['iVehicleTypeId'] ?>" <?php echo $p001Lbl; ?> class="ServiceText"><?= $langage_lbl['LBL_ENABLE_SERVICE_PRICE_TXT']; ?></label>
                                 <?php
                                 }
                              }else{?>
                                 <label id="defaultText_<?= $val['iVehicleTypeId'] ?>" ><?= $langage_lbl['LBL_PRICE_FEATURE_APPLICABLE_SERVICE_TXT']; ?></label>
                              <?php } ?>
                        
                           </div>
                        </div>
                     </li>
                     <?php } ?>                              
                     </ul>
                     </div>
                     <?php
               }
            }
         }
         if($emptySubCatData == '0'){ ?>
         <div> <?= $langage_lbl['LBL_NO_SERVICE_AVAIL_WEB']; ?></div>
         <?php }
         if($emptyVehicleType == '0') { ?>                                                  
         <div> <?= $langage_lbl['LBL_NO_SERVICE_AVAIL_WEB']; ?></div>
         <?php } ?>
         </div>  
         </div>
      <!-- </form> -->
      <?php } else { ?>
         <div class="add-car-services-hatch add-services-hatch add-services-taxi">                        
         <div class="card-block no-services-available"><div> <?= $langage_lbl['LBL_NO_SERVICE_AVAIL_WEB']; ?></div></div></div>
      <?php } ?>
      </div>
   </div>
   <div class="button-block">
      <input type="submit" class="save-vehicle gen-btn" name="submit1" id="submit1" value="<?= $langage_lbl['LBL_SUBMIT_BUTTON_TXT']; ?>" onclick="return check_empty();" style="margin: 0 15px 10px 0;">
      <!--<a href="#" onclick="add_driver_form();" class="gen-btn">Cancel</a>-->
   </div>    

</form>
<!--</section>-->