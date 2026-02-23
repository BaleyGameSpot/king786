<?php
include_once 'common.php';
$script = "Profile";
$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';

/*$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';*/
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

$eSystem = isset($_SESSION['sess_eSystem']) ? $_SESSION['sess_eSystem'] : 'General';
$new = '';
$db_doc = array();
if (isset($_SESSION['sess_new'])) {
    $new = $_SESSION['sess_new'];
    unset($_SESSION['sess_new']);
}


$AUTH_OBJ->checkMemberAuthentication();

$db_country = $obj->MySQLSelect("select * from country where eStatus = 'Active' ORDER BY vCountry ASC ");

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $CONFIG_OBJ->getConfigurations("configurations", "SHOW_CITY_FIELD");
}
if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol)) {
    $db_currency = array();
    $currencyData['vName'] = $vSystemDefaultCurrencyName;
    $currencyData['vSymbol'] = $vSystemDefaultCurrencySymbol;
    $db_currency[] = $currencyData;
} else {
    $db_currency = $obj->MySQLSelect("select * from currency where eStatus = 'Active' ORDER BY vName ASC ");
}
$access = 'company,driver';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($access, $url);
/* if($APP_TYPE == 'Ride-Delivery'){
  $eTypeQuery = " AND (eType='Ride' OR eType='Delivery')";
  } else if($APP_TYPE == 'Ride-Delivery-UberX'){
  $eTypeQuery = " AND (eType='Ride' OR eType='Delivery' OR eType='UberX')";
  } else {
  $eTypeQuery = " AND eType='".$APP_TYPE."'";
  } */
if ($user == 'company') {
    $sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
    $sql = "SELECT vNoc,vCerti,eSystem from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_doc = $obj->MySQLSelect($sql);
    $docType = $db_doc[0]['eSystem'] == 'DeliverAll' ? 'store' : 'company';
    // echo '<pre>';print_r($db_user);die;
    //$sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_" . $_SESSION['sess_lang'] . "  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $_SESSION['sess_iUserId'] . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='".$docType."'  and dm.doc_usertype= dl.doc_usertype and dm.status='Active' and (dm.country ='" . $db_user[0]['vCountry'] . "' OR dm.country ='All')";
    $sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_" . $_SESSION['sess_lang'] . "  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $_SESSION['sess_iUserId'] . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='" . $docType . "' and dm.status='Active' and (dm.country ='" . $db_user[0]['vCountry'] . "' OR dm.country ='All') ORDER BY dm.iDisplayOrder ASC ";
    $db_userdoc = $obj->MySQLSelect($sql);
    $count_all_doc = 0;
    if (!empty($db_userdoc)) {
        $count_all_doc = scount($db_userdoc);
    }
}
$othercompany = 0;
if (empty($db_user[0]['tSessionId'])) {
    $dbCompanySessionData = $obj->MySQLSelect("SELECT tSessionId,iCompanyId FROM `company` WHERE tSessionId != '' LIMIT 0,1");
    $db_user[0]['tSessionId'] = $dbCompanySessionData[0]['tSessionId'];
    $othercompany = 1;
}
if ($user == 'driver') {
    $sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
    $db_user = $obj->MySQLSelect($sql);
    $sql = "SELECT dm.doc_masterid masterid, dm.doc_usertype ,dm.doc_name_" . $_SESSION['sess_lang'] . "  as d_name , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" . $_SESSION['sess_iUserId'] . "' ) dl on dl.doc_masterid=dm.doc_masterid where dm.doc_usertype='driver' and dm.status='Active' and (dm.country ='" . $db_user[0]['vCountry'] . "' OR dm.country ='All') ORDER BY dm.iDisplayOrder ASC";
    $db_userdoc = $obj->MySQLSelect($sql);
    $count_all_doc = scount($db_userdoc);
    if ($SITE_VERSION == "v5") {
        $data_driver_pref = Get_User_Preferences($_SESSION['sess_iUserId']);
    }
}
$noc = $certi = $licence = '';
if (scount($db_doc) > 0) {
    $noc = $db_doc[0]['vNoc'];
    $certi = $db_doc[0]['vCerti'];
    if ($user == 'driver') {
        $licence = $db_doc[0]['vLicence'];
    }
}
if (scount($Data_ALL_langArr) > 0) {
    $db_lang = array();
    for ($dl = 0; $dl < scount($Data_ALL_langArr); $dl++) {
        if (strtoupper($Data_ALL_langArr[$dl]['eStatus']) == "ACTIVE") {
            $db_lang[] = $Data_ALL_langArr[$dl];
        }
    }
} else {
    $db_lang = $obj->MySQLSelect("select * from language_master where eStatus = 'Active' ORDER BY vTitle ASC ");
}
$lang = "";
for ($i = 0; $i < scount($db_lang); $i++) {
    if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) {
        $lang_user = $db_lang[$i]['vTitle'];
    }
}
for ($i = 0; $i < scount($db_country); $i++) {
    if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) {
        $country = $db_country[$i]['vCountry'];
    }
}

