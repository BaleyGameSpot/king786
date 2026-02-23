<?php
include_once('../common.php');
$eSystem = "DeliverAll";

$script = 'StoreVehicleType';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
$queryString = '';

$view = "view-vehicle-type";
$create = "create-vehicle-type";
$edit = "edit-vehicle-type";
$updateStatus = "update-status-vehicle-type";
$delete = "delete-vehicle-type";

if ($eType == 'runner') {
    $commonTxt = '-runner-delivery';
    $script = "RunnerVehicleType";
    $queryString = 'eType=' . $eType;
} else if ($eType == 'genie') {
    $commonTxt = '-genie-delivery';
    $script = "GenieVehicleType";
    $queryString = 'eType=' . $eType;
} else {
    $commonTxt = '-deliverall';
    $queryString = '';
}


//if(in_array($eType,['runner','genie'])){
    $view = $view.$commonTxt;
    $edit  = $edit.$commonTxt;
    $updateStatus  = $updateStatus.$commonTxt;
    $delete  =  $delete.$commonTxt;
    $create  =  $create.$commonTxt;
//}

$sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);

$sqllocation = "SELECT * FROM location_master WHERE eStatus = 'Active' AND eFor = 'VehicleType' ORDER BY  vLocationName ASC ";
$db_location = $obj->MySQLSelect($sqllocation);

/* to fetch max iDisplayOrder from table for insert */
$select_order   = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM vehicle_type where eType ='".$eSystem."'");
$iDisplayOrder  = isset($select_order[0]['iDisplayOrder'])?$select_order[0]['iDisplayOrder']:0;
$iDisplayOrder_max  = $iDisplayOrder + 1; // Maximum order number

$tbl_name = 'vehicle_type';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$vVehicleType = isset($_POST['vVehicleType']) ? $_POST['vVehicleType'] : '';
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '-1';
$fDeliveryCharge = isset($_POST['fDeliveryCharge']) ? $_POST['fDeliveryCharge'] : '';
$fDeliveryChargeCancelOrder = isset($_POST['fDeliveryChargeCancelOrder']) ? $_POST['fDeliveryChargeCancelOrder'] : '';
$fRadius= isset($_POST['fRadius']) ? $_POST['fRadius'] : '';
$fCommision= isset($_POST['fCommision']) ? $_POST['fCommision'] : '';

$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
//  for ordering
$iDisplayOrder  = isset($_POST['iDisplayOrder'])?$_POST['iDisplayOrder']:$iDisplayOrder;
$temp_order     = isset($_POST['temp_order'])? $_POST['temp_order'] : "";

if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) {
    $fDeliveryCharge = $fDeliveryChargeCancelOrder = 0;
}

$vTitle_store = array();
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}

