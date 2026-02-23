<?php
include_once('../common.php');

 
$start = @date("Y");
$end = '1970';
$tbl_name = 'driver_vehicle';
$script = 'TrackServiceDriverVehicle';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';


$db_driver_detail_sql = "SELECT iDriverId,concat(vName,' ',vLastName) AS DriverName FROM register_driver WHERE eStatus!='Deleted' ORDER By iDriverId ASC";
$db_driver_detail = $obj->MySQLSelect($db_driver_detail_sql);

$sql = "SELECT * FROM driver_vehicle WHERE iDriverVehicleId = '" . $id . "' ";
$db_mdl = $obj->MySQLSelect($sql);

// set all variables with either post (when submit) either blank (when insert)
$vLicencePlate = isset($_POST['vLicencePlate']) ? $_POST['vLicencePlate'] : '';
$iMakeId = isset($_POST['iMakeId']) ? $_POST['iMakeId'] : '';
$iModelId = isset($_POST['iModelId']) ? $_POST['iModelId'] : '';
$iYear = isset($_POST['iYear']) ? $_POST['iYear'] : '';
$eStatus_check = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'off';
$iDriverId = isset($_POST['iDriverId']) ? $_POST['iDriverId'] : '';
$vColour = isset($_POST['vColour']) ? $_POST['vColour'] : '';
$iCompanyId = isset($_POST['iCompanyId']) ? $_POST['iCompanyId'] : '';
$eStatus = ($eStatus_check == 'on') ? 'Active' : 'Inactive';
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$eType = 'TrackService';


$sql = "SELECT ma.* FROM make AS ma JOIN model as mo ON ma.iMakeId=mo.iMakeId WHERE ma.eStatus='Active' AND mo.eStatus='Active' GROUP BY ma.iMakeId ORDER By ma.vMake ASC";
$db_make = $obj->MySQLSelect($sql);

$sql = "SELECT * from track_service_company WHERE eStatus = 'Active' ORDER BY iTrackServiceCompanyId ASC";
$db_company = $obj->MySQLSelect($sql);

if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-driver-vehicle-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create ' . strtolower($langage_lbl_admin["LBL_TEXI_ADMIN"]);
        header("Location:track_service_driver_vehicle.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-driver-vehicle-trackservice')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update ' . strtolower($langage_lbl_admin["LBL_TEXI_ADMIN"]);
        header("Location:track_service_driver_vehicle.php");
        exit;
    }

    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:track_service_driver_vehicle.php?id=" . $id);
        exit;
    }

    //Added By Hasmukh On 30-10-2018 For Check eAddedDeliverVehicle Value End
    require_once("library/validation.class.php");
    $validobj = new validation();
    $validobj->add_fields($_POST['iMakeId'], 'req', 'Make is required.');
    $validobj->add_fields($_POST['iModelId'], 'req', 'Model is required.');
    $validobj->add_fields($_POST['iYear'], 'req', 'Year is required.');
    $validobj->add_fields($_POST['vLicencePlate'], 'req', 'Licence plate Id is required.');
    if (ONLYDELIVERALL == 'No') {
        $validobj->add_fields($_POST['iCompanyId'], 'req', 'Company is required.');
    }
    $validobj->add_fields($_POST['iDriverId'], 'req', $langage_lbl_admin["LBL_DRIVER_TXT_ADMIN"] . ' is required.');

    $error = $validobj->validate();
    if ($error) {
        $_SESSION['success'] = '3';
        $_SESSION['var_msg'] = $error;
        header("location:vehicle_add_form.php");
        exit();
    } else {
        $q = "INSERT INTO ";
        $where = '';

        if ($id != '') {
            $q = "UPDATE ";
            $where = " WHERE `iDriverVehicleId` = '" . $id . "'";
        }
        $query = $q . " `" . $tbl_name . "` SET
			`iModelId` = '" . $iModelId . "',
			`vLicencePlate` = '" . $vLicencePlate . "',
			`iYear` = '" . $iYear . "',
			`iMakeId` = '" . $iMakeId . "',
			`iTrackServiceCompanyId` = '" . $iCompanyId . "',
			`iDriverId` = '" . $iDriverId . "',
			`vColour` = '" . $vColour . "',
			`eStatus` = '" . $eStatus . "',
			`eType` = '" . $eType . "'"
                . $where;
        $obj->sql_query($query);

        $id = ($id != '') ? $id : $obj->GetInsertId();

        $obj->sql_query("UPDATE register_driver SET iDriverVehicleId = '$id' WHERE iDriverId = '$iDriverId' ");
        
        if ($action == "Add") {
            $sql = "SELECT vName, vLastName, vEmail FROM register_driver WHERE iDriverId = '" . $iDriverId . "'";
            $db_status = $obj->MySQLSelect($sql);

            $maildata['EMAIL'] = $db_status[0]['vEmail'];
            $maildata['NAME'] = $db_status[0]['vName'] . " " . $db_status[0]['vLastName'];
            
            $maildata['DETAIL'] = '<br />' . str_replace(['#VEHICLE_TXT#', '#JOB_TXT#', '#USER#'], [$langage_lbl_admin['LBL_TEXI_ADMIN'], $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'], $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']], $langage_lbl_admin['LBL_VEHICLE_ADDED_ADMIN_EMAIL']);
            $COMM_MEDIA_OBJ->SendMailToMember("VEHICLE_BOOKING", $maildata);
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
}

