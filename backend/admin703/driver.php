<?php
include_once('../common.php');
global $userObj;
if (!$userObj->hasPermission('view-providers')) {
    $userObj->redirect();
}
//$defaultcurename = get_default_currency();
$script = 'Driver';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY rd.iDriverId DESC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY rd.vName ASC"; else

        $ord = " ORDER BY rd.vName DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY c.vCompany ASC"; else

        $ord = " ORDER BY c.vCompany DESC";
}
if ($sortby == 3) {
    if ($order == 0) $ord = " ORDER BY rd.vEmail ASC"; else

        $ord = " ORDER BY rd.vEmail DESC";
}
if ($sortby == 4) {
    if ($order == 0) $ord = " ORDER BY rd.tRegistrationDate ASC"; else

        $ord = " ORDER BY rd.tRegistrationDate DESC";
}
if ($sortby == 5) {
    if ($order == 0) $ord = " ORDER BY rd.eStatus ASC"; else

        $ord = " ORDER BY rd.eStatus DESC";
}
if ($sortby == 6) {
    if ($order == 0) $ord = " ORDER BY `count` ASC"; else

        $ord = " ORDER BY `count` DESC";
}
if ($sortby == 7) {
    if ($order == 0) $ord = " ORDER BY `eIsFeatured` ASC"; else

        $ord = " ORDER BY `eIsFeatured` DESC";
}
if ($sortby == 8) {
    if ($order == 0) $ord = " ORDER BY `eIsPremium` ASC"; else

        $ord = " ORDER BY `eIsPremium` DESC";
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
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
if (isset($_POST['action'])) {
    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']."?" . $parameters);
        exit;
    }

    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : "";

    if($_POST['action'] == "Featured") {
        $eIsFeatured = isset($_REQUEST['eIsFeatured']) ? $_REQUEST['eIsFeatured'] : "No";
        $Fsql = "UPDATE `register_driver` SET `eIsFeatured`='" . $eIsFeatured . "' WHERE iDriverId ='" . $iDriverId . "'";
        $obj->sql_query($Fsql);

        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_Record_Updated_successfully"];
        header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']);
        exit;
        
    } elseif ($_POST['action'] == "PremiumDriver") {
        $eIsPremium = isset($_REQUEST['eIsPremium']) ? $_REQUEST['eIsPremium'] : "No";
        $Fsql = "UPDATE `register_driver` SET `eIsPremium`='" . $eIsPremium . "' WHERE iDriverId ='" . $iDriverId . "'";
        $obj->sql_query($Fsql);

        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin["LBL_Record_Updated_successfully"];
        header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']);
        exit;

    } elseif ($_POST['action'] == "addmoney") {
        if (isset($_REQUEST['iBalance']) && $_REQUEST['iBalance'] > 0) {
            $eUserType = $_REQUEST['eUserType'];
            $iUserId = $_REQUEST['iDriverId'];
            $iBalance = $_REQUEST['iBalance'];
            $eFor = $_REQUEST['eFor'];
            $eType = $_REQUEST['eType'];
            $iTripId = 0;
            $tDescription = '#LBL_AMOUNT_CREDIT_BY_ADMIN#';
            $ePaymentStatus = 'Unsettelled';
            $dDate = Date('Y-m-d H:i:s');
            $WALLET_OBJ->PerformWalletTransaction($iUserId, $eUserType, $iBalance, $eType, $iTripId, $eFor, $tDescription, $ePaymentStatus, $dDate);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = "Amount has been successfully added to the " . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . "'s wallet.";
            header("Location:" . $tconfig["tsite_url_main_admin"] . $LOCATION_FILE_ARRAY['DRIVER.PHP']);
            exit;
        }
    }    
}

