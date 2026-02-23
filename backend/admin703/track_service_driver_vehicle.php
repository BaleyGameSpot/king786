<?php
include_once('../common.php');

 
if (!$userObj->hasPermission('view-driver-vehicle-trackservice')) {
    $userObj->redirect();
}
$script = "TrackServiceDriverVehicle";
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dv.iDriverVehicleId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY m.vMake ASC";
    else
        $ord = " ORDER BY m.vMake DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY c.vCompany ASC";
    else
        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY dv.vLicencePlate ASC";
    else
        $ord = " ORDER BY dv.vLicencePlate DESC";
}

if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY dv.eStatus ASC";
    else
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
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$ssql = '';
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
} else if ($eType != '' && $keyword == '' && $eStatus == '') {
    $ssql .= " AND dv.eType = '" . clean($eType) . "'";
} else if ($eType != '' && $keyword == '' && $eStatus != '') {
    $ssql .= " AND dv.eStatus = '" . clean($eStatus) . "' AND dv.eType = '" . clean($eType) . "'";
}
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
    $eStatussql = " AND dv.eType = 'TrackService' AND rd.eStatus != 'Deleted'";
} else {
    $eStatussql = " AND dv.eStatus != 'Deleted' AND dv.eType = 'TrackService' AND rd.eStatus != 'Deleted' ";
}

$sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md, track_service_company c WHERE 1=1 AND dv.iDriverId = rd.iDriverId AND dv.iTrackServiceCompanyId = c.iTrackServiceCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND rd.iTrackServiceCompanyId > 0 " . $eStatussql . $ssql . $dri_ssql;

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
if ($page <= 0)
    $page = 1;
//Pagination End

if (!empty($eStatus)) {
    $eQuery = " AND dv.eType = 'TrackService' AND rd.eStatus != 'Deleted'";
} else {
    $eQuery = " AND dv.eStatus != 'Deleted' AND dv.eType = 'TrackService' AND rd.eStatus != 'Deleted'";
}

$sql = "SELECT dv.iDriverVehicleId, dv.iDriverId, dv.eStatus, rd.iTrackServiceCompanyId,dv.vLicencePlate, m.vMake, md.vTitle, CONCAT(rd.vName,' ',rd.vLastName) AS driverName, c.vCompany, dv.eType, rd.tSessionId FROM driver_vehicle dv, register_driver rd, make m, model md, track_service_company c WHERE 1=1 AND dv.iDriverId = rd.iDriverId AND dv.iTrackServiceCompanyId = c.iTrackServiceCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId AND rd.iTrackServiceCompanyId > 0 $eQuery $ssql $dri_ssql $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);


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
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
<head>
<meta charset="UTF-8" />
<title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Vehicles</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<?php include_once('global_files.php'); ?>

</head>
<!-- END  HEAD-->