// for Edit
if ($action == 'Edit') {
    $sql = "SELECT * from  $tbl_name where iDriverVehicleId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $iMakeId = $value['iMakeId'];
            $iModelId = $value['iModelId'];
            $vLicencePlate = $value['vLicencePlate'];
            $iYear = $value['iYear'];
            $eType = $value['eType'];
            $iDriverId = $value['iDriverId'];
            $iCompanyId = $value['iTrackServiceCompanyId'];
            $eStatus = $value['eStatus'];
            $vColour = $value['vColour'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME ?> |  <?php echo $langage_lbl_admin['LBL_VEHICLE_TXT_ADMIN']; ?> <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="keywords" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once('global_files.php'); ?>
        <!-- On OFF switch -->
        <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
        <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
        <link rel="stylesheet" href="../assets/validation/validatrix.css" />
        <link rel="stylesheet" href="../assets/css/modal_alert.css" />
        <link rel="stylesheet" href="css/select2/select2.min.css" />
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action . " " . $langage_lbl_admin['LBL_VEHICLE_TITLE']; ?></h2>
                            <a href="track_service_driver_vehicle.php" class="back_link">
                                <input type="button" value="<?= $langage_lbl_admin['LBL_RIDER_back_to_listing']; ?>" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php include('valid_msg.php'); ?>
                            <form name="_track_service_vehicle_form" id="_track_service_vehicle_form" method="post" action="">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="track_service_driver_vehicle.php"/>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Make<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name = "iMakeId" id="iMakeId" class="form-control" onChange="get_model(this.value, '')" >
                                            <option value="">CHOOSE MAKE</option>
                                            <?php for ($j = 0; $j < scount($db_make); $j++) { ?>
                                                <option value="<?= $db_make[$j]['iMakeId'] ?>" <?php if ($iMakeId == $db_make[$j]['iMakeId']) { ?> selected <?php } ?>><?= $db_make[$j]['vMake'] ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Model<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <div id="carmdl">
                                            <select name="iModelId" id="iModelId" class="form-control" >
                                                <option value="">CHOOSE MODEL </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Year<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name = "iYear" id="iYear" class="form-control" >
                                            <option value="">CHOOSE YEAR </option>
                                            <?php for ($j = $start; $j >= $end; $j--) { ?>
                                                <option value="<?= $j ?>" <?php if ($iYear == $j) { ?> selected <?php } ?>><?= $j ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>License Plate<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vLicencePlate"  id="vLicencePlate" value="<?= $vLicencePlate; ?>" placeholder="Licence Plate" >
                                        <b><span id="plate_warning" class="error"></span></b>
                                    </div>
                                </div>

                                <div class="row" id="companylisthtml">
                                    <div class="col-lg-12">
                                        <label>Company<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name = "iCompanyId" id="iCompanyId" class="form-control filter-by-text"  data-text="CHOOSE COMPANY">
                                            <option value="">CHOOSE COMPANY</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label><?php echo $langage_lbl_admin['LBL_VEHICLE_DRIVER_TXT_ADMIN']; ?> <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name = "iDriverId" id="driverNo" class="form-control filter-by-text" data-text="<?= $langage_lbl_admin['LBL_CHOOSE_DRIVER_ADMIN']; ?>">
                                            <option value=""><?php echo $langage_lbl_admin['LBL_CHOOSE_DRIVER_ADMIN']; ?> </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Vehicle <?php echo $langage_lbl_admin['LBL_COLOR_ADD_VEHICLES']; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vColour"  id="vColour" value="<?= $vColour; ?>"  placeholder="Vehicle Color" >
                                    </div>
                                </div>
                                
                                <?php if ($eStatus != 'Deleted') { ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Status</label>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="make-switch" data-on="success" data-off="warning">
                                                <input type="checkbox" name="eStatus" id="eStatus" <?= ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> />
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>	
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php if (($action == 'Edit' && $userObj->hasPermission('edit-driver-vehicle-trackservice')) || ($action == 'Add' && $userObj->hasPermission('create-driver-vehicle-trackservice'))) { ?>
                                            <input type="submit" class="btn btn-default" name="submit" id="submit"  value="<?php if ($action == 'Add') { ?><?= $action; ?> <?php echo $langage_lbl_admin['LBL_Vehicle']; ?><?php } else { ?>Update<?php } ?>">
                                        <?php } ?>
                                        <a href="track_service_driver_vehicle.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>
                                Please close the application and open it again to see the reflected changes after saving the values above.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->


        <?php include_once('footer.php'); ?>
        <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
        <script src="../assets/js/modal_alert.js"></script>
        <script src="js/plugins/select2.min.js"></script>
        <script>
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
                $("select.filter-by-text#driverNo").each(function () {
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
                                var company_id = $('#iCompanyId option:selected').val();
                                var queryParameters = {
                                    term: params.term,
                                    page: params.page || 1,
                                    usertype: 'TrackServiceDriver',
                                    company_id: company_id
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
                            cache: false
                        }
                    }); //theme: 'classic'
                });
            });

            $(function () {
                $("select.filter-by-text#iCompanyId").each(function () {

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
                                    usertype: 'TrackServiceCompany',

                                }

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
                            cache: false
                        }
                    }); //theme: 'classic'
                });
            });
            
            var sId = '<?= $iDriverId;?>';
            var sSelect = $('select.filter-by-text#driverNo');
            var sIdCompany = '<?= $iCompanyId;?>';
            var sSelectCompany = $('select.filter-by-text#iCompanyId');
            var itemname;
            var itemid;
            
            if(sIdCompany != ''){

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompany + '&usertype=TrackServiceCompany',
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
                        sSelectCompany.append(option);
                    }
                    else {
                        console.log(response.result);
                    }
                });
            }
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
                        //sSelect.append(option).trigger('change');
                        sSelect.append(option);
                    }
                    else {
                        // console.log(response.result);
                    }
                });
            }
            var $eventSelect = $("select.filter-by-text#iCompanyId");
            $eventSelect.on("change", function (e) { 
                $('select.filter-by-text#driverNo').val(null).trigger('change');
            });
            var $eventstoreSelect = $("select.filter-by-text#iCompanyIdhtml");
            $eventstoreSelect.on("change", function (e) { 
                $('select.filter-by-text#driverNo').val(null).trigger('change');
            });

            <?php if ($action == 'Edit') { ?>
                window.onload = function () {
                    get_model('<?php echo $db_data[0]['iMakeId']; ?>', '<?php echo $db_data[0]['iModelId']; ?>');
                };
            <?php } ?>

            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "track_service_driver_vehicle.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);

            });

            function get_model(model, modelid) {
                $("#carmdl").html('Wait...');

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_find_model.php',
                    'AJAX_DATA': "action=get_model&model=" + model + "&iModelId=" + modelid,
                };
                getDataFromAjaxCall(ajaxData, function(response) {
                    if(response.action == "1") {
                        var data = response.result;
                        $("#carmdl").html(data); 
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