<?php
include_once('../common.php');
$script = 'Delivery Charges';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$queryString = '';
$view = 'view-delivery-charges';
$edit  = 'edit-delivery-charges';
$delete  = 'delete-delivery-charges';
$updateStatus  = 'update-status-delivery-charges';
$create  = 'create-delivery-charges';
if ($eType == 'runner') {
    $commonTxt = '-runner-delivery';
    $script = "RunnerDeliveryCharges";
    $queryString = 'eType=' . $eType;
} else if ($eType == 'genie') {
    $commonTxt = '-genie-delivery';
    $script = "GenieDeliveryCharges";
    $queryString = 'eType=' . $eType;
}
if(in_array($eType,['runner','genie'])){
    $view = $view.$commonTxt;
    $edit  = $edit.$commonTxt;
    $delete  =  $delete.$commonTxt;
    $updateStatus  =  $updateStatus.$commonTxt;
    $create  =  $create.$commonTxt;
}

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';


$tbl_name = 'delivery_charges';
$tbl_name1 = 'location_master';

if(isset($_POST['iDistanceRangeTo']) && $_POST['iDistanceRangeTo'] == mb_convert_encoding('&#x221E;', 'UTF-8', 'HTML-ENTITIES')){
     $_POST['iDistanceRangeTo'] = '100000000';
}

$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '';
$fOrderPriceValue = isset($_POST['fOrderPriceValue']) ? $_POST['fOrderPriceValue'] : '';
$fDeliveryChargeAbove = isset($_POST['fDeliveryChargeAbove']) ? $_POST['fDeliveryChargeAbove'] : '';
$fDeliveryChargeBelow = isset($_POST['fDeliveryChargeBelow']) ? $_POST['fDeliveryChargeBelow'] : '';
$fFreeOrderPriceSubtotal = isset($_POST['fFreeOrderPriceSubtotal']) ? $_POST['fFreeOrderPriceSubtotal'] : '';
$iFreeDeliveryRadius = isset($_POST['iFreeDeliveryRadius']) ? $_POST['iFreeDeliveryRadius'] : '';
$iDistanceRangeFrom = isset($_POST['iDistanceRangeFrom']) ? $_POST['iDistanceRangeFrom'] : '';
$iDistanceRangeTo = isset($_POST['iDistanceRangeTo']) ? $_POST['iDistanceRangeTo'] : '';
$set_to_infinity = isset($_POST['set_to_infinity']) ? $_POST['set_to_infinity'] : 0;
$fDeliveryChargeBuyAnyService = isset($_POST['fDeliveryChargeBuyAnyService']) ? $_POST['fDeliveryChargeBuyAnyService'] : 0;
$fDeliveryChargeBuyAnyServiceCancelledOrder = isset($_POST['fDeliveryChargeBuyAnyServiceCancelledOrder']) ? $_POST['fDeliveryChargeBuyAnyServiceCancelledOrder'] : 0;

if($set_to_infinity == 1)
{
    $iDistanceRangeTo = '100000000';
}

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

