<?php
include_once('../common.php');

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

if ($action == "Add" && !$userObj->hasPermission('create-rating-feedback-ques')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to add feeback questions.';
    header("Location:rating_feedback_ques.php");
    exit;
}

if ($action == "Edit" && !$userObj->hasPermission('edit-rating-feedback-ques')) {
    $_SESSION['success'] = 3;
    $_SESSION['var_msg'] = 'You do not have permission to update feeback questions.';
    header("Location:rating_feedback_ques.php");
    exit;
}


$tbl_name = 'rating_feedback_questions';
$script = "RatingFeedbackQuestions";

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
// set all variables with either post (when submit) either blank (when insert)
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';

/* to fetch max iDisplayOrder from table for insert */
$select_order = $obj->MySQLSelect("SELECT count(iDisplayOrder) AS iDisplayOrder FROM " . $tbl_name);
$iDisplayOrder = isset($select_order[0]['iDisplayOrder']) ? $select_order[0]['iDisplayOrder'] : 0;
$iDisplayOrder_max = $iDisplayOrder + 1; // Maximum order number

$iDisplayOrder = isset($_POST['iDisplayOrder']) ? $_POST['iDisplayOrder'] : $iDisplayOrder;
$temp_order = isset($_POST['temp_order']) ? $_POST['temp_order'] : "";


