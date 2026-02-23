<?php
include_once '../common.php';

global $userObj;

require_once TPATH_CLASS . "/Imagecrop.class.php";
$thumb = new thumbnail();

if (!$MODULES_OBJ->mapAPIreplacementAvailable()) {
    header("Location:" . $tconfig["tsite_url_main_admin"]);
}
$sql = "SELECT vCountryCode,vCountry from country where eStatus = 'Active'";
$db_code = $obj->MySQLSelect($sql);
$sql = "select cn.vCountryCode,cn.vCountry,cn.vPhoneCode,cn.vTimeZone from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'";
$db_con = $obj->MySQLSelect($sql);

$vRideCountry = isset($_REQUEST['vRideCountry']) ? $_REQUEST['vRideCountry'] : $db_con[0]['vCountryCode'];
$vTimeZone = isset($_REQUEST['vTimeZone']) ? $_REQUEST['vTimeZone'] : $db_con[0]['vTimeZone'];
$vCountry = $db_con[0]['vCountryCode'];
$search_address = $db_con[0]['vCountry']; // Google HQexit;



$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : ''; // Added By HJ On 12-08-2019 For Edit eEnableDemoLocDispatch Value If QA User as Per Disucss WIth KS
$action = ($id != '') ? 'Edit' : 'Add';
$vSid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : '';
$DbName = TSITE_DB;
$TableName = "auth_accounts_places";
/* Added by HV on 13-05-2021 To restrict addition of accounts if GOOGLE_PLAN_ACCOUNTS_LIMIT reached */
if($action == "Add") {
    if ($vSid != '' || $vSid != 0) {
        if ($vSid != '') {
            $searchQuery1['vServiceId'] = intVal($vSid);
        }

        $data_acc = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery1);
    } else {
        $data_acc = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName);
    }

    $total_accounts = scount($data_acc);
    $lAddOnConfiguration = json_decode($SETUP_INFO_DATA_ARR[0]['lAddOnConfiguration'], true);
    $lAddOnConfiguration['GOOGLE_PLAN'] = 2;
    if(isset($lAddOnConfiguration['GOOGLE_PLAN']) && in_array($lAddOnConfiguration['GOOGLE_PLAN'], [1,2]) && $total_accounts == GOOGLE_PLAN_ACCOUNTS_LIMIT) 
    {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You cannot add any more accounts.';
        header("Location:map_api_mongo_auth_places.php?id=".$vSid);
        exit;
    }
}
/* Added by HV on 13-05-2021 To restrict addition of accounts if GOOGLE_PLAN_ACCOUNTS_LIMIT reached End */

$tbl_name = 'register_driver';
$script = 'map_api_setting';

// set all variables with either post (when submit) either blank (when insert)

if ($vSid != '') {$searchQuery['vServiceId'] = intVal($vSid);
    $usage_order = array();
    $data_by_serviceID = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);
    $serviceIDIntheRecord = $data_by_serviceID[0]['vServiceId'];
    foreach ($data_by_serviceID as $values_by_serviceID_value) {
        $usage_order[] = $values_by_serviceID_value['vUsageOrder'];
    }
    $max_usage_order = max($usage_order) + 1;
}

if ($id != '') {
    $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
    $data_by_serviceID = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);

    $sid_val = $data_by_serviceID[0]['vServiceId'];
    $vUsageOrderVal = $data_by_serviceID[0]['vUsageOrder'];
    $usage_order = array();
    $searchQuerySid['vServiceId'] = intVal($sid_val);
    $data_by_serviceIDnew = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuerySid);

    $totalActiveAccount = [];
    $totalAccounts = 0;
    foreach ($data_by_serviceIDnew as $key => $values_by_serviceID_value) {
        $usage_order[] = $values_by_serviceID_value['vUsageOrder'];
        $totalActiveAccount[$key] = $values_by_serviceID_value['eStatus'];
        $totalAccounts = $totalAccounts + 1;
    }
    $max_usage_order = max($usage_order) + 1;
}

unset($totalActiveAccount[$vUsageOrderVal - 1]);

$requiredServicesAry = array("Active");
$result = array_diff($requiredServicesAry, $totalActiveAccount);

$vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
$vServiceId = isset($_POST['vServiceId']) ? $_POST['vServiceId'] : '';
$vAuthKey = isset($_POST['vAuthKey']) ? $_POST['vAuthKey'] : '';
$vUsageOrder = isset($_POST['vUsageOrder']) ? $_POST['vUsageOrder'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
$EntityType = isset($_REQUEST['EntityType']) ? $_REQUEST['EntityType'] : '';
$alowinact = $_REQUEST['alowinact'];
$alowme = $_REQUEST['alowme'];

if (isset($_POST['submit'])) {
    if ($action == 'Add' && !$userObj->hasPermission('create-map-api-service-account')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:map_api_mongo_auth_places.php");
        exit;
    }

    if ($action == 'Edit' && !$userObj->hasPermission('edit-map-api-service-account')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"];
        header("Location:map_api_mongo_auth_places.php");
        exit;
    }

    // if (!empty($id) && SITE_TYPE == 'Demo') {
    //     $_SESSION['success'] = 2;
    //     header("Location:map_api_mongo_auth_places.php?id=" . $id);
    //     exit;
    // }

     if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:map_api_mongo_auth_places.php?success=2");
        exit;
    }
    require_once "library/validation.class.php";
    $validobj = new validation();
    $validobj->add_fields($_POST['vTitle'], 'req', ' Title is required');
    $validobj->add_fields($_POST['vServiceId'], 'req', 'Service id is required');
    $validobj->add_fields(strtolower($_POST['vAuthKey']), 'req', 'Email Address is required.');
    $validobj->add_fields(strtolower($_POST['vUsageOrder']), 'req', 'Please enter usage order.');
    $validobj->add_fields(strtolower($_POST['eStatus']), 'req', 'Please select status.');

    $error = $validobj->validate();

    /* Added by SP for phone unique validation end */
    /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
    $eSystem = "";

    /* 06-09-219 check phone validation end */

    //Other Validations

    if ($error) {
        $success = 3;
        $newError = $error;
        //exit;
    } else {

        $vRefCodePara = '';

        if ($action == 'Edit') {

            if ($eStatus == "Inactive") {

                if (scount($result) > 0) {

                    $DbName = TSITE_DB;
                    $TableNameAuthMaster = "auth_master_accounts_places";
                    $requiredServicesAry = array("Geocoding", "AutoComplete", "Direction");
                    $activeRecordsResult = $obj->fetchAllCollectionFromMongoDB($DbName, $TableNameAuthMaster);
                    foreach ($activeRecordsResult as $key => $activeRecordsResult) {
                        if ($activeRecordsResult['eStatus'] == "Active") {
                            $AllactiveServices[$key + 1] = $activeRecordsResult['vActiveServices'];
                        }
                    }
                    unset($AllactiveServices[$serviceIDIntheRecord]);
                    $AllactiveServices = array_values($AllactiveServices);
                    for ($i = 0; $i <= scount($AllactiveServices); $i++) {
                        $explodeData = explode(",", $AllactiveServices[$i]);
                        foreach ($explodeData as $Row) {
                            if ($Row != '') {
                                $RowAry[] = $Row;
                            }
                        }
                    }

                    if ($alowme == '') {

                        $resultNew = array_diff($requiredServicesAry, $RowAry);
                        $redirect = $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places.php?id=" . $sid_val;
                        $redirectForthis = $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places_action.php?alowme=Y&id=$id&eStatus=Inactive&action=Edit";
                        $redirectForInactiveService = $tconfig["tsite_url_main_admin"] . "map_api_mongo_auth_places_action.php?alowinact=Y&id=$id&eStatus=Inactive&action=Edit";
                        echo "<script language='JavaScript' type='text/javascript' >
                    function goback(){
                        window.location.href ='$redirect';
                    }
                    var countResult = " . scount($resultNew) . ";
                        if (confirm('Your service will be inactive. Do you like to inactive service?')) {
                            if(countResult > 0){
                                alert('Keep atleast one service active.');
                                window.location.href ='$redirect';
                            }else{
                                window.location.href ='$redirectForthis';
                            }
                            
                    }else{
                        window.location.href ='$redirect';
                    }
                    </script>";
                        exit;
                    }
                }

            }

            // if ($_REQUEST['alowme'] == 'Y') {
            //     $DbName = TSITE_DB;
            //     $TableNameMaster = "auth_master_accounts_places";
            //     $uniqueFieldNameMaster = 'vServiceId';
            //     $uniqueFieldValueMaster = intval($vServiceId);
            //     $tempDataMaster = [];
            //     $tempDataMaster["eStatus"] = $eStatus;
            //     $asdasd = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableNameMaster, $uniqueFieldNameMaster, $uniqueFieldValueMaster, $tempDataMaster);
            // }
            $DbName = TSITE_DB;
            $TableName = "auth_accounts_places";
            $uniqueFieldName = '_id';
            $uniqueFieldValue = trim($id);
            $updatedActiveData = implode(',', $_REQUEST['selectedval']);
            $tempData = [];
            $tempData["vTitle"] = $vTitle;
            $tempData["vServiceId"] = intval($vServiceId);
            $tempData["auth_key"] = $vAuthKey;
            $tempData["EntityType"] = $EntityType;
            $tempData["vUsageOrder"] = intval($vUsageOrder);
            $tempData["eStatus"] = $eStatus;

            $data_drv = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);

            $DbName = TSITE_DB;
            $TableName = "auth_accounts_places";
            $searchQuery = [];
            if ($id != '') {
                $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
            }

            $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);

            if (scount($data_drv) > 0) {
                foreach ($data_drv as $key => $value) {
                    $vTitle = $value['vTitle'];
                    $vServiceId = $value['vServiceId'];
                    $vAuthKey = $value['auth_key'];
                    $vUsageOrder = intval($value['vUsageOrder']);
                    $eStatus = $value['eStatus'];
                }
            }
        }

        if ($action == 'Add') {
            $DbName = TSITE_DB;
            $TableName = "auth_accounts_places";
            $tempData = [];
            $tempData['vTitle'] = $vTitle;
            $tempData['vServiceId'] = intval($vServiceId);
            $tempData['auth_key'] = $vAuthKey;
            // $tempData["EntityType"] = $EntityType;
            $tempData['vUsageOrder'] = intval($vUsageOrder);
            $tempData['eStatus'] = $eStatus;

            $insertedId = $obj->insertRecordsToMongoDBWithDBName($DbName, $TableName, $tempData);
            // header("Location:map_api_mongo_auth_places_action.php?id=" . $insertedId);
            header("Location:map_api_mongo_auth_places.php?id=" . $vServiceId);
            exit;
        }

        if ($action == "Add") {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
        } else {
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        }

        if ($action == "Edit") {
            // header("Location:map_api_mongo_auth_places_action.php?id=" . $id);
            header("Location:map_api_mongo_auth_places.php?id=" . $vServiceId . "");
            exit;
        } else {
            // header("Location:map_api_mongo_auth_places_action.php?id=" . $vSid);
            header("Location:map_api_mongo_auth_places.php?id=" . $vServiceId);
            exit;
        }
    }
}

