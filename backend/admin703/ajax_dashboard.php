<?php
include_once('../common.php');

$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';

session_write_close();
$chart_type = (isset($_REQUEST['chart_type']) && !empty($_REQUEST['chart_type'])) ? $_REQUEST['chart_type'] : "";
$year = (isset($_REQUEST['year']) && !empty($_REQUEST['year'])) ? $_REQUEST['year'] : date('Y');
$getMonth = (isset($_REQUEST['getMonth']) && !empty($_REQUEST['getMonth'])) ? $_REQUEST['getMonth'] : 0;

if ($chart_type == "Earning_Report") {
    $earning_month = [];
    $total_Earns = [];

    for ($i = 1; $i <= $getMonth; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $month = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($month."-".$year));

        $earning_month[] = date('M', strtotime($startdate));
        if ($onlyRideShareEnable == 'Yes') {
            $total_Earns[] = getRideShareTotalEarns($startdate, $enddate);
        } else {
            $total_Earns[] = getTotalEarns($startdate, $enddate);
        }
    }

    $data['earning_month'] = $earning_month;
    $data['total_Earns'] = $total_Earns;

}
elseif ($chart_type == "Earning_Report_six") {
    $earning_month = [];
    $total_Earns = [];

    $getSetupInfo = $DASHBOARD_OBJ->getSetupInfo();

    for ($i = $getMonth - 1; $i >= 0; $i--) {
        $startdate = date('Y-m-01 00:00:00', strtotime("-$i month"));
        $enddate = date('Y-m-t 23:59:59', strtotime("-$i month"));

        $earning_month[] = date('Y-M', strtotime("-$i month"));
        if (ONLYDELIVERALL == "Yes") { 
            $total_Earns[] = getStoreTotalEarns($startdate, $enddate);
        } else if ($onlyRideShareEnable == 'Yes') {
             $total_Earns[] = getRideShareTotalEarns($startdate, $enddate);
        }else {
            $total_Earns[] = getTotalEarns($startdate, $enddate);
        }
    }    

    $data['earning_month'] = $earning_month;
    $data['total_Earns'] = $total_Earns;

}
elseif ($chart_type == "Total_Ride_jobs") {
    $month = [];
    $finishRidetotalByMonth = [];
    $cancelledRidetotalByMonth = [];

    for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $currentmonth = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($currentmonth."-".$year));

        $month[] = date('M', strtotime($startdate));
        $cancelledRidetotalByMonth[] = getTripStates('cancelled', $startdate, $enddate, '1');
        $finishRidetotalByMonth[] = getTripStates('finished', $startdate, $enddate, '1');
    }
    $data['month'] = $month;
    $data['cancelledRidetotalByMonth'] = $cancelledRidetotalByMonth;
    $data['finishRidetotalByMonth'] = $finishRidetotalByMonth;

}
elseif ($chart_type == "Total_Order") {
    $order_month = [];
    $finishOrdertotalByMonth = [];
    $cancelledOrdertotalByMonth = [];

    for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $month = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($month."-".$year));

        $order_month[] = date('M', strtotime($startdate));
        $cancelledOrdertotalByMonth[] = getStoreTripStates('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getStoreTripStates('Delivered', $startdate, $enddate, '1');

    }

    $data['order_month'] = $order_month;
    $data['cancelledOrdertotalByMonth'] = $cancelledOrdertotalByMonth;
    $data['finishOrdertotalByMonth'] = $finishOrdertotalByMonth;

}
elseif ($chart_type == "user_and_provider") {
    for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $currentmonth = date('F', $getMonthTimeStamp);
        
        $startdate = date($year . '-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($currentmonth."-".$year));

        $months[] = date('M', strtotime($startdate));
        $getRiderCount = getRiderCount('finished', $startdate, $enddate, '1');
        $user[] = $getRiderCount[0]['count(iUserId)'];
        $provider[] = getDriverDetailsDashboard('active',$startdate, $enddate);
        $store[] = getStoreDetailsDashboard('active',$startdate, $enddate);
    }
    
    $data['months'] = $months;
    $data['user'] = $user;
    $data['provider'] = $provider;
    $data['store'] = $store;

}
elseif ($chart_type == "server_status_chart") {
    $working = 0;
    $missing = 0;
    $server_settings = $DASHBOARD_OBJ->server_settings();
    $phpini_settings = $DASHBOARD_OBJ->phpini_settings();
    $php_modules = $DASHBOARD_OBJ->php_modules();
    $mysql_settings = $DASHBOARD_OBJ->mysql_settings();
    $mysql_suggestions = $DASHBOARD_OBJ->mysql_suggestions();
    $folder_permissions = $DASHBOARD_OBJ->folder_permissions();
    
    if($server_settings == 1) {
        $working += 1;
    } else {
        $missing += 1;
    }
    if($phpini_settings == 1) {
        $working += 1;
    } else {
        $missing += 1;
    }
    if($php_modules == 1) {
        $working += 1;
    } else {
        $missing += 1;
    }
    if($mysql_settings == 1) {
        $working += 1;
    } else {
        $missing += 1;
    }
    if($mysql_suggestions == 1) {
        $working += 1;
    } else {
        $missing += 1;
    }
    if($folder_permissions == 1) {
        $working += 1;
    } else {
        $missing += 1;
    }
    
    $data['status'] = ['working','missing'];
    $data['number'] = [$working,$missing];

    $data['working'] = 6;
    $data['missing'] = 3;

}
elseif ($chart_type == "GodsView") {
    $eServiceStatus = (isset($_REQUEST['eServiceStatus'])) ? $_REQUEST['eServiceStatus'] : "";
    $eServiceType = (isset($_REQUEST['eServiceType'])) ? $_REQUEST['eServiceType'] : "";

    $makeDataArr = $modelDataArr = $driverVehicleArr = array();

    $makeData = $obj->MySQLSelect("SELECT iMakeId, vMake FROM make");
    foreach ($makeData as $make) {
        $makeDataArr[$make['iMakeId']] = $make['vMake'];
    }
    $modelData = $obj->MySQLSelect("SELECT iModelId, vTitle FROM model");
    foreach ($modelData as $model) {
        $modelDataArr[$model['iModelId']] = $model['vTitle'];
    }
    
    if($eServiceType == "Ride") {
        $vehicle_type_ride = $obj->MySQLSelect("SELECT GROUP_CONCAT(iVehicleTypeId) as iVehicleTypeIds FROM vehicle_type WHERE eType = 'Ride' AND eStatus = 'Active' ");
        $iVehicleTypeIdArr = explode(",", $vehicle_type_ride[0]['iVehicleTypeIds']);
       /* $sql = '';
        if(isset($iVehicleTypeIdArr) && !empty($iVehicleTypeIdArr))
        {
            $sql = 'AND ( ';
            $i = 0;
            foreach($iVehicleTypeIdArr as $arr)
            {
                if($i != 0)
                {
                    $sql .= ' OR ';

                }
                $sql .= " FIND_IN_SET('".$arr."', vCarType) ";
                $i++;
            }

            $sql .= ')';
        }

        $driver_vehicle_data = $obj->MySQLSelect("SELECT iDriverVehicleId, iDriverId, vCarType, iMakeId, iModelId, vLicencePlate FROM driver_vehicle WHERE eStatus = 'Active' $sql ");
        
        foreach ($driver_vehicle_data as $driver_vehicle) {
            $driverVehicleArr[$driver_vehicle['iDriverId']][$driver_vehicle['iDriverVehicleId']] = $driver_vehicle;
        }*/

        $driver_fulldata = $obj->MySQLSelect("SELECT dv.iDriverVehicleId, dv.iDriverId, dv.vCarType, dv.iMakeId, dv.iModelId, dv.vLicencePlate FROM driver_vehicle as dv left join register_driver rd on rd.iDriverId=dv.iDriverId WHERE dv.eStatus = 'Active' AND  rd.eStatus = 'Active' AND  dv.eType!= 'UberX'");

        foreach ($driver_fulldata as $driver_vehicle) {
            $job_services=array();
            $driver_vehicle_type= explode(",", $driver_vehicle['vCarType']);
            $job_services = array_intersect($driver_vehicle_type, $iVehicleTypeIdArr);
            $counter=scount($job_services);
            $job_services=implode(",",$job_services);

            $driver_vehicle['vCarType']='';
            if(!empty($counter) && $counter>0){
                $driver_vehicle['vCarType']=$job_services;
                $driverVehicleArr[$driver_vehicle['iDriverId']][$driver_vehicle['iDriverVehicleId']] = $driver_vehicle;
            }

        }

    } elseif ($eServiceType == "Delivery") {
        $vehicle_type_ride = $obj->MySQLSelect("SELECT GROUP_CONCAT(iVehicleTypeId) as iVehicleTypeIds FROM vehicle_type WHERE eType IN ('Deliver', 'DeliverAll') AND eStatus = 'Active' ");
        $iVehicleTypeIdArr = explode(",", $vehicle_type_ride[0]['iVehicleTypeIds']);

        $driver_fulldata = $obj->MySQLSelect("SELECT dv.iDriverVehicleId, dv.iDriverId, dv.vCarType, dv.iMakeId, dv.iModelId, dv.vLicencePlate FROM driver_vehicle as dv left join register_driver rd on rd.iDriverId=dv.iDriverId WHERE dv.eStatus = 'Active' AND  rd.eStatus = 'Active' AND  dv.eType!= 'UberX'");

        foreach ($driver_fulldata as $driver_vehicle) {
            $job_services=array();
            $driver_vehicle_type= explode(",", $driver_vehicle['vCarType']);
            $job_services = array_intersect($driver_vehicle_type, $iVehicleTypeIdArr);
            $counter=scount($job_services);
            $job_services=implode(",",$job_services);

            $driver_vehicle['vCarType']='';
            if(!empty($counter) && $counter>0){
                $driver_vehicle['vCarType']=$job_services;
            $driverVehicleArr[$driver_vehicle['iDriverId']][$driver_vehicle['iDriverVehicleId']] = $driver_vehicle;
        }

        }

    }
    elseif ($eServiceType == "Job") {
        $vehicle_type_ride = $obj->MySQLSelect("SELECT GROUP_CONCAT(iVehicleTypeId) as iVehicleTypeIds FROM vehicle_type WHERE eType = 'UberX' AND eStatus = 'Active' ");
        $iVehicleTypeIdArr = explode(",", $vehicle_type_ride[0]['iVehicleTypeIds']);

        $driver_fulldata = $obj->MySQLSelect("SELECT dv.iDriverVehicleId,dv.iDriverId,dv.vCarType FROM driver_vehicle as dv left join register_driver rd on rd.iDriverId=dv.iDriverId WHERE dv.eStatus = 'Active' AND rd.eStatus = 'Active' AND  dv.eType= 'UberX'");

        foreach ($driver_fulldata as $driver_vehicle) {
            $job_services=array();
            $driver_vehicle_type= explode(",", $driver_vehicle['vCarType']);
            $job_services = array_intersect($driver_vehicle_type, $iVehicleTypeIdArr);
            $counter=scount($job_services);
            $job_services=implode(",",$job_services);

            $driver_vehicle['vCarType']='';
            if(!empty($counter) && $counter>0){
                $driver_vehicle['vCarType']=$job_services;
                $driverVehicleArr[$driver_vehicle['iDriverId']] = $driver_vehicle;
            }

        }
    }

    $tripData = $obj->MySQLSelect("SELECT iTripId, iActive, iDriverId, eType, eSystem FROM trips WHERE iActive IN ('Active', 'Arrived', 'On Going Trip')");
    $tripArr = array();
    foreach ($tripData as $trip) {
        $tripArr[$trip['iTripId']] = $trip;
    }

    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + INTERVAL_SECONDS) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));

    $ssql = "";

    $ssql_vehicle = " AND iDriverVehicleId > 0";
    if ($eServiceType == "Job") {
        $ssql_vehicle = "";
    }
    $providers = $obj->MySQLSelect("SELECT iDriverId, vLatitude, vLongitude, iDriverVehicleId, vAvailability, vTripStatus, iTripId, CONCAT(vName, ' ', vLastName) as name, vEmail, vCode, vPhone, vImage, tLocationUpdateDate FROM register_driver WHERE eStatus = 'Active' AND vLatitude NOT IN ('', 'undefined') AND vLongitude NOT IN ('', 'undefined') $ssql $ssql_vehicle");

    $providersArr = array();
    $all_count = $available_count = $not_available_count = $pickup_count = $dropoff_count = $arrived_count = 0;
    foreach ($providers as $provider) {
        if ($eServiceType == "Job") {
            if(isset($driverVehicleArr[$provider['iDriverId']]) && !empty($driverVehicleArr[$provider['iDriverId']])) {
                $driver_vehicle = explode(",", $driverVehicleArr[$provider['iDriverId']]['vCarType']);
                $job_services = array_intersect($driver_vehicle, $iVehicleTypeIdArr);
                if(scount($job_services) > 0) {
                    if(in_array($provider['vTripStatus'], ['Active', 'Arrived', 'On Going Trip']) && $tripArr[$provider['iTripId']]['eType'] != "UberX") {
                        continue;
                    }

                    $all_count++;

                    $dataArr = array();
                    $dataArr['iDriverId'] = $provider['iDriverId'];
                    $dataArr['ServiceStatusIcon'] = "all";
                    $dataArr['ServiceStatusTitle'] = "All";
                    $dataArr['map_icon'] = $tconfig['tsite_url_main_admin'] . 'img/map_icons/all-sp.png';
                    if($provider['vAvailability'] == "Available" && strtotime($provider['tLocationUpdateDate']) > strtotime($str_date)) {
                        $dataArr['map_icon'] = $tconfig['tsite_url_main_admin'] . 'img/map_icons/available-sp.png';
                        $dataArr['ServiceStatusIcon'] = "available";
                        $dataArr['ServiceStatusTitle'] = "Available";
                        $available_count++;

                    } elseif ($provider['vTripStatus'] == "Active") {
                        $dataArr['map_icon'] = $tconfig['tsite_url_main_admin'] . 'img/map_icons/enroute-pickup-sp.png';
                        $dataArr['ServiceStatusIcon'] = "pickup";
                        $dataArr['ServiceStatusTitle'] = "Way to Pickup";
                        $pickup_count++;

                    } elseif ($provider['vTripStatus'] == "Arrived") {
                        $dataArr['map_icon'] = $tconfig['tsite_url_main_admin'] . 'img/map_icons/arrived-sp.png';
                        $dataArr['ServiceStatusIcon'] = "arrived";
                        $dataArr['ServiceStatusTitle'] = "Arrived";
                        $arrived_count++;

                    } elseif ($provider['vTripStatus'] == "On Going Trip") {
                        $dataArr['map_icon'] = $tconfig['tsite_url_main_admin'] . 'img/map_icons/ongoing-sp.png';
                        $dataArr['ServiceStatusIcon'] = "ongoing";
                        $dataArr['ServiceStatusTitle'] = "Way to Dropoff";
                        $dropoff_count++;

                    } elseif ($provider['vAvailability'] == "Not Available" && ($provider['vTripStatus'] == "Not Active" || $provider['vTripStatus'] == "NONE" || $provider['vTripStatus'] == "Cancelled")) {
                        $dataArr['map_icon'] = $tconfig['tsite_url_main_admin'] . 'img/map_icons/all-sp.png';
                        $dataArr['ServiceStatusIcon'] = "all";
                        $dataArr['ServiceStatusTitle'] = "Not Available";
                        $not_available_count++;
                    }

                    $dataArr['tracking_url'] = '';
                    if(in_array($provider['vTripStatus'], ['Active', 'Arrived', 'On Going Trip'])) {
                        $dataArr['tracking_url'] = $tconfig['tsite_url_main_admin'] . "map_tracking.php?iTripId=" . base64_encode(base64_encode($provider['iTripId']));
                    }

                    if ($provider['vImage'] != 'NONE' && !empty($provider['vImage']) && file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $provider['iDriverId'] . '/2_'.$provider['vImage'])) { 
                        $dataArr['image'] = $tconfig["tsite_upload_images_driver"] . '/' . $provider['iDriverId'] . '/2_' . $provider['vImage'];
                    } else {
                        $dataArr['image'] = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
                    }

                    $dataArr['vLatitude'] = $provider['vLatitude'];
                    $dataArr['vLongitude'] = $provider['vLongitude'];
                    $dataArr['name'] = clearName($provider['name']);
                    $dataArr['email'] = clearEmail($provider['vEmail']);
                    $dataArr['phone_no'] = '+' . $provider['vCode'] . ' ' . clearPhone($provider['vPhone']);

                    if(($eServiceStatus == "Available" && $provider['vAvailability'] == "Available" && strtotime($provider['tLocationUpdateDate']) > strtotime($str_date)) 
                        || ($eServiceStatus == "Pickup" && $provider['vTripStatus'] == "Active")
                        || ($eServiceStatus == "Arrived" && $provider['vTripStatus'] == "Arrived")
                        || ($eServiceStatus == "OnGoing" && $provider['vTripStatus'] == "On Going Trip")
                        || ($eServiceStatus == "Not Available" && $provider['vAvailability'] == "Not Available" && ($provider['vTripStatus'] == "Not Active" || $provider['vTripStatus'] == "NONE" || $provider['vTripStatus'] == "Cancelled"))
                        || ($eServiceStatus == "All")
                    ) {
                        $providersArr[] = $dataArr;
                    } else {
                        continue;
                    }
                }
            }

        } else {
            if(isset($driverVehicleArr[$provider['iDriverId']][$provider['iDriverVehicleId']])) {
                $driverVehicleData = $driverVehicleArr[$provider['iDriverId']][$provider['iDriverVehicleId']];
                $driver_vehicle = explode(",", $driverVehicleData['vCarType']);
                $ride_vehicles = array_intersect($driver_vehicle, $iVehicleTypeIdArr);
                if(scount($ride_vehicles) > 0) {
                    if(in_array($provider['vTripStatus'], ['Active', 'Arrived', 'On Going Trip'])
                        && !(($eServiceType == "Ride" && $tripArr[$provider['iTripId']]['eType'] == "Ride" && $tripArr[$provider['iTripId']]['eSystem'] == "General") 
                            || ($eServiceType == "Delivery" && $tripArr[$provider['iTripId']]['eType'] == "Multi-Delivery") 
                            || ($eServiceType == "Delivery" && $tripArr[$provider['iTripId']]['eType'] == "Ride" && $tripArr[$provider['iTripId']]['eSystem'] == "DeliverAll")
                        )

                    ) {
                        continue;
                    }

                    $all_count++;

                    $dataArr = array();
                    $dataArr['iDriverId'] = $provider['iDriverId'];
                    $dataArr['ServiceStatusIcon'] = "all";
                    $dataArr['ServiceStatusTitle'] = "All";
                    $dataArr['map_icon'] = $tconfig['tsite_url'] . 'resizeImg.php?h=40&src=' . $tconfig['tsite_url_main_admin'] . 'img/map_icons/all.png';

                    $SET_LAT_LONG = 'Yes';
                    if(in_array($provider['vLatitude'] , ['', 'undefined']) || in_array($provider['vLongitude'] , ['', 'undefined']))
                    {
                        $SET_LAT_LONG = 'No';

                    }
                    if($SET_LAT_LONG == "Yes" && $provider['vAvailability'] == "Available" && strtotime($provider['tLocationUpdateDate']) > strtotime($str_date)) {
                        $dataArr['map_icon'] = $tconfig['tsite_url'] . 'resizeImg.php?h=40&src=' . $tconfig['tsite_url_main_admin'] . 'img/map_icons/available.png';
                        $dataArr['ServiceStatusIcon'] = "available";
                        $dataArr['ServiceStatusTitle'] = "Available";
                        $available_count++;

                    }
                    elseif ($SET_LAT_LONG == "Yes" &&  $provider['vTripStatus'] == "Active") {
                        $dataArr['map_icon'] = $tconfig['tsite_url'] . 'resizeImg.php?h=40&src=' . $tconfig['tsite_url_main_admin'] . 'img/map_icons/enroute-pickup.png';
                        $dataArr['ServiceStatusIcon'] = "pickup";
                        $dataArr['ServiceStatusTitle'] = "Way to Pickup";
                        $pickup_count++;

                    } elseif ($SET_LAT_LONG == "Yes" &&  $provider['vTripStatus'] == "Arrived") {
                        $dataArr['map_icon'] = $tconfig['tsite_url'] . 'resizeImg.php?h=40&src=' . $tconfig['tsite_url_main_admin'] . 'img/map_icons/arrived.png';
                        $dataArr['ServiceStatusIcon'] = "arrived";
                        $dataArr['ServiceStatusTitle'] = "Arrived";
                        $arrived_count++;

                    } elseif ($SET_LAT_LONG == "Yes" &&  $provider['vTripStatus'] == "On Going Trip") {
                        $dataArr['map_icon'] = $tconfig['tsite_url'] . 'resizeImg.php?h=40&src=' . $tconfig['tsite_url_main_admin'] . 'img/map_icons/ongoing.png';
                        $dataArr['ServiceStatusIcon'] = "ongoing";
                        $dataArr['ServiceStatusTitle'] = "Way to Dropoff";
                        $dropoff_count++;

                    } elseif (

                        ( $provider['vAvailability'] == "Not Available" && ($provider['vTripStatus'] == "Not Active" || $provider['vTripStatus'] == "NONE" || $provider['vTripStatus'] == "Cancelled") )

                        || ($provider['vAvailability'] == "Available" && strtotime($provider['tLocationUpdateDate']) < strtotime($str_date))

                        || ($provider['vTripStatus'] == "NONE" && $provider['vAvailability'] == "" )
                    )
                    {
                        $dataArr['map_icon'] = $tconfig['tsite_url'] . 'resizeImg.php?h=40&src=' . $tconfig['tsite_url_main_admin'] . 'img/map_icons/all.png';
                        $dataArr['ServiceStatusIcon'] = "all";
                        $dataArr['ServiceStatusTitle'] = "Not Available";
                        $not_available_count++;
                    }                   

                    $dataArr['tracking_url'] = '';
                    if(in_array($provider['vTripStatus'], ['Active', 'Arrived', 'On Going Trip'])) {
                        $dataArr['tracking_url'] = $tconfig['tsite_url_main_admin'] . "map_tracking.php?iTripId=" . base64_encode(base64_encode($provider['iTripId']));
                    }

                    if ($provider['vImage'] != 'NONE' && !empty($provider['vImage']) && file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $provider['iDriverId'] . '/2_'.$provider['vImage'])) { 
                        $dataArr['image'] = $tconfig["tsite_upload_images_driver"] . '/' . $provider['iDriverId'] . '/2_' . $provider['vImage'];
                    } else {
                        $dataArr['image'] = $tconfig["tsite_url"] . "assets/img/profile-user-img.png";
                    }

                    $dataArr['vLatitude'] = $provider['vLatitude'];
                    $dataArr['vLongitude'] = $provider['vLongitude'];
                    $dataArr['name'] = clearName($provider['name']);
                    $dataArr['email'] = clearEmail($provider['vEmail']);
                    $dataArr['phone_no'] = '+' . $provider['vCode'] . ' ' . clearPhone($provider['vPhone']);
                    $dataArr['vehicle_model'] = $makeDataArr[$driverVehicleData['iMakeId']] . ' ' . $modelDataArr[$driverVehicleData['iModelId']];
                    $dataArr['vehicle_license'] = $driverVehicleData['vLicencePlate'];

                    if(($eServiceStatus == "Available" && $provider['vAvailability'] == "Available" && strtotime($provider['tLocationUpdateDate']) > strtotime($str_date)) 
                        || ($eServiceStatus == "Pickup" && $provider['vTripStatus'] == "Active")
                        || ($eServiceStatus == "Arrived" && $provider['vTripStatus'] == "Arrived")
                        || ($eServiceStatus == "OnGoing" && $provider['vTripStatus'] == "On Going Trip")
                        || ($eServiceStatus == "Not Available" && $provider['vAvailability'] == "Not Available" && ($provider['vTripStatus'] == "Not Active" || $provider['vTripStatus'] == "NONE" || $provider['vTripStatus'] == "Cancelled"))
                        || ($eServiceStatus == "All")
                    ) {
                        $providersArr[] = $dataArr;
                    } else {
                        continue;
                    }                    
                }else{

                }
            }else{

               /* echo "---1---";*/
                /*echo "<pre>";
                print_r($provider);*/

            }
        }        
    }

    $data = $providersArr;
    $returnArr['total'] = scount($providersArr);
    $returnArr['all_count'] = $all_count;
    $returnArr['available_count'] = $available_count;
    $returnArr['not_available_count'] = $not_available_count;
    $returnArr['pickup_count'] = $pickup_count;
    $returnArr['arrived_count'] = $arrived_count;
    $returnArr['dropoff_count'] = $dropoff_count;

}
elseif ($chart_type == "Service_Stats") {
    $service_option = (isset($_REQUEST['service_option'])) ? $_REQUEST['service_option'] : "TODAY";

    $sql1 = "";
    if(strtoupper($service_option) == "TODAY") {
        $start_date = date("Y-m-d 00:00:00");
        $end_date = date("Y-m-d 23:59:59");

        $sql1 = " AND (tTripRequestDate BETWEEN '$start_date' AND '$end_date') ";
    }
    $FINAL_SQL = "SELECT         
        (SELECT COUNT(iTripId) FROM `trips` WHERE eType = 'Ride' AND eSystem = 'General' $sql1) as total_rides,
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Active', 'Arrived', 'On Going Trip') AND eType = 'Ride' AND eSystem = 'General') as total_inprocess_rides, 
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Finished') AND eType = 'Ride' AND eSystem = 'General' $sql1) as total_finished_rides,     
        
        (SELECT COUNT(iTripId) FROM `trips` WHERE eType = 'Multi-Delivery' AND eSystem = 'General' $sql1) as total_deliveries,     
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Active', 'Arrived', 'On Going Trip') AND eType = 'Multi-Delivery' AND eSystem = 'General' $sql1) as total_inprocess_deliveries, 
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Finished') AND eType = 'Multi-Delivery' AND eSystem = 'General' $sql1) as total_finished_deliveries, 
        
        (SELECT COUNT(iTripId) FROM `trips` WHERE eType = 'UberX' AND eSystem = 'General' $sql1) as total_jobs,
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Active', 'Arrived', 'On Going Trip' $sql1) AND eType = 'UberX' AND eSystem = 'General') as total_inprocess_jobs,
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Finished' ) AND eType = 'UberX' AND eSystem = 'General' $sql1) as total_finished_jobs";

    $systemStats = $obj->MySQLSelect($FINAL_SQL);

    $data = array(
        'Total_Trips'           => $systemStats[0]['total_rides'],
        'Trips_Stats'           => array($systemStats[0]['total_inprocess_rides'], $systemStats[0]['total_finished_rides']),
        'Trips_Status_Label'    => array('In Process', 'Completed'),
        'Trips_Status_Color'    => array('#FFC300', '#174FEB'),

        'Total_Parcel_Deliveries'           => $systemStats[0]['total_deliveries'],
        'Parcel_Deliveries_Stats'           => array($systemStats[0]['total_inprocess_deliveries'], $systemStats[0]['total_finished_deliveries']),
        'Parcel_Deliveries_Status_Label'    => array('In Process', 'Completed'),
        'Parcel_Deliveries_Status_Color'    => array('#FFC300', '#174FEB'),

        'Total_UberX'           => $systemStats[0]['total_jobs'],
        'UberX_Stats'           => array($systemStats[0]['total_inprocess_jobs'], $systemStats[0]['total_finished_jobs']),
        'UberX_Status_Label'    => array('In Process', 'Completed'),
        'UberX_Status_Color'    => array('#FFC300', '#174FEB'),
    );
}


