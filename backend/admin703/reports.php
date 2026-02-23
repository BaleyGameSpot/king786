<?php
include_once('../common.php');

if (!$userObj->hasPermission('view-driver-reward-report')) {
    $userObj->redirect();
}

$CampaignData = $DRIVER_REWARD_OBJ->getCampaign();
$getActiveCampaign = $DRIVER_REWARD_OBJ->getActiveCampaign();
$script = 'Reports';
//Start Sorting

$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;

$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';

$ord = ' ORDER BY dr.iDriverReward DESC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY rd.vName ASC";
    else
        $ord = " ORDER BY rd.vName DESC";
}

if ($sortby == 2) {
    if ($order == 0)
        $ord = " ORDER BY dr.iAcceptanceRate ASC";
    else
        $ord = " ORDER BY dr.iAcceptanceRate DESC";
}

if ($sortby == 3) {
    if ($order == 0)
        $ord = " ORDER BY rs.vLevel ASC";
    else
        $ord = " ORDER BY rs.vLevel DESC";
}

if ($sortby == 4) {
    if ($order == 0)
        $ord = " ORDER BY dr.tDate ASC";
    else
        $ord = " ORDER BY dr.tDate DESC";
}

if ($sortby == 5) {
    if ($order == 0)
        $ord = " ORDER BY dr.fRatings ASC";
    else
        $ord = " ORDER BY dr.fRatings DESC";
}



//End Sorting

$cmp_ssql = "";

$dri_ssql = "";

// Start Search Parameters

$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";

$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$iCampaignId = isset($_REQUEST['iCampaignId']) ? $_REQUEST['iCampaignId'] : 0;

$ssql = '';

if ($keyword != '') {

    $keyword_new = $keyword;

    $chracters = array("(", "+", ")");

    $removespacekeyword = preg_replace('/\s+/', '', $keyword);

    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));

    if (is_numeric($keyword_new)) {

        $keyword_new = $keyword_new;

    } else {

        $keyword_new = $keyword;

    }

    if ($option != '') {

        $option_new = $option;

        if ($option == 'drivername') {
            $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
        }
        if ($option == 'level') {
            $option_new = "rs.vLevel";
        }

      
        if ($eStatus != '') {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND c.eStatus = '" . clean($eStatus) . "'";
            }

        } else {
            $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
            }
        }

    } else {
        $ssql .= " AND ( (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%') OR (rs.vLevel LIKE '%" . clean($keyword_new) . "%')  )";
        if (SITE_TYPE == 'Demo') {
            $ssql .= " AND ((concat(rd.vName,' ',rd.vLastName) = '" . clean($keyword_new) . "') OR (rs.vLevel LIKE '%" . clean($keyword_new) . "%'))";
        }
    }
} 

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

if ($iCampaignId) {
    $ssql .= " AND dr.iCampaignId = " . $iCampaignId . "";
} else if(scount($getActiveCampaign) > 0) {
    $ssql .= " AND dr.iCampaignId = " .$getActiveCampaign[0]['iCampaignId'] . "";
}

$sql ="SELECT COUNT(rs.iRewardId) AS Total FROM `driver_reward` as dr JOIN reward_settings as rs ON dr.iRewardId = rs.iRewardId JOIN register_driver as rd ON rd.iDriverId = dr.iDriverId WHERE 1 = 1 $ssql $ord";
$totalData = $obj->MySQLSelect($sql);


$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//

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
if (!empty($eStatus)) {
    $equery = "";
} else {
    $equery = " AND  c.eStatus != 'Deleted'";
}

if ($iCampaignId) {
    $ssql .= " AND dr.iCampaignId = " . $iCampaignId . "";
}else if(scount($getActiveCampaign) > 0){
    $ssql .= " AND dr.iCampaignId = " .$getActiveCampaign[0]['iCampaignId'] . "";
}

