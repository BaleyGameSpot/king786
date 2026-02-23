<?php

include_once('../common.php');



if (!$userObj->hasPermission('view-providers-bidding-requests')) {

    $userObj->redirect();

}

$script = 'biddingDriverRequest';



$sql = "SELECT rd.vName,rd.vLastName,rd.vCode,rd.vEmail,rd.vPhone, bdr.iDriverId FROM register_driver AS rd JOIN bidding_driver_request AS bdr ON bdr.iDriverId = rd.iDriverId GROUP BY bdr.iDriverId";



$Requests = $obj->MySQLSelect($sql);

?>

<!DOCTYPE html>

<html lang="en">

    <!-- BEGIN HEAD-->

    <head>

        <meta charset="UTF-8" />

        <title><?= $SITE_NAME ?> | <?= $langage_lbl_admin['LBL_DRIVER_SERVICE_MODIFICATION_REQUESTS_TXT']; ?> </title>

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

                                <h2><?= $langage_lbl_admin['LBL_DRIVER_SERVICE_MODIFICATION_REQUESTS_TXT'] . " ( Bidding Services)" ?></h2>

                                <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->

                            </div>

                        </div>

                        <hr />

                    </div>



                    <div class="table-list">

                        <div class="row">

                            <div class="col-lg-12">

                                <div class="admin-nir-export">



                                    <?php if (!empty($data_drv)) { ?>

                                        <div class="panel-heading">

                                            <form name="_export_form" id="_export_form" method="post" >

                                                <button type="button" onclick="showExportTypes('coupon')" >Export</button>

                                            </form>

                                        </div>

                                    <?php } ?>

                                </div>

                                <div style="clear:both;"></div>

                                <div class="table-responsive">

                                    <form class="_list_form" id="_list_form" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">

                                        <table class="table table-striped table-bordered table-hover">

                                            <thead>

                                                <tr>

                                                    <th class="align-center">#</th>

                                                    <th>Name</th>

                                                    <th>Email</th>

                                                    <th>Phone</th>

                                                    <th class="text-center">View Requests</th>

                                                </tr>

                                            </thead>

                                            <tbody>

                                                <?php

                                                    if(scount($Requests) == 0){

                                                        echo '<tr>';

                                                            echo '<td colspan="5" class="text-center">'.$langage_lbl['LBL_SERVICE_REQ_NOT_FOUND'].'</td>';

                                                        echo '<tr>';

                                                    }else{

                                                        foreach ($Requests as $key => $Request) {

                                                            echo '<tr>';

                                                                echo '<td class="text-center">'.($key + 1).'</td>';

                                                           

															if ($userObj->hasPermission('view-providers')) {

																echo '<td><a href="javascript:void(0);" onClick="show_driver_details('.$Request['iDriverId'].')" style="text-decoration: underline;">'.clearName($Request['vName']." ".$Request['vLastName']).'</a></td>';

															} else {

																echo '<td>'.clearName($Request['vName']." ".$Request['vLastName']).'</td>';

															}

															//  echo '<td>'.clearName($Request['vName'].' '.$Request['vLastName']).'</td>';

                                                                if($Request['vEmail'] != ''){

                                                                    echo '<td>'.clearEmail(" " . $Request['vEmail']).'</td>';

                                                                } else {

                                                                    echo '<td>--</td>';

                                                                }

                                                                echo '<td>(+'.$Request['vCode'].') '.clearPhone(" " . $Request['vPhone']).'</td>';

                                                                echo '<td class="text-center"><a href="action_bidding_driver_request.php?did='.base64_encode(base64_encode($Request['iDriverId'])).'" class="btn btn-primary">View Requested Services </a></td>';

                                                            echo '<tr>';

                                                        }

                                                    }

													

                                                ?>

                                            </tbody>

											

                                        </table>

                                    </form>

                                </div>

                            </div> <!--TABLE-END-->

                        </div>

                    </div>

                    <div class="admin-notes">

                        <h4>Notes:</h4>

                        <ul>

                            <li> This module will list all the providers who has requested the admin to approve new services.</li>

                            <li> Admin can click on "View Requested Services" button to see the details of requested services by the providers.</li>

                            <li> Admin will see the list of all the providers whose request is in pending state.</li>

                            

                        </ul>

                    </div>

                </div>

            </div>

            <!--END PAGE CONTENT -->

        </div>

        <!--END MAIN WRAPPER -->

        <?php

        include_once('footer.php');

        ?>

    <!-- END BODY-->

</html>



        <div  class="modal fade" id="detail_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >

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

                        <div id="imageIcons" style="display:none">

                            <div align="center">                                                                       

                                <img src="../default.gif"><br/>                                                            

                                <span>Retrieving details,please Wait...</span>                       

                            </div>    

                        </div>

                        <div id="driver_detail"></div>

                    </div>

                </div>

            </div>



        </div>

		 



        <script>        

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

                    getDataFromAjaxCall(ajaxData, function(response) {

                        if(response.action == "1") {

                            var data = response.result;

                            $("#driver_detail").html(data);

                            $("#imageIcons").hide();  

                        }

                        else {

                            console.log(response.result);

                            $("#imageIcons").hide();  

                        }

                    });

                }

            }

        </script>        

			