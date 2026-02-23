<?php

include_once('../common.php');

$exDocConfig = true;

if (!$userObj->hasPermission('expired-documents') || $exDocConfig != true) {

    $userObj->redirect();

}

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'Expired Documents';

//Start Sorting
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$ord = ' ORDER BY dm.doc_usertype ASC';

if ($sortby == 1) {

    if ($order == 0)

        $ord = " ORDER BY dm.doc_usertype ASC";

    else

        $ord = " ORDER BY dm.doc_usertype DESC";

}

if ($sortby == 2) {

    if ($order == 0)

        $ord = " ORDER BY dm.country ASC";

    else

        $ord = " ORDER BY dm.country DESC";

}

if ($sortby == 3) {

    if ($order == 0)

        $ord = " ORDER BY dm.doc_name_" . $default_lang . " ASC";

    else

        $ord = " ORDER BY dm.doc_name_" . $default_lang . " DESC";

}

if ($sortby == 4) {

    if ($order == 0)

        $ord = " ORDER BY dl.ex_date ASC";

    else
        $ord = " ORDER BY dl.ex_date DESC";

}

if ($sortby == 5) {

    if ($order == 0)

        $ord = " ORDER BY doc_username ASC";

    else

        $ord = " ORDER BY doc_username DESC";

}

if ($sortby == 6) {

    if ($order == 0)

        $ord = " ORDER BY doc_useremail ASC";

    else

        $ord = " ORDER BY doc_useremail DESC";

}

if ($sortby == 7) {

    if ($order == 0)

        $ord = " ORDER BY doc_userphone ASC";

    else

        $ord = " ORDER BY doc_userphone DESC";

}

// Start Search Parameters

$option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";

$keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

$searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

$ssql = '';

$ssql1 = '';

if ($keyword != '') {

    if ($option != '') {

        if (strpos($option, 'ex_date') !== false) {

            $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";

        }else if (strpos($option, 'doc_username') !== false) {

            $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

        }else if (strpos($option, 'doc_useremail') !== false) {

            $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

        }else if (strpos($option, 'doc_userphone') !== false) {

            $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

        } else {

            $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";

        }

    } else {

		$ssql1 .= " AND (doc_username LIKE '%" . $keyword . "%' OR doc_useremail LIKE '%" . $keyword . "%' OR doc_userphone LIKE '%" . $keyword . "%' OR doc_usertype LIKE '%" . $keyword . "%' OR country LIKE '%" . $keyword . "%' OR doc_name_" . $default_lang . " LIKE '%" . $keyword . "%' OR ex_date LIKE '" . $keyword . "')";

    }

}

// End Search Parameters

//Pagination Start

$per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page

$sql = "SELECT * from (SELECT COUNT(dm.doc_masterid) AS Total,dl.doc_masterid,dl.doc_userid,dm.doc_name_". $default_lang . ",dm.country,dm.doc_usertype,dl.ex_date,CASE dm.doc_usertype

    WHEN 'user' THEN (select CONCAT(vName,' ', vLastName) from register_user where iUserId = dl.doc_userid) END AS doc_username, CASE dm.doc_usertype

    WHEN 'user' THEN ''

    END AS vehicle,

    CASE dm.doc_usertype

    WHEN 'user' THEN (select vEmail from register_user where iUserId = dl.doc_userid)

    END AS doc_useremail,

    CASE dm.doc_usertype

    WHEN 'user' THEN (select vEmail from register_user where iUserId = dl.doc_userid)

    END AS doc_userphone

    FROM ride_share_document_list dl INNER JOIN document_master dm ON dl.doc_masterid = dm.doc_masterid  where dm.ex_status = 'yes' AND  dl.ex_date!='0000-00-00' AND dl.ex_date < CURDATE() AND dm.status !='Deleted' $ssql $ord) AS documentlist WHERE doc_username IS NOT NULL $ssql1";

$totalData = $obj->MySQLSelect($sql);

$total_results = $totalData[0]['Total'];

$total_pages = ceil($total_results / $per_page); //total pages we going to have

$show_page = 1;



//-------------if page is setcheck------------------//

if (isset($_GET['page'])) {

    $show_page = $_GET['page'];             //it will telles the current page

    if ($show_page > 0 && $show_page <= $total_pages) {

        $start = ($show_page - 1) * $per_page;

        $end = $start + $per_page;

    } else {

        // error - show first set of results

        $start = 0;

        $end = $per_page;

    }

} else {

    // if page isn't set, show first set of results

    $start = 0;

    $end = $per_page;

}

// display pagination

$page = isset($_GET['page']) ? intval($_GET['page']) : 0;

$tpages = $total_pages;

if ($page <= 0)

    $page = 1;

//Pagination End


