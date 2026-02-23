<?php
include_once('../common.php');
/*require_once(TPATH_CLASS . "Imagecrop.class.php");
$thumb = new thumbnail();*/
$rowid = isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : '';
$id = explode('-',$rowid);
      
$form_action = "company_document_action.php?id=" . $id[1] . "&master=" . $id[0];
$doc_path = $tconfig["tsite_upload_compnay_doc_path"];
$doc_url = $tconfig["tsite_upload_compnay_doc"];

if($userType == "tracking_company"){
    $doc_path = $tconfig["tsite_upload_track_company_doc_path"];
    $doc_url = $tconfig["tsite_upload_track_company_doc"];
}
/*$ssql = "";
if(isset($id[3])) {
    $ssql = " AND doc_usertype = '" . $id[3] . "' ";
    $form_action = "track_service_company_document_action.php?id=" . $id[1] . "&master=" . $id[0];
    $doc_path = $tconfig["tsite_upload_track_company_doc_path"];
    $doc_url = $tconfig["tsite_upload_track_company_doc"];
}*/

$sql = "select  *  from document_master  where doc_masterid='".$id[0]."' $ssql " ;
$db_user_doc = $obj->MySQLSelect($sql);

$sql = "select * from document_list where doc_masterid='".$id[0]."' AND doc_userid='".$id[1]."' $ssql";
$db_user_li = $obj->MySQLSelect($sql);
//if($db_user[0]['doc_name']== ''){ $vName = $db_user1[0]['doc_name'];}else{ $vName=$db_user[0]['doc_name'];}
?>

<div class="upload-content">
    <h4><?php echo $db_user_doc[0]['doc_name']; ?></h4>
    <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="<?= $form_action ?>" name="frm6">
        <input type="hidden" name="action" value ="document"/>
        <input type="hidden" name="doc_type" value="<?php echo $id[0]; ?>">
        <input type="hidden" name="doc_path" value =" <?php echo $doc_path; ?>">
        <input type="hidden" name="user_type" value ="<?php echo $userType; ?>">
        <div class="form-group">
            <div class="col-lg-12">
                <div class="fileupload fileupload-new" data-provides="fileupload">
                    <div class="fileupload-preview thumbnail" style="width: 100%; height: 150px; ">
                        <?php if ($db_user_li[0]['doc_file'] == '') { 
                            echo 'No '.$db_user_doc[0]['doc_name'].' Photo';
                            
                        } else { ?>
                            <?php
                            $file_ext = $UPLOAD_OBJ->GetFileExtension($db_user_li[0]['doc_file']);
                            if ($file_ext == 'is_image') {
                                ?>

                                <img src = "<?= $doc_url . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'] ?>"  alt ="<?php echo $db_user_doc[0]['doc_name'];?> not found"/>
                            <?php } else { ?>
                                <a href="<?= $doc_url . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'] ?>" target="_blank"><?php echo $db_user_li[0]['doc_name']; ?></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <div>
                        <span class="btn btn-file btn-success"><span class="fileupload-new">Upload <?php echo $db_user_doc[0]['doc_name'] ?> Photo</span>
                            <span class="fileupload-exists">Change</span>
                            <input type="file" name="company_doc" /></span>
                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">Remove</a>
                        <input type="hidden" name="company_doc_hidden"  id="company_doc" value="<?php echo ($db_user_li[0]['doc_file'] !="") ? $db_user_li[0]['doc_file'] : '';?>" />
                    </div>
                    <div class="upload-error"><span class="file_error"></span></div>
                </div>
            </div>
        </div>
        <?php if($db_user_doc[0]['ex_status']=='yes') { ?>
        <h5><b>EXP. DATE</b></h5>      
         <div class="col-lg-13 exp-date">
            <div class="input-group input-append date" id="dp122">
                <input class="form-control" type="text" name="dLicenceExp" value="<?php echo ($db_user_li[0]['ex_date'] !="") ? $db_user_li[0]['ex_date'] : '';?>" readonly="" required/>
                <span class="input-group-addon add-on"><i class="icon-calendar"></i></span>
            </div>
            <div class="exp-error"><span class="exp_error"></span></div>
        </div>    
        <?php }  ?>
       <input type="submit" class="save" name="save" value="Save">
        <input type="button" class="cancel" data-dismiss="modal" name="cancel" value="Cancel">
    </form>
</div>
<script>
$(document).ready(function() {
    $('#frm6').validate({
        ignore: 'input[type=hidden]',
        errorClass: 'help-block error',
        errorElement: 'span',
        errorPlacement: function(error, element) {
            if (element.attr("name") == "company_doc")
            {
                error.insertAfter("span.file_error");
            } else if(element.attr("name") == "dLicenceExp"){
                error.insertAfter("span.exp_error");
            } else {
                error.insertAfter(element);
            }
        },
        rules: {
            company_doc: {
                required: {
                    depends: function(element) {
                        if ($("#company_doc").val() == "") { 
                            return true;
                        } else { 
                            return false;
                        } 
                    }
                },
                extension: "pdf|jpg|png|bmp|jpeg|doc|docx|txt|xls|xlsx|heic|csv"
            }
        },
        messages: {
            company_doc: {
                required: requiredFieldMsg,
                extension: docUploadingExtenstionMsg
                // extension: 'Please Upload valid file format. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png'

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
			format: 'YYYY-MM-DD',
			minDate: moment(),
			ignoreReadonly: true,
            keepInvalid:true
		});
    });
</script>