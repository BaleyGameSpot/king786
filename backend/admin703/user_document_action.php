<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
if ($action == "document") {
    $doc_id = isset($_REQUEST['doc_id']) ? $_REQUEST['doc_id'] : '';
    $doc_masterid = isset($_REQUEST['doc_masterid']) ? $_REQUEST['doc_masterid'] : '';
    $ex_date = isset($_REQUEST['dLicenceExp']) ? $_REQUEST['dLicenceExp'] : '';
    $image_name = isset($_FILES['driver_doc']['name']) ? $_FILES['driver_doc']['name'] : '';
    $image_object = isset($_FILES['driver_doc']['tmp_name']) ? $_FILES['driver_doc']['tmp_name'] : '';
    if (!empty($image_name)) {
        $img_path = $tconfig["tsite_upload_ride_share_documents_path"];
        $temp_gallery = $img_path . '/';
        $filecheck = basename($image_name);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        if ($ext != "jpg" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "heic" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
            $var_msg = $languageLabelsArr['LBL_FILE_EXT_VALID_ERROR_MSG'] . " .jpg, .jpeg, .png, .bmp, .heic, .pdf, .doc, .docx";
            $returnArr['Action'] = "0";
            $returnArr['message'] = $var_msg;
            setDataResponse($returnArr);
        }
        $Photo_Gallery_folder = $img_path . '/' . $iUserId . '/';
        $Photo_Gallery_folder_temp = $img_path . '/' . $iUserId . '/';
        if (!is_dir($img_path . '/')) {
            mkdir($img_path . '/', 0777);
            chmod($img_path . '/', 0777);
        }
        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
            chmod($Photo_Gallery_folder, 0777);
        }
        $img1 = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder_temp, $image_object, $image_name, '', 'jpg,png,jpeg,bmp,heic,pdf,doc,docx');
        $vImgName = $img1[0];
        $Data_insert['doc_file'] = $vImgName;
    }

    $Data_insert['doc_masterid'] = $doc_masterid;
    $Data_insert['doc_userid'] = $iUserId;
    if (!empty($ex_date)) {
        $Data_insert['ex_date'] = date('Y-m-d', strtotime($ex_date));
    }
    $Data_insert['doc_userid'] = $iUserId;
    $Data_insert['eApproveDoc'] = 'Yes';
    if (!empty($doc_id) && $doc_id > 0) {
        $where = " doc_id = '$doc_id' ";
        $obj->MySQLQueryPerform("ride_share_document_list", $Data_insert, 'update', $where);
        $document_id = $doc_id;
    } else {
        $Data_insert['dAddedDate'] = date('Y-m-d H:i:s');
        $document_id = $obj->MySQLQueryPerform("ride_share_document_list", $Data_insert, 'insert');
    }
    $_SESSION['ACTION_MESSAGE'] = "LBL_FILE_UPLOADED_SUCCESS_MSG";
    $_SESSION['msg_code'] = 6;

}
$approveBtn = isset($_REQUEST['approveBtn']) ? $_REQUEST['approveBtn'] : 'Yes';
$isDocApproved = isset($_REQUEST['isDocApproved']) ? $_REQUEST['isDocApproved'] : '';
$isRevokeApprovalDoc = isset($_REQUEST['isRevokeApprovalDoc']) ? $_REQUEST['isRevokeApprovalDoc'] : '';
$success = isset($_SESSION['msg_code']) ? $_SESSION['msg_code'] : '';
//$_SESSION['msg_code'] = '';
$sql = "select eApproveDoc,vName,vLastName,vEmail from register_user where iUserId = '" . $iUserId . "'";
$db_user = $obj->MySQLSelect($sql);
$vName = $db_user[0]['vName'] . ' ' . $db_user[0]['vLastName'];
if (strtoupper($isRevokeApprovalDoc) == "YES") {
    $userid = $iUserId;
    $register_user_update['eApproveDoc'] = 'No';
    $where = " iUserId = '$userid' ";
    $obj->MySQLQueryPerform("register_user", $register_user_update, 'update', $where);
    $where = " doc_userid = '$userid' ";
    $obj->MySQLQueryPerform("ride_share_document_list", $register_user_update, 'update', $where);
    if (isset($db_user[0]['vEmail']) && !empty($db_user[0]['vEmail'])) {
        $maildata['EMAIL'] = $db_user[0]['vEmail'];
        $maildata['vPublisherName'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'];
        $maildata['EMAIL_NAME'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'];
        $COMM_MEDIA_OBJ->SendMailToMember("RIDE_SHARE_DOCUMENT_APPROVED", $maildata);
    }
    $_SESSION['msg_code'] = 3;
    header("Location:" . $tconfig["tsite_url_main_admin"] . "user_document_action.php?iUserId=" . $iUserId);
    exit;
}
if (strtoupper($isDocApproved) == "YES") {

    $userid = $iUserId;

    $ride_share_document_list = $obj->MySQLSelect("SELECT count(*) FROM `ride_share_document_list` WHERE 'iUserId' = '$userid' AND  'eApproveDoc' = 'No'");


    $register_user_update['eApproveDoc'] = 'Yes';
    $where = " iUserId = '$userid' ";
    $obj->MySQLQueryPerform("register_user", $register_user_update, 'update', $where);
    $where = " doc_userid = '$userid' ";
    $obj->MySQLQueryPerform("ride_share_document_list", $register_user_update, 'update', $where);
    if (isset($db_user[0]['vEmail']) && !empty($db_user[0]['vEmail'])) {
        $maildata['EMAIL'] = $db_user[0]['vEmail'];
        $maildata['vPublisherName'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'];
        $maildata['EMAIL_NAME'] = $db_user[0]['vName'] . " " . $db_user[0]['vLastName'];
        $COMM_MEDIA_OBJ->SendMailToMember("RIDE_SHARE_DOCUMENT_APPROVED", $maildata);
    }
    $_SESSION['msg_code'] = 4;
    header("Location:" . $tconfig["tsite_url_main_admin"] . "user_document_action.php?success=4&iUserId=" . $iUserId);
    exit;
}
$documents = $obj->MySQLSelect("SELECT 
        rdl.doc_masterid,rdl.eApproveDoc , rdl.ex_date,rdl.doc_file,rdl.doc_id,(SELECT CASE WHEN COUNT(*) > 0 THEN 'Yes' ELSE 'No' END  as 'isUploaded' 
        FROM `ride_share_document_list` 
        WHERE doc_masterid = dm.doc_masterid AND doc_userid = {$iUserId}) as isUploaded,dm.doc_masterid, dm.ex_status, dm.doc_name_EN as doc_name 
        FROM document_master as dm LEFT JOIN ride_share_document_list rdl ON (rdl.doc_masterid = dm.doc_masterid AND doc_userid = {$iUserId} ) 
        WHERE  dm.doc_usertype = 'user' AND dm.status = 'Active' ORDER BY iDisplayOrder ASC ");
$img_url = $tconfig["tsite_upload_ride_share_documents"];
$img_path = $tconfig["tsite_upload_ride_share_documents_path"] . '/' . $iUserId . '/';
$Photo_Gallery_folder = $img_url . '/' . $iUserId . '/';
$eApproveDoc = "NotDocFound";
if (!empty($documents)) {
    $i = 0;
    $eApproveDoc = "Yes";
    $eApproveStatus = "Active";
    $UploadedDocCount = 0;
    foreach ($documents as $document) {
        if ($document['eApproveDoc'] == "No") {
            $eApproveDoc = "No";
            $eApproveStatus = "Inactive";
        }
        if (empty($document['doc_id'])) {
            $documents[$i]['doc_id'] = '';
        }
        if (empty($document['doc_masterid'])) {
            $documents[$i]['doc_masterid'] = '';
        }
        if (empty($document['ex_date'])) {
            $documents[$i]['ex_date'] = '';
        }
        if (empty($document['doc_file'])) {
            $documents[$i]['doc_file'] = '';
            if ($eApproveDoc == "Yes") {

                //$eApproveDoc = "NotDocFound";
                //$eApproveStatus = "Inactive";
            }
        } else {
            $documents[$i]['doc_file'] = $Photo_Gallery_folder . $document['doc_file'];
            $documents[$i]['doc_file_org'] = $document['doc_file'];
            $documents[$i]['is_doc'] = "No";
            $doc_file_arr = explode(".", $document['doc_file']);
            $doc_file_ext = strtolower($doc_file_arr[scount($doc_file_arr) - 1]);
            $images_ext_arr = explode(",", $tconfig["tsite_upload_image_file_extensions"]);
            if (!in_array($doc_file_ext, $images_ext_arr)) {
                $documents[$i]['is_doc'] = "Yes";
            }
            $UploadedDocCount += 1;


        }
        $i++;
    }

    if($UploadedDocCount == 0){
        $eApproveDoc = "NotDocFound";
        $eApproveStatus = "Inactive";
    }
}

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?> <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="../assets/css/bootstrap-fileupload.min.css">
    <script src="../assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php
    include_once('header.php');
    ?>
    <?php
    include_once('left_menu.php');
    ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= ucfirst($action); ?> Document of <?= $vName; ?></h2>

                    <form id="ApprovedDoc" method="post">
                        <input type="hidden" value="<?php echo $iUserId; ?>" name="iUserId" id="iUserId">
                        <input type="hidden" value="yes" name="isDocApproved" id="isDocApproved">
                        <input type="hidden" value="No" name="isRevokeApprovalDoc" id="isRevokeApprovalDoc">
                    </form>
                    <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                    <?php if ($eApproveDoc != "NotDocFound") { ?>

                        <?php if ($eApproveDoc == "No") { ?>
                            <input type="button" class="add-btn" value="Approve"
                                   onClick="isAllowedApprovDoc('<?php echo $eApproveDoc; ?>')">
                        <?php } ?>
                        <?php if ($eApproveDoc == "Yes") { ?>
                            <input type="button" class="add-btn" value="Revoke Approval "
                                   onClick="revokeApprovalDoc('<?php echo $eApproveDoc; ?>')">
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <input type="hidden" name="iUserId" value="<?= $iUserId; ?>"/>
                    <? if ($success == 4) {
                        $_SESSION['msg_code'] = ''; ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Document Approved Successfully..
                        </div>
                        <br/>
                    <? } ?>
                    <? if ($success == 3) {
                        $_SESSION['msg_code'] = ''; ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            The approval for the document has been successfully revoked.
                        </div>
                        <br/>
                    <? } ?>

                    <? if ($_SESSION['msg_code'] == 6) {
                            $_SESSION['msg_code'] = ''; ?>

                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            Document Details Updated Successfully.
                        </div>
                        <br/>
                    <? } ?>

                    <?php if(isset($documents) && !empty($documents)){ ?>
                    <div class="row">
                        <div class="col-lg-12">
                            <h3> Document Approval Status : <span> <?php echo $eApproveStatus; ?> </span></h3>
                        </div>
                    </div>
                    <div class="row company-document-action">
                        <?php foreach ($documents as $doc) {
                            ?>
                            <div class="col-lg-3">
                                <div class="panel panel-default upload-clicking">
                                    <div class="panel-heading new">
                                        <div>   <?php echo $doc['doc_name']; ?></div>
                                    </div>
                                    <div class="panel-body">

                                        <?php
                                        $LINK_SHOW = 0;
                                        if ($doc['doc_file_org'] != '' && file_exists($img_path . '/' . $doc['doc_file_org'])) {
                                            $LINK_SHOW = 1;
                                            ?>

                                            <?php $file_ext = $UPLOAD_OBJ->GetFileExtension($doc['doc_file']); ?>
                                            <?php if ($file_ext == 'is_image') {
                                                $resizeimgpath1 = $tconfig['tsite_url'] . "resizeImg.php?src=" . $doc['doc_file'] . "&w=200";
                                                ?>
                                                <p>
                                                    <img width="200px"
                                                         src="<?= $resizeimgpath1; ?>" style="cursor:pointer;"/>
                                                </p>
                                            <?php } else { ?>
                                                <p>
                                                    <a href="<?= $doc['doc_file'] ?>"
                                                       target="_blank" title="<?= $doc['doc_file'] ?>">
                                                        <?php echo $doc['doc_name']; ?>
                                                    </a>

                                                </p>
                                                <?php
                                            }
                                        } else {
                                            echo "<p>" . $doc['doc_name'] . ' not found' . "</p>";
                                        }
                                        ?>
                                        <br/>
                                        <?php // if ($userObj->hasPermission('edit-provider-document')) { ?>
                                        <b>
                                            <?php if ($LINK_SHOW == 1) { ?>
                                                <a target="_blank" class="btn btn-info"
                                                   id="custId"
                                                   href="<?= $doc['doc_file'] ?>">
                                                    <?php
                                                    if ($doc['doc_name'] != '') {
                                                        echo 'View
                                                        ';
                                                    }
                                                    ?>
                                                </a>
                                                <button class="btn btn-info"

                                                        id="custId"
                                                        onClick="EditDoc('<?php echo $doc['doc_masterid']; ?>');"
                                                        href="#">
                                                    <?php
                                                    if ($doc['doc_name'] != '') {
                                                        echo 'Edit';
                                                    }
                                                    ?>
                                                </button>
                                            <?php } ?>
                                        </b>
                                        <?php //} ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="col-lg-12">
                            <div class="modal fade " id="user_upload_doc" tabindex="-1" role="dialog"
                                 aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-content image-upload-1">
                                    <div class="fetched-data">
                                        <div class="upload-content">
                                            <h4 id="document_name_header"></h4>
                                            <form class="form-horizontal" id="frm6" method="post"
                                                  enctype="multipart/form-data"
                                                  action="user_document_action.php?iUserId=<?php echo $iUserId; ?>"
                                                  name="frm6">
                                                <input type="hidden" name="action" value="document"/>

                                                <input type="hidden" id="document_iUserId" name="iUserId"
                                                       value="<?php echo $iUserId; ?>"/>
                                                <input type="hidden" id="document_doc_id" name="doc_id" value=""/>
                                                <input type="hidden" id="document_doc_masterid" name="doc_masterid"
                                                       value=""/>
                                                <input type="hidden" id="document_ex_date" name="ex_date" value=""/>

                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <div class="fileupload fileupload-new"
                                                             data-provides="fileupload">
                                                            <div class="fileupload-preview thumbnail"
                                                                 style="width: 100%; height: 150px; line-height: 150px;">

                                                            </div>
                                                            <div>
                                                                <span class="btn btn-file btn-success">
                                                                    <span id="Document_Name" class="fileupload-new"
                                                                          style="text-transform: uppercase;"></span>
                                                                    <span class="fileupload-exists">Change</span>
                                                                    <input type="file" name="driver_doc"/>
                                                                </span>
                                                                <a href="#"
                                                                   class="btn btn-danger fileupload-exists dismissUpload"
                                                                   data-dismiss="fileupload">Remove</a>
                                                                <input type="hidden" name="driver_doc_hidden"
                                                                       id="driver_doc" value=""/>
                                                            </div>
                                                            <div class="upload-error"><span class="file_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-13 " id="document_exp_status">
                                                    EXP. DATE
                                                    <br>
                                                    <div class="col-lg-13 exp-date">
                                                        <div class="input-group input-append date" id="dp122">
                                                            <input class="form-control" type="text" id="uploadDocExDate"
                                                                   name="dLicenceExp"
                                                                   value="" required/>
                                                            <span class="input-group-addon add-on"><i
                                                                        class="icon-calendar"></i></span>
                                                        </div>
                                                        <div class="exp-error"><span class="exp_error"></span></div>
                                                    </div>
                                                </div>
                                                <input type="submit" class="save" name="save" value="Save">
                                                <input type="button" class="cancel" data-dismiss="modal" name="cancel"
                                                       value="Cancel">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <?php }else{ ?>

                        <h3> No Docuemnt Found. </h3>

                   <?php } ?>


                </div>
            </div>
        </div>
    </div>
</div>
<!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<!-- Modal -->
<div class="row loding-action" id="imageIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<? include_once('footer.php'); ?>
<link rel="stylesheet" type="text/css" media="screen"
      href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<!-- Start :: Datepicker css-->
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css"/>
<!-- Start :: Datepicker-->
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
<!-- Start :: Datepicker Script-->
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/uniform/jquery.uniform.min.js"></script>
<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script src="../assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
<script src="../assets/js/formsInit.js"></script>
<script src="../assets/js/modal_alert.js"></script>
<script>

    var TSITE_UPLOAD_DOCS_FILE_EXTENSIONS_org = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS = "<?php echo  $tconfig["tsite_upload_docs_file_extensions"]; ?>";
    TSITE_UPLOAD_DOCS_FILE_EXTENSIONS = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS.replace(/,/g, '|');

    var isAllowedApprovDoc = (allowed) => {
        if (confirm("Are you sure you want the approved document user document?")) {
            if (allowed == "Yes") {
                show_alert("", 'The documents have already been approved.', "", "", "ok");
            } else if (allowed == "NotDocFound") {
                show_alert("", 'The user has not uploaded any Document.', "", "", "ok");
            } else {
                $("#ApprovedDoc").submit();
            }
        }
    }
    var revokeApprovalDoc = () => {
        if (confirm("Are you sure you want the revoke approval document user document?")) {
            $('#isRevokeApprovalDoc').val('Yes');
            $("#ApprovedDoc").submit();
        }
    }

    function EditDoc(idVal) {
        $("#imageIcon").show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>user_document_fetch.php',
            'AJAX_DATA': 'rowid=' + idVal + '-' + '<?php echo $_REQUEST['iUserId']; ?>' + '&userType=user', //Pass $id
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                data = jQuery.parseJSON(data);
                setFromData(data);
            } else {
                console.log(response.result);
            }
        });
    }

    var setFromData = (data) => {
        var doc_path = document.getElementsByName('doc_path');
        var uploadDocExDate = document.getElementById('uploadDocExDate');
        var FileUploadPreView = document.getElementsByClassName('fileupload-preview');
        var DOCUMENT_EXP_STATUS = document.getElementById('document_exp_status');
        var user_upload_doc = document.getElementById('user_upload_doc');
        var DOCUMENT_NAME = document.getElementById('Document_Name');
        var document_name_header = document.getElementById('document_name_header');
        var DOCUMENT_DOC_ID = document.getElementById('document_doc_id');
        var DOCUMENT_DOC_MASTERID = document.getElementById('document_doc_masterid');
        var DRIVER_DOC = document.getElementById('driver_doc');


        DOCUMENT_DOC_ID.value = data.doc_id;
        DOCUMENT_DOC_MASTERID.value = data.doc_masterid;
        uploadDocExDate.value = data.uploadDocExDate;
        DRIVER_DOC.value = data.uploadDoc;


        document_name_header.innerHTML = DOCUMENT_NAME.innerHTML = data.doc_name;
        var previewImage = ''
        if (data.doc_type === "is_image") {
            previewImage = document.createElement('img');
            previewImage.src = data.doc_Url;
        } else {
            previewImage = document.createElement('a');
            previewImage.href = data.doc_Url;
            previewImage.text = 'Document';
        }
        FileUploadPreView[0].innerHTML = '';
        FileUploadPreView[0].appendChild(previewImage);
        if (data.ex_status === "no") {
            DOCUMENT_EXP_STATUS.style.visibility = "hidden";
            uploadDocExDate.classList.add("ignore");

        } else {
            DOCUMENT_EXP_STATUS.style.visibility = "visible";
            uploadDocExDate.classList.remove("ignore");
        }
        $('#user_upload_doc').modal('show');
        $("#imageIcon").hide();
    }
    $('#dp122').datetimepicker({
        showClose: true,
        format: 'YYYY-MM-DD',
        minDate: moment(),
        ignoreReadonly: true,
        keepInvalid: true
    });

    $(document).ready(function() {
        var today = new Date();
        $('#frm6').validate({
            ignore: '.ignore',
            errorClass: 'help-block error',
            errorElement: 'span',
            errorPlacement: function(error, element) {
                if (element.attr("name") == "driver_doc"){
                    error.insertAfter("span.file_error");
                } else if(element.attr("name") == "dLicenceExp"){
                    error.insertAfter("span.exp_error");
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                driver_doc: {
                    required: {
                        depends: function(element) {
                            if ($("#driver_doc").val() == "") {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    },
                    extension: TSITE_UPLOAD_DOCS_FILE_EXTENSIONS
                },
                dLicenceExp: {
                    required: true,
                    date : true,
                }
            },
            messages: {
                driver_doc: {
                    required: 'Please Upload Image.',
                    extension: "Please choose a valid image file with the right extension (" +TSITE_UPLOAD_DOCS_FILE_EXTENSIONS_org+ ")."
                }
            },
            submitHandler: function (form) {
                if ($(form).valid()) {
                    ShpSq6fAm7(form);
                    form.submit();
                }
                return false; // prevent normal form posting
            }
        });
    });
</script>
</body>
<!-- END BODY-->
</html>
