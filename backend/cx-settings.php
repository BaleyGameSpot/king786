<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();

$abc = 'admin,driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';

//added by SP because settings url open for user and driver also so restrict it.
if (strtolower($_SESSION['sess_eSystem']) != "deliverall") {
    $AUTH_OBJ->AuthMemberRedirect();
    exit;
}
$script = "Settings";
$tbl_name = 'company';
if(isset($_REQUEST['id'])){
    $_REQUEST['id'] = base64_decode(base64_decode(trim($_REQUEST['id'])));
}
$id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : $_SESSION['sess_iCompanyId'];
$action = ($id != '') ? 'Edit' : 'Add';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : '';
if (!empty($vSystemDefaultCurrencyName) && !empty($vSystemDefaultCurrencySymbol)) {
    $db_currency = $currencyData = array();
    $currencyData['vName'] = $vSystemDefaultCurrencyName;
    $currencyData['vSymbol'] = $vSystemDefaultCurrencySymbol;
    $currencyData['Ratio'] = $vSystemDefaultCurrencyRatio;
    $db_currency[] = $currencyData;
} else {
    $db_currency = $obj->MySQLSelect("select vName,vSymbol,Ratio from currency where eDefault = 'Yes'");
}

if (scount($country_data_arr) > 0) {
    $db_country = $country_data_retrieve;
} else {
    $db_country = $obj->MySQLSelect("select iCountryId,vCountry,vCountryCode from country ORDER BY  vCountry ASC ");
}
if (scount($Data_ALL_langArr) > 0) {
    $db_lang = array();
    for ($dl = 0; $dl < scount($Data_ALL_langArr); $dl++) {
        if (strtoupper($Data_ALL_langArr[$dl]['eStatus']) == "ACTIVE") {
            $db_lang[] = $Data_ALL_langArr[$dl];
        }
    }
} else {
    $db_lang = $obj->MySQLSelect("select vCode,vTitle from language_master where eStatus = 'Active' order by vTitle asc");
}

$takeaway = $MODULES_OBJ->isTakeAwayEnable() ? "Yes" : "No";
//added by SP on 14-10-2020 for timeslot changes
$ENABLE_TIMESLOT_ADDON = !empty($MODULES_OBJ->isEnableTimeslotFeature()) ? "Yes" : "No";

if (strtoupper($ENABLE_TIMESLOT_ADDON) == "YES") {
    $timingArray = array('vMonFromSlot', 'vMonToSlot', 'vTueFromSlot', 'vTueToSlot', 'vWedFromSlot', 'vWedToSlot', 'vThuFromSlot', 'vThuToSlot', 'vFriFromSlot', 'vFriToSlot', 'vSatFromSlot', 'vSatToSlot', 'vSunFromSlot', 'vSunToSlot');

    $orgtimingArray = array('vMonFromSlot1', 'vMonToSlot1', 'vTueFromSlot1', 'vTueToSlot1', 'vWedFromSlot1', 'vWedToSlot1', 'vThuFromSlot1', 'vThuToSlot1', 'vFriFromSlot1', 'vFriToSlot1', 'vSatFromSlot1', 'vSatToSlot1', 'vSunFromSlot1', 'vSunToSlot1', 'vMonFromSlot2', 'vMonToSlot2', 'vTueFromSlot2', 'vTueToSlot2', 'vWedFromSlot2', 'vWedToSlot2', 'vThuFromSlot2', 'vThuToSlot2', 'vFriFromSlot2', 'vFriToSlot2', 'vSatFromSlot2', 'vSatToSlot2', 'vSunFromSlot2', 'vSunToSlot2');
    $sltAry = array(1, 2);
}

if (empty($SHOW_CITY_FIELD)) {
    $SHOW_CITY_FIELD = $CONFIG_OBJ->getConfigurations("configurations", "SHOW_CITY_FIELD");
}