elseif ($chart_type == "Service_Stats_Trip") {
    $service_option = (isset($_REQUEST['service_option'])) ? $_REQUEST['service_option'] : "TODAY";

    $sql1 = "";
    if(strtoupper($service_option) == "TODAY") {
        $start_date = date("Y-m-d 00:00:00");
        $end_date = date("Y-m-d 23:59:59");

        $sql1 = " AND (tTripRequestDate BETWEEN '$start_date' AND '$end_date') ";
    }
    $FINAL_SQL = "SELECT         
        (SELECT COUNT(iTripId) FROM `trips` WHERE eType = 'Ride' AND eSystem = 'General' $sql1) as total_rides,
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Active', 'Arrived', 'On Going Trip') AND eType = 'Ride' AND eSystem = 'General') as total_inprocess_rides, 
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Finished') AND eType = 'Ride' AND eSystem = 'General' $sql1) as total_finished_rides,     
        
        (SELECT COUNT(iTripId) FROM `trips` WHERE eType = 'Multi-Delivery' AND eSystem = 'General' $sql1) as total_deliveries,     
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Active', 'Arrived', 'On Going Trip') AND eType = 'Multi-Delivery' AND eSystem = 'General' $sql1) as total_inprocess_deliveries, 
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Finished') AND eType = 'Multi-Delivery' AND eSystem = 'General' $sql1) as total_finished_deliveries, 
        
        (SELECT COUNT(iTripId) FROM `trips` WHERE eType = 'UberX' AND eSystem = 'General' $sql1) as total_jobs,
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Active', 'Arrived', 'On Going Trip' $sql1) AND eType = 'UberX' AND eSystem = 'General') as total_inprocess_jobs,
        (SELECT COUNT(iTripId) FROM `trips` WHERE iActive IN ('Finished' ) AND eType = 'UberX' AND eSystem = 'General' $sql1) as total_finished_jobs";

    $systemStats = $obj->MySQLSelect($FINAL_SQL);

    $data = array(
        'Total_Trips'           => $systemStats[0]['total_rides'],
        'Trips_Stats'           => array($systemStats[0]['total_inprocess_rides'], $systemStats[0]['total_finished_rides']),
        'Trips_Status_Label'    => array('In Process', 'Completed'),
        'Trips_Status_Color'    => array('#FFC300', '#174FEB'),

        'Total_Parcel_Deliveries'           => $systemStats[0]['total_deliveries'],
        'Parcel_Deliveries_Stats'           => array($systemStats[0]['total_inprocess_deliveries'], $systemStats[0]['total_finished_deliveries']),
        'Parcel_Deliveries_Status_Label'    => array('In Process', 'Completed'),
        'Parcel_Deliveries_Status_Color'    => array('#FFC300', '#174FEB'),

        'Total_UberX'           => $systemStats[0]['total_jobs'],
        'UberX_Stats'           => array($systemStats[0]['total_inprocess_jobs'], $systemStats[0]['total_finished_jobs']),
        'UberX_Status_Label'    => array('In Process', 'Completed'),
        'UberX_Status_Color'    => array('#FFC300', '#174FEB'),
    );
}

