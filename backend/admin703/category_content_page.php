<?php
include_once('../common.php');
include_once('ajax_category_content_page.php');
$iLanguageMasId = $id = $_REQUEST['id'] ?? '';
if(empty($id)){
    $sql = "SELECT iLanguageMasId FROM language_master WHERE vCode = '" . $default_lang . "'";
    $language_master = $obj->MySQLSelect($sql);
    $iLanguageMasId = $id = $language_master[0]['iLanguageMasId'];
}
$sql = "SELECT vCode,vTitle FROM language_master WHERE iLanguageMasId = '" . $id . "'";
$db_data = $obj->MySQLSelect($sql);
$vCode = $db_data[0]['vCode'];
$title = $db_data[0]['vTitle'];

$tbl_name = getAppTypeWiseHomeTable();
//$script = 'categoryInnerPage';
$script = 'ServiceSection';
$book_data = $obj->MySQLSelect("SELECT booking_ids FROM $tbl_name WHERE vCode = '" . $_SESSION['sess_lang'] . "'");
$booking_ids_db = $book_data[0]['booking_ids'];

$vcatdata_first = getSeviceCategoryDataForHomepage($booking_ids_db, 0, 1, 'Yes');
if(!empty($booking_ids_db)) {
    $booking_ids_db .= ",326,341";
} else {
    $booking_ids_db = "326,341";
}

if($MODULES_OBJ->isEnableMedicalServices()) {
    $booking_ids_db .= ",3,22,26,158";
}

$vcatdata_sec = getSeviceCategoryDataForHomepage($booking_ids_db, 1, 1, 'Yes');
$vcatdata_main = array_merge($vcatdata_first, $vcatdata_sec);
$vcatdata_main = array_unique($vcatdata_main, SORT_REGULAR);


$parentCat = $subCat = [];
foreach ($vcatdata_main as $vcat) {
    $checkIn = in_array($vcat['eCatType'], [
        'MoreDelivery',
        'Genie',
        'VideoConsult',
        'ServiceBid',
        'MoreService',
        'RentEstate',
        'RentCars',
        'RentItem',
        'RideShare',
        'MedicalService',
        'TrackService',
        'NearBy',
        'TrackAnyService'
    ]);
    if ($checkIn || ($vcat['eCatType'] == 'DeliverAll' && $vcat['iServiceId'] == 0) || ($vcat['eCatType'] == 'Ride' && $vcat['eForMedicalService'] != 'Yes')) {
        $parentCat[] = $vcat;
    } else {
        $subCat[] = $vcat;
    }
}

$sql = "SELECT booking_ids,morebooking_ids FROM $tbl_name as hc LEFT JOIN language_master as lm on lm.vCode = hc.vCode  WHERE lm.iLanguageMasId = '" . $iLanguageMasId . "'";
$db_data = $obj->MySQLSelect($sql);

$booking_ids = $db_data[0]['booking_ids'];
$booking_ids = explode(',', $booking_ids);

$morebooking_ids = $db_data[0]['morebooking_ids'];
$morebooking_ids = explode(',', $morebooking_ids);

