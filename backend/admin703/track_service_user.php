<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-users-trackservice')) {
    $userObj->redirect();
}
$script = 'TrackServiceUser';
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$searchRider = (isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '');
$ord = ' ORDER BY tsu.iTrackServiceUserId DESC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY tsu.vName ASC";
    else

        $ord = " ORDER BY tsu.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY tsu.vEmail ASC";
    else

        $ord = " ORDER BY tsu.vEmail DESC";
}
if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY tsu.eStatus ASC";
    else

        $ord = " ORDER BY tsu.eStatus DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY tsu.dAddedDate ASC";
    else

        $ord = " ORDER BY tsu.dAddedDate DESC";
}
$rdr_ssql = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$ssql = '';
if ($keyword != '') {
    $keyword_new = $keyword;
    $chracters = array("(", "+", ")");
    $removespacekeyword = preg_replace('/\s+/', '', $keyword);
    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
    if (is_numeric($keyword_new)) {
        $keyword_new = $keyword_new;
    }
    else {
        $keyword_new = $keyword;
    }
    if ($option != '') {
        $option_new = $option;
        if ($option == 'RiderName') {
            $option_new = "CONCAT(tsu.vName,' ',tsu.vLastName)";
        }
        if ($option == 'MobileNumber') {
            $option_new = "CONCAT(tsu.vPhoneCode,'',tsu.vPhone)";
        }
        if ($option == 'vEmail') {
            $option_new = "tsu.vEmail";
        }
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND eStatus = '" . clean($eStatus) . "'";
            }
        }
        else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
            }
        }
    }
    else {
        if ($eStatus != '') {
            $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%" . clean($keyword_new) . "%')) AND eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) = '" . clean($keyword_new) . "')) AND eStatus = '" . clean($eStatus) . "'";
            }
        }
        else {
            $ssql .= " AND (concat(tsu.vName,' ',tsu.vLastName) LIKE '%" . clean($keyword_new) . "%' OR tsu.vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(tsu.vPhoneCode,'',tsu.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND (concat(tsu.vName,' ',tsu.vLastName) LIKE '%" . clean($keyword_new) . "%' OR tsu.vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(tsu.vPhoneCode,'',tsu.vPhone) = '" . clean($keyword_new) . "'))";
            }
        }
    }
}
else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND tsu.eStatus = '" . clean($eStatus) . "'";
}
$ssql1 = "AND (tsu.vEmail != '' OR tsu.vPhone != '')";
$per_page = $DISPLAY_RECORD_NUMBER;
if ($eStatus != '') {
    $estatusquery = "";
}
else {
    $estatusquery = " AND tsu.eStatus != 'Deleted'";
}


if($searchRider != ''){
    $ssql .= " AND tsu.iUserId = '" . clean($searchRider) . "'";
}
$sql = "SELECT COUNT(iTrackServiceUserId) AS Total FROM track_service_users tsu 
        JOIN track_service_company as tsc ON (tsc.iTrackServiceCompanyId = tsu.iTrackServiceCompanyId)
        WHERE 1=1 $estatusquery $ssql $ssql1 $rdr_ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page);
