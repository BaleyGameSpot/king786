<?php
include_once('../common.php');



if(!$userObj->hasPermission('view-track-service-company')){
  $userObj->redirect();
}

if (SITE_TYPE == 'Demo') {
  $_SESSION['success'] = 2;
  header("location:track_service_company.php");
  exit;
}

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? 'Edit' : 'Add';
$doc_type = isset($_REQUEST['doc_type']) && $_REQUEST['doc_type'] != '';
$backlink=isset($_POST['backlink'])?$_POST['backlink']:'';
$previousLink=isset($_POST['backlink'])?$_POST['backlink']:'';

$sql = "select vCountry,vCompany from track_service_company where iTrackServiceCompanyId = '".$_REQUEST['id']."'";
$iCompanyId = $obj->MySQLSelect($sql);

$script = 'TrackServiceCompany';


$sql1= "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file ,dl.req_date,dl.doc_id,dl.req_file,  dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" .$_REQUEST['id']."' and doc_usertype='trackcompany') dl on dl.doc_masterid=dm.doc_masterid  
    where dm.doc_usertype='trackcompany' and dm.status='Active' and (dm.country ='".$iCompanyId[0]['vCountry']."' OR dm.country ='All') $eTypeQuery ORDER BY `iDisplayOrder`";

$iCompanyIddoc = $obj->MySQLSelect($sql1);
$count_all = scount($iCompanyIddoc);


/* Query for Requested review Expired Docs */
$sql2= "SELECT dm.doc_masterid masterid, dm.doc_usertype , dm.doc_name ,dm.ex_status,dm.status, dl.doc_masterid masterid_list ,dl.ex_date,dl.doc_file ,dl.req_date,dl.doc_id,dl.req_file,  dl.status, dm.eType FROM document_master dm left join (SELECT * FROM `document_list` where doc_userid='" .$_REQUEST['id']."' and doc_usertype='trackcompany') dl on dl.doc_masterid=dm.doc_masterid  
   where dl.req_date != '' AND dl.req_date != '0000-00-00' and dm.doc_usertype='trackcompany' and dm.status='Active' and (dm.country ='".$iCompanyId[0]['vCountry']."' OR dm.country ='All') $eTypeQuery ORDER BY `iDisplayOrder`";

$iCompanyIddoc2 = $obj->MySQLSelect($sql2);
$count_all2 = scount($iCompanyIddoc2);



