<?php
	
	include_once 'common.php';

	if($THEME_OBJ->isXThemeActive() == 'Yes') {
  		include_once("cx-phone_number_verification.php"); exit;
	}
				
	$_REQUEST['action']=(base64_decode(base64_decode(trim($_REQUEST['action'])))); 
	$_REQUEST['id'] = decrypt($_REQUEST['id']);	
	$action =  $_REQUEST['action']; 
	$userid = isset($_REQUEST['id'])?$_REQUEST['id']:''; 	
	$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
	$vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
	$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';

	$iRefUserId = isset($_POST['iRefUserId']) ? $_POST['iRefUserId'] : '';
	$eRefType = isset($_POST['eRefType']) ? $_POST['eRefType'] : '';

	$_SESSION['sess_iUserId'] =$userid;
	if($action == 'rider'){				
		$where = " iUserId = '$userid'";
		$table = "register_user";
	} else {
		$where = "iDriverId = '$userid'";
		$table = "register_driver";
	}
	$db_sql = "select * from $table WHERE $where";
	$db_user = $obj->MySQLSelect($db_sql);
	$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : $db_user[0]['vEmail'];		
	//echo ""; print_r($_SESSION);exit;
	//echo "<pre>"; print_r($_POST);exit;
	if (isset($_POST['submit'])) {			
		if($action == 'rider'){			
			$data['vPhone']= $vPhone;
			$data['vPhoneCode']= $vPhoneCode;
			$data['vCountry']= $vCountry;
			$data['vEmail']= $vEmail;

			$data['iRefUserId'] = $iRefUserId;
			$data['eRefType'] = $eRefType; 
			$data['dRefDate']=Date('Y-m-d H:i:s');

			$where = " iUserId = '$userid'";
			$table = "register_user";
			$_SESSION["sess_user"] = "rider";			
		}else{		
			$data['vPhone']= $vPhone;
			$data['vCode']= $vPhoneCode;
			$data['vCountry']= $vCountry;
			$data['vEmail']= $vEmail;

			$data['iRefUserId'] = $iRefUserId;
			$data['eRefType'] = $eRefType; 
			$data['dRefDate']=Date('Y-m-d H:i:s');

			$where = "iDriverId = '$userid'";
			$table = "register_driver";
			$_SESSION["sess_user"] = "driver";
		}			
		$id = $obj->MySQLQueryPerform($table,$data,'update',$where);
		/* Multi Level Referral */
		if ($iRefUserId != "")
		{
			 $refData = $obj->MySQLSelect("SELECT * FROM user_referrer_transaction WHERE iMemberId = ".$iRefUserId." AND eUserType = '".$eRefType."'");
			 if($refData[0]['tReferrerInfo'] == "") {
				  $referrerInfo[] = array('Position of Referrer' => 1, 'iMemberId' => $iRefUserId, 'eUserType' => $eRefType);
			 } else {
				  $referrerInfo = json_decode($refData[0]['tReferrerInfo'], true);
				  $referrerInfo[] = array('Position of Referrer' => (scount($referrerInfo)+1), 'iMemberId' => $iRefUserId, 'eUserType' => $eRefType);
			 }
			 $Data_Referrer['tReferrerInfo'] = json_encode($referrerInfo);
			 $Data_Referrer['iMemberId'] = $userid;
			 $Data_Referrer['eUserType'] = ($action == 'rider') ? 'Rider' : 'Driver';
			 $obj->MySQLQueryPerform("user_referrer_transaction", $Data_Referrer, 'insert');
		}
		/* Multi Level Referral End */
		if($id > 0){
		
			$db_sql = "select * from $table WHERE $where";
			$db_user = $obj->MySQLSelect($db_sql);				
			$_SESSION['sess_iMemberId']=$userid;
			
			$_SESSION["sess_vName"]= $db_user[0]['vName'];
			$_SESSION["sess_vLastName"]= $db_user[0]['vLastName'];
			$_SESSION["sess_vEmail"] = $db_user[0]['vEmail'];
			$_SESSION["sess_eGender"]= $db_user[0]['eGender'];
			
			if($action == 'rider'){
				$_SESSION["sess_vImage"]= $db_user[0]['vImgName'];
				if($THEME_OBJ->isCubexThemeActive() == 'Yes'){
					$filename="cx-profile_rider.php";
				} else {
					$filename="profile_rider.php";
				}
				//$filename="profile_rider.php";
			}else{
				$_SESSION["sess_vImage"]= $db_user[0]['vImage'];
				$filename="profile.php";
			}
			$link = $tconfig["tsite_url"].$filename;
			
			
		}
	}
	//echo ""; print_r($_SESSION); exit;
	$AUTH_OBJ->AuthMemberRedirect();
	
	$sql = "select * from country where eStatus='Active'";
	$db_country = $obj->MySQLSelect($sql);

