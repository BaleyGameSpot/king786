<?php
include_once('../common.php');

if (!$userObj->hasPermission('view-rental-intercity-packages')) {
    $userObj->redirect();
}
if ($default_lang == "") {
    $default_lang = "EN";
}

$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$script = 'InterCity Rental Package';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
    else
        $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
}
if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY vt.fPricePerKM ASC";
    else
        $ord = " ORDER BY vt.fPricePerKM DESC";
}
if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY vt.fPricePerMin ASC";
    else
        $ord = " ORDER BY vt.fPricePerMin DESC";
}
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$iLocationid = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : "";

$ssql = '';
if ($keyword != '') {
    if ($option != '') {
      
            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
        
    } else {
       
            $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";
        
    }
} else if ($eType != '' && $keyword == '') {
    $ssql .= " AND vt.eType = '" . $eType . "'";
}
$locations_where = "";
if (scount($userObj->locations) > 0) {
    $locations = implode(', ', $userObj->locations);
    $locations_where = " AND vt.iLocationid IN(-1, {$locations}) ";
    $ssql .= $locations_where;
}
// End Search Parameters
if ($iLocationid != '') {

    $ssql .= " AND vt.iLocationid = '" . $iLocationid . "'";

}

$ssql .= " AND vt.eFly = '0' AND vt.eIconType != 'Ambulance' AND vt.eBidStatus != 'Yes' AND vt.eInterCityStatus = 'Yes'";

