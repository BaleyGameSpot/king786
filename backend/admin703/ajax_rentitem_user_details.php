<?
include_once("../common.php");

$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';

$iRentItemPostId = isset($_REQUEST['iRentItemPostId']) ? $_REQUEST['iRentItemPostId'] : '';

$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';

$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : 'RentItem';

$sql="select iUserId,eStatus from rentitem_post where iRentItemPostId = '$iRentItemPostId' ";

$data_user = $obj->MySQLSelect($sql);

 ?> 


<?php if($eStatus != 'Reject' && $eStatus != 'Deleted'){ ?>
 <form name="frmfeatured" id="frmfeatured" action="" method="post">
  	<input type="hidden" name="iUserId" value="<?php echo $data_user[0]['iUserId']; ?>" >
	<input type="hidden" name="eStatus1" value="<?php echo $eStatus;?>" >
	<input type="hidden" name="iRentItemPostId" value="<?php echo $iRentItemPostId;?>" >
	<input type="hidden" name="action" value="statusupdate" >
	<input type="hidden" name="eType" value="<?php echo $eType;?>" >
				
	<div class="modal-footer">
		<button type="button" class="btn btn-ok" data-dismiss="modal">Not Now</button>
		<button type="submit" class="save" id="<?php echo $eStatus;?>">
			<i class="<?= ($data_user[0]['eStatus'] == "Pending") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i>&nbsp;Yes
		</button>
	</div>  
</form>
<?php } else if($eStatus == 'Deleted'){ ?>
<form name="frmfeatured" id="frmfeatured" action="" method="post">
  	<input type="hidden" name="iUserId" value="<?php echo $data_user[0]['iUserId']; ?>" >
	<input type="hidden" name="eStatus1" value="<?php echo $eStatus;?>" >
	<input type="hidden" name="iRentItemPostId" value="<?php echo $iRentItemPostId;?>" >
	<input type="hidden" name="action" value="statusupdate" >
	<input type="hidden" name="eDeletedBy" value="Admin" >
	<input type="hidden" name="eType" value="<?php echo $eType;?>" >
	<label for="vDeletedReason">Delete Reason: </label>
    <br/>
    <textarea name="vDeletedReason" class="form-control1" id="vDeletedReason<?= $iRentItemPostId; ?>" rows="4" cols="40" required="required" style="resize: both !important;width: 100%;"></textarea>
	<div class="modal-footer">
		<button type="button" class="btn btn-ok" data-dismiss="modal">Not Now</button>
		<button class="save" id="deleted">
			<i class="<?= ($data_user[0]['eStatus'] == "Pending") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i>&nbsp;Yes
		</button>
	</div>  
</form>

<?php } else if($eStatus == 'Reject') {  ?>
	<form role="form" name="reject_form" id="reject_form<?php echo $iRentItemPostId?>" method="post" action="" class="margin0">
		<input type="hidden" name="iUserId" value="<?php echo $data_user[0]['iUserId']; ?>" >
		<input type="hidden" name="eStatus1" value="<?php echo $eStatus;?>" >
		<input type="hidden" name="iRentItemPostId" value="<?php echo $iRentItemPostId;?>" >
		<input type="hidden" name="action" value="statusupdate" >
		<input type="hidden" name="eType" value="<?php echo $eType;?>" >
		<label for="reject_reason">Reject Reason: </label>
		<br/>
		<textarea name="reject_reason" class="form-control1 reject_reason" id="reject_reason<?= $iRentItemPostId; ?>" rows="4" cols="40" required="required" style="resize: both !important;width: 100%;"></textarea>	

		<div class="modal-footer">
			<button type="button" class="btn btn-ok" data-dismiss="modal">Not Now</button>
			<button class="save" id="<?php echo $eStatus;?>">
				<i class="<?= ($data_user[0]['eStatus'] == "Pending") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i>&nbsp;Yes
			</button>
		</div>
	</form>
<?php } else { ?>
	<input type="hidden" name="iUserId" value="<?php echo $data_user[0]['iUserId']; ?>" >
	<input type="hidden" name="eStatus1" value="<?php echo $eStatus;?>" >
	<input type="hidden" name="iRentItemPostId" value="<?php echo $iRentItemPostId;?>" >
	<input type="hidden" name="action" value="statusupdate" >
	<input type="hidden" name="eType" value="<?php echo $eType;?>" >			
	<div class="modal-footer">
		<button type="button" class="btn btn-ok" data-dismiss="modal">Not Now</button>
		<button class="save" id="<?php echo $eStatus;?>">
			<i class="<?= ($data_user[0]['eStatus'] == "Pending") ? 'fa fa-check-circle' : 'fa fa-check-circle-o' ?>"></i>&nbsp;Yes
		</button>
	</div>  
<?php } ?>

<script>   
	<?php if($eStatus == 'Deleted' || $eStatus == 'Reject'){ ?>
	$("input[type=text].form-control,textarea.form-control1").keypress(function(e) {
       if (e.which === 32 && !this.value.length) {
           e.preventDefault();
       }      
   });

	$('#deleted').click(function(){
	    if($.trim($('#vDeletedReason<?= $iRentItemPostId; ?>').val()) == ''){
	      $('#vDeletedReason<?= $iRentItemPostId; ?>').val('');
	    }
	});

	$('#Reject').click(function(){
	    if($.trim($('#reject_reason<?= $iRentItemPostId; ?>').val()) == ''){
	      $('#reject_reason<?= $iRentItemPostId; ?>').val('');
	    }
	});
	<?php } ?>
	
	AOT6KSNzku();
</script>