<!-- BEGIN BODY-->
<body class="padTop53 " >
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
                        <?php $drv_text = ($drv_name != "") ? "Vehicles of " . clearName($drv_name) : $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                            <h2><?= $drv_text ?> Vehicles</h2>
                        </div>
                    </div>
                    <hr />
                </div>
                <?php include('valid_msg.php'); ?>
                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                            <tr>
                                <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                <td width="10%" class=" padding-right10">
                                    <select name="option" id="option" class="form-control">
                                        <option value="">All</option>
                                        <option  value="m.vMake" <?php if ($option == "m.vMake") {
                                            echo "selected";
                                        } ?> >Vehicle</option>
                                        <option value="c.vCompany" <?php if ($option == 'c.vCompany') {
                                            echo "selected";
                                        } ?> >Company</option>
                                        <option value="" <?php if ($option == "CONCAT(rd.vName,' ',rd.vLastName)" || ($iDriverId != "" && $drv_name != "")) {
                                            echo "selected";
                                        } ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>

                                        <option value="dv.vLicencePlate" <?php if ($option == 'dv.vLicencePlate') {
                                            echo "selected";
                                        } ?> >License Plate</option>
                                    </select>
                                </td>
                                <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo clearName($keyword); ?>"  class="form-control" /></td>

                                <td width="13%" class="estatus_options" id="eStatus_options" >
                                    <select name="eStatus" id="estatus_value" class="form-control">
                                        <option value="" >Select Status</option>
                                        <option value='Active' <?php if ($eStatus == 'Active') {
                                            echo "selected";
                                        } ?> >Active</option>
                                        <option value="Inactive" <?php if ($eStatus == 'Inactive') {
                                            echo "selected";
                                        } ?> >Inactive</option>
                                        <option value="Deleted" <?php if ($eStatus == 'Deleted') {
                                            echo "selected";
                                        } ?> >Delete</option>
                                    </select>
                                </td>

                                <td>
                                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                    <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'track_service_driver_vehicle.php'"/>
                                </td>
                                <?php if ($userObj->hasPermission('create-driver-vehicle-trackservice')) { ?>
                                    <td width="30%"><a class="add-btn" href="track_service_driver_vehicle_action.php" style="text-align: center;">ADD VEHICLE</a></td>
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
                                        <?php if ($userObj->hasPermission(['update-status-driver-vehicle-trackservice', 'delete-driver-vehicle-trackservice'])) { ?>
                                            <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                <option value="" >Select Action</option>
                                                <?php if ($userObj->hasPermission('update-status-driver-vehicle-trackservice')) { ?>
        										<option value="Active"
                                                <?php if ($option == 'Active') {
                                                    echo "selected";
                                                } ?> > Activate</option>

                                                <option value="Inactive" <?php if ($option == 'Inactive') {
                                                    echo "selected";
                                                } ?> >Deactivate</option>
                                                <?php } ?>
                                                <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-driver-vehicle-trackservice')) { ?>
                                                    <option value="Deleted" <?php if ($option == 'Delete') {
                                                            echo "selected";
                                                        } ?> >Delete</option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </span>
                                </div>
                            </div>
                            <div style="clear:both;"></div>
                            <div class="table-responsive">
                                <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th width="3%" class="align-center"><input type="checkbox" id="setAllCheck" ></th>
                                                <th width="20%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1') { echo $order; } else { ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_LEFT_MENU_VEHICLES']; ?> <?php if ($sortby == 1) {
                                                if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                <th width="15%"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') { echo $order; } else { ?>0<?php } ?>)">Licence Plate <?php if ($sortby == 4) {
                                                        if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                <th width="18%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') { echo $order; } else { ?>0<?php } ?>)">Company <?php if ($sortby == 2) {
                                                        if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                            
                                                <th width="18%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {
                                                echo $order;
                                            } else { ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?php if ($sortby == 3) {
                                                if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                <th width="10%" class="align-center">View/Edit Document(s)</th>
                                               
                                                <th width="9%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') { echo $order; } else { ?>0<?php } ?>)">Status <?php if ($sortby == 5) {
                                                        if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                            } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                <th width="8%" class="align-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (!empty($data_drv)) {
                                            for ($i = 0; $i < scount($data_drv); $i++) {

                                                $default = '';
                                                if ($data_drv[$i]['eDefault'] == 'Yes') {
                                                    $default = 'disabled';
                                                }

                                                $vname = $data_drv[$i]['vMake'] . ' ' . $data_drv[$i]['vTitle'];
                                                ?>
                                                    <tr class="gradeA">
                                                        <td align="center"><input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iDriverVehicleId']; ?>" />&nbsp;</td>
                                                        <td><?= $vname; ?></td>
                                                        <td><?= clearName($data_drv[$i]['vLicencePlate']); ?></td>
 														<td> <?php if ($userObj->hasPermission('view-track-service-company')) { ?><a href="javascript:void(0);" onClick="show_track_company_details('<?= $data_drv[$i]['iTrackServiceCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearCmpName($data_drv[$i]['vCompany']); ?><?php if ($userObj->hasPermission('view-track-service-company')) { ?></a><?php } ?></td>
 														<td><?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['driverName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?></td>
                                                        <?php if ($doc_count != 0) { ?>
                                                        <td align="center" >
                                                            <a href="vehicle_document_action.php?id=<?= $data_drv[$i]['iDriverVehicleId']; ?>&vehicle=<?= $data_drv[$i]['vMake'] ?>" data-toggle="tooltip" title="Edit <?= $langage_lbl_admin['LBL_Vehicle']; ?> Document">
                                                                <img src="img/edit-doc.png" alt="Edit Document" >
                                                            </a>
                                                        </td>
                                                        <?php } ?>
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
                                                            <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                        </td>
                                                        <td align="center" class="action-btn001">
                                                            <div class="share-button openHoverAction-class" style="display: block;">
                                                                <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iDriverVehicleId']; ?>">
                                                                    <ul>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a href="track_service_driver_vehicle_action.php?id=<?= $data_drv[$i]['iDriverVehicleId']; ?>&vehicle=<?= $data_drv[$i]['vMake'] ?>" data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit" >
                                                                            </a>
                                                                        </li>
                                                                        <?php if ($userObj->hasPermission('update-status-driver-vehicle-trackservice')) { ?>
                                                                        <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iDriverVehicleId']; ?>', 'Inactive')"  data-toggle="tooltip" title="Activate">
                                                                                <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                            </a></li>
                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iDriverVehicleId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >	
                                                                            </a></li>
                                                                        <?php } ?>
                                                                        <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-driver-vehicle-trackservice')) { ?>
                                                                        <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDeletevehicleCustom('<?php echo $data_drv[$i]['iDriverVehicleId']; ?>', '<?php echo $data_drv[$i]['iDriverId']; ?>','<?php echo $data_drv[$i]['eStatus']; ?>')" data-toggle="tooltip" title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete" >
                                                                            </a></li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
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
    <form name="pageForm" id="pageForm" action="action/track_service_driver_vehicle.php" method="post" >
        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
        <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
        <input type="hidden" name="iDriverVehicleId" id="iMainId01" value="" >
        <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
        <input type="hidden" name="vLicencePlate" id="vLicencePlate" value="<?php echo $vLicencePlate; ?>" >
        <input type="hidden" name="status" id="status01" value="" >
        <input type="hidden" name="statusVal" id="statusVal" value="" >
        <input type="hidden" name="option" value="<?php echo $option; ?>" >
        <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
        <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
        <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
        <input type="hidden" name="iDriverId" id="iDriverId" value="<?php echo $iDriverId; ?>">
        <input type="hidden" name="method" id="method" value="" >
    </form>
    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="delete_driver_vehicle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h4></h4></div>
                <div class="modal-body"><p><?= $langage_lbl_admin['LBL_ACTIVE_VEHICLE_NOT_DELETE'] ?></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">OK</button></div>
            </div>
        </div>
    </div>
    <?php include_once('footer.php'); ?>
	
	 
<div class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>Track Service <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details<button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons1" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="track_company_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4>
					<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>Track Service Company Details
					<button type="button" class="close" data-dismiss="modal">x</button>
				</h4>
			</div>
			<div class="modal-body" style="max-height: 450px;overflow: auto;">
				<div id="track_company_imageIcons" style="display:none">
					<div align="center">
						<img src="default.gif">
						<br/> <span>Retrieving details,please Wait...</span>
					</div>
				</div>
				<div id="track_comp_detail"></div>
			</div>
		</div>
	</div>
</div>

    <script>
        
        function changeStatusDeletevehicleCustom(drivervehicleid,driverid,vehicle_status) {
            if(vehicle_status == 'Active') {
                $('#delete_driver_vehicle').modal('show');
            } else {
                changeStatusDeletevehicle(drivervehicleid,driverid);
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