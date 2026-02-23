<?php
/*
 * File Type : PHP
 * File Created On  : 19-06-2020
 * File Created By : HJ
 * Purpose : For Upload Item CSV file as Bulk Upload
 */
include_once('../common.php');

if (!$userObj->hasPermission('manage-import-bulk-items')) {
    $userObj->redirect();
}
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;

$script = "ImportItem";
$step = 1;
$uploadedFile = "";
$serviceCatIds = getCurrentActiveServiceCategoriesIds();
$getServiceData = $obj->MySQLSelect("SELECT iServiceId,vServiceName_".$default_lang." AS serviceName,prescription_required FROM service_categories WHERE iServiceId IN ($serviceCatIds);");

$ServiceDataArr = array();
foreach ($getServiceData as $ServiceData) {
    $ServiceDataArr['SERVICE_CAT_' . $ServiceData['iServiceId']] = $ServiceData;
}

$ssql1 = "";
if($MODULES_OBJ->isEnableAnywhereDeliveryFeature())
{
    $ssql = " AND eBuyAnyService = 'No' ";
}

$getStoreData = $obj->MySQLSelect("SELECT iCompanyId,iServiceId,vCompany,iServiceIdMulti FROM company WHERE iServiceId > 0 OR iServiceIdMulti > 0 AND eStatus!='Deleted' $ssql");
$storeArr = array();
for($h=0;$h<scount($getStoreData);$h++){
    if($getStoreData[$h]['iServiceIdMulti'] != ""){
        $iServiceIdMulti = $getStoreData[$h]['iServiceIdMulti'];
        $iseviceid = explode(",", $iServiceIdMulti);
        foreach ($iseviceid as $k => $val) {
            $storeArr[$val][]= $getStoreData[$h];
        }
    } else {
    $storeArr[$getStoreData[$h]['iServiceId']][]= $getStoreData[$h];
    }
}
$errorMsg = "";
if (isset($_POST['comparedb'])) {

    if (SITE_TYPE == 'Demo') {
        header("Location:import_item_data.php?id=" . $id . "&success=2");
        exit;
    }
    //echo "<pre>";print_r($_FILES);die;
    if (isset($_FILES["uploadFile"])) {
        $uploadFile = uploadImage($_FILES["uploadFile"]);
        //echo "<pre>";print_r($uploadFile);die;
        if (isset($uploadFile['fileName']) && $uploadFile['fileName'] != "") {
            $uploadedFile = $uploadFile['fileName'];
            $fileextension = end(explode('.', $uploadedFile));
            if(strtoupper($fileextension) == "CSV"){
                $step = 2;
            }else{
                $imgUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                header("Location:import_item_data.php?error=".$imgUploadingExtenstionMsg);
                die;
            }
            
        }
        //echo "<pre>";print_r($tableDataArr1);die;
    } else {
        $imgUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        header("Location:import_item_data.php?error=".$imgUploadingExtenstionMsg);
        die;
    }
}

$adminUrl = $tconfig['tsite_url_main_admin'];
$siteUrl = $tconfig['tsite_url'];

