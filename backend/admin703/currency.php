<?php
ob_start();
include_once('../common.php');
if (!$userObj->hasPermission('manage-currency')){
    $userObj->redirect();
}
$success = $_REQUEST['success'];
$sql = "SELECT * FROM currency  order by iDispOrder";
$db_currency = $obj->MySQLSelect($sql);
$count_all = scount($db_currency);
$tbl_name = "currency";
$iCurrencyId = $id = isset($_GET['id'])?$_GET['id']:'';
$flag = isset($_GET['flag'])?$_GET['flag']:'';
if (!empty($iCurrencyId) && $iCurrencyId != 0){
    if ($flag == 'up'){
        $sel_order = $obj->MySQLSelect("SELECT iDispOrder FROM ".$tbl_name." WHERE iCurrencyId ='".$iCurrencyId."'");
        $order_data = isset($sel_order[0]['iDispOrder'])?$sel_order[0]['iDispOrder']:0;
        $val = $order_data - 1;
        if ($val > 0){
            $obj->sql_query("UPDATE ".$tbl_name." SET iDispOrder='".$order_data."' WHERE iDispOrder='".$val."'");
            $obj->sql_query("UPDATE ".$tbl_name." SET iDispOrder='".$val."' WHERE iCurrencyId = '".$iCurrencyId."'");
        }
    }else if ($flag == 'down'){
        $sel_order = $obj->MySQLSelect("SELECT iDispOrder FROM ".$tbl_name." WHERE iCurrencyId ='".$iCurrencyId."'");
        $order_data = isset($sel_order[0]['iDispOrder'])?$sel_order[0]['iDispOrder']:0;
        $val = $order_data + 1;
        $obj->sql_query("UPDATE ".$tbl_name." SET iDispOrder='".$order_data."' WHERE iDispOrder='".$val."'");
        $obj->sql_query("UPDATE ".$tbl_name." SET iDispOrder='".$val."' WHERE iCurrencyId = '".$iCurrencyId."'");
    }
    if (!empty($OPTIMIZE_DATA_OBJ)){
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
    }
    header("Location:currency.php");
    exit;
}
$rounding_enable = $MODULES_OBJ->isEnableRoundingMethod();
$rformating_enable = $MODULES_OBJ->isEnableReverseFormatFeature();
$vName = "SELECT vName FROM currency  order by iDispOrder";
$db_vName = $obj->MySQLSelect($vName);
for ($i = 0;$i < scount($db_vName);$i++){
    $db_name[$i] = $db_vName[$i]["vName"];
}
$script = 'Currency';
if (isset($_REQUEST['reload']) && !empty($_REQUEST['reload'])){
    $siteUrl = $tconfig['tsite_url']."".SITE_ADMIN_URL."/currency.php?success=1";
    ?>
    <script>window.location.replace("<?php echo $siteUrl; ?>");</script>
<?php } ?>
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
    <title>
        <?=$SITE_NAME;?>
        | Currency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <? include_once('global_files.php'); ?>
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <? include_once('header.php'); ?>
    <? include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Currency</h2>
                    </div>
                </div>
                <hr/>
            </div>
            <div style="clear:both;"></div>
            <? if ($success == 1){ ?>
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                </div>
                <br/>
                <?
            }else if ($success == 2){
                ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                </div>
                <br/>
                <?
            }
            ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <form action="currency_action.php" method="post" id="formId">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                    <tr>
                                        <th style="text-align:center">Currency</th>
                                        <th>Ratio</th>
                                        <th>Threshold Amount
                                            <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title='<?=htmlspecialchars('Currency Wise Minimum Payment Drivers can Request from Website Driver Account to Admin.',ENT_QUOTES,'UTF-8')?>'></i>
                                        </th>
                                        <th>Symbol</th>
                                        <? if ($rounding_enable){ ?>
                                            <th style="text-align:center">Rounding Off
                                                <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title='<?=htmlspecialchars('<p class="text-left">The rounding is applied when the fare is generated in decimal value, where decimal range from 0 - 0.50 will consider as 0 and decimal range from 0.51 - 1 will consider as 1. <br><br>E.g. 1. Fare before rounding is $8.33 and Fare after rounding is $8.00  <br>E.g. 2. Fare before rounding is $8.50 and Fare after rounding is $8.00 <br>E.g. 3. Fare before rounding is $8.51 and Fare after rounding is $9.00</p>',ENT_QUOTES,'UTF-8')?>' data-html="true"></i>
                                            </th>
                                        <? } ?>
                                        <? if ($rformating_enable){ ?>
                                            <th>Currency formatting&nbsp;<i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title='<?=htmlspecialchars('<p class="text-left">Once you activate Currency Formatting for any currency then currency formatting of the currency will reverse. <br><br>E.g. When enabled dot(.) will be considered as currency separator and comma(,) would be considered as decimal separator - $ 1.086,00 <br><br>E.g. When disabled comma(,) will be considered as currency separator and dot(.) would be considered as decimal separator - $ 1,086.00</p>',ENT_QUOTES,'UTF-8')?>' data-html="true"></i>
                                            </th>
                                            <th>Reverse Symbol&nbsp;<i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title='<?=htmlspecialchars('<p class="text-left">Once you activate Reverse Symbol for any currency then respective symbol of the respective currency will reverse. <br><br>E.g. When Reverse Symbol is disabled, currency symbol ($) would be shown on left side of value - $ 505.00<br><br>E.g. When Reverse Symbol is enabled, currency symbol ($) would be shown on right side of value - 505.00 $</p>',ENT_QUOTES,'UTF-8')?>' data-html="true"></i>
                                            </th>
                                        <? } ?>
                                        <th style="text-align:center">Default</th>
                                        <th style="text-align:center">Display Order</th>
                                        <th style="text-align:center">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($db_currency as $key => $value){
                                        $eStatus = $value['eStatus'];
                                        $iDispOrder = $value['iDispOrder'];
                                        $eDefault = "";
                                        if ($value['eDefault'] == "Yes"){
                                            $eDefault = "Yes";
                                            $readonlyadd = "readonly";
                                        }else{
                                            $eDefault = "No";
                                            $readonlyadd = "";
                                        }
                                        $eRoundingOffEnable = (!empty($value['eRoundingOffEnable']) && $value['eRoundingOffEnable'] == 'Yes')?'Yes':'No';
                                        $eReverseformattingEnable = (!empty($value['eReverseformattingEnable']) && $value['eReverseformattingEnable'] == 'Yes')?'Yes':'No';
                                        $eReverseSymbolEnable = (!empty($value['eReverseSymbolEnable']) && $value['eReverseSymbolEnable'] == 'Yes')?'Yes':'No';
                                        echo '<tr>
                                        <td><input class="form-control" type="hidden" name="iCurrencyId[]" value="'.$value['iCurrencyId'].'" />'.$value["vName"].'</td>
                                        <td><input class="form-control" name="Ratio[]" id="ratio_'.$value['iCurrencyId'].'" type="text" value='.$value['Ratio'].' '.$readonlyadd.' required/></td>
                                        <td><input class="form-control" name="fThresholdAmount[]" type="text" value='.$value['fThresholdAmount'].' /></td>
                                        <td><input  class="form-control" name="vSymbol[]" type="text" value='.$value['vSymbol'].' required/></td>';
                                        ?>

                                        <!--<td style="text-align:center">
                                        </tr>-->
                                        <? if ($rounding_enable){ ?>
                                            <td style="text-align:center">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eRoundingOffEnable[<?=$value['iCurrencyId'];?>]" <?=($eRoundingOffEnable == 'Yes')?'checked':'';?>/>
                                            </td>
                                        <? } ?>
                                        <? if ($rformating_enable){ ?>
                                            <td style="text-align:center">
                                                <div class="make-switch" data-on="success" data-off="warning">
                                                    <input type="checkbox" name="eReverseformattingEnable[<?=$value['iCurrencyId'];?>]" <?=($eReverseformattingEnable == 'Yes')?'checked':'';?>/>
                                            </td>

                                            <td style="text-align:center">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" name="eReverseSymbolEnable[<?=$value['iCurrencyId'];?>]" <?=($eReverseSymbolEnable == 'Yes')?'checked':'';?>/>

                                            </div>
                                        <? } ?>
                                        </td>
                                        <?php echo '<td align="Center">'.$eDefault.'</td>'; ?>

                                        <td width="10%" align="center">
                                            <? if ($iDispOrder != 1 && $key > 0){ ?>
                                                <a href="currency.php?id=<?=$value['iCurrencyId'];?>&flag=up" class="btn btn-warning">
                                                    <i class="icon-arrow-up"></i>
                                                </a>
                                            <? }
                                            if ($iDispOrder != $count_all && $key < scount($db_currency) - 1){ ?>
                                                <a href="currency.php?id=<?=$value['iCurrencyId'];?>&flag=down" class="btn btn-warning">
                                                    <i class="icon-arrow-down"></i>
                                                </a>
                                            <? } ?>
                                        </td>

                                        <?php if ($userObj->hasPermission('update-status-manage-currency')){ ?>
                                            <td width="12%" class="estatus_options" id="eStatus_options" align="center">
                                                <?php if ($readonlyadd != ""){
                                                    ?>
                                                    <input type="hidden" name="eStatus[]" id="estatus_value" value="Active" class="form-control">
                                                    <?=$eStatus;?>
                                                <?php }else{ ?>
                                                    <select attr-selected-value="<?php echo $eStatus; ?>" name="eStatus[]" id="estatus_value" class="form-control estatus_value">
                                                        <option value='Active' <?php
                                                        if ($eStatus == 'Active'){
                                                            echo "selected";
                                                        }
                                                        ?> >Active
                                                        </option>
                                                        <option value="Inactive" <?php
                                                        if ($eStatus == 'Inactive'){
                                                            echo "selected";
                                                        }
                                                        ?> >Inactive
                                                        </option>
                                                    </select>
                                                <?php } ?>
                                            </td>
                                        <?php }else{ ?>
                                            <td width="12%" class="estatus_options" id="eStatus_options" align="center"><?=$eStatus;?></td>
                                        <?php } ?>
                                        <?php
                                        /* echo '<td><input  class="form-control" name="eDefault" id="eDefault_'.$value['iCurrencyId'].'" type="radio" value="'.$value['iCurrencyId'].'" '.$eDefault.' /></td>'; */
                                        echo '</tr>';
                                    }
                                    ?>
                                    <?php if ($userObj->hasPermission('update-status-manage-currency')){ ?>
                                        <tr>
                                            <td colspan="7" align="center">
                                                <input onclick="ProcessedToSubmit()" type="submit" name="btnSubmit" class="btn btn-default" value="Edit currency">

                                            </td>
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
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<div class="modal fade" id="myModalcurrency" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">x</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"> Edit</h4>
            </div>
            <div class="modal-body">

                <div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range 1</label>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" class="form-control" readonly name="fMiddleRangeValue" id="fMiddleRangeValue" value="0" placeholder="Middle Range Value">
                        </div>
                        <div class="range1">
                            To
                        </div>
                        <div class="col-lg-3">
                            <input type="text" class="form-control" name="iFirstRangeValue1" min="0" max="99" id="iFirstRangeValue1" value="<?=$fMiddleRangeValue;?>" placeholder="First Range Value">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range1 Value</label>
                        </div>
                        <div class="col-lg-3">
                            <?php $fMiddleRangeValue = floatval($fMiddleRangeValue); ?>
                            <select class="form-control" name='iFirstRangeValue' id="iFirstRangeValue">
                                <?php //if(is_float($fMiddleRangeValue) == true){
                                if ($fMiddleRangeValue === floatval($fMiddleRangeValue)){
                                    ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iFirstRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value="0.50" <? if ($iFirstRangeValue == "0.50") { ?>selected<?php } ?>>0.5</option>
                                    <option value="1" <? if ($iFirstRangeValue == "1") { ?>selected<?php } ?>>1</option>
                                <?php }else{ ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iFirstRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value="50" <? if ($iFirstRangeValue == "50") { ?>selected<?php } ?>>0.5</option>
                                    <option value="100" <? if ($iFirstRangeValue == "100") { ?>selected<?php } ?>>1</option>
                                <?php } ?>
                            </select>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range 2</label>
                        </div>
                        <div class="col-lg-3">
                            <input type="text" class="form-control" readonly name="fMiddleRangeValue2" id="fMiddleRangeValue2" value="<?=$fMiddleRangeValue;?>" placeholder="Middle Range Value2">
                        </div>
                        <div class="range2">
                            To
                        </div>
                        <div class="col-lg-3">
                            <input type="text" class="form-control" readonly name="iSecRangeValue1" id="iSecRangeValue1" value="<?=$iSecRangeValue;?>" placeholder="Second Range Value">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <label> Range 2 Value </label>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control" name='iSecRangeValue' id="iSecRangeValue">
                                <?php //if(is_float($fMiddleRangeValue) == true)  {
                                if ($fMiddleRangeValue === floatval($fMiddleRangeValue)){
                                    ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iSecRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value="0.50" <? if ($iSecRangeValue == "0.50") { ?>selected<?php } ?>>0.5</option>
                                    <option value="1" <? if ($iSecRangeValue == "1") { ?>selected<?php } ?>>1</option>
                                <?php }else{ ?>
                                    <option value=''>-- Select Value --</option>
                                    <option value='0' <? if ($iSecRangeValue == "0") { ?>selected<?php } ?>>0</option>
                                    <option value="50" <? if ($iSecRangeValue == "1") { ?>selected<?php } ?>>50</option>
                                    <option value="100" <? if ($iSecRangeValue == "100") { ?>selected<?php } ?>>100</option>
                                <?php } ?>

                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="save_currency_rounding_data(this)">Edit</button>

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<? include_once('footer.php'); ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<link rel="stylesheet" href="../assets/css/modal_alert.css"/>
<script src="../assets/js/modal_alert.js"></script>
<script type="text/javascript">

    $("form").submit(function (event) {
        event.preventDefault();
        ShpSq6fAm7($('#formId'));
        $('#formId').get(0).submit();
    });
    $("#iFirstRangeValue1").on('keydown', function (e) {
        if ($("#iFirstRangeValue1").val() != 0) {
            if (e.keyCode === 190 || e.keyCode === 110) {
                return false;
            }
        }
    });
    $("#iFirstRangeValue1").on('keyup', function (e) {
        var iFirstRangeValue = $("#iFirstRangeValue1").val();
        if (iFirstRangeValue != '') {
            $("#fMiddleRangeValue2").val(iFirstRangeValue);
        }
        var inputtxt = $("#fMiddleRangeValue2");
        var decimal = /^[-+]?[0-9]+\.[0-9]+$/;
        if (inputtxt.val().match(decimal)) {
            $("#iSecRangeValue1").val("1");
        } else {
            if (inputtxt.val() != '0') {
                $("#iSecRangeValue1").val("100");
            }
        }
        if ($("#iFirstRangeValue1").val().match(decimal)) {
            var options1 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>";
            $("#iFirstRangeValue").html(options1);
            var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='0.5'>0.5</option><option value='1'>1</option>";
            $("#iSecRangeValue").html(options2);
        } else {
            var options1 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='50'>50</option><option value='100'>100</option>";
            $("#iFirstRangeValue").html(options1);
            var options2 = "<option value=''>-- Select Value --</option><option value='0'>0</option><option value='50'>50</option><option value='100'>100</option>";
            $("#iSecRangeValue").html(options2);
        }
    });

    function showhideroundingoffRange() {
        if ($('input[name=eRoundingOffEnable]').is(':checked')) {
            $("#showroundingoffRange").show();
        } else {
            $("#showroundingoffRange").hide();
        }
    }

    showhideroundingoffRange();

    function showhiderReverseformatting() {
        if ($('input[name=eReverseformattingEnable]').is(':checked')) {
            $("#showroundingoffRange").show();
        } else {
            $("#showroundingoffRange").hide();
        }
    }

    showhiderReverseformatting();

    function save_currency_rounding_data(e) {
        var fMiddleRangeValue = $("#fMiddleRangeValue").val();
        var fMiddleRangeValue = $("#iFirstRangeValue1").val();
        var iFirstRangeValue = $("#iFirstRangeValue").val();
        var iSecRangeValue = $("#iSecRangeValue").val();
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_edit_rounding_off.php',
            'AJAX_DATA': {
                'iCurrencyId': iCurrencyId,
                'fMiddleRangeValue': fMiddleRangeValue,
                'iFirstRangeValue': iFirstRangeValue,
                'iSecRangeValue': iSecRangeValue
            },
            'REQUEST_DATA_TYPE': 'html'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml2 = response.result;
            }
        });
    }

    function checkStatusChanged() {
        var estatus_value = $(".estatus_value");
        var returnValue = false;
        for (var i = 0; i < estatus_value.length; i++) {
            var this_input = $(estatus_value[i]);
            var selected_value = this_input.val();
            var prev_value = this_input.attr('attr-selected-value');
            if (selected_value != prev_value && selected_value == 'Inactive') {
                returnValue = true;
            }
        }
        return returnValue;
    }

    function ProcessedToSubmit() {
        event.preventDefault();
        if (checkStatusChanged()) {
            show_alert("", "Some currencies have been marked as inactive. As a result, if any user or driver had set one of these currencies as their default, it will be switched to the system's default currency. Users will be automatically logged out and will need to log in again <br><br> To proceed, please click 'OK'. To cancel, click 'Cancel'.",  "Ok", "Cancel","", "SubmitForm");
        }else{
            SubmitForm(0);
        }
    }

    function SubmitForm(btnID) {
        if(btnID == 0) {
            ShpSq6fAm7($('#formId').get(0));
            $('#formId').get(0).submit();
        }
    }
</script>
</body>
<!-- END BODY-->
</html>