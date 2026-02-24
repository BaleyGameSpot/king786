<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$iCompanyId = $_SESSION['sess_iUserId'];
$menu_itemid = isset($_REQUEST['menu_itemid']) ? $_REQUEST['menu_itemid'] : "";

$iMenuItemId = isset($_REQUEST["iMenuItemId"]) ? $_REQUEST["iMenuItemId"] : '';
$Status = isset($_REQUEST["Status"]) ? $_REQUEST["Status"] : '';
$ssql = '';
$script = 'MenuItems';
$tbl_name = "menu_items";

if (!empty($_SESSION['sess_lang'])) {
	$default_lang = $_SESSION['sess_lang'];
}
if ($iMenuItemId != '' && $Status != '') {
	if (SITE_TYPE != '') {
		$query = "UPDATE menu_items SET eStatus = '" . $Status . "' WHERE iMenuItemId = '" . $iMenuItemId . "'";
		$obj->sql_query($query);
		$_REQUEST['success'] = '1';
		if ($Status == 'Active') {
			$var_msg = "Item activated successfully.";
		} else {
			$var_msg = "Item inactivated successfully.";
		}
	} else {
		header("Location:menuitems.php?success=2");
		exit;
	}
}

if (!empty($menu_itemid)) {
	$ssql .= " AND f.iFoodMenuId = '" . $menu_itemid . "'";
}

if ($action == 'delete') {
	if (SITE_TYPE != '') {
		$sql = "SELECT * FROM `$tbl_name` WHERE iMenuItemId = '" . $hdn_del_id . "'";
		$db_oldData = $obj->MySQLSelect($sql);
		if (!empty($db_oldData)) {
			$iDisplayOrder = $db_oldData[0]['iDisplayOrder'];
			$iFoodMenuId = $db_oldData[0]['iFoodMenuId'];

			$query = "UPDATE menu_items SET eStatus = 'Deleted' WHERE iMenuItemId = '" . $hdn_del_id . "'";
			$obj->sql_query($query);

			$DeleteMsg = $langage_lbl['LBL_MENU_ITEM_DELETE_MSG'];
			header("Location:menuitems.php?success=1&menu_itemid=" . $menu_itemid . "&var_msg=" . $DeleteMsg);
			exit();
		}
	} else {
		header("Location:menuitems.php?success=2");
		exit();
	}
}

if ($action == 'view') {
	$sql = "SELECT mi.*,f.vMenu_" . $default_lang . ",c.vCompany FROM  `menu_items` as mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId  WHERE 1=1 AND f.iCompanyId = '" . $iCompanyId . "' AND mi.eStatus != 'Deleted' $ssql";
	$data_drv = $obj->MySQLSelect($sql);
}

// Stats for summary cards
$sql_total   = "SELECT COUNT(*) as cnt FROM menu_items mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId WHERE f.iCompanyId = '$iCompanyId' AND mi.eStatus != 'Deleted'";
$sql_active  = "SELECT COUNT(*) as cnt FROM menu_items mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId WHERE f.iCompanyId = '$iCompanyId' AND mi.eStatus = 'Active'";
$sql_inactive = "SELECT COUNT(*) as cnt FROM menu_items mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId WHERE f.iCompanyId = '$iCompanyId' AND mi.eStatus = 'Inactive'";
$total_items    = $obj->MySQLSelect($sql_total);
$active_items   = $obj->MySQLSelect($sql_active);
$inactive_items = $obj->MySQLSelect($sql_inactive);
$cnt_total    = !empty($total_items[0]['cnt'])   ? $total_items[0]['cnt']   : 0;
$cnt_active   = !empty($active_items[0]['cnt'])  ? $active_items[0]['cnt']  : 0;
$cnt_inactive = !empty($inactive_items[0]['cnt'])? $inactive_items[0]['cnt']: 0;

