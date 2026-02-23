<?php
include_once('../common.php');

$eType = $_REQUEST['eType'] ?? '';

if($eType == "VideoConsult"){
    $permission_name = "update-providers-videoconsult-service-requests";
}else{
    $permission_name = "update-providers-on-demand-service-requests";
}

$did = base64_decode(base64_decode($_REQUEST['did']));
$sql = "SELECT vName,vLastName FROM register_driver WHERE iDriverId = " . $did;
$dDetails = $obj->MySQLSelect($sql);
$name = $dDetails[0]['vName'];
$vLastName = $dDetails[0]['vLastName'];
$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$editUrl = '';
$script = 'DriverRequest';
if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'Yes') {
    if ($eType == 'VideoConsult') {
        $script = 'DriverRequest_VideoConsult';
        $editUrl = '&eType=VideoConsult';
    }
}
$sql = "SELECT dsr.*  FROM register_driver AS rd 
        JOIN driver_service_request AS dsr ON dsr.iDriverId = rd.iDriverId 
        WHERE dsr.iDriverId = " . $did . " AND dsr.cRequestStatus = 'Pending'";
$Requests = $obj->MySQLSelect($sql);
if ($MODULES_OBJ->isEnableVideoConsultingService()) {
    $Requests_VC = $obj->MySQLSelect("SELECT dsr.iVehicleCategoryId, dsr.eVideoConsultEnableProvider, vc.vCategory_$default_lang as vCategory, vc1.vCategory_$default_lang as vParentCategory FROM driver_services_video_consult_charges as dsr LEFT JOIN vehicle_category as vc ON vc.iVehicleCategoryId = dsr.iVehicleCategoryId LEFT JOIN vehicle_category as vc1 ON vc1.iVehicleCategoryId = vc.iParentId WHERE dsr.iDriverId = $did AND dsr.eStatus = 'Pending' ");

    if ($THEME_OBJ->isCubeJekXv3ProThemeActive() == 'Yes') {
        if ($eType == 'VideoConsult') {
            $Requests = $Requests_VC;
	    $script = 'DriverRequest_VideoConsult';
			$editUrl = '&eType=VideoConsult';
        }
    }else {
     	//$Requests = array_merge($Requests, $Requests_VC);
	if ($eType == 'VideoConsult') {
            $Requests = $Requests_VC;
	   $script = 'DriverRequest_VideoConsult';
			$editUrl = '&eType=VideoConsult';
        } else {
	    $Requests = $Requests;
	}
    }
}
// echo "<pre>"; print_r($Requests); exit;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
if (isset($_POST['submit'])) {
    $status = $_POST['status'];
    $VehicleCatIds = $_POST['VehicleCatIds'];
    $VC_CatIds = $_POST['VC_CatIds'];
    $driverId = $_POST['driverId'];
    if (SITE_TYPE == 'Demo') {
        header("Location:action_driver_service_request.php?did=" . $_REQUEST['did'] . $editUrl . "&success=2");
        exit;
    }
    $sql = 'SELECT dv.vCarType, rd.vEmail, rd.vName ,rd.vLastName FROM driver_vehicle AS dv JOIN register_driver AS rd ON rd.iDriverId = dv.iDriverId WHERE dv.iDriverId = ' . $did . ' AND dv.vLicencePlate = "My Services"';
    $existingServicesdb = $obj->MySQLSelect($sql);
    // echo '<pre>';print_r($existingServices);echo '</pre>';die;
    $existingServices = explode(',', $existingServicesdb[0]['vCarType']);
    $rejectedServices = [];
    $newServices = [];
    $newServicesVC = [];
    $rejectedServicesVC = [];
    foreach ($status as $key => $value) {
        if ($status[$key] == 'Approve') {
            if (isset($VehicleCatIds[$key])) {
                $newServices[] = $VehicleCatIds[$key];
            }
            if (isset($VC_CatIds[$key])) {
                $newServicesVC[] = $VC_CatIds[$key];
            }
        }
        if ($status[$key] == 'Reject') {
            if (isset($VehicleCatIds[$key])) {
                $rejectedServices[] = $VehicleCatIds[$key];
            }
            if (isset($VC_CatIds[$key])) {
                $rejectedServicesVC[] = $VC_CatIds[$key];
            }
        }
    }
    $allServices = implode(',', array_merge($newServices, $existingServices));
    /*if(isset($_REQUEST['test'])){
        echo"<pre>";print_r($newServicesVC);print_R($rejectedServicesVC);die;
    }*/
    $sqlu = 'UPDATE driver_vehicle SET vCarType = "' . $allServices . '" WHERE iDriverId = "' . $did . '" AND vLicencePlate = "My Services"';
    $existingServices = $obj->sql_query($sqlu);
    if ($existingServices) {
        $rejectedNewServies = array_merge($newServices, $rejectedServices);
        if (!empty($rejectedNewServies)) {
            /* Delete Request as Its Processed */
            $sqlDel = 'DELETE FROM driver_service_request WHERE iDriverId = "' . $did . '" AND iVehicleCategoryId IN (' . implode(',', $rejectedNewServies) . ')';
            $obj->sql_query($sqlDel);
        }
        if (!empty($newServices) || !empty($rejectedServices)) {
            /* Send Email to Driver */
            $rejectedServicesArr = array();
            foreach ($rejectedServices as $k1 => $rval) {
                $sql = "SELECT vt.vVehicleType,vt.iVehicleCategoryId,vc.vCategory_" . $default_lang . " AS catName, vc1.vCategory_" . $default_lang . " AS pCate FROM vehicle_type AS vt  LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId   LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc1 ON vc1.iVehicleCategoryId = vc.iParentId  WHERE iVehicleTypeId = " . $rval;
                $rejectedServicesrecords = $obj->MySQLSelect($sql);
                $rejectedServicesArr[] = $rejectedServicesrecords[0]['catName']." - ".$rejectedServicesrecords[0]['vVehicleType'];
            }

            $newServicesArr = array();
            foreach ($newServices as $k1 => $nval) {
                $sql1 = "SELECT vt.vVehicleType,vt.iVehicleCategoryId,vc.vCategory_" . $default_lang . " AS catName, vc1.vCategory_" . $default_lang . " AS pCate FROM vehicle_type AS vt  LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId   LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc1 ON vc1.iVehicleCategoryId = vc.iParentId  WHERE iVehicleTypeId = " . $nval;
                $newServicesrecords = $obj->MySQLSelect($sql1);
                $newServicesArr[] = $newServicesrecords[0]['catName']." - ".$newServicesrecords[0]['vVehicleType'];
            }

            if(scount($newServicesArr) > 0){
                $getMaildata['vEmail'] = $existingServicesdb[0]['vEmail'];
                $getMaildata['FromName'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
                $getMaildata['EMAIL_NAME'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
                $getMaildata['serviceMsg'] = implode(", ", $newServicesArr);
                //$getMaildata['serviceMsg'] = $langage_lbl_admin['LBL_DRIVER_SERVICE_ACCEPTED_REJECT'];
                $mail = $COMM_MEDIA_OBJ->SendMailToMember('DRIVER_SERVICE_ACCEPTED_REJECT', $getMaildata);
            }

            if(scount($rejectedServicesArr) > 0){
                $getMaildata['vEmail'] = $existingServicesdb[0]['vEmail'];
                $getMaildata['FromName'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
                $getMaildata['EMAIL_NAME'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
                $getMaildata['serviceMsg'] = implode(", ", $rejectedServicesArr);
                $mail = $COMM_MEDIA_OBJ->SendMailToMember('DRIVER_SERVICE_REJECTED_BY_ADMIN', $getMaildata);
            }

        }
    }

    if (!empty($newServicesVC)) {
        foreach ($newServicesVC as $ServicesVC) {
            $obj->sql_query("UPDATE driver_services_video_consult_charges SET eStatus = 'Active', eApproved = 'Yes' WHERE iVehicleCategoryId = '$ServicesVC' AND iDriverId = '$did'");
        }

        $newServicesArrVC = array();
        foreach ($newServicesVC as $k1 => $nval) {
            $vc_data_1 = $obj->MySQLSelect("SELECT vc.vCategory_" . $default_lang . " as vCategory FROM vehicle_category vc WHERE iVehicleCategoryId = '$nval'");
            $newServicesArrVC[] = $vc_data_1[0]['vCategory'];
        }

        if(scount($newServicesArrVC) > 0){
            $getMaildata['vEmail'] = $existingServicesdb[0]['vEmail'];
            $getMaildata['FromName'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
            $getMaildata['EMAIL_NAME'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
            $getMaildata['serviceMsg'] = implode(", ", $newServicesArrVC);
            $mail = $COMM_MEDIA_OBJ->SendMailToMember('DRIVER_SERVICE_ACCEPTED_REJECT', $getMaildata);
        }
    }

    if (!empty($rejectedServicesVC)) {
        foreach ($rejectedServicesVC as $ServicesVC) {
            $obj->sql_query("UPDATE driver_services_video_consult_charges SET eStatus = 'Inactive', eApproved = 'No', eVideoConsultEnableProvider = 'No' WHERE iVehicleCategoryId = '$ServicesVC' AND iDriverId = '$did'");
        }

        $rejectedServicesArrVC = array();
        foreach ($rejectedServicesVC as $k1 => $nval) {
            $vc_data_1 = $obj->MySQLSelect("SELECT vc.vCategory_" . $default_lang . " as vCategory FROM vehicle_category vc WHERE iVehicleCategoryId = '$nval'");
            $rejectedServicesArrVC[] = $vc_data_1[0]['vCategory'];
        }

        if(scount($rejectedServicesArrVC) > 0){
            $getMaildata['vEmail'] = $existingServicesdb[0]['vEmail'];
            $getMaildata['FromName'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
            $getMaildata['EMAIL_NAME'] = $existingServicesdb[0]['vName'] . " " . $existingServicesdb[0]['vLastName'];
            $getMaildata['serviceMsg'] = implode(", ", $rejectedServicesArrVC);
            $mail = $COMM_MEDIA_OBJ->SendMailToMember('DRIVER_SERVICE_REJECTED_BY_ADMIN', $getMaildata);
        }
    }

    $_SESSION['success'] = '1';
    $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];

    header("location:driver_service_request.php" . str_replace('&', '?', $editUrl));
    exit;
}
$title = clearName($name . " " . $vLastName);
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Service Request for <?= $title ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style type="text/css">
        .service-table td label {
            font-weight: normal;
            cursor: pointer;
            margin: 0 0 5px 5px;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Service Request for <?= $title ?></h2>
                        <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                        <a class="back_link" href="driver_service_request.php<?= str_replace('&', '?', $editUrl) ?>">
                            <input type="button" value="Back to Listing" class="add-btn">
                        </a>
                    </div>
                </div>
                <hr/>
            </div>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export">
                            <form method="POST">
                                <input type='hidden' name="driverId" value="<?php echo $did; ?>">
                                <?php if ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable ">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x
                                        </button>
                                        <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                    </div>
                                    <!-- <br/> -->
                                <?php } ?>
                                <?php if (!empty($Requests)) {
                                    // echo '<pre>' ; print_r($Requests);
                                    ?>
                                    <table class="table table-striped table-bordered table-hover service-table">
                                        <thead>
                                        <tr>
											<th class="align-center">#</th>
                                            <th>Category</th>
                                            <th>Service Name</th>
                                            <th class="align-center">Requested Value</th>
                                            <th class="align-center">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($Requests as $key => $Request) {
                                            if (!isset($Request['eVideoConsultEnableProvider'])) {
                                                $sql = "SELECT vt.vVehicleType,vt.iVehicleCategoryId,vc.vCategory_" . $default_lang . " AS catName, vc1.vCategory_" . $default_lang . " AS pCate FROM vehicle_type AS vt
                                                                    LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId 
                                                                    LEFT JOIN " . $sql_vehicle_category_table_name . " AS vc1 ON vc1.iVehicleCategoryId = vc.iParentId 
                                                                    WHERE iVehicleTypeId = " . $Request['iVehicleCategoryId'];
                                                $existingServices = $obj->MySQLSelect($sql);
                                                // echo '<pre>' ; print_r($existingServices);
                                                echo '<tr>';
                                                echo '<td class="text-center">' . ($key + 1) . '</td>';
                                                echo '<td>' . $existingServices[0]['pCate'] . '</td>';
                                                echo '<td><strong>' . $existingServices[0]['catName'] . ' - ' . $existingServices[0]['vVehicleType'] . '</strong></td>';
                                                echo '<td class="text-center">Enable </td>';
                                                echo '<td class="text-left">
                                                                            <input type="radio" name="status[' . $key . ']" id="status1_[' . $key . ']" value="Pending" checked><label for="status1_[' . $key . ']">Pending </label><br>
                                                                            <input type="radio" name="status[' . $key . ']" id="status2_[' . $key . ']" value="Approve"><label for="status2_[' . $key . ']">Approve </label><br>
                                                                            <input type="radio" name="status[' . $key . ']" id="status3_[' . $key . ']" value="Reject"><label for="status3_[' . $key . ']">Reject </label>
                                                                            <input type="hidden" name="VehicleCatIds[]" value="' . $Request['iVehicleCategoryId'] . '">
                                                                            </td>';
                                                echo '</tr>';
                                            } else {
                                                echo '<tr>';
                                                echo '<td class="text-center">' . ($key + 1) . '</td>';
                                                echo '<td>' . $Request['vParentCategory'] . '</td>';
                                                echo '<td><strong>' . $Request['vCategory'] . ' (Video Consultation)</strong></td>';
                                                echo '<td class="text-center">Enable </td>';
                                                echo '<td class="text-left">
                                                                            <input type="radio" name="status[' . $key . ']" id="status1_[' . $key . ']" value="Pending" checked><label for="status1_[' . $key . ']">Pending </label><br>
                                                                            <input type="radio" name="status[' . $key . ']" id="status2_[' . $key . ']" value="Approve"><label for="status2_[' . $key . ']">Approve </label><br>
                                                                            <input type="radio" name="status[' . $key . ']" id="status3_[' . $key . ']" value="Reject"><label for="status3_[' . $key . ']">Reject </label>
                                                                            <input type="hidden" name="VC_CatIds[' . $key . ']" value="' . $Request['iVehicleCategoryId'] . '">
                                                                            </td>';
                                                echo '</tr>';
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php if ($userObj->hasPermission($permission_name)) { ?>

                                    <input type="submit" name="submit" value="Process Request" class="btn btn-primary">

                                    <?php } ?>
                                <?php } ?>
                            </form>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li> This module will list the details of all the services requested by the providers.</li>
                    <li> Administrator can take appropriate action (Approve , Reject , Pending).</li>
                    <li> Pending request will remain here, which the admin can approve or reject on later stage.</li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php
include_once('footer.php');
?>
<!-- END BODY-->
</html>