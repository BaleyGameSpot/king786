<?php
include_once("common.php");
$confirlAlert = 0;
GetPagewiseSessionMemberType('restaurant_listing');
$fromOrder = "guest";
if (isset($_REQUEST['order']) && $_REQUEST['order'] != ""){
    $fromOrder = $_REQUEST['order'];
}
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == "driver" && !isset($_SESSION['sess_iAdminUserId'])){
    header('Location:profile');
}
$orderDetailsSession = "ORDER_DETAILS_".strtoupper($fromOrder);
$orderServiceSession = "MAUAL_ORDER_SERVICE_".strtoupper($fromOrder);
$orderUserIdSession = "MANUAL_ORDER_USERID_".strtoupper($fromOrder);
$orderAddressIdSession = "MANUAL_ORDER_ADDRESSID_".strtoupper($fromOrder);
$orderAddressSession = "MANUAL_ORDER_ADDRESS_".strtoupper($fromOrder);
$orderCouponSession = "MANUAL_ORDER_PROMOCODE_".strtoupper($fromOrder);
$orderCouponNameSession = "MANUAL_ORDER_PROMOCODE_NAME_".strtoupper($fromOrder);
$orderLatitudeSession = "MANUAL_ORDER_LATITUDE_".strtoupper($fromOrder);
$orderLongitudeSession = "MANUAL_ORDER_LONGITUDE_".strtoupper($fromOrder);
$orderServiceNameSession = "MANUAL_ORDER_SERVICE_NAME_".strtoupper($fromOrder);
$orderDataSession = "MANUAL_ORDER_DATA_".strtoupper($fromOrder);
$vLang = "EN";
if (isset($_SESSION['sess_lang'])){
    $vLang = $_SESSION['sess_lang'];
}
$vLangSql = "SELECT eDirectionCode FROM language_master WHERE vCode = '".$vLang."'";
$vLangData = $obj->MySQLSelect($vLangSql);
$eDirectionCode = $vLangData[0]['eDirectionCode'];
if (isset($_REQUEST['sid']) && $_REQUEST['sid'] > 0){
    unset($_SESSION[$orderDetailsSession]);
    unset($_SESSION[$orderDataSession]);
    unset($_SESSION[$orderServiceSession]);
    unset($_SESSION[$orderCouponSession]);
    unset($_SESSION[$orderCouponNameSession]);
    unset($_SESSION[$orderServiceNameSession]);
    $_SESSION[$orderServiceSession] = $_REQUEST['sid'];
    $service_categories = array();
    if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)){
        $service_categories = $serviceCategoriesTmp;
        $_SESSION[$orderServiceNameSession] = $service_categories[($_SESSION[$orderServiceSession] - 1)]['vServiceName'];
    }
    $_SESSION[$orderServiceNameSession] = $service_categories[($_SESSION[$orderServiceSession] - 1)]['vServiceName'];
}
//echo "<pre>";print_r($_SESSION);die;
//echo $selServiceName;die;
$meta = $STATIC_PAGE_OBJ->FetchStaticPage(1,$vLang);
$_SESSION['sess_language'] = $vLang;
if (!isset($_SESSION[$orderServiceSession]) || empty($_SESSION[$orderServiceSession]) || !in_array($_SESSION[$orderServiceSession],$service_categories_ids_arr)){
    unset($_SESSION[$orderServiceSession]);
    header("location:user-order-information?order=".$fromOrder);
    exit;
}else{
    $iServiceId = $_SESSION[$orderServiceSession];
}
if ($MODULES_OBJ->isSingleStoreSelection() && $fromOrder != "admin"){
    $service_categories = array();
    if (isset($serviceCategoriesTmp) && !empty($serviceCategoriesTmp)){
        $service_categories = $serviceCategoriesTmp;
    }
    //$cnt_sc = scount($service_categories);
    //if($cnt_sc==1) {
    session_start();
    $store_data = getStoreDataForSystemStoreSelection($iServiceId);
    //$iCompanyId = $store_data[0]['iCompanyId'];
    $iCompanyId = $store_data['iCompanyId'];
    $_SESSION[$orderLongitudeSession] = $store_data['vRestuarantLocationLat'];
    $_SESSION[$orderLatitudeSession] = $store_data['vRestuarantLocationLong'];
    $_SESSION[$orderServiceSession] = $store_data['iServiceId'];
    $_SESSION[$orderAddressSession] = $store_data['vCaddress'];
    $_SESSION[$orderServiceNameSession] = $service_categories[($store_data['iServiceId'] - 1)]['vServiceName'];
    header("location: store-items?id=".$iCompanyId."&order=".$fromOrder);
    exit;
    //}
}
global $intervalmins;
//$vTimeZone = "Asia/Kolkata";
$vTimeZone = date_default_timezone_get();
$cmpMinutes = ceil((fetchtripstatustimeMAXinterval() + $intervalmins) / 60);
$str_date = @date('Y-m-d H:i:s',strtotime('-'.$cmpMinutes.' minutes'));
//$LIST_RESTAURANT_LIMIT_BY_DISTANCE = $CONFIG_OBJ->getConfigurations("configurations", "LIST_RESTAURANT_LIMIT_BY_DISTANCE");
//$DRIVER_REQUEST_METHOD = $CONFIG_OBJ->getConfigurations("configurations", "DRIVER_REQUEST_METHOD");
$param = ($DRIVER_REQUEST_METHOD == "Time")?"tOnline":"tLastOnline";
$script = "Restaurant";
$vServiceAddress = 0;
$vLatitude = $vLongitude = $iUserId = $iUserAddressId = "";
if (isset($_SESSION[$orderUserIdSession])){
    $iUserId = $_SESSION[$orderUserIdSession];
}
if (isset($_SESSION[$orderAddressIdSession])){
    $iUserAddressId = $_SESSION[$orderAddressIdSession];
}
if (isset($_SESSION[$orderAddressSession])){
    $vServiceAddress = $fulladdress = $_SESSION[$orderAddressSession];
}
if (isset($_SESSION[$orderLatitudeSession])){
    $vLatitude = $_SESSION[$orderLatitudeSession];
}
if (isset($_SESSION[$orderLongitudeSession])){
    $vLongitude = $_SESSION[$orderLongitudeSession];
}
//echo "<pre>";print_r($_SESSION);die;
$languageLabelsArr = $LANG_OBJ->FetchLanguageLabels($vLang,"1",$iServiceId);
$selServiceName = "";
$atText = $languageLabelsArr['LBL_AT_TXT'];
if (isset($_SESSION[$orderServiceNameSession]) && $_SESSION[$orderServiceNameSession] != ""){
    $selServiceName = $_SESSION[$orderServiceNameSession]." ".$atText." - ";
}
if (!empty($iUserId) && empty($vLongitude) && empty($vLatitude) && !empty($iUserAddressId)){
    if (empty($iUserId) || empty($iUserAddressId)){
        header("location:user-order-information?error=1&var_msg=".$languageLabelsArr['LBL_NO_USER_ADDRESS_FOUND']."&order=".$fromOrder);
        exit;
    }
    $Dataua = $obj->MySQLSelect("SELECT *  FROM `user_address`  WHERE iUserAddressId = '".$iUserAddressId."' AND iUserId = '".$iUserId."'");
    //print_r($iUserId);die;
    if (scount($Dataua) > 0){
        $vBuildingNo = $Dataua[0]['vBuildingNo'];
        $vLandmark = $Dataua[0]['vLandmark'];
        $vAddressType = $Dataua[0]['vAddressType'];
        $a = $b = '';
        if ($vBuildingNo != ''){
            $a = ucfirst_multibyte($vBuildingNo).", ";
        }
        if ($vLandmark != ''){
            $b = ucfirst_multibyte($vLandmark).", ";
        }
        $fulladdress = $a."".$b."".$Dataua[0]['vServiceAddress'];
        $vServiceAddress = ucfirst_multibyte($Dataua[0]['vServiceAddress']);
        $vLatitude = $Dataua[0]['vLatitude'];
        $vLongitude = $Dataua[0]['vLongitude'];
        $vTimeZone = $Dataua[0]['vTimeZone'];
    }
}
$sourceLocationArr = array($vLatitude,
                           $vLongitude);
$allowed_ans = checkAreaRestriction($sourceLocationArr,"No");
$checkUser = GetSessionMemberType();
$checkFavStore = $MODULES_OBJ->isFavouriteStoreModuleAvailable();
$sql_query = $ssql = $leftjoinsql = "";
if (($checkUser == 'rider' || strtolower($checkUser) == "user") && !empty($iUserId) && $checkFavStore == 1){
    include "include/features/include_fav_store.php";
    $sql_query = getFavSelectQuery('',$iUserId);
}
$tsite_url = $tconfig['tsite_url'];
if (strtoupper(ONLYDELIVERALL) == 'YES'){
    $redirect_location = $tsite_url.'?order='.$fromOrder;
}else{
    $redirect_location = $tsite_url.'order-items?order='.$fromOrder;
}
if (strtolower($checkUser) == 'store' || strtolower($checkUser) == 'admin'){
    $redirect_location = $tsite_url.'user-order-information?order='.$fromOrder;
}
$having_ssql = "";
if (SITE_TYPE == "Demo" && $searchword == ""){
    $vAddress = $vServiceAddress;
    $having_ssql .= " OR company.eDemoDisplay = 'Yes'";
    if ($vAddress != ""){
        // $ssql .= " AND ( company.vRestuarantLocation like '%$vAddress%' OR company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
    }else{
        // $ssql .= " AND ( company.vRestuarantLocation like '%India%' OR company.eDemoDisplay = 'Yes')";
    }
}
$postcuisineIds = (isset($_POST['cuisine_type']))?$_POST['cuisine_type']:"";
$postoffers = (isset($_POST['offers']))?$_POST['offers']:"";
$postfavStore = (isset($_POST['favStore']))?$_POST['favStore']:"";
$iServiceIdDef = $iServiceId;
$filter = isset($_POST['filter'])?$_POST['filter']:0;
if ($filter == 1){
    $cuisine_types = (scount($_POST['cuisine_type']))?$_POST['cuisine_type']:"";
    if ($cuisine_types != ""){
        $cuisine_types = "'".implode("','",$cuisine_types)."'";
        $cuisinesql = "select cuisineId from cuisine where cuisineName in (".$cuisine_types.")";
        $cuisineData = $obj->MySQLSelect($cuisinesql);
        $cuisineIds = array();
        foreach ($cuisineData as $cvalue){
            $cuisineIds[] = $cvalue['cuisineId'];
        }
        $cuisineIds = implode(',',$cuisineIds);
        $leftjoinsql .= " LEFT JOIN company_cuisine on company.iCompanyId = company_cuisine.iCompanyId";
        $ssql .= " AND company_cuisine.cuisineId IN (".$cuisineIds.")";
    }
    if ($postoffers != ""){
        $ssql .= " AND fOfferAppyType != 'None'";
    }
}
$ssql1 = " AND company.eBuyAnyService = 'No' ";
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()){
    $fsql = " AND (FIND_IN_SET('".$iServiceId."', company.iServiceId) OR FIND_IN_SET('".$iServiceId."', company.iServiceIdMulti) )";
}else{
    $fsql = " AND iServiceId = '".$iServiceId."' ";
}
$sql = "SELECT DISTINCT (company.iCompanyId),ROUND(( 6371 * acos( cos( radians(".$vLatitude.") ) * cos( radians( vRestuarantLocationLat ) ) * cos( radians( vRestuarantLocationLong ) - radians(".$vLongitude.") ) + sin( radians(".$vLatitude.") ) * sin( radians( vRestuarantLocationLat ) ) ) ),2) AS distance, company.* ".$sql_query." FROM `company` $leftjoinsql WHERE vRestuarantLocationLat != '' AND vRestuarantLocationLong != '' AND eStatus='Active' AND eSystem = 'DeliverAll' $fsql $ssql $ssql1 HAVING (distance < ".$LIST_RESTAURANT_LIMIT_BY_DISTANCE.$having_ssql.") ORDER BY `company`.`iCompanyId` ASC";
$Data = $obj->MySQLSelect($sql);
// echo "<pre>"; print_r($sql); exit;
$storeIdArr = array();
if (scount($Data) > 0){
    for ($ii = 0;$ii < scount($Data);$ii++){
        $iCompanyId = $Data[$ii]['iCompanyId'];
        array_push($storeIdArr,$iCompanyId);
    }
}
$iToLocationId = GetUserGeoLocationId($sourceLocationArr);
$storeDetails = getStoreDetails($storeIdArr,$iUserId,$iToLocationId,$languageLabelsArr);
for ($rs = 0;$rs < scount($Data);$rs++){
    $restStatus = "closed";
    if (isset($storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status']) && $storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status'] != ""){
        $restStatus = strtolower($storeDetails['restaurantStatusArr'][$Data[$rs]['iCompanyId']]['status']);
    }
    $Data[$rs]['restaurantstatus'] = $restStatus;
    $vAvgRating = $Data[$rs]['vAvgRating'];
    $Data[$rs]['vAvgRating'] = ($vAvgRating > 0)?number_format($Data[$rs]['vAvgRating'],1):number_format(0,1);
    $Data[$rs]['vAvgRatingOrig'] = $Data[$rs]['vAvgRating'];
}
for ($rs = 0;$rs < scount($Data);$rs++){
    $fsql1 = "";
    if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()){
        $fsql1 = " AND iServiceId = '$iServiceId'";
    }
    $getFooMenu = $obj->MySQLSelect("SELECT GROUP_CONCAT(iFoodMenuId) as foodMenuIds FROM food_menu WHERE iCompanyId = '".$Data[$rs]['iCompanyId']."' AND eStatus = 'Active' $fsql1");
    if (!empty($getFooMenu[0]['foodMenuIds'])){
        $getStoreItems = $obj->MySQLSelect("SELECT COUNT(iMenuItemId) as menuItemCount FROM menu_items WHERE iFoodMenuId IN (".$getFooMenu[0]['foodMenuIds'].") AND eStatus = 'Active'");
        if ($getStoreItems[0]['menuItemCount'] == 0){
            unset($Data[$rs]);
        }
    }else{
        unset($Data[$rs]);
    }
}
$Data = array_values($Data);
//echo "<pre>";print_r($storeDetails);die;
//Added By HJ On 22-06-2019 For Get Store By Filter Start
if (isset($_POST["sortby"])){
    $sortby = $_POST["sortby"];
}else{
    if (isset($_COOKIE['store_sortby'])){
        $sortby = $_COOKIE["store_sortby"];
    }else{
        $sortby = "relevance";
    }
}
if ($sortby == "" || $sortby == NULL){
    $sortby = "relevance";
}
if ($sortby == "rating"){
    $sortfield = "vAvgRatingOrig";
    $sortorder = SORT_DESC;
}elseif ($sortby == "time"){
    $sortfield = "fPrepareTime";
    $sortorder = SORT_ASC;
}elseif ($sortby == "costlth"){
    $sortfield = "fPricePerPerson";
    $sortorder = SORT_ASC;
}elseif ($sortby == "costhtl"){
    $sortfield = "fPricePerPerson";
    $sortorder = SORT_DESC;
}else{
    $sortfield = "restaurantstatus";
    $sortorder = SORT_DESC;
}

