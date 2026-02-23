<?php
include_once('../common.php');
$eMasterType = isset($_REQUEST['eType'])?geteTypeForBSR($_REQUEST['eType']):"RentItem";

$ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY = ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY;
//$userObj->redirect();
if (!$userObj->hasPermission('manage-'.strtolower($eMasterType).'-fields')){
    $userObj->redirect();
}
$iMasterServiceCategoryId = get_value($master_service_category_tbl,'iMasterServiceCategoryId','eType',$eMasterType,'','true');
$script = $eMasterType.'Fields';
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
/* get make */
$iRentItemId = isset($_REQUEST['iRentItemId'])?$_REQUEST['iRentItemId']:'';
$iRentFieldId = isset($_REQUEST['iRentFieldId'])?$_REQUEST['iRentFieldId']:'';
$eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:'';
$success = isset($_REQUEST['success'])?$_REQUEST['success']:0;
$tbl_name = 'rentitem_fields';
//Start Sorting
$sortby = isset($_REQUEST['sortby'])?$_REQUEST['sortby']:0;
$order = isset($_REQUEST['order'])?$_REQUEST['order']:'';
$ord = ' ORDER BY r.iRentItemId,r.iOrder ASC';
if ($sortby == 1){
    if ($order == 0){
        $ord = " ORDER BY r.vFieldName ASC";
    }else{
        $ord = " ORDER BY r.vFieldName DESC";
    }
}
if ($sortby == 2){
    if ($order == 0){
        $ord = " ORDER BY r.iRentItemId ASC";
    }else{
        $ord = " ORDER BY r.iRentItemId DESC";
    }
}
if ($sortby == 3){
    if ($order == 0){
        $ord = " ORDER BY r.iOrder ASC";
    }else{
        $ord = " ORDER BY r.iOrder DESC";
    }
}
if ($sortby == 4){
    if ($order == 0){
        $ord = " ORDER BY r.eStatus ASC";
    }else{
        $ord = " ORDER BY r.eStatus DESC";
    }
}
//End Sorting
// Start Search Parameters
$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$ssql = '';
if ($keyword != ''){
    if ($option != ''){
        if (strpos($option,'eStatus') !== false){
            $ssql .= " AND ".stripslashes($option)." LIKE '".stripslashes($keyword)."'";
        }else{
            $ssql .= " AND ".stripslashes($option)." LIKE '%".stripslashes($keyword)."%'";
        }
    }else{
        $ssql .= " AND r.vFieldName LIKE '%".$keyword."%' OR r.eStatus LIKE '%".$keyword."%'";
    }
}
$ssql = '';
if ($keyword != ''){

    $keyword_new = $keyword;
    $chracters = array("(","+",")");
    $removespacekeyword = preg_replace('/\s+/','',$keyword);
    $keyword_new = trim(str_replace($chracters,"",$removespacekeyword));
    if (is_numeric($keyword_new)){

        $keyword_new = $keyword_new;
    }else{

        $keyword_new = $keyword;
    }
    if ($eStatus != '' && $iRentItemId == ''){

        $ssql .= " AND r.vFieldName LIKE '%".$keyword."%' AND r.eStatus = '".clean($eStatus)."'";
    }else if ($eStatus != '' && $iRentItemId != ''){

        $ssql .= " AND r.vFieldName LIKE '%".$keyword."%' AND r.eStatus = '".clean($eStatus)."' AND r.iRentItemId = '".clean($iRentItemId)."'";
    }else if ($eStatus == '' && $iRentItemId != ''){

        $ssql .= " AND r.vFieldName LIKE '%".$keyword."%' AND r.iRentItemId = '".clean($iRentItemId)."'  AND r.eStatus != 'Deleted'";
    }else{

        $ssql .= " AND r.vFieldName LIKE '%".$keyword."%'  AND r.eStatus != 'Deleted'";
    }
}else if ($eStatus != '' && $keyword == '' && $iRentItemId == ''){

    $ssql .= " AND r.eStatus = '".clean($eStatus)."'";
}else if ($eStatus != '' && $keyword == '' && $iRentItemId != ''){

    $ssql .= " AND r.eStatus = '".clean($eStatus)."' AND r.iRentItemId = '".$iRentItemId."'";
}else if ($eStatus == '' && $keyword == '' && $iRentItemId != ''){

    $ssql .= " AND r.iRentItemId = '".$iRentItemId."' AND r.eStatus != 'Deleted'";
}else{
    $eStatussql = " AND r.eStatus != 'Deleted'";
}
// End Search Parameters
$eTypesql = "";
if ($iMasterServiceCategoryId != ""){
    $eTypesql = " And rc.iMasterServiceCategoryId = '".$iMasterServiceCategoryId."'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "SELECT COUNT(r.iRentFieldId) AS Total FROM `".$tbl_name."` as r LEFT JOIN rent_items_category as rc on rc.iRentItemId=r.iRentItemId WHERE 1=1 $eTypesql $eStatussql $ssql";
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])){
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages){
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }else{
        // error - show first set of results
        $start = 0;
        $end = $per_page;
    }
}else{
    // if page isn't set, show first set of results
    $start = 0;
    $end = $per_page;
}
// display pagination
$page = isset($_GET['page'])?intval($_GET['page']):0;
$tpages = $total_pages;
if ($page <= 0){
    $page = 1;
}
//Pagination End
$sql = "SELECT r.* FROM ".$tbl_name." as r LEFT JOIN rent_items_category as rc on rc.iRentItemId=r.iRentItemId WHERE 1=1 $eTypesql $eStatussql $ssql $ord LIMIT $start, $per_page ";
$data_drv = $obj->MySQLSelect($sql);