if (isset($_REQUEST['export']) && strtoupper($_REQUEST['export']) == "CSV") {
    $service_id = $_REQUEST['service_id'];
    $langData = $obj->MySQLSelect("SELECT * FROM language_master WHERE eStatus='Active'");
    $date = new DateTime();
    $timestamp_filename = $date->getTimestamp();
    $filename = "item_".$timestamp_filename . ".csv";
    $fp = fopen('php://output', 'w');
    // fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

    $sampleItem = array();
    $menu_items_demo = $obj->MySQLSelect("SELECT menu_items.*,food_menu.vMenu_$default_lang FROM menu_items INNER JOIN food_menu ON menu_items.iFoodMenuId = food_menu.iFoodMenuId INNER JOIN company ON food_menu.iCompanyId = company.iCompanyId WHERE company.iServiceId = '$service_id' AND vItemType_$default_lang != '' ORDER BY RAND() LIMIT 5");    

    $c = 1;
    foreach ($menu_items_demo as $menu_item_demo) {
        $sampleItemArr = array(
            "SR"                => $c,
            "SKU"               => $menu_item_demo['vSKU'],
            "ITEM_NAME"         => $menu_item_demo['vItemType_'.$default_lang],
            "ITEM_DESC"         => $menu_item_demo['vItemDesc_'.$default_lang],
            "ITEM_CATEGORY"     => $menu_item_demo['vMenu_'.$default_lang],
            "IMAGE_URL"         => $siteUrl."webimages/upload/MenuItem/".$menu_item_demo['vImage'],
            "ITEM_PRICE"        => $menu_item_demo['fPrice'],
            "ITEM_TYPE"         => $menu_item_demo['eFoodType'],
            "OFFER_PER"         => $menu_item_demo['fOfferAmt'],
            "IS_AVAILABLE"      => $menu_item_demo['eAvailable'],
            "IS_RECOMMENDED"    => $menu_item_demo['eRecommended'],
            "IS_ACTIVE"         => $menu_item_demo['eStatus'] == "Active" ? "Yes" : "No",
            "DISPLAY_ORDER"     => $c
        );

        if (ENABLE_PRESCRIPTION_UPLOAD == "Yes" && $service_id == "5") {
            $sampleItemArr['PRESCRIPTION_REQUIRED'] = $menu_item_demo['prescription_required'];
        }

        $sampleItem[] = $sampleItemArr;

        $c++;
    }
    for($m=0;$m<scount($sampleItem);$m++){
        $itemName = $sampleItem[$m]['ITEM_NAME'];
        $itemDesc = $sampleItem[$m]['ITEM_DESC'];
        $itemCategory = $sampleItem[$m]['ITEM_CATEGORY'];
        for($l=0;$l<scount($langData);$l++){
            $sampleItem[$m]['ITEM_NAME_'.$langData[$l]['vCode']] = $itemName;
            $sampleItem[$m]['ITEM_DESC_'.$langData[$l]['vCode']] = $itemDesc;
            $sampleItem[$m]['ITEM_CATEGORY_'.$langData[$l]['vCode']] = $itemCategory;
        }
    }
    $header = array("SR","SKU","ITEM_NAME");
    for($l=0;$l<scount($langData);$l++){
        $header[] = "ITEM_NAME_".$langData[$l]['vCode'];
    }
    $header[] = "ITEM_DESC";
    for($l=0;$l<scount($langData);$l++){
        $header[] = "ITEM_DESC_".$langData[$l]['vCode'];
    }
    $header[] = "ITEM_CATEGORY";
    for($l=0;$l<scount($langData);$l++){
        $header[] = "ITEM_CATEGORY_".$langData[$l]['vCode'];
    }
    
    $otherHeader = array("IMAGE_URL","ITEM_PRICE","ITEM_TYPE","OFFER_PER","IS_AVAILABLE","IS_RECOMMENDED","IS_BEST_SELLER","IS_ACTIVE","DISPLAY_ORDER");
    if (ENABLE_PRESCRIPTION_UPLOAD == "Yes" && $service_id == "5") {
        $otherHeader[] = 'PRESCRIPTION_REQUIRED';
    }

    $trimHeader = array();
    $finalHeader = array_merge($header, $otherHeader);
    for($n=0;$n<scount($finalHeader);$n++){
        $trimHeader[] = $finalHeader[$n];
    }
    //echo "<pre>";print_r($trimHeader);die;
    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename='.$filename);
    fputcsv($fp, $trimHeader);
    
    $itemDataArr = array();
    for($b=0;$b<scount($sampleItem);$b++){
        $newItemArr = array();
        foreach($trimHeader as $key=>$val){
            $newItemArr[] = $sampleItem[$b][$val];
        }
        fputcsv($fp, $newItemArr);
    }
    //header("Location:import_item_data.php");
    exit;
}
$errorMsg = "";
if(isset($_REQUEST['error']) && trim($_REQUEST['error']) != ""){
    $errorMsg = trim($_REQUEST['error']);
}
function uploadImage($attachment, $time = "") {
    global $tconfig;

    $fileextension = end(explode('.', $attachment['name']));
    if(strtoupper($fileextension) == "CSV"){
        $step = 2;
    }else{
        $imgUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        // header("Location:import_item_data.php?error=Please upload only CSV file");
        header("Location:import_item_data.php?error=".$imgUploadingExtenstionMsg);
        die;
    }

    if ($time == '') {
        $time = date("Ymd");
    }
    $attachmentSize = $attachment['size'];
    $attachmentName = $attachment['name'];
    $imageType = $attachment['type'];
    $imageTempName = $attachment['tmp_name'];
    $filename = time().".".$fileextension;
    //$uploadPath = "attachment/";
    //echo "<pre>";print_r($tconfig);die;
    $uploadPath = $tconfig["tsite_upload_bulk_item_csv_path"]."/";
    $attachment_name = $time . "_" . $filename; // NAME NAME OF THE FILE FOR OUR SYSTEM
    $newname = $uploadPath . $attachment_name; // FULL PATH OF FILE DESTINATION
    $uploadeFile = move_uploaded_file($imageTempName, $newname);
    if ($uploadeFile) { // UPLOAD FILE TO DESTIGNATION FOLDER 
        $result = array("status" => "Success", "fileName" => $newname, "imageType" => $imageType); // IF SUCCESS THEN RETURN TYPE AND NAME
    } else { // UPLOAD ERRPR
        $result = array("status" => "Upload error");
    }
    return $result; // RETURN VALUE TO THE CALL FUNCTION 
}

