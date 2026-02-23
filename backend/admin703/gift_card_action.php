<?php
include_once('../common.php');
$script = "GiftCard";

$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);
$db_currency = $obj->MySQLSelect("SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'");
if (scount($db_currency) > 0) {
    $defaultCurrency = $db_currency[0]['vName'];
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$error = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';

$tbl_name = 'gift_cards';
// set all variables with either post (when submit) either blank (when insert)
$iGiftCardId = isset($_REQUEST['iGiftCardId']) ? $_REQUEST['iGiftCardId'] : '';
$vGiftCardCode = isset($_REQUEST['vGiftCardCode']) ? $_REQUEST['vGiftCardCode'] : '';
$tDescription = isset($_REQUEST['tDescription']) ? $_REQUEST['tDescription'] : '';
$fAmount = isset($_REQUEST['fAmount']) ? $_REQUEST['fAmount'] : '';
$eUserType = isset($_REQUEST['eUserType']) ? $_REQUEST['eUserType'] : '';
$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : 'Active';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$action = ($iGiftCardId != '') ? 'Edit' : 'Add';
//Added BY HJ On 09-01-2020 For Set Option Name As Per Service End
if (isset($_POST['submitform'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-giftcard')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create Gift Card.';
        header("Location:gift_card.php");
        exit;
    }

    if ($action == "Edit" && !$userObj->hasPermission('edit-giftcard')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update Gift Card.';
        header("Location:gift_card.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        header("Location:gift_card_action.php?iGiftCardId=" . $iGiftCardId . '&success=2');
        exit;
    }

    $iMemberId = 0;
    if ($eUserType == "UserSpecific") {
        $iMemberId = $iUserId;
    } elseif ($eUserType == "DriverSpecific") {
        $iMemberId = $iDriverId;
    }

    $Data_GiftCard = array();
    $Data_GiftCard['vGiftCardCode'] = $vGiftCardCode;
    $Data_GiftCard['tDescription'] = $tDescription;
    $Data_GiftCard['fAmount'] = $fAmount;
    $Data_GiftCard['eUserType'] = $eUserType;
    $Data_GiftCard['iMemberId'] = $iMemberId;
    $Data_GiftCard['eStatus'] = $eStatus;
    $Data_GiftCard['dAddedDate'] = date('Y-m-d H:i:s');
    $Data_GiftCard['eCreatedBy'] = "Admin";
    $Data_GiftCard['iCreatedById'] = $_SESSION['sess_iAdminUserId'];

    if ($iGiftCardId != '') {
        $where = " iGiftCardId = '" . $iGiftCardId . "'";
        $obj->MySQLQueryPerform($tbl_name, $Data_GiftCard, "update", $where);
    } else {
        $obj->MySQLQueryPerform($tbl_name, $Data_GiftCard, "insert");

        /*--------------------- send mail to RECEIVER  ------------------*/
        /*if (!empty($tReceiverEmail)) {
            $_REQUEST['isAdmin'] = 1;
            $_REQUEST['tReceiverEmail'] = "heni.patel@v3cube.in";
            $data = $COMM_MEDIA_OBJ->giftcardemaildataRecipt($_REQUEST, $vGiftCardCode);
        }*/



        /*if ($eUserType == 'DriverSpecific') {
            $driverId = $iMemberId;
            $sql = "SELECT vCurrencyDriver as vCurrency,iDriverId, concat(vName,' ',vLastName) as tReceiverName,vEmail AS tReceiverEmail,vCode AS vReceiverPhoneCode ,vPhone AS vReceiverPhone from  register_driver WHERE iDriverId IN ($driverId)";
            $registerData = $obj->MySQLSelect($sql);
        }
        if ($eUserType == 'UserSpecific') {
            $userId = $iMemberId;
            $sql = "SELECT vCurrencyPassenger as vCurrency, iUserId,  concat(vName,' ',vLastName) as tReceiverName,vEmail AS tReceiverEmail,vPhoneCode AS vReceiverPhoneCode ,vPhone AS vReceiverPhone from  register_user WHERE iUserId IN ($userId)";
            $registerData = $obj->MySQLSelect($sql);
        }
        if(in_array($eUserType , ['DriverSpecific', 'UserSpecific'])) {
            $vLangCode = $LANG_OBJ->FetchDefaultLangData("vCode");
            $dataArraySMSNew['RECEIVER_NAME'] = $registerData[0]['tReceiverName'];
            $dataArraySMSNew['GIFT_CARD_CODE'] = $vGiftCardCode;
            $dataArraySMSNew['SENDER_NAME'] = "Admin";
            $dataArraySMSNew['AMOUNT'] = formateNumAsPerCurrency($fAmount, $registerData[0]['vCurrency']);;
            $message = $COMM_MEDIA_OBJ->GetSMSTemplate('GIFT_CARD_RECEIVED', $dataArraySMSNew, "", $vLangCode);
            $result = $COMM_MEDIA_OBJ->SendSystemSMS($registerData[0]['vReceiverPhone'], $registerData[0]['vReceiverPhoneCode'], $message);
        }*/
        /*--------------------- send mail to RECEIVER  ------------------*/
    }

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    header("Location:" . $backlink);
    exit;
}

if (isset($_POST['method']) && $_POST['method'] == "checkDuplicateCode") {
    $vGiftCardCode = $_POST['vGiftCardCode'] ?? '';
    echo $GIFT_CARD_OBJ->duplicateCode($vGiftCardCode,$iGiftCardId);
    exit;
}

if (isset($_POST['method']) && $_POST['method'] == "GenerateGiftCardCode") {
    echo $GIFT_CARD_OBJ->GenerateGiftCardCode();
    exit;
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iGiftCardId = '" . $iGiftCardId . "'";
    $db_data = $obj->MySQLSelect($sql);

    $vGiftCardCode = $db_data[0]['vGiftCardCode'];
    $fAmount = setTwoDecimalPoint($db_data[0]['fAmount']);
    $eUserType = $db_data[0]['eUserType'];
    $iMemberId = $db_data[0]['iMemberId'];
    $eStatus = $db_data[0]['eStatus'];
    $tDescription = $db_data[0]['tDescription'];
}

$all_users = $obj->MySQLSelect("SELECT iUserId, CONCAT(vName, ' ', vLastName, ' (+', vPhoneCode, vPhone, ')') as userDetail FROM register_user WHERE eStatus = 'Active' ");
$all_drivers = $obj->MySQLSelect("SELECT iDriverId, CONCAT(vName, ' ', vLastName, ' (+', vCode, vPhone, ')') as driverDetail FROM register_driver WHERE eStatus = 'Active' ");

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
    <title>Admin | Gift Card <?= $action; ?> </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <link rel="stylesheet" href="css/select2/select2.min.css" type="text/css">
    <style type="text/css">
        .member-note {
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
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
                    <h2><?= $action; ?> Gift Card</h2>
                    <a href="gift_card.php">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <?php if ($success == 3) { ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                    <?php print_r($error); ?>
                </div>
                <br/>
            <?php } ?>
            <?php if ($success == 2) { ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">�</button>
                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                </div><br/>
            <?php } ?>
            <div class="body-div coupon-action-part">
                <div class="form-group">
                    <form name="_gift_card_form" id="_gift_card_form" method="post" action=""
                          enctype="multipart/form-data" class="">
                        <input type="hidden" name="iGiftCardId" id="iGiftCardId" value="<?php
                        if (isset($db_data[0]['iGiftCardId'])) {
                            echo $db_data[0]['iGiftCardId'];
                        }
                        ?>">
                        <input type="hidden" name="previousLink" id="previousLink"
                               value="<?php echo $previousLink; ?>"/>
                        <input type="hidden" name="backlink" id="backlink" value="gift_card.php"/>
                        <input type="hidden" id="action" value="<?= $action ?>"/>

                        <div class="row coupon-action-n1">
                            <div class="col-lg-12">
                                <label>Gift Card Code :<span class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input style="text-transform: uppercase;" type="text" class="form-control" name="vGiftCardCode" <?php
                                if ($action == 'Edit') {
                                    echo "readonly";
                                } else {
                                    ?><?php } ?> id="vGiftCardCode" value="<?= $vGiftCardCode; ?>"
                                       placeholder="Gift Card Code">
                                <?php
                                if ($action == 'Edit') {

                                } else {
                                    ?>
                                    <a style="margin: 0 !important;" class="btn btn-sm btn-info"
                                       onClick="generateGiftCardCode()">Generate Gift Card Code</a>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Gift Card Name (For Admin Purpose)</label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="tDescription" id="tDescription"
                                       value="<?= $tDescription ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <label>Amount (In <?= $defaultCurrency ?>)<span class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <input type="text" class="form-control" name="fAmount" id="fAmount"
                                       value="<?= $fAmount ?>">
                            </div>
                        </div>

                        <div style = "display: none" class="row">
                            <div class="col-lg-12">
                                <label>Applicable For <span class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select class="form-control" name="eUserType" id="eUserType">
                                    <option value="Anyone" <?= $eUserType == "Anyone" ? "selected" : "" ?>>Anyone
                                    </option>
                                    <option value="UserSpecific" <?= $eUserType == "UserSpecific" ? "selected" : "" ?>>
                                        Specific <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?></option>
                                    <option value="DriverSpecific" <?= $eUserType == "DriverSpecific" ? "selected" : "" ?>>
                                        Specific <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                </select>
                                <div id="AnyoneNote" class="member-note">
                                    <strong>Note: </strong> This type of code can be redeemed by anyone. You can share
                                    it privately to anyone through any communication channel.
                                </div>
                                <div id="UserSpecificNote" class="member-note">
                                    <strong>Note: </strong> This type of code can be redeemed by
                                    registered <?= strtolower($langage_lbl_admin['LBL_RIDERS_ADMIN']) ?> only. They will
                                    receive the code on their registered Phone number or Email.
                                </div>
                                <div id="DriverSpecificNote" class="member-note">
                                    <strong>Note: </strong> This type of code can be redeemed by
                                    registered <?= strtolower($langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']) ?> only.
                                    They will receive the code on their registered Phone number or Email.
                                </div>
                            </div>
                        </div>

                        <div class="row"
                             id="UserSpecific" <?= $eUserType == "UserSpecific" ? '' : 'style="display: none;"' ?>>
                            <div class="col-lg-12">
                                <label>Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?> <span
                                            class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select class="form-control" name="iUserId" id="iUserId">
                                    <option value="">
                                        Select <?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] ?></option>
                                    <?php foreach ($all_users as $user) { ?>
                                        <option value="<?= $user['iUserId'] ?>" <?= $user['iUserId'] == $iMemberId ? "selected" : "" ?>><?= $user['userDetail'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="row"
                             id="DriverSpecific" <?= $eUserType == "DriverSpecific" ? '' : 'style="display: none;"' ?>>
                            <div class="col-lg-12">
                                <label>Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> <span
                                            class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select class="form-control" name="iDriverId" id="iDriverId">
                                    <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?></option>
                                    <?php foreach ($all_drivers as $driver) { ?>
                                        <option value="<?= $driver['iDriverId'] ?>" <?= (isset($iMemberId) && $driver['iDriverId'] == $iMemberId) ? "selected" : "" ?>><?= $driver['driverDetail'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="row coupon-action-n3">
                            <div class="col-lg-12">
                                <label>Status<span class="red"> *</span></label>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <select id="eStatus" name="eStatus" class="form-control ">
                                    <option value="Active"
                                            <?php if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Active") { ?>selected <?php } ?> >
                                        Active
                                    </option>
                                    <option value="Inactive"
                                            <?php if (isset($db_data[0]['eStatus']) && $db_data[0]['eStatus'] == "Inactive") { ?>selected <?php } ?> >
                                        Inactive
                                    </option>
                                </select>
                            </div>
                        </div>


                        <div class="row coupon-action-n4">
                            <div class="col-lg-12">
                                <?php if (($action == 'Edit' && $userObj->hasPermission('edit-giftcard')) || ($action == 'Add' && $userObj->hasPermission('create-giftcard'))) { ?>
                                    <input type="submit" class="btn btn-default" name="submitform" id="submitform"
                                           value="<?php if ($action == 'Add') { ?><?= $action; ?> Gift Card<?php } else { ?>Update<?php } ?>">
                                <?php } ?>
                                <a href="gift_card.php" class="btn btn-default back_link">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
    </div>
</div>
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type='text/javascript' src='../assets/js/jquery-ui.min.js'></script>
<script type="text/javascript" src="js/plugins/select2.min.js"></script>
<script>
    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "gift_card.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);

        $('#eUserType').trigger('change');
    });

    $('#eUserType').change(function () {
        $('#UserSpecific, #DriverSpecific, .member-note').hide();
        if ($(this).val() == "UserSpecific") {
            $('#UserSpecific, #UserSpecificNote').show();
        } else if ($(this).val() == "DriverSpecific") {
            $('#DriverSpecific, #DriverSpecificNote').show();
        } else {
            $('#AnyoneNote').show();
        }
    });

    $('#iUserId, #iDriverId').select2();

    function generateGiftCardCode() {
        $('#loaderIcon').show();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>gift_card_action.php',
            'AJAX_DATA': 'method=GenerateGiftCardCode'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $('#loaderIcon').hide();
            if (response.action == "1") {
                var data = response.result;
                $('#vGiftCardCode').val(data);
                $("#_gift_card_form").validate().element("#vGiftCardCode");
                $('#vGiftCardCode').focus().blur();
            } else {
                console.log(response.result);
            }
        });
    }
</script>
</body>
</html>