$sql ="SELECT rc.vTitle,rd.vName,rd.vLastName ,dr.iDriverId, JSON_UNQUOTE(JSON_VALUE(rs.vLevel, '$.vLevel_".$default_lang."')) as vLevel,rs.vImage,dr.iRewardId,dr.tDate,dr.iAcceptanceRate,dr.iCancellationRate,dr.fRatings,dr.vMinimumTrips,rd.vTimeZone, 
(SELECT COUNT(iDriverId) FROM `trips` WHERE `iDriverId` = dr.iDriverId AND iActive = 'Finished') AS completed_trip,
(SELECT COUNT(iDriverId) FROM `trips` WHERE `iDriverId` = dr.iDriverId AND iActive NOT IN ('Finished')) AS uncompleted_trip,
(SELECT COUNT(iDriverId) FROM `trips` WHERE `iDriverId` = dr.iDriverId)  AS total_trip 
FROM `driver_reward` as dr JOIN reward_settings as rs ON dr.iRewardId = rs.iRewardId 
JOIN register_driver as rd ON rd.iDriverId = dr.iDriverId 
JOIN reward_campaign as rc ON rc.iCampaignId = dr.iCampaignId WHERE 1 = 1 $ssql $ord LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);

$endRecord = scount($data_drv);

$doc_count_query = $obj->MySQLSelect("SELECT doc_masterid as total FROM `document_master` WHERE `doc_usertype` ='company' AND status = 'Active'");
$doc_count = scount($doc_count_query);

$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}

$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>

<!DOCTYPE html>

