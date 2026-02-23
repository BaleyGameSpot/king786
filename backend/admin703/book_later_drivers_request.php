<?php
include_once('../common.php');
if (!$userObj->hasPermission('view-ride-job-later-bookings')){
    $userObj->redirect();
}
$script = 'CabBooking';
$iCabBookingId = $_REQUEST['iCabBookingId'];
$Schedule_Ride_Data = $SCHEDULE_RIDE_OBJ->GetLaterBookingInfo($iCabBookingId);
$CAB_BOOKING_DETAILS = $Schedule_Ride_Data['CAB_BOOKING_DETAILS'];
$SCHEDULE_RIDE_DRIVER_REQUEST_ROW_TITLE = $Schedule_Ride_Data['SCHEDULE_RIDE_DRIVER_REQUEST_ROW_TITLE'];
$DRIVER_REQUEST_HISTORY = $Schedule_Ride_Data['DRIVER_REQUEST_HISTORY'];
$published_rides_data = [];
function isValidDate($date)
{
    list($year,$month,$day) = explode('-',$date);
    return checkdate($month,$day,$year);
}

$systemTimeZone = date_default_timezone_get();
$date_format_data_array = array(
    'langCode' => $default_lang,
    'DateFormatForWeb' => 1
);
$date_format_data_array['tdate'] = (!empty($CAB_BOOKING_DETAILS['vTimeZone']))  ? converToTz($CAB_BOOKING_DETAILS['dBooking_date'], $CAB_BOOKING_DETAILS['vTimeZone'], $systemTimeZone) : $CAB_BOOKING_DETAILS['dBooking_date'];
$get_dBooking_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
?>
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
    <title>Admin | Publish Ride</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="" name="keywords"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <?php include_once('global_files.php'); ?>
    <link rel="stylesheet" href="../assets/css/modal_alert.css"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
</head>
<style type="text/css">
    .tg {
        border-collapse: collapse;
        border-spacing: 0;
    }

    .tg td {
        font-family: Arial, sans-serif;
        font-size: 14px;
        padding: 10px 5px;
        border-style: solid;
        border-width: 1px;
        overflow: hidden;
        word-break: normal;
        border-color: black;
    }

    .tg th {
        font-family: Arial, sans-serif;
        font-size: 14px;
        font-weight: normal;
        padding: 10px 5px;
        border-style: solid;
        border-width: 1px;
        overflow: hidden;
        word-break: normal;
        border-color: black;
    }

    .tg .tg-0lax {
        text-align: left;
        vertical-align: top
    }
