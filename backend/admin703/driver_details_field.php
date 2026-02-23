<?php
include_once('../common.php');

if (!$userObj->hasPermission('view-driver-detail-fields-rideshare')) {
    $userObj->redirect();
}

$script = 'RideShareDriverFields';
$tbl_name = 'ride_share_driver_fields';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';

$ord = ' ORDER BY `iDisplayOrder` ASC';
if($sortby == 1){
    if($order == 0)
        $ord = " ORDER BY vFieldName ASC";
    else
        $ord = " ORDER BY vFieldName DESC";
}

if($sortby == 2){
    if($order == 0)
        $ord = " ORDER BY tDescription ASC";
    else
        $ord = " ORDER BY tDescription DESC";
}

if($sortby == 3){
    if($order == 0)
        $ord = " ORDER BY iDisplayOrder ASC";
    else
        $ord = " ORDER BY iDisplayOrder DESC";
}

if($sortby == 4){
    if($order == 0)
        $ord = " ORDER BY eStatus ASC";
    else
        $ord = " ORDER BY eStatus DESC";
}
//End Sorting


// Start Search Parameters
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

$ssql = '';
if($keyword != ''){
    if($eStatus != '') {
        $ssql .= " AND (vFieldName LIKE '%".$keyword."%' OR tFieldName LIKE '%".$keyword."%' OR tDescription LIKE '%".$keyword."%' OR eInputType LIKE '%".$keyword."%') AND eStatus = '" . $eStatus . "' ";
    } else {
        $ssql.= " AND (vFieldName LIKE '%".$keyword."%' OR tFieldName LIKE '%".$keyword."%' OR tDescription LIKE '%".$keyword."%' OR eInputType LIKE '%".$keyword."%' OR eStatus LIKE '%".$keyword."%') ";
    }
} elseif ($eStatus != '') {
    $ssql .= " AND eStatus = '" . $eStatus . "' ";
}
// End Search Parameters

if(empty($eStatus)) {
    $eStatussql = " AND eStatus != 'Deleted'";    
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(iFieldId) AS Total FROM `".$tbl_name."` WHERE 1=1 $eStatussql $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page);
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];
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
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = "SELECT * FROM ".$tbl_name." WHERE 1=1 $eStatussql  $ssql $ord LIMIT $start, $per_page ";
$data_drv = $obj->MySQLSelect($sql);	

$endRecord = scount($data_drv);

