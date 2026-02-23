<?php
include_once('../common.php');

$script = 'otpservicecategory';

if (!$userObj->hasPermission('manage-otp-for-stores')) {
    $userObj->redirect();
}

// if (!$MODULES_OBJ->isEnableOTPVerificationDeliverAll()) { 
//     header("Location:" . $tconfig["tsite_url_main_admin"] . "dashboard.php"); 
//     exit;
// }

if (isset($_REQUEST['subcat']) && $_REQUEST['subcat'] > 0) {
    $parent_ufx_catid = $_REQUEST['subcat'];
}

   
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDisplayOrder ASC';
if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vServiceName_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vServiceName_" . $default_lang . " DESC";
}

if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}

if ($sortby == 7) {
    if ($order == 0)
        $ord = " ORDER BY eOTPCodeEnable ASC";
    else
        $ord = " ORDER BY eOTPCodeEnable DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY iDisplayOrder ASC";
    else
        $ord = " ORDER BY iDisplayOrder DESC";
}

//End Sorting
$rdr_ssql = "";

// Start Search Parameters


$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : "185";

if (isset($_POST['action']) && $_POST['action'] == "OtpStatus") {    
    $iServiceId = isset($_REQUEST['iServiceIdEdit']) ? $_REQUEST['iServiceIdEdit'] : "";  
    $eOTPCodeEnable = isset($_POST['eOTPCodeEnable']) ? $_POST['eOTPCodeEnable'] : 'No';    

    $Fsql = "UPDATE `service_categories` SET `eOTPCodeEnable`='" . $eOTPCodeEnable . "' WHERE iServiceId ='" . $iServiceId . "'"; 
    $obj->sql_query($Fsql); 
    
    $_SESSION['success'] = '1';  
    $_SESSION['var_msg'] = $langage_lbl_admin["LBL_Record_Updated_successfully"];  
    header("Location:" . $tconfig["tsite_url_main_admin"] . "manage_otp_for_stores.php");
    exit;
}