<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | Reward reports</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
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
                                <h2>Reward Report</h2>
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
                                    <td width="10%" class="padding-right10"><select name="option" id="option" class="form-control">
                                            <option value="">All</option>
                                            <option value="drivername" <?php
                                            if ($option == "drivername") {
                                                echo "selected";
                                            }
                                            ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name</option>

                                            <option value="level" <?php
                                            if ($option == "level") {
                                                echo "selected";
                                            }
                                            ?> ><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Achieve Level</option>

                                        </select>
                                    </td>
                                    <td width="15%" class="searchform"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="15%" class="padding-right10" > 
                                        <select name = "iCampaignId" class="form-control">  
                                            <option value="">Select Campaign</option>
                                            <?php
                                            if(scount($getActiveCampaign) > 0){
                                                if($iCampaignId == 0)
                                                {
                                                    $iCampaignId = $getActiveCampaign[0]['iCampaignId'];
                                                }
                                            }
                                            
                                            foreach($CampaignData as $Campaign){
                                                $selected = '';
                                                
                                                if($iCampaignId == $Campaign['iCampaignId'])
                                                {
                                                    $selected = "selected";
                                                }
                                                ?>

                                                <option <?php echo $selected; ?> value = '<?php echo $Campaign['iCampaignId'] ?>' ><?php echo $Campaign['Title'] ?> </option>
                                                <?php 
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'reports.php'"/>
                                    </td>


                                </tr>

                            </tbody>

                        </table>
                    </form>

                    <div class="table-list">

                        <div class="row">

                            <div class="col-lg-12">

                                <div class="admin-nir-export">
                                    <div class="changeStatus col-lg-12 option-box-left">
                                    </div>
                                    <?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post">
                                                <button type="button" onClick="showExportTypes('Reward_Report')">Export</button>
                                            </form>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                   
                                                    <th width="18%"><a href="javascript:void(0);" onClick="Redirect(1,<?php
                                                        if ($sortby == '1') {
                                                            echo $order;
                                                        } else {
                                                        ?>0<?php } ?>)"><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> Name<?php
                                                        if ($sortby == 1) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                        } else {
                                                            ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                                    </th width="18%" >
                                                    <th>Campaign</th>
                                                    <th width="6%" class='align-center'><a href="javascript:void(0);" onClick="Redirect(3,<?php
                                                            if ($sortby == '3') {
                                                                echo $order;
                                                            } else {
                                                                ?>0<?php } ?>)"> Level <?php
                                                        if ($sortby == 3) {
                                                            if ($order == 0) {
                                                                ?>
                                                                    <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?>
                                                                    <i class="fa fa-sort-amount-desc" aria-hidden="true"></i>
                                                                    <?php
                                                                }
                                                            } else {
                                                                ?>  <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th class="align-center"> Trip</th>
                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(2,<?php
                                                    if ($sortby == '2') {
                                                        echo $order;
                                                    } else {
                                                        ?>0<?php } ?>)">Acceptance Rate <?php
                                                        if ($sortby == 2) {
                                                            if ($order == 0) {
                                                                ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                              }
                                                          } else {
                                                              ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    <th width="12%" class="align-center">Cancellation Rate</th>
                                                    <!-- <th width="15%">Ratings</th> -->
                                                    <th width="10%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(5,<?php
                                                        if ($sortby == '5') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Ratings <?php
                                                            if ($sortby == 5) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                    ?> <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>

                                                    <th width="12%" class="align-center"><a href="javascript:void(0);" onClick="Redirect(4,<?php
                                                        if ($sortby == '4') {
                                                            echo $order;
                                                        } else {
                                                            ?>0<?php } ?>)">Date <?php
                                                            if ($sortby == 4) {
                                                                if ($order == 0) {
                                                                    ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                                }
                                                            } else {
                                                    ?> <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    if (!empty($data_drv) && scount($CampaignData) > 0)
                                                    {
                                                        $serverTimeZone = date_default_timezone_get();
                                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                                            $default = '';
                                                            if (isset($data_drv[$i]['iCompanyId']) && $data_drv[$i]['iCompanyId'] == 1) {
                                                                $default = 'disabled';

                                                    }

                                                    $date_format_data_array = array(
                                                        'tdate' => (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) :  $data_drv[$i]['tDate'],
                                                        'langCode' => $default_lang,
                                                        'DateFormatForWeb' => 1
                                                    );
                                                    $get_tDate_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                ?>

                                                <tr class="gradeA">
                                                    <td><?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details(<?= $data_drv[$i]['iDriverId']; ?>)" style="text-decoration: underline;"><?php } ?><?= clearName($data_drv[$i]['vName'].' '.$data_drv[$i]['vLastName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a><?php } ?></td>
                                                    
                                                    <td> <?= $data_drv[$i]['vTitle']; ?></td>
                                                    <td class="align-center"> <?= $data_drv[$i]['vLevel']; ?></td>
                                                    <td class="align-center"> <?= $data_drv[$i]['vMinimumTrips'];?></td>
                                                    <td class="align-center"> <?= $data_drv[$i]['iAcceptanceRate']; ?></td>
                                                    <td class="align-center"> <?= $data_drv[$i]['iCancellationRate']; ?></td>
                                                    <td class="align-center"> <?= $data_drv[$i]['fRatings']; ?></td>
                                                    <td class="align-center"><?= $get_tDate_date_format['tDisplayDate'];//DateTime($data_drv[$i]['tDate']); ?></td>

                                                    
                                                </tr>

                                                <?php 
                                            }} else {?>

                                            <tr class="gradeA">
                                                <td colspan="8"> No Records Found.</td>
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
                            <li>Reports module will list all Reward reports on this page.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/company.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iCompanyId" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" nam="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
            <input type="hidden" name="iCampaignId" value="<?php echo $iCampaignId;?>">
        </form>
        <?php
        include_once('footer.php');
        ?>
        <div  class="modal fade" id="driver_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4>
                            <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
<?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body" style="max-height: 450px;overflow: auto;">
                        <div id="imageIcons" style="display:none">
                            <div align="center">                                                                       
                                <img src="../default.gif"><br/>                                                            
                                <span>Retrieving details,please Wait...</span>                       
                            </div>    
                        </div>
                        <div id="driver_detail"></div>
                    </div>
                </div>
            </div>

        </div>
		 

 <script>        
		function show_driver_details(driverid) {
			$("#driver_detail").html('');
			$("#imageIcons").show();
			$("#driver_detail_modal").modal('show');

			if (driverid != "") { 
				var ajaxData = {
					'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
					'AJAX_DATA': "iDriverId=" + driverid,
					'REQUEST_DATA_TYPE': 'html'
				};
				getDataFromAjaxCall(ajaxData, function(response) {
					if(response.action == "1") {
						var data = response.result;
						$("#driver_detail").html(data);
						$("#imageIcons").hide();  
					}
					else {
						console.log(response.result);
						$("#imageIcons").hide();  
					}
				});
			}
		} 
    

            $("#Search").on('click', function () {

                var action = $("#_list_form").attr('action');

                var formValus = $("#frmsearch").serialize();

                window.location.href = action + "?" + formValus;

            });

            

        </script>

    </body>

    <!-- END BODY-->

</html>