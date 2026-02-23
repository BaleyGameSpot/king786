<?php

	
	include_once('common.php');
	
	
	if(isset($_REQUEST['refcode']))
	{

		echo $REFERRAL_OBJ->ValidateReferralCode($_REQUEST['refcode']);
		exit;
			
		
	}
?>