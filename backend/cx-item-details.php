<?php
include_once('common.php');

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'rider';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);
$eUserType = $_SESSION['sess_user'];
$lang = $_SESSION['sess_lang'];

$iRentItemPostId = $_REQUEST['iItemPostId'] = base64_decode(base64_decode($_REQUEST['iItemPostId']));
$script = 'RentItemDetail';

$getmastertype = "SELECT rp.iItemCategoryId,rc.iMasterServiceCategoryId FROM rentitem_post as rp LEFT JOIN rent_items_category as rc on rc.iRentItemId = rp.iItemCategoryId WHERE  rp.iRentItemPostId = '" . $iRentItemPostId . "'";
$db_mastertype = $obj->MySQLSelect($getmastertype);
$eTypeNew = get_value($master_service_category_tbl, 'eType', 'iMasterServiceCategoryId', $db_mastertype[0]['iMasterServiceCategoryId'], '', 'true');

if ($iRentItemPostId != "") {
    $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $iRentItemPostId, "" , $lang, "All");
    $getRentItemPostData = $getRentItemPostData[0];

    if (scount($getRentItemPostData) == 0) {
        header('Location:bsr_listing.php?eType='.$eTypeNew);
        exit();
    }
} else {
    header('Location:bsr_listing.php?eType='.$eTypeNew);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_RENT_ITEM_DETAILS']; ?></title>
    <meta name="keywords" value=""/>
    <meta name="description" value=""/>
    <?php include_once("top/top_script.php"); ?>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>"></script>

    <link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" rel="stylesheet" />
    <style>
        .column1 {
          float: left;
          padding: 5px;
        }

        .row1::after {
          content: "";
          clear: both;
          display: table;
        }

        .rideshare_userDetails {
            min-height: auto !important;
            display: block;
            margin-bottom: 20px;
        }
    </style>
</head>

<body id="wrapper">
<!-- home page -->
<!-- home page -->
<div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <!-- End: Top Menu-->
    <!-- First Section -->
    <?php include_once("top/header.php"); ?>
    <!-- End: First Section -->
    <section class="profile-section">
        <div class="profile-section-inner">
            <div class="profile-caption _MB0_">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_RENT_ITEM_DETAILS']; ?></h1>
                </div>
                <ul class="overview-detail">
                    <li>
                        <div class="overview-data">
                            <strong><?= $langage_lbl['LBL_RENT_POST_NUMBER']; ?></strong>
                            <span><?= !empty($getRentItemPostData['vRentItemPostNoMail']) ? "#" . $getRentItemPostData['vRentItemPostNoMail'] : "&nbsp;"; ?></span>
                        </div>
                    </li>
                    <li>

                        <div class="overview-data">
                            <strong><?= $langage_lbl['LBL_RENT_CATEGORY']; ?></strong>
                            <span>
                                <?php
                                if (isset($getRentItemPostData['vCatName']) && !empty($getRentItemPostData['vCatName'])) {
                                    echo $getRentItemPostData['vCatName'];
                                }
                                ?>
                            </span>
                        </div>
                    </li>

                    <li>
                        <div class="overview-data">
                            <strong><?= $langage_lbl['LBL_RENT_DATE_POSTED']; ?></strong>
                            <span><?= !empty($getRentItemPostData['dRentItemPostDate']) ? DateTime($getRentItemPostData['dRentItemPostDate']) : "&nbsp;"; ?></span>
                        </div>
                    </li>
                    <li>
                        <div class="overview-data">
                            <strong><?= $langage_lbl['LBL_RENT_RENEWAL_DATE']; ?></strong>
                            <span><?php echo  DateTime($getRentItemPostData['dRenewDate']); 
                                $dRenewDate = strtotime($getRentItemPostData['dRenewDate']);
                                $dApprovedDate = strtotime(date('Y-m-d H:i:s'));
                                $datediff = $dRenewDate - $dApprovedDate;
                                if($getRentItemPostData['eStatus'] == "Approved" && $datediff > 0){
                                    echo " (".round($datediff / (60 * 60 * 24)) ." days left)";
                                } ?></span>
                        </div>
                    </li>

                </ul>
            </div>
        </div>
    </section>
    <section class="profile-earning">
        <div class="profile-earning-inner">
            <div class="left-block">

                <div class="trip-detail-map rideshare_userDetails" id="invoice_map">
                    <div id="map-canvas" class="gmap3" style="width:100%;height:300px;margin-bottom:10px;"></div>
                </div>

                <?php $Fields = $getRentItemPostData['RentitemFieldarray']; 
                if(!empty($Fields)){ ?>
                    <div class="rideshare_userDetails">
                        <div class="invoice-data-holder" style="font-size: 13px;">
                            <table class="table table-striped table-bordered table-hover">
                                <tbody>
                                    <?php foreach ($Fields as $k => $val) { 
                                        foreach ($val as $keyval => $v) {
                                            if($keyval == 'eDescription' || $keyval == 'eName' ){
                                                continue;
                                            }
                                        ?>
                                            <tr>
                                                <td><b><?php echo $keyval ?></b></td>
                                                <td><?php echo $v ?></td>
                                            </tr>
                                    <?php } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>

                <?php if (!empty($getRentItemPostData['timeslot'])) { ?>
                    <div class="rideshare_userDetails">
                        <table class="table table-striped table-bordered table-hover">
                            <thead><th colspan="2"><h5><?= $langage_lbl['LBL_RENT_ITEM_PICKUP_AVAILBILITY']; ?></h5></th></thead>
                            <tbody>
                            <?php foreach ($getRentItemPostData['timeslot'] as $k => $timelval) { 
                                foreach ($timelval as $daysname => $daysvalue) { ?>
                                <tr>
                                    <td><h6><?php echo $daysname;?></h6></td><td><?php echo $daysvalue;?></td>
                                </tr>
                            <?php } } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>

            </div>
            <div class="left-right">
                <div class="inv-destination-data rideshare_userDetails">
                    <div>
                        <strong class="sub-block-title"><?= $langage_lbl['LBL_OTHER_DETAILS']; ?></strong>
                        <ul>
                             <li>
                                <div class="location-point">
                                    <strong><?= $langage_lbl['LBL_LOCATION_FOR_FRONT']; ?></strong>
                                    
                                </div>

                                <div  style="font-size: 13px;"><?php if(!empty($getRentItemPostData['vBuildingNo'])){ 
                                    echo $getRentItemPostData['vBuildingNo'];?>, 
                                <?php } ?>
                                <?php if(!empty($getRentItemPostData['vAddress'])){ 
                                    echo $getRentItemPostData['vAddress'];?>, 
                                <?php } ?>
                                <?php echo $getRentItemPostData['vLocation'];?></div>

                            <li>
                                <div class="location-point">
                                    <strong><?= $langage_lbl['LBL_RENT_ITEM_PRICING_STRUCTURE']; ?></strong>
                                </div>

                                <div  style="font-size: 13px;"><?php if(!empty($getRentItemPostData['eRentItemDuration'])) { 
                                 echo $getRentItemPostData['fAmount']. " / ". $getRentItemPostData['eRentItemDuration'];
                                } else { 
                                    echo $getRentItemPostData['fAmount'];
                                } ?> </div>

                            </li>
                            <?php if($getRentItemPostData['eStatus'] == 'Approved'){ ?>
                                <li>
                                    
                                    <div class="location-point">
                                        <strong><?= $langage_lbl['LBL_RENT_APPROVED_AT']; ?></strong>
                                    </div>

                                    <div style="font-size: 13px;"><?php echo date_format(date_create($getRentItemPostData[0]['dApprovedDate']),"d F, Y");?></div>

                                </li>
                            <?php } ?>

                            <li>
                                <div class="location-point">
                                    <strong><?= $langage_lbl['LBL_RENT_STATUS']; ?></strong>
                                </div>

                                <div  style="font-size: 13px;"><?php echo $getRentItemPostData['eStatus'];?> </div>

                            </li>
                        </ul>

                    </div>
                </div>

                <strong class="sub-block-title"><?= $langage_lbl['LBL_VIEW_PHOTOS']; ?></strong>
                <?php if (!empty($getRentItemPostData['Images'])) { ?>
                    <div class="invoice-data-holder rideshare_userDetails">
                        <div class="info-buttons">
                            <div class="row1">
                                <!-- <?php foreach ($getRentItemPostData['Images'] as $key => $value) {  
                                    if($value['eFileType'] == "Image") {?>
                                   <div class="column1" ><a data-fancybox="gallery" rel="group1" href="<?php echo $value['vImage'];?>"><img src="<?php echo $value['vImage'];?>" alt="<?php echo $value['iRentImageId'];?>" width="80" height="80"></a></div>
                                <?php } else if($value['eFileType'] == "Video"){ ?>
                                    <div class="column1"><a data-fancybox="gallery" data-width="640" data-height="360"  rel="group1" href="#myVideo"><video controls id="myVideo" poster="<?php echo $value['ThumbImage'];?>" width="80" height="80" preload="metadata" style="display: inline-block;"> <source src="<?php echo $value['vImage'];?>#t=0.5" type="video/mp4"></video></a></div>
                                <?php }  } ?> -->
                                <?php foreach ($getRentItemPostData['Images'] as $key => $value) {  
                                if ($value['eFileType'] == "Image") { ?>
                                    <div class="column1">
                                        <a data-fancybox="gallery" href="<?php echo $value['vImage']; ?>">
                                            <img src="<?php echo $value['vImage']; ?>" alt="<?php echo $value['iRentImageId']; ?>" width="80" height="80">
                                        </a>
                                    </div>
                                <?php } else if ($value['eFileType'] == "Video") { ?>
                                    <div class="column1">
                                        <a data-fancybox="gallery" data-src="#video-<?php echo $key; ?>" href="javascript:;">
                                            <img src="<?php echo $value['ThumbImage']; ?>" alt="Video Thumbnail" width="80" height="80">
                                        </a>
                                        <div style="display: none;" id="video-<?php echo $key; ?>">
                                            <video controls poster="<?php echo $value['ThumbImage']; ?>" width="640" height="360" preload="metadata">
                                                <source src="<?php echo $value['vImage']; ?>#t=0.5" type="video/mp4">
                                            </video>
                                        </div>
                                    </div>
                                <?php } 
                            } ?>

                            </div>
                        </div>

                    </div> 
                <?php } ?>
            </div>

        </div>
    </section>

    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
</div>
<!-- footer part end -->
<!-- Footer Script -->
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<!-- Footer Script -->
<script src="assets/js/gmap3.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

<script type="text/javascript">
    var lats = [];
    var longs = [];
    var markers = [];
    var map;
    var newIcon;
    function initialize() {
        var thePoint = new google.maps.LatLng('<?= $getRentItemPostData['vLatitude']; ?>', '<?= $getRentItemPostData['vLongitude']; ?>');
        var mapOptions = {
            zoom: 12,
            center: thePoint,
            minZoom: 2
        };

        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
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
<!-- End: Footer Script -->
</body>

</html>