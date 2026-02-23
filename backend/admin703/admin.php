<?php
include_once('../common.php');
$admin = isset($_REQUEST['admin']) ? $_REQUEST['admin'] : '';

if (!$userObj->hasPermission('view-admin') && $admin != "hotels") {
    $userObj->redirect();
} else if (!$userObj->hasPermission('view-hotel') && $admin == "hotels") {
    $userObj->redirect();
}
$create = "create-admin";
$edit = "edit-admin";
$delete = "delete-admin";
$updateStatus = "update-status-admin";
$urlAppend = "";
if ($admin == "hotels") {
    $create = "create-hotel";
    $edit = "edit-hotel";
    $delete = "delete-hotel";
    $updateStatus = "update-status-hotel";
    $urlAppend = "&admin=hotels";
}

$script = 'Admin';
if ($admin == "hotels") {
	$script = 'Hotels';
}
$query = Models\Administrator::with([
    'roles',
    'locations'
]);

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$var_filter = isset($var_filter) ? $var_filter : '';
$ord = ' ORDER BY a.iAdminId ASC';
if ($sortby == 1) {
    if ($order == 0) {
        $ord = " ORDER BY vFirstName ASC";
    } else {
        $ord = " ORDER BY vFirstName DESC";
    }
}
if ($sortby == 2) {
    if ($order == 0) {
        $ord = " ORDER BY vEmail ASC";
    } else {
        $ord = " ORDER BY vEmail DESC";
    }
}
if ($sortby == 3) {
    if ($order == 0) {
        $ord = " ORDER BY a.iGroupId ASC";
    } else {
        $ord = " ORDER BY a.iGroupId DESC";
    }
}
if ($sortby == 4) {
    if ($order == 0) {
        $ord = " ORDER BY a.eStatus ASC";
    } else {
        $ord = " ORDER BY a.eStatus DESC";
    }
}
if ($sortby == 6) {
    if ($order == 0) {
        $ord = " ORDER BY h.tRegistrationDate ASC";
    } else {
        $ord = " ORDER BY h.tRegistrationDate DESC";
    }
}
//End Sorting
$hotelPanel = ($MODULES_OBJ->isEnableHotelPanel('Yes')) ? "Yes" : "No";
$kioskPanel = ($MODULES_OBJ->isEnableKioskPanel('Yes')) ? "Yes" : "No";
$ssql = '';
if (ONLYDELIVERALL == 'Yes' || $THEME_OBJ->isRideCXThemeActive() == 'Yes' || $THEME_OBJ->isRideDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXv2ThemeActive() == 'Yes' || $THEME_OBJ->isServiceXThemeActive() == 'Yes' || $THEME_OBJ->isServiceXv2ThemeActive() == 'Yes' || $THEME_OBJ->isRideCXv2ThemeActive() == 'Yes' || $hotelPanel == 'No') {
    $ssql .= " AND a.iGroupId != 4";
}


if ($admin == "hotels") {
	$ssql .= " AND a.iGroupId = 4";
}else{
    $ssql .= " AND a.iGroupId != 4";
}
$role_sql = "select * from admin_groups a where eStatus = 'Active'" . $ssql;
$role_sql_data = $obj->MySQLSelect($role_sql);
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$eRole = isset($_REQUEST['eRole']) ? stripslashes($_REQUEST['eRole']) : "";

if ($keyword != '') {
    $keyword_new = $keyword;
    $chracters = array(
        "(",
        "+",
        ")"
    );
    $removespacekeyword = preg_replace('/\s+/', '', $keyword);
    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
    if (is_numeric($keyword_new)) {
        $keyword_new = $keyword_new;
    } else {
        $keyword_new = $keyword;
    }
    if ($option != '') {
        $option_new = $option;
        if ($option == 'Name') {
            $option_new = "CONCAT(vFirstName,' ',vLastName)";
        }
        if ($option == 'vEmail') {
            $option_new = "vEmail";
        }
        if ($option == 'vGroup') {
            $option_new = "vGroup";
        }
        if ($option == 'vContactNo') {
            $option_new = "CONCAT(vCode,' ',vContactNo)";
        }
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND a.eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND a.eStatus = '" . clean($eStatus) . "'";
            }
        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
            }
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%') AND a.eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%') AND a.eStatus = '" . clean($eStatus) . "'";
            }
        } else {
            $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%')";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%')";
            }
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND a.eStatus = '" . clean($eStatus) . "'";
}
 