if ($action = 'document' && isset($_POST['doc_type'])) {
    $expDate = $_POST['dLicenceExp'];
    // if (SITE_TYPE == 'Demo') { // commented After Change given by KS sir Done by NModi on 11-12-20
    //     header("location:profile?success=2&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    //     exit;
    // }
    $user = $_POST['user'];
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
    if ($image_name == "") {
        if ($expDate != "") {
            if ($user == 'company') {
                $sql = "select ex_date from document_list where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='" . $docType . "'  and doc_masterid='" . $masterid . "'";
            } else {
                $sql = "select ex_date from document_list where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver' and doc_masterid='" . $masterid . "'";
            }
            $query = $obj->MySQLSelect($sql);
            $fetch = $query[0];
            if ($fetch['ex_date'] == $expDate) {
                if ($user == 'company') {
                    $sql = "UPDATE `document_list` SET  ex_date='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='" . $docType . "' and doc_masterid='" . $masterid . "'";
                } else {
                    $sql = "UPDATE `document_list` SET  ex_date='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver' and doc_masterid='" . $masterid . "'";
                }
            } else {
                if ($user == 'company') {
                    $sql = "INSERT INTO `document_list` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '" . $_REQUEST['doc_type'] . "', '" . $docType . "', '" . $_REQUEST['id'] . "', '" . $expDate . "', '', 'Inactive', CURRENT_TIMESTAMP)";
                } else {
                    $sql = " INSERT INTO `document_list` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '', 'Inactive', CURRENT_TIMESTAMP)";
                }
            }
            $query = $obj->sql_query($sql);
        }
        $var_msg = $langage_lbl['LBL_UPLOAD_IMG_ERROR'];
        $_SESSION['message_code'] = "0";
        $_SESSION['session_msg'] = $var_msg;
        header("location:profile?id=" . $_REQUEST['id']);
        //header("location:profile?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    }
    if ($_FILES['driver_doc']['name'] != "") {
        require_once(SITE_ADMIN_URL."/library/validation.class.php");
        $validobj = new validation();

        /* $check_file_query = "select iDriverId,vNoc from company where iDriverId=" . $_REQUEST['id'];
          $check_file = $obj->sql_query($check_file_query);
          $check_file['vNoc'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vNoc']; */
        $filecheck = basename($_FILES['driver_doc']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
        //     $flag_error = 1;
        //     $var_msg = $langage_lbl['LBL_WRONG_FILE_SELECTED_TXT'];
        // }

        $docUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_docs_file_extensions_validation_extensions"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['driver_doc'], $tconfig["tsite_upload_docs_file_extensions"], $docUploadingExtenstionMsg); 

        if ($error) {
            $_SESSION['message_code'] = "0";
            $_SESSION['session_msg'] = $error;
            header("location:profile?id=" . $_REQUEST['id']);
            //header("location:profile?success=0&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
            //getPostForm($_POST, $var_msg, "driver_document_action.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
            exit;
        } else {
            $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            // $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
            $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = $tconfig["tsite_upload_docs_file_extensions"]);
            $vImage = $vFile[0];
            $var_msg = $langage_lbl['LBL_UPLOAD_MSG'];
            $tbl = 'document_list';
            if ($user == 'company') {
                $sql = "select doc_id,doc_file from  " . $tbl . "  where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='" . $docType . "'  and doc_masterid=" . $_REQUEST['doc_type'];
            } else {
                $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
            }
            $db_data = $obj->MySQLSelect($sql);
            $q = "INSERT INTO ";
            $where = '';
            if (scount($db_data) > 0) {
                if ($user == 'company') {
                    $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='" . $docType . "'  and doc_masterid=" . $_REQUEST['doc_type'];
                    $q = "UPDATE ";
                    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
                } else {
                    $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
                    $q = "UPDATE ";
                    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
                }
            } else {
                if ($user == 'company') {
                    $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
                        . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', '" . $docType . "', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive', CURRENT_TIMESTAMP)";
                } else {
                    $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
                        . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive',     CURRENT_TIMESTAMP)";
                }
            }
            $obj->sql_query($query);
            ###### Email #######
            if ($user == 'company') {
                $maildata['NAME'] = $db_user[0]['vCompany'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'] . ")";
            } else {
                $maildata['NAME'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'] . " (" . $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'] . ")";
            }


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
            if ($user == 'company') {
                $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_COMPANY'];
            } else {
                $maildata['DOCUMENTFOR'] = $langage_lbl['LBL_DOCUMNET_UPLOAD_BY_DRIVER'];
            }
            $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD_WEB", $maildata);
            if ($user == 'driver') {
                $sqlquery = "SELECT vEmail,vCompany FROM company WHERE iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
                $Companydata = $obj->MySQLSelect($sqlquery);
                $maildata['COMPANYEMAIL'] = $Companydata[0]['vEmail'];
                $maildata['COMPANYNAME'] = $Companydata[0]['vCompany'];
                $maildata['EMAIL_NAME'] = $Companydata[0]['vCompany'];
                $COMM_MEDIA_OBJ->SendMailToMember("DOCCUMENT_UPLOAD_WEB_COMPANY", $maildata);
            }
            #######Email ##########
            //Start :: Log Data Save
            $vNocPath = $vImage;
            save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'company', 'noc', $vNocPath);
            //End :: Log Data Save
            // Start :: Status in edit a Document upload time
            // $set_value = "`eStatus` ='inactive'";
            //estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
            // End :: Status in edit a Document upload time
            $_SESSION['message_code'] = "1";
            $_SESSION['session_msg'] = $var_msg;
            header("location:profile?id=" . $_REQUEST['id']);
            //header("location:profile?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        }
    } else {
        $vImage = $_POST['driver_doc_hidden'];
        $var_msg = $langage_lbl['LBL_UPLOAD_DOC_SUCCESS_UPLOAD_DOC'];
        $tbl = 'document_list';
        if ($user == 'company') {
            $sql = "select doc_id,doc_file from  " . $tbl . "  where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='" . $docType . "'  and doc_masterid=" . $_REQUEST['doc_type'];
        } else {
            $sql = "select doc_id from  " . $tbl . "  where doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
        }
        $db_data = $obj->MySQLSelect($sql);
        $q = "INSERT INTO ";
        $where = '';
        if (scount($db_data) > 0) {
            if ($user == 'company') {
                $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='" . $docType . "'  and doc_masterid=" . $_REQUEST['doc_type'];
                $q = "UPDATE ";
                $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
            } else {
                $query = "UPDATE `" . $tbl . "` SET `doc_file`='" . $vImage . "' , `ex_date`='" . $expDate . "' WHERE doc_userid='" . $_REQUEST['id'] . "' and doc_usertype='driver'  and doc_masterid=" . $_REQUEST['doc_type'];
                $q = "UPDATE ";
                $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
            }
        } else {
            if ($user == 'company') {
                $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) " . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', '" . $docType . "', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive', CURRENT_TIMESTAMP)";
            } else {
                $query = " INSERT INTO `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) " . "VALUES " . "( '" . $_REQUEST['doc_type'] . "', 'driver', '" . $_REQUEST['id'] . "', '" . $expDate . "', '" . $vImage . "', 'Inactive',     CURRENT_TIMESTAMP)";
            }
        }
        $obj->sql_query($query);
        $vNocPath = $vImage;
        save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'company', 'noc', $vNocPath);
        $_SESSION['message_code'] = "1";
        $_SESSION['session_msg'] = $var_msg;
        header("location:profile?id=" . $_REQUEST['id']);
        // header("location:profile?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
    }
}
$test = array();
if (scount($db_userdoc) > 0) {
    for ($i = 0; $i < scount($db_userdoc); $i++) {
        if ($db_userdoc[$i]['doc_file'] != "") {
            $test[] = '1';
        }
    }
}
if (scount($test) == scount($db_userdoc)) {
    $UploadDocuments = 'Yes';
} else {
    $UploadDocuments = 'No';
}
$companyLabel = $langage_lbl['LBL_COMPANY_SIGNUP'];
if (strtolower($eSystem) == "deliverall") {
    $companyLabel = $langage_lbl['LBL_STORE_NAME'];
} else if (strtolower($eSystem) == "organization") {
    $companyLabel = $langage_lbl['LBL_ORGANIZATION_NAME_WEB'];
}

$languageArr = array();
if (isset($_SESSION['sess_eSystem']) && $_SESSION['sess_eSystem'] == "DeliverAll") {
    $languageArr['LBL_TRY_AGAIN_LATER'] = $langage_lbl['LBL_MISSED_DETAILS_MSG'];
    $languageArr['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'] = $langage_lbl['LBL_NO_CUISINES_AVAILABLE_FOR_RESTAURANT'];
    $languageArr['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'] = $langage_lbl['LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT'];
}
$languageArr['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'] = $langage_lbl['LBL_DELIVER_ALL_SERVICE_DISABLE_TXT'];
$languageArr['LBL_INFO_UPDATED_TXT'] = $langage_lbl['LBL_INFO_UPDATED_TXT'];
$languageArr['SESSION_OUT'] = "SESSION_OUT";

$json_lang = json_encode($languageArr);
$acceptOrder = $MODULES_OBJ->isEnableAcceptingOrderFromWeb();
?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_HEADER_PROFILE_TXT']; ?> </title>
    <!-- Default Top Script and css -->
    <?php include_once "top/top_script.php"; ?>
    <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css">
    <link rel="stylesheet" href="assets/css/modal_alert.css"/>
    <?php if ($user == 'driver' && $APP_TYPE == 'UberX') { ?>
        <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>
    <?php } ?>
    <!-- End: Default Top Script and css-->

    <style type="text/css">
        .fileupload .newrow.has-error .help-block.error{
            position: relative;
            width: 250px;
            text-wrap: wrap;
            line-height: 14px;
            padding-bottom: 10px;
        }
        .error{
            text-align: center;
        }

        #driver_doc-error{
            width: 180px !important;
        }
    
    
    </style>
