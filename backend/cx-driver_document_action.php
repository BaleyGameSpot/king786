<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$TRACKING_COMPANY = 0;
if ($_SESSION['sess_user'] == 'tracking_company') {
    $TRACKING_COMPANY = 1;
}
if ($TRACKING_COMPANY == 1) {
    $abc = 'tracking_company';
    $iTrackServiceCompanyId = $_SESSION['sess_iTrackServiceCompanyId'];
}
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$success = '';
if(isset($_SESSION['message_code']) && !empty($_SESSION['message_code']))
{
    $success = $_SESSION['message_code'];
    $_SESSION['message_code'] = '';
}
$var_msg = '';
if(isset($_SESSION['session_msg']) && !empty($_SESSION['session_msg']))
{
    $var_msg = $_SESSION['session_msg'];
    $_SESSION['session_msg'] = '';
}


setRole($abc, $url);
require_once(TPATH_CLASS . "/Imagecrop.class.php");
if($APP_TYPE == "UberX"){
    $driverlisturl = "providerlist";
    $driverdocumentactionurl = "provider_document_add_form";
} else { 
    $driverlisturl = "driverlist";
    $driverdocumentactionurl = "driver_document_add_form";
}
if ($_SESSION['sess_user'] == 'company' ) {
    $DriverId = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $Dsql = "SELECT iCompanyId FROM register_driver WHERE iDriverId = '" . $DriverId . "'";
    $db_cmp_data = $obj->MySQLSelect($Dsql);
    $cmpid = $db_cmp_data[0]['iCompanyId'];
    $sess_iCompanyId = $_SESSION['sess_iCompanyId'];
    if ($sess_iCompanyId != $cmpid) {
        $_SESSION['message_code'] = '0';
        $_SESSION['session_msg'] = $langage_lbl['LBL_NOT_YOUR_DRIVER'];
        header("Location:".$driverlisturl);
       // header("Location:".$driverlisturl."?success=0&var_msg=" . $langage_lbl['LBL_NOT_YOUR_DRIVER']);
        exit();
    }
}

if($TRACKING_COMPANY == 1){
    $DriverId = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
    $Dsql = "SELECT iTrackServiceCompanyId FROM register_driver WHERE iDriverId = '" . $DriverId . "'";
    $db_cmp_data = $obj->MySQLSelect($Dsql);
    $cmpid = $db_cmp_data[0]['iTrackServiceCompanyId'];
    if ($iTrackServiceCompanyId != $cmpid) {
        $_SESSION['message_code'] = '0';
        $_SESSION['session_msg'] = $langage_lbl['LBL_NOT_YOUR_DRIVER'];
        header("location:".$driverlisturl);
        //header("Location:".$driverlisturl."?success=0&var_msg=" . $langage_lbl['LBL_NOT_YOUR_DRIVER']);
        exit();
    }
}
$thumb = new thumbnail();
$script = "Driver";
$sql = "SELECT * FROM country WHERE eStatus = 'Active'";
$db_country = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
/*$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';*/
/*
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : 0;
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';*/


