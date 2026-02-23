<?php
include_once('common.php');

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);

if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
} else{
	  $vLang = $default_lang;
}

$script="Manageorder";

$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');
$ssql='';

$paidtype = (isset($_REQUEST['paidStatus']) && $_REQUEST['paidStatus'] != '') ? $_REQUEST['paidStatus'] : $langage_lbl['LBL_NEW'];

$class1 = $class2 = $class3 = '';
if ($paidtype == $langage_lbl['LBL_NEW']) {
    $class1 = 'active';
    $OrderArray = array('1');
} else if ($paidtype == $langage_lbl['LBL_PROCESSING']) {
    $class2 = 'active';
    $OrderArray = array('2','4');
} else {
    $class3 = 'active';
   $OrderArray = array('5');
}

$iStatusCode = '('.implode(',',$OrderArray).')';


$sql = "SELECT o.iOrderId,o.vOrderNo,o.tOrderRequestDate,o.fNetTotal,o.iDriverId,o.eBuyAnyService,o.fTotalGenerateFare,o.fRestaurantPayAmount,o.fRestaurantPaidAmount,o.iStatusCode,o.vTimeZone,o.fCommision,o.fDeliveryCharge,o.fOffersDiscount,u.vCountry, Concat(u.vName,' ',u.vLastName) as Username,c.vCompany,os.vStatus_".$vLang." as vStatus,(select count(od.iOrderId) from order_details as od where od.iOrderId = o.iOrderId) as TotalItem,o.eTakeaway,o.fTipAmount From orders as o LEFT JOIN company as c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os ON os.iStatusCode = o.iStatusCode LEFT JOIN register_user as u ON u.iUserId = o.iUserId WHERE o.iCompanyId = '".$_SESSION['sess_iUserId']."' AND o.iStatusCode IN $iStatusCode ".$ssql." AND IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') GROUP BY o.iOrderId ORDER BY o.iOrderId DESC ";

$db_order_detail = $obj->MySQLSelect($sql);
$totalOrders = scount($db_order_detail);

if(file_exists($logogpath."driver-view-icon.png")){
    $invoice_icon = $logogpath."driver-view-icon.png";
}else{
    $invoice_icon = "assets/img/driver-view-icon.png";
}

if(file_exists($logogpath."canceled-invoice.png")){
 $canceled_icon = $logogpath."canceled-invoice.png";   
}else{
 $canceled_icon = "assets/img/canceled-invoice.png";   
}


/*if ($action == 'view') {*/	
	$sql = "SELECT f.*,c.vCompany,c.tSessionId,c.iServiceId,(select count(iMenuItemId) from menu_items where iFoodMenuId = f.iFoodMenuId AND eStatus != 'Deleted') as MenuItems FROM  `food_menu` as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId  WHERE 1=1 AND f.iCompanyId = '" . $_SESSION['sess_iUserId'] . "' AND f.eStatus != 'Deleted' $dri_ssql";
	$data_drv = $obj->MySQLSelect($sql);
/*}*/
$sqlc = "SELECT eAvailable,tSessionId,iServiceId FROM company WHERE iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
$result_company = $obj->MySQLSelect($sqlc);