elseif ($chart_type == "admin_earnings_today")
{

    $startDate = strtotime(date('Y-m-d 00:00:00'));
    $endDate = strtotime(date('Y-m-d 23:59:59'));

    $currentDate = $startDate;

    $series = [];
    $TodayEarnsArr = [];
    $TodayOrgOutStandingReportArr = [];
    $TodayOutstandingReportArr = [];



    $currentDate = date('Y-m-d H:i:s');
    $date = []; // Initialize $date as an array
    for ($i = 0; $i < 12; $i++) {

        $sec = 3600;

        $date2 = $enddate= $lastDay = date('Y-m-d H:i:s', strtotime($currentDate));
        $date1 = $startdate = $currentDate = date('Y-m-d H:i:s', (strtotime($currentDate) - $sec));


        $TodayEarns = getStoreTotalEarns($date1, $date2);
        $TodayEarns += getRideShareTotalEarns($date1, $date2);
        $TodayEarns += getTotalEarns($date1, $date2);

        $outstanding_report = outstanding_reports($date1, $date2);
        $org_outstanding_report = org_outstanding_reports($date1, $date2);
        $TodayEarnsArr[] = $TodayEarns;
        $TodayOrgOutStandingReportArr[] = $org_outstanding_report;
        $TodayOutstandingReportArr[] = $outstanding_report;
        //$date[] =  date('h:i a', strtotime($date1));

        $date[] = [date('g', strtotime($date2)) ,date('a', strtotime($date2))];

    }

    $data['adminEarning'] = '';
    if (allZeros($TodayEarnsArr) && allZeros($TodayOutstandingReportArr) && allZeros($TodayOrgOutStandingReportArr))
    {
        $data['adminEarning'] = "NotFoundData";
    }


    $series[] = array(
        "name" => 'Total Earning',
        "data" => array_reverse($TodayEarnsArr),
    );

    $series[] = array(
        "name" => 'Outstanding Amount',
        "data" => array_reverse($TodayOutstandingReportArr),
    );

    if(ONLY_MEDICAL_SERVICE != 'Yes'  && $MODULES_OBJ->isRideFeatureAvailable('Yes')) {
        $series[] = array(
            "name" => 'Org. Outstanding Amount',
            "data" => array_reverse($TodayOrgOutStandingReportArr),
        );
    }


    $data['DateTimeArr'] = array_reverse($date);
    $data['SeriesArr'] = $series;
    $data['colors'] = ['#008ffb', '#00e396','#e99939'];
}

