<?php
ob_start();
include_once('../common.php');

if (!$userObj->hasPermission('manage-language')) {
    $userObj->redirect();
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';

$sql = "SELECT * FROM language_master  order by iDispOrder";
$db_languages = $obj->MySQLSelect($sql);
$count_all = scount($db_languages);

$tbl_name = "language_master";
$iLanguageMasId = $id = isset($_GET['id']) ? $_GET['id'] : '';
$flag = isset($_GET['flag']) ? $_GET['flag'] : '';

if (!empty($iLanguageMasId) && $iLanguageMasId != 0) {
    if ($flag == 'up') {
        $sel_order = $obj->MySQLSelect("SELECT iDispOrder FROM " . $tbl_name . " WHERE iLanguageMasId ='" . $iLanguageMasId . "'");
        $order_data = isset($sel_order[0]['iDispOrder']) ? $sel_order[0]['iDispOrder'] : 0;
        $val = $order_data - 1;
        if ($val > 0) {
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDispOrder='" . $order_data . "' WHERE iDispOrder='" . $val . "'");
            $obj->sql_query("UPDATE " . $tbl_name . " SET iDispOrder='" . $val . "' WHERE iLanguageMasId = '" . $iLanguageMasId . "'");
        }
    } else if ($flag == 'down') {
        $sel_order = $obj->MySQLSelect("SELECT iDispOrder FROM " . $tbl_name . " WHERE iLanguageMasId ='" . $iLanguageMasId . "'");

        $order_data = isset($sel_order[0]['iDispOrder']) ? $sel_order[0]['iDispOrder'] : 0;

        $val = $order_data + 1;
        $obj->sql_query("UPDATE " . $tbl_name . " SET iDispOrder='" . $order_data . "' WHERE iDispOrder='" . $val . "'");
        $obj->sql_query("UPDATE " . $tbl_name . " SET iDispOrder='" . $val . "' WHERE iLanguageMasId = '" . $iLanguageMasId . "'");
    }
    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
    }
    updateSystemData();
    header("Location:language.php");
    exit;
}

$vTitle = "SELECT vTitle FROM language_master  order by iDispOrder";
$db_vName = $obj->MySQLSelect($vTitle);
for ($i = 0; $i < scount($db_vName); $i++) {
    $db_name[$i] = $db_vName[$i]["vTitle"];
}
$script = 'Language';
if (isset($_REQUEST['reload'])) {
    $siteUrl = $tconfig['tsite_url'] . "".SITE_ADMIN_URL."/language.php?success=1";
    ?>
    <script>window.location.replace("<?php echo $siteUrl; ?>");</script>
<?php } ?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>
            <?= $SITE_NAME; ?>
            | Language</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
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
                                <h2>Language</h2>
                                <!-- <input type="button" id="show-add-form" value="ADD A DRIVER" class="add-btn"> <input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn"> -->
                            </div>
                        </div>
                        <hr />
                    </div>
                    <div style="clear:both;"></div>
                    <? if ($success == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                        </div>
                        <br/>
                        <?
                    } else if ($success == 2) {
                        ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                        <br/>
                        <?php  }   ?>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                        <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                            <thead>
                                                <tr>
                                                    <th width="40%">Language</th>
                                                    <th width="10%" style="text-align:center">LangCode</th>
                                                    <th width="10%" style="text-align:center">Default</th>
                                                    <th width="10%" style="text-align:center">Display Order</th>
                                                    <th width="10%" style="text-align:center">Status</th>
                                                    <th width="10%" style="text-align:center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($db_languages as $key => $value) {
                                                $eStatus = $value['eStatus'] ;
                                                $iDispOrder = $value['iDispOrder'] ;
                                                $eDefault = "";
                                                if ($value['eDefault'] == "Yes") {
                                                    $eDefault = "Yes";
                                                    $readonlyadd = "readonly";
                                                } else {
                                                    $eDefault = "No";
                                                    $readonlyadd = "";
                                                } ?>

                                    <tr>
                                        <td><?php echo $value["vTitle"];?></td>
                                        <td align="center"><?php echo $value['vCode'];?></td>
                                        <td align="center"><?php echo  $eDefault;?></td>
                                    <td width="10%" align="center">
                                        <? if ($iDispOrder != 1 && $key > 0) { ?>
                                            <a href="language.php?id=<?= $value['iLanguageMasId']; ?>&flag=up" class="btn btn-warning">
                                                <i class="icon-arrow-up"></i>
                                            </a>
                                        <? } if ($iDispOrder != $count_all && $key < scount($db_languages) - 1) { ?>
                                            <a href="language.php?id=<?= $value['iLanguageMasId']; ?>&flag=down" class="btn btn-warning">
                                                <i class="icon-arrow-down"></i>
                                            </a>
                                        <? } ?>
                                    </td>
                                    <td width="10%" align="center">
                                        <? if ($eStatus == 'Active') {
                                            $dis_img = "img/active-icon.png";
                                        } else if ($eStatus == 'Inactive') {
                                            $dis_img = "img/inactive-icon.png";
                                        } ?>
                                        <img src="<?= $dis_img; ?>" alt="<?= $eStatus; ?>" data-toggle="tooltip" title="<?= $eStatus; ?>"></td>
                                    <?php if ($userObj->hasPermission('update-status-manage-language')) { ?>
                                    <td align="center" style="text-align:center;" class="action-btn001">
                                        <?php if ($readonlyadd != "") {
                                           echo '--';
                                        } else { ?>
                                        <div class="share-button openHoverAction-class" style="display: block;">
                                            <label class="entypo-export"><span><img  src="images/settings-icon.png" alt=""></span></label>
                                            <div class="social show-moreOptions openPops_<?= $value['iLanguageMasId']; ?>">
                                                <ul>
                                                    <?php if ($userObj->hasPermission('update-status-manage-language')) { ?>
                                                        <li class="entypo-facebook"data-network="facebook">
                                                            <a href="javascript:void(0);" onclick="changeLangStatus('<?= $value['iLanguageMasId']; ?>', 'Inactive')" data-toggle="tooltip" title="Activate">
                                                                <img src="img/active-icon.png"  alt="Activate">
                                                            </a>
                                                        </li>
                                                        <li class="entypo-gplus" data-network="gplus">
                                                            <a href="javascript:void(0);" onclick="changeLangStatus('<?= $value['iLanguageMasId']; ?>', 'Active')" data-toggle="tooltip" title="Deactivate">
                                                                <img src="img/inactive-icon.png" alt="Deactivate">
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                </tr>
                                <?php  } ?>

                                </tbody>
                            </table>
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
<form name="pageForm" id="pageForm" action="language_action.php" method="post">
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iLanguageMasId" id="iMainId01" value="">
    <input type="hidden" name="status" id="status01" value="">
    <input type="hidden" name="statusVal" id="statusVal" value="">
    <input type="hidden" name="option" value="<?php echo $option; ?>">
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>">
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>">
    <input type="hidden" name="iDispOrder" id="iDispOrder" value="<?php echo $iDispOrder; ?>">
    <input type="hidden" name="method" id="method" value="">
</form>
<? include_once('footer.php'); ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript">
function changeLangStatus(iLanguageMasId, status) {
    var action = $("#pageForm").attr('action');
    if (status == 'Active') {
        status = 'Inactive';
    } else {
        status = 'Active';
    }
    $("#iMainId01").val(iLanguageMasId);
    $("#status01").val(status);
    var formValus = $("#pageForm").serialize();
    window.location.href = action + "?" + formValus;
}
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
</script>
</body>
<!-- END BODY-->
</html>