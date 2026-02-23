<?php
include_once('../common.php');

$iRentItemPostId =  isset($_REQUEST['iItemPostId']) ? $_REQUEST['iItemPostId'] : '';

$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
$script = 'RentItemDetail';

$getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $iRentItemPostId, "" , $default_lang, "All");
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>

    <meta charset="UTF-8"/>

    <title><?= $SITE_NAME ?> | Item Details</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>

    <meta content="" name="keywords" />

    <meta content="" name="description" />

    <meta content="" name="author" />

    <?php include_once('global_files.php'); ?>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" rel="stylesheet" />
    <style>
        td p {
            padding-top:10px;
        }

        .column1 {
          float: left;
          width: 33.33%;
          padding: 5px;
        }

        /* Clearfix (clear floats) */
        .row1::after {
          content: "";
          clear: both;
          display: table;
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
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>Item Details</h2>
                    </div>
                </div>
                <hr/>
            </div>

            <div class="table-list">

                <div class="row">

                    <div class="col-lg-12">
                        <div class="panel panel-default">

                            <!-- <div class="panel-heading">
                            </div> -->
                            <div class="panel-body rider-invoice-new">

                                <div class="row">

                                    <div class="col-sm-6 rider-invoice-new-left" style="border:0">
                                        <div id="map-canvas" class="gmap3" style="width:100%;height:300px;margin-bottom:10px;border:1px solid #e7e5e5;"></div>

                                        <table class="table table-striped table-bordered table-hover">
                                            <thead><th colspan="2"> <h5>Other Details For <?php echo $getRentItemPostData[0]['vRentItemPostNo'];?></h5></th></thead>
                                            <tbody>
                                                <?php
                                                $Fields = $getRentItemPostData[0]['RentitemFieldarray'];
                                                $systemTimeZone = date_default_timezone_get();
                                                if(!empty($Fields)){ ?>
                                                <?php   foreach ($Fields as $k => $val) {
                                                    foreach ($val as $keyval => $v) {
                                                        if($keyval == 'eDescription' || $keyval == 'eName'){
                                                            continue;
                                                        }

                                                        $date_format_data_array = array(
                                                            'langCode' => $default_lang,
                                                            'DateFormatForWeb' => 1
                                                        );
                                                        $date_format_data_array['tdate'] = (!empty($getRentItemPostData[0]['vTimeZone'])) ? converToTz($getRentItemPostData[0]['dRentItemPostDate'],$getRentItemPostData[0]['vTimeZone'], $systemTimeZone) : $getRentItemPostData[0]['dRentItemPostDate'];
                                                        $get_dRentItemPostDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                                        $date_format_data_array['tdate'] = (!empty($getRentItemPostData[0]['vTimeZone'])) ? converToTz($getRentItemPostData[0]['dApprovedDate'],$getRentItemPostData[0]['vTimeZone'], $systemTimeZone) : $getRentItemPostData[0]['dApprovedDate'];
                                                        $get_dApprovedDate_format = DateformatCls::getNewDateFormat($date_format_data_array);

                                                        if ($getRentItemPostData[0]['dRenewDate'] != "0000-00-00 00:00:00") {
                                                            $date_format_data_array['tdate'] = (!empty($getRentItemPostData[0]['vTimeZone'])) ? converToTz($getRentItemPostData[0]['dRenewDate'],$getRentItemPostData[0]['vTimeZone'], $systemTimeZone) : $getRentItemPostData[0]['dRenewDate'];
                                                            $get_dRenewDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                                                        }


                                                    ?>
                                                        <tr>
                                                            <td><h6><?php echo $keyval ?></h6></td>
                                                            <td><?php echo $v ?></td>
                                                        </tr>
                                                <?php } }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="col-sm-6 rider-invoice-new-right" >
                                         <table class="table table-striped table-hover">
                                            <tbody>
                                                <tr>
                                                    <td><h5>Category Name:</h5></td>
                                                    <td> <h6><?php echo $getRentItemPostData[0]['vCatName'];?></h6></td>
                                                </tr>
                                                <tr>
                                                    <td><h5>User Name</h5> </td>
                                                    <td><p><?php echo $getRentItemPostData[0]['vUserName'];?></p></td>
                                                </tr>
                                                <?php if (!empty($getRentItemPostData[0]['fAmount'])) { ?>
                                                <tr>
                                                    <td><h5>Pricing Structure</h5> </td>
                                                    <?php if(!empty($getRentItemPostData[0]['eRentItemDuration'])) { ?>
                                                        <td><p><?php echo $getRentItemPostData[0]['fAmount']. " / ". $getRentItemPostData[0]['eRentItemDuration'];?></p></td>
                                                    <?php } else { ?>
                                                        <td><p><?php echo $getRentItemPostData[0]['fAmount'];?></p></td>
                                                    <?php } ?>
                                                </tr>
                                                 <?php } ?>

                                                <tr>
                                                    <td><h5>Location</h5></td>
                                                    <td><p>
                                                        <?php if(!empty($getRentItemPostData[0]['vBuildingNo'])){
                                                            echo $getRentItemPostData[0]['vBuildingNo'];?>,
                                                        <?php } ?>
                                                        <?php if(!empty($getRentItemPostData[0]['vAddress'])){
                                                            echo $getRentItemPostData[0]['vAddress'];?>,
                                                        <?php } ?>
                                                        <?php echo $getRentItemPostData[0]['vLocation'];?></p></td>

                                                </tr>
                                                <tr>
                                                    <td><h5>Date of Posted</h5></td>
                                                    <td><p><?php echo $get_dRentItemPostDate_format['tDisplayDateTime'];//DateTime($getRentItemPostData[0]['dRentItemPostDate'],'9');
                                                    //echo date_format(date_create($getRentItemPostData[0]['dRentItemPostDate']),"d F, Y"); ?></p></td>
                                                </tr>
                                                <?php if($getRentItemPostData[0]['eStatus'] == 'Approved'){ ?>
                                                <tr>
                                                    <td><h5>Date of Approved</h5></td>
                                                    <td><p><?php echo $get_dApprovedDate_format['tDisplayDateTime'];//DateTime($getRentItemPostData[0]['dApprovedDate'],'9');
                                                    //echo date_format(date_create($getRentItemPostData[0]['dApprovedDate']),"d F, Y");?></p></td>
                                                </tr>

                                                 <tr>
                                                    <td><h5>Valid Till</h5></td>
                                                    <td><p><?php echo $getRentItemPostData[0]['eRentItemDurationDateTxt'];?></p></td>
                                                </tr>
                                            <?php } ?>
                                                 <tr>
                                                    <td><h5>Status</h5></td>
                                                    <td><p><?php echo $getRentItemPostData[0]['eStatus'];?> </p></td>
                                                </tr>

                                                <?php if ($getRentItemPostData[0]['dRenewDate'] != "0000-00-00 00:00:00") { ?>
                                                <tr>
                                                    <td><h5>Date of RenewPost</h5></td>
                                                    <td><p><?php echo $get_dRenewDate_format['tDisplayDateTime']// DateTime($getRentItemPostData[0]['dRenewDate'],'9');
                                                    //date_format(date_create($getRentItemPostData[0]['dRenewDate']),"d F, Y");?></p></td>
                                                </tr>
                                               <?php } ?>

                                                <?php $imagArr = $getRentItemPostData[0]['Images'];
                                                if(!empty($imagArr)){?>
                                                <tr>
                                                    <td colspan="2"><h5>Item Photos</h5></td>
                                                </tr>
                                                <tr><td colspan="2"><div class="row1">
                                                    <?php  foreach ($imagArr as $key => $value) {
                                                        if($value['eFileType'] == "Image") {?>
                                                       <div class="column1" ><a data-fancybox="gallery" rel="group1" href="<?php echo $value['vImage'];?>"><img src="<?php echo $value['vImage'];?>" alt="<?php echo $value['iRentImageId'];?>" width="150" height="150"></a></div>
                                                    <?php } else if($value['eFileType'] == "Video"){ ?>
                                                        <div class="column1"><a data-fancybox="gallery" data-width="640" data-height="360"  rel="group1" href="#myVideo"><!-- <img class="card-img-top img-fluid" width="150" height="150"  src="<?php echo $value['ThumbImage'];?>" /> --><video controls id="myVideo" poster="<?php echo $value['ThumbImage'];?>" width="150" height="150" preload="metadata"> <source src="<?php echo $value['vImage'];?>#t=0.5" type="video/mp4"></video></a></div>
                                                    <?php }  } ?></div>
                                                </td></tr>
                                                <?php } ?>

                                               <?php if (!empty($getRentItemPostData[0]['timeslot'])) { ?>
                                                <table class="table table-striped table-bordered table-hover">
                                                    <thead><th colspan="2"><h5>Pickup Avaliability</h5></th></thead>
                                                    <tbody>
                                                    <?php foreach ($getRentItemPostData[0]['timeslot'] as $k => $timelval) {
                                                        foreach ($timelval as $daysname => $daysvalue) { ?>
                                                        <tr>
                                                            <td><h6><?php echo $daysname;?></h6></td><td><?php echo $daysvalue;?></td>
                                                        </tr>
                                                    <?php } } ?>
                                                    </tbody>
                                                </table>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                             </div>
                         </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!--END PAGE CONTENT -->

</div>

<!--END MAIN WRAPPER -->

<?php include_once('footer.php'); ?>
<script src="https://maps.google.com/maps/api/js?key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $tconfig["tsite_url"] ?>assets/js/google_map_init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

<script type="text/javascript">

    var lats = [];

    var longs = [];

    var markers = [];

    var map;

    var newIcon;

    function initialize() { //alert('<?= json_encode($latitudes) ?>');

        var thePoint = new google.maps.LatLng('<?= $getRentItemPostData[0]['vLatitude']; ?>', '<?= $getRentItemPostData[0]['vLongitude']; ?>');

        GOOGLE_MAP_OBJ.options.center = thePoint;
        GOOGLE_MAP_OBJ.options.zoom = 16;
        map = GOOGLE_MAP_OBJ.init('map-canvas');

        var marker = new google.maps.Marker({
            position: thePoint,
            map: map,
            title:""
        });

    }
    $(document).ready(function() {
        google.maps.event.addDomListener(window, 'load', initialize);
        /* Apply fancybox to multiple items */
        // Fancybox Config
        $('[data-fancybox="gallery"]').fancybox({
          buttons: [
            "slideShow",
            "thumbs",
            "zoom",
            "fullScreen",
            "close"
          ],
          loop: true,
          protect: true
        });

    });
</script>
</body>
<!-- END BODY-->
</html>