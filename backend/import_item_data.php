<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
setRole($abc,$url);
$script = "ImportItem";
$step = 1;
$uploadedFile = "";
$iCompanyId = $_SESSION['sess_iCompanyId'];
$getStoreData = $obj->MySQLSelect("SELECT iCompanyId,iServiceId,vCompany FROM company WHERE iCompanyId = '".$iCompanyId."'");

$storeId = $storeServiceId = 0;
if(scount($getStoreData) > 0){
    $storeId = $getStoreData[0]['iCompanyId'];
    $storeServiceId = $getStoreData[0]['iServiceId'];
}
$docUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
$errorMsg = "";
if (isset($_POST['comparedb'])) {

    if (isset($_FILES["uploadFile"])) {
        $uploadFile = uploadImage($_FILES["uploadFile"]);

        if (isset($uploadFile['fileName']) && $uploadFile['fileName'] != "") {
            $uploadedFile = $uploadFile['fileName'];
            $fileextension = end(explode('.', $uploadedFile));
            if(strtoupper($fileextension) == "CSV"){
                $step = 2;
            }else{
                // header("Location:import_item_data.php?error=Please upload only CSV file");
                $docUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                header("Location:import_item_data.php?error=".$docUploadingExtenstionMsg);
                die;
            }
        }
   
    } else {
        $docUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        header("Location:import_item_data.php?error=". $docUploadingExtenstionMsg);
        die;
    }
}
$siteUrl = $tconfig['tsite_url'];

