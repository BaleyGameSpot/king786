<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$script = "Coupon";

$flyEnable = "No";
if($MODULES_OBJ->isAirFlightModuleAvailable()) {
    $flyEnable = "Yes"; 
}

$id = isset($_GET['id']) ? $_GET['id'] : '';
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$db_currency = $obj->MySQLSelect("SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'");
$defaultCurrency = "USD";
if (scount($db_currency) > 0) {
    $defaultCurrency = $db_currency[0]['vName'];
}
$iCouponId = isset($_REQUEST['iCouponId']) ? $_REQUEST['iCouponId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$error = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$action = ($iCouponId != '') ? 'Edit' : 'Add';
$tbl_name = 'coupon';
// set all variables with either post (when submit) either blank (when insert)
$iCouponId = isset($_REQUEST['iCouponId']) ? $_REQUEST['iCouponId'] : '';
$existsCoupon = isset($_REQUEST['existscoupon']) ? $_REQUEST['existscoupon'] : '';
$vCouponCode = isset($_REQUEST['vCouponCode']) ? $_REQUEST['vCouponCode'] : '';
$fDiscount = isset($_REQUEST['fDiscount']) ? $_REQUEST['fDiscount'] : '';
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
$eValidityType = isset($_REQUEST['eValidityType']) ? $_REQUEST['eValidityType'] : '';
$dActiveDate = isset($_REQUEST['dActiveDate']) ? $_REQUEST['dActiveDate'] : '';
$dExpiryDate = isset($_REQUEST['dExpiryDate']) ? $_REQUEST['dExpiryDate'] : '';
$iUsageLimit = isset($_REQUEST['iUsageLimit']) ? $_REQUEST['iUsageLimit'] : '';
$iUsed = isset($_REQUEST['iUsed']) ? $_REQUEST['iUsed'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : 'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eSystemType = isset($_REQUEST['eSystemType']) ? $_REQUEST['eSystemType'] : 'General';
$couponsystem = isset($_REQUEST['couponsystem']) ? $_REQUEST['couponsystem'] : 'General';
$vPromocodeType = isset($_REQUEST['vPromocodeType']) ? $_REQUEST['vPromocodeType'] : 'Public';
$eStoreType = isset($_POST['eStoreType']) ? $_POST['eStoreType'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '0';
$iServiceIdNew = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '0';
$eFreeDelivery_check = isset($_POST['eFreeDelivery']) ? $_POST['eFreeDelivery'] : 'off';
$eFreeDelivery = ($eFreeDelivery_check == 'on') ? 'Yes' : 'No';
$iLocationId = isset($_POST['iLocationId']) ? $_POST['iLocationId'] : '0';

if(!empty($eStoreType)) {
    $eSystemType = "DeliverAll";
    if($eStoreType == "All") {
        $iCompanyId = $iServiceIdNew = 0;
    }
}
if($eFreeDelivery == "Yes") {
    $fDiscount = 0;
}
$eFly = 0;
if($eSystemType=='Fly' || $couponsystem == 'Fly') {
    $eFly = 1;
    $eSystemType = 'Ride';
}
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service Start
$serviceIds = getCurrentActiveServiceCategoriesIds();
$optionName = "DeliverAll";
if ($serviceIds == 1) {
    $optionName = "Food";
}
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service End
if (isset($_POST['submit'])) {
    //echo "<pre>";print_r($_POST);die;
    if ($action == "Add" && !$userObj->hasPermission('create-promocode')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Promo Code.';
        header("Location:coupon.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-promocode')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Promo Code.';
        header("Location:coupon.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:coupon_action.php?iCouponId=" . $iCouponId . '&success=2');
        exit;
    }
    
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['vCouponCode'], 'req', 'Coupon Code is required');
    $validobj->add_fields($_POST['tDescription_' . $default_lang], 'req', 'Description is required');
    if($eFreeDelivery == "No") {
        $validobj->add_fields($_POST['fDiscount'], 'req', 'Discount is required');
    }
    if ($_POST['eValidityType'] == "Defined") {
        $validobj->add_fields($_POST['dActiveDate'], 'req', 'Activation Date is required');
        $validobj->add_fields($_POST['dExpiryDate'], 'req', 'Expiry Date is required');
    }
    $validobj->add_fields($_POST['iUsageLimit'], 'req', 'Usage Limit is required');
    $validobj->add_fields($_POST['eStatus'], 'req', 'Status is required');
    $validobj->add_fields($_POST['vPromocodeType'], 'req', 'Promocode Type is required');
    $error = $validobj->validate();

    if ($error) {
        $success = 3;
        $newError = $error;
    } else {
        //Added By HJ On 06-03-2019 For Check Coupon Code with It's System Type As Per Discuss With KS Sir Start
        if ($action == 'Add') {
            $couponsystem = $eSystemType;
        }
        $whereSystemType = " AND (eSystemType='" . $couponsystem . "' OR eSystemType='General')";
        if ($couponsystem == "General") {
            $whereSystemType = " AND (eSystemType='General' OR eSystemType!='General')";
        }
        if ($action == 'Edit') {
            $whereSystemType .= " AND iCouponId != '" . $iCouponId . "'";
        }
        //echo $whereSystemType;die;
        $checkPromocode = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE vCouponCode='" . $vCouponCode . "' AND eStatus != 'Deleted'" . $whereSystemType);
        //echo "<pre>";print_R($checkPromocode);die;
        //Added By HJ On 06-03-2019 For Check Coupon Code with It's System Type As Per Discuss With KS Sir End
        if (scount($checkPromocode) > 0) {
            $existsType = $checkPromocode[0]['eSystemType'];
            //$_SESSION['success'] = '3';
            //$_SESSION['var_msg'] = 'Promo Code already exists.';
            header("Location:coupon_action.php?success=3&var_msg=Promo code already exists in <b>" . $existsType . "</b> system.&existscoupon=" . $vCouponCode);
            exit;
        } else {
            $descArr = array();
            for ($b = 0; $b < scount($db_master); $b++) {
                $tDescription = "";
                if (isset($_POST['tDescription_' . $db_master[$b]['vCode']])) {
                    $tDescription = htmlspecialchars($_POST['tDescription_' . $db_master[$b]['vCode']],ENT_IGNORE);
                }
                $descArr["tDescription_" . $db_master[$b]['vCode']] = $tDescription;
            }
            
            $jsonDesc =  getJsonFromAnArr($descArr);
           
            $q = "INSERT INTO ";
            $where = '';
            if ($action == 'Edit') {
                $str = "";
            } else {
                $str = " , eSystemType = '" . $eSystemType . "' ";
            }
            if ($eValidityType == 'Permanent') {
                $dActiveDate = $dExpiryDate = '';
            }

            $Data_Coupon = array();
            $Data_Coupon['vCouponCode'] = $vCouponCode;
            $Data_Coupon['fDiscount'] = $fDiscount;
            $Data_Coupon['eType'] = $eType;
            $Data_Coupon['eValidityType'] = $eValidityType;
            $Data_Coupon['dActiveDate'] = $dActiveDate;
            $Data_Coupon['dExpiryDate'] = $dExpiryDate;
            $Data_Coupon['iUsageLimit'] = $iUsageLimit;
            $Data_Coupon['tDescription'] = $jsonDesc;
            $Data_Coupon['eFly'] = $eFly;
            $Data_Coupon['vPromocodeType'] = $vPromocodeType;
            $Data_Coupon['eStoreType'] = $eStoreType;
            $Data_Coupon['iCompanyId'] = $iCompanyId;
            $Data_Coupon['iServiceId'] = $iServiceId;
            $Data_Coupon['eFreeDelivery'] = $eFreeDelivery;
            $Data_Coupon['iLocationId'] = $iLocationId;
            $Data_Coupon['eStatus'] = $eStatus;
            $Data_Coupon['eSystemType'] = $eSystemType;

            if ($iCouponId != '') {
                $where = " iCouponId = '" . $iCouponId . "'";
                $obj->MySQLQueryPerform($tbl_name, $Data_Coupon, "update", $where);
            }
            else {
                $obj->MySQLQueryPerform($tbl_name, $Data_Coupon, "insert");
            }
            

            if ($action == "Add") {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            } else {
                $_SESSION['success'] = '1';
                $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            }
        }
        header("Location:" . $backlink);
        exit;
    }
}
// for Edit
$eSystemType = "General";
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCouponId = '" . $iCouponId . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCouponCode = $value['vCouponCode'];
            $fDiscount = $value['fDiscount'];
            $eType = $value['eType'];
            $eValidityType = $value['eValidityType'];
            $dActiveDate = $value['dActiveDate'];
            $dExpiryDate = $value['dExpiryDate'];
            $iUsageLimit = $value['iUsageLimit'];
            $iUsed = $value['iUsed'];
            $eStatus = $value['eStatus'];
            $eSystemType = $value['eSystemType'];
            $eFly = $value['eFly'];
            $tDescription = json_decode($value['tDescription'], true);
            /*foreach ($tDescription as $key4 => $value4) {
                $userEditDataArr[$key4] = $value4;
            }*/
            $vPromocodeType = $value['vPromocodeType'];
            $eStoreType = $value['eStoreType'];
            $iCompanyId = $value['iCompanyId'];
            $iServiceIdNew = $value['iServiceId'];
            $eFreeDelivery = $value['eFreeDelivery'];
            $iLocationId = $value['iLocationId'];

            if($iCompanyId > 1) {
                $db_company = $obj->MySQLSelect("SELECT vCompany,vEmail FROM `company` WHERE iCompanyId = '$iCompanyId'");
                $db_company = clearName($db_company[0]['vCompany'])." - ( ".clearEmail($db_company[0]['vEmail'])." )";    
            }
            
        }
    }
}
//$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
$rideEnable = $MODULES_OBJ->isRideFeatureAvailable() ? "Yes" : "No";
$deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable() ? "Yes" : "No";
$deliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable() ? "Yes" : "No";
$onlyDeliverallModule = strtoupper(ONLYDELIVERALL);
if(isset($cubeDeliverallOnly) && $cubeDeliverallOnly > 0){
    $onlyDeliverallModule = "YES";
}
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$catdata = serviceCategories;
if(!empty($catdata)){
$allservice_cat_data = json_decode($catdata, true);
foreach ($allservice_cat_data as $k => $val) {
    $iServiceIdArr[] = $val['iServiceId'];
}
    if(!empty($iServiceIdArr)){
$serviceIds = implode(",", $iServiceIdArr);
$service_category = "SELECT iServiceId,vServiceName_" . $default_lang . " as servicename,eStatus FROM service_categories WHERE iServiceId IN (" . $serviceIds . ") AND eStatus = 'Active'";
$service_cat_list = $obj->MySQLSelect($service_category);
    }
}

