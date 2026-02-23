<?php
include_once('../common.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$script = 'Custom Delivery Charges';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$queryString = '';

$view = "view-custom-delivery-charges";
$create = "create-custom-delivery-charges";
$edit = "edit-custom-delivery-charges";
$delete = "delete-custom-delivery-charges";

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

$tbl_name = 'custom_delivery_charges_order';

$sql = "SELECT * FROM $tbl_name WHERE eStatus = 'Active'";
$deliverydata = $obj->MySQLSelect($sql);

/*if(scount($deliverydata) == 3 && $action == 'Add') {
        $_SESSION['success'] = 1;
        $_SESSION['var_msg'] = "You Can't add/edit delivery charge. you can add only 3 records.";
        header("Location:custom_delivery_charge_order.php");
        exit;
}*/

if(isset($_POST['iDistanceRangeTo']) && $_POST['iDistanceRangeTo'] == mb_convert_encoding('&#x221E;', 'UTF-8', 'HTML-ENTITIES')){
     $_POST['iDistanceRangeTo'] = '100000000';
}

$iDistanceRangeFrom = isset($_POST['iDistanceRangeFrom']) ? $_POST['iDistanceRangeFrom'] : '';
$iDistanceRangeTo = isset($_POST['iDistanceRangeTo']) ? $_POST['iDistanceRangeTo'] : '';
$fDeliveryCharge = isset($_POST['fDeliveryCharge']) ? $_POST['fDeliveryCharge'] : '';
$fDeliveryChargeUser = isset($_POST['fDeliveryChargeUser']) ? $_POST['fDeliveryChargeUser'] : 0;
$fDeliveryChargeCancelled = isset($_POST['fDeliveryChargeCancelled']) ? $_POST['fDeliveryChargeCancelled'] : 0;
$iVehicleTypeId = isset($_POST['iVehicleTypeId']) ? $_POST['iVehicleTypeId'] : '';
$set_to_infinity = isset($_POST['set_to_infinity']) ? $_POST['set_to_infinity'] : 0;
if($set_to_infinity == 1)
{
    $iDistanceRangeTo = '100000000';
}
// echo "<pre>"; print_r($_POST); exit;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submitbtn'])) {
    if ($action == "Add" && !$userObj->hasPermission($create)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Delivery Charges.';
        header("Location:custom_delivery_charge_order.php?".$queryString);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Delivery Charges.';
        header("Location:custom_delivery_charge_order.php?".$queryString);
        exit;
    }
    //Start :: Upload Image Script
    /*if (!empty($id)) {
        if (SITE_TYPE == 'Demo') {
            $_SESSION['success'] = 2;
            header("Location:custom_delivery_charge_order.php?id=" . $id);
            exit;
        }
    }*/
    if (SITE_TYPE == 'Demo') {
        header("Location:custom_delivery_charge_order.php?id=" . $id . '&success=2&'.$queryString);
        exit;
    }

    $error = 0;
    $sqlCheckData = $obj->MySQLSelect("SELECT * FROM custom_delivery_charges_order WHERE eStatus = 'Active'");
    if(scount($sqlCheckData) > 0 && $action == "Add")
    {
        $getLastDistance = $obj->MySQLSelect("SELECT iDistanceRangeTo FROM custom_delivery_charges_order WHERE iVehicleTypeId = $iVehicleTypeId ORDER BY iDistanceRangeTo DESC LIMIT 1");
        $getFromStartRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM custom_delivery_charges_order WHERE iVehicleTypeId = $iVehicleTypeId AND iDistanceRangeFrom = 0");

        if(scount($getFromStartRange) > 0)
        {
            $getDistanceRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM custom_delivery_charges_order WHERE iVehicleTypeId = $iVehicleTypeId");
            if($iDistanceRangeFrom != $getLastDistance[0]['iDistanceRangeTo'])
            {
                $_SESSION['success'] = 3;

                if($getLastDistance[0]['iDistanceRangeTo'] >= "100000000")
                {
                    $_SESSION['var_msg'] = 'You cannot add delivery charge as you have already added maximum distance range for the selected vehicle type.';    
                }
                else {
                    $_SESSION['var_msg'] = 'Distance Range From should be equals to '.$getLastDistance[0]['iDistanceRangeTo'];
                }
                
                $error = 1;
            }

            $inRange = 0;
            foreach ($getDistanceRange as $range) {
                if(($iDistanceRangeFrom > $range['iDistanceRangeFrom'] && $iDistanceRangeFrom < $range['iDistanceRangeTo']) || ($iDistanceRangeTo > $range['iDistanceRangeFrom'] && $iDistanceRangeTo <= $range['iDistanceRangeTo'])) {
                    $inRange = 1;
                }
            }

            if($inRange == 1)
            {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = 'Distance Range From/To lies within already defined delivery charge for the selected vehicle type.';
                $error = 1;
            }
        }
        else {
            if($iDistanceRangeFrom != 0)
            {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = 'Distance Range From should start from 0.';
                $error = 1;
            }
            else {
                $getDistanceRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM custom_delivery_charges_order WHERE iVehicleTypeId = $iVehicleTypeId");

                $inRange = 0;
                foreach ($getDistanceRange as $range) {
                    if(($iDistanceRangeFrom > $range['iDistanceRangeFrom'] && $iDistanceRangeFrom < $range['iDistanceRangeTo']) || ($iDistanceRangeTo > $range['iDistanceRangeFrom'] && $iDistanceRangeTo <= $range['iDistanceRangeTo'])) {
                        $inRange = 1;
                    }
                }

                if($inRange == 1)
                {
                    $_SESSION['success'] = 3;
                    $_SESSION['var_msg'] = 'Distance Range From/To lies within already defined delivery charge for the selected vehicle type.';
                    $error = 1;
                }
            }
        }
    }
    else if(scount($sqlCheckData) == 0 && $action == "Add") {
        $sqlCheckData = $obj->MySQLSelect("SELECT * FROM custom_delivery_charges_order WHERE eStatus = 'Active'");
        if($iDistanceRangeFrom != 0)
        {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'Distance Range From should start from 0.';
            $error = 1;
        }
    } else {
        /*$getLastDistance = $obj->MySQLSelect("SELECT iDistanceRangeTo FROM custom_delivery_charges_order WHERE iDeliveyChargeId != $id ORDER BY iDistanceRangeTo DESC LIMIT 1");
        $getDistanceRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM custom_delivery_charges_order WHERE iVehicleTypeId = $iVehicleTypeId AND iDeliveyChargeId != $id");

        if($iDistanceRangeFrom != $getLastDistance[0]['iDistanceRangeTo'])
        {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'Distance Range From should be equals to '.$getLastDistance[0]['iDistanceRangeTo'];
            $error = 1;
        }
            
        $inRange = 0;
        if(scount($getDistanceRange) > 0)
        {
            foreach ($getDistanceRange as $range) {
                if(($iDistanceRangeFrom >= $range['iDistanceRangeFrom'] && $iDistanceRangeFrom < $range['iDistanceRangeTo']) || ($iDistanceRangeTo > $range['iDistanceRangeFrom'] && $iDistanceRangeTo <= $range['iDistanceRangeTo'])) {
                    $inRange = 1;
                }
            }
        }        

        if($inRange == 1)
        {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'Distance Range From/To lies within already defined delivery charge for the selected vehicle type.';
            $error = 1;
        }*/
    }

    if($iDistanceRangeTo <= $iDistanceRangeFrom && $error == 0 && $action == "Add")
    {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'Distance Range From should be less than or equals to Distance Range To';
        $error = 1;
    }
    
    if($error == 0)
    {
        $q = "INSERT INTO ";
        $where = '';

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDeliveyChargeId` = '" . $id . "'";
        }

        $query = $q . " `" . $tbl_name . "` SET
          `iDistanceRangeFrom` = '" . $iDistanceRangeFrom . "',
          `iDistanceRangeTo` = '" . $iDistanceRangeTo . "',              
          `iVehicleTypeId` = '" . $iVehicleTypeId . "',
          `fDeliveryChargeUser` = '" . $fDeliveryChargeUser . "',
          `fDeliveryChargeCancelled` = '" . $fDeliveryChargeCancelled . "',
          `fDeliveryCharge` = '" . $fDeliveryCharge . "'" . $where;

        if ($id != '') {
            $query = $q . " `" . $tbl_name . "` SET
              `fDeliveryChargeUser` = '" . $fDeliveryChargeUser . "',
              `fDeliveryChargeCancelled` = '" . $fDeliveryChargeCancelled . "',
              `fDeliveryCharge` = '" . $fDeliveryCharge . "'" . $where;
        }

        $obj->sql_query($query);

        $id = ($id != '') ? $id : $obj->GetInsertId();

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }

        header("Location:custom_delivery_charge_order.php?".$queryString);
        exit;
    }
    
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iDeliveyChargeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iDistanceRangeFrom = $value['iDistanceRangeFrom'];
            $iDistanceRangeTo = $value['iDistanceRangeTo'];
            $fDeliveryCharge = $value['fDeliveryCharge'];
            $fDeliveryChargeUser = $value['fDeliveryChargeUser'];
            $iVehicleTypeId = $value['iVehicleTypeId'];
            $fDeliveryChargeCancelled = $value['fDeliveryChargeCancelled'];
        }
    }
}

/*if($action == "Add")
{
    $getLastDistance = $obj->MySQLSelect("SELECT iDistanceRangeTo FROM custom_delivery_charges_order ORDER BY iDistanceRangeTo DESC LIMIT 1");

    $iDistanceRangeFrom = 0;
    if($iDistanceRangeFrom != $getLastDistance[0]['iDistanceRangeTo'])
    {
        $iDistanceRangeFrom = $getLastDistance[0]['iDistanceRangeTo'];
    }
}*/


$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

$vehicle_type_sql = "SELECT vt.*, IF(vt.iLocationId = '-1', 'All Locations', lm.vLocationName) as vLocationName from vehicle_type as vt LEFT JOIN location_master as lm ON lm.iLocationId = vt.iLocationId where vt.eStatus ='Active' AND vt.eType = 'DeliverAll'";

$db_select_data = $obj->MySQLSelect($vehicle_type_sql);

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Driver Delivery Charges  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="css/bootstrap-select.css" rel="stylesheet" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style type="text/css">
            .distance-range {
                display: flex;
                align-items: center;
            }

            .distance-range div:nth-child(2) {
                padding: 0;
                text-align: center;
                width: 20px
            }

            .bootstrap-select.btn-group.disabled, .bootstrap-select.btn-group > .disabled {
                background-color: #eeeeee;
                opacity: 1
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action . " Driver Delivery Charges"; ?> </h2>
                            <a class="back_link" href="custom_delivery_charge_order.php?<?php echo $queryString; ?>">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <?php include('valid_msg.php'); ?>
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <?php } ?>
                    <div class="body-div">
                        <div class="form-group location-wise-box">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" action="" enctype="multipart/form-data" id="deliveryChargeForm">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="custom_delivery_charge_order.php?<?php echo $queryString; ?>"/>

                                <div class="row">                   
                                    <div class="col-lg-12">
                                        <label>Distance Range <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="row" style="padding-bottom: 0">
                                            <div class="distance-range">
                                                <div class="col-lg-3">
                                                    <input type="text" class="form-control distance-range-input" name="iDistanceRangeFrom"  id="iDistanceRangeFrom" value="<?= $iDistanceRangeFrom; ?>" required="required" placeholder="<?= 'Distance From ('.$DEFAULT_DISTANCE_UNIT.')' ?>" <?= ($action == "Edit") ? 'disabled="disabled"' : '' ?>>
                                                </div>
                                                <div class="col-lg-1">
                                                    <i class="icon-minus"></i>
                                                </div>
                                                
                                                <div class="col-lg-3">
                                                    <input type="text" class="form-control distance-range-input" name="iDistanceRangeTo"  id="iDistanceRangeTo"  required="required" placeholder="<?= 'Distance To ('.$DEFAULT_DISTANCE_UNIT.')' ?>" <?php if($iDistanceRangeTo == '100000000'){ ?> value="&#8734" readonly<?php } else { ?> value="<?=
                                                    $iDistanceRangeTo; ?>" <?php } ?> <?= ($action == "Edit") ? 'disabled="disabled"' : '' ?>>
                                                </div>
                                            </div>
                                            <div class="distance-range" style="align-items: unset;">
                                                <div class="col-lg-3 dist-from-error">
                                                    
                                                </div>
                                                <div class="col-lg-1">
                                                    
                                                </div>
                                                <div class="col-lg-3" id="comapare_distance_section" <?= ($action == "Add" && empty($iDistanceRangeTo)) ? 'style="display: none"' : '' ?>>
                                                    <div class="dist-to-error"></div>
                                                    <?php if($action == "Add") { ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label style="width: max-content">
                                                                <input type="checkbox" name="set_to_infinity" id="set_to_infinity" value="1" <?= ($iDistanceRangeTo == '100000000') ? 'checked' : '' ?>>
                                                                
                                                                <span id="comapare_distance">Set "Distance To" more than <?= $iDistanceRangeFrom ?></span>
                                                                
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Vehicle Type<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control selectpicker"  name='iVehicleTypeId' id="iVehicleTypeId" required="required" data-live-search="true" <?= ($action == "Edit") ? 'disabled' : '' ?>>
                                            <option value="">Select Vehicle Type</option>
                                            <?php foreach ($db_select_data as $k => $val) { ?>
                                                    <option value="<?= $val['iVehicleTypeId']; ?>"
                                                    <?php if ($val['iVehicleTypeId'] == $iVehicleTypeId) { echo "selected"; } ?>><?= $val['vVehicleType_'.$_SESSION['sess_lang']] . ' (' . $val['vLocationName'] . ')' ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="iVehicleTypeId-error"></div>
                                    </div>
                                    <!-- <div class="clear"></div>
                                    <div class="col-lg-12 restrict_area">
                                        <div class="exist_area error"></div>
                                    </div> -->
                                </div>

                                <?php /*
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Charge For User(Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control delivery-charge-amount" name="fDeliveryChargeUser"  id="fDeliveryChargeUser" value="<?= $fDeliveryChargeUser; ?>" required="required">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Charge For Driver(Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control delivery-charge-amount" name="fDeliveryCharge"  id="fDeliveryCharge" value="<?= $fDeliveryCharge; ?>" required="required">
                                    </div>
                                </div>
                                */ ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Pay Per Order to Driver For Completed Orders (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control delivery-charge-amount" name="fDeliveryCharge"  id="fDeliveryCharge" value="<?= $fDeliveryCharge; ?>" required="required">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Pay Per Order to Driver For Cancelled Orders (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control delivery-charge-amount" name="fDeliveryChargeCancelled"  id="fDeliveryChargeCancelled" value="<?= ($action == "Add" && $fDeliveryChargeCancelled <= 0 ) ? '' : $fDeliveryChargeCancelled; ?>" required="required">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' && $userObj->hasPermission($create))) { ?>
                                            <input type="submit" class="save btn-info" name="submitbtn" id="submitbtn" value="<?php  if($action=='Add'){?><?= $action; ?> Delivery Charges<?php } else{ ?>Update<?php } ?>">
                                        <?php } ?>
                                        <a href="custom_delivery_charge_order.php?<?php echo $queryString; ?>" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <?php include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script src="js/bootstrap-select.js"></script>
        <script>

            $(document).ready(function () {
                $(window).keydown(function (event) {
                    if (event.keyCode == 13) {
                        event.preventDefault();
                        return false;
                    }
                });
            });

            $(".delivery-charge-amount").keydown(function (e) {
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

            $(".distance-range-input").keydown(function (e) {
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13]) !== -1 ||
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

            var successMSG1 = '<?php echo $success; ?>';

            if (successMSG1 != '') {
                setTimeout(function () {
                    $(".msgs_hide").hide(1000)
                }, 5000);
            }

            $(document).ready(function () {
                // jquery validation
                var validator = $('#deliveryChargeForm').validate({
                    rules: {
                        iDistanceRangeFrom: {
                            required: true,
                            number: true
                        },
                        iDistanceRangeTo: {
                            required: {
                               depends: function(element) {
                                    if ($('#set_to_infinity').prop('checked') == false) {
                                        return true;
                                    } else{
                                        return false;}
                                    }
                             },
                            //number: true
                        },
                        fDeliveryCharge: {
                            required: true,
                            number: true
                        }
                    },
                    messages: {
                        iDistanceRangeFrom: {
                            number: 'Please enter valid distance.'
                        },
                        iDistanceRangeTo: {
                            number: 'Please enter valid distance.'
                        },
                        fDeliveryCharge: {
                            number: 'Please enter valid amount.'
                        }
                    },
                    errorPlacement: function(error, element) {
                        if (element.attr("id") == "iDistanceRangeFrom" )
                            error.appendTo(".dist-from-error");
                        else if  (element.attr("id") == "iDistanceRangeTo" )
                            error.appendTo(".dist-to-error");
                        else if (element.attr("id") == "iVehicleTypeId")
                            error.appendTo(".iVehicleTypeId-error");
                        else
                            error.insertAfter(element);
                    }
                });

                $('select#iVehicleTypeId').on('change', function () {
                    validator.element($(this));
                });

            });

            $('#set_to_infinity').click(function() {
                if($(this).prop('checked') == true)
                {
                    $('#iDistanceRangeTo').val("∞");
                    $('#iDistanceRangeTo').prop('readonly', true);
                }
                else {
                    $('#iDistanceRangeTo').prop('readonly', false);
                }
            });

            $('#iDistanceRangeFrom').keyup(function() {
                if($.trim($(this).val()) == "")
                {
                    $('#comapare_distance_section').hide();
                }
                else {
                    $('#comapare_distance_section').show();
                    $('#comapare_distance').html('Set "Distance To" more than ' + $(this).val());   
                }
            });
        
        </script>
    </body>
    <!-- END BODY-->
</html>