$show_page = 1;
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
if (!empty($eStatus)) {
    $esql = "";
}
else {
    $esql = " AND tsu.eStatus != 'Deleted'";
}
$sql = "SELECT CONCAT(ru.vName , ' ',ru.vLastName) as vRiderName , CONCAT(rd.vName , ' ',rd.vLastName) as vProviderName ,tsu.iUserId,tsc.vCompany,tsu.iTrackServiceUserId,tsu.iDriverId,tsu.iTrackServiceCompanyId,CONCAT(tsu.vName,' ',tsu.vLastName) AS name, tsu.vEmail, tsu.vPhone AS mobile,tsu.vPhoneCode,tsu.dAddedDate,tsu.eStatus, tsu.vInviteCode FROM track_service_users tsu
        JOIN track_service_company as tsc ON (tsc.iTrackServiceCompanyId = tsu.iTrackServiceCompanyId)
        JOIN register_driver as rd ON (rd.iDriverId = tsu.iDriverId)
        LEFT JOIN register_user as ru ON (ru.iUserId = tsu.iUserId)
        WHERE 1=1 $esql $ssql $ssql1 $rdr_ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);

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
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
</head>
<body class="padTop53 ">
<div id="wrap">
    <?php include_once('header.php'); ?>

    <?php include_once('left_menu.php'); ?>
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?></h2>
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
                            <label for="textfield"><strong>Search:</strong></label>
                        </td>
                        <td width="8%" class=" padding-right10"><select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="RiderName" <?php
                                if ($option == "RiderName") {
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
                                <option value="MobileNumber" <?php
                                if ($option == 'MobileNumber') {
                                    echo "selected";
                                }
                                ?> >Mobile
                                </option>
                            </select>
                        </td>
                        <td width="15%" class="searchform">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" class="form-control"/>
                        </td>
                        <td width="11%" class="estatus_options" id="eStatus_options">
                            <select name="eStatus" id="estatus_value" class="form-control">
                                <option value="">Select Status</option>
                                <option value='Active' <?php
                                if ($eStatus == 'Active') {
                                    echo "selected";
                                }
                                ?> >Active
                                </option>
                                <option value="Inactive" <?php
                                if ($eStatus == 'Inactive') {
                                    echo "selected";
                                }
                                ?> >Inactive
                                </option>
                                <option value="Deleted" <?php
                                if ($eStatus == 'Deleted') {
                                    echo "selected";
                                }
                                ?> >Delete
                                </option>
                            </select>
                        </td>
                        <td width="13%" class="estatus_options" id="eStatus_options">
                            <select class="form-control filter-by-text" name='searchRider'
                                    data-text="Select Track User"
                                    id="searchRider">

                                <option value="">Select </option>

                            </select>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'rider.php'"/>
                        </td>
                        <?php if ($userObj->hasPermission('create-users-trackservice')) { ?>
                            <td width="30%">
                                <a class="add-btn" href="track_service_user_action.php" style="text-align: center;">
                                    Add <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></a>
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
                                    <?php if ($userObj->hasPermission(['update-status-users-trackservice', 'delete-users-trackservice'])) { ?>
                                        <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                            <option value="">Select Action</option>
                                            <?php if ($userObj->hasPermission('update-status-users-trackservice')) { ?>
                                                <option value='Active' <?php
                                                if ($option == 'Active') {
                                                    echo "selected";
                                                }
                                                ?> >Activate
                                                </option>
                                                <option value="Inactive" <?php
                                                if ($option == 'Inactive') {
                                                    echo "selected";
                                                }
                                                ?> >Deactivate
                                                </option>
                                            <?php } ?>

                                            <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-users-trackservice')) { ?>
                                                <option value="Deleted" <?php
                                                if ($option == 'Delete') {
                                                    echo "selected";
                                                }
                                                ?> >Delete
                                                </option>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </span>
                            </div>
                            <?php if (!empty($data_drv)) { ?>
                                <div class="panel-heading">
                                    <form name="_export_form" id="_export_form" method="post">
                                        <button type="button" onClick="showExportTypes('rider')">Export</button>
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th align="center" width="50px" style="text-align:center;">
                                            <input type="checkbox" id="setAllCheck">
                                        </th>
                                        <th>
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            }
                                            else {
                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?>
                                                Name <?php if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }
                                                else {
                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th>
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            }
                                            else {
                                                ?>0<?php } ?>)">Email <?php if ($sortby == 2) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }
                                                else {
                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th><?= $langage_lbl_admin['LBL_TRACK_SERVICE_ASSIGNED_DRIVER_TXT']; ?></th>
                                        <th>Company</th>
                                        <th>Track User</th>
                                        <th style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            }
                                            else {
                                                ?>0<?php } ?>)">Created Date <?php if ($sortby == 3) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }
                                                else {
                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th >Mobile</th>
                                        <th width="12%" style="text-align:center;" >Send Invite Code</th>
                                        <th width="4%" style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            }
                                            else {
                                                ?>0<?php } ?>)">Status <?php if ($sortby == 4) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }
                                                else {
                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="3%" style="text-align:center;">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            ?>
                                            <tr class="gradeA">
                                                <td align="center" style="text-align:center;">
                                                    <input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iTrackServiceUserId']; ?>"/>&nbsp;
                                                </td>
                                                <td> <?php if ($userObj->hasPermission('view-track-service-users')) { ?><a href="javascript:void(0);" onClick="show_track_rider_details('<?= $data_drv[$i]['iTrackServiceUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['name']); ?><?php if ($userObj->hasPermission('view-track-service-users')) { ?></a><?php } ?></td>
                                                <? if ($data_drv[$i]['vEmail'] != '') { ?>
                                                    <td><? echo clearEmail($data_drv[$i]['vEmail']); ?></td>
                                                <? } else { ?>
                                                    <td>--</td>
                                                <? } ?>
												
                                                <td><?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $data_drv[$i]['iDriverId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['vProviderName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?></td>
                                                <td> <?php if ($userObj->hasPermission('view-track-service-company')) { ?><a href="javascript:void(0);" onClick="show_track_company_details('<?= $data_drv[$i]['iTrackServiceCompanyId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearCmpName($data_drv[$i]['vCompany']); ?><?php if ($userObj->hasPermission('view-track-service-company')) { ?></a><?php } ?></td>
                                                <td>   <a href="javascript:void(0);" onClick="show_rider_details('<?= $data_drv[$i]['iUserId']; ?>')" style="text-decoration: underline;"><?= clearName($data_drv[$i]['vRiderName']); ?></a></td>
                                                <td  style="text-align:center;" data-order="<?= $data_drv[$i]['iTrackServiceUserId']; ?>"><? echo DateTime($data_drv[$i]['dAddedDate']) ?></td>
                                                <td class="center">
                                                    <?php if (!empty($data_drv[$i]['mobile'])) { ?>
                                                        (+<?= $data_drv[$i]['vPhoneCode'] ?>) <?= clearPhone($data_drv[$i]['mobile']); ?><?php } ?>
                                                </td>
                                                <td style="text-align:center;" >
                                                    <?php if ($data_drv[$i]['iUserId'] == 0) { ?>
                                                        <a onClick="sendInviteCode('<?php echo $data_drv[$i]['iTrackServiceUserId']; ?>')" class="add-btn-sub" href="javascript:void(0);" style="text-align: center; margin-bottom: 10px;">Resend Invite Code
                                                        </a>
                                                        <div style="text-align: center;"><strong>Invite Code:</strong> <?= $data_drv[$i]['vInviteCode'] ?></div>
                                                    <?php }
                                                    else { echo '--'; } ?>
                                                </td>
                                                <td   align="center">
                                                    <?
                                                    if ($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    }
                                                    else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    }
                                                    else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus'] ?>" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                </td>
                                                <td align="center" style="text-align:center;" class="action-btn001">
                                                    <div class="share-button openHoverAction-class" style="display: block;">
                                                        <label class="entypo-export"><span>
                                                                <img src="images/settings-icon.png" alt="">
                                                            </span></label>
                                                        <div class="social show-moreOptions for-five openPops_<?= $data_drv[$i]['iTrackServiceUserId']; ?>">
                                                            <ul>
                                                                <li class="entypo-twitter" data-network="twitter">
                                                                    <a href="track_service_user_action.php?id=<?= $data_drv[$i]['iTrackServiceUserId']; ?>" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                </li>
                                                                <?php if ($userObj->hasPermission('update-status-users-trackservice')) { ?>
                                                                    <li class="entypo-facebook" data-network="facebook">
                                                                        <a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iTrackServiceUserId']; ?>', 'Inactive')" data-toggle="tooltip" title="Activate">
                                                                            <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                        </a>
                                                                    </li>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);" onClick="changeStatus('<?php echo $data_drv[$i]['iTrackServiceUserId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                            <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>

                                                                <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-users-trackservice')) { ?>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iTrackServiceUserId']; ?>')" data-toggle="tooltip" title="Delete">
                                                                            <img src="img/delete-icon.png" alt="Delete">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?
                                        }
                                    }
                                    else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="8"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>
                        <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?> module will list
                        all <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?> on this page.
                    </li>
                    <li>
                        Administrator can Activate / Deactivate / Delete
                        any <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/track_service_user.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iTrackServiceUserId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>