?>
<!DOCTYPE html>
	<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
 <!--   <title><?=$SITE_NAME?> | Login Page</title>-->
   <title><?php echo $meta_arr['meta_title'];?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
    <style>
    .header-page-a p:before {background: none;}
    .login-form-left b label{font-weight: 500;color: #000}
    .reset-password span.error { margin-bottom: 10px;margin-top: -10px;}
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
		<section class="profile-section">
			<div class="profile-section-inner">
			<div class="profile-caption">
					<div class="page-heading">
					<h1><?=$langage_lbl['LBL_COMPLATE_PROCESS_TXT'];?></h1>
					<?if(SITE_TYPE =='Demo'){?>
						<p><?=$langage_lbl['LBL_SINCE_IT_IS_DEMO'];?></p>
					<?}?>
					</div>
					<?php	
						if($success == 1) { ?>
						<div class="alert alert-danger alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						<?php echo $_REQUEST['var_msg']; ?>
					</div>
					<? } ?>
					<form name="resetpassword12" action="" class="form-signin general-form" method = "post" id="resetpassword12" >	
						<input type="hidden" value="<?=$action; ?>" id="action" name="action12" >
						<div class="partation">
							<?php if($db_user[0]['vEmail'] == ""){?>
							<div class="half-column">
								<strong>&nbsp;</strong>
								<div class="form-group">
									<label><?= $langage_lbl['LBL_EMAIL_TEXT'] ?></label>
									<input name="vEmail" id="vEmail" type="text" class="login-input-b" value="" required />
								</div>
							<?php } ?>
							</div>
							<div class="half-column">
								<div class="form-group">
									<strong><?= $langage_lbl['LBL_COUNTRY_TXT'] ?></strong>
									<select class="select-reset-password"  name ="vCountry" id="vCountry" onChange="changeCode(this.value);" required >
										<? for($i=0;$i<scount($db_country);$i++){ ?>
										<option value ="<?= $db_country[$i]['vCountryCode'] ?>" <?php if($db_country[$i]['vCountryCode'] == $DEFAULT_COUNTRY_CODE_WEB){echo "selected";}?>><?= $db_country[$i]['vCountry'] ?></option>
										<? } ?>
									</select>
								</div>
							</div>
							<div class="form-group phone-column half">
								<label><?=$langage_lbl['LBL_SIGNUP_777-777-7777']; ?></label>
								<input name="vPhoneCode" id="code" type="text" class="phonecode" value="" required />
								<input name="vPhone" id="vPhone" type="text" class="login-input-b" value="" required />
							</div>
							<?php if($REFERRAL_SCHEME_ENABLE == 'Yes'){ ?>
								<div class="form-group half" id="refercodeCheck">
									<label><?= $langage_lbl['LBL_SIGNUP_REFERAL_CODE'] ?></label>
									<input name="vRefCode" id="vRefCode" type="text" class="login-input-b" value="" style="width:100%;" onBlur="validate_refercode(this.value)"/>
									<input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"  class="create-account-input" value="" />
									<input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input" value=""  />
								</div>
							<?php }  ?>
							<div class="half-column">
								<div class="check-combo">
									<div class="check-main newrow">
										<span class="check-hold">
											<input id="c1" name="RememberMe" type="checkbox" class="required login-input-b" value="remember" required>
											<span class="check-button"></span>
										</span>
									</div><label for="c1"><?=$langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="terms-condition" target="_blank" style="margin: 0;float: none; "><?=$langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?>  </a> and <a href="privacy-policy" target="_blank" style="margin: 0;float: none; "><?=$langage_lbl['LBL_PRIVACY_POLICY_TEXT']; ?>  </a></label>
								</div>
							</div>
						</div>
						<input type="submit" class="submit-but gen-btn" name="submit" value="<?=$langage_lbl['LBL_SIGN_UP']; ?>" />
						</b> 
					</form>
					
					<form name="frmsignup" id="frmsignup" action="signuprider_a.php" method="POST">

                                        <?php if ($error != "" && ($_REQUEST['type'] == 'user' || empty($_REQUEST['type']))) { ?>
                                            <div class="row">
                                                <div class="col-sm-12 alert alert-danger">
                                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                                    <?= $var_msg; ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="partation">
                                            <h1><?= $langage_lbl['LBL_ACC_INFO'] ?></h1>
                                            <div class="form-group onethird newrow">
                                                <label><?= $langage_lbl['LBL_EMAIL_TEXT_SIGNUP']; ?> <span class="red">*</span></label>
                                                <input type="email" Required name="vEmail" class="create-account-input" id="vEmail_verify" value="<?php echo $vEmail; ?>" />
                                            </div>

                                            <div class="form-group onethird newrow">
                                                <div class="relative_ele">
                                                    <label><?= $langage_lbl['LBL_PASSWORD']; ?> <span class="red">*</span></label>
                                                    <input id="pass" type="password" name="vPassword" class="create-account-input create-account-input1 " required value="" />
                                                    <!--<button type="button" onclick="showHidePassword('pass')" id="eye"><img src="assets/img/eye.png" alt="eye"/></button>-->
                                                </div>
                                            </div>
                                            <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                                                <div class="form-group onethird newrow">
                                                    <label id="referlbl"><?= $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?></label>
                                                    <input id="vRefCode" type="text" name="vRefCode" class="create-account-input create-account-input1 vRefCode_verify" value="<?php echo $vRefCode; ?>" onBlur="validate_refercode(this.value)"/>
                                                    <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"  class="create-account-input" value="" />
                                                    <input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input" value=""  />
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="partation">
                                            <h1><?= $langage_lbl['LBL_BASIC_INFO'] ?></h1>
                                            <div class="form-group onethird newrow">
                                                <label><?= $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?> <span class="red">*</span></label>
                                                <input name="vName" type="text" onkeypress="return IsAlphaNumeric(event, this.id);" class="create-account-input" id="vName"value="<?php echo $vFirstName; ?>" required />
                                                <span id="vName_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <label><?= $langage_lbl['LBL_SIGN_UP_LAST_NAME_HEADER_TXT']; ?> <span class="red">*</span></label>
                                                <input name="vLastName" type="text" onkeypress="return IsAlphaNumeric(event, this.id);" class="create-account-input create-account-input1" id="vLastName" value="<?php echo $vLastName; ?>" required />
                                                <span id="vLastName_spaveerror" style="color: Red; display: none;font-size: 11px;">* White space not allowed</span>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <select class="" required name='vCountry' id="vCountry" onChange="setState(this.value, '');" >
                                                    <option value=""><?= $langage_lbl['LBL_SELECT_TXT'] ?></option>
                                                    <? for ($i = 0; $i < scount($db_country); $i++) { ?>
                                                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <? if ($DEFAULT_COUNTRY_CODE_WEB == $db_country[$i]['vCountryCode']) { ?>selected<? } ?>><?= $db_country[$i]['vCountry'] ?></option>
                                                    <? } ?>
                                                </select>
                                            </div>
                                            <div class="form-group onethird phone-column newrow">
                                                <label><?= $langage_lbl['LBL_SIGNUP_777-777-7777']; ?> <span class="red">*</span></label>
                                                <!--<select name="vPhoneCode" id="code">
                                                    <option value="91">+91</option>
                                                </select>-->
                                                <input type="text" name="vPhoneCode" readonly id="code" class="phonecode" />
                                                <input required type="text"  id="vPhone" value="<?php echo $vPhone; ?>" class="create-account-input create-account-input1 vPhone_verify" name="vPhone"/>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <select name="vLang" class="">
                                                    <? for ($i = 0; $i < scount($db_lang); $i++) { ?>
                                                        <option value="<?= $db_lang[$i]['vCode'] ?>" <?
                                                        if ($db_lang[$i]['eDefault'] == 'Yes') {
                                                            echo 'selected';
                                                        }
                                                        ?>>
                                                        <?= $db_lang[$i]['vTitle'] ?>
                                                        </option>
<? } ?>
                                                </select>
                                            </div>
                                            <div class="form-group onethird newrow">
                                                <select class="" required name = 'vCurrencyPassenger'>
                                                        <?php for ($i = 0; $i < scount($db_currency); $i++) { ?>
                                                        <option value = "<?= $db_currency[$i]['vName'] ?>" <? if ($db_currency[$i]['eDefault'] == "Yes") { ?>selected<? } ?>>
                                                        <?= $db_currency[$i]['vName'] ?>
                                                        </option>
<? } ?>
                                                </select>
                                            </div>

                                            <div class="form-group  captcha-column newrow">
<?php include_once("recaptcha.php"); ?>
                                                <!--<span id="recaptcha-msg" style="display: none;" class="error">This field is required.</span>-->
                                            </div>
                                            <div class="onethird check-combo">
                                                <div class="check-main newrow">
                                                    <span class="check-hold">
                                                        <input type="checkbox" name="remember-me" id="c1" value="remember">
                                                        <span class="check-button"></span>
                                                    </span>
                                                </div><label for="c1"><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="terms_condition.php" target="_blank"><?= $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a></label>
                                            </div>
                                            <div class="button-block">
                                                <div class="btn-hold">
                                                    <input type="submit" name="SUBMIT" value="<?= $langage_lbl['LBL_REGISTER_SMALL']; ?>"/>
                                                    <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="member-txt">
                                        <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="sign-in" tabindex="5" ><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
                                        </div>
<?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes" || $PASSENGER_GOOGLE_LOGIN == "Yes" || $PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                            <div class="aternate-login" data-name="OR"></div>
                                            <div class="soc-login-row">
                                                <label><?= $langage_lbl['LBL_REGISTER_WITH_SOCIAL_ACC']; ?></label>
                                                <ul class="social-list" id="rider-social">
                                                    <?php if ($PASSENGER_FACEBOOK_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="facebook-rider/rider" tabindex="6"><img src="assets/img/page/facebook-new.png" alt="Facebook"></a></li>
                                                    <?php } if ($PASSENGER_LINKEDIN_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="linkedin-rider/rider" tabindex="7"><img src="assets/img/page/linkedin-new.png" alt="Linkedin"></a></li>
                                                    <?php } if ($PASSENGER_GOOGLE_LOGIN == "Yes") { ?>
                                                        <li><a target="_blank" href="google/rider" tabindex="8"><img src="assets/img/page/google-new.png" alt="Google Plus"></a></li>
                                            <?php } ?>
                                                </ul>
                                            </div>
<?php } ?>
                                        <input type = 'reset' class = 'resetform' value = 'reset' style="display:none"/>
                                    </form>
			</div>
		</section>	
	
	<!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    <!-- footer part end -->
    		<!-- -->
            <div style="clear:both;"></div>
			</div>
	</div>
	<!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <!-- End: Footer Script -->
  <script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
	<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
    <script>
    	var errormessage;
	 $('#resetpassword12').validate({						
	    //onsubmit: true,
	    onkeyup: function(element) {$(element).valid()},
		ignore: 'input[type=hidden]',
		errorClass: 'help-block error',
		errorElement: 'span',		 
		errorPlacement: function (error, e) {
			e.parents('.newrow > b').append(error);
		},
		highlight: function (e) {
			$(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
			$(e).closest('.newrow b input').addClass('has-shadow-error');
			$(e).closest('.help-block').remove();
		},
		success: function (e) {
			e.prev('input').removeClass('has-shadow-error');
			e.closest('.newrow').removeClass('has-success has-error');
			e.closest('.help-block').remove();
			e.closest('.help-inline').remove();
		},
		rules: {			
			vEmail: {required: true, email: true,
				remote: {
						url: 'ajax_validate_email_new.php',
						type: "post",
					    data: {
                            iDriverId: '',
                            usertype:  function(e){
                                return $('input[name=action12]').val();
                            }
                        },
                        dataFilter: function(response) {
                            //response = $.parseJSON(response);
                            if (response == 'deleted')  {
                                errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                                return false;
                            } else if(response == 'false'){
                                errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                                return false;
                            } else {
                                return true;
                            }
                        },
					}
		    },		
			vPhone: {required: true,minlength: 3,digits: true,
						remote: {
							onkeyup: true,
							url: 'ajax_mobile_new_check.php',
							type: "post",						
							data: {userType:function () {return $("#action").val();}},
						}
			},
			RememberMe:{required: true}
			
		},
		messages: {
			vEmail: {remote: function(){ return errormessage; }},
			vPhone: {minlength: 'Please enter at least three Number.',digits: 'Please enter proper mobile number.',remote: 'Phone Number is already exists.'}
		}
	});
	
	function changeCode(id) {
		var request = $.ajax({
			type: "POST",
			url: 'change_code.php',
			data: 'id=' + id,
			success: function (data)
			{
				document.getElementById("code").value = data;
			}
		});
	}
	
	function validate_refercode(id){        

	if(id == ""){
	    return true;
	}else{


	    var request = $.ajax({
	        type: "POST",
	        url: 'ajax_validate_refercode.php',
	        data: 'refcode=' +id,
	        success: function (data)
	        {
	            if(data == 0){
					$("#referCheck").remove();
					$('#refercodeCheck').append('<div class="required-label error" id="referCheck" >* <?= addslashes($langage_lbl['LBL_REFER_CODE_ERROR']); ?></div>');
		            $('#vRefCode').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
		            $('#vRefCode').val("");
		            return false;
	            } else {
	            	$("#referCheck").remove();
	                var reponse = data.split('|');              
	                $('#iRefUserId').val(reponse[0]);
	                $('#eRefType').val(reponse[1]);
	            }
	        }
	    });

	}   

	}

	$(document).ready(function () {
		var code = '<?=$DEFAULT_COUNTRY_CODE_WEB?>';
		changeCode(code);
	});
		
	</script>
	
</body>
</html>