<?php
include_once('../common.php');

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | Dashboard</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
        <!-- GLOBAL STYLES -->
        <?php include_once('global_files.php'); ?>
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/new_main.css" />
        <link rel="stylesheet" href="css/adminLTE/AdminLTE.min.css" />
        <script type="text/javascript" src="js/plugins/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="js/plugins/morris/raphael-min.js"></script>
        <script type="text/javascript" src="js/plugins/morris/morris.min.js"></script> 
        <script type="text/javascript" src="js/actions.js"></script>
        <link rel="stylesheet" href="css/admin_new/dashboard.css">
        <!-- END THIS PAGE PLUGINS-->
        <!--END GLOBAL STYLES -->

        <!-- PAGE LEVEL STYLES -->
        <!-- END PAGE LEVEL  STYLES -->
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>

            <!--PAGE CONTENT -->
            <div id="content" class="content_right">
                <header class="main_header">
                    <div class="header">
                        <div class="actionlist">
                            <span class="adminname"><?php echo $_SESSION['sess_vAdminFirstName'] . "&nbsp;" . $_SESSION['sess_vAdminLastName']; ?><span>Admin</span></span>
                            <a href="#"><i data-v-3fe659be="" class="ri-calendar-line"></i></a>
                            <a href="#"><i data-v-3fe659be="" class="ri-message-line"></i></a>
                            <a href="#"><i data-v-3fe659be="" class="ri-mail-line"></i></a>
                            <a href="#"><i data-v-3fe659be="" class="ri-checkbox-line"></i></a>
                            <a href="#"><i data-v-3fe659be="" class="ri-star-line"></i></a>
                        </div>
                        <!-- <a href="<?= $dashboardLink; ?>" title="" class="logo"> <span class="logo-mini"> <img src="<?php echo $logosmall; ?>" alt="" /> </span> <span class="logo-lg minus"> <img src="<?php echo $logo; ?>" alt="" /> </span> </a> -->
                        <div class="d-flex align-center flex-start">
                            <div class="actionlist">
                                <a href="#"><i data-v-3fe659be="" class="ri-home-2-line"></i></a>
                                <a href="#"><i data-v-3fe659be="" class="ri-home-2-line"></i></a>
                                <a href="#"><i data-v-3fe659be="" class="ri-home-2-line"></i></a>
                                <a href="#"><i data-v-3fe659be="" class="ri-home-2-line"></i></a>
                                <a href="logout.php" title="Logout" class="header-top-button mr15"><i class="ri-shut-down-line"></i></a>
                            </div>
                        </div>
                    </div>  
                </header>
                <div class="cintainerinner">
                    <div class="row clearfix d-flex">
                        <div class="col-md-4 col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Congratulations <span class="hightlight">John!</span></strong>
                                    </div>
                                    <p>You have won gold medal</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Statistics</strong>
                                    </div>
                                    <ul class="statlist dynamic-devide">
                                        <li>
                                            <i class="icon-color1"><img src="img/icons/user.svg" alt=""></i>
                                            <div class="stat-block">
                                                <b>230k</b>
                                                <span>Users</span>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="icon-color2"><img src="img/icons/users.svg" alt=""></i>
                                            <div class="stat-block">
                                                <b>230k</b>
                                                <span>Providers</span>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="icon-color3"><img src="img/icons/building.svg" alt=""></i>
                                            <div class="stat-block">
                                                <b>230k</b>
                                                <span>Companies</span>
                                            </div>
                                        </li>
                                        <li>
                                            <i class="icon-color4"><img src="img/icons/money.svg" alt=""></i>
                                            <div class="stat-block">
                                                <b>230k</b>
                                                <span>Total Earnings</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row clearfix d-flex">
                        <div class="col-md-4 col-xl-4">
                            <div class="row clearfix">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-head">
                                                <strong>Trips</strong>
                                                <span class="count">2,76k</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-head">
                                                <strong>Orders</strong>
                                                <span class="count">6,24k</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Revenue Report</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head"><strong>Goal Overview</strong></div>
                                </div>
                                <div class="jobsrow">
                                    <div class="jobscol">
                                        <div class="card-foot">
                                            <strong>Canceled</strong>
                                            <span class="count pending-color">6,24k</span>
                                        </div>
                                    </div>
                                    <div class="jobscol">
                                        <div class="card-foot">
                                            <strong>Completed</strong>
                                            <span class="count success-color">6,24k</span>
                                        </div>
                                    </div>
                                    <div class="jobscol">
                                        <div class="card-foot">
                                            <strong>In Progress</strong>
                                            <span class="count proccess-color">6,24k</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head"><strong>Goal Overview</strong></div>
                                </div>
                                <div class="jobsrow">
                                    <div class="jobscol">
                                        <div class="card-foot">
                                            <strong>Canceled</strong>
                                            <span class="count pending-color">6,24k</span>
                                        </div>
                                    </div>
                                    <div class="jobscol">
                                        <div class="card-foot">
                                            <strong>Completed</strong>
                                            <span class="count success-color">6,24k</span>
                                        </div>
                                    </div>
                                    <div class="jobscol">
                                        <div class="card-foot">
                                            <strong>In Progress</strong>
                                            <span class="count proccess-color">6,24k</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="date-format">
                                        <div class="meetup-day"><h6> THU </h6><h3> 24 </h3></div>
                                        <div class="card-head"><strong>Developer Meetup</strong><p>Meet world popular developers</p></div>
                                    </div>
                                    <ul class="statlist vertical newstyle">
                                        <li>
                                            <div>
                                                <i class="icon-color1 ri-calendar-2-line"></i>
                                                <div class="stat-block">
                                                    <b>Sat, May 25, 2020</b>
                                                    <span>10:AM to 6:PM</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <i class="icon-color2 ri-map-pin-line"></i>
                                                <div class="stat-block">
                                                    <b>Central Park</b>
                                                    <span>Manhattan, New york City</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head"><strong>Lorem Ipsum Heading</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="common-table">
                                <!-- Table -->
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($x = 0; $x <= 4; $x++) { ?>
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <ul class="statlist vertical">
                                                    <li class="mb0">
                                                        <div class="d-flex align-center">
                                                            <i class="icon-color1 ri-calendar-2-line"></i>
                                                            <div class="stat-block">
                                                                <span>Lorem Ipsum</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php for ($x = 0; $x <= 3; $x++) { ?>
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-head d-flex justify-space align-start">
                                            <strong>Notifications Alerts Panel</strong>
                                            <a href="#" class="viewsmall">View All</a>
                                        </div>
                                        <ul class="statlist vertical">
                                            <li>
                                                <div>
                                                    <i class="icon-color1 ri-notification-line"></i>
                                                    <div class="stat-block">
                                                        <b>230k</b>
                                                        <span>Users</span>
                                                    </div>
                                                </div>
                                                <small class="text-color1 normalfont">1 Hour Ago</small>
                                            </li>
                                            <li>
                                                <div>
                                                    <i class="icon-color2 ri-notification-line"></i>
                                                    <div class="stat-block">
                                                        <b>230k</b>
                                                        <span>Users</span>
                                                    </div>
                                                </div>
                                                <small class="text-color2 normalfont">1 Hour Ago</small>
                                            </li>
                                            <li>
                                                <div>
                                                    <i class="icon-color3 ri-notification-line"></i>
                                                    <div class="stat-block">
                                                        <b>230k</b>
                                                        <span>Users</span>
                                                    </div>
                                                </div>
                                                <small class="text-color3 normalfont">1 Hour Ago</small>
                                            </li>
                                            <li>
                                                <div>
                                                    <i class="icon-color4 ri-notification-line"></i>
                                                    <div class="stat-block">
                                                        <b>230k</b>
                                                        <span>Users</span>
                                                    </div>
                                                </div>
                                                <small class="text-color4 normalfont">1 Hour Ago</small>
                                            </li>
                                            <li>
                                                <div>
                                                    <i class="icon-color1 ri-notification-line"></i>
                                                    <div class="stat-block">
                                                        <b>230k</b>
                                                        <span>Users</span>
                                                    </div>
                                                </div>
                                                <small class="text-color1 normalfont">1 Hour Ago</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php include_once('footer.php'); ?>
            </div>
            <!--END PAGE CONTENT -->
        </div>
    </body>
</html>