</style>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner" id="page_height" style="">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Booking Allotment History #<?php echo $CAB_BOOKING_DETAILS['vBookingNo']; ?>    </h2>
                    <input type="button" class="add-btn" value="Close" onClick="javascript:window.top.close();">
                    <div style="clear:both;"></div>
                </div>
            </div>
            <hr/>
            <?php if (isset($_REQUEST['success']) && $_REQUEST['success'] == 1){ ?>
                <div class="alert alert-success paddiing-10">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    Email has been sent successfully to the respective E-mail address.
                </div>
            <?php } ?>

            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <b>Your <?php echo $langage_lbl_admin['LBL_TRIP_TXT_ADMIN']; ?> </b>
                                <?php
                                if (($CAB_BOOKING_DETAILS['dBooking_date'] == "0000-00-00 00:00:00")){
                                    echo "Was Cancelled.";
                                } else {
                                   /* echo @date('h:i A', @strtotime($CAB_BOOKING_DETAILS['dBooking_date']));
                                    ?> on <?= @date('d M Y', @strtotime($CAB_BOOKING_DETAILS['dBooking_date']));*/
                                    echo $get_dBooking_date_format['tDisplayDateTime'];
                                }
                                ?>
                            </div>

                            <div class="panel-body rider-invoice-new">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4>Pick up Location </h4>
                                        <!--<div id="map-canvas" class="gmap3"
                                             style="width:100%;height:300px;margin-bottom:10px;"></div>-->
                                        <span class="location-from"><i class="icon-map-marker"></i>
                                                    <b>Pick-up Location <p><?=$CAB_BOOKING_DETAILS['vSourceAddresss'];?></p></b></span>

                                        <span class="location-to"><i
                                                    class="icon-map-marker"></i> <b> Drop-off Location <p><?=$CAB_BOOKING_DETAILS['tDestAddress'];?></p></b></span>

                                        <div class="rider-invoice-bottom">
                                            <div class="">
                                                <div class="row">
                                                    <div class="ride-member-section col-sm-6" style="word-wrap: break-word;">
                                                        <div class="left member_image_div">
                                                            <img src="<?php echo $CAB_BOOKING_DETAILS['user_vImgName']; ?>"
                                                                    style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                                    align="left" height="45" width="45" class="CToWUd">
                                                        </div>
                                                        <div class="right">
                                                            <div>
                                                                <b><?php echo clearName($langage_lbl_admin['LBL_USER']); ?></b>
                                                            </div>
                                                            <div><?php echo $CAB_BOOKING_DETAILS['user_vName']." ".$CAB_BOOKING_DETAILS['user_vLastName']; ?></div>
                                                            <div><?php echo clearEmail($CAB_BOOKING_DETAILS['user_vEmail']); ?></div>
                                                            <div>
                                                                +<?php echo $CAB_BOOKING_DETAILS['user_vPhoneCode']; ?>  <?php echo clearPhone($CAB_BOOKING_DETAILS['user_vPhone']); ?></div>
                                                        </div>
                                                    </div>

                                                    <?php if (isset($CAB_BOOKING_DETAILS['driver_vName']) && !empty($CAB_BOOKING_DETAILS['driver_vName'])){ ?>

                                                        <div class="ride-member-section col-sm-6" style="word-wrap: break-word;">
                                                            <div class="left member_image_div ">
                                                                <img src="<?php echo $CAB_BOOKING_DETAILS['driver_vImgName']; ?>"
                                                                        style="outline:none;text-decoration:none;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7"
                                                                        align="left" height="45" width="45" class="CToWUd">
                                                            </div>
                                                            <div class="right">
                                                                <div>
                                                                    <b><?php echo clearName($langage_lbl_admin['LBL_DRIVER']); ?></b>
                                                                </div>
                                                                <div><?php echo $CAB_BOOKING_DETAILS['driver_vName']." ".$CAB_BOOKING_DETAILS['driver_vLastName']; ?></div>
                                                                <div><?php echo clearEmail($CAB_BOOKING_DETAILS['driver_vEmail']); ?></div>
                                                                <div>
                                                                    +<?php echo $CAB_BOOKING_DETAILS['driver_vCode']; ?>  <?php echo
                                                                    clearPhone($CAB_BOOKING_DETAILS['driver_vPhone']); ?></div>
                                                            </div>
                                                        </div>

                                                    <?php } ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="clear"></div>

                                </div>

                                <div class="row">

                                    <div class="col-sm-12">

                                        <h4> List of Drivers Receiving Requests for Later Booking
                                            #<?php echo $CAB_BOOKING_DETAILS['vBookingNo']; ?></h4>
                                        <hr/>

                                        <?php
                                        if (isset($DRIVER_REQUEST_HISTORY) && !empty($DRIVER_REQUEST_HISTORY)){ ?>
                                            <table style="min-height: 260px;" class="table table-striped table-bordered table-hover">
                                                <tr>
                                                    <?php
                                                    ?>
                                                    <?php foreach ($SCHEDULE_RIDE_DRIVER_REQUEST_ROW_TITLE as $data){ ?>
                                                        <th style="<?php echo $data['style']; ?>" width="<?php echo $data['width']; ?>%"><?php echo $data['vTitle']; ?></th>
                                                    <?php } ?>
                                                </tr>

                                                <?php foreach ($DRIVER_REQUEST_HISTORY as $HISTORY){

                                                    ?>
                                                    <tr class="<?php echo $HISTORY['TR_CLASS']; ?>">
                                                        <?php foreach ($HISTORY['TR_DATA'] as $data){ ?>

                                                        <td style="<?php echo $data['style']; ?>">
                                                            <?php if ($data['type'] == "Image"){ ?>
                                                                <img src="<?php echo $tconfig['tsite_url'] ?>resizeImg.php?w=80&src=<?php echo $data['Value']; ?>">
                                                            <?php } else if ($data['type'] == "Date") {
                                                                $date_format_data_array['tdate'] = (!empty($CAB_BOOKING_DETAILS['vTimeZone']))  ? converToTz($data['Value'], $CAB_BOOKING_DETAILS['vTimeZone'], $systemTimeZone) : $data['Value'];
                                                                $get_Value_date_format = DateformatCls::getNewDateFormat($date_format_data_array); 
                                                                echo $get_Value_date_format['tDisplayDateTime'];
                                                                //DateTime($data['Value'], 25);
                                                             } else if ($data['type'] == "Name"){ ?>

                                                                        <a href="javascript:void(0);" onClick="show_driver_details('<?=$data['iDriverId'];?>')" style="text-decoration: underline;">
                                                                            <?php echo $data['Value']; ?>
                                                                        </a>
                                                            <?php } else { 
                                                                echo $data['Value']; 
                                                            } ?>
                                                        </td>
                                                        <?php } ?>
                                                    </tr>
                                                <?php } ?>
                                            </table>
                                        <?php } ?>
                                    </div>
                                    <div class="clear"></div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->

<div class="modal fade " id="detail_modal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i style="margin:2px 5px 0 2px;"><img src="images/rider-icon.png" alt=""></i>Driver Details
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            <div class="modal-body" style="max-height: 450px;overflow: auto;">
                <div id="imageIcons1">
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

<?php include_once('footer.php'); ?>
<script src="../assets/js/gmap3.js"></script>
<script src="../assets/js/modal_alert.js"></script>

</body>
<!-- END BODY-->
</html>