$sql = "SELECT vTitle_" . $vLang . " as vTitle,iCancelReasonId FROM cancel_reason WHERE eStatus = 'Active' AND eType = 'DeliverAll' AND (eFor = 'Company' OR eFor='General')";
$CancelReasonData = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_ORDER']; ?></title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?><style type="text/css">.button-block a.active.gen-btn{background-color: gray;}</style>
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

			<section class="profile-section my-trips">
			    <div class="profile-section-inner">
			        <div class="profile-caption">
			            <div class="page-heading">
			                <h1>Order</h1>
			            </div>
						<!-- <div class="button-block end">
						<a href="javascript:void(0);" class="gen-btn" onClick="add_food_form();"><?= $langage_lbl['LBL_ACTION_ADD']; ?> <?=$langage_lbl['LBL_FOOD_CATEGORY_FRONT']; ?></a>
			            </div>	 -->		
			        </div>
			    </div>
			</section>

			
			<section class="profile-earning">
                <div class="profile-earning-inner">
                    <div class="table-holder">
                        <div class="page-contant">
                            <div class="page-contant-inner">
                                <!-- trips page -->
                                <!-- <div class="trips-page"> -->
                                <form name="frmreview" id="frmreview" method="post" action="">
                                    <input type="hidden" name="paidStatus" value="" id="paidStatus">
                                    <input type="hidden" name="action" value="" id="action">
                                    <input type="hidden" name="iRatingId" value="" id="iRatingId">
                                </form>

                                <div class="trips-table">
                                    <div class="payment-tabs">
                                        <div class="button-block">
                                        	<div class="threebtn">
	                                            <a href="javascript:void(0);" onClick="getReview('<?= $langage_lbl['LBL_NEW']; ?>');" class="<?= $class1; ?> gen-btn" ><?= $langage_lbl['LBL_NEW']; ?></a>
	                                            <a href="javascript:void(0);" onClick="getReview('<?= $langage_lbl['LBL_PROCESSING']; ?>');" class="<?= $class2; ?> gen-btn"><?= $langage_lbl['LBL_PROCESSING']; ?></a>
	                                            <a href="javascript:void(0);" onClick="getReview('<?= $langage_lbl['LBL_DISPATCHED']; ?>');" class="<?= $class3; ?> gen-btn"><?= $langage_lbl['LBL_DISPATCHED']; ?></a>
	                                            
                                            </div>
	                                        <div class="toggle-combo" style="position: relative;display: inline-block;">

		                                        <form name="availability" method="post" action="" style="display: inline-flex;">
		                                            <label><?php echo $langage_lbl['LBL_ACCEPTING_ORDERS']; ?></label>
			                                        <span class="toggle-switch">
			                                            <input type="checkbox" onchange="check_box_value_checked(this.value);" id="eAvailabilty" class="chk" name="eAvailable" <?php if ($result_company[0]['eAvailable'] == 'Yes') { ?>checked<?php } ?> value="Yes" />
			                                            <span class="toggle-base"></span>
			                                        </span>
			                                    </form>
			                                    <!--<a href="#" class="gen-btn" id="myLink"><?= $langage_lbl['LBL_REFRESH']; ?></a>-->
	                                        </div>
                                        </div>
                                    </div>
                                    <div class="trips-table-inner">
                                        <div class="driver-trip-table">
                                            <form  name="frmbooking" id="frmbooking" method="post" action="">
                                                <input type="hidden" id="type" name="type" value="<?= $type; ?>">
                                                <input type="hidden" id="action" name="action" value="send_equest">
                                                <?php if ($_REQUEST['success'] == 1) { ?>
                                                    <div class="alert alert-success alert-dismissable">
                                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                                                        <?= $var_msg ?>
                                                    </div>
                                                <?php } else if ($_REQUEST['success'] == 2) { ?>
                                                    <div class="alert alert-danger alert-dismissable">
                                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                                        <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                                                    </div>
                                                <?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 0) {
                                                    ?>
                                                    <div class="alert alert-danger alert-dismissable">
                                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                                                        <?= $var_msg ?>
                                                    </div>
                                                <?php }
                                                ?>
                                                <table width="100%" border="0" class="ui celled table custom-table dataTable no-footer"  cellpadding="0" cellspacing="1" id="dataTables-example">
								          			<thead>
														<tr>
															<th><?=$langage_lbl['LBL_ORDER_NO_TXT'];?></th>		
															<th><?=$langage_lbl['LBL_ORDER_DATE_TXT']; ?></th>
						        							<th><?=$langage_lbl['LBL_PASSENGER_NAME_TEXT_DL']; ?></th>
															<th><?=$langage_lbl['LBL_TOTAL_ITEM_TXT']; ?></th>
															<th><?=$langage_lbl['LBL_ORDER_STATUS_TXT']; ?></th>
															<th><?=$langage_lbl['LBL_VIEW_DETAIL_TXT']; ?></th>
														</tr>
													</thead>
													<tbody>
													<?php 
													$systemTimeZone = date_default_timezone_get();
	                                                $db_records = $obj->MySQLSelect("SELECT iOrderId,count(CASE WHEN eStatus = 'Accept' THEN iDriverId END) as total_accept,max(tDate) as ttDate,count(iOrderId) as corder  FROM driver_request  WHERE 1 = 1 GROUP BY iOrderId ORDER BY  `tDate` DESC");
	                                                $orderDataArr = array();
	                                                for ($r = 0; $r < scount($db_records); $r++) {
	                                                    $orderDataArr[$db_records[$r]['iOrderId']] = $db_records[$r];
	                                                }
														for($i=0;$i<scount($db_order_detail);$i++)
														{
															$iOrderId = $db_order_detail[$i]['iOrderId'];
                                                    	$iOrderStatusCode = $db_order_detail[$i]['iStatusCode'];
															$iOrderIdnew = $db_order_detail[$i]['iOrderId'];
															$getUserCurrencyLanguageDetails = getCompanyCurrencyLanguageDetailsWeb($_SESSION['sess_iUserId'],$iOrderIdnew);
															$Ratio = $getUserCurrencyLanguageDetails['Ratio'];
															
															$systemTimeZone = date_default_timezone_get();
															if($db_order_detail[$i]['tOrderRequestDate']!= "" && $db_order_detail[$i]['vTimeZone'] != "")  {
																$tOrderRequestDate = converToTz($db_order_detail[$i]['tOrderRequestDate'],$db_order_detail[$i]['vTimeZone'],$systemTimeZone);
															} else {
																$tOrderRequestDate = $db_order_detail[$i]['tOrderRequestDate'];
															}
														?>
														<tr class="gradeA">
															<td align="center">
																<?=$db_order_detail[$i]['vOrderNo'];?>
																<?= $db_order_detail[$i]['eTakeaway'] == 'Yes' ? '<br><span class="grey-color">'.$langage_lbl['LBL_TAKE_AWAY'].'</span>' : ''?>
															</td>
															<td data-order="<?php echo $tOrderRequestDate; ?>"><?= DateTime1($tOrderRequestDate,'yes');?></td>
															<td align="center"><?= clearName($db_order_detail[$i]['Username']);?></td>
															<td align="center"><?=$db_order_detail[$i]['TotalItem'];?></td>
															<td align="center"><?= str_replace("#STORE#", $db_order_detail[$i]['vCompany'], $db_order_detail[$i]['vStatus']); ?></td>
															<td align="center" width="10%">
															  <?php if($db_order_detail[$i]['iStatusCode'] == '1'){ ?>
															  	<button  href="#" class="gen-btn confirmbutton" onclick="openConfirmModal(this);" data-id="<?= $db_order_detail[$i]['iOrderId']; ?>" type="button" style="padding: 5px;font-size: 14px;background-color: green"><?= $langage_lbl['LBL_CONFIRM_TXT']; ?></button>
															  	<button  href="#" class="gen-btn declinebutton" onclick="openDeclineModal(this);" data-id="<?= $db_order_detail[$i]['iOrderId']; ?>" type="button" style="padding: 5px;font-size: 14px;"><?= $langage_lbl['LBL_DECLINE_TXT']; ?></button>
															  <?php	} ?>

															  <?php if ($db_order_detail[$i]['iStatusCode'] == '2' || $db_order_detail[$i]['iStatusCode'] == '4'){
	                                                            $currentdate = @date('Y-m-d H:i:s');
	                                                            $total_accept = $corder = $cabbook = 0;
	                                                            $checkdate = "";
	                                                            $vCountry = $db_order_detail[$i]['vCountry'];
															   //$eOrderType = $db_order_detail[$i]['eOrderType'];
															
                                                            //if ($iOrderStatusCode == 2) {
														
																/*if (isset($orderDataArr[$iOrderId])) {
                                                                    $tDate = $orderDataArr[$iOrderId]['ttDate'];
                                                                    $corder = $orderDataArr[$iOrderId]['corder'];
                                                                    $total_accept = $orderDataArr[$iOrderId]['total_accept'];
                                                                }
                                                                $checkdate = date('Y-m-d H:i:s', strtotime("+" . $RIDER_REQUEST_ACCEPT_TIME . " seconds", strtotime($tDate)));
                                                                if ($corder == 0) {
                                                                    ?> 
                                                                    <button  href="#" class="gen-btn"  onclick="openDriverModal(this);"  data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" style="padding: 5px;font-size: 14px;">Assign to the <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?></button>
                                                                    <?php
                                                                } else {
                                                                    $checkdate;
                                                                    $currentdate = date('Y-m-d H:i:s');
                                                                    $time1 = strtotime($currentdate);
                                                                    $time2 = strtotime($checkdate);
                                                                    if ($total_accept == 0 && $time1 <= $time2) {
                                                                        ?>
                                                                        <button  href="#"  class="gen-btn"  onclick="openDriverModal(this);"   data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" style="padding: 5px;font-size: 14px;">Please wait for <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> accept request</button>
                                                                    <?php } else { ?>
                                                                        <button  href="#"   class="gen-btn" onclick="openDriverModal(this);"   data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button"   style="padding: 5px;font-size: 14px;">>Assign to the <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?></button>
                                                                        <?php
                                                                    }
                                                                }*/
                                                                if ($db_order_detail[$i]['eBuyAnyService'] == "No") {
		                                                                /*$currentdate = @date('Y-m-d H:i:s');
		                                                                $total_accept = $corder = $cabbook = 0;
		                                                                $checkdate = $tDate = "";
		                                                                $vCountry = $db_order_detail[$i]['vCountry'];*/
	                                                                if (($iOrderStatusCode == 2 && $iDriverId <= 0) || ($iOrderStatusCode == 4)) {
	                                                                    if (isset($orderDataArr[$iOrderId])) {
	                                                                        $tDate = $orderDataArr[$iOrderId]['ttDate'];
	                                                                        $corder = $orderDataArr[$iOrderId]['corder'];
	                                                                        $total_accept = $orderDataArr[$iOrderId]['total_accept'];
	                                                                    }
	                                                                    $checkdate = date('Y-m-d H:i:s', strtotime("+" . $RIDER_REQUEST_ACCEPT_TIME . " seconds", strtotime($tDate)));

	                                                                    if ($corder == 0) {
	                                                                        ?> 
	                                                                        <button href="#" onclick="openDriverModal(this);" class="gen-btn" data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" style="padding: 5px;font-size: 14px;"><?= $langage_lbl['LBL_ASSIGN_DRIVER']; ?></button>
	                                                                        <?php
	                                                                    } else {
	                                                                        $currentdate = @date('Y-m-d H:i:s');
	                                                                        $time1 = strtotime($currentdate);
	                                                                        $time2 = strtotime($checkdate);
	                                                                       
	                                                                        if ($total_accept == 0 && $time1 <= $time2) {
	                                                                            ?>
	                                                                            <button href="#" class="gen-btn" data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" style="padding: 5px;font-size: 14px;">Please wait for <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> accept request</button>
	                                                                            <!--onclick="openDriverModal(this);"-->
	                                                                        <?php }  else if ($db_order_detail[$i]['iDriverId'] > 0) { 
	                                                                        	echo '--';
	                                                                        	}else { ?>

	                                                                        	<button href="#" onclick="openDriverModal(this);" class="gen-btn" data-country="<?= $vCountry; ?>" data-id="<?= $iOrderId; ?>" type="button" style="padding: 5px;font-size: 14px;"><?= $langage_lbl['LBL_ASSIGN_DRIVER']; ?></button>

	                                                                        	<button  href="#" class="gen-btn declinebutton" onclick="openDeclineModal(this);" data-id="<?= $db_order_detail[$i]['iOrderId']; ?>" type="button" style="padding: 5px;font-size: 14px;"><?= $langage_lbl['LBL_DECLINE_TXT']; ?></button>

	                                                                        <?php }
	                                                                    }
	                                                                }
	                                                            }
                                                                ?>  	
																<?php //}
															  	}?>
															</td>		
														</tr>
													<?php } ?>		
													</tbody>
								        		</table>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div style="clear:both;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="col-lg-12">
                <div class="custom-modal-main" id="assign_driver_modalnew" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="custom-modal">
                        <div class="model-header">
                        	<h4 class="modal-title"><?= $langage_lbl['LBL_ASSIGN_DRIVER']; ?></h4>
                         	<i class="icon-close" data-dismiss="modal"></i>
                        </div>
                        <div class="map-popup" style="display:none" id="driver_popup"></div>
                        <div class="model-body">
                        	<input type="hidden" name="iOrderId" id="iOrderIdManual" value="" >
        					<input type="hidden" name="vCountry" id="vCountryManual" value="" >
                            <div class="form-group col-lg-12">
                                <!-- <label class="optional"><?= $langage_lbl['LBL_OR_TXT']; ?></label> -->
                                <span class="auto_assign001">
                                    <input type="radio" name="eAutoAssign" id="eAutoAssign" onclick="changedData('1');" checked value="Yes" >&nbsp;<?= $langage_lbl['LBL_OTHER_TXT']; ?>
                                </span>
                                <span class="auto_assign001">
                                    <input type="radio" name="eAutoAssign" onclick="changedData('2');" id="eAutoAssign1" value="No" >&nbsp;<?= $langage_lbl['LBL_PERSONAL']; ?>
                                </span>	
                                
                            </div>
                            <div class="form-group col-lg-12" style="display: inline-block;">
				                <p id="driverSet001"></p></span>
				                <ul id="driver_main_list" class="order_list_d" style="display:none;">
				                    <div class="" id="imageIcons" style="width:100%;">
				                        <div align="center">
				                            <img src="default.gif">
				                            <span> <?= $langage_lbl['LBL_RETRIEVING_WEB']." ".$langage_lbl['LBL_DIVER']; ?>.<?=$langage_lbl['LBL_PLEASE_WAIT'];?>...</span>                    
				                        </div>                                                           
				                    </div>
				                </ul>
				            </div>
                            <input type="hidden" name="iDriverId" id="iDriverId" value="" class="form-control">
                            <div class="form-group" style="display: inline-block; margin-top: 20px;">
				               <input type="button" onclick="send_req_btn()" name="submit" value="<?= $langage_lbl['LBL_SEND_REQUEST']." ".$langage_lbl['LBL_DIVER'];?>" class="gen-btn sendbtn" >
				            </div>
                       <!--  </form> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="custom-modal-main" id="declineorder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="custom-modal">
                        <div class="model-header">
                        	<h4 class="modal-title"><?= $langage_lbl['LBL_DECLINE_ORDER']; ?></h4>
                         	<i class="icon-close" data-dismiss="modal"></i>
                        </div>
                        <div class="model-body">
                        	<form action="" method="post" name="declineform" class="general-form profile_edit profile-caption active"> 
	                            <div class="form-group col-lg-12">
	                            <select name="cancelreason" id="cancelreason" class="form-group" required="required">
	                            	<option value=""><?=$langage_lbl['LBL_SELECT_REASON']?></option>
	                            	<?php foreach($CancelReasonData as $k=>$v) { ?>
	                               		<option value="<?=$v['iCancelReasonId']?>"><?=$v['vTitle']?></option>
	                               <?php } ?>
	                            </select>
	                            </div>
	                            <input type="hidden" name="iOrderId" id="declineiOrderId" value="" class="form-control">
	                            <!-- <div class="form-group" style="display: inline-block; margin-top: 20px;color: #fff"> -->
					               <input type="button" name="submit" id="myBtn" value="<?= $langage_lbl['LBL_DECLINE_TXT']; ?>" class="gen-btn" >
					            <!-- </div> -->
	                      	</form>
                        </div>
                    </div>
                </div>
            </div>
			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
            <!-- End:food menu page-->
            <div style="clear:both;"></div>
		</div>
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');?>
		<script src="assets/js/jquery-ui.min.js"></script>
		<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script type="text/javascript">
		$(function(){ // let all dom elements are loaded
			    $(document).on('click','[data-dismiss="modal"]',function(e){
			        e.preventDefault();
			       $('.declinebutton').prop('disabled', false);
			    });
	
		});
            function check_box_value_checked(val1)
            {
            	$("input#eAvailabilty").attr("disabled", true); //disable input
                if ($('#eAvailabilty').is(':checked'))
                {
                	var data = {

			            "tSessionId": '<?= $data_drv[0]['tSessionId'];?>',

			            "GeneralMemberId": '<?= $_SESSION['sess_iCompanyId'];?>',

			            "iCompanyId": '<?= $_SESSION['sess_iCompanyId'];?>',

			            "UserType": 'Company',

			            "GeneralUserType": 'Company',

			            "type": 'UpdateRestaurantAvailability',

			            "eAvailable": 'Yes',

			            "CALL_TYPE": 'Update',

			            "iServiceId": '<?= $data_drv[0]['iServiceId'];?>',

			        };
                    data = $.param(data);

			        $.ajax({

			            type: "POST",

			            dataType: "json",

			            url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

			            data: data,

			            async: false,

			            success: function (response) {
			            	$("input#eAvailabilty").removeAttr("disabled");
			            	//console.log(response.message);
			            	if(response.Action == 1){
				            	if(response.message === 'LBL_INFO_UPDATED_TXT'){
			            			alert('<?= $langage_lbl['LBL_INFO_UPDATED_TXT'];?>');
			            		} 
			            		window.location.replace("cx-order_request.php");
			            		return true;
			            	} else {
			            		if (response.message === 'LBL_TRY_AGAIN_LATER'){
			            			alert('<?= $langage_lbl['LBL_TRY_AGAIN_LATER'];?>');
			            		} if (response.message === 'LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'){
			            			alert('<?= $langage_lbl['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'];?>');
			            		} if (response.message === 'LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'){
			            			alert('<?= $langage_lbl['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'];?>');
			            		} if (response.message === 'LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'){
			            			alert('<?= $langage_lbl['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'];?>');
			            		}
			            		return false;	
			            	}
			               
			            }

			        });
                } else {
                   var data = {

			            "tSessionId": '<?= $data_drv[0]['tSessionId'];?>',

			            "GeneralMemberId": '<?= $_SESSION['sess_iCompanyId'];?>',

			            "iCompanyId": '<?= $_SESSION['sess_iCompanyId'];?>',

			            "UserType": 'Company',

			            "GeneralUserType": 'Company',

			            "type": 'UpdateRestaurantAvailability',

			            "eAvailable": 'No',

			            "CALL_TYPE": 'Update',

			            "iServiceId": '<?= $data_drv[0]['iServiceId'];?>',

			        };
                    data = $.param(data);

			        $.ajax({

			            type: "POST",

			            dataType: "json",

			            url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

			            data: data,

			            async: false,

			            success: function (response) {
			            	$("input#eAvailabilty").removeAttr("disabled");
			            	//console.log(response.message);
			            	if(response.Action == 1){
				            	if(response.message === 'LBL_INFO_UPDATED_TXT'){
			            			alert('<?= $langage_lbl['LBL_INFO_UPDATED_TXT'];?>');
			            		} 
			            		window.location.replace("cx-order_request.php");
			            		return true;
			            	} else {
			            		if (response.message === 'LBL_TRY_AGAIN_LATER'){
			            			alert('<?= $langage_lbl['LBL_TRY_AGAIN_LATER'];?>');
			            		} if (response.message === 'LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'){
			            			alert('<?= $langage_lbl['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'];?>');
			            		} if (response.message === 'LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'){
			            			alert('<?= $langage_lbl['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'];?>');
			            		} if (response.message === 'LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'){
			            			alert('<?= $langage_lbl['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'];?>');
			            		}
			            		return false;	
			            	}
			               
			            }

			        });
                }
            }

          	function openConfirmModal(elem) {
          		$('.confirmbutton').prop('disabled', true);
	          	var orderId = $(elem).attr("data-id");
	      		var data = {

		            "tSessionId": '<?= $result_company[0]['tSessionId'];?>',

		            "GeneralMemberId": '<?= $_SESSION['sess_iCompanyId'];?>',

		            "iCompanyId": '<?= $_SESSION['sess_iCompanyId'];?>',

		            "UserType": 'Company',

		            "GeneralUserType": 'Company',

		            "type": 'ConfirmOrderByRestaurant',

		            "iOrderId": orderId,

		            "ePickedUp": 'No',

		            "iServiceId": '<?= $result_company[0]['iServiceId'];?>',

		        };
                data = $.param(data);

		        $.ajax({

		            type: "POST",

		            dataType: "json",

		            url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

		            data: data,

		            async: false,

		            success: function (response) {
		            	//console.log(response);
	            		if(response.Action == 1){
			            	if(response.message === 'LBL_CONFIRM_ORDER_BY_RESTAURANT'){
		            			alert('<?= $langage_lbl['LBL_CONFIRM_ORDER_BY_RESTAURANT'];?>');
		            		} 
		            		window.location.replace("cx-order_request.php");
		            		return true;
		            	} else {
		            		if (response.message === 'LBL_CANCEL_ORDER_ADMIN_TXT'){
		            			alert('<?= $langage_lbl['LBL_CANCEL_ORDER_ADMIN_TXT'];?>');
		            		} if (response.message === 'LBL_NO_ORDERS_FOUND_TXT'){
		            			alert('<?= $langage_lbl['LBL_NO_ORDERS_FOUND_TXT'];?>');
		            		}
		            		$('.confirmbutton').prop('disabled', false);
		            		return false;	
		            	}
		            }

		        });
			}

			function openDeclineModal(elem) {
				$('.declinebutton').prop('disabled', true);
				if (confirm('Do you want to decline this order?')) {
				    var orderId = $(elem).attr("data-id");
				    $("#declineiOrderId").val(orderId);
					$('#declineorder').addClass('active');
					
				} else {
				    alert('Why did you press Decline? You should have confirmed');
				    $('.declinebutton').prop('disabled', false);
				}
			}

			function openDriverModal(elem) {
				var radioValue = $("input[name='eAutoAssign']:checked").val();
				
				if (radioValue == "No") {
					$("#eAutoAssign1").prop("checked", false);
					$("#eAutoAssign").prop("checked", true);
					changedData("1");
				}
				var orderId = $(elem).attr("data-id");
				var country = $(elem).attr("data-country");
				
				$("#iOrderIdManual").val(orderId);
				$("#vCountryManual").val(country);
				$("#driver_main_list").hide();
				$('#assign_driver_modalnew').addClass('active');
			}

			function setDriverListing(vCountry, orderId) {
				iVehicleTypeId = '';
				keyword = '';
				eLadiesRide = 'No';
				eHandicaps = 'No';
				eType = '';
				$.ajax({
					type: "POST",
					url: "get_available_driver_list_order.php",
					dataType: "html",
					data: {vCountry: vCountry, type: '', iVehicleTypeId: iVehicleTypeId, keyword: keyword, eLadiesRide: eLadiesRide, eHandicaps: eHandicaps, AppeType: eType, orderId: orderId},
					success: function (dataHtml2) {
						//console.log(dataHtml2);
						$('#driver_main_list').html('');
						if (dataHtml2 != "") {
							$('#driver_main_list').show();
							$('#driver_main_list').html(dataHtml2);
							if ($("#eAutoAssign").is(':checked')) {
								//$("input:radio").attr('disabled', 'disabled');
							}
						} else {
							$('#driver_main_list').html('<h4 style="margin:25px 0 0 15px">Sorry , No <?= $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?> Found.</h4>');
							$('#driver_main_list').show();
						}
					}, error: function (dataHtml2) {

					}
				});
			}
			function putDriverId(driverid) {
				$("#assign_driver_modalnew #iDriverId").val(driverid);
			}
			function changedData(type) {
				var country = $("#vCountryManual").val();
				var orderId = $("#iOrderIdManual").val();
				$("#driver_main_list").hide();
				if (type == "1") {
					$("#driver_main_list").hide();
					$("#driverSet001").hide();
				} else {
					$("#driver_main_list").hide();//show()
					$("#driverSet001").hide();//show()
					//setDriverListing(country, orderId);
				}
			}
			$(document).ready(function() {
				//$('#myBtn').one('click', function(){
				$("#myBtn").click(function(){
					 $('#myBtn').prop('disabled', true);
			        var orderId = $("#declineiOrderId").val();
			        var cancelreason = $("#cancelreason").val();
			        if(cancelreason == ''){
			        	alert("<?php echo $langage_lbl['LBL_RESTRICT_SEL_REASON']; ?>");
			        	$('#myBtn').prop('disabled', false);
			        	return false;
			        } else {

			        	var data = {

				            "tSessionId": '<?= $result_company[0]['tSessionId'];?>',

				            "GeneralMemberId": '<?= $_SESSION['sess_iCompanyId'];?>',

				            "iCompanyId": '<?= $_SESSION['sess_iCompanyId'];?>',

				            "UserType": 'Company',

				            "GeneralUserType": 'Company',

				            "type": 'DeclineOrder',

				            "iCancelReasonId" : cancelreason,

				            "iOrderId": orderId,

				            "ePickedUp": 'No',

				            "iServiceId": '<?= $result_company[0]['iServiceId'];?>',

				        };
		                data = $.param(data);

				        $.ajax({

				            type: "POST",

				            dataType: "json",

				            url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

				            data: data,

				            async: false,

				            success: function (response) {
			            		if(response.Action == 1){
					            	if(response.message === 'LBL_CANCEL_ORDER_ADMIN_TXT'){
				            			alert('<?= $langage_lbl['LBL_CANCEL_ORDER_ADMIN_TXT'];?>');
				            		} 
				            		if(response.message === 'LBL_ORDER_DECLINE_BY_RESTAURANT'){
				            			alert('<?= $langage_lbl['LBL_ORDER_DECLINE_BY_RESTAURANT'];?>');
				            		} 
				            		window.location.replace("cx-order_request.php");
				            		return true;
				            	} else {
				            		if (response.message === 'LBL_NO_ORDERS_FOUND_TXT'){
				            			alert('<?= $langage_lbl['LBL_NO_ORDERS_FOUND_TXT'];?>');
				            		}
				            		$('#myBtn').prop('disabled', false);
				            		return false;	
				            	}
				            }

				        });
			        }
			    });
			});

			function send_req_btn() {
				$('.sendbtn').prop('disabled', true);
				$.ajax({
					type: "POST",
					url: "get_available_driver_list_order.php",
					dataType: "json",
					data: {orderId: $("#iOrderIdManual").val(),requestsent:1},
					success: function (dataHtml2) {
						sendrequesttodriver(dataHtml2);
					}
				});
			}
			function sendrequesttodriver(dataHtml2) {
			    radioValue = $("input[name='eAutoAssign']:checked").val();
			    if (radioValue=='No') {
			        driverid = $("#assign_driver_modalnew #iDriverId").val(); 
			        eDriverType = 'personal';  
			    } else {
			        driverid = '';
			        eDriverType = 'site'; 
			    }
			    /*if (radioValue == 'No' && driverid == '') {
			    	 alert("Please select the driver.");
			    	 return false;
			    }*/
			    var sendrequestparam = {
			        "tSessionId": dataHtml2.tSessionId,
			        "GeneralMemberId": dataHtml2.GeneralMemberId,
			        "GeneralUserType": 'Passenger',
			        "type": 'sendRequestToDrivers',
			        "iOrderId": $("#iOrderIdManual").val(),
			        "eSystem": dataHtml2.eSystem,
			        "vDeviceToken": dataHtml2.vDeviceToken,
			        "iDriverIdWeb": driverid,
			        "iServiceId": '<?= $data_drv[0]['iServiceId'];?>',
			        "eDriverType": eDriverType
			    };
			    sendrequestparam = $.param(sendrequestparam);
			    // $("#loaderIcon").show();
			    $.ajax({

			        type: "POST",

			        dataType: "json",

			        async: false,

			        url: "<?= $tconfig["tsite_url"] . ManualBookingAPIUrl; ?>",

			        data: sendrequestparam,

			        success: function (response) {
			        	
			            if (response.Action == '1') {

			                alert("<?php echo $langage_lbl['LBL_REQ_IN_PROCESS']; ?>");
			                window.location.replace("cx-order_request.php");
				            return true;

			            } else {

			                //alert(response.Message);
			                if (response.message == 'NO_CARS'){
			                	alert("<?php echo $langage_lbl['LBL_NO_DRIVER_FOUND']; ?>");
			                } else if(response.message == 'SESSION_OUT'){
			                	alert("<?php echo $langage_lbl['LBL_SESSION_TIME_OUT']; ?>");
			                }

			                $("#request-loader001").hide();

			                $("#requ_title").hide();

			            }
			            $('.sendbtn').prop('disabled', false);
			        }
			    });
			}
			$(document).ready(function () {
				$('#dataTables-example').dataTable({
					"oLanguage": langData,
					"aaSorting": [],
				});
				$("#myLink").click(function() {
				    window.location.reload();
				});
			});
		</script>
		<script type="text/javascript">
            $(document).ready(function () {
                $("[name='dataTables-example_length']").each(function () {
                    $(this).wrap("<em class='select-wrapper'></em>");
                    $(this).after("<em class='holder'></em>");
                });
                $("[name='dataTables-example_length']").change(function () {
                    var selectedOption = $(this).find(":selected").text();
                    $(this).next(".holder").text(selectedOption);
                }).trigger('change');
            });
            function getReview(type)
            {
                window.history.pushState(null, null, window.location.pathname);
                $('#paidStatus').val(type);
                //  window.location.href = "payment_request.php?paidStatus="+type;
                
                document.frmreview.submit();
            }
 
        </script>
		<!-- End: Footer Script -->
	</body>
</html>
