<?php
include_once('../common.php');

$script = 'Custom Delivery Charges';
$tblname = 'custom_delivery_charges_order';

$view = "view-custom-delivery-charges";
$create = "create-custom-delivery-charges";
$edit = "edit-custom-delivery-charges";
$delete = "delete-custom-delivery-charges";

$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$queryString = '';
if ($eType == 'runner') {
    $commonTxt = '-runner-delivery';
    $script = "RunnerCustomDeliveryCharges";
    $queryString = 'eType=' . $eType;
} else if ($eType == 'genie') {
    $commonTxt = '-genie-delivery';
    $script = "GenieCustomDeliveryCharges";
    $queryString = 'eType=' . $eType;
}


if(in_array($eType,['runner','genie'])){
    $view = $view.$commonTxt;
    $edit  = $edit.$commonTxt;
    $delete  =  $delete.$commonTxt;
    $create  =  $create.$commonTxt;
}

if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}
$sql = "SELECT dc.*,vt.iLocationId,lm.vLocationName FROM $tblname as dc LEFT JOIN vehicle_type as vt ON vt.iVehicleTypeId = dc.iVehicleTypeId LEFT JOIN location_master as lm ON lm.iLocationId = vt.iLocationId WHERE dc.eStatus = 'Active'";
$data = $obj->MySQLSelect($sql);

$currSql = "SELECT * FROM currency WHERE eDefault = 'Yes'";
$currencyData = $obj->MySQLSelect($currSql);

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Driver Delivery Charges</title>
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
                                <h2>Driver Delivery Charges</h2>
                                <?php //if(scount($data) < 3){
                                    if ($userObj->hasPermission($create)) { ?>
                                <a href="custom_delivery_charges_order_action.php?<?php echo $queryString; ?>" class="add-btn">ADD DELIVERY CHARGE</a>
                                <?php } //}?>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-lg-12" style="color: red;margin-bottom: 19px;">
                                Please note that delivery charges defined here will be applied to Store Deliveries.
                            </div>

                        </div>
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="20%" >Vehicle Type</th>
                                                    <th width="12%" >Localization</th>
                                                    <th width="12%" style="text-align:center;">Distance Range (<?php echo $DEFAULT_DISTANCE_UNIT; ?>)</th>
                                                    
                                                    <?php /*<th>Delivery Charge For User (<?php //echo $currencyData[0]['vName']; ?>)</th>
                                                    
                                                    <th>Delivery Charge For Driver (<?php echo $currencyData[0]['vName']; ?>)</th>*/ ?>
                                                    <th width="20%" style="text-align:center;">Delivery Charges Per Order For Completed Orders (<?php echo $currencyData[0]['vName']; ?>)</th>
                                                    <th width="20%" style="text-align:center;">Delivery Charges Per Order For Cancelled Orders (<?php echo $currencyData[0]['vName']; ?>)</th>
                                                    <?php if ($userObj->hasPermission($edit)) { ?>
                                                    <th width="8%" style="text-align:center;">Action</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(scount($data) > 0) { ?>
                                                    <?php foreach ($data as $value) {
                                                    $vSql = "SELECT vVehicleType_".$_SESSION['sess_lang']." as vVehicleType FROM vehicle_type WHERE iVehicleTypeId = '".$value['iVehicleTypeId']."'";
                                                    $vehicleData = $obj->MySQLSelect($vSql);
                                                    ?>
                                                    <?php if($value['iDistanceRangeTo'] == '100000000'){
                                                            $value['iDistanceRangeTo'] = "&#8734"; 
                                                        } else { 
                                                            $value['iDistanceRangeTo'] = $value['iDistanceRangeTo']; 
                                                        } ?>
                                                        <tr>
                                                        <td><?= $vehicleData[0]['vVehicleType']; ?></td>
                                                        <td><?= ($value['iLocationId'] == "-1") ? "All Locations" : $value['vLocationName']; ?></td>
                                                        <td align="center"><?= $value['iDistanceRangeFrom'].' - '.$value['iDistanceRangeTo']; ?></td>
                                                            
                                                        <?php /*<td><?= $value['fDeliveryChargeUser']; ?></td>
                                                        <td><?= $value['fDeliveryCharge']; ?></td>*/ ?>
                                                        <td align="center"><?= formateNumAsPerCurrency($value['fDeliveryCharge'],''); ?></td>
                                                        <td align="center"><?= formateNumAsPerCurrency($value['fDeliveryChargeCancelled'],''); ?></td>
                                                        <?php if ($userObj->hasPermission($edit)) { ?>
                                                            <td align="center" class="action-btn001">
                                                                <div class="share-button openHoverAction-class" style="display: block;">
                                                                    <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                                    <div class="social show-moreOptions openPops_<?= $value['iDeliveyChargeId']; ?>">
                                                                        <ul>
                                                                            <li class="entypo-twitter" data-network="twitter">
                                                                                <a href="custom_delivery_charges_order_action.php?id=<?= $value['iDeliveyChargeId']; ?>&<?php echo $queryString; ?>" data-toggle="tooltip" title="Edit">
                                                                                    <img src="img/edit-icon.png" alt="Edit" >
                                                                                </a>
                                                                            </li>
       
                                                                            <?php if ($value['eStatus'] != 'Deleted' && $userObj->hasPermission($delete)) { ?>
                                                                                <li class="entypo-gplus" data-network="gplus">
                                                                                    <a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $value['iDeliveyChargeId']; ?>')" data-toggle="tooltip" title="Delete">
                                                                                        <img src="img/delete-icon.png" alt="Delete" >
                                                                                    </a>
                                                                                </li>
                                                                            <?php } ?>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <!-- <td align="center">
                                                                <a href="custom_delivery_charges_order_action.php?id=<?= $value['iDeliveyChargeId']; ?>" data-toggle="tooltip" title="Edit">
                                                                    <img src="img/edit-icon.png" alt="Edit">
                                                                </a>
                                                            </td> -->
                                                        <?php } ?>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <tr class="gradeA">
                                                        <td colspan="6"> No Records Found.</td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                                </div>
                            </div>
                            <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>Administrator can Add / Edit / Delete any delivery charge.</li>
                            <li>To deactivate the delivery charge, set delivery charges as zero for both completed and cancelled delivery charges.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/custom_delivery_charges_order_action.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iDeliveyChargeId" id="iMainId01" value="" >
            <input type="hidden" name="method" id="method" value="" >
        </form>
        <?php include_once('footer.php'); ?>
        <script type="text/javascript">
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