if (isset($_POST['submit'])) {
    if (SITE_TYPE == 'Demo') {
        header("Location:rating_feedback_ques_action.php?id=" . $id . "&success=2");
        exit;
    }
    

    if ($temp_order == "1" && $action == "Add") {
        $temp_order = $iDisplayOrder_max;
    }
    if ($temp_order > $iDisplayOrder) {
        for ($i = $temp_order - 1; $i >= $iDisplayOrder; $i--) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i + 1) . "' WHERE iDisplayOrder = '" . $i . "'";
            $obj->sql_query($sql);
        }
    } else if ($temp_order < $iDisplayOrder) {
        for ($i = $temp_order + 1; $i <= $iDisplayOrder; $i++) {
            $sql = "UPDATE " . $tbl_name . " SET iDisplayOrder = '" . ($i - 1) . "' WHERE iDisplayOrder = '" . $i . "'";
            $obj->sql_query($sql);
        }
    }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iFeedbackId` = '" . $id . "'";
    }
    
    $tQuestionArr = array();
    for ($i = 0; $i < scount($db_master); $i++) {
        $tQuestionArr["tQuestion_" . $db_master[$i]['vCode']] = $_POST['tQuestion_' . $db_master[$i]['vCode']];
    }

    $jsonQuestion = getJsonFromAnArr($tQuestionArr);

    $query = $q . " `" . $tbl_name . "` SET 
                `tQuestion` = '" . $jsonQuestion . "',
                `eStatus` = '" . $eStatus . "',
                `iDisplayOrder` = '" . $iDisplayOrder . "'"
            . $where;
            
    $obj->sql_query($query);

    $id = ($id != '') ? $id : $obj->GetInsertId();

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');  
    }

    updateSystemData();
    header("location:" . $backlink);
    exit;
}


// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iFeedbackId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);


    if ($count_all > 0) {
        for ($i = 0; $i < $count_all; $i++) {
            $tQuestion = json_decode($db_data[0]['tQuestion'], true);
            foreach ($tQuestion as $key => $value) {
                $userEditDataArr[$key] = $value;
            }

            $eStatus = $db_data[0]['eStatus'];
            $iDisplayOrder_db = $db_data[0]['iDisplayOrder'];
        }
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Rating Feedback Questions  <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />

        <!-- PAGE LEVEL STYLES -->
        <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
        <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
        <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
        <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
        <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
        <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
        <style>
            ul.wysihtml5-toolbar > li {
                position: relative;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>       
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Rating Feedback Questions </h2>
                            <a href="rating_feedback_ques.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />  
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } ?>

                            <?php if ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>

                            <form method="post" name="_rating_feedback_form" id="_rating_feedback_form" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="rating_feedback_ques.php"/>

                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Question <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="tQuestion_Default" name="tQuestion_Default" value="<?= isset($userEditDataArr['tQuestion_'.$default_lang]) ? $userEditDataArr['tQuestion_'.$default_lang] : ''; ?>" data-originalvalue="<?= isset($userEditDataArr['tQuestion_'.$default_lang]) ? $userEditDataArr['tQuestion_'.$default_lang] : ''; ?>" readonly="readonly" required <?php if(empty($id)) { ?> onclick="editQuestion('Add')" <?php } ?>>
                                    </div>
                                    <?php if(!empty($id)) { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editQuestion('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                    

                                <div  class="modal fade" id="Rating_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Feedback Question
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tQuestion_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vLTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vTitle = 'tQuestion_' . $vCode;
                                                        $$vTitle = $userEditDataArr['tQuestion_' . $vCode];

                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Question (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
                                                            </div>
                                                            <?php
                                                            $page_title_class = 'col-lg-12';
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { 
                                                                        $page_title_class = 'col-lg-9';
                                                                    }
                                                                } else { 
                                                                    if($vCode == $default_lang) {
                                                                        $page_title_class = 'col-lg-9';
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                            <div class="<?= $page_title_class ?>">
                                                                <input type="text" class="form-control" name="<?= $vTitle; ?>" id="<?= $vTitle; ?>" value="<?= $$vTitle; ?>" data-originalvalue="<?= $$vTitle; ?>" placeholder="<?= $vLTitle; ?> Value">
                                                                <div class="text-danger" id="<?= $vTitle.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>

                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="col-lg-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tQuestion_', 'EN');">Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-lg-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('tQuestion_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                }
                                                            }
                                                            ?>
                                                        </div> 

                                                    <?php 
                                                    }
                                                ?>
                                            </div>
                                            <div class="modal-footer" style="margin-top: 0">
                                                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveQuestion()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'tQuestion_')">Cancel</button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>                                    
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Question <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="tQuestion_<?= $default_lang ?>" name="tQuestion_<?= $default_lang ?>" value="<?= $userEditDataArr['tQuestion_'.$default_lang]; ?>" required>
                                    </div>
                                </div>
                                <?php } ?>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Order</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">

                                        <input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $iDisplayOrder_db : '1'; ?>">
                                        <?php
                                        $display_numbers = ($action == "Add") ? $iDisplayOrder_max : $iDisplayOrder;
                                        ?>
                                        <select name="iDisplayOrder" class="form-control">
                                            <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
                                                <option value="<?= $i ?>" <?php
                                                if ($i == $iDisplayOrder_db) {
                                                    echo "selected";
                                                }
                                                ?>> -- <?= $i ?> --</option>
                                            <?php } ?>
                                        </select>

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Status</label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <div class="make-switch" data-on="success" data-off="warning">
                                            <input type="checkbox" name="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?>/>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">     
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-rating-feedback-ques')) || ($action == 'Add' && $userObj->hasPermission('create-rating-feedback-ques'))) { ?>                 
                                            <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Question">
                                            <input type="reset" value="Reset" class="btn btn-default">
                                        <?php } ?>

                                        <a href="rating_feedback_ques.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>

<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

<!-- GLOBAL SCRIPTS -->
<!--<script src="../assets/plugins/jquery-2.0.3.min.js"></script>-->
<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="../assets/plugins/modernizr-2.6.2-respond-1.1.0.min.js"></script>
<!-- END GLOBAL SCRIPTS -->


<!-- PAGE LEVEL SCRIPTS -->
<script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
<script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>
<script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
<script src="../assets/plugins/pagedown/Markdown.Converter.js"></script>
<script src="../assets/plugins/pagedown/Markdown.Sanitizer.js"></script>
<script src="../assets/plugins/Markdown.Editor-hack.js"></script>
<script src="../assets/js/editorInit.js"></script>

    </body>
    <!-- END BODY--> 

    <script>
         $(function () {
        formWysiwyg();
    });
        $(document).ready(function () {
            var referrer;
            if ($("#previousLink").val() == "") {
                alert(referrer);
                referrer = document.referrer;

            } else {
                referrer = $("#previousLink").val();
            }
            if (referrer == "") {
                referrer = "rating_feedback_ques.php";
            } else {
                $("#backlink").val(referrer);
            }
            $(".back_link").attr('href', referrer);
        });


        function editQuestion(action)
        {
            $('#modal_action').html(action);
            $('#Rating_Modal').modal('show');
        }

        function saveQuestion()
        {
         //   $('#tQuestion_<?= $default_lang ?>_error').hide();
            if($('#tQuestion_<?= $default_lang ?>').val() == "") {
                $('#tQuestion_<?= $default_lang ?>_error').show();
                $('#tQuestion_<?= $default_lang ?>').focus();
                clearInterval(langVar);
                langVar = setTimeout(function() {
                    $('#tQuestion_<?= $default_lang ?>_error').hide();
                }, 5000);
                return false;
            }

            $('#tQuestion_Default').val($('#tQuestion_<?= $default_lang ?>').val());
            $('#tQuestion_Default').closest('.row').removeClass('has-error');
            $('#tQuestion_Default-error').remove();

            
            $('#Rating_Modal').modal('hide');
        }
    </script>   
</html>