if (isset($_POST['submitbtn'])) {
    if ($action == "Add" && !$userObj->hasPermission($view)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Delivery Charges.';
        header("Location:delivery_charges.php?".$queryString);
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission($edit)) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Delivery Charges.';
        header("Location:delivery_charges.php?".$queryString);
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:delivery_charges_action.php?id=" . $id . '&success=2');
        exit;
    }

    $error = 0;
    if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder())
    {
        $sqlCheckData = $obj->MySQLSelect("SELECT * FROM delivery_charges WHERE eStatus = 'Active'");
        if(scount($sqlCheckData) > 0 && $action == "Add")
        {
            $getFromStartRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM delivery_charges WHERE iLocationId = $iLocationId AND iDistanceRangeFrom = 0");

            if(scount($getFromStartRange) > 0)
            {
                $getDistanceRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM delivery_charges WHERE iLocationId = $iLocationId");
                /*if($iDistanceRangeFrom != $getLastDistance[0]['iDistanceRangeTo'])
                {
                    $_SESSION['success'] = 3;
                    $_SESSION['var_msg'] = 'Distance Range From should be equals to '.$getLastDistance[0]['iDistanceRangeTo'];
                    $error = 1;
                }*/

                $inRange = 0;
                foreach ($getDistanceRange as $range) {

                    if(($iDistanceRangeFrom >= $range['iDistanceRangeFrom'] && $iDistanceRangeFrom < $range['iDistanceRangeTo']) || ($iDistanceRangeTo > $range['iDistanceRangeFrom'] && $iDistanceRangeFrom < $range['iDistanceRangeTo'])) {
                        $inRange = 1;
                    }
                }

                if($inRange == 1)
                {
                    $_SESSION['success'] = 3;
                    $_SESSION['var_msg'] = 'Distance Range From/To lies within already defined delivery charge for the selected location.';
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
                    $getDistanceRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM delivery_charges WHERE iLocationId = $iLocationId");

                    $inRange = 0;

                    foreach ($getDistanceRange as $range) {
                        if(($iDistanceRangeFrom > $range['iDistanceRangeFrom'] && $iDistanceRangeFrom < $range['iDistanceRangeTo']) || ($iDistanceRangeTo > $range['iDistanceRangeFrom'] && $iDistanceRangeTo <= $range['iDistanceRangeTo'])) {
                            $inRange = 1;
                            $_SESSION['var_msg'] = 'Distance Range From/To lies within already defined delivery charge for the selected location.';
                        }
                        elseif (($range['iDistanceRangeFrom'] >= $iDistanceRangeFrom && $range['iDistanceRangeFrom'] < $iDistanceRangeTo) || ($range['iDistanceRangeTo'] >= $iDistanceRangeFrom && $range['iDistanceRangeTo'] <= $iDistanceRangeTo)) {
                            $inRange = 1;
                            $_SESSION['var_msg'] = 'A Distance Range delivery charge already lies in your input range for the selected location.';
                        }
                    }

                    if($inRange == 1)
                    {
                        $_SESSION['success'] = 3;
                        $error = 1;
                    }
                }
            }
        }
        else if(scount($sqlCheckData) == 0 && $action == "Add") {
            if($iDistanceRangeFrom != 0)
            {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = 'Distance Range From should start from 0.';
                $error = 1;
            }
        } else {
            /*$getDistanceRange = $obj->MySQLSelect("SELECT iDistanceRangeFrom,iDistanceRangeTo FROM delivery_charges WHERE iLocationId = $iLocationId AND iDeliveyChargeId != $id");

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
                $_SESSION['var_msg'] = 'Distance Range From/To lies within already defined delivery charge for the selected location.';
                $error = 1;
            }*/
        }

        if($iDistanceRangeTo <= $iDistanceRangeFrom && $error == 0 && $action == "Add")
        {
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = 'Distance Range From should be less than or equals to Distance Range To';
            $error = 1;
        }
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
          `iLocationId` = '" . $iLocationId . "',
          `fOrderPriceValue` = '" . $fOrderPriceValue . "',              
          `fDeliveryChargeAbove` = '" . $fDeliveryChargeAbove . "',                         
          `fDeliveryChargeBelow` = '" . $fDeliveryChargeBelow . "',
          `fFreeOrderPriceSubtotal` = '" . $fFreeOrderPriceSubtotal . "',          
          `iFreeDeliveryRadius` = '" . $iFreeDeliveryRadius . "',
          `iDistanceRangeFrom` = '" . $iDistanceRangeFrom . "',
          `iDistanceRangeTo` = '" . $iDistanceRangeTo . "',
          `fDeliveryChargeBuyAnyService` = '" . $fDeliveryChargeBuyAnyService . "',
          `fDeliveryChargeBuyAnyServiceCancelledOrder` = '" . $fDeliveryChargeBuyAnyServiceCancelledOrder . "'" . $where;

        if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder())
        {
            if ($id != '') {
                $query = $q . " `" . $tbl_name . "` SET
                  `fOrderPriceValue` = '" . $fOrderPriceValue . "',              
                  `fDeliveryChargeAbove` = '" . $fDeliveryChargeAbove . "',                         
                  `fDeliveryChargeBelow` = '" . $fDeliveryChargeBelow . "',
                  `fFreeOrderPriceSubtotal` = '" . $fFreeOrderPriceSubtotal . "',          
                  `iFreeDeliveryRadius` = '" . $iFreeDeliveryRadius . "',
                  `fDeliveryChargeBuyAnyService` = '" . $fDeliveryChargeBuyAnyService . "',
                  `fDeliveryChargeBuyAnyServiceCancelledOrder` = '" . $fDeliveryChargeBuyAnyServiceCancelledOrder . "'" . $where;
            }
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

        header("Location:delivery_charges.php?".$queryString);
            exit;
    }
    
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iDeliveyChargeId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iLocationId = $value['iLocationId'];
            $fOrderPriceValue = $value['fOrderPriceValue'];
            $fDeliveryChargeAbove = $value['fDeliveryChargeAbove'];
            $fDeliveryChargeBelow = $value['fDeliveryChargeBelow'];
            $fFreeOrderPriceSubtotal = $value['fFreeOrderPriceSubtotal'];
            $iFreeDeliveryRadius = $value['iFreeDeliveryRadius'];
            $iDistanceRangeFrom = $value['iDistanceRangeFrom'];
            $iDistanceRangeTo = $value['iDistanceRangeTo'];
            $fDeliveryChargeBuyAnyService = $value['fDeliveryChargeBuyAnyService'];
            $fDeliveryChargeBuyAnyServiceCancelledOrder = $value['fDeliveryChargeBuyAnyServiceCancelledOrder'];
            
            //added by SP 27-06-2019 for remove validation to leave blank
            if($fFreeOrderPriceSubtotal==0) $fFreeOrderPriceSubtotal = '';
            if($iFreeDeliveryRadius==0) $iFreeDeliveryRadius = '';
        }
    }
}

