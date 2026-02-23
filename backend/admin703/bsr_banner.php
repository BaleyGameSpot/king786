<?php
include_once '../common.php';
$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
$permission_name = strtolower($eMasterType);
$create = "create-banner-".$permission_name;
$view = "view-banner-".$permission_name;
$edit = "edit-banner-".$permission_name;
$delete = "delete-banner-".$permission_name;
$updateStatus = "update-status-banner-".$permission_name;
if (!$userObj->hasPermission($view)) {
    $userObj->redirect();
}
$eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
$iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
$script = $eMasterType.'Banner';
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
//Delete
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
// Update eStatus
$iUniqueId = isset($_REQUEST['iUniqueId']) ? $_REQUEST['iUniqueId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
//sort order
$flag = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$select_lang = isset($_REQUEST['selectlang']) ? stripslashes($_REQUEST['selectlang']) : '';

$languages = $obj->MySQLSelect("SELECT * FROM language_master WHERE eStatus = 'Active' ORDER BY iDispOrder");
$tbl_name = 'banners';
$per_page = $DISPLAY_RECORD_NUMBER;
$whereserviceId = '';
if($iMasterServiceCategoryId != "") {
    $whereserviceId = " AND iMasterServiceCategoryId = '$iMasterServiceCategoryId'";    
}
$vCodeLang = isset($_REQUEST['vCode']) ? $_REQUEST['vCode'] : $default_lang;
if($select_lang != "") {
    $whereserviceId .= " AND vCode = '$select_lang'";    
} else {
    $select_lang = $vCodeLang;
    $whereserviceId .= " AND vCode = '$select_lang'";    
}
$ParameterUrl = 'eType=' . $_REQUEST['eType'] . '&selectlang=' . $vCodeLang;
//delete record
//echo $hdn_del_id;die;
if ($hdn_del_id != '') {
    if (SITE_TYPE != 'Demo') {
        $data_q = "SELECT Max(iDisplayOrder) AS iDisplayOrder FROM `" . $tbl_name . "` WHERE 1  $whereserviceId ";
        $data_rec = $obj->MySQLSelect($data_q);

        $order = isset($data_rec[0]['iDisplayOrder']) ? $data_rec[0]['iDisplayOrder'] : 0;

        $data_logo = $obj->MySQLSelect("SELECT iDisplayOrder FROM " . $tbl_name . " WHERE iUniqueId = '" . $hdn_del_id . "' $whereserviceId ");

        if (scount($data_logo) > 0) {
            $iDisplayOrder = isset($data_logo[0]['iDisplayOrder']) ? $data_logo[0]['iDisplayOrder'] : '';
            $obj->sql_query("DELETE FROM `" . $tbl_name . "` WHERE iUniqueId = '" . $hdn_del_id . "' $whereserviceId");

            if ($iDisplayOrder < $order) {
                for ($i = $iDisplayOrder + 1; $i <= $order; $i++) {
                    $obj->sql_query("UPDATE " . $tbl_name . " SET iDisplayOrder = " . ($i - 1) . " WHERE iDisplayOrder = " . $i .$whereserviceId);
                }
            }
        }
        header("Location:bsr_banner.php?".$ParameterUrl);
        exit();
    } else {
        $_SESSION['success'] = '2';
       header("Location:bsr_banner.php?".$ParameterUrl);
        exit();
    }
}

if ($iUniqueId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE `" . $tbl_name . "` SET eStatus = '" . $status . "' WHERE iUniqueId = '" . $iUniqueId . "' $whereserviceId";
        $obj->sql_query($query);
       header("Location:bsr_banner.php?".$ParameterUrl);
        exit();
    } else {
        $_SESSION['success'] = '2';
       header("Location:bsr_banner.php?".$ParameterUrl); 
        exit();
    }
}
$db_data1 = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE 1 $whereserviceId  AND eFor = 'General' ORDER BY iDisplayOrder");
//$db_data2 = $obj->MySQLSelect("SELECT * FROM " . $tbl_name . " WHERE 1 $whereserviceId AND vCode != '" . $default_lang . "' AND eFor = 'General' ORDER BY vCode,iDisplayOrder");
$db_dataAll = $db_data1;
$total_results = scount($db_dataAll);
$total_pages = ceil($total_results / $per_page);//total pages we going to have
$show_page = 1;

