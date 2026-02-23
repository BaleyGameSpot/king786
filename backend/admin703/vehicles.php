<?php

include_once('../common.php');

/*if (!$userObj->hasPermission('view-provider-taxis')) {

    $userObj->redirect();

}*/

$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";


$commonTxt = '';
if ($eType == 'Ride') {

    $commonTxt = 'taxi-service';

}

if ($eType == 'Ride') {

    $commonTxt = 'taxi-service';

}

if ($eType == 'Deliver') {

    $commonTxt = 'parcel-delivery';

}

if ($eType == 'Ambulance') {

    $commonTxt = 'medical';

}





$view = ["view-provider-vehicles-" . $commonTxt];

$create = ["create-provider-vehicles-" . $commonTxt];

$edit = ["edit-provider-vehicles-" . $commonTxt];

$delete = ["delete-provider-vehicles-" . $commonTxt];

$updateStatus = ["update-status-provider-vehicles-" . $commonTxt];

//$editDocument = ["edit-provider-vehicles-document-" . $commonTxt];

$editDocument = ["edit-provider-vehicles-document"];





if (empty($eType)) {

    $view = [

        'view-provider-vehicles-taxi-service',

        'view-provider-vehicles-parcel-delivery',

        'view-provider-vehicles-medical',

        'view-provider-vehicles'

    ];

    $create = [

        'create-provider-vehicles-taxi-service',

        'create-provider-vehicles-parcel-delivery',

        'create-provider-vehicles-medical',

        'create-provider-vehicles'

    ];

    $edit = [

        'edit-provider-vehicles-taxi-service',

        'edit-provider-vehicles-parcel-delivery',

        'edit-provider-vehicles-medical',

        'edit-provider-vehicles'

    ];

    $delete = [

        'delete-provider-vehicles-taxi-service',

        'delete-provider-vehicles-parcel-delivery',

        'delete-provider-vehicles-medical',

        'delete-provider-vehicles'

    ];

    $updateStatus = [

        'update-status-provider-vehicles-taxi-service',

        'update-status-provider-vehicles-parcel-delivery',

        'update-status-provider-vehicles-medical',

        'update-status-provider-vehicles'

    ];

}



if (!$userObj->hasPermission($view)) {

    $userObj->redirect();

}

$script = "Vehicle";

$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY dv.iDriverVehicleId DESC';

if ($sortby == 1) {

    if ($order == 0) $ord = " ORDER BY m.vMake ASC"; else

        $ord = " ORDER BY m.vMake DESC";

}

if ($sortby == 2) {

    if ($order == 0) $ord = " ORDER BY c.vCompany ASC"; else

        $ord = " ORDER BY c.vCompany DESC";

}

if ($sortby == 3) {

    if ($order == 0) $ord = " ORDER BY rd.vName ASC"; else

        $ord = " ORDER BY rd.vName DESC";

}

if ($sortby == 4) {

    if ($order == 0) $ord = " ORDER BY dv.eType ASC"; else

        $ord = " ORDER BY dv.eType DESC";

}

if ($sortby == 5) {

    if ($order == 0) $ord = " ORDER BY dv.eStatus ASC"; else

        $ord = " ORDER BY dv.eStatus DESC";

}

//End Sorting

$dri_ssql = "";

if (SITE_TYPE == 'Demo') {

    $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";

}

// Start Search Parameters

$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";

$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$REQUEST_eType = $eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";



$script = "Vehicle_";

$ssql = '';

$queryString = $query_str = '';

if (in_array($eType, [

    'Ride',

    'Deliver',

    'Ambulance'

])) {

    if ($eType == 'Ride') {

        $ssql3 .= "AND vt.eType ='Ride' AND eIconType != 'Ambulance'";

    } else if ($eType == 'Ambulance') {

        $ssql3 .= "AND vt.eType ='Ride' AND eIconType = 'Ambulance'";

    } else {

        $ssql3 .= "AND vt.eType ='Deliver' AND eIconType != 'Ambulance' ";

    }

    $sql = "SELECT GROUP_CONCAT(DISTINCT(iVehicleTypeId)) as Id from  vehicle_type  as vt where 1 = 1 $ssql3 AND vt.eStatus != 'Deleted'   AND eIconType != 'Fly' ";

    $vehicle_type_id = $obj->MySQLSelect($sql);

    if (!empty($vehicle_type_id)) {

        $search_array = explode(',', $vehicle_type_id[0]['Id']);

        $query_array = array();

        foreach ($search_array as $needle) {

            $query_array[] = sprintf('FIND_IN_SET("%s",%s)', $needle, 'dv.vCarType');

        }

        $query_str = implode(' OR ', $query_array);

        $ssql .= ' AND ( ' . $query_str . ' )';

    }

    $script .= $eType;

    $queryString = 'eType=' . $eType;

    if ($eType == 'Deliver') {

        $eType = '';

    }

}

