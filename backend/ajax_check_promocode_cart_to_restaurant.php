<?php

include_once('common.php');


$fromOrder = "guest";
if (isset($_REQUEST['fromorder']) && $_REQUEST['fromorder'] != "") {
    $fromOrder = $_REQUEST['fromorder'];
}
$orderServiceSession = "MAUAL_ORDER_SERVICE_" . strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_" . strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_" . strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_" . strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_" . strtoupper($fromOrder);
$orderStoreIdSession = "MANUAL_ORDER_STORE_ID_" . strtoupper($fromOrder);

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
$vLang = $_SESSION['sess_lang'];
$promoCode = isset($_REQUEST['couponCode']) ? clean($_REQUEST['couponCode']) : '';
$iUserAddressId = isset($_REQUEST['iUserAddressId']) ? clean($_REQUEST['iUserAddressId']) : '';
$eTakeAway = isset($_REQUEST['eTakeAway']) ? clean($_REQUEST['eTakeAway']) : 'No';
//$UserDetailsArr = getUserCurrencyLanguageDetails($iUserId);
//$Ratio = $UserDetailsArr['Ratio'];
$curr_date = @date("Y-m-d");
$promoCode = strtoupper($promoCode);
$langage_lbl = $LANG_OBJ->FetchLanguageLabels($vLang, $iServiceId);
    