if(!empty($Data)){
    foreach ($Data as $k => $v){
        $Data_name[$sortfield][$k] = $v[$sortfield];
        $Data_name['restaurantstatus'][$k] = $v['restaurantstatus'];
    }
    array_multisort($Data_name['restaurantstatus'],SORT_DESC,$Data_name[$sortfield],$sortorder,$Data);
    $Data = array_values($Data);
}

if ($postfavStore != ""){
    $favStoreData = array();
    foreach ($Data as $fkey => $fvalue){
        if ($fvalue['eFavStore'] == "Yes"){
            $favStoreData[] = $fvalue;
        }
    }
    $Data = $favStoreData;
}
if ($MODULES_OBJ->isStoreClassificationEnable()){
    // Store Categories
    $CategoryWiseStores = array();
    foreach ($Data as $dkey => $dvalue){
        $storCattagsSql = "select iCategoryId from store_category_tags where iCompanyId = ".$dvalue['iCompanyId'];
        $storCattagsData = $obj->MySQLSelect($storCattagsSql);
        if (scount($storCattagsData)){
            foreach ($storCattagsData as $sctvalue){
                $store_cat_sql = "select iCategoryId,JSON_UNQUOTE(JSON_VALUE(tCategoryName, '$.tCategoryName_".$vLang."')) as tCategoryName,JSON_UNQUOTE(JSON_VALUE(tCategoryDescription, '$.tCategoryDescription_".$vLang."')) as tCategoryDescription,tCategoryImage,iDisplayOrder from store_categories where iCategoryId = ".$sctvalue['iCategoryId']." AND eStatus = 'Active' AND iServiceId = ".$iServiceId;
                $store_cat_sql_data = $obj->MySQLSelect($store_cat_sql);
                foreach ($store_cat_sql_data as $sctdata){
                    $sctName = $sctdata['tCategoryName'];
                    $sctDesc = $sctdata['tCategoryDescription'];
                    $tCategoryImage = $sctdata['tCategoryImage'];
                    if (scount($CategoryWiseStores) > 0){
                        $getTitlekey = searchStoreCategoryTitle($sctName,$CategoryWiseStores);
                        if ($getTitlekey > -1){
                            if (scount($CategoryWiseStores[$getTitlekey]['subData']) < 11){
                                $CategoryWiseStores[$getTitlekey]['subData'][] = $dvalue;
                            }
                            $CategoryWiseStores[$getTitlekey]['totalStore'] += 1;
                        }else{
                            $CategoryWiseStores[] = array('iCategoryId'    => $sctdata['iCategoryId'],
                                                          'vTitle'         => $sctName,
                                                          'vDescription'   => ($sctDesc != "")?$sctDesc:"",
                                                          'vCategoryImage' => ($tCategoryImage != '')?($tconfig['tsite_upload_images_store_categories']."/".$tCategoryImage):"",
                                                          'iDisplayOrder'  => $sctdata['iDisplayOrder'],
                                                          'totalStore'     => 1,
                                                          'subData'        => array($dvalue),);
                        }
                    }else{
                        $CategoryWiseStores[] = array('iCategoryId'    => $sctdata['iCategoryId'],
                                                      'vTitle'         => $sctName,
                                                      'vDescription'   => ($sctDesc != "")?$sctDesc:"",
                                                      'vCategoryImage' => ($tCategoryImage != '')?($tconfig['tsite_upload_images_store_categories']."/".$tCategoryImage):"",
                                                      'iDisplayOrder'  => $sctdata['iDisplayOrder'],
                                                      'totalStore'     => 1,
                                                      'subData'        => array($dvalue),);
                    }
                }
            }
        }
        if ($dvalue['fOfferAppyType'] != "None"){
            $sctSql = "select iCategoryId,JSON_UNQUOTE(JSON_VALUE(tCategoryName, '$.tCategoryName_".$vLang."')) as tCategoryName,JSON_UNQUOTE(JSON_VALUE(tCategoryDescription, '$.tCategoryDescription_".$vLang."')) as tCategoryDescription,tCategoryImage,iDisplayOrder from store_categories where eType = 'offers' AND iServiceId = ".$iServiceId." AND eStatus = 'Active'";
            $sctSql_data = $obj->MySQLSelect($sctSql);
            $sctNameOffer = $sctSql_data[0]['tCategoryName'];
            $sctDescOffer = $sctSql_data[0]['tCategoryDescription'];
            $getTitlekey = searchStoreCategoryTitle($sctNameOffer,$CategoryWiseStores);
            {
                if ($getTitlekey > -1){
                    if (scount($CategoryWiseStores[$getTitlekey]['subData']) < 11){
                        $CategoryWiseStores[$getTitlekey]['subData'][] = $dvalue;
                    }
                    $CategoryWiseStores[$getTitlekey]['totalStore'] += 1;
                }else{
                    $CategoryWiseStores[] = array('iCategoryId'    => $sctSql_data[0]['iCategoryId'],
                                                  'vTitle'         => $sctNameOffer,
                                                  'vDescription'   => ($sctDescOffer != "")?$sctDescOffer:"",
                                                  'vCategoryImage' => ($sctSql_data[0]['tCategoryImage'] != "")?($tconfig['tsite_upload_images_store_categories']."/".$sctSql_data[0]['tCategoryImage']):"",
                                                  'iDisplayOrder'  => $sctSql_data[0]['iDisplayOrder'],
                                                  'totalStore'     => 1,
                                                  'subData'        => array($dvalue));
                }
            }
        }
        $date1 = date('Y-m-d H:i:s');
        $date2 = $dvalue['tRegistrationDate'];
        $diff = strtotime($date2) - strtotime($date1);
        $diff_days = abs(round($diff / 86400));
        $sctSql = "select iCategoryId,JSON_UNQUOTE(JSON_VALUE(tCategoryName, '$.tCategoryName_".$vLang."')) as tCategoryName,JSON_UNQUOTE(JSON_VALUE(tCategoryDescription, '$.tCategoryDescription_".$vLang."')) as tCategoryDescription,tCategoryImage,iDisplayOrder,iDaysRange from store_categories where eType = 'newly_open' AND iServiceId = ".$iServiceId." AND eStatus = 'Active'";
        $sctSql_data = $obj->MySQLSelect($sctSql);
        $sctNameNew = $sctSql_data[0]['tCategoryName'];
        $sctDescNew = $sctSql_data[0]['tCategoryDescription'];
        $sctDaysRange = ($sctSql_data[0]['iDaysRange'] != "")?$sctSql_data[0]['iDaysRange']:30;
        if ($diff_days <= $sctDaysRange){
            $getTitlekey = searchStoreCategoryTitle($sctNameNew,$CategoryWiseStores);
            {
                if ($getTitlekey > -1){
                    if (scount($CategoryWiseStores[$getTitlekey]['subData']) < 11){
                        $CategoryWiseStores[$getTitlekey]['subData'][] = $dvalue;
                    }
                    $CategoryWiseStores[$getTitlekey]['totalStore'] += 1;
                }else{
                    $CategoryWiseStores[] = array('iCategoryId'    => $sctSql_data[0]['iCategoryId'],
                                                  'vTitle'         => $sctNameNew,
                                                  'vDescription'   => ($sctDescNew != "")?$sctDescNew:"",
                                                  'vCategoryImage' => ($sctSql_data[0]['tCategoryImage'] != "")?($tconfig['tsite_upload_images_store_categories']."/".$sctSql_data[0]['tCategoryImage']):"",
                                                  'iDisplayOrder'  => $sctSql_data[0]['iDisplayOrder'],
                                                  'totalStore'     => 1,
                                                  'subData'        => array($dvalue));
                }
            }
        }
    }
    usort($CategoryWiseStores,function ($a,$b){
        return $a["iDisplayOrder"] - $b["iDisplayOrder"];
    });
    foreach ($CategoryWiseStores as $catkey => $catvalue){
        shuffle($CategoryWiseStores[$catkey]['subData']);
        $shuffled_arr = $CategoryWiseStores[$catkey]['subData'];
        $movetolast = array();
        foreach ($shuffled_arr as $mkey => $mvalue){
            if ($mvalue['restaurantstatus'] == 'closed'){
                $movetolast[] = $shuffled_arr[$mkey];
                unset($shuffled_arr[$mkey]);
            }
        }
        $CategoryWiseStores[$catkey]['subData'] = array_merge($shuffled_arr,$movetolast);
        if ($CategoryWiseStores[$catkey]['iCategoryId'] == ""){
            unset($CategoryWiseStores[$catkey]);
        }
    }
}
$page = 1;
$per_page = 12;
$totalStore = scount($Data); //Added By HJ On 18-01-2020 As Per Discuss Between CS and KS Sir
$TotalPages = ceil(scount($Data) / $per_page);
$pagecount = $page - 1;
$start_limit = $pagecount * $per_page;
$Data = array_slice($Data,$start_limit,$per_page);
if ($allowed_ans != "Yes"){
    $Data = array();
}
//Added By HJ On 22-06-2019 For Get Store By Filter End
//$allcuisine = getcuisinelist($storeIdArr, 0);
//$cuisineArr = $storeDetails['cuisineArr'];
$allStoreCuisin = array_values($storeDetails['companyCuisineArr']);
$finalCuisineArr = $cuisineArr = array();
foreach ($allStoreCuisin as $cuisineKey => $cuisineArr){
    for ($f = 0;$f < scount($cuisineArr);$f++){
        $finalCuisineArr[] = $cuisineArr[$f];
    }
}
if (scount($finalCuisineArr) > 0){
    $cuisineoriginalarray = array_unique($finalCuisineArr);
    $cuisineArr = array_values($cuisineoriginalarray);
}
$cuisinecount = $storeDetails['cuisinecount'];
$confirmLabel = $languageLabelsArr['LBL_DELETE_CART_ITEM'];
$noOfferTxt = $languageLabelsArr['LBL_NO_OFFER_TXT'];
$pageHead = $SITE_NAME." | ".$languageLabelsArr['LBL_STORE_LISTING_MANUAL_TXT'];
//if (scount($service_categories_ids_arr) > 1) {
function restaurant_listing_page()
{
    return true;
}

