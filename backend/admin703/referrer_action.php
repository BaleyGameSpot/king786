<?php
include_once('../common.php');

if($REFERRAL_SCHEME_ENABLE == "No"){
	
header('Location: dashboard.php'); exit;

}

if($MODULES_OBJ->isEnableMultiLevelReferralSystem())
{
	header('Location: multi_level_referrer_action.php?'.http_build_query($_GET)); exit;
}
$script = 'view-referrer';  	
	
$id =$_REQUEST['id'];
$etype ="";	
$type=(isset($_REQUEST['eUserType'])?$_REQUEST['eUserType']:'');

if($type == 'Driver'){
	$tablename ='register_driver';
	$iUserId = "iDriverId";
}else{	
	$tablename = 'register_user';
	$iUserId = 'iUserId';
}	

$query = "SELECT concat(vName, ' ' ,vLastName) as MemberName FROM ".$tablename." WHERE ".$iUserId." = '".$id."' ";			
$result = $obj->MySQLSelect($query);	
$MemberName = clearName($result[0]['MemberName']);

if($type == 'Driver'){

	$q1 = "SELECT rd.vName,rd.vLastName,concat(rd.vName, ' ' ,rd.vLastName) as OrgName,rd.eRefType,rd.iDriverId,rd.iRefUserId,rd.dRefDate FROM register_driver as rd LEFT JOIN register_driver as rd1 on rd1.iDriverId=rd.iRefUserId WHERE rd.iRefUserId = '".$id."'";
	$result_driver = $obj->MySQLSelect($q1);

	$q2 = "SELECT ru.vName,ru.vLastName,concat(ru.vName, ' ' ,ru.vLastName) as OrgName,ru.eRefType, ru.iUserId,ru.iRefUserId,ru.dRefDate FROM register_user as ru LEFT JOIN register_driver as rd1 on rd1.iDriverId=ru.iRefUserId WHERE ru.iRefUserId = '".$id."'";
	$result_rider = $obj->MySQLSelect($q2);
} else {

	$q3 = "SELECT rd1.vName,rd1.vLastName,concat(rd1.vName, ' ' ,rd1.vLastName) as OrgName,ru.eRefType,rd1.iDriverId,rd1.iRefUserId,rd1.dRefDate FROM register_user as ru LEFT JOIN register_driver as rd1 on rd1.iRefUserId=ru.iUserId WHERE rd1.iRefUserId = '".$id."' AND rd1.eRefType = 'Rider'";
	$result_driver = $obj->MySQLSelect($q3);

    $q4 = "SELECT ru.vName,ru.vLastName,concat(ru.vName, ' ' ,ru.vLastName) as OrgName,ru.eRefType, ru.iUserId,ru.iRefUserId,ru.dRefDate FROM register_user as ru LEFT JOIN register_user as ru1 on ru1.iUserId=ru.iRefUserId WHERE ru.iRefUserId = '".$id."' AND ru.eRefType = 'Rider'";
    $result_rider = $obj->MySQLSelect($q4);
}
$referrerDataNew = array_merge($result_driver,$result_rider);

$referrerSql = "SELECT iUserId,eUserType,fromUserId,fromUserType,dDate,iBalance,iTripId FROM user_wallet WHERE iUserId = $id AND eFor = 'Referrer' AND fromUserId = 0";
$referrerData = $obj->MySQLSelect($referrerSql);

