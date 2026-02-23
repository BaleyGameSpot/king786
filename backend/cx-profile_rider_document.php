<?php
$RIDE_SHARE_OBJ->WebCommonParam();
$PublishedRides_DATA = $RIDE_SHARE_OBJ->GetUserDocument();
$Document_data = $PublishedRides_DATA['message'];
$iUserId = $_SESSION['sess_iUserId'];
$userData = $obj->MySQLSelect("SELECT tSessionId FROM register_user as ru  WHERE ru.iUserId = '" . $iUserId . "' ");
$tSessionId = $userData[0]['tSessionId'];

?>
<?php if (isset($Document_data) && !empty($Document_data)) { ?>
<div class="" style="margin-top: 10px;">
    <p> <span style="font-weight: 600;font-size: 30px;" > <?= $langage_lbl['LBL_MANAGE_DOCUMENT'] ?>  </span> <span style="color: red;">(<?= $langage_lbl['LBL_RIDE_SHARE_MANAGE_DOC_MENU_HINT_TXT'] ?>)</span>  </p>


</div>
<?php } ?>

<ul>

    <?php if (isset($Document_data) && !empty($Document_data)) {
        foreach ($Document_data as $doc) {
            $file_ext = $UPLOAD_OBJ->GetFileExtension($doc['doc_file']);
            $doc['doc_type'] = $file_ext;

            ?>

            <li>
                <div class="upload-block">
                    <input type="hidden" id="ex_status" value="yes">
                    <strong>
                        <?php echo $doc['doc_name']; ?>
                    </strong>
                    <input type="hidden" id="doc_id" value="">
                    <div class="doc-image-block">

                        <?php if ($file_ext == 'is_image') {  $doc['doc_file_not_uploaded'] = 1; ?>
                            <a href="<?php echo $doc['doc_file']; ?>"
                               target="_blank">
                                <img src="<?php echo $doc['doc_file']; ?>"
                                     style="cursor:pointer;" alt="<?php echo $doc['doc_name']; ?>">
                            </a>
                        <?php } else { ?>
                            <?php

                            if(isset($doc['doc_file']) && !empty($doc['doc_file']))
                            {
                                $URL = $doc['doc_file'];
                                $target_blank = 'target="_blank"';
                                $doc['doc_file_not_uploaded'] = 1;
                            }else{
                                $doc['doc_file'] = $URL = "javascript:void(0);";
                                $doc['doc_file_not_uploaded'] = '';
                                $target_blank = '';
                            }
                            ?>
                            <a <?php echo $target_blank; ?>
                                    href="<?php echo  $URL; ?>"
                                title="<?= $doc['doc_file'] ?>">
                                <?php echo $doc['doc_name']; ?>
                            </a>
                        <?php } ?>
                        <br> <b></b>
                    </div>
                    <div class="button-block" style="justify-content: center;">
                        <button class="btn gen-btn" data-toggle="modal" data-target="#uiModal" id="custId"
                                onclick="documentEditFromOpen('<?php echo base64_encode(json_encode($doc)); ?>')">
                            <?php echo $doc['doc_name']; ?>
                        </button>
                    </div>
                </div>
            </li>
        <?php }
    } ?>
    <div class="col-lg-12">
        <div class="custom-modal-main in  fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
             aria-hidden="true" style="max-width: 171270px; max-height: 85950px;">
            <div class="custom-modal">
                <div class="modal-content image-upload-1">
                    <div class="fetched-data">
                        <div class="upload-content ">
                            <div class="model-header">
                                <h4 id = "document_name_header"></h4><i class="icon-close"
                                                                                 data-dismiss="modal"></i></div>
                            <form class="form-horizontal frm6" id="frm66" method="post" enctype="multipart/form-data"
                                  action="profile?id=33&amp;master=1" name="frm6" novalidate="novalidate">
                                <input type="hidden" name="action" value="document"/>

                                <input type="hidden" id="document_iUserId" name="iUserId"
                                       value="<?php echo $iUserId; ?>"/>
                                <input type="hidden" id="document_doc_id" name="doc_id" value=""/>
                                <input type="hidden" id="document_doc_masterid" name="doc_masterid"
                                       value=""/>
                                <input type="hidden" id="document_ex_date" name="ex_date" value=""/>

                                <div class="model-body">

                                    <div class="fileupload fileupload-new" data-provides="fileupload">

                                        <div class="fileupload-preview01 thumbnail">

                                        </div>
                                        <div class="newrow userDocDetails">
                    <span class="btn btn-file btn-success gen-btn"><span class="fileupload-new" id = "Document_Name"></span>
                        <span class="fileupload-exists">Change</span>
                        <input type="file" name="upload_driver_doc"  id="upload_driver_doc"></span>
                                            <a href="#" class="btn btn-danger fileupload-exists gen-btn dismissUpload"
                                               data-dismiss="fileupload">Remove</a>
                                            <input type="hidden" name="driver_doc_hidden" id="driver_doc"
                                                   value="">
                                        </div>
                                        <div class="upload-error"><span class="file_error"></span></div>

                                    </div>
                                    <div class="filters-column exp-date newrow userDocDetails" id="document_exp_status">
                                        <label>Exp. Date</label>
                                        <input class="form-control" type="text" id="uploadDocExDate"
                                               name="dLicenceExp" value="" readonly=""
                                               aria-required="true">
                                        <span class="input-group-addon add-on"><i class="icon-cal"
                                                                                  id="from-date"></i></span>
                                        <div class="exp-error"><span class="exp_error"></span></div>
                                    </div>



                                </div>
                                <div class="model-footer">
                                    <div class="button-block">
                                        <input onclick="EditDocument()" type="button" class="save save11 gen-btn" name="save" value="Save">
                                        <input type="button" class="cancel11 gen-btn" data-dismiss="modal" name="cancel"
                                               value="Cancel">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</ul>