$sql = "SELECT * from (SELECT dl.doc_masterid,dl.doc_userid,dm.doc_name_". $default_lang . ",dm.country,dm.doc_usertype,dl.ex_date,dl.req_date,CASE dm.doc_usertype

    WHEN 'user' THEN (select CONCAT(vName,' ', vLastName) from register_user where iUserId = dl.doc_userid)

    END AS doc_username,

    CASE dm.doc_usertype

    WHEN 'user' THEN ''

    END AS vehicle,

    CASE dm.doc_usertype

    WHEN 'user' THEN (select vEmail from register_user where iUserId = dl.doc_userid)

    END AS doc_useremail,

    CASE dm.doc_usertype

    WHEN 'user' THEN (select vEmail from register_user where iUserId = dl.doc_userid)

    END AS doc_userphone

    FROM ride_share_document_list dl INNER JOIN document_master dm ON dl.doc_masterid = dm.doc_masterid  where dm.ex_status = 'yes' AND dl.ex_date!='0000-00-00' AND dl.ex_date < CURDATE() AND dm.status !='Deleted'  $ssql $ord) AS documentlist WHERE doc_username IS NOT NULL $ssql1   LIMIT $start, $per_page";

$data_drv = $obj->MySQLSelect($sql);

$endRecord = scount($data_drv);

$var_filter = "";

foreach ($_REQUEST as $key => $val) {

    if ($key != "tpages" && $key != 'page')

        $var_filter .= "&$key=" . stripslashes($val);

}


$reload = $_SERVER['PHP_SELF'] . "?tpages=" . $tpages . $var_filter;

//$dDocStatus = $CONFIG_OBJ->getConfigurations("configurations", "SET_DRIVER_OFFLINE_AS_DOC_EXPIRED");

?>

<!DOCTYPE html>

<html lang="en">

<!-- BEGIN HEAD-->

