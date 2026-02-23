<?php
include_once('../common.php');

if ($REFERRAL_SCHEME_ENABLE == "No") {
    header('Location: dashboard.php');
    exit;
}

if (!$userObj->hasPermission('manage-referral-report')) {
    $userObj->redirect();
}
$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
$script = 'referrer';
$type = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Driver';

if($onlyRideShareEnable == 'Yes' || $onlyBSREnable == 'Yes'){
    $type = 'Rider';
}

$tableName = "register_user";
$refTableName = "register_driver";
$primaryId = "iUserId";
$userType = "Rider";
$refId = "ru.iDriverId";

if ($type == 'Driver') {
    $tableName = "register_driver";
    $refTableName = "register_user";
    $primaryId = "iDriverId";
    $userType = "Driver";
    $refId = "ru.iUserId";
}

$getMemberData = $obj->MySQLSelect("SELECT CONCAT(vName,' ',vLastName) AS memberName,eStatus,$primaryId FROM " . $tableName);
if($type == 'Driver')
{
    $getDriverRefData = $obj->MySQLSelect("SELECT CONCAT(rd.vName,' ',rd.vLastName) AS OrgdriverName,rd.eRefType as eRefType,rd1.$primaryId,rd1.iRefUserId FROM " . $tableName . " as rd LEFT JOIN " . $tableName . " as rd1 on rd1.iRefUserId=rd.$primaryId WHERE rd1.eRefType = '" . $userType . "'");
}
else{
    $getDriverRefData = $obj->MySQLSelect("SELECT CONCAT(rd.vName,' ',rd.vLastName) AS OrgdriverName,rd.eRefType as eRefType,rd1.iDriverId,rd1.iRefUserId FROM " . $tableName . " as rd LEFT JOIN " . $refTableName . " as rd1 on rd1.iRefUserId=rd.$primaryId WHERE rd1.eRefType = '" . $userType . "'");
}
$refDataArr = array();
for ($r = 0; $r < scount($getDriverRefData); $r++) {
    $refDataArr[$getDriverRefData[$r]['iRefUserId']][] = $getDriverRefData[$r];
}
if($type == 'Driver')
{
    $getUserRefData = $obj->MySQLSelect("SELECT CONCAT(ru.vName,' ',ru.vLastName) AS passangerName,CONCAT(rd1.vName,' ',rd1.vLastName) AS OrgdriverName,ru.eRefType as eRefType , ru.iUserId,ru.iRefUserId,ru.eRefType FROM " . $refTableName . " as ru LEFT JOIN " . $tableName . " as rd1 on rd1.$primaryId=ru.iRefUserId WHERE ru.eRefType = '" . $userType . "' AND rd1.$primaryId > 0");
}
else {
    $getUserRefData = $obj->MySQLSelect("SELECT CONCAT(ru.vName,' ',ru.vLastName) AS passangerName,CONCAT(rd1.vName,' ',rd1.vLastName) AS OrgdriverName,ru.eRefType as eRefType , ru.iUserId,ru.iRefUserId,ru.eRefType FROM " . $tableName . " as ru LEFT JOIN " . $tableName . " as rd1 on rd1.$primaryId=ru.iRefUserId WHERE ru.eRefType = '" . $userType . "' AND rd1.$primaryId > 0");
}
for ($ru = 0; $ru < scount($getUserRefData); $ru++) {
    $refDataArr[$getUserRefData[$ru]['iRefUserId']][] = $getUserRefData[$ru];
}
$totalRecord = $result = $refUserDataArr = array();

foreach ($getMemberData as $key => $value) {
    if (isset($refDataArr[$value[$primaryId]])) {
        $result[$value[$primaryId]][] = $refDataArr[$value[$primaryId]];
    }
}

foreach ($result as $key => $value) {
    foreach ($value as $ky => $ve) {
        foreach ($ve as $k => $v) {
            $totalRecord[$key][] = $v;
        }
    }
}

$ssql = " AND fromUserId = 0";
if($MODULES_OBJ->isEnableMultiLevelReferralSystem())
{
    $ssql = " AND fromUserId > 0 ";
}
$refWalletData = $obj->MySQLSelect("SELECT sum(iBalance) as totalbalance,iUserId,eUserType,eFor from `user_wallet` WHERE  eFor = 'Referrer' $ssql GROUP BY iUserId,eUserType");

for ($r = 0; $r < scount($refWalletData); $r++) {
    $refUserDataArr[$refWalletData[$r]['iUserId']][$refWalletData[$r]['eUserType']] = $refWalletData[$r]['totalbalance'];
}

//$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';

?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <title><?= $SITE_NAME ?> | MLM Referral Report</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
    <? include_once('global_files.php'); ?>
