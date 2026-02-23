<?php
include_once('common.php');

$script="ProcessingOrder";
$AUTH_OBJ->checkMemberAuthentication();

$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$ssql='';
$orderinventorystore = !empty($MODULES_OBJ->isEnableOrderInventoryStore()) ? true : false;

$os_ssql = "";
if(!$MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
    $os_ssql .= " AND eBuyAnyService = 'No'";
}
if(!$MODULES_OBJ->isTakeAwayEnable()) {
    $os_ssql .= " AND eTakeaway != 'Yes'";   
}

$orderStatusCodes = "1,2,4,5";
$orderStatus = $obj->MySQLSelect("select iOrderStatusId,vStatus,iStatusCode from order_status WHERE 1 = 1 AND iStatusCode IN (".$orderStatusCodes.") $os_ssql GROUP BY iStatusCode");

$searchOrderStatus = isset($_REQUEST['searchOrderStatus']) ? $_REQUEST['searchOrderStatus'] : '';
if($action!='')
{
    $startDate=$_REQUEST['startDate'];
    $endDate=$_REQUEST['endDate'];
    $dateRange = isset($_REQUEST['dateRange']) ? $_REQUEST['dateRange'] : '';
    if($startDate!=''){
        $ssql.=" AND Date(o.tOrderRequestDate) >='".$startDate."'";
    }
    if($endDate!=''){
        $ssql.=" AND Date(o.tOrderRequestDate) <='".$endDate."'";
    }
    if ($searchOrderStatus != '') {
        $ssql .= " AND o.iStatusCode ='" . $searchOrderStatus . "'";
    }
}
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
    $vLang = $default_lang;
}
$processOrderArray = array('1','2','4','5');
// $processOrderArray = array('6','7','8','9','11');
$iStatusCode = '('.implode(',',$processOrderArray).')';
$sql = "SELECT c.eAvailable,c.eStatus,c.tSessionId,o.iDriverId,o.iUserId,c.iCompanyId,c.iGcmRegId,c.iServiceId,o.iOrderId,o.vOrderNo,o.tOrderRequestDate, o.vTimeZone, o.fNetTotal,o.fTotalGenerateFare,o.fOutStandingAmount,o.fCommision,o.fDeliveryCharge,o.fOffersDiscount, Concat(u.vName,' ',u.vLastName) as Username,c.vCompany,os.vStatus_".$vLang." as vStatus,(select count(od.iOrderId) from order_details as od where od.iOrderId = o.iOrderId) as TotalItem,o.eTakeaway,o.iStatusCode,o.eOrderplaced_by,o.eCancelledbyDriver From orders as o LEFT JOIN company as c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os ON os.iStatusCode = o.iStatusCode LEFT JOIN register_user as u ON u.iUserId = o.iUserId WHERE o.iCompanyId = '".$_SESSION['sess_iUserId']."' AND o.iStatusCode IN $iStatusCode ".$ssql." AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') GROUP BY o.iOrderId ORDER BY o.iOrderId DESC "; 

$db_order_detail = $obj->MySQLSelect($sql);

$jsondb_order_detail = array_column($db_order_detail, 'iOrderId');
$jsondb_order_detail = json_encode($jsondb_order_detail);


// $invoice_icon = "driver-view-icon.png";
// $canceled_icon = "canceled-invoice.png";

if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}

if(file_exists($logogpath."canceled-invoice.png")) {
    $canceled_icon = $logogpath."canceled-invoice.png";
} else {
    $canceled_icon = "assets/img/canceled-invoice.png";
}

$dbCompanyData = $obj->MySQLSelect("SELECT eAvailable,eStatus,tSessionId,iCompanyId,iServiceId FROM `company` WHERE iCompanyId= '".$_SESSION['sess_iUserId']."' LIMIT 0,1");
$db_userdata = array();
$db_userdata[0]['tSessionId'] = $dbCompanyData[0]['tSessionId'];
$db_userdata[0]['iCompanyId'] = $dbCompanyData[0]['iCompanyId'];
$db_userdata[0]['iServiceId'] = $dbCompanyData[0]['iServiceId'];
if(empty($db_userdata[0]['tSessionId'])) {
$dbCompanySessionData = $obj->MySQLSelect("SELECT tSessionId,iCompanyId FROM `company` WHERE tSessionId != '' LIMIT 0,1");
$db_userdata[0]['tSessionId'] = $dbCompanySessionData[0]['tSessionId'];
$db_userdata[0]['iCompanyId'] = $dbCompanySessionData[0]['iCompanyId'];
}


//$db_userdata = $obj->MySQLSelect("SELECT tSessionId,iUserId FROM `register_user` WHERE tSessionId != '' LIMIT 0,1");

$sqlCancleReason = $obj->MySQLSelect("SELECT vTitle_" . $vLang . " as vTitle,iCancelReasonId FROM cancel_reason WHERE eStatus = 'Active' AND eType = 'DeliverAll' AND (eFor = 'Company' OR eFor='General')");

$db_records = $obj->MySQLSelect("SELECT iOrderId,count(CASE WHEN eStatus = 'Accept' THEN iDriverId END) as total_accept,max(tDate) as ttDate,count(iOrderId) as corder  FROM driver_request  WHERE 1 = 1 GROUP BY iOrderId ORDER BY  `tDate` DESC");
$orderRequestDataArr = array();
for ($r = 0; $r < scount($db_records); $r++) {
    $orderRequestDataArr[$db_records[$r]['iOrderId']] = $db_records[$r];
}
                                               
