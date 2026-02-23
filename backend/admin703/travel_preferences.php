<?php
include_once('../common.php');
$script = "Travel_Preferences";
$tbl_name = 'travel_preferences';
$defaultLang = $LANG_OBJ->FetchSystemDefaultLang();


$categoryId = isset($_REQUEST['categoryId']) ? stripslashes($_REQUEST['categoryId']) : 0;
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

/*------------------search-----------------*/
$ssql = '';
if ($keyword != '') {
    if ($option != '') {
        if (strpos($option, 'eStatus') !== false) {
            $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
        } else {
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        }
    } else {
        $ssql .= " AND (TP.vTitle LIKE '%" . $keyword . "%' OR TP.TravelPreferencesId LIKE '%" . $keyword . "%' OR TP.iDisplayOrder LIKE '%" . $keyword . "%' OR TP.eStatus LIKE '%" . $keyword . "%' OR TPC.vTitle LIKE '%" . $keyword . "%')";
    }
}

if($categoryId > 0){
    $ssql .= " AND TP.iTravelPreferencesCategoryId = {$categoryId} ";
}

/*------------------search-----------------*/
/*------------------Pagination-----------------*/
$per_page = $DISPLAY_RECORD_NUMBER;
$sql = "SELECT COUNT(TP.TravelPreferencesId) AS Total FROM ".$tbl_name." TP JOIN travel_preferences_category as TPC ON (TP.iTravelPreferencesCategoryId = TPC.iTravelPreferencesCategoryId) WHERE TP.eStatus != 'Deleted' $ssql ";

$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0) $page = 1;
/*------------------Pagination-----------------*/


$sql = "SELECT TP.iDisplayOrder,TP.eStatus,JSON_UNQUOTE(JSON_VALUE(TPC.vTitle, '$.vTitle_" . $defaultLang . "')) as categoryTitle,TP.eDeafultSelected,TP.iTravelPreferencesCategoryId,TP.TravelPreferencesId,JSON_UNQUOTE(JSON_VALUE(TP.vTitle, '$.vTitle_" . $defaultLang . "')) as Title FROM ".$tbl_name." TP JOIN travel_preferences_category as TPC ON (TP.iTravelPreferencesCategoryId = TPC.iTravelPreferencesCategoryId) WHERE TP.eStatus != 'Deleted' $ssql LIMIT $start, $per_page";
$data_list = $obj->MySQLSelect($sql);

/*------------------Category-----------------*/
$sql = "SELECT iTravelPreferencesCategoryId,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $defaultLang . "')) as vCategory FROM travel_preferences_category WHERE eStatus != 'Deleted' ORDER BY  vTitle ASC ";
$db_cat = $obj->MySQLSelect($sql);

/*------------------Category-----------------*/


$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>