</head>
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
                            <h2>MLM Referral Report</h2>
                        </div>
                    </div>
                    <hr />
                </div>
                <?php include('valid_msg.php'); ?>
                <div class="table-list">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel panel-default">
                                <?php if($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes'){ ?>
                                    <div class="panel-heading referrer-page-tab">
                                        <ul class="nav nav-tabs">
                                            <li <?php if ($type == 'Driver') { ?> class="active" <?php } ?>>
                                                <a data-toggle="tab"  onclick="getReview('Driver')"  href="#home" ><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></a></li>
                                            <li <?php if ($type == 'Rider') { ?> class="active" <?php } ?>>
                                                <a data-toggle="tab" onClick="getReview('Rider')"  href="#menu1"><?= $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></a></li>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <div class="panel-body">
                                    <div>
                                        <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                            <table class="table responsive  table-striped table-bordered table-hover nowrap" id="dataTables-example" cellspacing="0" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th  width="10%">Member Name</th>
                                                        <th width="10%" style="text-align:center;">Total Members Referred</th>
                                                        <?php 
                                                            if(ONLYDELIVERALL == "Yes") { 
                                                                $ref_text = "order";
                                                            } else if (DELIVERALL == "Yes") {
                                                                $ref_text = "trip/order";
                                                            } else if ($APP_TYPE == "Delivery") {
                                                                $ref_text = "Delivery";
                                                            } else if ($APP_TYPE == "UberX") {
                                                                $ref_text = "job";
                                                            } else {
                                                                $ref_text = "trip";
                                                            } 
                                                        ?>
                                                        <th  width="10%" style="text-align:center;">Total Amount Earned <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title='Amount earned in wallet once refferal do a successful first <?= $ref_text ?>.'></i></th>                                                             
                                                        <th width="8%" style="text-align:center;">View Referral Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                                            
                                                    <?php
                                                    if (scount($totalRecord) > 0) {
                                                        $i = 0;
                                                        foreach ($totalRecord as $k => $val) {
                                                            //$totalbalance = getTotalbalance($k, $userType);
                                                            $totalbalance = setTwoDecimalPoint(0);
                                                            if (isset($refUserDataArr[$k][$userType])) {
                                                                $totalbalance = setTwoDecimalPoint($refUserDataArr[$k][$userType]);
                                                            }
                                                            ?>
                                                            <tr>
                                                                <td> <?php if ($type=='Driver') { //if ($val[0]['eRefType']=='Driver') { ?>
                                                               <?php if ($userObj->hasPermission('view-providers')) { ?><a href="javascript:void(0);" onClick="show_driver_details('<?= $val[0]['iRefUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($val[0]['OrgdriverName']); ?><?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?>
 															    <?php } else { ?>
																  <?php if ($userObj->hasPermission('view-users')) { ?><a href="javascript:void(0);" onClick="show_rider_details('<?= $val[0]['iRefUserId']; ?>')" style="text-decoration: underline;"><?php } ?><?= clearName($val[0]['OrgdriverName']); ?><?php if ($userObj->hasPermission('view-users')) { ?></a><?php } ?>
																	<?php  } ?></td>
                                                                <td align="center"><?= scount($val); ?></td>
                                                                <td align="center"><?= ($totalbalance > 0) ? formateNumAsPerCurrency($totalbalance,'') : '--'; ?></td>
                                                                <td align="center"> <a href="referrer_action.php?id=<?php echo $k; ?>&eUserType=<?= $userType; ?>" data-toggle="tooltip" target="_blank">
                                                                        <img src="img/view-details.png">
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <?
                                                            $i++;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </form>
                                        <form name="frmreview" id="frmreview" method="post" action="">
                                            <input type="hidden" name="reviewtype" value="" id="reviewtype">
                                            <input type="hidden" name="action" value="" id="action">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div> <!--TABLE-END-->
                    </div>
                </div>
            </div>
        </div>
        <!--END PAGE CONTENT -->
    </div>
    <!--END MAIN WRAPPER -->
    <? include_once('footer.php'); ?>
	
<div class="modal fade " id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i><?= $langage_lbl_admin['LBL_RIDER']; ?> Details<button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail"></div>
            </div>
        </div>
    </div>
</div>

<div  class="modal fade" id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog" >
		<div class="modal-content">
			<div class="modal-header">
				<h4>
					<i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i><?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details<button type="button" class="close" data-dismiss="modal">x</button>
				</h4>
			</div>
			<div class="modal-body" style="max-height: 450px;overflow: auto;">
				<div id="imageIcons1" style="display:none">
					<div align="center">                                                                       
						<img src="default.gif"><br/>                                                            
						<span>Retrieving details,please Wait...</span>                       
					</div>    
				</div>
				<div id="driver_detail"></div>
			</div>
		</div>
	</div>
</div>
    <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function () {
            $('#dataTables-example').dataTable({
                "order": [[1, "desc"]],
                "iDisplayLength": 25,

            });
        });
    </script>
    <script>
        $("#setAllCheck").on('click', function () {
            if ($(this).prop("checked")) {
                jQuery("#_list_form input[type=checkbox]").each(function () {
                    if ($(this).attr('disabled') != 'disabled') {
                        this.checked = 'true';
                    }
                });
            } else {
                jQuery("#_list_form input[type=checkbox]").each(function () {
                    this.checked = '';
                });
            }
        });
        $("#Search").on('click', function () {
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action + "?" + formValus;
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
        function getReview(type)
        {
            $('#reviewtype').val(type);
            ShpSq6fAm7(frmreview);
            document.frmreview.submit();
        }
    </script>
</body>
<!-- END BODY-->
</html>