//}
$safetyimg = "/webimages/icons/DefaultImg/ic_safety.png";
$safetyimgurl = (file_exists($tconfig["tpanel_path"].$safetyimg))?$tconfig["tsite_url"].$safetyimg:"";
$safetyurl = $tconfig["tsite_url"]."safety-measures?fromweb=Yes&order=".$fromOrder;
$galleryimg = "/webimages/icons/DefaultImg/ic_gallery.png";
$galleryimgurl = (file_exists($tconfig["tpanel_path"].$galleryimg))?$tconfig["tsite_url"].$galleryimg:"";
$galleryurl = $tconfig["tsite_url"]."safety-measures?fromweb=Yes&order=".$fromOrder;
$scSql = "SELECT eShowTerms FROM service_categories WHERE iServiceId = ".$iServiceId;
$scSqlData = $obj->MySQLSelect($scSql);
$eShowTerms = $scSqlData[0]['eShowTerms'];
$enableAgeFeature = "No";
if ($MODULES_OBJ->isEnableTermsServiceCategories() && $eShowTerms == "Yes" && $_SESSION['sess_user'] == "rider"){
    $enableAgeFeature = "Yes";
}
$sess_user = "user";
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == "driver"){
    $sess_user = "driver";
}
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == "driver"){
    $sql = "SELECT * FROM register_".$sess_user." WHERE iDriverId='".$_SESSION['sess_iUserId']."'";
    $db_booking = $obj->MySQLSelect($sql);
    $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='".$db_booking[0]['vCurrencyDriver']."'";
    $db_curr_ratio = $obj->MySQLSelect($sql);
}else{
    if (isset($_SESSION['sess_iUserId'])){
        $sql = "SELECT * FROM register_".$sess_user." WHERE iUserId='".$_SESSION['sess_iUserId']."'";
        $db_booking = $obj->MySQLSelect($sql);
        $sql = "SELECT fThresholdAmount, Ratio, vName, vSymbol FROM currency WHERE vName='".$db_booking[0]['vCurrencyPassenger']."'";
        $db_curr_ratio = $obj->MySQLSelect($sql);
    }
}
$currencycode = isset($db_curr_ratio[0]['vName'])?$db_curr_ratio[0]['vName']:'';
if (empty($currencycode)){
    if (!empty($_SESSION['sess_currency'])){
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE vName = '".$_SESSION['sess_currency']."'";
    }else{
        $sqlp = "SELECT vName,vSymbol,Ratio FROM currency WHERE eDefault = 'Yes'";
    }
    $currencyData = $obj->MySQLSelect($sqlp);
    $currencycode = $currencyData[0]['vName'];
}
?>
<!DOCTYPE html>
<html lang="en"
        dir="<?=(isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?=$pageHead;?></title>
    <meta name="keywords" value="<?=$meta['meta_keyword'];?>"/>
    <meta name="description" value="<?=$meta['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <style type="text/css">
        .who-txt {
            display: flex;
            align-items: center;
            margin: 0 0 12px 0;
            min-height: 34px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            box-sizing: border-box;
            color: #000;
        }

        .who-txt img {
            width: 24px;
            margin-right: 10px;
        }

        [dir="rtl"] .who-txt img {
            margin-left: 10px;
            margin-right: 0;
        }

        .store-search-box a.list-group-item-hover {
            color: #555;
            text-decoration: none;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body id="order-pages">
<div id="main-uber-page">
    <?php include_once("top/left_menu.php");
    include_once("top/header_topbar.php"); ?>
    <div class="page-contant home-page-data">
        <div class="page-contant-inner _MLR0_  page-contant-inner-av">
            <?php if (scount($Data) > 0 && !empty($Data)){ ?>
                <div class="listing-main" id="restlisting">
                    <?php if ($MODULES_OBJ->isStoreClassificationEnable() && scount($CategoryWiseStores) > 0){ ?>
                        <?php if ($filter != 1){ ?>
                            <div class="rest-menu-main">
                                <div class="rest-menu-left full-width">
                                    <div class="leftbar-filter fixed_ele" style="left: 15px;">
                                        <div class="filter-data fixed_ele" style="left: auto;">
                                            <nav>
                                                <ul>
                                                    <?php if (scount($CategoryWiseStores) > 0){
                                                        $cwscount = 1;
                                                        foreach ($CategoryWiseStores as $cwskey => $CategoryStore){
                                                            if ($cwscount == 1){
                                                                $tabactive = "active";
                                                            }else{
                                                                $tabactive = "";
                                                            }
                                                            ?>
                                                            <li><a class="<?php echo $tabactive ?>"
                                                                        id="activeTab_<?php echo $cwskey ?>"
                                                                        onclick="enableActiveTab('<?php echo $cwskey ?>');"
                                                                        data-href="#cat<?php echo $cwskey ?>"><?php echo $CategoryStore['vTitle'] ?></a>
                                                            </li>
                                                            <?php $cwscount++;
                                                        }
                                                    } ?>
                                                    <li><a class="" id="activeTab_all" onclick="enableActiveTab('all');"
                                                                data-href="#catall"><?=$languageLabelsArr['LBL_ALL'].' '.$languageLabelsArr['LBL_RESTAURANTS_TXT'];?></a>
                                                    </li>
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                    <div class="product-list-right filter-menu-tem">
                                        <?php if (scount($CategoryWiseStores) > 0){
                                            $cwscount = 1;
                                            foreach ($CategoryWiseStores as $cwskey => $CategoryStore){
                                                if ($cwscount == 1){
                                                    $sectionactive = "active";
                                                }else{
                                                    $sectionactive = "";
                                                }
                                                ?>
                                                <section class="rest-menu-cat <?php echo $sectionactive ?>"
                                                        id="cat<?php echo $cwskey ?>">
                                                    <div class="hold-cat-title">
                                                        <h3><?php echo $CategoryStore['vTitle']; ?></h3>
                                                        <span><?php echo $CategoryStore['totalStore']." ".$languageLabelsArr['LBL_RESTAURANTS']; ?></span>
                                                    </div>
                                                    <ul class="rest-listing store-category-listing">
                                                        <?php $currencySymbol = "$";
                                                        if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != ""){
                                                            $currencySymbol = $storeDetails['currencySymbol'];
                                                        }
                                                        $subDataCount = 1;
                                                        $subData = $CategoryStore['subData'];
                                                        for ($i = 0;$i < scount($subData);$i++){
                                                            $fDeliverytime = 0;
                                                            $iCompanyId = $subData[$i]['iCompanyId'];
                                                            $vAvgRating = $subData[$i]['vAvgRating'];
                                                            $iServiceId = $subData[$i]['iServiceId'];
                                                            $subData[$i]['vAvgRating'] = ($vAvgRating > 0)?number_format($subData[$i]['vAvgRating'],1):number_format(0,1);
                                                            $subData[$i]['vAvgRatingOrig'] = $subData[$i]['vAvgRating'];
                                                            $subData[$i]['vCompany'] = stripslashes(ucfirst_multibyte($subData[$i]['vCompany']));

                                                            if ($subData[$i]['vImage'] == "" || !file_exists($tconfig['tsite_upload_images_compnay_path'].'/'.$iCompanyId.'/'.$subData[$i]['vImage'])){
                                                                $subData[$i]['vImage'] = $tsite_url.'assets/img/custome-store/food-menu-order-list.png';
                                                            }else{
                                                                $subData[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'].'/'.$iCompanyId.'/'.$subData[$i]['vImage'];
                                                            }
                                                            if ($subData[$i]['vCoverImage'] != ""){
                                                                $subData[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'].'/'.$iCompanyId.'/'.$subData[$i]['vCoverImage'];
                                                            }
                                                            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
                                                            if (isset($storeDetails['storeDemoImageArr'][$iCompanyId]) && $storeDetails['storeDemoImageArr'][$iCompanyId] != "" && SITE_TYPE == "Demo"){
                                                                $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'].$storeDetails['storeDemoImageArr'][$iCompanyId];
                                                                if (file_exists($demoImgPath)){
                                                                    $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'].$storeDetails['storeDemoImageArr'][$iCompanyId];
                                                                    $subData[$i]['vImage'] = $demoImgUrl;
                                                                }
                                                            }
                                                            //echo "".$subData[$i]['vImage']."====".
                                                            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
                                                            $subData[$i]['Restaurant_Cuisine'] = "";
                                                            $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
                                                            $subData[$i]['Restaurant_OrderPrepareTime'] = "0 mins";
                                                            $subData[$i]['restaurantstatus'] = $restaurantstatus = "Closed";
                                                            if (isset($storeDetails['companyCuisineArr'][$iCompanyId])){
                                                                $subData[$i]['Restaurant_Cuisine'] = implode(", ",$storeDetails['companyCuisineArr'][$iCompanyId]);
                                                            }
                                                            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])){
                                                                $subData[$i]['Restaurant_OrderPrepareTime'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
                                                            }
                                                            $subData[$i]['Restaurant_OrderPrepareTime'] = str_replace('mins','<img src="'.$tsite_url.'assets/img/custome-store/delivery_time.png" class="delivery_time_ico" alt='.$subData[$i]['Restaurant_OrderPrepareTime'].'><br>mins',$subData[$i]['Restaurant_OrderPrepareTime']);
                                                            $Data[$i]['Restaurant_OrderPrepareTimeValue'] = "0";
                                                            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimeValue'])){
                                                                $Data[$i]['Restaurant_OrderPrepareTimeValue'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimeValue'];
                                                            }
                                                            $Data[$i]['Restaurant_OrderPrepareTimePostfix'] = "mins";
                                                            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimePostfix'])){
                                                                $Data[$i]['Restaurant_OrderPrepareTimePostfix'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimePostfix'];
                                                            }
                                                            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])){
                                                                $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
                                                            }
                                                            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])){
                                                                $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
                                                            }
                                                            if ($Restaurant_OfferMessage_short == ""){
                                                                $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
                                                            }
                                                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])){
                                                                $subData[$i]['restaurantstatus'] = $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
                                                            }
                                                            $restOpenTime = $restCloseTime = "";
                                                            $timeSlotAvailable = "No";
                                                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'])){
                                                                $restOpenTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'];
                                                            }
                                                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'])){
                                                                $restCloseTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'];
                                                            }
                                                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'])){
                                                                $timeSlotAvailable = $storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'];
                                                            }
                                                            $restPricePerPerson = $restMinOrdValue = 1;
                                                            if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])){
                                                                $restPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
                                                            }
                                                            $subData[$i]['Restaurant_fPricePerPer'] = formateNumAsPerCurrency($restPricePerPerson,$currencycode).' <div>'.ucfirst_multibyte($languageLabelsArr['LBL_PER_PERSON_TXT'])."</div>";
                                                            if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])){
                                                                $restMinOrdValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
                                                            }
                                                            $subData[$i]['Restaurant_fMinOrderValue'] = formateNumAsPerCurrency($restMinOrdValue,$currencycode)." <div>".ucfirst_multibyte($languageLabelsArr['LBL_MIN_SMALL']).". ".ucfirst_multibyte($languageLabelsArr['LBL_ORDER'])."</div>";
                                                            $subData[$i]['Restaurant_Opentime'] = $restOpenTime;
                                                            $subData[$i]['Restaurant_Closetime'] = $restCloseTime;
                                                            $subData[$i]['timeslotavailable'] = $timeSlotAvailable;
                                                            if (isset($subData[$i]['Restaurant_Opentime']) && !empty($subData[$i]['Restaurant_Opentime'])){
                                                                $subData[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'].' '.$subData[$i]['Restaurant_Opentime'];
                                                            }else{
                                                                $subData[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'];
                                                            }
                                                            if (isset($subData[$i]['timeslotavailable']) && !empty($subData[$i]['timeslotavailable']) && $subData[$i]['timeslotavailable'] == 'Yes'){
                                                                $subData[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_NOT_ACCEPT_ORDERS_TXT'];
                                                            }
                                                            $banner_images = 0;
                                                            if ($MODULES_OBJ->isEnableStorePhotoUploadFacility()){
                                                                $banner_data = $obj->MySQLSelect("SELECT * FROM store_wise_banners WHERE iCompanyId = ".$iCompanyId." AND eStatus = 'Active' GROUP BY iUniqueId ORDER BY iUniqueId DESC");
                                                                if (scount($banner_data) > 0){
                                                                    $banner_images = 1;
                                                                }
                                                            }
                                                            if ($subDataCount <= 11){ ?>
                                                                <li <?php if (strtolower($restaurantstatus) == "closed"){ ?> class="rest-closed" <?php } ?> >
                                                                    <?php if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1){ ?>
                                                                        <div class="add-favorate">
                                                                    <span class="fav-check">
                                                                        <input id="favouriteManualStore"
                                                                                name="favouriteManualStore"
                                                                                data-company="<?=$iCompanyId;?>"
                                                                                data-service="<?=$iServiceId;?>"
                                                                                onclick="updateFavStoreStatus(this);"
                                                                                class="favouriteManualStore"
                                                                                type="checkbox"
                                                                                value="Yes" <?php if (isset($subData[$i]['eFavStore']) && !empty($subData[$i]['eFavStore']) && $subData[$i]['eFavStore'] == 'Yes'){
                                                                            echo "checked";
                                                                        } ?>>
                                                                        <span class="custom-check"></span>
                                                                    </span>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <div data-status="<?=$subData[$i]['Restaurant_Open_And_Close_time'];?>"
                                                                            class="outeranchor">
                                                                        <a href="<?=$tsite_url;?>store-items?id=<?=$iCompanyId;?>&order=<?=$fromOrder;?>">


                                                                            <!-- <div class="rest-pro setImageRatio"
                                                                                 style="background-image:url(<?php /*= ($subData[$i]['vImage']); */ ?>);"></div>-->

                                                                            <div attr-background-image="<?=($subData[$i]['vImage']);?>" class="rest-pro setImageRatio setImageRatioJS shine resize_image">
                                                                            </div>
                                                                        </a>
                                                                        <div class="procapt">
                                                                            <a href="<?=$tsite_url;?>store-items?id=<?=$iCompanyId;?>&order=<?=$fromOrder;?>">
                                                                                <strong title="<?=$subData[$i]['vCompany'];?>">
                                                                                    <span class="item-list-name"><?=$subData[$i]['vCompany'];?></span>
                                                                                    <?php if ($vAvgRating > 0){ ?>
                                                                                        <span class="rating">
                                                                                            <img src="<?=$tsite_url;?>assets/img/star.svg" alt=""> <?=$subData[$i]['vAvgRatingOrig'];?>
                                                                                        </span>
                                                                                    <?php } ?>
                                                                                    <div class="food-detail"
                                                                                            title="<?=$subData[$i]['Restaurant_Cuisine'];?>"><?=$subData[$i]['Restaurant_Cuisine'];?></div>
                                                                                </strong>
                                                                                <div class="span-row">
                                                                            <span class="timing">
                                                                                <?=$Data[$i]['Restaurant_OrderPrepareTimeValue'];?>
                                                                                <img src="<?=$tsite_url?>assets/img/custome-store/delivery_time.png"
                                                                                        class="delivery_time_ico"
                                                                                        alt="<?=$Data[$i]['Restaurant_OrderPrepareTimeValue'].' '.$Data[$i]['Restaurant_OrderPrepareTimePostfix'];?>"><br>
                                                                                <?=$Data[$i]['Restaurant_OrderPrepareTimePostfix'];?>
                                                                            </span>
                                                                                    <span class="on-nin"><?=$subData[$i]['Restaurant_fMinOrderValue'];?></span>
                                                                                    <?php if ($iServiceId == 1){ ?>
                                                                                        <span class="on-nos"><?=$subData[$i]['Restaurant_fPricePerPer'];?></span>
                                                                                    <?php } ?>
                                                                                </div>
                                                                                <span class="discount-txt">
                                                                            <img src="<?=$tsite_url;?>assets/img/discount.svg"
                                                                                    alt="">
                                                                            <?=$Restaurant_OfferMessage;?>
                                                                        </span>
                                                                            </a>

                                                                            <?php //if($iServiceId==1 || $iServiceId==2)
                                                                            // {
                                                                            if (($subData[$i]['eSafetyPractices'] == 'Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()) || ($MODULES_OBJ->isEnableStorePhotoUploadFacility() && $banner_images == 1)){ ?>
                                                                                <?php if ($subData[$i]['eSafetyPractices'] == 'Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()){ ?>
                                                                                    <a href="<?=$safetyurl.'&id='.base64_encode($iCompanyId).'&iServiceId='.base64_encode($iServiceId);?>"
                                                                                            class="who-txt" target="new">
                                                                                        <img src="<?=$safetyimgurl?>"
                                                                                                alt="">
                                                                                        <?=$languageLabelsArr['LBL_SAFETY_NOTE_TITLE_LIST']?>
                                                                                    </a>
                                                                                <?php }else{ ?>
                                                                                    <a href="<?=$galleryurl.'&id='.base64_encode($iCompanyId).'&iServiceId='.base64_encode($iServiceId);?>"
                                                                                            class="who-txt" target="new">
                                                                                        <img src="<?=$galleryimgurl?>"
                                                                                                alt="">
                                                                                        <?=$languageLabelsArr['LBL_VIEW_PHOTOS']?>
                                                                                    </a>
                                                                                <?php } ?>
                                                                            <?php }else{ ?>
                                                                                <span class="who-txt"
                                                                                        style="border:none"></span>
                                                                            <?php }
                                                                            // } ?>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            <?php }
                                                            $moreStore = $CategoryStore['totalStore'] - 11;
                                                            if ($subDataCount == 11 && $moreStore != 0){
                                                                ?>
                                                                <li>
                                                                    <a href="javascript:void(0);"
                                                                            class="more-stores outeranchor"
                                                                            data-category="<?=$CategoryStore['iCategoryId']?>"
                                                                            data-categoryname="<?=$CategoryStore['vTitle']?>"
                                                                            data-categorycount="<?=$CategoryStore['totalStore']?>"><span>+<?=$moreStore.' '.$languageLabelsArr['LBL_MORE']?></span></a>
                                                                </li>
                                                            <?php } ?>
                                                            <?php $subDataCount++;
                                                        }
                                                        ?>
                                                    </ul>
                                                </section>
                                                <?php $cwscount++;
                                            }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        <?php } ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php if (scount($Data) > 0 && !empty($Data)){ ?>
        <div class="restaurant-section-title" id="catall">
            <a href="javascript:void(0);" class="wrap-title">
                <img src="<?=$tsite_url;?>assets/img/down.svg" alt="">
                <span><?=$languageLabelsArr['LBL_ALL'].' '.$languageLabelsArr['LBL_RESTAURANTS'];?></span>
            </a>
            <a href="javascript:void(0);" class="pull-right closeStoreCat" style="display: none;">
                <i class="fa fa-times"></i>
            </a>
        </div>
        <div class="restaurant-section">
            <div class="filter-navbar-section">
                <div class="filter-navbar">
                    <div class="navbar-restaurant-count">
                        <?php if (scount($Data) > 0 && !empty($Data)){ ?>
                            <?=$totalStore.' '.$languageLabelsArr['LBL_RESTAURANTS'];?>
                        <?php } ?>
                    </div>
                    <div id="search_store" class="fullscreen-search">
                        <div class="fullscreen-search-content">
                            <div class="store-search-box">
                                <i class="fa fa-search"></i>
                                <input type="text" autocomplete="off"
                                        placeholder="<?=$languageLabelsArr['LBL_MANUAL_STORE_SEARCH_RESTAURANT'];?>"
                                        name="search_restaurant" id="search_restaurant"> <!-- onkeypress="searching(this.value);" -->
                                <div class="ele_type" style="display:none;">
                                    <ul>
                                        <li id="storesearchactive" class="active"><a onclick="openItemStoreSearch(this)"
                                                    id="storesearchresult"><?=$languageLabelsArr['LBL_RESTAURANTS_TXT'];?></a>
                                        </li>
                                        <li id="itemsearchactive"><a onclick="openItemStoreSearch(this)"
                                                    id="itemsearchresult"><?=$languageLabelsArr['LBL_MENU_ITEMS_FRONT'];?></a>
                                        </li>
                                    </ul>
                                </div>
                                <!-- <div style="display:none;" class="list-group restaurant-list" onscroll="Searchloadmorerestaurants()"></div> -->
                                <div style="display:none;" class="list-group restaurant-list"></div>

                                <img src="<?=$tsite_url;?>assets/img/close.svg" alt="" onclick="closeSearch()">

                            </div>

                        </div>

                    </div>

                    <div class="navbar-filter-options">

                        <a class="search_div" onclick="openSearch()">

                            <i class="fa fa-search"></i><?=$languageLabelsArr['LBL_Search'];?>

                        </a>

                        <a href="javascript:void(0)"
                                class="navbar-filter-opt <?php if ($sortby == "relevance"){
                                    echo 'active';
                                } ?>"
                                data-value="relevance"><?=$languageLabelsArr['LBL_RELEVANCE'];?></a>

                        <a href="javascript:void(0)"
                                class="navbar-filter-opt <?php if ($sortby == "time"){
                                    echo 'active';
                                } ?>"
                                data-value="time"><?=$languageLabelsArr['LBL_DELIVERY_TIME'];?></a>

                        <a href="javascript:void(0)"
                                class="navbar-filter-opt <?php if ($sortby == "rating"){
                                    echo 'active';
                                } ?>" <?php if ($sortby == "rating"){
                            echo 'class="active"';
                        } ?>
                                data-value="rating"><?=$languageLabelsArr['LBL_RATING'];?></a>

                        <label class="filter-main" onclick="openNav()">

                            <div class="filter-label">

                                <img src="<?=$tsite_url;?>assets/img/custome-store/controls.svg" alt="" width="30px;">

                            </div>

                        </label>

                        <div id="mySidenav" class="sidenav">

                            <form action="" method="POST" id="filter_form">

                                <div class="closebtn"><span class="roundButton" onclick="closeNav()">

                                        <img src="<?=$tsite_url;?>assets/img/cancel-new.svg"
                                                width="20px;"></span> <?=$languageLabelsArr['LBL_FILTER_TXT'];?>

                                </div>

                                <input type="hidden" name="filter" value="<?=$filter?>">

                                <div class="filters">

                                    <?php
                                    if ($cuisinecount > 0){ ?>

                                        <div class="filter-option-label pull-left full-width"><?=$languageLabelsArr['LBL_CUISINES']?></div>

                                        <?php
                                        $storenoimage = $tsite_url.'assets/img/custome-store/no_service.png';
                                        for ($w = 0;$w < $cuisinecount;$w++){
                                            //$checkbox_checked = "";
                                            //if (in_array($cuisineArr[$w], $postcuisineIds)) $checkbox_checked = "checked";
                                            $checkbox_checked = (is_array($postcuisineIds) && in_array($cuisineArr[$w],$postcuisineIds))?"checked":"";
                                            ?>

                                            <label class="filter-options">

                                        <span class="check-holder">

                                        <input name="cuisine_type[]" class="filterFavStore" type="checkbox"
                                                value="<?=$cuisineArr[$w];?>" <?php echo $checkbox_checked; ?>>

                                        <span class="check-box"></span>

                                        </span>

                                                <span class="filter-option-text"><?=$cuisineArr[$w];?></span>

                                            </label>

                                            <?php
                                        }
                                    }else{
                                        $storenoimage = $tsite_url.'assets/img/custome-store/no_service.png';
                                        ?>

                                        <div class="show-msg-restaurant-listing">

                                            <img src="<?=$storenoimage;?>" alt="">

                                            <strong><?php echo $languageLabelsArr['LBL_NO_CUISINE_FOUND_TXT']; ?></strong>

                                        </div>

                                    <?php } ?>

                                    <div class="clearfix"></div>

                                    <div class="filter-option-label pull-left full-width"><?=$languageLabelsArr['LBL_SHOW_RESTAURANTS_WITH']?></div>

                                    <label class="filter-options">

                                        <span class="check-holder">

                                        <input name="offers" class="filterFavStore" type="checkbox"
                                                value="Offers" <?php if ($postoffers != ""){
                                            echo "checked";
                                        } ?>>

                                        <span class="check-box"></span>

                                        </span>

                                        <span class="filter-option-text"><?=$languageLabelsArr['LBL_OFFER']?></span>

                                    </label>

                                    <?php if ($MODULES_OBJ->isFavouriteStoreModuleAvailable() && (strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user")){ ?>

                                        <label class="filter-options">

                                        <span class="check-holder">

                                        <input name="favStore" class="filterFavStore" type="checkbox"
                                                value="favStore" <?php if ($postfavStore != ""){
                                            echo "checked";
                                        } ?>>

                                        <span class="check-box"></span>

                                        </span>

                                            <span class="filter-option-text"><?=$languageLabelsArr['LBL_FAVOURITE_STORE'].' '.$languageLabelsArr['LBL_RESTAURANTS_TXT']?></span>

                                        </label>

                                    <?php } ?>

                                    <div class="clearfix"></div>

                                    <div class="filter-option-label pull-left full-width"><?=$languageLabelsArr['LBL_SORT_BY_TXT'];?></div>

                                    <label class="filter-options-radio">

                                        <input type="radio" checked="checked" name="sortby"
                                                value="relevance" <?php if ($sortby == "relevance"){
                                            echo "checked";
                                        } ?>>

                                        <span class="checkmark"></span>

                                        <span class="filter-option-text"><?=$languageLabelsArr['LBL_RELEVANCE'];?></span>

                                    </label>

                                    <label class="filter-options-radio">

                                        <input type="radio" name="sortby"
                                                value="rating" <?php if ($sortby == "rating"){
                                            echo "checked";
                                        } ?>>

                                        <span class="checkmark"></span>

                                        <span class="filter-option-text"><?=$languageLabelsArr['LBL_RATING'];?></span>

                                    </label>

                                    <label class="filter-options-radio">

                                        <input type="radio" name="sortby"
                                                value="time" <?php if ($sortby == "time"){
                                            echo "checked";
                                        } ?>>

                                        <span class="checkmark"></span>

                                        <span class="filter-option-text"><?=$languageLabelsArr['LBL_DELIVERY_TIME'];?></span>

                                    </label>

                                    <?php if ($iServiceIdDef == 1){ ?>

                                        <label class="filter-options-radio">

                                            <input type="radio" name="sortby"
                                                    value="costlth" <?php if ($sortby == "costlth"){
                                                echo "checked";
                                            } ?>>

                                            <span class="checkmark"></span>

                                            <span class="filter-option-text"><?=$languageLabelsArr['LBL_COST_LTOH'];?></span>

                                        </label>

                                        <label class="filter-options-radio">

                                            <input type="radio" name="sortby"
                                                    value="costhtl" <?php if ($sortby == "costhtl"){
                                                echo "checked";
                                            } ?>>
                                            <span class="checkmark"></span>
                                            <span class="filter-option-text"><?=$languageLabelsArr['LBL_COST_HTOL'];?></span>
                                        </label>
                                    <?php } ?>
                                </div>
                                <div class="apply-filter-buttons">
                                    <a class="gen-btn know-more-btn-new"
                                            id="clear_filter"><?=$languageLabelsArr['LBL_CLEAR']?></a>
                                    <a class="gen-btn"
                                            id="apply_filter"><?=$languageLabelsArr['LBL_APPLY_FILTER']?></a>
                                </div>
                            </form>
                        </div>
                        <div class="overlay" onclick="closeNav()" id="myOverlay"></div>
                    </div>
                </div>
            </div>
            <div class="restaurant-section-content">

                <div class="restaurant-listing">

                    <ul class="rest-listing" id="rest-listing">

                        <?php $currencySymbol = "$";
                        if (isset($storeDetails['currencySymbol']) && $storeDetails['currencySymbol'] != ""){
                            $currencySymbol = $storeDetails['currencySymbol'];
                        }
                        for ($i = 0;$i < scount($Data);$i++){
                            $fDeliverytime = 0;
                            $iCompanyId = $Data[$i]['iCompanyId'];
                            $vAvgRating = $Data[$i]['vAvgRating'];
                            $iServiceId = $Data[$i]['iServiceId'];
                            $Data[$i]['vAvgRating'] = ($vAvgRating > 0)?number_format($Data[$i]['vAvgRating'],1):number_format(0,1);
                            $Data[$i]['vAvgRatingOrig'] = $Data[$i]['vAvgRating'];
                            $Data[$i]['vCompany'] = stripslashes(ucfirst_multibyte($Data[$i]['vCompany']));
                            if ($Data[$i]['vImage'] == "" || !file_exists($tconfig['tsite_upload_images_compnay_path'].'/'.$iCompanyId.'/3_'.$Data[$i]['vImage'])){
                                $Data[$i]['vImage'] = $tsite_url.'assets/img/custome-store/food-menu-order-list.png';
                            }else{
                                $Data[$i]['vImage'] = $tconfig['tsite_upload_images_compnay'].'/'.$iCompanyId.'/3_'.$Data[$i]['vImage'];
                            }
                            if ($Data[$i]['vCoverImage'] != ""){
                                $Data[$i]['vCoverImage'] = $tconfig['tsite_upload_images_compnay'].'/'.$iCompanyId.'/'.$Data[$i]['vCoverImage'];
                            }
                            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image Start
                            if (isset($storeDetails['storeDemoImageArr'][$iCompanyId]) && $storeDetails['storeDemoImageArr'][$iCompanyId] != "" && SITE_TYPE == "Demo"){
                                $demoImgPath = $tconfig['tsite_upload_demo_compnay_doc_path'].$storeDetails['storeDemoImageArr'][$iCompanyId];
                                if (file_exists($demoImgPath)){
                                    $demoImgUrl = $tconfig['tsite_upload_demo_compnay_doc'].$storeDetails['storeDemoImageArr'][$iCompanyId];
                                    $Data[$i]['vImage'] = $demoImgUrl;
                                }
                            }
                            //Added By HJ On 26-06-2019 For Get And Display Store Demo Image End
                            $Data[$i]['Restaurant_Cuisine'] = "";
                            $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
                            $Data[$i]['Restaurant_OrderPrepareTime'] = "0 mins";
                            $Data[$i]['restaurantstatus'] = $restaurantstatus = "Closed";
                            if (isset($storeDetails['companyCuisineArr'][$iCompanyId])){
                                $Data[$i]['Restaurant_Cuisine'] = implode(", ",$storeDetails['companyCuisineArr'][$iCompanyId]);
                            }
                            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'])){
                                $Data[$i]['Restaurant_OrderPrepareTime'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTime'];
                            }
                            $Data[$i]['Restaurant_OrderPrepareTime'] = str_replace('mins','<img src="'.$tsite_url.'assets/img/custome-store/delivery_time.png" class="delivery_time_ico" alt='.$Data[$i]['Restaurant_OrderPrepareTime'].'><br>mins',$Data[$i]['Restaurant_OrderPrepareTime']);
                            $Data[$i]['Restaurant_OrderPrepareTimeValue'] = "0";
                            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimeValue'])){
                                $Data[$i]['Restaurant_OrderPrepareTimeValue'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimeValue'];
                            }
                            $Data[$i]['Restaurant_OrderPrepareTimePostfix'] = "mins";
                            if (isset($storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimePostfix'])){
                                $Data[$i]['Restaurant_OrderPrepareTimePostfix'] = $storeDetails[$iCompanyId]['Restaurant_OrderPrepareTimePostfix'];
                            }
                            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'])){
                                $Restaurant_OfferMessage_short = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage_short'];
                            }
                            if (isset($storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'])){
                                $Restaurant_OfferMessage = $storeDetails['offerMsgArr'][$iCompanyId]['Restaurant_OfferMessage'];
                            }
                            if ($Restaurant_OfferMessage_short == ""){
                                $Restaurant_OfferMessage_short = $Restaurant_OfferMessage = $noOfferTxt;
                            }
                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['status'])){
                                $Data[$i]['restaurantstatus'] = $restaurantstatus = $storeDetails['restaurantStatusArr'][$iCompanyId]['status'];
                            }
                            $restOpenTime = $restCloseTime = "";
                            $timeSlotAvailable = "No";
                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'])){
                                $restOpenTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['opentime'];
                            }
                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'])){
                                $restCloseTime = $storeDetails['restaurantStatusArr'][$iCompanyId]['closetime'];
                            }
                            if (isset($storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'])){
                                $timeSlotAvailable = $storeDetails['restaurantStatusArr'][$iCompanyId]['timeslotavailable'];
                            }
                            $restPricePerPerson = $restMinOrdValue = 1;
                            if (isset($storeDetails['restaurantPricePerPerson'][$iCompanyId])){
                                $restPricePerPerson = $storeDetails['restaurantPricePerPerson'][$iCompanyId];
                            }
                            $Data[$i]['Restaurant_fPricePerPer'] = formateNumAsPerCurrency($restPricePerPerson,$currencycode).' <div>'.ucfirst_multibyte($languageLabelsArr['LBL_PER_PERSON_TXT'])."</div>";
                            if (isset($storeDetails['restaurantMinOrdValue'][$iCompanyId])){
                                $restMinOrdValue = $storeDetails['restaurantMinOrdValue'][$iCompanyId];
                            }
                            $Data[$i]['Restaurant_fMinOrderValue'] = formateNumAsPerCurrency($restMinOrdValue,$currencycode)." <div>".ucfirst_multibyte($languageLabelsArr['LBL_MIN_SMALL']).". ".ucfirst_multibyte($languageLabelsArr['LBL_ORDER'])."</div>";
                            $Data[$i]['Restaurant_Opentime'] = $restOpenTime;
                            $Data[$i]['Restaurant_Closetime'] = $restCloseTime;
                            $Data[$i]['timeslotavailable'] = $timeSlotAvailable;
                            if (isset($Data[$i]['Restaurant_Opentime']) && !empty($Data[$i]['Restaurant_Opentime'])){
                                $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'].' '.$Data[$i]['Restaurant_Opentime'];
                            }else{
                                $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_CLOSED_TXT'];
                            }
                            if (isset($Data[$i]['timeslotavailable']) && !empty($Data[$i]['timeslotavailable']) && $Data[$i]['timeslotavailable'] == 'Yes'){
                                $Data[$i]['Restaurant_Open_And_Close_time'] = $languageLabelsArr['LBL_NOT_ACCEPT_ORDERS_TXT'];
                            }
                            $banner_images = 0;
                            if ($MODULES_OBJ->isEnableStorePhotoUploadFacility()){
                                $banner_data = $obj->MySQLSelect("SELECT * FROM store_wise_banners WHERE iCompanyId = ".$iCompanyId." AND eStatus = 'Active' GROUP BY iUniqueId ORDER BY iUniqueId DESC");
                                if (scount($banner_data) > 0){
                                    $banner_images = 1;
                                }
                            }
                            ?>
                            <li <?php if (strtolower($restaurantstatus) == "closed"){ ?> class="rest-closed" <?php } ?>
                                    data-page="2">
                                <?php if ((strtolower($checkUser) == 'rider' || strtolower($checkUser) == "user") && $checkFavStore == 1){ ?>
                                    <div class="add-favorate">
                                    <span class="fav-check">
                                    <input id="favouriteManualStore" name="favouriteManualStore"
                                            data-company="<?=$iCompanyId;?>" data-service="<?=$iServiceId;?>"
                                            onclick="updateFavStoreStatus(this);" class="favouriteManualStore"
                                            type="checkbox"
                                            value="Yes" <?php if (isset($Data[$i]['eFavStore']) && !empty($Data[$i]['eFavStore']) && $Data[$i]['eFavStore'] == 'Yes'){
                                        echo "checked";
                                    } ?>>
                                    <span class="custom-check"></span>
                                    </span>
                                    </div>
                                <?php } ?>
                                <div data-status="<?=$Data[$i]['Restaurant_Open_And_Close_time'];?>"
                                        class="outeranchor">
                                    <a href="<?=$tsite_url;?>store-items?id=<?=$iCompanyId;?>&order=<?=$fromOrder;?>">
                                        <!--<div class="rest-pro" style="background-image:url(<?php /*=($Data[$i]['vImage']);*/?>);"></div>-->

                                        <div attr-background-image="<?=($Data[$i]['vImage']);?>" class="rest-pro setImageRatio setImageRatio2JS"></div>

                                    </a>
                                    <div class="procapt">
                                        <a href="<?=$tsite_url;?>store-items?id=<?=$iCompanyId;?>&order=<?=$fromOrder;?>">
                                            <strong title="<?=$Data[$i]['vCompany'];?>">
                                                <span class="item-list-name"><?=$Data[$i]['vCompany'];?></span><span
                                                        class="rating"><img src="<?=$tsite_url;?>assets/img/star.svg"
                                                            alt=""> <?=$Data[$i]['vAvgRatingOrig'];?></span>
                                                <div class="food-detail"
                                                        title="<?=$Data[$i]['Restaurant_Cuisine'];?>"><?=$Data[$i]['Restaurant_Cuisine'];?></div>
                                            </strong>
                                            <div class="span-row">
                                            <span class="timing">
                                                <?=$Data[$i]['Restaurant_OrderPrepareTimeValue'];?>
                                                <img src="<?=$tsite_url?>assets/img/custome-store/delivery_time.png"
                                                        class="delivery_time_ico"
                                                        alt="<?=$Data[$i]['Restaurant_OrderPrepareTimeValue'].' '.$Data[$i]['Restaurant_OrderPrepareTimePostfix'];?>"><br>
                                                <?=$Data[$i]['Restaurant_OrderPrepareTimePostfix'];?>
                                            </span>
                                                <span class="on-nin"><?=$Data[$i]['Restaurant_fMinOrderValue'];?></span>
                                                <?php if ($iServiceId == 1){ ?>
                                                    <span class="on-nos"><?=$Data[$i]['Restaurant_fPricePerPer'];?></span>
                                                <?php } ?>
                                            </div>
                                            <span class="discount-txt">
                                        <img src="<?=$tsite_url;?>assets/img/discount.svg" alt="">
                                        <?=$Restaurant_OfferMessage;?>
                                        </span>
                                        </a>
                                        <?php /*if($iServiceId==1 || $iServiceId==2) {
                                        if($Data[$i]['eSafetyPractices']=='Yes') { ?><a href="<?= $safetyurl.'&id='.$iCompanyId; ?>" class="who-txt" target="new"><?php } else { ?><span class="who-txt" style="border:none"><?php } ?>
                                        <?php if($Data[$i]['eSafetyPractices']=='Yes') { ?>
                                        <img src="<?= $safetyimgurl ?>" alt="">
                                        <?= $languageLabelsArr['LBL_SAFETY_NOTE_TITLE_LIST'] ?>
                                        <?php } ?>
                                    <?php if($Data[$i]['eSafetyPractices']=='Yes') { ?></a><?php } else { ?></span><?php } }*/ ?>
                                        <?php //if($iServiceId==1 || $iServiceId==2)
                                        //{
                                        if (($Data[$i]['eSafetyPractices'] == 'Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()) || $MODULES_OBJ->isEnableStorePhotoUploadFacility() && $banner_images == 1){ ?>
                                            <?php if ($Data[$i]['eSafetyPractices'] == 'Yes' && $MODULES_OBJ->isEnableStoreSafetyProcedure()){ ?>
                                                <a href="<?=$safetyurl.'&id='.base64_encode($iCompanyId).'&iServiceId='.base64_encode($iServiceId);?>"
                                                        class="who-txt" target="new">
                                                    <img src="<?=$safetyimgurl?>" alt="">
                                                    <?=$languageLabelsArr['LBL_SAFETY_NOTE_TITLE_LIST']?>
                                                </a>
                                            <?php }else{ ?>
                                                <a href="<?=$galleryurl.'&id='.base64_encode($iCompanyId).'&iServiceId='.base64_encode($iServiceId);?>"
                                                        class="who-txt" target="new">
                                                    <img src="<?=$galleryimgurl?>" alt="">
                                                    <?=$languageLabelsArr['LBL_VIEW_PHOTOS']?>
                                                </a>
                                            <?php } ?>
                                        <?php }else{ ?>
                                            <span class="who-txt" style="border:none"></span>
                                        <?php }
                                        // } ?>
                                    </div>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                    <ul class="rest-listing catallStore" id="rest-category-listing" style="display: none;"></ul>
                    <div class="loader" style="display: none;">
                        <img src="default.gif"/>
                    </div>
                </div>
            </div>
        </div>
    <?php }
    $class = "hide";
    $notFoundLabel = $languageLabelsArr['LBL_NO_RECORD_FOUND'];
    if (empty($Data)){
        $class = "show";
        $notFoundLabel = $languageLabelsArr['LBL_OUT_OF_DELIVERY_AREA'];
        if ($filter == 1){
            $notFoundLabel = $languageLabelsArr['LBL_SEARCH_RESULT'];
        }
    }
    $storenoimage = $tsite_url.'assets/img/custome-store/no_service.png';
    ?>
    <div class="listing-main">
        <div align="center" class="<?=$class;?> show-msg-restaurant-listing show-msg-restaurant-listing-msg">
            <h4 style="margin: 0; padding: 50px 0;">
                        <span style="color:#343434;">
                            <b style="margin:20px 0 0; padding:0px; float:left; width:100%;"><img
                                        src="<?=$storenoimage;?>" alt=""
                                        width="250px;"></b><strong><?=$notFoundLabel;?></strong>
                            <?php if ($filter == 1){ ?>
                                <div>
                                <button class="btn" type="button"
                                        id="clear_filter_no_result"><?=$languageLabelsArr['LBL_CLEAR'].' '.$languageLabelsArr['LBL_FILTER_TXT'];?></button>
                            </div>
                            <?php }else{ ?>
                                <div>
                                <button class="btn"
                                        onClick="window.location.href = '<?=$redirect_location;?>'"><?=$languageLabelsArr['LBL_CHANGE_LOCATION'];?></button>
                            </div>
                            <?php } ?>
                            <!-- <p style="margin:20px 0; padding:0px; float:left; width:100%; font-size:15px; color:#7e808c;">We can't find anything related to your search.<br />Try a different search.</p> -->
                        </span>
            </h4>
        </div>
    </div>
    <?php if ($enableAgeFeature == "Yes"){
        include_once('age_restriction_modal.php');
    } ?>
</div>
<!-- home page end-->
<!-- footer part -->
<?php include_once('footer/footer_home.php'); ?>
<script type="text/javascript" src="assets/js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script type="text/javascript" src="assets/js/jquery-ui.min.js"></script>
<!-- End:contact page-->
<div style="clear:both;"></div>
</div>
<?php include_once('top/footer_script.php'); ?>
<!-- End: Footer Script -->
<script>
    $(document).ready(function () {
        $("ul.rest-listing li").each(function (index) {
            var ELE = $(this).find('.span-row span')
            if (ELE.length == 3) {
                ELE.closest('.span-row').addClass('dual-val')
            }
        });
        $(window).on('load', function () {
            EQUAL_HEIGHT();
            EQUAL_HEIGHT_();
        });
        $('[data-toggle="tooltip"]').tooltip();
        moveServiceLocation();
        <?php if($enableAgeFeature == "Yes") { ?>
        $(document).ready(function () {
            if (getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') == "") {
                $('#age_restriction_btn').prop('checked', false);
                $('#restriction_modal').modal({backdrop: 'static', keyboard: false}, 'show');
                $('#restriction_modal').addClass('custom-modal-main active');
                $('body').css('overflow', 'hidden');
            }
        });
        if (getCookie("goBackUrl") == "") {
            document.cookie = "goBackUrl=" + document.referrer;
        }
        $("body").on("contextmenu", function (e) {
            if (getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') == "") {
                return false;
            }
        });
        $(document).keydown(function (e) {
            if (e.which === 123) {
                if (getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') == "") {
                    return false;
                }
            }
        });
        $('body').attr('unselectable', 'on')
            .css({
                '-moz-user-select': '-moz-none',
                '-moz-user-select': 'none',
                '-o-user-select': 'none',
                '-khtml-user-select': 'none',
                '-webkit-user-select': 'none',
                '-ms-user-select': 'none',
                'user-select': 'none'
            })
            .bind('selectstart', function () {
                return false;
            });
        $('#age_restriction_btn').click(function () {
            if ($('#age_restriction').prop('checked') == false) {
                $('.checkmark').addClass('check-error');
                $('.check-required').show();
                return false;
            } else {
                $('.checkmark').removeClass('check-error');
                $('.check-required').hide();
                var date = new Date();
                date.setTime(date.getTime() + (3000 * 1000));
                document.cookie = "AGE_RESTRICTION_<?= $iServiceId ?>=" + date.toGMTString() + "; expires=" + date.toGMTString();
                removeRestrictionCss();
                $('#restriction_modal').modal('hide');
                $('body').css('overflow', 'auto');
            }
        });
        $('#age_restriction').click(function () {
            if ($(this).prop('checked') == true) {
                $('.checkmark').removeClass('check-error');
                $('.check-required').hide();
            }
        });
        $('.rest-listing li a').click(function (e) {
            if (getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') == "") {
                e.preventDefault();
            }
        });
        $('.rest-listing li a').on('contextmenu', function (e) {
            if (getCookie('AGE_RESTRICTION_<?= $iServiceId ?>') == "") {
                return false;
            }
        });
        <?php } ?>
    });
    <?php if($enableAgeFeature == "Yes") { ?>
    function removeRestrictionCss() {
        $('body').attr('unselectable', 'on')
            .css({
                '-moz-user-select': '',
                '-moz-user-select': '',
                '-o-user-select': '',
                '-khtml-user-select': '',
                '-webkit-user-select': '',
                '-ms-user-select': '',
                'user-select': ''
            })
            .bind('selectstart', function () {
                return true;
            });
    }

    function goBack() {
        if (getCookie('goBackUrl') != "") {
            window.location.href = getCookie('goBackUrl');
        } else {
            window.location.href = document.referrer;
        }
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
    <?php } ?>
    $(window).resize(function () {
        moveServiceLocation();
    });
    $(window).load(function () {
        <?php if($filter == 1) { ?>
        $('.rest-menu-main').hide();
        $('.closeStoreCat').show();
        window.scrollTo(0, 0);
        <?php } ?>
    });
    $.fn.equalHeight = function () {
        var maxHeight = 0;
        return this.each(function (index, box) {
            var boxHeight = $(box).height();
            maxHeight = Math.max(maxHeight, boxHeight);
        }).height(maxHeight);
    };

    function EQUAL_HEIGHT() {
        $('ul.rest-listing li .food-detail').css('height', 'auto');
        $('ul.rest-listing li .food-detail').equalHeight();
        $(window).resize(function () {
            $('ul.rest-listing li .food-detail').css('height', 'auto');
            $('ul.rest-listing li .food-detail').equalHeight();
        });
    }

    function EQUAL_HEIGHT_() {
        $('ul.rest-listing li .discount-txt').css('height', 'auto');
        $('ul.rest-listing li .discount-txt').equalHeight();
        $(window).resize(function () {
            $('ul.rest-listing li .discount-txt').css('height', 'auto');
            $('ul.rest-listing li .discount-txt').equalHeight();
        });
    }

    EQUAL_HEIGHT_();
    var myVar = null;
    document.getElementById("search_restaurant").addEventListener("keyup", function () {
        var inputValue = this.value;
        // console.log(inputValue);
        searching(inputValue);
        // Your further processing here
    });

    function searching(search) {
        // var search = $('#search_restaurant').val();
        clearTimeout(myVar);
        $('.store-search-box').find('.restaurant-list').html("");
        $(".ele_type").hide();
        $(".restaurant-list").addClass('searchresultdiv');
        //console.log($('#search_restaurant').val());
        if (search.length >= 2) {
            myVar = setTimeout(function () {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_load_store_list.php',
                    'AJAX_DATA': {searchid: search, order: '<?php echo $fromOrder; ?>'},
                    'REQUEST_DATA_TYPE': 'html'
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var dataHtml = response.result;
                        $(".list-group").show();
                        $('.store-search-box').find('.restaurant-list').html(dataHtml);
                        var activeSearch = $('#itemsearchactive').hasClass('active');
                        if (activeSearch == true) {
                            itemSearchDivShowhide("itemsearchresult");
                        } else {
                            itemSearchDivShowhide("storesearchresult");
                        }
                        var storelistaval = $("#storelistaval").val();
                        var itemlistaval = $("#itemlistaval").val();
                        if (storelistaval == "1" && itemlistaval == "1") {
                            $(".ele_type").show();
                            $(".restaurant-list").removeClass('searchresultdiv');
                        } else {
                            $(".ele_type").hide();
                            $(".restaurant-list").addClass('searchresultdiv');
                            if (storelistaval == "1") {
                                $("#itemsearchresultdiv").hide();
                                $("#storesearchresultdiv").show();
                            }
                            if (itemlistaval == "1") {
                                $("#itemsearchresultdiv").show();
                                $("#storesearchresultdiv").hide();
                            }
                        }
                        document.getElementById("itemsearchresultdiv").focus();
                    } else {
                        console.log(response.result);
                    }
                });
            }, 500);
        }
    }

    function Searchloadmorerestaurants() {
        var obj = $('.restaurant-list');
        var search = $('#search_restaurant').val();
        clearTimeout(myVar);
        var page = $('.restaurant-list a:last-child').data('page');
        $(".ele_type").hide();
        $(".restaurant-list").addClass('searchresultdiv');
        if (search.length >= 2 && ((obj.scrollTop() - 1) === (obj[0].scrollHeight - obj[0].offsetHeight))) {
            myVar = setTimeout(function () {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_load_store_list.php',
                    'AJAX_DATA': {searchid: search, order: '<?php echo $fromOrder; ?>', page: page},
                    'REQUEST_DATA_TYPE': 'html'
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var dataHtml = response.result;
                        $(".list-group").show();
                        $('.store-search-box').find('.restaurant-list').append(dataHtml);
                        var activeSearch = $('#itemsearchactive').hasClass('active');
                        if (activeSearch == true) {
                            itemSearchDivShowhide("itemsearchresult");
                            $("#itemsearchresultdiv").show();
                        } else {
                            itemSearchDivShowhide("storesearchresult");
                            $("#storesearchresultdiv").show();
                        }
                        var storelistaval = $("#storelistaval").val();
                        var itemlistaval = $("#itemlistaval").val();
                        if (storelistaval == "1" && itemlistaval == "1") {
                            $(".ele_type").show();
                            $(".restaurant-list").removeClass('searchresultdiv');
                        } else {
                            $(".ele_type").hide();
                            $(".restaurant-list").addClass('searchresultdiv');
                            if (storelistaval == "1") {
                                $("#itemsearchresultdiv").hide();
                                $("#storesearchresultdiv").show();
                            }
                            if (itemlistaval == "1") {
                                $("#itemsearchresultdiv").show();
                                $("#storesearchresultdiv").hide();
                            }
                        }
                        document.getElementById("itemsearchresultdiv").focus();
                    } else {
                        console.log(response.result);
                    }
                });
            }, 500);
        }
    }

    function updateFavStoreStatus(elem) {
        favStore = 'No';
        $('.favouriteManualStore').each(function () {
            if (elem.checked) {
                favStore = elem.value;
            }
        });
        var iUserId = '<?= $iUserId ?>'
        var companyId = $(elem).attr("data-company");
        var iServiceId = $(elem).attr("data-service");
        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>ajax_fav_manual_store.php',
            'AJAX_DATA': {iCompanyId: companyId, iUserId: iUserId, iServiceId: iServiceId, eFavStore: favStore},
            'REQUEST_DATA_TYPE': 'text'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                return true;
            } else {
                console.log(response.result);
            }
        });
    }

    var lastEnabledTab = 0;
    var lastScrollTop;

    function enableActiveTab(foodMenuId) {
        $("[id^='activeTab_']").removeClass("active");
        lastEnabledTab = foodMenuId;
        $("#activeTab_" + foodMenuId).addClass("active");
        if (foodMenuId == "all1") {
            if ($('header').hasClass('sticky')) {
                $('html, body').animate({
                    scrollTop: $("#rest-listing").offset().top - ($('header').height() + 70)
                }, 500);
            } else {
                $('html, body').animate({
                    scrollTop: $("#rest-listing").offset().top - ($('header').height() + 350)
                }, 500);
            }
        } else if (foodMenuId == "allStore") {
            lastScrollTop = $(".restaurant-section-title").offset().top - ($('header').height());
            $('html, body').animate({
                scrollTop: $(".restaurant-section-title").offset().top - ($('header').height())
            }, 500, function () {
                $('.rest-menu-main').hide();
                window.scrollTo(0, 0);
            });
        } else {
            if ($('header').hasClass('sticky')) {
                $('html, body').animate({
                    scrollTop: $("#cat" + foodMenuId).offset().top - ($('header').height() + 18),
                }, 500)
            } else {
                $('html, body').animate({
                    scrollTop: $("#cat" + foodMenuId).offset().top - ($('header').height() + 85),
                }, 500)
            }
        }
    }

    var sections = $('section');
    var nav = $('nav');
    var nav_height = nav.outerHeight();
    var bg_changed = 0;
    $(window).on('scroll', function () {
        var cur_pos = $(this).scrollTop();
        sections.each(function () {
            var top = $(this).offset().top - nav_height - 250;
            var bottom = top + $(this).outerHeight();
            if (cur_pos >= top && cur_pos <= bottom) {
                nav.find('a').removeClass('active');
                sections.removeClass('active');
                $(this).addClass('active');
                nav.find('a[data-href="#' + $(this).attr('id') + '"]').addClass('active');
            }
        });
        filterNavbar();
    });
    <?php $sidenav_dir = ($eDirectionCode == "ltr")?"right":"left"; ?>
    function openNav() {
        $('#mySidenav').toggle('slide', {
            direction: '<?= $sidenav_dir ?>'
        }, 500);
        $('#myOverlay').fadeIn(500);
        $('body,html').addClass('overflow-hide');
    }

    function closeNav() {
        $('#myOverlay').fadeOut(500);
        $('#mySidenav').toggle('slide', {
            direction: '<?= $sidenav_dir ?>'
        }, 500);
        $('body,html').removeClass('overflow-hide');
    }

    $('#apply_filter').click(function () {
        $('[name="filter"]').val(1);
        $('#filter_form').submit();
    });
    $('#clear_filter').click(function () {
        $('#filter_form').find('input[type="checkbox"]').prop('checked', false);
        $('#filter_form').find('input[type="radio"]').prop('checked', false);
        $('#filter_form').submit();
    });
    $('#clear_filter_no_result').click(function () {
        window.location.href = window.location.href;
    });
    var sticky_added = 0;

    function filterNavbar() {
        var navbar = $('.filter-navbar');
        var navbar_section = $('.filter-navbar-section');
        var list = navbar_section.offset().top - 60;
        if (window.pageYOffset >= list) {
            if (sticky_added == 0) {
                $('.restaurant-section').removeClass('restaurant-section-bg-1').addClass('restaurant-section-bg-2', 'fast');
                $('.restaurant-section-title').css('padding-bottom', '43px');
                navbar.addClass("sticky");
                navbar.hide();
                $('header').slideUp(200);
                navbar.slideDown(200);
                sticky_added = 1;
            }
        } else {
            if (sticky_added == 1) {
                $('.restaurant-section').removeClass('restaurant-section-bg-2').addClass('restaurant-section-bg-1', 'fast');
                navbar.slideUp(200, function () {
                    navbar.removeClass("sticky");
                    navbar.show();
                });
                $('.restaurant-section-title').css('padding-bottom', '70px');
                $('header').slideDown(200);
                sticky_added = 0;
            }
        }
    }

    var category, categoryCount, categoryname;
    $('.navbar-filter-opt').click(function () {
        var sortby = $(this).data('value');
        var order = '<?php echo $fromOrder ?>';
        $('.navbar-filter-opt').removeClass('active');
        curr_elem = $(this);
        $('input[name="sortby"]').each(function () {
            if ($(this).val() == sortby) {
                $(this).prop('checked', true);
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_load_filter_store.php',
                    'AJAX_DATA': $('#filter_form').serialize() + "&order=" + order,
                    'REQUEST_DATA_TYPE': 'html'
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var dataHtml = response.result;
                        if (category == undefined || category == "") {
                            $(".restaurant-listing").find('#rest-listing').remove();
                            $(".restaurant-listing").prepend(dataHtml);
                            curr_elem.addClass('active');
                            if ($('header').hasClass('sticky')) {
                                if ($('.filter-navbar').hasClass('sticky')) {
                                    $('html, body').animate({
                                        scrollTop: $(".restaurant-section-content").offset().top - ($('.filter-navbar-sticky').height() + 60)
                                    }, 500);
                                } else {
                                    $('html, body').animate({
                                        scrollTop: $(".restaurant-section-content").offset().top - ($('.filter-navbar-sticky').height() + 87)
                                    }, 500);
                                }
                            } else {
                                if ($('.filter-navbar').hasClass('sticky')) {
                                    $('html, body').animate({
                                        scrollTop: $(".restaurant-section-content").offset().top - ($('.filter-navbar-sticky').height() + 60)
                                    }, 500);
                                } else {
                                    $('html, body').animate({
                                        scrollTop: $(".restaurant-section-content").offset().top - ($('.filter-navbar-sticky').height() + 154)
                                    }, 500);
                                }
                            }
                        } else {
                            $(".restaurant-section-content").find('#rest-category-listing').remove();
                            dataHtml = dataHtml.replace('id="rest-listing"', 'id="rest-category-listing"')
                            $(dataHtml).insertBefore($('.loader'));
                            curr_elem.addClass('active');
                            if ($('.filter-navbar').hasClass('sticky')) {
                                $('html, body').animate({
                                    scrollTop: $(".restaurant-section-content").offset().top - ($('.filter-navbar-sticky').height() + 60)
                                }, 500);
                            } else {
                                $('html, body').animate({
                                    scrollTop: $(".restaurant-section-content").offset().top - ($('.filter-navbar-sticky').height() + 154)
                                }, 500);
                            }
                        }
                    } else {
                        console.log(response.result);
                    }
                });
            }
        });
    });
    $('.wrap-title').click(function () {
        if ($('header').hasClass('sticky')) {
            if ($('#rest-listing').css('display') == "none") {
                $('html, body').animate({
                    scrollTop: $(".restaurant-section-content").find('#rest-category-listing').offset().top - ($('.filter-navbar').height() + 70)
                }, 500);
            } else {
                $('html, body').animate({
                    scrollTop: $(".restaurant-section-content").find('#rest-listing').offset().top - ($('.filter-navbar').height() + 70)
                }, 500);
            }
        } else {
            if ($('#rest-listing').css('display') == "none") {
                $('html, body').animate({
                    scrollTop: $(".restaurant-section-content").find('#rest-category-listing').offset().top - ($('.filter-navbar').height() + 140)
                }, 500);
            } else {
                $('html, body').animate({
                    scrollTop: $(".restaurant-section-content").find('#rest-listing').offset().top - ($('.filter-navbar').height() + 140)
                }, 500);
            }
        }
    });

    function openSearch() {
        $("#search_store").show();
        $('body,html').addClass('overflow-hide');
    }

    function closeSearch() {
        $("#search_store").hide();
        $('body,html').removeClass('overflow-hide');
    }

    function openItemStoreSearch(elem) {
        $('#search_restaurant').focus();
        var boxId = $(elem).attr("id");
        itemSearchDivShowhide(boxId);
    }

    function itemSearchDivShowhide(boxId) {
        if (boxId == "itemsearchresult") {
            $("#itemsearchactive").addClass('active');
            $("#itemsearchresultdiv").show();
            $("#storesearchactive").removeClass('active');
            $("#storesearchresultdiv").hide();
        } else {
            $("#storesearchactive").addClass('active');
            $("#storesearchresultdiv").show();
            $("#itemsearchactive").removeClass('active');
            $("#itemsearchresultdiv").hide();
        }
    }

    $(window).scroll(function () {
        if (($(window).scrollTop() + $(window).height()) > ($(document).height() - 400)) {
            if (category == undefined || category == "") {
                $("#rest-category-listing").hide();
                loadmorerestaurants();
            } else {
                $("#rest-listing").hide();
                if ($('#rest-category-listing').css('display') != "none") {
                    loadMoreCategoryRestaurants(category, categoryCount);
                }
            }
        }
    });

    function loadmorerestaurants() {
        var obj = $('#rest-listing');
        var order = '<?php echo $fromOrder ?>';
        var total_pages = '<?= $TotalPages ?>';
        var page = $('#rest-listing li:last-child').data('page');
        //var search = $('#search_restaurant').val();
        if (page <= total_pages) {
            clearTimeout(myVar);
            if (obj.scrollTop() === (obj[0].scrollHeight - obj[0].offsetHeight)) {
                $('.loader').show();
                myVar = setTimeout(function () {
                    var ajaxData = {
                        'URL': '<?= $tconfig['tsite_url'] ?>ajax_load_store_data.php',
                        'AJAX_DATA': $('#filter_form').serialize() + "&order=" + order + "&page=" + page,
                        'REQUEST_DATA_TYPE': 'html',
                        'REQUEST_ASYNC': false
                    };
                    getDataFromAjaxCall(ajaxData, function (response) {
                        if (response.action == "1") {
                            var dataHtml = response.result;
                            $('#rest-listing').append(dataHtml);
                            $('.loader').hide();
                        } else {
                            console.log(response.result);
                        }
                    });
                }, 800);
            }
        }
    }

    $('.more-stores').click(function () {
        category = $(this).data('category');
        categoryname = $(this).data('categoryname');
        categoryCount = $(this).data('categorycount');
        loadMoreCategoryRestaurants(category, categoryCount, categoryname);
    });

    function loadMoreCategoryRestaurants(category, categoryCount, category_name) {
        if ($('#rest-category-listing li:last-child').data('page') == undefined) {
            var page = 1;
        } else {
            var page = $('#rest-category-listing li:last-child').data('page');
        }
        var total_pages = $('#rest-category-listing li:last-child').data('totalpages');
        var postcuisineIds = '<?= json_encode($postcuisineIds,JSON_UNESCAPED_SLASHES) ?>';
        var postfavStore = '<?= $postfavStore ?>';
        var postoffers = '<?= $postoffers ?>';
        var filter = '<?= ($filter == 1)?1:0 ?>';
        var sortby = '<?= $sortby ?>';
        var obj = $('#rest-category-listing');
        if (page == 1) {
            enableActiveTab('allStore');
        }
        $('.restaurant-section-title').find('span').text(category_name);
        $('.closeStoreCat').show();
        if (total_pages == undefined || page <= total_pages) {
            $('.loader').show();
            clearTimeout(myVar);
            myVar = setTimeout(function () {
                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_load_store_category_data.php',
                    'AJAX_DATA': {
                        order: '<?php echo $fromOrder; ?>',
                        page: page,
                        postcuisineIds: postcuisineIds,
                        category: category,
                        postfavStore: postfavStore,
                        filter: filter,
                        sortby: sortby,
                        postoffers: postoffers
                    },
                    'REQUEST_DATA_TYPE': 'html',
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var dataHtml = response.result;
                        if (page == 1) {
                            $('#rest-listing').hide();
                            $('#rest-category-listing').html(dataHtml);
                            $('.navbar-restaurant-count').html(categoryCount + " <?= $languageLabelsArr['LBL_RESTAURANTS'] ?>");
                            $("#rest-category-listing").show();
                        } else {
                            $('#rest-category-listing').append(dataHtml);
                        }
                        $('.loader').hide();
                    } else {
                        console.log(response.result);
                        $('.loader').hide();
                    }
                });
            }, 500);
        }
    }

    $('.closeStoreCat').click(function () {
        <?php if($filter == 1) { ?>
        window.location.href = window.location.href;
        <?php } else { ?>
        category = "";
        $('#rest-category-listing').html("").hide();
        $('.navbar-restaurant-count').html("<?= $totalStore.' '.$languageLabelsArr['LBL_RESTAURANTS'] ?>");
        $('.restaurant-section-title').find('span').html("<?= $languageLabelsArr['LBL_ALL'].' '.$languageLabelsArr['LBL_RESTAURANTS']; ?>");
        $('#rest-listing, .rest-menu-main').show();
        $('.closeStoreCat').hide();
        // enableActiveTab('0');
        window.scrollTo(0, lastScrollTop);
        <?php } ?>
    });

    function resetServiceCatagory() {
        var e = document.getElementById("servicename");
        var serviceId = e.options[e.selectedIndex].value;
        var serviceName = e.options[e.selectedIndex].text;
        var cartAmount = $("#subtotalamount").text();
        var cartTotItems = "<?= $confirlAlert; ?>";
        var userType = '<?= $fromOrder; ?>';
        if (cartTotItems > 0 || cartAmount.trim() != "") {
            if (confirm("<?= $confirmLabel; ?>")) {
                window.location.href = 'store-listing?sid=' + serviceId + '&order=' + userType;
            }
        } else {
            window.location.href = 'store-listing?sid=' + serviceId + '&order=' + userType;
        }
    }

    function moveServiceLocation() {
        if (window.innerWidth <= 1150) {
            $('.location-service-element').hide();
            $('.service-location-icon').show();
            //$('.location-service-element').insertAfter('header');
        } else {
            $('.location-service-element').show();
            $('.service-location-icon').hide();
            //$('.header-left').append($('.location-service-element'));
        }
    }

    $('#servicenamedropdown').click(function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
            $(this).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $('.service-categories-dropdown .drop').hide();
        } else {
            $(this).addClass('active');
            $(this).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $('.service-categories-dropdown .drop').show();
        }
    });
    $('.service-categories-dropdown .drop li').click(function () {
        var selectedService = $(this).data('value');
        $('#servicename').val(selectedService);
        $('#servicename').trigger('change');
    });
    var curr_li, curr_li_new;
    $(window).on('keydown', function (e) {
        var search_input = document.getElementById('search_restaurant');
        var last_result = $('#itemsearchresultdiv .list-group-item').last();
        var last_result_new = $('#storesearchresultdiv .list-group-item').last();
        if (e.which === 40) {
            if (document.activeElement === search_input) {
                $('#search_restaurant').trigger('blur');
                if ($('#itemsearchresultdiv').css('display') != 'none') {
                    $('#itemsearchresultdiv .list-group-item').eq(0).addClass('list-group-item-hover');
                    $('#itemsearchresultdiv .list-group-item').eq(0).focus();
                    curr_li = $('#itemsearchresultdiv .list-group-item').eq(0);
                } else {
                    $('#storesearchresultdiv .list-group-item').eq(0).addClass('list-group-item-hover');
                    $('#storesearchresultdiv .list-group-item').eq(0).focus();
                    curr_li_new = $('#storesearchresultdiv .list-group-item').eq(0);
                }
                e.preventDefault();
            } else if ((last_result.hasClass('list-group-item-hover') && $('#itemsearchresultdiv').css('display') != 'none') || last_result_new.hasClass('list-group-item-hover') && $('#storesearchresultdiv').css('display') != 'none') {
                return false;
            } else {
                $('#itemsearchresultdiv .list-group-item').blur();
                $('#itemsearchresultdiv .list-group-item').removeClass('list-group-item-hover');
                $('#storesearchresultdiv .list-group-item').blur();
                $('#storesearchresultdiv .list-group-item').removeClass('list-group-item-hover');
                if ($('#itemsearchresultdiv').css('display') != 'none') {
                    curr_li.next().addClass('list-group-item-hover');
                    curr_li.next().focus();
                    curr_li = curr_li.next();
                } else {
                    curr_li_new.next().addClass('list-group-item-hover');
                    curr_li_new.next().focus();
                    curr_li_new = curr_li_new.next();
                }
            }
        } else if (e.which === 38) {
            if ($('#itemsearchresultdiv').css('display') != 'none') {
                if (curr_li.attr('tabindex') === "2") {
                    $('#itemsearchresultdiv .list-group-item').blur();
                    $('#itemsearchresultdiv .list-group-item').removeClass('list-group-item-hover');
                    $('#search_restaurant').focus();
                } else {
                    $('#itemsearchresultdiv .list-group-item').blur();
                    $('#itemsearchresultdiv .list-group-item').removeClass('list-group-item-hover');
                    curr_li.prev().addClass('list-group-item-hover');
                    curr_li.prev().focus();
                    curr_li = curr_li.prev();
                }
            } else {
                if (curr_li_new.attr('tabindex') === "2") {
                    $('#storesearchresultdiv .list-group-item').blur();
                    $('#storesearchresultdiv .list-group-item').removeClass('list-group-item-hover');
                    $('#search_restaurant').focus();
                } else {
                    $('#storesearchresultdiv .list-group-item').blur();
                    $('#storesearchresultdiv .list-group-item').removeClass('list-group-item-hover');
                    curr_li_new.prev().addClass('list-group-item-hover');
                    curr_li_new.prev().focus();
                    curr_li_new = curr_li_new.prev();
                }
            }
        }
        // console.log(selected)
    });

</script>
<script>

    $(document).ready(function () {
        resizeImage("setImageRatioJS", "attr-background-image" , "backGroundImage");
        resizeImage("setImageRatio2JS", "attr-background-image" , "backGroundImage");
    });

</script>

</body>
</html>