//for update

if ($_REQUEST['alowme'] == 'Y') {
    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $searchQuery = [];
    if ($id != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
    }

    $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);

    if (scount($data_drv) > 0) {
        foreach ($data_drv as $key => $value) {
            $vTitle = $value['vTitle'];
            $vServiceId = $value['vServiceId'];
            $vAuthKey = $value['auth_key'];
            $vUsageOrder = intval($value['vUsageOrder']);
            // $eStatus = $value['eStatus'];
        }
    }
    
    
    $DbName = TSITE_DB;
    $TableNameMaster = "auth_master_accounts_places";
    $uniqueFieldNameMaster = 'vServiceId';
    $uniqueFieldValueMaster = $vServiceId;
    $tempDataMaster = [];
    $tempDataMaster["eStatus"] = $eStatus;
    $asdasd = $obj->updateRecordsToMongoDBWithDBName($DbName, $TableNameMaster, $uniqueFieldNameMaster, $uniqueFieldValueMaster, $tempDataMaster);

    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $uniqueFieldName = '_id';
    $uniqueFieldValue = trim($id);
    $updatedActiveData = implode(',', $_REQUEST['selectedval']);
    $tempData = [];
    $tempData["vTitle"] = $vTitle;
    $tempData["vServiceId"] = intval($vServiceId);
    $tempData["auth_key"] = $vAuthKey;
    $tempData["vUsageOrder"] = intval($vUsageOrder);
    $tempData["eStatus"] = $eStatus;

    $data_drv = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);

    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $searchQuery = [];
    if ($id != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
    }

    $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);

    if (scount($data_drv) > 0) {
        foreach ($data_drv as $key => $value) {
            $vTitle = $value['vTitle'];
            $vServiceId = $value['vServiceId'];
            // $EntityType = $value['EntityType'];
            
            $vAuthKey = $value['auth_key'];
            $vUsageOrder = intval($value['vUsageOrder']);
            $eStatus = $value['eStatus'];
        }
    }
}
// for Edit