$sectionType = $_POST['Type'] ?? '';
if ($sectionType == 'parent') {
    echo masterService();
    exit;
}
if ($sectionType == 'sub') {
    echo subService();
    exit;
}
if ($sectionType == 'allservices') {
    echo AllService();
    exit;
}
if ($sectionType == 'Save') {
    echo vehicleCategoryDisplayTOHomePage();
    exit;
}
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN']; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <style type="text/css">
        .service-title {
            padding: 10px;
            font-size: 20px;
            font-weight: 600;
            border-radius: 5px;
            margin: 0;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        hr.service-line {
            border: 1px solid;
            width: calc(100% - 20px);
            margin: 0 0 20px 10px;
        }

        .toggle-list-inner .toggle-combo {
            padding: 21px 21px 14px;
        }

        .check-combo ul {
            padding-left: 14px;
        }
        .check-combo ul li {
            margin-bottom: 12px;
        }
    </style>
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
            <div id="add-hide-show-div" class="vehicleCategorylist">
                <div class="row">
                    <h2>Services for Home Page</h2>
                     <button type="button" class="add-btn" onclick="getServices('allservices')">Manage</button>
                    <?php /*<a href="home_content_cubejekxv3pro_action.php?id=<?php echo $iLanguageMasId; ?>" class="back_link">
                        <input type="button" value="Back To Page" class="add-btn">
                    </a>*/ ?>
                </div>
                <hr/>
            </div>
            <?php include('valid_msg.php'); ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="admin-nir-export vehicle-cat">
                            <div style="clear:both;"></div>
                            <div class="table-responsive1">
                                <div class="table table-striped  table-hover">
                                    <div class="profile-earning">
                                        <div class="partation">
                                            <div class="service-title">
                                                <span>Major Services</span>
                                                <!-- <span>
                                                    <button type="button" class="add-btn"
                                                            onclick="getServices('parent')">Manage</button>
                                                </span> -->
                                            </div>
                                            <hr class="service-line"/>
                                            <ul style="padding-left: 0px;" class="setings-list">
                                                <?php if (isset($parentCat) && !empty($parentCat)) {
                                                    foreach ($parentCat as $vcat) {
                                                        echo cardOfCategory(1, 0, $vcat);
                                                    }
                                                } else { ?>
                                                    <li style="font-size: 16px">No Services Found.</li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <div class="partation">
                                            <div class="service-title">
                                                <span>More Service</span>
                                               <!--  <span>
                                                    <button type="button" class="add-btn"
                                                            onclick="getServices('sub')">Manage</button>
                                                </span> -->
                                            </div>
                                            <hr class="service-line"/>
                                            <ul style="padding-left: 0px;" class="setings-list">
                                                <?php if (isset($subCat) && !empty($subCat)) {
                                                    foreach ($subCat as $vcat) {
                                                        echo cardOfCategory(0, 1, $vcat);
                                                    }
                                                } else { ?>
                                                    <li style="font-size: 16px">No Services Found.</li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="modal fade" id="services_modal" tabindex="-1" role="dialog" aria-hidden="true"
                 data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content nimot-class">
                        <div class="modal-header">
                            <h4>
                                Service - Inner Page
                                <button type="button" class="close" data-dismiss="modal">x</button>
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="">
                                <input type="hidden" name="id" value="<?= $iLanguageMasId; ?>">
                                <table class="table table-striped table-bordered table-hover"
                                       id="service-table">
                                    <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Display Order</th>
                                        <th>Include On Major Services Section <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title='For Adding service to our major service section on the home page.' ></i></th>
                                        <th>Include On More Services Section <i class="icon-question-sign" data-placement="bottom" data-toggle="tooltip" data-original-title='For Adding service to more service section in the major service section on the home page.' ></i></th>
                                    </tr>
                                    </thead>
                                    <tbody id="service-list"></tbody>
                                </table>
                            </form>
                        </div>
                        <div class="modal-footer" style="text-align: left">
                            <button type="button" class="btn btn-default" onclick="saveServices()">Save
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row loding-action" id="loaderIcon" style="display:none;">
                <div align="center">
                    <img src="default.gif">
                </div>
            </div>

        </div>
    </div>

    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
    function showAlert() {
        alert('In order to update the status, you need to click on the "MANAGE" button.');
    }

    function getServices(sectionType) {

        $("#loaderIcon").show();
        $('#service-list').html('');
        $('#services_modal').modal('show');
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'category_content_page.php' ?>',
            'AJAX_DATA': {Type: sectionType, id: $('input[name="id"]').val()},
            'REQUEST_DATA_TYPE': 'html',
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            $("#loaderIcon").hide();
            if (response.action == "1") {
                var responseData = response.result;
                $('#service-table').show();
                $('#service-list').html(responseData);
                $('.make-switch')['bootstrapSwitch']();
            }
        });
    }

    function saveServices(display_order = 0) {
        $("#loaderIcon").show();

        var iVehicleCategoryIdArr = [];
        var iVehicleCategoryIdRemoveArr = [];

        var iVehicleCategoryIdMoreArr = [];
        var iVehicleCategoryIdMoreRemoveArr = [];

        var iDisplayOrderArr = [];

        for (var i = 0; i < $('input[name="iVehicleCategoryId[]"]').length; i++) {
            if ($('input[name="iVehicleCategoryId[]"]').eq(i).is(":checked")) {
                iVehicleCategoryIdArr.push($('input[name="iVehicleCategoryId[]"]').eq(i).val());
            } else {
                iVehicleCategoryIdRemoveArr.push($('input[name="iVehicleCategoryId[]"]').eq(i).val());  
            }
        }

        for (var i = 0; i < $('input[name="iVehicleCategoryIdMore[]"]').length; i++) {
            if ($('input[name="iVehicleCategoryIdMore[]"]').eq(i).is(":checked")) {
                iVehicleCategoryIdMoreArr.push($('input[name="iVehicleCategoryIdMore[]"]').eq(i).val());
            } else {
                iVehicleCategoryIdMoreRemoveArr.push($('input[name="iVehicleCategoryIdMore[]"]').eq(i).val());
            }
        }

        for (var i = 0; i < $('select[name="ms_display_order[]"]').length; i++) {
            iDisplayOrderArr.push($('select[name="ms_display_order[]"]').eq(i).val());
        }


        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] . 'category_content_page.php' ?>',
            'AJAX_DATA': {
                Type: 'Save',
                iVehicleCategoryIdArr: iVehicleCategoryIdArr.toString(),
                iVehicleCategoryIdRemoveArr: iVehicleCategoryIdRemoveArr.toString(),
                iVehicleCategoryIdMoreArr: iVehicleCategoryIdMoreArr.toString(),
                iVehicleCategoryIdMoreRemoveArr: iVehicleCategoryIdMoreRemoveArr.toString(),
                iDisplayOrderArr: iDisplayOrderArr.toString(),
                id: $('input[name="id"]').val(),
            },
        };
        getDataFromAjaxCall(ajaxData, function (response) {


            if (response.action == "1") {

                if(display_order == 1)
                {
                    getServices('allservices')

                }else{
                    location.reload();
                }
            } else {
                $("#loaderIcon").hide();
            }
        });
    }


    function language_wise_page(sel) {
        $("#loaderIcon").show();
        var url = window.location.href;
        url = new URL(url);
        url.searchParams.set("id", sel.value);
        window.location.href = url.href;
    }



    /*------------------ordering-----------------*/

    var prevValue = 0 ;
    function capturePrevValue(elem) {
        prevValue = parseInt($(elem).val().split('-')[1]);
    }


    function checkAndUpdateSelection(changedDropdown, prevValue, callback)
    {
        var $dropdowns = $('.form-control');
        var temp_order = parseInt($(changedDropdown).val().split('-')[1]);
        var TOTAL_SELECT =  $dropdowns.length;


        var displayOrders = [];
        if (temp_order > prevValue) {
            for (var DI = prevValue; DI <= temp_order; DI++) {
                displayOrders.push(DI);
            }
        } else {
            for (var DI = temp_order; DI <= prevValue; DI++) {
                displayOrders.push(DI);
            }
        }
        $dropdowns.each(function() {
            var $this = $(this);
            var order = parseInt($this.val().split('-')[1]);

            if ($this[0] !== changedDropdown && displayOrders.includes(order)) {
                var newOrder = temp_order > prevValue ? order - 1 : order + 1;
                var newValue = $this.val().split('-')[0] + '-' + newOrder;
                $this.val(newValue);
            }
        });

      //  callback(1);
    }




    function change_category_order(elem,prevValue1)
    {
        checkAndUpdateSelection(elem,prevValue,saveServices);
        $(elem).blur();
        //saveServices(1);
    }

    /*------------------ordering-----------------*/


</script>
</body>
<!-- END BODY-->
</html>