//$Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ; 
if ($APP_TYPE == 'Delivery') {
    $Vehicle_type_name = 'Deliver';
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $Vehicle_type_name = 'Ride-Delivery';
} else {
    $Vehicle_type_name = $APP_TYPE;
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "";
if ($Vehicle_type_name == "Ride-Delivery") {
    $sql = "SELECT count(iVehicleTypeId) AS Total from  vehicle_type  as vt where 1 = 1 AND vt.eType ='Ride' AND vt.ePoolStatus =  'No' AND vt.estatus ='Active' $ssql";
} else {
    $sql = "SELECT count(vt.iVehicleTypeId) as Total  from  vehicle_type as vt where vt.eType='" . $Vehicle_type_name . "' AND vt.ePoolStatus =  'No' AND vt.estatus ='Active' $ssql";
}
//echo $sql; die;
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
$start = 0;
$end = $per_page;
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End

$sql = "";

if ($Vehicle_type_name == "Ride-Delivery") {

    $sql = "SELECT vt.*,lm.vLocationName from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1= 1  AND vt.eType='Ride' AND vt.ePoolStatus='No' AND vt.estatus ='Active' $ssql $ord LIMIT $start, $per_page";
} else {
    if ($APP_TYPE == 'Ride-Delivery-UberX') {
        $sql = "SELECT vt.*,c.vCountry,ct.vCity,st.vState,lm.vLocationName
		from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId 
		left join state as st ON st.iStateId = vt.iStateId 
		left join city as ct ON ct.iCityId = vt.iCityId 
    left join location_master as lm ON lm.iLocationId = vt.iLocationid 
		where 1=1 AND vt.estatus ='Active' $ssql $ord LIMIT $start, $per_page";
    } else {
        $sql = "SELECT vt.*,c.vCountry,ct.vCity,st.vState,lm.vLocationName
		from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId 
		left join state as st ON st.iStateId = vt.iStateId 
		left join city as ct ON ct.iCityId = vt.iCityId 
    left join location_master as lm ON lm.iLocationId = vt.iLocationid 
		where eType='" . $Vehicle_type_name . "' AND vt.ePoolStatus='No' AND vt.estatus ='Active' $ssql $ord LIMIT $start, $per_page";
    }
}

$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

$Lsql = "SELECT lm.*,c.vCountry FROM location_master as lm LEFT JOIN country as c on c.iCountryId=lm.iCountryId WHERE lm.eStatus != 'Deleted' AND eFor='VehicleType' LIMIT $start, $per_page ";
$LocationData = $obj->MySQLSelect($Lsql);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> |Manage InterCity Packages</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                                <h2>Manage InterCity Packages</h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?= $keyword; ?>"  class="form-control" /></td>

                                    <td width="13%" class="location_options" id="location_options">

                                        <select name="location" id="location_value" class="form-control">

                                            <option value="">Select Location</option>

                                            <option value="-1" <?php
                                            if ($iLocationid == '-1') {

                                                echo "selected";

                                            }
                                            ?> >All Location

                                            </option>

                                            <?php foreach ($LocationData as $l) { ?>

                                                <option value="<?= $l["iLocationId"] ?>" <?php
                                                if ($iLocationid == $l["iLocationId"]) {

                                                    echo "selected";

                                                }
                                                ?> ><?= $l['vLocationName'] ?></option>

                                            <? } ?>

                                        </select>

                                    </td>

                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'intercity_vehicle_list.php'"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                   <!--  <th align="center" width="3%" style="text-align:center;"><input type="checkbox" id="setAllCheck" ></th> -->

                                                    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                            ?>0<?php } ?>)">Vehicle Type<?php   if ($sortby == 1) {
                                                                                         if ($order == 0) {
                                                                                             ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                                ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                                  <th width="15%">Location Name</th>
                                                    <th width="10%" style="text-align:center;">Intercity Packages</th>                                                                                                                                      <!--   <th width="8%" style="text-align:center;">Action</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($data_drv)) {
                                                    //Added By HJ ON 21-09-2020 For Optimize For Loop Query 
                                                    $rentalDataArr = array();
                                                    $getRentalData = $obj->MySQLSelect("SELECT count(iRentalPackageId) AS TotalPackage,iVehicleTypeId from  rental_package where 1=1 GROUP BY iVehicleTypeId");
                                                    for($m=0;$m<scount($getRentalData);$m++){
                                                        $rentalDataArr[$getRentalData[$m]['iVehicleTypeId']] = $getRentalData[$m]['TotalPackage'];
                                                    }
                                                    for ($i = 0; $i < scount($data_drv); $i++) {
                                                        $total_rental_package = 0;
                                                        if(isset($rentalDataArr[$data_drv[$i]['iVehicleTypeId']])){
                                                            $total_rental_package = $rentalDataArr[$data_drv[$i]['iVehicleTypeId']];
                                                        }
                                                        ?>
                                                        <tr class="gradeA">                                                                                                                                                  
                                                            <td><?= $data_drv[$i]['vVehicleType_' . $default_lang] ?></td>
                                                            <td> 
                                                                <?php if (($data_drv[$i]['iLocationid'] == "-1")) { ?> All Locations 
                                                                <?php } else { ?><?= $data_drv[$i]['vLocationName']; ?><?php } ?>
                                                            </td>
                                                            <td style="text-align:center;">
                                                                <?php if ($userObj->hasPermission(['view-rental-intercity-packages', 'create-rental-intercity-packages'])) { ?>
                                                                    <a href="intercity_package.php?id=<?= $data_drv[$i]['iVehicleTypeId']; ?>" class="add-btn-sub">Add/View (<?= $total_rental_package; ?>) </a> 
                                                                <?php } ?>
                                                            </td>

                                                        </tr>    
                                                    <?
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="4"> No Records Found.</td>
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
                            <li>
                                InterCity <?= $langage_lbl_admin['LBL_Vehicle']; ?> Type  module will list all intercity <?= $langage_lbl_admin['LBL_Vehicle']; ?> Types on this page.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

<?php include_once('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#eType_options').hide();
        $('#option').each(function () {
            if (this.value == 'vt.eType') {
                $('#eType_options').show();
                $('.searchform').hide();
            }
        });
    });
    $(function () {
        $('#option').change(function () {
            if ($('#option').val() == 'vt.eType') {
                $('#eType_options').show();
                $("input[name=keyword]").val("");
                $('.searchform').hide();
            } else {
                $('#eType_options').hide();
                $("#eType_value").val("");
                $('.searchform').show();
            }
        });
    });

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
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        window.location.href = action + "?" + formValus;
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