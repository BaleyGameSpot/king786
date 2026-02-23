<?php
    include_once("common.php");
    //added by SP for cubex changes on 07-11-2019


    

    $fromOrder = "guest";
    if (isset($_REQUEST['order']) && $_REQUEST['order'] != "") {
        $fromOrder = $_REQUEST['order'];
    }
    
    $orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
    $orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
    $orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
    $orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
    $orderAddressSession = "MANUAL_ORDER_ADDRESS_" . strtoupper($fromOrder);
    $orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
    $orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
    $orderLatitudeSession = "MANUAL_ORDER_LATITUDE_" . strtoupper($fromOrder);
    $orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_" . strtoupper($fromOrder);
    $orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_" . strtoupper($fromOrder);
    $orderDataSession = "MANUAL_ORDER_DATA_" . strtoupper($fromOrder);
    $iServiceId =$_SESSION[$orderServiceSession];
    $vLang = "EN";
    if (isset($_SESSION['sess_lang'])) {
        $vLang = $_SESSION['sess_lang'];
    }
    $_SESSION['sess_language'] = $vLang;
    global $intervalmins;
    $vTimeZone = date_default_timezone_get();
    $cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
    $str_date = @date('Y-m-d H:i:s', strtotime('-' . $cmpMinutes . ' minutes'));
    
    $param = ($DRIVER_REQUEST_METHOD == "Time") ? "tOnline" : "tLastOnline";
    $script = "Restaurant";
    $vServiceAddress = 0;
    $vLatitude = $vLongitude = $iUserId = $iUserAddressId = "";
    if (isset($_SESSION[$orderUserIdSession])) {
        $iUserId = $_SESSION[$orderUserIdSession];
    }
    if (isset($_SESSION[$orderAddressIdSession])) {
        $iUserAddressId = $_SESSION[$orderAddressIdSession];
    }
    if (isset($_SESSION[$orderAddressSession])) {
        $vServiceAddress = $fulladdress = $_SESSION[$orderAddressSession];
    }
    if (isset($_SESSION[$orderLatitudeSession])) {
        $vLatitude = $_SESSION[$orderLatitudeSession];
    }
    if (isset($_SESSION[$orderLongitudeSession])) {
        $vLongitude = $_SESSION[$orderLongitudeSession];
    }

    $languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);
    $selServiceName = "";
    $atText = $languageLabelsArr['LBL_AT_TXT'];
    if (isset($_SESSION[$orderServiceNameSession]) && $_SESSION[$orderServiceNameSession] != "") {
        $selServiceName = $_SESSION[$orderServiceNameSession] . " " . $atText . " - ";
    }
    if (!empty($iUserId) && empty($vLongitude) && empty($vLatitude) && !empty($iUserAddressId)) {
        if (empty($iUserId) || empty($iUserAddressId)) {
            header("location:user-order-information?error=1&var_msg=" . $languageLabelsArr['LBL_NO_USER_ADDRESS_FOUND'] . "&order=" . $fromOrder);
            exit;
        }
        $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'");
        //print_r($iUserId);die;
        if (scount($Dataua) > 0) {
            $vBuildingNo = $Dataua[0]['vBuildingNo'];
            $vLandmark = $Dataua[0]['vLandmark'];
            $vAddressType = $Dataua[0]['vAddressType'];
            $a = $b = '';
            if ($vBuildingNo != '') {
                $a = ucfirst($vBuildingNo) . ", ";
            }
            if ($vLandmark != '') {
                $b = ucfirst($vLandmark) . ", ";
            }
            $fulladdress = $a . "" . $b . "" . $Dataua[0]['vServiceAddress'];
            $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
            $vLatitude = $Dataua[0]['vLatitude'];
            $vLongitude = $Dataua[0]['vLongitude'];
            $vTimeZone = $Dataua[0]['vTimeZone'];
        }
    }
    $sourceLocationArr = array($vLatitude, $vLongitude);
    ///$allowed_ans = checkAreaRestriction($sourceLocationArr, "No");
    $checkUser = GetSessionMemberType();
    $checkFavStore = $MODULES_OBJ->isFavouriteStoreModuleAvailable();
    
    
    $sql_query = $ssql = $leftjoinsql = "";
    if (($checkUser == 'rider' || strtolower($checkUser) == "user") && !empty($iUserId) && $checkFavStore == 1) {
        include "include/features/include_fav_store.php";
        $sql_query = getFavSelectQuery('', $iUserId);
    }
    $tsite_url = $tconfig['tsite_url'];
    $redirect_location = $tsite_url . 'order-items?order=' . $fromOrder;
    if (strtolower($checkUser) == 'store' || strtolower($checkUser) == 'admin') {
        $redirect_location = $tsite_url . 'user-order-information?order=' . $fromOrder;
    }
    $having_ssql = "";
    if (SITE_TYPE == "Demo" && $searchword == "") {
        $vAddress = $vServiceAddress;
        $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
        if ($vAddress != "") {
            // $ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        } else {
            // $ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
        }
    }

    $postcuisineIds = (isset($_POST['cuisine_type'])) ? $_POST['cuisine_type'] : array();
    $postoffers = (isset($_POST['offers'])) ? $_POST['offers'] : "";
    $postfavStore = (isset($_POST['favStore'])) ? $_POST['favStore'] : "";
    $iServiceIdDef = $iServiceId;
    if(isset($_POST['filter']))
    {
        $cuisine_types = (scount($_POST['cuisine_type'])) ? $_POST['cuisine_type'] : "";
        if($cuisine_types != "")
        {
            $cuisine_types = "'".implode("','", $cuisine_types)."'";
            $cuisinesql = "select cuisineId from cuisine where cuisineName in (".$cuisine_types.")";
            $cuisineData = $obj->MySQLSelect($cuisinesql);
            $cuisineIds = array();
            foreach ($cuisineData as $cvalue) {
                $cuisineIds[] = $cvalue['cuisineId'];
            }
            $cuisineIds = implode(',', $cuisineIds);

            $leftjoinsql .= " LEFT JOIN company_cuisine on company.iCompanyId = company_cuisine.iCompanyId";
            $ssql .= " AND company_cuisine.cuisineId IN (".$cuisineIds.")";
        }
        if($postoffers != "")
        {
            $ssql .= " AND fOfferAppyType != 'None'";
        }
    }

    $ssql1 = " AND company.eBuyAnyService = 'No' ";
    if($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
        $fsql = " AND FIND_IN_SET('" . $iServiceId . "', company.iServiceId) OR FIND_IN_SET('" . $iServiceId . "', company.iServiceIdMulti) ";
    }
    else {
        $fsql = " AND iServiceId = '" . $iServiceId . "' ";
    }
    $sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(" . $vLatitude . ") ) * cos( radians( vRestuarantLocationLat ) ) * cos( radians( vRestuarantLocationLong ) - radians(" . $vLongitude . ") ) + sin( radians(" . $vLatitude . ") ) * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* " . $sql_query . " FROM `company` $leftjoinsql WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' $fsql $ssql $ssql1 HAVING (distance < " . $LIST_RESTAURANT_LIMIT_BY_DISTANCE . $having_ssql . ") ORDER BY `company`.`iCompanyId` ASC";

    $Data = $obj->MySQLSelect($sql);
    $storeIdArr = array();
    if (scount($Data) > 0) {
        for ($ii = 0; $ii < scount($Data); $ii++) {
            $iCompanyId = $Data[$ii]['iCompanyId'];
            array_push($storeIdArr, $iCompanyId);
        }
    }
    $iToLocationId = GetUserGeoLocationId($sourceLocationArr);
    
    
    //$storeIdArr = array(288);
    $storeDetails = getStoreDetails($storeIdArr, $iUserId, $iToLocationId, $languageLabelsArr);
    //echo "<pre>";print_r($storeDetails);die;
    for ($rs = 0; $rs < scount($Data); $rs++) {
        $restStatus = "closed";
        if (isset($storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status']) && $storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status'] != "") {
            $restStatus = strtolower($storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status']);
        }
        $Data[$rs]['restaurantstatus'] = $restStatus;
        $vAvgRating = $Data[$rs]['vAvgRating'];
        $Data[$rs]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$rs]['vAvgRating'], 1) : 0;
        $Data[$rs]['vAvgRatingOrig'] = $Data[$rs]['vAvgRating'];

        $getFooMenu = $obj->MySQLSelect("SELECT GROUP_CONCAT(iFoodMenuId) as foodMenuIds FROM food_menu WHERE iCompanyId = '". $Data[$c]['iCompanyId'] ."'");
        
        if(!empty($getFooMenu[0]['foodMenuIds'])) {
            $getStoreItems = $obj->MySQLSelect("SELECT COUNT(iMenuItemId) as menuItemCount FROM menu_items WHERE iFoodMenuId IN (". $getFooMenu[0]['foodMenuIds'] ."");

            if($getStoreItems[0]['menuItemCount'] == 0) {
                unset($Data[$c]);
            }
        }
        else {
            unset($Data[$c]);
        }

        // $getStoreItems = $obj->MySQLSelect("SELECT COUNT(mi.iMenuItemId) as menuItemCount FROM `menu_items` as mi LEFT JOIN food_menu as fm ON fm.iFoodMenuId = mi.iFoodMenuId LEFT JOIN company as c ON c.iCompanyId = fm.iCompanyId WHERE c.iCompanyId = '". $Data[$c]['iCompanyId'] ."'");
        // if($getStoreItems[0]['menuItemCount'] == 0) {
        //     unset($Data[$c]);
        // }
    }
    //echo "<pre>";print_r($storeDetails);die;
    //Added By HJ On 22-06-2019 For Get Store By Filter Start
    $sortby = isset($_POST["sortby"]) ? $_POST["sortby"] : 'relevance';
    if ($sortby == "" || $sortby == NULL) {
        $sortby = "relevance";
    }
    if ($sortby == "rating") {
        $sortfield = "vAvgRatingOrig";
        $sortorder = SORT_DESC;
    } elseif ($sortby == "time") {
        $sortfield = "fPrepareTime";
        $sortorder = SORT_ASC;
    } elseif ($sortby == "costlth") {
        $sortfield = "fPricePerPerson";
        $sortorder = SORT_ASC;
    } elseif ($sortby == "costhtl") {
        $sortfield = "fPricePerPerson";
        $sortorder = SORT_DESC;
    } else {
        $sortfield = "restaurantstatus";
        $sortorder = SORT_DESC;
    }
    foreach ($Data as $k => $v) {
        $Data_name[$sortfield][$k] = $v[$sortfield];
        $Data_name['restaurantstatus'][$k] = $v['restaurantstatus'];
    }
    array_multisort($Data_name['restaurantstatus'], SORT_DESC, $Data_name[$sortfield], $sortorder, $Data);
    $Data = array_values($Data);

    if($postfavStore != "")
    {
        $favStoreData = array();
        foreach ($Data as $fkey => $fvalue) {
            if($fvalue['eFavStore'] == "Yes")
            {
                $favStoreData[] = $fvalue;
            }
        }
        $Data = $favStoreData;
    }
    
    $page = isset($_REQUEST['page']) ? trim($_REQUEST['page']) : 1;
    $per_page = 12;
    $totalStore = scount($Data); //Added By HJ On 18-01-2020 As Per Discuss Between CS and KS Sir
    $TotalPages = ceil(scount($Data) / $per_page);
    $pagecount = $page - 1;
    $start_limit = $pagecount * $per_page;
    $next_page = $page + 1;
    $Data = array_slice($Data, $start_limit, $per_page);
    //Added By HJ On 22-06-2019 For Get Store By Filter End
    //$allcuisine = getcuisinelist($storeIdArr, 0);
    //$cuisineArr = $storeDetails['cuisineArr'];
    $allStoreCuisin = array_values($storeDetails['companyCuisineArr']);
    $finalCuisineArr = $cuisineArr = array();
    foreach ($allStoreCuisin as $cuisineKey => $cuisineArr) {
        for ($f = 0; $f < scount($cuisineArr); $f++) {
            $finalCuisineArr[] = $cuisineArr[$f];
        }
    }
    if (scount($finalCuisineArr) > 0) {
        $cuisineoriginalarray = array_unique($finalCuisineArr);
        $cuisineArr = array_values($cuisineoriginalarray);
    }
    $cuisinecount = $storeDetails['cuisinecount'];
    $confirmLabel = $languageLabelsArr['LBL_DELETE_CART_ITEM'];
    $noOfferTxt = $languageLabelsArr['LBL_NO_OFFER_TXT'];
    $pageHead = $SITE_NAME . " | " . $languageLabelsArr['LBL_STORE_LISTING_MANUAL_TXT'];

    setcookie('store_sortby', $sortby, time() + 3600);
