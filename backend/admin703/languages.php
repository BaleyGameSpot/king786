<?php
include_once('../common.php');
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$catdata = serviceCategories;
$allservice_cat_data = json_decode($catdata, true);

$selectedlanguage = isset($_REQUEST['selectedlanguage']) ? stripslashes($_REQUEST['selectedlanguage']) : '';
if (!$userObj->hasPermission('view-general-label')) {
    $userObj->redirect();
}
//edit is available in webproject demo and not in bbcsproducts server
$edit_available = 0;
if ( !empty($_SERVER['HTTP_HOST']) && !in_array('bbcsproducts', explode('.', $_SERVER['HTTP_HOST']))  ) {
    $edit_available = 1;
}
if (!empty($_SERVER['HTTP_HOST']) && ( $_SERVER['HTTP_HOST'] == "dev.bbcsproducts.net" || $_SERVER['HTTP_HOST'] == "demo.bbcsproducts.net"))
{
    $edit_available = 1;
}

//$edit_available = 1;
$tbl_name = 'language_label';
$script = 'language_label';
if ($selectedlanguage != '') {
    $tbl_name = 'language_label_' . $selectedlanguage;
    $script = 'language_label_' . $selectedlanguage;
}
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY vValue ASC';
if ($sortby == 1) {
    if ($order == 0) $ord = " ORDER BY vLabel ASC"; else
        $ord = " ORDER BY vLabel DESC";
}
if ($sortby == 2) {
    if ($order == 0) $ord = " ORDER BY vValue ASC"; else
        $ord = " ORDER BY vValue DESC";
}
//End Sorting
$adm_ssql = "";
if (SITE_TYPE == 'Demo') {
    //$adm_ssql = " And ad.tRegistrationDate > '" . WEEK_DATE . "'";
}
// Start Search Parameters
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$keywords = isset($_REQUEST['keywords']) ? stripslashes($_REQUEST['keywords']) : "";
$checktext = isset($_REQUEST['checktext']) ? stripslashes($_REQUEST['checktext']) : "";
//$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$default_lang_title = $LANG_OBJ->FetchSystemDefaultLangName();
$hdn_del_id = isset($_POST['hdn_del_id']) ? $_POST['hdn_del_id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$page_id = isset($_REQUEST['lp_id']) ? $_REQUEST['lp_id'] : 0;
$pageid = isset($_REQUEST['lp_id']) ? stripslashes($_REQUEST['lp_id']) : "";
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : "All ";
$ssql = '';
if ($keywords != '') {
    $ssql .= " AND (vLabel  = '" . addslashes($keywords) . "') ";
}
if ($keyword != '') {
    if ($option != '') {
        if (strpos($option, 'eStatus') !== false) {
            $ssql .= " AND " . addslashes($option) . " LIKE '" . addslashes($keyword) . "'";
        } else {
            if ($checktext == 'Yes' && $option == 'vValue') {
                $ssql .= " AND " . addslashes($option) . " LIKE '" . addslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . addslashes($option) . " LIKE '%" . addslashes($keyword) . "%'";
            }
        }
    } else {
        $ssql .= " AND (vLabel  LIKE '%" . addslashes($keyword) . "%' OR vValue  LIKE '%" . addslashes($keyword) . "%') ";
    }
}
// End Search Parameters
if ($pageid != "") {
    $ssql .= " AND lPage_id = '" . $pageid . "'";
}
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$sql = "select vTitle from language_master where vCode = '" . $default_lang . "'";
$lang_title = $obj->MySQLSelect($sql);
$sql = "SELECT COUNT(LanguageLabelId) AS Total FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "' and eStatus='Active' $ssql";
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
if ($page <= 0) $page = 1;
//Pagination End
$sql = "SELECT LanguageLabelId,lPage_id,vCode,vLabel,vValue FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "' and eStatus='Active' $ssql $ord LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page') $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;


/*------------------for show a default label value-----------------*/