$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val){
    if ($key != "tpages" && $key != 'page'){
        $var_filter .= "&$key=".stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
$ordersql = " ORDER BY iMasterServiceCategoryId,iDisplayOrder";
$rSql = "AND iMasterServiceCategoryId = '".$iMasterServiceCategoryId."' AND ( estatus = 'Active' || estatus = 'Inactive' )";
$rentitem = $RENTITEM_OBJ->getRentItemMaster('admin',$rSql,0,0,$default_lang,$ordersql);
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?=$SITE_NAME?> | Manage Form Fields</title>
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
                        <h2>Manage Form Fields</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                    <tbody>
                    <tr>
                        <td><label for="textfield"><strong>Search:</strong></label></td>
                        <td>
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" class="form-control"/>
                        </td>
                        <td class=" padding-right10">
                            <select name="iRentItemId" class="form-control">

                                <option value="">Select Category</option>

                                <?php foreach ($rentitem as $rentkey => $rentitemval){ ?>

                                    <option value="<?=$rentitemval['iRentItemId'];?>" <?=$rentitemval['iRentItemId'] == $iRentItemId?"selected":""?> ><?=$rentitemval['vTitle']?></option>
                                <?php } ?>

                            </select>
                        </td>
                        <?php if ($ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "No"){ ?>
                            <td class="estatus_options" id="eStatus_options">

                                <select name="eStatus" id="estatus_value" class="form-control">

                                    <option value="">Select Status</option>

                                    <option value='Active' <?php
                                    if ($eStatus == 'Active'){

                                        echo "selected";
                                    }
                                    ?> >Active

                                    </option>

                                    <option value="Inactive" <?php
                                    if ($eStatus == 'Inactive'){

                                        echo "selected";
                                    }
                                    ?> >Inactive

                                    </option>

                                    <option value="Deleted" <?php
                                    if ($eStatus == 'Deleted'){

                                        echo "selected";
                                    }
                                    ?> >Delete

                                    </option>

                                </select>

                            </td>
                        <?php } ?>

                        <td>
                            <?php $reloadurl = "data_fields.php?eType=".$_REQUEST['eType']; ?>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search"/>
                            <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href='<?php echo $reloadurl; ?>'"/>
                        </td>

                        <?php if ($userObj->hasPermission('create-'.strtolower($eMasterType).'-fields') && $ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "No"){ ?>
                            <td>
                                <a class="add-btn" href="data_fields_action.php?eType=<?php echo $_REQUEST['eType']; ?>" style="text-align: center;">Add Fields</a>
                            </td>
                        <?php } ?>
                    </tr>
                    </tbody>
                </table>

            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export">
                            <div class="changeStatus col-lg-12 option-box-left">
                                    <span class="col-lg-2 new-select001">
                                        <?php if (
                                            $userObj->hasPermission(['update-status-rentitem-fields',
                                                                     'delete-rentitem-fields'])
                                        ){ ?>
                                            <select name="changeStatus" id="changeStatus" class="form-control" onchange="ChangeStatusAll(this.value);">
                                                    <option value="">Select Action</option>
                                                    <?php if ($userObj->hasPermission('update-status-rentitem-fields')){ ?>
                                                        <option value='Active' <?php if ($option == 'Active'){
                                                            echo "selected";
                                                        } ?> >Active</option>
                                                        <option value="Inactive" <?php if ($option == 'Inactive'){
                                                            echo "selected";
                                                        } ?> >Inactive</option>
                                                    <?php } ?>

                                                   <!--  <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-rentitem-fields')){ ?>
                                                        <option value="Deleted" <?php if ($option == 'Delete'){
                                                       echo "selected";
                                                   } ?> >Delete</option>
                                                    <?php } ?> -->
                                            </select>
                                        <?php } ?>
                                    </span>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>

                                        <?php if ($ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "No"){ ?>
                                            <th align="center" width="3%" style="text-align:center;">
                                                <input type="checkbox" id="setAllCheck"></th>
                                        <?php } ?>

                                        <th width="20%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Field Name <?php if ($sortby == 1){
                                                    if ($order == 0){ ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }else{ ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>

                                        <th width="20%">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2'){
                                                echo $order;
                                            }else{ ?>0<?php } ?>)">Cateogry <?php if ($sortby == 2){
                                                    if ($order == 0){ ?>
                                                        <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                }else{ ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>

                                        <?php if ($ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "No"){ ?>
                                            <th width="8%" align="center" style="text-align:center;">
                                                <a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3'){
                                                    echo $order;
                                                }else{ ?>0<?php } ?>)">Order <?php if ($sortby == 3){
                                                        if ($order == 0){ ?>
                                                            <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                            <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    }else{ ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                </a></th>

                                            <th width="8%" align="center" style="text-align:center;">
                                                <a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4'){
                                                    echo $order;
                                                }else{ ?>0<?php } ?>)">Status <?php if ($sortby == 4){
                                                        if ($order == 0){ ?>
                                                            <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php }else{ ?>
                                                            <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }
                                                    }else{ ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?>
                                                </a></th>
                                        <?php } ?>
                                        <th width="8%" align="center" style="text-align:center;">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)){

                                        for ($i = 0;$i < scount($data_drv);$i++){

                                            /*$default = '';
                                            if ($data_drv[$i]['eDefault'] == 'Yes'){
                                                $default = 'disabled';
                                            }*/
                                            $tFieldName = json_decode($data_drv[$i]['tFieldName'] , true);

                                            $eStatus = $data_drv[$i]['eStatus'];
                                            $eDescription = $data_drv[$i]['eDescription'];
                                            $eTitle = $data_drv[$i]['eTitle'];
                                            $eListing = $data_drv[$i]['eListing'];
                                            $getrentitem = $RENTITEM_OBJ->getrentitem('admin',$data_drv[$i]['iRentItemId']);
                                            ?>
                                            <tr class="gradeA">
                                                <?php if ($ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "No"){ ?>
                                                    <td align="center" style="text-align:center;">
                                                        <input type="checkbox" id="checkbox" name="checkbox[]" <?php //echo $default; ?> value="<?php echo $data_drv[$i]['iRentFieldId']; ?>"/>&nbsp;
                                                    </td>

                                                <?php } ?>

                                                <td><?=$tFieldName['tFieldName_EN'];?></td>
                                                <td><?=$getrentitem['vTitle'];?></td>
                                                <?php if ($ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "No"){ ?>
                                                    <td align="center" style="text-align:center;"><?=$data_drv[$i]['iOrder'];?></td>

                                                    <td align="center" style="text-align:center;">
                                                        <?php if ($data_drv[$i]['eStatus'] == 'Active'){
                                                            $dis_img = "img/active-icon.png";
                                                        }else if ($data_drv[$i]['eStatus'] == 'Inactive'){
                                                            $dis_img = "img/inactive-icon.png";
                                                        }else if ($data_drv[$i]['eStatus'] == 'Deleted'){
                                                            $dis_img = "img/delete-icon.png";
                                                        } ?>
                                                        <img src="<?=$dis_img;?>" alt="<?=$data_drv[$i]['eStatus'];?>" data-toggle="tooltip" title="<?=$data_drv[$i]['eStatus'];?>">
                                                    </td>
                                                <?php } ?>
                                                <td align="center" style="text-align:center;" class="action-btn001">
                                                    <div class="share-button openHoverAction-class" style="display: block;">
                                                        <label class="entypo-export"><span><img src="images/settings-icon.png" alt=""></span></label>
                                                        <div class="social show-moreOptions openPops_<?=$data_drv[$i]['iRentFieldId'];?>">
                                                            <ul>
                                                                <li class="entypo-twitter" data-network="twitter">
                                                                    <a href="data_fields_action.php?eType=<?=$_REQUEST['eType']?>&id=<?=$data_drv[$i]['iRentFieldId'];?>" data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a></li>
                                                                <?php //if ($data_drv[$i]['eDefault'] != 'Yes'){ ?>
                                                                    <?php if ($userObj->hasPermission('update-status-rentitem-fields')){ ?>
                                                                        <li class="entypo-facebook" data-network="facebook">
                                                                            <a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iRentFieldId']; ?>','Inactive')" data-toggle="tooltip" title="Active">
                                                                                <img src="img/active-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                            </a></li>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);" onclick="changeStatus('<?php echo $data_drv[$i]['iRentFieldId']; ?>','Active')" data-toggle="tooltip" title="Inactive">
                                                                                <img src="img/inactive-icon.png" alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                                            </a></li>
                                                                    <?php } ?>
                                                                    <?php if ($eStatus != 'Deleted' && $userObj->hasPermission('delete-rentitem-fields') && ($eListing != 'Yes' && $eDescription != 'Yes' && $eTitle != 'Yes')){ ?>
                                                                        <li class="entypo-gplus" data-network="gplus">
                                                                            <a href="javascript:void(0);" onclick="changeStatusDelete('<?php echo $data_drv[$i]['iRentFieldId']; ?>')" data-toggle="tooltip" title="Delete">
                                                                                <img src="img/delete-icon.png" alt="Delete">
                                                                            </a></li>
                                                                    <?php } ?>
                                                                <?php //} ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php }
                                    }else{ ?>
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
                    <li>
                        Administrator can Activate / Deactivate / Delete any Rent Item Fields.
                    </li>
                    <li>
                        Administrator can export data in XLS or PDF format.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<form name="pageForm" id="pageForm" action="action/data_fields.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iRentFieldId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="eType" value="<?=$_REQUEST['eType'];?>">
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
        var action = $("#_list_form").attr('action');
        var formValus = $("#frmsearch").serialize();
        //window.location.href = action+"?"+formValus;
        window.location.href = action + "?eType=<?php echo $_REQUEST['eType'];?>&" + formValus;
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