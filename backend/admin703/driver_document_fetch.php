<?php
include_once('../common.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$rowid = isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
$userType = isset($_REQUEST['userType']) ? $_REQUEST['userType'] : '';
$id = explode('-', $rowid);

/*  $sql = "select  dm.`doc_masterid`, dm.`doc_usertype`, dm.`doc_name`, dm.`ex_status`, dl.`doc_id`, dl.`doc_masterid`, dl.`doc_usertype`, dl.`doc_userid`, dl.`ex_date`, dl.`doc_file`,rd.`iDriverId`
 from document_master as dm 
 left join document_list  as dl on dl.doc_masterid= dm.doc_masterid
 left join  register_driver as rd on  dl.doc_userid= rd.iDriverId  
 where dm.doc_masterid='".$id[0]."' AND rd.iDriverId='".$id[1]."'" ;  

$db_user = $obj->MySQLSelect($sql);
$vName = $db_user[0]['doc_file']; */
$sql = "select  *  from document_master  where doc_masterid='" . $id[0] . "'";
$db_user_doc = $obj->MySQLSelect($sql);
$sql = "select * from document_list where doc_masterid='" . $id[0] . "' AND doc_userid='" . $id[1] . "'";
$db_user_li = $obj->MySQLSelect($sql);
?>
<div class="upload-content">
    <h4><?php echo $db_user_doc[0]['doc_name']; ?></h4>
    <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data"
          action="driver_document_action.php?id=<?php echo $id[1]; ?>" name="frm6">
        <input type="hidden" name="action" value="document"/>
        <input type="hidden" name="doc_type" value="<?php echo $id[0]; ?>"/>
        <input type="hidden" name="driver_old_document" value="<?php echo $db_user[0]['doc_file']; ?>"/>
        <input type="hidden" name="doc_path" value=" <?php echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
        <input type="hidden" name="user_type" value="<?php echo $userType; ?>"/>

        <div class="form-group">
            <div class="col-lg-12">
                <div class="fileupload fileupload-new" data-provides="fileupload">
                    <div class="fileupload-preview thumbnail" style="width: 100%; height: 150px; line-height: 150px;">
                        <?php
                        $DriverVehicle = [];
                        if ($db_user_li[0]['doc_file'] == '') {
                            echo 'No ' . $db_user_doc[0]['doc_name'] . ' Photo';
                            $DriverVehicle['URl'] = 'No ' . $db_user_doc[0]['doc_name'] . ' Photo';
                            $DriverVehicle['type'] = "TEXT";
                        } else { ?>
                            <?php
                            $file_ext = $UPLOAD_OBJ->GetFileExtension($db_user_li[0]['doc_file']);
                            if ($file_ext == 'is_image') {
                                $DriverVehicle['type'] = "IMAGE";
                                $DriverVehicle['URl'] = $tconfig["tsite_upload_driver_doc"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'];
                                ?>
                                <img src="<?= $tconfig["tsite_upload_driver_doc"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file']; ?>"
                                     style="width:200px;" alt="Licence not found"/>
                            <?php } else {
                                $DriverVehicle['type'] = "DOC";
                                $DriverVehicle['URl'] = $tconfig["tsite_upload_driver_doc"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'];
                                $DriverVehicle['DOC_NAME'] = $db_user_doc[0]['doc_name'];
                                ?>
                                <a href="<?= $tconfig["tsite_upload_driver_doc"] . '/' . $id[1] . '/' . $db_user_li[0]['doc_file'] ?>"
                                   target="_blank"><?php echo $db_user_doc[0]['doc_name']; ?></a>
                            <?php } ?>
                        <?php }
                        $DriverVehicle = json_encode($DriverVehicle);
                        ?>
                    </div>
                    <div>
                        <span class="btn btn-file btn-success">
                            <span class="fileupload-new" style="text-transform: uppercase;"><?php echo $db_user_doc[0]['doc_name']; ?></span>
                            <span class="fileupload-exists">Change</span>
                            <input type="file" name="driver_doc"/>
                        </span>
                        <a href="#" class="btn btn-danger fileupload-exists dismissUpload" data-dismiss="fileupload">Remove</a>
                        <input type="hidden" name="driver_doc_hidden" id="driver_doc"
                               value="<?php echo ($db_user_li[0]['doc_file'] != "") ? $db_user_li[0]['doc_file'] : ''; ?>"/>
                    </div>
                    <div class="upload-error"><span class="file_error"></span></div>
                </div>
            </div>
        </div>
        <?php if ($db_user_doc[0]['ex_status'] == 'yes') { ?>
            EXP. DATE<br>
            <div class="col-lg-13">
                <div class="col-lg-13 exp-date">
                    <div class="input-group input-append date" id="dp122">
                        <input class="form-control" type="text" name="dLicenceExp"
                               value="<?php echo ($db_user_li[0]['ex_date'] != "") ? $db_user_li[0]['ex_date'] : ''; ?>"
                               readonly="" required/>
                        <span class="input-group-addon add-on"><i class="icon-calendar"></i></span>
                    </div>
                    <div class="exp-error"><span class="exp_error"></span></div>
                </div>
            </div>
        <?php } ?>
        <input type="submit" class="save" name="save" value="Save">
        <input type="button" class="cancel" data-dismiss="modal" name="cancel" value="Cancel">
    </form>
</div>
<script>
    $(document).ready(function () {
        var today = new Date();
        $('#frm6').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block error',
            errorElement: 'span',
            errorPlacement: function (error, element) {
                if (element.attr("name") == "driver_doc") {
                    error.insertAfter("span.file_error");
                } else if (element.attr("name") == "dLicenceExp") {
                    error.insertAfter("span.exp_error");
                } else {
                    error.insertAfter(element);
                }
            },
            rules: {
                /*driver_doc: {
                    required: {
                        depends: function (element) {
                            if ($("#driver_doc").val() == "") {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    },
                    extension: "jpg|jpeg|png|gif|pdf|doc|docx"
                },*/
                driver_doc: {
                    required: true,
                    // extension: "jpg|jpeg|png|gif|pdf|doc|docx"
                    extension: "pdf|jpg|png|bmp|jpeg|doc|docx|txt|xls|xlsx|heic|csv"
                },
                dLicenceExp: {
                    required: true,
                    date: true,
                }
            },
            messages: {
                driver_doc: {
                    // required: 'Please Upload Image.',
                    // extension: 'Please Upload valid file format. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png'
                    required: requiredFieldMsg,
                    extension: docUploadingExtenstionMsg
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
            keepInvalid: true
        });
    });
    $(document).on('click', '.dismissUpload', function () {
        var vehicleData = JSON.parse('<?php echo $DriverVehicle; ?>');
        if (vehicleData.type == 'TEXT') {
            $('.fileupload-preview.thumbnail').text('No ' + vehicleData.URl + ' Photo');
        } else if (vehicleData.type == "IMAGE") {
            $('.fileupload-preview.thumbnail').html('<img src="' + vehicleData.URl + '" style="width:200px;" alt="Licence not found"/>');
        } else if (vehicleData.type == "DOC") {
            $('.fileupload-preview.thumbnail').html(' <a href="' + vehicleData.URl + '" target="_blank">'+ vehicleData.DOC_NAME +'</a>');
        }
    });

</script>