// set all variables with either post (when submit) either blank (when insert)
$vCompany = isset($_POST['vCompany']) ? $_POST['vCompany'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vContactName = isset($_POST['vContactName']) ? $_POST['vContactName'] : '';
$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $DEFAULT_COUNTRY_CODE_WEB;
$vState = isset($_POST['vState']) ? $_POST['vState'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$vRestuarantLocation = isset($_POST['vRestuarantLocation']) ? $_POST['vRestuarantLocation'] : '';
$vRestuarantLocationLat = isset($_POST['vRestuarantLocationLat']) ? $_POST['vRestuarantLocationLat'] : '';
$vRestuarantLocationLong = isset($_POST['vRestuarantLocationLong']) ? $_POST['vRestuarantLocationLong'] : '';
$vCaddress = isset($_POST['vCaddress']) ? $_POST['vCaddress'] : '';
$vZip = isset($_POST['vZip']) ? $_POST['vZip'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vAcctHolderName = isset($_POST['vAcctHolderName']) ? $_POST['vAcctHolderName'] : '';
$vAcctNo = isset($_POST['vAcctNo']) ? $_POST['vAcctNo'] : '';
$vBankName = isset($_POST['vBankName']) ? $_POST['vBankName'] : '';
$vBankLocation = isset($_POST['vBankLocation']) ? $_POST['vBankLocation'] : '';
$vSwiftCode = isset($_POST['vSwiftCode']) ? $_POST['vSwiftCode'] : '';
$vPaymentEmail = isset($_POST['vPaymentEmail']) ? $_POST['vPaymentEmail'] : '';
$iServiceId = isset($_POST['iServiceId']) ? $_POST['iServiceId'] : '';

$cuisineId = isset($_POST['cuisineId']) ? $_POST['cuisineId'] : '';

$vFromMonFriTimeSlot1 = isset($_POST['vFromMonFriTimeSlot1']) ? $_POST['vFromMonFriTimeSlot1'] : '';
$vToMonFriTimeSlot1 = isset($_POST['vToMonFriTimeSlot1']) ? $_POST['vToMonFriTimeSlot1'] : '';
$vFromMonFriTimeSlot2 = isset($_POST['vFromMonFriTimeSlot2']) ? $_POST['vFromMonFriTimeSlot2'] : '';
$vToMonFriTimeSlot2 = isset($_POST['vToMonFriTimeSlot2']) ? $_POST['vToMonFriTimeSlot2'] : '';
$vFromSatSunTimeSlot1 = isset($_POST['vFromSatSunTimeSlot1']) ? $_POST['vFromSatSunTimeSlot1'] : '';
$vToSatSunTimeSlot1 = isset($_POST['vToSatSunTimeSlot1']) ? $_POST['vToSatSunTimeSlot1'] : '';
$vFromSatSunTimeSlot2 = isset($_POST['vFromSatSunTimeSlot2']) ? $_POST['vFromSatSunTimeSlot2'] : '';
$vToSatSunTimeSlot2 = isset($_POST['vToSatSunTimeSlot2']) ? $_POST['vToSatSunTimeSlot2'] : '';

$fMinOrderValue = isset($_POST['fMinOrderValue']) ? $_POST['fMinOrderValue'] : '';
$fPackingCharge = isset($_POST['fPackingCharge']) ? $_POST['fPackingCharge'] : '';
$iMaxItemQty = isset($_POST['iMaxItemQty']) ? $_POST['iMaxItemQty'] : '';
$fPrepareTime = isset($_POST['fPrepareTime']) ? $_POST['fPrepareTime'] : '';
$fOfferAppyType = isset($_POST['fOfferAppyType']) ? $_POST['fOfferAppyType'] : '';
$fOfferType = isset($_POST['fOfferType']) ? $_POST['fOfferType'] : '';
$fTargetAmt = isset($_POST['fTargetAmt']) ? $_POST['fTargetAmt'] : '';
$fOfferAmt = isset($_POST['fOfferAmt']) ? $_POST['fOfferAmt'] : '';
$fMaxOfferAmt = isset($_POST['fMaxOfferAmt']) ? $_POST['fMaxOfferAmt'] : '';
$fPricePerPerson = isset($_POST['fPricePerPerson']) ? $_POST['fPricePerPerson'] : '';
$eDriverOption = isset($_POST['eDriverOption']) ? $_POST['eDriverOption'] : 'Site';
$eTakeaway = isset($_POST['eTakeaway']) ? $_POST['eTakeaway'] : '';
//$edeliveryAvailable = isset($_POST['edeliveryAvailable']) ? $_POST['edeliveryAvailable'] : '';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
//$fTax =  isset($_POST['fTax'])?$_POST['fTax']:'';

//added by SP on 14-10-2020 for timeslot changes
if (strtoupper($ENABLE_TIMESLOT_ADDON) == "YES") {
    for ($j = 0; $j < scount($sltAry); $j++) {
        $sl1 = $sltAry[$j];
        for ($i = 0; $i < scount($timingArray); $i++) {
            $fieldName = $timingArray[$i] . $sl1;
            $$fieldName = isset($_POST[$fieldName]) ? $_POST[$fieldName] : '';
        }
    }
}

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$var_msg =  isset($_SESSION['session_msg']) ? $_SESSION['session_msg'] : '';

unset($_SESSION['success']);
unset($_SESSION['session_msg']);

if (isset($_POST['submitsettings'])) {

    // if (SITE_TYPE == 'Demo') { // commented After Change given by KS sir Done by NModi on 11-12-20
    //     header("Location:settings.php?success=2");
    //     exit;
    // }

    if (isset($country_data_arr[$vCountry])) {
        $db_country_data = array();
        $db_country_data[] = $country_data_arr[$vCountry];
        //echo "<pre>";print_r($db_countryx);die;
    } else {
        $db_country_data = $obj->MySQLSelect("select vPhoneCode from country where vCountryCode = '$vCountry'");
    }
    if ($vCode == "") {
        $vCode = $db_country_data[0]['vPhoneCode'];
    }

    $CompanyData ['vCompany'] = $vCompany;
    $CompanyData ['vEmail'] = $vEmail;
    $CompanyData ['vContactName'] = $vContactName;
    $CompanyData ['vCode'] = $vCode;
    $CompanyData ['vPhone'] = $vPhone;
    $CompanyData ['vCountry'] = $vCountry;
    $CompanyData ['vState'] = $vState;
    $CompanyData ['vCity'] = $vCity;
    $CompanyData ['vRestuarantLocation'] = $vRestuarantLocation;
    $CompanyData ['vRestuarantLocationLat'] = $vRestuarantLocationLat;
    $CompanyData ['vRestuarantLocationLong'] = $vRestuarantLocationLong;
    $CompanyData ['vCaddress'] = $vCaddress;
    $CompanyData ['vZip'] = $vZip;
    $CompanyData ['vLang'] = $vLang;
    $CompanyData ['vAcctHolderName'] = $vAcctHolderName;
    $CompanyData ['vAcctNo'] = $vAcctNo;
    $CompanyData ['vBankName'] = $vBankName;
    $CompanyData ['vBankLocation'] = $vBankLocation;
    $CompanyData ['vSwiftCode'] = $vSwiftCode;
    $CompanyData ['vPaymentEmail'] = $vPaymentEmail;
    $CompanyData ['iServiceId'] = $iServiceId;

    //added by SP on 14-10-2020 for timeslot changes
    if (strtoupper($ENABLE_TIMESLOT_ADDON) == "YES") {
        foreach ($orgtimingArray as $orgvalue) {
            $CompanyData[$orgvalue] = date('H:i:s', strtotime($$orgvalue));
        }
    }
    else {
        $vFromMonFriTimeSlot1_arr = explode(" ", $vFromMonFriTimeSlot1);
        $CompanyData ['vFromMonFriTimeSlot1'] = $vFromMonFriTimeSlot1_arr[0];

        $vToMonFriTimeSlot1_arr = explode(" ", $vToMonFriTimeSlot1);
        $CompanyData ['vToMonFriTimeSlot1'] = $vToMonFriTimeSlot1_arr[0];

        $vFromMonFriTimeSlot2_arr = explode(" ", $vFromMonFriTimeSlot2);
        $CompanyData ['vFromMonFriTimeSlot2'] = $vFromMonFriTimeSlot2_arr[0];

        $vToMonFriTimeSlot2_arr = explode(" ", $vToMonFriTimeSlot2);
        $CompanyData ['vToMonFriTimeSlot2'] = $vToMonFriTimeSlot2_arr[0];

        $vFromSatSunTimeSlot1_arr = explode(" ", $vFromSatSunTimeSlot1);
        $CompanyData ['vFromSatSunTimeSlot1'] = $vFromSatSunTimeSlot1_arr[0];

        $vToSatSunTimeSlot1_arr = explode(" ", $vToSatSunTimeSlot1);
        $CompanyData ['vToSatSunTimeSlot1'] = $vToSatSunTimeSlot1_arr[0];

        $vFromSatSunTimeSlot2_arr = explode(" ", $vFromSatSunTimeSlot2);
        $CompanyData ['vFromSatSunTimeSlot2'] = $vFromSatSunTimeSlot2_arr[0];

        $vToSatSunTimeSlot2_arr = explode(" ", $vToSatSunTimeSlot2);
        $CompanyData ['vToSatSunTimeSlot2'] = $vToSatSunTimeSlot2_arr[0];
    }
    //update it when fOfferAppyType is set None then fOfferType is also set blank otherwise in calculation it takes flat..
    if ($fOfferAppyType == 'None') {
        $fOfferType = '';
    }
    if($fMinOrderValue > 0){
        $CompanyData ['fMinOrderValue'] = $fMinOrderValue/$db_currency[0]['Ratio'];
    }
    if(!empty($fPackingCharge) && $fPackingCharge > 0){
        $CompanyData ['fPackingCharge'] = $fPackingCharge/$db_currency[0]['Ratio'];
    }
    $CompanyData ['iMaxItemQty'] = $iMaxItemQty;
    $CompanyData ['fPrepareTime'] = $fPrepareTime;
    $CompanyData ['fOfferAppyType'] = $fOfferAppyType;
    $CompanyData ['fOfferType'] = $fOfferType;
    $CompanyData ['fTargetAmt'] = $fTargetAmt/$db_currency[0]['Ratio'];
    if($fOfferType == "Flat"){
        $CompanyData ['fOfferAmt'] = $fOfferAmt/$db_currency[0]['Ratio'];
    } else {
        $CompanyData ['fOfferAmt'] = $fOfferAmt;
    }
    if(!empty($fMaxOfferAmt) && $fMaxOfferAmt > 0){
        $CompanyData ['fMaxOfferAmt'] = $fMaxOfferAmt/$db_currency[0]['Ratio'];
    }
    if(!empty($fPricePerPerson) && $fPricePerPerson > 0){
        $CompanyData ['fPricePerPerson'] = $fPricePerPerson/$db_currency[0]['Ratio'];
    }
    $CompanyData ['eDriverOption'] = $eDriverOption;
    if($takeaway == "Yes") {
        $CompanyData ['eTakeaway'] = $eTakeaway;
    }
    
    //$CompanyData['edeliveryAvailable'] = $edeliveryAvailable;
    // $CompanyData ['fTax'] = $fTax;

    if ($fOfferAppyType == 'None') {
        $CompanyData ['fTargetAmt'] = 0;
        $CompanyData ['fOfferAmt'] = 0;
        $CompanyData ['fMaxOfferAmt'] = 0;
    }

    if ($fOfferType == 'Flat') {
        $CompanyData ['fMaxOfferAmt'] = 0;
    }
    if (isset($country_data_arr[$CompanyData ['vCode']])) {
        $CountryData = array();
        $CountryData[] = $country_data_arr[$CompanyData ['vCode']];
       // $eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    } else {
        $CountryData = $obj->MySQLSelect("SELECT eZeroAllowed,vCountryCode FROM `country` WHERE vPhoneCode = '" . $CompanyData ['vCode'] . "'");
        //$eZeroAllowed = $CountryData[0]['eZeroAllowed'];
    }
   /* if ($eZeroAllowed == 'Yes') {
        $CompanyData ['vPhone'] = $CompanyData ['vPhone'];
    } else {
        $first = substr($CompanyData ['vPhone'], 0, 1);

        if ($first == "0") {
            $CompanyData ['vPhone'] = substr($CompanyData ['vPhone'], 1);
        }
    }*/

    if ($id != '') {
        $cQuery = 'SELECT vEmail,vPhone FROM company WHERE  `iCompanyId` = "' . $id . '"';
        $CompanyOldData = $obj->MySQLSelect($cQuery);
        $OldEmail = $CompanyOldData[0]['vEmail'];
        $OldPhone = $CompanyOldData[0]['vPhone'];

        if ($OldEmail != '' && $vEmail != '') {
            if ($OldEmail != $vEmail) {
                $CompanyData['eAvailable'] = 'No';
                $CompanyData['eEmailVerified'] = 'No';
            }
        }

        if ($OldPhone != '' && $vPhone != '') {
            if ($OldPhone != $vPhone) {
                $CompanyData['eAvailable'] = 'No';
                $CompanyData['ePhoneVerified'] = 'No';
            }
        }

        $where = " `iCompanyId` = '" . $id . "'";
        //print_r($CompanyData);die;
        $company_id = $obj->MySQLQueryPerform($tbl_name, $CompanyData, 'update', $where);
    }

    $id = ($id != '') ? $id : $company_id;

    $q = "SELECT count(ccId) as total_cuisine FROM company_cuisine WHERE iCompanyId ='" . $id . "'";
    $CuisineOldData = $obj->MySQLSelect($q);
    if ($CuisineOldData[0]['total_cuisine'] > 0) {
        $q1 = "DELETE FROM company_cuisine WHERE `iCompanyId`='" . $id . "'";
        $oldid = $obj->sql_query($q1);
    }
    foreach ($cuisineId as $key => $value) {
        $cusdata['iCompanyId'] = $id;
        $cusdata['cuisineId'] = $value;
        $cusine_id = $obj->MySQLQueryPerform('company_cuisine', $cusdata, 'insert');
    }
    if ($_FILES['vCoverImage']['name'] != '') {
        /* if (isset($_POST['imagebase64'])) {*/
        $image_object = $_FILES['vCoverImage']['tmp_name'];
        $image_name = $_FILES['vCoverImage']['name'];
        $img_path = $tconfig["tsite_upload_images_compnay_path"];
        $Photo_Gallery_folder = $img_path . '/' . $id . '/';

        $temp_gallery = $img_path . '/';

        $check_file_query = "select iCompanyId,vCoverImage from company where iCompanyId=" . $id;
        $check_file = $obj->sql_query($check_file_query);
        $oldCoverImageImage = $check_file[0]['vCoverImage'];

        $check_file = $img_path . '/' . $id . '/' . $oldCoverImageImage;

        if ($oldCoverImageImage != '' && file_exists($check_file)) {
            @unlink($img_path . '/' . $id . '/' . $oldCoverImageImage);
            @unlink($img_path . '/' . $id . '/1_' . $oldCoverImageImage);
            @unlink($img_path . '/' . $id . '/2_' . $oldCoverImageImage);
            @unlink($img_path . '/' . $id . '/3_' . $oldCoverImageImage);
        }

        if (!is_dir($Photo_Gallery_folder)) {
            mkdir($Photo_Gallery_folder, 0777);
        }

        //$data = $_POST['imagebase64'];

        /*list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);

        if(!empty($data)){
            $data = base64_decode($data);*/

        $img1 = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);
        if ($img1 != '') {
            if (is_file($Photo_Gallery_folder . $img1)) {
                include_once(TPATH_CLASS . "/SimpleImage.class.php");
                $img = new SimpleImage();
                $img1 = $UPLOAD_OBJ->img_data_upload($Photo_Gallery_folder, $img1, $Photo_Gallery_folder, $tconfig["tsite_upload_images_cover_size3"], $tconfig["tsite_upload_images_cover_size4"], $tconfig["tsite_upload_images_cover_size5"], "");
            }
        }
        $vImgName = $img1;

        /* $imageName = time().'.png';
         file_put_contents($Photo_Gallery_folder.$imageName, $data);*/

        $sql = "UPDATE company SET `vCoverImage` = '" . $vImgName . "' WHERE `iCompanyId` = '" . $id . "'";
        $obj->sql_query($sql);
        //}
        /*}*/
    }

    if (strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES" && $iServiceId == 1) {
        if ($_FILES['vKioskImage']['name'] != "") {
                require_once(SITE_ADMIN_URL."/library/validation.class.php");
                $validobj = new validation();
            $image_object = $_FILES['vKioskImage']['tmp_name'];
            $image_name = $_FILES['vKioskImage']['name'];
            $img_path = $tconfig["tsite_upload_images_compnay_path"];
            $temp_gallery = $img_path . '/';
            $oldKioskImage = $_POST['oldKioskImage'];
            $check_file = $img_path . '/' . $id . '/' . $oldKioskImage;
            $filecheck = basename($_FILES['vKioskImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $flag_error = 0;
            // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            //     $flag_error = 1;
            //     $var_msg = $langage_lbl['LBL_UPLOAD_IMG_ERROR'];
            // }
            $error = "";
            
            $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl['LBL_FILE_UPLOADING_EXTENSION_MSG']);
            $error = $validobj->validateFileType($_FILES['vKioskImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
            // echo $error;exit;
            if($error){
                $var_msg = $error;
                 // getPostForm($_POST, $var_msg, "settings?success=0&var_msg=" . $var_msg);
                $_SESSION['success'] = '3';
                $_SESSION['session_msg'] = $error;
                header("location:settings");
                    exit;
            }

            if ($oldKioskImage != '' && file_exists($check_file)) {
                @unlink($img_path . '/' . $id . '/' . $oldKioskImage);
            }
            if ($flag_error == 1) {
                    getPostForm($_POST, $var_msg, "settings?success=0&var_msg=" . $var_msg);
                    exit;
            } else { 
                $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                    chmod($Photo_Gallery_folder, 0777);
                }
                $imgKiosk = $UPLOAD_OBJ->GeneralUploadImage($image_object, $image_name, $Photo_Gallery_folder, '', '', '', '', '', '', 'Y', '', $Photo_Gallery_folder);

                $sql = "UPDATE " . $tbl_name . " SET `vKioskImage` = '" . $imgKiosk . "' WHERE `iCompanyId` = '" . $id . "'";
                $obj->sql_query($sql);
            }
        }
    }

    $var_msg = 'Settings Updated Successfully.';
    $_SESSION['sess_lang'] = $vLang;


    if($_SESSION['sess_user'] == "company"){
        header("location:settings?success=1&var_msg=" . $var_msg);
    }else {
        header("location:profile?success=1&var_msg=" . $var_msg);
    }

}

// for Edit

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iCompanyId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $sql1 = "SELECT cuisineId FROM `company_cuisine` WHERE iCompanyId = '" . $id . "'";
    $db_cusinedata = $obj->MySQLSelect($sql1);
    foreach ($db_cusinedata as $key => $value) {
        $cusineselecteddata[] = $value['cuisineId'];
    }
    $db_currency_new = $obj->MySQLSelect("select vName,vSymbol,Ratio from currency where vName = '".$db_data[0]['vCurrencyCompany']."'");
    $companyratio = $db_currency_new[0]['Ratio'];
    if (scount($db_data) > 0) {
        foreach ($db_data as $key => $value) {
            $vCompany = $value['vCompany'];
            $vEmail = $value['vEmail'];
            $vContactName = cleanall(htmlspecialchars($value['vContactName']));
            $vCode = $value['vCode'];
            $vPhone = $value['vPhone'];
            $vCountry = $value['vCountry'];
            $vCity = $value['vCity'];
            $vState = $value['vState'];
            $vRestuarantLocation = $value['vRestuarantLocation'];
            $vRestuarantLocationLat = $value['vRestuarantLocationLat'];
            $vRestuarantLocationLong = $value['vRestuarantLocationLong'];
            $vCaddress = cleanall(htmlspecialchars($value['vCaddress']));
            $vZip = $value['vZip'];
            $vLang = $value['vLang'];
            $vAcctHolderName = $value['vAcctHolderName'];
            $vAcctNo = $value['vAcctNo'];
            $vBankName = $value['vBankName'];
            $vBankLocation = $value['vBankLocation'];
            $vSwiftCode = $value['vSwiftCode'];
            $vPaymentEmail = $value['vPaymentEmail'];

            //added by SP on 14-10-2020 for timeslot changes
            if (strtoupper($ENABLE_TIMESLOT_ADDON) != "YES") {
                $vFromMonFriTimeSlot1 = $value['vFromMonFriTimeSlot1'];
                $vToMonFriTimeSlot1 = $value['vToMonFriTimeSlot1'];
                $vFromMonFriTimeSlot2 = $value['vFromMonFriTimeSlot2'];
                $vToMonFriTimeSlot2 = $value['vToMonFriTimeSlot2'];
                $vFromSatSunTimeSlot1 = $value['vFromSatSunTimeSlot1'];
                $vToSatSunTimeSlot1 = $value['vToSatSunTimeSlot1'];
                $vFromSatSunTimeSlot2 = $value['vFromSatSunTimeSlot2'];
                $vToSatSunTimeSlot2 = $value['vToSatSunTimeSlot2'];
                /* if($vFromMonFriTimeSlot1  == "00:00:00" ){
                   $vFromMonFriTimeSlot1 = "";
                   $vToMonFriTimeSlot1 = "";

               } */
            }

            $fMinOrderValue = $value['fMinOrderValue']*$companyratio;
            $fPackingCharge = $value['fPackingCharge']*$companyratio;
            $iMaxItemQty = $value['iMaxItemQty'];
            $fPrepareTime = $value['fPrepareTime'];
            $fOfferAppyType = $value['fOfferAppyType'];
            $fOfferType = $value['fOfferType'];
            $fTargetAmt = $value['fTargetAmt']*$companyratio;
            if ($fOfferType == 'Flat') {
                $fOfferAmt = $value['fOfferAmt']*$companyratio;
            } else {
                $fOfferAmt = $value['fOfferAmt'];
            }
            // $fTax = ($value['fTax'] != 0) ? $value['fTax'] : '';
            $fPricePerPerson = $value['fPricePerPerson']*$companyratio;
            $oldvCoverImageImage = $value['vCoverImage'];
            $fMaxOfferAmt = $value['fMaxOfferAmt']*$companyratio;
            $iServiceId = $value['iServiceId'];
            $iServiceIdMulti = $value['iServiceIdMulti'];
            $eDriverOption = $value['eDriverOption'];
            $eTakeaway = $value['eTakeaway'];
            $oldKioskImage = $value['vKioskImage'];
            //$edeliveryAvailable = $value['edeliveryAvailable'];
        }
    }
}

$cuisineLang = $default_lang;
if (isset($_SESSION['sess_lang']) && $_SESSION['sess_lang'] != "") {
    $cuisineLang = $_SESSION['sess_lang'];
}
$cuisine_sql = "";
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories() && !empty($iServiceIdMulti)) {
    $cuisine_sql = " OR iServiceId IN (" . $iServiceIdMulti . ")";
}
$selectcuisine_sql = "SELECT cuisineId,cuisineName_" . $cuisineLang . " as cuisineName FROM  `cuisine` WHERE (iServiceId IN (" . $iServiceId . ") $cuisine_sql) AND eStatus = 'Active'";

$db_cuisine = $obj->MySQLSelect($selectcuisine_sql);
$companyLabel = $langage_lbl['LBL_COMPANY_SIGNUP'];
if (isset($_SESSION['sess_eSystem']) && strtolower($_SESSION['sess_eSystem']) == "deliverall") {
    $companyLabel = $langage_lbl['LBL_STORE_NAME'];
} else if (isset($_SESSION['sess_eSystem']) && strtolower($_SESSION['sess_eSystem']) == "organization") {
    $companyLabel = $langage_lbl['LBL_ORGANIZATION_NAME_WEB'];
}


if (isset($country_data_arr[$DEFAULT_COUNTRY_CODE_WEB])) {
    $db_countryx = array();
    $db_countryx[] = $country_data_arr[$DEFAULT_COUNTRY_CODE_WEB];
    //echo "<pre>";print_r($db_countryx);die;
} else {
    $sql_country = "select tLatitude,tLongitude from country where vCountryCode = '$DEFAULT_COUNTRY_CODE_WEB'";
    $db_countryx = $obj->MySQLSelect($sql_country);
}

$lat_default = $db_countryx[0]['tLatitude'];
$long_default = $db_countryx[0]['tLongitude'];
$isStoreDriver = $MODULES_OBJ->isStorePersonalDriverAvailable(); // Added By HJ On 20-03-2020 For Check Manage Driver By Store Enable

//$deliveryAvailable = isDeliveryAvailable() ? "Yes" : "No";
//$deliveryAvailable = "No";
?>
<!DOCTYPE html>
<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $SITE_NAME ?> | <?= $langage_lbl['LBL_SETTINGS_FRONT']; ?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <!-- End: Default Top Script and css-->
    <script src="//maps.google.com/maps/api/js?sensor=true&key=<?= $GOOGLE_SEVER_API_KEY_WEB ?>&libraries=places"
            type="text/javascript"></script>
    <script type='text/javascript' src='assets/map/gmaps.js'></script>
    <link rel="stylesheet" href="assets/css/select2/select2.min.css" type="text/css">
    <script type="text/javascript" src="assets/plugins/select2/select2.min.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

    <style>
        #image_demo img {
            width: 250px;
            height: auto;
        }

        .cover-photo-banner-control-restaurant {
            margin-top: 10px;
        }

        label {
            margin-top: 20px;
        }
    </style>

    <?php //added by SP on 14-10-2020 for timeslot changes
    if (strtoupper($ENABLE_TIMESLOT_ADDON) == "YES") { ?>
        <style>
            .setting-page-map {
                width: 100%;
                min-height: calc(100% - 106px);
                min-height: -o-calc(100% - 106px);
                min-height: -ms-calc(100% - 106px);
                min-height: -moz-calc(100% - 106px);
                min-height: -webkit-calc(100% - 106px);
                border: 1px solid #c1c1c1;
                border-radius: 4px;
                height: 500px;
            }
        </style>
    <?php } ?>

</head>
<body>
<!-- home page -->
<div id="main-uber-page" class="store-setting-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php"); ?>
    <!-- End: Left Menu-->
    <!-- Top Menu -->
    <?php include_once("top/header_topbar.php"); ?>
    <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
    <!-- End: Top Menu-->
    <!-- contact page-->

    <link rel="stylesheet" href="templates/<?= $template; ?>/assets/plugins/bootstrap/css/bootstrap.css"/>
    <section class="profile-section my-trips">
        <div class="profile-section-inner">
            <div class="profile-caption">
                <div class="page-heading">
                    <h1><?= $langage_lbl['LBL_SETTINGS_FRONT']; ?></h1>
                </div>
                <div class="button-block end">

                </div>

                <div class="driver-add-vehicle-setting">
                    <?php if (isset($_REQUEST['success'])&&  $_REQUEST['success'] == 1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= $langage_lbl['LBL_Record_Updated_successfully']; ?>
                        </div>
                    <?php } else if (isset($_REQUEST['success']) && $_REQUEST['success'] == 2) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= $langage_lbl['LBL_EDIT_DELETE_RECORD']; ?>
                        </div>
                    <?php } else if ((isset($_REQUEST['success']) && $_REQUEST['success'] == 0) || $var_msg != "")  { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?= isset($_REQUEST['error_msg']) ? $_REQUEST['error_msg'] : $var_msg; ?>
                        </div>
                    <?php } ?>

                    <form method="post" action="" class="company_form general-form" id="company_form" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= base64_encode(base64_encode($id)); ?>"/>
                        <input type="hidden" name="oldvCoverImageImage" value="<?= $oldvCoverImageImage; ?>"/>
                        <input type="hidden" name="uid" id="u_id1" value="<?= $_SESSION['sess_iUserId']; ?>">
                        <input type="hidden" name="iServiceId" id="iServiceId" value="<?= $iServiceId; ?>">
                        <!-- <input type="hidden" name="imagebase64" id="imagebase64" value="" /> -->
                        <div class="partation">
                            <div class="form-group half">
                                <label><?= $companyLabel; ?><span class="red"> *</span></label>
                                <input type="text" id="vCompany" class="" name="vCompany" id="vCompany"    value="<?= $vCompany; ?>" required>
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_PROFILE_YOUR_EMAIL_ID']; ?><?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>    <span class="red"> *</span><?php } ?></label>
                                
				<input type="text" id="vEmail" class="" name="vEmail" id="vEmail"      value="<?= $vEmail; ?>" <?php if ($ENABLE_EMAIL_OPTIONAL != "Yes") { ?> required <?php } ?> >
                            </div>
                            <div class="half-column">
                                <div class="form-group">
                                    <label><?= $langage_lbl['LBL_RESTURANT_LOCATION_WEB_TXT']; ?><span             class="red"> *</span></label>
                                    <input type="text" id="vRestuarantLocation" class="" name="vRestuarantLocation"        value="<?= $vRestuarantLocation; ?>" placeholder="" required>
                                    <input type="hidden" name="vRestuarantLocationLat" id="vRestuarantLocationLat"    value="<?= $vRestuarantLocationLat ?>">
                                    <input type="hidden" name="vRestuarantLocationLong" id="vRestuarantLocationLong"  value="<?= $vRestuarantLocationLong ?>">
                                </div>
                                <?php //added by SP on 14-10-2020 for timeslot changes
                                if (strtoupper($ENABLE_TIMESLOT_ADDON) != "YES") { ?>
                                    <div id="map" class="setting-page-map"></div>
                                <?php } ?>
                            </div>
                            <div class="half-column">
                                <div class="partation">
                                    <?php $floating = (scount($db_cuisine) > 0) ? 'floating' : ''; ?>
                                    <div class="form-group full <?php echo $floating; ?>" id="AvilableCusineSetting">
                                        <label><?= $langage_lbl['LBL_COMPANY_CUISINE_WEB']; ?><span    class="red"> *</span></label>
                                        <select class="" id="js-cuisine-multiple" name="cuisineId[]" multiple="multiple"          required>
                                            <?php foreach ($db_cuisine as $cuisinedata) { ?>
                                                
						<option name="<?= $cuisinedata['cuisineId'] ?>" value="<?= $cuisinedata['cuisineId'] ?>" <?php echo (isset($cusineselecteddata) && in_array($cuisinedata['cuisineId'], $cusineselecteddata)) ? 'selected="selected"' : ""; ?>><?= $cuisinedata['cuisineName'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="CuisineClass">
                                        </div>
                                    </div>

                                    <?php //added by SP on 14-10-2020 for timeslot changes
                                    if (strtoupper($ENABLE_TIMESLOT_ADDON) != "YES") { ?>
                                        <strong class="slot-label"><?= $langage_lbl['LBL_SLOT1_MONDAY_TO_FRIDAY']; ?>  <span class="red"> *</span></strong>
                                        <div class='form-group half'>
                                            <strong class="static-label">From</strong>
                                            <div class='input-group date' id='vFromMonFriTimeSlot1'>
                                                <input type='text' class=" TimeField" name="vFromMonFriTimeSlot1"    id="vFromMonFriTimeSlot1-1" required   value="<?= $vFromMonFriTimeSlot1; ?>"/>
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                            <span class="FromError1"></span>
                                        </div>
                                        <div class='form-group half'>
                                            <strong class="static-label">To</strong>
                                            <div class='input-group date' id='vToMonFriTimeSlot1'>
                                                <input type='text' class=" TimeField" name="vToMonFriTimeSlot1" id="vToMonFriTimeSlot-1" required    value="<?= $vToMonFriTimeSlot1; ?>"/>
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                            <span class="ToError1"></span>
                                        </div>
                                        <strong class="slot-label"><?= $langage_lbl['LBL_SLOT2_MONDAY_TO_FRIDAY']; ?></strong>
                                        <div class='form-group half'>
                                            <strong class="static-label">From</strong>
                                            <div class='input-group date' id='vFromMonFriTimeSlot2'>
                                                <input type='text' class="" name="vFromMonFriTimeSlot2"  id="vFromMonFriTimeSlot2-2" value="<?= $vFromMonFriTimeSlot2; ?>"/>
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                        </div>
                                        <div class='form-group half'>
                                            <strong class="static-label">To</strong>
                                            <div class='input-group date' id='vToMonFriTimeSlot2'>
                                                <input type='text' class="" name="vToMonFriTimeSlot2"         value="<?= $vToMonFriTimeSlot2; ?>"/>
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                        </div>
                                        <strong class="slot-label"><?= $langage_lbl['LBL_SLOT1_SATURDAY_AND_SUNDAY']; ?>  <span class="red"> *</span> </strong>
                                        <div class='form-group half'>
                                            <strong class="static-label">From</strong>
                                            <div class='input-group date' id='vFromSatSunTimeSlot1'>
                                                <input type='text' class=" TimeField" name="vFromSatSunTimeSlot1" required value="<?= $vFromSatSunTimeSlot1; ?>"/>
                                                <input type="hidden" name="new-vToSatSunTimeSlot1-1"            id="new-vToSatSunTimeSlot1-1" value="">
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                            <span class="FromError2"></span>
                                        </div>
                                        <div class='form-group half'>
                                            <strong class="static-label">To</strong>
                                            <div class='input-group date' id='vToSatSunTimeSlot1'>
                                                <input type='text' required class=" TimeField" name="vToSatSunTimeSlot1"    id="new-vToSatSunTimeSlot1" value="<?= $vToSatSunTimeSlot1; ?>"/>
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                            <span class="ToError2"></span>
                                        </div>
                                        <strong class="slot-label"><?= $langage_lbl['LBL_SLOT2_SATURDAY_AND_SUNDAY']; ?></strong>
                                        <div class='form-group half'>
                                            <strong class="static-label">To</strong>
                                            <div class='input-group date' id='vFromSatSunTimeSlot2'>
                                                <input type='text' class="" name="vFromSatSunTimeSlot2"  id="new-vFromSatSunTimeSlot2" value="<?= $vFromSatSunTimeSlot2; ?>"/>
                                                <input type="hidden" name="new-vFromSatSunTimeSlot2-2" id="new-vFromSatSunTimeSlot2-2" value="">
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                        </div>
                                        <div class='form-group half'>
                                            <strong class="static-label">To</strong>
                                            <div class='input-group date' id='vToSatSunTimeSlot2'>
                                                <input type='text' class="" name="vToSatSunTimeSlot2"  value="<?= $vToSatSunTimeSlot2; ?>"/>
                                                <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                            </span>
                                            </div>
                                        </div>
                                    <?php } ?>

                                </div>
                            </div>
                        </div>
                        <div id="map" class="setting-page-map"></div>
                        <div class="partation">
                            <!--=========================================SLOT AREA=================================-->
                            <?php
                            for ($j = 0; $j < scount($sltAry); $j++) {
                                $sl1 = $sltAry[$j];
                                ?>
                                <div class="half-column">
                                    <div class="col-lg-12 text-danger">
                                        <label><?= $langage_lbl['LBL_SLOT']; ?> <?= $sl1 ?></label>
                                    </div>
                                    <?php
                                    for ($i = 0; $i < scount($timingArray); $i++) {
                                        $slotVarName1 = $timingArray[$i] . $sl1;
                                        //echo $db_data[0][$slotVarName1];
                                        if ($db_data[0][$slotVarName1] == '00:00:00') {
                                            // echo "gg";exit;
                                        }
                                        ?>

                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <?php if (substr($timingArray[$i], 1, -8) == 'Mon' || substr($timingArray[$i], 1, -6) == 'Mon') {
                                                            $datelabel = $langage_lbl['LBL_MONDAY_TXT'];
                                                        } else if (substr($timingArray[$i], 1, -8) == 'Tue' || substr($timingArray[$i], 1, -6) == 'Tue') {
                                                            $datelabel = $langage_lbl['LBL_TUESDAY_TXT'];
                                                        } else if (substr($timingArray[$i], 1, -8) == 'Wed' || substr($timingArray[$i], 1, -6) == 'Wed') {
                                                            $datelabel = $langage_lbl['LBL_WEDNESDAY_TXT'];
                                                        } else if (substr($timingArray[$i], 1, -8) == 'Thu' || substr($timingArray[$i], 1, -6) == 'Thu') {
                                                            $datelabel = $langage_lbl['LBL_THURSDAY_TXT'];
                                                        } else if (substr($timingArray[$i], 1, -8) == 'Fri' || substr($timingArray[$i], 1, -6) == 'Fri') {
                                                            $datelabel = $langage_lbl['LBL_FRIDAY_TXT'];
                                                        } else if (substr($timingArray[$i], 1, -8) == 'Sat' || substr($timingArray[$i], 1, -6) == 'Sat') {
                                                            $datelabel = $langage_lbl['LBL_SATURDAY_TXT'];
                                                        } else if (substr($timingArray[$i], 1, -8) == 'Sun' || substr($timingArray[$i], 1, -6) == 'Sun') {
                                                            $datelabel = $langage_lbl['LBL_SUNDAY_TXT'];
                                                        } ?>
                                                        <span><?php echo $datelabel; ?> </span>
                                                        <?php if ($sl1 == 1) { ?> <span class="red"
                                                                                        style="display: inline-block; color: #d63b3b; font-size: 20px; vertical-align: top;  height: 10px; "> *</span> <?php } ?>
                                                        <div class='input-group date timepickerField'>
                                                            <?php
                                                            if ($sl1 == 1) {
                                                                $required = "required";
                                                            } else {
                                                                $required = "";
                                                            }
                                                            ?>
                                                            <input type='text' class="form-control"      name="<?= $slotVarName1 ?>"       value="<?= $db_data[0][$slotVarName1]; ?>" <?= $required ?> />
                                                            <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                        </div>
                                                        <span class="FromError1"></span>
                                                    </div>
                                                </div>
                                                <div class='col-lg-2' style="text-align: center;">
                                                    <div style="font-weight: bold;"><?= $langage_lbl['LBL_To']; ?></div>
                                                </div>
                                                <?php
                                                $i++;
                                                $slotVarName2 = $timingArray[$i] . $sl1;
                                                ?>
                                                <div class='col-lg-5'>
                                                    <div class="form-group">
                                                        <span><!-- <?= substr($timingArray[$i], 1, -6) ?> --><?php echo $datelabel; ?></span>
                                                        <div class='input-group date timepickerField'>
                                                            <input type='text' class="form-control TimeField"           name="<?= $slotVarName2 ?>"                             value="<?= $db_data[0][$slotVarName2]; ?>"/>
                                                            <span class="input-group-addon">
                                                        <span class="glyphicon glyphicon-calendar"></span>
                                                    </span>
                                                        </div>
                                                        <span class="ToError1"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div class="half-column">
                                <strong>&nbsp;</strong>
                                <div class="form-group">
                                    <label><?= $langage_lbl['LBL_PROFILE_ADDRESS']; ?><span class="red">*</span></label>
                                    <input type="text" class="profile-address-input"
                                           value="<?= cleanall(htmlspecialchars($vCaddress)); ?>" name="vCaddress"
                                           required>
                                </div>
                            </div>

                            <div class="half-column">
                                <div class="form-group">
                                    <strong><?= $langage_lbl['LBL_SELECT_CONTRY']; ?><span class="red"> *</span>
                                    </strong>
                                    <?php
                                    if (scount($db_country) > 1) {
                                        $style = "";
                                    } else {
                                        $style = " disabled=disabled";
                                    } ?>
                                    <select <?= $style ?> class="vCountry" name="vCountry" id="vCountry"
                                                          onChange="changeCode(this.value); setState(this.value, '');"
                                                          required>
                                        <?php for ($i = 0; $i < scount($db_country); $i++) { ?>
                                            <option
                                                <?php if ($vCountry == $db_country[$i]['vCountryCode']) { ?>selected<?php } ?>
                                                value="<?= $db_country[$i]['vCountryCode'] ?>"><?= $db_country[$i]['vCountry'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="required-label" id="vCountryCheck"></div>
                                </div>
                            </div>

                            <div class="half-column">
                                <strong>&nbsp;</strong>
                                <div class="form-group">
                                    <label id="selectstatelblc"></label>
                                    <select class="vState" name='vState' id="vState"
                                            onChange="setCity(this.value, '');">
                                        <option value=""><?= $langage_lbl['LBL_SELECT_TXT']; ?></option>
                                    </select>
                                </div>
                            </div>

                            <?php if ($SHOW_CITY_FIELD == 'Yes') { ?>
                                <div class="form-group half">
                                    <strong><?= $langage_lbl['LBL_CITY_TXT']; ?></strong>
                                    <select class="" name='vCity' id="vCity">
                                        <option value=""><?= $langage_lbl['LBL_SELECT_CITY']; ?></option>
                                    </select>
                                </div>
                            <?php } ?>
                            <div class="half-column">
                                <strong>&nbsp;</strong>
                                <div class="form-group">
                                    <label><?= $langage_lbl['LBL_ZIP_CODE']; ?> <span class="red">*</span></label>
                                    <input type="text" class="profile-address-input" value="<?= $vZip; ?>" name="vZip"
                                           required>
                                </div>
                            </div>
                            <div class="half-column">
                                <strong>&nbsp;</strong>
                                <div class="form-group">
                                    <label><?= $langage_lbl['LBL_WEB_CONTACT_PERSON_NAME']; ?><span
                                                class="red"> *</span></label>
                                    <input type="text" class="" name="vContactName" id="vContactName"
                                           value="<?= $vContactName; ?>" required>
                                </div>
                            </div>
                            <div class="half-column">
                                <strong>&nbsp;</strong>
                                <div class="form-group phone-column">
                                    <input type="text" class="phonecode" id="code" name="vCode" value="<?= $vCode ?>"
                                           readonly/>
                                    <input type="text" class="" name="vPhone" id="vPhone" value="<?= $vPhone; ?>"
                                           placeholder="<?= $langage_lbl['LBL_Phone_Number']; ?>" required>
                                </div>
                            </div>
                            <div class="form-group half">
                                <?php if (scount($db_lang) <= 1) { ?>
                                    <input name="vLang" type="hidden" class="create-account-input"
                                           value="<?php echo $db_lang[0]['vCode']; ?>"/>
                                <?php } else { ?>

                                    <select class="" name='vLang' required>
                                        <option value="">--select--</option>
                                        <?php for ($i = 0; $i < scount($db_lang); $i++) { ?>
                                            <option value="<?= $db_lang[$i]['vCode'] ?>" <?= ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>>
                                                <?= $db_lang[$i]['vTitle'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                <?php } ?>
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_PAYMENT_EMAIL_TXT']; ?></label>
                                <input type="email" class="" name="vPaymentEmail" id="vPaymentEmail"
                                       value="<?= $vPaymentEmail ?>">
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_WEB_ACCOUNT_HOLDER_NAME']; ?></label>
                                <input type="text" class="" name="vAcctHolderName" id="vAcctHolderName"
                                       value="<?= $vAcctHolderName ?>">
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_WEB_ACCOUNT_NUMBER']; ?></label>
                                <input type="text" class="" name="vAcctNo" id="vAcctNo" value="<?= $vAcctNo ?>"/>
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_BANK_NAME']; ?></label>
                                <input type="text" class="" name="vBankName" id="vBankName" value="<?= $vBankName ?>">
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_BANK_LOCATION_WEB']; ?></label>
                                <input type="text" class="" name="vBankLocation" id="vBankLocation"
                                       value="<?= $vBankLocation ?>">
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_BIC_SWIFT_CODE']; ?></label>
                                <input type="text" class="" name="vSwiftCode" id="vSwiftCode"
                                       value="<?= $vSwiftCode ?>">
                            </div>

                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_ESTIMATED_TIME_PREPARE_ORDER']; ?>
                                    (<?= $langage_lbl['LBL_IN_MINUTES_TXT']; ?>)<span class="red">*</span></label>
                                <input type="text" class="" name="fPrepareTime" id="fPrepareTime"
                                       value="<?= $fPrepareTime; ?>" required>
                            </div>
                            <!-- new Field added -->
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_MIN_AMOUNT_PER_ORDER']; ?>
                                    (In <?= $db_currency[0]['vName'] ?>) </label>
                                <input type="text" class="" name="fMinOrderValue" id="fMinOrderValue"
                                       value="<?= $fMinOrderValue; ?>">
                            </div>
                            <div class="form-group half">
                                <label><?= $langage_lbl['LBL_MAX_ALLOW_QTY_BY_USER']; ?><span
                                            class="red">*</span></label>
                                <input type="text" class="" name="iMaxItemQty" id="iMaxItemQty"
                                       value="<?= $iMaxItemQty; ?>" required>
                            </div>
                            <!-- new Field added -->

                            <div class="half-column">
                                <div class="form-group">
                                    <strong><?= $langage_lbl['LBL_OFFER_APPLY_ON_TXT']; ?><span
                                                class="red"> *</span></strong>
                                    <select class="" name="fOfferAppyType" id="fOfferAppyType" required>
                                        <option value="None" <?php if ($fOfferAppyType == 'None') {
                                            echo 'selected';
                                        } ?>><?= $langage_lbl['LBL_OPTION_NONE']; ?></option>
                                        <option value="First" <?php if ($fOfferAppyType == 'First') {
                                            echo 'selected';
                                        } ?>><?= $langage_lbl['LBL_FIRST_ORDER']; ?></option>
                                        <option value="All" <?php if ($fOfferAppyType == 'All') {
                                            echo 'selected';
                                        } ?>><?= $langage_lbl['LBL_ALL_ORDER']; ?></option>
                                    </select>
                                    <small class="notes">[<?= $langage_lbl['LBL_OFFER_NOTE'] ?>]</small>
                                </div>
                            </div>
                            <div class="form-group half" id="fOfferTypeDiv">
                                <strong><?= $langage_lbl['LBL_OFFER_TYPE_WEB_TXT'] ?></strong>
                                <select class="" name="fOfferType" id="fOfferType">
                                    <option value="Flat" <?php if ($fOfferType == 'Flat') {
                                        echo 'selected';
                                    } ?>><?= $langage_lbl['LBL_FLAT_OFFER'] ?></option>
                                    <option value="Percentage" <?php if ($fOfferType == 'Percentage') {
                                        echo 'selected';
                                    } ?>><?= $langage_lbl['LBL_PERCENTAGE_OFFER'] ?></option>
                                </select>
                            </div>
                            <div class="half-column" id="fOfferAmtDiv">
                                <div class="form-group">
                                    <strong><?= $langage_lbl['LBL_OFFER_DISCOUNT_TXT'] ?><span class="addnote"
                                                                                               style="float: none;"></span><span
                                                class="red"> *</span></strong>
                                    <input type="text" class="" name="fOfferAmt" id="fOfferAmt"
                                           value="<?= $fOfferAmt; ?>">
                                </div>
                            </div>
                            <div class="half-column" id="fMaxOfferAmtDiv">
                                <div class="form-group">
                                    <strong><?= $langage_lbl['LBL_MAX_OFF_AMOUNT'] ?>
                                        (In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign"
                                                                                data-placement="top"
                                                                                data-toggle="tooltip"
                                                                                data-original-title='Set the amount to limit user to get the maximum off amount on each order. E.g. If offer is 50% off, and maximum off amount is <?= $db_currency[0]['vSymbol']; ?>250, then on order of <?= $db_currency[0]['vSymbol']; ?>2000 user can get <?= $db_currency[0]['vSymbol']; ?>250 off, but not <?= $db_currency[0]['vSymbol']; ?>1000 off.'></i></strong>
                                    <input type="text" class="" name="fMaxOfferAmt" id="fMaxOfferAmt"
                                           value="<?= $fMaxOfferAmt; ?>">
                                </div>
                            </div>
                            <?php if ($isStoreDriver > 0) { ?>
                                <div class="form-group half">
                                    <strong><?= $langage_lbl['LBL_PROVIDER_SELECTION_TXT'] ?></strong>
                                    <select class="" name="eDriverOption" id="eDriverOption">
                                        <option value="All" <?php
                                        if ($eDriverOption == 'All') {
                                            echo 'selected';
                                        }
                                        ?>><?= $langage_lbl['LBL_BOTH_DELIEVERY_DRIVERS']; ?></option>
                                        <option value="Personal" <?php
                                        if ($eDriverOption == 'Personal') {
                                            echo 'selected';
                                        }
                                        ?>><?= $langage_lbl['LBL_PERSONAL_DELIVERY_DRIVER']; ?></option>
                                        <option value="Site" <?php
                                        if ($eDriverOption == 'Site') {
                                            echo 'selected';
                                        }
                                        ?>><?= $langage_lbl['LBL_SITE_DELIVERY_DRIVER']; ?></option>
                                    </select>
                                </div>
                            <?php } ?>
                            <div class="half-column" id="fTargetAmtDiv">
                                <div class="half-column half-new">
                                    <div class="form-group">
                                        <strong><?= $langage_lbl['LBL_TARGET_AMOUNT_TXT'] ?>(In <?= $db_currency[0]['vName'] ?>) <i class="icon-question-sign" data-placement="top"
                                        data-toggle="tooltip" data-original-title='"Set the minimum total order amount to avail the offer. E.g. "Get <?= $db_currency[0]['vSymbol']; ?>7 off on order above <?= $db_currency[0]['vSymbol']; ?>50" OR "Get 20% off on order above <?= $db_currency[0]['vSymbol']; ?>50", so <?= $db_currency[0]['vSymbol']; ?>50 is the target amount to get the off."'></i>
                                        </strong>
                                        <input type="text" class="" name="fTargetAmt" id="fTargetAmt"
                                               value="<?= $fTargetAmt; ?>">
                                        <small class="notes">
                                            <span><?php echo $langage_lbl['LBL_NOTE_FRONT'] ?></span>
                                            <?php  $langage_lbl['LBL_NOTE_FOR_TARGET_AMOUNT'] = str_replace("####", $db_currency[0]['vSymbol'],  $langage_lbl['LBL_NOTE_FOR_TARGET_AMOUNT']); 
                                            echo $langage_lbl['LBL_NOTE_FOR_TARGET_AMOUNT'];?>
                                        </small>
                                    </div>


                                </div>
                            </div>

                            <?php if ($iServiceId == '1') { ?>
                                <div class="form-group half">
                                    <strong><?= $langage_lbl['LBL_COST_PER_ORDER']; ?>
                                        (In <?= $db_currency[0]['vName'] ?>)<span class="red"> *</span></strong>
                                    <input type="text" class="" name="fPricePerPerson" id="fPricePerPerson"
                                           value="<?= $fPricePerPerson; ?>"
                                           placeholder="<?= $langage_lbl['LBL_COST_PER_ORDER']; ?> ">
                                </div>
                            <?php } ?>
                            <?php if ($takeaway == 'Yes') { ?>
                                <div class="form-group half">
                                    <strong><?= $langage_lbl['LBL_TAKE_AWAY'] ?></strong>
                                    <select class="" name="eTakeaway">
                                        <!--<option value=''><?= $langage_lbl_admin['LBL_SELECT_TXT']; ?></option>-->
                                        <option value="Yes" <?php if ($eTakeaway == 'Yes') {
                                            echo 'selected';
                                        } ?>><?= $langage_lbl['LBL_BTN_YES_TXT'] ?></option>
                                        <option value="No" <?php if ($eTakeaway == 'No' || $eTakeaway == '') {
                                            echo 'selected';
                                        } ?>><?= $langage_lbl['LBL_BTN_NO_TXT'] ?></option>
                                    </select>
                                </div>
                            <?php } //if($deliveryAvailable=='Yes') { ?>
                            <!--<div class="row">
                                    <div class="col-lg-12">
                                        <label><?= $langage_lbl['LBL_DELIVERY_AVAILABLE'] ?></label>
                                    </div>
                                    <div class="col-lg-12">
                                        <select class="form-control" name="edeliveryAvailable" id="edeliveryAvailable">
                                            <option value="Yes" <?php if ($edeliveryAvailable == 'Yes') {
                                echo 'selected';
                            } ?>><?= $langage_lbl['LBL_BTN_YES_TXT'] ?></option>
                                            <option value="No" <?php if ($edeliveryAvailable == 'No') {
                                echo 'selected';
                            } ?>><?= $langage_lbl['LBL_BTN_NO_TXT'] ?></option>
                                        </select>
                                    </div>
                                </div>-->
                            <?php //} ?>

                            <?php if (strtoupper(ENABLE_ORDER_FROM_STORE_KIOSK) == "YES" && $iServiceId == 1) { ?>
                                <div class="form-group half">
                                    <strong><?php echo $langage_lbl['LBL_KIOSK_IMG_TXT'] ?></strong>
                                    <div class="imageupload">
                                        <div class="file-tab">
                                        <span id="single_img001">
                                        <?php
                                        $imgpth = $tconfig["tsite_upload_images_compnay_path"] . '/' . $id . '/' . $oldKioskImage;

                                        $imgUrl = $tconfig["tsite_upload_images_compnay"] . '/' . $id . '/' . $oldKioskImage;

                                        if ($oldKioskImage != "" && file_exists($imgpth)) {

                                            ?>
                                            <img src="<?php echo $imgUrl; ?>" alt="Image preview" class="thumbnail"
                                                 style="max-width: 250px; max-height: 250px">
                                        <?php } ?>
                                        </span>
                                            <div class="fileUploading_main">
                                                <input type="hidden" name="oldKioskImage"
                                                       value="<?= trim($oldKioskImage); ?>">
                                                <div class="fileUploading" filechoose="<?= $langage_lbl['LBL_CHOOSE_FILE'] ?>">
                                                    <!-- <input name="vKioskImage" onchange="preview_mainImg(event);"
                                                           type="file" accept="image/*"> -->
                                                           <input name="vKioskImage" onchange="preview_mainImg(event);"
                                                           type="file">
                                                </div>
                                                [Note: Recommended dimension is 1536px * 2048px.]

                                                <!--added by SP for required validation add in menu item image when recommended is on on 26-07-2019 -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                            <input type="hidden" name="new-vToMonFriTimeSlot-1" id="new-vToMonFriTimeSlot-1" value="">
                            <input type="hidden" name="new-vFromMonFriTimeSlot2-2" id="new-vFromMonFriTimeSlot2-2"
                                   value="">
                        </div>
                        <div class="button-block">
                            <input type="submit" class="gen-btn" name="submitsettings" id="submitsettings"
                                   value="<?= $langage_lbl['LBL_SAVE_SETTINGS']; ?>">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <!-- footer part -->
    <?php include_once('footer/footer_home.php'); ?>
    <!-- footer part end -->
    <!-- End:contact page-->
    <div style="clear:both;"></div>
</div>
<!-- home page end-->
<!-- Footer Script -->
<?php include_once('top/footer_script.php');
$lang = $LANG_OBJ->getLanguageData($_SESSION['sess_lang'])['vLangCode']; ?>
<script type="text/javascript"
        src="<?php echo $tconfig["tsite_url_main_admin"] ?>js/validation/jquery.validate.min.js"></script>
<?php if ($lang != 'en') { ?>
    <script type="text/javascript" src="assets/js/validation/localization/messages_<?= $lang; ?>.js"></script>
<?php } ?>
<script type="text/javascript" src="assets/js/validation/additional-methods.js"></script>
<script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="assets/js/moment.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen"
      href="assets/css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="assets/js/bootstrap-datetimepicker.min.js"></script>
<link rel="stylesheet" href="assets/css/croppie.css"/>
<script src="assets/js/croppie.js"></script>
<script>
    var map;

    function initialize() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: parseFloat('<?= $lat_default ?>'), lng: parseFloat('<?= $long_default ?>')},
            zoom: 8
        });

        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });

        $('#vRestuarantLocation').keyup(function (e) {
            buildAutoComplete("vRestuarantLocation", e, "<?=$MIN_CHAR_REQ_GOOGLE_AUTO_COMPLETE;?>", "<?=$_SESSION['sess_lang'];?>", function (latitude, longitude, address) {

                var location = new google.maps.LatLng(latitude, longitude);
                map.setCenter(location);
                map.setZoom(17);
                marker.setPosition(location);
                marker.setVisible(true);

                $("#vRestuarantLocation").val(address);
                $("#vRestuarantLocationLat").val(latitude);
                $("#vRestuarantLocationLong").val(longitude);


            });
        });

        if ($("#vRestuarantLocation").val() != "") {
            var myLatLng = new google.maps.LatLng($("#vRestuarantLocationLat").val(), $("#vRestuarantLocationLong").val());
            marker.setPosition(myLatLng);
            map.setCenter(myLatLng);
            map.setZoom(17);
            marker.setVisible(true);
        }
    }

    google.maps.event.addDomListener(window, 'load', initialize);

    $(function () {

        <?php //added by SP on 14-10-2020 for timeslot changes
        if(strtoupper($ENABLE_TIMESLOT_ADDON) == "YES") { ?>
        $('.timepickerField').datetimepicker({
            format: 'hh:mm A',
            ignoreReadonly: true,
        });
        <?php } else { ?>
        $('#vFromMonFriTimeSlot1').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
        });
        $('#vToMonFriTimeSlot1').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
            useCurrent: false //Important! See issue #1075
        });

        /* $("#vFromTimeSlot1").on("dp.change", function (e) {
             $('#vToTimeSlot1').data("DateTimePicker").minDate(e.date);
         });
         $("#vToTimeSlot1").on("dp.change", function (e) {
             $('#vFromTimeSlot1').data("DateTimePicker").maxDate(e.date);
         });*/

        $('#vFromMonFriTimeSlot2').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
        });
        $('#vToMonFriTimeSlot2').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
            useCurrent: false
        });


        /*$("#vFromTimeSlot2").on("dp.change", function (e) {
            $('#vToTimeSlot2').data("DateTimePicker").minDate(e.date);
        });
        $("#vToTimeSlot2").on("dp.change", function (e) {
            $('#vFromTimeSlot2').data("DateTimePicker").maxDate(e.date);
        });*/

        $('#vFromSatSunTimeSlot1').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
        });
        $('#vToSatSunTimeSlot1').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
            useCurrent: false //Important! See issue #1075
        });

        $('#vFromSatSunTimeSlot2').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
        });
        $('#vToSatSunTimeSlot2').datetimepicker({
            format: 'HH:mm A',
            ignoreReadonly: true,
            useCurrent: false
        });

        $("#vToMonFriTimeSlot1").on("dp.change", function (e) {
            var vToMonFriTimeSlotstartTime = $('#vToMonFriTimeSlot-1').val();
            var st = minFromMidnight(vToMonFriTimeSlotstartTime);
            $('#new-vToMonFriTimeSlot-1').val(st);

        });

        $("#vFromMonFriTimeSlot2").on("dp.change", function (e) {
            var vFromMonFriTimeSlot2 = $('#vFromMonFriTimeSlot2-2').val();
            var st = minFromMidnight(vFromMonFriTimeSlot2);
            $('#new-vFromMonFriTimeSlot2-2').val(st);

        });

        $("#vToSatSunTimeSlot1").on("dp.change", function (e) {
            var vToSatSunTimeSlot1 = $('#new-vToSatSunTimeSlot1').val();
            var st = minFromMidnight(vToSatSunTimeSlot1);
            $('#new-vToSatSunTimeSlot1-1').val(st);
        });

        $("#vFromSatSunTimeSlot2").on("dp.change", function (e) {
            var vFromSatSunTimeSlot2 = $('#new-vFromSatSunTimeSlot2').val();
            var st = minFromMidnight(vFromSatSunTimeSlot2);
            $('#new-vFromSatSunTimeSlot2-2').val(st);
        });

        <?php } ?>


        /*Offer Apply Type*/
        var fOfferAppyType = $('#fOfferAppyType').val();
        var fOfferTypeval = $('#fOfferType').val();
        if (fOfferAppyType == 'None') {
            $("#fOfferTypeDiv").hide();
            $("#fTargetAmtDiv").hide();
            $('#fTargetAmt').removeAttr('required');
            $("#fOfferAmtDiv").hide();
            $("#fMaxOfferAmtDiv").hide();
            $('#fOfferAmt').removeAttr('required');
        } else {
            $("#fOfferTypeDiv").show();
            $("#fTargetAmtDiv").show();
            $("#fOfferAmtDiv").show();

            if (fOfferAppyType != 'None' && fOfferTypeval == 'Percentage') {
                $("#fMaxOfferAmtDiv").show();
            }
            if (fOfferAppyType != 'None' && fOfferTypeval == 'Flat') {
                $('#fTargetAmt').attr('required', 'required');
            } else {
                $('#fTargetAmt').removeAttr('required');
            }
            $('#fOfferAmt').attr('required', 'required');
        }

        $('#fOfferAppyType').on('change', function () {
            var fOfferAppyTypechange = this.value;
            var fOfferTypevalonchange = $('#fOfferType').val();
            if (fOfferAppyTypechange == 'None') {
                $("#fOfferTypeDiv").hide();
                $("#fTargetAmtDiv").hide();
                $("#fOfferAmtDiv").hide();
                $("#fMaxOfferAmtDiv").hide();
                $('#fOfferAmt').removeAttr('required');
                $('#fTargetAmt').removeAttr('required');
            } else {
                $("#fOfferTypeDiv").show();
                $("#fTargetAmtDiv").show();
                $("#fOfferAmtDiv").show();

                if (fOfferAppyTypechange != 'None' && fOfferTypevalonchange == 'Percentage') {
                    $("#fMaxOfferAmtDiv").show();
                }
                if (fOfferAppyTypechange != 'None' && fOfferTypevalonchange == 'Flat') {
                    $('#fTargetAmt').attr('required', 'required');
                } else {
                    $('#fTargetAmt').removeAttr('required');
                }
                $('#fOfferAmt').attr('required', 'required');
            }
        });

        /*Offer Type*/

        var fOfferType1 = $('#fOfferType').val();
        var fOfferAppyType1 = $('#fOfferAppyType').val();
        if (fOfferAppyType1 != 'None' && fOfferType1 == 'Percentage') {
            $("#fMaxOfferAmtDiv").show();
            $('#fTargetAmt').removeAttr('required');
            $(".addnote").html("(%)");
        } else {
            $("#fMaxOfferAmtDiv").hide();
            $(".addnote").html("(In <?=$db_currency[0]['vName']?>)");
            if (fOfferAppyType1 != 'None' && fOfferType1 == 'Flat') {
                $('#fTargetAmt').attr('required', 'required');
            } else {
                $('#fTargetAmt').removeAttr('required');
            }
        }

        $('#fOfferType').on('change', function () {
            var fOfferAppyType2 = $('#fOfferAppyType').val();
            var fOfferType2 = this.value;
            if (fOfferAppyType2 != 'None' && fOfferType2 == 'Percentage') {
                $("#fMaxOfferAmtDiv").show();
                $('#fTargetAmt').removeAttr('required');
                $(".addnote").html("(%)");
            } else {
                $("#fMaxOfferAmtDiv").hide();
                if (fOfferAppyType2 != 'None' && fOfferType2 == 'Flat') {
                    $('#fTargetAmt').attr('required', 'required');
                } else {
                    $('#fTargetAmt').removeAttr('required');
                }
                $(".addnote").html("(In <?=$db_currency[0]['vName']?>)");
            }
        });

    });

    var interval;
    $(document).ready(function () {
        $('#js-cuisine-multiple').select2();


        <?php //added by SP on 14-10-2020 for timeslot changes
        if(strtoupper($ENABLE_TIMESLOT_ADDON) != "YES") { ?>
        var startTime = $('#vToMonFriTimeSlot-1').val();
        var st = minFromMidnight(startTime);
        $('#new-vToMonFriTimeSlot-1').val(st);

        var vFromMonFriTimeSlot2 = $('#vFromMonFriTimeSlot2-2').val();
        var st2 = minFromMidnight(vFromMonFriTimeSlot2);
        $('#new-vFromMonFriTimeSlot2-2').val(st2);
        <?php } ?>

    });

    $(document).on('blur', '.select2-search__field', function () {

        if ($('#js-cuisine-multiple').select2('data').length > 0) {
            clearTimeout(interval);
            interval = setTimeout(function () {
                $('#AvilableCusineSetting').addClass('floating');
            }, 10);
        }
    });

    $(function () {

        $.validator.addMethod("greaterThan",
            function (value, element, param) {
                var $min = $(param);
                if (this.settings.onfocusout) {
                    $min.off(".validate-greaterThan").on("blur.validate-greaterThan", function () {
                        $(element).valid();
                    });
                }
                if (param != '') {
                    return parseInt(value) > parseInt($min.val());
                } else {
                    return true;
                }
            }, "Max must be greater than min");

//Company Start
        var errormessage;
        user = '<?= $user?>';
//var countryCode = $("#code").val();
        var countryCode = "<?php echo $vCountry; ?>";
        var dataa = {};
        dataa.iCompanyId = "<?= $_SESSION['sess_iUserId'];?>";
        dataa.usertype = 'store';
        dataa.vCountry = countryCode;
        if ($('#company_form').length !== 0) {

            $.validator.addMethod('ge', function (value, element, param) {
                var value2 = $('#new-vFromMonFriTimeSlot2-2').val();
                return this.optional(element) || parseInt(value2) > parseInt($(param).val());
            }, '<?= addslashes($langage_lbl['LBL_SLT_2_FRM_RESTRICT']); ?>');


            $.validator.addMethod('gesun', function (value, element, param) {
                var value2 = $('#new-vFromSatSunTimeSlot2-2').val();
                return this.optional(element) || parseInt(value2) > parseInt($(param).val());
            }, '<?= addslashes($langage_lbl['LBL_SLT_2_FRM_RESTRICT']); ?>');


            $('#company_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    if (element.attr("name") == "cuisineId[]") {
                        error.insertAfter(".CuisineClass");
                    } else if (element.attr("name") == "vFromMonFriTimeSlot1") {
                        error.appendTo(".FromError1");
                    } else if (element.attr("name") == "vToMonFriTimeSlot1") {
                        error.appendTo(".ToError1");
                    } else if (element.attr("name") == "vFromSatSunTimeSlot1") {
                        error.appendTo(".FromError2");
                    } else if (element.attr("name") == "vToSatSunTimeSlot1") {
                        error.appendTo(".ToError2");
                    } else if (element.attr("name") == "vFromMonFriTimeSlot2") {
                        error.appendTo(".ToErrortime");
                    } else if (element.attr("name") == "vFromSatSunTimeSlot2") {
                        error.appendTo(".ToErrortimeSun");
                    } else if (element.attr("name") == "vKioskImage") {
                       error.appendTo(".fileUploading_main");
                    } else {
                        error.insertAfter(element);
                    }
                },
                invalidHandler: function (form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        validator.errorList[0].element.focus();
                    }
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },

                rules: {
                    vCompany: {required: true, minlength: 2},
                    vContactName: {required: true},
                    vEmail: {
                        <?php if($ENABLE_EMAIL_OPTIONAL != "Yes") { ?>required: true, <?php } ?>
                        email: true,
                        remote: {
                            url: 'ajax_validate_email.php',
                            type: "post",
                            cache: false,
                            data: {
                                id: function (e) {
                                    return $('#vEmail').val();
                                },
                                usr: function (e) {
                                    return user;
                                },
                                uid: function (e) {
                                    return $("#u_id1").val();
                                },
                                action:'Edit-Profile'
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "<?= addslashes(trim($langage_lbl['LBL_CHECK_DELETE_ACCOUNT'])); ?>";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "<?= addslashes(trim($langage_lbl['LBL_EMAIL_EXISTS_MSG'])); ?>";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true,//phonevalidate: true,
                        remote: {
                            url: 'ajax_driver_mobile_new.php',
                            type: "post",
                            data: dataa,
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_CHECK_DELETE_ACCOUNT']); ?>";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "<?= addslashes($langage_lbl['LBL_PHONE_EXIST_MSG']); ?>";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    // vFromMonFriTimeSlot2: {ge: '#new-vToMonFriTimeSlot-1'},
                    // vFromSatSunTimeSlot2: {gesun: '#new-vToSatSunTimeSlot1-1'},

                    vCaddress: {required: true, minlength: 2},
                    vZip: {required: true, minlength: 2},
                    vLang: {required: true},
                    'cuisineId[]': {required: true},
                    fMinOrderValue: {/*required: true,*/number: true},
                    fPackingCharge: {number: true},
                    iMaxItemQty: {required: true, digits: true, min: 1},
                    fPrepareTime: {required: true, digits: true},
                    fOfferAppyType: {required: true},
                    vCountry: {required: true},
                    vState: {required: true},
                    vRestuarantLocation: {required: true},
                    fOfferAmt: {
                        number: function () {
                            return $("#fOfferAmt").prop('required');
                        },
                        min: function () {
                            return $("#fOfferAmt").prop('required');
                            //    return $("#fOfferAmtDiv").is(":visible");
                        },
                        max: function () {
                            if ($("#fOfferAmt").prop('required') == true && $("#fOfferType").val() == 'Percentage') {
                                return 100;
                            }
                        }
                    },
                    fTargetAmt: {
                        number: function () {
                            if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                return true;
                            }
                        },
                        greaterThan: function () {
                            //console.log($("#fTargetAmt").prop('required'));
                            if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                return '#fOfferAmt';
                            } else {
                                return '';
                            }
                        },
                        min: function () {
                            return $("#fTargetAmt").prop('required');
                        }
                    },
                    fMaxOfferAmt: {number: true},
                    fPricePerPerson: {required: true, number: true},
                    vKioskImage:{
                        extension: imageUploadingExtenstionjsrule
                    }
                },
                messages: {
                    vKioskImage:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vCompany: {
                        required: '<?= addslashes($langage_lbl['LBL_STORE_REQUIRED_MSG_FRONT']); ?>',
                        minlength: '<?= addslashes($langage_lbl['LBL_STORE_NAME_MINIMUM_MSG_FRONT']); ?>'
                    },
                    vEmail: {
                        required: '<?= addslashes($langage_lbl['LBL_EMAIL_ADDRESS_FRONT_MSG']); ?>',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vContactName: {
                        required: '<?= addslashes($langage_lbl['LBL_STORE_CONTACT_PERSON_NAME_TXT']); ?>'
                    },
                    vPhone: {
                        required: '<?= addslashes($langage_lbl['LBL_PHONE_REQUIRED_TXT']); ?>',
                        minlength: '<?= addslashes($langage_lbl['LBL_PHONE_MINIMUM_MSG_FRONT']); ?>',
                        digits: '<?= addslashes($langage_lbl['LBL_PHONE_VALID_MSG_FRONT']); ?>',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: '<?= addslashes($langage_lbl['LBL_ADDRESS_REQUIRED_FRONT']); ?>'
                    },
                    vZip: {
                        required: '<?= addslashes($langage_lbl['LBL_ZIP_CODE_FRONT_WEB']); ?>'
                    },
                    vLang: {
                        required: '<?= addslashes($langage_lbl['LBL_LANG_REQUIRED_FRONT']); ?>'
                    },
                    'cuisineId[]': {
                        required: '<?= addslashes($langage_lbl['LBL_SELECT_CUISINE_FRONT']); ?>'
                    },
                    vCountry: {
                        required: '<?= addslashes($langage_lbl['LBL_COUNTRY_REQUIRED_FRONT']); ?>'
                    },
                    fOfferAmt: {
                        min: '<?= addslashes($langage_lbl['LBL_OFFERAMT_MSG_REQUIRED']); ?>'
                    },
                    fTargetAmt: {
                        greaterThan: '<?= addslashes($langage_lbl['LBL_TARGET_VALIDATION_FRONT']); ?>',
                        min: '<?= addslashes($langage_lbl['LBL_TRGAMT_VALIDATION_MAX_FRONT']); ?>'
                    },
                    fPrepareTime: {
                        required: '<?= addslashes($langage_lbl['LBL_ESTIMATE_TIME_MSG_FRONT']); ?>'
                    },
                    fPricePerPerson: {
                        required: '<?= addslashes($langage_lbl['LBL_COST_PER_PERSON_FRONT_MSG']); ?>'
                    },

                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled', false);

                    if ($(form).valid())
                        form.submit();
                    return false; // prevent normal form posting
                }
            });
        }
    });


    function minFromMidnight(tm) {
        var ampm = tm.substr(-2);
        var clk;
        if (tm.length <= 6) {
            clk = tm.substr(0, 4);
        } else {
            clk = tm.substr(0, 5);
        }
        if (clk != "") {
            var m = parseInt(clk.match(/\d+$/)[0], 10);
            var h = parseInt(clk.match(/^\d+/)[0], 10);
            h += (ampm.match(/pm/i)) ? 12 : 0;
            return h * 60 + m;
        }
    }

    //Company End
    /*    var oldValue = $("#hide-profile-div").width();
        var w = parseFloat(oldValue) - 8;
        $(".croppie-container .cr-boundary").hide();
        var $image_crop = $('#image_demo').croppie({
            enableExif: true,
            viewport: {
              width:w,
              height:190,
              type:'square'
            },
            boundary:{
              width:oldValue,
              height:200
            },
            showZoomer: false,
            enableOrientation: true
        });
        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                     $(".img-ipm").hide();
                    result = e.target.result;
                    arrTarget = result.split(';');
                    tipo = arrTarget[0];
                    if (tipo == 'data:image/jpeg' || tipo == 'data:image/png' || tipo == 'data:image/jpg') {
                        $image_crop.croppie('bind', {
                            url: e.target.result
                        });
                        $('#image_demo').addClass('ready');
                    } else {
                        alert('Accept only .jpg or .png image types');
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }*/
    /*    $('#vCoverImage').on('change', function(){
            $(".cr-boundary").show();
            readFile(this);
          });
          $('#submitsettings').click(function(event){
            $image_crop.croppie('result', {
              type: 'canvas',
              size: 'viewport'
            }).then(function(response){
                if(response != ''){
                    $('#imagebase64').val(response);
                    $('#company_form').submit();
                }
            })
          });*/
    $('[data-toggle="tooltip"]').tooltip();
    var successMSG1 = '<?php echo $success; ?>';
    if (successMSG1 != '') {
        setTimeout(function () {
            $(".msgs_hide").hide(1000)
        }, 5000);
    }

    function setCity(id, selected) {
        var fromMod = 'company';

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {stateId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vCity").html(dataHtml);
            } else {
                console.log(response.result);
            }
        });
    }

    function setState(id, selected) {
        var fromMod = 'company';

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_stateCity.php',
            'AJAX_DATA': {countryId: id, selected: selected, fromMod: fromMod},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var dataHtml = response.result;
                $("#vState").html(dataHtml);
                if (selected == '')
                    setCity('', selected);
            } else {
                console.log(response.result);
            }
        });
    }

    setState('<?php echo $vCountry; ?>', '<?php echo $vState; ?>');
    setCity('<?php echo $vState; ?>', '<?php echo $vCity; ?>');

    function changeCode(id) {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url'] ?>change_code.php',
            'AJAX_DATA': {id: id},
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                document.getElementById("code").value = data;
            } else {
                console.log(response.result);
            }
        });
    }

    changeCode('<?php echo $vCountry; ?>');

    $("#submitsettings").on("click", function (event) {
        var isvalidate = $("#company_form")[0].checkValidity();
        if (isvalidate) {
            event.preventDefault();
            var vEmail = $("#vEmail").val();
            var vPhone = $("#vPhone").val();
            var iCompanyId = '<?php echo $id?>';

            if (iCompanyId != '') {

                var ajaxData = {
                    'URL': '<?= $tconfig['tsite_url'] ?>ajax_check_Email_Country.php',
                    'AJAX_DATA': {vEmail: vEmail, vPhone: vPhone, iCompanyId: iCompanyId},
                    'REQUEST_DATA_TYPE': 'html'
                };
                getDataFromAjaxCall(ajaxData, function (response) {
                    if (response.action == "1") {
                        var dataHtml5 = response.result;
                        if ($.trim(dataHtml5) != '') {
                            alert($.trim(dataHtml5));
                            $("#company_form").submit();
                            return true;
                        } else {
                            $("#company_form").submit();
                            return true;
                        }
                    } else {
                        console.log(response.result);
                    }
                });
            } else {
                $("#vCountry").prop('disabled', false);
                $("#_company_form").submit();
                return true;
            }
        }
    });
    //$("#eTakeaway").change(function () {
    //    var eTakeaway = $(this).val();
    //    if (eTakeaway == "No") {
    //        $('#edeliveryAvailable option[value=Yes]').attr('selected','selected');
    //    }
    //});

    function preview_mainImg(event) {
        $("#single_img001").html('');
        $('#single_img001').append("<img src='" + URL.createObjectURL(event.target.files[0]) + "' class='thumbnail' style='max-width: 250px; max-height: 250px' >");
        $(".changeImg001").text('Change');
        $(".remove_main").show();

    }
</script>
<!-- End: Footer Script -->
</body>
</html>