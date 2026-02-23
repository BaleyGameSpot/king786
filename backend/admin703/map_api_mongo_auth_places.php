<?php
include_once '../common.php';

if(strtoupper(ENABLE_MAP_API_SETTING) != "YES") {
    header("Location:" . $tconfig["tsite_url_main_admin"]);
    exit;
}

if (!$userObj->hasPermission('view-map-api-service-account')) {
    $userObj->redirect();
}
if (!$MODULES_OBJ->mapAPIreplacementAvailable()) {
    header("Location:" . $tconfig["tsite_url_main_admin"]);
    exit;
}

if (isset($_POST['submit'])) {
    $action = $_POST['submit'];

    $vTitle = isset($_POST['vTitle']) ? $_POST['vTitle'] : '';
    $vServiceId = isset($_POST['vServiceAccountId']) ? $_POST['vServiceAccountId'] : '';
    $vAuthKey = isset($_POST['vAuthKey']) ? $_POST['vAuthKey'] : '';
    $vUsageOrder = isset($_POST['vUsageOrder']) ? $_POST['vUsageOrder'] : '';
    $eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
    $api_key_id = isset($_POST['api_key_id']) ? $_POST['api_key_id'] : '';

    if (SITE_TYPE == 'Demo') {
        $_SESSION['success'] = 2;
        header("Location:map_api_mongo_auth_places.php?success=2");
        exit;
    }

    if ($action == 'Add' && !$userObj->hasPermission('create-map-api-service-account')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to add API Key';
        header("Location:map_api_mongo_auth_places.php");
        exit;
    }

    if ($action == 'Update' && !$userObj->hasPermission('edit-map-api-service-account')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update API Key';
        header("Location:map_api_mongo_auth_places.php");
        exit;
    }

    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $uniqueFieldName = '_id';
    $uniqueFieldValue = trim($api_key_id);
    $tempData = [];
    $tempData["auth_key"] = $vAuthKey;
    $tempData["eDefault"] = "No";
    if($eStatus == "Inactive") {
        $tempData["auth_key_inactive"] = $vAuthKey;
        $tempData["auth_key"] = $GOOGLE_SEVER_GCM_API_KEY;
    } else {
        $tempData["auth_key_inactive"] = "";
    }

    $data_drv = $obj->updateRecordsToMongoDBWithDBNameById($DbName, $TableName, $uniqueFieldName, $uniqueFieldValue, $tempData);

    $DbName = TSITE_DB;
    $TableName = "auth_accounts_places";
    $searchQuery = [];
    if ($api_key_id != '') {
        $searchQuery['_id'] = new MongoDB\BSON\ObjectID($api_key_id);
    }

    $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }

    header("Location:map_api_mongo_auth_places.php?id=" . $vServiceId);
    exit;
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;

$script = 'map_api_setting';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';

// Start Sorting - Initialize $orderByField
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$orderByField = '';

if ($sortby == '4') {
    if ($order == 0) {
        $orderByField = ['vUsageOrder' => intVal('-1')];
    } else {
        $orderByField = ['vUsageOrder' => intVal('1')];
    }
}
if ($sortby == '3') {
    if ($order == 0) {
        $orderByField = ['eStatus' => intVal('-1')];
    } else {
        $orderByField = ['eStatus' => intVal('1')];
    }
}
// End Sorting

// Start Search Parameters
$id = isset($_REQUEST['id']) ? stripslashes($_REQUEST['id']) : "";
$sid = isset($_REQUEST['sid']) ? stripslashes($_REQUEST['sid']) : "";
$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$EntityType_option = isset($_REQUEST['EntityType_option']) ? $_REQUEST['EntityType_option'] : "";
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
$ssql = '';
$cmp_name = "";
// End Search Parameters

$ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";

// Pagination variables
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$per_page = isset($DISPLAY_RECORD_NUMBER) ? $DISPLAY_RECORD_NUMBER : 10;
$tpages = 1;

if ($eStatus != '') {
    $eStatussql = "";
} else {
    $eStatussql = " AND rd.eStatus != 'Deleted'";
}

// for MongoDB
$DbName = TSITE_DB;
$TableName = "auth_accounts_places";
$searchQuery = [];