//echo $step."<br>";
$reload = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?=$langage_lbl_admin['LBL_IMPORT_BULK_ITEM_LEFT_MENU']; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <?php include_once('global_files.php'); ?>
        <link type="text/css" rel="stylesheet" href="<?= $adminUrl; ?>css/stepper/materialize.min.css" media="screen,projection" />
        <link type="text/css" rel="stylesheet" href="<?= $adminUrl; ?>css/stepper/style.css" media="screen,projection" />
        <link type="text/css" rel="stylesheet" href="<?= $adminUrl; ?>css/stepper/prism.css" media="screen,projection" />
        <link type="text/css" rel="stylesheet" href="<?= $adminUrl; ?>css/stepper/mstepper.min.css" media="screen,projection" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"rel="stylesheet">
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
                                <h2><?=$langage_lbl_admin['LBL_IMPORT_BULK_ITEM_LEFT_MENU']; ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <div style="display: none;" id="alertfail" class="alert alert-danger alert-dismissable marginbottom-10 msg-test-001">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        <p id="messagetxtfail"></p>
                    </div>
                   
                    <?php if ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable ">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div>
                    <?php } ?>
                    <div style="display: none;" id="alertscs" class="alert alert-success alert-dismissable marginbottom-10 msg-test-001">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                        <p id="messagetxtscs"></p>
                    </div>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div style="clear:both;"></div>
                                <div class="table-responsive" id="demos_horizontal">
                                    <div class="col s12">
                                        <div class="card">
                                            <div class="card-content">
                                                <ul class="stepper horizontal demos" id="horizontal" >
                                                    <li class="step" id="step1">
                                                        <div data-step-label="" class="step-title waves-effect waves-dark"><?=$langage_lbl_admin['LBL_STEP_UPLOAD_FILE_TXT']; ?></div>
                                                        <form name='dbcompare' id='dbcompare'  method='post' enctype="multipart/form-data">
                                                            <div class="step-content">
                                                                <?php if(scount($getServiceData) > 1) { ?>
                                                                <label for="servicecat"><strong>Select Service Category</strong></label>
                                                                    <div class="row">
                                                                        <div class="input-field col s6">
                                                                            <select name="servicecat" id="servicecat" onchange="getStoreList(this.value);" required="required" class="form-control">
                                                                                <option value="">Select Category</option>
                                                                                <?php for($g=0;$g<scount($getServiceData);$g++) { ?>
                                                                                 <option value="<?= $getServiceData[$g]['iServiceId']; ?>"><?= $getServiceData[$g]['serviceName']; ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                <?php }else { ?>
                                                                    <input type="hidden" value="<?= $getServiceData[0]['iServiceId']; ?>" name="servicecat" id="servicecat">
                                                                <?php } ?>
                                                                <label for="iCompanyId"><strong>Select Store</strong></label>
                                                                <div class="row">
                                                                    <div class="input-field col s6">
                                                                        <select name="iCompanyId" id="iCompanyId" onchange="setCompanyId(this.value);" required="required" class="form-control filter-by-text-store">
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!--<label for="uploadFileCuisine">Select Cuisine csv File</label>
                                                                <div class="row">
                                                                    <div class="input-field col s12">
                                                                        <input type="file" class="validate" name="uploadFileCuisine" id="uploadFileCuisine" required=""/>

                                                                    </div>
                                                                </div>-->
                                                                <label for="uploadFile"><strong><?=$langage_lbl_admin['LBL_SELECT_ITEM_CSV_FILE_TXT']; ?> </strong></label>&nbsp;&nbsp;<a style="cursor: pointer;" onclick="downloadCsv();">(<?=$langage_lbl_admin['LBL_DOWNLOAD_CSV_SAMPLE_FILE_TXT']; ?>)</a>
                                                                <div class="row">
                                                                    <div class="input-field col s12">
                                                                        <!-- <input type="file" class="validate" name="uploadFile" id="uploadFile" accept=".csv" required=""/> -->
                                                                        <input type="file" class="validate" name="uploadFile" id="uploadFile" required=""/>
                                                                    </div>
                                                                </div>
                                                                <div class="step-actions">
                                                                    <input type="submit" class="waves-effect waves-dark btn blue" name='comparedb' value="<?=$langage_lbl_admin['LBL_CONTINUE_BTN']; ?>">
                                                                    <!--<button class="waves-effect waves-dark btn blue next-step">CONTINUE</button>-->
                                                                </div>
                                                            </div>
                                                            <!--<div class="step-content">
                                                                <div class="row">
                                                                   <div class="input-field col s12">
                                                                      <input id="linear_email" name="linear_email" type="email" class="validate" required>
                                                                      <label for="linear_email">Your e-mail</label>
                                                                   </div>
                                                                </div>
                                                                <div class="step-actions">
                                                                   <button class="waves-effect waves-dark btn blue next-step">CONTINUE</button>
                                                                </div>
                                                             </div>-->
                                                        </form>
                                                    </li>
                                                    <li class="step" id="step2">
                                                        <div class="step-title waves-effect waves-dark"><?=$langage_lbl_admin['LBL_STEP_DATA_PROCESS_TXT']; ?></div>
                                                        <div class="step-content">
                                                            <span id="donotbutton" style="color: red;"><?=$langage_lbl_admin['LBL_DO_NOT_REFRESH_PAGE_NOTE_TXT']; ?></span>
                                                            <div class="row">
                                                                <div class="input-field col s12">
                                                                    <ul class="proccess-list">
                                                                        <li><i>1</i> <span><?=$langage_lbl_admin['LBL_VALIDATE_DATA_TXT']; ?></span><img id="validatetick" class="mark-icon" src="<?= $adminUrl; ?>images/tick.png"><img id="validateloader" class="loader-gif" src="<?= $adminUrl; ?>images/giphy.gif"></li>
                                                                        <li><i>2</i> <span><?=$langage_lbl_admin['LBL_IMPORT_ITEM_CATEGORY_TXT']; ?></span><img id="categorytick" class="mark-icon" src="<?= $adminUrl; ?>images/tick.png"><img id="categoryloader" class="loader-gif" src="<?= $adminUrl; ?>images/giphy.gif"></li>
                                                                        <li><i>3</i> <span><?=$langage_lbl_admin['LBL_IMPORT_ITEM_DATA_TXT']; ?></span><img id="itemtick" class="mark-icon" src="<?= $adminUrl; ?>images/tick.png"><img id="itemloader" class="loader-gif" src="<?= $adminUrl; ?>images/giphy.gif"></li>
                                                                        <li><i>4</i> <span><?=$langage_lbl_admin['LBL_CONFIG_ITEM_IMAGE_TXT']; ?></span><img id="imagetick" class="mark-icon" src="<?= $adminUrl; ?>images/tick.png"><img id="imageloader" class="loader-gif" src="<?= $adminUrl; ?>images/giphy.gif"></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="step-actions">
                                                                <button id="secondstepbtn" class="waves-effect waves-dark btn blue next-step" data-feedback="someFunction" style="color:#ffffff !important;"><?=$langage_lbl_admin['LBL_CONTINUE_BTN']; ?></button>
                                                                <!--<button class="waves-effect waves-dark btn-flat previous-step">BACK</button>-->
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="step" id="step3">

                                                        <div class="step-title waves-effect waves-dark"><?=$langage_lbl_admin['LBL_STEP_DATA_FINALIZE_TXT']; ?></div>
                                                        <div class="step-content center-ico">
                                                            <div style="display: none;    margin-top: 20px; text-align: center;" id="alertfailnew" class="alert alert-danger alert-dismissable marginbottom-10 msg-test-001">
                                                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                                                    <p id="messagetxtfailnew"></p>
                                                                     <p id="messagetxtfailitem"></p>
                                                                </div>

                                                            <!--Items data added successfully!-->
                                                            <img id="finaltick" src="<?= $adminUrl; ?>images/tick.png">
                                                            <div class="step-actions">
                                                                
                                                                <a target="_blank" href="<?= $adminUrl; ?>menu_item.php"><span class="waves-effect waves-dark btn blue" style="color:#ffffff !important;"><?=$langage_lbl_admin['LBL_VIEW']; ?></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                <a href="<?= $adminUrl; ?>import_item_data.php"><span class="waves-effect waves-dark btn blue" style="color:#ffffff !important;"><?=$langage_lbl_admin['LBL_START_OVER_BTN_TXT']; ?></span></a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4><?=$langage_lbl_admin['LBL_STEP_AND_IMP_NOTE_TXT']; ?></h4>
                        <ul>
                            <li><?=$langage_lbl_admin['LBL_STEP_NOTE_ONE_TXT']; ?></li>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_ONE_TXT']; ?></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_TWO_TXT']; ?></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_THREE_TXT']; ?> <a target="_blank" href="https://www.openoffice.org/"><?= $langage_lbl_admin['LBL_CLICK_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_FOUR_TXT']; ?></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_FIVE_TXT']; ?></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_SIX_TXT']; ?> <a target="_blank" href="<?= $siteUrl; ?>assets/img/openwith.png"><?=$langage_lbl_admin['LBL_SEE_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_SEVEN_TXT']; ?> <a target="_blank" href="<?= $siteUrl; ?>assets/img/openoffice.png"><?=$langage_lbl_admin['LBL_SEE_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?= $langage_lbl_admin['LBL_STEP_NOTE_ONE_SUB_EIGHT_TXT']; ?> <a target="_blank" href="https://www.office.com/"><?= $langage_lbl_admin['LBL_CLICK_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_TXT']; ?></li>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_ONE_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_SEVENTEEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_TWO_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_THREE_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_FOUR_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_FIVE_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_SIX_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_SEVEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_EIGHT_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_NINE_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_TEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_ELEVEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_TWELVE_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_THIRTEEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_FIFTEEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_SIXTEEN_TXT']; ?></p>
                            <p>- <?= $langage_lbl_admin['LBL_STEP_NOTE_TWO_SUB_FOURTEEN_TXT']; ?></p>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_THREE_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_FOUR_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_FIVE_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_SIX_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_SEVEN_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_EIGHT_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_NINE_TXT']; ?></li>
                            <li><?= $langage_lbl_admin['LBL_STEP_NOTE_TEN_TXT']; ?></li>
                        </ul> 
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <?php include_once('footer.php'); ?>
        <link rel="stylesheet" href="css/select2/select2.min.css"/>
        <script src="js/plugins/select2.min.js"></script>
        <!--<script src="js/jquery-1.7.1.min.js"></script>-->
        <script src="<?= $adminUrl; ?>js/stepper/materialize.min.js"></script>
        <script src="<?= $adminUrl; ?>js/stepper/mstepper.js"></script>
        <!-- <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script> -->
        <script src="<?= $adminUrl; ?>js/stepper/prism.js"></script>
        <script>
            var failcounter = 0;
            var successcounter = 0;
            var iMenuItemId = 0;
            document.addEventListener('DOMContentLoaded', function () {
                var sideNav = document.querySelector('.toc-wrapper');
                var footer = document.querySelector('#footer');
                //console.log(sideNav.offsetHeight)
                M.Pushpin.init(sideNav, {top: sideNav.offsetTop, offset: 77, bottom: footer.offsetTop + footer.offsetHeight - 350});
                var scrollSpy = document.querySelectorAll('.scrollspy');
                M.ScrollSpy.init(scrollSpy);
            });
            var domSteppers = document.querySelectorAll('.stepper.demos');
            for (var i = 0, len = domSteppers.length; i < len; i++) {
                var domStepper = domSteppers[i];
                new MStepper(domStepper);
            }
            var stepNo = '<?= $step; ?>';
            $(document).ready(function () {
                var defaultServiceId = $("#servicecat").val();
                getStoreList(defaultServiceId);
                $("#validatetick,#validateloader,#categorytick,#categoryloader,#itemloader,#itemtick,#imagetick,#imageloader","#donotbutton").hide();
                var errorMsg = "<?= $errorMsg; ?>";
                if(errorMsg != ""){
                    $("#alertfail").show();
                    $("#messagetxtfail").text(errorMsg);
                }
                setTimeout(function () {
                    $("#alertfail,#alertscs").hide();
                }, 7000);
                if (stepNo == 2) {
                    var itemSrSkip = "";
                    $("#step1,#step3").removeClass("active");
                    $("#step2").addClass("active");
                    $("#step1").addClass("done");
                    var uploadedFile = '<?= $uploadedFile; ?>';
                    validateData(uploadedFile, "validate","Yes",itemSrSkip);
                } else if (stepNo == 3) {
                    $("#step1,#step2").removeClass("active");
                    $("#step1,#step2").addClass("done");
                    $("#step3").addClass("active");
                } else {
                    $("#step2,#step3").removeClass("active");
                    $("#step1").addClass("active");
                }
            });
            function someFunction(destroyFeedback) {
                setTimeout(function () {
                    destroyFeedback(true);
                }, 1000);
            }
            var allstoredata = [];
            allstoredata = <?= json_encode($storeArr, JSON_UNESCAPED_UNICODE); ?>;
            function getStoreList(serviceId){
                var storeHtml = "<option value=''>Select Store</option>";
                if(serviceId > 0){
                    var storeArr = allstoredata[serviceId];
                    for (var t = 0, n = storeArr.length; t < n; t++){
                        storeHtml += "<option value='"+storeArr[t]['iCompanyId']+"'>"+storeArr[t]['vCompany']+"</option>";
                    }
                    // console.log(storeHtml);
                }
                $("#iCompanyId").html(storeHtml);
            }
            function setCompanyId(iCompanyId){
                localStorage.serviceId = $("#servicecat").val();
                localStorage.companyId = iCompanyId;
            }
            function downloadCsv(){
                <?php if(scount($getServiceData) > 1) { ?>
                if($('#servicecat').val() == "") {
                    alert("<?= $langage_lbl_admin['LBL_SELECT_SERVICE_CATEGORY_TXT'] ?>");
                    return false;
                }
                var service_id = $('#servicecat').val();
                <?php } else { ?>
                var service_id = '<?= $getServiceData[0]['iServiceId']; ?>';
                <?php } ?>
                var action = "<?= $adminUrl; ?>import_item_data.php";
                
                window.location.href = action + '?export=csv&service_id=' + service_id;
            }

            function containsNumbers(str) {
              return /[0-9]/.test(str);
            }
            function validateData(uploadedFile, stepType,eValidate,itemSrSkip) {
                //alert(uploadedFile);
                if (stepType == "validate") {
                    $('#secondstepbtn').prop('disabled', true);
                    $("#validateloader,#donotbutton").show();
                    $("#validatetick,#categoryloader,#categorytick,#itemtick,#itemloader,#imagetick,#imageloader").hide();
                }
                var serviceId = localStorage.serviceId;
                var companyId = localStorage.companyId;
                var resultactionvar = 0;
                var resultaction = 0;
                var resultactionmessage = "";


                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_data_process.php',
                    'AJAX_DATA': {file: uploadedFile, step: stepType,iServiceId:serviceId,iCompanyId:companyId,validate:eValidate,itemSrSkip:itemSrSkip},
                    'REQUEST_DATA_TYPE': 'json'
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var dataHtml = response.result;
                        var stepName = dataHtml.step;
                        var skipItemSrNo = dataHtml.skipItemSrNo;
                        iMenuItemId = dataHtml.iMenuItemId;

                        resultaction = response.result.action;

                        if(iMenuItemId > 0){
                            successcounter = 1;
                        }

                        if(containsNumbers(skipItemSrNo)){
                            failcounter = 1;
                        }

                         if(resultaction > 1){
                             resultactionmessage = response.result.message;
                                $("#messagetxtfailitem").text(resultactionmessage);
                            }
                    
                        if (dataHtml.action > 0) {
                           
                            if (stepName == "validate") {
                                if(skipItemSrNo != "" && skipItemSrNo != undefined){
                                    failcounter = 1;
                                    stepType = "importCat";
                                    validateData(uploadedFile, stepType,"No",skipItemSrNo);
                                }else{
                                    if (confirm("<?=$langage_lbl_admin['LBL_CONFIRM_MSG_SKIP_ITEM_VALIDATION_TXT']; ?>") && dataHtml.action != "9") {
                                        validateData(uploadedFile, stepType,"No",skipItemSrNo);
                                    } else {
                                        $("#step3,#step2").removeClass("active");
                                        $("#step1").addClass("active");
                                        $("#step1").removeClass("done");
                                        $('#secondstepbtn').prop('disabled', false);
                                        $("#validateloader,#donotbutton").hide();
                                        $("#alertfail").show();
                                        $("#messagetxtfail").text(dataHtml.message);
                                    }
                                }
                            }
                        } else {
                            //$("#alertscs").show();
                            //$("#messagetxtscs").text(dataHtml.message);
                            if (stepName == "validate") {
                                stepType = "importCat";
                                $('#secondstepbtn').prop('disabled', true);
                                $("#validatetick,#categoryloader,#donotbutton").show();
                                $("#validateloader,#categorytick,#itemtick,#itemloader,#imagetick,#imageloader").hide();
                            } else if (stepName == "importCat") {
                                stepType = "importItem";
                                $('#secondstepbtn').prop('disabled', true);
                                $("#validatetick,#categorytick,#itemloader,#donotbutton").show();
                                $("#validateloader,#categoryloader,#itemtick,#imagetick,#imageloader").hide();
                            } else if (stepName == "importItem") {
                                stepType = "configImage";
                                $('#secondstepbtn').prop('disabled', true);
                                $("#validatetick,#categorytick,#itemtick,#imageloader,#donotbutton").show();
                                $("#validateloader,#categoryloader,#itemloader,#imagetick").hide();
                            } else if (stepName == "configImage") {
                                $('#secondstepbtn').prop('disabled', true);
                                $("#validatetick,#categorytick,#itemtick,#imagetick,#donotbutton").show();
                                $("#validateloader,#categoryloader,#itemloader,#imageloader").hide();
                                stepType = "";
                                $("#step1,#step2").removeClass("active");
                                $("#step3").addClass("active");
                            }
                        
                            if(failcounter == 1 && successcounter == 1){
                                    $("#alertfailnew").show();
                                    $("#messagetxtfailnew").text("<?=$langage_lbl_admin['LBL_SYSTEM_SKIP_ITEM_TXT']; ?>");
                            }else if(successcounter == 1 && failcounter == 0){
                                    $("#alertfailnew").hide();
                                    $("#messagetxtfailnew").text("");
                            }else if(successcounter == 0 && failcounter == 1){
                                    $("#alertfailnew").show();
                                    $("#messagetxtfailnew").text("<?=$langage_lbl_admin['LBL_ERROR_FAIL_TO_UPLOAD_CSV_TXT'];?>");
                            }


                             if(resultactionvar != ""){
                                alert("1111");
                                $("#alertfailitem").show();
                            }
                            if (stepType != "") {
                                validateData(uploadedFile, stepType,"Yes",skipItemSrNo);
                            } else {
                                localStorage.serviceId = 0;
                                localStorage.companyId = 0;
                                $("#step1,#step2").addClass("done");
                            }
                        }  
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }
            $(document).ready(function() {
                $('.filter-by-text-store').select2();
            });

            if ($('#dbcompare').length !== 0) {
                console.log(csvUploadingExtenstionMsg);
                $("#dbcompare").validate({
                    ignore: 'input[type=hidden]',
                    errorClass: 'help-block',
                    errorElement: 'span',
                    // errorPlacement: function (error, e) {
                    //     e.parents('.row > div').append(error);
                    // },
                    highlight: function (e) {
                        $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                        $(e).closest('.help-block').remove();
                    },
                    success: function (e) {
                        e.closest('.row').removeClass('has-success has-error');
                        e.closest('.help-block').remove();
                        e.closest('.help-inline').remove();
                    },
                    rules: {
                            uploadFile:{
                                required:true,
                                extension: "csv"
                        }
                    },
                    messages: {
                            uploadFile:{
                                required: requiredFieldMsg,
                                extension: csvUploadingExtenstionMsg
                            }
                        },
                });
            }

        </script> 
    </body>
    <!-- END BODY-->
</html>