$ssql = '';
$cmp_name = "";
if ($iCompanyId != "") {
    $ssql .= " AND rd.iCompanyId='" . $iCompanyId . "'";
    $sql = "select vCompany from company where iCompanyId = '" . $iCompanyId . "'";
    $data_cmp1 = $obj->MySQLSelect($sql);
    $cmp_name = $data_cmp1[0]['vCompany'];
    $keyword = $cmp_name;
}
//Added By HJ On 18-05-2020 For Store Driver Listing Start
if (isset($_REQUEST['store']) && $_REQUEST['store'] > 0) {
    $keyword = "";
}
//Added By HJ On 18-05-2020 For Store Driver Listing End
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
        if ($option == 'MobileNumber') {
            $option_new = "CONCAT(rd.vCode,'',rd.vPhone)";
        }
        if ($option == 'DriverName') {
            $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
        }
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND rd.eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND rd.eStatus = '" . clean($eStatus) . "'";
            }
        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
            }
        }
    } else {
        if (ONLYDELIVERALL == 'Yes') {
            if ($eStatus != '') {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND rd.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) = '" . clean($keyword_new) . "')) AND rd.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) = '" . clean($keyword_new) . "'))";
                }
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR c.vCompany LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND rd.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR c.vCompany LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) = '" . clean($keyword_new) . "')) AND rd.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR c.vCompany LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR c.vCompany LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) = '" . clean($keyword_new) . "'))";
                }
            }
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND rd.eStatus = '" . clean($eStatus) . "'";
}
// End Search Parameters
$ssql1 = " AND (rd.vEmail != '' OR rd.vPhone != '') AND rd.iTrackServiceCompanyId = 0 ";
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
if ($eStatus != '') {
    $eStatussql = "";
} else {
    $eStatussql = " AND rd.eStatus != 'Deleted'";
}


if (ONLYDELIVERALL == 'Yes') {
    $sql = "SELECT COUNT(iDriverId) AS Total FROM register_driver rd WHERE 1 = 1  $eStatussql $ssql $ssql1 $dri_ssql";
} else {
    $sql = "SELECT COUNT(iDriverId) AS Total FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE 1 = 1  $eStatussql $ssql $ssql1 $dri_ssql";
}
///echo $sql;die;
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
//Pagination End
if (!empty($eStatus)) {
    $eQuery = "";
} else {
    $eQuery = " AND rd.eStatus != 'Deleted'";
}
if (ONLYDELIVERALL == 'Yes') {
    $sql = "SELECT rd.iCompanyId,rd.iDriverId,rd.vEmail,rd.tRegistrationDate,rd.vPhone,rd.vCode,rd.eStatus,rd.eIsFeatured,rd.eIsPremium,rd.vCountry,rd.vTimeZone,(SELECT count(dv.iDriverVehicleId) FROM driver_vehicle AS dv WHERE dv.iDriverId=rd.iDriverId AND dv.eStatus != 'Deleted' AND dv.iMakeId != 0 AND dv.iModelId != 0 AND dv.eType != 'UberX') AS `count`,CONCAT(rd.vName,' ',rd.vLastName) AS driverName FROM register_driver rd WHERE 1=1 $eQuery $ssql $ssql1 $dri_ssql $ord LIMIT $start, $per_page";
} else {
    $sql = "SELECT rd.iCompanyId,rd.iDriverId,rd.vEmail,rd.tRegistrationDate,rd.vPhone,rd.vCode,rd.eStatus,rd.eIsFeatured,rd.eIsPremium,rd.vCountry,rd.vTimeZone,(SELECT count(dv.iDriverVehicleId) FROM driver_vehicle AS dv WHERE dv.iDriverId=rd.iDriverId AND dv.eStatus != 'Deleted' AND dv.iMakeId != 0 AND dv.iModelId != 0 AND dv.eType != 'UberX') AS `count`,CONCAT(rd.vName,' ',rd.vLastName) AS driverName, c.vCompany,c.eStatus as cmp_status FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE 1=1 $eQuery $ssql $ssql1 $dri_ssql $ord LIMIT $start, $per_page";
}

