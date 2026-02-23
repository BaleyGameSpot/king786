<?php
include_once('../common.php');
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
$onlyFETEnable = !empty($MODULES_OBJ->isOnlyEnableFETApp()) ? 'Yes' : 'No';
$tsiteUrl = $tconfig["tsite_url"];
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : 'admin';
$data = $obj->MySQLSelect('SELECT * FROM setup_info');
if (isset($data[0]['eEnableHotel']) && $data[0]['eEnableHotel'] == "No" && strtolower($userType) == "hotel") {
    header('Location:' . $tsiteUrl . 'admin');
    exit();
}
$cubeDeliverallOnly = $MODULES_OBJ->isOnlyDeliverAllSystem();
$filePanel = "Admin"; // Used In setup_validation.php File For Include Js File By HJ On 22-04-2019
if ($IS_INHOUSE_DOMAINS == false && $_SERVER["HTTP_HOST"] == "192.168.1.141") {
    $data = $obj->MySQLSelect('SELECT * FROM setup_info');
    if (isset($data[0]['iSetupId']) && $data[0]['iSetupId'] > 0) {
        include_once("../setup_validation.php");
        if ($errorcountsystemvalidation > 0) {
            exit();
        }
    }
}

$_SESSION['sess_signin'] = "admin";
$AUTH_OBJ->AuthAdminRedirect();

$logo = "logo.png";

$activeTab = "1";
$activeTabId = "super001";
if ($userType == "billing") {
    $activeTab = "3";
    $activeTabId = "billing001";
}
if (file_exists($tconfig["tpanel_path"] . $logogpath . $logo)) {
    $admin_logo = $tsiteUrl . $logogpath . $logo;
} else {
    $admin_logo = $tsiteUrl . '' . SITE_ADMIN_URL . '/' . 'images/' . $logo;
}
$fav_icon_image = 'favicon.ico';
if (file_exists($tconfig["tsite_upload_apptype_images_panel"] .$template ."/". $fav_icon_image)) {
    $fav_icon_image = $tconfig["tsite_upload_apptype_images"] .$template ."/". $fav_icon_image;
} else {
    if (file_exists($tconfig["tpanel_path"] . $logogpath . $fav_icon_image)) {
        $fav_icon_image = $tsiteUrl . $logogpath . $fav_icon_image;
    } else {
        $fav_icon_image = $tsiteUrl . $fav_icon_image;
    }
}
$adminUrl = $tconfig["tsite_url_main_admin"];

$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');