$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? 'Edit' : 'Add';
$action_show = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? $langage_lbl['LBL_DRIVER_EDIT'] : $langage_lbl['LBL_DRIVER_ADD'];
$sql = "SELECT * FROM language_master WHERE eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
$sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $_REQUEST['id'] . "'";
$db_user = $obj->MySQLSelect($sql);
$LicenceEXP = $db_user[0]['dLicenceExp'] ? $db_user[0]['dLicenceExp'] : '';
$vName = $db_user[0]['vName'];
$vLicence = $db_user[0]['vLicence'];
$vNoc = $db_user[0]['vNoc'];
$vCerti = $db_user[0]['vCerti'];
$action_doc = isset($_REQUEST['action_doc']) ? $_REQUEST['action_doc'] : '';
if ($action = 'document' && isset($_POST['doc_type'])) {
    $expDate = $_POST['dLicenceExp'];
    // if (SITE_TYPE == 'Demo') {
    //     header("location:driver_document_action.php?success=2&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    //     exit;
    // }
    $masterid = $_REQUEST['master'];
    if (isset($_POST['doc_path'])) {
        $doc_path = $_POST['doc_path'];
    }
    $temp_gallery = $doc_path . '/';
    $image_object = $_FILES['driver_doc']['tmp_name'];
    $image_name = $_FILES['driver_doc']['name'];
    if (empty($image_name)) {
        $image_name = $_POST['driver_doc_hidden'];
    }
    if ($expDate != "") {
        $sql = "UPDATE `document_list` SET  ex_date='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_masterid='" . $masterid . "'";
        $obj->sql_query($sql);
    }
    if ($_FILES['driver_doc']['name'] != "") {
        // $filecheck = basename($_FILES['driver_doc']['name']);
        // $fileextarr = explode(".", $filecheck);
        // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        // $flag_error = 0;
        // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
        //     $flag_error = 1;
        //     $var_msg = $langage_lbl['LBL_IMAGE_FORMAT_ERROR_MSG'];
        // }
        require_once(SITE_ADMIN_URL."/library/validation.class.php");
        $validobj = new validation();
        $docUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_docs_file_extensions_validation_extensions"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['driver_doc'], $tconfig["tsite_upload_docs_file_extensions"], $docUploadingExtenstionMsg); 

        if ($error) {
            $_SESSION['message_code'] = '3';
            $_SESSION['session_msg'] = $error;
            header("location:".$driverdocumentactionurl."?id=" . $_REQUEST['id']);
            //exit;
        } else {
            $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
            $vImage = $vFile[0];
            $var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
            $tbl = 'document_list';
            $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
            $db_data = $obj->MySQLSelect($sql);
            if (scount($db_data) > 0) {
                $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
            } else {
                $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) " . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive', 	CURRENT_TIMESTAMP)";
            }
            $obj->sql_query($query);
            $vNocPath = $vImage;
            ###### Email #######
            /*$maildata['NAME'] = $db_user[0]['vName'];
            $maildata['EMAIL'] = $db_user[0]['vEmail'];
            $maildata['COMPANY'] = $_SESSION['sess_vCompany']." Company ";
            $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD", $maildata);*/
            ###### Email #######
            $maildata['NAME'] = $_SESSION['sess_vCompany'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'] . ")";

            //$maildata['EMAIL'] = $_SESSION['sess_vEmail'];

            if(isset($db_user[0]['vEmail']) && !empty($db_user[0]['vEmail']))
            {
                $maildata['EMAIL'] = $langage_lbl['LBL_EMAIL_TEXT'].': '.$db_user[0]['vEmail'];
            }

            if(isset($db_user[0]['vPhone']) && !empty($db_user[0]['vPhone']))
            {
                $maildata['PHONE'] = $langage_lbl['LBL_PHONE'].': '."+".$db_user[0]['vCode']." ".$db_user[0]['vPhone'];
            }


            $docname_SQL = "SELECT doc_name_" . $default_lang . " as docname FROM document_master WHERE doc_masterid = '" . $_REQUEST['doc_type'] . "'";
            $docname_data = $obj->MySQLSelect($docname_SQL);
            $maildata['DOCUMENTTYPE'] = $docname_data[0]['docname'];
            $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'];
            $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD_WEB", $maildata);
            #######Email ##########
            #######Email ##########
            save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'company', 'noc', $vNocPath);
            $_SESSION['message_code'] = '1';
            $_SESSION['session_msg'] = $var_msg;
            header("location:".$driverdocumentactionurl."?id=" . $_REQUEST['id']);
            //header("location:".$driverdocumentactionurl."?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        }
    } else {
        $vImage = $_POST['driver_doc_hidden'];
        $var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
        $tbl = 'document_list';
        $sql = "select doc_id from  " . $tbl . " where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
        $db_data = $obj->MySQLSelect($sql);
        if (scount($db_data) > 0) {
            $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
        } else {
            $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
                . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive',    CURRENT_TIMESTAMP)";
        }
        $obj->sql_query($query);
        $vNocPath = $vImage;
        /*###### Email #######
        $maildata['NAME'] = $db_user[0]['vName'];
        $maildata['EMAIL'] = $db_user[0]['vEmail'];
        $maildata['COMPANY'] = $_SESSION['sess_vCompany']." Company ";
        $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD", $maildata);
        #######Email ##########*/
        save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'company', 'noc', $vNocPath);
        $_SESSION['message_code'] = '1';
        $_SESSION['session_msg'] = $var_msg;
        header("location:".$driverdocumentactionurl."?id=" . $_REQUEST['id']);
        //header("location:".$driverdocumentactionurl."?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    }
}
if ($APP_TYPE == 'Ride-Delivery') {
    $eTypeQuery = " AND (eType='Ride' OR eType='Delivery')";
} else if ($APP_TYPE == 'Ride-Delivery-UberX') {
    $eTypeQuery = " AND (eType='Ride' OR eType='Delivery' OR eType='UberX')";
} else {
    $eTypeQuery = " AND eType='" . $APP_TYPE . "'";
}
//echo $APP_TYPE;die;
/*$sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_".$_SESSION['sess_lang']."  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status,dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $id . "' ) dl on dl.doc_masterid=dm.doc_masterid
	where dm.doc_usertype='driver' and dm.status='Active' and (dm.country ='".$db_user[0]['vCountry']."' OR dm.country ='All') $eTypeQuery";*/
