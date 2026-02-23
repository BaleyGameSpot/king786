<?php

include_once('common.php');

include_once($tconfig["tpanel_path"] . "assets/libraries/include_advance_api.php");
$responce = array();

$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
checkCartItemStatus($fromOrder); // Added By HJ On 18-01-2020 For Check Cart's Item Status 141 Mantis Bug - #2367
$orderDetailsSession = "ORDER_DETAILS_" . strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);
if (isset($_SESSION[$orderDetailsSession])) {
    $_REQUEST["OrderDetails"] = json_encode($_SESSION[$orderDetailsSession]);
}

$iServiceId = "1";
$iUserId = $iUserAddressId = "";
if (isset($_SESSION[$orderServiceSession])) {
    $iServiceId = $_SESSION[$orderServiceSession];
}
if (isset($_SESSION[$orderUserIdSession])) {
    $iUserId = $_SESSION[$orderUserIdSession];
}
if (isset($_SESSION[$orderAddressIdSession])) {
    $iUserAddressId = $_SESSION[$orderAddressIdSession];
}
//print_r($iUserAddressId);die;
$cart_id_update = isset($_REQUEST["cart_id_update"]) ? $_REQUEST["cart_id_update"] : '';
$iCompanyId = isset($_SESSION[$orderStoreIdSession]) ? $_SESSION[$orderStoreIdSession] : '';
$CheckNonVegFoodType = isset($_REQUEST["CheckNonVegFoodType"]) ? $_REQUEST["CheckNonVegFoodType"] : 'No';
$fDeliverytime = 0;
$sql = "SELECT *  FROM `user_address`  WHERE iUserAddressId = '" . $iUserAddressId . "' AND iUserId = '" . $iUserId . "'";
$Dataua = $obj->MySQLSelect($sql);
$vServiceAddress = $vBuildingNo = $vLandmark = $vAddressType = $vLatitude = $vLongitude = "";
$vTimeZone = date_default_timezone_get();
if (scount($Dataua) > 0) {
    $vServiceAddress = ucfirst($Dataua[0]['vServiceAddress']);
    $vBuildingNo = $Dataua[0]['vBuildingNo'];
    $vLandmark = $Dataua[0]['vLandmark'];
    $vAddressType = $Dataua[0]['vAddressType'];
    $vLatitude = $Dataua[0]['vLatitude'];
    $vLongitude = $Dataua[0]['vLongitude'];
    $vTimeZone = !empty($Dataua[0]['vTimeZone']) ? $Dataua[0]['vTimeZone'] : $vTimeZone;
}

$idss = isset($_REQUEST["idss"]) ? $_REQUEST["idss"] : '';
if (!empty($idss)) {
    $couponCode = isset($_SESSION[$orderCouponSession]) ? $_SESSION[$orderCouponSession] : '';
} else {
    $couponCode = isset($_REQUEST["couponCode"]) ? $_REQUEST["couponCode"] : '';
}
$ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
$vDeviceToken = isset($_REQUEST["vDeviceToken"]) ? $_REQUEST["vDeviceToken"] : '';
$OrderDetails = isset($_REQUEST["OrderDetails"]) ? $_REQUEST["OrderDetails"] : '';
$eTakeAway = isset($_REQUEST["eTakeAway"]) ? $_REQUEST["eTakeAway"] : 'No';
$tipAmount = isset($_REQUEST["fTipAmount"]) ? $_REQUEST["fTipAmount"] : 0;
$selectedTipPos = isset($_REQUEST["selectedTipPos"]) ? $_REQUEST["selectedTipPos"] : 0;

//echo "<pre>";print_r($_SESSION);die;
$vInstruction = isset($_REQUEST["vInstruction"]) ? $_REQUEST["vInstruction"] : '';
$passengerLat = $vLatitude;
$passengerLon = $vLongitude;
$fChangeAmount = isset($_REQUEST["changeAmount"]) ? $_REQUEST["changeAmount"] : '';
$CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
if ($CheckUserWallet == "" || $CheckUserWallet == NULL) {
    $CheckUserWallet = "No";
}
$Data = array();
$a = $b = "";
if ($vBuildingNo != '') {
    $a = ucfirst($vBuildingNo) . ", ";
}
if ($vLandmark != '') {
    $b = ucfirst($vLandmark) . ", ";
}
$fulladdress = $a . "" . $b . "" . $vServiceAddress;
$Data['UserSelectedAddress'] = $fulladdress;
$Data['UserSelectedLatitude'] = $vLatitude;
$Data['UserSelectedLongitude'] = $vLongitude;
$Data['UserSelectedAddressId'] = $iUserAddressId;
// # Checking Distance Between Company and User Address ##
$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);

if($fromOrder == "store"){
    $UserDetailsArr = getCompanyCurrencyLanguageDetails($iCompanyId);
}

$Ratio = $UserDetailsArr['Ratio'];
$currencySymbol = $UserDetailsArr['currencySymbol'];
$currencyName = $UserDetailsArr['currencycode'];
$vLang = $UserDetailsArr['vLang'];
if (isset($_SESSION['sess_lang'])) {
    $vLang = $_SESSION['sess_lang'];
}
if ($vLang == "" || $vLang == NULL) {
    $vLang = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}