if ($keyword != '') {

    if ($option != '') {

        if ($eStatus != '') {

            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND dv.eStatus = '" . clean($eStatus) . "'";

        } else {

            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";

        }

    } else {

        if (ONLYDELIVERALL == 'Yes') {

            if ($eStatus != '') {

                $ssql .= " AND (m.vMake LIKE '%" . clean($keyword) . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%') AND dv.eStatus = '" . clean($eStatus) . "'";

            } else {

                $ssql .= " AND (m.vMake LIKE '%" . clean($keyword) . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%')";

            }

        } else {

            if ($eStatus != '') {

                $ssql .= " AND (m.vMake LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%') AND dv.eStatus = '" . clean($eStatus) . "'";

            } else {

                $ssql .= " AND (m.vMake LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%')";

            }

        }

    }

} else if ($eStatus != '' && $keyword == '' && $eType == '') {

    $ssql .= " AND dv.eStatus = '" . clean($eStatus) . "'";

} else if ($eType != '' && $keyword == '' && $eStatus == '' && $eType != 'Ambulance') {

    $ssql .= " AND dv.eType = '" . clean($eType) . "'";

} else if ($eType != '' && $keyword == '' && $eStatus != '') {

    $ssql .= " AND dv.eStatus = '" . clean($eStatus) . "' AND dv.eType = '" . clean($eType) . "'";

}

//here it is not put bc from admin side etype is always added as ride

//if(!$MODULES_OBJ->isRideFeatureAvailable()) {

//    $ssql .= " AND dv.eType != 'Ride'";

//}

//if(!$MODULES_OBJ->isDeliveryFeatureAvailable()) {

//    $ssql .= " AND dv.eType != 'Delivery'";

//}

//if(!$MODULES_OBJ->isUberXFeatureAvailable()) { 

//    $ssql .= " AND dv.eType != 'UberX'";     

//}

// End Search Parameters

if ($iDriverId != "") {

    $query1 = "SELECT COUNT(iDriverVehicleId) as total FROM driver_vehicle where iDriverId ='" . $iDriverId . "'";

    $totalData = $obj->MySQLSelect($query1);

    $total_vehicle = $totalData[0]['total'];

    $actionSearch = isset($_REQUEST['actionSearch']) ? $_REQUEST['actionSearch'] : 0;

    if ($total_vehicle > 1 || ($total_vehicle == "1" && $actionSearch == "1")) {

        $ssql .= " AND dv.iDriverId='" . $iDriverId . "'";

    }

}

//Pagination Start

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

if ($eStatus != '') {

    $eStatussql = " AND dv.eType != 'UberX' AND rd.eStatus != 'Deleted'";

} else {

    $eStatussql = " AND dv.eStatus != 'Deleted' AND dv.eType != 'UberX' AND rd.eStatus != 'Deleted' ";

}

if (ONLYDELIVERALL == 'Yes') {

    $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md WHERE 1=1 AND dv.iDriverId = rd.iDriverId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId" . $eStatussql . $ssql . $dri_ssql;

} else {

    if ($APP_TYPE == 'UberX') {

        $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md, company c WHERE 1=1 AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId" . $eStatussql . $ssql . $dri_ssql;

    } else {

        $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md, company c WHERE 1=1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId" . $eStatussql . $ssql . $dri_ssql;

    }

}

$totalData = $obj->MySQLSelect($sql);

$total_results = $totalData[0]['Total'];

$total_pages = ceil($total_results / $per_page); //total pages we going to have

$show_page = 1;

//-------------if page is setcheck------------------//