$all_referrer_data = array();
foreach ($referrerData as $referrer) 
{

	$refSql = "SELECT t.iDriverId,concat(rd.vName, ' ' ,rd.vLastName) as referrer_name FROM trips as t LEFT JOIN register_driver as rd on rd.iDriverId= t.iDriverId WHERE rd.iRefUserId = $id AND eRefType = '".$type."'" ;
	$refData = $obj->MySQLSelect($refSql);
	
	if(empty($refData)){
		$refSql = "SELECT t.iUserId,concat(ru.vName, ' ' ,ru.vLastName) as referrer_name FROM trips as t LEFT JOIN register_user as ru on ru.iUserId= t.iUserId WHERE ru.iRefUserId = $id AND eRefType = '".$type."'" ;
		$refData = $obj->MySQLSelect($refSql);
	}


	$fromUserId = $referrer['iUserId'];
	$fromUserType = $referrer['fromUserType'];
	$iBalance = $referrer['iBalance'];
	$fromUserName = $refData[0]['referrer_name'];
	$referrerDate = date('jS F Y', strtotime($referrer['dDate']));

	$all_referrer_data[] = array(
		'fromUserId'	=> $fromUserId,
		'fromUserType'	=> $fromUserType,
		'fromUserName'	=> $fromUserName,
		'referrerDate'	=> $referrerDate,
		'iBalance'	=> $iBalance
	);
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>Admin | Referrer</title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

          <? include_once('global_files.php');?>
		  <style type="text/css">
		  	/* Style the tab */
		    .tab {
		      overflow: hidden;
		      border: 1px solid #ccc;
		      background-color: #f1f1f1;
		    }

		    /* Style the buttons that are used to open the tab content */
		    .tab button {
		      background-color: inherit;
		      float: left;
		      border: none;
		      outline: none;
		      cursor: pointer;
		      padding: 14px 16px;
		      transition: 0.3s;
		    }

		    /* Change background color of buttons on hover */
		    .tab button:hover {
		      background-color: #ddd;
		    }

		    /* Create an active/current tablink class */
		    .tab button.active {
		      background-color: #ccc;
		    }

		    /* Style the tab content */
		    .tabcontent {
		      display: none;
		    }

		  </style>
     </head>
     <!-- END  HEAD-->
     <!-- BEGIN BODY-->
     <body class="padTop53 " >

          <!-- MAIN WRAPPER -->
          <div id="wrap">
               <? include_once('header.php'); ?>
               <? include_once('left_menu.php'); ?>

               <!--PAGE CONTENT -->
               <div id="content">
                    <div class="inner">
					
						<div class="row">
							<div class="col-lg-12">
								<h2><?=$MemberName;?>  Referral Details</h2>
								<a href="javascript:void(0);" class="back_link">
									<input type="button" value="Back to Listing" class="add-btn">
								</a>
							</div>
						</div>
						<hr />                       
						<div class="tab">
		                  <button class="tablinks referalusertab" onclick="openTabContent(event, 'referalusercontent')" id="defaultOpen"> Referred Members</button>
		                  <button class="tablinks tripcompletedtab" onclick="openTabContent(event, 'tripcompletedcontent')"> Referral Earning Details</button>
		                </div>		
						<div class="body-div tabcontent" id="referalusercontent">
							<div class="table-list">
							  <div class="row">
							       <div class="col-lg-12">
							            <div class="panel panel-default" style="border: 0;margin-bottom: 0">
							              
							                 <div class="panel-body" style="padding:0">
							                      <div class="table-responsive">
							                           <table class="table table-striped table-bordered table-hover" id="dataTables-example">
							                                <thead>
							                                    <tr>
																	<th width="35%">Referred Member Name</th>
																	<th width="35%">Member Type</th>
																	<th width="35%">Date of Referred</th>
							                                    </tr>
							                                </thead>
							                                <tbody>										 
																	
															<?php 
																 $count = scount($referrerDataNew);
																 if($count > 0){
																 for($i=0;$i<scount($referrerDataNew);$i++){ ?>
																	 <tr class="gradeA">
																		

							        									<td ><?=clearName($referrerDataNew[$i]['OrgName']);?></td> 
																		<?php 
																			$time = strtotime($referrerDataNew[$i]['dRefDate']);
																			$myFormatForView = date("jS F Y", $time);
																		?>
																		<td><?= (($referrerDataNew[$i]['iDriverId'] > 0 && !empty($referrerDataNew[$i]['iDriverId']))) ? 'Provider' : 'User' ?></td>
																		<td><?= $myFormatForView ?></td> 
																	 </tr>																 
																	 
																<?php  }
																	
																 }else{ ?>																 
																  <tr class="gradeA">
																  <td colspan ="3" align="center"> No Details Found </td> 
																  </tr>
																 
																<?php  } 	?>                                                               

							                                </tbody>
							                           </table>
													   
							                      </div>

							                 </div>
							            </div>
							       </div> <!--TABLE-END-->
							  </div>
							</div>
							 
							<!-- <div class="table-list">
							  <div class="row">
							       <div class="col-lg-12">
							            <div class="panel panel-default">
							                 <div class="panel-heading">
							                 <strong>
							                 	<?php echo $MemberName;?>'s Referral <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN'];?>   												
							                 </strong>
							                 </div>
											 </hr>
											 
							                 <div class="panel-body">
							                      <div class="table-responsive">
							                           <table class="table table-striped table-bordered table-hover" id="dataTables-example">
							                                <thead>
							                                    <tr>
																	<th width="35%">Referred Member Name</th>
																	<th width="35%">Date Referred</th>						
							                                    </tr>
							                                </thead>
							                                <tbody>                                                                 
																 <?php 
																  $count = scount($result_rider);
																 if($count > 0){
																 for($i=0;$i<scount($result_rider);$i++){ ?>										 
																	 
																	 <tr class="gradeA">
																	
							        <td ><?=clearName($result_rider[$i]['OrgRiderName']);?></td> 
																		<?php 
																			$time = strtotime($result_rider[$i]['dRefDate']);
																			$myFormatForView = date("jS F Y", $time);		 
																		?>
																		<td><?= $myFormatForView ?></td>
																	 </tr>																 
																	 
																<?php  }
																	
																 } else { ?>																 
																  <tr class="gradeA">
																  <td colspan ="3" align="center"> No Details Found </td> 
																  </tr>
																 
																<?php  } 	?> 
																           
							                                    

							                                </tbody>
							                           </table>
													   
							                      </div>

							                 </div>
							            </div>
							       </div> 
							  </div>
							</div> -->
						</div>
						

						<div class="body-div tabcontent" id="tripcompletedcontent">
							<div class="table-list">
							    <div class="row">
							        <div class="col-lg-12">
							            <div class="panel panel-default" style="border: 0;margin-bottom: 0">
							       
							                <div class="panel-body" style="padding:0">
							                    <div class="table-responsive">
							                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
							                            <thead>
							                                <tr>
							                                    <th width="35%">Referred Member Name</th>
							                                   	<th width="35%">Amount Earned</th>
						                                   		<th width="35%">Date of Received</th>
							                                </tr>
							                            </thead>
							                            <tbody>
							                                <?php if(scount($all_referrer_data) > 0) { ?>
							                                <?php $countRef = 1; foreach ($all_referrer_data as $referrer_data) { ?>
							                                	<tr class="gradeA">
							                                        <td>
							                                        	<!-- <div class="panel-group pull-left full-width" id="accordion<?= $countRef ?>" role="tablist" aria-multiselectable="true">
																		    <div class="panel panel-default">
																		        <div class="panel-heading pull-left full-width cursor-pointer referrer-list collapsed" role="tab" id="heading<?= $countRef ?>" data-toggle="collapse" data-parent="#accordion<?= $countRef ?>" href="#collapse<?= $countRef ?>" aria-expanded="true" aria-controls="collapse<?= $countRef ?>">
																		            <h4 class="panel-title pull-left">
																		                <a role="button" data-toggle="collapse" data-parent="#accordion<?= $countRef ?>" href="#collapse<?= $countRef ?>" aria-expanded="true" aria-controls="collapse<?= $countRef ?>"> -->
																		                <?= clearName($referrer_data['fromUserName']);?>
																		            <!--     </a>
																		            </h4>
																		            <i class="fa fa-chevron-down pull-right"></i>
																		        </div>
																		        <div id="collapse<?= $countRef ?>" class="panel-collapse collapse pull-left full-width" role="tabpanel" aria-labelledby="heading<?= $countRef ?>">
																		            <div class="panel-body">
																		            	<ul class="list-group">
																		            	<?php $referrer_data['fromRefData'] = array_reverse($referrer_data['fromRefData']); ?>
																		                <?php foreach ($referrer_data['fromRefData'] as $fromRef) { ?>
																		                	<li class="list-group-item"><?= clearName($fromRef['name']); ?> (<?= ($fromRef['eUserType'] == 'Rider') ? 'User' : 'Provider' ?>)</li>
																		                <?php } ?>
																		                </ul>
																		            </div>
																		        </div>
																		    </div>
																		</div> -->
							                                        </td>
							                                        <td><?= ($referrer_data['iBalance'] > 0) ? formateNumAsPerCurrency($referrer_data['iBalance'],'') : '--'; ?></td>
							                                      <td><?= $referrer_data['referrerDate'] ?></td>
							                                    </tr>
							                                <?php $countRef++; } ?>
							                                
							                                <?php } else{ ?>
							                                    <tr class="gradeA">
							                                        <td colspan ="2"> No Details Found </td>
							                                    </tr>
							                                <?php } ?>
							                            </tbody>
							                        </table>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div>
							</div>
						</div>

                    </div>
               </div>

               <!--END PAGE CONTENT -->
          </div>
          <!--END MAIN WRAPPER -->


          <? include_once('footer.php');?>
          <script>
			function confirm_delete(action,id)
			{
					 //alert(action);alert(id);
				 var confirm_ans = confirm("Are You sure You want to Delete this Rider?");
					   //alert(confirm_ans);
				 if(confirm_ans=='false')
					 {
						return false;
						}
					 else
					 {
						 $('#action').val(action);
						 $('#iRatingId').val(id);
						 document.frmreview.submit();
					}
													 
			 }
			 function getReview(type)
			{
				
				$('#reviewtype').val(type);
				document.frmreview.submit();
					
			}
			
			$(document).ready(function() {
				var referrer;
				referrer =  document.referrer;
				if(referrer == "") {
					referrer = "referrer.php";
				}
				$(".back_link").attr('href',referrer);
			});
			
			// Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();


        function openTabContent(evt, Pagename) {

	      var i, tabcontent, tablinks;

	      tabcontent = document.getElementsByClassName("tabcontent");
	      for (i = 0; i < tabcontent.length; i++) {
	        tabcontent[i].style.display = "none";
	      }

	      tablinks = document.getElementsByClassName("tablinks");
	      for (i = 0; i < tablinks.length; i++) {
	        tablinks[i].className = tablinks[i].className.replace(" active", "");
	      }

	      document.getElementById(Pagename).style.display = "block";
	      evt.currentTarget.className += " active";
	    }
		</script>
     </body>
     <!-- END BODY-->
</html>
