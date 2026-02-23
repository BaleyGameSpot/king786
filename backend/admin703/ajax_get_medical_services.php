<?php
include_once '../common.php';

$eMedicalServiceCat = isset($_REQUEST['eMedicalServiceCat']) ? $_REQUEST['eMedicalServiceCat'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'GET';

if($action == "STATUS") {
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'Active';
    $obj->sql_query("UPDATE $master_service_category_tbl SET tCategoryDetails = JSON_SET(tCategoryDetails, '$." . $eMedicalServiceCat . ".eStatus', '$status') WHERE eType = 'MedicalServices'");

    if(strtoupper(ONLY_MEDICAL_SERVICE) == "YES") {
        $eServiceType = $eMedicalServiceCat;
        if($eMedicalServiceCat == "BookService") {
            $eServiceType = 'UberX';
        } elseif ($eMedicalServiceCat == "MoreService") {
            $eServiceType = 'Other';
        }
        $obj->sql_query("UPDATE app_home_screen_view SET eStatus = '$status' WHERE eServiceType = '$eServiceType'");
    }
    $oCache->flushData();
    echo "success";
    exit;
}
$ord = "";
$ssql = "";
if($eMedicalServiceCat == "BookService") {
    $ord = " ORDER BY vc.eCatType DESC";
} elseif ($eMedicalServiceCat == "MoreService") {
    $ord = " ORDER BY vc.eCatType ASC";
} else {
    $ssql = " AND eVideoConsultEnable = 'Yes' ";
}

if(!$MODULES_OBJ->isRideFeatureAvailable('Yes')) {
    $ssql .= " AND vc.iVehicleCategoryId != '326' ";
}

if(!$MODULES_OBJ->isDeliverAllFeatureAvailable('Yes')) {
    $ssql .= " AND vc.iServiceId != '5' ";
}

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$meds_data = $obj->MySQLSelect("SELECT vc.iParentId,vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo, vc.vListLogo1,vc.vListLogo2,vc.vCategory_" . $default_lang . " as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType,vc.eForMedicalService, vc.eVideoConsultEnable, vc.tMedicalServiceInfo, (select count(iVehicleCategoryId) from " . $sql_vehicle_category_table_name . " where iParentId = vc.iVehicleCategoryId AND eStatus != 'Deleted') as SubCategories FROM " . $sql_vehicle_category_table_name . " as vc WHERE eStatus != 'Deleted' AND (vc.iParentId='0' OR vc.iParentId = '3') AND vc.eForMedicalService = 'Yes' AND vc.iVehicleCategoryId != 3 $ssql $ord");

if($action == "GET") {
    $medicalServiceHtml = "";
    foreach ($meds_data as $medical_service) {
        $checked = "";
        
        if(!empty($medical_service['tMedicalServiceInfo'])) {
            $tMedicalServiceInfoArr = json_decode($medical_service['tMedicalServiceInfo'], true);
            
            if($eMedicalServiceCat == "BookService") {
                if($tMedicalServiceInfoArr['BookService'] == "Yes") {
                    $checked = 'checked="checked"';    
                }
                
                $display_order = $tMedicalServiceInfoArr['iDisplayOrderBS'];
            }
            if($eMedicalServiceCat == "MoreService") {
                if($tMedicalServiceInfoArr['MoreService'] == "Yes") {
                    $checked = 'checked="checked"';    
                }
                
                $display_order = $tMedicalServiceInfoArr['iDisplayOrderMS'];
            }
            if($eMedicalServiceCat == "VideoConsult") {
                if($tMedicalServiceInfoArr['VideoConsult'] == "Yes") {
                    $checked = 'checked="checked"';    
                }
                
                $display_order = $tMedicalServiceInfoArr['iDisplayOrderVC'];
            }
        }

        $select_options = "";
        for($i = 1; $i <= scount($meds_data); $i++) {
            $select_options .= '<option value="' . $i . '" ' . ($i == $display_order ? 'selected' : ''). '>' . $i . '</option>';
        }

        $medicalServiceHtml .= '<tr><td>' . $medical_service['vCategory'] . '</td><td><select class="form-control" name="ms_display_order[]" >' . $select_options . '</select></td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryId[]" value="' . $medical_service['iVehicleCategoryId'] . '" ' . $checked . ' /></div></div></td>';
    }

    echo $medicalServiceHtml;
    exit;
} else {
    $iVehicleCategoryIdArr = isset($_REQUEST['iVehicleCategoryIdArr']) ? $_REQUEST['iVehicleCategoryIdArr'] : '';
    $iDisplayOrderArr = isset($_REQUEST['iDisplayOrderArr']) ? $_REQUEST['iDisplayOrderArr'] : '';

    $iVehicleCategoryIds = array_column($meds_data, 'iVehicleCategoryId');
    
    $iVehicleCategoryIdArr = explode(",", $iVehicleCategoryIdArr);
    $iDisplayOrderArr = explode(",", $iDisplayOrderArr);

    foreach ($meds_data as $k => $medical_service) {
        if(!empty($medical_service['tMedicalServiceInfo'])) {
            $tMedicalServiceInfoArr = json_decode($medical_service['tMedicalServiceInfo'], true);
            if($eMedicalServiceCat == "BookService") {
                if(!in_array($medical_service['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
                    $tMedicalServiceInfoArr['BookService'] = "No";    
                } else {
                    $tMedicalServiceInfoArr['BookService'] = "Yes";    
                }
                $tMedicalServiceInfoArr['iDisplayOrderBS'] = $iDisplayOrderArr[$k];
            } elseif ($eMedicalServiceCat == "MoreService") {
                if(!in_array($medical_service['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
                    $tMedicalServiceInfoArr['MoreService'] = "No";
                } else {
                    $tMedicalServiceInfoArr['MoreService'] = "Yes";    
                }
                $tMedicalServiceInfoArr['iDisplayOrderMS'] = $iDisplayOrderArr[$k];
            } else {
                if(!in_array($medical_service['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
                    $tMedicalServiceInfoArr['VideoConsult'] = "No";
                } else {
                    $tMedicalServiceInfoArr['VideoConsult'] = "Yes";    
                }
                $tMedicalServiceInfoArr['iDisplayOrderVC'] = $iDisplayOrderArr[$k];
            }
        } else {
            $tMedicalServiceInfoArr = array(
                'BookService'     => "No",
                'MoreService'     => "No",
                'VideoConsult'    => "No",
                'iDisplayOrderBS' => 1,
                'iDisplayOrderMS' => 1,
                'iDisplayOrderVC' => 1,
            );
        }

        $tMedicalServiceInfoJson = json_encode($tMedicalServiceInfoArr);
        $obj->sql_query("UPDATE $sql_vehicle_category_table_name SET tMedicalServiceInfo = '$tMedicalServiceInfoJson' WHERE iVehicleCategoryId = '" . $medical_service['iVehicleCategoryId'] . "'");
    }

    $returnArr['Action'] = "1";
    setDataResponse($returnArr);
    exit;
}

?>