$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND sc.eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";
        }
    } else {
        if ($eStatus != '') {
            $ssql .= " AND (sc.vServiceName_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND sc.eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND (sc.vServiceName_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
        }
    }
} else if ($eStatus != '' && $keyword == '') {
    $ssql .= " AND sc.eStatus = '" . clean($eStatus) . "'";
}
if (isset($_REQUEST['subcat']) && $_REQUEST['subcat'] > 0) {
    $ssql .= " AND sc.iParentId = '" . $_REQUEST['subcat'] . "'";
}
//Added By SP 
if ($eStatus != '') {
    $ssql .= "";
} else {
    $ssql .= " AND sc.eStatus != 'Deleted'";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page


$sql = "SELECT COUNT(sc.iServiceId) AS Total FROM `service_categories` as sc  JOIN vehicle_category as vc ON (vc.iServiceId = sc.iServiceId) WHERE 1=1 AND vc.eStatus != 'Deleted' $ssql GROUP BY vc.iServiceId $rdr_ssql";


$totalData = $obj->MySQLSelect($sql);

//$total_results = $totalData[0]['Total'];
$total_results = scount($totalData);
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
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
 
 $sql = "SELECT sc.iServiceId,sc.eStatus,sc.vServiceName_" . $default_lang . ",sc.eOTPCodeEnable,sc.iDisplayOrder FROM service_categories as sc JOIN vehicle_category as vc ON (vc.iServiceId = sc.iServiceId)   where 1=1 AND vc.eStatus != 'Deleted' AND sc.eStatus != 'Deleted' $ssql $rdr_ssql GROUP BY sc.iServiceId $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);
$hideColumn = 1; // 1-Hide ,0Show
if (isset($serviceType) && is_array($serviceType) && in_array(1, $serviceType)) {
    $hideColumn = 0;
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
        <title><?= $SITE_NAME ?> | Manage OTP For Service Categories</title>
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
                                <h2>Manage OTP For Service Categories</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <input type="hidden" name="sub_cid" value="<?php echo $sub_cid; ?>">
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <input type="hidden" name="option" id="option" value="">
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="12%" class="estatus_options" id="eStatus_options" >
                                        <select name="eStatus" id="estatus_value" class="form-control">
                                            <option value="" >Select Status</option>
                                            <option value='Active' <?php
                                                if ($eStatus == 'Active') {
                                                    echo "selected";
                                                }
                                                ?> >Active</option>
                                            <option value="Inactive" <?php
                                                if ($eStatus == 'Inactive') {
                                                    echo "selected";
                                                }
                                                ?> >Inactive</option>
                                            <?php if ($userObj->hasPermission('delete-vehicle-category')) { ?>
                                            <option value="Deleted" <?php
                                                if ($eStatus == 'Deleted') {
                                                    echo "selected";
                                                }
                                                ?> >Deleted</option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'manage_otp_for_stores.php'"/>
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
                                        <?php  if($sub_cid != '185'){ ?>
                                        <span class="col-lg-2 new-select001">
                                            <?php if ($userObj->hasPermission(['update-status-vehicle-category', 'delete-vehicle-category'])) { ?>
                                            <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                <option value="" >Select Action</option>
                                                <?php if ($userObj->hasPermission('update-status-vehicle-category')) { ?>
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
                                                <?php if ($userObj->hasPermission('delete-vehicle-category')&& $eStatus != 'Deleted') { ?>
                                                <option value="Deleted" <?php
                                                    if ($option == 'Delete') {
                                                        echo "selected";
                                                    }
                                                    ?> >Delete</option>
                                                <?php } ?> 
                                            </select>
                                            <?php } ?>
                                        </span>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="18%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Name <?php
                                                        if ($sortby == 1) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                        }
                                                        } else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <?php if ($hideColumn == 0) { ?>               
                                                    <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                        if ($sortby == '3') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?> <?php
                                                        if ($sortby == 3) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                        }
                                                        } else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <?php } ?>
                                                    <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Display Order <?php
                                                        if ($sortby == 4) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                        }
                                                        } else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="8%" align="center" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                        if ($sortby == '2') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Status <?php
                                                        if ($sortby == 2) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                        }
                                                        } else {
                                                        ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="12%" class="align-center">
                                                        <a href="javascript:void(0);" onClick="Redirect(7,<?php if ($sortby == '7') { echo $order; } else { ?>0<?php } ?>)">Ask OTP Confirmation Code Before Delivery 
                                                            <?php if ($sortby == 7) { 
                                                                if ($order == 0) { ?>
                                                                    <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> 
                                                                <?php } else { ?>
                                                                    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i>
                                                                <?php } 
                                                            } else { ?>
                                                                <i class="fa fa-sort" aria-hidden="true"></i> 
                                                            <?php } ?>
                                                        </a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    for ($i = 0; $i < scount($data_drv); $i++) {
                                                        
                                                            $iServiceIdEdit =  $data_drv[$i]['iServiceId'];
                                                        
                                                        ?>
                                                    <tr class="gradeA">
                                                        <td><?php echo $data_drv[$i]['vServiceName_' . $default_lang . '']; ?></td>
                                                        <td align="center"><?php echo $data_drv[$i]['iDisplayOrder']; ?></td>
                                                        <td width="10%" align="center">
                                                            <?php
                                                                if ($data_drv[$i]['eStatus'] == 'Active') {
                                                                    $dis_img = "img/active-icon.png";
                                                                } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                                    $dis_img = "img/inactive-icon.png";
                                                                } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                                    $dis_img = "img/delete-icon.png";
                                                                }
                                                                ?>
                                                            <img src="<?= $dis_img; ?>" alt="<?= $data_drv[$i]['eStatus'] ?>" data-toggle="tooltip" title="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                        </td>
                                                        <td align="center" style="text-align:center;" class="action-btn001">
                                                            <?php if ($userObj->hasPermission('update-status-otp-for-stores')) { ?>
                                                            <form name="frmfeatured" id="frmfeatured" action="" method="post">
                                                                <input type="hidden" name="iServiceIdEdit" value="<?= $iServiceIdEdit; ?>" >
                                                                <input type="hidden" name="eOTPCodeEnable" value="<?= ($data_drv[$i]['eOTPCodeEnable'] == "Yes") ? 'No' : 'Yes' ?>" >
                                                                <input type="hidden" name="action" value="OtpStatus" >
                                                                <button class="btn <?= ($data_drv[$i]['eOTPCodeEnable'] == "Yes") ? 'btn-success' : '' ?> "><i class="<?= ($data_drv[$i]['eOTPCodeEnable'] == "Yes") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i> <?php if (!empty($data_drv[$i]['eOTPCodeEnable'])) { ?>
                                                                    <?= ucfirst($data_drv[$i]['eOTPCodeEnable']); ?>
                                                                <?php } else { ?>
                                                                    No
                                                                <?php } ?>
                                                                </button>
                                                            </form>
                                                            <?php } else { ?>
                                                                --
                                                            <?php } ?>
                                                        </td>                                           
                                                    </tr>
                                                    <?php
                                                    }
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
                            </div>
                            <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>Manage OTP For Service Categories module will list all Service Categories on this page.</li>
                            <li>Administrator can manage OTP confirmation code for different Service Categories.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        
        <?php include_once('footer.php'); ?>
        <script>
            /* $(document).ready(function() {
             $('#eStatus_options').hide(); 
             $('#option').each(function(){
             if (this.value == 'eStatus') {
             $('#eStatus_options').show(); 
             $('.searchform').hide(); 
             }
             });
             });
             $(function() {
             $('#option').change(function(){
             if($('#option').val() == 'eStatus') {
             $('#eStatus_options').show();
             $("input[name=keyword]").val("");
             $('.searchform').hide(); 
             } else {
             $('#eStatus_options').hide();
             $("#estatus_value").val("");
             $('.searchform').show();
             } 
             });
             });*/
            
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
                //alert(action);
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