if (isset($_REQUEST['export']) && strtoupper($_REQUEST['export']) == "CSV") {
    $service_id = $storeServiceId;
    $langData = $obj->MySQLSelect("SELECT * FROM language_master WHERE eStatus='Active'");
    $date = new DateTime();
    $timestamp_filename = $date->getTimestamp();
    $filename = "item_".$timestamp_filename . ".csv";
    $fp = fopen('php://output', 'w');
    
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
    $finalHeader = array_merge($header, $otherHeader);

    header('Content-type: application/csv');
    header('Content-Disposition: attachment; filename='.$filename);
    fputcsv($fp, $finalHeader);
    $itemDataArr = array();
    for($b=0;$b<scount($sampleItem);$b++){
        $newItemArr = array();
        foreach($finalHeader as $key=>$val){
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
    $_REQUEST['error'] = "";
}
function uploadImage($attachment, $time = "") {
    global $tconfig, $docUploadingExtenstionMsg;

    $fileextension = end(explode('.', $attachment['name']));
    if(strtoupper($fileextension) == "CSV"){
        $step = 2;
    }else{
        header("Location:import_item_data.php?error=". $docUploadingExtenstionMsg);
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

$docUploadingExtenstionMsg = str_replace("####","csv",$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
?>
<!DOCTYPE html>
<html lang="en" dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?=$SITE_NAME?> | <?=$langage_lbl['LBL_IMPORT_BULK_ITEM_LEFT_MENU']; ?></title>
        <!-- Default Top Script and css -->
        <?php include_once("top/top_script.php");?>
        <link href="https://fonts.googleapis.com/css?family=Material+Icons|Roboto:300,400,500" rel="stylesheet">
        <link type="text/css" rel="stylesheet" href="<?= $siteUrl; ?>assets/css/stepper/materialize.min.css" media="screen,projection" />
        <link type="text/css" rel="stylesheet" href="<?= $siteUrl; ?>assets/css/stepper/style.css" media="screen,projection" />
        <link type="text/css" rel="stylesheet" href="<?= $siteUrl; ?>assets/css/stepper/prism.css" media="screen,projection" />
        <link type="text/css" rel="stylesheet" href="<?= $siteUrl; ?>assets/css/stepper/mstepper.min.css" media="screen,projection" />
        <style>
            .upload-element-new .input-field {
                float: none;
                width: auto !important;
                display: inline-block;
                margin: 0;
                padding: 0;
                position: relative;
                height: auto;
                margin-top: 10px;
            }
            .upload-element-new .input-field #uploadFile {
                position: relative;
                height:40px;
                cursor:pointer;
            }
            .upload-element-new .input-field:after {
                content: '<?php echo $langage_lbl['LBL_CHOOSE_FILE']; ?>';
                font-size: 16px;
                color: #fff;
                position: absolute;
                width: 100%;
                height: 100%;
                left: 0;
                top: 0;
                background-color: #000;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                border-radius: 5px;
                pointer-events: none;
            }
            /*  */
            .admin-notes p {
                display: block;
                width: 100%;
                margin-bottom: 10px;
                line-height: 28px;
            }
            .admin-notes ul li {
                line-height: 26px;
                font-size: 15px;
                margin-bottom: 30px;
                width: 100%;
            }
            .admin-notes p a {
                color:#239707;
            }
            .uploaded_file i img {
                width: 15px;
                vertical-align: middle;
            }
            .uploaded_file {
                padding: 8px 10px;
                margin-top: 10px;
                display: inline-block;
                border: 1px solid #ccc;
            }
            .uploaded_file i {
                margin-right: 10px;
            }
            #messagetxtfail{
                color: #ffffff !important;
            }
            .fileerror{
/*                top: 135px;*/
                position: relative !important;
                bottom: -1px !important;
    
            }
        </style>
        <script>
            $(document).ready(function(){
                var imageUploadingExtenstionMsg = "<?=$docUploadingExtenstionMsg?>";
                $('#uploadFile').change(function(e){
                    var fileName = e.target.files[0].name;
                    $( ".uploaded_file" ).remove();
                    $('<br><div class="uploaded_file"><i><img src="assets/img/csv.svg"/></i>'+fileName+'</div>').insertAfter('.upload-element-new .input-field');
                });
            })
        </script>
    </head>
    <body>
        <!-- home page -->
    <div id="main-uber-page">
            <!-- Left Menu -->
            <?php include_once("top/left_menu.php");?>
            <!-- End: Left Menu-->
            <!-- Top Menu -->
            <?php include_once("top/header_topbar.php");?>
            <!-- End: Top Menu-->
            <!-- contact page-->
            <section class="profile-section my-trips">
                <div class="profile-section-inner">
                    <div class="profile-caption">
                        <div class="page-heading">
                            <h1><?=$langage_lbl['LBL_IMPORT_BULK_ITEM_LEFT_MENU']; ?></h1>
                        </div>          
                    </div>
                </div>
            </section>
            <section class="profile-earning">
                <div class="profile-earning-inner">
                    <div class="card" style="width:100%">
                        <div class="card-content">
                        <!-- trips page -->
                        <div style="display: none;" id="alertfail" class="alert alert-danger alert-dismissable marginbottom-10 msg-test-001">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <p id="messagetxtfail"></p>
                        </div>
                        <div style="display: none;" id="alertscs" class="alert alert-success alert-dismissable marginbottom-10 msg-test-001">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                            <p id="messagetxtscs"></p>
                        </div>
                        <!--PAGE CONTENT -->
                            <ul class="stepper horizontal demos" id="horizontal" >
                                <li class="step" id="step1">
                                    <div data-step-label="" class="step-title waves-effect waves-dark"><?=$langage_lbl['LBL_STEP_UPLOAD_FILE_TXT']; ?></div>
                                    <form name='dbcompare' method='post' enctype="multipart/form-data">
                                        <div class="step-content">
                                            <input type="hidden" value="<?= $getServiceData[0]['iServiceId']; ?>" name="servicecat" id="servicecat">
                                            <label for="uploadFile"><strong><?=$langage_lbl['LBL_SELECT_ITEM_CSV_FILE_TXT']; ?> </strong></label>&nbsp;&nbsp;<a style="cursor: pointer;" onclick="downloadCsv();">(<?=$langage_lbl['LBL_DOWNLOAD_CSV_SAMPLE_FILE_TXT']; ?>)</a>

                                            <div class="row">
                                                <div class="upload-element-new">
                                                    <div class="input-field col s12" data-name="Choose File">
                                                        <input type="file" class="validate" name="uploadFile" id="uploadFile" onChange="validate_fileextension(this.value);" accept=".csv" required=""/>
                                                        
                                                    </div>
                                                    <div class="fileerror help-block error" ></div>
                                                   
                                                </div>
                                            </div>
                                            <div class="step-actions">
                                                <input type="submit" class="waves-effect waves-dark btn" name='comparedb' id='comparedb'  value="<?=$langage_lbl['LBL_CONTINUE_BTN']; ?>">
                                                <!--<button class="waves-effect waves-dark btn blue next-step">CONTINUE</button>-->
                                            </div>
                                        </div>
                                    </form>
                                </li>
                                <li class="step" id="step2">
                                    <div class="step-title waves-effect waves-dark"><?=$langage_lbl['LBL_STEP_DATA_PROCESS_TXT']; ?></div>
                                    <div class="step-content">
                                        <span id="donotbutton" style="color: red;"><?=$langage_lbl['LBL_DO_NOT_REFRESH_PAGE_NOTE_TXT']; ?></span>
                                        <div class="row">
                                            <div class="input-field col s12">
                                                <ul class="proccess-list">
                                                    <li><i>1</i> <span><?=$langage_lbl['LBL_VALIDATE_DATA_TXT']; ?></span><img id="validatetick" class="mark-icon" src="<?= $siteUrl; ?>assets/img/tick.png"><img id="validateloader" class="loader-gif" src="<?= $siteUrl; ?>assets/img/giphy.gif"></li>
                                                    <li><i>2</i> <span><?=$langage_lbl['LBL_IMPORT_ITEM_CATEGORY_TXT']; ?></span><img id="categorytick" class="mark-icon" src="<?= $siteUrl; ?>assets/img/tick.png"><img id="categoryloader" class="loader-gif" src="<?= $siteUrl; ?>assets/img/giphy.gif"></li>
                                                    <li><i>3</i> <span><?=$langage_lbl['LBL_IMPORT_ITEM_DATA_TXT']; ?></span><img id="itemtick" class="mark-icon" src="<?= $siteUrl; ?>assets/img/tick.png"><img id="itemloader" class="loader-gif" src="<?= $siteUrl; ?>assets/img/giphy.gif"></li>
                                                    <li><i>4</i> <span><?=$langage_lbl['LBL_CONFIG_ITEM_IMAGE_TXT']; ?></span><img id="imagetick" class="mark-icon" src="<?= $siteUrl; ?>assets/img/tick.png"><img id="imageloader" class="loader-gif" src="<?= $siteUrl; ?>assets/img/giphy.gif"></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="step-actions">
                                            <button id="secondstepbtn" class="waves-effect waves-dark btn next-step" data-feedback="someFunction" style="color:#ffffff !important;"><?=$langage_lbl['LBL_CONTINUE_BTN']; ?></button>
                                        </div>
                                    </div>
                                </li>
                                <li class="step" id="step3">
                                    <div class="step-title waves-effect waves-dark"><?=$langage_lbl['LBL_STEP_DATA_FINALIZE_TXT']; ?></div>
                                    <div class="step-content center-ico">
                                        <!--Items data added successfully!-->
                                        <img id="finaltick" src="<?= $siteUrl; ?>assets/img/tick.png">
                                        <div class="step-actions">
                                            <a target="_blank" href="<?= $siteUrl; ?>menuitems.php"><span class="waves-effect waves-dark btn" style="color:#ffffff !important;"><?=$langage_lbl['LBL_VIEW']; ?></span></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;                                   
                                            <a href="<?= $adminUrl; ?>import_item_data.php"><span class="waves-effect waves-dark btn" style="color:#ffffff !important;"><?=$langage_lbl['LBL_START_OVER_BTN_TXT']; ?></span></a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <!--END PAGE CONTENT -->
                            <div style="clear:both;"></div>
                            <input type="hidden" name="del_btn_id" id="del_btn_id" value="">
                        </div>
                    </div>
                    <div class="gen-cms-page">
                        <div class="gen-cms-page-inner">
                            <!--<h2 class="header-page">
                            </h2>-->
                            <div class="static-page">
                              <h4><?=$langage_lbl['LBL_STEP_AND_IMP_NOTE_TXT']; ?></h4>
                              
                                <div>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_ONE_TXT']; ?></b></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_ONE_TXT']; ?></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_TWO_TXT']; ?></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_THREE_TXT']; ?> <a target="_blank" href="https://www.openoffice.org/"><?= $langage_lbl['LBL_CLICK_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_FOUR_TXT']; ?></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_FIVE_TXT']; ?></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_SIX_TXT']; ?> <a target="_blank" href="<?= $siteUrl; ?>assets/img/openwith.png"><?=$langage_lbl['LBL_SEE_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_SEVEN_TXT']; ?> <a target="_blank" href="<?= $siteUrl; ?>assets/img/openoffice.png"><?=$langage_lbl['LBL_SEE_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <?= $langage_lbl['LBL_STEP_NOTE_ONE_SUB_EIGHT_TXT']; ?> <a target="_blank" href="https://www.office.com/"><?= $langage_lbl['LBL_CLICK_HERE_IMPORT_ITEM_DATA']; ?></a></p>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_TWO_TXT']; ?></b></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_ONE_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_SEVENTEEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_TWO_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_THREE_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_FOUR_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_FIVE_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_SIX_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_SEVEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_EIGHT_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_NINE_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_TEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_ELEVEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_TWELVE_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_THIRTEEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_FIFTEEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_SIXTEEN_TXT']; ?></p>
                                    <p>- <?= $langage_lbl['LBL_STEP_NOTE_TWO_SUB_FOURTEEN_TXT']; ?></p>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_THREE_TXT']; ?></b></p>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_SEVEN_TXT']; ?></b></p>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_EIGHT_TXT']; ?></b></p>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_NINE_TXT']; ?></b></p>
                                    <p><b><?=$langage_lbl['LBL_STEP_NOTE_TEN_TXT']; ?></b></p>
                                </div> 
                            </div>
                        </div> 
                </div>
            </section>
            
            <!-- footer part -->
            <?php include_once('footer/footer_home.php');?>
            <!-- footer part end -->
            <!-- End:food menu page-->
            <div style="clear:both;"></div>
        </div>
        <!-- Footer Script -->
]       <?php include_once('top/footer_script.php');?>
        <script src="assets/js/jquery-ui.min.js"></script>
        <script src="<?= $siteUrl; ?>assets/js/stepper/materialize.min.js"></script>
        <script src="<?= $siteUrl; ?>assets/js/stepper/mstepper.js"></script>
        <!-- <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script> -->
        <script src="<?= $siteUrl; ?>assets/js/stepper/prism.js"></script>
        <script>

            function validate_fileextension(filename) {
                
                var imageUploadingExtenstionMsg = "<?=$docUploadingExtenstionMsg?>";
                var fileExtension = $.parseJSON(csvUploadingExtenstionJson);
                if ($.inArray(filename.split('.').pop().toLowerCase(), fileExtension) == -1) {
                    $(".fileerror").html(imageUploadingExtenstionMsg);
                    $('#comparedb').prop("disabled", true);
                    return false;
                }
                else {
                    $('#comparedb').prop("disabled", false);
                    $(".fileerror").html("");
                }
            }
            /*document.addEventListener('DOMContentLoaded', function () {
                var sideNav = document.querySelector('.toc-wrapper');
                var footer = document.querySelector('#footer');
                //console.log(sideNav.offsetHeight)
                M.Pushpin.init(sideNav, {top: sideNav.offsetTop, offset: 77, bottom: footer.offsetTop + footer.offsetHeight - 350});
                var scrollSpy = document.querySelectorAll('.scrollspy');
                M.ScrollSpy.init(scrollSpy);
            });*/
            var domSteppers = document.querySelectorAll('.stepper.demos');
            for (var i = 0, len = domSteppers.length; i < len; i++) {
                var domStepper = domSteppers[i];
                new MStepper(domStepper);
            }
            var stepNo = '<?= $step; ?>';
            $(document).ready(function () {
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
            function downloadCsv(){
                var action = "<?= $siteUrl; ?>import_item_data.php";
                window.location.href = action + '?export=csv';
            }
            function validateData(uploadedFile, stepType,eValidate,itemSrSkip) {
                //alert(uploadedFile);
                if (stepType == "validate") {
                    $('#secondstepbtn').prop('disabled', true);
                    $("#validateloader,#donotbutton").show();
                    $("#validatetick,#categoryloader,#categorytick,#itemtick,#itemloader,#imagetick,#imageloader").hide();
                }
                var serviceId = "<?= $storeServiceId; ?>";
                var companyId = "<?= $storeId; ?>";

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_data_process.php',
                    'AJAX_DATA': {file: uploadedFile, step: stepType,iServiceId:serviceId,iCompanyId:companyId,validate:eValidate,itemSrSkip:itemSrSkip},
                    'REQUEST_DATA_TYPE': 'json'
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var dataHtml = response.result;
                        console.log(dataHtml.action);
                        var stepName = dataHtml.step;
                        var skipItemSrNo = dataHtml.skipItemSrNo;
                        if (dataHtml.action > 0) {
                            if (stepName == "validate") {
                                if(skipItemSrNo != "" && skipItemSrNo != undefined){
                                    stepType = "importCat";
                                    validateData(uploadedFile, stepType,"No",skipItemSrNo);
                                }else{
                                    if (confirm("<?=$langage_lbl['LBL_CONFIRM_MSG_SKIP_ITEM_VALIDATION_TXT']; ?>") && dataHtml.action != "9") {
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
        </script>
    </body>
</html>