</head>
<body>
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once "top/left_menu.php"; ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once "top/header_topbar.php"; ?>
    <!-- End: Top Menu-->
    <!-- contact page-->
    <section class="profile-section">
        <div class="profile-section-inner">
            <?php
            if ($user == 'company') {
                if (SITE_TYPE == 'Demo') {
                    ?>
                    <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                        <?php if ($docType == "company") { ?>
                            <p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p><?php } ?>
                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>
                        <?php if (isset($db_user[0]['iServiceId']) && $db_user[0]['iServiceId'] == 0) { ?>
                            <p><?= $langage_lbl['LBL_STEP1']; ?></p><!--    <p><?= $langage_lbl['LBL_STEP2']; ?></p>-->
                            <p><?= $langage_lbl['LBL_STEP3']; ?></p>
                        <?php } else { ?>
                            <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p><p>
                                - <?= $langage_lbl['LBL_STEP1_DL_DEMO']; ?></p><p>
                                - <?= $langage_lbl['LBL_STEP3_DL_DEMO']; ?></p><p>
                                - <?= $langage_lbl['LBL_STEP2_DL_DEMO']; ?></p>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                        <?php if ($docType == "company") { ?>
                            <p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p>
                            <?php if ($UploadDocuments == 'No') { ?>
                                <p><?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW']; ?></p>
                            <?php } ?><?php }
                        if ($docType == "company") { ?>
                            <p><?= $langage_lbl['LBL_ALSO_ADD_DRIVERS']; ?></p>
                            <p><?= $langage_lbl['LBL_EITHER_YOU_AS_A_COMPANY_DRIVER']; ?></p>
                        <?php } else { ?>
                            <p><?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW_DL']; ?></p>
                            <p><?= $langage_lbl['LBL_STEP1_DL']; ?></p><p><?= $langage_lbl['LBL_STEP3_DL']; ?></p>
                            <p><?= $langage_lbl['LBL_STEP2_DL']; ?></p>
                        <?php } ?>
                    </div>
                    <?php
                }
            } else {
                ?><?php if (SITE_TYPE == 'Demo') { ?>
                    <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                        <p><?= $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER']; ?></p>
                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION_ADDVEHICLE']; ?></p>
                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM_DRIVER']; ?></p>
                    </div>
                    <?php
                } else {
                    if ($UploadDocuments == 'No') {
                        ?>
                        <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                            <p>
                                <?php
                                if (isset($_REQUEST['first']) && $_REQUEST['first'] == 'yes') {
                                    echo $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER'];
                                }
                                ?>
                            </p>
                            <p>
                                <?php
                                if ($db_user[0]['eStatus'] == 'inactive') {
                                    echo $langage_lbl['LBL_INACTIVE_DRIVER_MESSAGE'];
                                }
                                ?>
                            </p>
                            <p>
                                <?= $langage_lbl['LBL_KINDLY_PROVIDE_BELOW_VISIBLE']; ?>
                            </p>
                        </div>
                    <?php } else if ($db_user[0]['eStatus'] == 'inactive') { ?>
                        <div class="demo-warning" style="width: 100%; margin-bottom:30px;">
                            <p>
                                <?= $langage_lbl['LBL_INACTIVE_DRIVER_MESSAGE']; ?>
                            </p>
                        </div>
                        <?php
                    }
                }
            }
            ?>
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_PROFILE_TITLE_TXT']; ?></h1>
                </div>
                <div style="width:100%">
                    <?php if ($success == 1) { ?>
                        <div class="alert alert-success" style="width: 100%;margin: 0px 0 30px 0;">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <!-- <?= $langage_lbl['LBL_PROFILE_UPDATED']; ?> -->
                            <?php $var_msg = ($var_msg != "" && $var_msg != "1") ? $var_msg : $langage_lbl['LBL_PROFILE_UPDATED'];
                            echo $var_msg;
                            ?>
                        </div>
                    <?php } else if ($success == 2) {
                        ?>
                        <div class="alert alert-danger">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                    <?php } else if ($success == 0 && $var_msg != "") {
                        ?>
                        <div class="alert alert-danger msgs_hide">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $var_msg; ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="profile-image">
                    <?php
                    if ($user == 'company') {
                        $img_path = $tconfig["tsite_upload_images_compnay"];
                        $profileImgpath = $tconfig["tsite_upload_images_compnay_path"];
                    } else if ($user == 'driver') {
                        $img_path = $tconfig["tsite_upload_images_driver"];
                        $profileImgpath = $tconfig["tsite_upload_images_driver_path"];
                    }
                    if (($db_data[0]['vImage'] == 'NONE' || $db_data[0]['vImage'] == '') || !file_exists($profileImgpath . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'])) {
                        ?>
                        <img src="assets/img/profile-user-img.png" alt="">
                        <?php
                    } else { ?>
                        <img src="<?= $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'] ?>" style="height:150px;"/>
                    <?php } ?>
                    <a data-toggle="modal" data-target="#uiModal_4"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                </div>
                <div class="profile-block">
                    <div class="profile-caption-header">
                        <label><?= $langage_lbl['LBL_HELLO'] . ", " . cleanall(htmlspecialchars($db_user[0]['vCompany'])) . ' ' . cleanall(htmlspecialchars($db_user[0]['vLastName'])); ?></label>
                        <div>
                            <button class="profile_edit_btn"><?= $langage_lbl['LBL_EDIT_PROFILE_TXT']; ?></button>
                            <!------------------for delete account----------------->
                          <!--   <button id = "profile_Delete_btn" class="profile_Delete_btn"><?= $langage_lbl['LBL_DELETE_ACCOUNT_TXT']; ?></button> -->
                            <!------------------for delete account----------------->
                        </div>
                    </div>
                    <div class="profile-detail">
                        <?php if ($acceptOrder > 0 && $_SESSION['sess_eSystem'] == "DeliverAll") {
                            $radiobtn = "";
                            if ($db_user[0]['eStatus'] != 'Active') {
                                $radiobtn = "disabled='disabled'";
                            }
                            ?>
                            <div class="profile-column">
                                <i class="icon-accept" aria-hidden="true">
                                    <img src="assets/img/choices_b.svg" alt="">
                                </i>
                                <div class="data_info">
                                    <strong><?= $langage_lbl['LBL_ACCEPTING_ORDERS']; ?></strong> <span>
                                        <span class="toggle-switch">
                                            <input <?= $radiobtn ?> type="checkbox" onchange="return autoAcceptStatus(this);" name="accept_order" <?= ($db_user[0]['eAvailable'] == 'Yes') ? 'checked' : ''; ?> data-status="<?= ($db_user[0]['eAvailable'] == "Yes") ? 'No' : 'Yes' ?>" id="accept_order" class="valid" aria-invalid="false">
                                            <span class="toggle-base"></span> </span> </span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if ($db_user[0]['vEmail'] != "") { ?>
                            <div class="profile-column">
                                <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                <div class="data_info">
                                    <strong><?= $langage_lbl['LBL_EMAIL_LBL_TXT']; ?></strong>
                                    <span><?= $db_user[0]['vEmail']; ?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="profile-column">
                            <i class="icon-call" aria-hidden="true"></i>
                            <div class="data_info">
                                <strong><?= $langage_lbl['LBL_PHONE']; ?></strong>
                                <span dir="ltr"><?php if (!empty($db_user[0]['vPhone'])) { ?>(+<?= $db_user[0]['vCode'] ?>) <?= $db_user[0]['vPhone'] ?><?php } ?></span>
                            </div>
                        </div>
                        <div class="profile-column">
                            <i class="icon-location" aria-hidden="true"></i>
                            <div class="data_info">
                                <strong><?= $langage_lbl['LBL_COUNTRY_TXT']; ?></strong>
                                <span><?php if ($db_user[0]['vCountry'] != "") { ?><?= $country ?><?php } ?></span>
                            </div>
                        </div>
                        <?php if ($user == 'driver') {
                            if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>
                                <div class="profile-column">
                                    <i class="icon-refer" aria-hidden="true"></i>
                                    <div class="data_info">
                                        <strong><?php echo $langage_lbl['LBL_REFERAL_CODE_SIGNUP']; ?></strong>
                                        <span><?= $db_user[0]['vRefCode'] ?></span>
                                    </div>
                                </div>
                            <?php }
                        } ?>
                    </div>
                </div>
            </div>
            <form id="frm1" method="post" action="javascript:void(0);" class="general-form profile_edit profile-caption addVehicleCX ">
                <input type="hidden" class="edit" name="action" value="allInOne">
                <div class="partation">
                    <h1><?= $langage_lbl['LBL_PERSONAL_INFO_TXT']; ?></h1>
                    <input type="hidden" name="uid" id="u_id1" value="<?= $_SESSION['sess_iUserId']; ?>">
                    <input type="hidden" name="user_type" id="user_type" value="<?= $user; ?>">
                    <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?> <span class="red">*</span></label>
                            <input type="email" id="in_email" class="edit-profile-detail-form-input" value="<?= $db_user[0]['vEmail'] ?>" name="email" <?= isset($db_user[0]['vEmail']) ? '' : ''; ?> required title="Please enter valid email address">
                            <div class="required-label" id="emailCheck"></div>
                        </div>
                    <?php } else { ?>
                        <div class="form-group half newrow phone-column">
                            <label><?= $langage_lbl['LBL_Phone_Number']; ?><span class="red">*</span></label>
                            <input type="text" class="input-phNumber1 phonecode" id="code" name="vCode" value="<?= $db_user[0]['vCode'] ?>" readonly>
                            <input name="phone" id="phone" type="text" value="<?= $db_user[0]['vPhone'] ?>" class="edit-profile-detail-form-input input-phNumber2" title="Please enter proper phone number." onKeyUp="return isNumberKey(event);" onkeypress="return isNumberKey(event);" onblur="return isNumberKey(event);" required/>
                        </div>
                    <?php } ?>
                    <?php
                    if ($user == 'driver') {
                        ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?><span class="red">*</span>
                            </label>
                            <input type="text" class="edit-profile-detail-form-input" value="<?= cleanall(htmlspecialchars($db_user[0]['vName'])); ?>" name="name" required>
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_YOUR_LAST_NAME']; ?><span class="red">*</span></label>
                            <input type="text" class="edit-profile-detail-form-input" value="<?= cleanall(htmlspecialchars($db_user[0]['vLastName'])); ?>" name="lname" required>
                        </div>
                        <?php
                    } else if ($user == 'company') {
                        ?>
                        <div class="form-group half newrow">
                            <label><?= $companyLabel; ?><span class="red">*</span></label>
                            <input type="text" class="edit-profile-detail-form-input" value="<?= cleanall(htmlspecialchars($db_user[0]['vCompany'])); ?>" name="vCompany" required>
                        </div>
                    <?php } ?>
                    <!-- <div class="form-group half">
                         <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?><span class="red">*</span></label> 
                        <select class="custom-select-new vCountry" name = 'vCountry' onChange="changeCode(this.value);" required>
                            <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option>
                            <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="required-label" id="vCountryCheck"></div>                                           
                    </div>   -->
                    <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>
                        <div class="form-group half newrow phone-column">
                            <label><?= $langage_lbl['LBL_Phone_Number']; ?><span class="red">*</span></label>
                            <input type="text" class="input-phNumber1 phonecode" id="code" name="vCode" value="<?= $db_user[0]['vCode'] ?>" readonly>
                            <input name="phone" id="phone" type="text" value="<?= $db_user[0]['vPhone'] ?>" class="edit-profile-detail-form-input input-phNumber2" title="Please enter proper phone number." onKeyUp="return isNumberKey(event);" onkeypress="return isNumberKey(event);" onblur="return isNumberKey(event);" required/>
                        </div>
                    <?php } else { ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?> </label>
                            <input type="email" id="in_email" class="edit-profile-detail-form-input" value="<?= $db_user[0]['vEmail'] ?>" name="email" <?= isset($db_user[0]['vEmail']) ? '' : ''; ?> title="Please enter valid email address">
                            <div class="required-label" id="emailCheck"></div>
                        </div>
                    <?php } ?>
                    <?php if (strtolower($eSystem) == 'general') { ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_VAT_NUMBER_SIGNUP']; ?></label>
                            <input type="text" class="form-control" name="vVatNum" id="vVatNum" value="<?= $db_user[0]['vVat']; ?>">
                        </div>
                    <?php } ?>
                </div>
                <div class="partation">
                    <h1><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?></h1>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?><?php if ($user == 'company') { ?>
                                <span class="red">*</span><?php } ?> </label>
                        <input type="text" class="profile-address-input" value="<?= cleanall(htmlspecialchars($db_user[0]['vCaddress'])); ?>" name="address1" <?php if ($user == 'company') { ?>required<?php } ?>>
                    </div>
                    <div class="form-group half newrow floating">
                        <!--<select class="custom-select-new vCountry" name = 'vCountry' onChange="changeCode(this.value);" required>
                        <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option>
                        <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                        <option value = "<?= $db_country[$i]['vCountryCode'] ?>" <?php if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>><?= $db_country[$i]['vCountry'] ?></option>
                    <?php } ?>
                    </select>-->
                        <!--<div class="required-label" id="vCountryCheck"></div>      -->
                        <label><?= $langage_lbl['LBL_SELECT_CONTRY']; ?><?php if ($user == 'company') { ?>
                                <span class="red">*</span><?php } ?> </label>
                        <?php
                        if (scount($db_country) > 1) {
                            $style = "";
                        } else {
                            $style = " disabled=disabled";
                        } ?>
                        <select <?= $style ?> class="custom-select-new vCountry" name="vCountry" id="vCountry" onChange="changeCode(this.value); setState(this.value, '');" required>
                            <!--  <?php if (scount($db_country) > 1) { ?>
                                    <option value=""><?= $langage_lbl['LBL_SELECT_CONTRY']; ?></option>
                                <?php } ?> -->
                            <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                <option <?php if ($db_user[0]['vCountry'] == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?> value="<?= $db_country[$i]['vCountryCode'] ?>"><?= $db_country[$i]['vCountry'] ?></option>
                            <?php } ?>
                        </select>
                        <div class="required-label" id="vCountryCheck"></div>
                    </div>
                    <div class="form-group half newrow floating">
                        <label id="selectstatelblc"></label>
                        <select class="form-control" name='vState' id="vState" onChange="setCity(this.value, '');">
                            <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option>
                        </select>
                    </div>
                    <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
                        <div class="form-group half newrow floating">
                            <label><?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_CITY_TXT']; ?> </label>
                            <select class="form-control" name='vCity' id="vCity">
                                <!-- <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option> -->
                            </select>
                        </div>
                    <?php } ?>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_ZIP_CODE']; ?></label>
                        <input type="text" class="profile-address-input" value="<?= $db_user[0]['vZip'] ?>" name="vZipcode">
                    </div>
                </div>
                <div class="partation">
                    <h1><?= $langage_lbl['LBL_PROFILE_PASSWORD_LBL_TXT']; ?></h1>
                    <?php if ($db_user[0]['vPassword'] != "") { ?>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_CURR_PASS_HEADER']; ?> </label>
                            <input type="password" class="input-box" name="cpass" id="cpass" onkeyup="nospaces(this)" autocomplete="new-password">
                        </div>
                    <?php } ?>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_NEW_PASSWORD_TXT']; ?></label>
                        <input type="password" class="input-box" name="npass" id="npass" onkeyup="nospaces(this)">
                    </div>
                    <div class="form-group half newrow">
                        <label><?= $langage_lbl['LBL_Confirm_New_Password']; ?></label>
                        <input type="password" class="input-box" name="ncpass" id="ncpass" onkeyup="nospaces(this)">
                    </div>
                </div>
                <!-- bank detail -->
                <?php if ($user == 'driver') { ?>
                    <div class="partation">
                        <h1><?= $langage_lbl['LBL_BANK_DETAILS_TXT']; ?></h1>
                        <!-- <input  type="hidden" class="edit" name="action" value="bankdetail"> -->
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_PAYMENT_EMAIL_TXT']; ?><span class="red">*</span></label>
                            <input type="email" class="profile-address-input" value="<?= $db_user[0]['vPaymentEmail'] ?>" name="vPaymentEmail" required>
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_PROFILE_BANK_HOLDER_TXT']; ?> </label>
                            <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBankAccountHolderName'] ?>" name="vBankAccountHolderName">
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_ACCOUNT_NUMBER']; ?></label>
                            <input type="text" class="profile-address-input" value="<?= $db_user[0]['vAccountNumber'] ?>" name="vAccountNumber">
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_BANK_NAME']; ?> </label>
                            <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBankName'] ?>" name="vBankName">
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_BANK_LOCATION']; ?></label>
                            <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBankLocation'] ?>" name="vBankLocation">
                        </div>
                        <div class="form-group half newrow">
                            <label><?= $langage_lbl['LBL_BIC_SWIFT_CODE']; ?></label>
                            <input type="text" class="profile-address-input" value="<?= $db_user[0]['vBIC_SWIFT_Code'] ?>" name="vBIC_SWIFT_Code">
                        </div>
                    </div>
                <?php } ?>
                <div class="partation">
                    <?php
                    // $other variable added by NM on 5/8/20
                    $other = 0;
                    if (scount($db_lang) <= 1) {
                        ?>
                        <input name="lang1" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode']; ?>"/>
                    <?php } else {
                        $other = 1;
                        ?>
                        <h1 class='other_info'><?= $langage_lbl['LBL_OTHER_INFO_TXT']; ?></h1>
                        <div class="form-group half newrow floating">
                            <label><?= $langage_lbl['LBL_SELECT_LANGUAGE_TXT']; ?> </label>
                            <select name="lang1" class="custom-select-new profile-language-input">
                                <?php
                                for ($i = 0; $i < scount($db_lang); $i++) {
                                    ?>
                                    <option value="<?= $db_lang[$i]['vCode'] ?>" <?php if ($db_user[0]['vLang'] == $db_lang[$i]['vCode']) { ?> selected <?php } ?>><?php echo $db_lang[$i]['vTitle']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php }
                    if ($user == 'driver') { ?><?php if (scount($db_currency) <= 1) {
                        ?>
                        <input name="vCurrencyDriver" type="hidden" class="create-account-input" value="<?php echo $db_currency[0]['vName']; ?>"/>
                    <?php } else {
                        $other = 1;
                        ?>
                        <div class="form-group half newrow">
                            <!-- <label><?= $langage_lbl['LBL_SELECT_CURRENCY']; ?><span class="red">*</span></label> -->
                            <select class="custom-select-new vCurrencyDriver" name='vCurrencyDriver' required>
                                <option value="">--select--</option>
                                <?php for ($i = 0; $i < scount($db_currency); $i++) { ?>
                                    <option value="<?= $db_currency[$i]['vName'] ?>" <?php if ($db_user[0]['vCurrencyDriver'] == $db_currency[$i]['vName']) { ?>selected<?php } ?>><?= $db_currency[$i]['vName'] ?></option>
                                <?php } ?>
                            </select>
                            <div class="required-label" id="vCurrencyDriverCheck"></div>
                        </div>
                    <?php }
                    } ?>

                    <?php if ($user == 'driver' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL != 'Yes') {
                        $other = 1;
                        ?>
                        <div class="form-group full has-textarea">
                            <label><?= $langage_lbl['LBL_PROFILE_DESCRIPTION']; ?></label>
                            <textarea name="tProfileDescription" rows="8" cols="40" class="form-control" id="tProfileDescription"><?= $db_user[0]['tProfileDescription'] ?></textarea>
                        </div>
                    <?php } ?>
                    <input type="hidden" id="otherinfo" name="otherinfo" value="<?= $other; ?>">
                </div>
                <div class="button-block">
                    <div class="btn-hold">
                        <input name="save" id="validate_submit" type="submit" value="<?= $langage_lbl['LBL_Save']; ?>" style="padding: 15px 35px;">
                    </div>
                    <div class="btn-hold">
                        <input name="" id="hide-edit-profile-div" type="button" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>" class="gen-btn cancel_btn" style="padding: 15px 35px;">
                    </div>
                </div>
        </div>
        </form>
    </section>
    <section class="profile-earning reqDocs">
        <div class="profile-earning-inner">
            <?php if ($count_all_doc != 0) { ?>
                <h2><?= $langage_lbl['LBL_REQUIRED_DOCS']; ?></h2>
            <?php } ?>
            <ul>
                <!-- Document Start -->
                <?php if ($count_all_doc != 0) { ?>
                    <!--  <h3><?= $langage_lbl['LBL_REQUIRED_DOCS']; ?></h3> -->
                    <?php
                    for ($i = 0; $i < $count_all_doc; $i++) {
                        if ($db_userdoc[$i]['eType'] == 'UberX') {
                            $etypeName = 'Service';
                        } else {
                            $etypeName = $db_userdoc[$i]['eType'];
                        }
                        ?>
                        <li class="<?php echo !empty($class_name) ? $class_name : ''; ?>">
                            <div class="upload-block">
                                <div class="panel panel-default upload-clicking">
                                    <input type="hidden" id="ex_status" value="<?php echo $db_userdoc[$i]['ex_status']; ?>">
                                    <strong>
                                        <?php echo $db_userdoc[$i]['d_name']; ?><?php if (($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') && $user == 'driver') { ?><?php } ?>
                                    </strong>
                                    <input type="hidden" id="doc_id" value="<?php $db_userdoc[$i]['doc_file']; ?>">
                                    <div class="doc-image-block">
                                        <?php if ($db_userdoc[$i]['doc_file'] != '') { ?><?php
                                            $filecheck = $db_userdoc[$i]['doc_file'];
                                            $fileextarr = explode(".", $filecheck);
                                            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                                            $file_ext = $UPLOAD_OBJ->GetFileExtension($db_userdoc[$i]['doc_file']);
                                            $file_ext = file_ext_new($db_userdoc[$i]['doc_file']);
                                            if ($file_ext == 'is_image') {
                                                if ($user == 'company') {
                                                    $path = $tconfig["tsite_upload_compnay_doc"];
                                                } else {
                                                    $path = $tconfig["tsite_upload_driver_doc"];
                                                }
                                                ?>
                                                <?php
                                                if($ext == "heic") { ?>
                                                    <p>
                                                        <a href="<?= $tconfig_new . '/' . $sessionUserId . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><?php echo $db_userdoc[$i]['doc_name']; ?></a>
                                                    </p>
                                                <?php }
                                                else {
                                                    ?>
                                                <a href="<?= $path . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank">
                                                    <img src="<?= $path . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" style="cursor:pointer;" alt="<?= $db_userdoc[$i]['d_name']; ?> Image"/>
                                                </a>
                                                <?php } ?>
                                                <?php
                                            } else if ($file_ext == 'is_pdf') {
                                                $imgpath = $tconfig["tsite_url"] . '/assets/img/pdf.jpg';
                                                $resizeimgpath = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath . "&w=150";
                                                if ($user == 'company') {
                                                    $tconfig_new = $tconfig["tsite_upload_compnay_doc"];
                                                } else {
                                                    $tconfig_new = $tconfig["tsite_upload_driver_doc"];
                                                }
                                                ?>
                                                <p>
                                                    <a href="<?= $tconfig_new . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank">
                                                        <img attr-data='attr-data' src="<?= $resizeimgpath; ?>" style="cursor:pointer;" alt="<?php echo $db_userdoc[$i]['d_name']; ?>"/>
                                                    </a>
                                                </p>
                                                <?php
                                            } else {
                                                $imgpath = $tconfig["tsite_url"] . '/assets/img/document.png';
                                                $resizeimgpath = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath . "&w=150";
                                                if ($user == 'company') {
                                                    $tconfig_new = $tconfig["tsite_upload_compnay_doc"];
                                                } else {
                                                    $tconfig_new = $tconfig["tsite_upload_driver_doc"];
                                                }
                                                ?>
                                                <p>
                                                    <a href="<?= $tconfig_new . '/' . $_SESSION['sess_iUserId'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank">
                                                        <img attr-data='attr-data' src="<?= $resizeimgpath; ?>" style="cursor:pointer;" alt="<?php echo $db_userdoc[$i]['d_name']; ?>"/>
                                                    </a>
                                                </p>
                                            <?php } ?><?php
                                        } else {
                                            echo '<p><span>' . $db_userdoc[$i]['d_name'] . "</span><br> <span  class='no-record'>" . $langage_lbl['LBL_NOT_FOUND'] . '</span></p>';
                                        }
                                        ?>
                                        <br/> <b></b>
                                    </div>
                                    <button class="btn gen-btn" data-toggle="modal" data-target="#uiModal" id="custId" onClick="setModel001('<?php echo $db_userdoc[$i]['masterid']; ?>')">
                                        <?php
                                        if ($db_userdoc[$i]['doc_file'] != '') {
                                            echo $db_userdoc[$i]['d_name'];
                                        } else {
                                            echo $db_userdoc[$i]['d_name'];
                                        }
                                        ?>
                                    </button>
                                </div>
                            </div>
                        </li>
                    <?php } ?><?php } ?>
                <div class="col-lg-12">
                    <div class="custom-modal-main in  fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                        <div class="custom-modal">
                            <div class="modal-content image-upload-1">
                                <div class="fetched-data"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </ul>
        </div>
    </section>
    <div class="page-contant">
        <div class="page-contant-inner">
            <!-- profile page -->
            <div class="driver-profile-page">
                <!-- bank detail -->
                <?php if ($user == 'driver') { ?><?php
                }
                if ($APP_TYPE == 'UberX') {
                    $class_name = 'driver-profile-bottom-part required-documents-bottom-part two-part-document';
                } else {
                    $class_name = 'driver-profile-bottom-part required-documents-bottom-part';
                }
                ?>

                <?php if ($user == "driver" && $SITE_VERSION == "v5") { ?>
                    <div class="<?php echo $class_name; ?>">
                        <h3><?= $langage_lbl['LBL_PREFERENCES_TEXT'] ?> </h3>
                        <p>
                        <div class="driver-profile-info-aa col-md-5"> <?php foreach ($data_driver_pref as $val) { ?>
                                <img data-toggle="tooltip" class="borderClass-aa border_class-bb" title="<?= $val['pref_Title'] ?>" src="<?= $tconfig["tsite_upload_preference_image_panel"] . $val['pref_Image'] ?>">
                            <?php } ?>
                        </div>
                        <span class="col-md-5">
                            <a href="preferences.php" id="show-edit-language-div" class="hide-language">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                <?= $langage_lbl['LBL_PROFILE_EDIT'] ?></a>
                        </span></p>
                    </div>
                <?php } ?>
            </div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <input type="hidden" name="responsemsg" id="responsemsg">
    <div class="col-lg-12">
        <div class="custom-modal-main in " id="uiModal_4" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="custom-modal">
                <div class="model-header">
                    <h4><?= $langage_lbl['LBL_PROFILE_PICTURE']; ?></h4>
                </div>
                <div class="upload-content">
                    <form class="form-horizontal frm9" id="frm9" method="post" enctype="multipart/form-data" action="upload_doc.php" name="frm9">
                        <input type="hidden" name="action" value="photo"/>
                        <input type="hidden" name="img_path" value="
                                <?php
                        if ($user == 'company') {
                            echo $tconfig["tsite_upload_images_compnay_path"];
                        } else if ($user == 'driver') {
                            echo $tconfig["tsite_upload_images_driver_path"];
                        }
                        ?>"/>
                        <div class="form-group">
                            <div class="col-lg-12">
                                <div class="model-body fileupload fileupload-new" data-provides="fileupload">
                                    <div class="fileupload-preview thumbnail" id="fileupload-preview">
                                        <?php
                                        if ($user == 'company') {
                                            $img_path = $tconfig["tsite_upload_images_compnay"];
                                            $profileImgpath = $tconfig["tsite_upload_images_compnay_path"];
                                        } else if ($user == 'driver') {
                                            $img_path = $tconfig["tsite_upload_images_driver"];
                                            $profileImgpath = $tconfig["tsite_upload_images_driver_path"];
                                        }
                                        if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') || !file_exists($profileImgpath . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'])) { ?>
                                            <img class="imagename" src="assets/img/profile-user-img.png" alt="">
                                            <?php
                                        } else { ?>
                                            <img class="imagename" src="<?= $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'] ?>" style="height:150px;"/>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <span class="btn btn-file btn-success gen-btn"><span class="fileupload-new"><?= $langage_lbl['LBL_UPLOAD_PHOTO']; ?></span>
                                            <span class="fileupload-exists"><?= $langage_lbl['LBL_Driver_document_CHANGE']; ?></span>
                                            <input type="file" name="photo" id="profilePic"/>
                                        </span>
                                        <input type="hidden" name="photo_hidden" id="photo" value="<?php echo ($db_data[0]['vImage'] != "") ? $db_data[0]['vImage'] : ''; ?>"/>
                                        <?php if (($db_user[0]['vImage'] == 'NONE' || $db_user[0]['vImage'] == '') || !file_exists($profileImgpath . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'])) { 
                                            $vImgNamepreview = "assets/img/profile-user-img.png";
                                        } else {
                                            $vImgNamepreview = $img_path . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
                                        } ?>
                                        <a href="#" class="gen-btn fileupload-exists" data-dismiss="fileupload" onclick="change_img('<?= $vImgNamepreview; ?>')"><?= $langage_lbl['LBL_REMOVE_TEXT']; ?></a>
                                    </div>
                                    <div class="upload-error"><span class="file_error"></span></div>
                                </div>
                                <div class="model-footer">
                                    <div class="button-block">
                                        <input type="submit" class="gen-btn" name="save" value="<?= $langage_lbl['LBL_Save']; ?>">
                                        <input type="button" class="gen-btn" data-dismiss="modal" name="cancel" value="<?= $langage_lbl['LBL_BTN_PROFILE_CANCEL_TRIP_TXT']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <!--<div class="custom-modal-main123 in" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="custom-modal">
                        <div class="modal-content modal-content-profile">

                            <div class="model-header">
                                <h4><?= $langage_lbl['LBL_NOTE_FOR_DEMO']; ?></h4>
                                <i class="icon-close" data-dismiss="modal"></i>
                            </div>

                            <div class="model-body">
                                <form role="form" name="verification" id="verification">
                                    <?php if ($user == 'driver') { ?>
                                        <p><?= $langage_lbl['LBL_PROFILE_WE_SEE_YOU_HAVE_REGISTERED_AS_A_DRIVER']; ?></p>
                                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION_ADDVEHICLE']; ?></p>

                                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM_DRIVER']; ?></p>
                                    <?php } else { ?>
                                        <p><?= $langage_lbl['LBL_WE_SEE_YOU_HAVE_REGISTERED_AS_A_COMPANY']; ?></p>
                                        <p><?= $langage_lbl['LBL_SINCE_IT_IS_DEMO_VERSION']; ?></p>

                                        <p><?= $langage_lbl['LBL_STEP1']; ?></p>
                                        <p><?php //= $langage_lbl['LBL_STEP2'];    
            ?></p>
                                        <p><?= $langage_lbl['LBL_STEP3']; ?></p>

                                        <p><?= $langage_lbl['LBL_HOWEVER_IN_REAL_SYSTEM']; ?></p>
                                    <?php } ?>
                                    <div class="form-group">

                                    </div>
                                    <p class="help-block" id="verification_error"></p>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>-->
    </div>
    <?php

    include_once('account_delete_process_web.php');

    ?>
    <!-- footer part -->
    <?php include_once 'footer/footer_home.php'; ?>
    <!-- footer part end -->
    <!-- -->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php
include_once 'top/footer_script.php';
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode'];
?>
<link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css"/>
<style>
    .upload-error .help-block {
        color: #b94a48;
    }
    .upload-error{
        position: relative;
        width: 250px;
        text-wrap: wrap;
        line-height: 19px;
        font-size: 14px;
        text-align: center;
    }
</style>
<script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="assets/validation/validatrix.css"/>
<script type="text/javascript" src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
<script src="assets/js/modal_alert.js"></script>
<!-- <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $tconfig["tsite_url_main_admin"] ?>css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css"> -->
<script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/moment.min.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js"></script>
<script src="assets/js/jquery-ui.min.js"></script>
<?php if ($lang != 'en') { ?>
    <!--  <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js" ></script> -->
    <?php include_once('otherlang_validation.php'); ?><?php } ?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<!-- End: Footer Script -->
<script type="text/javascript">
    //Added to resolve mantis bugs by NM on 5/8/20 START
    var otherinfo = $('#otherinfo').val();
    var AUTO_ACCEPT_STATUS = "";
    var dataa = {};
    languagedata = <?php echo $json_lang; ?>;
    if (otherinfo == 1) {
        $('.other_info').text('<?= $langage_lbl['LBL_OTHER_INFO_TXT']; ?>');
    } else {
        $('.other_info').text('');
    }

    //Added to resolve mantis bugs by NM on 5/8/20 END
    function nospaces(t) {
        if (t.value.match(/\s/g)) {
            alert('Password should not contain whitespace.');
            //t.value=t.value.replace(/\s/g,'');
            t.value = '';
        }
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode
        if (charCode > 31 && (charCode < 35 || charCode > 57)) {
            return false;
        } else {
            return true;
        }
    }

    function change_img(action) {
        $('#fileupload-preview').html('<img src="' + action + '" />');
        $(".imagename").fadeIn();
    }

    function autoAcceptStatus(elem) {
        //accept_order
        var companyId = "<?= $_SESSION['sess_iUserId']; ?>";
        var typed = "eAvailable";
        var status = $(elem).attr("data-status");
        var newStatus = "Yes";
        if (status == "Yes") {
            var newStatus = "No";
        }
        if (newStatus == "No") {
            $('#accept_order').prop('checked', false);
        } else {
            $('#accept_order').prop('checked', true);
        }
        checkStoreAvailability(status);
    }

    <?php if ($_SESSION['sess_eSystem'] == "DeliverAll") { ?>

    function checkStoreAvailability(updateStatus) {
        AUTO_ACCEPT_STATUS = updateStatus;
        var sendrequestparam = {
            "tSessionId": "<?= $db_user[0]['tSessionId']; ?>",
            "GeneralMemberId": "<?= !empty($othercompany) ? $dbCompanySessionData[0]['iCompanyId'] : $db_user[0]['iCompanyId']; ?>",
            "iCompanyId": "<?= $db_user[0]['iCompanyId']; ?>",
            "iServiceId": "<?= $db_user[0]['iServiceId']; ?>",
            "GeneralUserType": 'Company',
            "UserType": 'Company',
            "type": 'UpdateRestaurantAvailability',
            "eAvailable": updateStatus,
            "CALL_TYPE": "Update",
            'async_request': false,
            "isFromWeb": 'Yes'
        };
        $("#responsemsg").val('');
        sendrequestparam = $.param(sendrequestparam);
        getDataFromApi(sendrequestparam, function (response) {
            response = JSON.parse(response);
            $("#responsemsg").val(response.message);
            //console.log(response.message);
            if (response.Action == '1') {
                show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>", languagedata[response.message], "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                    if (btn_id == 0) {
                        location.reload();
                    }
                });
            } else {
                if ($("#responsemsg").val() == "LBL_NO_FOOD_MENU_ITEM_AVAILABLE_TXT") {
                    show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>", "<?= addslashes($langage_lbl['LBL_NOT_ITEM_ADD']); ?>", "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                        if (btn_id == 0) {
                            window.location = "<?= $tconfig["tsite_url"]; ?>menuitems.php";
                        }
                    });
                } else if ($("#responsemsg").val() == "DO_PHONE_VERIFY") {
                    show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>", "<?= addslashes($langage_lbl['LBL_ACCOUNT_VERIFY_ALERT_RESTAURANT']); ?>", "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                        /*if (btn_id == 0) {
                            window.location = "<?= $tconfig["tsite_url"]; ?>profile";
                                }*/
                    });
                } else {
                    show_alert("<?= addslashes($langage_lbl['LBL_ATTENTION']); ?>", languagedata[response.message], "<?= addslashes($langage_lbl['LBL_BTN_OK_TXT']); ?>", "", "", function (btn_id) {
                        if (btn_id == 0) {
                            window.location = "<?= $tconfig["tsite_url"]; ?>settings";
                        }
                    });
                }
            }
        });
    }
    <?php } ?>

    var successMSG1 = '<?php echo $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }
    $("#dp3").datepicker();
    $("#dp3").datepicker({
        dateFormat: "yy-mm-dd",
        changeYear: true,
        changeMonth: true,
        yearRange: "-100:+10"
    });
    $(document).ready(function () {
        $("#show-edit-profile-div").click(function () {
            $("#hide-profile-div").hide();
            $("#show-edit-profile").show();
        });
        $("#hide-edit-profile-div").click(function () {
            $("#show-edit-profile").hide();
            $("#hide-profile-div").show();
            $("#frm1")[0].reset();
            var selectedOption = $('.custom-select-new.vCountry').find(":selected").text();
            var selectedOption1 = $('.custom-select-new.vCurrencyDriver').find(":selected").text();
            if (selectedOption != "" || selectedOption1 != "") {
                $('.custom-select-new.vCountry').next(".holder").text(selectedOption);
                $('.custom-select-new.vCurrencyDriver').next(".holder").text(selectedOption1);
            }
        });
        $("#show-edit-password-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $(".hide-password-div").hide();
            $("#show-edit-password").show(300);
        });
        $("#hide-edit-password-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $("#show-edit-password").hide();
            $(".hide-password-div").show();
            $("#frm3")[0].reset();
        });
        $("#show-edit-address-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $(".hide-address-div").hide();
            $("#show-edit-address").show(300);
        });
        $("#hide-edit-address-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $("#show-edit-address").hide();
            $(".hide-address-div").show();
            $("#frm2")[0].reset();
            var selectedOption = $('#vCountry').find(":selected").text();
            var selectedOption1 = $('#vState').find(":selected").text();
            var selectedOption2 = $('#vCity').find(":selected").text();
            if (selectedOption != "" || selectedOption1 != "" || selectedOption2 != "") {
                $('#vCountry').next(".holder").text(selectedOption);
                $('#vState').next(".holder").text(selectedOption1);
                $('#vCity').next(".holder").text(selectedOption2);
            }
        });
        $("#show-edit-language-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $(".hide-language-div").hide();
            $("#show-edit-language").show(300);
        });
        $("#hide-edit-language-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $("#show-edit-language").hide();
            $(".hide-language-div").show();
            $("#frm4")[0].reset();
            var selectedOption = $('.profile-language-input').find(":selected").text();
            if (selectedOption != "") {
                $('.profile-language-input').next(".holder").text(selectedOption);
            }
        });
        $("#show-edit-bankdetail-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $(".hide-bankdetail-div").hide();
            $("#show-edit-bankdeatil").show(300);
        });
        $("#hide-edit-bankdetail-div").click(function () {
            $('.hidev').show();
            $('.showV').hide();
            $("#show-edit-bankdeatil").hide();
            $(".hide-bankdetail-div").show();
            $("#frm6")[0].reset();
        });
        $("#show-edit-vat-div").click(function () {
            $("#hide-vat-div").hide();
            $("#show-edit-vat").show();
        });
        $("#hide-edit-vat-div").click(function () {
            $("#show-edit-vat").hide();
            $("#hide-vat-div").show();
        });
        $("#show-edit-accessibility-div").click(function () {
            $("#hide-accessibility-div").hide();
            $("#show-edit-accessibility").show();
        });
        $("#hide-edit-accessibility-div").click(function () {
            $("#show-edit-accessibility").hide();
            $("#hide-accessibility-div").show();
        });
        $('.demo-close').click(function (e) {
            $(this).parent().hide(1000);
        });
        var user = '<?= SITE_TYPE; ?>';
        if (user == 'Demo') {
            var a = '<?= $new; ?>';
            if (a != undefined && a != '') {
                //$('#formModal').modal('show');
            }
            //$('#formModal').modal('show');
        }
        $('[data-toggle="tooltip"]').tooltip();
        $('#cancel-btn').on('click', function () {
            $('#photo').val('');
        });
        $('.frm9').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, element) {
                if (element.attr("name") == "photo") {
                    error.insertAfter("span.file_error");
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                photo: {
                    required: {
                        depends: function (element) {
                            if ($("#photo").val() == "NONE" || $("#photo").val() == "") {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    },
                    // extension: "jpg|jpeg|png|gif"
                    extension: imageUploadingExtenstionjsrule
                },
            },
            messages: {
                photo: {
                    required: '<?= addslashes($langage_lbl['LBL_UPLOAD_IMG']); ?>',
                    extension: imageUploadingExtenstionMsg
                    // extension: '<?= addslashes($langage_lbl['LBL_UPLOAD_IMG_ERROR']); ?>'
                },
            }
        });
    });

    function setModel001(idVal) {
        // $('#uiModal').on('show.bs.modal', function (e) {
        // var rowid = $(e.relatedTarget).data('id');
        $('.fetched-data').html("");
        var id = '<?php echo $_SESSION['sess_iUserId']; ?>';
        var user = '<?php echo $user; ?>';
        var usertype = '<?php echo $docType; ?>';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>cx-company_document_fetch1.php',
            'AJAX_DATA': 'rowid=' + idVal + '-' + id + '-' + user + '-' + usertype,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $('#uiModal').modal('show');
                $('.fetched-data').html(data);
            } else {
                console.log(response.result);
            }
        });
    }

    function validate_password_fb() {
        //var cpass = document.getElementById('cpass').value;
        var npass = document.getElementById('npass').value;
        var ncpass = document.getElementById('ncpass').value;
        var err = '';
        if (npass == '') {
            err += "<?php echo addslashes($langage_lbl['LBL_NEW_PASS_MSG']) ?><br/>";
        }
        if (npass.length < 6) {
            err += "<?php echo addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']) ?><br/>";
        }
        if (ncpass == '') {
            err += "<?php echo addslashes($langage_lbl['LBL_REPASS_MSG']) ?><br/>";
        }
        if (err == "") {
            if (npass != ncpass)
                err += "<?php echo addslashes($langage_lbl['LBL_PASS_NOT_MATCH']) ?><br/>";
        }
        if (err == "") {
            //editProfile('pass');
            //return false;
        } else {
            $('#npass').val('');
            $('#ncpass').val('');
            // alert(err);
            bootbox.dialog({
                title: "&nbsp;",
                message: "<h3>" + err + "</h3>",
                buttons: {
                    danger: {
                        label: "Ok",
                        className: "btn-danger",
                    },
                }
            });
            /*bootbox.dialog({
                message: "<h3>"+err+"</h3>",
                buttons: {
                    danger: {
                        label: "Ok",
                        className: "btn-danger",
                    },
                }
            });*/
            //document.getElementById("err_password").innerHTML = '<div class="alert alert-danger">' + err + '</div>';
            return false;
        }
    }

    function validate_password() {
        var cpass = document.getElementById('cpass').value;
        var npass = document.getElementById('npass').value;
        var ncpass = document.getElementById('ncpass').value;
        var err = '';
        /*if(ncpass == '' && npass == ''){

                                                    editProfile('allInOne');
                                                    return true;

                                                }else{

                                                    if (cpass == '') {
                                                        err += "<?= addslashes($langage_lbl['LBL_CURRENT_PASS_MSG']); ?><br />";
                                                        }
                                                        if (npass == '') {
                                                            err += "<?= addslashes($langage_lbl['LBL_NEW_PASS_MSG']); ?><br />";
                                                        }*/
        if (npass.length < 6) {
            err += "<?= addslashes($langage_lbl['LBL_PASS_LENGTH_MSG']); ?><br />";
        }
        if (npass.length > 16) {
            err += "<?= addslashes($langage_lbl['LBL_PASS__MAX_LENGTH_MSG']); ?><br />";
        }
        if (ncpass == '') {
            err += "<?= addslashes($langage_lbl['LBL_REPASS_MSG']); ?><br />";
        }
        if (err == "") {
            if (npass != ncpass)
                err += "<?= addslashes($langage_lbl['LBL_PASS_NOT_MATCH']); ?><br />";
        }
        if (err == "") {
        } else {
            $('#cpass').val('');
            $('#npass').val('');
            $('#ncpass').val('');
            bootbox.hideAll()
            bootbox.dialog({
                title: "&nbsp;",
                message: "<h3>" + err + "</h3>",
                buttons: {
                    danger: {
                        label: "Ok",
                        className: "btn-danger",
                    },
                }
            });
            return false;
        }
        // }
    }

    function editProfile(action) {
        var chk = '<?php echo SITE_TYPE; ?>';
        if (action == 'allInOne') {
            data = $("#frm1").serialize();
        }
        if (action == 'login') {
            data = $("#frm1").serialize();
        }
        if (action == 'address') {
            data = $("#frm2").serialize();
        }
        if (action == 'pass') {
            data = $("#frm3").serialize();
        }
        if (action == 'lang') {
            data = $("#frm4").serialize();
        }
        if (action == 'vat') {
            data = $("#frm5").serialize();
        }
        if (action == 'access') {
            data = $("#frm10").serialize();
        }
        if (action == 'bankdetail') {
            data = $("#frm6").serialize();
        }
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>profile_action.php',
            'AJAX_DATA': data,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                console.log(data);
                if (data == '0' || data == 0) {
                    err = "<?php echo addslashes($langage_lbl['LBL_INCCORECT_CURRENT_PASS_ERROR_MSG']) ?>";
                    bootbox.dialog({
                        message: "<h3>" + err + "</h3>",
                        buttons: {
                            danger: {
                                label: "Ok",
                                className: "btn-danger",
                            },
                        }
                    });
                    $('#npass').val('');
                    $('#ncpass').val('');
                    $('#cpass').val('');
                    return false;
                } else if (data == '4') {
                    err = "<?php echo addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']) ?>";
                    bootbox.dialog({
                        message: "<h3>" + err + "</h3>",
                        buttons: {
                            danger: {
                                label: "Ok",
                                className: "btn-danger",
                            },
                        }
                    });
                } else if (data == '5') {
                    err = "<?php echo addslashes($langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']) ?>";
                    bootbox.dialog({
                        message: "<h3>" + err + "</h3>",
                        buttons: {
                            danger: {
                                label: "Ok",
                                className: "btn-danger",
                            },
                        }
                    });
                } else if (data == '2' || data == '3' || data == 2 || data == 3) {
                    window.location = "profile?success=2&var_msg=" + data;
                    return false;
                } else {
                    window.location = 'profile?success=1&var_msg=' + data;
                    return false;
                }
            } else {
                console.log(response.result);
            }
        });
    }

    function changeCode(id) {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': 'id=' + id,
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
                document.getElementById("vCountry").value = id;
                setState(id, '<?php echo $db_user[0]['vState']; ?>');
                setCity('<?php echo $db_user[0]['vState']; ?>', '<?php echo $db_user[0]['vCity']; ?>');
            } else {
                console.log(response.result);
            }
        });
    }

    function setCity(id, selected) {
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {
                stateId: id,
                selected: selected,
                fromMod: fromMod
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCity").html(dataHtml);
            } else {
                console.log(response.result);
            }
        });
    }

    function setState(id, selected) {
        var fromMod = 'driver';
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {
                countryId: id,
                selected: selected,
                fromMod: fromMod
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vState").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                console.log(response.result);
            }
        });
    }

    setState('<?php echo $db_user[0]['vCountry']; ?>', '<?php echo $db_user[0]['vState']; ?>');
    setCity('<?php echo $db_user[0]['vState']; ?>', '<?php echo $db_user[0]['vCity']; ?>');