$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . $tbl_name . "' AND COLUMN_NAME = 'reference'";
$GetColumns  = $obj->MySQLSelect($sql);
$showReferenceValue = 0;
if(isset($GetColumns) && !empty($GetColumns))
{

    $sql_label = '';
    $sql_text_prepare = '';

    $getvLabel = array_column($data_drv , "vLabel");
    if(isset($getvLabel) && !empty($getvLabel)){

        $getvLabelCount = count($getvLabel);
        foreach($getvLabel as $key => $Label)
        {
            if($getvLabelCount == ($key + 1)){
                $sql_text_prepare .= "'" . $Label . "'";
            }else{
                $sql_text_prepare .= "'" . $Label . "',";
            }

        }

        $sql_label = ' AND vLabel IN (' . $sql_text_prepare . ')';
    }
    $showReferenceValue = 1;

    $sql = "SELECT LanguageLabelId,lPage_id,vCode,vLabel,vValue,reference FROM " . $tbl_name . " WHERE vCode = 'EN' AND eStatus='Active' $ssql $sql_label";

    $default_value = $obj->MySQLSelect($sql);
    $default_value_arr = [];
    foreach ($default_value as $value)
    {
        $default_value_arr[$value['vLabel']] = $value['reference'];
    }
}

/*------------------for show a default label value-----------------*/
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | Language Label</title>
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
                        <?php
                        if ($selectedlanguage != '') {
                            if (scount($allservice_cat_data) > 0) {
                                foreach ($allservice_cat_data as $langOpt) {
                                    /* added by SP for taskapp on 1-7-2019, in all links name shown wrong */
                                    if ($selectedlanguage == $langOpt['iServiceId']) {
                                        $name = clearName($langOpt['vServiceName']);
                                    }
                                }
                            }
                        } else {
                            $name = '';
                        }

                        ?>
                        <h2><?php echo $langage_lbl_admin['LBL_LANGUAGE_ADMIN']; ?> <?php if (!empty($name)) { ?> (<?= $name ?>) <?php } ?></h2>
                    </div>
                </div>
                <hr/>
                <!-- <?php if (SITE_TYPE != 'Demo') { ?>
                                                <div class="languages-top-part">
                            <?php if (!isset($_SESSION['sess_editingToken'])) { ?>
                                                                                                    <h3 class="box_a">For Easy editing click "Enable Online Web Editing"</h3>
                            <?php } else { ?> 
                                                                                            <h3 class="box_a">To disable Easy editing click "Disable Online Web Editing"</h3>
                            <?php } ?>
                                                      
                                                       <div class="admin_bax1">
                                                                <p><?php if (!isset($_SESSION['sess_editingToken'])) { ?>
                                                                                                                    <a href="easy_editing_save.php?type=enable&platform=web" class="btn btn-primary">Enable Online Web Editing</a>
                            <?php } else { ?>
                                                                                        <a href="easy_editing_save.php?type=disable&platform=web" class="btn btn-danger">Disable Online Web Editing</a>  <a href="<?php echo $tconfig['tsite_url']; ?>" target="_blank" class="btn btn-primary">View Website</a> 
                            <?php } ?>
                                                                </p>
                                                            </div>
                                                      </div>
                        <?php } ?> -->
                <div class="clearfix"></div>
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
                        <td width="10%" class=" padding-right10">
                            <select name="option" id="option" class="form-control">
                                <option value="">All</option>
                                <option value="vLabel" <?php
                                if ($option == "vLabel") {
                                    echo "selected";
                                }
                                ?> >Code
                                </option>
                                <option value="vValue" <?php
                                if ($option == 'vValue') {
                                    echo "selected";
                                }
                                ?> >Value In <?= $lang_title[0]['vTitle'] ?> Language
                                </option>
                            </select>
                        </td>
                        <td width="15%">
                            <input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"
                                   class="form-control"/>
                        </td>
                        <td width="6%" id="exactcheckbox">
                            <div class="checkbox" style="margin-left:5px;">
                                <input type="checkbox" name="checktext" value="Yes" id="exactcheckbox_val" <?php
                                if ($checktext == 'Yes') {
                                    echo 'checked';
                                }
                                ?> >
                                Exact Value
                            </div>
                        </td>
                        <td>
                            <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search"
                                   title="Search"/>
                            <?php $BackButtonlink = !empty($selectedlanguage) ? 'languages.php?selectedlanguage='.$selectedlanguage: 'languages.php';?>
                            <input type="button" value="Reset" class="btnalt button11"
                                   onClick="window.location.href = '<?php echo $BackButtonlink;?>'"/>
                        </td>
                        <?php if ($userObj->hasPermission('create-general-label')) { ?>


                        <td width="30%">
                            <?php if (!empty($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == "dev.bbcsproducts.net")) { ?>
                                <?php if (empty($selectedlanguage)) { ?>
                                    <a class="add-btn input-pass" href="javascript:void(0);" data-toggle="modal"
                                       data-target="#input_pass_modal"
                                       data-fileaction="languages_action_multisystem.php" style="text-align: center;">
                                        Add Label
                                    </a>
                                <?php } else { ?>
                                    <a class="add-btn input-pass" href="javascript:void(0);" data-toggle="modal"
                                       data-target="#input_pass_modal"
                                       data-fileaction="languages_action_multisystem_food_other.php"
                                       style="text-align: center;">Add Label
                                    </a>
                                <?php }
                            } else { ?>
                                <?php if ($userObj->hasPermission('create-general-label')) {
                                    if ($edit_available == 1) {
                                        if (!empty($selectedlanguage)) { ?>
                                            <a class="add-btn"
                                               href="languages_action.php?selectedlanguage=<?= $selectedlanguage ?>"
                                               style="text-align: center;">Add Label
                                            </a>
                                        <?php } else { ?>
                                            <a class="add-btn" href="languages_action.php" style="text-align: center;">
                                                Add Label
                                            </a>
                                        <?php }
                                    }
                                } ?>
                            <?php } ?>
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
                                <!-- <span class="col-lg-2 new-select001">
                                            <select name="changeStatus" id="changeStatus" class="form-control" onChange="ChangeStatusAll(this.value);">
                                                    <option value="" >Select Action</option>
                                                    
                                                    <option value="Deleted" <?php
                                if ($option == 'Delete') {
                                    echo "selected";
                                }
                                ?> >Make Delete</option>
                                            </select>
                                    </span> -->
                                <!-- <form method="POST" action="" name="mylangform">
                                        <?php if (scount($allservice_cat_data) > 1) { ?>
                                                             <span class="col-lg-2 new-select001">
                                                                 <select name="selectedlanguage" id="selectedlanguage" class="form-control" >
                                            <?php foreach ($allservice_cat_data as $langOpt) { ?>
                                                                                     <option value="<?php echo $langOpt['iServiceId']; ?>" <?php
                                    if ($selectedlanguage == $langOpt['iServiceId']) {
                                        echo "selected";
                                    }
                                    ?>><?php echo clearName($langOpt['vServiceName']); ?></option>
                                            <?php } ?>
                                                                 </select>
                                                             </span>
                                        <?php } else { ?>
                                                             <input type="hidden" name="selectedlanguage" id="selectedlanguage" value="<?= $allservice_cat_data[0]['iServiceId']; ?>">
                                        <?php } ?>
                                         </form> -->
                            </div>
                            <?php if (!empty($data_drv) && $_SERVER['HTTP_HOST'] == "dev.bbcsproducts.net" && $userObj->hasPermission('export-general-label')) { ?>
                                <div class="panel-heading">
                                    <form name="_export_form" id="_export_form" method="post">
                                        <button type="button" onClick="showExportTypes('languages')">Export</button>
                                    </form>
                                </div>
                            <?php } ?>
                        </div>
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                  action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover dd-tt">
                                    <thead>
                                    <tr>

                                        <?php if($showReferenceValue == 1){ ?>

                                            <th width="30%">
                                                Default Text (Read Only)
                                            </th>

                                        <?php } ?>

                                        <th width="20%">
                                            <a href="javascript:void(0);" onClick="Redirect(1,<?php
                                            if ($sortby == '1') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Code <?php
                                                if ($sortby == 1) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <th width="60%">
                                            <a href="javascript:void(0);" onClick="Redirect(2,<?php
                                            if ($sortby == '2') {
                                                echo $order;
                                            } else {
                                                ?>0<?php } ?>)">Value In <?= $lang_title[0]['vTitle'] ?> Language <?php
                                                if ($sortby == 2) {
                                                    if ($order == 0) {
                                                        ?>
                                                        <i class="fa fa-sort-amount-asc"
                                                           aria-hidden="true"></i> <?php } else { ?>
                                                        <i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php
                                                    }
                                                } else {
                                                    ?>
                                                    <i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a>
                                        </th>
                                        <?php if ($edit_available == 1 && $userObj->hasPermission(['edit-general-label', 'delete-general-label'])) { ?>
                                            <th width="8%" align="center" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($data_drv)) {
                                        for ($i = 0; $i < scount($data_drv); $i++) { ?>
                                            <tr class="gradeA">
                                                <?php if($showReferenceValue == 1){ ?>
                                                    <td>
                                                        <?php if (isset($default_value_arr[$data_drv[$i]['vLabel']])) { ?>
                                                            <?= $default_value_arr[$data_drv[$i]['vLabel']]; ?>
                                                        <?php } else {
                                                            echo "-";
                                                        } ?>
                                                    </td>
                                                <?php } ?>

                                                <td><?= $data_drv[$i]['vLabel']; ?></td>
                                                <td><?= $data_drv[$i]['vValue']; ?></td>
                                                <?php if ($edit_available == 1 && $userObj->hasPermission(['edit-general-label', 'delete-general-label'])) { ?>
                                                    <td align="center" style="text-align:center;" class="action-btn001">
                                                        <?php if (!empty($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == "dev.bbcsproducts.net")) { ?>
                                                            <?php if (empty($selectedlanguage)) { ?>
                                                                <?php if ($userObj->hasPermission('edit-general-label')) { ?>
                                                                    <a class="input-pass" href="javascript:void(0);"
                                                                       data-toggle="modal"
                                                                       data-target="#input_pass_modal"
                                                                       data-fileaction="languages_action_multisystem.php?id=<?= $data_drv[$i]['LanguageLabelId']; ?>">
                                                                        <img src="img/edit-icon.png" alt="Edit"
                                                                             data-toggle="tooltip" title="Edit">
                                                                    </a>
                                                                <?php } ?>
                                                                <?php /*<a href="javascript:void(0)" data-toggle="tooltip" title="Delete" onclick="delete_lang_label('<?= $data_drv[$i]['vLabel']; ?>')">
                                                                            <img src="img/delete-icon.png" alt="Delete">
                                                                        </a>*/ ?>
                                                                <?php if ($userObj->hasPermission('delete-general-label')) { ?>
                                                                    <a class="input-pass" href="javascript:void(0)"
                                                                       data-toggle="modal"
                                                                       data-target="#input_pass_modal"
                                                                       data-fileaction="delete_label"
                                                                       data-langlabel="<?= $data_drv[$i]['vLabel']; ?>"
                                                                       data-action="delete_lang_label">
                                                                        <img src="img/delete-icon.png" alt="Delete">
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } else { ?>
                                                                <?php if ($userObj->hasPermission('edit-general-label')) { ?>
                                                                    <a class="input-pass" href="javascript:void(0);"
                                                                       data-toggle="modal"
                                                                       data-target="#input_pass_modal"
                                                                       data-fileaction="languages_action_multisystem_food_other.php?id=<?= $data_drv[$i]['LanguageLabelId']; ?>&selectedlanguage=<?= $selectedlanguage ?>">
                                                                        <img src="img/edit-icon.png" alt="Edit"
                                                                             data-toggle="tooltip" title="Edit">
                                                                    </a>
                                                                <?php } ?>
                                                                <?php /*<a href="javascript:void(0)" data-toggle="tooltip" title="Delete" onclick="delete_food_other_lang_label('<?= $data_drv[$i]['vLabel']; ?>','<?= $selectedlanguage ?>')">
                                                                            <img src="img/delete-icon.png" alt="Delete">
                                                                        </a>*/ ?>
                                                                <?php if ($userObj->hasPermission('delete-general-label')) { ?>
                                                                    <a class="input-pass" href="javascript:void(0)"
                                                                       data-toggle="modal"
                                                                       data-target="#input_pass_modal"
                                                                       data-fileaction="delete_label"
                                                                       data-langlabel="<?= $data_drv[$i]['vLabel']; ?>"
                                                                       data-action="delete_food_other_lang_label">
                                                                        <img src="img/delete-icon.png" alt="Delete">
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } else {
                                                            if (!empty($selectedlanguage)) { ?>
                                                                <?php if ($userObj->hasPermission('edit-general-label')) { ?>
                                                                    <a href="languages_action.php?id=<?= $data_drv[$i]['LanguageLabelId']; ?>&selectedlanguage=<?= $selectedlanguage ?>"
                                                                       data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } else { ?>
                                                                <?php if ($userObj->hasPermission('edit-general-label')) { ?>
                                                                    <a href="languages_action.php?id=<?= $data_drv[$i]['LanguageLabelId']; ?>"
                                                                       data-toggle="tooltip" title="Edit">
                                                                        <img src="img/edit-icon.png" alt="Edit">
                                                                    </a>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
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
                    <li>
                        Language Label module will list all labels on this page.
                    </li>
                    <li>
                        Administrator can Edit any language label.
                    </li>
                    <li>
                        Administrator can export data in XLS format.
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="modal fade" id="input_pass_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4>Input Passwords
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <form class="form-horizontal" id="pass_form" method="POST" action="">
                <input type="hidden" id="iReferralId" name="iReferralId" value="">
                <div class="col-lg-12">
                    <div class="ddtt" style="margin-top: 10px">
                        <h4>Development DB <span class="red">*</span></h4>
                        <input type="text" name="development_db" id="development_db" class="form-control" style="margin-top: 5px">
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="ddtt" style="margin-top: 10px">
                        <h4>Project Setup DB <span class="red">*</span></h4>
                        <input type="text" name="project_setup_db" id="project_setup_db" class="form-control" style="margin-top: 5px">
                    </div>
                </div>
                <?php /*<div class="col-lg-12">
                    <div class="ddtt" style="margin: 10px 0 20px 0">
                        <h4>prod_kingx <span class="red">*</span></h4>
                        <input type="text" name="prod_kingx" id="prod_kingx" class="form-control" style="margin-top: 5px">
                    </div>
                </div>*/?>
                <div class="nimot-class-but" style="margin-bottom: 20px">
                    <button type="submit" class="btn btn-info btn-ok pass-submit-btn" style="margin-left: 15px !important">Submit</button>
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" style="margin-left: 10px">Close</button>
                </div>
            </form>
            <div style="clear:both;"></div>
        </div>
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/languages.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="vLabel" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>">
    <input type="hidden" name="checktext" id="checktext" value="<?php echo $checktext; ?>">
    <input type="hidden" name="selectedlanguage" id="selectedlanguage" value="<?php echo $selectedlanguage; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<?php include_once('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#exactcheckbox').hide();
        $('#option').each(function () {
            if (this.value == 'vValue') {
                $('#exactcheckbox').show();
            }
        });
    });
    $(function () {
        $('#option').change(function () {
            if ($('#option').val() == 'vValue') {
                $('#exactcheckbox').show();
            } else {
                $('#exactcheckbox').hide();
                $("#exactcheckbox_val").val("");
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
        var selectedlanguage = "<?php echo $selectedlanguage ?>";

        if (selectedlanguage != '') {
            window.location.href = action + "?" + formValus + "&selectedlanguage=" + selectedlanguage;
        } else {
            window.location.href = action + "?" + formValus;
        }
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
    $(document).ready(function () {
        $('#selectedlanguage').change(function () {
            mylangform.submit();
        });
    });

    function delete_lang_label_old(vLabel) {
        var confirm_box = confirm("Are you sure that you want to delete label '" + vLabel + "'");
        if (confirm_box == true) {
            $(".loader-default").show();

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_delete_language_label.php',
                'AJAX_DATA': {'vLabel': vLabel},
                'REQUEST_CACHE': false
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    var obj = JSON.parse(data);
                    //alert(obj.message);
                    $(".loader-default").hide();
                    if (obj.Action == 1) {
                        if (confirm(obj.message)) {
                            window.location.replace("languages.php");
                        }
                    } else {
                        if (confirm("Label is not deleted successfully")) {
                            window.location.replace("languages.php");
                        }
                        //alert("Label is not deleted successfully");
                    }
                } else {
                    console.log(response.result);
                    $(".loader-default").hide();
                }
            });
        }
    }

    function delete_lang_label(vLabel) {
        var confirm_box = confirm("Are you sure that you want to delete label '" + vLabel + "'");
        if (confirm_box == true) {
            $(".loader-default").show();

            var development_db = $('#development_db').val();
            var project_setup_db = $('#project_setup_db').val();
            // var prod_kingx = $('#prod_kingx').val();

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>languages_action_multisystem.php',
                'AJAX_DATA': {
                    'vDeleteLabel': vLabel,
                    'DeleteLabel': 'Yes',
                    'development_db': development_db,
                    'project_setup_db': project_setup_db,
                    // 'prod_kingx': prod_kingx
                },
                'REQUEST_CACHE': false
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    var obj = JSON.parse(data);
                    //alert(obj.message);
                    $(".loader-default").hide();
                    if (obj.Action == 1) {
                        if (confirm(obj.message)) {
                            window.location.replace("languages.php");
                        }
                    } else {
                        if (confirm("Label is not deleted successfully")) {
                            window.location.replace("languages.php");
                        }
                        //alert("Label is not deleted successfully");
                    }
                } else {
                    console.log(response.result);
                    $(".loader-default").hide();
                }
            });
        }
    }

    function delete_food_other_lang_label(vLabel) {
        var confirm_box = confirm("Are you sure that you want to delete label '" + vLabel + "'");
        if (confirm_box == true) {
            $(".loader-default").show();

            var development_db = $('#development_db').val();
            var project_setup_db = $('#project_setup_db').val();
            // var prod_kingx = $('#prod_kingx').val();

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>languages_action_multisystem_food_other.php',
                'AJAX_DATA': {
                    'vDeleteLabel': vLabel,
                    'DeleteLabel': 'Yes',
                    'development_db': development_db,
                    'project_setup_db': project_setup_db,
                    // 'prod_kingx': prod_kingx
                },
                'REQUEST_CACHE': false
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    var obj = JSON.parse(data);
                    //alert(obj.message);
                    $(".loader-default").hide();
                    if (obj.Action == 1) {
                        if (confirm(obj.message)) {
                            window.location.replace("languages.php");
                        }
                    } else {
                        if (confirm("Label is not deleted successfully")) {
                            window.location.replace("languages.php");
                        }
                        //alert("Label is not deleted successfully");
                    }
                } else {
                    console.log(response.result);
                    $(".loader-default").hide();
                }
            });
        }
    }

    $('.input-pass').click(function () {
        var fileaction = $(this).data('fileaction');
        $('.pass-submit-btn').attr('type', 'submit');
        if (fileaction == "delete_label") {
            $('.pass-submit-btn').attr('type', 'button');

            var file_data_action = $(this).data('action');
            var file_lang_label = $(this).data('langlabel');
            if (file_data_action == "delete_lang_label") {
                $('.pass-submit-btn').attr('onclick', 'delete_lang_label("' + file_lang_label + '")');
            } else {
                $('.pass-submit-btn').attr('onclick', 'delete_food_other_lang_label("' + file_lang_label + '")');
            }
        } else {
            $('#pass_form').attr('action', fileaction);
        }
    });

    $('#pass_form').validate({
        rules: {
            development_db: {
                required: true
            },
            project_setup_db: {
                required: true
            },
            /*prod_kingx: {
                required: true
            },*/
        },
        submitHandler: function (form) {
            if ($(form).valid()) {
                ShpSq6fAm7(form);
                form.submit();
            }
            return false; // prevent normal form posting
        }
    });
</script>
</body>
<!-- END BODY-->
</html>
