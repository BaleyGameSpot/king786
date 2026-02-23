<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-rental-packages')){
    $userObj->redirect();
}
if ($default_lang == ""){
    $default_lang = "EN";
}
$script = 'InterCity Rental Package';
$id = isset($_GET['id'])?$_GET['id']:'';
$sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
$order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
$tbl_name = 'rental_package';
$iVehicleTypeId = isset($_REQUEST['iVehicleTypeId'])?$_REQUEST['iVehicleTypeId']:'';
$success = isset($_REQUEST['success'])?$_REQUEST['success']:0;
$fPrice = isset($_POST['fPrice'])?$_POST['fPrice']:'';
$fKiloMeter = isset($_POST['fKiloMeter'])?$_POST['fKiloMeter']:'';
$fHour = isset($_POST['fHour'])?$_POST['fHour']:'';
$fPricePerKM = isset($_POST['fPricePerKM'])?$_POST['fPricePerKM']:'';
$fPricePerHour = isset($_POST['fPricePerHour'])?$_POST['fPricePerHour']:'';
$new_iVehicleTypeId = isset($_POST['iVehicleTypeId'])?$_POST['iVehicleTypeId']:'';
$ord = ' ORDER BY vPackageName_'.$default_lang.' ASC';
if ($sortby == 1){
    if ($order == 0){
        $ord = " ORDER BY vPackageName_".$default_lang." ASC";
    }else{
        $ord = " ORDER BY vPackageName_".$default_lang." DESC";
    }
}
if ($sortby == 2){
    if ($order == 0){
        $ord = " ORDER BY fPrice ASC";
    }else{
        $ord = " ORDER BY fPrice DESC";
    }
}
if ($sortby == 3){
    if ($order == 0){
        $ord = " ORDER BY fKiloMeter ASC";
    }else{
        $ord = " ORDER BY fKiloMeter DESC";
    }
}
if ($sortby == 4){
    if ($order == 0){
        $ord = " ORDER BY fHour ASC";
    }else{
        $ord = " ORDER BY fHour DESC";
    }
}
if ($sortby == 4){
    if ($order == 0){
        $ord = " ORDER BY fPricePerKM ASC";
    }else{
        $ord = " ORDER BY fPricePerKM DESC";
    }
}
if ($sortby == 4){
    if ($order == 0){
        $ord = " ORDER BY fPricePerHour ASC";
    }else{
        $ord = " ORDER BY fPricePerHour DESC";
    }
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER;
$sql = "";
$sql = "SELECT count(iRentalPackageId) as Total from rental_package where iVehicleTypeId='".$id."'";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page);
$show_page = 1;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])){
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages){
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }else{
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
}else{
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page'])?intval($_GET['page']):0;
$tpages = $total_pages;
if ($page <= 0){
    $page = 1;
}
//Pagination End

$sql = "SELECT rp.iRentalPackageId, rp.iVehicleTypeId, rp.vPackageName_".$default_lang." as packageName, rp.fPrice, rp.fKiloMeter, rp.fHour, rp.fPricePerKM, rp.fPricePerHour ,vt.vVehicleType FROM rental_package as rp LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId=rp.iVehicleTypeId  WHERE rp.iVehicleTypeId = '".$id."' $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$query = "SELECT vVehicleType,iLocationid FROM vehicle_type WHERE iVehicleTypeId = '".$id."'";
$data_main = $obj->MySQLSelect($query);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val){
    if ($key != "tpages" && $key != 'page'){
        $var_filter .= "&$key=".stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8">
<![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9">
<![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?=$SITE_NAME;?> | Rental Packages </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
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
                        <h2><?php echo $data_main[0]['vVehicleType']; ?> Rental Packages</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php if ($success == 3){ ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php print_r($error); ?>
                </div>
                <br/>
            <?php } ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="30%">
                            <a href="intercity_vehicle_list.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                                <?php if ($userObj->hasPermission('create-rental-packages')){ ?>
                            </a>
                            <a id="flip" class="add-btn" href="rental_package_action.php?iVehicleTypeId=<?=$id;?>" style="text-align: center;">
                                Add new Package
                            </a>
                        </td>
                        <?php } ?>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="admin-nir-export">
                <div class="changeStatus col-lg-12 option-box-left">
                                <span class="col-lg-2 new-select001">
                                    <?php if ($userObj->hasPermission('delete-rental-packages')){ ?>
                                        <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                        <option value="">Select Action</option>
                                        <option value="Deleted" <?php if ($option == 'Delete'){
                                            echo "selected";
                                        } ?> >Delete</option>
                                    </select>
                                    <?php } ?>
                                </span>
                </div>
            </div>
            <div style="clear:both;"></div>
            <?php include('valid_msg.php'); ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th align="center" width="3%" style="text-align:center;">
                                            <input type="checkbox" id="setAllCheck"></th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Package Name<?php if ($sortby == 1){
                                                    if ($order == 0){ ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }else{ ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Rental Total Price(In <?=$db_currency[0]['vName']?>) <?php if ($sortby == 2){
                                                    if ($order == 0){ ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }else{ ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Rental
                                                <em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?><?php if ($sortby == 3){
                                                        if ($order == 0){ ?>
                                                            <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                            <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    }else{ ?> <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                            </a></th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Rental Hour<?php if ($sortby == 4){
                                                    if ($order == 0){ ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }else{ ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Additional Price/<em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?>
                                                    <br/>(In <?=$db_currency[0]['vName']?>) <?php if ($sortby == 5){
                                                        if ($order == 0){ ?>
                                                            <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                            <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    }else{ ?> <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                            </a></th>
                                        <th width="15%">
                                            <a href="javascript:void(0);" onClick="Redirect(6,<?php if ($sortby == '6'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Additional Price/Min
                                                <br/>(In <?=$db_currency[0]['vName']?>) <?php if ($sortby == 6){
                                                    if ($order == 0){ ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }else{ ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="8%" align="center" style="text-align:center;">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)){
                                        for ($i = 0;$i < scount($data_drv);$i++){
                                            $sql = "SELECT count(iRentalPackageId) AS TotalPackage from  rental_package where iVehicleTypeId = '".$data_drv[$i]['iVehicleTypeId']."'";
                                            $rental_package = $obj->MySQLSelect($sql);
                                            $total_rental_package = $rental_package[0]['TotalPackage'];
                                            ?>
                                            <tr class="gradeA">
                                                <td style="text-align:center;">
                                                    <input type="checkbox" id="checkbox" name="checkbox[]" value="<?php echo $data_drv[$i]['iRentalPackageId']; ?>"/>&nbsp;
                                                </td>
                                                <td><?=$data_drv[$i]['packageName']?></td>
                                                <td><?=formateNumAsPerCurrency($data_drv[$i]['fPrice'],'');?></td>
                                                <td><?=$data_drv[$i]['fKiloMeter']?></td>
                                                <td><?=$data_drv[$i]['fHour']?></td>
                                                <td><?=$data_drv[$i]['fPricePerKM']?></td>
                                                <td><?=$data_drv[$i]['fPricePerHour']?></td>

                                                <td align="center" style="text-align:center;" class="action-btn001">
                                                    <div class="share-button openHoverAction-class"
                                                        style="display: block;">
                                                        <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span>
                                                        </label>
                                                        <div class="social show-moreOptions for-five openPops_<?=$data_drv[$i]['iUserId'];?>">
                                                            <ul>

                                                                <?php if ($userObj->hasPermission('edit-users')){ ?>
                                                                    <li class="entypo-twitter"
                                                                        data-network="twitter">
                                                                        <a href="intercity_package_action.php?iVehicleTypeId=<?=$data_drv[$i]['iVehicleTypeId'];?>&iRentalPackageId=<?=$data_drv[$i]['iRentalPackageId'];?>"
                                                                            data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>
                                                                <?php if($userObj->hasPermission('delete-rental-packages')){ ?>
                                                                    <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="changeStatusDelete('<?php echo $data_drv[$i]['iRentalPackageId']; ?>')"data-toggle="tooltip" title="Delete">
                                                                            <img src="img/delete-icon.png" alt="Delete" >
                                                                        </a>
                                                                    </li>
                                                                <?php } ?>

                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>
                                        <?php }
                                    }else{ ?>
                                        <tr class="gradeA">
                                            <td colspan="8"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                        <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                                <li>Commision for the taxi rental will be the commission for the vehicle type</li>
                                <li>Please enter minimum package duration to be 1 hour</li>
                                <li>Package hours can be round number like (2 hour), and it cannot be half past values like( 2:30).</li>
                                <li>If the "Enable Surge charge on rental" option is enabled from the settings then surge prices are applied to the taxi rental trips.</li>
                                <li>Also, waiting and cancellation charges are applied to the rent rate if applicable to the vehicle type.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
    <form name="pageForm" id="pageForm" action="action/rental_package.php" method="post">
        <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
        <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
        <input type="hidden" name="iRentalPackageId" id="iMainId01" value="">
        <input type="hidden" name="status" id="status01" value="">
        <input type="hidden" name="eType" id="eType" value="<?php echo $eType; ?>">
        <input type="hidden" name="statusVal" id="statusVal" value="">
        <input type="hidden" name="option" value="<?php echo $option; ?>">
        <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
        <input type="hidden" name="id" id="id" value="<?php echo $id ?>">
        <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
        <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
        <input type="hidden" name="method" id="method" value="">
    </form>
<?php include_once('footer.php'); ?>
</body>

<script>

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
<!-- END BODY-->
</html>