if (isset($_GET['page'])) {

    $show_page = $_GET['page'];             //it will telles the current page

    if ($show_page > 0 && $show_page <= $total_pages) {

        $start = ($show_page - 1) * $per_page;

        $end = $start + $per_page;

    } else {

        // error - show first set of results

        $start = 0;

        $end = $per_page;

    }

} else {

    // if page isn't set, show first set of results

    $start = 0;

    $end = $per_page;

}

// display pagination

$page = isset($_GET['page']) ? intval($_GET['page']) : 0;

$tpages = $total_pages;

if ($page <= 0) $page = 1;

//Pagination End

if (!empty($eStatus)) {

    $eQuery = " AND dv.eType != 'UberX' AND rd.eStatus != 'Deleted'";

} else {

    $eQuery = " AND dv.eStatus != 'Deleted' AND dv.eType != 'UberX' AND rd.eStatus != 'Deleted'";

}

if (ONLYDELIVERALL == 'Yes') {

    $sql = "SELECT dv.iDriverVehicleId, dv.iDriverId, dv.eStatus, m.vMake, md.vTitle,CONCAT(rd.vName,' ',rd.vLastName) AS driverName, dv.eType, rd.tSessionId FROM driver_vehicle dv, register_driver rd, make m, model md WHERE 1=1 AND dv.iDriverId = rd.iDriverId  AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eQuery $ssql $dri_ssql $ord LIMIT $start, $per_page";

} else {

    if ($APP_TYPE == 'UberX') {

        $sql = "SELECT dv.iDriverVehicleId,dv.eStatus,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,dv.vLicencePlate, c.vCompany, dv.eType, rd.tSessionId FROM driver_vehicle dv, register_driver rd,company c WHERE 1=1 AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId $eQuery $ssql $dri_ssql";

    } else {

        $sql = "SELECT dv.iDriverVehicleId, dv.iDriverId, dv.eStatus, m.vMake, md.vTitle,CONCAT(rd.vName,' ',rd.vLastName) AS driverName, c.vCompany, dv.eType, rd.tSessionId FROM driver_vehicle dv, register_driver rd, make m, model md, company c WHERE 1=1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eQuery $ssql $dri_ssql $ord LIMIT $start, $per_page";

    }

}

$data_drv = $obj->MySQLSelect($sql);

/* if($APP_TYPE == 'Ride-Delivery'){

  $eTypeQuery = " AND (eType='Ride' OR eType='Delivery')";

  } else if($APP_TYPE == 'Ride-Delivery-UberX'){

  $eTypeQuery = " AND (eType='Ride' OR eType='Delivery' OR eType='UberX')";

  } else {

  $eTypeQuery = " AND eType='".$APP_TYPE."'";

  } */

$sql1 = "SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='car' AND status = 'Active'";

$doc_count_query = $obj->MySQLSelect($sql1);

$doc_count = scount($doc_count_query);

$drv_name = "";

if ($iDriverId != "") {

    if ($total_vehicle > 1 || ($total_vehicle == "1" && $actionSearch == "1")) {

        $drv_name = $data_drv[0]['driverName'];

        $keyword = $drv_name;

    }

}

$endRecord = scount($data_drv);

$var_filter = "";

foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;







?>

<!DOCTYPE html>

<html lang="en">

<!-- BEGIN HEAD-->