</script>
<script type="text/javascript">
    user = '<?= $user ?>';
    //var dataa = {};
    if (user == 'company') {
        dataa.iCompanyId = "<?= $_SESSION['sess_iUserId']; ?>";
        esystem = '<?= $eSystem; ?>';
        if (esystem == 'DeliverAll') dataa.usertype = 'store';
        else dataa.usertype = user;
        dataa.vCountry = $('#vCountry option:selected').val();
    } else {
        dataa.iDriverId = "<?= $_SESSION['sess_iUserId']; ?>";
        dataa.usertype = user;
        dataa.vCountry = $('#vCountry option:selected').val();
    }
    var errormessage;
    // point number 2769 add preventXss method to prevent html code in input field -- SP (01-03-2022)
    $.validator.addMethod("preventXss", function (value, element) {
        if (/<(br|basefont|hr|input|source|frame|param|area|meta|!--|col|link|option|base|img|wbr|!DOCTYPE|a|abbr|acronym|address|applet|article|aside|audio|b|bdi|bdo|big|blockquote|body|button|canvas|caption|center|cite|code|colgroup|command|datalist|dd|del|details|dfn|dialog|dir|div|dl|dt|em|embed|fieldset|figcaption|figure|font|footer|form|frameset|head|header|hgroup|h1|h2|h3|h4|h5|h6|html|i|iframe|ins|kbd|keygen|label|legend|li|map|mark|menu|meter|nav|noframes|noscript|object|ol|optgroup|output|p|pre|progress|q|rp|rt|ruby|s|samp|script|section|select|small|span|strike|strong|style|sub|summary|sup|table|tbody|td|textarea|tfoot|th|thead|time|title|tr|track|tt|u|ul|var|video).*?>|<(video).*?<\/\2>/i.test(value) == true) {
            return false
            e.preventDefault();
        } else {
            return true;
        }
    }, "<?= addslashes($langage_lbl['LBL_ENTER_VALID_VALUE_WEB']); ?>");
    $('#frm1').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        errorElement: 'span',
        errorPlacement: function (error, e) {
            /*if (element.attr("name") == "vCurrencyDriver")
                error.appendTo('#vCurrencyDriverCheck');
            else if (element.attr("name") == "vCountry")
                error.appendTo('#vCountryCheck');
            else
                error.insertAfter(element);*/
            e.parents('.newrow').append(error);
        },
        highlight: function (e) {
            $(e).closest('.newrow').removeClass('has-success has-error').addClass('has-error');
            $(e).closest('.newrow input').addClass('has-shadow-error');
            $(e).closest('.help-block').remove();
        },
        success: function (e) {
            e.prev('input').removeClass('has-shadow-error');
            e.closest('.newrow').removeClass('has-success has-error');
            e.closest('.help-block').remove();
            e.closest('.help-inline').remove();
        },
        onkeyup: function (element, event) {
            if (event.which === 9 && this.elementValue(element) === "") {
                return;
            } else {
                this.element(element);
            }
        },
        rules: {
            email: {
                <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?> required: true,
                <?php } ?>
                email: true,
                remote: {
                    url: 'ajax_validate_email_new.php',
                    type: "post",
                    cache: false,
                    data: {
                        vEmail: function (e) {
                            return $('#in_email').val();
                        },
                        usertype: function (e) {
                            return user;
                        },
                        iCompanyId: function (e) {
                            return $("#u_id1").val();
                        },
                        usertype_store: function (e) {
                            return dataa.usertype;
                        },
                        action:'Edit-Profile'
                    },
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_EMAIL_EXISTS_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                    async: false
                }
            },
            name: {
                required: function (e) {
                    return $('input[name=user_type]').val() == 'driver';
                },
                minlength: function (e) {
                    if ($('input[name=user_type]').val() == 'driver') {
                        return 2;
                    } else {
                        return false;
                    }
                },
                maxlength: function (e) {
                    if ($('input[name=user_type]').val() == 'driver') {
                        return 30;
                    } else {
                        return false;
                    }
                }
            },
            lname: {
                required: function (e) {
                    return $('input[name=user_type]').val() == 'driver';
                },
                minlength: function (e) {
                    if ($('input[name=user_type]').val() == 'driver') {
                        return 2;
                    } else {
                        return false;
                    }
                },
                maxlength: function (e) {
                    if ($('input[name=user_type]').val() == 'driver') {
                        return 30;
                    } else {
                        return false;
                    }
                }
            },
            vCompany: {
                required: function (e) {
                    return $('input[name=user_type]').val() == 'company';
                },
                minlength: function (e) {
                    if ($('input[name=user_type]').val() == 'company') {
                        return 1;
                    } else {
                        return false;
                    }
                },
                maxlength: function (e) {
                    if ($('input[name=user_type]').val() == 'company') {
                        return 30;
                    } else {
                        return false;
                    }
                },
                preventXss: true,
                // pattern: /^[a-zA-Z'.\s]{1,40}$/,
            },
            phone: {
                required: true,
                minlength: 3,
                digits: true,
                remote: {
                    url: 'ajax_driver_mobile_new.php',
                    type: "post",
                    data: dataa,
                    beforeSend : function(xhr) {
                        mO4u1yc3dx(xhr);
                    },
                    dataFilter: function (response) {
                        //response = $.parseJSON(response);
                        if (response == 'deleted') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                            return false;
                        } else if (response == 'false') {
                            errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                            return false;
                        } else {
                            return true;
                        }
                    },
                    async: false
                }
            },
            cpass: {
                required: function () {
                    return !!$('#npass').val();
                }
            },
            npass: {
                minlength: 6,
            },
            ncpass: {
                minlength: 6,
                equalTo: "#npass"
            },
            vZipcode: {
                //required: true,
                alphanumeric: true
            }
        },
        messages: {
            email: {
                remote: function () {
                    return errormessage;
                }
            },
            vCompany: {
                //required: 'Company Name is required.',
                //minlength: 'Company Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            name: {
                //required: 'First Name is required.',
                //minlength: 'First Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            lname: {
                //required: 'Last Name is required.',
                //minlength: 'Last Name at least 2 characters long.',
                //maxlength: 'Please enter less than 30 characters.'
            },
            phone: {
                //minlength: 'Please enter at least three Number.',
                //digits: 'Please enter proper mobile number.',
                remote: function () {
                    return errormessage;
                }
            },
            vZipcode: {
                number: '<?= $langage_lbl['LBL_INVALID'] ?>'
            }
        },
        submitHandler: function () {
            $("#vCountry").prop('disabled', false);
            $("#country").prop('disabled', false);
            if ($("#ncpass").val() == '') {
                valid = true;
            } else {
                <?php if ($db_user[0]['vPassword'] != "") { ?>
                valid = validate_password();
                <?php } else { ?>
                valid = validate_password_fb();
                <?php } ?>
            }
            if ($("#frm1").valid() && valid != false) {
                editProfile('allInOne');
            }
        }
    });
    //var from = document.getElementById('vWorkLocation');
    //autocomplete_from1 = new google.maps.places.Autocomplete(from);
    /*      google.maps.event.addListener(autocomplete_from1, 'place_changed', function() {
     var placeaddress = autocomplete_from1.getPlace();

     $('#vWorkLocationLatitude').val(placeaddress.geometry.location.lat());
     $('#vWorkLocationLongitude').val(placeaddress.geometry.location.lng());

     });  */
    $(document).on('click', '.profile_edit_btn', function () {
        $('.profile_edit').addClass('active');
        // general_label();
    })
    $(document).on('click', '.cancel_btn', function () {
        $('.profile_edit').removeClass('active');
    })
    $(document).on('click', '.dismissUpload', function () {
        $('#driver_doc').val(null);
        $('#profilePic').val(null);
        $('.fileupload-preview.thumbnail').html('');
    });
    var vStatec = $('#vState').find(":selected").val();
    //var vStatec = $('#vState:selected').val();
    if (vStatec === undefined || vStatec === null) {
        $("#selectstatelblc").html('');
    } else {
        $("#selectstatelblc").html('<?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_STATE_TXT']; ?> ');
    }
    $('#vState').on('change', function () {
        if (this.value != '') {
            $("#selectstatelblc").html('<?= $langage_lbl['LBL_SELECT_TXT'] . " " . $langage_lbl['LBL_STATE_TXT']; ?> ');
        } else {
            $("#selectstatelblc").html('');
        }
    });
</script>
</body>
</html>