?> 

<ul class="rest-listing" id="rest-listing">
    <?php
        $currencySymbol = "$";
        if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != "") {
            $currencySymbol = $storeDetails['currencySymbol'];
        }
        for ($i = 0; $i < scount($Data); $i++) {
            $fDeliverytime = 0;
            $iCompanyId = $Data[$i]['iCompanyId'];
            $vAvgRating = $Data[$i]['vAvgRating'];
            $iServiceId = $Data[$i]['iServiceId'];
            $Data[$i]['vAvgRating'] = ($vAvgRating > 0) ? number_format($Data[$i]['vAvgRating'], 1) : 0;
            $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
            $Data[$i]['vCompany'] = stripslashes(ucfirst_multibyte($Data[$i]['vCompany']));
            //$CompanyDetailsArr = getCompanyDetails($iCompanyId, $iUserId, "No", "", $iServiceId, $vLang);
            //$restaurant_status_arr = GetStoreWorkingHoursDetails($iCompanyId, $iUserId);
            //echo "<pre>";print_r($Data[$i]);die;
            if ($Data[$i]['vImage'] != "") {
                $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/3_' . $Data[$i]['vImage'];
            } else {
                /* if ($iServiceId != 1) {
                  $Data[$i]['vImage'] = $tsite_url . 'assets/img/custome-store/deliveryall-menu-order-list.png';
                  } else {
                  $Data[$i]['vImage'] = $tsite_url . 'assets/img/custome-store/food-menu-order-list.png';
                  } */
                $Data[$i]['vImage'] = $tsite_url . 'assets/img/custome-store/food-menu-order-list.png';
            }
            if ($Data[$i]['vCoverImage'] != "") {
                $Data[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/' . $Data[$i]['vCoverImage'];
            }
            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
            if (isset($storeDetails['storeDemoImageArr'][$iCompanyId]) && $storeDetails['storeDemoImageArr'][$iCompanyId] != "" && SITE_TYPE == "Demo") {
                $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
                if (file_exists($demoImgPath)) {
                    $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $storeDetails['storeDemoImageArr'][$iCompanyId];
                    $Data[$i]['vImage'] = $demoImgUrl;
                }
            }
            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
            $Data[$i]['Restaurant_Cuisine'] = "";
            $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
            $Data[$i]['Restaurant_OrderPrepareTime'] = "0 mins";
            $Data[$i]['restaurantstatus'] = $restaurantstatus = "Closed";
            if (isset($storeDetails['companyCuisineArr'][$iCompanyId])) {
                $Data[$i]['Restaurant_Cuisine'] = implode(", ", $storeDetails['companyCuisineArr'][$iCompanyId]);
            }
            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])) {
                $Data[$i]['Restaurant_OrderPrepareTime'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
            }
            $Data[$i]['Restaurant_OrderPrepareTime'] = str_replace('mins', '<img src="' . $tsite_url . 'assets/img/custome-store/delivery_time.png" class="delivery_time_ico" alt=' . $Data[$i]['Restaurant_OrderPrepareTime'] . '><br>mins', $Data[$i]['Restaurant_OrderPrepareTime']);
            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])) {
                $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
            }
            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])) {
                $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
            }
            if ($Restaurant_OfferMessage_short == "") {
                $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
            }
            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])) {
                $Data[$i]['restaurantstatus'] = $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
            }
            $restOpenTime = $restCloseTime = "";
            $timeSlotAvailable = "No";
            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'])) {
                $restOpenTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'];
            }
            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'])) {
                $restCloseTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'];
            }
            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'])) {
                $timeSlotAvailable = $storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'];
            }
            $restPricePerPerson = $restMinOrdValue = 1;
            if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])) {
                $restPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
            }
            //echo "<pre>";print_r($storeDetails);die;
            $Data[$i]['Restaurant_fPricePerPer'] = $currencySymbol . $restPricePerPerson . ' <div>' . ucfirst_multibyte($languageLabelsArr['LBL_PER_PERSON_TXT']) . "</div>";
            if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])) {
                $restMinOrdValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
            }
            $Data[$i]['Restaurant_fMinOrderValue'] = $currencySymbol . $restMinOrdValue . " <div>" . ucfirst_multibyte($languageLabelsArr['LBL_MIN_SMALL']) . ". " . ucfirst_multibyte($languageLabelsArr['LBL_ORDER']) . "</div>";
            $Data[$i]['Restaurant_Opentime'] = $restOpenTime;
            $Data[$i]['Restaurant_Closetime'] = $restCloseTime;
            $Data[$i]['timeslotavailable'] = $timeSlotAvailable;
            if (isset($Data[$i]['Restaurant_Opentime']) && !empty($Data[$i]['Restaurant_Opentime'])) {
                $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'] . ' ' . $Data[$i]['Restaurant_Opentime'];
            } else {
                $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'];
            }
        
            if (isset($Data[$i]['timeslotavailable']) && !empty($Data[$i]['timeslotavailable']) && $Data[$i]['timeslotavailable'] == 'Yes') {
                $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_NOT_ACCEPT_ORDERS_TXT'];
            }
            
            $safetyimg = "/webimages/icons/DefaultImg/ic_safety.png";
            $safetyimgurl = (file_exists($tconfig["tpanel_path"].$safetyimg)) ? $tconfig["tsite_url"].$safetyimg : "";
            $safetyurl = $tconfig["tsite_url"]."safety-measures?fromweb=Yes&order=" . $fromOrder;

            $galleryimg = "/webimages/icons/DefaultImg/ic_gallery.png";
            $galleryimgurl = (file_exists($tconfig["tpanel_path"].$galleryimg)) ? $tconfig["tsite_url"].$galleryimg : "";
            $galleryurl = $tconfig["tsite_url"]."safety-measures?fromweb=Yes&order=" . $fromOrder;

            $company_data = $obj->MySQLSelect("SELECT eSafetyPractices FROM company WHERE iCompanyId = $iCompanyId");
            $banner_images = 0;
            if($MODULES_OBJ->isEnableStorePhotoUploadFacility()) {
                $banner_data = $obj->MySQLSelect("SELECT * FROM store_wise_banners WHERE iCompanyId = ".$iCompanyId . " AND eStatus = 'Active' GROUP BY iUniqueId ORDER BY iUniqueId DESC");
                if(scount($banner_data) > 0) {
                    $banner_images = 1;
                }
            } 
            ?>

    <li <?php if (strtolower($restaurantstatus) == "closed") { ?> class="rest-closed" <?php } ?> data-page="<?= $next_page ?>">
        <div data-status="<?= $Data[$i]['Restaurant_Open_And_Close_time']; ?>" class="outeranchor">
            <?php
                if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1) {
                    ?>
            <div class="add-favorate">
                <span class="fav-check">
                <input id="favouriteManualStore" name="favouriteManualStore" data-company="<?= $iCompanyId; ?>" data-service="<?= $iServiceId; ?>" onclick="updateFavStoreStatus(this);" class="favouriteManualStore" type="checkbox" value="Yes" <?php
                    if (isset($Data[$i]['eFavStore']) && !empty($Data[$i]['eFavStore']) && $Data[$i]['eFavStore'] == 'Yes') {
                        echo "checked";
                    }
                    ?>>
                <span class="custom-check"></span>
                </span>
            </div>
            <?php } ?> 
            <a href="<?= $tsite_url; ?>store-items?id=<?= $iCompanyId; ?>&order=<?= $fromOrder; ?>"><div class="rest-pro" style="background-image:url(<?= ($Data[$i]['vImage']); ?>);" ></div></a>
            <div class="procapt">
                <a href="<?= $tsite_url; ?>store-items?id=<?= $iCompanyId; ?>&order=<?= $fromOrder; ?>">
                <strong title="<?= $Data[$i]['vCompany']; ?>">
                    <span class="item-list-name"><?= $Data[$i]['vCompany']; ?></span><span class="rating"><img src="<?= $tsite_url; ?>assets/img/star.svg" alt=""> <?= $Data[$i]['vAvgRatingOrig']; ?></span><?php
                        if (strtolower($restaurantstatus) == "closed") {
                            ?><?php
                        }
                        ?>
                    <div class="food-detail" title="<?= $Data[$i]['Restaurant_Cuisine']; ?>"><?= $Data[$i]['Restaurant_Cuisine']; ?></div>
                </strong>
                <!-- <span class="food-type"><?php
                    // echo $Data[$i]['Restaurant_Cuisine'];
                    ?></span> -->
                <div class="span-row">
                    <span class="timing"><?= $Data[$i]['Restaurant_OrderPrepareTime']; ?></span>
                    <span class="on-nin"><?= $Data[$i]['Restaurant_fMinOrderValue']; ?></span>
                    <?php if ($iServiceId == 1) { ?>
                    <span class="on-nos"><?= $Data[$i]['Restaurant_fPricePerPer']; ?></span>
                    <?php } ?>
                </div>
                <span class="discount-txt">
                <img src="<?= $tsite_url; ?>assets/img/discount.svg" alt="">
                <?= $Restaurant_OfferMessage; ?>
                </span>
                </a>
                <?php /*
                if($iServiceId==1 || $iServiceId==2) {
                if($Data[$i]['eSafetyPractices']=='Yes') { ?><a href="<?= $safetyurl.'&id='.$iCompanyId; ?>" class="who-txt" target="new"><?php } else { ?><span class="who-txt" style="border:none"><?php } ?>
                <?php if($Data[$i]['eSafetyPractices']=='Yes') { ?>
                <img src="<?= $safetyimgurl ?>" alt="">
                <?= $languageLabelsArr['LBL_SAFETY_NOTE_TITLE_LIST'] ?>
                <?php } ?>
                <?php if($Data[$i]['eSafetyPractices']=='Yes') { ?></a><?php } else { ?></span><?php } }*/ ?>
                <?php //if($iServiceId==1 || $iServiceId==2) 
                {
                    if(($Data[$i]['eSafetyPractices'] =='Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()) || ($MODULES_OBJ->isEnableStorePhotoUploadFacility() && $banner_images == 1)) { ?>
                        <?php if($Data[$i]['eSafetyPractices'] =='Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()) { ?>
                        <a href="<?= $safetyurl.'&id='.base64_encode($iCompanyId).'&iServiceId='.base64_encode($iServiceId); ?>" class="who-txt" target="new">
                            <img src="<?= $safetyimgurl ?>" alt="">
                            <?= $languageLabelsArr['LBL_SAFETY_NOTE_TITLE_LIST'] ?>
                        </a>
                        <?php } else { ?>
                        <a href="<?= $galleryurl.'&id='.base64_encode($iCompanyId).'&iServiceId='.base64_encode($iServiceId); ?>" class="who-txt" target="new">
                            <img src="<?= $galleryimgurl ?>" alt="">
                            <?= $languageLabelsArr['LBL_VIEW_PHOTOS'] ?>
                        </a>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="who-txt" style="border:none"></span>
                    <?php }
                } ?>
            </div>
        </div>
    </li>
    <?php }
        ?>
</ul>