<?
include_once("../common.php");




$iUserId = isset($_REQUEST['iUserId'])?$_REQUEST['iUserId']:''; 
$trackingCompany = isset($_REQUEST['trackingCompany'])?$_REQUEST['trackingCompany']:'';
// clearName(

$sql="select ru.iUserId,concat(ru.vName,' ',ru.vLastName) as Name,ru.vEmail,ru.vPhoneCode,ru.vPhone,ru.vImage,ru.eStatus,cn.vCountry as country from track_service_users ru left join country cn on cn.vCountryCode = ru.vCountry  where iTrackServiceUserId = '$iUserId'";
$data_user = $obj->MySQLSelect($sql);
 

$reg_date1 = $data_user[0]['tRegistrationDate'];
if($reg_date1 != "0000-00-00 00:00:00"){
	//$reg_date = date("l, M d \<\s\u\p\>S\<\/\s\u\p\>\ Y",strtotime($reg_date1));
	$reg_date = DateTime($reg_date1);
}else{
	$reg_date = "";
}

if($data_user[0]['vImage'] != "" && file_exists($tconfig["tsite_upload_images_track_company_user_path"].'/'.$iUserId.'/2_'.$data_user[0]['vImage']))
	$image_path = $tconfig["tsite_upload_images_track_company_user"].'/'.$iUserId.'/2_'.$data_user[0]['vImage'];
else{
	$image_path = "../assets/img/profile-user-img.png";
}


$rating_width = ($data_user[0]['vAvgRating'] * 100) / 5;
if($data_user[0]['vAvgRating'] > 0){
	$Rating = '<span title="'.$data_user[0]['vAvgRating'].'" style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
	<span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
	</span>';
}else{
	//$Rating = "No ratings received";
	$Rating = '<span title="'.$data_user[0]['vAvgRating'].'" style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
	<span style="margin: 0;float:left;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
	</span>';
}
?>
<style>
.text_design{
	font-size: 12px;
	font-weight: bold;
	font-family: verdana;
}
.border_table{
	border:1px solid #dddddd;
}
.no-cursor{
    cursor: text;
}
</style>
	
	<table border="1" class="table table-bordered" width="100%" align="center" cellspacing="5" cellpadding="10px" >
		<tbody>
		<tr>
			<td rowspan="3" height="150px" width="150px" ><img width="150px" src="<?=$image_path?>"></td>
			<td>
				<table border="0" width="100%" height="150px" cellspacing="5" cellpadding="5px">
					<tr>
						<td width="140px" class="text_design">Name</td>
						<td><?=clearName($data_user[0]['Name'])?></td>
					</tr>
					<tr>
						<td class="text_design">Email</td>
						<td><?=clearEmail($data_user[0]['vEmail'])?></td>
					</tr>
					<? if($data_user[0]['vPhone'] != ""){ ?>
					<tr>
						<td class="text_design">Phone Number</td>
						<td>
							<?php
								$phone = "(+";
								if($data_user[0]['vPhoneCode'] != ""){
									$phone .= $data_user[0]['vPhoneCode'].") ";
								}
								$phone .= clearPhone($data_user[0]['vPhone']);
								echo $phone;
							?>
						</td>
					</tr>
					<? } ?>
					<tr>
						<td class="text_design">Rating</td>
						<td><?=$Rating?></td>
					</tr>
					<tr>
						<td class="text_design">Status</td>
						<td>
							<?
								$class="";
								if($data_user[0]['eStatus'] == "Active"){
									$class = "btn-success";
								}else if($data_user[0]['eStatus'] == "Inactive"){
									$class = "btn";
								}else{
									$class = "btn-danger";
								}
							?>
							<button class="btn <?=$class?> no-cursor"><?=ucfirst($data_user[0]['eStatus'])?></button>
						</td>
					</tr>
					
				</table>
			</td>
		</tr><tr></tr><tr></tr><tr></tr>
		<? if($data_user[0]['country'] != ""){ ?>
		<tr>
			<td class="text_design">Country</td>
			<td>
				<?=$data_user[0]['country'];?>	
			</td>
		</tr>
		<? } ?>
		 <? if($reg_date != ""){?>
					<tr>
						<td width="150px" class="text_design">Registration Date</td>
						<td><?=$reg_date?></td>
					</tr>
					<? } ?> 
		 
		</tbody>
	</table>
</div>
<div class="modal-footer"> 
	<a href="track_service_user_action.php?id=<?= $iUserId; ?>" class="btn btn-primary btn-ok" target="blank">Edit <?php echo $langage_lbl_admin['LBL_RIDER']; ?></a>
 	<button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
</div>
		