if (isset($_POST['btnsubmit'])) {

    if($action == "Add" && !$userObj->hasPermission($create)){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create '.strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:".$LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP']."?".$queryString);
        exit;
    }

    if($action == "Edit" && !$userObj->hasPermission($edit)){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update '.strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:".$LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP']."?".$queryString);
        exit;
    }


    if (SITE_TYPE == 'Demo') {
        header("Location:".$LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE_ACTION']."?id=" . $id . "&success=2");
        exit;
    }

    if($temp_order == "1" && $action == "Add"){
        $temp_order = $iDisplayOrder_max;
    }
    if($temp_order > $iDisplayOrder) { 
        for($i = $temp_order-1; $i >= $iDisplayOrder; $i--) { 
           $sql="UPDATE ".$tbl_name." SET iDisplayOrder = '".($i+1)."' WHERE iDisplayOrder = '".$i."' AND eType ='".$eSystem."'";
            $obj->sql_query($sql);
        }
    } else if($temp_order < $iDisplayOrder) {
        for($i = $temp_order+1; $i <= $iDisplayOrder; $i++) {
            $sql="UPDATE ".$tbl_name." SET iDisplayOrder = '".($i-1)."' WHERE iDisplayOrder = '".$i."' AND eType ='".$eSystem."'";
            $obj->sql_query($sql);
        }
    }

    $vVehicleType = $_POST['vVehicleType_'.$default_lang];

    $q = "INSERT INTO ";
    $where = '';
    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iVehicleTypeid` = '" . $id . "'";
    }
    $sql_str = '';
    if(scount($vTitle_store) > 0){
        for ($i = 0; $i < scount($vTitle_store); $i++) {
            $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
            $sql_str .= $vValue." = '".$_POST[$vTitle_store[$i]]."',";
        }
    }
    
    $query = $q . " `" . $tbl_name . "` SET
			`vVehicleType` = '" . $vVehicleType . "',
            `iLocationid` = '" . $iLocationId . "',
            `fDeliveryCharge` = '" . $fDeliveryCharge . "',
            `fDeliveryChargeCancelOrder` = '" . $fDeliveryChargeCancelOrder . "',
			`fRadius` = '" . $fRadius . "',
            `eType` = '" . $eSystem . "',
            `fCommision` = '" . $fCommision . "',
			" . $sql_str."
            `iDisplayOrder` = '".$iDisplayOrder."'"
            . $where;
    $obj->sql_query($query);
    $id = ($id != '') ? $id : $obj->GetInsertId();


    if ($action == "Add") {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        $_SESSION['success'] = "1";
        header("Location:".$LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP']."?".$queryString);
        exit;
    } else {
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
        header("Location:".$LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP']."?".$queryString);
        exit;
    }
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleTypeid = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vLabel = $id;
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {

            foreach ($db_data as $key => $value) {
                $vValue = 'vVehicleType_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $vVehicleType = $value['vVehicleType'];
                $fDeliveryCharge = $value['fDeliveryCharge'];
                $fDeliveryChargeCancelOrder = $value['fDeliveryChargeCancelOrder'];
                $fRadius = $value['fRadius'];
                $iLocationId = $value['iLocationid'];
                $iDisplayOrder_db = $value['iDisplayOrder'];
                $fCommision = $value['fCommision'];

                $userEditDataArr[$vValue] = $$vValue;
            }
        }
    }
}
$sql = "select vName,vSymbol from currency where eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8" />
    <title>Admin | <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <? include_once('global_files.php');   ?>
</head>
<!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2> <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> </h2>
                             <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                           </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) {?>
                            <div class="alert alert-success alert-dismissable msgs_hide">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div><br/>
                            <? } elseif ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable ">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                            <? } else if ($success == 3) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $_REQUEST['varmsg']; ?> 
                            </div><br/>	
                            <? } ?>
                            <? if(isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] !=Null ) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> Record  Not Updated .</div><br/>
                            <? } ?>                   
                            <form id="_store_vehicleType_form" name="_store_vehicleType_form" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="<?php echo $LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP']?>?<?php echo $queryString; ?>"/>
								<div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>
                                <div class="row" style="display: none;">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?><span class="red"> *</span> 
                                            <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Please add if your vehicle type is "Hatchback" , "Sedan" , "SUV" , "Van" , Luxurious Car" etc'></i>
                                        </label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="vVehicleType"  id="vVehicleType"  value="<?= $vVehicleType; ?>">
                                    </div>
                                </div>

                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" required id="vVehicleType_Default" name="vVehicleType_Default" value="<?= isset($userEditDataArr['vVehicleType_'.$default_lang]) ? $userEditDataArr['vVehicleType_'.$default_lang] : '' ; ?>" data-originalvalue="<?= isset($userEditDataArr['vVehicleType_'.$default_lang]) ? $userEditDataArr['vVehicleType_'.$default_lang] : ''; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editVehicleType('Add')" <?php } ?> >
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editVehicleType('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="vehicle_type_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> <?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vVehicleType_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vVehicleType_' . $vCode;
                                                        $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '' ;

                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>  
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if($EN_available) {
                                                                if($vCode == "EN") { 
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            } else { 
                                                                if($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vVehicleType_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vVehicleType_', '<?= $default_lang ?>');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                }
                                                            }
                                                            ?>
                                                        </div> 
                                                    <?php 
                                                    }
                                                ?>
                                            </div>
                                            <div class="modal-footer" style="margin-top: 0">
                                                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveVehicleType()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vVehicleType_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']; ?> <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vVehicleType_<?= $default_lang ?>" name="vVehicleType_<?= $default_lang ?>" value="<?= $userEditDataArr['vVehicleType_'.$default_lang]; ?>" required>
                                    </div>
                                </div>
                                <?php } ?>
                                
                             <div class="row">
                                     <div class="col-lg-12">
                                          <label>Select Location <span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Select the location in which you would like to appear this vehicle type. For example "Luxurious" vehicle type to appear for any specific city or state or may be for whole country. You can define these locations from "Manage Locations >> Geo Fence Location" section'></i></label>
                                     </div>
                                     <div class="col-md-6 col-sm-6">

                                        <select class="form-control" name = 'iLocationId' id="iLocationId" onchange="changeCode_distance(this.value);">
                                            <option value="">Select Location</option>
                                            <option value="-1" <?if($iLocationId== "-1"){?> selected <? } ?>>All</option>
                                            <?php 
                                            foreach ($db_location as $i => $row) {
                                                if(scount($userObj->locations) > 0 && !in_array($row['iLocationId'], $userObj->locations)){
                                                    continue;
                                                }
                                                ?>
                                                <option value = "<?= $row['iLocationId'] ?>" <?if($iLocationId == $row['iLocationId']){?>selected<? } ?>><?= $row['vLocationName'] ?></option>
                                            <?php } ?>
                                        </select>
                                     </div>

                                    <?php if($userObj->hasPermission('create-geo-fence-locations')){ ?>
                                         <div class="col-md-6 col-sm-6">
                                           <a class="btn btn-primary" href="location.php" target="_blank">Enter New Location</a>
                                        </div>
                                    <?php } ?>
                                </div>

                                <?php if(!$MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) { ?>
                                <div class="row" id="hide-km">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_DELIVERY_CHARGES_PER_ORDER_FOR_COMPLETED_ORDERS']; ?> (In <?=$db_currency[0]['vName']?>)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the delivery charge for completed orders, as per type and location. E.q. $10 if Delivery is done by a car for location California."'></i></label>
                                    </div>
                                    
                                     <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="fDeliveryCharge"  id="fDeliveryCharge" value="<?= $fDeliveryCharge; ?>" >
                                    </div>

                                </div>

                                <div class="row" id="hide-km">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_DELIVERY_CHARGES_PER_ORDER_FOR_CANCELLED_ORDERS']; ?> (In <?=$db_currency[0]['vName']?>)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='"Set the minimum delivery charge for canceled orders, as per type and location. E.q. $5 if a <?php echo strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> was on-route by a car for location California."'></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="fDeliveryChargeCancelOrder"  id="fDeliveryChargeCancelOrder" value="<?= $fDeliveryChargeCancelOrder; ?>" >
                                    </div>

                                </div>
                                <?php } ?>
                                <div class="row" id="hide-price">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_DELIVERY_RADIUS']; ?><span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Driver will get the order request for this vehicle type for the specified range. E.g. if the type is Cycle then get request within 2 KM'></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="fRadius"  id="fRadius" value="<?= $fRadius; ?>"  required>
                                    </div>
                                </div>
                                <?php if($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label> Commission (%)<span class="red"> *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='This would be the amount which you are willing to charge from the <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> in form of commission for each order. This is applicable for Delivery Genie & Runner only.'></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="fCommision"  id="fCommision" value="<?= $fCommision; ?>" required>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Display Order</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        
                                        <input type="hidden" name="temp_order" id="temp_order" value="<?=($action == 'Edit') ? $iDisplayOrder_db : '1';?>">
                                        <?
                                            $display_numbers = ($action=="Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                        ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <? for($i=1; $i <= $display_numbers; $i++){ ?>
                                                <option value="<?=$i?>" <?if($i == $iDisplayOrder_db){echo "selected";}?>> -- <?=$i?> --</option>
                                            <? } ?>
                                        </select>
                                        
                                    </div>
                                </div>
            					<div class="col-lg-12">
                                    <?php if(($action == 'Edit' && $userObj->hasPermission($edit)) || ($action == 'Add' &&  $userObj->hasPermission($create))){ ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Vehicle Type" >
                                        <input type="reset" value="Reset" class="btn btn-default">
                                    <?php } ?>
                                    <a href="<?php echo $LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE.PHP']?>?<?php echo $queryString; ?>" class="btn btn-default back_link">Cancel</a>
                                </div>          
                            </form>
                        </div>      
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>
<? include_once('footer.php');  ?>
<script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
<script type="text/javascript" src="js/form-validation.js" ></script>

<script>
	$('[data-toggle="tooltip"]').tooltip();
	var successMSG1 = '<?php echo $success; ?>';
	if (successMSG1 != '') {
		setTimeout(function () {
			$(".msgs_hide").hide(1000)
		}, 5000);
	}
</script>
<!--For Faretype End--> 
<script>
	function changeCode_distance(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_unit.php',
            'AJAX_DATA': {id: id},
        };
        getDataFromAjaxCall(ajaxData, function(response) {
            if(response.action == "1") {
                var dataHTML2 = response.result;
                if(dataHTML2 != null)
                    $("#change_eUnit").text(dataHTML2);
            }
            else {
                console.log(response.result);
            }
        });
	}
	changeCode_distance('<?=$iLocationId?>');
</script>
<script type="text/javascript" language="javascript">
$(document).ready(function() {
    var referrer;
    if($("#previousLink").val() == "" ){
        referrer =  document.referrer;
    }else { 
        referrer = $("#previousLink").val();
    }
    if(referrer == "") {
        referrer = "<?php echo $LOCATION_FILE_ARRAY['STORE_VEHICLE_TYPE_ACTION'];?>?<?php echo $queryString; ?>";
    }else {
        $("#backlink").val(referrer);
    }
    $(".back_link").attr('href',referrer);
});

function editVehicleType(action)
{
    $('#modal_action').html(action);
    $('#vehicle_type_Modal').modal('show');
}

function saveVehicleType()
{
    //console.log($('#vVehicleType_<?= $default_lang ?>').val().trim());
    if($('#vVehicleType_<?= $default_lang ?>').val().trim() == "") {
        $('#vVehicleType_<?= $default_lang ?>_error').show();
        $('#vVehicleType_<?= $default_lang ?>').focus();
        clearInterval(langVar);
        langVar = setTimeout(function() {
            $('#vVehicleType_<?= $default_lang ?>_error').hide();
        }, 5000);
        return false;
    }

    $('#vVehicleType_Default').val($('#vVehicleType_<?= $default_lang ?>').val());
    $('#vVehicleType_Default').closest('.row').removeClass('has-error');
    $('#vVehicleType_Default-error').remove();
    $('#vehicle_type_Modal').modal('hide');
}
$(document).ready(function () {
    $('#_store_vehicleType_form').validate({
        rules: {
            fRadius: {
                required: true,
                number: true
            },
            fCommision :{
                required: true,
                number: true
            }
        },
    });
});
</script>
</body>
<!-- END BODY-->
</html>