<head>

    <meta charset="UTF-8" />

    <title><?= $SITE_NAME ?> | Expired Documents</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <?php include_once('global_files.php'); ?>

    <link href="../assets/css/jquery-ui.css" rel="stylesheet" />

    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />

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


                                <?php if($_REQUEST['success'] == '1' && $_REQUEST['page'] == ''){ ?>

                                    <div class="alert alert-success">Setting for Restrict Drivers Updted Successfully.</div>

                                    <br>

                                <? } ?>


                                <h2><?php echo $langage_lbl_admin['LBL_EXPIRED_DOCUMETS']; ?></h2>


                              <!--   <?php if(ENABLE_EXPIRE_DOCUMENT == "Yes"){ ?>

                                    <div class="panel-heading" style="text-align:right">

                                        <div class="col-lg-12">

                                        <label> Restrict Drivers to be online if one or more document is expired. </label>

                                            <div class="make-switch" data-on="success" data-off="warning">

                                                <input type="checkbox" name="dDocStatus" id="dDocStatus" <?= ($dDocStatus != '' && $dDocStatus == 'Yes') ? 'checked' : ''; ?>>

                                            </div>

                                            <div class="upResponce"></div>

                                        </div>

                                    </div>

                                <?php } ?> -->

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

                                    <td width="10%" class=" padding-right10"><select name="option" id="option" class="form-control">

                                            <option value="">All</option>

                                            <option  value="dm.doc_usertype" <?php if ($option == "dm.doc_usertype") {

												echo "selected";

											} ?> >Document For</option>

											<option  value="dm.country" <?php if ($option == "dm.country") {

												echo "selected";

											} ?> >Country</option>

					                         <option value="<?= 'dm.doc_name_' . $default_lang ?>" <?php if ($option == "dm.doc_name_".$default_lang) {

												echo "selected";

											} ?> >Document Name</option>

											<option  value="dl.ex_date" <?php if ($option == "dl.ex_date") {

													echo "selected";

												} ?> >Expire Date</option>

											<option  value="doc_username" <?php if ($option == "doc_username") {

													echo "selected";

												} ?> >Document User Name</option>

											<option  value="doc_useremail" <?php if ($option == "doc_useremail") {

													echo "selected";

												} ?> >Email</option>

											<option  value="doc_userphone" <?php if ($option == "doc_userphone") {

													echo "selected";

												} ?> >Phone</option>


                                        </select>

                                    </td>

                                    <td width="15%"><input type="Text" id="keyword" name="keyword" value="<?php echo $keyword; ?>"  class="form-control" /></td>

                                    <td width="12%">

                                        <input type="submit" value="Search" class="btnalt button11" id="Search" name="Search" title="Search" />

                                        <input type="button" value="Reset" class="btnalt button11" onClick="window.location.href = 'expired_documents.php'"/>

                                    </td>

                                    <td width="30%"><!--<a class="add-btn" href="page_action.php" style="text-align: center;">Add Pages</a>--></td>

                                </tr>

                            </tbody>

                        </table>



                    </form>

                    <div class="table-list">

                        <div class="row">

                            <div class="col-lg-12">


                                <div class="table-responsive">

                                    <form class="_list_form" id="_list_form" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">

                                        <table class="table table-striped table-bordered table-hover">

                                            <thead>

                                                <tr>

                                                    <th width="12%"><a href="javascript:void(0);" onClick="Redirect(1,<?php if ($sortby == '1') {

														echo $order;

													} else { ?>0<?php } ?>)">Document For <?php if ($sortby == 1) {

														if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>



													<th width="10%"><a href="javascript:void(0);" onClick="Redirect(2,<?php if ($sortby == '2') {

														echo $order;

													} else { ?>0<?php } ?>)">Country <?php if ($sortby == 2) {

													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

															} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>



													<th width="25%"><a href="javascript:void(0);" onClick="Redirect(3,<?php if ($sortby == '3') {

														echo $order;

													} else { ?>0<?php } ?>)">Document Name <?php if ($sortby == 3) {

													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

															} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>



													<th width="10%" style="text-align:center;"><a href="javascript:void(0);" onClick="Redirect(4,<?php if ($sortby == '4') {

														echo $order;

													} else { ?>0<?php } ?>)">Expire Date  <?php if ($sortby == 4) {

													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>



													<th width="15%"><a href="javascript:void(0);" onClick="Redirect(5,<?php if ($sortby == '5') {

														echo $order;

													} else { ?>0<?php } ?>)">Document User Name <?php if ($sortby == 5) {

													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>



													<th width="18%"><a href="javascript:void(0);" onClick="Redirect(6,<?php if ($sortby == '6') {

														echo $order;

													} else { ?>0<?php } ?>)">Email <?php if ($sortby == 6) {

													if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

													} else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>



                                                    <th width="15%"><a href="javascript:void(0);" onClick="Redirect(7,<?php if ($sortby == '7') {

														echo $order;

													} else { ?>0<?php } ?>)">Phone <?php if ($sortby == 7) {

                                                        if ($order == 0) { ?><i class="fa fa-sort-amount-asc" aria-hidden="true"></i> <?php } else { ?><i class="fa fa-sort-amount-desc" aria-hidden="true"></i><?php }

                                        } else { ?><i class="fa fa-sort" aria-hidden="true"></i> <?php } ?></a></th>


                                                </tr>

                                            </thead>

                                            <tbody>

												<?php

												if (!empty($data_drv)) {

													for ($i = 0; $i < scount($data_drv); $i++) {

														$file = '';

														$document_for = '';

														if($data_drv[$i]['doc_usertype'] == 'user'){

                                                            $file = 'user_document_action.php?id='.$data_drv[$i]['doc_userid'].'&action=edit&user_type=user';

                                                            $document_for ='User';

                                                        } 

                                                        ?>

															<tr class="gradeA">

                                                            <td><?= $document_for; ?></td>

                                                            <td><?= $data_drv[$i]['country']; ?></td>

															<td><?= $data_drv[$i]['doc_name_' . $default_lang]; ?></td>

															<td align="center"><?= DateTime($data_drv[$i]['ex_date']); ?></td>

															<td><?= clearName(" " . $data_drv[$i]['doc_username']); ?></td>

															<td><?= clearEmail(" " . $data_drv[$i]['doc_useremail']); ?></td>

															<td><?= clearPhone(" " . $data_drv[$i]['doc_userphone']); ?></td>

                                                        </tr>

												<?php }

											} else { ?>

                                                    <tr class="gradeA">

                                                        <td colspan="7"> No Records Found.</td>

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

                            <li> Expired Documents module will list all Expired Documents on this page. </li>

                            <li> Administrator can view Expired Documents. </li>

                        </ul>

                    </div>

                </div>

            </div>

            <!--END PAGE CONTENT -->

        </div>

        <!--END MAIN WRAPPER -->


<?php include_once('footer.php'); ?>

<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>

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



            $(document).on("change", "#dDocStatus",function (e) {


                var ckVal = $(this).is(':checked');


                var ajaxData = {

                    'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_expiry_doc_setting_change.php',

                    'AJAX_DATA': "ckVal=" + ckVal,

                };

                getDataFromAjaxCall(ajaxData, function(response) {

                    if(response.action == "1") {

                        var data = response.result;

                        $('.upResponce').text(data);

                        window.location = 'expired_documents.php?success=1';

                    }

                    else {

                       // console.log(response.result);

                    }

                });

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


        </script>

    </body>

    <!-- END BODY-->

</html>