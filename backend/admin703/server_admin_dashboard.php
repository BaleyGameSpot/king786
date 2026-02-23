<?php
include_once('../common.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!$userObj->hasPermission('manage-server-admin-dashboard')) {
    $userObj->redirect();
}

$script = "server_dashboard";
$server_info = $DASHBOARD_OBJ->getServerInfo();
$server_status_info =  $DASHBOARD_OBJ->serverStatusInfo();
if (isset($_REQUEST['GET_SERVER_DATA'])) {
	$returnArr['action'] = "1";
	$returnArr['message'] = $server_info;
	echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);
	exit;
}

if (isset($_REQUEST['GET_SERVER_STATUS_INFO'])) {
	$server_status_info =  $DASHBOARD_OBJ->serverStatusInfo();
	$returnArr['action'] = "1";
	$returnArr['action1'] = "1";
	$returnArr['message'] = $server_status_info;
	echo json_encode($returnArr, JSON_UNESCAPED_UNICODE);
	exit;
}

$conn_per = ($server_info['totalconnections'] != 0)
    ? round(($server_info['connections'] / $server_info['totalconnections']) * 100)
    : 0;
$sys_load_per = sys_getloadavg()[1] * 100;
$php_process_count = $server_info['php_process_count'] + mt_rand(100, 999);
$php_process_count = ($server_info['php_process_count'] / $php_process_count) * 100;

$SystemDiagnosticData = $DASHBOARD_OBJ->getSystemDiagnosticData();

$server_working = $server_missing = 0;
foreach ($SystemDiagnosticData as $SysData) {
	if ($SysData['value']) {
		$server_working++;
	} else {
		$server_missing++;
	}
}

$server_alerts = 3;
$server_status = ['Working', 'Errors', 'Alerts'];
$server_number = [$server_working, $server_missing, $server_alerts];

if (isset($_REQUEST['CLEAR_CACHE_DATA']) && strtoupper($_REQUEST['CLEAR_CACHE_DATA']) == "YES") {
	updateSystemData();

	header('Location: ' . $_SERVER['PHP_SELF']);
	exit;
}
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD-->

<head>
	<meta charset="UTF-8" />
	<title><?= $SITE_NAME; ?> | Server Monitoring</title>
	<meta content="width=device-width, initial-scale=1.0" name="viewport" />
	<!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
	<!-- GLOBAL STYLES -->
	<?php include_once('global_files.php'); ?>
	<link rel="stylesheet" href="css/style.css" />
	<link rel="stylesheet" href="css/new_main.css" />
	<link rel="stylesheet" href="css/admin_new/dashboard.css">
	<link rel="stylesheet" href="css/requirement.css" />
	<script src="js/apexcharts.js"></script>
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

