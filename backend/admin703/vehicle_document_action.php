<?php
include_once('../common.php');

/*if(!$userObj->hasPermission('edit-provider-vehicles-document-taxi-service')){
  $userObj->redirect();
}*/
$eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";

if ($eType == 'Ride') {
    $commonTxt = 'taxi-service';
}
if ($eType == 'Ride') {
    $commonTxt = 'taxi-service';
}
if ($eType == 'Deliver') {
    $commonTxt = 'parcel-delivery';
}
if ($eType == 'Ambulance') {
    $commonTxt = 'medical';
}
//$editDocument = ["edit-provider-vehicles-document-" . $commonTxt];
$editDocument = ["edit-provider-vehicles-document"];
if (!$userObj->hasPermission($editDocument)) {
   $userObj->redirect();
}
$script = "Vehicle_" . $eType;
$ParameterUrl = '&eType=' . $_REQUEST['eType'];
require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();
$sql = "SELECT * FROM country WHERE eStatus='Active'";
$db_country = $obj->MySQLSelect($sql);
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? 'Edit' : 'Add';
$doc_type = isset($_REQUEST['doc_type']) && $_REQUEST['doc_type'] != '';
$backlink=isset($_POST['backlink'])?$_POST['backlink']:'';
$previousLink=isset($_POST['backlink'])?$_POST['backlink']:'';

$sql = "select iDriverId,eType from driver_vehicle where iDriverVehicleId = '".$_REQUEST['id']."'";
$iDriverVehicleData = $obj->MySQLSelect($sql);
$iDriverVehicleId=$iDriverVehicleData[0]['iDriverId'];
$AppTypeeType = $iDriverVehicleData[0]['eType'];

$sql = "select vCountry,vCity from register_driver where iDriverId = '" .$iDriverVehicleId . "'";
$db_user = $obj->MySQLSelect($sql);
// get country id
 $sql = "select iCountryId from country where vCountryCode = '".$db_user[0]['vCountry']."'";
 $db_countrycode = $obj->MySQLSelect($sql);
// end country get  
//$script = 'Vehicle';
$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
if($APP_TYPE == 'Ride-Delivery'){
    $QeType = " AND (eType='".$AppTypeeType."')";
} else if($APP_TYPE == 'Ride-Delivery-UberX'){
    $QeType = " AND (eType='".$AppTypeeType."')";
} else {
    $QeType = " AND eType='".$APP_TYPE."'"; 
}
$sql1= "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file , dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" .$_REQUEST['id']."' and doc_usertype='car') dl on dl.doc_masterid=dm.doc_masterid  
    where dm.doc_usertype='car' and dm.status='Active' and (dm.country ='".$db_user[0]['vCountry']."' OR dm.country ='All') ORDER BY dm.iDisplayOrder";
$db_userdoc = $obj->MySQLSelect($sql1);
$count_all = scount($db_userdoc);

$sql2= "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file ,dl.req_date,dl.doc_id,dl.req_file, dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" .$_REQUEST['id']."' and doc_usertype='car') dl on dl.doc_masterid=dm.doc_masterid  
    where dl.req_date != '' AND dl.req_date != '0000-00-00' and  dm.doc_usertype='car' and dm.status='Active' and (dm.country ='".$db_user[0]['vCountry']."' OR dm.country ='All') ORDER BY dm.iDisplayOrder";
$db_userdoc2 = $obj->MySQLSelect($sql2);
$count_all2 = scount($db_userdoc2);
// echo '<pre>';print_r($db_userdoc2 );echo '</pre>';die;

