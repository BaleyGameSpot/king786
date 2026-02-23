<?php
include_once('../common.php');

if (!$userObj->hasPermission('dashboard-notifications-alerts-panel')) {
    $userObj->redirect();
}
$script = '';
$tableName = "contactus";
//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY iContactusId DESC';

if ($sortby == 1) {
    if ($order == 0)
        $ord = " ORDER BY vFirstname ASC";
    else
        $ord = " ORDER BY vFirstname DESC";
}
//End Sorting

$cmp_ssql = "";

// Start Search Parameters
$searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
$searchCompanyMain = isset($_REQUEST['searchCompanyMain']) ? $_REQUEST['searchCompanyMain'] : '';
$searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
$searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
$iContactusId = isset($_REQUEST['iContactusId']) ? $_REQUEST['iContactusId'] : 0;
$ssql = '';
if ($startDate != '') {
    $ssql .= " AND Date(tRequestDate) >='" . $startDate . "'";
}
if ($endDate != '') {
    $ssql .= " AND Date(tRequestDate) <='" . $endDate . "'";
}
if ($searchRider != '' || $searchCompany != '' || $searchDriver != '' || $searchCompanyMain!='') {
    $ssql1 = " AND iMemberId IN(";
    if(!empty($searchRider)) {
        $ssql1 .= $searchRider . ", ";
    }
    if(!empty($searchCompany)) {
        $ssql1 .= $searchCompany . ", ";
    }
    if(!empty($searchCompanyMain)) {
        $ssql1 .= $searchCompanyMain . ", ";
    }
    if(!empty($searchDriver)) {
        $ssql1 .= $searchDriver . ", ";
    }
    $ssql1 = trim($ssql1,", ");
    $ssql1 .= ")";

    $ssql .= $ssql1;
}

if($iContactusId != 0)
{
    $ssql = "AND iContactusId =".$iContactusId;
}

//Pagination Start
$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql = "SELECT COUNT(*) AS Total FROM `document_list` AS dl LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid LEFT JOIN company AS c ON ( c.iCompanyId = dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store')) LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver') LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car') LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId WHERE  dm.doc_name_EN != ''  ORDER BY dl.edate DESC";


$totalData = $obj->MySQLSelect($sql);
$total_results = $totalData[0]['Total'];

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


$sql = "SELECT dm.doc_name_" . $default_lang . ",dl.doc_usertype,rd.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) AS `Driver`,CONCAT(rdn.vName,' ',rdn.vLastName) AS `DriverName`,dv.iDriverVehicleId, c.vCompany,dl.edate,c.iCompanyId,rdn.vTimeZone FROM `document_list` AS dl LEFT JOIN document_master AS dm ON dm.doc_masterid=dl.doc_masterid LEFT JOIN company AS c ON ( c.iCompanyId = dl.doc_userid AND (dl.doc_usertype='company' || dl.doc_usertype='store')) LEFT JOIN register_driver AS rd ON (rd.iDriverId=dl.doc_userid AND dl.doc_usertype='driver') LEFT JOIN driver_vehicle AS dv ON (dv.iDriverVehicleId=dl.doc_userid AND dl.doc_usertype='car') LEFT JOIN register_driver AS rdn ON rdn.iDriverId=dv.iDriverId WHERE  dm.doc_name_EN != '' ORDER BY dl.edate DESC LIMIT $start, $per_page";
$db_notification_1 = $obj->MySQLSelect($sql);

$endRecord = scount($db_notification_1);

