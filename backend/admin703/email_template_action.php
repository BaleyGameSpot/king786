<?
include_once('../common.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$vPurpose = isset($_REQUEST['vPurpose']) ? $_REQUEST['vPurpose'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = $script = 'email_templates';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
// fetch all lang from language_master table
// $sql = "SELECT * FROM `language_master` ORDER BY `eDefault`";
$sql = "SELECT * FROM `language_master`  ORDER BY `eDefault`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
// set all variables with either post (when submit) either blank (when insert)
$iEmailId = isset($_POST['iEmailId']) ? $_POST['iEmailId'] : $id;
if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vSubject = 'vSubject_' . $db_master[$i]['vCode'];
        $$vSubject = isset($_POST[$vSubject]) ? $_POST[$vSubject] : '';
        $vBody = 'vBody_' . $db_master[$i]['vCode'];
        $$vBody = isset($_POST[$vBody]) ? $_POST[$vBody] : '';
    }
}
if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-email-templates')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Email Templates.';
        header("Location:email_template.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-email-templates')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Email Templates.';
        header("Location:email_template.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:email_template_action.php?id=" . $iEmailId . '&success=2');
        exit;
    }
    if (scount($db_master) > 0) {
        $str = '';
        for ($i = 0; $i < scount($db_master); $i++) {
            $vSubject = 'vSubject_' . $db_master[$i]['vCode'];
            $$vSubject = $_REQUEST[$vSubject];
            $vBody = 'vBody_' . $db_master[$i]['vCode'];
            $$vBody = $_REQUEST[$vBody];

            $str .= " " . $vSubject . " = '" . ($$vSubject) . "', " . $vBody . " = '" . ($$vBody) . "', ";
        }
    }
    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $where = " WHERE `iEmailId` = '" . $iEmailId . "'";
    }
    $query = $q . " `" . $tbl_name . "` SET " . $str . "
		`vPurpose` = '" . $vPurpose . "'"
            . $where;
    $Id = $obj->sql_query($query);
    if ($action == 'Add') {
        $iEmailId = $obj->GetInsertId();
    }
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:" . $backlink);
}
// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iEmailId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {
                $vSubject = 'vSubject_' . $db_master[$i]['vCode'];
                $$vSubject = $value[$vSubject];
                $vBody = 'vBody_' . $db_master[$i]['vCode'];
                $$vBody = $value[$vBody];
                //$vEmail_Code = $value['vEmail_Code'];
                $vPurpose = $value['vPurpose'];
                $vSection = $value['vSection'];
            }
        }
    }
}

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <title>Admin | Email Template <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <? include_once('global_files.php'); ?>
    <!-- PAGE LEVEL STYLES -->
    <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
    <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
    <link rel="stylesheet" href="../assets/css/Markdown.Editor.hack.css" />
    <link rel="stylesheet" href="../assets/plugins/CLEditor1_4_3/jquery.cleditor.css" />
    <link rel="stylesheet" href="../assets/css/jquery.cleditor-hack.css" />
    <link rel="stylesheet" href="../assets/css/bootstrap-wysihtml5-hack.css" />
    <script type="text/javascript">
        (function () {
            var converter1 = Markdown.getSanitizingConverter();
            var editor1 = new Markdown.Editor(converter1);
            editor1.run();
        });
    </script>
    <style>
        ul.wysihtml5-toolbar > li {
            position: relative;
        }
    </style>