<div class="modal fade " id="track_rider_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>Track Service <?= $langage_lbl_admin['LBL_RIDER']; ?> Details<button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="track_rider_imageIcons">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="track_rider_detail"></div>
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

<div  class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog" >
		<div class="modal-content">
			<div class="modal-header">
				<h4>
					<i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>Track Service <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
					<button type="button" class="close" data-dismiss="modal">x</button>
				</h4>
			</div>
			<div class="modal-body" style="max-height: 450px;overflow: auto;">
				<div id="imageIcons1" style="display:none">
					<div align="center">                                                                       
						<img src="default.gif"><br/>                                                            
						<span>Retrieving details,please Wait...</span>                       
					</div>    
				</div>
				<div id="driver_detail"></div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/rider-icon.png" alt="">
                    </i>
                    Track <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons">
                    <div align="center">
                        <img src="default.gif">
                        <br/> <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail"></div>
            </div>
        </div>
    </div>
</div>
<?php include_once('footer.php'); ?>
<? include_once('searchfunctions.php'); ?>
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/js/modal_alert.js"></script>
<script>
    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        }
        else {
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
    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
        }
    }); 
	
    function checkzero(userlimit) {
        if (userlimit != "") {
            if (userlimit == 0) {
                $('#iLimitmsg').html('<span class="red">You Can Not Enter Zero Number</span>');
            }
            else if (userlimit <= 0) {
                $('#iLimitmsg').html('<span class="red">You Can Not Enter Negative Number</span>');
            }
            else {
                $('#iLimitmsg').html('');
            }
        }
        else {
            $('#iLimitmsg').html('');
        }
    }

    function sendInviteCode(iTrackServiceUserId) {
        $('#loaderIcon').show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_track_service_company_driver.php',
            'AJAX_DATA': {
                module: 'track_service_send_invite_code',
                iTrackServiceUserId: iTrackServiceUserId,
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').hide();
            show_alert("", "Invite Code Sent.", "", "", "<?= $langage_lbl['LBL_BTN_OK_TXT'] ?>");
        });
    }
 
	
</script>
</body>
</html>

