<?php
include_once('../common.php');

$eSystem = "DeliverAll";
define("TRIP_REASON", "trip_reason");
define("USER_PROFILE_MASTER", "user_profile_master");
$script = 'BusinessTripReason';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$sql = "SELECT vCode,vTitle,eDefault FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$lableNameArr = array("vReasonTitle");
$lableArr = array("Reason");
$sql = "SELECT * FROM " . USER_PROFILE_MASTER . " WHERE eStatus !='Deleted'";
$data_drv = $obj->MySQLSelect($sql);
$userDataArr = array();
$profileMasterId = 0;
for ($u = 0; $u < scount($data_drv); $u++) {
    $shortProfileName = (array) json_decode($data_drv[$u]['vShortProfileName']);
    $profileName = (array) json_decode($data_drv[$u]['vProfileName']);
    $title = (array) json_decode($data_drv[$u]['vTitle']);
    $subTitle = (array) json_decode($data_drv[$u]['vSubTitle']);
    $eng_arr = array();
    $eng_arr['iUserProfileMasterId'] = $data_drv[$u]['iUserProfileMasterId'];
    $eng_arr['vShortProfileName'] = $shortProfileName['vShortProfileName_'.$default_lang];
    $eng_arr['vProfileName'] = $profileName['vProfileName_'.$default_lang];
    $eng_arr['vTitle'] = $title['vTitle_'.$default_lang];
    $eng_arr['vSubTitle'] = $subTitle['vSubTitle_'.$default_lang];
    $eng_arr['eStatus'] = $data_drv[$u]['eStatus'];
    $userDataArr[] = $eng_arr;
}
if (isset($_POST['btnsubmit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-trip-reason-taxi-service')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:trip_reason.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-trip-reason-taxi-service')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        header("Location:trip_reason.php");
        exit;
    }
    if (SITE_TYPE == 'Demo') {
        header("Location:trip_reason_action.php?id=" . $id . "&success=2");
        exit;
    }
    $reasonTitleArr = array();
    if (isset($_POST['iUserProfileMasterId']) && $_POST['iUserProfileMasterId'] != "") {
        $profileMasterId = $_POST['iUserProfileMasterId'];
    }
    for ($i = 0; $i < scount($db_master); $i++) {
        $vTitle = "";
        if (isset($_POST['vReasonTitle_' . $db_master[$i]['vCode']])) {
            $vTitle = $_POST['vReasonTitle_' . $db_master[$i]['vCode']];
        }
        $q = "INSERT INTO ";
        $where = '';
        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iTripReasonId` = '" . $id . "'";
        }
        $reasonTitleArr["vReasonTitle_" . $db_master[$i]['vCode']] = $vTitle;
    }
    $time = time();
    if (scount($reasonTitleArr) > 0) {
        //$jsonTitle = $obj->cleanQuery(json_encode($reasonTitleArr));
        $jsonTitle = getJsonFromAnArr($reasonTitleArr);
        $query = $q . " `" . TRIP_REASON . "` SET `vReasonTitle` = '" . $jsonTitle . "',`iUserProfileMasterId`='" . $profileMasterId . "'" . $where;
        $obj->sql_query($query);
        $id = ($id != '') ? $id : $obj->GetInsertId();
    }
    if ($action == "Add") {
        $_SESSION['var_msg'] = $langage_lbl['LBL_RECORD_INSERT_MSG'];
        $_SESSION['success'] = "1";
        header("Location:trip_reason.php");
        exit;
    } else {
        $_SESSION['var_msg'] = $langage_lbl['LBL_Record_Updated_successfully'];
        $_SESSION['success'] = "1";
        header("Location:trip_reason.php");
        exit;
    }
}
// for Edit
$userEditDataArr = array();
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . TRIP_REASON . " WHERE iTripReasonId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    if (scount($db_data) > 0) {
        $vReasonTitle = json_decode($db_data[0]['vReasonTitle'], true);
        foreach ($vReasonTitle as $key => $value) {
            $userEditDataArr[$key] = $value;
        }
        $profileMasterId = $db_data[0]['iUserProfileMasterId'];
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
        <title>Admin | Business Trip Reason <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php
            include_once('header.php');
            include_once('left_menu.php');
            ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2> Business Trip Reason </h2>
                            <a href="javascript:void(0);" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable msgs_hide">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?= $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable ">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } else if ($success == 3) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                    <?php echo $_REQUEST['varmsg']; ?> 
                                </div><br/>	
                            <?php } ?>
                            <?php if (isset($_REQUEST['var_msg']) && $_REQUEST['var_msg'] != Null) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record  Not Updated .
                                </div><br/>
                            <?php } ?>                   
                            <form id="_trip_reason" name="_trip_reason" method="post" action="" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="trip_reason.php"/>
                                <div class="row"> 
                                    <div class="col-lg-12" id="errorMessage"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Select Organization type <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <!--<select class="form-control" name = 'iUserProfileMasterId' id="iUserProfileMasterId" required="" onchange="changeCode_distance(this.value);"> Commented on 29-02-2020 Its not defined function-->
										<select class="form-control" name = 'iUserProfileMasterId' id="iUserProfileMasterId">
                                            <option value="">Select Organization type</option>
                                            <?php
                                            for ($p = 0; $p < scount($userDataArr); $p++) {
                                                ?>
                                                <option value = "<?= $userDataArr[$p]['iUserProfileMasterId'] ?>" <?php if ($profileMasterId == $userDataArr[$p]['iUserProfileMasterId']) { ?>selected<?php } ?>><?= $userDataArr[$p]['vProfileName'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <?php if (scount($db_master) > 1) { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Reason <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vReasonTitle_Default" name="vReasonTitle_Default" value="<?= isset($userEditDataArr['vReasonTitle_'.$default_lang]) ? $userEditDataArr['vReasonTitle_'.$default_lang]: ''; ?>" data-originalvalue="<?= isset($userEditDataArr['vReasonTitle_'.$default_lang]) ? $userEditDataArr['vReasonTitle_'.$default_lang] : '' ; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editTripReason('Add')" <?php } ?>>
                                    </div>
                                    <?php if($id != "") { ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editTripReason('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <div  class="modal fade" id="trip_reason_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                    <div class="modal-dialog modal-lg" >
                                        <div class="modal-content nimot-class">
                                            <div class="modal-header">
                                                <h4>
                                                    <span id="modal_action"></span> Reason
                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vReasonTitle_')">x</button>
                                                </h4>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <?php
                                                    
                                                    for ($i = 0; $i < $count_all; $i++) 
                                                    {
                                                        $vCode = $db_master[$i]['vCode'];
                                                        $vTitle = $db_master[$i]['vTitle'];
                                                        $eDefault = $db_master[$i]['eDefault'];
                                                        $vValue = 'vReasonTitle_' . $vCode;
                                                        $$vValue = isset($userEditDataArr[$vValue]) ? $userEditDataArr[$vValue] : '';

                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                ?>
                                                        <?php
                                                        $page_title_class = 'col-lg-12';
                                                        if (scount($db_master) > 1) {
                                                            if($EN_available) {
                                                                if($vCode == "EN") { 
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            } else { 
                                                                if($vCode == $default_lang) {
                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                }
                                                            }
                                                        }
                                                        ?>
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <label>Reason (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                                
                                                            </div>
                                                            <div class="<?= $page_title_class ?>">
                                                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" value="<?= $$vValue; ?>" data-originalvalue="<?= $$vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                                                <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                            </div>
                                                            <?php
                                                            if (scount($db_master) > 1) {
                                                                if($EN_available) {
                                                                    if($vCode == "EN") { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vReasonTitle_', 'EN');" >Convert To All Language</button>
                                                                    </div>
                                                                <?php }
                                                                } else { 
                                                                    if($vCode == $default_lang) { ?>
                                                                    <div class="col-md-3 col-sm-3">
                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vReasonTitle_', '<?= $default_lang ?>');" >Convert To All Language</button>
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
                                                    <button type="button" class="save" style="margin-left: 0 !important" onclick="saveTripReason()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vReasonTitle_')"><?= $langage_lbl['LBL_CANCEL_TXT']; ?></button>
                                                </div>
                                            </div>
                                            
                                            <div style="clear:both;"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Reason <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="text" class="form-control" id="vReasonTitle_<?= $default_lang ?>" name="vReasonTitle_<?= $default_lang ?>" value="<?= $userEditDataArr['vReasonTitle_'.$default_lang]; ?>" required>
                                    </div>
                                </div>
                                <?php } ?>
                                <?php /*
                                if (scount($db_master) > 0) {
                                    for ($i = 0; $i < scount($db_master); $i++) {
                                        $vCode = $db_master[$i]['vCode'];
                                        $vTitle = $db_master[$i]['vTitle'];
                                        $eDefault = $db_master[$i]['eDefault'];
                                        $descVal = 'tDescription_' . $vCode;
                                        for ($l = 0; $l < scount($lableNameArr); $l++) {
                                            $lableText = $lableArr[$l];
                                            $lableName = $lableNameArr[$l] . '_' . $vCode;
                                            $required = ($eDefault == 'Yes') ? 'required' : '';
                                            $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label><?= $lableText; ?> (<?= $vTitle; ?>) <?php echo $required_msg; ?></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <input type="text" class="form-control" name="<?= $lableName; ?>" id="<?= $lableName; ?>" value="<?= $userEditDataArr[$lableName]; ?>" placeholder="<?= $vTitle; ?> Value" <?= $required; ?>>
                                                    <div class="text-danger" id="<?= $lableName.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                </div>
                                                <?php if ($vCode == $default_lang && scount($db_master) > 1) { ?>
                                                    <div class="col-md-6 col-sm-6">
                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('<?php echo $lableNameArr[$l].'_'; ?>', '<?= $default_lang ?>');">Convert To All Language</button>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <?php
                                        }
                                    }
                                }*/
                                ?>
                                <div class="col-lg-12">
                                    <?php if (($action == 'Edit' && $userObj->hasPermission('edit-trip-reason-taxi-service')) || ($action == 'Add' && $userObj->hasPermission('create-trip-reason-taxi-service'))) { ?>
                                        <input type="submit" class="btn btn-default" name="btnsubmit" id="btnsubmit" value="<?= $action; ?> Reason" >
                                        <input type="reset" value="Reset" class="btn btn-default">
                                    <?php } ?>
                                    <a href="trip_reason.php" class="btn btn-default back_link">Cancel</a>
                                </div>          
                            </form>
                        </div>      
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
        <!--END MAIN WRAPPER -->
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>
        <?php include_once('footer.php'); ?>
        <script type="text/javascript" src="js/validation/jquery.validate.min.js" ></script>
        <script type="text/javascript" src="js/validation/additional-methods.min.js" ></script>
        <script type="text/javascript" src="js/form-validation.js" ></script>
        <script>
                                                            $('[data-toggle="tooltip"]').tooltip();
                                                            var successMSG1 = '<?php echo $success; ?>';
                                                            if (successMSG1 != '') {
                                                                setTimeout(function () {
                                                                    $(".msgs_hide").hide(1000)
                                                                }, 5000);
                                                            }
        </script>
        <!--For Faretype End--> 
        <script type="text/javascript" language="javascript">
            
            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "trip_reason.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });

            function editTripReason(action)
            {
                $('#modal_action').html(action);
                $('#trip_reason_Modal').modal('show');
            }

            function saveTripReason()
            {
                if($('#vReasonTitle_<?= $default_lang ?>').val() == "") {
                    $('#vReasonTitle_<?= $default_lang ?>_error').show();
                    $('#vReasonTitle_<?= $default_lang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function() {
                        $('#vReasonTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#vReasonTitle_Default').val($('#vReasonTitle_<?= $default_lang ?>').val());
                $('#vReasonTitle_Default').closest('.row').removeClass('has-error');
                $('#vReasonTitle_Default-error').remove();
                $('#trip_reason_Modal').modal('hide');
            }
        </script>
    </body>
    <!-- END BODY-->
</html>