$def_timezone = $obj->MySQLSelect("SELECT vTimeZone FROM country WHERE vCountryCode = '".$DEFAULT_COUNTRY_CODE_WEB."'");

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
    <title><?= $SITE_NAME ?> | Notifications Alerts Panel</title>
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
                        <h2>Notifications Alerts Panel</h2>
                    </div>
                </div>
                <hr />
            </div>
            <?php include('valid_msg.php'); ?>
            <form style = "display: none" name="frmsearch" id="frmsearch" action="javascript:void(0);" method="post">
                <div class="Posted-date mytrip-page payment-report">
                    <input type="hidden" name="action" value="search">
                    <h3>Search...</h3>
                    <span>
                            <input type="text" id="dp4" name="startDate" placeholder="From Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff">
                            <input type="text" id="dp5" name="endDate" placeholder="To Date" class="form-control" value="" readonly="" style="cursor:default; background-color: #fff">
                            <div class="col-lg-3 select001">
                                <select class="form-control filter-by-text" name = 'searchRider' data-text="Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?>" id="searchRider">
                                    <option value="">Select <?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN']; ?></option>
                                </select>
                            </div>
                            <div class="col-lg-3 select001">
                                <select class="form-control filter-by-text driver_container" name = 'searchDriver' data-text="Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?>" id="searchDriver">
                                    <option value="">Select <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']; ?></option>
                                </select>
                            </div>
                        </span>
                </div>
                <div class="row payment-report payment-report1 payment-report2">
                    <div class="col-lg-2 select001">
                        <select class="form-control filter-by-text" name = "searchCompany" id="searchCompany" data-text="Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>">
                            <option value="">Select <?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?></option>
                        </select>
                    </div>
                    <div class="col-lg-2 select001">
                        <select class="form-control filter-by-text" name = "searchCompanyMain" id="searchCompanyMain" data-text="Select Company">
                            <option value="">Select Company</option>
                        </select>
                    </div>
                </div>
                <div class="tripBtns001"><b>
                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />
                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'contactus.php'"/>
                    </b></div>
            </form>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div style="clear:both;"></div>
                        <div class="table-responsive">
                            <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                <table class="table table-striped table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th width="20%">Name</th>
                                        <th>Description </th>
                                        <th width="10%">Date Time</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if (!empty($db_notification_1)) {
                                        $systemTimeZone = date_default_timezone_get();
                                        for ($i = 0; $i < scount($db_notification_1); $i++) { 
                                            $date_format_data_array = array(
                                                'tdate' =>  (!empty($db_notification_1[$i]['vTimeZone'])) ? converToTz($db_notification_1[$i]['edate'], $db_notification_1[$i]['vTimeZone'], $systemTimeZone) : $db_notification_1[$i]['edate'],
                                                'langCode' => $default_lang,
                                                'DateFormatForWeb' => 1
                                            );
                                            $get_edate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                            ?>
                                            <tr class="gradeA">
                                                <?php
                                                $url = "#";
                                                if ($db_notification_1[$i]['doc_usertype'] == 'driver') {
                                                    $url = "driver_document_action.php";
                                                    $id = $db_notification_1[$i]['iDriverId'];
                                                    if ($db_notification_1[$i]['doc_name_' . $default_lang] != '') {
                                                        $msg = strtoupper($db_notification_1[$i]['doc_name_' . $default_lang]) . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                        $name = clearName($db_notification_1[$i]['Driver']);
                                                    } else {
                                                        $msg = $db_notification_1[$i]['doc_name_' . $default_lang] . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                        $name = clearName($db_notification_1[$i]['Driver']);
                                                    }
                                                } else if ($db_notification_1[$i]['doc_usertype'] == 'company') {
                                                    $url = "company_document_action.php";
                                                    $id = $db_notification_1[$i]['iCompanyId'];
                                                    if ($db_notification_1[$i]['doc_name_' . $default_lang] != '') {
                                                        $msg = strtoupper($db_notification_1[$i]['doc_name_' . $default_lang]) . " uploaded by " . $db_notification_1[$i]['doc_usertype'] . "";
                                                        $name = clearCmpName($db_notification_1[$i]['vCompany']);
                                                    } else {
                                                        $msg = $db_notification_1[$i]['doc_name_' . $default_lang] . " uploaded by " . $db_notification_1[$i]['doc_usertype'] . "";
                                                        $name = clearCmpName($db_notification_1[$i]['vCompany']);
                                                    }
                                                } else if ($db_notification_1[$i]['doc_usertype'] == 'car') {
                                                    $url = "vehicle_document_action.php";
                                                    $id = $db_notification_1[$i]['iDriverVehicleId'];
                                                    if ($db_notification_1[$i]['doc_name_' . $default_lang] != '') {
                                                        $msg = strtoupper($db_notification_1[$i]['doc_name_' . $default_lang]) . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                        $name = clearName($db_notification_1[$i]['DriverName']);
                                                    } else {
                                                        $msg = $db_notification_1[$i]['doc_name_' . $default_lang] . " uploaded by " . $langage_lbl['LBL_DRIVER_TXT_ADMIN'] . "";
                                                        $name = clearName($db_notification_1[$i]['DriverName']);
                                                    }
                                                } else if ($db_notification_1[$i]['doc_usertype'] == 'store') {
                                                    $url = "store_document_action.php";
                                                    $id = $db_notification_1[$i]['iCompanyId'];
                                                    if ($db_notification_1[$i]['doc_name_' . $default_lang] != '') {
                                                        $msg = strtoupper($db_notification_1[$i]['doc_name_' . $default_lang]) . " uploaded by " . $db_notification_1[$i]['doc_usertype'] . "";
                                                        $name = clearCmpName($db_notification_1[$i]['vCompany']);
                                                    } else {
                                                        $msg = $db_notification_1[$i]['doc_name_' . $default_lang] . " uploaded by " . $db_notification_1[$i]['doc_usertype'] . "";
                                                        $name = clearCmpName($db_notification_1[$i]['vCompany']);
                                                    }
                                                }


                                                ?>
                                                <td><?php echo $name; ?></td>
                                                <td><?php echo $msg; ?></td>
                                                <td><?php echo $get_edate_format['tDisplayDateTime'];//DateTime($db_notification_1[$i]['edate'] , '21'); ?></td>
                                                <td>


                                                    <a href="<?= $url; ?>?id=<?php echo $id; ?>&action=edit" data-toggle="tooltip" title="View">
                                                        <img src="img/edit-icon.png" alt="Edit">
                                                    </a> </td>


                                            </tr>
                                        <?php } } else { ?>
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
            <div style = "display: none" class="admin-notes">
                <h4>Note:</h4>
                <ul>
                    <li>This will list all the support requests submitted through Contact Us Form.</li>
                    <!-- <li>You can communicate with the member via mentioned contact details externally.</li> -->
                </ul>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<div class="modal fade " id="detail_modal_user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <!--<i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>-->
                    <i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>
                    <?php echo $langage_lbl_admin['LBL_RIDER']; ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons_user">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="rider_detail" ></div>
            </div>
        </div>
    </div>
</div>
<div  class="modal fade" id="detail_modal_driver" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h4>
                    <i style="margin:2px 5px 0 2px;"><img src="images/icon/driver-icon.png" alt=""></i>
                    <?= $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] ?> Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons_driver" style="display:none">
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
<div  class="modal fade" id="detail_modal_store" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h4><i aria-hidden="true" class="fa fa-building-o" style="margin:2px 5px 0 2px;"></i>
                    <span id="typetitle"></span> Details<button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons_store" style="display:none">
                    <div align="center">
                        <img src="default.gif"><br/>
                        <span>Retrieving details,please Wait...</span>
                    </div>
                </div>
                <div id="comp_detail"></div>
            </div>
        </div>
    </div>