$vName = $iCompanyId[0]['vCompany'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';

if ($action='document' && isset($_POST['doc_type'])) {

    $expDate=$_POST['dLicenceExp'];
	
    // if (SITE_TYPE == 'Demo') {
        // header("location:company_document_action.php?success=2&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        // exit;
    // }
    $masterid= $_REQUEST['doc_type'];

    if (isset($_POST['doc_path'])) {
        $doc_path = $_POST['doc_path'];
    }
    $temp_gallery = $doc_path . '/';
     $image_object = $_FILES['company_doc']['tmp_name'];
     $image_name = $_FILES['company_doc']['name'];   

    if( empty($image_name )) {
        $image_name = $_POST['company_doc_hidden']; 
    } 

    if ($image_name == "") {
    
        if($expDate != ""){
			
			 $sql = "select ex_date from document_list where doc_userid='".$_REQUEST['id']."' and doc_masterid='".$masterid."'";
            $db_licence = $obj->sql_query($sql);	
			
			
			 if($db_licence[0]['ex_date']==$expDate)
			 {	
				 $var_msg = $langage_lbl_admin['LBL_Record_Updated_successfully'];				

			}
			else 
			{	
				if ($_FILES['company_doc']['name'] != "") {
				$filecheck = basename($_FILES['company_doc']['name']); 
				 $fileextarr = explode(".", $filecheck);
				$ext = strtolower($fileextarr[scount($fileextarr) - 1]);
				  $var_msg1  = '';		  

				  if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
					   //$flag_error = 1;
					 $var_msg1 = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
				  }else{
				  
				   $var_msg1 = $langage_lbl_admin['LBL_Record_Updated_successfully'];
				  
				  }	
				 } 
				$var_msg=$langage_lbl_admin['LBL_Record_Updated_successfully']. $var_msg1;			

				$tbl ='document_list'; 
                if(scount($db_licence) != 0) {
                    $q = "UPDATE ";
                    $where = " WHERE `doc_userid` = '" . $_REQUEST['id'] . "'";
    				$query = $q . " `" . $tbl . "` SET `ex_date` = '".$expDate."'  " . $where;
                } else {
                    $q = "INSERT INTO ";
                    $query = $q . " `" . $tbl . "` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) VALUES ( '".$_REQUEST['doc_type']."', 'trackcompany', '".$_REQUEST['id']."', '".$expDate."', '', 'Inactive', CURRENT_TIMESTAMP)";
                }
				$obj->sql_query($query);
			} 
			header("location:track_service_company_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			exit;
        }
         $var_msg = "Please upload valid file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
         header("location:track_service_company_document_action.php?success=3&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        exit;
    }

if ($_FILES['company_doc']['name'] != "") {     
       
       $check_file_query = "select doc_file,doc_userid from document_list where doc_masterid='".$masterid."'AND doc_userid=" . $_REQUEST['id'];
        $check_file = $obj->sql_query($check_file_query);
        $check_file['doc_file'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['doc_file'];
        $filecheck = basename($_FILES['company_doc']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
            $flag_error = 1;
            $var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
        }
       
        if ($flag_error == 1) {
            $var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
		header("location:track_service_company_document_action.php?success=3&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			exit;  
        }  else {
              $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
				
                mkdir($Photo_Gallery_folder, 0777);
            }
            
            $vFile = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, $prefix = '', $vaildExt = "pdf,doc,docx,jpg,jpeg,gif,png");
            $vImage = $vFile[0];
            $var_msg = "File uploaded successfully";
            $tbl = 'document_list';
            $sql = "select doc_id from  ".$tbl."  where doc_userid='".$_REQUEST[id]."' and doc_usertype='trackcompany'  and doc_masterid=".$_REQUEST['doc_type'] ;
            $db_data = $obj->MySQLSelect($sql);
            
            $q = "INSERT INTO ";
            $where = '';

            if (scount($db_data) > 0) {
	        $query="UPDATE `".$tbl."` SET `doc_file`='".$vImage."' , `ex_date`='".$expDate."' WHERE doc_userid='".$_REQUEST[id]."' and doc_usertype='trackcompany'  and doc_masterid=".$_REQUEST['doc_type'];
               
        } else {
            $query =" INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
               . "VALUES " . "( '".$_REQUEST['doc_type']."', 'trackcompany', '".$_REQUEST['id']."', '".$expDate."', '".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
           
			}

            $obj->sql_query($query);

            //Start :: Log Data Save
            if (empty($check_file[0]['doc_file'])) {
                $vNocPath = $vImage;
            } else {
                $vNocPath = $check_file[0]['doc_file'];
            }
            save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'trackcompany', 'Document Company', $vNocPath);
           
            header("location:track_service_company_document_action.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
            exit;
        }
    } else {
        $check_file_query = "select doc_file,doc_userid from document_list where doc_masterid='".$masterid."'AND doc_userid=" . $_REQUEST['id'];
        $check_file = $obj->sql_query($check_file_query);
        $check_file['doc_file'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['doc_file'];
        $vImage = $_POST['company_doc_hidden'];
        $tbl = 'document_list';
        $sql = "select doc_id from  ".$tbl."  where doc_userid='".$_REQUEST[id]."' and doc_usertype='trackcompany'  and doc_masterid=".$_REQUEST['doc_type'] ;
        $db_data = $obj->MySQLSelect($sql);
        if (scount($db_data) > 0) {
        $query="UPDATE `".$tbl."` SET `doc_file`='".$vImage."' , `ex_date`='".$expDate."' WHERE doc_userid='".$_REQUEST[id]."' and doc_usertype='trackcompany'  and doc_masterid=".$_REQUEST['doc_type'];
        } else {
        $query ="INSERT INTO `".$tbl."` ( `doc_masterid`, `doc_usertype`, `doc_userid`, `ex_date`, `doc_file`, `status`, `edate`) "
               . "VALUES " . "( '".$_REQUEST['doc_type']."', 'trackcompany', '".$_REQUEST['id']."', '".$expDate."', '".$vImage."', 'Inactive', CURRENT_TIMESTAMP)";
        }
        $obj->sql_query($query);
        $var_msg = $langage_lbl_admin['LBL_Record_Updated_successfully'];
        //Start :: Log Data Save
        if (empty($check_file[0]['doc_file'])) {
            $vNocPath = $vImage;
        } else {
            $vNocPath = $check_file[0]['doc_file'];
        }
        save_log_data($_SESSION['sess_iUserId'], $_REQUEST['id'], 'trackcompany', 'Document Company', $vNocPath);
        header("location:track_service_company_document_action.php?success=1&id=" . $_REQUEST['id'] . "&var_msg=" . $var_msg);
        exit;
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
        <title><?= $SITE_NAME ?> | Company <?= $action; ?></title>
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
        <script src="../	assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
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
                            <a class="back_link" href="track_service_company.php<?= isset($_REQUEST['type']) ? '?type='.$_REQUEST['type'] : '' ?>">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                            <!-- <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();"> -->
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) {?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?= $var_msg; ?>
                            </div><br/>
                            <?php } ?>

                            <?php if ($success == 2) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                            <?php } ?>
                            <?php if ($success == 3) {?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                <?= $var_msg; ?>
                            </div><br/>
                            <?php } ?>


                            <?php if ($success == 4) {?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                Document Approved Successfully..
                            </div><br/>
                            <?php } ?>
                                                        
                            <input type="hidden" name="id" value="<?= $id; ?>"/>
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="track_service_company.php"/>
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4 style="margin-top:0px;">DOCUMENTS</h4>
                                </div>
                            </div>
                            <div class="row company-document-action">

                                <?php for ($i = 0; $i < $count_all; $i++) { ?>
                                        <div class="col-lg-3">
                                        <div class="panel panel-default upload-clicking">
									        <div class="panel-heading"><?= $iCompanyIddoc[$i]['doc_name']; ?>
                                            </div>
                                            <div class="panel-body">
                                                <?php if ($iCompanyIddoc[$i]['doc_file'] != '' && file_exists($tconfig["tsite_upload_track_company_doc_path"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc[$i]['doc_file'])) { ?>
                                                    <?php
                                                     $file_ext = $UPLOAD_OBJ->GetFileExtension($iCompanyIddoc[$i]['doc_file']);
                                                     $file_ext = file_ext_new($iCompanyIddoc[$i]['doc_file']);
                                                    if ($file_ext == 'is_image') {
                                                        $imgpath = $tconfig["tsite_upload_track_company_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc[$i]['doc_file'];
                                                        $resizeimgpath = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath . "&w=200";
                                                        ?>
                                                        <a href="<?= $tconfig["tsite_upload_compnay_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc[$i]['doc_file'] ?>" target="_blank"><img src = "<?= $resizeimgpath; ?>" style="cursor:pointer;" alt ="YOUR DRIVING LICENCE" /></a>
                                                        <!-- data-toggle="modal" data-target="#myModallicence" -->
                                                    <?php }  else if ($file_ext == 'is_pdf') {
                                                        $imgpath = $tconfig["tsite_url"] . '/assets/img/pdf.jpg';
                                                        $resizeimgpath = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath . "&w=150";
                                                        ?>
                                                        <p><a href="<?= $tconfig["tsite_upload_compnay_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc[$i]['doc_file'] ?>" target="_blank"><img src="<?= $resizeimgpath; ?>" style="cursor:pointer;" alt="<?php echo $iCompanyIddoc[$i]['doc_name']; ?>"/> </a></p>
                                                   <?php } else {
                                                        $imgpath = $tconfig["tsite_url"] . '/assets/img/document.png';
                                                        $resizeimgpath = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath . "&w=150"; ?>
                                                        <p><a href="<?= $tconfig["tsite_upload_compnay_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc[$i]['doc_file'] ?>" target="_blank"><img src="<?= $resizeimgpath; ?>" style="cursor:pointer;" alt="<?php echo $iCompanyIddoc[$i]['doc_name']; ?>"/> </a></p>
                                                    <?php } ?>
                                                    <?php
                                                } else {
                                                    echo "<p>".$iCompanyIddoc[$i]['doc_name'] . ' not found'."</p>";
                                                }
                                                ?>
                                                <br/>
                                                <?php if($userObj->hasPermission('manage-company-document')){ ?>
                                                    <b><button class="btn btn-info" data-toggle="modal" data-target="#uiModal" id="custId" onClick="setModel001('<?php echo $iCompanyIddoc[$i]['masterid']; ?>','<?php echo $iCompanyIddoc[$i]['ex_status']; ?>');"  >

                                                        <?php
                                                        if ($iCompanyIddoc[$i]['doc_name'] != '') {
                                                            echo $iCompanyIddoc[$i]['doc_name'];
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

                                <?php for ($i = 0; $i < $count_all2; $i++) { ?>
                                        <div class="col-lg-3">
                                        <div class="panel panel-default upload-clicking">
                                            <div class="panel-heading"><?= $iCompanyIddoc2[$i]['doc_name']; ?>
                                            </div>
                                            <div class="panel-body" style="display: inline-block;">
                                                <?php if ($iCompanyIddoc2[$i]['req_file'] != '' && file_exists($tconfig["tsite_upload_track_company_doc_path"] . '/' . $_REQUEST['id'] . '/' . $db_userdoc[$i]['req_file'])) { ?>
                                                    <?php
                                                    $file_ext = $UPLOAD_OBJ->GetFileExtension($iCompanyIddoc2[$i]['req_file']);
                                                    if ($file_ext == 'is_image') {
                                                        $imgpath1 = $tconfig["tsite_upload_track_company_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc2[$i]['req_file'];
                                                        $resizeimgpath1 = $tconfig['tsite_url'] . "resizeImg.php?src=" . $imgpath1 . "&w=200";
                                                        ?>
                                                        <a href="<?= $tconfig["tsite_upload_track_company_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc2[$i]['req_file'] ?>" target="_blank"><img src = "<?= $resizeimgpath1; ?>" style="cursor:pointer;" alt ="YOUR DRIVING LICENCE" /></a>
                                                        <!-- data-toggle="modal" data-target="#myModallicence" -->
                                                    <?php } else { ?>
                                                        <p><a href="<?= $tconfig["tsite_upload_track_company_doc"] . '/' . $_REQUEST['id'] . '/' . $iCompanyIddoc2[$i]['req_file'] ?>" target="_blank"><?php echo $iCompanyIddoc2[$i]['doc_name']; ?></a></p>
                                                    <?php } ?>
                                                    <?php
                                                } else {
                                                    echo "<p>".$iCompanyIddoc2[$i]['doc_name'] . ' not found'."</p>";
                                                }
                                                ?>
                                                 <?php if(!empty($iCompanyIddoc2[$i]['req_date'])){?>
                                                    <h5>Requested Date : <?php echo $iCompanyIddoc2[$i]['req_date']; ?></h5>
                                                    
                                                    <input type="hidden" name="approvedIds[]" class="approvedIds" value="<?php echo $iCompanyIddoc2[$i]['doc_id']; ?>">
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
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>company_document_fetch.php',
                'AJAX_DATA': 'rowid=' + idVal + '-' + <?php echo $_REQUEST['id']; ?>+'-'+ex_status+ '-trackcompany',
                'REQUEST_CACHE': false
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    $('#uiModal').modal('show');
                    $('.fetched-data').html(data);//Show fetched data from database
                }
                else {
                    console.log(response.result);
                }
            });
		}
		
</script>
<?php include_once('footer.php');?>

<link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>


<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<!-- Start :: Datepicker css-->
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<!-- Start :: Datepicker-->

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
<script>
    
$(document).on('click', '#approveDoc', function(event) {
    
    var docsIds = $('input[name="approvedIds[]"]').map(function(){ 
                    return this.value; 
                }).get();


    var ajaxData = {
        'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_approve_docs.php',
        'AJAX_DATA': 'docsIds=' + docsIds
    };
    getDataFromAjaxCall(ajaxData, function(response) {
        if(response.action == "1") {
            window.location = 'track_service_company_document_action.php?success=4&id=<?php echo $_REQUEST['id']; ?>';  
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