//$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $db_order_detail[0]['iServiceId']);
//$json_lang = json_encode($languageLabelsArr);
$languageArr = array();
$languageArr['LBL_TRY_AGAIN_LATER'] = $langage_lbl_admin['LBL_MISSED_DETAILS_MSG'];
$languageArr['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'] = $langage_lbl_admin['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'];
$languageArr['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'] = $langage_lbl_admin['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'];
$languageArr['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'] = $langage_lbl_admin['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'];
$languageArr['LBL_INFO_UPDATED_TXT'] = $langage_lbl_admin['LBL_INFO_UPDATED_TXT'];
$languageArr['SESSION_OUT'] = "SESSION_OUT";
$json_lang = json_encode($langage_lbl);
$acceptOrder = $MODULES_OBJ->isEnableAcceptingOrderFromWeb();

$statusData = $obj->MySQLSelect("SELECT vStatus_$vLang as vStatus FROM order_status WHERE iStatusCode = '4'");
$lbl_accepted_driver = $statusData[0]['vStatus'];

$statusData = $obj->MySQLSelect("SELECT vStatus_$vLang as vStatus FROM order_status WHERE iStatusCode = '5'");
$lbl_pickedup_driver = $statusData[0]['vStatus'];

if(!empty($db_order_detail)) {
    $statusData = $obj->MySQLSelect("SELECT vStatus_$vLang as vStatus FROM order_status WHERE iStatusCode = '2'");
    $lbl_accepted_store = str_replace("#STORE#", $db_order_detail[0]['vCompany'], $statusData[0]['vStatus']);    
}

//reassign driver - for that reset current driver
$Datadriver = $obj->MySQLSelect("SELECT tSessionId,iDriverId FROM `register_driver` WHERE tSessionId != '' LIMIT 1");
if(empty($Datadriver)){
    $Datadriver = $obj->MySQLSelect("SELECT tSessionId,iDriverId FROM `register_driver` WHERE 1 LIMIT 1");
    $Data_update_passenger = array();
    $whereCondition = " iDriverId = ".$Datadriver[0]['iDriverId'];
    $Data_update_passenger['tSessionId'] = $Datadriver[0]['tSessionId'] = session_id() . time();
    $obj->MySQLQueryPerform("register_driver", $Data_update_passenger, 'update', $whereCondition);
}
$driversessionid = $Datadriver[0]['tSessionId'];
$driverId = $Datadriver[0]['iDriverId'];
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_PROCESSING_ORDERS_TXT_WEB']; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
    <!-- End: Default Top Script and css-->
    <style type="text/css">
    	.grey-color {
    		color: grey !important;
    	}
        .smallbtn button {
            padding: 7px 7px 7px 7px !important;
            font-size: 13px !important;
        }
    </style>
</head>
<body>
  <!-- home page -->
    <div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- contact page-->


