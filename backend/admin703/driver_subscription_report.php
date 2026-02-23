<?php
include_once('../common.php');

if (!$userObj->hasPermission('manage-driver-subscription-report')) {
    $userObj->redirect();
}
if ($default_lang == "") {
    $default_lang = "EN";
}
$script = 'DriverSubscriptionReport';
$tblname = 'driver_subscription_plan';
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iDriverSubscriptionPlanId DESC';
$cmp_ssql = "";
// Start Search Parameters
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDriver = isset($_REQUEST['searchDriver']) ? stripslashes($_REQUEST['searchDriver']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";

$defaultDetails = $obj->MySQLSelect("SELECT * FROM `language_master` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");
$vcode = $defaultDetails[0]['vCode'];
$currencySymbol = $defaultDetails[0]['vCurrencySymbol'];

$ssql = '';
if ($keyword != '') {
    $keyword_new = $keyword;
    $chracters = array("(", "+", ")");
    $removespacekeyword = preg_replace('/\s+/', '', $keyword);
    $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
    if (is_numeric($keyword_new)) {
        $keyword_new = $keyword_new;
    } else {
        $keyword_new = $keyword;
    }
    $keyword_new = explode('/',$keyword_new);
    $keyword_new = $keyword_new[0];
    if ($option != '') {
        $option_new = $option;
        if($option_new=='providerName') {
            $ssql .= " AND (rd.vName LIKE '%".clean($keyword_new)."%' OR rd.vLastName LIKE '%".clean($keyword_new)."%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%".clean($keyword_new)."%' )";
        } else if($option_new=='ePlanValidity') {
             $ssql .= " AND CONCAT(d.vPlanPeriod,' ',d.ePlanValidity) LIKE '%" . clean($keyword_new) . "%'";
        } else {
            $ssql .= " AND d." . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
        }
    } else { 
            $ssql .= " AND (d.vPlanName LIKE '%" . clean($keyword_new) . "%' OR CONCAT(d.vPlanPeriod,' ',d.ePlanValidity) LIKE '%" . clean($keyword_new) . "%')";
            $ssql .= " OR (rd.vName LIKE '%".clean($keyword_new)."%' OR rd.vLastName LIKE '%".clean($keyword_new)."%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%".clean($keyword_new)."%')";
            
    }
}
if($searchDriver!='') {
    $ssql .= " AND rd.iDriverId = $searchDriver";
}
// End Search Parameters
//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
$tblPlan = 'driver_subscription_plan';
$tblDetails = 'driver_subscription_details';

$getField = "d.eSubscriptionStatus, d.vPlanName, d.vPlanDescription,d.vPlanPeriod,d.ePlanValidity,CONCAT('$currencySymbol',d.fPrice) as fPlanPrice,d.fPrice as fPriceOrg,d.tSubscribeDate,d.tExpiryDate,d.tClosedDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays,d.tSubscribeDate, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name,rd.vTimeZone";
$sql = "SELECT $getField FROM $tblDetails d LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";
$totalData = $obj->MySQLSelect($sql);
$total_results = scount($totalData);
$total_pages = ceil($total_results / $per_page); //total pages we going to have
$show_page = 1;
$start = 0;
$end = $per_page;
//-------------if page is setcheck------------------//
if (isset($_GET['page'])) {
    $show_page = $_GET['page'];             //it will telles the current page
    if ($show_page > 0 && $show_page <= $total_pages) {
        $start = ($show_page - 1) * $per_page;
        $end = $start + $per_page;
    }
}
// display pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$tpages = $total_pages;
if ($page <= 0)
    $page = 1;
//Pagination End
$sql = $sql." LIMIT $start, $per_page";
$data_drv = $obj->MySQLSelect($sql);
$endRecord = scount($data_drv);
$var_filter = "";
foreach ($_REQUEST as $key => $val) {
    if ($key != "tpages" && $key != 'page')
        $var_filter .= "&$key=" . stripslashes($val);
}
$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT'] ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->

    <!-- BEGIN BODY-->
    <body class="padTop53 " >
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
                                <h2><?php echo $langage_lbl_admin['LBL_DRIVER_SUBSCRIPTION_REPORT']; ?></h2>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include('valid_msg.php'); ?>
                    <form name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="admin-nir-table">
                            <tbody>
                                <tr>
                                    <td width="5%"><label for="textfield"><strong>Search:</strong></label></td>
                                    <td width="10%" class="padding-right10">
                                        <select name="option" id="option" class="form-control">
                                            <option  value="vPlanName" <?php if ($option == "vPlanName") { echo "selected";} ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?></option>
                                            <option  value="ePlanValidity" <?php if ($option == "ePlanValidity") { echo "selected"; } ?>><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?></option>
                                        </select>
                                    </td>
                                    <td width="15%" class="searchform padding-right10"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>
                                    <td width="20%" class="padding-right10">
                                    <div class="select001">
                                    <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">
                                        <option value="">Select <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                    </select>
                                    </div>
                                    </td>
                                    <td>
                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'driver_subscription_report.php'"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </form>
                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="admin-nir-export">
                                <?php if (!empty($data_drv)) { ?>
                                        <div class="panel-heading">
                                            <form name="_export_form" id="_export_form" method="post" >
                                                <input type="hidden" name="searchDriver" value="<?php echo isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : ''; ?>">
                                                <button type="button" onClick="showExportTypes('driversubscription')">Export</button>
                                            </form>
                                        </div>
                                <?php } ?>
                                </div>
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
<!--                                                    <th align="center" width="3%" style="text-align:center;">
                                                        <input type="checkbox" id="setAllCheck" >
                                                    </th>-->
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].' Name'; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']; ?></th>
                                                    <!--<th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PERIOD']; ?></th>-->
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_SUBSCRIBE_DATE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_EXPIRE_DATE']; ?></th>
                                                    <th><?php echo $langage_lbl_admin['LBL_DRIVER_CANCEL_DATE']; ?></th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                        <?php
                                                        if (!empty($data_drv)) {
                                                            $serverTimeZone = date_default_timezone_get();
                                                            for ($i = 0; $i < scount($data_drv); $i++) {
                                                                $default = '';
                                                                $driverId = $data_drv[$i]['iDriverId'];
                                                                $durTxt = '';
                                                                if($data_drv[$i]['ePlanValidity']=='Weekly') {
                                                                    $durTxt = $langage_lbl_admin['LBL_SUB_WEEKS'];
                                                                }
                                                                if($data_drv[$i]['ePlanValidity']=='Monthly') {
                                                                    $durTxt = $langage_lbl_admin['LBL_SUB_MONTH'];
                                                                } 
                                                                $date_format_data_array = array(
                                                                    'langCode' => $default_lang,
                                                                    'DateFormatForWeb' => 1
                                                                );
                                                                $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tSubscribeDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['tSubscribeDate'];
                                                                $get_subscribe_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                                $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tExpiryDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['tExpiryDate'];
                                                                $get_expiry_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                                $date_format_data_array['tdate'] = (!empty($data_drv[$i]['vTimeZone'])) ? converToTz($data_drv[$i]['tClosedDate'],$data_drv[$i]['vTimeZone'],$serverTimeZone) : $data_drv[$i]['tClosedDate'];
                                                                $get_tClosedDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                                                ?>
                                                        <tr class="gradeA">

                                                            <td>
                                                                <?php if ($userObj->hasPermission('view-providers')) { ?>
                                                                <a href="javascript:void(0);" onClick="show_driver_details('<?= $driverId; ?>')"  style="text-decoration: underline;"><?php } ?>
                                                                <?= clearName($data_drv[$i]['name']); ?>
                                                                <?php if ($userObj->hasPermission('view-providers')) { ?></a> <?php } ?>
                                                            </td>
                                                            <td><?= $data_drv[$i]['vPlanName']; ?></td>
                                                            <td><?= $data_drv[$i]['vPlanPeriod']." ".$durTxt; ?></td>
                                                            <td><?= formateNumAsPerCurrency($data_drv[$i]['fPriceOrg'],''); ?>
                                                            <td><?= $get_subscribe_date_format['tDisplayDate'];//DateTime($data_drv[$i]['tSubscribeDate']); ?></td>
                                                            <td><?= $get_expiry_date_format['tDisplayDate'];//DateTime($data_drv[$i]['tExpiryDate']); ?></td>
                                                            <td><?php if($data_drv[$i]['eSubscriptionStatus']=='Cancelled') echo $get_tClosedDate_format['tDisplayDate'];/*DateTime($data_drv[$i]['tClosedDate']);*/ else echo '-'; ?></td>
                                                            <td><?= $data_drv[$i]['eSubscriptionStatus']; ?></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr class="gradeA">
                                                        <td colspan="8"> No Records Found.</td>
                                                    </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php include('pagination_n.php'); ?>
                                </div>
                            </div> <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>
                                If in case the <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?>  had already subscribed, then <?= strtolower($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> can still subscribe for another plan. It would increase the subscription days.
                            </li>
                            <li>
                                Ex. <?= ucfirst($langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']); ?> is subscribed to 1 month subscription and in middle of 15th day, he subscribe to 1 week plan then his subscription validity days will increased to 22 days instead of 15 days.
                            </li>
                            <li>
                                Administrator can export data in XLS format.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/driver_subscription.php" method="post" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iDriverSubscriptionPlanId" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

<div class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h4>

                    <i style="margin:2px 5px 0 2px;">

                        <img src="images/icon/driver-icon.png" alt="">

                    </i>

                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details

                    <button type="button" class="close" data-dismiss="modal">x</button>

                </h4>

            </div>

            <div class="modal-body" style="max-height: 450px;overflow: auto;">

                <div id="imageIcons" style="display:none">

                    <div align="center">

                        <img src="default.gif">

                        <br/>

                        <span>Retrieving details,please Wait...</span>

                    </div>

                </div>

                <div id="driver_detail"></div>

            </div>

        </div>

    </div>

</div>
<?php include_once('footer.php'); ?>
<link rel="stylesheet" href="css/select2/select2.min.css" />
<script src="js/plugins/select2.min.js"></script>
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

    $('body').on('keyup', '.select2-search__field', function() {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ( $( ".select2-results__options" ).is( ".select2-results__message" ) ) {
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });
    function formatDesign(item) {

        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        //console.log(item);
        var selectionText = item.text.split("--");
        if(selectionText[2] != null && selectionText[1] != null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2]+'</span>');
        } else if(selectionText[2] == null && selectionText[1] != null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if(selectionText[2] != null && selectionText[1] == null){
            var $returnString =  $('<span>'+selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }

        return $returnString;
    };

    function formatDesignnew(item){
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#searchDriver").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                   // quietMillis:100,
                    data: function (params) {
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype:'Driver',
                           // company_id:$('#searchCompany option:selected').val()
                        }
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                    
                        if(data.length < 10){
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
                        
                        return {
                            results: $.map(data, function (item) {

                                if(item.Phoneno != '' && item.vEmail != ''){
                                    var textdata = item.fullName  + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if(item.Phoneno == '' && item.vEmail != ''){
                                    var textdata = item.fullName  + "--" + "Email: " + item.vEmail;
                                } else if(item.Phoneno != '' && item.vEmail == ''){
                                    var textdata = item.fullName  + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                              more: more
                            }
                        };
                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });

    var sId = '<?= $searchDriver;?>';
    var sSelect = $('select.filter-by-text#searchDriver');
    if(sId != ''){

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Driver',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function(response) {
            if(response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if(item.Phoneno != '' && item.vEmail != ''){
                        var textdata = item.fullName  + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if(item.Phoneno == '' && item.vEmail != ''){
                        var textdata = item.fullName  + "--" + "Email: " + item.vEmail;
                    } else if(item.Phoneno != '' && item.vEmail == ''){
                        var textdata = item.fullName  + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelect.append(option).trigger('change'); 
            }
            else {
                
            }
        });
    }

    function show_driver_details(driverid) {
        $("#driver_detail").html('');
        $("#imageIcons").show();
        $("#detail_modal").modal('show');

        if (driverid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': "iDriverId=" + driverid,
                'REQUEST_DATA_TYPE': 'html'
            };

            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#imageIcons").hide();
                } else {
                    
                    $("#imageIcons").hide();
                }
            });
        }
    }
</script>
</body>
<!-- END BODY-->
</html>
