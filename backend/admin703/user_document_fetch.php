<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$rowid = isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : '';
$id = explode('-',$rowid);

$sql = "select  ex_status,doc_name,ex_status  from document_master  where doc_masterid='".$id[0]."'" ;
$db_user_doc = $obj->MySQLSelect($sql);

$sql = "select doc_id,doc_file,ex_date from ride_share_document_list where doc_masterid='".$id[0]."' AND doc_userid='".$id[1]."'";
$db_user_li = $obj->MySQLSelect($sql);

$file_ext = $UPLOAD_OBJ->GetFileExtension($db_user_li[0]['doc_file']);

$ARR = [];
$ARR['doc_name']  = $db_user_doc[0]['doc_name'];
$ARR['ex_status']  = $db_user_doc[0]['ex_status'];
$ARR['uploadDoc']  = $db_user_li[0]['doc_file'];
$ARR['uploadDocExDate']  = $db_user_li[0]['ex_date'];
$ARR['doc_Url']  = $tconfig["tsite_upload_ride_share_documents"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'];;
$ARR['doc_type']  = $file_ext;
$ARR['user_id']  = $id[1];
$ARR['doc_masterid']  = $id[0];
$ARR['doc_id']  = $db_user_li[0]['doc_id'];
$ARR['userType']  = $userType;
echo json_encode($ARR);
exit;
?>
<!--<div class="upload-content">
    <h4><?php /*echo $db_user_doc[0]['doc_name']; */?></h4>
    <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="user_document_action.php?id=<?php /*echo $id[1] ; */?>" name="frm6">
        <input type="hidden" name="action" value ="document"/>
        <input type="hidden" name="doc_type" value="<?php /*echo $id[0]; */?>" />
        <input type="hidden" name="doc_path" value =" <?php /*echo $tconfig["tsite_upload_ride_share_documents_path"]; */?>"/>
        <input type="hidden" name="user_type" value ="<?php /*echo $userType; */?>"/>

        <div class="form-group">
            <div class="col-lg-12">
                <div class="fileupload fileupload-new" data-provides="fileupload">
                    <div class="fileupload-preview thumbnail" style="width: 100%; height: 150px; line-height: 150px;">
                        <?php /*if ($db_user_li[0]['doc_file'] == '') {
                            echo 'No '.$db_user_doc[0]['doc_name'].' Photo';
                        } else { */?>
                            <?php
/*                            $file_ext = $UPLOAD_OBJ->GetFileExtension($db_user_li[0]['doc_file']);
                            if ($file_ext == 'is_image') {
                                */?>
                                <img src = "<?php /*= $tconfig["tsite_upload_ride_share_documents"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file']; */?>" style="width:200px;" alt ="Licence not found"/>
                            <?php /*} else { */?>
                                <a href="<?php /*= $tconfig["tsite_upload_ride_share_documents"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'] */?>" target="_blank"><?php /*echo $db_user_doc[0]['doc_name']; */?></a>
                            <?php /*} */?>
                        <?php /*} */?>
                    </div>
                    <div>
                        <span class="btn btn-file btn-success"><span class="fileupload-new" style="text-transform: uppercase;"><?php /*echo $db_user_doc[0]['doc_name']; */?></span>
                            <span class="fileupload-exists">Change</span>
                            <input type="file" name="driver_doc"/></span>
                        <a href="#" class="btn btn-danger fileupload-exists dismissUpload" data-dismiss="fileupload">Remove</a>
                        <input type="hidden" name="driver_doc_hidden"  id="driver_doc" value="<?php /*echo ($db_user_li[0]['doc_file'] !="") ? $db_user_li[0]['doc_file'] : '';*/?>" />
                    </div>
                    <div class="upload-error"><span class="file_error"></span></div>
                </div>
            </div>
        </div>
        <?php /*if($db_user_doc[0]['ex_status']=='yes') { */?>
            EXP. DATE<br>
            <div class="col-lg-13">
                <div class="col-lg-13 exp-date">
                    <div class="input-group input-append date" id="dp122">
                        <input class="form-control" type="text" name="dLicenceExp" value="<?php /*echo ($db_user_li[0]['ex_date'] !="") ? $db_user_li[0]['ex_date'] : '';*/?>" readonly="" required/>
                        <span class="input-group-addon add-on"><i class="icon-calendar"></i></span>
                    </div>
                    <div class="exp-error"><span class="exp_error"></span></div>
                </div>
            </div>
        <?php /*}  */?>
        <input type="submit" class="save" name="save" value="Save">
        <input type="button" class="cancel" data-dismiss="modal" name="cancel" value="Cancel">
    </form>
</div>-->
<script>
    $(document).ready(function() {
        var today = new Date();
        $('#frm6').validate({
            ignore: 'input[type=hidden]',
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
                    extension: "jpg|jpeg|png|gif|pdf|doc|docx"
                },
                dLicenceExp: {
                    required: true,
                    date : true,
                }
            },
            messages: {
                driver_doc: {
                    required: 'Please Upload Image.',
                    extension: 'Please Upload valid file format. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png'
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
    $(function () {
        newDate = new Date('Y-M-D');
        $('#dp122').datetimepicker({
            showClose: true,
            format: 'YYYY-MM-DD',
            minDate: moment(),
            ignoreReadonly: true,
            keepInvalid:true
        });
    });

    $(document).on('click', '.dismissUpload', function () {
    });

</script>