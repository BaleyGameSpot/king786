<?php
include_once('../common.php');



$start = @date("Y");
$end = '1970';

$tbl_name = 'driver_vehicle';
$tbl_name1 = 'service_pro_amount';
$script = 'Driver';

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

$sql = "select iDriverVehicleId from driver_vehicle where iDriverId = '" . $iDriverId . "' AND eType='UberX'";
$db_drv_veh = $obj->MySQLSelect($sql);

$id = isset($_POST['id']) ? $_POST['id'] : $db_drv_veh[0]['iDriverVehicleId'];
$action = ($id != '') ? 'Edit' : 'Add';

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$redirectToDocumentPage = isset($_POST['redirectToDocumentPage']) ? $_POST['redirectToDocumentPage'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '3';
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '1';
$fAmount = isset($_POST['fAmount']) ? $_POST['fAmount'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : Date('Y');
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$vCarType = isset($_POST['vCarType']) ? $_POST['vCarType'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$eType = isset($_POST['eType']) ? $_POST['eType'] : 'UberX';


$SearchService = isset($_REQUEST['SearchService']) ? $_REQUEST['SearchService'] : 0;
$isAjax = isset($_REQUEST['isAjax']) ? $_REQUEST['isAjax'] : 'No';
$sqlSearch = '';
if ($isAjax == 'Yes') {
    $search_keyword = $SearchService;
    $sqlSearch = " AND vt.vVehicleType LIKE '%" . $SearchService . "%'";
    $sqlSearchCat = " AND vc.vCategory_" . $_SESSION['sess_lang'] . " LIKE '%" . $SearchService . "%'";
}


$sql = "select * from driver_vehicle where iDriverVehicleId = '" . $id . "' ";
$db_mdl = $obj->MySQLSelect($sql);

$sql = "select iCompanyId from `register_driver` where iDriverId = '" . $iDriverId . "'";
$db_usr = $obj->MySQLSelect($sql);
$iCompanyId = $db_usr[0]['iCompanyId'];


$sql1 = "select Ratio,vName,vSymbol from currency where eDefault = 'Yes'";
$db_usrcurrency = $obj->MySQLSelect($sql1);
$vSymbol = $db_usrcurrency[0]['vSymbol'];
$ration = $db_usrcurrency[0]['Ratio'];

/* $sql = "SELECT * from make WHERE eStatus='Active' ORDER By vMake ASC";
  $db_make = $obj->MySQLSelect($sql);

  $sql = "SELECT * from company WHERE eStatus='Active'";
  $db_company = $obj->MySQLSelect($sql); */

if (!$userObj->hasPermission('manage-provider-services')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to manage service.';
    header("Location:driver.php?iDriverId=" . $iDriverId);
    exit;
}


if (isset($_POST['submit'])) {

    if (SITE_TYPE == 'Demo' && $id != '') {
        $_SESSION['success'] = 2;
        header("Location:driver.php?iDriverId=" . $iDriverId);
        exit;
    }



    require_once("library/validation.class.php");
    $validobj = new validation();


    //Commented By HJ On 10-12-2019 As Per Discuss With KS B'coz in app not checked this validation start
    /*if (empty($_REQUEST['vCarType'])) {
        $validobj->add_fields($_POST['vCarType'], 'req', 'You must select at least one service type.');
    }*/
    //Commented By HJ On 10-12-2019 As Per Discuss With KS B'coz in app not checked this validation End
    $error = $validobj->validate();

    if ($error) {
        $success = 3;
        $newError = $error;
        //exit;
    } else {

        if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
            $vLicencePlate = 'My Services';
        } else {
            $vLicencePlate = $vLicencePlate;
        }

        $q = "INSERT INTO ";
        $where = '';

        if ($action == 'Edit') {
            $str = ' ';
        } else {
            $eStatus = 'Active';
        }

        $cartype = implode(",", $_REQUEST['vCarType']);

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverId` = '" . $iDriverId . "' AND `iDriverVehicleId` = '" . $id . "'";
        }

        $query = $q . " `" . $tbl_name . "` SET
		`iModelId` = '" . $iModelId . "',
		`vLicencePlate` = '" . $vLicencePlate . "',
		`iYear` = '" . $iYear . "',
		`iMakeId` = '" . $iMakeId . "',
		`iCompanyId` = '" . $iCompanyId . "',
		`iDriverId` = '" . $iDriverId . "',
		`eStatus` = 'Active',
		`eType` = '" . $eType . "',
		`vCarType` = '" . $cartype . "' $str"
            . $where;

        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();

        if ($id != "" && $db_mdl[0]['eStatus'] != $eStatus) {
            if ($SEND_TAXI_EMAIL_ON_CHANGE == 'Yes') {
                $sql23 = "SELECT m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName
					FROM driver_vehicle dv, register_driver rd, make m, model md, company c
					WHERE dv.eStatus != 'Deleted' AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND dv.iDriverVehicleId = '" . $id . "'";
                $data_email_drv = $obj->MySQLSelect($sql23);
                $maildata['EMAIL'] = $data_email_drv[0]['vEmail'];
                $maildata['NAME'] = $data_email_drv[0]['vName'];
                $maildata['EMAIL_NAME'] = $data_email_drv[0]['vName'];
                $maildata['DETAIL'] = "Your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . " " . $data_email_drv[0]['vTitle'] . " For COMPANY " . $data_email_drv[0]['companyFirstName'] . " is temporarly " . $eStatus;
                $COMM_MEDIA_OBJ->SendMailToMember("ACCOUNT_STATUS", $maildata);
            }
        }

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
            $maildata['NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
            $maildata['EMAIL_NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
            $maildata['DETAIL'] = "Thanks for adding your " . $langage_lbl_admin['LBL_TEXI_ADMIN'] . ".<br />We will soon verify and check it's documentation and proceed ahead with activating your account.<br />We will notify you once your account become active and you can then take " . $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . " with " . $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] . ".";
            $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING", $maildata);
        }

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }

        if ($redirectToDocumentPage != "") {
            header("location:" . $redirectToDocumentPage);
        } else {
            header("location:" . $backlink);
        }
    }
}
$fAmount = array();
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT t.*,t1.fAmount,t1.iServProAmntId,t1.iVehicleTypeId AS `VehicleId`,t1.iDriverVehicleId AS `DriverVehilceId` FROM $tbl_name AS t LEFT JOIN $tbl_name1 t1 ON t.iDriverVehicleId=t1.iDriverVehicleId
			WHERE t.iDriverId = '" . $iDriverId . "' AND t.iDriverVehicleId = '" . $db_drv_veh[0]['iDriverVehicleId'] . "'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>";print_r($db_data);die;
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iMakeId = $value['iMakeId'];
            $iModelId = $value['iModelId'];
            $vLicencePlate = $value['vLicencePlate'];
            $iYear = $value['iYear'];
            $eCarX = $value['eCarX'];
            $eCarGo = $value['eCarGo'];
            $iDriverId = $value['iDriverId'];
            $vCarType = $value['vCarType'];
            $iCompanyId = $value['iCompanyId'];
            $eStatus = $value['eStatus'];
            $iDriverVehicleId = $value['iDriverVehicleId'];
            $eType = $value['eType'];
            $amt = $value['fAmount'] * $ration;
            $fAmount[$value['VehicleId']] = $amt;
            //echo "<pre>";print_r($fAmount);die;
        }
    }
}


$vCarTyp = explode(",", $vCarType);

//$Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;	
if ($APP_TYPE == 'Delivery') {
    $Vehicle_type_name = 'Deliver';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $Vehicle_type_name = 'UberX';
} else {
    $Vehicle_type_name = $APP_TYPE;
}

if ($Vehicle_type_name == "Ride-Delivery") {
    $vehicle_type_sql = "SELECT * FROM  vehicle_type WHERE(eType ='Ride' OR eType ='Deliver') AND iCountryId='-1'";
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
        $getvehiclecat = "SELECT vc.iVehicleCategoryId, vc.vCategory_EN AS main_cat FROM " . $sql_vehicle_category_table_name . " AS vc WHERE vc.eStatus='Active' AND vc.iParentId='0' $whereParentId";
        $vehicle_type_data = $obj->MySQLSelect($getvehiclecat);
        $i = 0;
        foreach ($vehicle_type_data as $key => $val) {
            $vehicle_type_sql = "SELECT vc.eVideoConsultEnable,vt.vVehicleType,vc.iParentId,vc.vCategory_" . $_SESSION['sess_lang'] . ",vc.iVehicleCategoryId from  vehicle_type as vt  left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId where vt.eType='" . $Vehicle_type_name . "' AND vc.iParentId ='" . $val['iVehicleCategoryId'] . "'  AND vc.eStatus='Active' GROUP BY vc.iVehicleCategoryId";
            $vehicle_type_dataOld = $obj->MySQLSelect($vehicle_type_sql);
            $vehicle_type_data[$i]['SubCategory'] = $vehicle_type_dataOld;
            $j = 0;
            foreach ($vehicle_type_dataOld as $subkey => $subvalue) {
                $vehicle_type_sql1 = "SELECT vt.*,vc.*,lm.vLocationName FROM vehicle_type AS vt LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc ON vt.iVehicleCategoryId = vc.iVehicleCategoryId LEFT JOIN location_master AS lm ON lm.iLocationId = vt.iLocationid WHERE vt.eType='" . $Vehicle_type_name . "' AND vc.iVehicleCategoryId = '" . $subvalue['iVehicleCategoryId'] . "' AND (lm.iCountryId='" . $iCountryId . "' || vt.iLocationid='-1') AND vt.eStatus='Active'";
                $vehicle_type_dataNew = $obj->MySQLSelect($vehicle_type_sql1);
                $vehicle_type_data[$i]['SubCategory'][$j]['VehicleType'] = $vehicle_type_dataNew;
                $j++;
            }

            $i++;
        }
    } else {
        $vehicle_type_sql = "SELECT * FROM vehicle_type WHERE eType='" . $Vehicle_type_name . "' AND iCountryId='-1'";
        $vehicle_type_data = $obj->MySQLSelect($vehicle_type_sql);
    }
}

if (!empty($search_keyword) && $MODULES_OBJ->isEnableSearchUfxServices()) {
    $vehicleCategoryData = $vehicle_type_data;
    //print_R($vehicleCategoryData);
    foreach ($vehicleCategoryData as $key => $value) {
        $main_cat = $subcat = $subcattype = $mainsubcat = 0;
        //print_R($value); exit;
        if (stripos($value['main_cat'], $search_keyword) !== false) {
            $main_cat = 1;
        }
        //print_R($value['SubCategory']); exit;
        if (isset($value['SubCategory']) && $main_cat == 0) {

            foreach ($value['SubCategory'] as $skey => $sCategory) {

                if (stripos($sCategory['vCategory_' . $_SESSION['sess_lang']], $search_keyword) !== false) {
                    $subcat = 1;
                } else {
                    $subcattype = 0;

                    foreach ($sCategory['VehicleType'] as $skeyType => $sCategoryType) {
                        if (stripos($sCategoryType['vVehicleType_' . $_SESSION['sess_lang']], $search_keyword) !== false) {
                            $subcattype = 1;
                            $mainsubcat = 1;
                        } else {
                            unset($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType'][$skeyType]);
                        }
                    }


                    if (!empty($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType'])) {
                        $vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType'] = array_values($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType']);
                    }

                    if (empty($vehicleCategoryData[$key]['SubCategory'][$skey]['VehicleType']) && $subcattype == 0) {
                        unset($vehicleCategoryData[$key]['SubCategory'][$skey]);
                    }
                }
            }

            if (!empty($vehicleCategoryData[$key]['SubCategory'])) {
                $vehicleCategoryData[$key]['SubCategory'] = array_values($vehicleCategoryData[$key]['SubCategory']);
            }
        }

        if (($main_cat == 0 && $subcat == 0 && $mainsubcat == 0) || empty($vehicleCategoryData[$key]['SubCategory'])) {

            unset($vehicleCategoryData[$key]);
        }
    }
    $vehicle_type_data = $vehicleCategoryData;
}
$isEnableServiceTypeWiseProviderDocument  = $MODULES_OBJ->isEnableServiceTypeWiseProviderDocument();
?>
<form name="vehicle_form" id="vehicle_form" method="post" action="">
    <input type="hidden" name="iDriverId" value="<?= $iDriverId ?>" />
    <input type="hidden" name="iCompanyId" value="<?= $iCompanyId ?>" />
    <input type="hidden" name="iMakeId" value="<?= $iMakeId ?>" />
    <input type="hidden" name="iModelId" value="<?= $iModelId ?>" />
    <input type="hidden" name="iYear" value="<?= $iYear ?>" />
    <input type="hidden" name="vLicencePlate" value="<?= $vLicencePlate; ?>" />
    <input type="hidden" name="id" value="<?= $iDriverVehicleId; ?>" />
    <input type="hidden" name="eType" value="<?= $eType; ?>" />
    <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>" />
    <input type="hidden" name="backlink" id="backlink" value="driver.php" />
    <input type="hidden" name="redirectToDocumentPage" id="redirectToDocumentPage" value="<?= $redirectToDocumentPage; ?>" />
    <!--<div class="row"> 
        <div class="col-lg-12">
            <label><?= $langage_lbl_admin['LBL_SERVICE_ADMIN']; ?> Type <span class="red">*</span></label>
        </div>
    </div>-->
    <div class="checkbox-group required add-services-hatch car-type-custom col-md-12 pull-left" style="margin-top: 20px">
        <ul style="padding-left: 0;">
            <?php
            $emptySubCatData = '0';
            foreach ($vehicle_type_data as $key => $value) {
                if (scount($value['SubCategory']) > 0) { // condition added NM "no service available issue"
                    $emptySubCatData = empty($value['SubCategory']) ? '0' : '1 ';
                }
                foreach ($value['SubCategory'] as $Vehicle_Type) {
                    if (!empty($Vehicle_Type['VehicleType'])) {
            ?>
                        <?php
                        if ($Vehicle_type_name == 'UberX') {
                            $vname = $Vehicle_Type['vCategory_' . $_SESSION['sess_lang']];
                            $vehicle_Name = $Vehicle_Type['vVehicleType'];
                        } else {
                            $vname = $Vehicle_Type['vVehicleType'];
                        }
                        $iParentcatId = $Vehicle_Type['iParentId'];
                        $sql_query = "SELECT ePriceType FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId = '" . $iParentcatId . "' ";
                        $ePricetype_data = $obj->MySQLSelect($sql_query);
                        $ePricetype = $ePricetype_data[0]['ePriceType'];
                        ?>
                        <div class="main-cat">
                            <?php echo $value['main_cat'] . " - " . $vname; ?>
                            <?php if ($MODULES_OBJ->isEnableVideoConsultingService() && $Vehicle_Type['eVideoConsultEnable'] == 'Yes') { ?>
                                <div class="later-clock left" onclick="manageVideoConsultService('<?= $Vehicle_Type['iVehicleCategoryId'] ?>', '<?= $value['main_cat'] . ' - ' . $vname ?>')">
                                    <button type="button" class="add-btn"> Manage Video Consult Service Charge</button>
                                </div>
                            <?php } ?>
                        </div>
                        <fieldset>
                            <?php
                            foreach ($Vehicle_Type['VehicleType'] as $val) {
                                if ($val['eFareType'] == 'Fixed') {
                                    $eFareType = 'Fixed';
                                    $fAmount_old = $val['fFixedFare'] * $ration;
                                } else if ($val['eFareType'] == 'Hourly') {
                                    $eFareType = 'Per hour';
                                    $fAmount_old = $val['fPricePerHour'] * $ration;
                                } else {
                                    $eFareType = '';
                                    $fAmount_old = '';
                                }
                                $vehicle_typeName = $val['vVehicleType_' . $_SESSION['sess_lang']];

                                if (!empty($val['vLocationName'])) {
                                    $localization = '(Location : ' . $val["vLocationName"] . ')';
                                } else {
                                    $localization = '';
                                }
                            ?>
                                <li style="list-style: outside none none;">
                                    <b><?php echo $vehicle_typeName; ?><br />
                                        <span style="font-size: 12px;"><?php echo $localization; ?></span>
                                    </b>
                                    <div class="make-switch" data-on="success" data-off="warning">
                                        <input type="checkbox" class="chk vCarTypeClass" name="vCarType[]" id="vCarType_<?= $val['iVehicleTypeId'] ?>" <?php if ($ePricetype == "Provider") { ?>onchange="check_box_value(this.value);" <?php } ?> <?php if (in_array($val['iVehicleTypeId'], $vCarTyp)) { ?>checked<?php } ?> value="<?= $val['iVehicleTypeId'] ?>" />
                                    </div>
                                    <?php
                                    if ($ePricetype == "Provider") {
                                        $p001 = "style='display:none;'";
                                        if (in_array($val['iVehicleTypeId'], $vCarTyp)) {
                                            $p001 = "style='display:block;'";
                                        }
                                        $fAmount_new = $fAmount[$val['iVehicleTypeId']];
                                        $famount_val = (empty($fAmount_new)) ? round($fAmount_old, 2) : round($fAmount_new, 2);
                                    ?>
                                        <div class="hatchback-search" id="amt1_<?= $val['iVehicleTypeId'] ?>" <?php echo $p001; ?>>
                                            <input type="hidden" name="desc" id="desc_<?= $val['iVehicleTypeId'] ?>" value="<?= $val['vVehicleType_' . $default_lang] ?>">
                                            <?php if ($val['eFareType'] != 'Regular') { ?>
                                                <label class="fare_type" style="margin-right:5px;"><?php echo $vSymbol; ?></label>
                                                <input class="form-control" type="text" name="fAmount[<?= $val['iVehicleTypeId'] ?>]" value="<?= $famount_val; ?>" placeholder="Enter Amount for <?= $val['vVehicleType_' . $default_lang] ?>" id="fAmount_<?= $val['iVehicleTypeId'] ?>" maxlength="10"><label class="fare_type"><?php echo $eFareType; ?></label>
                                        </div>
                                <?php
                                            }
                                        }
                                ?>
                                </li>
                            <?php } ?>
                        </fieldset>
            <?php
                    }
                }
            }
            ?>
        </ul>
        <?php if ($emptySubCatData == '0') { ?>
            <div style="margin-bottom: 35px;font-size: 16px;"> <?= $langage_lbl_admin['LBL_NO_SERVICE_AVAIL_WEB']; ?></div>
        <?php } ?>
    </div>
    <div class="row" style="display: none;">
        <div class="col-lg-12">
            <label>Status</label>
        </div>
        <div class="col-lg-6">
            <div class="make-switch" data-on="success" data-off="warning">
                <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="row">
        <div class="col-lg-12">
            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action . " " . $langage_lbl_admin['LBL_SERVICE_ADMIN']; ?>" onclick="return check_empty();">
            <a href="javascript:void(0);" onclick="reset_form('vehicle_form');" class="btn btn-default">Reset</a>
            <a href="vehicles.php" class="btn btn-default back_link">Cancel</a>
        </div>
    </div>
</form>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>