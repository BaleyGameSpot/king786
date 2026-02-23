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
                        <div class="col-md-8 col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Congratulations <span class="hightlight">John!</span></strong>
                                    </div>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vulputate facilisis velit, vitae fermentum nulla ultrices et.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="statlist vertical">
                                        <li>
                                            <div>
                                                <i class="icon-color1"><img src="img/icons/notification.svg" alt=""></i>
                                                <div class="stat-block">
                                                    <b>Loads</b>
                                                    <span>Online Participant</span>
                                                </div>
                                            </div>
                                            <small class="progressline-bar"><span style="transition: width 2s ease 0s; width: 50%;"></span></small>
                                        </li>
                                        <li>
                                            <div>
                                                <i class="icon-color1"><img src="img/icons/notification-black.svg" alt=""></i>
                                                <div class="stat-block">
                                                    <b>Requests</b>
                                                    <span>Offline Participant</span>
                                                </div>
                                            </div>
                                            <small class="progressline-bar"><span style="transition: width 2s ease 0s; width: 80%;"></span></small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row clearfix d-flex">
                        <div class="col-md-8 col-xl-8">
                            <div class="row clearfix">
                                <div class="col-sm-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="server-usage">
                                                <i class="icon-color1 ri-cpu-line"></i>
                                                <div class="stat-block">
                                                    <span>CPU</span>
                                                    <b class="text-color1">4.8% <icon class="ri-arrow-up-line"></icon></b>
                                                    <small>Avg +65%</small>
                                                </div>
                                                <small class="progressline-bar"><span style="transition: width 2s ease 0s; width: 50%;" class="bgcolor1"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="server-usage">
                                                <i class="icon-color3 ri-window-line"></i>
                                                <div class="stat-block">
                                                    <span>RAM</span>
                                                    <b class="text-color3">4.2% <icon class="ri-arrow-down-line"></icon></b>
                                                    <small>Avg +85%</small>
                                                </div>
                                                <small class="progressline-bar"><span style="transition: width 2s ease 0s; width: 50%;" class="bgcolor3"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="server-usage">
                                                <i class="icon-color1 ri-u-disk-line"></i>
                                                <div class="stat-block">
                                                    <span>DISK</span>
                                                    <b class="text-color1">5.8GB <icon class="ri-arrow-up-line"></icon></b>
                                                    <small>Avg +36%</small>
                                                </div>
                                                <small class="progressline-bar"><span style="transition: width 2s ease 0s; width: 50%;" class="bgcolor1"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="server-usage">
                                                <i class="icon-color3 ri-global-line"></i>
                                                <div class="stat-block">
                                                    <span>SERVICES</span>
                                                    <b class="text-color3">3.5KB <icon class="ri-arrow-down-line"></icon></b>
                                                    <small>Avg +48%</small>
                                                </div>
                                                <small class="progressline-bar"><span style="transition: width 2s ease 0s; width: 50%;" class="bgcolor3"></span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-4">
                            <div class="card halfheight" style="background-color:#FF6E00">
                                <div class="card-body">
                                    <div class="row d-flex align-center">
                                        <div class="col-sm-6  d-flex align-center justify-start">
                                            <svg viewBox="0 0 36 36" class="circular-chart orange">
                                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                <path class="circle" stroke-dasharray="77, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                <text x="18" y="23" class="percentage">77</text>
                                            </svg>
                                            <div class="usage-label">Storage <br>Usage</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="card-head online-count">
                                                <strong>594875625</strong>
                                                <p>Online Users</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card halfheight">
                                <div class="card-body d-flex align-center">
                                    <img src="img/icons/testing.jpg" alt="" class="mr15">
                                    <ul class="common-listing">
                                        <li class="text-color1">Total Processes: 61<i class="ri-arrow-up-line"></i></li>
                                        <li class="text-color2">Total Threands: 993<i class="ri-arrow-down-line"></i></li>
                                        <li class="text-color3">Total Handles: 26957<i class="ri-arrow-up-line"></i></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Server Traffic Source</strong>
                                        <img src="img/icons/server-trafic.svg" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Lorem Ipsum</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 col-xl-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Bandwidth Public</strong>
                                        <img src="img/icons/bandwidth.svg" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-4">
                            <div class="card halfheight">
                                <div class="card-body d-flex align-center">
                                    <img src="img/icons/testing.jpg" alt="" class="mr15">
                                    <div class="listing-holder">
                                        <strong>Disk Usage</strong>
                                        <ul class="common-listing">
                                            <li class="text-color1">Max Usage</li>
                                            <li class="text-color2">Average Usage</li>
                                            <li class="text-color3">Minimum Usage</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card halfheight">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Load Average</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-6">
                            <div class="row d-flex">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="card-head">
                                                <strong>CPU Daily Usage</strong>
                                            </div>
                                            <img src="img/icons/speedtest.svg" alt="">
                                            <div class="cpureading">
                                                <h4>50.03%</h4>
                                                <p class="mb-0">CPU usage is <span class="text-color1">good</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card halfheight" style="background-color:#FF6E00">
                                        <div class="card-body d-flex align-center">
                                            <svg viewBox="0 0 36 36" class="circular-chart orange">
                                                <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                                <path class="circle" stroke-dasharray="77, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                                <text x="18" y="23" class="percentage">77</text>
                                            </svg>
                                            <div class="usage-label">Most Recent Alarams</div>
                                        </div>
                                    </div>
                                    <div class="card halfheight">
                                        <div class="card-body d-flex align-center">
                                            <img src="img/icons/testing.jpg" alt="" class="mr15">
                                            <div class="listing-holder">
                                                <strong>Heat Map</strong>
                                                <ul class="common-listing">
                                                    <li class="text-color1">Clear</li>
                                                    <li class="text-color2">Critical</li>
                                                    <li class="text-color3">Trouble</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="card-head">
                                        <strong>Ram Usage</strong>
                                    </div>
                                    <img src="img/icons/ram-usage.svg" alt="">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="common-table">
                                <div class="card-head">
                                    <strong>Active Instances</strong>
                                </div>
                                <!-- Table -->
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Servers</th>
                                            <th>IP Address</th>
                                            <th>Created</th>
                                            <th>Tag</th>
                                            <th>Provider</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($x = 0; $x <= 4; $x++) { ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-center justify-start">
                                                    <icon class="common-icon round ri-user-fill mr15 icon-color4"></icon>
                                                    <div class="small-combo">
                                                        <strong>Noveruche Admin</strong>
                                                        <p>8GB/80GB/SF02-Ubuntu Iconic- jfkakf-daksl...</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>192.168.130.26</td>
                                            <td>2 Months ago</td>
                                            <td>Web Server</td>
                                            <td>Indioserver</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include_once('footer.php'); ?>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        
    </body>
</html>