</head>
<body class="padTop53 " >
    <!-- MAIN WRAPPER -->
    <div id="wrap">
        <? include_once('header.php'); ?>
        <? include_once('left_menu.php'); ?>
        <!--PAGE CONTENT -->
        <div id="content">
            <div class="inner">
                <div class="row">
                    <div class="col-lg-12">
                        <h2><?= $action; ?> Email Template</h2>
                        <a href="email_template.php" class="back_link">
                            <input type="button" value="Back to Listing" class="add-btn">
                        </a>
                    </div>
                </div>
                <hr />
                <div class="body-div">
                    <div class="form-group">
                        <? if ($success == 1) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                 <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                            </div><br/>
                        <? } elseif ($success == 2) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                            </div><br/>
                        <? } ?> 
                        <form method="post" name="_email_template_form" id="_email_template_form" action=""  enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= $id; ?>"/>
                            <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                            <input type="hidden" name="backlink" id="backlink" value="email_template.php"/>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Purpose</label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" name="vPurpose"  id="vPurpose" value="<?= $vPurpose; ?>" placeholder="Purpose">
                                </div>
                            </div>

                            <?php if (scount($db_master) > 1) { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Email Subject <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vSubject_Default" value="<?= $db_data[0]['vSubject_'.$default_lang]; ?>" data-originalvalue="<?= $db_data[0]['vSubject_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editEmailSubjectBody('Add')" <?php } ?>>
                                </div>
                                <?php if($id != "") { ?>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editEmailSubjectBody('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                </div>
                                <?php } ?>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Email Body <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <textarea class="form-control ckeditor" rows="10" id="vBody_Default" readonly="readonly"><?= $db_data[0]['vBody_'.$default_lang]; ?></textarea>
                                </div>
                                <?php if($id != "") { ?>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editEmailBody('Edit', 'EmailBody_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                </div>
                                <?php } ?>
                            </div>

                            <div  class="modal fade" id="EmailSubjectBody_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg" >
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Email Subject
                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vSubject_')">x</button>
                                            </h4>
                                        </div>
                                        
                                        <div class="modal-body">
                                            <?php
                                                
                                                for ($i = 0; $i < $count_all; $i++) 
                                                {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];
                                                    $vSubject = 'vSubject_' . $vCode;

                                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Subject (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                            
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
                                                            <input type="text" class="form-control" name="<?= $vSubject; ?>" id="<?= $vSubject; ?>" value="<?= $$vSubject; ?>" data-originalvalue="<?= $$vSubject; ?>" placeholder="<?= $vLTitle; ?> Value">
                                                            <div class="text-danger" id="<?= $vSubject.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                        </div>

                                                        <?php
                                                        if (scount($db_master) > 1) {
                                                            if($EN_available) {
                                                                if($vCode == "EN") { ?>
                                                                <div class="col-lg-3">
                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vSubject_', 'EN');">Convert To All Language</button>
                                                                </div>
                                                            <?php }
                                                            } else { 
                                                                if($vCode == $default_lang) { ?>
                                                                <div class="col-lg-3">
                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vSubject_', '<?= $default_lang ?>');">Convert To All Language</button>
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
                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveEmailSubjectBody()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vSubject_')">Cancel</button>
                                            </div>
                                        </div>
                                        
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <div  class="modal fade" id="EmailBody_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg" >
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Email Body
                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                            </h4>
                                        </div>
                                        
                                        <div class="modal-body">
                                            <?php
                                                
                                                for ($i = 0; $i < $count_all; $i++) 
                                                {
                                                    $vCode = $db_master[$i]['vCode'];
                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                    $eDefault = $db_master[$i]['eDefault'];

                                                    $vBody = 'vBody_' . $vCode;
                                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <label>Body (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                            
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $vBody; ?>"  id="<?= $vBody; ?>"  placeholder="<?= $vLTitle; ?> Value"> <?= $$vBody; ?></textarea>
                                                            <div class="text-danger" id="<?= $vBody.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                        </div>
                                                    </div> 
                                                <?php 
                                                }
                                            ?>
                                        </div>
                                        <div class="modal-footer" style="margin-top: 0">
                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveEmailBody('vBody_', 'EmailBody_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                        
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Email Subject <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="vSubject_<?= $default_lang ?>" name="vSubject_<?= $default_lang ?>" value="<?= $db_data[0]['vSubject_'.$default_lang]; ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Email Body <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <textarea class="form-control ckeditor" rows="10" id="vBody_<?= $default_lang ?>" name="vBody_<?= $default_lang ?>" required><?= $db_data[0]['vBody_'.$default_lang]; ?></textarea>
                                </div>
                            </div>
                            <?php } ?>

                            <?/*
                            if ($count_all > 0) {
                                for ($i = 0; $i < $count_all; $i++) {
                                    $vCode = $db_master[$i]['vCode'];
                                    $vLTitle = $db_master[$i]['vTitle'];
                                    $eDefault = $db_master[$i]['eDefault'];
                                    $vSubject = 'vSubject_' . $vCode;
                                    $vBody = 'vBody_' . $vCode;
                                    $required = ($eDefault == 'Yes') ? 'required' : '';
                                    $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?= $vLTitle; ?> Subject <?= $required_msg; ?></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" class="form-control " name="<?= $vSubject; ?>"  id="<?= $vSubject; ?>" value="<?= $$vSubject; ?>" placeholder="<?= $vLTitle; ?> Subject" <?= $required; ?>>
                                            <div class="text-danger" id="<?= $vSubject.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                        </div>
                                        <? if($vCode == $default_lang  && scount($db_master) > 1){ ?>
                                        <div class="col-md-6 col-sm-6">
                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vSubject_', '<?= $default_lang ?>');">Convert To All Language</button>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <!--- Editor -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label><?= $vLTitle; ?> Body <?= $required_msg; ?></label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <textarea class="form-control ckeditor" rows="10" name="<?= $vBody; ?>"  id="<?= $vBody; ?>"  placeholder="<?= $vLTitle; ?> Body" <?= $required; ?>> <?= $$vBody; ?></textarea>
                                        </div>
                                    </div>
                                    <!--- Editor -->
                                <?
                                }
                            }*/
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
<?php if (($action == 'Edit' && $userObj->hasPermission('edit-email-templates')) || ($action == 'Add' && $userObj->hasPermission('create-email-templates'))) { ?>
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Email Template">
                                        <input type="reset" value="Reset" class="btn btn-default">
<?php } ?>
                                    <a href="email_template.php" class="btn btn-default back_link">Cancel</a>
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
    <div class="row loding-action" id="loaderIcon" style="display:none; z-index: 99999">
        <div align="center">                                                                       
            <img src="default.gif">                                                              
            <span>Language Translation is in Process. Please Wait...</span>                       
        </div>
    </div>
<? include_once('footer.php'); ?>
    <!-- PAGE LEVEL SCRIPTS -->
<!--<script src="../assets/plugins/CLEditor1_4_3/jquery.cleditor.min.js"></script>
    <script src="../assets/plugins/wysihtml5/lib/js/wysihtml5-0.3.0.js"></script>
    <script src="../assets/plugins/bootstrap-wysihtml5-hack.js"></script>Remove it becoz image icon is not working-->
    <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
    <script src="../assets/plugins/ckeditor/config.js"></script>
</body>
</html>
<script>
//        $(function () {
//            $('.wysihtml5').wysihtml5({
//                "html": true,
//            });
//        });
</script>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "email_template.php";
        } else { //alert('hi');
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });

    function editEmailSubjectBody(action)
    {
        $('#modal_action').html(action);
        $('#EmailSubjectBody_Modal').modal('show');
    }

    function saveEmailSubjectBody()
    {
        //var vBodyLength = CKEDITOR.instances['vBody_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

        if($('#vSubject_<?= $default_lang ?>').val() == "") {
            $('#vSubject_<?= $default_lang ?>_error').show();
            $('#vSubject_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function() {
                $('#vSubject_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        /*else if(!vBodyLength) {
            $('#vBody_<?= $default_lang ?>_error').show();
            $('#vBody_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function() {
                $('#vBody_<?= $default_lang ?>_error').hide();
            }, 5000);
            e.preventDefault();
            return false;
        }*/

        $('#vSubject_Default').val($('#vSubject_<?= $default_lang ?>').val());
        // var vBodyHTML = CKEDITOR.instances['vBody_<?= $default_lang ?>'].getData();
        // CKEDITOR.instances['vBody_Default'].setData(vBodyHTML);
        $('#EmailSubjectBody_Modal').modal('hide');
    }

    function editEmailBody(action, modal_id)
    {
        $('#'+modal_id).find('#modal_action').html(action);
        $('#'+modal_id).modal('show');
    }

    function saveEmailBody(input_id, modal_id)
    {
        var DescLength = CKEDITOR.instances[input_id+'<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
        if(!DescLength) {
            $('#'+input_id+'<?= $default_lang ?>_error').show();
            $('#'+input_id+'<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function() {
                $('#'+input_id+'<?= $default_lang ?>_error').hide();
            }, 5000);
            e.preventDefault();
            return false;
        }

        var DescHTML = CKEDITOR.instances[input_id + '<?= $default_lang ?>'].getData();
        CKEDITOR.instances[input_id+'Default'].setData(DescHTML);
        $('#'+modal_id).modal('hide');
    }
</script>