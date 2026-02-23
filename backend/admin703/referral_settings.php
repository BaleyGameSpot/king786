<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-referral-settings')){
    $userObj->redirect();
}
$script = 'Referral';
$tblname = 'multi_level_referral_master';
if (isset($_POST['iReferralId'])){
    $sql = "SELECT iLevel FROM $tblname WHERE eStatus = 'Active' ORDER BY iLevel DESC LIMIT 1";
    $iLevelData = $obj->MySQLSelect($sql);
    $iLevel = $iLevelData[0]['iLevel'];
    $iReferralId = $_POST['iReferralId'];
    $action = ($iReferralId != "")?'Edit':'Add';
    if ($action == "Add" && !$userObj->hasPermission('create-referral-settings')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create referral level.';
        header("Location:referral_settings.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-referral-settings')){
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update referral level.';
        header("Location:referral_settings.php");
        exit;
    }
    if (SITE_TYPE == 'Demo'){
        header("Location:referral_settings.php?success=2");
        exit;
    }
    if ($iReferralId == ""){
        if ($iLevel < $REFERRAL_LEVEL){
            $Data_Insert['vTitle'] = 'Level - '.($iLevel + 1);
            $Data_Insert['iAmount'] = $_POST['iAmount'];
            $Data_Insert['iLevel'] = $iLevel + 1;
            $obj->MySQLQueryPerform($tblname,$Data_Insert,'insert');
            $_SESSION['success'] = 1;
            $_SESSION['var_msg'] = "Referral Level added successfully.";
        }else{
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = "Cannot add referral level. You can add maximum upto Level - $REFERRAL_LEVEL.";
        }
    }else{
        $where = " iReferralId = $iReferralId";
        $Data_Update['iAmount'] = $_POST['iAmount'];
        $obj->MySQLQueryPerform($tblname,$Data_Update,'update',$where);
        $_SESSION['success'] = 1;
        $_SESSION['var_msg'] = "Referral Level updated successfully.";
    }
}
$sql = "SELECT iLevel FROM $tblname WHERE eStatus = 'Active' ORDER BY iLevel DESC LIMIT 1";
$iLevelData = $obj->MySQLSelect($sql);
$iLevel = $iLevelData[0]['iLevel'];
$sql = "SELECT * FROM $tblname WHERE eStatus = 'Active' ORDER BY iLevel ASC LIMIT $REFERRAL_LEVEL";
$data = $obj->MySQLSelect($sql);
$currSql = "SELECT * FROM currency WHERE eDefault = 'Yes'";
$currencyData = $obj->MySQLSelect($currSql);
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?=$SITE_NAME?> | Referral Settings</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- Main LOading -->
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Referral Settings</h2>
                        <?php if ($userObj->hasPermission('create-referral-settings')){ ?>
                            <a href="javascript:void(0);" id="add_referral_level" class="add-btn" data-action="Add"
                                    data-referraltitle="<?php echo($iLevel + 1) ?>">ADD REFERRAL LEVEL
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post"
                                    action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th width="35%">Referral Level Title</th>
                                        <th width="10%" style="text-align:center;">Amount
                                            (<?php echo ($REFERRAL_AMOUNT_EARN_STRATEGY == "Fixed")?$currencyData[0]['vName']:$REFERRAL_AMOUNT_EARN_STRATEGY; ?>)
                                        </th>
                                        <?php if ($userObj->hasPermission('create-referral-settings')){ ?>
                                            <th width="10%" style="text-align:center;">Action</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (scount($data) > 0){ ?>
                                        <?php foreach ($data as $value){ ?>
                                            <tr>
                                                <td><?php echo $value['vTitle']; ?></td>
                                                <td align="center"><?php echo round($value['iAmount'],10); ?>%</td>
                                                <?php if ($userObj->hasPermission('create-referral-settings')){ ?>
                                                    <td align="center">
                                                        <a href="javascript:void(0);" data-toggle="tooltip" title="Edit"
                                                                data-id="<?php echo $value['iReferralId']; ?>"
                                                                data-action="Edit"
                                                                data-referraltitle="<?php echo $value['iLevel']; ?>"
                                                                data-amount="<?php echo round($value['iAmount'],10); ?>"
                                                                class="edit-referral-level">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                        <?php } ?>
                                    <?php }else{ ?>
                                        <tr>
                                            <td colspan="3"><?php echo $langage_lbl['LBL_NO_DATA_FOUND']; ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                    <!--TABLE-END-->
                </div>
            </div>
            <div class="admin-notes">
                <h4>Notes:</h4>
                <ul>
                    <li>You can add maximum upto <?=$REFERRAL_LEVEL?> referral levels.</li>
                    <li>Once added, referral level cannot be deleted.</li>
                    <?php include_once('referral_setting_note.php'); ?>
                </ul>
            </div>
            <div class="modal fade" id="add_edit_referral_modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content nimot-class">
                        <div class="modal-header">
                            <h4>
                                <span id="action"></span>
                                Referral Level -
                                <span id="referral_title"></span>
                                <button type="button" class="close" data-dismiss="modal">x</button>
                            </h4>
                        </div>
                        <form class="form-horizontal" id="add_edit_referral_setting_form" method="POST" action="">
                            <input type="hidden" id="iReferralId" name="iReferralId" value="">
                            <div class="col-lg-12">
                                <div class="input-group input-append">
                                    <div class="ddtt" style="margin-top: 10px">
                                        <h4><?=$langage_lbl['LBL_ENTER_AMOUNT'];?></h4>
                                        <div class="input-group input-append">
                                        <input type="Number" name="iAmount" id="iAmount" class="form-control iAmount"
                                                onKeyup="checkzero(this.value);"  min="0">
                                        <div class="input-group-addon">
                                            <span class="input-group-text" id="basic-addon1">%</span>
                                        </div>
                                        </div>
                                        <?php if ($REFERRAL_AMOUNT_EARN_STRATEGY == "Fixed"){ ?>
                                            <div>The amount entered will be added
                                                in <?php echo $currencyData[0]['vName']; ?></div>
                                        <?php }else{ ?>
                                            <div>The amount entered will be considered
                                                in <?=strtolower($REFERRAL_AMOUNT_EARN_STRATEGY);?></div>
                                        <?php } ?>
                                    </div>
                                    <div id="iLimitmsg" style="margin-bottom: 10px"></div>
                                </div>
                            </div>
                            <div class="nimot-class-but" style="margin-bottom: 20px">
                                <button type="button" onClick="check_add_money();" class="save"
                                        id="referral_setting_btn"
                                        style="margin-left: 15px !important"><?=$langage_lbl['LBL_Save'];?></button>
                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                        <div style="clear:both;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php'); ?>
<script type="text/javascript">
    function checkzero(userlimit) {
        if (userlimit != "") {
            if (userlimit == 0) {
                $('#iLimitmsg').html('<span class="red">You Can Not Enter Zero Number</span>');
            } else if (userlimit <= 0) {
                $('#iLimitmsg').html('<span class="red">You Can Not Enter Negative Number</span>');
            } else {
                $('#iLimitmsg').html('');
            }
        } else {
            $('#iLimitmsg').html('');
        }
    }

    function check_add_money() {
        var iAmount = $(".iAmount").val();
        if (iAmount == '') {
            alert("Please Enter Amount");
            return false;
        } else if (iAmount == 0) {
            alert("You Can Not Enter Zero Number");
            return false;
        } else {
            $("#referral_setting_btn").val('Please wait ...').attr('disabled', 'disabled');
            $('#add_edit_referral_setting_form').submit();
        }
    }

    $(".iAmount").keydown(function (e) {
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            (e.keyCode == 65 && e.ctrlKey === true) ||
            (e.keyCode == 67 && e.ctrlKey === true) ||
            (e.keyCode == 88 && e.ctrlKey === true) ||
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            return;
        }
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $('#add_referral_level, .edit-referral-level').click(function () {
        var action = $(this).data('action');
        var iReferralId = $(this).data('id');
        var vTitle = $(this).data('referraltitle');
        var iAmount = $(this).data('amount');
        $('#action').text(action);
        $('#referral_title').text(vTitle);
        $('#iAmount').val(iAmount);
        $('#iReferralId').val(iReferralId);
        $('#add_edit_referral_modal').modal('show');
    });
    $('.entypo-export').click(function (e) {
        e.stopPropagation();
        var $this = $(this).parent().find('div');
        $(".openHoverAction-class div").not($this).removeClass('active');
        $this.toggleClass('active');
    });
    $(document).on("click", function (e) {
        if ($(e.target).is(".openHoverAction-class,.show-moreOptions,.entypo-export") === false) {
            $(".show-moreOptions").removeClass("active");
        }
    });
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
</body>
<!-- END BODY-->
</html>