$sql = "select * from driver_vehicle where iDriverVehicleId = '" . $_REQUEST['id'] . "'";
$db_user = $obj->MySQLSelect($sql);
$vName = $db_user[0]['vLicencePlate'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
if ($action='document' && isset($_POST['doc_type'])) {
		
    $expDate=$_POST['dLicenceExp'];
	
    // if (SITE_TYPE == 'Demo') {
        // header("location:vehicle_document_fetch.php?success=2&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        // exit;
    // }
    $masterid= $_REQUEST['doc_type'];
    if (isset($_POST['doc_path'])) {
        $doc_path = $_POST['doc_path'];
    }
    $temp_gallery = $doc_path . '/';
     $image_object = $_FILES['vehicle_doc']['tmp_name'];
     $image_name = $_FILES['vehicle_doc']['name'];
    if( empty($image_name )) {
        $image_name = $_POST['vehicle_doc_hidden']; 
    } 
    if ($image_name == "") {
     if($expDate != ""){
              $sql = "select ex_date from document_list where doc_userid='".$_REQUEST['id']."' and doc_masterid='".$masterid."'"; 
			  // $query = mysqli_query($sql);
			  $query = $obj->MySQLSelect($sql);
              $fetch = $query[0];
			  
   
                    if($fetch['ex_date'] != $expDate){    
                    
                       $sql="UPDATE `document_list` SET  ex_date='".$expDate."' WHERE doc_userid='".$_REQUEST['id']."' and doc_masterid='".$masterid."'";
            } else {
                $sql = "INSERT INTO `document_list` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '".$_REQUEST['doc_type']."', 'car', '".$_REQUEST['id']."', '".$expDate."', '', 'Inactive', CURRENT_TIMESTAMP)";
                  }
            // $query= mysqli_query($sql);
            $query= $obj->sql_query($sql);
        }
         $var_msg = "Please Upload valid file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
        header("location:vehicle_document_action.php?success=3&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg.$ParameterUrl);
        exit;
    }
    if ($_FILES['vehicle_doc']['name'] != "") {
        
//         ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
        require_once("library/validation.class.php");
        $validobj = new validation();
        $check_file_query = "select iDriverId,vNoc from register_driver where iDriverId=" . $_REQUEST['id'];
        $check_file = $obj->sql_query($check_file_query);
        $check_file['vNoc'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vNoc'];

        $filecheck = basename($_FILES['vehicle_doc']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
        //     $flag_error = 1;
        //     $var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
        // }
        $docUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_docs_file_extensions_validation_extensions"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vehicle_doc'], $tconfig["tsite_upload_docs_file_extensions"], $docUploadingExtenstionMsg);
        if ($error) {
            header("location:vehicle_document_action.php?success=3&id=" . $_REQUEST['id'] . "&var_msg=" . $error.$ParameterUrl);
            exit;
        } else {
             $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            //$img = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
            $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = $tconfig["tsite_upload_docs_file_extensions"]);
            $vImage = $vFile[0];
            $var_msg = "document uploaded successfully";
            $tbl = 'document_list';
            $sql = "select doc_id from  ".$tbl."  where doc_userid='".$_REQUEST['id']."' and doc_usertype='car'  and doc_masterid=".$_REQUEST['doc_type'] ;
			$db_data = $obj->MySQLSelect($sql);
/*            $q = "INSERT INTO ";
            $where = '';
*/
            if (scount($db_data) > 0) {
	        $query="UPDATE `".$tbl."` SET `doc_file`='".$vImage."' , `ex_date`='".$expDate."' WHERE doc_userid='".$_REQUEST['id']."' and doc_usertype='car'  and doc_masterid=".$_REQUEST['doc_type'];
/*                $q = "UPDATE ";
                $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";*/
            } else {
            $query =" INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
                   . "VALUES "
                   . "( '".$_REQUEST['doc_type']."', 'car', '".$_REQUEST['id']."', '".$expDate."', '".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
           
			}
            $obj->sql_query($query);
            //Start :: Log Data Save
            if (empty($check_file[0]['vNoc'])) {
                $vNocPath = $vImage;
            } else {
                $vNocPath = $check_file[0]['vNoc'];
            }
            save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'company', 'noc', $vNocPath);
            //End :: Log Data Save
            // Start :: Status in edit a Document upload time
            // $set_value = "`eStatus` ='inactive'";
            //estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
            // End :: Status in edit a Document upload time
            header("location:vehicle_document_action.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg.$ParameterUrl);
        }
    } else {
        $check_file_query = "select iDriverId,vNoc from register_driver where iDriverId=" . $_REQUEST['id'];
        $check_file = $obj->sql_query($check_file_query);
        $check_file['vNoc'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vNoc'];
        $vImage = $_POST['vehicle_doc_hidden'];
        $var_msg = "document uploaded successfully";
        $tbl = 'document_list';
        $sql = "select doc_id from  ".$tbl."  where doc_userid='".$_REQUEST['id']."' and doc_usertype='car'  and doc_masterid=".$_REQUEST['doc_type'] ;
        $db_data = $obj->MySQLSelect($sql);
        if (scount($db_data) > 0) {
        $query="UPDATE `".$tbl."` SET `doc_file`='".$vImage."' , `ex_date`='".$expDate."' WHERE doc_userid='".$_REQUEST['id']."' and doc_usertype='car'  and doc_masterid=".$_REQUEST['doc_type'];
        } else {
        $query =" INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
               . "VALUES " . "( '".$_REQUEST['doc_type']."', 'car', '".$_REQUEST['id']."', '".$expDate."', '".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
        }
        $obj->sql_query($query);
        //Start :: Log Data Save
        if (empty($check_file[0]['vNoc'])) {
            $vNocPath = $vImage;
        } else {
            $vNocPath = $check_file[0]['vNoc'];
        }
        save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'company', 'noc', $vNocPath);
        header("location:vehicle_document_action.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg. $ParameterUrl);
    }
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="keywords" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php  include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <link rel="stylesheet" href="../assets/css/bootstrap-fileupload.min.css" >
        <script src="../assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
        <style type="text/css">
            .upload-clicking img{
                min-height: 202px;
                max-height: 202px;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                            <h2><?= ucfirst($action); ?> Document of  <?= $vName; ?></h2>
                            <!-- <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();"> -->
                            <a class="back_link" href="vehicles.php<?= isset($_REQUEST['type']) ? '?type='.$_REQUEST['type'] : '' ?><?= isset($_REQUEST['eType']) ? '?eType='.$_REQUEST['eType'] : '' ?>">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) {?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $var_msg; ?>
                            </div><br/>
                            <?} ?>
                            <? if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                            <?} ?>
                            <? if ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $var_msg; ?>
                            </div><br/>
                            <?} ?>

                            <? if ($success == 4) {?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                Document Approved Successfully..
                            </div><br/>
                            <?} ?>
                            <input type="hidden" name="id" value="<?= $id; ?>"/>
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="vehicles.php"/>
                            <input type="hidden" name="eType" id="eType" value="<?php echo $eType; ?>"/>
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 style="margin-top:0px;">DOCUMENTS</h4>
                                </div>
                            </div>
                            <div class="row company-document-action">
                                <?php for ($i = 0; $i < $count_all; $i++) {  
                                    if($db_userdoc[$i]['eType'] == 'UberX'){
                                        $etypeName = 'Service';
                                    } else {
                                        $etypeName = $db_userdoc[$i]['eType'];
                                    }
                                ?>
                                        <div class="col-lg-3">
                                        <div class="panel panel-default upload-clicking">
                                            <div class="panel-heading">
                                                <div><?php echo $db_userdoc[$i]['doc_name'];?></div>
                                                <?php if($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX'){ ?>
                                                    <!-- <div style="font-size: 10px;">(For <?= $etypeName; ?>)</div> -->
                                                <?php } ?>
                                            </div>
                                            <div class="panel-body">
                                                <?php if ($db_userdoc[$i]['doc_file'] != '' && file_exists('../webimages/upload/documents/vehicles/' . $_REQUEST['id'] . '/' . $db_userdoc[$i]['doc_file'])) { ?>
                                                    <?php
                                                    $file_ext = $UPLOAD_OBJ->GetFileExtension($db_userdoc[$i]['doc_file']);
                                                    if ($file_ext == 'is_image') {
                                                        $imgpath = $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc[$i]['doc_file'];
                                                        $resizeimgpath = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath . "&w=200";
                                                        ?>
                                                        <a href="<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><img src = "<?=  $resizeimgpath; ?>" style="cursor:pointer;" alt ="YOUR DRIVING LICENCE" /></a>
                                                    <?php } else { ?>
                                                        <p><a href="<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc[$i]['doc_file'] ?>" target="_blank"><?php echo $db_userdoc[$i]['doc_name']; ?></a></p>
                                                    <?php } ?>
                                                    <?php
                                                } else {
                                                    echo "<p>".$db_userdoc[$i]['doc_name'] . ' not found'."</p>";
                                                }
                                                ?>
                                                <br/>
                                                <?php if($userObj->hasPermission($editDocument)){  ?>
                                                <b><button class="btn btn-info" data-toggle="modal" data-target="#uiModal" id="custId"  onClick="setModel001('<?php echo $db_userdoc[$i]['masterid']; ?>','<?php echo $db_userdoc[$i]['ex_status']; ?>');"  >
                                                        <?php
                                                        if ($db_userdoc[$i]['doc_name'] != '') {
                                                            echo $db_userdoc[$i]['doc_name'];
                                                        } 
                                                        ?>
                                                    </button></b>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                 <?php } ?>

                              
                                <div class="col-lg-12">
                                    <div class="modal fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-content image-upload-1">
                                            <div class="fetched-data"></div>
                                        </div>
                                    </div>
                                </div>
                             
                             
                            </div>
                        </div>
                    </div>

                    <!-- Expired Documents start  -->
                    <?php if($count_all2 != 0 && $SET_DRIVER_OFFLINE_AS_DOC_EXPIRED == 'Yes') {?>
                    <div class="body-div">
                        <div class="form-group">

                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 style="margin-top:0px;">NEW UPLOADED DOCUMENTS</h4>
                                    <input type="button" name="approveDoc" id="approveDoc" value="APPROVE DOCUMENTS" class="btn btn-success pull-right" >
                                </div>
                            </div>
                            <div class="row company-document-action">
                                <?php for ($i = 0; $i < $count_all2; $i++) {  
                                    if($db_userdoc2[$i]['eType'] == 'UberX'){
                                        $etypeName = 'Service';
                                    } else {
                                        $etypeName = $db_userdoc2[$i]['eType'];
                                    }
                                ?>
                                        <div class="col-lg-3">
                                        <div class="panel panel-default upload-clicking">
                                            <div class="panel-heading">
                                                <div><?php echo $db_userdoc2[$i]['doc_name'];?></div>
                                                <?php if($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX'){ ?>
                                                    <!-- <div style="font-size: 10px;">(For <?= $etypeName; ?>)</div> -->
                                                <?php } ?>
                                            </div>
                                            <div class="panel-body"  style="display: inline-block;">
                                                <?php if ($db_userdoc2[$i]['req_file'] != '' && file_exists('../webimages/upload/documents/vehicles/' . $_REQUEST['id'] . '/' . $db_userdoc2[$i]['req_file'])) { ?>
                                                    <?php
                                                    $file_ext = $UPLOAD_OBJ->GetFileExtension($db_userdoc2[$i]['req_file']);
                                                    if ($file_ext == 'is_image') {
                                                        $imgpath1 = $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc2[$i]['req_file'];
                                                        $resizeimgpath1 = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath1 . "&w=200";
                                                        ?>
                                                        <a href="<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc2[$i]['req_file'] ?>" target="_blank"><img src = "<?= $resizeimgpath1; ?>" style="cursor:pointer;" alt ="YOUR DRIVING LICENCE" /></a>
                                                    <?php } else { ?>
                                                        <p><a href="<?= $tconfig["tsite_upload_vehicle_doc_panel"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc2[$i]['req_file'] ?>" target="_blank"><?php echo $db_userdoc2[$i]['doc_name']; ?></a></p>
                                                    <?php } ?>
                                                    <?php
                                                } else {
                                                    echo "<p>".$db_userdoc2[$i]['doc_name'] . ' not found'."</p>";
                                                }
                                                ?>
                                                 <?php if(!empty($db_userdoc2[$i]['req_date'])){?>
                                                    <h5>Requested Date : <?php echo $db_userdoc2[$i]['req_date']; ?></h5>
                                                    
                                                <input type="hidden" name="approvedIds[]" class="approvedIds" value="<?php echo $db_userdoc2[$i]['doc_id']; ?>">
                                                <?php } ?>
                                                <br/>
                                            </div>
                                        </div>
                                    </div>
                                 <?php } ?>

                              
                                <div class="col-lg-12">
                                    <div class="modal fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                        <div class="modal-content image-upload-1">
                                            <div class="fetched-data"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <!-- End Expired Documents  -->
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
    <!-- Modal -->              
   
    <script>
	
	 function setModel001(idVal,ex_status) {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>vehicle_document_fetch.php',
                'AJAX_DATA': 'eType=<?php echo $eType; ?>&rowid=' + idVal + '-' + <?php echo $_REQUEST['id']; ?>+'-'+ex_status,
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    $('#uiModal').modal('show');
                    $('.fetched-data').html(data);
                }
                else {
                    console.log(response.result);
                }
            });
		}
		
</script>
<? include_once('footer.php');?>
<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/uniform/jquery.uniform.min.js"></script>
<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>

<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
<script src="../assets/js/formsInit.js"></script>
<script>
$(document).on('click', '#approveDoc', function(event) {
    
    var docsIds = $('input[name="approvedIds[]"]').map(function(){ 
        return this.value; 
    }).get();
                
    var ajaxData = {
        'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_approve_docs.php',
        'AJAX_DATA': 'docsIds=' + docsIds,
    };
    getDataFromAjaxCall(ajaxData, function(response) {
        if(response.action == "1") {
            var data = response.result;
            window.location = 'vehicle_document_action.php?success=4&id=<?php echo $_REQUEST['id']; ?>';
        }
        else {
            console.log(response.result);
        }
    });
});   
</script>
</body>
<!-- END BODY-->
</html>