</div>
<form name="pageForm" id="pageForm" action="action/contactus.php" method="post" >
    <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
    <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
    <input type="hidden" name="iCompanyId" id="iMainId01" value="" >
    <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
    <input type="hidden" name="status" id="status01" value="" >
    <input type="hidden" name="statusVal" id="statusVal" value="" >
    <input type="hidden" name="option" value="<?php echo $option; ?>" >
    <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
    <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
    <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
    <input type="hidden" name="method" id="method" value="" >
    <input type="hidden" name="startDate" value="<?= $startDate; ?>" >
    <input type="hidden" name="endDate" value="<?= $endDate; ?>" >
    <input type="hidden" name="searchCompany" value="<?= $searchCompany; ?>" >
    <input type="hidden" name="searchCompanyMain" value="<?= $searchCompanyMain; ?>" >
    <input type="hidden" name="searchDriver" value="<?= $searchDriver; ?>" >
    <input type="hidden" name="searchRider" value="<?= $searchRider; ?>" >
</form>
<?php
include_once('footer.php');
?>
<script src="../assets/js/modal_alert.js"></script>
<link rel="stylesheet" href="../assets/css/modal_alert.css" />
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="css/select2/select2.min.css" />
<script src="js/plugins/select2.min.js"></script>

<?php //include_once('searchfunctions.php'); ?>
<script>
    var startDate;
    var endDate;
    $('#dp4').datepicker()
        .on('changeDate', function (ev) {
            startDate = new Date(ev.date);
            if (endDate != null) {
                if (ev.date.valueOf() < endDate.valueOf()) {
                    alert('The start date can not be greater then the end date');
                    //$('#alert').show().find('strong').text('The start date can not be greater then the end date');
                } else {
                    $('#alert').hide();
                    $('#startDate').text($('#dp4').data('date'));
                }
            }
            $('#dp4').datepicker('hide');
        });
    $('#dp5').datepicker()
        .on('changeDate', function (ev) {
            endDate = new Date(ev.date);
            if (startDate != null) {
                if (ev.date.valueOf() < startDate.valueOf()) {
                    alert("The end date can not be less then the start date");
                    //$('#alert').show().find('strong').text('The end date can not be less then the start date');
                } else {
                    $('#alert').hide();
                    $('#endDate').text($('#dp5').data('date'));
                }
            }
            $('#dp5').datepicker('hide');
        });

    $(document).ready(function () {
        $('#usertype_options').hide();
        $('#option').each(function () {
            if (this.value == 'eUserType') {
                $('#usertype_options').show();
                $('.searchform').hide();
            }
        });
        if ('<?= $startDate ?>' != '') {
            $("#dp4").val('<?= $startDate ?>');
            $("#dp4").datepicker('update', '<?= $startDate ?>');
        }
        if ('<?= $endDate ?>' != '') {
            $("#dp5").datepicker('update', '<?= $endDate; ?>');
            $("#dp5").val('<?= $endDate; ?>');
        }
    });
    $('#option').change(function () {
        if ($('#option').val() == 'eUserType') {
            $('#usertype_options').show();
            $("input[name=keyword]").val("");
            $('.searchform').hide();
        } else {
            $('#usertype_options').hide();
            $("#estatus_value").val("");
            $('.searchform').show();
        }
    });
    $("#Search").on('click', function () {
        if ($("#dp5").val() < $("#dp4").val()) {
            alert("From date should be lesser than To date.")
            return false;
        } else {
            var action = $("#_list_form").attr('action');
            var formValus = $("#frmsearch").serialize();
            window.location.href = action + "?" + formValus;
        }
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

    function show_details(contactid) {
        //$("#rider_detail").html($("#condetails_" +contactid).html());
        //$("#detail_modal").modal('show');
        message = $("#condetails_" +contactid).html();
        show_alert("Message",message,"ok","","",function (btn_id) {}, true,true,true);

    }
    function show_company_details(companyid,usertype) {
        $("#comp_detail").html('');
        $("#imageIcons_store").show();
        $("#detail_modal_store").modal('show');

        if(usertype=='company') {
            urlfile = "ajax_company_details.php";
            modeltitle = "Company";
        } else {
            urlfile = "ajax_store_details.php";
            modeltitle = "<?= $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']; ?>";
        }
        $("#typetitle").html(modeltitle);
        if (companyid != "") {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>' + urlfile,
                'AJAX_DATA': {"iCompanyId": companyid,"editTrip":"No"},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    $("#comp_detail").html(data);
                    $("#imageIcons_store").hide();
                }
                else {
                    console.log(response.result);
                    $("#imageIcons_store").hide();
                }
            });
        }
    }
    function show_driver_details(driverid) {
        $("#driver_detail").html('');
        $("#imageIcons_driver").show();
        $("#detail_modal_driver").modal('show');
        if (driverid != "") {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': {"iDriverId": driverid,"editTrip":"No"},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#imageIcons_driver").hide();
                }
                else {
                    console.log(response.result);
                    $("#imageIcons_driver").hide();
                }
            });
        }
    }
    function show_rider_details(userid) {
        $("#rider_detail").html('');
        $("#imageIcons_user").show();
        $("#detail_modal_user").modal('show');
        if (userid != "") {

            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': {"iUserId": userid,"editTrip":"No"},
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function(response) {
                if(response.action == "1") {
                    var data = response.result;
                    $("#rider_detail").html(data);
                    $("#imageIcons_user").hide();
                }
                else {
                    console.log(response.result);
                    $("#imageIcons_user").hide();
                }
            });
        }
    }
    function formatDesign(item) {
        //console.log(item.text);
        /*if(item.text == 'Searching…'){
            console.log('item1');
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
       }*/
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
        //$(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
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
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype:'Driver',
                            //company_id:$('#searchCompany option:selected').val()
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
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

    $(function () {
        $("select.filter-by-text#searchRider").each(function () {
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
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype:'Rider',
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
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

    $(function () {
        $("select.filter-by-text#searchCompany").each(function () {
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
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype:'Store',
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
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
    $(function () {
        $("select.filter-by-text#searchCompanyMain").each(function () {
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
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype:'Company', 
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
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
    // Fetch the preselected item, and add to the control
    var sId = '<?= $searchDriver;?>';
    var sSelect = $('select.filter-by-text#searchDriver');
    var sIdRider = '<?= $searchRider;?>';
    var sSelectRider = $('select.filter-by-text#searchRider');
    var sIdCompany = '<?= $searchCompany;?>';
    var sSelectCompany = $('select.filter-by-text#searchCompany');
    var sIdCompanyMain = '<?= $searchCompanyMain;?>';
    var sSelectCompanyMain = $('select.filter-by-text#searchCompanyMain');
    var itemname;
    var itemid;
    if(sId != ''){

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Driver',
            'AJAX_DATA': '',
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
                console.log(response.result);
            }
        });
    }

    if(sIdRider != ''){

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdRider + '&usertype=Rider',
            'AJAX_DATA': '',
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
                sSelectRider.append(option).trigger('change');
            }
            else {
                console.log(response.result);
            }
        });
    }

    if(sIdCompany != ''){

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompany + '&usertype=Store',
            'AJAX_DATA': '',
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
                sSelectCompany.append(option);
            }
            else {
                console.log(response.result);
            }
        });
    }
    if(sIdCompanyMain != ''){

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompanyMain + '&usertype=Company',
            'AJAX_DATA': '',
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
                sSelectCompanyMain.append(option);
            }
            else {
                console.log(response.result);
            }
        });
    }

</script>
</body>
<!-- END BODY-->
</html>