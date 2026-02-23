<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
if (!$userObj->hasPermission('edit-documents')) {
    $userObj->redirect();
}
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id = $id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'document_master';
$script = 'Document Master';
$sql_vehicle_category_table_name = getVehicleCategoryTblName();
$doc_usertype = isset($_POST['doc_type']) ? $_POST['doc_type'] : '';
$doc_country1 = isset($_POST['country']) ? $_POST['country'] : '';
$Document_type = isset($_POST['Document_type']) ? $_POST['Document_type'] : '';
$exp = isset($_POST['exp']) ? $_POST['exp'] : '';
$eType = isset($_POST['eType']) ? $_POST['eType'] : 'Ride';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$iVehicleCategoryId = isset($_POST['iVehicleCategoryId']) ? $_POST['iVehicleCategoryId'] : '';
$iBiddingId = isset($_POST['iBiddingId']) ? $_POST['iBiddingId'] : '';
$eDocServiceType = isset($_POST['eDocServiceType']) ? $_POST['eDocServiceType'] : 'General';
if ($eDocServiceType == "BiddingSpecific") {
    $iVehicleCategoryId = '';
} else {
    $iBiddingId = '';
}
/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT max(iDisplayOrder) AS iDisplayOrder FROM document_master where 1 = 1 GROUP BY doc_usertype");
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder_max = $iDisplayOrder + 1; // Maximum order number
$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$vTitle_store = array();
$sql = "SELECT vCode,vTitle,eDefault FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0) {

    for ($i = 0; $i < $count_all; $i++) {

        $vValue = 'doc_name_' . $db_master[$i]['vCode'];
        array_push($vTitle_store, $vValue);
        $$vValue = isset($_POST[$vValue]) ? $_POST[$vValue] : '';
    }
}
if (isset($_POST['btnsubmit'])) {

    if ($action == "Add" && !$userObj->hasPermission('create-documents')) {

        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Document.';
        header("Location:document_master_list.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-documents')) {

        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Document.';
        header("Location:document_master_list.php");
        exit;
    }
    $sql1 = "SELECT vCountry FROM country where iCountryId='" . $doc_country1 . "'";
    $data_contry = $obj->MySQLSelect($sql1);
    $doc_country = $data_contry[0]['vCountry'];
    $Document_type = $_POST['doc_name_' . $default_lang];
    if ($eFareType == "Fixed") {

        $ePickStatus = "Inactive";
        $eNightStatus = "Inactive";
    } else {

        $ePickStatus = $ePickStatus;
        $eNightStatus = $eNightStatus;
    }
    if ($eNightStatus == "Active") {

        if ($tNightStartTime > $tNightEndTime) {

            header("Location:vehicle_type_action.php?id=" . $id . "&success=4");
            exit;
        }
    }
    if (SITE_TYPE == 'Demo') {

        header("Location:document_master_add.php?id=" . $id . "&success=2");
        exit;
    }
    for ($i = 0; $i < scount($vTitle_store); $i++) {

        $vValue = 'doc_name_' . $db_master[$i]['vCode'];
        // echo $_POST[$vTitle_store[$i]] ; exit;
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {

            $q = "UPDATE ";
            $where = " WHERE `doc_masterid` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET             

            `doc_usertype` = '" . $doc_usertype . "',

            `doc_name` = '" . $Document_type . "' ,

            `country` = '" . $doc_country1 . "',

            `ex_status` = '" . $exp . "',

            `eDocServiceType` = '" . $eDocServiceType . "', 

            `iVehicleCategoryId` = '" . $iVehicleCategoryId . "', 
             `iBiddingId` = '" . $iBiddingId . "', 
            `iDisplayOrder` = '" . $iDisplayOrder . "',

            " . $vValue . " = '" . $_POST[$vTitle_store[$i]] . "'"
            . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    $_SESSION['success'] = '1';
    if ($action == "Edit") {

        $msg = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    } else {

        $msg = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }
    $_SESSION['var_msg'] = $msg;
    // $obj->sql_query($query);
    header("Location:" . $backlink);
    exit;
    // header("Location:document_master_list.php");
}
// for Edit
if ($action == 'Edit') {

    $sql = "SELECT * FROM " . $tbl_name . " WHERE doc_masterid = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {

        for ($i = 0; $i < scount($db_master); $i++) {

            foreach ($db_data as $key => $value) {

                $vValue = 'doc_name_' . $db_master[$i]['vCode'];
                $$vValue = $value[$vValue];
                $doc_usertype = $value['doc_usertype'];
                $doc_country = $value['country'];
                $doc_name = $value['doc_name'];
                $exp = $value['ex_status'];
                $iDisplayOrder_db = $value['iDisplayOrder'];
                //$eType = $value['eDocServiceType'];
                $iVehicleCategoryId = $value['iVehicleCategoryId'];
                $iBiddingId = $value['iBiddingId'];
                $eDocServiceType = $value['eDocServiceType'];
                $arrLang[$vValue] = $$vValue;
            }
        }
    }
}


if($parent_ufx_catid > 0){
    $sql = "iParentId = $parent_ufx_catid";
}else{
    $sql = "iParentId = 0";
}
$sql_cat = "SELECT iVehicleCategoryId,vCategory_$default_lang,vCategory_EN FROM " . $sql_vehicle_category_table_name . " WHERE $sql AND eStatus='Active' AND eCatType='ServiceProvider'";
$db_catdata = $obj->MySQLSelect($sql_cat);

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
$vLang = $LANG_OBJ->FetchDefaultLangData("vCode");
if ($MODULES_OBJ->isEnableBiddingWiseProviderDoc()) { 
    $bidding_cat = $BIDDING_OBJ->getBiddingMaster('webservice', '', '', '', $vLang);
}
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
    <meta charset="UTF-8"/>
    <title>Admin | <?php echo $langage_lbl_admin['LBL_DOCUMENT_TYPE']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php
    include_once('global_files.php');
    ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        .doc-service-label {
            font-weight: normal;
            cursor: pointer;
        }

        .doc-service-label:first-child {
            margin-right: 20px;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php
    include_once('header.php');
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2> <?php echo $langage_lbl_admin['LBL_DOCUMENT_TYPE']; ?> </h2>
                    <a href="javascript:void(0);" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                    <!--                         <a href="document_master_list.php">
                        <input type="button" value="Back to Listing" class="add-btn">

                        </a> -->
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                    <?php } elseif ($success == 2) { ?>
                        <div class="alert alert-danger alert-dismissable ">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                    <?php } elseif ($success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <?php echo $_REQUEST['varmsg']; ?>
                        </div>
                        <br/>
                    <?php } elseif ($success == 4) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            "Please Select Night Start Time less than Night End Time."
                        </div>
                        <br/>
                    <?php } ?>
                    <?php if (!empty($_REQUEST['var_msg'])) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Record Not Updated .
                        </div>
                        <br/>
                    <?php } ?>
                    <form id="_document_master" method="post" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id; ?>"/>
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="document_master_list.php"/>
                        <?php if (!empty($MODULES_OBJ->isOnlyEnableRideSharingPro())) { ?>
                            <input type="hidden" name="doc_type" id="doc_type" value="user"/>
                        <?php } else {?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Document For <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" name='doc_type' id="doc_type" required
                                            onChange="changeDisplayOrder(this.value, '<?php echo $id; ?>');">
                                        <?php if ($APP_TYPE != "UberX") { ?>
                                            <option value="car" <?php if ($doc_usertype == "car") echo 'selected="selected"'; ?> >
                                                <?= $langage_lbl_admin['LBL_Vehicle'] ?>
                                            </option>
                                        <?php } ?>
                                        <?php if (ONLYDELIVERALL == "No") { ?>
                                            <option value="company"<?php if ($doc_usertype == "company") echo 'selected="selected"'; ?>>
                                                <?= $langage_lbl_admin['LBL_COMPANY_SIGNIN'] ?>
                                            </option>
                                        <?php } ?>
                                        <option value="driver"<?php if ($doc_usertype == "driver") echo 'selected="selected"'; ?>><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                        <?php if (DELIVERALL == "Yes") { ?>
                                            <option value="store" <?php if ($doc_usertype == "store") echo 'selected="selected"'; ?>>
                                                <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] ?>
                                            </option>
                                        <?php } if ($MODULES_OBJ->isEnableRideShareService()) { ?>
                                            <option value="user" <?php if ($doc_usertype == "user") echo 'selected="selected"'; ?>>
                                                <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?> (Ride Sharing)
                                            </option>
                                        <?php } if ($MODULES_OBJ->isEnableTrackServiceFeature()) { ?>
                                            <option value="trackcompany" <?php if ($doc_usertype == "trackcompany") echo 'selected="selected"'; ?>>
                                                Tracking Company
                                            </option>
                                        <?php } if ($MODULES_OBJ->isEnableParkingFeature()) { ?>
                                            <option value="parking" <?php if ($doc_usertype == "parking") echo 'selected="selected"'; ?>>
                                                <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?> (Parking)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>
                        <?php
                        if ($ENABLE_SERVICE_TYPE_WISE_PROVIDER_DOC == 'Yes') {

                            if ($MODULES_OBJ->isEnableServiceTypeWiseProviderDocument() == "Yes") { ?>
                                <?php /*
                                    <div class="row" id="servicetype">
                                    
                                        <div class="col-lg-12">
                                    
                                            <label>Service Type <span class="red">*</span></label>
                                    
                                        </div>
                                    
                                        <div class="col-md-6 col-sm-6">
                                    
                                            <select  class="form-control" name = 'eType' required id='etypedelivery'>
                                    
                                                <option value="Ride" <?php if ($eType == "Ride") echo 'selected="selected"'; ?> >Ride</option>
                                    
                                                <option value="Delivery"<?php if ($eType == "Delivery") echo 'selected="selected"'; ?>>Delivery</option>
                                    
                                                <option value="ServiceProvider" <?php if ($eType == "ServiceProvider") echo 'selected="selected"'; ?> id="servicetype-uberx" >Other Services</option>
                                    
                                            </select>
                                        </div>
                                        </div>
                                    */ ?>
                                <div class="row" id="servicetype">
                                    <div class="col-lg-12">
                                        <label>Document Type <span class="red">*</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <label class="doc-service-label" for="eDocServiceTypeG">
                                            <input type="radio" name="eDocServiceType" id="eDocServiceTypeG"
                                                   value="General" <?php if ($eDocServiceType == "General") echo 'checked="checked"'; ?> >
                                            General Document
                                        </label>
                                        <label class="doc-service-label" for="eDocServiceTypeS">
                                            <input type="radio" name="eDocServiceType" id="eDocServiceTypeS"
                                                   value="ServiceSpecific" <?php if ($eDocServiceType == "ServiceSpecific") echo 'checked="checked"'; ?> >
                                            Service Specific Document
                                        </label>
                                        <?php if ($MODULES_OBJ->isEnableBiddingWiseProviderDoc()) { ?>
                                            <label class="doc-service-label" for="eDocServiceTypeh">
                                                <input type="radio" name="eDocServiceType" id="eDocServiceTypeh"
                                                       value="BiddingSpecific" <?php if ($eDocServiceType == "BiddingSpecific") echo 'checked="checked"'; ?> >
                                                Bidding Service Specific Document
                                            </label>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row" id="otherservice">
                                <div class="col-lg-12">
                                    <label>Service Category <span class="red">*</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" name='iVehicleCategoryId' required>
                                        <option value="">Select Service Category</option>
                                        <?php foreach ($db_catdata as $key_cat => $val_cat) {
                                            ?>
                                            <option value="<?= $val_cat['iVehicleCategoryId'] ?>" <?php if ($iVehicleCategoryId == $val_cat['iVehicleCategoryId']) echo 'selected="selected"'; ?> ><?php echo $val_cat['vCategory_' . $default_lang] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($MODULES_OBJ->isEnableBiddingWiseProviderDoc()) { ?>

                            <div class="row" id="biddingService">
                                <div class="col-lg-12">
                                    <label>Bidding Service Category <span class="red">*</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <select class="form-control" id="iBiddingId" name='iBiddingId' required>
                                        <option value="">Select Bidding Category</option>
                                        <?php foreach ($bidding_cat as $key_cat => $val_cat) {
                                            ?>
                                            <option value="<?= $val_cat['iBiddingId'] ?>" <?php if ($iBiddingId == $val_cat['iBiddingId']) echo 'selected="selected"'; ?> ><?php echo $val_cat['vCategory'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Country <span class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select id="country" class="form-control" name='country' required>
                                    <option value="All">All Country</option>
                                    <?php
                                    // country
                                    $sql = "SELECT iCountryId,vCountry,vCountryCode FROM country WHERE eStatus='Active' ORDER BY iCountryId ASC";
                                    $db_data1 = $obj->MySQLSelect($sql);
                                    foreach ($db_data1 as $value) {

                                        ?>
                                        <option <?php if (isset($db_data[0]['country']) && $db_data[0]['country'] == $value['vCountryCode']) {
                                            echo 'selected';
                                        } ?> value="<?php echo $value['vCountryCode']; ?>"><?php echo $value['vCountry']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Expire On Date <span class="red"> *</span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='Yes option will ask for Date'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="radio" name="exp" id="exp"
                                       value="yes" <?php if ($exp == "yes") echo 'checked="checked"'; ?> required> Yes
                                <input type="radio" name="exp" id="exp"
                                       value="no" <?php if ($exp == "no") echo 'checked="checked"'; ?> required> No
                            </div>
                        </div>
                        <div class="row" style="display: none;">
                            <div class="col-lg-12">
                                <label>Document Name <span class="red"> *</span>
                                    <i class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                       data-original-title='Name of Document for admin use. e.g. Insurance, Driving Licence... etc'></i>
                                </label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="Document_type" id="Document_type"
                                       value="<?= $doc_name; ?>">
                            </div>
                        </div>
                        <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_DOCUMENT_TYPE'] ?> <span class="red"> *</span> <i
                                                class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                                data-original-title='Name of Document as per language. e.g. Insurance, Driving Licence... etc'></i></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ? 'readonly-custom' : '' ?>"
                                           id="doc_name_Default" name="doc_name_Default"
                                           value="<?= isset($arrLang['doc_name_' . $default_lang]) ? $arrLang['doc_name_' . $default_lang] : ''; ?>"
                                           data-originalvalue="<?= isset($arrLang['doc_name_' . $default_lang]) ? $arrLang['doc_name_' . $default_lang] : ''; ?>"
                                           readonly="readonly" <?php if ($id == "") { ?> onclick="editDocumentName('Add')" <?php } ?>>
                                </div>
                                <?php if (!empty($id)) { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editDocumentName('Edit')"><span
                                                    class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="modal fade" id="document_name_Modal" tabindex="-1" role="dialog"
                                 aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_DOCUMENT_TYPE'] ?>
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'doc_name_')">x
                                                </button>
                                            </h4>
                                        </div>
                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0; $i < $count_all; $i++) {
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'doc_name_' . $vCode;
                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                $vCodeDefault = $default_lang;
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        $vCodeDefault = 'EN';
                                                    } else {
                                                        $vCodeDefault = $default_lang;
                                                    }
                                                }
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (scount($db_master) > 1) {
                                                    if ($EN_available) {
                                                        if ($vCode == "EN") {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    } else {
                                                        if ($vCode == $default_lang) {
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    }
                                                }
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label><?= $langage_lbl_admin['LBL_DOCUMENT_TYPE'] ?>
                                                            (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                    </div>
                                                    <div class="<?= $page_title_class ?>">
                                                        <input type="text" class="form-control" name="<?= $vValue; ?>"
                                                               id="<?= $vValue; ?>" value="<?= $$vValue; ?>"
                                                               data-originalvalue="<?= $$vValue; ?>"
                                                               placeholder="<?= $vTitle; ?> Value">
                                                        <div class="text-danger" id="<?= $vValue . '_error'; ?>"
                                                             style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1) {
                                                        if ($EN_available) {
                                                            if ($vCode == "EN") { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('doc_name_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        } else {
                                                            if ($vCode == $default_lang) { ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('doc_name_', '<?= $default_lang ?>');">
                                                                        Convert To All Language
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
                                        <div class="modal-footer" style="margin-top: 0">
                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?= $langage_lbl['LBL_NOTE']; ?>
                                                    : </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveDocumentName()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'doc_name_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                            </div>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?= $langage_lbl_admin['LBL_DOCUMENT_TYPE'] ?> <span class="red"> *</span> <i
                                                class="icon-question-sign" data-placement="top" data-toggle="tooltip"
                                                data-original-title='Name of Document as per language. e.g. Insurance, Driving Licence... etc'></i></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="doc_name_<?= $default_lang ?>"
                                           id="doc_name_<?= $default_lang ?>"
                                           value="<?= $arrLang['doc_name_' . $default_lang]; ?>" required>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Display Order</label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <!--  <input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $iDisplayOrder_max : '1'; ?>">
                                        <?php $display_numbers = $iDisplayOrder_max; ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                <option value="<?= $i ?>" <?php
                                    if ($i == $iDisplayOrder_db) {
                                        echo "selected";
                                    }
                                    ?>> -- <?= $i ?> --</option>
                                                    <?php } ?>
                                        </select> -->
                                <span id="showDisplayOrder001">
                                        <?php if ($action == 'Add') { ?>
                                            <input type="hidden" name="temp_order" id="temp_order"
                                                   value="<?= ($action == 'Edit') ? $iDisplayOrder_max : '1'; ?>">
                                        <?php $display_numbers = $iDisplayOrder_max; ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                <option value="<?= $i ?>" <?php
                                                if (isset($iDisplayOrder_db) && $i == $iDisplayOrder_db) {
                                                    echo "selected";
                                                }
                                                ?>> -- <?= $i ?> --</option>
                                            <?php } ?>
                                        </select>
                                        <?php } else { ?>
                                            <input type="hidden" name="temp_order" id="temp_order"
                                                   value="<?= ($action == 'Edit') ? $iDisplayOrder_max : '1'; ?>">
                                        <?php $display_numbers = $iDisplayOrder_max; ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                <option value="<?= $i ?>" <?php
                                                if (isset($iDisplayOrder_db) &&$i == $iDisplayOrder_db) {
                                                    echo "selected";
                                                }
                                                ?>> -- <?= $i ?> --</option>
                                            <?php } ?>
                                        </select>
                                        <?php } ?>
                                    </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-documents')) || ($action == 'Add' && $userObj->hasPermission('create-documents'))) { ?>
                                    <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit"
                                           value="<?= $action . " " . $langage_lbl_admin['LBL_DOCUMENT_TYPE']; ?>">
                                <?php } ?>
                                <a href="document_master_list.php" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    $(document).ready(function () {
        var doc_usertype = $('#doc_type option:selected').val();
        if(doc_usertype == "" || typeof doc_usertype == 'undefined'){
            var doc_usertype = $('#doc_type').val();
        }
        changeDisplayOrder(doc_usertype, '<?php echo $id; ?>');
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "document_master_list.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
    $('[data-toggle="tooltip"]').tooltip();
    var successMSG1 = '<?php echo $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }
    /*if ($("#doc_type option:selected").val() == 'car') {

        $("#servicetype-uberx").hide();

    } else {

        $("#servicetype-uberx").show();

    }



    if ($("#doc_type option:selected").val() == 'company') {

        $("#servicetype").hide();

    } else {

        $("#servicetype").show();

    }*/
    if ($("#doc_type option:selected").val() == 'driver') {
        $("#servicetype").show();
    } else {
        $("#servicetype").hide();
    }
    $('#doc_type').on('change', function (e) {
        var valueSelected = this.value;
        /*if (valueSelected == 'company') {

            $("#servicetype").hide();

            $("#servicetype-uberx").show();

            $("#otherservice").hide();

        } else if (valueSelected == 'car') {

            $("#servicetype-uberx").hide();

            $("#servicetype").show();

            $("#otherservice").hide();

        } else {

            $("#servicetype").show();

            $("#servicetype-uberx").show();

        }*/
        if (valueSelected == 'driver') {
            $("#servicetype").show();
            $("#servicetype-uberx").show();
            // ("#otherservice").hide();
        } else {
            $("#servicetype").hide();
            $("#servicetype-uberx").hide();
            $("#otherservice").hide();
            $('#iVehicleCategoryId').attr('required', false);
            $('#iBiddingId').attr('required', false);
        }
        if (valueSelected == 'driver' && $('input[name="eDocServiceType"]:checked').val() == "ServiceSpecific") {
            $("#otherservice").show();
        } else {
            $("#otherservice").hide();
        }
        if (valueSelected == 'driver' && $('input[name="eDocServiceType"]:checked').val() == "BiddingSpecific") {
            $("#biddingService").show();
        } else {
            $("#biddingService").hide();
        }
    });
    /*
    $("#otherservice").hide();
    $('#iVehicleCategoryId').attr('required', false);
    if ($("#etypedelivery option:selected").val() == 'ServiceProvider') {

        $("#otherservice").show();
        $("#otherservice").prop('required',true);
        $('#iVehicleCategoryId').attr('required', true);

    }

    $('#etypedelivery').on('change', function (e) {

        var valueSelected = this.value;

        if (valueSelected == 'ServiceProvider') {

            $("#otherservice").show();
            $("#otherservice").prop('required',true);
            $('#iVehicleCategoryId').attr('required', true);

        } else {

            $("#otherservice").hide();
            $('#iVehicleCategoryId').attr('required', false);
        }

    });*/
    $("#otherservice").hide();
    $("#biddingService").hide();
    $('#iBiddingId').attr('required', false);
    $('#iVehicleCategoryId').attr('required', false);
    if ($('input[name=eDocServiceType]:checked').val() == 'ServiceSpecific') {
        $('#iVehicleCategoryId').attr('required', true);
        $("#otherservice").show();
    }
    if ($('input[name=eDocServiceType]:checked').val() == 'BiddingSpecific') {
        $('#iBiddingId').attr('required', true);
        $("#biddingService").show();
    }
    $('input[name="eDocServiceType"]').click(function () {
        $('#iVehicleCategoryId').attr('required', false);
        $('#iBiddingId').attr('required', false);
        var getDocumentType = $(this).val();
        if (getDocumentType == 'ServiceSpecific') {
            $('#iVehicleCategoryId').attr('required', true);
            $("#otherservice").show();
        } else {
            $("#otherservice").hide();
        }
        if (getDocumentType == 'BiddingSpecific') {
            $('#iBiddingId').attr('required', true);
            $("#biddingService").show();
        } else {
            $("#biddingService").hide();
        }
    });

    function editDocumentName(action) {
        $('#modal_action').html(action);
        $('#document_name_Modal').modal('show');
    }

    function saveDocumentName() {
        if ($('#doc_name_<?= $default_lang ?>').val() == "") {
            $('#doc_name_<?= $default_lang ?>_error').show();
            $('#doc_name_<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#doc_name_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#doc_name_Default').val($('#doc_name_<?= $default_lang ?>').val());
        $('#doc_name_Default').closest('.row').removeClass('has-error');
        $('#doc_name_Default-error').remove();
        $('#document_name_Modal').modal('hide');
    }

    function changeDisplayOrder(doc_type, id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_display_order_for_document.php',
            'AJAX_DATA': {doc_type: doc_type, id: id},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $("#showDisplayOrder001").html('');
                $("#showDisplayOrder001").html(data);
            }
            else {
                console.log(response.result);
            }
        });
    }

    /*------------------for field verification-----------------*/
    var DEFAULT_LANG = "<?= $default_lang ?>";
    var default_lang_field = $("#doc_name_"+DEFAULT_LANG);
    var default_lang_field_error = $("#doc_name_"+DEFAULT_LANG+'_error');
    default_lang_field.on("keyup  change", function() {

        if(default_lang_field.val() === "")
        {
            default_lang_field_error.show();
            default_lang_field.focus();
        }else{
            default_lang_field_error.hide();
        }

    });
    /*------------------for field verification-----------------*/
</script>
</body>
<!-- END BODY-->
</html>