$ufxEnable = 'No';
$ufxDocCondition = " AND dm.eDocServiceType!='ServiceSpecific' AND dm.eType!='UberX'";
if ($MODULES_OBJ->isUfxFeatureAvailable() == 'Yes') {
    $ufxEnable = 'Yes';
    $ufxDocCondition = "";
}
/*$sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_".$_SESSION['sess_lang']."  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status,dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $id . "' ) dl on dl.doc_masterid=dm.doc_masterid
where dm.doc_usertype='driver' and dm.status='Active' and (dm.country ='".$db_user[0]['vCountry']."' OR dm.country ='All')";*/
$sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_" . $_SESSION['sess_lang'] . "  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status,dm.eType,dm.iVehicleCategoryId,vc.vCategory_" . $_SESSION['sess_lang'] . " as vCategory,dm.eDocServiceType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $id . "' ) dl on dl.doc_masterid=dm.doc_masterid  LEFT JOIN vehicle_category vc on vc.iVehicleCategoryId = dm.iVehicleCategoryId
    where dm.doc_usertype='driver' and dm.status='Active' $ufxDocCondition and (dm.country ='" . $db_user[0]['vCountry'] . "' OR dm.country ='All') ORDER BY dm.iDisplayOrder ASC";
$db_userdoc = $obj->MySQLSelect($sql);
$db_userdoc = getProviderDocumentServiceWise($id, $db_userdoc);
$count_all_doc = scount($db_userdoc);
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_DRIVER_COMPANY_TXT'] . " " . $action_show; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css">
    <!-- <link rel="stylesheet" href="assets/css/jquery-ui.css"> -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .fileupload-preview {
            line-height: 150px;
        }

        .fileupload .newrow.has-error {
            text-align: center;
        }

        #driver_doc-error {
            position: relative;
            width: 240px;
            white-space: normal;
            margin-bottom: 15px;
            line-height: 15px;
            bottom: -7px;
            text-align: center;  
        }
        .alert{
            width:100%;
        }
        #driver_doc-error {
            width: 100%;
        }
        #dtpckrdLicenceExp-error{
            width: 100%;
            text-align: center;
        }
    </style>
    <!-- End: Default Top Script and css-->
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= ucfirst($action_show); ?> <?= $langage_lbl['LBL_Driver_document_Document_of']; ?>  <?= $vName; ?></h1>
                </div>
                <div class="button-block end">
                    <a href="<?php echo $driverlisturl;?>" class="gen-btn"><?= $langage_lbl['LBL_BACK_To_Listing_WEB']; ?></a>
                </div>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="card-block">
                <h1 class="driver-head"><?= $langage_lbl['LBL_REQUIRED_DOCS']; ?></h1>
                <? if ($success == 1) { ?>
                    <div class="alert alert-success alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= $var_msg ?>
                    </div>
                <? } else if ($success == 2) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                    </div>
                    <?php
                } else if ($success == 3) { ?>
                    <div class="alert alert-danger alert-dismissable">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <?= $var_msg; ?>
                    </div>
                <?php } ?>
                <div class="driver-vehicles-page">
                    <div class="<?php echo isset($class_name) ? $class_name : ''; ?>">
                        <div class="profile-req-doc profile-req-doc-driver driver-document-action-page">
                            <div class="profile-req-doc-inner">
                                <ul>
                                    <?php for ($i = 0; $i < $count_all_doc; $i++) {
                                        if ($db_userdoc[$i]['eType'] == 'UberX') {
                                            $etypeName = 'Service';
                                        } else {
                                            $etypeName = $db_userdoc[$i]['eType'];
                                        }
                                        ?>
                                        <li>
                                            <div class="upload-block">
                                                <div class="panel panel-default upload-clicking">
                                                    <input type="hidden" id="ex_status" value="<?php echo $db_userdoc[$i]['ex_status']; ?>">
                                                    <div class="panel-heading">
                                                        <strong>
                                                            <div>
                                                                <?php echo $db_userdoc[$i]['doc_name']; ?>
                                                                <?php if ($MODULES_OBJ->isEnableServiceTypeWiseProviderDocument() == "Yes" && $db_userdoc[$i]['vCategory'] != "" && $db_userdoc[$i]['isShowInfo'] == "Yes") { ?>
                                                                    <i class="fa fa-question-circle tooltip-new" data-placement="bottom" data-toggle="tooltip" data-original-title>
                                                                        <span class="tooltiptext-new"><?php echo $langage_lbl['LBL_DOCUMENT_FOR'] . " " . $db_userdoc[$i]['vCategory']; ?></span>
                                                                    </i>
                                                                <?php } ?>
                                                            </div>
                                                            <?php if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') { ?>
                                                                <!-- <div style="font-size: 10px;">(For <?= $etypeName; ?>)</div> -->
                                                            <?php } ?>
                                                        </strong>
                                                    </div>
                                                    <input type="hidden" id="doc_id" value="<?php $db_userdoc[$i]['doc_file']; ?>">
                                                    <div class="panel-body1">
                                                        <div class="doc-image-block">
                                                            <?php if ($db_userdoc[$i]['doc_file'] != '') { ?><?php
                                                                $file_ext = $UPLOAD_OBJ->GetFileExtension($db_userdoc[$i]['doc_file']);
                                                                if ($file_ext == 'is_image') {
                                                                    ?>
                                                                    <a href="<?= $tconfig["tsite_upload_driver_doc"] . '/' . $id . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank">
                                                                        <img src="<?= $tconfig["tsite_upload_driver_doc"] . '/' . $id . '/' . $db_userdoc[$i]['doc_file'] ?>" alt="<?= $db_userdoc[$i]['doc_name']; ?> Image"/>
                                                                    </a>
                                                                <?php } else {
                                                                    ?>
                                                                    <a href="<?= $tconfig["tsite_upload_driver_doc"] . '/' . $id . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><?php echo $db_userdoc[$i]['doc_name']; ?></a>
                                                                <?php } ?><?php
                                                            } else {
                                                                echo '<p>' . $db_userdoc[$i]['doc_name'] . " <span  class='no-record'>" . $langage_lbl['LBL_NOT_FOUND'] . '</span></p>';
                                                            }
                                                            ?>
                                                        </div>
                                                        <br/> <b>
                                                            <button class="btn btn-info gen-btn" data-toggle="modal" data-target="#uiModal" id="custId" onClick="setModel001('<?php echo $db_userdoc[$i]['masterid']; ?>')">
                                                                <?php
                                                                if ($db_userdoc[$i]['doc_file'] != '') {
                                                                    echo $db_userdoc[$i]['doc_name'];
                                                                } else {
                                                                    echo $db_userdoc[$i]['doc_name'];
                                                                }
                                                                ?>
                                                            </button>
                                                        </b>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="custom-modal-main fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="custom-modal">
                        <div class="model-content image-upload-1">
                            <div class="fetched-data"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="page-contant">
        <div class="page-contant-inner">
            <h2 class="header-page trip-detail driver-detail1"></h2>
            <!-- driver vehicles page -->
            <div style="clear:both;"></div>
        </div>
    </div>
    <!-- End:contact page-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode']; ?>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js"></script>
<?php if ($lang != 'en') { ?>
    <!-- <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
    <? include_once('otherlang_validation.php'); ?><?php } ?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/moment.min.js"></script>
<!-- Start :: Datepicker css-->
<!-- <link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css" />
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script src="assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script> -->
<!-- Start :: Datepicker-->
<script src="assets/js/jquery-ui.min.js"></script>
<script type="text/javascript">
    function setModel001(idVal) {
        var id = '<?php echo $id; ?>';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>cx-driver_document_fetch1.php',
            'AJAX_DATA': 'rowid=' + idVal + '-' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $('#uiModal').modal('show');
                $('.fetched-data').html(data);
            }
            else {
                console.log(response.result);
            }
        });
    }

    function confirm_delete(id) {
        var tsite_url = '<?php echo $tconfig["tsite_url"]; ?>';
        if (id != '') {
            var confirm_ans = confirm('<?=addslashes($langage_lbl['LBL_DELETE_VEHICLE_CONFIRM_MSG']);?>');
            if (confirm_ans == true) {
                window.location.href = "vehicle.php?action=delete&id=" + id;
            }
        }
        //document.getElementById(id).submit();
    }
</script>
<!-- End: Footer Script -->
</body>
</html>