<body class="padTop53 dasboard-main-responsive">

	<!-- MAIN WRAPPER -->
	<div id="wrap">
		<?php include_once('header.php'); ?>
		<!--PAGE CONTENT -->
		<div id="content" class="content_right">
			<div class="cintainerinner">
				<div class="row clearfix d-flex">
					<div class="col-sm-12 col-md-6 col-xl-4">
						<div class="card">
							<div class="card-body">
								<div class="card-head">
									<strong>Hi <?= clearName($_SESSION['sess_vAdminFirstName']) ?>!</strong>
								</div>
								<p>Welcome to System Dashboard. Here you will find the important sections of system and statistics.</p>
							</div>
						</div>
					</div>
					<div class="col-sm-12 col-md-6 col-xl-4">
						<div class="card">
							<div class="card-body">
								<ul class="statlist vertical">
									<li id="php_process">
										<div class="d-flex justify-start align-items-center">
											<i class="icon-color1"><img src="img/icons/notification.svg" alt=""></i>
											<div class="stat-block">
												<b>PHP Process</b>
												<span class="stat-block-sub">No. of processes running</span>
											</div>
											<div class="data-count">--</div>
										</div>

									</li>
									<li id="node_process" class="mb0">
										<div class="d-flex justify-start align-items-center">
											<i class="icon-color2"><img src="img/icons/notification-black.svg" alt=""></i>
											<div class="stat-block">
												<b>Node Process</b>
												<span class="stat-block-sub">No. of processes running</span>
											</div>
											<div class="data-count">--</div>
										</div>

									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="com-sm-12 col-md-12 col-xl-4">
						<div class="card">
							<div class="card-body">
								<ul class="statlist vertical">
									<li id="httpd_process">
										<div class="d-flex justify-start align-items-center">
											<i class="icon-color1"><img src="img/icons/notification.svg" alt=""></i>
											<div class="stat-block">
												<b>HTTPD Process</b>
												<span class="stat-block-sub">No. of processes running</span>
											</div>
											<div class="data-count">--</div>
										</div>

									</li>
									<li class="mb0">
										<div class="d-flex justify-start align-items-center">
											<i class="icon-color2"><img src="img/icons/notification-black.svg" alt=""></i>
											<div class="stat-block">
												<b>Concurrent Apache Connections</b>
												<span class="stat-block-sub">No. of processes running</span>
											</div>
											<div id="concurrent_apache_connections" class="data-count">--</div>
										</div>

									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="row clearfix d-flex">
					<div class="col-md-12 col-xl-8">
						<div class="row d-flex clearfix">
							<div id="cpuLoad" class="col-xs-12 col-sm-6 col-md-6 col-xl-3">
								<div class="card">
									<div class="card-body">
										<div class="server-usage">
											<i class="icon-color1 ri-cpu-line"></i>
											<div class="stat-block">
												<span class="stat-block-sub">CPU</span>
												<b class="text-color1">--</b>
											</div>
											<small class="progressline-bar"><span class="bgcolor1"></span></small>
										</div>
									</div>
								</div>
							</div>
							<div id="ramUsed" class="col-xs-12 col-sm-6 col-md-6 col-xl-3">
								<div class="card">
									<div class="card-body">
										<div class="server-usage">
											<i class="icon-color2 ri-window-line"></i>
											<div class="stat-block">
												<span class="stat-block-sub">RAM</span>
												<b id="ramUsednum" class="text-color2">--</b>
											</div>
											<small class="progressline-bar"><span style="transition: width 2s ease 0s;" class="bgcolor2"></span></small>
										</div>
									</div>
								</div>
							</div>
							<div id="diskSize" class="col-xs-12 col-sm-6 col-md-6 col-xl-3">
								<div class="card">
									<div class="card-body">
										<div class="server-usage">
											<i class="icon-color3 ri-u-disk-line"></i>
											<div class="stat-block">
												<span class="stat-block-sub">DISK</span>
												<b id="diskused" class="text-color3">--</b>
											</div>
											<small class="progressline-bar"><span style="transition: width 2s ease 0s;" class="bgcolor3"></span></small>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-6 col-md-6 col-xl-3">
								<div class="card">
									<div class="card-body">
										<div class="server-usage">
											<i class="icon-color5 ri-global-line"></i>
											<div class="stat-block">
												<span class="stat-block-sub">CONNECTIONS</span>
												<div id="connections_chart"></div>
											</div>

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-xl-4">
						<div class="card">
							<div class="card-body pb0">
								<div class="card-head d-flex justify-space align-start">
									<strong class="mb0">Server Statistics <small class="small-subtext">Last Updated: <?= date('d M Y') . " AT " . date('h:i A') ?></small></strong>
									<a href="#system_diagnostics" class="viewsmall system_diagnostics">View</a>
								</div>
								<div id="serverStatuschart"></div>
							</div>
							<div class="jobsrow">
								<div class="jobscol">
									<div class="card-foot">
										<strong>Working</strong>
										<span class="count success-color"><?= $server_working ?></span>
									</div>
								</div>
								<div class="jobscol">
									<div class="card-foot">
										<strong>Errors</strong>
										<span class="count pending-color"><?= $server_missing ?></span>
									</div>
								</div>
								<div class="jobscol">
									<div class="card-foot">
										<strong>Alerts</strong>
										<span class="count proccess-color"><?= $server_alerts ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
						<div class="card">
							<div class="card-body">
								<div class="card-head">
									<strong>CPU Usage (%)</strong>
								</div>
								<div style="display:none" id="disk_graph"></div>
								<div id="ram_graph1"></div>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
						<div class="card">
							<div class="card-body">
								<div class="card-head">
									<strong>RAM Usage (%)</strong>
								</div>
								<div id="ram_graph"></div>
							</div>
						</div>
					</div>
					<!-- TODO: dynamic chart:- -->
					<!-- <div class="col-md-12 col-xl-12">
						<div class="card">
							<div class="card-body">
								<div class="card-head">
									<strong>dynamic chart test div</strong>
								</div>
								<div id="ram_graph1"></div>
							</div>
						</div>
					</div> -->

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
						<div class="card">
							<div class="card-body">
								<div class="card-head">
									<strong>Disk Usage Statistics</strong>
									<div id="disk_usage_chart"></div>
								</div>
							</div>
							<div class="jobsrow">
								<div class="jobscol">
									<div class="card-foot">
										<strong>Disk Used</strong>
										<span class="count pending-color"><?= $server_info['diskused']; ?> GB</span>
									</div>
								</div>
								<div class="jobscol">
									<div class="card-foot">
										<strong>Disk Free</strong>
										<span class="count success-color"><?= $server_info['diskfree']; ?> GB</span>
									</div>
								</div>
								<div class="jobscol">
									<div class="card-foot">
										<strong>Total</strong>
										<span class="count proccess-color"><?= $server_info['disktotal']; ?> GB</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
						<div class="card">
							<div class="card-body">
								<div class="card-head">
									<strong>RAM Usage Statistics</strong>
									<div id="ram_usage_chart"></div>
								</div>
							</div>
							<div class="jobsrow">
								<div class="jobscol">
									<div class="card-foot">
										<strong>RAM Used</strong>
										<span id = "memused" class="count pending-color"></span>
									</div>
								</div>
								<div class="jobscol">
									<div class="card-foot">
										<strong>RAM Free</strong>
										<span id = "memavailable" class="count success-color"></span>
									</div>
								</div>
								<div class="jobscol">
									<div class="card-foot">
										<strong>Total</strong>
										<span id = "memtotal" class="count proccess-color"></span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12 formob767" id="system_diagnostics">
						<div class="card">
							<div class="card-body">
								<div class="card-head d-flex justify-space align-start">
									<strong>System Diagnostic / Error Console</strong>
									<a href="<?= $_SERVER['PHP_SELF'] ?>?CLEAR_CACHE_DATA=Yes" class="viewsmall">Clear Cache</a>
								</div>
								<div class="common-table">
									<!-- Table -->
									<table class="table">
										<thead>
											<tr>
												<th>System Settings</th>
												<th class="text-center" style="width: 300px;">Status</th>
												<th class="text-center" style="width: 300px;">Action</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($SystemDiagnosticData as $SysData) { ?>
												<tr>
													<td><?= $SysData['title'] . (isset($SysData['subtitle']) ? '<br>' . $SysData['subtitle'] : '')  ?></td>
													<td class="text-center">
														<?php if ($SysData['value']) { ?>
															<span class="icon server-status-icon success-color"><i class="ri-check-line"></i></span>
														<?php } else { ?>
															<span class="icon server-status-icon pending-color"><i class="ri-alert-line"></i></span>
														<?php } ?>
													</td>

													<td class="text-center">
														<a href="javascript:void(0);" class="viewsmall" onclick="openRequirementsModal('<?= $SysData['modal_id'] ?>')">View</a>
													</td>
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

			<!-- HTML -->
			<?php include_once('footer.php'); ?>
		</div>
		<!--END PAGE CONTENT -->
	</div>

	<?php if(isset($_SESSION['gcs_error'])) { ?>
	<div class="modal fade" tabindex="-1" role="dialog" id="gcs_error_modal" data-keyboard="true" data-backdrop="static" style="font-size: 15px">
	    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	        <div class="modal-content" style="width: 100%">
	            <div class="modal-body">
	                <p>Something went wrong while clearing the cache.</p>
	                <?php if(isJsonTextGT($_SESSION['gcs_error'])) { ?>
	                <pre><?= json_encode(json_decode($_SESSION['gcs_error'], true), JSON_PRETTY_PRINT); ?></pre>
	                <?php } else { ?>
	                <pre><?= $_SESSION['gcs_error'] ?></pre>
	                <?php } ?>
	                <div class="text-center">
	                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('gcs_error_modal')">Close</a>
	                </div>
	            </div>
	        </div>
	    </div>
	</div>
	<?php } ?>

	<script>
		var concurrentApacheConnectionsLastFive = [];
		$(document).ready(function(){
			$(document).on('click','.system_diagnostics',function(e) {
				e.preventDefault();
				var getElem = $(this).attr('href');
				var targetDistance = 20;

				var getOffset = $(getElem).offset().top;
				
				$('#wrap').animate({
					scrollTop: getOffset - targetDistance
				}, 500);

				return false;
			});

			<?php if(isset($_SESSION['gcs_error'])) { ?>
				$('#gcs_error_modal').modal('show');
			<?php unset($_SESSION['gcs_error']); } ?>
		})
		var cpuUsageSeries = [];
		var ramUsageSeries = [];
		var connectionSeries = [];

		function Series() {
			var series = [];
			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>server_admin_dashboard.php',
				'AJAX_DATA': "GET_SERVER_STATUS_INFO=Yes",
				'REQUEST_DATA_TYPE': 'json',
                'REQUEST_ASYNC' :false,
			};
			getDataFromAjaxCall(ajaxData, function(response) {
				if (response.action == "1") {
					var data = response.result;

					/* -------------------------------- TOTAL_RAM ------------------------------- */
					var total_ram = data.message.TOTAL_RAM[data.message.TOTAL_RAM.length - 1];
					/* -------------------------------- TOTAL_RAM ------------------------------- */
					/* ------------------------------ AVAILABLE_RAM ----------------------------- */
					var available = data.message.AVAILABLE_RAM[data.message.AVAILABLE_RAM.length - 1];
					/* ------------------------------ AVAILABLE_RAM ----------------------------- */
					/* ------------------------------ RAM_USED_PERCENTAGE ------------------------------ */
					var ramUsed = data.message.RAM_USED_PERCENTAGE[data.message.RAM_USED_PERCENTAGE.length - 1];

					ram_usage_chart(total_ram, available, ramUsed);

					var count = data.message.CONCURRENT_APACHE_CONNECTIONS.length;
					var i = (data.message.CONCURRENT_APACHE_CONNECTIONS.length - 5);

					while (i < count) {
						x = new Date(data.message.COMMAND_EXECUTED_TIME[i]).getTime();
						y = data.message.CONCURRENT_APACHE_CONNECTIONS[i].trim();
						series.push([x, y]);
						i++;
					}
				}
			});

			return series;
		}


		function generateMinuteWiseTimeSeries_() {
			var i = 0;
			var series = [];

			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>server_admin_dashboard.php',
				'AJAX_DATA': "GET_SERVER_STATUS_INFO=Yes",
				'REQUEST_DATA_TYPE': 'json'
			};
			getDataFromAjaxCall(ajaxData, function(response) {
				if (response.action == "1") {
					var data = response.result;

					var count = data.message.CURRENT_CPU_USAGE.length;
					while (i < count) {
						x = new Date(data.message.COMMAND_EXECUTED_TIME[i]).getTime();
						y = data.message.CURRENT_CPU_USAGE[i];
						series.push([x, y]);
						i++;
					}
				}

			});

			return series;

		}


		function generateMinuteWiseTimeSeriesRam_() {
			var i = 0;
			var series = [];

			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>server_admin_dashboard.php',
				'AJAX_DATA': "GET_SERVER_STATUS_INFO=Yes",
				'REQUEST_DATA_TYPE': 'json'
			};
			getDataFromAjaxCall(ajaxData, function(response) {
				if (response.action == "1") {
					var data = response.result;

					var count = data.message.RAM_USED_PERCENTAGE.length;
					while (i < count) {
						x = new Date(data.message.COMMAND_EXECUTED_TIME[i]).getTime();
						y = data.message.RAM_USED_PERCENTAGE[i];
						series.push([x, y]);
						i++;
					}
				}

			});

			return series;

		}

		function getRandom() {

			var i = iteration;
			return (
				(Math.sin(i / trigoStrength) * (i / trigoStrength) +
					i / trigoStrength +
					1) *
				(trigoStrength * 2)
			);
		}



		var optionsLine = {
			chart: {
				height: 350,
				type: "area",
				stacked: true,
				animations: {
					enabled: true,
					easing: 'linear',
					dynamicAnimation: {
						speed: 1000
					}
				},
				events: {
					animationEnd: function(chartCtx) {
						const newData1 = chartCtx.w.config.series[0].data.slice();
						newData1.shift();
					}
				},

				toolbar: {
					show: false
				},
				zoom: {
					enabled: false
				}
			},
			dataLabels: {
				enabled: false
			},
			stroke: {
				curve: "straight",
				width: 5
			},
			grid: {
				padding: {
					left: 0,
					right: 0
				}
			},
			markers: {
				size: 0,
				hover: {
					size: 0
				}
			},
			series: [{
				name: "CPU Usage",
				data: generateMinuteWiseTimeSeries_(),

			}],
			xaxis: {
				type: "datetime",
				range: 2700000,
				labels: {
					formatter: function(value) {
						var formattedDate = new Date(value);

						const h = formattedDate.getHours();
						const i = formattedDate.getMinutes();

						const hh = h < 10 ? `0${h}` : h.toString();
						const ii = i < 10 ? `0${i}` : i.toString();

						return hh + ':' + ii;
					}
				}
			},

			yaxis: {
				min: 0,
				max: 100,
				tickAmount: 5,
				labels: {
					formatter: function(value) {
						return Math.round(value);
					}
				}
			},
			tooltip: {
				x: {
					formatter: function(value) {
						var formattedDate = new Date(value);
						
						const y = formattedDate.getFullYear();
						const m = formattedDate.getMonth() + 1;
						const d = formattedDate.getDate();
						const h = formattedDate.getHours();
						const i = formattedDate.getMinutes();
						
						const yyyy = y.toString();
						const mm = m < 10 ? `0${m}` : m.toString();
						const dd = d < 10 ? `0${d}` : d.toString();
						const hh = h < 10 ? `0${h}` : h.toString();
						const ii = i < 10 ? `0${i}` : i.toString();
						
						return dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + ii;
					}
				},
				y: {
					formatter: function(value, {
						series,
						seriesIndex,
						dataPointIndex,
						w
					}) {
						return value + "%"
					}
				},
			},
			legend: {
				show: true,
				floating: true,
				horizontalAlign: "left",
				onItemClick: {
					toggleDataSeries: false
				},
				position: "top",
				offsetY: -33,
				offsetX: 60
			}
		};

		var optionsLineRam = {
			chart: {
				height: 350,
				type: "area",
				stacked: true,
				animations: {
					enabled: true,
					easing: 'linear',
					dynamicAnimation: {
						speed: 1000
					}
				},
				events: {
					animationEnd: function(chartCtx) {
						const newData1 = chartCtx.w.config.series[0].data.slice();
						newData1.shift();
					}
				},

				toolbar: {
					show: false
				},
				zoom: {
					enabled: false
				}
			},
			dataLabels: {
				enabled: false
			},
			stroke: {
				curve: "straight",
				width: 5
			},
			grid: {
				padding: {
					left: 0,
					right: 0
				}
			},
			markers: {
				size: 0,
				hover: {
					size: 0
				}
			},
			series: [{
				name: "RAM Usage",
				data: generateMinuteWiseTimeSeriesRam_(),

			}],
			xaxis: {
				type: "datetime",
				range: 2700000,
				labels: {
					formatter: function(value) {
						var formattedDate = new Date(value);

						const h = formattedDate.getHours();
						const i = formattedDate.getMinutes();

						const hh = h < 10 ? `0${h}` : h.toString();
						const ii = i < 10 ? `0${i}` : i.toString();

						return hh + ':' + ii;
					}
				}
			},

			yaxis: {
				min: 0,
				max: 100,
				tickAmount: 5,
				labels: {
					formatter: function(value) {
						return Math.round(value);
					}
				}
			},
			tooltip: {
				x: {
					formatter: function(value) {
						var formattedDate = new Date(value);

						const y = formattedDate.getFullYear();
						const m = formattedDate.getMonth() + 1;
						const d = formattedDate.getDate();
						const h = formattedDate.getHours();
						const i = formattedDate.getMinutes();

						const yyyy = y.toString();
						const mm = m < 10 ? `0${m}` : m.toString();
						const dd = d < 10 ? `0${d}` : d.toString();
						const hh = h < 10 ? `0${h}` : h.toString();
						const ii = i < 10 ? `0${i}` : i.toString();

						return dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + ii;
					}
				},
				y: {
					formatter: function(value, {
						series,
						seriesIndex,
						dataPointIndex,
						w
					}) {
						return value + "%"
					}
				},
			},
			legend: {
				show: true,
				floating: true,
				horizontalAlign: "left",
				onItemClick: {
					toggleDataSeries: false
				},
				position: "top",
				offsetY: -33,
				offsetX: 60
			}
		};


		var connectionLine1 = {
			series: [{
				name: 'Connections',
				data: Series()
			}],
			chart: {
				height: 80,
				type: 'line',
				animations: {
					enabled: true,
					easing: 'linear',
					dynamicAnimation: {
						speed: 1000
					}
				},
				toolbar: {
					show: false,
					tools: {
						download: false
					}
				}
			},
			dataLabels: {
				enabled: false
			},
			stroke: {
				width: 3
			},
			grid: {
				show: false,
			},
			markers: {
				size: 2,
				colors: ['#ff9f43'],
				strokeColors: ['#ff9f43'],
				strokeWidth: 2,
				strokeOpacity: 1,
				strokeDashArray: 0,
				fillOpacity: 1,
				discrete: [{
					seriesIndex: 0,
					dataPointIndex: 5,
					fillColor: "#ffffff",
					strokeColor: ['#ff9f43'],
					size: 5
				}],
				shape: "circle",
				radius: 2,
				hover: {
					size: 3
				}
			},
			xaxis: {
				show: false,
				labels: {
					show: false,
					style: {
						fontSize: "0px",
					}
				},
				categories: [],
				axisBorder: {
					show: false,
				},
				axisTicks: {
					show: false,
				}

			},
			yaxis: {
				show: false,
				labels: {
					show: false,
					style: {
						fontSize: "0px",
					}
				},
			},
			legend: {
				show: false
			},
			colors: ['#ff9f43'],
			tooltip: {
				x: {
					formatter: function(value) {
						var formattedDate = new Date(value);

						const y = formattedDate.getFullYear();
						const m = formattedDate.getMonth() + 1;
						const d = formattedDate.getDate();
						const h = formattedDate.getHours();
						const i = formattedDate.getMinutes();

						const yyyy = y.toString();
						const mm = m < 10 ? `0${m}` : m.toString();
						const dd = d < 10 ? `0${d}` : d.toString();
						const hh = h < 10 ? `0${h}` : h.toString();
						const ii = i < 10 ? `0${i}` : i.toString();

						return dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + ii;
					}
				},
				y: {
					style: {
						fontSize: '7px',
					},
					formatter: function(value, {
						series,
						seriesIndex,
						dataPointIndex,
						w
					}) {
						return value
					},

				},
			},
		};

		var chartLine = '';
		var chartLineRam = '';
		var chartConnectionRam;
		$(document).ready(function() {
			setTimeout(function() {
				var disk_usage_chart = new ApexCharts(document.querySelector("#disk_usage_chart"), disk_usage_chart_options);
				disk_usage_chart.render();

				// var ram_usage_chart = new ApexCharts(document.querySelector("#ram_usage_chart"), ram_usage_chart_options);
				// ram_usage_chart.render();

				var serverStatuschart = new ApexCharts(document.querySelector("#serverStatuschart"), serverStatuschartOptions);
				serverStatuschart.render();

				chartLine = new ApexCharts(document.querySelector("#ram_graph1"), optionsLine);
				chartLine.render();

				chartLineRam = new ApexCharts(document.querySelector("#ram_graph"), optionsLineRam);
				chartLineRam.render();


				chartConnectionRam = new ApexCharts(document.querySelector("#connections_chart"), connectionLine1);
				chartConnectionRam.render();
				setServerInfo_();

			}, 50);
		});

		var disk_usage_chart_options = {
			series: [<?= $server_info['diskfree']; ?>, <?= $server_info['diskused']; ?>],
			labels: ["Disk Free (<?= $server_info['diskfree']; ?> GB)", "Disk Used (<?= $server_info['diskused']; ?> GB)"],
			chart: {
				height: 200,
				type: 'donut',
			},
			dataLabels: {
				enabled: false
			},
			fill: {
				colors: ['#28c76f', '#ea5455']
			},
			legend: {
				show: true,

				markers: {
					fillColors: ['#28c76f', '#ea5455'],
				},
			},
			tooltip: {
				fillSeriesColor: true,
				y: {
					formatter: function(value, {
						series,
						seriesIndex,
						dataPointIndex,
						w
					}) {
						return ""
					}
				},
			},
			colors: ['#28c76f', '#ea5455']
		};








		var server_status = <?php echo json_encode($server_status); ?>;
		var server_number = <?php echo json_encode($server_number); ?>;

		var serverStatuschartOptions = {
			series: server_number,
			labels: server_status,
			chart: {
				type: 'donut',
				height: 100,
				animations: {
					enabled: true,
				}
			},
			dataLabels: {
				enabled: false
			},
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						width: 200
					},
					legend: {
						position: 'bottom'
					}
				}
			}],
			fill: {
				colors: ['#28c76f', '#ea5455', '#ff9900']
			},
			legend: {
				show: true,
				offsetY: -5,
				markers: {
					fillColors: ['#28c76f', '#ea5455', '#ff9900']
				},
			},
			tooltip: {
				fillSeriesColor: true,
			},
			colors: ['#28c76f', '#ea5455', '#ff9900'],
			grid: {
				padding: {
					top: 10,
					bottom: 10,
				}
			}
		};



		function setServerInfo() {
			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>server_admin_dashboard.php',
				'AJAX_DATA': "GET_SERVER_DATA=Yes",
				'REQUEST_DATA_TYPE': 'json'
			};
			getDataFromAjaxCall(ajaxData, function(response) {
				if (response.action == "1") {
					var data = response.result;
				} else {
					$("#imageIcons").hide();
				}
			});
		}


		function setServerInfo_() {

			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>server_admin_dashboard.php',
				'AJAX_DATA': "GET_SERVER_STATUS_INFO=Yes",
				'REQUEST_DATA_TYPE': 'json'
			};
			getDataFromAjaxCall(ajaxData, function(response) {
				if (response.action == "1") {

					var data = response.result;
					/* ---------------------------- PHP_PROCESS_COUNT --------------------------- */
					$("#php_process .data-count").text(data.message.PHP_PROCESS_COUNT.pop());
					/* ---------------------------- PHP_PROCESS_COUNT --------------------------- */
					/* --------------------------- NODE_PROCESS_COUNT --------------------------- */
					$("#node_process .data-count").text(data.message.NODE_PROCESS_COUNT.pop());
					/* --------------------------- NODE_PROCESS_COUNT --------------------------- */
					/* --------------------------- HTTPD_PROCESS_COUNT -------------------------- */
					$("#httpd_process .data-count").text(data.message.HTTPD_PROCESS_COUNT.pop());
					/* --------------------------- HTTPD_PROCESS_COUNT -------------------------- */

					/* ---------------------- CONCURRENT_APACHE_CONNECTIONS --------------------- */

					var concurrentApacheConnections = data.message.CONCURRENT_APACHE_CONNECTIONS[data.message.CONCURRENT_APACHE_CONNECTIONS.length - 1];
					var time = data.message.COMMAND_EXECUTED_TIME[data.message.COMMAND_EXECUTED_TIME.length - 1];
					$("#concurrent_apache_connections").text(concurrentApacheConnections);

					//last five value get
					concurrentApacheConnectionsLastFive = [];
					for (let i = (data.message.CONCURRENT_APACHE_CONNECTIONS.length - 5); i < data.message.CONCURRENT_APACHE_CONNECTIONS.length; i++) {
						concurrentApacheConnectionsLastFive.push(data.message.CONCURRENT_APACHE_CONNECTIONS[i]);
					}

					//connection_chart(concurrentApacheConnectionsLastFive);
					dynamic_chart_connection(concurrentApacheConnections, time);
					/* ---------------------- CONCURRENT_APACHE_CONNECTIONS --------------------- */

					/* -------------------------------- TOTAL_RAM ------------------------------- */
					var total_ram = data.message.TOTAL_RAM[data.message.TOTAL_RAM.length - 1];
					/* -------------------------------- TOTAL_RAM ------------------------------- */
					/* ------------------------------ AVAILABLE_RAM ----------------------------- */
					var available = data.message.AVAILABLE_RAM[data.message.AVAILABLE_RAM.length - 1];
					/* ------------------------------ AVAILABLE_RAM ----------------------------- */
					/* ------------------------------ RAM_USED_PERCENTAGE ------------------------------ */
					var ramUsed = data.message.RAM_USED_PERCENTAGE[data.message.RAM_USED_PERCENTAGE.length - 1];

					//ram_usage_chart(total_ram, available, ramUsed);
					var time = data.message.COMMAND_EXECUTED_TIME[data.message.COMMAND_EXECUTED_TIME.length - 1];
					var ramUsed2 = data.message.RAM_USED_PERCENTAGE[data.message.RAM_USED_PERCENTAGE.length - 2];
					if (ramUsed2 > ramUsed) {
						$arrow = 'down';
					} else {
						$arrow = 'up';
					}
					
					var ramUsedCss = {
						"transition": "width 2s ease 0s",
					}
					$("#ramUsed #ramUsednum").html(ramUsed + "%<i class='ri-arrow-" + $arrow + "-line'></i>");
					$("#ramUsed .progressline-bar .bgcolor2").css(ramUsedCss);
					$("#ramUsed .progressline-bar .bgcolor2").width(parseFloat(ramUsed) + '%');
					//ram_graph(data.message.RAM_USED_PERCENTAGE, data.message.COMMAND_EXECUTED_TIME);

					dynamic_chart_ram(ramUsed, time);
					/* ------------------------------ RAM_USED_PERCENTAGE ------------------------------ */

					/* ------------------------------ CURRENT_CPU_USAGE ------------------------------ */

					var cpuUsage = data.message.CURRENT_CPU_USAGE[data.message.CURRENT_CPU_USAGE.length - 1];
					var time = data.message.COMMAND_EXECUTED_TIME[data.message.COMMAND_EXECUTED_TIME.length - 1];
					var cpuUsage2 = data.message.CURRENT_CPU_USAGE[data.message.CURRENT_CPU_USAGE.length - 2];
					if (cpuUsage2 > cpuUsage) {
						$arrow = 'down';
					} else {
						$arrow = 'up';
					}
					var cpuUsageCss = {
						"transition": "width 2s ease 0s",
					}
					$("#cpuLoad .text-color1").html(cpuUsage + "%<i class='ri-arrow-" + $arrow + "-line'></i>");
					$("#cpuLoad .progressline-bar .bgcolor1").css(cpuUsageCss);
					$("#cpuLoad .progressline-bar .bgcolor1").width(parseFloat(cpuUsage) + '%');
					//disk_graph(data.message.CURRENT_CPU_USAGE, data.message.COMMAND_EXECUTED_TIME);
					dynamic_chart(cpuUsage, time);
					/* ------------------------------ CURRENT_CPU_USAGE ------------------------------ */

					/* ------------------------------ USED_DISK_SIZE_PERCENTAGE ------------------------------ */
					var UsedDiskSizePercentage = data.message.USED_DISK_SIZE_PERCENTAGE[data.message.USED_DISK_SIZE_PERCENTAGE.length - 1];
					var UsedDiskSizePercentage2 = data.message.USED_DISK_SIZE_PERCENTAGE[data.message.USED_DISK_SIZE_PERCENTAGE.length - 2];
					if (UsedDiskSizePercentage2 > UsedDiskSizePercentage) {
						$arrow = 'down';
					} else {
						$arrow = 'up';
					}
					var diskSizeCss = {
						"transition": "width 2s ease 0s",
					}
					$("#diskSize .text-color3").html(UsedDiskSizePercentage + "%<i class='ri-arrow-down-line'></i>");
					$("#diskSize .progressline-bar .bgcolor3").css(diskSizeCss);
					$("#diskSize .progressline-bar .bgcolor3").width(parseFloat(UsedDiskSizePercentage) + '%');
					/* ------------------------------ USED_DISK_SIZE_PERCENTAGE ------------------------------ */


				} else {

				}

				setTimeout(setServerInfo_, 7500);
			});

		}

		function dynamic_chart(cpuUsage, time) {
			x = new Date(time).getTime();
			
			chartLine.updateSeries([{
				data: [
					...chartLine.w.config.series[0].data,
					[x, cpuUsage]
				]
			}]);
		}

		function dynamic_chart_ram(cpuUsage, time) {
			x = new Date(time).getTime();
			chartLineRam.updateSeries([{
				data: [
					...chartLineRam.w.config.series[0].data,
					[x, cpuUsage]
				]
			}]);

		}

		function dynamic_chart_connection(cpuUsage, time) {
			x = new Date(time).getTime();
			if (chartConnectionRam.w.config.length > 0) {
				chartConnectionRam.updateSeries([{
					data: [
						...chartConnectionRam.w.config.series[0].data,
						[x, cpuUsage]
					]
				}]);
			}

		}

		function disk_graph(CURRENT_CPU_USAGE, COMMAND_EXECUTED_TIME) {
			$("#disk_graph").html('');
			var disk_graph_options = {
				series: [{
					name: 'CPU Usage',
					data: CURRENT_CPU_USAGE
				}],
				chart: {
					height: 300,
					type: 'area',
					toolbar: {
						show: false,
						tools: {
							download: false
						}
					},
					animations: {
						enabled: true,
					},
					redrawOnParentResize: true
				},
				dataLabels: {
					enabled: false
				},
				stroke: {
					curve: 'smooth'
				},
				xaxis: {
					type: 'datetime',
					categories: COMMAND_EXECUTED_TIME,
					tickAmount: 5,
					labels: {
						formatter: function(value) {
							var formattedDate = new Date(value);

							const h = formattedDate.getHours();
							const i = formattedDate.getMinutes();

							const hh = h < 10 ? `0${h}` : h.toString();
							const ii = i < 10 ? `0${i}` : i.toString();

							return hh + ':' + ii;
						}
					}

				},
				yaxis: {
					min: 0,
					max: 100,
					tickAmount: 5,
					labels: {
						formatter: function(value) {

							return Math.round(value);
						}
					}
				},
				tooltip: {
					x: {
						formatter: function(value) {
							var formattedDate = new Date(value);

							const y = formattedDate.getFullYear();
							const m = formattedDate.getMonth() + 1;
							const d = formattedDate.getDate();
							const h = formattedDate.getHours();
							const i = formattedDate.getMinutes();

							const yyyy = y.toString();
							const mm = m < 10 ? `0${m}` : m.toString();
							const dd = d < 10 ? `0${d}` : d.toString();
							const hh = h < 10 ? `0${h}` : h.toString();
							const ii = i < 10 ? `0${i}` : i.toString();

							return dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + ii;
						}
					},
					y: {
						formatter: function(value, {
							series,
							seriesIndex,
							dataPointIndex,
							w
						}) {
							return value + "%"
						}
					},
				},
			};

			var disk_graph = new ApexCharts(document.querySelector("#disk_graph"), disk_graph_options);
			disk_graph.render();
		}

		function ram_graph(RAM_USED_PERCENTAGE, COMMAND_EXECUTED_TIME) {

			$("#ram_graph").html('');
			var ram_graph_options = {
				series: [{
					name: 'RAM Usgae',
					data: RAM_USED_PERCENTAGE
				}],
				chart: {
					height: 300,
					type: 'area',
					toolbar: {
						show: false,
						tools: {
							download: false
						}
					},
					animations: {
						enabled: false,
						easing: 'linear',
						dynamicAnimation: {
							speed: 1000
						}
					},
				},
				dataLabels: {
					enabled: false
				},
				stroke: {
					curve: 'smooth'
				},
				xaxis: {
					type: 'datetime',
					categories: COMMAND_EXECUTED_TIME,
					tickAmount: 5,
					labels: {
						formatter: function(value) {
							var formattedDate = new Date(value);
							const h = formattedDate.getHours();
							const i = formattedDate.getMinutes();

							const hh = h < 10 ? `0${h}` : h.toString();
							const ii = i < 10 ? `0${i}` : i.toString();

							return hh + ':' + ii;
						}
					}

				},
				yaxis: {
					min: 0,
					max: 100,
					tickAmount: 5,
					labels: {
						formatter: function(value) {
							return Math.round(value);
						}
					}
				},
				tooltip: {
					x: {
						formatter: function(value) {
							var formattedDate = new Date(value);

							const y = formattedDate.getFullYear();
							const m = formattedDate.getMonth() + 1;
							const d = formattedDate.getDate();
							const h = formattedDate.getHours();
							const i = formattedDate.getMinutes();

							const yyyy = y.toString();
							const mm = m < 10 ? `0${m}` : m.toString();
							const dd = d < 10 ? `0${d}` : d.toString();
							const hh = h < 10 ? `0${h}` : h.toString();
							const ii = i < 10 ? `0${i}` : i.toString();

							return dd + '-' + mm + '-' + yyyy + ' ' + hh + ':' + ii;

						}
					},
					y: {
						formatter: function(value, {
							series,
							seriesIndex,
							dataPointIndex,
							w
						}) {
							return value + "%"
						}
					},
				},
			};

			var ram_graph = new ApexCharts(document.querySelector("#ram_graph"), ram_graph_options);
			ram_graph.render();
		}

		function ram_usage_chart_1(total_ram, available, ramUsed) {
			var series = [parseInt(available), parseInt(ramUsed)];
			
			var ram_usage_chart_options = {
				series: [available, ramUsed],
				labels: ["RAM Free (ff GB)", "RAM Used (fff GB)"],
				chart: {
					height: 200,
					type: 'donut',
					animations: {
						enabled: true,
					}
				},
				dataLabels: {
					enabled: false
				},
				fill: {
					colors: ['#28c76f', '#ea5455']
				},
				legend: {
					show: true,

					markers: {
						fillColors: ['#28c76f', '#ea5455'],
					},
				},
				tooltip: {
					fillSeriesColor: true,
					y: {
						formatter: function(value, {
							series,
							seriesIndex,
							dataPointIndex,
							w
						}) {
							return ""
						}
					},
				},
				colors: ['#28c76f', '#ea5455']
			};

			var ram_usage_chart = new ApexCharts(document.querySelector("#ram_usage_chart"), ram_usage_chart_options);
			ram_usage_chart.render();

		}

		function ram_usage_chart(total_ram, available, ramUsed) {
			ramUsed = (total_ram - available).toFixed(2);
			$("#memused").text( ramUsed + ' GB');
			$("#memavailable").text(available + 'GB');
			$("#memtotal").text(total_ram + 'GB');
			
			var options = {
				series: [parseInt(available), parseInt(ramUsed)],
				labels: ["RAM Free ("+available+" GB)", "RAM Used ("+ramUsed+" GB)"],
				chart: {
					height: 200,
					type: 'donut',
					animations: {
						enabled: true,
					}
				},
				dataLabels: {
					enabled: false
				},
				fill: {
					colors: ['#28c76f', '#ea5455']
				},
				legend: {
					show: true,

					markers: {
						fillColors: ['#28c76f', '#ea5455'],
					},
				},
				tooltip: {
					fillSeriesColor: true,
					y: {
						formatter: function(value, {
							series,
							seriesIndex,
							dataPointIndex,
							w
						}) {
							return ""
						}
					},
				},
				colors: ['#28c76f', '#ea5455']
			
			};

			var chart = new ApexCharts(document.querySelector("#ram_usage_chart"), options);
			chart.render();

		}

        $(".system_diagnostics").on('click', function(event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function() {
                    window.location.hash = hash;
                });
            }
        });
	</script>
	<?php include_once('server_requirements.php'); ?>
	<script type="text/javascript" src="js/requirement_v3.js"></script>
</body>

</html>