// Food menu categories for filter
$sql_categories = "SELECT iFoodMenuId, vMenu_".$default_lang." as vMenuName FROM food_menu WHERE iCompanyId = '$iCompanyId' AND eStatus = 'Active' ORDER BY iDispOrder";
$db_categories = $obj->MySQLSelect($sql_categories);
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_MENU_ITEMS_FRONT']; ?></title>
	<!-- Default Top Script and css -->
	<?php include_once("top/top_script.php"); ?>
	<style>
		/* ===== Menu Items - Professional UI ===== */
		.mi-page-wrapper {
			padding: 0 0 40px 0;
		}

		/* --- Hero Header --- */
		.mi-hero {
			background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
			padding: 32px 28px 28px;
			margin-bottom: 0;
			position: relative;
			overflow: hidden;
		}
		.mi-hero::before {
			content: '';
			position: absolute;
			top: -60px; right: -60px;
			width: 220px; height: 220px;
			border-radius: 50%;
			background: rgba(255,255,255,0.04);
		}
		.mi-hero::after {
			content: '';
			position: absolute;
			bottom: -40px; left: 120px;
			width: 160px; height: 160px;
			border-radius: 50%;
			background: rgba(255,255,255,0.03);
		}
		.mi-hero-inner {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 16px;
			position: relative;
			z-index: 1;
		}
		.mi-hero-title {
			color: #ffffff;
			font-size: 26px;
			font-weight: 700;
			margin: 0 0 4px 0;
			letter-spacing: 0.3px;
		}
		.mi-hero-subtitle {
			color: rgba(255,255,255,0.6);
			font-size: 13px;
			margin: 0;
		}
		.mi-hero-actions {
			display: flex;
			gap: 10px;
			align-items: center;
		}
		.mi-btn-primary {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			background: #e94560;
			color: #fff !important;
			border: none;
			border-radius: 8px;
			padding: 11px 20px;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none !important;
			transition: background 0.2s, box-shadow 0.2s, transform 0.15s;
			box-shadow: 0 4px 15px rgba(233,69,96,0.35);
		}
		.mi-btn-primary:hover {
			background: #c73652;
			box-shadow: 0 6px 20px rgba(233,69,96,0.45);
			transform: translateY(-1px);
		}
		.mi-btn-secondary {
			display: inline-flex;
			align-items: center;
			gap: 7px;
			background: rgba(255,255,255,0.1);
			color: #fff !important;
			border: 1px solid rgba(255,255,255,0.2);
			border-radius: 8px;
			padding: 10px 18px;
			font-size: 14px;
			font-weight: 500;
			cursor: pointer;
			text-decoration: none !important;
			transition: background 0.2s;
		}
		.mi-btn-secondary:hover {
			background: rgba(255,255,255,0.18);
		}

		/* --- Stats Cards --- */
		.mi-stats-row {
			display: flex;
			gap: 0;
			background: #fff;
			border-bottom: 1px solid #e8ecf0;
		}
		.mi-stat-card {
			flex: 1;
			padding: 20px 24px;
			border-right: 1px solid #e8ecf0;
			display: flex;
			align-items: center;
			gap: 16px;
			transition: background 0.2s;
		}
		.mi-stat-card:last-child { border-right: none; }
		.mi-stat-card:hover { background: #f8f9fc; }
		.mi-stat-icon {
			width: 46px; height: 46px;
			border-radius: 12px;
			display: flex; align-items: center; justify-content: center;
			font-size: 20px;
			flex-shrink: 0;
		}
		.mi-stat-icon.total   { background: #e8f4fd; color: #2980b9; }
		.mi-stat-icon.active  { background: #eafaf1; color: #27ae60; }
		.mi-stat-icon.inactive{ background: #fef9e7; color: #f39c12; }
		.mi-stat-value {
			font-size: 28px;
			font-weight: 700;
			color: #1a1a2e;
			line-height: 1;
			margin-bottom: 3px;
		}
		.mi-stat-label {
			font-size: 12px;
			color: #8a94a6;
			font-weight: 500;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		/* --- Main Content Card --- */
		.mi-main-card {
			background: #fff;
			border-radius: 0;
			margin: 0;
		}

		/* --- Toolbar --- */
		.mi-toolbar {
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 12px;
			padding: 18px 24px;
			border-bottom: 1px solid #e8ecf0;
		}
		.mi-toolbar-left {
			display: flex;
			align-items: center;
			gap: 10px;
			flex-wrap: wrap;
		}
		.mi-search-wrap {
			position: relative;
		}
		.mi-search-wrap input {
			border: 1px solid #dde1e7;
			border-radius: 8px;
			padding: 9px 14px 9px 36px;
			font-size: 13px;
			color: #3d4451;
			background: #f8f9fc;
			width: 220px;
			transition: border-color 0.2s, background 0.2s;
			outline: none;
		}
		.mi-search-wrap input:focus {
			border-color: #e94560;
			background: #fff;
			box-shadow: 0 0 0 3px rgba(233,69,96,0.08);
		}
		.mi-search-wrap .mi-search-icon {
			position: absolute;
			left: 11px; top: 50%;
			transform: translateY(-50%);
			color: #aab0bc;
			font-size: 14px;
			pointer-events: none;
		}
		.mi-filter-select {
			border: 1px solid #dde1e7;
			border-radius: 8px;
			padding: 9px 32px 9px 12px;
			font-size: 13px;
			color: #3d4451;
			background: #f8f9fc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%23aab0bc'/%3E%3C/svg%3E") no-repeat right 10px center;
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			cursor: pointer;
			outline: none;
			transition: border-color 0.2s;
		}
		.mi-filter-select:focus {
			border-color: #e94560;
			background-color: #fff;
		}
		.mi-toolbar-right {
			font-size: 12px;
			color: #8a94a6;
		}

		/* --- Table --- */
		.mi-table-wrap {
			overflow-x: auto;
		}
		table.mi-table {
			width: 100%;
			border-collapse: collapse;
			font-size: 13.5px;
		}
		table.mi-table thead tr {
			background: #f4f6fb;
			border-bottom: 2px solid #e8ecf0;
		}
		table.mi-table thead th {
			padding: 13px 16px;
			text-align: left;
			font-weight: 600;
			color: #5a6478;
			font-size: 12px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			white-space: nowrap;
		}
		table.mi-table tbody tr {
			border-bottom: 1px solid #f0f2f7;
			transition: background 0.15s;
		}
		table.mi-table tbody tr:hover {
			background: #fafbff;
		}
		table.mi-table tbody td {
			padding: 14px 16px;
			color: #3d4451;
			vertical-align: middle;
		}

		/* Item image in table */
		.mi-item-img {
			width: 50px; height: 50px;
			object-fit: cover;
			border-radius: 10px;
			border: 2px solid #e8ecf0;
			background: #f4f6fb;
		}
		.mi-item-img-placeholder {
			width: 50px; height: 50px;
			border-radius: 10px;
			background: linear-gradient(135deg, #e8ecf0, #f4f6fb);
			display: flex; align-items: center; justify-content: center;
			color: #aab0bc;
			font-size: 20px;
		}

		/* Item name cell */
		.mi-item-name {
			font-weight: 600;
			color: #1a1a2e;
			font-size: 14px;
			margin-bottom: 2px;
		}
		.mi-item-meta {
			font-size: 11.5px;
			color: #aab0bc;
		}

		/* Category badge */
		.mi-badge-cat {
			display: inline-block;
			background: #eef2ff;
			color: #3d5af1;
			border-radius: 20px;
			padding: 3px 10px;
			font-size: 11.5px;
			font-weight: 500;
			white-space: nowrap;
		}

		/* Order badge */
		.mi-order-badge {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 30px; height: 30px;
			border-radius: 50%;
			background: #f4f6fb;
			color: #5a6478;
			font-weight: 600;
			font-size: 13px;
			border: 1px solid #e8ecf0;
		}

		/* Status toggle */
		.mi-status-toggle {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 12px;
			font-weight: 500;
		}
		.mi-status-dot {
			width: 8px; height: 8px;
			border-radius: 50%;
		}
		.mi-status-dot.active   { background: #27ae60; box-shadow: 0 0 0 3px rgba(39,174,96,0.15); }
		.mi-status-dot.inactive { background: #e74c3c; box-shadow: 0 0 0 3px rgba(231,76,60,0.12); }
		.mi-status-dot.deleted  { background: #95a5a6; }

		.mi-toggle-btn {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 5px 12px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
			cursor: pointer;
			border: none;
			text-decoration: none;
			transition: opacity 0.2s;
		}
		.mi-toggle-btn:hover { opacity: 0.85; }
		.mi-toggle-btn.active   { background: #eafaf1; color: #27ae60; }
		.mi-toggle-btn.inactive { background: #fdf2f2; color: #e74c3c; }

		/* Action buttons */
		.mi-action-wrap {
			display: flex;
			gap: 6px;
			align-items: center;
		}
		.mi-action-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 34px; height: 34px;
			border-radius: 8px;
			border: 1px solid transparent;
			cursor: pointer;
			transition: background 0.15s, border-color 0.15s, transform 0.1s;
			font-size: 15px;
			text-decoration: none !important;
		}
		.mi-action-btn:hover { transform: translateY(-1px); }
		.mi-action-btn.edit {
			background: #eef2ff;
			color: #3d5af1;
			border-color: #d5dcfb;
		}
		.mi-action-btn.edit:hover {
			background: #dbe4ff;
		}
		.mi-action-btn.delete {
			background: #fdf2f2;
			color: #e74c3c;
			border-color: #fad7d7;
		}
		.mi-action-btn.delete:hover {
			background: #fce8e8;
		}

		/* --- Alerts --- */
		.mi-alert {
			display: flex;
			align-items: flex-start;
			gap: 12px;
			padding: 14px 18px;
			border-radius: 10px;
			font-size: 13.5px;
			margin-bottom: 16px;
			animation: slideDown 0.3s ease;
		}
		@keyframes slideDown {
			from { opacity: 0; transform: translateY(-8px); }
			to   { opacity: 1; transform: translateY(0); }
		}
		.mi-alert.success {
			background: #eafaf1;
			border-left: 4px solid #27ae60;
			color: #1d6b3d;
		}
		.mi-alert.danger {
			background: #fdf2f2;
			border-left: 4px solid #e74c3c;
			color: #962d2d;
		}
		.mi-alert-icon { font-size: 18px; margin-top: 1px; flex-shrink: 0; }
		.mi-alert-close {
			margin-left: auto;
			cursor: pointer;
			opacity: 0.5;
			background: none;
			border: none;
			font-size: 16px;
			line-height: 1;
			color: inherit;
			padding: 0;
		}
		.mi-alert-close:hover { opacity: 1; }

		/* --- Empty State --- */
		.mi-empty {
			text-align: center;
			padding: 60px 20px;
			color: #aab0bc;
		}
		.mi-empty-icon {
			font-size: 48px;
			margin-bottom: 16px;
			display: block;
		}
		.mi-empty h4 {
			font-size: 18px;
			color: #5a6478;
			font-weight: 600;
			margin-bottom: 8px;
		}
		.mi-empty p {
			font-size: 13.5px;
			margin-bottom: 20px;
		}

		/* --- DataTable overrides --- */
		.dataTables_wrapper .dataTables_length label,
		.dataTables_wrapper .dataTables_filter label {
			font-size: 13px;
			color: #5a6478;
		}
		.dataTables_wrapper .dataTables_length select,
		.dataTables_wrapper .dataTables_filter input {
			border: 1px solid #dde1e7;
			border-radius: 6px;
			padding: 5px 8px;
			font-size: 13px;
			outline: none;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button {
			border-radius: 6px !important;
			font-size: 13px;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button.current {
			background: #e94560 !important;
			border-color: #e94560 !important;
			color: #fff !important;
		}
		.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
			background: #f4f6fb !important;
			border-color: #dde1e7 !important;
			color: #1a1a2e !important;
		}
		.dataTables_wrapper .dataTables_info {
			font-size: 12.5px;
			color: #8a94a6;
		}

		/* --- Loading overlay --- */
		.mi-loading-overlay {
			position: fixed;
			inset: 0;
			background: rgba(255,255,255,0.75);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 9999;
			display: none;
		}
		.mi-spinner {
			width: 44px; height: 44px;
			border: 4px solid #e8ecf0;
			border-top-color: #e94560;
			border-radius: 50%;
			animation: spin 0.8s linear infinite;
		}
		@keyframes spin { to { transform: rotate(360deg); } }

		/* Responsive */
		@media (max-width: 768px) {
			.mi-hero-inner { flex-direction: column; align-items: flex-start; }
			.mi-stats-row { flex-direction: column; }
			.mi-stat-card { border-right: none; border-bottom: 1px solid #e8ecf0; }
			.mi-stat-card:last-child { border-bottom: none; }
			.mi-toolbar { flex-direction: column; align-items: flex-start; }
			.mi-search-wrap input { width: 100%; }
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

		<!-- Loading overlay -->
		<div class="mi-loading-overlay" id="mi-loading">
			<div class="mi-spinner"></div>
		</div>

		<div class="mi-page-wrapper">

			<!-- Hero Header -->
			<div class="mi-hero">
				<div class="mi-hero-inner">
					<div>
						<h1 class="mi-hero-title"><?= $langage_lbl['LBL_MENU_ITEMS_FRONT']; ?></h1>
						<p class="mi-hero-subtitle"><?= $langage_lbl['LBL_MANAGE_TXT'] ?? 'Manage your menu items, pricing and availability'; ?></p>
					</div>
					<div class="mi-hero-actions">
						<a href="javascript:void(0);" class="mi-btn-primary" onclick="add_menu_item_form();">
							<span class="icon-plus-sign" style="font-size:16px;"></span>
							<?= $langage_lbl['LBL_ACTION_ADD']; ?> <?= $langage_lbl['LBL_MENU_ITEM_FRONT']; ?>
						</a>
						<a href="food_menu.php" class="mi-btn-secondary">
							<span class="icon-list" style="font-size:15px;"></span>
							<?= $langage_lbl['LBL_CATEGORIES'] ?? 'Categories'; ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Stats Row -->
			<div class="mi-stats-row">
				<div class="mi-stat-card">
					<div class="mi-stat-icon total">
						<span class="icon-list-alt"></span>
					</div>
					<div>
						<div class="mi-stat-value"><?= $cnt_total; ?></div>
						<div class="mi-stat-label"><?= $langage_lbl['LBL_TOTAL_ITEMS'] ?? 'Total Items'; ?></div>
					</div>
				</div>
				<div class="mi-stat-card">
					<div class="mi-stat-icon active">
						<span class="icon-ok-circle"></span>
					</div>
					<div>
						<div class="mi-stat-value"><?= $cnt_active; ?></div>
						<div class="mi-stat-label"><?= $langage_lbl['LBL_ACTIVE'] ?? 'Active'; ?></div>
					</div>
				</div>
				<div class="mi-stat-card">
					<div class="mi-stat-icon inactive">
						<span class="icon-ban-circle"></span>
					</div>
					<div>
						<div class="mi-stat-value"><?= $cnt_inactive; ?></div>
						<div class="mi-stat-label"><?= $langage_lbl['LBL_INACTIVE'] ?? 'Inactive'; ?></div>
					</div>
				</div>
			</div>

			<!-- Main Content -->
			<div class="mi-main-card">

				<!-- Alerts -->
				<div style="padding: 0 24px;">
					<?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1) { ?>
					<div class="mi-alert success" id="mi-alert-box">
						<span class="mi-alert-icon">&#10003;</span>
						<span><?= htmlspecialchars($var_msg) ?></span>
						<button class="mi-alert-close" onclick="document.getElementById('mi-alert-box').style.display='none';">&times;</button>
					</div>
					<?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 2) { ?>
					<div class="mi-alert danger" id="mi-alert-box">
						<span class="mi-alert-icon">&#9888;</span>
						<span><?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?></span>
						<button class="mi-alert-close" onclick="document.getElementById('mi-alert-box').style.display='none';">&times;</button>
					</div>
					<?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 0) { ?>
					<div class="mi-alert danger" id="mi-alert-box">
						<span class="mi-alert-icon">&#9888;</span>
						<span><?= htmlspecialchars($var_msg) ?></span>
						<button class="mi-alert-close" onclick="document.getElementById('mi-alert-box').style.display='none';">&times;</button>
					</div>
					<?php } ?>
				</div>

				<!-- Toolbar -->
				<div class="mi-toolbar">
					<div class="mi-toolbar-left">
						<?php if (!empty($db_categories)) { ?>
						<select class="mi-filter-select" id="mi-cat-filter" onchange="filterByCategory(this.value)">
							<option value=""><?= $langage_lbl['LBL_ALL_CATEGORIES'] ?? 'All Categories'; ?></option>
							<?php foreach ($db_categories as $cat) { ?>
							<option value="<?= $cat['iFoodMenuId'] ?>" <?= ($menu_itemid == $cat['iFoodMenuId']) ? 'selected' : ''; ?>>
								<?= htmlspecialchars($cat['vMenuName']); ?>
							</option>
							<?php } ?>
						</select>
						<?php } ?>
					</div>
					<div class="mi-toolbar-right">
						<?= $cnt_total; ?> <?= $langage_lbl['LBL_TOTAL_ITEMS'] ?? 'total items'; ?>
					</div>
				</div>

				<!-- Table -->
				<div class="mi-table-wrap">
					<table width="100%" class="mi-table" id="dataTables-example">
						<thead>
							<tr>
								<th><?= $langage_lbl['LBL_MENU_TITLE']; ?></th>
								<th><?= $langage_lbl['LBL_CATEGORY_FRONT']; ?></th>
								<th><?= $langage_lbl['LBL_ITEM_IMAGE_FRONT']; ?></th>
								<th><?= $langage_lbl['LBL_DISPLAY_ORDER_FRONT']; ?></th>
								<th><?= $langage_lbl['LBL_Status']; ?></th>
								<th><?= $langage_lbl['LBL_FOOD_CATEOGRY_EDIT']; ?></th>
								<th><?= $langage_lbl['LBL_FOOD_CATEOGRY_DELETE']; ?></th>
							</tr>
						</thead>
					</table>
				</div>

			</div>
			<!-- /Main Content -->

		</div>
		<!-- /Page Wrapper -->

		<!-- footer part -->
		<?php include_once('footer/footer_home.php'); ?>
		<!-- footer part end -->
		<div style="clear:both;"></div>
	</div>
	<!-- /main-uber-page -->

	<!-- Footer Script -->
	<?php include_once('top/footer_script.php'); ?>
	<script src="assets/js/jquery-ui.min.js"></script>
	<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
	<script src="assets/js/modal_alert.js"></script>

	<script type="text/javascript">
		var currentCatFilter = "<?php echo $menu_itemid; ?>";

		$(document).ready(function () {
			var dt = $('#dataTables-example').dataTable({
				"oLanguage": langData,
				"processing": true,
				"serverSide": true,
				"serverMethod": "post",
				"ajax": {
					url: "ajax_menuitem_data.php",
					type: "POST",
					data: { menu_itemid: currentCatFilter },
					dataType: "json",
					beforeSend: function (xhr) {
						resizeImage("setImageRatioJS", "attr-image-src", "imageSrc");
						mO4u1yc3dx(xhr);
					},
					complete: function () {
						resizeImage("setImageRatioJS", "attr-image-src", "imageSrc");
					}
				},
				"aaSorting": [],
				"pageLength": 10,
				"dom": '<"mi-dt-top"lf>rt<"mi-dt-bottom"ip>',
				"language": {
					"processing": '<div class="mi-spinner" style="margin:10px auto;"></div>'
				}
			});

			// Auto-dismiss alerts after 5s
			setTimeout(function () {
				$('#mi-alert-box').fadeOut(400);
			}, 5000);
		});

		function filterByCategory(catId) {
			currentCatFilter = catId;
			var url = 'menuitems.php';
			if (catId !== '') {
				url += '?menu_itemid=' + catId;
			}
			window.location.href = url;
		}

		function confirm_delete(id) {
			$("#hdn_del_id").val(id);
			show_alert(
				"<?= addslashes($langage_lbl['LBL_DELETE']); ?>",
				"<?= addslashes($langage_lbl['LBL_MENU_ITEM_DELETE']); ?>",
				"<?= addslashes($langage_lbl['LBL_CONFIRM_TXT']); ?>",
				"<?= addslashes($langage_lbl['LBL_CANCEL_TXT']); ?>",
				"",
				function (btn_id) {
					if (btn_id == 0) {
						id = $("#hdn_del_id").val();
						ShpSq6fAm7($("#delete_form_" + id));
						document.getElementById("delete_form_" + id).submit();
					}
				}
			);
		}

		function add_menu_item_form() {
			window.location.href = "menu_item_action.php";
		}
	</script>
	<!-- End: Footer Script -->
</body>

</html>