elseif ($chart_type == "admin_earnings_total")
{


   /* $startDate = strtotime(date('Y-m-d 00:00:00'));
    $endDate = strtotime(date('Y-m-d 23:59:59'));
    $currentDate = $startDate;*/

    $series = [];
    $TodayEarnsArr = [];
    $TodayOrgOutStandingReportArr = [];
    $TodayOutstandingReportArr = [];

    /*while ($currentDate <= $endDate) {
        $sec = 3600;

        $date1= date('Y-m-d H:i:s', $currentDate);
        $date2= date('Y-m-d H:i:s',(strtotime($date1) + $sec));

        $TodayEarns = getStoreTotalEarns($date1, $date2);
        $TodayEarns += getRideShareTotalEarns($date1, $date2);
        $TodayEarns += getTotalEarns($date1, $date2);

        $outstanding_report = outstanding_reports($date1, $date2);
        $org_outstanding_report = org_outstanding_reports($date1, $date2);

        $TodayEarnsArr[] = $TodayEarns;
        $TodayOrgOutStandingReportArr[] = $org_outstanding_report;
        $TodayOutstandingReportArr[] = $outstanding_report;
        $date[] = $date2;
        $currentDate += $sec;
    }*/


    $currentDate = date('Y-m');
    $date = [];
    for ($i = 1; $i <= 12; $i++) {
        $startdate = $firstDay = $currentDate . '-01';
        $enddate= $lastDay = date('Y-m-t', strtotime($currentDate));
        $currentDate = date('Y-m', strtotime($currentDate . ' -1 month'));

        $TodayEarns = getStoreTotalEarns($startdate, $enddate);
        $TodayEarns += getRideShareTotalEarns($startdate, $enddate);
        $TodayEarns += getTotalEarns($startdate, $enddate);

        $outstanding_report = outstanding_reports($startdate, $enddate);
        $org_outstanding_report = org_outstanding_reports($startdate, $enddate);

        $TodayEarnsArr[] = $TodayEarns;
        $TodayOrgOutStandingReportArr[] = $org_outstanding_report;
        $TodayOutstandingReportArr[] = $outstanding_report;
        //$date[] = date('M', strtotime($startdate));
        $date[] = [ date('M', strtotime($startdate)) , date('Y', strtotime($startdate))];
    }


    $series[] = array(
        "name" => 'Total Earning',
        "data" => array_reverse($TodayEarnsArr),
    );

    $series[] = array(
        "name" => 'Outstanding Amount',
        "data" => array_reverse($TodayOutstandingReportArr),
    );

    if(ONLY_MEDICAL_SERVICE != 'Yes' && $MODULES_OBJ->isRideFeatureAvailable('Yes')) {
        $series[] = array(
            "name" => 'Org. Outstanding Amount',
            "data" => array_reverse($TodayOrgOutStandingReportArr),
        );
    }

    $data['DateTimeArr'] = array_reverse($date);
    $data['SeriesArr'] = $series;
    $data['colors'] = ['#008ffb', '#00e396','#e99939'];

}