if ($id != '' || $eStatus != '' || $keyword != '' || $EntityType_option != '') {
    if ($id != '') {
        $searchQuery['vServiceId'] = intVal($id);
    }
    if ($keyword != '') {
        $searchQuery['auth_key'] = $keyword;
    }
    if ($eStatus != '') {
        $searchQuery['eStatus'] = $eStatus;
    }

    if ($orderByField != '') {
        $data_drv = $obj->fetchAllRecordsFromMongoDBWithSortParams($DbName, $TableName, $searchQuery, $orderByField);
    } else {
        $data_drv = $obj->fetchAllRecordsFromMongoDBWithDBName($DbName, $TableName, $searchQuery);
    }
} else {
    $data_drv = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName);
}

// FIXED: Safe array handling
if (!is_array($data_drv)) {
    $data_drv = [];
}

// FIXED: Properly extract vUsageOrder values - handle both array and scalar values
$max_usage_order = 1;
if (!empty($data_drv)) {
    $vUsageOrderArr = [];
    foreach ($data_drv as $item) {
        if (isset($item['vUsageOrder'])) {
            // Handle if vUsageOrder is an array or scalar
            $val = $item['vUsageOrder'];
            if (is_array($val)) {
                $val = isset($val[0]) ? $val[0] : 0;
            }
            $vUsageOrderArr[] = intval($val);
        }
    }
    if (!empty($vUsageOrderArr)) {
        rsort($vUsageOrderArr);
        $max_usage_order = $vUsageOrderArr[0] + 1;
    }
}

/* Added by HV on 13-05-2021 To restrict addition of accounts if GOOGLE_PLAN_ACCOUNTS_LIMIT reached */
$total_accounts = scount($data_drv);
$lAddOnConfiguration = [];
if (isset($SETUP_INFO_DATA_ARR[0]['lAddOnConfiguration'])) {
    $lAddOnConfiguration = json_decode($SETUP_INFO_DATA_ARR[0]['lAddOnConfiguration'], true);
}
$restrict_account_add = "No";
if(isset($lAddOnConfiguration['GOOGLE_PLAN']) && in_array($lAddOnConfiguration['GOOGLE_PLAN'], [1,2]) && $total_accounts == GOOGLE_PLAN_ACCOUNTS_LIMIT) 
{
    $restrict_account_add = "Yes";
}
/* Added by HV on 13-05-2021 To restrict addition of accounts if GOOGLE_PLAN_ACCOUNTS_LIMIT reached End */

$active_accounts_temp = $data_drv;
if (!empty($active_accounts_temp)) {
    array_multisort(array_map(function($element) {
        $val = isset($element['vUsageOrder']) ? $element['vUsageOrder'] : 0;
        // Handle if vUsageOrder is an array
        if (is_array($val)) {
            $val = isset($val[0]) ? $val[0] : 0;
        }
        return intval($val);
    }, $active_accounts_temp), SORT_ASC, $active_accounts_temp);
}

$active_accounts = array();
foreach ($active_accounts_temp as $k => $data_acc) {
    if(isset($data_acc['eStatus']) && $data_acc['eStatus'] == "Active") {
        $active_accounts[$k] = $data_acc;
    }
}

$total_active_accounts = scount($active_accounts);
$days_per_account_arr_new = array();

if($total_active_accounts > 0) {
    $total_days = date('t');
    $month = date('M');
    
    $days_per_account = floor($total_days / $total_active_accounts);
    
    $days_arr = array();
    for($i = 1; $i <= $total_days; $i++) {
        $days_arr[] = addOrdinalNumberSuffix($i);
    }
    
    $days_per_account_arr = partition($days_arr, $total_active_accounts);
    $c = 0;
    foreach ($active_accounts as $key1 => $v) {
        if (isset($days_per_account_arr[$c])) {
            $days_per_account_arr_new[$key1]['days'] = $days_per_account_arr[$c];
        }
        $c++;
    }
}

$DbNameTitle = TSITE_DB;
$TableNameMaster = "auth_master_accounts_places";
$searchQueryServiceID = [];
$searchQueryServiceID['vServiceId'] = $id;
$data_Service_names = $obj->fetchAllRecordsFromMongoDBWithDBName($DbNameTitle, $TableNameMaster, $searchQueryServiceID);