<section class="profile-section my-trips">
    <div class="profile-section-inner">
        <div class="profile-caption" style="justify-content: space-between;">
            <div class="page-heading">
                <h1><?=$langage_lbl['LBL_PROCESSING_ORDERS_TXT_WEB']; ?></h1>
            </div>
            
            <form class="tabledata-filter-block filter-form" name="search"  method="post" onSubmit="return checkvalid()">
                <input type="hidden" name="action" value="search" />
                <div class="filters-column mobile-full">
                    <label>Select by order status</label>
                    <select id="searchOrderStatus" name="searchOrderStatus">
                        <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                        <?php foreach ($orderStatus as $value) { ?>
                            <option value="<?= $value['iStatusCode']; ?>" <?php
                            if ($searchOrderStatus == $value['iStatusCode']) {
                                echo "selected";
                            }
                            ?>><?= $value['vStatus']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="filters-column mobile-full">
                    <button class="driver-trip-btn"><?= $langage_lbl['LBL_COMPANY_TRIP_Search']; ?></button>
                    <!-- <button onClick="reset();" class="driver-trip-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></button> -->
                    <a href="cx-processing_orders_store.php" class="gen-btn"><?= $langage_lbl['LBL_MYTRIP_RESET']; ?></a>
                </div>
            </form>
            <!--<form name="availability" method="post" action="">-->
            <!--    <div class="filters-column mobile-full">-->
            <!--    <label><?php echo $langage_lbl['LBL_ACCEPTING_ORDERS']; ?></label>-->
            <!--    <span class="toggle-switch">-->
            <!--        <input type="checkbox" onchange="check_box_value_checked(this.value);" id="eAvailabilty" class="chk" name="eAvailable" <?php if ($result_company[0]['eAvailable'] == 'Yes') { ?>checked<?php } ?> value="Yes" />-->
            <!--        <span class="toggle-base"></span>-->
            <!--    </span>-->
            <!--    </div>-->
            <!--</form>-->
            
            <div class="profile-detail">
                    <?php if($acceptOrder > 0) {
                        $radiobtn = "";
                        if ($dbCompanyData[0]['eStatus'] != 'Active') {
                            $radiobtn = "disabled='disabled'";
                        }
                        ?>
                        <div class="profile-column">
                            <div class="accept-orders">
                                <i class="icon-accept" aria-hidden="true"><img src="assets/img/choices.svg" alt=""></i>
                                <div class="">
                                    <span>
                                        <span class="toggle-switch">
                                            <input <?= $radiobtn ?> type="checkbox" onchange="return autoAcceptStatus(this);" name="accept_order" <?= ($dbCompanyData[0]['eAvailable'] == 'Yes') ? 'checked' : ''; ?> data-status="<?= ($dbCompanyData[0]['eAvailable'] == "Yes") ? 'No' : 'Yes' ?>" id="accept_order" class="valid" aria-invalid="false">
                                            <span class="toggle-base"></span>
                                        </span>
                                    </span>
                                    <strong><?= $langage_lbl['LBL_ACCEPTING_ORDERS']; ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
            </div>
        </div>
    </div>
</section>

<section class="profile-earning">
    <div class="profile-earning-inner">
		<div class="table-holder">
			<div class="page-contant-inner">
		  		<!-- trips page -->
			  	<div class="trips-page smallbtn">
			    	<div class="trips-table"> 
                    <div class="trips-table-inner">
                    <div class="driver-trip-table">
                    <table width="100%" class="ui celled table custom-table dataTable no-footer" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
                    <thead>
                    <tr>
                        <th><?=$langage_lbl_admin['LBL_ORDER_NO_TXT'];?></th>				
                        <th><?=$langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
                        <th><?=$langage_lbl['LBL_PASSENGER_NAME_TEXT_DL']; ?></th>
                        <th><?=$langage_lbl['LBL_TOTAL_ITEM_TXT']; ?></th>
                        <th><?=$langage_lbl['LBL_ORDER_EARNING_TXT']; ?></th>
                        <th><?=$langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
                        <th><?=$langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
                        <? if(!empty($orderinventorystore)) { ?>
                        <th><?=$langage_lbl['LBL_ACTION_WEB']; ?></th>
                        <? } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <? for($i=0;$i<scount($db_order_detail);$i++) {
                        $getUserCurrencyLanguageDetails = getCompanyCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$db_order_detail[$i]['iOrderId']);
                        $Ratio = $getUserCurrencyLanguageDetails['Ratio'];
                        $currencycode = $getUserCurrencyLanguageDetails['currencycode'];
                        //$fTotalGenerateFare = $db_order_detail[$i]['fTotalGenerateFare'] - $db_order_detail[$i]['fCommision'] - $db_order_detail[$i]['fDeliveryCharge']- $db_order_detail[$i]['fOffersDiscount']-$db_order_detail[$i]['fOutStandingAmount'];
                        $fTotalGenerateFare = $db_order_detail[$i]['fTotalGenerateFare'] - $db_order_detail[$i]['fDeliveryCharge']- $db_order_detail[$i]['fOffersDiscount']-$db_order_detail[$i]['fOutStandingAmount'];

                        $systemTimeZone = date_default_timezone_get();
                        if($db_order_detail[$i]['tOrderRequestDate']!= "" && $db_order_detail[$i]['vTimeZone'] != "")  {
                            $tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'],$db_order_detail[$i]['vTimeZone'],$systemTimeZone);
                        } else {
                            $tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
                        } ?> 
                        <tr class="gradeA" data-orderid="<?= $db_order_detail[$i]['iOrderId'] ?>" data-userid="<?= $db_order_detail[$i]['iUserId'] ?>" data-accepted="No">
                            <td align="center">
                                <?=$db_order_detail[$i]['vOrderNo'];?>
                                <?= $db_order_detail[$i]['eTakeaway'] == 'Yes' ? '<br><span class="grey-color">'.$langage_lbl['LBL_TAKE_AWAY'].'</span>' : ($db_order_detail[$i]['eOrderplaced_by'] == "Kiosk" ? '<br><span class="grey-color">'.$langage_lbl['LBL_DINE_IN_TXT'].'</span>' : '')?>
                            </td>
                            <td data-order="<?php echo $tOrderRequestDate; ?>"><?= DateTime1($tOrderRequestDate,'yes');?></td>
                            <td align="center"><?= clearName($db_order_detail[$i]['Username']);?></td>
                            <td align="center"><?=$db_order_detail[$i]['TotalItem'];?></td>
                            <td align="center"><?=formateNumAsPerCurrency(($fTotalGenerateFare * $Ratio),$currencycode);?></td>
                            <!--<td align="center"><?=trip_currency($fTotalGenerateFare * $Ratio);?></td>-->
                            <td align="center" data-col="status"><?= str_replace("#STORE#", $db_order_detail[$i]['vCompany'], $db_order_detail[$i]['vStatus']); ?></td>
                            <td align="center" width="10%">
                              <a target = "_blank" href="order_invoice.php?iOrderId=<?=base64_encode(base64_encode($db_order_detail[$i]['iOrderId']))?>">
                                    <img alt="" src="<?php echo $invoice_icon;?>">
                             </a>
                            </td>
                            <? if(!empty($orderinventorystore)) { ?>
                            <td align="center" data-col="action">
                                <?php 
                                if(($db_order_detail[$i]['iStatusCode'] == 2) && ($db_order_detail[$i]['iCronStage'] >= 5)){ ?>
                                    <button onclick="return openDriverTypeModal('<?php echo $db_order_detail[$i]["iOrderId"]; ?>','<?php echo $db_order_detail[$i]['iCompanyId'] ?>','<?php echo $db_order_detail[$i]['iGcmRegId'] ?>','<?php echo $db_order_detail[$i]['iUserId'] ?>');" class="btn btn-sm gen-btn"><?= $langage_lbl['LBL_MANUAL_BOOKING_NO_DRIVER_AVAILABLE']; ?></button><?php 
                                } else {
                                    if ($db_order_detail[$i]['iStatusCode'] == 1) { ?>
                                        <button onclick="acceptorder('<?= $db_order_detail[$i]['iOrderId'] ?>','<?= $db_order_detail[$i]['iCompanyId'] ?>')" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_CONFIRM_TXT']; ?></button>
                                        <button onclick="showdeclineModel('<?= $db_order_detail[$i]['iOrderId'] ?>','<?= $db_order_detail[$i]['iCompanyId'] ?>')" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_DECLINE_TXT']; ?></button><?php 
                                    } else if($db_order_detail[$i]['iStatusCode'] == 2) {
                                        $iOrderId = $db_order_detail[$i]['iOrderId'];
                                        $currentdate = @date('Y-m-d H:i:s');
                                        $total_accept = $corder = $cabbook = 0;
                                        $checkdate = "";
                                        if (isset($orderRequestDataArr[$iOrderId])) {
                                            $tDate = $orderRequestDataArr[$iOrderId]['ttDate'];
                                            $corder = $orderRequestDataArr[$iOrderId]['corder'];
                                            $total_accept = $orderRequestDataArr[$iOrderId]['total_accept'];
                                        }
                                        $checkdate = date('Y-m-d H:i:s', strtotime("+" . $RIDER_REQUEST_ACCEPT_TIME . " seconds", strtotime($tDate)));
                                        if ($corder == 0) { 
                                            if($db_order_detail[$i]['eTakeaway']=="Yes") { ?>
                                                <button onclick="acceptorder('<?= $db_order_detail[$i]['iOrderId'] ?>','<?= $db_order_detail[$i]['iCompanyId'] ?>','Yes','<?= $db_order_detail[$i]['vTimeZone'] ?>')" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_PICKEDUP_ORDER']; ?></button>
                                            <? } else if ($db_order_detail[$i]['eOrderplaced_by'] != "Kiosk") { ?>
                                            <button onclick="return openDriverTypeModal('<?php echo $db_order_detail[$i]["iOrderId"]; ?>','<?php echo $db_order_detail[$i]['iCompanyId'] ?>','<?php echo $db_order_detail[$i]['iGcmRegId'] ?>','<?php echo $db_order_detail[$i]['iUserId'] ?>');" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_ASSIGN_TO_DRIVER']; ?></button>
                                            <?php } else {
                                                echo "-";
                                            } 
                                        } else {
                                            $currentdate = @date('Y-m-d H:i:s');
                                            $time1 = strtotime($currentdate);
                                            $time2 = strtotime($checkdate);
                                           
                                            if ($total_accept == 0 && $time1 <= $time2) { ?>
                                                <button href="#" class="btn btn-sm gen-btn"><?= $langage_lbl['LBL_WAITING_DRIVER_ACCEPT_TXT']; ?></button><?php 
                                            } else if ($db_order_detail[$i]['iDriverId'] > 0) {
                                                /*<!--<button class="btn btn-sm gen-btn" disabled><?php echo 'No Action Request'; ?></button>-->*/
                                                echo "-";
                                            } else if ($db_order_detail[$i]['eOrderplaced_by'] != "Kiosk") { ?>
                                                <button onclick="return openDriverTypeModal('<?php echo $db_order_detail[$i]["iOrderId"]; ?>','<?php echo $db_order_detail[$i]['iCompanyId'] ?>','<?php echo $db_order_detail[$i]['iGcmRegId'] ?>','<?php echo $db_order_detail[$i]['iUserId'] ?>');" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_ASSIGN_TO_DRIVER']; ?></button>
                                                <button onclick="showdeclineModel('<?= $db_order_detail[$i]['iOrderId'] ?>','<?= $db_order_detail[$i]['iCompanyId'] ?>')" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_DECLINE_TXT']; ?></button>
                                            <?php } else {
                                                echo "-";
                                            }
                                        }
                                    } else if($db_order_detail[$i]['iStatusCode'] == 9) { ?>
                                        <button style="color:#ff0000" class="btn btn-sm gen-btn" disabled><?= $langage_lbl['LBL_DECLINED'] ?></button><?php 
                                    } else { 
                                    /*<button class="btn btn-sm gen-btn" disabled><?php echo 'No Action Request'; ?></button>*/
                                    //reassign driver - for that reset current driver
                                    if(strtoupper($REASSIGN_DRIVER_AFTER_ACCEPTING_REQUEST)=='YES' && $db_order_detail[$i]['iStatusCode'] == 4 && $db_order_detail[$i]["iDriverId"] > 0 && $db_order_detail[$i]['eTakeaway'] == "No" && $db_order_detail[$i]['eCancelledbyDriver'] == "No") { ?>
                                        <button href="#" onclick="openResetDriverTypeModal(this);" class="btn btn-sm gen-btn" data-id="<?= $db_order_detail[$i]['iOrderId']; ?>" type="button" style="margin-top: 10px"><?= $langage_lbl['LBL_RESET_DRIVER']; ?></button>
                                    <? } else {
                                        echo "-";
                                    }
                                } } ?>
                                
                            </td>
                            <? } ?>
                        </tr>
                    <? } ?>		
                    </tbody>
                    </table>
                    </div></div></div>
			    </div>
			  <div style="clear:both;"></div>
			</div>
		</div>
	</div>
</section>						
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    <!-- footer part end -->
        <!-- End:contact page-->
        <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php'); ?>
    <div class="custom-modal-main" id="DriverResetTypeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="custom-modal">
            <div class="model-header">
                <h4 class="modal-title"><?= $langage_lbl['LBL_RESET_DRIVER']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
                <input type="hidden" name="iOrderIdResetType" id="iOrderIdResetType" value="" >
                <div class="form-group col-lg-12">
                    <p><?= $langage_lbl['LBL_RESET_PROVIDER_TXT']; ?></p>
                </div>
                <div class="form-group" id="resetconfirmshow" style="display: inline-block; margin-top: 20px;">
                   <input type="button" onclick="openResetDriverModal()" name="submit" value="Confirm" class="gen-btn" >
                   <input type="button" name="cancel" value="Cancel" class="gen-btn" data-dismiss="modal">
                </div>
                <div class="" id="resetconfirmhide" style="width:100%; display: none;">
                    <div align="center">
                        <img src="default.gif">
                        <span>Please Wait...</span>                    
                    </div>                                                           
                </div>
            </div>
        </div>
    </div>
    
    <div class="custom-modal-main" id="assignDriverModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="custom-modal">
            <div class="model-header">
                <h4 class="modal-title"><?= $langage_lbl['LBL_ASSIGN_DRIVER']; ?></h4>
                <i class="icon-close" data-dismiss="modal"></i>
            </div>
            <div class="model-body">
                <input type="hidden" name="iOrderIdManual" id="iOrderIdManual" value="" >
                <input type="hidden" name="iCompanyIdManual" id="iCompanyIdManual" value="" >
                <input type="hidden" name="iGcmRegIdManual" id="iGcmRegIdManual" value="" >
                <div class="form-group col-lg-12">
                    <span class="auto_assign001">
                        <input type="radio" name="eAutoAssign" id="eAutoAssign" checked value="Yes" >&nbsp;<?= $langage_lbl['LBL_OTHER_TXT']; ?>
                    </span>
                    <span class="auto_assign001">
                        <input type="radio" name="eAutoAssign" id="eAutoAssign1" value="No" >&nbsp;<?= $langage_lbl['LBL_PERSONAL']; ?>
                    </span>	
                </div>
                <div class="form-group" style="display: inline-block; margin-top: 20px;">
                   <input type="button" onclick="send_req_btn()" name="submit" value="<?= $langage_lbl['LBL_SEND_REQUEST']." ".$langage_lbl['LBL_DIVER'];?>" class="gen-btn sendbtn" >
                </div>
            </div>
        </div>
    </div>
    
    <div class="custom-modal-main in fade" id="declineModel" aria-hidden="true">
        <div class="custom-modal">
            <div class="modal-content image-upload-1">
                <div class="upload-content">
                    <div class="model-header">
                        <h4><?= $langage_lbl['LBL_DECLINE_ORDER'] ?></h4><i class="icon-close" data-dismiss="modal"> <span style="float: left;"></span></i>
                    </div>
                    <!--<form class="form-horizontal frm6" method="post">-->
                        <input type="hidden" id="iCompanyId" name="iCompanyId" value="">
                        <input type="hidden" id="UserType" name="UserType" value="">
                        <input type="hidden" id="iOrderId" name="iOrderId" value="">
                        <div class="model-body">
                            <select name="iCancelReasonId" id="iCancelReasonId" class="form-control">
                                <option value=""><?= $langage_lbl['LBL_SELECT_CANCEL_REASON'] ?></option>
                                <?php foreach ($sqlCancleReason as $value) { ?>
                                    <option value="<?= $value['iCancelReasonId'] ?>"><?= $value['vTitle'] ?></option>
                                <?php } ?>
                                <option value="other"><?= $langage_lbl['LBL_OTHER_TXT'] ?></option>
                            </select>
                            <hr style="border: 1px solid white;">
                            <textarea style="width: 100%;display: none;" id="cancelReason" name="vCancelReason" class="form-control" placeholder="Enter Reason Here"></textarea>
                            <span id="errorspan" style="font-size:11px;color:red;display:none"><?= $langage_lbl['LBL_FEILD_REQUIRD'] ?></span>
                        </div>
                        <div class="model-footer">
                            <div class="button-block">
                                <!--<input type="submit" class="save gen-btn" name="save" value="Save">-->
                                <!--<button onclick="declineorder()" class="btn btn-sm gen-btn"><?php echo ($_SESSION['sess_lang'] != 'ES') ? 'Yes' : 'Yes' ; ?></button>-->
                                <input onclick="declineorder()" type="button" class="gen-btn" name="cancel" value="Yes">
                                <input type="button" class="cancel11 gen-btn" data-dismiss="modal" name="cancel" value="No">
                            </div>
                        </div>
                    <!--</form>-->
                </div>
            </div>
        </div>
    </div>
    
<script src="assets/js/jquery-ui.min.js"></script>
<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="assets/js/modal_alert.js"></script>
<link href="assets/css/modal_alert.css" rel="stylesheet" />
<script type="text/javascript">
    var useridgeneral = '<?php echo $db_userdata[0]['iCompanyId']; ?>';
    var tSessionIdgeneral = '<?php echo $db_userdata[0]['tSessionId']; ?>';
    var tServiceIdgeneral = '<?php echo $db_userdata[0]['iServiceId']; ?>';
    
	if($('#my-trips-data').length > 0) {
        $('#my-trips-data').DataTable({"oLanguage": langData});
    }

    languagedata = <?php echo $json_lang; ?>;
    jsondb_order_detail = <?php echo $jsondb_order_detail; ?>;
     $(document).ready(function () {
        $('#dataTables-example').DataTable( {
            "oLanguage": langData,
            "order": [[ 1, "desc" ]],
             "bPaginate": false,
             "bInfo" : false
        });           
     });
    function reset() {
        location.reload();
    }

    var iOrderIdFun,iCompanyIdFun,ePickedUpFun,timeZoneFun,labeldeclined;
    function acceptorder(iOrderId, iCompanyId, ePickedUp = "No",timeZone = "Asia\/Kolkata") {
        iOrderIdFun = iOrderId;
        iCompanyIdFun = iCompanyId;
        ePickedUpFun = ePickedUp;
        timeZoneFun = timeZone;
        if (ePickedUp=='No') {
            show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT']; ?>", languagedata['LBL_CONFIRM_ORDER_ALERT'], "<?= $langage_lbl['LBL_BTN_NO_TXT']; ?>", "<?= $langage_lbl['LBL_BTN_YES_TXT']; ?>", "",function (btn_id) {
                if(btn_id==1) {
                    acceptingOrderProcess(iOrderIdFun, iCompanyIdFun, ePickedUpFun, timeZoneFun);
                } else {
                    return false;
                }
            },true,false);
        } else {
            acceptingOrderProcess(iOrderIdFun, iCompanyIdFun, ePickedUpFun, timeZoneFun);
        }
        
    }
    function acceptingOrderProcess(iOrderId, iCompanyId, ePickedUp = "No", timeZone = "Asia\/Kolkata") {
        iOrderIdFun = iOrderId;
        iCompanyIdFun = iCompanyId;
        ePickedUpFun = ePickedUp;
        timeZoneFun = timeZone;
        if (ePickedUp=='Yes') {
            var data = {
                    'type': 'GetOrderDetailsRestaurant', 
                    'iOrderId': iOrderId, 
                    'iCompanyId': iCompanyId, 
                    'vTimeZone': timeZone, 
                    "GeneralMemberId": useridgeneral, 
                    "GeneralUserType": 'Company', 
                    "tSessionId": tSessionIdgeneral,
                    "iServiceId": tServiceIdgeneral,
                    'async_request': false
                };
            data = $.param(data);
            getDataFromApi(data, function(response) {
                var response_data = JSON.parse(response);
                labeldeclined = response_data.message.LBL_PROOF_DECLINE_NOTE;
                if(response_data.message.vIdProofImageUploaded=="Yes") {
                    //var dataImage = response_data.message.vIdProofImage.response_data.message.vIdProofImageNote;
                    //var dataImage = "<img src='response_data.message.vIdProofImage\">"
                    show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT']; ?>", response_data.message.proffDataforWeb, "<?= $langage_lbl['LBL_DECLINE_TXT']; ?>", "<?= $langage_lbl['LBL_CONFIRM_TXT'] ?>", "",function (btn_id) {
                        if(btn_id==1) {
                            acceptingOrderProcessSecond(iOrderIdFun, iCompanyIdFun, ePickedUpFun, timeZoneFun);
                        } else {
                            show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT']; ?>", labeldeclined, "<?= $langage_lbl['LBL_CANCEL_TXT']; ?>", "<?= $langage_lbl['LBL_CONTACT_US_TXT'] ?>", "",function (btn_id) {
                                if(btn_id==1) {
                                    location.href = "/contact-us";
                                    //window.location.href = "http://google.com";
                                } else {
                                    return false;
                                }
                            });
                        }
                    },true,false);
                } else {
                    acceptingOrderProcessSecond(iOrderIdFun, iCompanyIdFun, ePickedUpFun, timeZoneFun);
                }
            });
        } else {
            acceptingOrderProcessSecond(iOrderIdFun, iCompanyIdFun, ePickedUpFun, timeZoneFun);
        }
        return false;
    }
    function acceptingOrderProcessSecond(iOrderId, iCompanyId, ePickedUp = "No", timeZone = "Asia/Kolkata") {
        var data = {
                    'type': 'ConfirmOrderByRestaurant', 
                    'iOrderId': iOrderId, 
                    'iCompanyId': iCompanyId, 
                    'ePickedUp': ePickedUp, 
                    "GeneralMemberId": useridgeneral, 
                    "GeneralUserType": 'Company', 
                    "tSessionId": tSessionIdgeneral,
                    "iServiceId": tServiceIdgeneral,
                    'async_request': false
                };
        data = $.param(data);
        getDataFromApi(data, function(response) {
            var response_data = JSON.parse(response);
            var label = response_data.message;
            if (response_data.Action==1) {
                if (ePickedUp=='Yes') {
                    label = 'LBL_CONFIRM_NOTE_PICKUP_ORDER';
                }
                show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT']; ?>", languagedata[label], "<?= $langage_lbl['LBL_OK']; ?>", "", "",function (btn_id) {
                    if(btn_id==0) {
                        //location.reload(); //for confirm order, page reload, this is commented when socketcluster code is open which is commented for now
                    }
                },true,false);
            } else {
                show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT']; ?>", languagedata[label], "<?= $langage_lbl['LBL_OK']; ?>", "", "",function (btn_id) {
                    return false;
                },true,false);
            }
        });
    }
    function assigndriver(iOrderId, iCompanyId,iGcmRegId) {
        //var useridgeneral = '<?php echo $db_userdata[0]['iUserId']; ?>';
        //var tSessionIdgeneral = '<?php echo $db_userdata[0]['tSessionId']; ?>';
        //var tServiceIdgeneral = '<?php echo $db_order_detail[0]['iServiceId']; ?>';
        radioValue = $("input[name='eAutoAssign']:checked").val();
        if (radioValue=='No') {
            driverid = $("#assignDriverModal #iDriverId").val(); 
            eDriverType = 'personal';  
        } else {
            driverid = '';
            eDriverType = 'site'; 
        }
        var data = {
            'type': 'sendRequestToDrivers', 
            'iOrderId': iOrderId, 
            'vDeviceToken': iGcmRegId, 
            'eDriverType': eDriverType, 
            "GeneralMemberId": useridgeneral, 
            "GeneralUserType": 'Company', 
            "tSessionId": tSessionIdgeneral,
            "iServiceId": tServiceIdgeneral,
            'async_request': false
        };
        data = $.param(data);
        getDataFromApi(data, function(response) {
            var response_data = JSON.parse(response);
            if (response_data.Action==1) {
                // location.reload();
                $('#assignDriverModal').removeClass('active');
                
                var action_html = '<button href="#" class="btn btn-sm gen-btn"><?= $langage_lbl['LBL_WAITING_DRIVER_ACCEPT_TXT']; ?></button>';

                $('#dataTables-example tr').each(function(index, value) {
                    if($(this).data('orderid') == iOrderId) {
                        $(this).find('td[data-col="action"]').html(action_html);
                        var user_id = $(this).data('userid');
                        updateOrderStatusAction(iOrderId, user_id);
                    }
                });
            } else {
                $('#assignDriverModal').removeClass('active');
                show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT'] ?>", languagedata['LBL_NO_DRIVERS_FOUND'], "<?= $langage_lbl['LBL_OK'] ?>", "", "",function (btn_id) {
                    return false;
                },true,false);
            }
        });
    }

    var RIDER_REQUEST_ACCEPT_TIME = parseInt("<?= $RIDER_REQUEST_ACCEPT_TIME ?>");
    function updateOrderStatusAction(iOrderId, iUserId) {
        var timeout = RIDER_REQUEST_ACCEPT_TIME * 1000;
        setTimeout(function() { 
            var companyId = "<?= $_SESSION['sess_iUserId']; ?>";
            var company_iGcmRegId = "<?= $db_order_detail[0]['iGcmRegId'] ?>";
            var action_html = '<button onclick="return openDriverTypeModal(\'' + iOrderId + '\',\'' + companyId + '\',\'' + company_iGcmRegId + '\',\'' + iUserId + '\');" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_ASSIGN_TO_DRIVER']; ?></button><button onclick="showdeclineModel(\'' + iOrderId + '\',\'' + companyId + '\')" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_DECLINE_TXT']; ?></button>';

            $('#dataTables-example tr').each(function(index, value) {
                if($(this).data('orderid') == iOrderId && $(this).data('accepted') == "No") {
                    $(this).find('td[data-col="action"]').html(action_html);
                }
            });
        }, timeout);
    }

    function declineorder() {
        var iCancelReasonId = $("#iCancelReasonId option:selected").val();
        var cancelReason = $("#cancelReason").val();
        if (iCancelReasonId=='' || (iCancelReasonId=='other' && cancelReason=='')) {
            $("#errorspan").show();
            return false;
        } else {
            $("#errorspan").hide();
        }
        //var useridgeneral = '<?php echo $db_userdata[0]['iUserId']; ?>';
        //var tSessionIdgeneral = '<?php echo $db_userdata[0]['tSessionId']; ?>';
        //var tServiceIdgeneral = '<?php echo $db_order_detail[0]['iServiceId']; ?>';
        var data = {
            'type': 'DeclineOrder', 
            'iOrderId': $("#iOrderId").val(), 
            'iCompanyId': $("#iCompanyId").val(),
            'UserType': 'Company',
            'vCancelReason': cancelReason, 
            'iCancelReasonId': iCancelReasonId, 
            "GeneralMemberId": useridgeneral, 
            "GeneralUserType": 'Company', 
            "tSessionId": tSessionIdgeneral,
            "iServiceId": tServiceIdgeneral,
            'async_request': false
        };
        data = $.param(data);
        getDataFromApi(data, function(response) {
            var response_data = JSON.parse(response);
            var label = response_data.message;
            $("#declineModel").removeClass('active');
            if (response_data.Action==1) {
                show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT'] ?>", languagedata[label], "<?= $langage_lbl['LBL_OK'] ?>", "", "",function (btn_id) {
                    //location.reload(); //for confirm order, page reload, this is commented when socketcluster code is open which is commented for now
                    removeOrderRow($("#iCompanyId").val());
                },true,false);
            } else {
                show_alert("<?= $langage_lbl['LBL_SUB_NOTE_TXT'] ?>", languagedata[label], "<?= $langage_lbl['LBL_OK'] ?>", "", "",function (btn_id) {
                    return false;
                },true,false);
            }
        });
    }
    function removeOrderRow(iOrderId) {
        $('#dataTables-example tr').each(function(index, value) {
            if($(this).data('orderid') == iOrderId) {
                $(this).remove();
            }
        });
    }
    function showdeclineModel(iOrderId, iCompanyId, UserType="Company") {
        $("#iCompanyId").val(iCompanyId);
        $("#UserType").val(UserType);
        $("#iOrderId").val(iOrderId);
        $("#declineModel").addClass('active');
    }
    $("#iCancelReasonId").change(function() {
        var val = $(this).val();
        $("#errorspan").hide();
        if (val == "other") {
            //$("#iCancelReasonId").prop('disabled', true);
            $("#cancelReason").val('');
            $("#cancelReason").slideDown();
        } else {
            //$("#iCancelReasonId").prop('disabled', false);
            $("#cancelReason").val('');
            $("#cancelReason").slideUp();
        }
    });
    function openDriverTypeModal(iOrderId, iCompanyId,iGcmRegId) {
        $("#iOrderIdManual").val(iOrderId);
        $("#iCompanyIdManual").val(iCompanyId);
        $("#iGcmRegIdManual").val(iGcmRegId);
        $('#assignDriverModal').addClass('active');
    }
    function send_req_btn() {
        iOrderId = $("#iOrderIdManual").val();
        iCompanyId = $("#iCompanyIdManual").val();
        iGcmRegId = $("#iGcmRegIdManual").val();
        assigndriver(iOrderId, iCompanyId,iGcmRegId);
    }
    $(document).ready(function(){
        $("[name='dataTables-example_length']").each(function(){
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'></em>");
        });
        $("[name='dataTables-example_length']").change(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        }).trigger('change');
    });
    function autoAcceptStatus(elem) {
        //accept_order
        var companyId = "<?= $_SESSION['sess_iUserId']; ?>";
        var typed = "eAvailable";
        var status = $(elem).attr("data-status");
        var newStatus = "Yes";
        if(status == "Yes"){
            var newStatus = "No";
        }
        if(newStatus == "No"){
            $('#accept_order').prop('checked', false);
        }else{
            $('#accept_order').prop('checked', true);
        }
        checkStoreAvailability(status);        
    }
    function checkStoreAvailability(updateStatus) {
        //var useridgeneral = '<?php echo $db_userdata[0]['iUserId']; ?>';
        //var tSessionIdgeneral = '<?php echo $db_userdata[0]['tSessionId']; ?>';
        //var tServiceIdgeneral = '<?php echo $db_order_detail[0]['iServiceId']; ?>';
        AUTO_ACCEPT_STATUS = updateStatus;
        var sendrequestparam = {
                "tSessionId": tSessionIdgeneral,
                "GeneralMemberId": useridgeneral,
                "iCompanyId": "<?= $db_userdata[0]['iCompanyId']; ?>",
                "iServiceId": tServiceIdgeneral,
                "GeneralUserType": 'Company',
                "UserType": 'Company',
                "type": 'UpdateRestaurantAvailability',
                "eAvailable": updateStatus,
                "CALL_TYPE": "Update",
                "test": "1",
                'async_request': false
        };
        $("#responsemsg").val('');
        sendrequestparam = $.param(sendrequestparam);
        getDataFromApi(sendrequestparam, function(response) {
            response = JSON.parse(response);
            $("#responsemsg").val(response.message);
            console.log(response.message);
            if (response.Action == '1') {
                show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>",languagedata[response.message],"<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>","","",function (btn_id) {
                    if(btn_id == 0){
                        location.reload();
                    }
                });
            }else{
                if ($("#responsemsg").val()=="LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT") {
                    show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>","<?= addslashes($langage_lbl['LBL_NOT_ITEM_ADD']); ?>","<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>","","",function (btn_id) {
                    if(btn_id == 0){
                        window.location = "<?= $tconfig["tsite_url"]; ?>menuitems.php";
                    }
                    });
                } else {
                    show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>",languagedata[response.message],"<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>","","",function (btn_id) {
                    if(btn_id == 0){
                        window.location = "<?= $tconfig["tsite_url"]; ?>settings";
                    }
                    });
                }
            }
        });
    }