elseif ($chart_type == "store_deliveries_today") {

    $_REQUEST['ENABLE_DEBUG'] = 1;
    $order_month = [];
    $finishOrdertotalByMonth = [];
    $cancelledOrdertotalByMonth = [];
    $inProcessOrdertotalByMonth = [];

    //$currentDateTime = strtotime(date('Y-m-d 00:00:00'));
    $currentDate = date('Y-m-d H:i:s');
    for ($i = 0; $i < 12; $i++) {

        $sec = 3600;

        /*$startdate= date('Y-m-d H:i:s', $currentDateTime);
        $enddate= date('Y-m-d H:i:s',(strtotime($startdate) + $sec));
        $currentDateTime += $sec;*/

        $enddate= $lastDay = date('Y-m-d H:i:s', strtotime($currentDate));
        $startdate = $currentDate = date('Y-m-d H:i:s', (strtotime($currentDate) - $sec));


        //$order_month[] = date('g a', strtotime($startdate));

        $order_month[] = [date('g', strtotime($enddate)) ,date('a', strtotime($enddate))  ];
        $cancelledOrdertotalByMonth[] = getStoreTripStates_new('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getStoreTripStates_new('Delivered', $startdate, $enddate, '1');
        $inProcessOrdertotalByMonth[] = getStoreTripStates_new('on going order', $startdate, $enddate, '1');
    }


    $data['storeOrder'] = '';
    if (allZeros($cancelledOrdertotalByMonth) && allZeros($finishOrdertotalByMonth) && allZeros($inProcessOrdertotalByMonth))
    {
        $data['storeOrder'] = "NotFoundData";
    }


    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledOrdertotalByMonth),
    );


    $data['DateTimeArr'] = array_reverse($order_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41'
    ];


}

