<?php
include_once("common.php");
$_REQUEST['type'] = (base64_decode(base64_decode(trim($_REQUEST['type']))));
$_REQUEST['id'] = decrypt($_REQUEST['id']);
$_REQUEST['time'] = (base64_decode(base64_decode(trim($_REQUEST['time']))));

$success = (isset($_REQUEST['success']) ? $_REQUEST['success'] : '');
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$token = isset($_REQUEST['_token']) ? $_REQUEST['_token'] : '';

if ($type == 'admin') {
    $sql = "SELECT iAdminId FROM administrators WHERE iAdminId='" . $id . "' AND vPassword_token='" . $token . "'";
    $db_driver = $obj->MySQLSelect($sql);

    if (scount($db_driver) > 0) {
        $tablename = 'administrators';
        $type1 = 'admin';
        $filed_Id = 'iAdminId';
        $type_id = $db_driver[0]['iAdminId'];
    }
}

if ($tablename != '' && $type1 != '' && $type_id != '') {
    $sql = "SELECT * FROM " . $tablename . " WHERE " . $filed_Id . "='" . $type_id . "'";
    $deatail = $obj->MySQLSelect($sql);
} else {
    $success = 2;
}
if ($_POST['submit']) {
    $newpassword = $_POST['newpassword'];
    $vPassword = $_POST['vPassword'];
    $_POST['type'] = (base64_decode(base64_decode(trim($_POST['type']))));
    $_POST['id'] = decrypt($_POST['id']);
    $token = isset($_REQUEST['_token']) ? $_REQUEST['_token'] : '';
    $success = (isset($_REQUEST['success']) ? $_REQUEST['success'] : '');
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $time = isset($_POST['time']) ? $_POST['time'] : '';

    if ($type == 'admin') {
        $sql = "SELECT iAdminId FROM administrators WHERE iAdminId='" . $id . "'";
        $db_admin = $obj->MySQLSelect($sql);
        if (scount($db_admin) > 0) {
            $tablename = 'administrators';
            $type_action = 'admin';
            $filed_Id = 'iAdminId';
        } else {
            $type = base64_encode(base64_encode($type));
            $id = encrypt($id);
            $var_msg = "Record is Not Found";
            header("Location:reset_password.php?type=" . $type . "&id=" . $id . "&_token=" . $token . "&&success=1&var_msg=" . $var_msg);
            exit;
        }
    }

    if ($tablename != '' && $type_action != '' && $id != '') {
        if ($newpassword == $vPassword) {

            $sql = "UPDATE " . $tablename . " set vPassword='" . encrypt_bycrypt($vPassword) . "',vPassword_token='' WHERE " . $filed_Id . "='" . $id . "'";
            $obj->sql_query($sql);

            if ($type_action == 'admin') {
                header("Location:".$tconfig["tsite_url_main_admin"]);
                exit;
            }

            header("Location:sign-in");
            exit;

        } else {
            $type_action = 'admin';
            $type = base64_encode(base64_encode($type_action));
            $id = encrypt($id);
            $var_msg = "Sorry !  Password Not Matched.";
            header("Location:reset_password.php?type=" . $type . "&id=" . $id . "&_token=" . $token . "&&success=1&var_msg=" . $var_msg);
            exit;
        }
    }
}
$cubexFlag = 'No';
if ($THEME_OBJ->isXThemeActive() == 'Yes') {
    $cubexFlag = 'Yes';
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <!--   <title><?= $SITE_NAME ?> | Login Page</title>-->
        <title><?php echo $meta_arr['meta_title']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php"); ?>
        <!-- End: Default Top Script and css-->
        <style type="text/css">
        .new-mobile001 {
            display: none;
        }
        .header-admin-hotel {
            position: relative;
            display: flex;
            padding: 0 15px;
            justify-content: space-between;
        }

        .main_header .logo {
            -webkit-transition: width .3s ease-in-out;
            -o-transition: width .3s ease-in-out;
            transition: width .3s ease-in-out;
            display: block;
            float: left;
            height: 71px;
            font-size: 20px;
            text-align: center;
            width: 230px;
            padding: 0 15px;
            font-weight: 300;
            overflow: hidden;
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
        }

        .main_header .logo {
            background: 0px;
            border-bottom: 0px;
            box-shadow: 0px 0px 0px inset;
        }

        .main_header .logo .logo-mini {
            display: none;
        }
        .main_header .logo .logo-lg {
            display: block;
            margin-top: 10px;
        }
        .main_header .logo .logo-lg img {
            max-height: 50px;
        }
        .reset-pass-left{
            width: 100%;
            margin-bottom: 15px;
        }
    </style>
    </head>
    <body id="wrapper">
        <!-- home page -->
        <div id="main-uber-page">
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php"); ?>
            <!-- End: Top Menu-->

            <!-- contact page-->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?= $langage_lbl['LBL_RESET_PASSWORD_TXT']; ?>
                                <?php if (SITE_TYPE == 'Demo') { ?>
                                    <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO']; ?></p>
                                <?php } ?></h1>
                        </div>	
                            
                    <?php if ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?=$langage_lbl['LBL_RESET_PWD_LINK_EXPIRED']; ?> 
                        </div><br/>
                    <?php } else { ?>
                    <?php if ($type != '' && $id != '') {
                        if (scount($deatail) >= 0) {
                            if ($success == 1) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $_REQUEST['var_msg']; ?>
                                </div><br/>
                            <?php } ?>
                         
                            <form name="resetpassword" class="general-form" action="" class="form-signin general-form" method = "post" id="resetpassword"> 
                                    <input type="hidden" name="type" value="<?php echo base64_encode(base64_encode($type)); ?>"/>
                                    <input type="hidden" name="id" value="<?php echo encrypt($id); ?>"/>
                                    <input type="hidden" name="_token" value="<?php echo $token; ?>"/>
                                    <div class="reset-pass-left">
                                        <div class="newrow reset-password-img">     
                                        <?php
                                        $name = $deatail[0]['vFirstName'] . " " . $deatail[0]['vLastName'];
                                        $type = 'admin';
                            
                                        if ($type == "admin") {
                                            $link = $tconfig['tsite_url_main_admin'];
                                        } else {
                                            $link = "login_new.php?action=" . $type;
                                        } ?>		
                                       
                                            <div class="which-user">	
                                                <?php if ($name != '') {  echo $name; } ?>	 
                                                <a href ="<?php echo $link; ?>"> <?= $langage_lbl['LBL_RESET_PAGE_BACK_LINK_TXT']; ?> </a>
                                            </div>
                                        </div>
                                    </div>                                                  
                                    <div class="reset-pass-right"><div class="partation">
                                            <div class="form-group half newrow">
                                                    <div class="relative_ele">
                                                        <label><?= $langage_lbl['LBL_NEW_PASSWORD_TXT']; ?></label>
                                                        <input name="newpassword" type="password"  id="newpassword"  placeholder="<?= $langage_lbl['LBL_NEW_PASSWORD_TXT']; ?>" class="login-input" value="" required />
                                                    </div>
                                             </div>
                                            </div>
                                            <div class="partation"><div class="form-group half newrow">
                                                <div class="relative_ele">
                                                    <label><?= $langage_lbl['LBL_CONFORM_PASSWORD_TXT']; ?></label>
                                                    <input name="vPassword" id="vPassword" type="password" placeholder="<?= $langage_lbl['LBL_CONFORM_PASSWORD_TXT']; ?>" class="login-input" value="" required />
                                                </div>
                                            </div></div>
                                            <div class="button-block"><div class="btn-hold">
                                                    <input type="submit" class="submit-but" name="submit" value="<?= $langage_lbl['LBL_SUBMIT_BUTTON_TXT']; ?>" />		
                                             </div></div></div></div>
                                             </form>
                                                                   
                                            <?php }
                                                } else {
                                                    if ($type == 'driver' || $type == 'company') {
                                                        $type = 'driver';
                                                        header("Location:login_new.php?action=" . $type . "");
                                                        exit;
                                                    } else if ($type == 'organization') {
                                                        $type = 'organization';
                                                        header("Location:organization_login.php?action=" . $type . "");
                                                        exit;
                                                    } else {
                                                        $type = 'rider';
                                                        header("Location:login_new.php?action=" . $type . "");
                                                        exit;
                                                    }
                                                    //header("Location:login_new.php?action=".$type.""); exit;
                                                } } ?>
                                                    <div style="clear:both;"></div>
                                                </div>
                                        </div>
                                        </section>
                                       
                                        <div style="clear:both;"></div>
                                </div>
                                <!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js" ></script>
<script type="text/javascript" src="assets/js/validation/additional-methods.js" ></script>
<script>

var errorele = 'help-block error';

$('#resetpassword').validate({
    ignore: 'input[type=hidden]',
    errorClass: errorele,
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
    rules: {
        vEmail: {required: true, email: true},
        vPassword: {required: true, equalTo: "#newpassword"},
        newpassword: {required: true, minlength: 6},
    },
    messages: {

    }
});
</script>
</body>
</html>