//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    } else {
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
} else {
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages=$total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$db_data = array_slice($db_dataAll, $start, $per_page); 
$endRecord = scount($db_data);
$var_filter = "";
foreach ($_REQUEST as $key=>$val)
{
    if($key != "tpages" && $key != 'page')
    $var_filter.= "&$key=".stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages.$var_filter;
?>
<!DOCTYPE html>
<!--[if IE 8]> 
<html lang="en" class="ie8">
<![endif]-->
<!--[if IE 9]> 
<html lang="en" class="ie9">
<![endif]-->
<!--[if !IE]><!--> 
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Banners</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once 'global_files.php'; ?>
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <!-- <script type="text/javascript">
            function confirm_delete()
            {
                var confirm_ans = confirm("Are You sure You want to Delete Banner?");
                return confirm_ans;
            }
        </script> -->
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53" >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once 'header.php'; ?>
            <?php include_once 'left_menu.php'; ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Banner</h2>
                            <?php if ($userObj->hasPermission($create)) { ?>
                            <a href="bsr_banner_action.php?eType=<?= $_REQUEST['eType']?>">
                            <input type="button" value="Add Banner" class="add-btn">
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <hr />
                    <?php include 'valid_msg.php'; ?>
                    <?php if (scount($languages) > 1) { ?>
                    <form name="frmsearch" id="frmsearch" action="" >
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="200px" class="estatus_options">
                                        <input type="hidden" name="eType" value="<?php echo $_REQUEST['eType'];?>">
                                        <select name="selectlang" id="selectlang" class="form-control">
                                            <option value="" disabled>Select Language</option>
                                            <?php foreach ($languages as $lang) { ?>
                                            <option value="<?= $lang['vCode'] ?>" <?php
                                                if ($select_lang == $lang['vCode']) {
                                                    echo "selected";
                                                }
                                                ?> > <?= $lang['vTitle'] . ' (' . $lang['vCode'] . ')'; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                   
                                    <td>
                                        <?php $reload_new = "bsr_banner.php?eType=".$_REQUEST['eType'];?>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='<?php echo $reload_new;?>'"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                <?php } ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        Banner
                                    </div>
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align: center;">Image</th>
                                                        <th>Title</th>
                                                        <th style="text-align: center;">Language</th>
                                                        <th style="text-align: center;">Order</th>
                                                       <!--  <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                            <th style="text-align: center;">Status</th>
                                                        <?php } ?>
                                                        <?php if ($userObj->hasPermission($edit)) { ?>
                                                            <th style="text-align: center;">Edit</th>
                                                        <?php } ?>
                                                        <?php if ($userObj->hasPermission($delete)) { ?>
                                                            <th style="text-align: center;">Delete</th>
                                                        <?php } ?> -->
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $count_all = scount($db_data);
                                                        if ($count_all > 0) {
                                                            for ($i = 0; $i < $count_all; $i++) {
                                                                $vTitle = $db_data[$i]['vTitle'];
                                                                $vImage = $db_data[$i]['vImage'];
                                                                $vCode = $db_data[$i]['vCode'];
                                                                $iDisplayOrder = $db_data[$i]['iDisplayOrder'];
                                                                $eStatus = $db_data[$i]['eStatus'];
                                                                $iUniqueId = $db_data[$i]['iUniqueId'];
                                                                $checked = ($eStatus == "Active") ? 'checked' : '';
                                                                ?>
                                                    <tr class="gradeA">
                                                        <td width="15%" align="center">
                                                            <?php if ($vImage != '' && file_exists($tconfig['tsite_upload_images_panel'] . '/' . $vImage)) { ?>
                                                            <img src="<?= $tconfig["tsite_url"].'resizeImg.php?h=100&src='.$tconfig['tsite_upload_images'] . $vImage; ?>" height="50" >
                                                            <?php } else { echo $vImage; }  ?>
                                                        </td>
                                                        <td><?= $vTitle; ?></td>
                                                       <!--  <?php if (scount($service_cat_data) > 1) { ?>
                                                        <td  align="center">
                                                            <?php foreach ($service_cat_data as $servicedata) { ?>
                                                            <?php if ($servicedata['iServiceId'] == $db_data[$i]['iServiceId']) { ?><span><?php echo (isset($servicedata['vServiceName']) ? $servicedata['vServiceName'] : ''); ?></span><?php } ?>
                                                            <?php } ?>
                                                        </td>
                                                        <?php } ?>
 -->
                                                        <td align="center" width="10%"><?= $vCode; ?></td>
                                                        <td width="10%" align="center"><?= $iDisplayOrder; ?> </td>
                                                        <!-- <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                        <td width="10%" align="center">
                                                            <a href="bsr_banner.php?eType=<?= $_REQUEST['eType']?>&iUniqueId=<?= $iUniqueId; ?>&vCode=<?php echo $vCode;?>&selectlang=<?php echo $vCode;?>&status=<?= ($eStatus == "Active") ? 'Inactive' : 'Active' ?>">
                                                                <button class="btn">
                                                                <i class="<?= ($eStatus == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?= $eStatus; ?>
                                                                </button>
                                                            </a>
                                                        </td>
                                                        <?php } ?>
                                                        <?php if ($userObj->hasPermission($edit)) { ?>
                                                        <td width="10%" align="center">
                                                            <a href="bsr_banner_action.php?eType=<?= $_REQUEST['eType']?>&id=<?= $iUniqueId; ?>&vCode=<?= $vCode ?>">
                                                            <button class="btn btn-primary">
                                                            <i class="icon-pencil icon-white"></i> Edit
                                                            </button>
                                                            </a>
                                                        </td>
                                                        <?php } ?>
                                                        <?php if ($userObj->hasPermission($delete)) { ?>
                                                        <td width="10%" align="center">
                                                            <form name="delete_form" id="delete_form" method="post" action="" onsubmit="return confirm_delete()" class="margin0">
                                                                <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?= $iUniqueId; ?>">
                                                                <input type="hidden" name="vCode" id="vCode" value="<?= $vCode; ?>">
                                                                <input type="hidden" name="iMasterServiceCategoryId" id="iMasterServiceCategoryId" value="<?= $iMasterServiceCategoryId; ?>">
                                                                <button class="btn btn-danger"> <i class="icon-remove icon-white"></i> Delete </button>
                                                            </form>
                                                        </td>
                                                        <?php } ?> -->
                                                        <td width="10%"  align="center">
                                                            <?php
                                                            if ($eStatus == 'Active') {
                                                                $dis_img = "img/active-icon.png";
                                                            } else if ($eStatus == 'Inactive') {
                                                                $dis_img = "img/inactive-icon.png";
                                                            } else if ($eStatus == 'Deleted') {
                                                                $dis_img = "img/delete-icon.png";
                                                            }
                                                            ?>
                                                            <img src="<?= $dis_img; ?>" alt="<?= $eStatus; ?>" data-toggle="tooltip" title="<?= $eStatus; ?>">
                                                        </td>
                                                        <td width="10%" align="center" style="text-align:center;" class="action-btn001">
                                                            <div class="share-button openHoverAction-class" style="display: block;">
                                                                <label class="entypo-export"><span><img src="images/settings-icon.png"  alt=""></span></label>
                                                                <div class="social show-moreOptions openPops_<?= $iUniqueId; ?>">
                                                                    <ul>
                                                                        <?php if ($userObj->hasPermission($edit)) { ?>
                                                                        <li class="entypo-twitter" data-network="twitter">
                                                                            <a href="bsr_banner_action.php?eType=<?= $_REQUEST['eType']?>&id=<?= $iUniqueId; ?>&vCode=<?= $vCode ?>" data-toggle="tooltip" title="Edit">
                                                                            <img src="img/edit-icon.png" alt="Edit">
                                                                            </a></li>
                                                                        <?php }  ?>
                                                                        <?php if ($userObj->hasPermission($updateStatus)) { ?>
                                                                            <li class="entypo-facebook" data-network="facebook"> 
                                                                                <a href="javascript:void(0);" onClick='window.location.href="bsr_banner.php?eType=<?= $_REQUEST['eType']?>&iUniqueId=<?= $iUniqueId; ?>&status=Active<?= ($eBuyAnyService != "") ? '&' . $eBuyAnyService : (($eForService != "") ? '&' . $eForService : '') ?>&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?><?= (in_array($eType, ["NearBy","Runner","Genie"])) ? '&eType=' . $eType : ''; ?>"' data-toggle="tooltip" title="Activate">
                                                                                    <img src="img/active-icon.png" alt="<?php echo $eStatus; ?>">
                                                                                </a>
                                                                            </li>
                                                                            <li class="entypo-gplus" data-network="gplus">
                                                                                <a href="javascript:void(0);" onClick='window.location.href="bsr_banner.php?eType=<?= $_REQUEST['eType']?>&iUniqueId=<?= $iUniqueId; ?>&status=Inactive<?= ($eBuyAnyService != "") ? '&' . $eBuyAnyService : (($eForService != "") ? '&' . $eForService : '') ?>&langSearch=<?= $vCode ?>&vCode=<?= $vCode ?><?= (in_array($eType, ["NearBy","Runner","Genie"])) ? '&eType=' . $eType : ''; ?>"' data-toggle="tooltip" title="Deactivate">
                                                                                    <img src="img/inactive-icon.png" alt="<?php echo $eStatus; ?>">
                                                                                </a>
                                                                            </li>
                                                                        <?php } ?>
                                                                        <?php if ($userObj->hasPermission($delete)) {  ?>
                                                                            <li class="entypo-gplus" data-network="gplus"><a href="javascript:void(0);" onClick="confirm_delete('<?= $iUniqueId; ?>','<?= $vCode; ?>','<?=$iMasterServiceCategoryId?>');" data-toggle="tooltip"  title="Delete">
                                                                                    <img src="img/delete-icon.png"   alt="Delete">
                                                                                </a></li>
                                                                        <?php } ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php }
                                                        }
                                                        ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php include('pagination_n.php'); ?>
                                    </div>
                                </div>
                            </div>
                            <!--TABLE-END-->
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iFaqcategoryId" id="iFaqcategoryId" value="" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
            <input type="hidden" name="eType" id="eType" value="<?php echo $eMasterType; ?>" >
        </form>
        <?php include_once 'footer.php'; ?>
        <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
        <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
        <script>
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

            function confirm_delete(iUniqueId,vCode,iMasterServiceCategoryId) {
                var confirm_ans = confirm("Are You sure You want to Delete Banner?");
                if (confirm_ans == true) {
                    window.location.href = 'bsr_banner.php?eType=<?php echo $_REQUEST['eType'];?>&hdn_del_id='+iUniqueId+'&vCode='+vCode+'&iMasterServiceCategoryId='+iMasterServiceCategoryId;
                }
            }
        </script>
    </body>
    <!-- END BODY-->
</html>