elseif ($chart_type == "store_deliveries_total") {
    $order_month = [];
    $finishOrdertotalByMonth = [];
    $cancelledOrdertotalByMonth = [];

    /*for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $month = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($month."-".$year));

        //  $order_month[] = date('Y-m-d\TH:i:s.u\Z', strtotime($startdate));
        $order_month[] = date('M', strtotime($startdate));

        $cancelledOrdertotalByMonth[] = getStoreTripStates_new('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getStoreTripStates_new('Delivered', $startdate, $enddate, '1');
        $inProcessOrdertotalByMonth[] = getStoreTripStates_new('on going order', $startdate, $enddate, '1');

    }*/


    $currentDate = date('Y-m');
    for ($i = 1; $i <= 12; $i++) {

        $startdate = $firstDay = $currentDate . '-01';
        $enddate= $lastDay = date('Y-m-t', strtotime($currentDate));
        $currentDate = date('Y-m', strtotime($currentDate . ' -1 month'));

        //$order_month[] = date('M', strtotime($startdate));
        $order_month[] = [ date('M', strtotime($startdate)) , date('Y', strtotime($startdate))];
        $cancelledOrdertotalByMonth[] = getStoreTripStates_new('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getStoreTripStates_new('Delivered', $startdate, $enddate, '1');
        $inProcessOrdertotalByMonth[] = getStoreTripStates_new('on going order', $startdate, $enddate, '1');
    }




    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledOrdertotalByMonth),
    );
    $data['DateTimeArr'] = array_reverse($order_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41'
    ];
}
elseif ($chart_type == "genie_runner_deliveries_today")
{
    $order_month = [];
    $finishOrdertotalByMonth = [];
    $cancelledOrdertotalByMonth = [];
    $inProcessOrdertotalByMonth = [];

    //$currentDateTime = strtotime(date('Y-m-d 00:00:00'));
    $currentDate = date('Y-m-d H:i:s');
    for ($i = 0; $i < 12; $i++) {

        $sec = 3600;

        /*$startdate= date('Y-m-d H:i:s', $currentDateTime);
        $enddate= date('Y-m-d H:i:s',(strtotime($startdate) + $sec));
        $currentDateTime += $sec;*/

        $enddate= $lastDay = date('Y-m-d H:i:s', strtotime($currentDate));
        $startdate = $currentDate = date('Y-m-d H:i:s', (strtotime($currentDate) - $sec));

        //$order_month[] = date('g a', strtotime($startdate));
        $order_month[] = [date('g', strtotime($enddate)) ,date('a', strtotime($enddate))];
        $cancelledOrdertotalByMonth[] = getGenieRunnerTripStates_new('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getGenieRunnerTripStates_new('Delivered', $startdate, $enddate, '1');
        $inProcessOrdertotalByMonth[] = getGenieRunnerTripStates_new('on going order', $startdate, $enddate, '1');
    }

    $data['genieRunnerOrder'] = '';
    if (allZeros($cancelledOrdertotalByMonth) && allZeros($finishOrdertotalByMonth) && allZeros($inProcessOrdertotalByMonth))
    {
        $data['genieRunnerOrder'] = "NotFoundData";
    }

    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledOrdertotalByMonth),
    );


    $data['GenieRunnerOrder'] = '';
    if (allZeros($inProcessOrdertotalByMonth) && allZeros($finishOrdertotalByMonth) && allZeros($cancelledOrdertotalByMonth))
    {
        $data['GenieRunnerOrder'] = "NotFoundData";
    }
    $data['DateTimeArr'] = array_reverse($order_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41'
    ];

}

elseif ($chart_type == "genie_runner_deliveries_total") {
    $order_month = [];
    $finishOrdertotalByMonth = [];
    $cancelledOrdertotalByMonth = [];

    /*for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $month = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($month."-".$year));

        //  $order_month[] = date('Y-m-d\TH:i:s.u\Z', strtotime($startdate));
        $order_month[] = date('M', strtotime($startdate));

        $cancelledOrdertotalByMonth[] = getGenieRunnerTripStates_new('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getGenieRunnerTripStates_new('Delivered', $startdate, $enddate, '1');
        $inProcessOrdertotalByMonth[] = getGenieRunnerTripStates_new('on going order', $startdate, $enddate, '1');
    }*/


    $currentDate = date('Y-m');
    for ($i = 1; $i <= 12; $i++) {
        $startdate = $firstDay = $currentDate . '-01';
        $enddate= $lastDay = date('Y-m-t', strtotime($currentDate));
        $currentDate = date('Y-m', strtotime($currentDate . ' -1 month'));

        //$order_month[] = date('M', strtotime($startdate));

        $order_month[] = [ date('M', strtotime($startdate)) , date('Y', strtotime($startdate))];
        $cancelledOrdertotalByMonth[] = getGenieRunnerTripStates_new('Cancelled', $startdate, $enddate, '1');
        $finishOrdertotalByMonth[] = getGenieRunnerTripStates_new('Delivered', $startdate, $enddate, '1');
        $inProcessOrdertotalByMonth[] = getGenieRunnerTripStates_new('on going order', $startdate, $enddate, '1');
    }
    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishOrdertotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledOrdertotalByMonth),
    );
    $data['DateTimeArr'] = array_reverse($order_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41'
    ];

}


elseif ($chart_type == "video_Consultation_today") {
    $vc_month = [];
    $finishVCtotalByMonth = [];
    $cancelledVCtotalByMonth = [];
    $inProcessVCtotalByMonth = [];

   // $currentDateTime = strtotime(date('Y-m-d 00:00:00'));
    $currentDate = date('Y-m-d H:i:s');
    for ($i = 0; $i < 12; $i++) {

        $sec = 3600;

        /*$startdate= date('Y-m-d H:i:s', $currentDateTime);
        $enddate= date('Y-m-d H:i:s',(strtotime($startdate) + $sec));
        $currentDateTime += $sec;*/

        $enddate= $lastDay = date('Y-m-d H:i:s', strtotime($currentDate));
        $startdate = $currentDate = date('Y-m-d H:i:s', (strtotime($currentDate) - $sec));

        $vc_month[] = [date('g', strtotime($enddate)) ,date('a', strtotime($enddate))  ];

        $cancelledVCtotalByMonth[] = getVideoConsultationTripStates('cancelled', $startdate, $enddate, '1');
        $finishVCtotalByMonth[] = getVideoConsultationTripStates('finished', $startdate, $enddate, '1');
        $inProcessVCtotalByMonth[] = getVideoConsultationTripStates('on ride', $startdate, $enddate, '1');
    }

    $data['VCService'] = '';
    if (allZeros($inProcessVCtotalByMonth) && allZeros($finishVCtotalByMonth) && allZeros($cancelledVCtotalByMonth))
    {
        $data['VCService'] = "NotFoundData";
    }

    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessVCtotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishVCtotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledVCtotalByMonth),
    );
    $data['DateTimeArr'] = array_reverse($vc_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41'
    ];

}

