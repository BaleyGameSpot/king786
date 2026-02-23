<?php
include_once 'common.php';

$_REQUEST['action'] = (base64_decode(base64_decode(trim($_REQUEST['action']))));
$_REQUEST['id'] = decrypt($_REQUEST['id']);
$action = $_REQUEST['action'];
$userid = isset($_REQUEST['id'])?$_REQUEST['id']:'';
$vPhone = isset($_POST['vPhone'])?$_POST['vPhone']:'';
$vPhoneCode = isset($_POST['vPhoneCode'])?$_POST['vPhoneCode']:'';
$vCountry = isset($_POST['vCountry'])?$_POST['vCountry']:'';
$iRefUserId = isset($_POST['iRefUserId'])?$_POST['iRefUserId']:'';
$eRefType = isset($_POST['eRefType'])?$_POST['eRefType']:'';
$token = isset($_REQUEST['token'])?$_REQUEST['token']:'';

$_SESSION['sess_iUserId'] = $userid;
if ($action == 'rider'){
    $where = " iUserId = '$userid'";
    $table = "register_user";
}else{
    $where = "iDriverId = '$userid'";
    $table = "register_driver";
}
$db_sql = "select * from $table WHERE $where";
$db_user = $obj->MySQLSelect($db_sql);

if (!empty($db_user[0]['vPhone'])  || ($_SESSION['token'] != $_REQUEST['token'])){

    header("Location: ".$tconfig['tsite_url']);
    exit;
}
$vEmail = isset($_POST['vEmail'])?$_POST['vEmail']:$db_user[0]['vEmail'];
//echo ""; print_r($_SESSION);exit;
//echo "<pre>"; print_r($_POST);exit;
if (isset($_POST['submit'])){
    if ($action == 'rider'){
        $data['vPhone'] = $vPhone;
        $data['vPhoneCode'] = $vPhoneCode;
        $data['vCountry'] = $vCountry;
        $data['vEmail'] = $vEmail;
        $data['iRefUserId'] = $iRefUserId;
        $data['eRefType'] = $eRefType;
        $data['dRefDate'] = Date('Y-m-d H:i:s');
        $where = " iUserId = '$userid'";
        $table = "register_user";
        $_SESSION["sess_user"] = "rider";
    }else{
        $data['vPhone'] = $vPhone;
        $data['vCode'] = $vPhoneCode;
        $data['vCountry'] = $vCountry;
        $data['vEmail'] = $vEmail;
        $data['iRefUserId'] = $iRefUserId;
        $data['eRefType'] = $eRefType;
        $data['dRefDate'] = Date('Y-m-d H:i:s');
        $where = "iDriverId = '$userid'";
        $table = "register_driver";
        $_SESSION["sess_user"] = "driver";
    }
    $id = $obj->MySQLQueryPerform($table,$data,'update',$where);
    /* Multi Level Referral */
    if ($iRefUserId != ""){
        $refData = $obj->MySQLSelect("SELECT * FROM user_referrer_transaction WHERE iMemberId = ".$iRefUserId." AND eUserType = '".$eRefType."'");
        if ($refData[0]['tReferrerInfo'] == ""){
            $referrerInfo[] = array('Position of Referrer' => 1,'iMemberId' => $iRefUserId,'eUserType' => $eRefType);
        }else{
            $referrerInfo = json_decode($refData[0]['tReferrerInfo'],true);
            $referrerInfo[] = array('Position of Referrer' => (scount($referrerInfo) + 1),
                                    'iMemberId'            => $iRefUserId,
                                    'eUserType'            => $eRefType);
        }
        $Data_Referrer['tReferrerInfo'] = json_encode($referrerInfo);
        $Data_Referrer['iMemberId'] = $userid;
        $Data_Referrer['eUserType'] = ($action == 'rider')?'Rider':'Driver';
        $obj->MySQLQueryPerform("user_referrer_transaction",$Data_Referrer,'insert');
    }
    /* Multi Level Referral End */
    if ($id > 0){

        $db_sql = "select * from $table WHERE $where";
        $db_user = $obj->MySQLSelect($db_sql);
        $_SESSION['sess_iMemberId'] = $userid;
        $_SESSION["sess_vName"] = $db_user[0]['vName'];
        $_SESSION["sess_vLastName"] = $db_user[0]['vLastName'];
        $_SESSION["sess_vEmail"] = $db_user[0]['vEmail'];
        $_SESSION["sess_eGender"] = $db_user[0]['eGender'];
        if ($action == 'rider'){
            $type = base64_encode(base64_encode('rider'));
            $id = rawurlencode(encrypt($db_user[0]['iUserId']));
            $newToken = RandomString(32);
            $url = $tconfig["tsite_url"].'reset_password.php?type='.$type.'&id='.$id.'&_token='.$newToken;
            if (!empty($db_user[0]['vPhone'])){
                $maildata['PHONE'] = $langage_lbl_admin["LBL_PHONE"].": +".$db_user[0]['vPhoneCode'].' '.$db_user[0]['vPhone'];
            }else{
                $maildata['PHONE'] = '';
            }
            if (!empty($db_user[0]['vEmail'])){
                $maildata['EMAIL'] = $langage_lbl["LBL_EMAIL_TEXT"].": ".$db_user[0]['vEmail'];
            }else{
                $maildata['EMAIL'] = '';
            }
            $maildata['EMAILORG'] = $db_user[0]['vEmail'];
            $maildata['NAME'] = $db_user[0]['vFirstName']." ".$db_user[0]['vLastName'];
            $maildata['EMAIL_NAME'] = $db_user[0]['vFirstName']." ".$db_user[0]['vLastName'];
            $maildata['PASSWORD'] = '';
            $maildata['SOCIALNOTES'] = $langage_lbl['LBL_SOCIAL_MEDIA_NOTES1_TXT'].'<br>'.$url.'<br>'.$langage_lbl['LBL_SOCIAL_MEDIA_NOTES2_TXT'];
            $COMM_MEDIA_OBJ->SendMailToMember("MEMBER_REGISTRATION_USER",$maildata);
            $sql = "UPDATE register_user set vPassword_token='".$newToken."' WHERE iUserId = '".$db_user[0]['iUserId']."' and eStatus != 'Deleted'";
            $obj->sql_query($sql);
            //$_SESSION["sess_vImage"]= $db_user[0]['vImgName'];
            if ($THEME_OBJ->isCubexThemeActive() == 'Yes' || $THEME_OBJ->isCubeXv2ThemeActive() == 'Yes'){
                $filename = "cx-profile_rider.php";
            }else{
                $filename = "profile_rider.php";
            }
            //$filename="profile_rider.php";
        }else{
            $type = base64_encode(base64_encode('driver'));
            $id = rawurlencode(encrypt($db_user[0]['iDriverId']));
            $newToken = RandomString(32);
            $url = $tconfig["tsite_url"].'reset_password.php?type='.$type.'&id='.$id.'&_token='.$newToken;
            $maildata['EMAIL'] = $langage_lbl_admin["LBL_EMAIL_TEXT"].": ".$db_user[0]['vEmail'];
            $maildata['PHONE'] = $langage_lbl_admin["LBL_PHONE"].": +".$db_user[0]['vCode'].$db_user[0]['vPhone'];
            $maildata['EMAILORG'] = $db_user[0]['vEmail'];
            $maildata['NAME'] = $db_user[0]['vFirstName']." ".$db_user[0]['vLastName'];
            $maildata['EMAIL_NAME'] = $db_user[0]['vFirstName']." ".$db_user[0]['vLastName'];
            $maildata['PASSWORD'] = '';
            $maildata['SOCIALNOTES'] = $langage_lbl['LBL_SOCIAL_MEDIA_NOTES1_TXT'].'<br>'.$url.'<br>'.$langage_lbl['LBL_SOCIAL_MEDIA_NOTES2_TXT'];
            $COMM_MEDIA_OBJ->SendMailToMember("DRIVER_REGISTRATION_USER",$maildata);
            $sql = "UPDATE register_driver set vPassword_token='".$newToken."' WHERE iDriverId='".$db_user[0]['iDriverId']."' and eStatus != 'Deleted'";
            $obj->sql_query($sql);
            //$_SESSION["sess_vImage"]= $db_user[0]['vImage'];
            $filename = "profile.php";
        }
        $link = $tconfig["tsite_url"].$filename;
        header('Location:'.$link);
        exit;
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
    <title><?php echo $meta_arr['meta_title']; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
    <style>
        .header-page-a p:before {
            background: none;
        }

        .login-form-left b label {
            font-weight: 500;
            color: #000
        }

        .reset-password span.error {
            margin-bottom: 10px;
            margin-top: -10px;
        }

        #RememberMe-error {
            min-width: 300px;
        }

        input.submit-but:disabled {
            background-color: #757575;
            cursor: default;
        }
    </style>
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <section class="profile-section">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?=ucfirst($langage_lbl['LBL_ACC_INFO']);?></h1>
                    <?php if (SITE_TYPE == 'Demo'){ ?>
                        <p><?=$langage_lbl['LBL_SINCE_IT_IS_DEMO'];?></p>
                    <?php } ?>
                </div>
                <?php
                if ($success == 1){ ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?php echo $_REQUEST['var_msg']; ?>
                    </div>
                <?php } ?>
                <form name="resetpassword12" action="" class="form-signin general-form" method="post" id="resetpassword12">
                    <input type="hidden" value="<?=$action;?>" id="action" name="action12">
                    <div class="partation">
                        <?php if ($db_user[0]['vEmail'] == ""){ ?>
                        <div class="half-column">
                            <strong>&nbsp;</strong>
                            <div class="form-group newrow">
                                <label><?=$langage_lbl['LBL_EMAIL_TEXT']?></label>
                                <input name="vEmail" id="vEmail" type="text" class="login-input-b" value=""/>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="half-column">
                            <div class="form-group newrow">
                                <strong><?=$langage_lbl['LBL_COUNTRY_TXT']?></strong>
                                <select class="select-reset-password" name="vCountry" id="vCountry" onChange="changeCode(this.value);" required>
                                    <?php for ($i = 0;$i < scount($db_country);$i++){ ?>
                                        <option value="<?=$db_country[$i]['vCountryCode']?>" <?php if ($db_country[$i]['vCountryCode'] == $DEFAULT_COUNTRY_CODE_WEB){
                                            echo "selected";
                                        } ?>><?=$db_country[$i]['vCountry']?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group phone-column half newrow">
                            <label><?=$langage_lbl['LBL_SIGNUP_777-777-7777'];?></label>
                            <input name="vPhoneCode" id="code" type="text" class="phonecode" value="" required readonly="readonly"/>
                            <input name="vPhone" id="vPhone" type="text" class="login-input-b" value="" required/>
                        </div>
                        <?php if ($REFERRAL_SCHEME_ENABLE == 'Yes'){ ?>
                            <div class="form-group half newrow" id="refercodeCheck">
                                <label><?=$langage_lbl['LBL_SIGNUP_REFERAL_CODE']?></label>
                                <input name="vRefCode" id="vRefCode" type="text" class="login-input-b" value="" style="width:100%;" onBlur="validate_refercode(this.value)"/>
                                <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId" class="create-account-input" value=""/>
                                <input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input" value=""/>
                            </div>
                        <?php } ?>
                        <div class="half-column" style="margin-top: 15px;">
                            <div class="check-combo" style="width: 100%;">
                                <div class="check-main newrow">
										<span class="check-hold">
											<input id="c1" name="RememberMe" type="checkbox" class="required login-input-b" value="remember" required>
											<span class="check-button"></span>
										</span>
                                </div>
                                <label for="c1"><?=$langage_lbl['LBL_SIGNUP_Agree_to'];?>
                                    <a href="terms-condition" target="_blank"><?=$langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION'];?>  </a>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="button-block">
                        <div class="btn-hold">
                            <input type="submit" class="submit-but" name="submit" value="<?=$langage_lbl['LBL_NEXT'];?>"/>
                            <img src="assets/img/apptype/<?php echo $template; ?>/arrow.svg" alt="">
                        </div>
                    </div>

                    </b>
                </form>
            </div>
    </section>

    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- -->
    <div style="clear:both;"></div>
</div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script>
    var errormessage;
    $('#resetpassword12').validate({
        //onsubmit: true,
        //onkeyup: function(element) {$(element).valid()},
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        onkeypress: true,
        errorElement: 'span',
        errorPlacement: function (error, e) {
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        //ignore: 'input[type=hidden]',
        //errorClass: 'help-block error',
        //errorElement: 'span',
        //errorPlacement: function (error, e) {
        //	e.parents('.newrow > b').append(error);
        //},
        //highlight: function (e) {
        //	$(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
        //	$(e).closest('.newrow b input').addClass('has-shadow-error');
        //	$(e).closest('.help-block').remove();
        //},
        //success: function (e) {
        //	e.prev('input').removeClass('has-shadow-error');
        //	e.closest('.newrow').removeClass('has-success has-error');
        //	e.closest('.help-block').remove();
        //	e.closest('.help-inline').remove();
        //},
        rules: {
            // vEmail: {required: true, email: true,
            // 	remote: {
            // 			url: 'ajax_validate_email_new.php',
            // 			type: "post",
            // 		    data: {
            //                          iDriverId: '',
            //                          usertype:  function(e){
            //                              return $('input[name=action12]').val();
            //                          }
            //                      },
            //                      dataFilter: function(response) {
            //                          //response = $.parseJSON(response);
            //                          if (response == 'deleted')  {
            //                              errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
            //                              return false;
            //                          } else if(response == 'false'){
            //                              errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
            //                              return false;
            //                          } else {
            //                              return true;
            //                          }
            //                      },
            // 		}
            //    },
            vPhone: {
                required: true, minlength: 3, digits: true,
                remote: {
                    onkeyup: true,
                    url: 'ajax_mobile_new_check.php',
                    type: "post",
                    data: {
                        userType: function () {
                            return $("#action").val();
                        }
                    },
                    beforeSend: function (xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                }
            },
            RememberMe: {required: true}
        },
        messages: {
            //vEmail: {remote: function(){ return errormessage; }},
            //vPhone: {minlength: 'Please enter at least three Number.',digits: 'Please enter proper mobile number.',remote: 'Phone Number is already exists.'}
            vPhone: {
                //minlength: 'Please enter at least three Number.',
                //digits: 'Please enter proper mobile number.',
                remote: function () {
                    return errormessage;
                }
            },
        }
    });

    function changeCode(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            } else {
                console.log(response.result);
            }
        });
    }

    function validate_refercode(id) {
        $("#referCheck").remove();
        if (id == "") {
            $('.submit-but').attr('disabled', false);
            return true;
        } else {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url'] ?>ajax_validate_refercode.php',
                'AJAX_DATA': 'refcode=' + id,
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    if (data == 0) {
                        $("#referCheck").remove();
                        $('#refercodeCheck').append('<div class="required-label error" id="referCheck" >* <?= addslashes($langage_lbl['LBL_REFER_CODE_ERROR']); ?></div>');
                        $('#vRefCode').attr("placeholder", "<?= addslashes($langage_lbl['LBL_SIGNUP_REFERAL_CODE']); ?>");
                        //$('#vRefCode').val("");
                        $('.submit-but').attr('disabled', true);
                        return false;
                    } else {
                        $("#referCheck").remove();
                        var reponse = data.split('|');
                        $('#iRefUserId').val(reponse[0]);
                        $('#eRefType').val(reponse[1]);
                        $('.submit-but').attr('disabled', false);
                    }
                } else {
                    // console.log(response.result);
                }
            });
        }
    }

    $(document).ready(function () {
        //console.log("<?php echo $db_user[0]['vCountry']?>");
        <?php if(!empty($db_user[0]['vCountry'])){ ?>
        var code = '<?= $db_user[0]['vCountry'];?>';
        <?php } else { ?>
        var code = '<?= $DEFAULT_COUNTRY_CODE_WEB;?>';
        <?php } ?>
        changeCode(code);
    });

</script>

</body>
</html>