</script>

<script>
    var searchOrderStatus = "<?= $searchOrderStatus ?>";
    //temporary reload at 2 minutes...
    setInterval(function(){
        var dt = new Date();
        var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        //location.reload();
    }, 120000);
    // As discuss with KS on 28-01-2021, comment this one, and reload page at 2 minutes..becoz it takes time to put and checked in everywhere..
    $(document).ready(function () {      
        
        var channel = 'ORDER_EVENT_NOTIFICATIONS';

        SOCKET_OBJ.subscribe(channel, function (response) {
            //var response = JSON.parse(data);

            if(response != '' && response != null) {
                //when new order placed at that refresh page bcoz at that time orderid is not in list.
                orderreceivelbl = "OrderRequested";
                companyId = "<?= $_SESSION['sess_iUserId']; ?>"; 
                company_iGcmRegId = "<?= $db_order_detail[0]['iGcmRegId'] ?>";
                if (orderreceivelbl==response.Message && response.iCompanyId == companyId) {
                    // location.reload();
                } else {
                    var idx = $.inArray(response.iOrderId, jsondb_order_detail);
                    if(idx!=-1) {
                        // location.reload();
                        $('#dataTables-example tr').each(function(index, value) {
                            if($(this).data('orderid') == response.iOrderId) {
                                // console.log("searchOrderStatus: " + searchOrderStatus);
                                if(searchOrderStatus == "1") {
                                    $(this).remove();    
                                }
                                else if(response.Message == "OrderConfirmByRestaurant") {
                                    var action_html = '<button onclick="return openDriverTypeModal(\'' + response.iOrderId + '\',\'' + companyId + '\',\'' + company_iGcmRegId + '\',\'' + response.iUserId + '\');" class="btn btn-sm gen-btn"><?php echo $langage_lbl['LBL_ASSIGN_TO_DRIVER']; ?></button>';

                                    $(this).find('td[data-col="action"]').html(action_html);
                                    $(this).find('td[data-col="status"]').html("<?= $lbl_accepted_store ?>");
                                }
                                else if(response.Message == "CabRequestAccepted") {
                                    if(searchOrderStatus == "2") {
                                        $(this).remove();    
                                    }
                                    else {
                                        var action_html = '-';
                                        $(this).find('td[data-col="action"]').html(action_html);
                                        $(this).find('td[data-col="status"]').html("<?= $lbl_accepted_driver ?>");
                                        $(this).data('accepted', 'Yes');
                                    }
                                }
                                else if(response.Message == "OrderPickedup") {
                                    if(searchOrderStatus == "4") {
                                        $(this).remove();    
                                    }
                                    else {
                                        var action_html = '-';
                                        $(this).find('td[data-col="action"]').html(action_html);
                                        $(this).find('td[data-col="status"]').html("<?= $lbl_pickedup_driver ?>");
                                    }
                                }
                                else if(response.Message == "OrderDeclineByRestaurant" || response.Message == "OrderDelivered") {
                                    $(this).remove();  
                                }
                            }
                        });
                    }
                }
            }
        });
    });
    //reassign driver - for that reset current driver
    function openResetDriverTypeModal(elem) {
        var orderId = $(elem).attr("data-id");
        var drivertypesel = $(elem).attr("data-drivertype");
        $('#DriverResetTypeModal').addClass('active');
        $("#iOrderIdResetType").val(orderId);
    }
    function openResetDriverModal(){
        var orderId = $("#iOrderIdResetType").val();
        $('#resetconfirmshow').hide();
        $('#resetconfirmhide').show();
        
        var resetreqparam = {
            "tSessionId": "<?= $driversessionid ?>",
            "GeneralMemberId": "<?= $driverId ?>",
            "GeneralUserType": 'Driver',
            "type": 'cancelDriverOrder',
            "iOrderId": orderId,
            "eSystem": "DeliverAll",
            "isFromAdmin": "Yes",
            "async_request": false
        };
        resetreqparam = $.param(resetreqparam);
        $("#loaderIcon").show();
        
        getDataFromApi(resetreqparam, function(response) {
            response_data = response.result;
            $('#resetconfirmshow').show();
            $('#resetconfirmhide').hide();
            if(response_data.Action == 1) {
                location.reload();
            }
            else {
                $('#resetconfirmshow').show();
                $('#resetconfirmhide').hide();
            }
        });
    }
</script>
<!-- End: Footer Script -->
</body>
</html>