$var_filter = "";
foreach ($_REQUEST as $key=>$val)
{
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;

?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
   <title><?=$SITE_NAME?> | Driver Details Fields</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <?php include_once('global_files.php');?>
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
                            <h2>Driver Details Fields</h2> 
                        </div>
                    </div>
                    <hr />
                </div>
                <?php include('valid_msg.php'); ?>
                <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                        <tbody>
                            <tr>
                                <td><label for="textfield"><strong>Search:</strong></label></td>
                                <td><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                           
                                <td  class="estatus_options" id="eStatus_options">
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
                                        ?> >Deleted
                                        </option>
                                    </select>
                                </td>

                                <td>
                                    <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                    <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='driver_details_field.php'"/>
                                </td>
                                
                                <?php if ($userObj->hasPermission('create-driver-detail-fields-rideshare')) { ?>
                                    <td><a class="add-btn" href="driver_details_field_action.php" style="text-align: center;">Add Field</a></td>
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
                                    <?php if ($userObj->hasPermission(['update-status-driver-detail-fields-rideshare', 'delete-driver-detail-fields-rideshare'])) { ?>
                                        <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                            <option value="" >Select Action</option>
                                            <?php if ($userObj->hasPermission('update-status-driver-detail-fields-rideshare')) { ?>
                                                <option value='Active' <?php if ($option == 'Active') { echo "selected"; } ?> >Active</option>
                                                <option value="Inactive" <?php if ($option == 'Inactive') {echo "selected"; } ?> >Inactive</option>
                                            <?php } ?>

                                            <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-driver-detail-fields-rideshare')) { ?>
                                                <option value="Deleted" <?php if ($option == 'Delete') {echo "selected"; } ?> >Delete</option>
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
                                                <th align="center" width="3%" style="text-align:center;">
                                                    <input type="checkbox" id="setAllCheck" >
                                                </th>

                                                <th width="15%">
                                                    <a href="javascript:void(0);" onClick="Redirect(1,<?php if($sortby == '1'){ echo $order; }else { ?>0<?php } ?>)">Field Name <?php if ($sortby == 1) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                </th>

                                                <th width="22%">
                                                    <a href="javascript:void(0);" onClick="Redirect(2,<?php if($sortby == '2'){ echo $order; }else { ?>0<?php } ?>)">Description <?php if ($sortby == 2) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                </th>

												<th width="10%" style="text-align:center;">
                                                    <a href="javascript:void(0);" onClick="Redirect(3,<?php if($sortby == '3'){ echo $order; }else { ?>0<?php } ?>)">Display Order <?php if ($sortby == 3) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                </th>

                                                <th  width="8%" style="text-align:center;">Input Type</th>
                                                <th  width="8%" style="text-align:center;">Required Field</th>
                                               														
                                                <th width="8%" style="text-align:center;">
                                                    <a href="javascript:void(0);" onClick="Redirect(4,<?php if($sortby == '4'){ echo $order; }else { ?>0<?php } ?>)">Status <?php if ($sortby == 4) { if($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php } }else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                </th>
                                                
                                                <th width="8%"   style="text-align:center;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if(!empty($data_drv)) {
                                                for ($i = 0; $i < scount($data_drv); $i++) { 
                                            ?>
                                            <tr class="gradeA">
                                                <td align="center" style="text-align:center;">
                                                    <input type="checkbox" id="checkbox" name="checkbox[]" <?php echo $default; ?> value="<?php echo $data_drv[$i]['iFieldId']; ?>" />
                                                </td>
                                                <td><?= $data_drv[$i]['vFieldName']; ?></td>
                                                <td><?= $data_drv[$i]['tDescription']; ?></td>
                                                <td align="center" ><?= $data_drv[$i]['iDisplayOrder']; ?></td>
                                                <td align="center" ><?= $data_drv[$i]['eInputType']; ?></td>
                                                <td align="center" ><?= $data_drv[$i]['eRequired']; ?></td>
                                                
                                                <td align="center">
                                                    <?php 
                                                    if($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if($data_drv[$i]['eStatus'] == 'Inactive'){
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if($data_drv[$i]['eStatus'] == 'Deleted'){
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="<?=$data_drv[$i]['eStatus'];?>" data-toggle="tooltip" title="<?=$data_drv[$i]['eStatus'];?>">
                                                </td>

                                                <td align="center" class="action-btn001">
                                                    <?php if ($userObj->hasPermission('edit-driver-detail-fields-rideshare') && $data_drv[$i]['eDefault'] == "Yes") { ?>
                                                        <a href="driver_details_field_action.php?id=<?= $data_drv[$i]['iFieldId']; ?>" data-toggle="tooltip" title="Edit">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                    <?php } else { ?>
                                                    <div class="share-button openHoverAction-class" style="display: block;">
                                                        <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                        <div class="social show-moreOptions openPops_<?= $data_drv[$i]['iFieldId']; ?>">
                                                            <ul>
                                                                <?php if ($userObj->hasPermission('edit-driver-detail-fields-rideshare')) { ?>
                                                                <li class="entypo-twitter" data-network="twitter"><a href="driver_details_field_action.php?id=<?= $data_drv[$i]['iFieldId']; ?>" data-toggle="tooltip" title="Edit">
                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                </a></li>
                                                                <?php } if ($userObj->hasPermission('update-status-driver-detail-fields-rideshare')) { ?>
                                                                    <li class="entypo-facebook" data-network="facebook"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iFieldId']; ?>','Inactive')"  data-toggle="tooltip" title="Active">
                                                                        <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >
                                                                    </a></li>
                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iFieldId']; ?>','Active')" data-toggle="tooltip" title="Inactive">
                                                                        <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>" >    
                                                                    </a></li>
                                                                <?php } ?>
                                                                <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-driver-detail-fields-rideshare')) { ?>
                                                                <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iFieldId']; ?>')"  data-toggle="tooltip" title="Delete">
                                                                    <img src="img/delete-icon.png" alt="Delete" >
                                                                </a></li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php } } else { ?>
                                            <tr class="gradeA">
                                                <td colspan="7"> No Records Found.</td>
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
                        <li>Administrator can Activate / Deactivate / Delete any Driver Details Fields.</li>
                    </ul>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
            
    <form name="pageForm" id="pageForm" action="action/driver_details_field.php" method="post" >
        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
        <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
        <input type="hidden" name="iFieldId" id="iMainId01" value="" >
        <input type="hidden" name="status" id="status01" value="" >
        <input type="hidden" name="statusVal" id="statusVal" value="" >
        <input type="hidden" name="option" value="<?php echo $option; ?>" >
        <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
        <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
        <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
        <input type="hidden" name="method" id="method" value="" >
    </form>
    <?php include_once('footer.php'); ?>
    <script>
        
        $("#setAllCheck").on('click',function(){
            if($(this).prop("checked")) {
                jQuery("#_list_form input[type=checkbox]").each(function() {
                    if($(this).attr('disabled') != 'disabled'){
                        this.checked = 'true';
                    }
                });
            }else {
                jQuery("#_list_form input[type=checkbox]").each(function() {
                    this.checked = '';
                });
            }
        });
        
        $("#Search").on('click', function(){
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action+"?"+formValus;
        });
        
        $('.entypo-export').click(function(e){
             e.stopPropagation();
             var $this = $(this).parent().find('div');
             $(".openHoverAction-class div").not($this).removeClass('active');
             $this.toggleClass('active');
        });
        
        $(document).on("click", function(e) {
            if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
              $(".show-moreOptions").removeClass("active");
            }
        });
        
    </script>
</body>
<!-- END BODY-->
</html>