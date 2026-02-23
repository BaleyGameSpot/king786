<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-users-trackanyservice')) {
    $userObj->redirect();
}
$script = 'TrackAnyServiceUser';
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
$sql = "SELECT COUNT(iTrackServiceUserId) AS Total FROM track_service_users as tsu WHERE 1=1 $estatusquery $ssql $ssql1 $rdr_ssql";
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
$sql = "SELECT tsu.iTrackServiceUserId, CONCAT(tsu.vName,' ',tsu.vLastName) AS name, tsu.vEmail, tsu.vPhone AS mobile, tsu.vPhoneCode,tsu.tRegistrationDate,tsu.eStatus,tsu.iUserId, tsu.tUserIds, (SELECT COUNT(ru.iUserId) FROM register_user as ru WHERE iUserId IN (tsu.tUserIds)) as LinkedMembers,(SELECT ru.vTimeZone FROM register_user as ru WHERE iUserId = tsu.tUserIds) as vTimeZone FROM track_service_users as tsu WHERE 1=1 $esql $ssql $ssql1 $rdr_ssql $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);
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
                        <td width="10%" class=" padding-right10"><select name="option" id="option" class="form-control">
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
                        <td width="13%" class="estatus_options" id="eStatus_options">
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

                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'track_any_service_user.php'"/>
                            <?php if(!empty($data_drv)) { ?>
                            <button type="button" onclick="showExportTypes('track_any_service_user')" class="btnalt button11">Export</button>
                            <?php } ?>
                        </td>
                        <?php if ($userObj->hasPermission('create-users-trackanyservice')) { ?>
                            <td width="30%">
                                <a class="add-btn" href="track_any_service_user_action.php" style="text-align: center;">
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
                                    <?php if ($userObj->hasPermission(['update-status-users-trackanyservice', 'delete-users-trackanyservice'])) { ?>
                                        <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                            <option value="">Select Action</option>
                                            <?php if ($userObj->hasPermission('update-status-users-trackanyservice')) { ?>
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

                                            <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-users-trackanyservice')) { ?>
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
                                        
                                        <th>Linked Members</th>
                                        <th style="text-align:center;">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            }
                                            else {
                                                ?>0<?php } ?>)">Registration Date <?php if ($sortby == 3) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }
                                                else {
                                                    ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th >Mobile</th>
                                        
                                        <th align="center" style="text-align:center;">
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
                                        <th align="center" style="text-align:center;">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        $systemTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            $date_format_data_array = array(
                                                'tdate' => (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tRegistrationDate'],$data_drv[$i]['vTimeZone'], $systemTimeZone) : $data_drv[$i]['tRegistrationDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                
                                            $get_tRegistrationDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                            ?>
                                            <tr class="gradeA">
                                                <td align="center" style="text-align:center;">
                                                    <input type="checkbox" id="checkbox" name="checkbox[]" value="<?php echo isset($data_drv[$i]['iTrackServiceUserId']) ? $data_drv[$i]['iTrackServiceUserId'] : ''; ?>"/>&nbsp;
                                                </td>
                                                <td>
                                                    <?php if ($userObj->hasPermission('view-users')) { ?><a href="track_any_service_user_action.php?id=<?= $data_drv[$i]['iTrackServiceUserId']; ?>" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['name']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?> 
                                                </td>
                                                <?php if ($data_drv[$i]['vEmail'] != '') { ?>
                                                    <td><?php echo clearEmail($data_drv[$i]['vEmail']); ?></td>
                                                <?php } else { ?>
                                                    <td>--</td>
                                                <?php } ?>

                                                <td>
                                                    <?php if($data_drv[$i]['LinkedMembers'] > 0) { ?>
                                                    <a href="javascript:void(0);" onclick="fetchLinkedMembers('<?= $data_drv[$i]['tUserIds']; ?>')" style="text-decoration: underline;"><?= $data_drv[$i]['LinkedMembers']; ?></a>
                                                    <?php } else { ?>
                                                        <?= $data_drv[$i]['LinkedMembers']; ?>
                                                    <?php } ?>
                                                </td>
                                                <td  style="text-align:center;" data-order="<?= $data_drv[$i]['iTrackServiceUserId']; ?>"><?php echo $get_tRegistrationDate_format['tDisplayDate'];//DateTime($data_drv[$i]['tRegistrationDate']) ?></td>
                                                <td class="center">
                                                    <?php if (!empty($data_drv[$i]['mobile'])) { ?>
                                                        (+<?= $data_drv[$i]['vPhoneCode'] ?>) <?= clearPhone($data_drv[$i]['mobile']); ?><?php } ?>
                                                </td>
                                                
                                                <td width="10%" align="center">
                                                    <?php
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
                                                                    <a href="track_any_service_user_action.php?id=<?= $data_drv[$i]['iTrackServiceUserId']; ?>" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                </li>
                                                                <?php if ($userObj->hasPermission('update-status-users-trackanyservice')) { ?>
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

                                                                <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-users-trackanyservice')) { ?>
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
                                            <?php
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
<form name="pageForm" id="pageForm" action="action/track_any_service_user.php" method="post">
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
<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/rider-icon.png" alt="">
                    </i>
                    <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details
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
<?php include_once('searchfunctions.php'); ?>
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

    function show_rider_details(userid) {
        $('#custom-alert').removeClass('active');
        $("#rider_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');
        if (userid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': "iUserId=" + userid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#rider_detail").html(data);
                    $("#imageIcons").hide();
                }
                else {
                    console.log(response.result);
                    $("#detail_modal").modal('hide');
                }
            });
        }
    }

    function fetchLinkedMembers(user_ids) {
        $('#loaderIcon').show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_track_service_company_driver.php',
            'AJAX_DATA': "module=fetch_linked_members&tUserIds=" + user_ids,
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').hide();
            if(response.action == "1") {
                var dataHtml2 = response.result;
                if(dataHtml2.Action == 1) {
                    if (dataHtml2.message != "") {
                        show_alert("Linked Member Details", dataHtml2.message,"","","<?= $langage_lbl_admin['LBL_BTN_OK_TXT'] ?>",undefined,true,true,true);
                    } 
                } else {
                    show_alert("", dataHtml2.message,"","","<?= $langage_lbl_admin['LBL_BTN_OK_TXT'] ?>");
                }
            }
            else {
                // console.log(response.result);
            }
        });
    }
</script>
</body>
</html>