$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang, "1", $iServiceId);
$sql = "select vCompany,vCaddress,fMaxOfferAmt,fTargetAmt,fOfferType,fOfferAppyType,fOfferAmt,vRestuarantLocation,vImage,iCompanyId,vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge,iMaxItemQty,vDemoStoreImage from `company` where iCompanyId = '" . $iCompanyId . "'";
$db_companydata = $obj->MySQLSelect($sql);
$vCompany = $fOfferAppyType = $fOfferType = $vRestuarantLocation = $vCaddress = "";
$fMaxOfferAmt = $fTargetAmt = $iMaxItemQty = $fCompanyTax = 0;
//echo "<pre>";print_R($db_companydata);die;
$TotaliQty = 0;
if (scount($db_companydata) > 0) {
    //$vCompany = ucwords(strtolower($db_companydata[0]['vCompany']));
    $vCompany = stripslashes(ucfirst($db_companydata[0]['vCompany']));
    $vCaddress = ucwords(strtolower($db_companydata[0]['vCaddress']));
    $vImage = $db_companydata[0]['vImage'];
    $vRestuarantLocation = $db_companydata[0]['vRestuarantLocation'];
    $fMaxOfferAmt = $db_companydata[0]['fMaxOfferAmt'];
    $fMaxOfferAmt = setTwoDecimalPoint($fMaxOfferAmt * $Ratio);
    $fTargetAmt = $db_companydata[0]['fTargetAmt'];
    $fTargetAmt = setTwoDecimalPoint($fTargetAmt * $Ratio);
    $fOfferAppyType = $db_companydata[0]['fOfferAppyType'];
    $fOfferType = $db_companydata[0]['fOfferType'];
    $iMaxItemQty = $db_companydata[0]['iMaxItemQty'];
}
if ($vImage == "" || !file_exists($tconfig['tsite_upload_images_compnay'] . '/' . $db_companydata[0]['iCompanyId'] . '/1_' . $vImage)) {
    $db_companydata[0]['vImage'] = $tconfig['tsite_url'] . 'assets/img/custome-store/food-menu-order-list.png';
} else {
    /* if ($iServiceId != 1) {
      $db_companydata[0]['vImage'] = $tconfig['tsite_url'] . '/assets/img/custome-store/deliveryall-menu-order-list.png';
      } else {
      $db_companydata[0]['vImage'] = $tconfig['tsite_url'] . '/assets/img/custome-store/food-menu-order-list.png';
      } */
    $db_companydata[0]['vImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $db_companydata[0]['iCompanyId'] . '/1_' . $vImage;
}
//Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
if (isset($db_companydata[0]['vDemoStoreImage']) && $db_companydata[0]['vDemoStoreImage'] != "" && SITE_TYPE == "Demo") {
    $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'] . $db_companydata[0]['vDemoStoreImage'];
    if (file_exists($demoImgPath)) {
        $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'] . $db_companydata[0]['vDemoStoreImage'];
        $db_companydata[0]['vImage'] = $demoImgUrl;
    }
}
//Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
$couponCode = trim($couponCode);
if ($couponCode != "") {
    //$checkvalidpromocode = CheckPromoCode($couponCode, $iUserId, $iCompanyId, $passengerLat, $passengerLon); //Added By HJ On 07-06-2019 For Optimized Code
}
$OrderDetails = json_decode(stripcslashes($OrderDetails), true);
$OrderDetailscount = scount($OrderDetails);

$OrderFareDetailsArr = array();
$fFinalTotal = $fTotalMenuItemBasePrice = $fFinalDiscountPercentage = 0;
if (!empty($OrderDetails)) {
    //Added By HJ On 09-05-2019 For Optimize Code Start
    $optionPriceArr = getAllOptionAddonPriceArr();
    $ordItemPriceArr = getAllMenuItemPriceArr();
    //Added By HJ On 09-05-2019 For Optimize Code End
    for ($j = 0; $j < scount($OrderDetails); $j++) {
        $typeitems = trim($OrderDetails[$j]['typeitem']);
        $iQty = $OrderDetails[$j]['iQty'];
        if ($typeitems == 'new') {
            //$fMenuItemPrice = FoodMenuItemBasicPrice($OrderDetails[$j]['iMenuItemId'], $iQty); //Commnent By HJ On 17-05-2019 For Optimize Below Code
            //Added By HJ On 09-05-2019 For Optimize Code Start
            $fMenuItemPrice = 0;
            if (isset($ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']]) && $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] > 0) {
                $fMenuItemPrice = $ordItemPriceArr[$OrderDetails[$j]['iMenuItemId']] * $iQty;
            }
            //Added By HJ On 09-05-2019 For Optimize Code End
            //$vOptionPrice = GetFoodMenuItemOptionPrice($OrderDetails[$j]['vOptionId']); //Commnent By HJ On 17-05-2019 For Optimize Below Code
            //Added By HJ On 09-05-2019 For Optimize Code Start
            $vOptionPrice = 0;
            $explodeOption = explode(",", $OrderDetails[$j]['vOptionId']);
            for ($fd = 0; $fd < scount($explodeOption); $fd++) {
                if (isset($optionPriceArr[$explodeOption[$fd]]) && $optionPriceArr[$explodeOption[$fd]] > 0) {
                    $vOptionPrice += $optionPriceArr[$explodeOption[$fd]];
                }
            }
            //Added By HJ On 09-05-2019 For Optimize Code End
            $vOptionPrice = $vOptionPrice * $iQty;
            //$vAddonPrice = GetFoodMenuItemAddOnPrice($OrderDetails[$j]['vAddonId']); //Commnent By HJ On 17-05-2019 For Optimize Below Code
            //Added By HJ On 09-05-2019 For Optimize Code Start
            $vAddonPrice = 0;
            $explodeAddon = explode(",", $OrderDetails[$j]['vAddonId']);
            for ($df = 0; $df < scount($explodeAddon); $df++) {
                if (isset($optionPriceArr[$explodeAddon[$df]]) && $optionPriceArr[$explodeAddon[$df]] > 0) {
                    $vAddonPrice += $optionPriceArr[$explodeAddon[$df]];
                }
            }
            //Added By HJ On 09-05-2019 For Optimize Code End
            $vAddonPrice = $vAddonPrice * $iQty;
            $fTotalMenuItemBasePrice = $fTotalMenuItemBasePrice + $vOptionPrice + $vAddonPrice + $fMenuItemPrice;
        }
    }
    if ($db_companydata[0]['fMaxOfferAmt'] > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
        $fFinalDiscountPercentage = (($fTotalMenuItemBasePrice * $db_companydata[0]['fOfferAmt']) / 100);
    }
    $fTotalDiscount = $iQty = 0;
    $fTotalMenuItemBasePrice = setTwoDecimalPoint($fTotalMenuItemBasePrice * $Ratio);
    $fFinalDiscountPercentage = setTwoDecimalPoint($fFinalDiscountPercentage * $Ratio);
    $OrderDetailsItemsArr = array();
    for ($i = 0; $i < scount($OrderDetails); $i++) {
        $typeitem = $OrderDetails[$i]['typeitem'];
        if ($OrderDetails[$i]['typeitem'] == 'new') {
            $iMenuItemId = $OrderDetails[$i]['iMenuItemId'];
            $iFoodMenuId = $OrderDetails[$i]['iFoodMenuId'];
            $vOptionId = $OrderDetails[$i]['vOptionId'];
            $vOptionId = trim($vOptionId, ",");
            $vAddonId = $OrderDetails[$i]['vAddonId'];
            $vAddonId = trim($vAddonId, ",");
            $iQty = $OrderDetails[$i]['iQty'];
            $tInst = $OrderDetails[$i]['tInst'];
            $vItemType = get_value('menu_items', 'vItemType_' . $vLang, 'iMenuItemId', $iMenuItemId, '', 'true');
            $MenuItemPriceArr = FetchMenuItemCostByStoreOffer($iMenuItemId, $iCompanyId, "1", $iUserId, "Calculate", $vOptionId, $vAddonId, $iServiceId);
            //echo "<pre>";print_R($MenuItemPriceArr);die;
            $TotOrders = $MenuItemPriceArr['TotOrders'];
            if ($fOfferType == "Flat" && $fOfferAppyType != "None" && $TotOrders == 0) {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = setTwoDecimalPoint($fOriginalPrice);
                $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $Ratio;
                $fDiscountPrice = setTwoDecimalPoint($fDiscountPrice);
                $fPrice = $fOriginalPrice;
                $fOfferAmt = 0;
            } else {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = setTwoDecimalPoint($fOriginalPrice);
                $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $iQty * $Ratio;
                //Added By HJ On 31-07-2020 As Per Discuss with GP Start - Tested By GP in Other Project
                if($fDiscountPrice > $db_companydata[0]['fMaxOfferAmt'] && $db_companydata[0]['fMaxOfferAmt'] > 0){
                    $fDiscountPrice = $db_companydata[0]['fMaxOfferAmt'];
                }
                //Added By HJ On 31-07-2020 As Per Discuss with GP End - Tested By GP in Other Project
                $fDiscountPrice = setTwoDecimalPoint($fDiscountPrice);
                $fPrice = $MenuItemPriceArr['fPrice'] * $iQty * $Ratio;
                $fPrice = setTwoDecimalPoint($fPrice);
                $fOfferAmt = $MenuItemPriceArr['fOfferAmt'];
                $fOfferAmt = setTwoDecimalPoint($fOfferAmt);
                if ($fOfferType == "Flat" && $fOfferAppyType == "All") {
                    $fDiscountPrice = $MenuItemPriceArr['fDiscountPrice'] * $Ratio;
                    $fDiscountPrice = setTwoDecimalPoint($fDiscountPrice);
                    $fPrice = $fOriginalPrice;
                    $fOfferAmt = 0;
                }
            }
            if ($fTotalMenuItemBasePrice < $fTargetAmt && $fOfferAppyType != "None") {
                $fOriginalPrice = $MenuItemPriceArr['fOriginalPrice'] * $iQty * $Ratio;
                $fOriginalPrice = setTwoDecimalPoint($fOriginalPrice);
                $fDiscountPrice = $fOfferAmt = 0;
                $fPrice = $fOriginalPrice;
            }
            $fTotalPrice = $fOriginalPrice;
            $fTotalPrice = setTwoDecimalPoint($fTotalPrice);
            $fFinalTotal = $fFinalTotal + $fTotalPrice;
            if ($fOfferAppyType != "None" && $TotOrders == 0) {
                if ($fOfferType == "Flat" && ($fOfferAppyType=="All" || ($fOfferAppyType=="First" && $TotOrders == 0))) {
                    $fTotalDiscount = $fDiscountPrice;
                } elseif ($fOfferType == "Percentage" && $fOfferAppyType != "None") {
                    $fTotalDiscount += $fDiscountPrice;
                } else {
                    $fTotalDiscount += $fDiscountPrice;
                }
            } else {
                if ($fOfferType == "Flat" && ($fOfferAppyType=="All" || ($fOfferAppyType=="First" && $TotOrders == 0))) {
                    $fTotalDiscount = $fDiscountPrice;
                } elseif ($fOfferType == "Percentage" && $fOfferAppyType != "None") {
                    $fTotalDiscount += $fDiscountPrice;
                } else {
                    $fTotalDiscount += $fDiscountPrice;
                }
            }
            //Added By HJ On 31-07-2020 As Per Discuss with GP Start - Tested By GP in Other Project
            if($fTotalDiscount > $db_companydata[0]['fMaxOfferAmt'] && $db_companydata[0]['fMaxOfferAmt'] > 0){
                $fTotalDiscount = $db_companydata[0]['fMaxOfferAmt'];
            }
            //Added By HJ On 31-07-2020 As Per Discuss with GP End - Tested By GP in Other Project
            /* if ($fMaxOfferAmt > 0 && $fOfferType == "Percentage" && $fOfferAppyType != "None") {
              $fTotalDiscount = ($fTotalDiscount > $fMaxOfferAmt) ? $fMaxOfferAmt : $fTotalDiscount;
              $fPrice = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? $fOriginalPrice : $fPrice;
              $fOfferAmt = ($fFinalDiscountPercentage > $fMaxOfferAmt) ? 0 : $fOfferAmt;
              } */
        }
        $type = "edit";
        if (isset($OrderDetails[$i]['eFoodType']) && !empty($OrderDetails[$i]['eFoodType'])) {
            $OrderDetailsItemsArr[$i]['eFoodType'] = $OrderDetails[$i]['eFoodType'];
        }
        $OrderDetailsItemsArr[$i]['iMenuItemId'] = $iMenuItemId;
        $OrderDetailsItemsArr[$i]['type'] = $type;
        $OrderDetailsItemsArr[$i]['iFoodMenuId'] = $iFoodMenuId;
        $OrderDetailsItemsArr[$i]['vItemType'] = $vItemType;
        $OrderDetailsItemsArr[$i]['iQty'] = $OrderDetails[$i]['iQty'];
        $OrderDetailsItemsArr[$i]['fOfferAmt'] = $fOfferAmt;
        // $OrderDetailsItemsArr[$i]['fOriginalPrice'] = $currencySymbol . ' ' . formatnum($fOriginalPrice);
		$OrderDetailsItemsArr[$i]['fOriginalPrice'] = formateNumAsPerCurrency(formatnum($fOriginalPrice),$currencyName);
        /* $OrderDetailsItemsArr[$i]['fPrice'] = $currencySymbol.' '.formatnum($fPrice); */
        // $OrderDetailsItemsArr[$i]['fPrice'] = $currencySymbol . ' ' . formatnum($fOriginalPrice);
        // $OrderDetailsItemsArr[$i]['fPrice'] = formateNumAsPerCurrency(formatnum($fOriginalPrice),$currencyName);
		$OrderDetailsItemsArr[$i]['fPrice'] = formateNumAsPerCurrency(formatnum($fPrice),$currencyName);
        $OrderDetailsItemsArr[$i]['tInst'] = $tInst;
        $OrderDetailsItemsArr[$i]['typeitem'] = $typeitem;
        $optionaddonname = "";
        if ($vOptionId != "") {
            $optionname = GetMenuItemOptionsToppingName($vOptionId, $vLang);
            $optionaddonname = ucfirst(trim($optionname));
        }
        if ($optionaddonname != "") {
            $optionaddonname = trim(trim($optionaddonname), ",");
        }
        if ($vAddonId != "") {
            $addonname = GetMenuItemOptionsToppingName($vAddonId, $vLang);
            if ($optionaddonname != "") {
                $optionaddonname .= ", " . ucfirst(trim($addonname));
            } else {
                $optionaddonname = ucfirst(trim($addonname));
            }
        }
        if ($optionaddonname != "") {
            $optionaddonname = trim(trim($optionaddonname), ",");
        }
        $OrderDetailsItemsArr[$i]['optionaddonname'] = $optionaddonname;
        if ($OrderDetails[$i]['typeitem'] == 'new') {
            $TotaliQty += $iQty;
        }
        if ($cart_id_update == $i) {
            $showfoptionaddonname = $optionaddonname;
            // $showfPrice = $currencySymbol . ' ' . formatnum($fOriginalPrice);
			$showfPrice = formateNumAsPerCurrency(formatnum($fOriginalPrice),$currencyName);
            /* $showfPrice = $currencySymbol.' '.formatnum($fPrice); */
        }
    }
    $Data['OrderDetailsItemsArr'] = $OrderDetailsItemsArr;
    //$fPackingCharge = get_value('company', 'fPackingCharge', 'iCompanyId', $iCompanyId, '', 'true');
    $fPackingCharge = 0;
    if (isset($db_companydata[0]['fPackingCharge']) && $db_companydata[0]['fPackingCharge'] > 0) {
        $fPackingCharge = setTwoDecimalPoint($db_companydata[0]['fPackingCharge'] * $Ratio);
    }
    // # Calculate Order Delivery Charge ##
    $fDeliveryCharge = 0;
    $sql = "SELECT vLatitude as passengerlat,vLongitude as passengerlong FROM user_address as ua WHERE iUserAddressId   = '" . $iUserAddressId . "'";
    $datad = $obj->MySQLSelect($sql);
    //$sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
    //$datac = $obj->MySQLSelect($sql);
    if (scount($datad) > 0) {
        $User_Address_Array = array($datad[0]['passengerlat'], $datad[0]['passengerlong']);
        $Rest_Address_Array = array($db_companydata[0]['restaurantlat'], $db_companydata[0]['restaurantlong']);
        $iToLocationId = GetUserGeoLocationId($User_Address_Array);  /*  user location */
        $iLocationId = GetUserGeoLocationId($Rest_Address_Array);    /*  restaurant location */
        if (isset($Data['UserSelectedLatitude']) && isset($Data['UserSelectedLongitude'])) {
            //$sql = "SELECT vRestuarantLocationLat as restaurantlat,vRestuarantLocationLong as restaurantlong,fPackingCharge FROM company WHERE iCompanyId = '" . $iCompanyId . "'";
            //$datac = $obj->MySQLSelect($sql);
            if (scount($db_companydata) > 0) {
                $User_Address_Array = array($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude']);
                $iLocationId = GetUserGeoLocationId($User_Address_Array);
                
                // $distance = distanceByLocation($Data['UserSelectedLatitude'], $Data['UserSelectedLongitude'], $db_companydata[0]['restaurantlat'], $db_companydata[0]['restaurantlong'], "K");
                $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault', 'Yes');
                $vGMapLangCode = $vLangCodeData[0]['vGMapLangCode'];

                $requestDataArr = array();
                $requestDataArr['SOURCE_LATITUDE'] = $db_companydata[0]['restaurantlat'];
                $requestDataArr['SOURCE_LONGITUDE'] = $db_companydata[0]['restaurantlong'];
                $requestDataArr['DEST_LATITUDE'] = $Data['UserSelectedLatitude'];
                $requestDataArr['DEST_LONGITUDE'] = $Data['UserSelectedLongitude'];
                $requestDataArr['LANGUAGE_CODE'] = $vGMapLangCode;
                // echo "<pre>"; print_r($requestDataArr); exit;
                $direction_data = getPathInfoBetweenLocations($requestDataArr);
                $distance = $direction_data['distance'] / 1000;

                $Data['distance'] = $distance;
                //Added By HJ On 02-01-2019 For Get All Location Delivery Charge Start As Per Discuss With CD Sir
                $checkAllLocation = 1;
                if (scount($iLocationId) > 0) {
                    // $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '" . $iLocationId . "' AND eStatus='Active'";
                    $iLocationIdArr = implode(',', $iLocationId);
                    $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId IN (" . $iLocationIdArr . ") AND eStatus='Active' GROUP BY iLocationId ORDER BY iDeliveyChargeId DESC";
                    
                    if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) {
                        $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId IN (" . $iLocationIdArr . ") AND eStatus='Active' AND $distance >= iDistanceRangeFrom AND $distance <= iDistanceRangeTo ORDER BY iDeliveyChargeId DESC";
                    }

                    $data_location = $obj->MySQLSelect($sql);
                    if (scount($data_location) > 0) {
                        $checkAllLocation = 0;
                    }
                }

                if ($checkAllLocation == 1) {
                    $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '0' AND eStatus='Active' GROUP BY iLocationId";

                    if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) {
                        $sql = "SELECT * FROM `delivery_charges` WHERE iLocationId = '0' AND eStatus='Active' AND $distance >= iDistanceRangeFrom AND $distance <= iDistanceRangeTo ORDER BY iDeliveyChargeId DESC";
                    }

                    $data_location = $obj->MySQLSelect($sql);
                }
                //print_r($data_location);die;

                //Added By HJ On 02-01-2019 For Get All Location Delivery Charge End As Per Discuss With CD Sir
                $iFreeDeliveryRadius = $distance = $fOrderPriceValue = $fDeliveryChargeAbove = $fDeliveryChargeBelow = $fFreeOrderPriceSubtotal = 0;
                if (scount($data_location) > 0) {
                    $fOrderPriceValue = $data_location[0]['fOrderPriceValue'];
                    $fOrderPriceValue = setTwoDecimalPoint($fOrderPriceValue * $Ratio);
                    $fDeliveryChargeAbove = $data_location[0]['fDeliveryChargeAbove'];
                    //$fDeliveryChargeAbove = setTwoDecimalPoint($fDeliveryChargeAbove * $Ratio);
                    $fDeliveryChargeBelow = $data_location[0]['fDeliveryChargeBelow'];
                    //$fDeliveryChargeBelow = setTwoDecimalPoint($fDeliveryChargeBelow * $Ratio);
                    $iFreeDeliveryRadius = $data_location[0]['iFreeDeliveryRadius'];
                    $fFreeOrderPriceSubtotal = $data_location[0]['fFreeOrderPriceSubtotal'];
                    $fFreeOrderPriceSubtotal = setTwoDecimalPoint($fFreeOrderPriceSubtotal * $Ratio);

                    $iDistanceRangeFrom = $data_location[0]['iDistanceRangeFrom'];
                    $iDistanceRangeTo = $data_location[0]['iDistanceRangeTo'];
                }
                

                if ($fFinalTotal >= $fOrderPriceValue) {
                    $fDeliveryCharge = $fDeliveryChargeAbove;
                    //$fDeliveryCharge = $fDeliveryChargeBelow;
                } else {
                    $fDeliveryCharge = $fDeliveryChargeBelow;
                    //$fDeliveryCharge = $fDeliveryChargeAbove;
                }
                if ($iFreeDeliveryRadius >= 0) {
                    if ($distance < $iFreeDeliveryRadius && $iFreeDeliveryRadius >= 0 && !empty($iFreeDeliveryRadius)) { //when zero for free order and radius then do not allow free order
                        $fDeliveryCharge = 0;
                    }
                }
                //if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0) { //added by SP for delivery charge blank then it does not count on 27-06-2019
                 if (!empty($fFreeOrderPriceSubtotal) && $fFreeOrderPriceSubtotal != 0 && !empty($fFreeOrderPriceSubtotal)) { //when zero for free order and radius then do not allow free order
                    if ($fFinalTotal > $fFreeOrderPriceSubtotal) {
                        $fDeliveryCharge = 0;
                    }
                }

                /* Custom Delivery Charges */
                $customDeliveryChargesuser = 0;
                if($MODULES_OBJ->isEnableDistanceWiseDeliveryChargeOrder()) {
                    /*$cdcSql = "SELECT * FROM custom_delivery_charges_order WHERE $distance >= iDistanceRangeFrom AND $distance <= iDistanceRangeTo ORDER BY iDeliveyChargeId DESC";
                    $cdcData = $obj->MySQLSelect($cdcSql);
                    if(scount($cdcData) > 0)
                    {
                        $customDeliveryChargesuser = $cdcData[0]['fDeliveryChargeUser'];
                    }*/
                }

                
                $Data['fDeliveryChargeUser'] = $customDeliveryChargesuser;

                $fDeliveryCharge = ($fDeliveryCharge + $customDeliveryChargesuser) * $Ratio;
                /* Custom Delivery Charges End */
            }
        }
    }
    //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 Start
    if($fTotalDiscount > $fFinalTotal){
        $fTotalDiscount = $fFinalTotal;
    }
    //Added By HJ On 29-02-2020 For Solved 141 Mantis Issue #3793 End
    $fTax = 0;
    // # Calculate Order Delivery Charge ##

    if ($fCompanyTax > 0) {
        $fcotaxamount = $fFinalTotal - $fTotalDiscount + $fPackingCharge;
        $fCompanyTax = setTwoDecimalPoint(($fcotaxamount * $fCompanyTax) / 100);
    } else {
        $TaxArr = getMemberCountryTax($iUserId, "Passenger");
        //$fTax = $TaxArr['fTax1'];
        $fTax = $TaxArr['fTax1'] + $TaxArr['fTax2'];


        if ($fTax > 0) {
            $ftaxamount = $fFinalTotal - $fTotalDiscount + $fPackingCharge;
            $fTax = setTwoDecimalPoint(($ftaxamount * $fTax) / 100);
        }
    }
    $fCommision = $ADMIN_COMMISSION;
    //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes Start
    if (isset($db_companydata[0]['fComissionPerOrder']) && $db_companydata[0]['fComissionPerOrder'] > 0 && $ENABLE_STORE_COMMISSION == "Yes") {
        $fCommision = $db_companydata[0]['fComissionPerOrder'];
    }
    //Added By HJ On 30-01-2019 For Applied Store Wise Commission Per Order If > 0 and ENABLE_STORE_COMMISSION==Yes End
    //check deliver charages $fDeliveryCharge 
    //echo $fFinalTotal."+".$fPackingCharge."+".$fDeliveryCharge."+".$fTax."+".$fCompanyTax."-".$fTotalDiscount;die;
    if($eTakeAway == 'Yes')
    {
        $fDeliveryCharge = 0;
    }

    $tipAmount1 = $tipAmount2 = $tipAmount3 = "";
    if($DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL == "Percentage")
    {
        $tipAmount1 = round((($TIP_AMOUNT_1/100) * ($fFinalTotal - $fTotalDiscount)), 2);
        $tipAmount2 = round((($TIP_AMOUNT_2/100) * ($fFinalTotal - $fTotalDiscount)), 2);
        $tipAmount3 = round((($TIP_AMOUNT_3/100) * ($fFinalTotal - $fTotalDiscount)), 2);
    }

    $Data['tipAmount1'] = formateNumAsPerCurrency($tipAmount1,$currencyName);
    $Data['tipAmount2'] = formateNumAsPerCurrency($tipAmount2,$currencyName);
    $Data['tipAmount3'] = formateNumAsPerCurrency($tipAmount3,$currencyName);
    if($selectedTipPos > 0)
    {
        if(in_array($selectedTipPos, [1,2,3]) && $DELIVERY_TIP_AMOUNT_TYPE_DELIVERALL == "Percentage")
        {
            $tipAmount = $tipAmount * ($fFinalTotal - $fTotalDiscount);
        }
    }
    $tipAmount = setTwoDecimalPoint($tipAmount);
    $Data['fDeliveryTipLabel'] = $languageLabelsArr['LBL_DELIVERY_TIP_TXT'];
    $Data['tipAmount'] = $tipAmount;
    $Data['fTipAmount'] = formateNumAsPerCurrency($tipAmount,$currencyName);

    $fNetTotal = $fTotalGenerateFare = ($fFinalTotal + $fPackingCharge + $fDeliveryCharge + $fTax + $fCompanyTax) - $fTotalDiscount;
    $fOrderFare_For_Commission = $fFinalTotal;
    $fCommision = setTwoDecimalPoint(($fOrderFare_For_Commission * $fCommision) / 100);
    /* Check Coupon Code For Count Total Fare Start */
    $discountValue = 0;
    $discountValueType = "cash";
    //check discount value
    $discountApplied = "No";
    $userAddressData = $obj->MySQLSelect("SELECT * FROM user_address WHERE iUserAddressId = '$iUserAddressId'");
    $userAddressLatitude = $userAddressData[0]['vLatitude'];
    $userAddressLongitude = $userAddressData[0]['vLongitude'];

    $db_companydata1 = $obj->MySQLSelect("SELECT vRestuarantLocationLat, vRestuarantLocationLong FROM company WHERE iCompanyId = '$iCompanyId'");
    $User_Address_Array = array($userAddressLatitude, $userAddressLongitude);
    $Rest_Address_Array = array($db_companydata1[0]['vRestuarantLocationLat'], $db_companydata1[0]['vRestuarantLocationLong']);
    $iLocationIdUser = GetUserGeoLocationIdPromoCode($User_Address_Array);
    $iLocationIdRest = GetUserGeoLocationIdPromoCode($Rest_Address_Array);

    if ($couponCode != '') {
        $getCouponData = $obj->MySQLSelect("SELECT fDiscount,eType,eStoreType,iCompanyId,eFreeDelivery,iLocationId FROM coupon WHERE vCouponCode='" . $couponCode . "' AND eSystemType IN ('DeliverAll','General') AND eStatus='Active' ORDER BY iCouponId ASC LIMIT 0,1");
        if (scount($getCouponData) > 0) {
            
            if($MODULES_OBJ->isEnableFreeDeliveryOrStoreSpecificPromoCode() && !empty($getCouponData[0]['eStoreType'])) {
                if($getCouponData[0]['eStoreType'] == "All") {
                    if($getCouponData[0]['eFreeDelivery'] == "Yes") {
                        $fNetTotal = $fNetTotal - $fDeliveryCharge;
                        $fDeliveryCharge = 0;    
                    }
                    $discountValue = $getCouponData[0]['fDiscount'];
                    $discountValueType = $getCouponData[0]['eType'];
                    $discountApplied = "Yes";
                }
                else {
                    if($getCouponData[0]['iCompanyId'] == $iCompanyId) {
                        if($getCouponData[0]['eFreeDelivery'] == "Yes") {
                            $fNetTotal = $fNetTotal - $fDeliveryCharge;
                            $fDeliveryCharge = 0;
                        }
                        $discountValue = $getCouponData[0]['fDiscount'];
                        $discountValueType = $getCouponData[0]['eType'];
                        $discountApplied = "Yes";
                    }
                }

                if($MODULES_OBJ->isEnableLocationWisePromoCode() && $getCouponData[0]['iLocationId'] > 0) {
                    if($eTakeAway == "No") {
                        $discountApplied = "Yes";
                        if(in_array($getCouponData[0]['iLocationId'], $iLocationIdUser) && in_array($getCouponData[0]['iLocationId'], $iLocationIdRest)) {
                            $discountValue = $getCouponData[0]['fDiscount'];
                            $discountValueType = $getCouponData[0]['eType'];                        
                        }
                        else {
                            $discountValue = 0;
                            $discountValueType = "cash";
                        }
                    }
                    else {
                        if(in_array($getCouponData[0]['iLocationId'], $iLocationIdRest)) {
                            $discountValue = $getCouponData[0]['fDiscount'];
                            $discountValueType = $getCouponData[0]['eType'];                        
                        }
                        else {
                            $discountValue = 0;
                            $discountValueType = "cash";
                        }
                    }
                }
            }

            if($MODULES_OBJ->isEnableLocationWisePromoCode() && $getCouponData[0]['iLocationId'] > 0 && $discountApplied == "No") {
                if($eTakeAway == "No") {
                    if(in_array($getCouponData[0]['iLocationId'], $iLocationIdUser) && in_array($getCouponData[0]['iLocationId'], $iLocationIdRest)) {
                        $discountValue = $getCouponData[0]['fDiscount'];
                        $discountValueType = $getCouponData[0]['eType'];
                    }
                }
                else {
                    if(in_array($getCouponData[0]['iLocationId'], $iLocationIdRest)) {
                        $discountValue = $getCouponData[0]['fDiscount'];
                        $discountValueType = $getCouponData[0]['eType'];
                    }
                }
            }

            if(!($MODULES_OBJ->isEnableFreeDeliveryOrStoreSpecificPromoCode() && $MODULES_OBJ->isEnableLocationWisePromoCode())) {
                $discountValue = $getCouponData[0]['fDiscount'];
                $discountValueType = $getCouponData[0]['eType'];
            }
        }
        //$discountValue = get_value('coupon', 'fDiscount', 'vCouponCode', $couponCode, '', 'true');
        //$discountValueType = get_value('coupon', 'eType', 'vCouponCode', $couponCode, '', 'true');
    }
    if ($couponCode != '' && $discountValue != 0) {
        if ($discountValueType == "percentage") {
            $discountApplyOn = $fNetTotal - ($fDeliveryCharge+$fTax); // Added By HJ On 27-06-2019 As Per Discuss With BM Mam // Tax Minus From Coupon Code As Per Discuss With CD sir and KS Sir On 31-01-2020
            $vDiscount = setTwoDecimalPoint($discountValue) . ' ' . "%";
            $discountValue = setTwoDecimalPoint(($discountApplyOn * $discountValue) / 100);
            //echo $discountValue;
        } else {
            //$curr_sym = get_value('currency', 'vSymbol', 'eDefault', 'Yes', '', 'true');
            if ($discountValue > $fNetTotal) {
				$vDiscount = formateNumAsPerCurrency(setTwoDecimalPoint($fNetTotal),$currencyName);
                // $vDiscount = setTwoDecimalPoint($fNetTotal) . ' ' . $currencySymbol;
            } else {
                // $vDiscount = setTwoDecimalPoint($discountValue) . ' ' . $currencySymbol;
				$vDiscount = formateNumAsPerCurrency(setTwoDecimalPoint($discountValue),$currencyName);
            }
        }
        //Added By HJ On 07-06-2019 For Convert Promocode Amount Into User Currency Ratio Start
        if ($discountValue > 0 && strtolower($discountValueType) == "cash") {
            $discountValue = $discountValue * $Ratio;
        }
        //Added By HJ On 07-06-2019 For Convert Promocode Amount Into User Currency Ratio End
        $fNetTotal =$fTotalGenerateFare= $fNetTotal - $discountValue;
        $Order_data[0]['fDiscount'] = $discountValue;
        $Order_data[0]['vDiscount'] = $vDiscount;
    }
    /* Check Coupon Code Total Fare  End */
    $fNetTotal = $fTotalGenerateFare = $fNetTotal + $tipAmount;
    /* Checking For Passenger Outstanding Amount */
    $fOutStandingAmount = GetPassengerOutstandingAmount($iUserId);
    $fOutStandingAmount = setTwoDecimalPoint($fOutStandingAmount * $Ratio);
    if ($fOutStandingAmount > 0) {
        $fNetTotal += $fOutStandingAmount;
        $fTotalGenerateFare = $fTotalGenerateFare + $fOutStandingAmount;
    }
    /*  Checking For Passenger Outstanding Amount */
    /* Check debit wallet For Count Total Order Fare Start */
    $user_wallet_debit_amount = 0;
    $DisplayCardPayment = "No";
    if ($iUserId > 0 && $CheckUserWallet == "Yes") {
        $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($iUserId, "Rider");
        //echo $user_available_balance;die;
        $user_available_balance = setTwoDecimalPoint($user_available_balance * $Ratio);
        if ($fNetTotal > $user_available_balance) {
            $fNetTotal = $fNetTotal - $user_available_balance;
            $user_wallet_debit_amount = $user_available_balance;
            $fTotalGenerateFare = $fNetTotal;
            $DisplayCardPayment = "Yes";
        } else {
            $user_wallet_debit_amount = ($fNetTotal > 0) ? $fNetTotal : 0;
            $fNetTotal =$fTotalGenerateFare= 0;
            $DisplayCardPayment = "No";
        }
    }
    //echo $user_wallet_debit_amount;die;
    //added by SP on 15-11-2019 for rounding off start
    if($fromOrder != "store"){
      
        $sqlp = "SELECT cu.vName, cu.iCurrencyId, cu.eRoundingOffEnable, cu.Ratio FROM register_user AS ru LEFT JOIN currency AS cu ON ru.vCurrencyPassenger = cu.vName WHERE ru.iUserId = '" . $iUserId . "'";
        $currData = $obj->MySQLSelect($sqlp);
        $vCurrency = $currData[0]['vName'];
        if ($currData[0]['eRoundingOffEnable'] == "Yes" && $MODULES_OBJ->isEnableRoundingMethod()) {
            $userCurrencyRatio = get_value('currency', 'Ratio', 'vName', $vCurrency, '', 'true');
            // $roundingOffTotal_fare_amountArr = getRoundingOffAmount($fNetTotal * $userCurrencyRatio, $vCurrency);
            $roundingOffTotal_fare_amountArr = getRoundingOffAmount($fNetTotal, $vCurrency);
            $fNetTotal = $fTotalGenerateFare = $roundingOffTotal_fare_amountArr['finalFareValue'];
            if ($roundingOffTotal_fare_amountArr['method'] == "Addition") {
                $eRoundingType = "Addition";
            } else {
                $eRoundingType = "Substraction";
            }
            $fRoundingAmount = setTwoDecimalPoint($roundingOffTotal_fare_amountArr['differenceValue']);
            $fRoundingAmount = $fRoundingAmount;
            $eRoundingType = $eRoundingType;
        }
    }
   

    /* Check debit wallet For Count Total Order Fare End */
    if ($fNetTotal < 0) {
        $fNetTotal = $fTotalGenerateFare = 0;
    }
    $Data['optionaddonname'] = $showfoptionaddonname;
    $Data['showfPrice'] = $showfPrice;
    // $Data['fSubTotal'] = $currencySymbol . " " . formatnum($fFinalTotal);
    // $Data['fTotalDiscount'] = $currencySymbol . " " . formatnum($fTotalDiscount);
	$Data['fSubTotal'] = formateNumAsPerCurrency(formatnum($fFinalTotal),$currencyName);
    $Data['fTotalDiscount'] = formateNumAsPerCurrency(formatnum($fTotalDiscount),$currencyName);
    $Data['totalDiscount'] = $fTotalDiscount;
    $fPackingCharge = setTwoDecimalPoint($fPackingCharge);
    $Data['PackingCharge'] = $fPackingCharge;
    // $Data['fPackingCharge'] = ($fPackingCharge > 0) ? $currencySymbol . " " . formatnum($fPackingCharge) : 0;
	$Data['fPackingCharge'] = ($fPackingCharge > 0) ? formateNumAsPerCurrency(formatnum($fPackingCharge),$currencyName) : 0;
    $fDeliveryCharge = setTwoDecimalPoint($fDeliveryCharge);
    $Data['DeliveryCharge'] = $fDeliveryCharge;
    // $Data['fDeliveryCharge'] = ($fDeliveryCharge > 0) ? $currencySymbol . " " . formatnum($fDeliveryCharge) : 0;
	$Data['fDeliveryCharge'] = ($fDeliveryCharge > 0) ? formateNumAsPerCurrency(formatnum($fDeliveryCharge),$currencyName) : 0;
    $fTax = setTwoDecimalPoint($fTax);
    $Data['tax'] = $fTax;
	$Data['fTax'] = ($fTax > 0) ? formateNumAsPerCurrency(formatnum($fTax),$currencyName) : 0;
    // $Data['fTax'] = ($fTax > 0) ? $currencySymbol . " " . formatnum($fTax) : 0;
    $fDiscount_Val = 0;
    if (isset($Order_data[0]['fDiscount']) && $Order_data[0]['fDiscount'] > 0) {
        $fDiscount_Val = setTwoDecimalPoint($Order_data[0]['fDiscount']);
    }
    $Data['Discount_Val'] = $fDiscount_Val;
    // $Data['fDiscount'] = ($fDiscount_Val > 0) ? $currencySymbol . " " . $fDiscount_Val : 0;
	$Data['fDiscount'] = ($fDiscount_Val > 0) ? formateNumAsPerCurrency($fDiscount_Val,$currencyName) : 0;
    $Data['CompanyTax'] = $fCompanyTax;
    $Data['OutStandingAmount'] = $fOutStandingAmount;
    //$Data['fCompanyTax'] = ($fCompanyTax > 0) ? $currencySymbol . " " . formatnum($fCompanyTax) : 0;
    // $Data['vDiscount'] = $Order_data[0]['vDiscount'];
    $fCommision = setTwoDecimalPoint($fCommision);
    // $Data['fCommision'] = ($fCommision > 0) ? $currencySymbol . " " . formatnum($fCommision) : 0;
	$Data['fCommision'] = ($fCommision > 0) ? formateNumAsPerCurrency(formatnum($fCommision),$currencyName) : 0;
    if (isset($fRoundingAmount) && !empty($fRoundingAmount) && $fRoundingAmount != 0 && $currData[0]['eRoundingOffEnable'] == "Yes" && $MODULES_OBJ->isEnableRoundingMethod()) {
        $Data['RoundingAmount'] = $fRoundingAmount;
        $Data['fRoundingAmount'] = ($fRoundingAmount > 0) ? formateNumAsPerCurrency(formatnum($fRoundingAmount),$currencyName) : 0;
    }
    $fNetTotal = setTwoDecimalPoint($fNetTotal);
    // $Data['fNetTotal'] = ($fNetTotal > 0) ? $currencySymbol . " " . formatnum($fNetTotal) : $currencySymbol . " 0";
	$Data['fNetTotal'] = ($fNetTotal > 0) ? formateNumAsPerCurrency(formatnum($fNetTotal),$currencyName) : formateNumAsPerCurrency(0,$currencyName);
    $Data['fNetTotalAmount'] = $fNetTotal;
    $fTotalGenerateFare = setTwoDecimalPoint($fTotalGenerateFare);
    $Data['GenerateFare'] = $fTotalGenerateFare;
    // $Data['fTotalGenerateFare'] = ($fTotalGenerateFare > 0) ? $currencySymbol . " " . formatnum($fTotalGenerateFare) : $currencySymbol . " 0";
	$Data['fTotalGenerateFare'] = ($fTotalGenerateFare > 0) ? formateNumAsPerCurrency(formatnum($fTotalGenerateFare),$currencyName) : formateNumAsPerCurrency(0,$currencyName);
    $Data['fTotalGenerateFareAmount'] = $fTotalGenerateFare;
    // $Data['fOutStandingAmount'] = ($fOutStandingAmount > 0) ? $currencySymbol . " " . formatnum($fOutStandingAmount) : $currencySymbol . " 0";
    // $Data['fWalletDebit'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : $currencySymbol . " 0";
	$Data['fOutStandingAmount'] = ($fOutStandingAmount > 0) ? formateNumAsPerCurrency(formatnum($fOutStandingAmount),$currencyName) : formateNumAsPerCurrency(0,$currencyName);
    // $Data['fWalletDebit'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : $currencySymbol . " 0";
	$Data['fWalletDebit'] = ($user_wallet_debit_amount > 0) ? formateNumAsPerCurrency(formatnum($user_wallet_debit_amount),$currencyName) : formateNumAsPerCurrency(0,$currencyName);
    $Data['user_wallet_debit_amount'] = $user_wallet_debit_amount;
    $Data['currencySymbol'] = $currencySymbol;
    $Data['DisplayCardPayment'] = $DisplayCardPayment;
    // $Data['DisplayUserWalletDebitAmount'] = ($user_wallet_debit_amount > 0) ? $currencySymbol . " " . formatnum($user_wallet_debit_amount) : "";
	$Data['DisplayUserWalletDebitAmount'] = ($user_wallet_debit_amount > 0) ? formateNumAsPerCurrency(formatnum($user_wallet_debit_amount),$currencyName) : "";
    $Data['DISABLE_CASH_PAYMENT_OPTION'] = ($fOutStandingAmount > 0) ? "Yes" : "No";
    $arrindex = 0;
    if ($fTotalDiscount > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fTotalDiscount);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT']] = "- " . formateNumAsPerCurrency(formatnum($fTotalDiscount),$currencyName);
        $arrindex++;
    }
    if ($fPackingCharge > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = $currencySymbol . " " . formatnum($fPackingCharge);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_PACKING_CHARGE']] = formateNumAsPerCurrency(formatnum($fPackingCharge),$currencyName);
        $arrindex++;
    }
    if ($fDeliveryCharge > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = $currencySymbol . " " . formatnum($fDeliveryCharge);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DELIVERY_CHARGES_TXT']] = formateNumAsPerCurrency(formatnum($fDeliveryCharge),$currencyName);
        $arrindex++;
    }
    if ($fTax > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = $currencySymbol . " " . formatnum($fTax);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_TAX_TXT']] = formateNumAsPerCurrency(formatnum($fTax),$currencyName);
        $arrindex++;
    }
    if ($fOutStandingAmount > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fOutStandingAmount);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT']] = formateNumAsPerCurrency(formatnum($fOutStandingAmount),$currencyName);
        $arrindex++;
    }

    if ($fDiscount_Val > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "- " . $currencySymbol . " " . formatnum($fDiscount_Val);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_DISCOUNT_TXT']] = "- " . formateNumAsPerCurrency(formatnum($fDiscount_Val),$currencyName);
        $arrindex++;
    }
    if ($user_wallet_debit_amount > 0) {
        // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . $currencySymbol . " " . formatnum($user_wallet_debit_amount);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_WALLET_ADJUSTMENT']] = "- " . formateNumAsPerCurrency(formatnum($user_wallet_debit_amount),$currencyName);
        $arrindex++;
    }
    //added by SP on 15-11-2019 for rounding off start
    //if($currData[0]['eRoundingOffEnable'] == "Yes" && ){
  
    if (isset($fRoundingAmount) && !empty($fRoundingAmount) && $fRoundingAmount != 0 && $currData[0]['eRoundingOffEnable'] == "Yes" && $MODULES_OBJ->isEnableRoundingMethod()) {
        $fRoundingAmount = $fRoundingAmount;
        $eRoundingType = $eRoundingType;
        if ($eRoundingType == "Addition") {
            $roundingMethod = "";
            $Data['RoundingMethod'] = "";
        } else {
            $roundingMethod = "-";
            $Data['RoundingMethod'] = "-";
        }
        $rounding_diff = isset($roundingOffTotal_fare_amountArr['differenceValue']) && $roundingOffTotal_fare_amountArr['differenceValue'] != '' ? $roundingOffTotal_fare_amountArr['differenceValue'] : "0.00";
        $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_ROUNDING_DIFF_TXT']] = $roundingMethod . " " . formateNumAsPerCurrency($fRoundingAmount,$currencyName);
         $arrindex++;
    }
    /* if ($fTotalGenerateFare > 0) { */
    // $OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']] = $currencySymbol . " " . formatnum($fTotalGenerateFare);
	$OrderFareDetailsArr[$arrindex][$languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT']] = formateNumAsPerCurrency(formatnum($fTotalGenerateFare),$currencyName);
    $arrindex++;
}
$Data['fsubTotallabel'] = $languageLabelsArr['LBL_SUBTOTAL_APP_TXT'];
$Data['fTotalDiscountlabel'] = $languageLabelsArr['LBL_OFFERS_DISCOUNT_TXT'];
$Data['fPackinlabel'] = $languageLabelsArr['LBL_PACKING_CHARGE'];
$Data['fDeliverylabel'] = $languageLabelsArr['LBL_DELIVERY_CHARGES_TXT'];
//$Data['fCompanytaxlabel'] =$languageLabelsArr['LBL_INFO_COMPANY_TAX_TXT'];
$Data['fTaxlabel'] = $languageLabelsArr['LBL_TOTAL_TAX_TXT'];
$Data['fOutStandinglabel'] = $languageLabelsArr['LBL_OUTSTANDING_AMOUNT_TXT'];
$Data['fDiscount_Vallabel'] = $languageLabelsArr['LBL_DISCOUNT_TXT'];
$Data['fRoundingAmount_label'] = $languageLabelsArr['LBL_ROUNDING_DIFF_TXT'];
$Data['fdebitlabel'] = $languageLabelsArr['LBL_WALLET_ADJUSTMENT'];
$Data['fTotalGeneratelabel'] = $languageLabelsArr['LBL_TOTAL_BILL_AMOUNT_TXT'];
$storeDataArr = array();
$restaurant_status_arr = GetStoreWorkingHoursDetails($iCompanyId, $iUserId,$vLang,$languageLabelsArr,$storeDataArr);
$Data['restaurantstatus'] = $restaurant_status_arr['restaurantstatus'];
$Data['Ordercounters'] = $OrderDetailscount;
$Data['FareDetailsArr'] = $OrderFareDetailsArr;
$Data['ToTalAddress'] = FetchTotalMemberAddress($iUserId, "Passenger", $passengerLat, $passengerLon, $iCompanyId);
$Data['vCompany'] = $vCompany;
$Data['vCaddress'] = $vCaddress;
$Data['vImage'] = $db_companydata[0]['vImage'];
$Data['vRestuarantLocation'] = $vRestuarantLocation;
$Data['iMaxItemQty'] = $iMaxItemQty;
$Data['TotaliQty'] = $TotaliQty;
$Data['fFinalTotal'] = $fFinalTotal;
$returnArr = $Data;
$returnArr['Action'] = "1";

//echo "<pre>";print_r($returnArr);die;
echo json_encode($returnArr);
exit;
?>