<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Travel Preferences Option </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Travel Preferences Option</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td width="5%">
                            <label for="textfield">
                                <strong>Search:</strong>
                            </label>
                        </td>
                        <td width="15%" class=" padding-right10">


                            <select name="categoryId" id="categoryId" class="form-control">
                                <option value="">Select Category</option>
                                <?php foreach ($db_cat as $cat){ ?>
                                    <option
                                            <?php if($cat['iTravelPreferencesCategoryId'] == $categoryId) {

                                                echo "Selected";
                                            }  ?>

                                            value="<?php echo $cat['iTravelPreferencesCategoryId']; ?>">
                                        <?php echo $cat['vCategory']; ?>
                                    </option>
                                <?php } ?>

                            </select>
                        </td>
                        <td width="15%">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = 'travel_preferences.php'"/>
                        </td>
                        <?php if ($userObj->hasPermission('create-travel-preferences')) { ?>
                            <td width="30%">
                                <a class="add-btn" href="travel_preferences_action.php" style="text-align: center;">Add Option</a>
                            </td>
                        <?php } ?>
                    </tr>
                    </tbody>
                </table>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                       <th>Title</th>
                                       <th>Category</th>
                                      <!-- <th>Display Order</th>-->
                                       <th style="text-align:center;">Status</th>
                                       <th style="text-align:center;">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $count_all = scount($data_list);
                                    if (!empty($data_list)) {

                                        foreach ($data_list as $data) {
                                            ?>
                                            <tr class="gradeA">

                                                <td><?= $data['Title']; ?></td>
                                                <td><?= $data['categoryTitle']; ?></td>
                                               <!-- <td><?php /*= $data['iDisplayOrder']; */?></td>-->

                                                <td align="center" style="text-align:center;">
                                                    <?php
                                                    if ($data['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    }
                                                    ?>
                                                    <img src="<?= $dis_img; ?>" alt="<?= $data['eStatus']; ?>"
                                                         data-toggle="tooltip"
                                                         title="<?= $data['eStatus']; ?>">
                                                </td>
                                                <?php  if ($userObj->hasPermission('edit-travel-preferences') && $userObj->hasPermission('update-status-travel-preferences')) {  ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <div class="share-button openHoverAction-class"
                                                             style="display: block;">
                                                            <label class="entypo-export">
                                                                <span><img src="images/settings-icon.png" alt=""></span>
                                                            </label>
                                                            <div class="social show-moreOptions openPops_<?= $data['TravelPreferencesId']; ?>">
                                                                <ul>
                                                                    <?php if ($userObj->hasPermission('edit-travel-preferences')) { ?>
                                                                        <li class="entypo-twitter"
                                                                            data-network="twitter">
                                                                            <a href="travel_preferences_action.php?id=<?= $data['TravelPreferencesId']; ?>"
                                                                               data-toggle="tooltip" title="Edit">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                            </a>
                                                                        </li>
                                                                    <?php } ?>
                                                                    <?php if ($data['eDefault'] != 'Yes') { ?>
                                                                        <?php if ($userObj->hasPermission('update-status-travel-preferences')) { ?>
                                                                            <li class="entypo-facebook"
                                                                                data-network="facebook">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatus('<?php echo $data['TravelPreferencesId']; ?>', 'Inactive')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Activate">
                                                                                    <img src="img/active-icon.png"
                                                                                         alt="<?php echo $data['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatus('<?php echo $data['TravelPreferencesId']; ?>', 'Active')"
                                                                                   data-toggle="tooltip"
                                                                                   title="Deactivate">
                                                                                    <img src="img/inactive-icon.png"
                                                                                         alt="<?php echo $data['eStatus']; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission('delete-travel-preferences')) { ?>
                                                                            <li class="entypo-gplus"
                                                                                data-network="gplus">
                                                                                <a href="javascript:void(0);"
                                                                                   onclick="changeStatusDelete('<?php echo $data['TravelPreferencesId']; ?>')"
                                                                                   data-toggle="tooltip" title="Delete">
                                                                                    <img src="img/delete-icon.png"
                                                                                         alt="Delete">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                    <?php } ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                <?php } else { ?>
                                                    <td align="center" style="text-align:center;">
                                                     <a href="travel_preferences_action.php?id=<?= $data['TravelPreferencesId']; ?>"
                                                           data-toggle="tooltip" title="Edit">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                    </td>
                                               <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr class="gradeA">
                                            <td colspan="7"> No Records Found.</td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                            <?php include('pagination_n.php'); ?>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>Travel Preferences Option module will list all Travel Preferences Option on this page.</li>
                    <li>Administrator can Activate / Deactivate / Delete any Travel Preferences Option.</li>
                    <!--<li>Administrator can export data in XLS or PDF format.</li>-->
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/travel_preferences.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="TravelPreferencesId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<?php
include_once('footer.php');
?>
<script>
    $("#setAllCheck").on('click', function () {
        if ($(this).prop("checked")) {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                if ($(this).attr('disabled') != 'disabled') {
                    this.checked = 'true';
                }
            });
        } else {
            jQuery("#_list_form input[type=checkbox]").each(function () {
                this.checked = '';
            });
        }
    });
    $("#Search").on('click', function () {
        //$('html').addClass('loading');
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
    });
    $('.entypo-export').click(function (e) {
        e.stopPropagation();
        var $this = $(this).parent().find('div');
        $(".openHoverAction-class div").not($this).removeClass('active');
        $this.toggleClass('active');
    });
    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
        }
    });
</script>
</body>
<!-- END BODY-->
</html>

