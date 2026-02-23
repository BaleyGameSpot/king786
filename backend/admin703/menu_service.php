<?php
include_once('../common.php');
if (!$userObj->hasPermission('manage-our-service-menu')){
    $userObj->redirect();
}
$script = 'masterServiceMenu';
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$ord = ' ORDER BY msm.iDisplayOrder = 0,msm.iDisplayOrder ASC';
//End Sorting
$rdr_ssql = $ssql = "";
// Start Search Parameters
$option = isset($_REQUEST['option'])?stripslashes($_REQUEST['option']):"";
$keyword = isset($_REQUEST['keyword'])?stripslashes($_REQUEST['keyword']):"";
$searchDate = isset($_REQUEST['searchDate'])?$_REQUEST['searchDate']:"";
$eStatus = isset($_REQUEST['eStatus'])?$_REQUEST['eStatus']:"";
$eType = isset($_REQUEST['eType'])?$_REQUEST['eType']:"";
$id = isset($_REQUEST['id'])?$_REQUEST['id']:"";
$menuid = isset($_REQUEST['menuid'])?$_REQUEST['menuid']:"";
if (isset($menuid) && !empty($menuid)){
    $id = $menuid;
}
$ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable()?"Yes":"No"; //add function to modules availibility
if ($ufxEnable != 'Yes'){
    $ssql .= " AND eCatType!='ServiceProvider'";
}
if (!$MODULES_OBJ->isAirFlightModuleAvailable(1)){
    $ssql .= " AND eCatType != 'Fly'";
}
if (!$MODULES_OBJ->isDonationFeatureAvailable()){
    $ssql .= " AND eCatType != 'Donation'";
}
if (!$MODULES_OBJ->isRideFeatureAvailable()){
    $ssql .= " AND eCatType != 'Ride' AND eCatType != 'MotoRide' AND eCatType != 'Rental' AND eCatType != 'MotoRental'";
}
if (!$MODULES_OBJ->isDeliveryFeatureAvailable()){
    $ssql .= " AND eCatType != 'Delivery' AND eCatType != 'MultipleDelivery' AND eCatType != 'MotoDelivery' AND eCatType != 'MoreDelivery'";
}
if (!$MODULES_OBJ->isDeliverAllFeatureAvailable()){
    $ssql .= " AND eCatType != 'DeliverAll'";
}
if (!$MODULES_OBJ->isEnableAnywhereDeliveryFeature()){
    $ssql .= " AND eCatType!='Genie' AND eCatType!='Runner' AND eCatType!='Anywhere'";
}
if (!$MODULES_OBJ->isEnableRentItemService()){
    $ssql .= " AND eCatType != 'RentItem'";
}
if (!$MODULES_OBJ->isEnableTrackAnyServiceFeature()){
    $ssql .= " AND eCatType != 'TrackAnyService'";
}
if (!$MODULES_OBJ->isEnableRideShareService()){
    $ssql .= " AND eCatType != 'RideShare'";
}
if (!$MODULES_OBJ->isEnableRentEstateService()){
    $ssql .= " AND eCatType != 'RentEstate'";
}
if (!$MODULES_OBJ->isEnableRentCarsService()){
    $ssql .= " AND eCatType != 'RentCars'";
}
if (!$MODULES_OBJ->isEnableNearByService()){
    $ssql .= " AND eCatType != 'NearBy'";
}
$MasterServiceCategory = "";
if (!empty($eType)){
    $master_service_category = $obj->MySQLSelect("SELECT JSON_UNQUOTE(JSON_VALUE(vCategoryName, '$.vCategoryName_".$default_lang."')) as vCategoryName FROM master_service_category WHERE eType = '$eType' ");
    $MasterServiceCategory = "(".$master_service_category[0]['vCategoryName'].")";
    $ssql = getMasterServiceCategoryQuery($eType,'',$menu = "Yes");
    if ($eType == "VideoConsult" && $MODULES_OBJ->isEnableVideoConsultingService()){
        $vc_data = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT(iParentId)) as ParentIds FROM vehicle_category WHERE eVideoConsultEnable = 'Yes'");
        $ssql .= " AND iVehicleCategoryId IN (".$vc_data[0]['ParentIds'].")";
    }elseif ($eType == "MedicalServices"){
        $ssql .= " AND eForMedicalService = 'Yes' ";
    }
}
//$ssql .= $ssqlSearch;
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
//Added By HJ On 14-11-2019 For Manage Service Category Row As Per Design Start
$calSize = 3;
for ($f = $per_page;$f < ($per_page + $calSize);$f++){
    $checkZero = $f / $calSize;
    $checkZero = is_numeric($checkZero) && floor($checkZero) != $checkZero;
    if (empty($checkZero)){
        $per_page = $f;
        break;
    }
}
//Added By HJ On 14-11-2019 For Manage Service Category Row As Per Design End
if ($eStatus != ''){
    $estatusquery = "";
}else{
    $estatusquery = " AND eStatus = 'Active'";
}
$not_sql = " AND iVehicleCategoryId != 297";
$parent_id_sql = " AND vc.iParentId='0' ";
if ($eType == "Ride"){
    $ssql .= " AND eForMedicalService = 'No' ";
}elseif (in_array($eType,['UberX','VideoConsult']) && $MODULES_OBJ->isEnableMedicalServices('Yes')){
    $ssql .= " AND vc.iVehicleCategoryId NOT IN (3,22,26,158) ";
}elseif ($eType == "DeliverAll" && $MODULES_OBJ->isEnableMedicalServices('Yes')){
    $ssql .= " AND vc.iServiceId NOT IN (5, 11) ";
}elseif ($eType == "MedicalServices"){
    $parent_id_sql = " AND (vc.iParentId='0' OR vc.iParentId = '3') ";
}
$sql = "SELECT COUNT(vc.iVehicleCategoryId) AS Total FROM ".$sql_vehicle_category_table_name." as vc  WHERE  1 = 1 AND vc.iVehicleCategoryId NOT IN (185) $parent_id_sql $estatusquery $ssql $rdr_ssql $not_sql";
// echo $sql;die;
$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = 100;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])){
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages){
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page'])?intval($_GET['page']):0;
$tpages = $total_pages;
if ($page <= 0){
    $page = 1;
}
//Pagination End
$parent_id_sql = " AND vc.iParentId='0' ";
if ($eType == "MedicalServices"){
    $parent_id_sql = " AND (vc.iParentId='0' OR vc.iParentId = '3') ";
}
$sql = "SELECT msm.iDisplayOrder as msmiDisplayOrder ,JSON_UNQUOTE(JSON_VALUE(msm.vTitle, '$.vTitle_EN')) as Title,msm.vTitle,vc.eCatType,vc.iVehicleCategoryId,vc.vBannerImage, vc.vLogo,vc.vListLogo1,vc.vListLogo2,vc.vCategory_".$default_lang." as vCategory, vc.eStatus, vc.iDisplayOrder,vc.eCatType, vc.eVideoConsultEnable,vc.tMedicalServiceInfo,  (select count(iVehicleCategoryId) from ".$sql_vehicle_category_table_name." where iParentId = vc.iVehicleCategoryId AND eStatus != 'Deleted') as SubCategories FROM ".$sql_vehicle_category_table_name." as vc  LEFT JOIN master_service_menu as msm ON msm.iServiceId  = vc.iVehicleCategoryId AND msm.iParentId=$id WHERE vc.iVehicleCategoryId NOT IN (185) AND vc.eStatus = 'Active'  $parent_id_sql $ssql $rdr_ssql $not_sql $ord LIMIT $start, $per_page";
//echo $sql;exit;
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val){
    if ($key != "tpages" && $key != 'page'){
        $var_filter .= "&$key=".stripslashes($val);
    }
}
$reload = $_SERVER['PHP_SELF']."?tpages=".$tpages.$var_filter;
$ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable(); // Added By HJ On 28-11-2019 For Check UberX Service Status
$eServiceType = !empty($eType)?'&eServiceType='.$eType:'';
$sql_1 = "SELECT iServiceMenuId,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_".$default_lang."')) as vTitle FROM `master_service_menu` WHERE eType = '".$eType."'";
$master_service_menu_ = $obj->MySQLSelect($sql_1);
$MasterServiceCategory = "(".$master_service_menu_[0]['vTitle'].")";
$id = $master_service_menu_[0]['iServiceMenuId'];
$sql_1 = "SELECT * FROM `master_service_menu` WHERE eStatus != 'Inactive' AND iParentId = ".$id;
$master_service_menu = $obj->MySQLSelect($sql_1);
$iServiceId = [];
foreach ($master_service_menu as $key => $a){
    $iServiceId[$key] = $a['iServiceId'];
}
if (!empty($eType) && $eType == "Bidding"){
    $page = 0;
    $total_results = 0;
    $total_pages = 0;
    $endRecord = 0;
    $reload = 0;
    $show_page = 0;
    $total_pages = 0;
    $getBiddingMaster = $BIDDING_OBJ->getBiddingMaster('admin');
    $total_results = scount($getBiddingMaster);
    $master_service_menu = [];
    if (isset($getBiddingMaster) && !empty($getBiddingMaster)){
        $i = 0;
        foreach ($getBiddingMaster as $bidding){
            $sql_1 = "SELECT *,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_EN')) as Title,vTitle FROM `master_service_menu` WHERE iServiceId = ".$bidding['iBiddingId']." AND iParentId = ".$id;
            $data = $obj->MySQLSelect($sql_1);
            $master_service_menu[$i]['vCategory'] = $bidding['vTitle'];
            $master_service_menu[$i]['iVehicleCategoryId'] = $bidding['iBiddingId'];
            $master_service_menu[$i]['eCatType'] = 'bidding';
            $master_service_menu[$i]['vLogo'] = '';
            $master_service_menu[$i]['vListLogo1'] = '';
            $master_service_menu[$i]['vListLogo2'] = '';
            $master_service_menu[$i]['vBannerImage'] = '';
            $master_service_menu[$i]['eStatus'] = $bidding['eStatus'];
            $master_service_menu[$i]['msmiDisplayOrder'] = $data[0]['iDisplayOrder'];
            $master_service_menu[$i]['Title'] = $data[0]['Title'];
            $master_service_menu[$i]['vTitle'] = $data[0]['vTitle'];
            $i++;
        }
    }
    $data_drv = $master_service_menu;
}

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
if (SITE_TYPE == 'Demo'){
    $_SESSION['success'] = "2";
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->

<head>
    <meta charset="UTF-8"/>
    <title><?=$SITE_NAME?> | <?=$langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN'];?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <style type="text/css">
        .medical-service-title {
            padding: 10px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 5px;
            margin: 0;
            width: 100%;
        }

        hr.medical-service-line {
            border: 1px solid;
            width: calc(100% - 20px);
            margin: 0 0 20px 10px;
        }

        .medical-service-note {
            margin-top: 10px;
        }
    </style>
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
            <div id="add-hide-show-div" class="vehicleCategorylist">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?=$langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN'];?> <?=$MasterServiceCategory?></h2>
                    </div>
                </div>
                <hr/>
            </div>

            <?php include('valid_msg.php'); ?>

            <?php if ($eType != "MedicalServices"){ ?>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="admin-nir-export vehicle-cat">
                                <div class="table-responsive1">
                                    <div class="table table-striped  table-hover">
                                        <div class="profile-earning">
                                            <div class="partation">
                                                <ul style="padding-left: 0px;" class="setings-list">
                                                    <?php if (!empty($data_drv)) {
                                                    for ($i = 0;$i < scount($data_drv);$i++){

                                                        $buttonStatus = $data_drv[$i]['eStatus'];
                                                        $btnChecked = 0;
                                                        if ($data_drv[$i]['eStatus'] == "Active"){
                                                            $btnChecked = 1;
                                                        }
                                                        $btnChecked = 0;
                                                        $buttonStatus = "Inactive";
                                                        if (in_array($data_drv[$i]['iVehicleCategoryId'],$iServiceId)){
                                                            $btnChecked = 1;
                                                            $buttonStatus = "Active";
                                                        }
                                                        if (!empty($data_drv[$i]['vTitle'])) {
                                                            $vLevel = json_decode($data_drv[$i]['vTitle'],true);
                                                        }
                                                        ?>
                                                        <li>
                                                            <form class="_list_form<?php echo $data_drv[$i]['iVehicleCategoryId'] ?>"
                                                                    id="_list_form<?php echo $data_drv[$i]['iVehicleCategoryId'] ?>"
                                                                    method="post" action="<?=$_SERVER['PHP_SELF']?>">
                                                                <input type="hidden" name="menuid" value="<?=$id;?>"
                                                                        id="menuid">
                                                                <input type="hidden" name="iVehicleCategoryId"
                                                                        value="<?=$data_drv[$i]['iVehicleCategoryId'];?>"
                                                                        id="iVehicleCategoryId">

                                                                <div class="toggle-list-inner">
                                                                    <div class="toggle-combo">
                                                                        <label>
                                                                            <div align="center">
                                                                                <!-- <img src="<?=$logoPath;?>" style="width:100px;"> -->
                                                                            </div>
                                                                            <div style="margin: 0 0 0 10px;">
                                                                                <td><?=$data_drv[$i]['vCategory'];?> </td>
                                                                            </div>
                                                                        </label>

                                                                        <span class="toggle-switch">
                                                                            <input type="checkbox"
                                                                                <?php if ($btnChecked > 0) { ?>checked="" <?php } ?>
                                                                                   onClick="changeMenuStatus('<?=$id;?>','<?=$data_drv[$i]['iVehicleCategoryId'];?>', '<?=$buttonStatus;?>')"
                                                                                    id="statusbutton" class="chk"
                                                                                    name="statusbutton" value="246">
                                                                            <span class="toggle-base"></span>
                                                                        </span>

                                                                    </div>
                                                                    <div class="">
                                                                        <div class="form-group">
                                                                            <label class="col-sm-12">Name<span
                                                                                        class="red"> *</span></label>
                                                                            <div class="input-group col-sm-12">
                                                                                <input class="form-control" type="text"
                                                                                        name="vLevel"
                                                                                        id="vTitle_<?=isset($data_drv[$i]['iVehicleCategoryId'])?$data_drv[$i]['iVehicleCategoryId']:''?>Default"
                                                                                        id="$('#vTitle_'+id+'<?=$default_lang?>'"
                                                                                        value="<?=isset($vLevel['vTitle_'.$default_lang])?$vLevel['vTitle_'.$default_lang]:'';?>"
                                                                                        placeholder="Name"
                                                                                        onclick="editDescription('Edit' , <?=isset($data_drv[$i]['iVehicleCategoryId'])?$data_drv[$i]['iVehicleCategoryId']:'';?>)"/>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label class="col-sm-12">Display
                                                                                Order</label>
                                                                            <div class="input-group col-sm-12">
                                                                                <select name="iDisplayOrder"
                                                                                        class="form-control">
                                                                                    <?php for ($j = 1;$j <= $total_results;$j++){ ?>
                                                                                        <option value="<?=$j?>" <?=$data_drv[$i]['msmiDisplayOrder'] == $j?"selected":""?>>
                                                                                            <?=$j?></option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                            </div>
                                                                            <input type="hidden" name="oldDisplayOrder"
                                                                                    id="oldDisplayOrder"
                                                                                    value="<?=$data_drv[$i]['msmiDisplayOrder'];?>">
                                                                        </div>

                                                                        <div class="form-group"
                                                                                style="padding-bottom: 15px">
                                                                            <div class="input-group col-sm-12">
                                                                                <input attr-formid='<?=$data_drv[$i]['iVehicleCategoryId'];?>'
                                                                                        type="submit" name="save"
                                                                                        value="Save"
                                                                                        class="btn btn-default">
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal fade"
                                                                                id="coupon_desc_Modal<?=isset($data_drv[$i]['iVehicleCategoryId'])?$data_drv[$i]['iVehicleCategoryId']:'';?>"
                                                                                tabindex="-1" role="dialog"
                                                                                aria-hidden="true" data-backdrop="static"
                                                                                data-keyboard="false">
                                                                            <div class="modal-dialog modal-lg">
                                                                                <div class="modal-content nimot-class">
                                                                                    <div class="modal-header">
                                                                                        <h4>
                                                                                            <span id="modal_action"></span>
                                                                                            Name
                                                                                            <button type="button"
                                                                                                    class="close"
                                                                                                    data-dismiss="modal"
                                                                                                    onclick="resetToOriginalValue(this, 'vTitle_')">
                                                                                                x
                                                                                            </button>
                                                                                        </h4>
                                                                                    </div>
                                                                                    <div class="modal-body">

                                                                                        <?php
                                                                                        for ($d = 0;$d < $count_all;$d++){
                                                                                            $vCode = $db_master[$d]['vCode'];
                                                                                            $vTitle = $db_master[$d]['vTitle'];
                                                                                            $eDefault = $db_master[$d]['eDefault'];
                                                                                            $descVal = 'vTitle_'.$data_drv[$i]['iVehicleCategoryId'].$vCode;
                                                                                            $descVal_ = 'vTitle_'.$vCode;
                                                                                            $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
                                                                                            ?>
                                                                                            <?php
                                                                                            $page_title_class = 'col-lg-12';
                                                                                            if (scount($db_master) > 1){
                                                                                                if ($EN_available){
                                                                                                    if ($vCode == "EN"){
                                                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                                                    }
                                                                                                }else{
                                                                                                    if ($vCode == $default_lang){
                                                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                            <div class="form-group row">
                                                                                                <div class="col-lg-12">
                                                                                                    <label>Name
                                                                                                        (<?=$vTitle;?>
                                                                                                        ) <?php echo $required_msg; ?></label>
                                                                                                </div>
                                                                                                <div class="<?=$page_title_class?>">
                                                                                                    <input type="text"
                                                                                                            name="<?=$descVal;?>"
                                                                                                            class="form-control"
                                                                                                            id="<?=$descVal;?>"
                                                                                                            placeholder="<?=$vTitle;?> Value"
                                                                                                            data-originalvalue="<?=$vLevel[$descVal_];?>"
                                                                                                            value="<?=$vLevel[$descVal_];?>">
                                                                                                    <div class="text-danger"
                                                                                                            id="<?=$descVal.'_error';?>"
                                                                                                            style="display: none;"><?=$langage_lbl_admin['LBL_REQUIRED']?></div>
                                                                                                </div>
                                                                                                <?php
                                                                                                if (scount($db_master) > 1){
                                                                                                    if ($EN_available){
                                                                                                        if ($vCode == "EN"){ ?>
                                                                                                            <div class="col-md-3 col-sm-3">
                                                                                                                <button type="button"
                                                                                                                        name="allLanguage"
                                                                                                                        id="allLanguage"
                                                                                                                        class="btn btn-primary"
                                                                                                                        onClick="getAllLanguageCode('vTitle_<?=isset($data_drv[$i]['iVehicleCategoryId'])?$data_drv[$i]['iVehicleCategoryId']:'';?>', '<?=$default_lang?>');">
                                                                                                                    Convert
                                                                                                                    To
                                                                                                                    All
                                                                                                                    Language
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        <?php }
                                                                                                    }else{
                                                                                                        if ($vCode == $default_lang){ ?>
                                                                                                            <div class="col-md-3 col-sm-3">
                                                                                                                <button type="button"
                                                                                                                        name="allLanguage"
                                                                                                                        id="allLanguage"
                                                                                                                        class="btn btn-primary"
                                                                                                                        onClick="getAllLanguageCode('vTitle_<?=isset($data_drv[$i]['iVehicleCategoryId'])?$data_drv[$i]['iVehicleCategoryId']:'';?>', '<?=$default_lang?>');">
                                                                                                                    Convert
                                                                                                                    To
                                                                                                                    All
                                                                                                                    Language
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        <?php }
                                                                                                    }
                                                                                                }
                                                                                                ?>
                                                                                            </div>
                                                                                            <?php
                                                                                        }
                                                                                        ?>
                                                                                    </div>
                                                                                    <div class="modal-footer"
                                                                                            style="margin-top: 0">
                                                                                        <h5 class="text-left"
                                                                                                style="margin-bottom: 15px; margin-top: 0;">
                                                                                            <strong><?=$langage_lbl['LBL_NOTE'];?>
                                                                                                : </strong><?=$langage_lbl['LBL_SAVE_INFO'];?>
                                                                                        </h5>
                                                                                        <div class="nimot-class-but"
                                                                                                style="margin-bottom: 0">
                                                                                            <button type="button"
                                                                                                    class="save"
                                                                                                    style="margin-left: 0 !important"
                                                                                                    onclick="saveDescription(<?=isset($data_drv[$i]['iVehicleCategoryId'])?$data_drv[$i]['iVehicleCategoryId']:'';?>)"><?=$langage_lbl['LBL_Save'];?></button>
                                                                                            <button type="button"
                                                                                                    class="btn btn-danger btn-ok"
                                                                                                    data-dismiss="modal"
                                                                                                    onclick="resetToOriginalValue(this, 'vTitle_')"><?=$langage_lbl['LBL_CANCEL_TXT'];?></button>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div style="clear:both;"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div style="display:none" class="check-combo">
                                                                        <label id="defaultText_246">
                                                                            <ul>

                                                                                <li class="entypo-twitter"
                                                                                        data-network="twitter"><a
                                                                                            href="vehicle_category_action.php?id=<?=$data_drv[$i]['iVehicleCategoryId'].$eServiceType;?>"
                                                                                            data-toggle="tooltip"
                                                                                            title="Edit">
                                                                                        <img src="img/edit-new.png"
                                                                                                alt="Edit">
                                                                                    </a></li>

                                                                            </ul>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div><?php
                                        }else{
                                            ?>
                                            <tr class="gradeA">
                                                <td colspan="8"> No Records Found.</td>
                                            </tr>
                                        <?php } ?>
                                    </div>
                                </div>
                                <!-- </form> -->
                                <?php include('pagination_n.php'); ?>

                            </div>
                        </div>
                        <!--TABLE-END-->
                    </div>
                </div>
            <?php }else{
                $OnDemandServicesArr = $VideoConsultServicesArr = $MoreServicesArr = array();
                if (!empty($data_drv)){
                    foreach ($data_drv as $med_service){
                        if (!empty($med_service['tMedicalServiceInfo'])){
                            $tMedicalServiceInfoArr = json_decode($med_service['tMedicalServiceInfo'],true);
                            if ($tMedicalServiceInfoArr['BookService'] == "Yes"){
                                $med_service['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderBS'];
                                $OnDemandServicesArr[] = $med_service;
                            }
                            if ($med_service['eVideoConsultEnable'] == "Yes" && $tMedicalServiceInfoArr['VideoConsult'] == "Yes"){
                                $med_service['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderVC'];
                                $VideoConsultServicesArr[] = $med_service;
                            }
                            if ($tMedicalServiceInfoArr['MoreService'] == "Yes"){
                                $med_service['ms_display_order'] = $tMedicalServiceInfoArr['iDisplayOrderMS'];
                                $MoreServicesArr[] = $med_service;
                            }
                        }
                    }
                    $ms_display_order = array_column($OnDemandServicesArr,'ms_display_order');
                    array_multisort($ms_display_order,SORT_ASC,$OnDemandServicesArr);
                    $ms_display_order = array_column($VideoConsultServicesArr,'ms_display_order');
                    array_multisort($ms_display_order,SORT_ASC,$VideoConsultServicesArr);
                    $ms_display_order = array_column($MoreServicesArr,'ms_display_order');
                    array_multisort($ms_display_order,SORT_ASC,$MoreServicesArr);
                }
                $MEDICAL_SERVICES_ARR = array(array('ServiceTitle' => $langage_lbl_admin['LBL_ON_DEMAND_MEDICAL_SERVICES_TITLE'],
                                                    'ServicesArr'  => $OnDemandServicesArr,),
                                              array('ServiceTitle' => $langage_lbl_admin['LBL_VIDEO_CONSULT_MEDICAL_SERVICES_TITLE'],
                                                    'ServicesArr'  => $VideoConsultServicesArr),
                                              array('ServiceTitle' => $langage_lbl_admin['LBL_MEDICAL_MORE_SERVICES_TITLE'],
                                                    'ServicesArr'  => $MoreServicesArr));
                ?>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="admin-nir-export vehicle-cat">
                                <div class="table-responsive1">
                                    <div class="table table-striped  table-hover">
                                        <div class="profile-earning">
                                            <?php foreach ($MEDICAL_SERVICES_ARR as $MEDICAL_SERVICE){ ?>
                                                <div class="partation">
                                                    <div class="medical-service-title">
                                                        <span><?=$MEDICAL_SERVICE['ServiceTitle']?></span>
                                                    </div>
                                                    <hr class="medical-service-line"/>
                                                    <ul style="padding-left: 0px;" class="setings-list">

                                                        <?php if (!empty($MEDICAL_SERVICE['ServicesArr'])){

                                                            foreach ($MEDICAL_SERVICE['ServicesArr'] as $MedService){

                                                                $buttonStatus = $MedService['eStatus'];
                                                                $btnChecked = 0;
                                                                if ($MedService['eStatus'] == "Active"){
                                                                    $btnChecked = 1;
                                                                }
                                                                $btnChecked = 0;
                                                                $buttonStatus = "Inactive";
                                                                if (in_array($MedService['iVehicleCategoryId'],$iServiceId)){
                                                                    $btnChecked = 1;
                                                                    $buttonStatus = "Active";
                                                                }
                                                                $vLevel = json_decode($MedService['vTitle'],true);
                                                                ?>
                                                                <li>
                                                                    <form class="_list_form<?php echo $MedService['iVehicleCategoryId'] ?>"
                                                                            id="_list_form<?php echo $MedService['iVehicleCategoryId'] ?>"
                                                                            method="post"
                                                                            action="<?=$_SERVER['PHP_SELF']?>">
                                                                        <input type="hidden" name="menuid"
                                                                                value="<?=$id;?>" id="menuid">
                                                                        <input type="hidden" name="iVehicleCategoryId"
                                                                                value="<?=$MedService['iVehicleCategoryId'];?>"
                                                                                id="iVehicleCategoryId">

                                                                        <input type="hidden" name="eType"
                                                                                value="<?=$eType;?>"
                                                                                id="eType">
                                                                        <div class="toggle-list-inner">
                                                                            <div class="toggle-combo">
                                                                                <label>
                                                                                    <div style="margin: 0 0 0 10px;">
                                                                                        <td><?=$MedService['vCategory'];?> </td>
                                                                                    </div>
                                                                                </label>

                                                                                <span class="toggle-switch">
                                                                            <input type="checkbox"
                                                                                <?php if ($btnChecked > 0) { ?>checked="" <?php } ?>
                                                                                   onClick="changeMenuStatus('<?=$id;?>','<?=$MedService['iVehicleCategoryId'];?>', '<?=$buttonStatus;?>')"
                                                                                    id="statusbutton" class="chk"
                                                                                    name="statusbutton" value="246">
                                                                            <span class="toggle-base"></span>
                                                                        </span>
                                                                            </div>
                                                                            <div class="">
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-12">Name<span
                                                                                                class="red"> *</span></label>
                                                                                    <div class="input-group col-sm-12">
                                                                                        <input class="form-control"
                                                                                                type="text" name="vLevel"
                                                                                                id="vTitle_<?=isset($MedService['iVehicleCategoryId'])?$MedService['iVehicleCategoryId']:''?>Default"
                                                                                                id="$('#vTitle_'+id+'<?=$default_lang?>'"
                                                                                                value="<?=isset($vLevel['vTitle_'.$default_lang])?$vLevel['vTitle_'.$default_lang]:'';?>"
                                                                                                placeholder="Name"
                                                                                                onclick="editDescription('Edit' , <?=isset($MedService['iVehicleCategoryId'])?$MedService['iVehicleCategoryId']:'';?>)"/>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                    <label class="col-sm-12">Display
                                                                                        Order</label>
                                                                                    <div class="input-group col-sm-12">
                                                                                        <select name="iDisplayOrder"
                                                                                                class="form-control">
                                                                                            <?php for ($j = 1;$j <= $total_results;$j++){ ?>
                                                                                                <option value="<?=$j?>" <?=$MedService['msmiDisplayOrder'] == $j?"selected":""?>>
                                                                                                    <?=$j?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                    <input type="hidden"
                                                                                            name="oldDisplayOrder"
                                                                                            id="oldDisplayOrder"
                                                                                            value="<?=$MedService['msmiDisplayOrder'];?>">
                                                                                </div>

                                                                                <div class="form-group"
                                                                                        style="padding-bottom: 15px">
                                                                                    <div class="input-group col-sm-12">
                                                                                        <input attr-formid='<?=$MedService['iVehicleCategoryId'];?>'
                                                                                                type="submit" name="save"
                                                                                                value="Save"
                                                                                                class="btn btn-default">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="modal fade"
                                                                                        id="coupon_desc_Modal<?=isset($MedService['iVehicleCategoryId'])?$MedService['iVehicleCategoryId']:'';?>"
                                                                                        tabindex="-1" role="dialog"
                                                                                        aria-hidden="true"
                                                                                        data-backdrop="static"
                                                                                        data-keyboard="false">
                                                                                    <div class="modal-dialog modal-lg">
                                                                                        <div class="modal-content nimot-class">
                                                                                            <div class="modal-header">
                                                                                                <h4>
                                                                                                    <span id="modal_action"></span>
                                                                                                    Name
                                                                                                    <button type="button"
                                                                                                            class="close"
                                                                                                            data-dismiss="modal"
                                                                                                            onclick="resetToOriginalValue(this, 'vTitle_')">
                                                                                                        x
                                                                                                    </button>
                                                                                                </h4>
                                                                                            </div>
                                                                                            <div class="modal-body">

                                                                                                <?php
                                                                                                for ($d = 0;$d < $count_all;$d++){
                                                                                                    $vCode = $db_master[$d]['vCode'];
                                                                                                    $vTitle = $db_master[$d]['vTitle'];
                                                                                                    $eDefault = $db_master[$d]['eDefault'];
                                                                                                    $descVal = 'vTitle_'.$MedService['iVehicleCategoryId'].$vCode;
                                                                                                    $descVal_ = 'vTitle_'.$vCode;
                                                                                                    $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
                                                                                                    ?>
                                                                                                    <?php
                                                                                                    $page_title_class = 'col-lg-12';
                                                                                                    if (scount($db_master) > 1){
                                                                                                        if ($EN_available){
                                                                                                            if ($vCode == "EN"){
                                                                                                                $page_title_class = 'col-md-9 col-sm-9';
                                                                                                            }
                                                                                                        }else{
                                                                                                            if ($vCode == $default_lang){
                                                                                                                $page_title_class = 'col-md-9 col-sm-9';
                                                                                                            }
                                                                                                        }
                                                                                                    }
                                                                                                    ?>
                                                                                                    <div class="form-group row">
                                                                                                        <div class="col-lg-12">
                                                                                                            <label>Name
                                                                                                                (<?=$vTitle;?>
                                                                                                                ) <?php echo $required_msg; ?></label>
                                                                                                        </div>
                                                                                                        <div class="<?=$page_title_class?>">
                                                                                                            <input type="text"
                                                                                                                    name="<?=$descVal;?>"
                                                                                                                    class="form-control"
                                                                                                                    id="<?=$descVal;?>"
                                                                                                                    placeholder="<?=$vTitle;?> Value"
                                                                                                                    data-originalvalue="<?=$vLevel[$descVal_];?>"
                                                                                                                    value="<?=$vLevel[$descVal_];?>">
                                                                                                            <div class="text-danger"
                                                                                                                    id="<?=$descVal.'_error';?>"
                                                                                                                    style="display: none;"><?=$langage_lbl_admin['LBL_REQUIRED']?></div>
                                                                                                        </div>
                                                                                                        <?php
                                                                                                        if (scount($db_master) > 1){
                                                                                                            if ($EN_available){
                                                                                                                if ($vCode == "EN"){ ?>
                                                                                                                    <div class="col-md-3 col-sm-3">
                                                                                                                        <button type="button"
                                                                                                                                name="allLanguage"
                                                                                                                                id="allLanguage"
                                                                                                                                class="btn btn-primary"
                                                                                                                                onClick="getAllLanguageCode('vTitle_<?=isset($MedService['iVehicleCategoryId'])?$MedService['iVehicleCategoryId']:'';?>', '<?=$default_lang?>');">
                                                                                                                            Convert
                                                                                                                            To
                                                                                                                            All
                                                                                                                            Language
                                                                                                                        </button>
                                                                                                                    </div>
                                                                                                                <?php }
                                                                                                            }else{
                                                                                                                if ($vCode == $default_lang){ ?>
                                                                                                                    <div class="col-md-3 col-sm-3">
                                                                                                                        <button type="button"
                                                                                                                                name="allLanguage"
                                                                                                                                id="allLanguage"
                                                                                                                                class="btn btn-primary"
                                                                                                                                onClick="getAllLanguageCode('vTitle_<?=isset($MedService['iVehicleCategoryId'])?$MedService['iVehicleCategoryId']:'';?>', '<?=$default_lang?>');">
                                                                                                                            Convert
                                                                                                                            To
                                                                                                                            All
                                                                                                                            Language
                                                                                                                        </button>
                                                                                                                    </div>
                                                                                                                <?php }
                                                                                                            }
                                                                                                        }
                                                                                                        ?>
                                                                                                    </div>
                                                                                                    <?php
                                                                                                }
                                                                                                ?>
                                                                                            </div>
                                                                                            <div class="modal-footer"
                                                                                                    style="margin-top: 0">
                                                                                                <h5 class="text-left"
                                                                                                        style="margin-bottom: 15px; margin-top: 0;">
                                                                                                    <strong><?=$langage_lbl['LBL_NOTE'];?>
                                                                                                        : </strong><?=$langage_lbl['LBL_SAVE_INFO'];?>
                                                                                                </h5>
                                                                                                <div class="nimot-class-but"
                                                                                                        style="margin-bottom: 0">
                                                                                                    <button type="button"
                                                                                                            class="save"
                                                                                                            style="margin-left: 0 !important"
                                                                                                            onclick="saveDescription(<?=isset($MedService['iVehicleCategoryId'])?$MedService['iVehicleCategoryId']:'';?>)"><?=$langage_lbl['LBL_Save'];?></button>
                                                                                                    <button type="button"
                                                                                                            class="btn btn-danger btn-ok"
                                                                                                            data-dismiss="modal"
                                                                                                            onclick="resetToOriginalValue(this, 'vTitle_')"><?=$langage_lbl['LBL_CANCEL_TXT'];?></button>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div style="clear:both;"></div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </li>
                                                            <?php }
                                                        } ?>
                                                    </ul>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- </form> -->
                            </div>
                        </div>
                        <!--TABLE-END-->
                    </div>
                </div>
            <?php } ?>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>Manage "Our Services" menu content and display order for home page.</li>
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<form name="pageForm" id="pageForm" action="action/menu_service.php" method="post">
    <input type="hidden" name="page" id="page" value="<?=$page;?>">
    <input type="hidden" name="tpages" id="tpages" value="<?=$tpages;?>">
    <input type="hidden" name="menuid" id="menuid" value="<?=$id;?>">
    <input type="hidden" name="iVehicleCategoryId" id="iMainId01" value="">
    <input type="hidden" name="eStatus" id="eStatus" value="<?=$eStatus;?>">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?=$option;?>">
    <input type="hidden" name="keyword" value="<?=$keyword;?>">
    <input type="hidden" name="sortby" id="sortby" value="<?=$sortby;?>">
    <input type="hidden" name="order" id="order" value="<?=$order;?>">
    <input type="hidden" name="method" id="method" value="">
    <input type="hidden" name="eType" value="<?=$eType?>">
</form>
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<?php include_once('footer.php'); ?>
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
        //alert(action);
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

    function saveMenuText(id) {
        var formListData = $("#_list_form" + id).serialize() + '&ajax=1';
        var ajaxData = {
            'URL': _system_admin_url + "action/menu_service.php",
            'AJAX_DATA': formListData,
        };
        $('#loaderIcon').find('span').hide();
        $('#loaderIcon').show();
        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').find('span').show();
            $('#loaderIcon').hide();
            if (response.action == "1") {
                location.reload();
            } else {
                alert("Something went wrong.");
            }
        });
    }

    function editDescription(action, id) {
        $('#modal_action').html(action);
        $('#coupon_desc_Modal' + id).modal('show');
    }

    function saveDescription(id) {
        if ($('#vTitle_' + id + '<?= $default_lang ?>').val() == "") {
            $('#vTitle_' + id + '<?= $default_lang ?>_error').show();
            $('#vTitle_' + id + '<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#vTitle_' + id + '<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vTitle_' + id + 'Default').val($('#vTitle_' + id + '<?= $default_lang ?>').val());
        $('#vTitle_' + id + 'Default').closest('.row').removeClass('has-error');
        $('#vTitle_' + id + 'Default-error').remove();
        $('#coupon_desc_Modal' + id).modal('hide');
    }
</script>
</body>
<!-- END BODY-->

</html>