$query = "SELECT vLocationName,iLocationId FROM " . $tbl_name1 . " WHERE eFor = 'UserDeliveryCharge' AND eStatus = 'Active'";

$db_location = $obj->MySQLSelect($query);
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | <?= ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) ? "User" : "" ?> Delivery Charges  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="css/bootstrap-select.css" rel="stylesheet" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
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
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action ?> <?= ($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) ? "User" : "" ?> Delivery Charges</h2>
                            <a class="back_link" href="delivery_charges.php?".<?php echo $queryString; ?>>
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <?php include('valid_msg.php'); ?>
                    <? if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <? } ?>
                    <div class="body-div">
                        <div class="form-group location-wise-box">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <? } ?>
                            <form method="post" action="" enctype="multipart/form-data" id="deliveryChargeForm">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="delivery_charges.php?".<?php echo $queryString; ?>/>

                                <div class="row">                   
                                    <div class="col-lg-12">
                                        <label>Select Location <span class="red"> *</span><i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="You can define the delivery charges for specific location, so this charges would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?> based on their location."></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="iLocationId" class="form-control" required="required" <?php if(!$MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?> onchange="checkdeliveryareaexist(this.value)" <?php } ?> <?php if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder() && $action == "Edit") { ?> disabled="disabled" <?php } ?>>
                                            <option value="">Select Location</option>
                                                      <option value="0" <?php if($iLocationId == '0') {echo "selected";} ?>>All Location</option>
                                            <?php foreach ($db_location as $key => $value) { ?>
                                                <option value="<?php echo $value['iLocationId'] ?>" <?php if ($value['iLocationId'] == $iLocationId) {   echo "selected"; }?>>
                                                    <?php echo $value['vLocationName'] ?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                    <?php if ($userObj->hasPermission('view-geo-fence-locations') && $action == 'Add') { ?>
                                        <div class="col-lg-6">
                                            <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location</a>
                                        </div>
                                    <?php } ?>
                                    <div class="clear"></div>
                                    <div class="col-lg-12 deliverycharge_area">
                                        <div class="exist_area error"></div>
                                    </div>
                                </div>
                                <?php if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?>
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
                                                    <input type="text" class="form-control distance-range-input" name="iDistanceRangeTo"  id="iDistanceRangeTo"  required="required" placeholder="<?= 'Distance To ('.$DEFAULT_DISTANCE_UNIT.')' ?>" <?php if($iDistanceRangeTo == '100000000'){ ?> value="&#8734" readonly<? } else { ?> value="<?=
                                                    $iDistanceRangeTo; ?>" <? } ?> <?= ($action == "Edit") ? 'disabled="disabled"' : '' ?>>
                                                </div>
                                            </div>
                                            <div class="distance-range" style="align-items: unset;">
                                                <div class="col-lg-3 dist-from-error">
                                                    
                                                </div>
                                                <div class="col-lg-1">
                                                    
                                                </div>
                                                <div class="col-lg-3" id="comapare_distance_section" <?= ($action == "Add" && $iDistanceRangeFrom == '') ? 'style="display: none"' : '' ?>>
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
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label> Order Total(Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='The value you define here will be treated as a base value based on which the delivery charges would be applicable. This price must be in USD.
For E.g.: If you define $5 as a value under "Order Total" field and if you wanted to apply the delivery charges if the value of order is greater or lesser than the value defined in order total.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fOrderPriceValue"  id="fOrderPriceValue" value="<?= $fOrderPriceValue; ?>" required="required" onkeypress="return isNegativeKey(event)">

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label> Delivery charge applicable on order amount greater than order total (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This charges would be applicable if the order value is greater than the order total
For E.g.: If you define $5 as a value under "Order Total" field and if the value of order is greater than $5, then delivery fees defined in this field would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeAbove"  id="fDeliveryChargeAbove" value="<?= $fDeliveryChargeAbove; ?>" required="required" onkeypress="return isNegativeKey(event)">

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label> Delivery charge applicable on order amount lesser than order total (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This charges would be applicable if the order value is lesser than the order total
For E.g.: If you define $5 as a value under "Order Total" field and if the value of order is lesser than $5, then delivery fees defined in this field would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?>.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeBelow"  id="fDeliveryChargeBelow" value="<?= $fDeliveryChargeBelow; ?>" required="required" onkeypress="return isNegativeKey(event)">

                                    </div>
                                </div> 
                                <div class="row">
                                    <div class="col-lg-12">
                                                    <label>  Free delivery for order amount above (Price In <?=$db_currency[0]['vName']?>) <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Ex. Free delivery on all orders above $50'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fFreeOrderPriceSubtotal"  id="fFreeOrderPriceSubtotal" value="<?= $fFreeOrderPriceSubtotal; ?>" onkeypress="return isNegativeKey(event)"><?php //added by SP 27-06-2019 for remove validation to leave blank ?>

                                    </div>
                                </div>                                                                    
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>  Free delivery radius <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='No delivery charges would be applicable if the delivery radius is within the specified range.
For E.g.: If you define 5km as a value under "Free delivery radius" field and if the delivery radius is within the 5km then in that <?php echo strtolower($langage_lbl_admin['LBL_RIDER']); ?> is eligible for the free delivery.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="iFreeDeliveryRadius" id="iFreeDeliveryRadius" value="<?= $iFreeDeliveryRadius; ?>" onkeypress="return isNegativeKey(event)"><?php //added by SP 27-06-2019 for remove validation to leave blank ?>
                                    </div>
                                </div>

                                <?php if($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Charges for Buy Any Service Feature for Completed Orders (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This charges would be applicable only for "Buy Any Service" feature.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeBuyAnyService" id="fDeliveryChargeBuyAnyService" value="<?= ($action == "Add" && $fDeliveryChargeBuyAnyService <= 0 ) ? '' : $fDeliveryChargeBuyAnyService; ?>" onkeypress="return isNegativeKey(event)"><?php //added by SP 27-06-2019 for remove validation to leave blank ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Delivery Charges for Buy Any Service Feature for Cancelled Orders (Price In <?=$db_currency[0]['vName']?>)<span class="red">*</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This charges would be applicable only for "Buy Any Service" feature.'></i></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeBuyAnyServiceCancelledOrder" id="fDeliveryChargeBuyAnyServiceCancelledOrder" value="<?= ($action == "Add" && $fDeliveryChargeBuyAnyServiceCancelledOrder <= 0 ) ? '' : $fDeliveryChargeBuyAnyServiceCancelledOrder; ?>" onkeypress="return isNegativeKey(event)"><?php //added by SP 27-06-2019 for remove validation to leave blank ?>
                                    </div>
                                </div>
                                <?php } ?>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' && $userObj->hasPermission($create))) { ?>
                                            <input type="submit" class="save btn-info" name="submitbtn" id="submitbtn" value="<?php  if($action=='Add'){?><?= $action; ?> Delivery Charges<?php } else{ ?>Update<?php } ?>">
<?php } ?>
                                        <a href="delivery_charges.php?".<?php echo $queryString; ?> class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- <div class="admin-notes">
                            <h4>Notes:</h4>
                            <ul>
                             <li>
                                The commission for Flat Fare is same which is set for the selected vehicle type here.
                              </li>
                            </ul>
                      </div> -->
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
<? include_once('footer.php'); ?>
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

            var successMSG1 = '<?php echo $success; ?>';

            if (successMSG1 != '') {
                setTimeout(function () {
                    $(".msgs_hide").hide(1000)
                }, 5000);
            }

            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                    //alert(referrer);
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "delivery_charges.php?<?php echo $queryString; ?>";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);

                // jquery validation
                $('#deliveryChargeForm').validate({
                    rules: {
                        iLocationId: {
                            required: true
                        },
                        fOrderPriceValue: {
                            required: true,
                            number: true,
                            min:0
                        },
                        fDeliveryChargeAbove: {
                            required: true,
                            number: true,
                            min:0
                        },
                        fDeliveryChargeBelow: {
                            required: true,
                            number: true,
                            min:0
                        },
                        iDistanceRangeFrom: {
                            required: true,
                            number: true,
                             min:0
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
                        fDeliveryChargeBuyAnyService: {
                            required: true,
                            number: true,
                             min:0
                        },
                        fDeliveryChargeBuyAnyServiceCancelledOrder: {
                            required: true,
                            number: true,
                             min:0
                        },
                        fFreeOrderPriceSubtotal: {
                            number: true,
                             min:0
                        },
                        iFreeDeliveryRadius: {
                            number: true,
                             min:0
                        }
                        //added by SP 27-06-2019 for remove validation to leave blank
//                        fFreeOrderPriceSubtotal: {
//                            required: true,
//                            number: true
//                        },
//                        iFreeDeliveryRadius: {
//                            required: true,
//                            number: true
//                        }
                    },
                    messages: {
                        iLocationId: {
                            required: 'Please Select From Location.'
                        },
                        fOrderPriceValue: {
                            required: 'Please Add Order Amount.'
                        },
                        iDistanceRangeFrom: {
                            number: 'Please enter valid distance.'
                        },
                        iDistanceRangeTo: {
                            number: 'Please enter valid distance.'
                        }
                    },
                    errorPlacement: function(error, element) {
                        if (element.attr("id") == "iDistanceRangeFrom" )
                            error.appendTo(".dist-from-error");
                        else if  (element.attr("id") == "iDistanceRangeTo" )
                            error.appendTo(".dist-to-error");
                        else
                            error.insertAfter(element);
                    }
                });

            });
        function checkdeliveryareaexist(iLocationId) {
            var deliverycharge_id = "";
            <?php if(!empty($id)) { ?>
            deliverycharge_id = <?php echo $id ?>;
            <?php } ?>

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_check_deliverycharge_area.php',
                'AJAX_DATA': 'iLocationId=' + iLocationId + '&deliverycharge_id='+ deliverycharge_id,
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    if(data > 0) {
                        $('.deliverycharge_area').css('padding-top','15px');
                        $( "div.exist_area" ).html("Please Check, This delivery charges Area Already Selected.");
                        $('input[type="submit"]').attr('disabled','disabled');
                    } else {
                        $('.deliverycharge_area').css('padding-top','0px');
                        $( "div.exist_area" ).html("");
                        $('input[type="submit"]').removeAttr('disabled');
                    } 
                }
                else {
                    console.log(response.result);
                }
            });
        }

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
        function isNegativeKey(evt){
            //var target = evt.target || evt.srcElement;
            var charCode = (evt.which) ? evt.which : event.keyCode;
            return !(charCode == 45);
        }
        </script>
    </body>
    <!-- END BODY-->
</html>

    