elseif ($chart_type == "video_Consultation_total") {
    $VC_month = [];
    $finishVCtotalByMonth = [];
    $cancelledVCtotalByMonth = [];

    /*for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $month = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($month."-".$year));

        //  $order_month[] = date('Y-m-d\TH:i:s.u\Z', strtotime($startdate));
        $VC_month[] = date('M', strtotime($startdate));

        $cancelledVCtotalByMonth[] = getVideoConsultationTripStates('cancelled', $startdate, $enddate, '1');
        $finishVCtotalByMonth[] = getVideoConsultationTripStates('finished', $startdate, $enddate, '1');
        $inProcessVCtotalByMonth[] = getVideoConsultationTripStates('on ride', $startdate, $enddate, '1');
    }*/

    $currentDate = date('Y-m');
    for ($i = 1; $i <= 12; $i++) {
        $startdate = $firstDay = $currentDate . '-01';
        $enddate= $lastDay = date('Y-m-t', strtotime($currentDate));

        $currentDate = date('Y-m', strtotime($currentDate . ' -1 month'));

        //$VC_month[] = date('Y M', strtotime($startdate));
        $VC_month[] = [ date('M', strtotime($startdate)) , date('Y', strtotime($startdate))];
        $cancelledVCtotalByMonth[] = getVideoConsultationTripStates('cancelled', $startdate, $enddate, '1');
        $finishVCtotalByMonth[] = getVideoConsultationTripStates('finished', $startdate, $enddate, '1');
        $inProcessVCtotalByMonth[] = getVideoConsultationTripStates('on ride', $startdate, $enddate, '1');
    }


    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessVCtotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishVCtotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledVCtotalByMonth),
    );
    $data['DateTimeArr'] = array_reverse($VC_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41'
    ];
}

elseif ($chart_type == "bid_post_today") {
    $Bid_Post_month = [];
    $finishVCtotalByMonth = [];
    $cancelledVCtotalByMonth = [];
    $inProcessVCtotalByMonth = [];

    //$currentDateTime = strtotime(date('Y-m-d 00:00:00'));
    $currentDate = date('Y-m-d H:i:s');
    for ($i = 0; $i < 12; $i++) {

        $sec = 3600;


        /*$startdate= date('Y-m-d H:i:s', $currentDateTime);
        $enddate= date('Y-m-d H:i:s',(strtotime($startdate) + $sec));
        $currentDateTime += $sec;*/

        $enddate= $lastDay = date('Y-m-d H:i:s', strtotime($currentDate));
        $startdate = $currentDate = date('Y-m-d H:i:s', (strtotime($currentDate) - $sec));

       // $Bid_Post_month[] = date('g a', strtotime($startdate));
        $Bid_Post_month[] = [date('g', strtotime($enddate)) ,date('a', strtotime($enddate))  ];
        $cancelledBidPosttotalByMonth[] = getBidPostTripStates('cancelled', $startdate, $enddate, '1');
        $finishBidPosttotalByMonth[] = getBidPostTripStates('finished', $startdate, $enddate, '1');
        $inProcessBidPosttotalByMonth[] = getBidPostTripStates('inProcess', $startdate, $enddate, '1');
        $pendingBidPosttotalByMonth[] = getBidPostTripStates('pending', $startdate, $enddate, '1');
    }

    $data['BiddingPost'] = '';
    if (allZeros($pendingBidPosttotalByMonth) && allZeros($inProcessBidPosttotalByMonth) && allZeros($finishBidPosttotalByMonth) && allZeros($cancelledBidPosttotalByMonth))
    {
        $data['BiddingPost'] = "NotFoundData";
    }

    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessBidPosttotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishBidPosttotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledBidPosttotalByMonth),
    );

    $series[] = array(
        "name" => 'Pending',
        "data" => array_reverse($pendingBidPosttotalByMonth),
    );

    $total = array_sum($inProcessBidPosttotalByMonth) + array_sum($finishBidPosttotalByMonth) + array_sum($cancelledBidPosttotalByMonth) + array_sum($pendingBidPosttotalByMonth);


    $data['BiddingService'] = '';
    if (allZeros($inProcessBidPosttotalByMonth) && allZeros($finishBidPosttotalByMonth) && allZeros($cancelledBidPosttotalByMonth) && allZeros($pendingBidPosttotalByMonth))
    {
        $data['BiddingService'] = "NotFoundData";
    }


    if($total == 0 || $total < 0){
        //$series = [];
    }
    $data['DateTimeArr'] = array_reverse($Bid_Post_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41',
        '#e99939'
    ];

}

elseif ($chart_type == "bid_post_total")
{
    $Bid_Post_month = [];
    $finishBidPosttotalByMonth = [];
    $cancelledBidPosttotalByMonth = [];

   /* for ($i = 1; $i <= 12; $i++) {
        $getMonthTimeStamp = mktime(0,0,0,$i, 1, $year);
        $month = date('F', $getMonthTimeStamp);

        $startdate = date($year.'-'.$i.'-01 00:00:00', strtotime("-$i month"));
        $enddate = date($year.'-'.$i.'-t 23:59:59', strtotime($month."-".$year));

        $Bid_Post_month[] = date('M', strtotime($startdate));

        $cancelledBidPosttotalByMonth[] = getBidPostTripStates('cancelled', $startdate, $enddate, '1');
        $finishBidPosttotalByMonth[] = getBidPostTripStates('finished', $startdate, $enddate, '1');
        $inProcessBidPosttotalByMonth[] = getBidPostTripStates('inProcess', $startdate, $enddate, '1');
        $pendingBidPosttotalByMonth[] = getBidPostTripStates('pending', $startdate, $enddate, '1');
    }*/

    $currentDate = date('Y-m');
    for ($i = 1; $i <= 12; $i++) {


        $startdate = $firstDay = $currentDate . '-01';
        $enddate= $lastDay = date('Y-m-t', strtotime($currentDate));

        $currentDate = date('Y-m', strtotime($currentDate . ' -1 month'));

       // $Bid_Post_month[] = date('M', strtotime($startdate));
        $Bid_Post_month[] = [ date('M', strtotime($startdate)) , date('Y', strtotime($startdate))];
        $cancelledBidPosttotalByMonth[] = getBidPostTripStates('cancelled', $startdate, $enddate, '1');
        $finishBidPosttotalByMonth[] = getBidPostTripStates('finished', $startdate, $enddate, '1');
        $inProcessBidPosttotalByMonth[] = getBidPostTripStates('inProcess', $startdate, $enddate, '1');
        $pendingBidPosttotalByMonth[] = getBidPostTripStates('pending', $startdate, $enddate, '1');
    }

    $series[] = array(
        "name" => 'In Process',
        "data" => array_reverse($inProcessBidPosttotalByMonth),
    );

    $series[] = array(
        "name" => 'Completed',
        "data" => array_reverse($finishBidPosttotalByMonth),
    );

    $series[] = array(
        "name" => 'Cancelled',
        "data" => array_reverse($cancelledBidPosttotalByMonth),
    );

    $series[] = array(
        "name" => 'Pending',
        "data" => array_reverse($pendingBidPosttotalByMonth),
    );

    $total = array_sum($inProcessBidPosttotalByMonth) + array_sum($finishBidPosttotalByMonth) + array_sum($cancelledBidPosttotalByMonth) + array_sum($pendingBidPosttotalByMonth);

    if($total == 0 || $total < 0){
        //$series = [];
    }
    $data['DateTimeArr'] = array_reverse($Bid_Post_month);
    $data['SeriesArr'] = $series;
    $data['colors'] = [
        '#ffc300',
        '#174feb',
        '#f31a41',
        '#e99939'
    ];
}