if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
    $ssql = " AND iServiceId IN(".$enablesevicescategory.")";
    $enablesevicescategory = str_replace(",", "|", $enablesevicescategory);
    $ssql .= " OR iServiceIdMulti REGEXP '(^|,)(" . $enablesevicescategory . ")(,|$)') ";
}
else {
    $ssql = " AND iServiceId IN(".$enablesevicescategory.")";
}

$db_location = $obj->MySQLSelect("SELECT vLocationName,iLocationId FROM location_master WHERE eFor = 'PromoCode' AND eStatus = 'Active'");

if(scount($iServiceIdArr) == 1) {
    $iServiceIdNew = $iServiceIdArr[0];
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | PromoCode <?= $action; ?> </title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />

        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <style type="text/css">
            .input-label {
                font-weight: normal;
                cursor: pointer;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>
                                <?= $action; ?>
                                Promo Code
                            </h2>
                            <a href="coupon.php">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a> </div>
                    </div>
                    <hr />
                    <? if ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php print_r($error); ?>
                        </div>
                        <br/>
                    <? } ?>
                    <? if ($success == 2) {?>
                         <div class="alert alert-danger alert-dismissable">
                              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                              <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <? } ?>
                    <div class="body-div coupon-action-part">
                        <div class="form-group"> 
                            <span style="color:red; font-size:small;" id="coupon_status"></span>

                            <form name="_coupon_form" id="_coupon_form" method="post" action="" enctype="multipart/form-data" class="">
                                <input type="hidden" name="iCouponId" value="<?php
                                if (isset($db_data[0]['iCouponId'])) {
                                    echo $db_data[0]['iCouponId'];
                                }
                                ?>">
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="coupon.php"/>
                                <input type="hidden" name="vCouponCodeval" id="vCouponCodeval" value="<?= $vCouponCode; ?>"/>

                                <div class="row coupon-action-n1">
                                    <div class="col-lg-12">
                                        <label>Coupon Code :<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" name="vCouponCode" <?php
                                        if ($action == 'Edit') {
                                            echo "readonly";
                                        } else {
                                            ?>  <? } ?> id="vCouponCode" value="<?= $vCouponCode; ?>" placeholder="Coupon Code">
                                               <?php
                                               if ($action == 'Edit') {
                                                   
                                               } else {
                                                   ?>
                                            <a style="margin: 0 !important;" class="btn btn-sm btn-info" onClick="randomStringToInput(this)">Generate Coupon Code</a>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-5">
                                        <textarea rows="3" class="form-control <?= ($iCouponId == "") ?  'readonly-custom' : '' ?>" id="tDescription_Default" name="tDescription_Default" data-originalvalue="<?= isset($tDescription['tDescription_'.$default_lang]) ? $tDescription['tDescription_'.$default_lang] : ''; ?>" readonly="readonly" required <?php if($iCouponId == "") { ?> onclick="editDescription('Add')" <?php } ?>><?= isset($tDescription['tDescription_'.$default_lang]) ? $tDescription['tDescription_'.$default_lang] : ''; ?></textarea>
                                        <div class="text-danger" id="tDescription_Default_error" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                    </div>
                                    <?php if($iCouponId != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescription('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="coupon_desc_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Description
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tDescription_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($d = 0; $d < $count_all; $d++) 
                                                    {
                                                        $vCode = $db_master[$d]['vCode'];
                                                        $vTitle = $db_master[$d]['vTitle'];
                                                        $eDefault = $db_master[$d]['eDefault'];
                                                        $descVal = 'tDescription_' . $vCode;
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
                                                                <label>Description (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <textarea name="<?= $descVal; ?>" rows="3" class="form-control" id="<?= $descVal; ?>" placeholder="<?= $vTitle; ?> Value" data-originalvalue="<?= isset($tDescription[$descVal]) ? $tDescription[$descVal] : ''; ?>"><?= isset($tDescription[$descVal]) ? $tDescription[$descVal] : ''; ?></textarea>
                                                                <div class="text-danger" id="<?= $descVal.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tDescription_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveDescription()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tDescription_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>

                                </div>
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Description <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <textarea rows="3" class="form-control" id="tDescription_<?= $default_lang ?>" name="tDescription_<?= $default_lang ?>" required><?= $tDescription['tDescription_'.$default_lang]; ?></textarea>
                                    </div>
                                </div>
                                <?php } ?>
                                
                                <div id="discount_details">
                                    <div class="row coupon-action-n2">
                                        <div class="col-lg-12">
                                            <label>Discount :<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" onkeypress="return isNumberKey(event)" class="form-control" name="fDiscount" id="fDiscount" value="<?= $fDiscount; ?>" placeholder="Discount">
                                            <select id="eType" name="eType" class="form-control">
                                                <option value="percentage" <?php if (isset($db_data[0]['eType']) && $db_data[0]['eType'] == "percentage") { ?> selected <?php } ?> >%</option>
                                                <option value="cash" <?php if (isset($db_data[0]['eType']) && $db_data[0]['eType'] == "cash") { ?>selected <?php } ?> >Flat Amount (In <?= $defaultCurrency; ?>) </option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Validity :<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="radio" name="eValidityType" id="eValidityType_Permanent" onClick="showhidedate(this.value)" value="Permanent"
                                            <?php if (isset($db_data[0]['eValidityType']) && $db_data[0]['eValidityType'] == "Permanent") { ?> checked <?php } ?> >
                                            <label class="input-label" for="eValidityType_Permanent"> Permanent</label>
                                            <input class="coup-act1" type="radio" name="eValidityType" id="eValidityType_Custom" onClick="showhidedate(this.value)" value="Defined" <?php if (isset($db_data[0]['eValidityType']) && $db_data[0]['eValidityType'] == "Defined") { ?> checked <?php } ?> >
                                            <label class="input-label" for="eValidityType_Custom"> Custom</label>
                                        </div>
                                    </div>

                                    <div class="row" id="date1" style="display:none;">
                                        <div class="col-lg-12" >
                                            <label>Activation Date :<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" style="float: left;margin-right: 10px; width:45%; cursor: pointer;background: #fff;" class="form-control" name="dActiveDate"  id="dActiveDate" value="<?= $dActiveDate ?>" placeholder="Activation Date" readonly>
                                        </div>
                                    </div>
                                    <div class="row" id="date2" style="display:none;">  
                                        <div class="col-lg-12">
                                            <label>Expiry Date:<span class="red"> *</span></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" style="float: left;margin-right: 10px; width:45%;cursor: pointer;background: #fff;" class="form-control" name="dExpiryDate" value="<?= $dExpiryDate ?>"  id="dExpiryDate" placeholder="Expiry Date" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl_admin['LBL_PROMOCODE_TYPE']; ?> <span class="red">*</span> <i data-html="true" class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title=" Public : If the Admin User selects PromoCode Type as “Public�? then all the User in entire system would be able to see the respective PromoCode in the apps while trying to apply the PromoCode.<br/> Private : If the Admin User selects PromoCode Type as “Private�? the respective PromoCode would not be visible to the in the apps while trying to apply the PromoCode. However, if the admin shares the private PromoCode with any of the user by any mode that promocode would be applied if it’s a valid. "></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6" >
                                        <input type="radio"  name="vPromocodeType"  id="vPromocodeType_Public"  value="Public"  <?php if ($vPromocodeType == "Public") echo 'checked="checked"'; ?> > <label class="input-label" for="vPromocodeType_Public"> Public </label>
                                        <input class="coup-act1" type="radio"  name="vPromocodeType"   id="vPromocodeType_Private" value="Private"  <?php if ($vPromocodeType == "Private") echo 'checked="checked"'; ?> > <label class="input-label" for="vPromocodeType_Private"> Private</label>
                                    </div>
                                </div>

                                <div class="row coupon-action-n3">
                                    <div class="col-lg-12">
                                        <label>Usage Limit <span class="red" > *</span> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Promo code can be used one time only for each user. So if you set Usage limit to 100 then 100 unique user can use this promo code."></i></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="number" id="iUsageLimit" value="<?= $iUsageLimit ?>"  name="iUsageLimit"  placeholder="Usage Limit" class="form-control" onKeyup="checkuserlimit(this.value);" min="0" oninput="validity.valid||(value='')" />
                                        <div id="iUsageLimitmsg"></div>
                                    </div>             

                                </div>

                                <? if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && $onlyDeliverallModule == "NO") { ?>
                                    <div class="row coupon-action-n3">
                                        <div class="col-lg-12">
                                            <label>System Type<span class="red"> *</span></label>
                                        </div>
                                        <?php if($eFly=='1') { ?>
                                            <input type="hidden" name="couponsystem" value="Fly">
                                        <?php } else { ?>
                                            <input type="hidden" name="couponsystem" value="<?= $eSystemType; ?>">
                                        <?php } ?>
                                        <div class="col-md-6 col-sm-6">
                                            <select <? if ($action == 'Edit') { ?> disabled=""<? } ?> id="eSystemType" name="eSystemType" class="form-control ">
                                                <option value="General" <?php if ($eSystemType == "General") { ?>selected <?php } ?> >General</option>
                                                <? if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                                                    if($rideEnable == "Yes") { ?>
                                                    <option value="Ride" <?php if ($eSystemType == "Ride" && $eFly=='0') { ?>selected <?php } ?> >Ride</option>
                                                    <? } if($deliveryEnable == "Yes") { ?>
                                                    <option value="Delivery" <?php if ($eSystemType == "Delivery") { ?>selected <?php } ?> >Delivery</option>
                                                <? } } ?>
                                                <? if ($APP_TYPE == 'Ride-Delivery-UberX' && $ufxEnable == "Yes") { ?>
                                                    <option value="UberX" <?php if ($eSystemType == "UberX") { ?>selected <?php } ?> >UberX</option>
                                                <? } ?>
                                                <? if (DELIVERALL == "Yes" && $deliverallEnable == "Yes") { ?>
                                                    <option value="DeliverAll" <?php if ($eSystemType == "DeliverAll") { ?>selected <?php } ?> >DeliverAll</option>
                                                <? } ?>
                                                    <? if ($flyEnable == "Yes") { ?>
                                                    <option value="Fly" <?php if ($eSystemType == "Ride" && $eFly=='1') { ?>selected <?php } ?> >Fly</option>
                                                <? } ?>
                                            </select>
                                        </div>
                                    </div>
                                <? } else if ($onlyDeliverallModule == "YES") { ?>
                                    <input type="hidden" name="eSystemType" class="form-control" id="eSystemType" value="DeliverAll">
                                <? } else { ?>
                                    <input type="hidden" name="eSystemType" class="form-control" id="eSystemType" value="<?= $APP_TYPE; ?>">
                                <? } ?>

                                <?php if($MODULES_OBJ->isEnableFreeDeliveryOrStoreSpecificPromoCode()) { ?>
                                    <?php if($action == "Add") { ?>
                                    <div class="row" id="StoreTypeOption">
                                        <div class="col-lg-12">
                                            <label>Promocode <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Type :</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="radio" name="eStoreType" id="eStoreType_All" value="All" <?php if ($db_data[0]['eStoreType'] == "All") { ?> checked <?php } ?> >
                                            <label class="input-label" for="eStoreType_All"> All </label>
                                            <input class="coup-act1" type="radio" name="eStoreType" id="eStoreType_Specific" value="StoreSpecific" <?php if ($db_data[0]['eStoreType'] == "StoreSpecific") { ?> checked <?php } ?> >
                                            <label class="input-label" for="eStoreType_Specific"> Specific Store </label>
                                        </div>
                                    </div>

                                    <div id="store_selection" <?php if($action == "Add") { ?> style="display: none;" <?php } ?>>
                                        <?php if(scount($service_cat_list) > 1) { ?>
                                        <div class="row coupon-action-n3">
                                            <div class="col-lg-12">
                                                <label>Service Type</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <select id="iServiceId" name="iServiceId" class="form-control" onchange="changeserviceCategory(this.value)">
                                                    <option value="">Select</option>
                                                    <?php for ($i = 0; $i < scount($service_cat_list); $i++) { ?>
                                                    <option value = "<?= $service_cat_list[$i]['iServiceId'] ?>" <?php if ($iServiceIdNew == $service_cat_list[$i]['iServiceId']) { ?>selected<?php } ?>><?= $service_cat_list[$i]['servicename'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php } else { ?>
                                            <input type="hidden" name="iServiceId" id="iServiceId" value="<?= $service_cat_list[0]['iServiceId'] ?>">
                                        <?php } ?>

                                        <div class="row coupon-action-n3">
                                            <div class="col-lg-12">
                                                <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <select name="iCompanyId" class="form-control" id="iCompanyId">
                                                    <option value="" >Select <?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" <?php if($action == "Add") { ?> style="display: none;" <?php } ?> id="free_delivery">
                                        <div class="col-lg-12">
                                            <label>Free Delivery Promo Code  <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Users won't be charged for delivery if they have selected this promo code."></i></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" name="eFreeDelivery" <?= ($iCouponId != '' && $eFreeDelivery == 'Yes') ? 'checked' : ''; ?>/>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } if($action == "Edit" && $eSystemType == "DeliverAll") { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Promocode <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?> Type : <?= $eStoreType ?></label>
                                                <input type="hidden" name="eStoreType" value="<?= $eStoreType ?>">
                                            </div>
                                        </div>
                                        <?php if($db_data[0]['eStoreType'] == "StoreSpecific") { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?php echo $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?> : <?= $db_company ?></label>
                                                    <input type="hidden" name="iCompanyId" value="<?= $iCompanyId ?>">
                                                    <input type="hidden" name="iServiceId" id="iServiceId" value="<?= $iServiceId ?>">
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Free Delivery Promo Code : <?= $eFreeDelivery ?></label>
                                                <input type="hidden" name="eFreeDelivery" value="<?= $eFreeDelivery ?>">
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>

                                <?php if($MODULES_OBJ->isEnableLocationWisePromoCode()) { ?>
                                    <div class="row coupon-action-n3">
                                        <div class="col-lg-12">
                                            <label>Select Location <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="You can add promocode for specific location, so this promocode would be applicable to the <?php echo strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']); ?> based on their location."></i></label>
                                        </div>
                                        <div class="col-lg-6 col-sm-6">
                                            <select name="iLocationId" class="form-control" <?php if($action == "Edit") { ?> disabled="disabled" <?php } ?>>
                                                <option value="0" <?php if($iLocationId == '0') {echo "selected";} ?>>Select Location</option>
                                                <?php foreach ($db_location as $key => $value) { ?>
                                                    <option value="<?php echo $value['iLocationId'] ?>" <?php if ($value['iLocationId'] == $iLocationId) {   echo "selected"; }?>><?php echo $value['vLocationName'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <?php if ($userObj->hasPermission('view-geo-fence-locations') && $action == "Add") { ?>
                                                <a class="btn btn-primary" href="location.php" target="_blank" style="margin: 3px 0 0 10px;">Enter New Location</a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php if($action == "Edit") { ?>
                                        <input type="hidden" name="iLocationId" value="<?= $iLocationId ?>">
                                    <?php } ?>
                                <?php } ?>

                                <div class="row coupon-action-n3">
                                    <div class="col-lg-12">
                                        <label>Status<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <select id="eStatus" name="eStatus" class="form-control ">
                                            <option value="Active" <?php if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Active") { ?>selected <?php } ?> >Active</option>
                                            <option value="Inactive" <?php if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Inactive") { ?>selected <?php } ?> >Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                

                                <div class="row coupon-action-n4">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-promocode')) || ($action == 'Add' && $userObj->hasPermission('create-promocode'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?php if ($action == 'Add') { ?><?= $action; ?> PromoCode<?php } else { ?>Update<?php } ?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>
                                        <!--                <a href="javascript:void(0);" <?php if ($action == 'Edit') { ?> onClick="reset_form('_coupon_form'),reset_CouponCode();" <?php } else { ?> onClick="reset_form('_coupon_form');"  <? } ?>  class="btn btn-default">Reset</a> -->
                                        <a href="coupon.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>
        <? include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script>
            $('[data-toggle="tooltip"]').tooltip();
            function validate_coupon(username)
            {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_validate_coupon.php',
                    'AJAX_DATA': 'vCouponCode=' + username
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        if (data == 0)
                        {
                            $('#coupon_status').html('<i class="icon icon-remove alert-danger alert">   Coupon Code Already Exist</i>');
                            $('input[type="submit"]').attr('disabled', 'disabled');
                            return false;
                        } else if (data == 1)
                        {
                            $('#coupon_status').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                            $('vCouponCode[type="submit"]').removeAttr('disabled');
                        } else if (data == 2)
                        {
                            $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> Please Enter Coupon Code</i>');
                            $('vCouponCode[type="submit"]').removeAttr('disabled');
                        } 
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }
        </script>

        <?php if ($action == 'Edit') { ?>
            <script>
                window.onload = function () {
                    showhidedate('<?php echo $eValidityType; ?>');
                };
            </script>
        <? } else { ?>
            <script>
                window.onload = function () {
                    $('input:radio[name=eValidityType][value=Permanent]').attr('checked', true);
                };
            </script>
        <?php } ?>
        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>

        <script type="text/javascript">
            var adt = $("#dActiveDate").val();
            if (adt == '0000-00-00')
            {
                $("#dActiveDate").datepicker({
                    minDate: 0, //for avoid previous dates
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        //dt.setDate(dt.getDate() + 1);
                        dt.setDate(dt.getDate());
                        $("#dExpiryDate").datepicker("option", "minDate", dt);
                    }
                }).val('');

                $("#dExpiryDate").datepicker({
                    minDate: 0,
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        //dt.setDate(dt.getDate() - 1);
                        dt.setDate(dt.getDate());
                        $("#dActiveDate").datepicker("option", "maxDate", dt);
                    }
                }).val('');
            } else
            {
                $("#dActiveDate").datepicker({
                    minDate: 0, //for avoid previous dates
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        // dt.setDate(dt.getDate() + 1);
                        dt.setDate(dt.getDate());
                        $("#dExpiryDate").datepicker("option", "minDate", dt);
                    }
                });

                $("#dExpiryDate").datepicker({
                    minDate: 0,
                    numberOfMonths: 1,
                    dateFormat: "yy-mm-dd",
                    onSelect: function (selected) {
                        var dt = new Date(selected);
                        //dt.setDate(dt.getDate() - 1);
                        dt.setDate(dt.getDate());
                        $("#dActiveDate").datepicker("option", "maxDate", dt);
                    }
                });
            }
            function showhidedate(val) {
                if (val == "Defined") {
                    document.getElementById("date1").style.display = '';
                    document.getElementById("date2").style.display = '';
                    document.getElementById("dActiveDate").lang = '*';
                    document.getElementById("dExpiryDate").lang = '*';
                } else {
                    document.getElementById("date1").style.display = 'none';
                    document.getElementById("date2").style.display = 'none';
                    document.getElementById("dActiveDate").required = false;
                    document.getElementById("dExpiryDate").required = false;

                    document.getElementById("dActiveDate").lang = '';
                    document.getElementById("dExpiryDate").lang = '';
                }
            }

            function randomStringToInput(clicked_element)
            {
                var self = $(clicked_element);
                var random_string = generateRandomString(6);
                var vCouponCode_field =$('input[name=vCouponCode]');
                vCouponCode_field.val(random_string);
                var form = $(vCouponCode_field).closest('form');
                form.validate().element(vCouponCode_field);

            }
            function generateRandomString(string_length)
            {
                var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                var string = '';
                for (var i = 0; i <= string_length; i++)
                {
                    var rand = Math.round(Math.random() * (characters.length - 1));
                    var character = characters.substr(rand, 1);
                    string = string + character;
                }
                return string;
            }

            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "coupon.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);

                if($('#iServiceId').length > 0) {
                    changeserviceCategory(<?= $iServiceIdNew ?>);
                }
                if($('[name="eFreeDelivery"]').length > 0) {
                    $('[name="eFreeDelivery"]').trigger('change');
                }
            });

            function checkuserlimit(userlimit)
            {
                if (userlimit != "") {
                    if (userlimit == 0)
                    {
                        $('#iUsageLimitmsg').html('<i class="icon icon-remove alert-danger alert">You Can Not Enter Zero Number</i>');
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    } else if (userlimit <= 0) {
                        $('#iUsageLimitmsg').html('<i class="icon icon-remove alert-danger alert">You Can Not Enter Negative Number</i>');
                        $('input[type="submit"]').attr('disabled', 'disabled');
                    } else {
                        $('#iUsageLimitmsg').html('');
                        $('input[type="submit"]').removeAttr('disabled');
                    }
                } else {
                    $('#iUsageLimitmsg').html('');
                }

            }

            function reset_CouponCode() {
                var vCouponCodeval = $('#vCouponCodeval').val();
                $('#vCouponCode').val(vCouponCodeval);
            }
            
            function isNumberKey(evt)
            {
                var charCode = (evt.which) ? evt.which : event.keyCode
                if (charCode > 47 && charCode < 58 || charCode == 46 || charCode == 127 || charCode == 8)
                    return true;
                return false;
            }

            function editDescription(action)
            {
                $('#modal_action').html(action);
                $('#coupon_desc_Modal').modal('show');
            }

            function saveDescription()
            {
                if($('#tDescription_<?= $default_lang ?>').val() == "") {
                    $('#tDescription_<?= $default_lang ?>_error').show();
                    $('#tDescription_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tDescription_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#tDescription_Default').val($('#tDescription_<?= $default_lang ?>').val());
                $('#tDescription_Default').closest('.row').removeClass('has-error');
                $('#tDescription_Default-error').remove();
                $('#coupon_desc_Modal').modal('hide');
            }
            <?php if ($action == 'Add') { ?>
                $('[name="eStoreType"]').click(function() {
                    var eStoreType = $('input[name=eStoreType]:checked').val();
                    if(eStoreType == 'All'){
                        $('#eSystemType').prop('disabled', false);
                        $('#free_delivery').show();
                        $('#store_selection').hide();
                    } else {

                        //if($('#eSystemType').length > 0) {
                            $('#eSystemType').val("DeliverAll");
                            $('#eSystemType').prop('disabled', true);
                            $('#free_delivery').show();
                            if($(this).val() == "StoreSpecific") {
                                $('#store_selection').show();    
                            } else {
                                $('#store_selection').hide();
                            }
                       // }
                    }
                });
            <?php } else { ?>
                $('[name="eStoreType"]').click(function() {
                    if($('#eSystemType').length > 0) {
                        $('#eSystemType').val("DeliverAll");
                        $('#eSystemType').prop('disabled', true);
                        $('#free_delivery').show();
                        if($(this).val() == "StoreSpecific") {
                            $('#store_selection').show();    
                        } else {
                            $('#store_selection').hide();
                        }
                    }
                });

            <?php } ?>


            var eSystemType = $("input[name=eSystemType]").val();
            var eSystemTypeSelect = $('select[name="eSystemType"]').val();
            if(eSystemTypeSelect == 'DeliverAll'|| eSystemType == 'DeliverAll'){
                $('#StoreTypeOption').show();
            } else {
                $('#StoreTypeOption').hide();
            }

            $('#eSystemType').on('change', function() {
              if(this.value == 'DeliverAll'){
                $('#StoreTypeOption').show();
              } else {
                $('#StoreTypeOption').hide();
              }
            });

            function changeserviceCategory(iServiceId) {
                var iCompanyId = '<?php echo $iCompanyId; ?>';

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_get_restorantcat_filter.php',
                    'AJAX_DATA': {iServiceIdNew: iServiceId, iCompanyId: iCompanyId},
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $("#iCompanyId").html('');
                        $("#iCompanyId").html(data); 
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }

            $('[name="eFreeDelivery"]').change(function() {
                if($(this).is(':checked') == true || $(this).val() == "Yes") {
                    $('#discount_details').hide();
                }
                else {
                    $('#discount_details').show();
                }
            });
        </script>
        <?php if ($action != 'Edit') { ?>
            <script>
                //randomStringToInput(document.getElementById("vCouponCode"));
            </script>
        <?php } ?>
    </body>
</html>