$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (scount($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $become_restaurant = $langage_lbl['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl['LBL_STORE'];
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8" />
    <title>Admin | Login Page</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="robots" content="noindex, nofollow" />
    <link rel="icon" href="<?= $fav_icon_image; ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= $adminUrl; ?>css/bootstrap.css" />
    <link rel="stylesheet" href="<?= $adminUrl; ?>css/login_v3.css" />
    <link rel="stylesheet" href="<?= $adminUrl; ?>css/style_v3.css" />
    <link type="text/less" href="css/admin_new/admin_style_new.less" rel="stylesheet" />
    <link rel="stylesheet" href="<?= $tsiteUrl; ?>assets/css/animate/animate.min.css" />
    <link rel="stylesheet" href="<?= $tsiteUrl; ?>assets/plugins/magic/magic.css" />
    <link rel="stylesheet" href="<?= $adminUrl; ?>css/font-awesome.css" />
    <link rel="stylesheet" href="<?= $tsiteUrl; ?>assets/plugins/font-awesome-4.6.3/css/font-awesome.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Exo+2:100,300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $adminUrl; ?>css/admin_new/remixicon.css">
    <script src="<?= $tsiteUrl; ?>assets/plugins/jquery-2.0.3.min.js"></script>
    <?php /*<script src="<?= $tsiteUrl; ?>admin/js/lottie.js"></script>*/ ?>
    <?php if (SITE_TYPE == "Demo") { ?>
        <style type="text/css">
            #login {
                min-height: -webkit-calc(100vh - 375px);
            }
            <?php if ($onlyRideShareEnable == 'Yes' || $onlyBSREnable == 'Yes' || $onlyFETEnable == 'Yes')  { ?>
                .admin-home-tab .nav-tabs li {
                   width: 50%;
                }
            <?php } ?>
        </style>
    <?php } ?>
    <?php if ($onlyRideShareEnable == 'Yes' || $onlyBSREnable == 'Yes' || $onlyFETEnable == 'Yes')  { ?>
        <style type="text/css">
            .admin-home-tab .nav-tabs li {
               width: 50%;
            }
        </style>
    <?php } ?>
    <script>
        var SITE_PANEL_PATH = '<?= $tconfig["tsite_folder"] ?>';
        document.write('<style type="text/css">body{display:none}</style>');
        jQuery(function($) {
            $('body').css('display','block');
        });
    </script>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="nobg loginPage">

    <input type="hidden" name="hdf_class" id="hdf_class" value="<?php echo $_SESSION['edita']; ?>">
    <div class="admin-mainflex">
        <!-- PAGE CONTENT -->
        <div class="left-slot">
            <div class="admin-identy-holder">
            <div class="admin-icon"><img src="<?= $tsiteUrl ?>assets/img/admin.svg" id="Admin" alt=" Admin"></div>
                <h1>Welcome to Admin Panel</h1>
                <strong>Monitor your server usage & services as well as manage applications & front website.</strong>
            </div>
            <div class="admin-main-image">
                <?php /*<lottie-player src="<?= $adminUrl; ?>img/admin-animation.json" autoplay loop></lottie-player> */ ?>
                <img src="<?= $tsiteUrl . 'resizeImg.php?w=600&src=' . $adminUrl; ?>img/dashboard.png">
            </div>
        </div>
        <div class="right-slot">

            <?php if ($userType == 'hotel') { ?>
                <div id="login">
                    <p style="display:none; padding:5px 0;" class="btn-block btn btn-rect btn-success" id="success" ></p>

                    <?php
                    if (isset($_SESSION['checkadminmsg']) && !empty($_SESSION['checkadminmsg'])) {
                        $msg = $_SESSION['checkadminmsg'];
                        echo ' <div class="data-msg-center"><p style="display:block;" class="btn-block btn btn-rect btn-danger errormsg text-muted text-center errormsg" id="errmsg">' . $msg . ' </p></div>';
                        unset($_SESSION['checkadminmsg']);
                    } else {
                        ?>
                        <div class="data-msg-center"><p style="display:none;" class="btn-block btn btn-rect btn-danger text-muted text-center" id="errmsg"></p></div>
                    <?php } ?>

                    <div class="admin-home-tab">
                        <div class="tab-content clearfix custom-tab">
                            <h2 class="adminpagetitle">Hotel Admin</h2>
                            <div>
                                <form autocomplete="off" action="" class="form-signin" method = "post" id="login_box" onSubmit="return chkValid();" style="margin:0 auto;border:0;">
                                    <div class="forminput-holder">
                                        <label for="email">Hotel Admin E-mail</label>
                                        <input type="text" placeholder="Email Address" class="form-control" name="vEmail" id="vEmail" required Value="" autocomplete="off"/>
                                    </div>
                                    <div class="forminput-holder">
                                        <label for="password">Password</label>
                                        <input type="password" placeholder="Password" class="form-control" name="vPassword" id="vPassword" required Value="" autocomplete="off"/>
                                    </div>
                                    <input type="hidden" name="group_id" id="group_id" value="4"/>
                                    <input type="submit" class="btn text-muted text-center btn-default" value="SIGN IN"/>
                                </form>
                            </div>
                        </div>
                        <? if (SITE_TYPE == "Demo") { ?>
                                <!--<div class="tab-content">
                                    <div id="super001" class="tab-pane active">
                                        <h3> Use below Detail for Demo Version</h3>
                                        <p><b>User Name:</b> hoteladmin@demo.com</p>
                                        <p><b>Password:</b> 123456 </p>
                                        <p>Hotel Administrator can book taxi.</p>
                                    </div>
                                </div>-->
                            <? } ?>
                            <div style="clear:both;"></div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="tab-content">
                        <div id="login" class="tab-pane active">
                            <div class="admin-logo"><img src="<?= $admin_logo; ?>" id="Admin" alt=" Admin" /></div>
                            <p style="display:none; padding:5px 0;" class="btn-block btn btn-rect btn-success" id="success" ></p>
                            <?php
                            if (isset($_SESSION['checkadminmsg']) && !empty($_SESSION['checkadminmsg'])) {
                                $msg = $_SESSION['checkadminmsg'];
                                echo '<div class="data-msg-center"><p style="display:block;" class="btn-block btn btn-rect btn-danger text-muted text-center errormsg" id="errmsg">' . $msg . ' </p></div>';
                                unset($_SESSION['checkadminmsg']);
                            } else {
                                ?>
                                <div class="data-msg-center"><p style="display:none;" class="btn-block btn btn-rect btn-danger text-muted text-center errormsg" id="errmsg"></p></div>
                            <?php } ?>
                            <!--
                                    <form action="" class="form-signin" method = "post" id="login_box" onSubmit="return chkValid();">
                                    <p class="head_login_005">Login</p>
                                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                                    <input type="text" placeholder="Email Address" class="form-control" name="vEmail" id="vEmail" required />
                                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                                    <input type="password" placeholder="Password" class="form-control" name="vPassword" id="vPassword" required />
                                    <input type="submit" class="btn text-muted text-center btn-default" value="SIGN IN"/>
                                    <br>
                                </form>-->
                                <h2 class="adminpagetitle">Login In to Dashboard</h2>
                                <div class="admin-home-tab">
                                    <ul class="nav nav-tabs">
                                        <li <?php if ($userType == "admin") { ?>class="active"<?php } ?>  onclick="setAdminType('1')"><a data-toggle="tab" href="#super001">All Admin</a></li>
                                        <?php if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes' && $onlyFETEnable != 'Yes') { ?>
                                            <li onclick="setAdminType('2')"><a data-toggle="tab" href="#dispatch001">Dispatcher Admin</a></li>
                                            <li <?php if ($userType == "billing") { ?>class="active"<?php } ?> onclick="setAdminType('3')"><a data-toggle="tab" class="active" href="#billing001">Billing Admin</a></li>
                                        <?php } ?>
                                        <li <?php if ($userType == "serveradmin") { ?>class="active"<?php } ?> onclick="setAdminType('5')"><a data-toggle="tab" class="active" href="#billing001">Server Admin</a></li>
                                    </ul>
                                    <div class="tab-content clearfix custom-tab">
                                        <div class="tab-pane active" id="super001">
                                            <form action="" class="form-signin" method = "post" id="login_box" onSubmit="return chkValid();" style="margin:0 auto;border:0;">
                                                <div class="forminput-holder">
                                                    <label for="email">Admin E-mail</label>
                                                    <input type="text" placeholder="Email Address" class="form-control" name="vEmail" id="vEmail" required Value="" autocomplete="new-email"/>
                                                </div>
                                                <div class="forminput-holder" style="margin-bottom: 0;">
                                                    <label for="password">Password</label>
                                                    <input type="password" placeholder="Password" class="form-control" name="vPassword" id="vPassword" required Value="" autocomplete="new-password"/>
                                                </div>
                                                <div class="forminput-holder" style=" text-align: right; margin-bottom: 0;">
                                                    <a href="javascript:void(0)" id="forgot_btn" onclick="change_heading('forgot');" tabindex="4" class="hotelhide"><?= $langage_lbl['LBL_FORGET_PASS_TXT']; ?></a>
                                                </div>
                                                <input type="hidden" name="group_id" id="group_id" value="1"/>
                                                <input type="submit" class="btn text-muted text-center btn-default" value="SIGN IN"/>
                                            </form>
                                        </div>
                                    </div>
                                    <? if (SITE_TYPE == "Demo") { ?>
                                    <!--<div class="tab-content">
                                        <div id="super001admin" class="tab-pane active">
                                            <h3> Use below Detail for Demo Version</h3>
                                            <p><b>User Name:</b> demo@demo.com</p>
                                            <p><b>Password:</b> 123456 </p>
                                            <p>Super Administrator can manage whole system and other user's rights too.</p>
                                        </div>
                                        <div id="dispatch001admin" class="tab-pane">
                                            <h3> Use below Detail for Demo Version</h3>
                                            <p><b>User Name:</b> demo2@demo.com</p>
                                            <p><b>Password:</b> 123456 </p>
                                            <p>Call Center Panel / Administrator Dispatcher Panel / Manual Taxi Booking Panel. This panel allows one to see all taxi's on map using God's View. And book taxi's for customer's who would call to book a taxi.</p>
                                        </div>
                                        <div id="billing001admin" class="tab-pane">
                                            <h3> Use below Detail for Demo Version</h3>
                                            <p><b>User Name:</b> demo3@demo.com</p>
                                            <p><b>Password:</b> 123456 </p>
                                            <p>This use will have access to reports only. Will be used by Accounts Team to manage finances and see profits/revenue.</p>
                                        </div>
                                    </div>-->
                                <? } ?>
                                <div style="clear:both;"></div>
                            </div>
                        </div>
                    <!-- forgot_sec_2023 -->
                        <div id="forgot" class="tab-pane">
                            <div class="data-msg-center">
                                <p id="errmsgf" style="display:none;" class="text-muted btn btn-danger btn-rect error-login-v"></p>
                                <p style="display:none;background-color: #14b368;" class="btn btn-rect btn-success error-login-v" id="successf"></p>
                            </div>
                            <h2 class="adminpagetitle"><?= $langage_lbl['LBL_FORGOR_PASSWORD']; ?></h2>
                            <div class="admin-home-tab" style="padding:0;">
                                 <ul class="nav nav-tabs">
                                    <li <?php if ($userType == "admin") { ?>class="active"<?php } ?>  onclick="setAdminType('1')" id="1"><a data-toggle="tab" href="#super001">All Admin</a></li>
                                    <?php if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes' && $onlyFETEnable != 'Yes') { ?>
                                        <li onclick="setAdminType('2')" id="2"><a data-toggle="tab" href="#dispatch001">Dispatcher Admin</a></li>
                                        <li <?php if ($userType == "billing") { ?>class="active"<?php } ?> onclick="setAdminType('3')" id="3"><a data-toggle="tab" class="active" href="#billing001">Billing Admin</a></li>
                                    <?php } ?>
                                    <li <?php if ($userType == "serveradmin") { ?>class="active"<?php } ?> onclick="setAdminType('5')" id="5"><a data-toggle="tab" class="active" href="#billing001">Server Admin</a></li>
                                </ul>
                            </div>
                            <div class="tab-content clearfix custom-tab">
                                <div class="tab-pane active" id="super001">
                                    <!-- forgot password -->
                                    <form action="" method="post" class="form-signin" id="frmforget" onSubmit="return forgotPass();">
                                        <div class="main_btn">
                                            <div class="main_btn_right">
                                                <div class="forminput-holder">
                                                    <label for="email">Admin E-mail</label>
                                                    <input type="email" required="required" placeholder="<?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?>" name="femail" class="form-control" id="femail" />
                                                    <input type="hidden" name="action" class="action" id="action" value="admin">
                                                    <input type="hidden" name="iscompany" class="iscompany" id="iscompany" value="0">
                                                    <input type="hidden" name="group_id" id="fgroup_id" value="1"/>
                                                </div>

                                                <div class="form-group  captcha-column newrow captchauser">
                                                    <div class="g-recaptcha" data-callback="triggerValidation" data-sitekey="<?= $GOOGLE_CAPTCHA_SITE_KEY ?>"></div>
                                                </div>
                                                <!-- Recover Password btn -->
                                                <div class="button-block1">
                                                    <div class="btn-hold">
                                                        <input type="submit" id="btn_submit" class="btn text-muted text-center btn-dark" tabindex="2" value="<?= $langage_lbl['LBL_Recover_Password']; ?>" />
                                                    </div>
                                                </div>
                                                <!-- OR round sec -->
                                                <div class="aternate-login" data-name="OR"></div>
                                                <!-- Already have an account? Sign in sec -->
                                                <div class="member-txt">
                                                    <?= $langage_lbl['LBL_ALREADY_HAVE_ACC']; ?> <a href="javascript:void(0)" onClick="change_heading('login');"><?= $langage_lbl['LBL_SIGN_IN']; ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <!-- forgot_sec_2023 -->
                    </div>
                <?php } ?>
                <? if (SITE_TYPE == "Demo") { ?>
                    <section class="warning" style="margin-top: 0;">
                        <div class="warning-inner">
                            <div class="warning-caption">
                                <h3><span class="blink">Warning!</span></h3>
                                <strong>This system is Designed and Developed by <span><a href="https://www.v3cube.com/" target="_blank">V3Cube.com.</a></span></strong>
                                <strong><span><a href="https://www.v3cube.com/" target="_blank">V3Cube.com</a></span> holds 100% selling rights for this system.</strong>
                                <br>
                                <p>V3Cube is located in the Western part of India. We do <span>NOT</span> have any Representatives, Resellers or Partner companies anywhere in the world. <br>Please email at <a href="mailto:sales@v3cube.com" target="_blank">"sales@v3cube.com"</a> if someone claims that this is his system to be his and is planning to sell it to you.</p>
                                <p>He is probably a <span>scammer.</span></p>
                                <strong>This system belongs to <span><a href="https://www.v3cube.com/" target="_blank">V3Cube.com</a></span> and its below listed sister concerns.</strong>
                                <div class="button-block">
                                    <a href="https://www.cubeTaxi.com/" target="_blank"><button style="margin-bottom: 10px;">CubeTaxi.com</button></a>
                                    <a href="https://www.esiteworld.com/" target="_blank"><button style="margin-bottom: 10px;">eSiteWorld.com</button></a>
                                    <a href="https://www.gojekclone.com/" target="_blank"><button style="margin-bottom: 10px;">GojekClone.com</button></a>
                                </div>
                            </div>
                        </div>
                    </section>
                <? } else { ?>
                    <div class="topNavbar">
                        <div class="userNavbar">
                            <ul>
                                <li><a href="<?= $tsiteUrl; ?>" title="" target="_blank"><i class="ri-arrow-go-back-fill"></i><span>Main website</span></a></li>
                                <li><a href="<?= $tsiteUrl . $cjRiderLogin; ?>" title="" target="_blank"><i class="ri-user-line"></i><span><?= $langage_lbl_admin['LBL_RIDER'] ?> Login</span></a></li>
                                <?php if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes' && $onlyFETEnable != 'Yes') { ?>

                                    <li><a href="<?= $tsiteUrl . $cjDriverLogin; ?>" title="" target="_blank"><i class="ri-steering-line"></i><span><?= $langage_lbl_admin['LBL_DRIVER'] ?> Login</span></a></li>
                                    <? if(strtoupper(ONLYDELIVERALL) != "YES" && $cubeDeliverallOnly == false) {
                                        $labelCompany = $langage_lbl_admin['LBL_COMPANY_SIGNIN'];
                                        if (DELIVERALL == "Yes" && $become_restaurant == "") {
                                            $labelCompany = $langage_lbl_admin['LBL_COMPANY'];
                                        }
                                        ?>
                                        <li><a href="<?= $tsiteUrl . $cjCompanyLogin; ?>" title="" target="_blank"><i class="ri-building-line"></i><span><?= $labelCompany; ?> Login</span></a></li>
                                    <? } if (!empty($become_restaurant)) {
                                        $serviceArray = $serviceIdArray = array();
                                        $serviceArray = json_decode(serviceCategories, true);
                                        $serviceIdArray = array_column($serviceArray, 'iServiceId');
                                        $become_restaurant = '';
                                        if(strtoupper(DELIVERALL) == "YES") {
                                            if (scount($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
                                                $become_restaurant = $langage_lbl['LBL_RESTAURANT_TXT'];
                                                $slink = "sign-in?type=restaurant";
                                            } else {
                                                $become_restaurant = $langage_lbl['LBL_STORE'];
                                                $slink = "sign-in?type=store";
                                            }
                                        }
                                        ?>
                                        <li><a href="<?= $tsiteUrl . $slink; ?>" title="" target="_blank"><i class="ri-store-2-line"></i><span><?= $become_restaurant; ?> Login</span></a></li>
                                    <? } ?>

                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <!--END PAGE CONTENT -->
        </div>

        <!-- PAGE LEVEL SCRIPTS -->

        <script src="<?= $tsiteUrl; ?>assets/plugins/bootstrap/js/bootstrap.js"></script>
        <script src="<?= $tsiteUrl; ?>assets/js/login.js"></script>
        <script src="<?= $tsiteUrl; ?>assets/js/getDataFromApi.js"></script>
        <script src="<?= $tsiteUrl ?><?php echo $templatePath; ?>assets/js/less.min.js"></script>
        <script src="https://www.google.com/recaptcha/api.js?explicit&hl=<?= $_SESSION['sess_lang'] ?>"></script>

        <script>
            less = { env: 'development'};

            var testLink = '<?php echo $_SESSION['current_link']; ?>';

            <?php if ($userType != 'hotel') { ?>
                $(document).ready(function () {
                    passLoginid('<?= $activeTabId; ?>', '<?= $activeTab; ?>');
                    setCredentials('<?= $activeTab; ?>', '<?= SITE_TYPE; ?>');
                });
            <?php } ?>
            function setCredentials(tpd, site_type) {
                if (site_type == "Demo")
                {
                    //if (tpd == 2) {
                    //    $("#vEmail").val('demo2@demo.com');
                    //    $("#vPassword").val('123456');
                    //} else if (tpd == 3)
                    //{
                    //    $("#vEmail").val('demo3@demo.com');
                    //    $("#vPassword").val('123456');
                    //} else
                    //{
                    //    $("#vEmail").val('demo@demo.com');
                    //    $("#vPassword").val('123456');
                    //}
                }
            }
            function passLoginid(tabid, login_group_id) {
                $(".custom-tab .tab-pane").attr('id', tabid);
                $("#group_id").val(login_group_id);
                $("#super001admin,#dispatch001admin,#billing001admin").hide();
                if (tabid == "dispatch001") {
                    $("#dispatch001admin").show();
                    $("label[for = email]").text("Dispatcher Admin E-mail");
                } else if (tabid == "billing001") {
                    $("#billing001admin").show();
                    $("label[for = email]").text("Billing Admin E-mail");
                } else {
                    $("#super001admin").show();
                    $("label[for = email]").text("Admin E-mail");
                }
            }
            $('input').keyup(function () {
                $this = $(this);
                if ($this.val().length == 1)
                {
                    var x = new RegExp("[\x00-\x80]+"); // is ascii
                    var isAscii = x.test($this.val());
                    if (isAscii)
                    {
                        $this.attr("dir", "ltr");
                    } else
                    {
                        $this.attr("dir", "rtl");
                    }
                }
            });
           /* function change_heading(heading, addClass, removeClass)
            {
                document.getElementById("login").innerHTML = heading;
                document.getElementById(addClass).className = "tab-pane";
                document.getElementById(removeClass).className = "tab-pane active";
            }*/

            function change_heading(type) {
            $('.error-login-v').hide();

            if (type == 'forgot') {

                $('#forgot').show();

                $('#login').hide();

                $("#btn_submit").val("<?= $langage_lbl['LBL_Recover_Password'] ?>");

                $("#femail").val('');

                $("#frmforget .forminput-holder, #frmforget .button-block1, #forgot .admin-home-tab").show();
                //$("#frmforget .form-group, #frmforget .button-block").show();

            } else {

                $('#forgot').hide();

                $('#login').show();
            }
        }
            function chkValid()
            {
                var id = document.getElementById("vEmail").value;
                var pass = document.getElementById("vPassword").value;

                if (id == '' || pass == '')
                {
                    document.getElementById("errmsg").style.display = '';
                    setTimeout(function () {
                        document.getElementById('errmsg').style.display = 'none';
                    }, 2000);
                } else {
                    var ajaxData = {
                        'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_login_action.php',
                        'AJAX_DATA': $("#login_box").serialize(),
                    };
                    getDataFromAjaxCall(ajaxData, function(response) {

                        if(response.action == "1") {
                            var dataHTml = response.result;
                            dataHTml = dataHTml.trim();

                            if (dataHTml == 1) {
                                document.getElementById("errmsg").innerHTML = 'You are not active. Please contact administrator to activate your account.';
                                document.getElementById("errmsg").style.display = '';
                                return false;
                            } else if (dataHTml == 5) {
                                document.getElementById("errmsg").innerHTML = 'You are no longer exist. Please contact the administrator to activate your account.';
                                document.getElementById("errmsg").style.display = '';
                                return false;
                            } else if (dataHTml == 2) {
                                document.getElementById("errmsg").style.display = 'none';
                                var hdf_class = $("#hdf_class").val();
                                if (hdf_class != "")
                                {
                                    window.location = "<?= $adminUrl; ?>languages.php";
                                } else
                                {
                                    <?php
                                    //added by SP for redirection on admin after login on 15-7-2019
                                    $redirecturl = explode('/', $_SESSION['login_redirect_url']);
                                    if (!empty($redirecturl[scount($redirecturl) - 1])) {
                                        if ($redirecturl[scount($redirecturl) - 1] == 'dashboard.php' && (ONLYDELIVERALL == "Yes" || $cubeDeliverallOnly > 0)) {
                                            // $dashboardLink = $adminUrl . 'store-dashboard.php';
                                            $dashboardLink = $adminUrl . 'dashboard.php';
                                        } else if ($redirecturl[scount($redirecturl) - 1] == 'store-dashboard.php' && (ONLYDELIVERALL == "No" && $cubeDeliverallOnly == false)) {
                                            $dashboardLink = $adminUrl . 'dashboard.php';
                                        } else if (stripos($redirecturl[scount($redirecturl) - 1], 'ajax_check_server_requirements.php') !== false || stripos($redirecturl[scount($redirecturl) - 1], 'ajax_dashboard.php') !== false) {
                                            // $dashboardLink = (ONLYDELIVERALL == "Yes" || $cubeDeliverallOnly > 0) ? $adminUrl . 'store-dashboard.php' : $adminUrl . 'dashboard.php';
                                            $dashboardLink = $adminUrl . 'dashboard.php';
                                        } else if ($redirecturl[scount($redirecturl) - 1] == 'userbooking.php?userType1=admin') { //added by SP because it will be redirect to the mainurl instead of admin url
                                            $dashboardLink = $tconfig["tsite_url"].'userbooking.php?userType1=admin';
                                        } else if ($redirecturl[scount($redirecturl) - 1] == 'logout.php') {
                                            $dashboardLink = $adminUrl . 'dashboard.php';
                                        } else {
                                            $dashboardLink = $adminUrl . $redirecturl[scount($redirecturl) - 1];
                                        }
                                    } else {
                                        // $dashboardLink = (ONLYDELIVERALL == "Yes" || $cubeDeliverallOnly > 0) ? $adminUrl . 'store-dashboard.php' : $adminUrl . 'dashboard.php';
                                        $dashboardLink = $adminUrl . 'dashboard.php';
                                    }

                                    $_SESSION['dashboardLink'] = $dashboardLink;

                                if ($_SERVER["HTTP_HOST"] == "192.168.1.131" || $_SERVER["HTTP_HOST"] == "192.168.1.141" || $_SERVER["HTTP_HOST"] == "192.168.1.151"/*  || $_SERVER["HTTP_HOST"] == "www.mobileappsdemo.com" || $_SERVER["HTTP_HOST"] == "www.webprojectsdemo.com" || $_SERVER["HTTP_HOST"] == "mobileappsdemo.com" || $_SERVER["HTTP_HOST"] == "webprojectsdemo.com" || $_SERVER["HTTP_HOST"] == "mobileappsdemo.net" || $_SERVER["HTTP_HOST"] == "www.mobileappsdemo.net" */) {
                                    $dashboardLink = $adminUrl . "project_setup.php";
                                }
                                ?>
                                if (testLink == "") {
                                    testLink = "<?= $dashboardLink; ?>";
                                }
                                window.location = testLink;
                            }
                                return true; // success registration
                            } else if (dataHTml == 3) {
                                document.getElementById("errmsg").innerHTML = 'Invalid combination of username & Password';
                                document.getElementById("errmsg").style.display = '';
                                //return false;
                            } else {
                                document.getElementById("errmsg").innerHTML = 'Invalid Email or Password';
                                document.getElementById("errmsg").style.display = '';
                            }
                            if ($('#errmsg').html() != '') {
                                setTimeout(function () {
                                    $('#errmsg').fadeOut();
                                }, 2000);
                            }
                        }
                        else {
                            console.log(response.result);
                        }
                    });
}
return false;
}

function forgotPass() {
    $('.error-login-v').hide();
    $("#btn_submit").val("<?= $langage_lbl['LBL_PLEASE_WAIT'] ?> ...");
    var site_type = '<? echo SITE_TYPE; ?>';
    var id = document.getElementById("femail").value;
    if (id == '') {
        document.getElementById("errmsg").style.display = '';
        document.getElementById("msg_close").style.display = '';
        document.getElementById("errmsg").innerHTML = '<?= addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR_TXT_IPHONE']); ?>';
    } else {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_fpass_action.php',
            'AJAX_DATA': $("#frmforget").serialize(),
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function(response) {
            if(response.action == "1") {
                var data = response.result;
                if (data.status == 1) {
                    document.getElementById("successf").innerHTML = data.msg;
                    document.getElementById("successf").style.display = '';
                    $("#frmforget .forminput-holder, #frmforget .button-block1, #forgot .admin-home-tab").hide();
                } else {
                    document.getElementById("errmsgf").innerHTML = data.msg;
                    document.getElementById("errmsgf").style.display = '';
                    //document.getElementById("msg_closef").style.display = '';
                    $("#btn_submit").val("<?= $langage_lbl['LBL_Recover_Password'] ?>");
                }
            } else {
                console.log(response.result);
            }
        });
    }
    return false;
}

function setAdminType(group_id) {
    $('#group_id').val(group_id);
    $('#fgroup_id').val(group_id);
    $("#forgot .nav-tabs li.active").removeClass("active");
    var group_id_class = "#"+ group_id;
    $("#forgot " + group_id_class).addClass("active");
}

/*------------------forgot password-----------------*/
function triggerValidation(){
    $('#g-recaptcha-response-error').remove();
}
/*------------------forgot password-----------------*/
</script>
<!--END PAGE LEVEL SCRIPTS -->
</body>
<!-- END BODY -->
<!-- Powered by V3Cube.com -->
</html>