if ($eRole != '' ) {
    $ssql .= " AND a.iGroupId = '" . clean($eRole) . "'";
}

$per_page = $DISPLAY_RECORD_NUMBER;
if ($eStatus != '') {
    $estatusquery = "";
} else {
    $estatusquery = " AND a.eStatus != 'Deleted'";
}
$sql = "SELECT COUNT(iAdminId) AS Total FROM administrators a LEFT JOIN admin_groups ag ON a.iGroupId = ag.iGroupId  WHERE ag.eStatus='Active' $estatusquery $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
 
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
if (!empty($eStatus)) {
    $eQuery = "";
} else {
    $eQuery = " AND a.eStatus != 'Deleted'";
}

 $sql = "SELECT a.vCode, a.vContactNo, a.iAdminId,a.iGroupId,a.vFirstName,a.vLastName,a.vEmail,vGroup,CONCAT('(+',vCode,') ',vContactNo) as mobile,a.eStatus,tRegistrationDate FROM administrators a LEFT JOIN admin_groups ag ON a.iGroupId = ag.iGroupId LEFT JOIN hotel h ON a.iAdminId = h.iAdminId  WHERE ag.eStatus='Active' $eQuery $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
//echo"<pre>"; 	print_r($data_drv);die;
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
    <title><?= $SITE_NAME ?> | <?php if ($admin == 'hotels') { ?>
            Hotels
        <?php } else { ?>
            Administrator
        <?php } ?></title>
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
                        <?php if ($admin == 'hotels') { ?>
                            <h2>Hotels</h2>
                        <?php } else { ?>
                            <h2>Administrator</h2>
                        <?php } ?>
                        <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <input type="hidden" name="admin" id="admin" value="<?php echo $admin; ?>">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="1%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="8%" class=" padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="Name" <?php
                                if ($option == "Name") {
                                    echo "selected";
                                }
                                ?> >Name
                                </option>
                                <option value="vEmail" <?php
                                if ($option == 'vEmail') {
                                    echo "selected";
                                }
                                ?> >E-mail
                                </option>
                                <?php if ($admin == "hotels") { ?>
                                    <option value="vContactNo" <?php
                                    if ($option == 'vContactNo') {
                                        echo "selected";
                                    }
                                    ?> >Phone
                                    </option>
                                <?php } ?>
                             </select>
                        </td>
                        <td width="10%">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td width="13%">
                            <select name="eStatus" id="StatusValue" class="form-control">
                                <option value="">Select Status</option>
                                <option value="Active" <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?>>Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?>>Inactive
                                </option>
                                <option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?>>Deleted
                                </option>
                            </select>
                        </td>
                        <?php if ($admin != 'hotels') { ?>
                            <td width="15%">
                                <select name="eRole" id="RoleValue" class="form-control">
                                    <option value="">Select Role</option>
                                    <?php foreach ($role_sql_data as $role_value) { ?>
                                        <option value="<?php echo $role_value['iGroupId']; ?>" <?php if ($eRole == $role_value['iGroupId']) echo "selected"; ?>><?php echo $role_value['vGroup']; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        <?php } ?>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>

                            <!--<input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = 'admin.php'"/>-->

                            <?php if ($admin == "hotels") { ?>
                                <input type="button" value="Reset" class="btnalt button11"
                                       onClick="window.location.href = 'admin.php?admin=hotels'"/>
                            <?php } else { ?>
                                <input type="button" value="Reset" class="btnalt button11"
                                       onClick="window.location.href = 'admin.php'"/>
                            <?php } ?>

                        </td>
                        <?php if ($userObj->hasPermission($create)) { ?>
                            <td width="22%">
                                <?php if ($admin == "hotels") { ?>
                                    <a class="add-btn" href="admin_action.php?admin=hotels" style="text-align: center;">
                                        Add
                                    </a>
                                <?php } else { ?>
                                    <a class="add-btn" href="admin_action.php" style="text-align: center;">Add</a>
                                <?php } ?>
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
                            <div class="changeStatus col-lg-6 option-box-left">

                                        <span class="col-lg-3 new-select001">

                                            <?php if ($userObj->hasPermission([
                                                $updateStatus,
                                                $delete
                                            ])) { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onChange="ChangeStatusAll(this.value);">

                                                    <option value="">Select Action</option>

                                                    <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                        <option value='Active' <?php
                                                        if ($option == 'Active') {
                                                            echo "selected";
                                                        }
                                                        ?> >Activate</option>
                                                        <option value="Inactive" <?php
                                                        if ($option == 'Inactive') {
                                                            echo "selected";
                                                        }
                                                        ?> >Deactivate</option>
                                                    <?php } ?>

                                                    <?php if ($userObj->hasPermission($delete) && $eStatus != "Deleted") { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                    <?php } ?>

                                                </select>
                                            <?php } ?>

                                        </span>
                            </div>
                            <?php if (!empty($data_drv)) { ?>
                                <div class="panel-heading">
                                    <form name="_export_form" id="_export_form" method="post" >
                                        <?php if (!empty($admin) && $admin == "hotels") { ?>
                                            <button type="button" onClick="showExportTypes('hotels')" >Export</button>
                                        <?php } else{ ?>
                                            <button type="button" onClick="showExportTypes('admin')" >Export</button>

                                        <?php } ?>  
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>

                                        <?php if ($userObj->hasPermission([
                                            $updateStatus,
                                            $delete
                                        ])) { ?>
                                        <th align="center" width="3%" style="text-align:center;">
                                            <input type="checkbox" id="setAllCheck">
                                        </th>
                                        <?php } ?>
                                        <th width="20%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Name <?php
                                                if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="20%">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Email <?php
                                                if ($sortby == 2) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="20%">
									<?php	if ($admin == "hotels") { ?> 
										Mobile No. 
									<?php } else{ ?>
									<a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Roles <?php
                                                if ($sortby == 3) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a><?php } ?>
                                        </th>
										
									<?php	if ($admin == "hotels") { ?> 
										<th width="20%">
										<a href="javascript:void(0);" onClick="Redirect(6,<?php
                                            if ($sortby == '6') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Registration Date <?php
                                                if ($sortby == 6) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
											<i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>	
										</th>
									<?php }   ?>
                                        <!--  <th width="15%"><a href="javascript:void(0);" >Locations </th> -->
                                        <th width="8%" align="center" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 4) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if ($userObj->hasPermission([$edit,$updateStatus,$delete])) { ?>
                                            <th width="8%" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        foreach ($data_drv as $key => $row) {
                                            $default = '';
                                            if ($_SESSION['sess_iAdminUserId'] == $row['iAdminId']) {
                                                $default = 'disabled';
                                            }
                                            if ($eStatus == '' && $row['eStatus'] == 'Deleted') {
                                                continue;
                                            }
                                            if ($eStatus != '' && $eStatus != $row['eStatus']) {
                                                continue;
                                            }
                                            ?>
                                            <tr class="gradeA">

                                                <?php if ($userObj->hasPermission([
                                                    $updateStatus,
                                                    $delete
                                                ])) { ?>
                                                <?php if (($_SESSION['sess_iAdminUserId'] == $row['iAdminId']) ||  (isset($row['eDefault']) && $row['eDefault'] == 'Yes')) { ?>
                                                    <td align="center" style="text-align:center;"></td>
                                                <?php } else { ?>
                                                    <td align="center" style="text-align:center;">
                                                        <input type="checkbox" id="checkbox"
                                                               name="checkbox[]" <?php echo $default; ?>
                                                               value="<?php echo $row['iAdminId']; ?>"/>&nbsp;
                                                    </td>
                                                <?php } } ?>
												  <td><a href="javascript:void(0);"
											   onClick="show_admin_details('<?= $row['iAdminId']; ?>')"
											   style="text-decoration: underline;"><?= clearName($row['vFirstName'] . ' ' . $row['vLastName']); ?></a>
                                                 </td>
                                                <td><?= clearEmail($row['vEmail']); ?></td>
                                              <td><?php  if ($admin == "hotels") {  ?>
                                                      <?="(+".clearPhone($row['vCode']).") ".clearPhone($row['vContactNo']);?>

                                                  <?php } else { ?> <?= clearName($row['vGroup']); ?>
												<?php } ?> </td>
													<?php	if ($admin == "hotels") { 
                                                            $date_format_data_array = array(
                                                                'tdate' => $row['tRegistrationDate'],
                                                                'langCode' => $default_lang,
                                                                'DateFormatForWeb' => 1
                                                            );
                                                            $get_registration_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                            

                                                        ?>
														<td><?=  $get_registration_date_format['tDisplayDateTime'];//DateTime($row['tRegistrationDate'], 7) ?></td>
													<?php } ?>
                                                <td align="center" style="text-align:center;">
                                                    <?php
                                                    if ($row['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($row['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($row['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
													 
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip"
                                                         title="<?php echo $row['eStatus']; ?>">
                                                </td>
                                                <?php if ($userObj->hasPermission([
                                                    $edit,
                                                    $updateStatus,
                                                    $delete
                                                ])) { ?>
                                                <td align="center" style="text-align:center;" class="action-btn001">
                                                    <?php if  ($_SESSION['sess_iAdminUserId'] == $row['iAdminId'] || (isset($row['eDefault']) && $row['eDefault'] == 'Yes')) { ?>

                                                            <?php if ($userObj->hasPermission($edit)) { ?>


                                                        <a href="admin_action.php?id=<?= $row['iAdminId']; ?><?php echo $urlAppend; ?>"
                                                           data-toggle="tooltip" title="Edit">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                        <?php }else {

                                                                echo "---";
                                                        } ?>
                                                    <?php } else { ?>
                                                        <?php if ($userObj->hasPermission([
                                                            $edit,
                                                            $updateStatus,
                                                            $delete
                                                        ])) { ?>
                                                            <div class="share-button share-button4 openHoverAction-class"
                                                                 style="display: block;">
                                                                <label class="entypo-export">
                                                                    <span><img src="images/settings-icon.png"
                                                                               alt=""></span>
                                                                </label>
                                                                <div class="social show-moreOptions openPops_<?= $row['iAdminId']; ?>">
                                                                    <ul>
                                                                        <?php if ($userObj->hasPermission($edit)) { ?>
                                                                            <li class="entypo-twitter"
                                                                                data-network="twitter">
                                                                                <a href="admin_action.php?id=<?= $row['iAdminId']; ?><?php echo $urlAppend; ?>"
                                                                                   data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png"
                                                                                         alt="Edit">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>

                                                                        <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatus('<?php echo $row['iAdminId']; ?>', 'Inactive')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?php echo $row['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatus('<?php echo $row['iAdminId']; ?>', 'Active')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Deactivate">
                                                                                    <img src="img/inactive-icon.png"
                                                                                         alt="<?php echo $row['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>

                                                                        <?php if ($userObj->hasPermission($delete) && $_SESSION['sess_iAdminUserId'] != $row['iAdminId'] && $row['eStatus'] != "Deleted") { ?>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onClick="changeStatusDelete('<?php echo $row['iAdminId']; ?>')"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="7"><?php echo $langage_lbl_admin['LBL_NO_RECORDS_FOUND1']; ?></td>
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
                    <?php if (!(ONLYDELIVERALL == 'Yes' || $THEME_OBJ->isRideCXThemeActive() == 'Yes' || $THEME_OBJ->isRideDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXv2ThemeActive() == 'Yes' || $THEME_OBJ->isRideCXv2ThemeActive() == 'Yes')) { ?>
                        <li>
                            <?= $admin == "hotels" ? "Hotels" : "Administrator" ?> module will list
                            all <?= $admin == "hotels" ? "hotel" : "admin" ?> users on
                            this page.
                        </li>
                    <?php } ?>
                    <li>
                        Administrator can Activate , Deactivate , Delete any
                        other <?= $admin == "hotels" ? "hotel" : "" ?> admin users.
                    </li>
                    <?php if ($admin != "hotels") { ?>
                        <li>Super Admin cannot be Activated , Deactivated or Deleted.</li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>

<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?php if ($admin == "hotels") { echo 'Hotel';} else { echo 'Admin'; } ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons" style="display:none">
                    <div align="center">
                        <img src="default.gif">
                        <br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="admin_detail"></div>
            </div>
        </div>
    </div>
</div>

<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/admin.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iAdminId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="admin" id="admin" value="<?php echo $admin; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<?php
include_once('footer.php');
?>
<script>
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



    function show_admin_details(adminid) {
        $("#admin_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');
        if (adminid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_admin_details.php',
                'AJAX_DATA': "iAdminId=" + adminid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#admin_detail").html(data);
                    $("#imageIcons").hide();
                } else {
                    console.log(response.result);
                }
            });
        }
    }
	
    $(document).on("click", function (e) {

        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {

            $(".show-moreOptions").removeClass("active");

        }

    });
</script>
</body>
<!-- END BODY-->
</html>