<style>
    .help-block {
        color: red;
    }
</style>
<script>

    var TSITE_UPLOAD_DOCS_FILE_EXTENSIONS_org = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS = "<?php echo  $tconfig["tsite_upload_docs_file_extensions"]; ?>";
    TSITE_UPLOAD_DOCS_FILE_EXTENSIONS = TSITE_UPLOAD_DOCS_FILE_EXTENSIONS.replace(/,/g, '|');
    $(document).ready(function () {
        $('#frm66').validate({
            ignore: ':hidden, .ignoreField',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                if (e.attr("name") === "vMinimumTrips") {
                } else {
                    e.parents('div.userDocDetails').after(error);
                }
            },

            highlight: function (e) {
                $(e).closest('.help-block').remove();
            },

            success: function (e) {
                e.closest('.help-block').remove();
            },

            rules: {
                upload_driver_doc: {
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
                },

            },

            messages: {
                upload_driver_doc: {
                    required: "Please choose an image file.",
                    extension: "Please choose a valid image file with the right extension (" +TSITE_UPLOAD_DOCS_FILE_EXTENSIONS_org+ ")."
                },
                dLicenceExp: "Please upload document.",
            },
        });
    });
    var documentEditFromOpen = (data) => {
        $("#frm66").validate().resetForm();
        var data = atob(data);
        data = jQuery.parseJSON(data);
        setFromData(data);
    }
    var setFromData = (data) => {


        var doc_path = document.getElementsByName('doc_path');
        var uploadDocExDate = document.getElementById('uploadDocExDate');
        var FileUploadPreView = document.getElementsByClassName('fileupload-preview01');
        var DOCUMENT_EXP_STATUS = document.getElementById('document_exp_status');
        var user_upload_doc = document.getElementById('user_upload_doc');
        var upload_driver_doc = document.getElementById('upload_driver_doc');
        var DOCUMENT_NAME = document.getElementById('Document_Name');
        var document_name_header = document.getElementById('document_name_header');
        var DOCUMENT_DOC_ID = document.getElementById('document_doc_id');
        var DOCUMENT_DOC_MASTERID = document.getElementById('document_doc_masterid');
        DOCUMENT_DOC_ID.value = data.doc_id;
        DOCUMENT_DOC_MASTERID.value = data.doc_masterid;
        uploadDocExDate.value = data.ex_date;
        var DRIVER_DOC = document.getElementById('driver_doc');
        document_name_header.innerHTML = DOCUMENT_NAME.innerHTML = data.doc_name;
        DRIVER_DOC.value = data.doc_file_not_uploaded;
        var previewImage = ''
        if (data.doc_type === "is_image") {
            previewImage = document.createElement('img');
            previewImage.src = data.doc_file;
        } else {
            previewImage = document.createElement('a');
            previewImage.href = data.doc_file;
            previewImage.text = data.doc_name;
        }
        FileUploadPreView[0].innerHTML = '';
        FileUploadPreView[0].appendChild(previewImage);
        if (data.ex_status === "no") {
            DOCUMENT_EXP_STATUS.style.visibility = "hidden";
            uploadDocExDate.classList.add('ignoreField');
        } else {
            DOCUMENT_EXP_STATUS.style.visibility = "visible";
            uploadDocExDate.classList.remove('ignoreField');
        }
        

      /*  if (data.doc_file_not_uploaded == "1") {
            upload_driver_doc.classList.add('ignoreField');
        } else {
            upload_driver_doc.classList.remove('ignoreField');
        }*/
        $('#uiModal').show();
        $("#imageIcon").hide();
    }

    var EditDocument = () => {

        if($("#frm66").valid()) {
        let doc_id = document.getElementById('document_doc_id').value;
        let doc_masterid = document.getElementById('document_doc_masterid').value;
        let ex_date = document.getElementById('uploadDocExDate').value;
        let vImage = document.getElementById('upload_driver_doc').files[0];


        var form_data = new FormData();

        form_data.append("GeneralMemberId",  "<?php  echo $_SESSION['sess_iUserId']; ?>");
        form_data.append("vGeneralLang",  "<?php  echo $_SESSION['sess_lang']; ?>");
        form_data.append("GeneralUserType",  "Passenger");
        form_data.append("type",  "PublishRideUploadDocuments");
        form_data.append("tSessionId",  "<?php  echo $tSessionId; ?>");
        form_data.append("vImage", vImage);
        form_data.append("ex_date", ex_date);
        form_data.append("doc_masterid", doc_masterid);
        form_data.append("doc_id", doc_id);

        UploadDataToServer(form_data, function (response) {

            if (response.Action == '1') {
                $('#uiModal').hide();
                //location.reload();

                var newURL = currentURL = window.location.href;
             //   var newURL = currentURL + "?success=3&message="+response.message;

                updateSessionValue(0,response.message,'LBL', function(response) {
                    window.location.href = newURL;
                });
               /* window.location.href = newURL;*/


            } else {
                var newURL = currentURL = window.location.href;
                //var newURL = currentURL + "?success=3&message="+response.message;
                updateSessionValue(0,response.message,'LBL', function(response) {
                    window.location.href = newURL;
                });

              /*  window.location.href = newURL;*/
                $('#uiModal').hide();


            }
        })
        }else{
            console.log("form not valid");
        }


    }




    var updateSessionValue = (MessageCode,Message,MessageType,callback) => {
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>cx-add_session_value.php',
            'AJAX_DATA': {
                MessageCode:MessageCode,
                Message:Message,
                MessageType:MessageType
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            callback();
        });
    }



</script>


