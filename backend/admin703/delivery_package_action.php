<?php
include_once('../common.php');
$AUTH_OBJ->checkMemberAuthentication();
$ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY = ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY;
$ALLOW_FIELD_ARRAY = ['FIELD_NAME',
                      'PLACE_HOLDER',
                      'ORDER',
                      'INPUT_TYPE',
                      'ALLOW_FLOAT ',
                      'REQUIRED',
                      'EDITABLE',
                      'STATUS'];
if ($ALLOW_ADMIN_EDIT_FROM_FIELDS_NAME_ONLY == "Yes"){
    $ALLOW_FIELD_ARRAY = [];
}


$id = isset($_REQUEST['id'])?$_REQUEST['id']:'';
$success = isset($_REQUEST['success'])?$_REQUEST['success']:0;
$action = ($id != '')?'Edit':'Add';
$backlink = isset($_POST['backlink'])?$_POST['backlink']:'';
$previousLink = isset($_POST['backlink'])?$_POST['backlink']:'';
$tbl_name = 'delivery_fields';
$script = 'Delivery Package';
$select_order = $obj->MySQLSelect("SELECT count(iOrder) AS iOrder FROM ".$tbl_name);
$iOrder = isset($select_order[0]['iOrder'])?$select_order[0]['iOrder']:0;
$iDisplayOrder_max = $iOrder + 1; // Maximum order number
// fetch all lang from language_master table
$sql = "SELECT * FROM `language_master`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
if ($count_all > 0){
    for ($i = 0;$i < $count_all;$i++){
        $vNameval1 = 'vFieldName_'.$db_master[$i]['vCode'];
        $$vNameval1 = isset($_POST[$vNameval1])?$_POST[$vNameval1]:'';
    }
}
// set all variables with either post (when submit) either blank (when insert)
$vName = isset($_POST['vFieldName'])?$_POST['vFieldName']:'';
$eStatus_check = isset($_POST['eStatus'])?$_POST['eStatus']:'off';
$eStatus = ($eStatus_check == 'on')?'Active':'Inactive';
$iOrder = isset($_POST['iOrder'])?$_POST['iOrder']:$iOrder;
$temp_order = isset($_POST['temp_order'])?$_POST['temp_order']:"";
$eInputType = isset($_POST['eInputType'])?$_POST['eInputType']:"Text";
$tDesc = isset($_POST['tDesc'])?$_POST['tDesc']:"";
$eAllowFloat = isset($_POST['eAllowFloat'])?$_POST['eAllowFloat']:"";
$eRequired = isset($_POST['eRequired'])?$_POST['eRequired']:"Yes";
$eEditable = isset($_POST['eEditable'])?$_POST['eEditable']:"Yes";
if (isset($_POST['submit'])){

    if (SITE_TYPE == 'Demo'){
        header("Location:delivery_package_action.php?id=".$id.'&success=2');
        exit;
    }
    if ($temp_order > $iOrder){
        for ($i = $temp_order - 1;$i >= $iOrder;$i--){
            $sql = "UPDATE ".$tbl_name." SET iOrder = '".($i + 1)."' WHERE iOrder = '".$i."'";
            $obj->sql_query($sql);
        }
    }else if ($temp_order < $iOrder){
        for ($i = $temp_order + 1;$i <= $iOrder;$i++){
            $sql = "UPDATE ".$tbl_name." SET iOrder = '".($i - 1)."' WHERE iOrder = '".$i."'";
            $obj->sql_query($sql);
        }
    }
    $tDesc = "";
    $tDescArr = array();
    if (scount($db_master) > 0){
        $str = '';
        for ($i = 0;$i < scount($db_master);$i++){
            $vNameval1 = 'vFieldName_'.$db_master[$i]['vCode'];
            $$vNameval1 = $_REQUEST[$vNameval1];
            $str .= " ".$vNameval1." = '".$$vNameval1."',";
            if (isset($_POST['tDesc_'.$db_master[$i]['vCode']])){
                $tDesc = $_POST['tDesc_'.$db_master[$i]['vCode']];
            }
            $tDescArr["tDesc_".$db_master[$i]['vCode']] = $tDesc;
        }
    }
    $jsonDesc = getJsonFromAnArr($tDescArr);
    $q = "INSERT INTO ";
    $where = '';
    if ($id != ''){
        $q = "UPDATE ";
        $where = " WHERE `iDeliveryFieldId` = '".$id."'";
    }
    $vName = $_REQUEST['vFieldName_EN']; // ovewritehere
    $query = $q." `".$tbl_name."` SET ".$str."
		`vFieldName` = '".$vName."',
		`iOrder` = '".$iOrder."',
		`eInputType` = '".$eInputType."',
		`tDesc` = '".$jsonDesc."',
		`eAllowFloat` = '".$eAllowFloat."',
		`eRequired` = '".$eRequired."',
		`eEditable` = '".$eEditable."',
		`eStatus` = '".$eStatus."'".$where;
    $obj->sql_query($query);
    $id = ($id != '')?$id:$obj->GetInsertId();
    //header("Location:make_action.php?id=".$id.'&success=1');
    if ($action == "Add"){
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    }else{
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("location:".$backlink);
}
// for Edit
$tDescArrDisplay = array();
if ($action == 'Edit'){
    $sql = "SELECT * FROM ".$tbl_name." WHERE iDeliveryFieldId = '".$id."'";
    $db_data = $obj->MySQLSelect($sql);
    //echo "<pre>"; print_r($db_data); exit;
    $vLabel = $id;
    if (scount($db_data) > 0){
        for ($i = 0;$i < scount($db_master);$i++){
            foreach ($db_data as $key => $value){
                $vNameval = 'vFieldName_'.$db_master[$i]['vCode'];
                $$vNameval = $value[$vNameval];
                $vName = $value['vFieldName'];
                $eStatus = $value['eStatus'];
                $iOrder_db = $db_data[0]['iOrder'];
                //$tDesc 	   = $db_data[0]['tDesc'];
                $eAllowFloat = $db_data[0]['eAllowFloat'];
                $eRequired = $db_data[0]['eRequired'];
                $eEditable = $db_data[0]['eEditable'];
                $eInputType = $db_data[0]['eInputType'];
                $arrLang[$vNameval] = $$vNameval;
                $tDesc = json_decode($value['tDesc'],true);
                foreach ($tDesc as $key => $value){
                    $tDescArrDisplay[$key] = $value;
                }
            }
        }
    }
}
$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);
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
    <title>Admin | Delivery Field <?=$action;?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>

    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?=$action;?> Package Type</h2>
                    <a href="delivery_package.php" class="back_link">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if ($success == 1){ ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div><br/>
                    <?php }elseif ($success == 2){ ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div><br/>
                    <?php } ?>
                    <form method="post" name="_make_form" id="_make_form" action="">
                        <input type="hidden" name="id" value="<?=$id;?>"/>
                        <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="delivery_package.php"/>
                        <!-- <div class="row">
									<div class="col-lg-12">
										<label>Delivery Package Type Label<span class="red"> *</span></label>
									</div>
									<div class="col-lg-6">
										<input type="text" class="form-control" name="vFieldName"  id="vFieldName" value="<?=$vName;?>" placeholder="Delivery Package Label" required>
									</div>
								</div> -->
                        <?php if (scount($db_master) > 1){ ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Field Name</label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control <?=($id == "")?'readonly-custom':''?>" id="vFieldName_Default" value="<?=$arrLang['vFieldName_'.$default_lang];?>" data-originalvalue="<?=$arrLang['vFieldName_'.$default_lang];?>" readonly="readonly" <?php if ($id == ""){ ?> onclick="editPackageType('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != ""){ ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editPackageType('Edit')">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="modal fade" id="package_type_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                                <div class="modal-dialog">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Delivery Package Type
                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vFieldName_')">x</button>
                                            </h4>
                                        </div>

                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0;$i < $count_all;$i++){
                                                $vCode = $db_master[$i]['vCode'];
                                                $vLTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vNameval = 'vFieldName_'.$vCode;
                                                $$Desc = 'Delivery Package Name '.$vCode;
                                                $required = ($eDefault == 'Yes')?'required':'';
                                                $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Delivery Package Type (<?=$vLTitle;?>) <?php echo $required_msg; ?></label>

                                                    </div>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="<?=$vNameval;?>" id="<?=$vNameval;?>" value="<?=$$vNameval;?>" data-originalvalue="<?=$$vNameval;?>" placeholder="<?=$$Desc;?> Value" <?=$required;?>>
                                                        <div class="text-danger" id="<?=$vNameval.'_error';?>" style="display: none;"><?=$langage_lbl_admin['LBL_REQUIRED']?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1){
                                                        if ($EN_available){
                                                            if ($vCode == "EN"){ ?>
                                                                <div class="col-lg-12">
                                                                    <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vFieldName_', 'EN');" style="margin-top: 10px">Convert To All Language</button>
                                                                </div>
                                                            <?php }
                                                        }else{
                                                            if ($vCode == $default_lang){ ?>
                                                                <div class="col-lg-12">
                                                                    <button type="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vFieldName_', '<?=$default_lang?>');" style="margin-top: 10px">Convert To All Language</button>
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
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important" onclick="savePackageType()"><?=$langage_lbl['LBL_Save'];?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vFieldName_')"><?=$langage_lbl['LBL_CANCEL_TXT'];?></button>
                                            </div>
                                        </div>

                                        <div style="clear:both;"></div>
                                    </div>
                                </div>

                            </div>
                        <?php }else{ ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Field Name</label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="vFieldName_<?=$default_lang?>" name="vFieldName_<?=$default_lang?>" value="<?=$arrLang['vFieldName_'.$default_lang];?>">
                                </div>
                            </div>
                        <?php } ?>
                        <?php /*if($count_all > 0) {
										for($i=0;$i<$count_all;$i++) {
											$vCode = $db_master[$i]['vCode'];
											$vLTitle = $db_master[$i]['vTitle'];
											$eDefault = $db_master[$i]['eDefault'];

											$vNameval = 'vFieldName_'.$vCode;
											$$Desc = 'Delivery Package Name '.$vCode;
											

											$required = ($eDefault == 'Yes')?'required':'';
											$required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
										?>
										<div class="row">
											<div class="col-lg-12">
												<label> Delivery Package Type (<?=$vLTitle;?>) <?=$required_msg;?></label>
											</div>
											<div class="col-lg-6">
												<input type="text" class="form-control" name="<?=$vNameval;?>"  id="<?=$vNameval;?>" value="<?=$$vNameval;?>" placeholder="<?=$$Desc;?> Value" <?=$required;?>>
												<div class="text-danger" id="<?= $vNameval.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
											</div>
											<?php if($vCode == $default_lang  && scount($db_master) > 1){ ?>
                                            <div class="col-lg-6">
                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vFieldName_', '<?= $default_lang ?>');">Convert To All Language</button>
                                            </div>
                                            <?php } ?>
										</div>

										
										<?php }
									}*/ ?>
                        <!-- <div class="row">
									<div class="col-lg-12">
										<label>Place Holder<span class="red"> *</span></label>
									</div>
									<div class="col-lg-6">
										<input type="text" class="form-control" name="tDesc"  id="tDesc" value="<?=$tDesc;?>" placeholder="Description" required>
									</div>
								</div> -->

                        <?php if (scount($db_master) > 1){ ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Place Holder <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control <?=($id == "")?'readonly-custom':''?>"
                                            id="tDesc_Default" name="tDesc_Default"
                                            value="<?=$tDescArrDisplay['tDesc_'.$default_lang];?>"
                                            data-originalvalue="<?=$tDescArrDisplay['tDesc_'.$default_lang];?>"
                                            readonly="readonly" <?php if ($id == ""){ ?> onclick="editDescription('Add')" <?php } ?>>
                                </div>
                                <?php if ($id != ""){ ?>
                                    <div class="col-lg-2">
                                        <button type="button" class="btn btn-info" data-toggle="tooltip"
                                                data-original-title="Edit" onclick="editDescription('Edit')"><span
                                                    class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="modal fade" id="tDesc_Modal" tabindex="-1" role="dialog" aria-hidden="true"
                                    data-backdrop="static" data-keyboard="false">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content nimot-class">
                                        <div class="modal-header">
                                            <h4>
                                                <span id="modal_action"></span> Place Holder
                                                <button type="button" class="close" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'tDesc_')">x
                                                </button>
                                            </h4>
                                        </div>

                                        <div class="modal-body">
                                            <?php
                                            for ($i = 0;$i < $count_all;$i++){
                                                $vCode = $db_master[$i]['vCode'];
                                                $vTitle = $db_master[$i]['vTitle'];
                                                $eDefault = $db_master[$i]['eDefault'];
                                                $vValue = 'tDesc_'.$vCode;
                                                $$vValue = $tDescArrDisplay[$vValue];
                                                $required = ($eDefault == 'Yes')?'required':'';
                                                $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
                                                ?>
                                                <?php
                                                $page_title_class = 'col-lg-12';
                                                if (scount($db_master) > 1){
                                                    if ($EN_available){
                                                        if ($vCode == "EN"){
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    }else{
                                                        if ($vCode == $default_lang){
                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                        }
                                                    }
                                                }
                                                ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Place Holder (<?=$vTitle;?>
                                                            ) <?php echo $required_msg; ?></label>

                                                    </div>
                                                    <div class="<?=$page_title_class?>">
                                                        <input type="text" class="form-control" name="<?=$vValue;?>"
                                                                id="<?=$vValue;?>" value="<?=$$vValue;?>"
                                                                data-originalvalue="<?=$$vValue;?>"
                                                                placeholder="Place Holder <?=$vTitle;?> Value">
                                                        <div class="text-danger" id="<?=$vValue.'_error';?>"
                                                                style="display: none;"><?=$langage_lbl_admin['LBL_REQUIRED']?></div>
                                                    </div>
                                                    <?php
                                                    if (scount($db_master) > 1){
                                                        if ($EN_available){
                                                            if ($vCode == "EN"){ ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('tDesc_', 'EN');">
                                                                        Convert To All Language
                                                                    </button>
                                                                </div>
                                                            <?php }
                                                        }else{
                                                            if ($vCode == $default_lang){ ?>
                                                                <div class="col-md-3 col-sm-3">
                                                                    <button type="button" name="allLanguage"
                                                                            id="allLanguage" class="btn btn-primary"
                                                                            onClick="getAllLanguageCode('tDesc_', '<?=$default_lang?>');">
                                                                        Convert To All Language
                                                                    </button>
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
                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                                                <strong><?=$langage_lbl['LBL_NOTE'];?>
                                                    : </strong><?=$langage_lbl['LBL_SAVE_INFO'];?></h5>
                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                <button type="button" class="save" style="margin-left: 0 !important"
                                                        onclick="saveDescription()"><?=$langage_lbl['LBL_Save'];?></button>
                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal"
                                                        onclick="resetToOriginalValue(this, 'tDesc_')"><?=$langage_lbl['LBL_CANCEL_TXT'];?></button>
                                            </div>
                                        </div>

                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php }else{ ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Place Holder <span class="red"> *</span></label>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <input type="text" class="form-control" id="tDesc_<?=$default_lang?>" name="tDesc_<?=$default_lang?>" value="<?=$arrLang['tDesc_'.$default_lang];?>" required>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Order</label>
                            </div>
                            <div class="col-lg-6">

                                <input type="hidden" name="temp_order" id="temp_order" value="<?=($action == 'Edit')?$iOrder_db:'1';?>">
                                <?php
                                $display_numbers = ($action == "Add")?$iDisplayOrder_max:$iOrder;
                                ?>
                                <select name="iOrder" class="form-control">
                                    <?php for ($i = 1;$i <= $display_numbers;$i++){ ?>
                                        <option value="<?=$i?>" <?php if ($i == $iOrder_db){
                                            echo "selected";
                                        } ?>> -- <?=$i?> --
                                        </option>
                                    <?php } ?>
                                </select>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>InputType<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='eInputType' id="eInputType">
                                    <option value="">Select</option>
                                    <option value="Text" <?=($eInputType == "Text")?'selected':"";?>>Text</option>
                                    <option value="Textarea" <?=($eInputType == "Textarea")?'selected':"";?>>Textarea</option>
                                    <option value="Select" <?=($eInputType == "Select")?'selected':"";?>>Select</option>
                                    <option value="Number" <?=($eInputType == "Number")?'selected':"";?>>Number</option>

                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>AllowFloat<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='eAllowFloat' id="eAllowFloat">
                                    <option value="">Select</option>
                                    <option value="Yes" <?php if ($eAllowFloat == "Yes"){
                                        echo 'selected';
                                    } ?>>Yes
                                    </option>
                                    <option value="No" <?=($eAllowFloat == "No")?'selected':"";?>>No</option>

                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Required<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='eRequired' id="eRequired">
                                    <option value="">Select</option>
                                    <option value="Yes" <?=($eRequired == "Yes")?'selected':"";?>>Yes</option>
                                    <option value="No" <?=($eRequired == "No")?'selected':"";?>>No</option>

                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Editable<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <select class="form-control" name='eEditable' id="eEditable">
                                    <option value="">Select</option>
                                    <option value="Yes" <?=($eEditable == "Yes")?'selected':"";?>>Yes</option>
                                    <option value="No" <?=($eEditable == "No")?'selected':"";?>>No</option>

                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Status</label>
                            </div>
                            <div class="col-lg-6">
                                <div class="make-switch" data-on="success" data-off="warning">
                                    <input type="checkbox" name="eStatus" <?=($id != '' && $eStatus == 'Inactive')?'':'checked';?>/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <input type="submit" class=" btn btn-default" name="submit" id="submit" value="<?=$action;?> Delivery Package">
                                <input type="reset" value="Reset" class="btn btn-default">
                                <!-- <a href="javascript:void(0);" onclick="reset_form('_make_form');" class="btn btn-default">Reset</a> -->
                                <a href="delivery_package.php" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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

<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
</body>
<!-- END BODY-->
</html>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") { //alert('pre1');
            referrer = document.referrer;
            // alert(referrer);
        } else { //alert('pre2');
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "delivery_package.php";
        } else { //alert('hi');
            $("#backlink").val(referrer);
            // alert($("#backlink").val(referrer));
        }
        $(".back_link").attr('href', referrer);
        //alert($(".back_link").attr('href',referrer));
    });

    function editPackageType(action) {
        $('#modal_action').html(action);
        $('#package_type_Modal').modal('show');
    }

    function savePackageType() {
        if ($('#vFieldName_<?= $default_lang ?>').val() == "") {
            $('#vFieldName_<?= $default_lang ?>_error').show();
            $('#vFieldName_<?= $default_lang ?>').focus();
            clearInterval(myVar);
            myVar = setTimeout(function () {
                $('#vFieldName_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#vFieldName_Default').val($('#vFieldName_<?= $default_lang ?>').val());
        $('#package_type_Modal').modal('hide');
    }

    function editDescription(action) {
        $('#modal_action').html(action);
        $('#tDesc_Modal').modal('show');
    }

    function saveDescription() {
        if ($('#tDesc_<?= $default_lang ?>').val() == "") {
            $('#tDesc_<?= $default_lang ?>_error').show();
            $('#tDesc_<?= $default_lang ?>').focus();
            clearInterval(langVar);
            langVar = setTimeout(function () {
                $('#tDesc_<?= $default_lang ?>_error').hide();
            }, 5000);
            return false;
        }
        $('#tDesc_Default').val($('#tDesc_<?= $default_lang ?>').val());
        $('#tDesc_Default').closest('.row').removeClass('has-error');
        $('#tDesc_Default-error').remove();
        $('#tDesc_Modal').modal('hide');
    }
</script>