<head>

    <meta charset="UTF-8"/>

    <title><?= $SITE_NAME ?> | Manage <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Vehicles</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <?php include_once('global_files.php'); ?>

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

                        <?

                        $drv_text = ($drv_name != "") ? "Vehicles of " . clearName($drv_name) : $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];

                        ?>

                        <h2><?= $drv_text ?> Vehicles</h2>

                    </div>

                </div>

                <hr/>

            </div>

            <?php include('valid_msg.php'); ?>

            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">

                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">

                    <tbody>

                    <tr>

                        <td width="5%">

                            <label for="textfield">

                                <strong>Search:</strong>

                            </label>

                        </td>

                        <td width="10%" class=" padding-right10">

                            <select name="option" id="option" class="form-control">

                                <option value="">All</option>

                                <option value="m.vMake" <?php if ($option == "m.vMake") {

                                    echo "selected";

                                } ?> >Vehicle

                                </option>

                                <? if (ONLYDELIVERALL != 'Yes') { ?>

                                    <option value="c.vCompany" <?php if ($option == 'c.vCompany') {

                                        echo "selected";

                                    } ?> ><? if ($MODULES_OBJ->isStorePersonalDriverAvailable() > 0) { ?>Company/Store<? } else { ?>Company <? } ?></option>

                                <? } ?>

                                <option value="CONCAT(rd.vName,' ',rd.vLastName)" <?php if ($option == "CONCAT(rd.vName,' ',rd.vLastName)" || ($iDriverId != "" && $drv_name != "")) {

                                    echo "selected";

                                } ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>

                                <? if ( ( $APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && ONLYDELIVERALL != 'Yes') { ?>

                                    <option value="dv.eType" <?php if ($option == 'dv.eType') {

                                        echo "selected";

                                    } ?> >Vehicle Type

                                    </option>

                                <? } ?>

                                <!-- <option value="dv.eStatus" <?php if ($option == 'dv.eStatus') {

                                    echo "selected";

                                } ?> >Status</option> -->

                            </select>

                        </td>

                        <td width="15%" class="searchform">

                            <input type="Text" id="keyword" name="keyword" value="<?php echo clearName($keyword); ?>"

                                   class="form-control"/>

                        </td>

                        <td width="13%" class="eType_options" id="eType_options">

                            <select name="eType" id="eType_value" class="form-control">

                                <option value="">Select Status</option>

                                <? if ($MODULES_OBJ->isRideFeatureAvailable()) { ?>

                                <option value='Ride' <?php if ($eType == 'Ride') {

                                    echo "selected";

                                } ?> >Ride</option><? }

                                if ($MODULES_OBJ->isDeliveryFeatureAvailable()) { ?>

                                <option value='Delivery' <?php if ($eType == 'Delivery') {

                                    echo "selected";

                                } ?> >Delivery</option><?php } ?>

                            </select>

                        </td>

                        <td width="13%" class="estatus_options" id="eStatus_options">

                            <select name="eStatus" id="estatus_value" class="form-control">

                                <option value="">Select Status</option>

                                <option value='Active' <?php if ($eStatus == 'Active') {

                                    echo "selected";

                                } ?> >Active

                                </option>

                                <option value="Inactive" <?php if ($eStatus == 'Inactive') {

                                    echo "selected";

                                } ?> >Inactive

                                </option>

                                <option value="Deleted" <?php if ($eStatus == 'Deleted') {

                                    echo "selected";

                                } ?> >Delete

                                </option>

                            </select>

                        </td>

                        <!-- <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td> -->

                        <td>

                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"

                                   title="Search"/>

                            <input type="button" value="Reset" class="btnalt button11"

                                   onClick="window.location.href = 'vehicles.php'"/>

                        </td>

                        <?php if ($userObj->hasPermission($create)) { ?>

                            <td width="30%">

                                <a class="add-btn"

                                   href="vehicle_add_form.php<?php echo ($queryString != '') ? '?' . $queryString : ''; ?>"

                                   style="text-align: center;">ADD

                                    VEHICLES

                                </a>

                            </td>

                        <?php } ?>

                    </tr>

                    </tbody>

                </table>

            </form>

            <div class="table-list">

                <div class="row">

                    <div class="col-lg-12">

                        <div class="admin-nir-export">

                            <div class="changeStatus col-lg-12 option-box-left">

                                        <span class="col-lg-2 new-select001">

<?php if ($userObj->hasPermission(array_merge($updateStatus, $delete))) { ?>

    <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">

                                                    <option value="">Select Action</option>

                                        <?php if ($userObj->hasPermission($updateStatus)) { ?>

                                            <option value="Active"

        <?php if ($option == 'Active') {

            echo "selected";

        } ?> > Activate</option>

                                            <option value="Inactive" <?php if ($option == 'Inactive') {

                                                echo "selected";

                                            } ?> >Deactivate</option>

                                        <?php } ?>

        <?php if ($eStatus != 'Deleted' && $userObj->hasPermission($delete)) { ?>

            <option value="Deleted" <?php if ($option == 'Delete') {

                echo "selected";

            } ?> >Delete</option>

        <?php } ?>

                                                </select>

<?php } ?>

                                        </span>

                            </div>

                            <?php if (!empty($data_drv)) { ?>

                                <!--<div class="panel-heading">

                                    <form name="_export_form" id="_export_form" method="post" >

                                        <button type="button" onclick="showExportTypes('vehicles')" >Export</button>

                                    </form>

                                </div>-->

                            <?php } ?>

                        </div>

                        <div style="clear:both;"></div>

                        <div class="table-responsive">

                            <form class="_list_form" id="_list_form" method="post"

                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">

                                <table class="table table-striped table-bordered table-hover">

                                    <thead>

                                    <tr>

                                        <th width="3%" class="align-center">

                                            <input type="checkbox" id="setAllCheck">

                                        </th>

                                        <th width="20%">

                                            <a href="javascript:void(0);"

                                               onClick="Redirect(1,<?php if ($sortby == '1') {

                                                   echo $order;

                                               } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_LEFT_MENU_VEHICLES']; ?> <?php if ($sortby == 1) {

                                                    if ($order == 0) { ?>

                                                        <i class="fa fa-sort-amount-asc"

                                                           aria-hidden="true"></i> <?php } else { ?>

                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

                                                } else { ?>

                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                        </th>

                                        <? if (ONLYDELIVERALL != 'Yes') { ?>

                                            <th width="20%">

                                                <a href="javascript:void(0);"

                                                   onClick="Redirect(2,<?php if ($sortby == '2') {

                                                       echo $order;

                                                   } else { ?>0<?php } ?>)"><? if ($MODULES_OBJ->isStorePersonalDriverAvailable() > 0) { ?>Company/Store<? } else { ?>Company <? } ?> <?php if ($sortby == 2) {

                                                        if ($order == 0) { ?>

                                                            <i class="fa fa-sort-amount-asc"

                                                               aria-hidden="true"></i> <?php } else { ?>

                                                            <i class="fa fa-sort-amount-desc"

                                                               aria-hidden="true"></i><?php }

                                                    } else { ?>

                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                            </th>

                                        <? } ?>

                                        <th width="20%">

                                            <a href="javascript:void(0);"

                                               onClick="Redirect(3,<?php if ($sortby == '3') {

                                                   echo $order;

                                               } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php if ($sortby == 3) {

                                                    if ($order == 0) { ?>

                                                        <i class="fa fa-sort-amount-asc"

                                                           aria-hidden="true"></i> <?php } else { ?>

                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

                                                } else { ?>

                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                        </th>

                                        <?php if ($doc_count != 0) {

                                            if ($APP_TYPE != 'UberX' && $userObj->hasPermission($editDocument)) {

                                                ?>

                                                <th width="8%" class="align-center">View/Edit Document(s)</th>

                                                <?php

                                            }

                                        }

                                        /* if($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery'){ */

                                        ?>

                                        <!-- <th width="8%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {

                                            echo $order;

                                        } else { ?>0<?php } ?>)">Service Type <?php if ($sortby == 4) {

                                            if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>  -->

                                        <? //}  ?>

                                        <th width="8%" class="align-center">

                                            <a href="javascript:void(0);"

                                               onClick="Redirect(5,<?php if ($sortby == '5') {

                                                   echo $order;

                                               } else { ?>0<?php } ?>)">Status <?php if ($sortby == 5) {

                                                    if ($order == 0) { ?>

                                                        <i class="fa fa-sort-amount-asc"

                                                           aria-hidden="true"></i> <?php } else { ?>

                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

                                                } else { ?>

                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>

                                        </th>

                                        <th width="8%" class="align-center">Action</th>

                                    </tr>

                                    </thead>

                                    <tbody>

                                    <?php

                                    if (!empty($data_drv)) {

                                        for ($i = 0; $i < scount($data_drv); $i++) {

                                            $default = '';

                                            if (isset($data_drv[$i]['eDefault']) && $data_drv[$i]['eDefault'] == 'Yes') {

                                                $default = 'disabled';

                                            }

                                            if ($APP_TYPE == 'UberX') {

                                                $vname = $data_drv[$i]['vLicencePlate'];

                                            } else {

                                                $vname = $data_drv[$i]['vMake'] . ' ' . $data_drv[$i]['vTitle'];

                                            }

                                            ?>

                                            <tr class="gradeA">

                                                <td align="center">

                                                    <input type="checkbox" id="checkbox"

                                                           name="checkbox[]" <?php echo $default; ?>

                                                           value="<?php echo $data_drv[$i]['iDriverVehicleId']; ?>"/>&nbsp;

                                                </td>

                                                <td><?= $vname; ?></td>

                                                <? if (ONLYDELIVERALL != 'Yes') { ?>

                                                    <td><?= clearCmpName($data_drv[$i]['vCompany']); ?></td>

                                                <? } ?>

                                                <td><?= clearName($data_drv[$i]['driverName']); ?></td>

                                                <?php if ($doc_count != 0) {

                                                    if ($APP_TYPE != 'UberX' && $userObj->hasPermission($editDocument)) {

                                                        ?>

                                                        <td align="center" width="12%">

                                                            <a href="vehicle_document_action.php?id=<?= $data_drv[$i]['iDriverVehicleId']; ?>&vehicle=<?= $data_drv[$i]['vMake'] ?>&eType=<?= $REQUEST_eType; ?>"

                                                               data-toggle="tooltip"

                                                               title="Edit <?= $langage_lbl_admin['LBL_Vehicle']; ?> Document">

                                                                <img src="img/edit-doc.png" alt="Edit Document">

                                                            </a>

                                                        </td>

                                                        <?php

                                                    }

                                                }

                                                if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {

                                                    ?>

                                                    <!-- <td><?= $data_drv[$i]['eType']; ?></td> -->

                                                <? } ?>

                                                <td align="center">

                                                    <?php

                                                    if ($data_drv[$i]['eStatus'] == 'Active') {

                                                        $dis_img = "img/active-icon.png";

                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {

                                                        $dis_img = "img/inactive-icon.png";

                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {

                                                        $dis_img = "img/delete-icon.png";

                                                    }

                                                    ?>

                                                    <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip"

                                                         title="<?php echo $data_drv[$i]['eStatus']; ?>">

                                                </td>

                                                <td align="center" class="action-btn001">

                                                    <?php if ($userObj->hasPermission(array_merge($edit, $updateStatus, $delete))) { ?>

                                                        <div class="share-button openHoverAction-class"

                                                             style="display: block;">

                                                            <label class="entypo-export">

                                                                <span><img src="images/settings-icon.png" alt=""></span>

                                                            </label>

                                                            <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iDriverVehicleId']; ?>">

                                                                <ul>

                                                                    <?php if ($userObj->hasPermission($edit)) { ?>

                                                                        <li class="entypo-twitter"

                                                                            data-network="twitter">

                                                                            <a href="vehicle_add_form.php?id=<?= $data_drv[$i]['iDriverVehicleId']; ?>&vehicle=<?= $data_drv[$i]['vMake'] ?>&<?php echo ($queryString != '') ? $queryString : ''; ?>"

                                                                               data-toggle="tooltip" title="Edit">

                                                                                <img src="img/edit-icon.png" alt="Edit">

                                                                            </a>

                                                                        </li>

                                                                    <?php } ?>

                                                                    <?php if ($userObj->hasPermission($updateStatus)) { ?>

                                                                        <li class="entypo-facebook"

                                                                            data-network="facebook">

                                                                            <a href="javascript:void(0);"

                                                                               onClick="changeStatus('<?php echo $data_drv[$i]['iDriverVehicleId']; ?>', 'Inactive')"

                                                                               data-toggle="tooltip" title="Activate">

                                                                                <img src="img/active-icon.png"

                                                                                     alt="<?php echo $data_drv[$i]['eStatus']; ?>">

                                                                            </a>

                                                                        </li>

                                                                        <li class="entypo-gplus" data-network="gplus">

                                                                            <a href="javascript:void(0);"

                                                                               onClick="changeStatus('<?php echo $data_drv[$i]['iDriverVehicleId']; ?>', 'Active')"

                                                                               data-toggle="tooltip" title="Deactivate">

                                                                                <img src="img/inactive-icon.png"

                                                                                     alt="<?php echo $data_drv[$i]['eStatus']; ?>">

                                                                            </a>

                                                                        </li>

                                                                    <?php } ?>

                                                                    <?php if ($eStatus != 'Deleted' && $userObj->hasPermission($delete)) { ?>

                                                                        <li class="entypo-gplus" data-network="gplus">

                                                                            <a href="javascript:void(0);"

                                                                               onclick="changeStatusDeletevehicleCustom('<?php echo $data_drv[$i]['iDriverVehicleId']; ?>', '<?php echo $data_drv[$i]['iDriverId']; ?>','<?php echo $data_drv[$i]['eStatus']; ?>')"

                                                                               data-toggle="tooltip" title="Delete">

                                                                                <img src="img/delete-icon.png"

                                                                                     alt="Delete">

                                                                            </a>

                                                                        </li>

                                                                    <?php } ?>

                                                                </ul>

                                                            </div>

                                                        </div>

                                                    <?php } else {

                                                        echo "--";

                                                    } ?>

                                                </td>

                                            </tr>

                                        <?php }

                                    } else { ?>

                                        <tr class="gradeA">

                                            <td colspan="8"> No Records Found.</td>

                                        </tr>

                                    <?php } ?>

                                    </tbody>

                                </table>

                            </form>

                            <?php include('pagination_n.php'); ?>

                        </div>

                    </div> <!--TABLE-END-->

                </div>

            </div>

            <div class="admin-notes">

                <h4>Notes:</h4>

                <ul>

                    <li>

                        Vehicles module will list all Vehicles on this page.

                    </li>

                    <li>

                        Administrator can Activate / Deactivate / Delete any Vehicle.

                    </li>

                    <!--<li>

                        Administrator can export data in XLS or PDF format.

                    </li>-->

                </ul>

            </div>

        </div>

    </div>

    <!--END PAGE CONTENT -->

</div>

<!--END MAIN WRAPPER -->

<div class="row loding-action" id="imageIcon" style="display:none; z-index: 99999">

    <div align="center">

        <img src="default.gif">

    </div>

</div>

<form name="pageForm" id="pageForm" action="action/vehicles.php" method="post">

    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">

    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">

    <input type="hidden" name="iDriverVehicleId" id="iMainId01" value="">

    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">

    <input type="hidden" name="eType" id="eType" value="<?php echo $eType; ?>">

    <input type="hidden" name="status" id="status01" value="">

    <input type="hidden" name="statusVal" id="statusVal" value="">

    <input type="hidden" name="option" value="<?php echo $option; ?>">

    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">

    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">

    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">

    <input type="hidden" name="iDriverId" id="iDriverId" value="<?php echo $iDriverId; ?>">

    <input type="hidden" name="method" id="method" value="">

</form>

<div data-backdrop="static" data-keyboard="false" class="modal fade" id="delete_driver_vehicle" tabindex="-1"

     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4></h4>

            </div>

            <div class="modal-body">

                <p><?= $langage_lbl_admin['LBL_ACTIVE_VEHICLE_NOT_DELETE'] ?></p>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">OK</button>

            </div>

        </div>

    </div>

</div>

<?php include_once('footer.php'); ?>

<script>



    function changeStatusDeletevehicleCustom(drivervehicleid, driverid, vehicle_status) {

        if (vehicle_status == 'Active') {

            $('#delete_driver_vehicle').modal('show');

        } else {

            changeStatusDeletevehicle(drivervehicleid, driverid);

        }

    }





    $(document).ready(function () {

        $('#eType_options').hide();

        $('#option').each(function () {

            if (this.value == 'dv.eType') {

                $('#eType_options').show();

                $('.searchform').hide();

            }

        });

    });

    $(function () {

        $('#option').change(function () {

            if ($('#option').val() == 'dv.eType') {

                $('#eType_options').show();

                $("input[name=keyword]").val("");

                $('.searchform').hide();

            } else {

                $('#eType_options').hide();

                $("#eType_value").val("");

                $('.searchform').show();

            }

        });

    });



    $("#setAllCheck").on('click', function () {

        if ($(this).prop("checked")) {

            jQuery("#_list_form input[type=checkbox]").each(function () {

                if ($(this).attr('disabled') != 'disabled') {

                    this.checked = 'true';

                }

            });

        } else {

            jQuery("#_list_form input[type=checkbox]").each(function () {

                this.checked = '';

            });

        }

    });



    $("#Search").on('click', function () {

        //$('html').addClass('loading');

        var action = $("#_list_form").attr('action');



        var formValus = $("#frmsearch").serialize();



        window.location.href = action + "?" + formValus;

    });



    $('.entypo-export').click(function (e) {

        e.stopPropagation();

        var $this = $(this).parent().find('div');

        $(".openHoverAction-class div").not($this).removeClass('active');

        $this.toggleClass('active');

    });



    $(document).on("click", function (e) {

        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {

            $(".show-moreOptions").removeClass("active");

        }

    });

</script>

</body>

<!-- END BODY-->

</html>