if ($action == 'Edit') {
    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $searchQuery = [];
    if ($id != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($id);
    }

    $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);

    if (scount($data_drv) > 0) {
        foreach ($data_drv as $key => $value) {
            $vTitle = $value['vTitle'];
            // $EntityType = $value['EntityType'];
            $vServiceId = intval($value['vServiceId']);
            $vAuthKey = $value['auth_key'];
            $vUsageOrder = intval($value['vUsageOrder']);
            $eStatus = $value['eStatus'];
        }
    }

    if ($SITE_VERSION == "v5") {
        $sql = "select * from preferences where eStatus ='Active'";
        $data_preference = $obj->MySQLSelect($sql);

        $data_driver_pref = Get_User_Preferences($id);
    }
}

$data_Service_names = $obj->fetchAllRecordsFromMongoDBWithDBName(TSITE_DB, "auth_master_accounts_places", ['vServiceId' => $vSid]);
$Servicetitle = $data_Service_names[0]['vServiceName'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- <meta charset="UTF-8" /> -->
        <title><?=$SITE_NAME?> | Maps API Key <?=$action;?></title>
        <!-- <meta content="width=device-width, initial-scale=1.0" name="viewport" /> -->
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <link rel="stylesheet" href="../assets/css/modal_alert.css" />
        <style type="text/css">
        	.ajax-loader {
			  visibility: visible;
			  background-color: rgba(255,255,255,0.1);
			  position: absolute;
			  z-index: 100 !important;
			  width: 100%;
			  height:300%;
			  top: 0;
			  bottom: 0;
			  left: 0;
			  right: 0;
			  padding:15%;
			}
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?=$action . ' API Key (' . $Servicetitle . ')';?></h2>
                            <a href="map_api_mongo_auth_places.php?id=<?=($vSid) ? $vSid : $vServiceId;?>"  class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 2) {?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php }?>
                            <?php if ($success == 3) {?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php print_r($error);?>
                                </div><br/>
                            <?php }?>
                            <form id="_authmongoplaces_form_new" name="_authmongoplaces_form_new" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="actionOf" id="actionOf" value="<?=$action;?>"/>
                                <input type="hidden" name="id" id="id" value="<?=$id;?>"/>
                                <input type="hidden" name="oldImage" value="<?=$oldImage;?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?=$previousLink;?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="map_api_mongo_auth_places.php"/>
                                <input type="hidden" name="sid" id="sid" value="<?=($vSid != "") ? $vSid : $vServiceId;?>" >
                                <input type="hidden" name="vServiceAccountId" id="vServiceAccountId" value="<?=($vSid != "") ? $vSid : $vServiceId;?>" >
                                <input type="hidden" name="search_address" id="search_address" value="<?=$search_address?>" >
                                <?php if ($id) { ?>
                                    <div class= "row col-md-12" id="hide-profile-div">
                                        <?php $class = ($SITE_VERSION == "v5") ? "col-lg-3" : "col-lg-4";?>
                                        <?php if ($SITE_VERSION == "v5") {?>
                                            <div class="col-lg-4">
                                                <fieldset class="col-md-12 field">
                                                    <legend class="lable"><h4 class="headind1"> Preferences: </h4></legend>
                                                    <p>
                                                    <div class=""> <?php foreach ($data_driver_pref as $val) { ?>
                                                            <img data-toggle="tooltip" class="borderClass-aa1 border_class-bb1" title="<?=$val['pref_Title']?>" src="<?=$tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image']?>">
                                                        <?php } ?>
                                                    </div>
                                                    <span class="col-md-12"><a href="" data-toggle="modal" data-target="#myModal" id="show-edit-language-div" class="hide-language1">
                                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                                            Manage Preferences</a>
                                                    </span>
                                                    </p>
                                                </fieldset>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Title<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vTitle"  id="vTitle" value="<?=$vTitle;?>" placeholder="Title" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Auth Key<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vAuthKey"  id="vAuthKey" value="<?=$vAuthKey;?>" placeholder="Auth Key" >
                                        <label id="vAuthKey-error" style="color:#b94a48;display:block;"></label>
                                        <input type="hidden" class="form-control" onkeypress="onlynumbers(event)" name="vServiceId"  id="vServiceId" value="<?=($vServiceId) ? $vServiceId : $vSid;?>" placeholder="Service Id" >
                                    </div>
                                </div>

                                <!-- <div class="row">
                                    <div class="col-lg-12">
                                        <label>Entity Type<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                       <select class="form-control" name = 'EntityType' id="EntityType" >
                                            <option <?= ($EntityType == '') ? 'selected' : ''; ?> value=''>Please select entity type</option>
                                            <option <?= ($EntityType == 'Guest') ? 'selected' : ''; ?> value='Guest'>Guest</option>
                                            <option <?= ($EntityType == 'Admin') ? 'selected' : ''; ?> value='Admin'>Admin</option>
                                            <option <?= ($EntityType == 'Store') ? 'selected' : ''; ?> value='Store'>Store</option>
                                            <option <?= ($EntityType == 'User') ? 'selected' : ''; ?> value='User'>User</option>
                                            <option <?= ($EntityType == 'Provider') ? 'selected' : ''; ?> value='Provider'>Provider</option>
                                            <option <?= ($EntityType == 'Organization') ? 'selected' : ''; ?> value='Organization'>Organization</option>
                                            <option <?= ($EntityType == 'Hotel') ? 'selected' : ''; ?> value='Hotel'>Hotel</option>
                                       </select>
                                    </div>
                                </div> -->

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Usage Order<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                    <select class="form-control" name = 'vUsageOrder' id="vUsageOrder" >
                                    <?php
										$html = '';
										for ($i = 1; $i <= $max_usage_order; $i++) {
										    if ($action == "Add") {
										        if ($i == $max_usage_order) {
										            $selected = " selected";
										        } else {
										            $selected = " ";
										        }
										    } else {
										        if ($vUsageOrder == $i) {
										            $selected = " selected";
										        } else {
										            $selected = " ";
										        }
										    }
										    $html .= '<option value = "' . $i . '" ' . $selected . '>' . $i . '</option>';
										}
										$html .= '</select>';
										echo $html;
									?>
                                    <label id="vUsageOrder-error" style="color:#b94a48;display:block;"></label>
                                        <!-- <input type="text" class="form-control" onkeypress="onlynumbers(event)" name="vUsageOrder" value="<?=$vUsageOrder;?>"  id="vUsageOrder" placeholder="Usage Order" > -->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="form-control" name = 'eStatus' id="eStatus" >
                                            <?php if ($action == "Add") {
												    $ActiveStatus = " Selected";
												    $InActiveStatus = " ";
												} else {
												    if ($eStatus == "Active") {
												        $ActiveStatus = " Selected";
												        $InActiveStatus = " ";
												    } else if ($eStatus == "Inactive") {
												        $ActiveStatus = " ";
												        $InActiveStatus = " Selected";
												    } else {
												        $ActiveStatus = " ";
												        $InActiveStatus = " ";
												    }
												} ?>
                                            <option value="">Select</option>
                                                <option value = "Active" <?=($ActiveStatus)?> >Active</option>
                                                <option value = "Inactive" <?=($InActiveStatus)?> >Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Add' && $userObj->hasPermission('create-map-api-service-account')) || ($action == 'Edit' && $userObj->hasPermission('edit-map-api-service-account'))) {?>
                                            <input type="submit" class="btn btn-default submits" name="submit" id="submit" value="<?php if ($action == 'Add') {?><?=$action;?> <?php echo $langage_lbl_admin['LBL_MANUAL_STORE_ACCOUNT']; ?><?php } else {?>Update<?php }?>">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php }?>
                                        <!-- <a href="javascript:void(0);" onClick="reset_form('_driver_form');" class="btn btn-default">Reset</a> -->
                                        <a href="map_api_mongo_auth_places.php?id=<?=($vSid) ? $vSid : $vServiceId;?>" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        

        <?php include_once 'footer.php';?>
        <script src="../assets/js/modal_alert.js"></script>
        <script>
            
            function showLoader() {
                var loader = $('<div class="row loding-action" id="imageIcon" style="display:block;z-index: +100 !important;" ><div align="center"><img src="default.gif"><span>Validation is in process. Please Wait...</span></div></div>').appendTo("body");
            }
            function hideLoader() {
                $("#imageIcon").remove();
                $("#imageIcon2").remove();
            }
            $(".submits").click(function(){
                hideLoader();
            });
        </script>

        <script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

        <script>

        var valid = 0;
        function submitform(){
            $('#submit').click();
        }
        $( document ).ready(function() {
            $("#vAuthKey").keypress(function(){
              $("#vAuthKey-error").text('');
            });
            $("#vAuthKey").blur(function(){
              $("#vAuthKey-error").text('');
            });
        });
        function onlynumbers(evt) {
            var theEvent = evt || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
            // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if( !regex.test(key) ) {
                theEvent.returnValue = false;
                if(theEvent.preventDefault) theEvent.preventDefault();
            }
        }
    // Map API Mongo Auth Places start
    // if ($('#_authmongoplaces_form_new').length !== 0) {
        var validator = $('#_authmongoplaces_form_new').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            onkeyup: false,
            onclick: false,
            onfocusout: false,
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
                // hideLoader();
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
                 hideLoader();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
                // hideLoader();
            },
            rules: {
                vTitle: { required: true },
                // vServiceId: {required: true},

                vAuthKey: {
                    required: true,
                    noSpace: true,
                    async: false,
                    remote: {
                        url: _system_admin_url + 'ajax_validate_auth_key.php',
                        type: "post",
                        data: {
                            vAuthKey: function () {
                                return $("#vAuthKey").val();
                            }, vServiceAccountId: function () {
                                return $("#vServiceAccountId").val();
                            }, id: function () {
                                return $("#id").val();
                            }, search_address: function () {
                                return $("#search_address").val();
                            }
                            // ,
                            // EntityType:function(){
                            //     return $("#EntityType").val();
                            // }
                        },
                        beforeSend : function(xhr) {
                            mO4u1yc3dx(xhr);
                        },
                        dataFilter: function (response) {
                            // console.log(response);
                            hideLoader();
                            responseArr = JSON.parse(response);
                            if (responseArr.Action == "1") {
                                $('#vAuthKey-error').html('');
                                return true;
                            } else {
                                hideLoader();

                                return "\"" + responseArr.message + "\"";
                                return false;
                            }
                        },

                    }
                },
                vUsageOrder: {
                    required: true,

                    remote: {
                        url: _system_admin_url + 'ajax_validate_usage_order.php',
                        type: "post",
                         // async: false,
                        data: {
                            usageOrder: function () {
                                return $("#vUsageOrder").val();
                            }, sid: function () {
                                return $("#sid").val();
                            }, id: function () {
                                return $("#id").val();
                            }
                        },
                        beforeSend : function(xhr) {
                            mO4u1yc3dx(xhr);
                        },
                        dataFilter: function (response) {
                            if (response > 0) {
                                errormessage = "Usage order is assigned, please select different.";
                                hideLoader();
                                return false;
                            } else {
                                return true;
                            }
                        }

                    }
                },
                // EntityType : {required: true},
                eStatus: { required: true }
            },
            messages: {
                vTitle: {
                    required: 'This field is required.'
                },
                // EntityType: {
                //     required: 'This field is required.'
                // },
                vServiceId: {
                    required: 'This field is required.'
                },
                vAuthKey: {
                    required: 'This field is required.',
                    noSpace: 'Auth key should not contain whitespace.',
                    remote: jQuery.validator.format('{0}')
                },
                vUsageOrder: {
                    required: 'This field is required.',
                    remote: 'Usage order is assigned, please select different.'
                },
                eStatus: {
                    required: 'This field is required.'
                }
            },
            submitHandler : function(form) {
                hideLoader();
                if ($(form).valid()){

                    valid = 1;
                    submitform();
                    return true;
                }else{
                    valid = 0;
                };
                hideLoader();
                return false;
            }
        });
    // }

        $('#submit').click(function() {
            showLoader();
            if (valid == 0) {
                validator.resetForm();
            }
            $("label.error, #vAuthKey-error").hide();
            $(".error").removeClass("error");
            $(".has-error").removeClass("has-error");
        });
        </script>
    </body>
    <!-- END BODY-->
</html>