elseif ($chart_type == "server_status")
{
    $SystemDiagnosticData = $DASHBOARD_OBJ->getSystemDiagnosticData();
    $working = $missing = 0;
    foreach ($SystemDiagnosticData as $SysData) {
        if ($SysData['value'] || strtoupper(SITE_TYPE) == "DEMO") {
            $working++;
        } else {
            $missing++;
        }
    }
    $alerts = 0;
    $server_status = ['Working', 'Errors', 'Alerts'];
    $server_number = [$working, $missing, $alerts];
    $server_working = $working;
    $server_missing = $missing;

    $data = array(
        'server_working' => $server_working,
        'server_missing' => $server_missing,
        'alerts' => $alerts,
        'server' => [
            (int)$working,
            (int)$missing,
            (int)$alerts,
        ],
        'server_status' => [
            'Working',
            'Errors',
            'Alerts'
        ],
        'server_color'  => [
            '#17c653',
            '#f31a41',
            '#ffc300'
        ]
    );
}

function getGenieRunnerTripStates_new($OrderStatus = "", $tOrderRequestDate = "", $dDeliveryDate = "", $dashbord_chart = "")
{
    global $MODULES_OBJ;
    $cmp_ssql = "";
    $dsql = "";
    if (SITE_TYPE == 'Demo') {
        $cmp_ssql = " And tOrderRequestDate > '" . WEEK_DATE . "'";
    }
    global $obj;
    $data = array();
    if ($tOrderRequestDate != "" && $dDeliveryDate != "") {
        $dsql = " AND tOrderRequestDate BETWEEN '" . $tOrderRequestDate . "' AND '" . $dDeliveryDate . "'";
        //$dsql = " AND Date(tOrderRequestDate) >= '" . $tOrderRequestDate . "' AND Date(tOrderRequestDate) <= '" . $dDeliveryDate . "' ";
    }
    if ($tOrderRequestDate != "" && $dDeliveryDate != "" && $dashbord_chart = "1") {
        $dsql = " AND tOrderRequestDate BETWEEN '" . $tOrderRequestDate . "' AND '" . $dDeliveryDate . "'";
        //$dsql = " AND Date(tOrderRequestDate) >= '" . $tOrderRequestDate . "' AND Date(tOrderRequestDate) <= '" . $dDeliveryDate . "' ";
    }
    $processing_status_array = array('1', '2', '4', '5');
    $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');
    if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
        $processing_status_array = array('1', '2', '4', '5', '13', '14');
        $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12', '13', '14');
    }
    if ($OrderStatus == 'on going order') {
        $iStatusCode = '(' . implode(',', $processing_status_array) . ')';
    } else {
        $iStatusCode = '(' . implode(',', $all_status_array) . ')';
    }

    if ($OrderStatus != "") {
        $ssl = "";
        if ($OrderStatus == "on going order") {
            $ssl .= " Where o.eBuyAnyService = 'Yes' AND o.iStatusCode IN $iStatusCode ";
        } else if ($OrderStatus == "Cancelled") {
            $ssl .= " Where o.iStatusCode IN ('9','8','7') AND o.eBuyAnyService = 'Yes' ";
        } else if ($OrderStatus == "Delivered") {
            $ssl .= " Where o.iStatusCode = '6' AND o.eBuyAnyService = 'Yes' ";
        }
        $sql = "SELECT COUNT(o.iOrderId) as tot FROM orders o LEFT JOIN order_status os ON (o.iStatusCode = os.iStatusCode AND os.eBuyAnyService = 'Yes') " . $cmp_ssql . $ssl . $dsql;
        $data = $obj->MySQLSelect($sql);

    }
    return $data[0]['tot'];
}


function getStoreTripStates_new($OrderStatus = "", $tOrderRequestDate = "", $dDeliveryDate = "", $dashbord_chart = "")
{
    global $MODULES_OBJ;
    $cmp_ssql = "";
    $dsql = "";
    if (SITE_TYPE == 'Demo') {
        $cmp_ssql = " And tOrderRequestDate > '" . WEEK_DATE . "'";
    }
    global $obj;
    $data = array();
    if ($tOrderRequestDate != "" && $dDeliveryDate != "") {
        $dsql = " AND tOrderRequestDate BETWEEN '" . $tOrderRequestDate . "' AND '" . $dDeliveryDate . "'";
        //$dsql = " AND Date(tOrderRequestDate) >= '" . $tOrderRequestDate . "' AND Date(tOrderRequestDate) <= '" . $dDeliveryDate . "' ";
    }
    if ($tOrderRequestDate != "" && $dDeliveryDate != "" && $dashbord_chart = "1") {
        $dsql = " AND tOrderRequestDate BETWEEN '" . $tOrderRequestDate . "' AND '" . $dDeliveryDate . "'";
        ///$dsql = " AND Date(tOrderRequestDate) >= '" . $tOrderRequestDate . "' AND Date(tOrderRequestDate) <= '" . $dDeliveryDate . "' ";
    }
    $processing_status_array = array('1', '2', '4', '5');
    $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');
    if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
        $processing_status_array = array('1', '2', '4', '5', '13', '14');
        $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12', '13', '14');
    }
    if ($OrderStatus == 'on going order') {
        $iStatusCode = '(' . implode(',', $processing_status_array) . ')';
    } else {
        $iStatusCode = '(' . implode(',', $all_status_array) . ')';
    }
    if ($OrderStatus != "") {
        $ssl = "";
        if ($OrderStatus == "on going order") {
            $ssl .= " Where IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.eBuyAnyService = 'No' AND o.iStatusCode IN $iStatusCode";
        } else if ($OrderStatus == "Cancelled") {
            $ssl .= " Where o.iStatusCode IN ('9','8','7') AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND o.eBuyAnyService = 'No' ";
        } else if ($OrderStatus == "Delivered") {
            $ssl .= " Where o.iStatusCode = '6' AND o.eBuyAnyService = 'No' AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes')";
        }


        $sql = "SELECT COUNT(o.iOrderId) as tot FROM orders o LEFT JOIN order_status os ON (o.iStatusCode = os.iStatusCode AND os.eBuyAnyService = 'No' ) " . $cmp_ssql . $ssl . $dsql;
        $data = $obj->MySQLSelect($sql);
    }
    return $data[0]['tot'];
}

$returnArr['Action'] = "1";
$returnArr['data'] = $data;
echo json_encode($returnArr);
exit();