$_REQUEST['eType'] = 'DeliverAll';
$_REQUEST['iUserId'] = $iUserId;
$_REQUEST['vCouponCode'] = $promoCode;
if(empty($promoCode) || $promoCode == ""){
    $returnArr['Action'] = "0"; // code is invalid
    $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderCouponNameSession]);
    echo json_encode($returnArr);exit;
}else{
    $validPromoCodesArr = getValidPromoCodes();
    // echo $_SESSION[$orderStoreIdSession]."<pre>";print_r($validPromoCodesArr);
    if (!empty($validPromoCodesArr) && !empty($validPromoCodesArr['CouponList']) && scount($validPromoCodesArr['CouponList']) > 0) {
        $returnArr['Action'] = "1"; // code is valid
        $returnArr["message"] = $langage_lbl["LBL_PROMO_APPLIED"];
        $returnArr["discountValueType"] = $validPromoCodesArr['CouponList'][0]['eType'];
        $returnArr["discountValue"] = $validPromoCodesArr['CouponList'][0]['fDiscount'];

        if($MODULES_OBJ->isEnableFreeDeliveryOrStoreSpecificPromoCode() && !empty($validPromoCodesArr['CouponList'][0]['eStoreType'])) {
            if($validPromoCodesArr['CouponList'][0]['eStoreType'] == "StoreSpecific") {
                if($validPromoCodesArr['CouponList'][0]['iCompanyId'] != $_SESSION[$orderStoreIdSession]) {
                    $returnArr['Action'] = "0"; // code is invalid
                    $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
                    unset($_SESSION[$orderCouponSession]);
                    unset($_SESSION[$orderCouponNameSession]);
                    echo json_encode($returnArr);
                    exit;
                }
                
                if($MODULES_OBJ->isEnableLocationWisePromoCode() && $validPromoCodesArr['CouponList'][0]['iLocationId'] > 0) {
                    $userAddressData = $obj->MySQLSelect("SELECT * FROM user_address WHERE iUserAddressId = '$iUserAddressId'");
                    $userAddressLatitude = $userAddressData[0]['vLatitude'];
                    $userAddressLongitude = $userAddressData[0]['vLongitude'];

                    $iCompanyId = $_SESSION[$orderStoreIdSession];
                    $db_companydata = $obj->MySQLSelect("SELECT vRestuarantLocationLat, vRestuarantLocationLong FROM company WHERE iCompanyId = '$iCompanyId'");

                    $User_Address_Array = array($userAddressLatitude, $userAddressLongitude);
                    $Rest_Address_Array = array($db_companydata[0]['vRestuarantLocationLat'], $db_companydata[0]['vRestuarantLocationLong']);
                    $iLocationIdUser = GetUserGeoLocationIdPromoCode($User_Address_Array);
                    $iLocationIdRest = GetUserGeoLocationIdPromoCode($Rest_Address_Array);

                    if(!in_array($validPromoCodesArr['CouponList'][0]['iLocationId'], $iLocationIdUser) || !in_array($validPromoCodesArr['CouponList'][0]['iLocationId'], $iLocationIdRest)) {
                        $returnArr['Action'] = "0"; // code is invalid
                        $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
                        unset($_SESSION[$orderCouponSession]);
                        unset($_SESSION[$orderCouponNameSession]);
                        echo json_encode($returnArr);exit;
                    }
                }
            }
        }

        if($MODULES_OBJ->isEnableLocationWisePromoCode() && !empty($iUserAddressId) && $validPromoCodesArr['CouponList'][0]['iLocationId'] > 0) {
            $userAddressData = $obj->MySQLSelect("SELECT * FROM user_address WHERE iUserAddressId = '$iUserAddressId'");
            $userAddressLatitude = $userAddressData[0]['vLatitude'];
            $userAddressLongitude = $userAddressData[0]['vLongitude'];

            $iCompanyId = $_SESSION[$orderStoreIdSession];
            $db_companydata = $obj->MySQLSelect("SELECT vRestuarantLocationLat, vRestuarantLocationLong FROM company WHERE iCompanyId = '$iCompanyId'");

            $User_Address_Array = array($userAddressLatitude, $userAddressLongitude);
            $Rest_Address_Array = array($db_companydata[0]['vRestuarantLocationLat'], $db_companydata[0]['vRestuarantLocationLong']);
            $iLocationIdUser = GetUserGeoLocationIdPromoCode($User_Address_Array);
            $iLocationIdRest = GetUserGeoLocationIdPromoCode($Rest_Address_Array);

            if($eTakeAway == "No") {
                if(!in_array($validPromoCodesArr['CouponList'][0]['iLocationId'], $iLocationIdUser) || !in_array($validPromoCodesArr['CouponList'][0]['iLocationId'], $iLocationIdRest)) {
                    $returnArr['Action'] = "0"; // code is invalid
                    $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
                    unset($_SESSION[$orderCouponSession]);
                    unset($_SESSION[$orderCouponNameSession]);
                    echo json_encode($returnArr);exit;
                }
            }
            else {
                if(!in_array($validPromoCodesArr['CouponList'][0]['iLocationId'], $iLocationIdRest)) {
                    $returnArr['Action'] = "0"; // code is invalid
                    $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
                    unset($_SESSION[$orderCouponSession]);
                    unset($_SESSION[$orderCouponNameSession]);
                    echo json_encode($returnArr);exit;
                }
            }
        }
        $_SESSION[$orderCouponSession] = $promoCode;
        echo json_encode($returnArr);exit;
    } else {
        $returnArr['Action'] = "0"; // code is invalid
        $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
        unset($_SESSION[$orderCouponSession]);
        unset($_SESSION[$orderCouponNameSession]);
        echo json_encode($returnArr);exit;
    }
}
exit;
// $sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '".$promoCode."' AND iUsageLimit > iUsed AND (eValidityType = 'Permanent' OR dExpiryDate > '$curr_date')";
// $sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '".$promoCode."' AND iUsageLimit > iUsed ORDER BY iCouponId ASC LIMIT 0,1";
$sql = "SELECT * FROM coupon where eStatus = 'Active' AND vCouponCode = '" . $promoCode . "' AND eSystemType IN ('DeliverAll','General') ORDER BY iCouponId ASC LIMIT 0,1";
$data = $obj->MySQLSelect($sql);
if (scount($data) > 0) {
    $discountValueType = $data[0]['eType'];
    $discountValue = $data[0]['fDiscount'];
    $discountValue = round(($discountValue * $Ratio), 2);
    $sql = "select iOrderId from orders where vCouponCode = '" . $promoCode . "' and iStatusCode NOT IN(11,12) and iUserId='$iUserId'";
    $data_coupon = $obj->MySQLSelect($sql);
    if (!empty($data_coupon)) {
        $returnArr['Action'] = "0"; // code is already used one time
        $returnArr["message"] = $langage_lbl["LBL_PROMOCODE_ALREADY_USED"];
        echo json_encode($returnArr);
        exit;
    } else {
        $eValidityType = $data[0]['eValidityType'];
        $iUsageLimit = $data[0]['iUsageLimit'];
        $iUsed = $data[0]['iUsed'];
        if ($iUsageLimit <= $iUsed) {
            $returnArr['Action'] = "0"; // code is invalid due to Usage Limit
            $returnArr["message"] = $langage_lbl["LBL_PROMOCODE_COMPLETE_USAGE_LIMIT"];
            unset($_SESSION[$orderCouponSession]);
            unset($_SESSION[$orderCouponNameSession]);
            echo json_encode($returnArr);
            exit;
        }
        if ($eValidityType == "Permanent") {
            $returnArr['Action'] = "1"; // code is valid
            $returnArr["message"] = $langage_lbl["LBL_PROMO_APPLIED"];
            $returnArr["discountValueType"] = $discountValueType;
            $returnArr["discountValue"] = $discountValue;
            $_SESSION[$orderCouponSession] = $promoCode;
            echo json_encode($returnArr);
            exit;
        } else {
            $dActiveDate = $data[0]['dActiveDate'];
            $dExpiryDate = $data[0]['dExpiryDate'];
            if ($dActiveDate <= $curr_date && $dExpiryDate >= $curr_date) {
                $returnArr['Action'] = "1"; // code is valid
                $returnArr["message"] = $langage_lbl["LBL_PROMO_APPLIED"];
                $returnArr["discountValueType"] = $discountValueType;
                $returnArr["discountValue"] = $discountValue;
                $_SESSION[$orderCouponSession] = $promoCode;
                echo json_encode($returnArr);
                exit;
            } else {
                $returnArr['Action'] = "0"; // code is invalid due to expiration
                $returnArr["message"] = $langage_lbl["LBL_PROMOCODE_EXPIRED"];
                unset($_SESSION[$orderCouponSession]);
                unset($_SESSION[$orderCouponNameSession]);
                echo json_encode($returnArr);
                exit;
            }
        } // languageLabelsArr[
    }
} else {
    $returnArr['Action'] = "0"; // code is invalid
    // $returnArr['Action']="01";// code is used by this user
    $returnArr["message"] = $langage_lbl["LBL_INVALID_PROMOCODE"];
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderCouponNameSession]);
    echo json_encode($returnArr);
    exit;
}
//  echo json_encode($returnArr); exit;
?>