$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$sql1 = "SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='driver' AND status = 'Active'";
$doc_count_query = $obj->MySQLSelect($sql1);
$doc_count = scount($doc_count_query);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page' && $key != 'checkbox') {
        $var_filter .= "&$key=" . stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable();
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Manage <?= $langage_lbl_admin['LBL_DRIVERS_SERVICE_PROVIDERS']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- Main Loading -->
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
                        <?php
                        $company_name = ($cmp_name != "") ? " of " . $cmp_name : "";
                        ?>
                        <h2><?= $langage_lbl_admin['LBL_DRIVERS_SERVICE_PROVIDERS'] . $company_name; ?></h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <input type="hidden" name="iDriverId" value="<?= !empty($iDriverId) ? $iDriverId : ''; ?>">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="10%" class="padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="DriverName" <?php
                                if ($option == "DriverName") {
                                    echo "selected";
                                }
                                ?> ><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name
                                </option>
                                <?php if (ONLYDELIVERALL != 'Yes') { ?>
                                    <option value="c.vCompany" <?php
                                    if ($option == "c.vCompany" || ($iCompanyId != "" && $cmp_name != "")) {
                                        echo "selected";
                                    }
                                    ?> ><?php if ($MODULES_OBJ->isStorePersonalDriverAvailable() > 0) { ?>Company/Store<?php } else { ?>Company <?php } ?>
                                        Name
                                    </option>
                                <?php } ?>
                                <option value="rd.vEmail" <?php
                                if ($option == 'rd.vEmail') {
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
                            <input type="Text" id="keyword" name="keyword" value="<?php
                            if (!empty($keyword)) {
                                echo $keyword;
                            }
                            ?>" class="form-control"/>
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
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = '<?= $LOCATION_FILE_ARRAY['DRIVER.PHP'] ?>'"/>
                        </td>
                        <td width="30%">
                            <?php if ($userObj->hasPermission('create-providers')) { ?>
                                <a class="add-btn" href="<?= $LOCATION_FILE_ARRAY['DRIVER_ACTION'] ?>" style="text-align: center;">
                                    Add <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a>
                            <?php } ?>
                        </td>
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

                                            <?php if ($userObj->hasPermission([
                                                'update-status-providers',
                                                'delete-providers'
                                            ]) && $eStatus != 'Deleted') { ?>
                                                <select name="changeStatus" id="changeStatus" class="form-control"
                                                        onChange="ChangeStatusAll(this.value);">

                                                    <option value="">Select Action</option>

                                                    <?php if ($userObj->hasPermission('update-status-providers')) { ?>
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
                                                    <?php if ($userObj->hasPermission('delete-providers')) { ?>
                                                        <option value="Deleted" <?php
                                                        if ($option == 'Delete') {
                                                            echo "selected";
                                                        }
                                                        ?> >Delete</option>
                                                    <?php }
                                                    ?>

                                                </select>
                                            <?php } ?>

                                        </span>
                            </div>
                            <?php if (!empty($data_drv) && $userObj->hasPermission('export-providers')) { ?>
                                <div class="panel-heading">
                                    <form name="_export_form" id="_export_form" method="post">
                                        <button type="button" onClick="showExportTypes('driver')">Export</button>
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <?php if ($userObj->hasPermission([
                                                'update-status-providers',
                                                'delete-providers'
                                                ]) && $eStatus != 'Deleted') { ?>
                                        <th width="3%" class="align-center">
                                            <input type="checkbox" id="setAllCheck">
                                        </th>
                                        <?php } ?>
                                        <th width="13%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>
                                                Name <?php
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
                                        <?php if (ONLYDELIVERALL == 'No') { ?>
                                            <th width="18%">
                                                <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                if ($sortby == '2') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)"><?php if ($MODULES_OBJ->isStorePersonalDriverAvailable() > 0) { ?>Company/Store<?php } else { ?>Company <?php } ?>
                                                    Name <?php
                                                    if ($sortby == 2) {
                                                        if ($order == 0) {
                                                            ?>
                                                            <i class="fa fa-sort-amount-asc"
                                                               aria-hidden="true"></i> <?php } else { ?>
                                                            <i class="fa fa-sort-amount-desc"
                                                               aria-hidden="true"></i><?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                        <?php } ?>
                                        <th width="18%">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php
                                            if ($sortby == '3') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Email <?php
                                                if ($sortby == 3) {
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
                                        <?php if ($APP_TYPE != "UberX") { ?>
                                            <th width="12%" class="align-center">
                                                <a href="javascript:void(0);" onClick="Redirect(6,<?php
                                                if ($sortby == '6') {
                                                    echo $order;
                                                } else {
                                                    ?>0<?php } ?>)"><?= $langage_lbl_admin['LBL_Vehicle'] ?> Count <?php
                                                    if ($sortby == 6) {
                                                        if ($order == 0) {
                                                            ?>
                                                            <i class="fa fa-sort-amount-asc"
                                                               aria-hidden="true"></i> <?php } else { ?>
                                                            <i class="fa fa-sort-amount-desc"
                                                               aria-hidden="true"></i><?php
                                                        }
                                                    } else {
                                                        ?>
                                                        <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                            </th>
                                        <?php } ?>
                                        <th width="12%" class="align-left">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php
                                            if ($sortby == '4') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Signup Date <?php
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
                                        <th width="12%" class="align-Left">Mobile</th>
                                        <?php if ($userObj->hasPermission('add-wallet-balance-provider')) { ?>
                                            <th class="align-Left">Wallet Balance</th>
                                        <?php } ?>

                                        <?php if ($doc_count != 0) { ?>
                                            <?php if ($userObj->hasPermission('edit-provider-document')) { ?>
                                                <th width="12%" class="align-center">View/Edit Document(s)</th>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php
                                        if (ONLYDELIVERALL == 'No' && $ufxEnable == "Yes") {
                                            if ($APP_TYPE == "UberX" || $APP_TYPE == 'Ride-Delivery-UberX') {
                                                ?>
                                                <?php if ($userObj->hasPermission('manage-provider-services')) { ?>
                                                    <th width="12%" class="align-center">Manage Services</th>
                                                <?php } ?>
                                                <?php if ($userObj->hasPermission('edit-availability')) { ?>
                                                    <th width="12%"
                                                        class="align-center"><?= "View/Edit " . $langage_lbl_admin['LBL_AVAILABILITY']; ?></th>
                                                <?php } ?>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <th width="12%" class="align-center">
                                            <a href="javascript:void(0);" onClick="Redirect(5,<?php
                                            if ($sortby == '5') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Status <?php
                                                if ($sortby == 5) {
                                                    if ($order == 0) { ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if (ONLYDELIVERALL == 'No' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxEnable == "Yes") { ?>
                                            <?php if ($userObj->hasPermission('edit-providers')) { ?>
                                                <th width="12%" class="align-center">
                                                    <a href="javascript:void(0);" onClick="Redirect(7,<?php
                                                    if ($sortby == '7') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)">IsFeatured <?php if ($sortby == 7) {
                                                            if ($order == 0) { ?>
                                                                <i class="fa fa-sort-amount-asc"
                                                                   aria-hidden="true"></i> <?php } else { ?>
                                                                <i class="fa fa-sort-amount-desc"
                                                                   aria-hidden="true"></i><?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                </th>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if($MODULES_OBJ->isEnablePremiumDriverPreference()) { ?>
                                            <?php if ($userObj->hasPermission('edit-providers')) { ?>
                                                <th width="12%" class="align-center">
                                                    <a href="javascript:void(0);" onClick="Redirect(8,<?php
                                                    if ($sortby == '8') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)">IsPremium <?php if ($sortby == 8) {
                                                            if ($order == 0) { ?>
                                                                <i class="fa fa-sort-amount-asc"
                                                                   aria-hidden="true"></i> <?php } else { ?>
                                                                <i class="fa fa-sort-amount-desc"
                                                                   aria-hidden="true"></i><?php
                                                            }
                                                        } else {
                                                            ?>
                                                            <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                </th>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if($userObj->hasPermission(['edit-providers' , 'update-status-providers' , 'delete-providers']) ){ ?>
                                        <th width="8%" class="align-center">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        $getCompanyData = $obj->MySQLSelect("SELECT eSystem,iCompanyId FROM company WHERE 1=1");
                                        $eSystemArr = $driverTimingArr = array();
                                        $serverTimeZone = date_default_timezone_get();
                                        for ($g = 0; $g < scount($getCompanyData); $g++) {
                                            $eSystemArr[$getCompanyData[$g]['iCompanyId']] = $getCompanyData[$g]['eSystem'];
                                        }
                                        $driverTimingArr = checkTimeAvailabilityIsSelectedByProvider(0);
                                        $driverServiceArr = checkServicesIsSelectedByProvider(0);
                                        //echo "<pre>";print_r($data_drv);die;
                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                            $status_cmp = ($data_drv[$i]['cmp_status'] == "Inactive") ? " (Inactive)" : "";
                                            $hideUfxColumn = 1;
                                            if (isset($eSystemArr[$data_drv[$i]['iCompanyId']]) && strtoupper($eSystemArr[$data_drv[$i]['iCompanyId']]) == "DELIVERALL") {
                                                $hideUfxColumn = 0;
                                            }
                                            $driverId = $data_drv[$i]['iDriverId'];
                                            $driverTimeCount = $driverServiceCount = 0;
                                            if (isset($driverTimingArr[$driverId])) {
                                                $driverTimeCount = scount($driverTimingArr[$driverId]);
                                            }
                                            $driverServiceArr = array();
                                            if (isset($driverServiceArr[$driverId])) {
                                                $driverServiceArr = $driverServiceArr[$driverId];
                                            }
                                            if (in_array(1, $driverServiceArr)) {
                                                $driverServiceCount = 1;
                                            }
                                            $Registration_vTimezone = $data_drv[$i]['vTimeZone'];
                                            if(empty($Registration_vTimezone))
                                            {
                                                $timeZone_sql = "SELECT vTimeZone FROM country WHERE vCountryCode='".$data_drv[$i]['vCountry']."' ";
                                                $get_timezone_data = $obj->MySQLSelect($timeZone_sql);
                                                if(isset($get_timezone_data) && !empty($get_timezone_data[0]['vTimeZone'])){
                                                    $Registration_vTimezone =  $get_timezone_data[0]['vTimeZone'];
                                                }
                                            }
                                            $date_format_data_array = array(
                                                'tdate' => (!empty($Registration_vTimezone)) ? converToTz($data_drv[$i]['tRegistrationDate'],$Registration_vTimezone,$serverTimeZone) : $data_drv[$i]['tRegistrationDate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_registration_date_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                            $time_zone_difference_text = "<br>(UTC:".DateformatCls::getUTCDiff($Registration_vTimezone,$date_format_data_array['tdate']).")";
                                            ?>
                                            <tr class="gradeA">
                                                <?php if ($userObj->hasPermission([
                                                'update-status-providers',
                                                'delete-providers'
                                                ]) && $eStatus != 'Deleted') { ?>
                                                <td align="center">
                                                    <input type="checkbox" id="checkbox"
                                                           name="checkbox[]" value="<?= $driverId; ?>"/>&nbsp;
                                                </td>
                                            <?php } ?>
                                                <td>
                                                    <a href="javascript:void(0);"
                                                       onClick="show_driver_details('<?= $driverId; ?>')"
                                                       style="text-decoration: underline;"><?= clearName($data_drv[$i]['driverName']); ?></a>
                                                </td>
                                                <?php if (ONLYDELIVERALL == 'No') { ?>
                                                    <td><?= clearCmpName($data_drv[$i]['vCompany'] . $status_cmp); ?></td>
                                                <?php } ?>
                                                <td style="word-break: break-all;">
                                                    <?php if ($data_drv[$i]['vEmail'] != '') { ?>

                                                        <?= clearEmail($data_drv[$i]['vEmail']); ?>
                                                    <?php } else {
                                                        echo '--';
                                                    } ?></td>
                                                <?php
                                                if ($APP_TYPE != "UberX") {
                                                    if ($data_drv[$i]['count'] != 0) {
                                                        ?>
                                                        <td align="center">
                                                            <?php if ($userObj->hasPermission([
                                                                'view-provider-vehicles-taxi-service',
                                                                'view-provider-vehicles-parcel-delivery',
                                                                'view-provider-vehicles'
                                                            ])) { ?>
                                                                <a href="vehicles.php?&actionSearch=1&iDriverId=<?= $driverId; ?>"
                                                                   target="_blank"><?= $data_drv[$i]['count']; ?></a>
                                                                <?php
                                                            } else {
                                                                echo $data_drv[$i]['count'];
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td align="center"><?= $data_drv[$i]['count']; ?></td>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                <td align="left"><?= $get_registration_date_format['tDisplayDate']."<br/>".$get_registration_date_format['tDisplayTime'].$time_zone_difference_text;
                                                //DateTime($data_drv[$i]['tRegistrationDate'], '7') ?></td>
                                                <td align="left">
                                                    <?php if (!empty($data_drv[$i]['vPhone'])) { ?>
                                                        (+<?= $data_drv[$i]['vCode']; ?>)
                                                        <?= clearPhone($data_drv[$i]['vPhone']); ?>
                                                    <?php } ?>
                                                </td>
                                                <?php if ($userObj->hasPermission('add-wallet-balance-provider')) { ?>
                                                    <td>
                                                        <?php
                                                        $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($driverId, "Driver");
                                                        if ($data_drv[$i]['eStatus'] != "Deleted") {
                                                            echo formateNumAsPerCurrency($user_available_balance, '');
                                                            ?>
                                                            <?php if ($userObj->hasPermission('add-wallet-balance-provider')) { ?>
                                                                <button type="button"
                                                                        onClick="Add_money_driver('<?= $driverId; ?>')"
                                                                        class="btn btn-success btn-xs">Add Balance
                                                                </button>
                                                            <?php } ?>
                                                            <?php
                                                        } else {
                                                            echo formateNumAsPerCurrency($user_available_balance, '');
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>

                                                <?php $selectedservice = checkUFXServicesIsSelectedByProvider($driverId);
                                                $selectedservicedocumentCount = isset($selectedservice) && !empty($selectedservice['documentCount']) ? $selectedservice['documentCount'] : '';
                                                if ($doc_count != 0) { ?>
                                                    <?php if($userObj->hasPermission('edit-provider-document')){     ?>
                                                    <td align="center">
                                                        <?php $newUrl2 = $LOCATION_FILE_ARRAY['DRIVER_DOCUMENT_ACTION']."?id=" . $driverId . "&action=edit&user_type=driver"; ?>
                                                        <a href="<?= $newUrl2; ?>" data-toggle="tooltip"
                                                           title="Edit <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Document"
                                                           class="adminProviderDocumentClass"
                                                           onclick="<?php if ($MODULES_OBJ->isEnableServiceTypeWiseProviderDocument() == "Yes" && $selectedservicedocumentCount == 0) { ?> alert('Please Add the services you will be offering'); <?php } ?> ">
                                                            <img src="img/edit-doc.png" alt="Edit Document">
                                                        </a>
                                                        <?php if (checkDocumentIsUploadedByProvider($driverId, $data_drv[$i]['vCountry']) > 0 && $SHOW_PROVIDER_FILL_DETAILS_TICK_MARK_TO_ADMIN == "Yes") { ?>
                                                            <img src="img/active-icon-c.png" alt="Edit Document">
                                                        <?php } ?>
                                                    </td>
                                                    <?php }    ?>
                                                    <?php
                                                }
                                                if (ONLYDELIVERALL == 'No' && $ufxEnable == 'Yes') {
                                                    if ($APP_TYPE == "UberX" || $APP_TYPE == 'Ride-Delivery-UberX') {
                                                        ?>
                                                        <?php if ($userObj->hasPermission('manage-provider-services')) { ?>
                                                            <td align="center">
                                                                <?php if ($hideUfxColumn > 0) { ?>
                                                                    <?php $newUrl2 = "manage_service_type.php?iDriverId=" . $driverId . ""; ?>
                                                                    <a href="<?= $newUrl2; ?>" data-toggle="tooltip"
                                                                       title="Edit Service Type">
                                                                        <img src="img/view-details.png"
                                                                             alt="Edit Document">
                                                                    </a>
                                                                    <?php if ($driverServiceCount > 0 && $SHOW_PROVIDER_FILL_DETAILS_TICK_MARK_TO_ADMIN == "Yes") { ?>
                                                                        <img src="img/active-icon-c.png" alt="">
                                                                    <?php } ?>
                                                                <?php } else { ?>
                                                                    -----
                                                                <?php } ?>
                                                            </td>
                                                        <?php } ?>
                                                        <?php if ($userObj->hasPermission('edit-availability')) { ?>
                                                            <td align="center">
                                                                <?php if ($hideUfxColumn > 0) { ?>
                                                                    <a href="add_availability.php?id=<?= $driverId; ?>">
                                                                        Edit
                                                                        <?= $langage_lbl_admin['LBL_AVAILABILITY']; ?>
                                                                    </a>
                                                                    <?php if ($driverTimeCount > 0 && $SHOW_PROVIDER_FILL_DETAILS_TICK_MARK_TO_ADMIN == "Yes") { ?>
                                                                        <img src="img/active-icon-c.png" alt="">
                                                                    <?php }
                                                                } else { ?>
                                                                    -----
                                                                <?php } ?>
                                                            </td>
                                                        <?php } ?>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                <td align="center">
                                                    <?php
                                                    if ($data_drv[$i]['eStatus'] == 'active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="image" data-toggle="tooltip"
                                                         title="<?= $data_drv[$i]['eStatus']; ?>">
                                                </td>
                                                <?php if (ONLYDELIVERALL == 'No' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxEnable == "Yes") { ?>
                                                    <?php if ($userObj->hasPermission('edit-providers')) { ?>
                                                        <td>
                                                            <?php if ($hideUfxColumn > 0) { ?>
                                                                <form name="frmfeatured" id="frmfeatured" action=""
                                                                      method="post">
                                                                    <input type="hidden" name="iDriverId"
                                                                           value="<?= $driverId; ?>">
                                                                    <input type="hidden" name="eIsFeatured"
                                                                           value="<?= ($data_drv[$i]['eIsFeatured'] == "Yes") ? 'No' : 'Yes' ?>">
                                                                    <input type="hidden" name="action" value="Featured">
                                                                    <button class="btn">
                                                                        <i class="<?= ($data_drv[$i]['eIsFeatured'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i> <?= ucfirst($data_drv[$i]['eIsFeatured']); ?>
                                                                    </button>
                                                                </form>
                                                            <?php } else { ?>
                                                                -----
                                                            <?php } ?>
                                                        </td>
                                                    <?php } ?>
                                                <?php } ?>

                                                <?php if($MODULES_OBJ->isEnablePremiumDriverPreference()) { ?>
                                                    <?php if ($userObj->hasPermission('edit-providers')) { ?>
                                                        <td>
                                                            <form name="frmPremiumDriver" id="frmPremiumDriver" action="" method="post">
                                                                <input type="hidden" name="iDriverId" value="<?= $driverId; ?>">
                                                                <input type="hidden" name="eIsPremium"
                                                                       value="<?= ($data_drv[$i]['eIsPremium'] == "Yes") ? 'No' : 'Yes' ?>">
                                                                <input type="hidden" name="action" value="PremiumDriver">
                                                                <button class="btn">
                                                                    <i class="<?= ($data_drv[$i]['eIsPremium'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i> <?= ucfirst($data_drv[$i]['eIsPremium']); ?>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    <?php } ?>
                                                <?php } ?>

                                                <?php if($userObj->hasPermission(['edit-providers' , 'update-status-providers' , 'delete-providers']) ){ ?>
                                                <td align="center" class="action-btn001">
                                                    <div class="share-button openHoverAction-class"
                                                         style="display: block;">
                                                        <label class="entypo-export">
                                                            <span><img src="images/settings-icon.png" alt=""></span>
                                                        </label>
                                                        <div class="social show-moreOptions for-five openPops_<?= $driverId; ?>">
                                                            <ul>
                                                                <?php if ($userObj->hasPermission('edit-providers')) { ?>
                                                                    <li class="entypo-twitter" data-network="twitter">
                                                                        <a href="<?= $LOCATION_FILE_ARRAY['DRIVER_ACTION'] ?>?id=<?= $driverId; ?>"
                                                                           data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>

                                                                <?php if ($userObj->hasPermission('update-status-providers')) { ?>
                                                                    <li class="entypo-facebook"
                                                                        data-network="facebook">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="changeStatus('<?= $driverId; ?>', 'Inactive')"
                                                                           data-toggle="tooltip" title="Activate">
                                                                            <img src="img/active-icon.png"
                                                                                 alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                        </a>
                                                                    </li>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="changeStatus('<?= $driverId; ?>', 'Active')"
                                                                           data-toggle="tooltip" title="Deactivate">
                                                                            <img src="img/inactive-icon.png"
                                                                                 alt="<?= $data_drv[$i]['eStatus']; ?>">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>
                                                                <?php if ($userObj->hasPermission('delete-providers') && $eStatus != 'Deleted') { ?>
                                                                    <li class="entypo-gplus" data-network="gplus">
                                                                        <a href="javascript:void(0);"
                                                                           onClick="changeStatusDelete('<?= $driverId; ?>')"
                                                                           data-toggle="tooltip" title="Delete">
                                                                            <img src="img/delete-icon.png"
                                                                                 alt="Delete">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>

                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="14"> No Records Found.</td>
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
                        <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> module will list
                        all <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> on this page.
                    </li>
                    <li>
                        Administrator can Activate / Deactivate / Delete
                        any <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> .
                    </li>
                    <li>
                        Administrator can export data in XLS format.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/driver.php" method="post">
    <input type="hidden" name="page" id="page" value="<?= $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?= $tpages; ?>">
    <input type="hidden" name="iDriverId" id="iMainId01" value="">
    <input type="hidden" name="iCompanyId" id="iCompanyId" value="<?= $iCompanyId; ?>">
    <input type="hidden" name="eStatus" id="eStatus" value="<?= $eStatus; ?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?= $option; ?>">
    <input type="hidden" name="keyword" value="<?= $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?= $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?= $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;">
                        <img src="images/icon/driver-icon.png" alt="">
                    </i>
                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
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
                <div id="driver_detail"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="driver_add_wallet_money" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;" class="fa fa-google-wallet"></i>
                    Add Balance
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <form class="form-horizontal" id="add_money_frm" method="POST" enctype="multipart/form-data" action=""
                  name="add_money_frm">
                <input type="hidden" id="action" name="action" value="addmoney">
                <input type="hidden" name="eTransRequest" id="eTransRequest" value="">
                <input type="hidden" name="eType" id="eType" value="Credit">
                <input type="hidden" name="eFor" id="eFor" value="Deposit">
                <input type="hidden" name="iDriverId" id="iDriver-Id" value="">
                <input type="hidden" name="eUserType" id="eUserType" value="Driver">
                <div class="col-lg-12">
                    <div class="input-group input-append">
                        <h5><?= $langage_lbl_admin['LBL_ADD_WALLET_DESC_TXT']; ?></h5>
                        <div class="ddtt">
                            <h4><?= $langage_lbl_admin['LBL_ENTER_AMOUNT']; ?></h4>
                            <input type="text" name="iBalance" id="iBalance" class="form-control iBalance add-ibalance"
                                   onKeyup="checkzero(this.value);">
                        </div>
                        <div id="iLimitmsg"></div>
                    </div>
                </div>
                <div class="nimot-class-but">
                    <input type="button" onClick="check_add_money();" class="save" id="add_money" value="<?= $langage_lbl_admin['LBL_Save']; ?>">
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
                </div>
            </form>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>
<?php include_once('footer.php'); ?>
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


    $(document).on("click", function (e) {

        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {

            $(".show-moreOptions").removeClass("active");

        }

    });


    function show_driver_details(driverid) {

        $("#driver_detail").html('');

        $("#imageIcons").show();

        $("#detail_modal").modal('show');


        if (driverid != "") {

            var ajaxData = {

                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',

                'AJAX_DATA': "iDriverId=" + driverid,

                'REQUEST_DATA_TYPE': 'html'

            };

            getDataFromAjaxCall(ajaxData, function (response) {

                if (response.action == "1") {

                    var data = response.result;

                    $("#driver_detail").html(data);

                    $("#imageIcons").hide();

                } else {

                    $("#imageIcons").hide();

                }

            });

        }

    }


    function Add_money_driver(driverid) {

        $("#driver_add_wallet_money").modal('show');

        $(".add-ibalance").val("");

        if (driverid != "") {

            var setDriverId = $('#iDriver-Id').val(driverid);


        }

    }


    function changeOrder(iAdminId) {

        $('#is_dltSngl_modal').modal('show');

        $(".action_modal_submit").unbind().click(function () {

            var action = $("#pageForm").attr('action');

            var page = $("#pageId").val();

            $("#pageId01").val(page);

            $("#iMainId01").val(iAdminId);

            $("#method").val('delete');

            var formValus = $("#pageForm").serialize();

            window.location.href = action + "?" + formValus;

        });

    }


    function check_add_money() {


        var iBalance = $(".add-ibalance").val();

        if (iBalance == '') {

            alert("Please enter amount");

            return false;

        } else if (iBalance == 0) {

            alert("You Can Not Enter Zero Number");

            return false;

        } else {

            $("#add_money").val('Please wait ...').attr('disabled', 'disabled');

            $('#add_money_frm').submit();

        }

    }


    $(".iBalance").keydown(function (e) {

        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||

            (e.keyCode == 65 && e.ctrlKey === true) ||

            (e.keyCode == 67 && e.ctrlKey === true) ||

            (e.keyCode == 88 && e.ctrlKey === true) ||

            (e.keyCode >= 35 && e.keyCode <= 39)) {

            return;

        }

        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {

            e.preventDefault();

        }

    });


    function checkzero(userlimit) {

        if (userlimit != "") {

            if (userlimit == 0) {

                $('#iLimitmsg').html('<span class="red">You Can Not Enter Zero Number</span>');

            } else if (userlimit <= 0) {

                $('#iLimitmsg').html('<span class="red">You Can Not Enter Negative Number</span>');

            } else {

                $('#iLimitmsg').html('');

            }

        } else {

            $('#iLimitmsg').html('');

        }

    }
</script>
</body>
<!-- END BODY-->
</html>