if (isset($data_Service_names[0]['vServiceName']) && $data_Service_names[0]['vServiceName'] != '') {
    $Servicetitle = $data_Service_names[0]['vServiceName'];
} else {
    $Servicetitle = isset($langage_lbl_admin['LBL_MAP_API_AUTH_MASTER_ACCOUNT_PLACES']) ? $langage_lbl_admin['LBL_MAP_API_AUTH_MASTER_ACCOUNT_PLACES'] : 'API Account';
    if (isset($company_name)) {
        $Servicetitle .= ' ' . $company_name;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?php echo isset($SITE_NAME) ? $SITE_NAME : 'Admin'; ?> | API Keys (<?php echo $Servicetitle; ?>)</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <?php include_once 'global_files.php';?>
        <style type="text/css">
            .base-api-key {
                font-size: 18px;
                padding: 5px 10px;
                background-color: #eeeeee;
                border: 1px solid #cccccc;
                border-radius: 5px;
            }
        </style>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
        <!-- Main Loading -->
        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once 'header.php';?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2>Base API Key (Primary Key)</h2>
                        </div>
                    </div>
                    <hr />
                    <div class="base-api-key"><?php echo !empty($GOOGLE_SEVER_GCM_API_KEY) ? $GOOGLE_SEVER_GCM_API_KEY : "Api Key Not Configured."; ?></div>
                </div>

                <div class="inner" style="margin-top: 0;">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2 style="color: #ff0000">Round Accounts Algorithm - Important Notice</h2>
                        </div>
                    </div>
                    <hr />
                    <div class="base-api-key" style="font-size: 15px;">
                        <p>With our "Round Accounts" Algorithm that will get you upto 2000 USD FREE Credits per month from Google.</p>
                        <p>Google gives 1st 200 USD API calls for free every month on each Account.<br>
                        This Feature will allow you to use 10 Google Accounts in sequence. Each Account gets you initial 200 USD free credit so you get a total of 2000 USD free units from Google every month. Thus you save a good amount of money using this algorithm.</p>
                        <br>
                        <p style="color: #ff0000"><strong>Warning: </strong></p>
                        
                        <p style="color: #ff0000">Currently hundreds of our client are using this algorithm in their Apps and Google has No Objection to this Flow.<br>
                        In future Google's policy may change. And they will be able to Detect that you are using this Trick to save Money. And then can take any or all of below listed actions and penalize you:</p>

                        <ul style="list-style: disc !important; color: #ff0000;">
                            <li>They may Stop 200 USD Credit.</li>
                            <li>They may Penalize you.</li>
                            <li>They may Ban all your Extra API Accounts.</li>
                            <li>They may Delete/Remove your Apps from Google Stores.</li>
                            <li>They may Terminate your Account.</li>
                        </ul>
                        <br>
                        <p style="color: #ff0000">So please use this Algorithm at your own Risk.</p>
                    </div>
                </div>

                <div class="inner" style="margin-top: 0;">
                    <div id="add-hide-show-div">
                        <div class="row">
                            <div class="col-lg-12">
                                <span style="float: left;">
                                    <h2 style="float: none;"><?php echo $Servicetitle; ?> API Keys </h2>
                                </span>
                                <?php if ($userObj->hasPermission('create-map-api-service-account') && $restrict_account_add == "No") { ?>
                                <?php } ?>
                            </div>
                        </div>
                        <hr />
                    </div>
                    <?php include 'valid_msg.php';?>

                    <div class="table-list">
                        <div class="row">
                            <div class="col-lg-12">
                                <div style="clear:both;"></div>
                                <div class="table-responsive">
                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                                        <table class="table table-striped table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th width="5%" class="align-center">Sr No. </th>
                                                    <th width="13%" class="align-center">Duration in Month</th>
                                                    <th width="13%" class="align-center">API Key </th>
                                                    <th width="8%" class="align-center">Set API Keys</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    if (!empty($data_drv)) {
                                                        for ($i = 0; $i < scount($data_drv); $i++) {
                                                            $range = "";
                                                            if(isset($data_drv[$i]['eStatus']) && $data_drv[$i]['eStatus'] == "Active") {
                                                                if (isset($days_per_account_arr_new[$i]['days']) && !empty($days_per_account_arr_new[$i]['days'])) {
                                                                    $days_count = scount($days_per_account_arr_new[$i]['days']);
                                                                    $range = "<br>(Key to be used from " . $days_per_account_arr_new[$i]['days'][0] . ' - ' . $days_per_account_arr_new[$i]['days'][$days_count - 1] . ")";
                                                                }
                                                            }

                                                            $api_key = isset($data_drv[$i]['auth_key']) ? $data_drv[$i]['auth_key'] : '';
                                                            $api_key_status = isset($data_drv[$i]['eStatus']) ? $data_drv[$i]['eStatus'] : 'Active';
                                                            if(!empty($data_drv[$i]['auth_key_inactive'])) {
                                                                $api_key = $data_drv[$i]['auth_key_inactive'];
                                                                $api_key_status = "Inactive";
                                                            }
                                                ?>
                                                <tr class="gradeA">
                                                    <td align="center"><?php echo $i+1; ?></td>
                                                    <td style="word-break: break-all;" class="align-center">Key <?php echo $i+1; ?><?php echo $range; ?></td>
                                                    <td style="word-break: break-all;" class="align-center">
                                                        <?php echo $api_key; ?>
                                                    </td>
                                                    <td align="center" class="action-btn001">
                                                        <button type="button" class="btn btn-info" onclick="addApiKey(this)" 
                                                            data-id="<?php echo isset($data_drv[$i]['_id']['$oid']) ? $data_drv[$i]['_id']['$oid'] : ''; ?>" 
                                                            data-sid="<?php echo isset($data_drv[$i]['vServiceId']) ? $data_drv[$i]['vServiceId'] : ''; ?>" 
                                                            data-authkey="<?php echo $api_key; ?>" 
                                                            data-apikeytitle="<?php echo isset($data_drv[$i]['vTitle']) ? $data_drv[$i]['vTitle'] : ''; ?>" 
                                                            data-order="<?php echo isset($data_drv[$i]['vUsageOrder']) ? (is_array($data_drv[$i]['vUsageOrder']) ? $data_drv[$i]['vUsageOrder'][0] : $data_drv[$i]['vUsageOrder']) : ''; ?>" 
                                                            data-status="<?php echo $api_key_status; ?>" 
                                                            data-defaultkey="<?php echo isset($data_drv[$i]['eDefault']) ? $data_drv[$i]['eDefault'] : 'No'; ?>">Add New / Update</button>
                                                    </td>
                                                </tr>
                                                <?php
                                                    }
                                                    } else {
                                                        ?>
                                                <tr class="gradeA">
                                                    <td colspan="5"> No Records Found.</td>
                                                </tr>
                                                <?php }?>
                                            </tbody>
                                        </table>
                                    </form>
                                    <?php include 'pagination_n.php';?>
                                </div>
                            </div>
                            <!--TABLE-END-->
                        </div>
                    </div>
                    <div class="admin-notes">
                        <h4>Notes:</h4>
                        <ul>
                            <li>If you don't set any new API Keys for any slot, Primary Key will be Used. Please click on "Add New/ Update" button to add your new API Keys or for particular slot. </li>
                            <li>If you keep Keys as Inactive, Primary Account Key will be Used. Please Activate Keys for particular slot to be used.</li>
                            <li>All API Keys and Accounts that you ever use in the System must always be Paid. Never leave them unpaid. Doing so, your Store Account can get Terminated by Google Services.</li>
                            <li>All active API Keys must be working.</li>
                            <li>All active API Keys must have enabled billing account.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <div class="row loding-action" id="loaderIcon" style="display:none;">
            <div align="center">
                <img src="default.gif">
            </div>
        </div>
        <!--END MAIN WRAPPER -->
        <form name="pageForm" id="pageForm" action="action/map_api_mongo_auth_places.php" method="post" >
            <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" >
            <input type="hidden" name="page" id="page" value="<?php echo $page; ?>">
            <input type="hidden" name="tpages" id="tpages" value="<?php echo $tpages; ?>">
            <input type="hidden" name="iOid" id="iMainId01" value="" >
            <input type="hidden" name="eStatus" id="eStatus" value="<?php echo $eStatus; ?>" >
            <input type="hidden" name="status" id="status01" value="" >
            <input type="hidden" name="statusVal" id="statusVal" value="" >
            <input type="hidden" name="option" value="<?php echo $option; ?>" >
            <input type="hidden" name="keyword" value="<?php echo $keyword; ?>" >
            <input type="hidden" name="sortby" id="sortby" value="<?php echo $sortby; ?>" >
            <input type="hidden" name="order" id="order" value="<?php echo $order; ?>" >
            <input type="hidden" name="method" id="method" value="" >
        </form>

        <div class="modal fade" id="add_api_key_modal" tabindex="-1" role="dialog" aria-hidden="true" >
            <div class="modal-dialog" >
                <div class="modal-content">
                    <div class="modal-header">
                        <h4><span id="api_key_action">Add</span> API Key
                            <button type="button" class="close" data-dismiss="modal">x</button>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <form id="_authmongoplaces_form" name="_authmongoplaces_form" method="post" action="">
                                <input type="hidden" name="vServiceAccountId" id="vServiceAccountId" value="<?php echo $id; ?>"/>
                                <input type="hidden" name="api_key_id" id="api_key_id" value=""/>
                                <input type="hidden" class="form-control" name="vTitle" id="vTitle" value="">
                                <input type="hidden" class="form-control" name="vUsageOrder" id="vUsageOrder" value="">

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>API Key<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <input type="text" class="form-control" name="vAuthKey"  id="vAuthKey" value="" placeholder="API Key" >
                                        <label id="vAuthKey-error" style="color:#b94a48;"></label>
                                    </div>
                                </div>

                                <div class="row" style="display: none;">
                                    <div class="col-lg-12">
                                        <label>Status <span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <select class="form-control" name="eStatus" id="eStatus" >
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">                                        
                                        <input type="submit" class="btn btn-info" name="submit" id="submit" value="Add" style="margin-right: 10px;">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once 'footer.php';?>
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

            var valid = 0;

            var validator = $('#_authmongoplaces_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                onkeyup: false,
                onclick: false,
                onfocusout: false,
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                     hideLoader();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vAuthKey: {
                        required: true,
                        noSpace: true,
                        async: false,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_auth_key.php',
                            type: "post",
                            data: {
                                vAuthKey: function () {
                                    return $("#vAuthKey").val();
                                }, vServiceAccountId: function () {
                                    return $("#vServiceAccountId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                hideLoader();
                                responseArr = JSON.parse(response);
                                if (responseArr.Action == "1") {
                                    $('#vAuthKey-error').html('');
                                    return true;
                                } else {
                                    hideLoader();
                                    return "\"" + responseArr.message + "\"";
                                }
                            },

                        }
                    },
                    eStatus: { required: true }
                },
                messages: {
                    vAuthKey: {
                        required: 'This field is required.',
                        noSpace: 'Auth key should not contain whitespace.',
                        remote: jQuery.validator.format('{0}')
                    },
                    eStatus: {
                        required: 'This field is required.'
                    }
                },
                submitHandler : function(form) {
                    $('#loaderIcon').hide();
                    if ($(form).valid()){
                        valid = 1;
                        submitform();
                        return true;
                    }else{
                        valid = 0;
                    };
                    hideLoader();
                    return false;
                }
            });

            $('#submit').click(function() {
                $('#loaderIcon').show();
                if (valid == 0) {
                    validator.resetForm();
                }
                $("label.error, #vAuthKey-error").hide();
                $(".error").removeClass("error");
                $(".has-error").removeClass("has-error");
            });

            function submitform(){
                $('#submit').click();
            }

            function addApiKey(elem) {
                editApiKey(elem, 'Add');
            }

            function editApiKey(elem, action = "Edit") {
                validator.resetForm();

                var vTitle = $(elem).data('apikeytitle');
                var vAuthKey = $(elem).data('authkey');
                var vUsageOrder = $(elem).data('order');
                var eStatus = $(elem).data('status');
                var vServiceId = $(elem).data('sid');
                var id = $(elem).data('id');
                var eDefault = $(elem).data('defaultkey');

                $('#api_key_action').text(action);
                $('#vTitle').val(vTitle);
                if(eDefault == "Yes") {
                    $('#vAuthKey').val("");    
                } else {
                    $('#vAuthKey').val(vAuthKey);
                }
                $('#vUsageOrder').val(vUsageOrder);
                $('#eStatus option[value="' + eStatus + '"]').prop('selected', true);
                $('#vServiceAccountId').val(vServiceId);
                $('#api_key_id').val(id);
                if(action == "Add") {
                    $('#submit').val("Add");    
                } else {
                    $('#submit').val("Update");
                }                

                $('#add_api_key_modal').modal('show');
            }
        </script>
    </body>
    <!-- END BODY-->
</html>