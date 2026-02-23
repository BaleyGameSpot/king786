<?php
include 'common.php';
/*ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);*/
/*------------------check member function-----------------*/
function checkMemberDataInfoForDeleteAccount($email, $pass, $userType, $countryCode, $id = "", $eSystem = "")
{
    global $generalobj, $obj, $ENABLE_EMAIL_OPTIONAL, $ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD;
    // echo "<PRE>";print_r($_REQUEST);exit;
    //echo $email."===".$pass."====".$userType."===".$countryCode."===".$id."===".$eSystem;die;
    if ($ENABLE_EMAIL_OPTIONAL != "Yes") {
        if (empty($email) || (empty($countryCode) && empty($pass))) {
            return array(
                'status'   => 0,
                'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
            );
        }
    }
    // if ((empty($countryCode) && empty($pass)) || (!empty($countryCode) && !empty($pass))) { //NM 9/7/20
    if ((empty($countryCode) && empty($pass))) {
        return array(
            'status'   => 0,
            'MSG_TYPE' => 'INVALID_DATA_PASS'
        );
    }
    $sqlid = "";
    $phoneField = "vPhone";
    echo "<pre>";
    print_r($userType);
    exit;
    if (strtoupper($userType) == "RIDER" || strtoupper($userType) == "PASSENGER") {
        $tableName = "register_user";
        $fields = 'iUserId, vName, vEmail, eStatus, vCurrencyPassenger, vPhone,vPassword,vLang,vCountry';
        if ($id != "") {
            $sqlid = ' AND iUserId !=' . $id;
        }
    } else if (strtoupper($userType) == "DRIVER") {
        $tableName = "register_driver";
        $fields = 'iDriverId,vCompany, iCompanyId, vName, vLastName, vEmail, vPhone,eStatus, vCurrencyDriver,vPassword,vLang,vCountry';
        if ($id != "") {
            $sqlid = ' AND iDriverId !=' . $id;
        }
    } else if (strtoupper($userType) == "COMPANY") {
        $tableName = "company";
        $fields = 'iCompanyId,vCompany, vName, vLang, vLastName, vEmail,vPhone, eStatus,vPassword,eSystem,vCountry';
        if ($id != "") {
            $sqlid = ' AND iCompanyId !=' . $id;
        }
    } else if (strtoupper($userType) == "ORGANIZATION") {
        $tableName = "organization";
        $fields = "iOrganizationId,vCompany, vLang, vEmail,vPhone, eStatus,vPassword,vCountry";
        if ($id != "") {
            $sqlid = ' AND iOrganizationId !=' . $id;
        }
    } else if (strtoupper($userType) == "ADMIN") {
        $tableName = "administrators";
        $phoneField = "vContactNo";
        $fields = "iAdminId, vEmail,vContactNo, eStatus,vPassword,vCountry";
        if ($id != "") {
            $sqlid = ' AND iAdminId !=' . $id;
        }
    } else {
        return array(
            'status'   => 0,
            'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG l'
        );
    }
    $ssql = $where = "";
    // if (empty($countryCode)) {
    //     $ssql = " ($phoneField = '" . $email . "' AND vCountry != '')";
    // } else {
    //     $ssql = " ($phoneField = '" . $email . "' AND vCountry='" . $countryCode . "')";
    // }
    // if (trim($eSystem) != "" && strtoupper($userType) == "COMPANY") {
    //     $ssql .= " AND eSystem='" . $eSystem . "'";
    // }
    $ssql = "";
    if ($ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
        $ssql = " (vEmail= '" . $email . "' AND vCountry != '')";
        if (isOnlyDigitsStrSGF($email) && !empty($countryCode)) {
            $ssql = " ($phoneField = '" . $email . "' AND vCountry='" . $countryCode . "')";
        }
        if (trim($eSystem) != "" && strtoupper($userType) == "COMPANY") {
            $ssql .= " AND eSystem='" . $eSystem . "'";
        }
        // echo "SELECT $fields FROM $tableName WHERE (" . $ssql . " " . $sqlid . " )";exit;
        $data = $obj->MySQLSelect("SELECT $fields FROM $tableName WHERE (" . $ssql . " " . $sqlid . " )");
    } else {
        // echo "ELSE";exit;
        if (empty($countryCode)) {
            $ssql = " ($phoneField = '" . $email . "' AND vCountry != '')";
        } else {
            $ssql = " ($phoneField = '" . $email . "' AND vCountry LIKE '" . $countryCode . "' AND vCountry != '')";
        }
        if (trim($eSystem) != "" && strtoupper($userType) == "COMPANY") {
            $ssql .= " AND eSystem='" . $eSystem . "'";
        }
        $data = $obj->MySQLSelect("SELECT $fields FROM $tableName WHERE ((vEmail = '" . $email . "' " . $sqlid . ") OR " . $ssql . " " . $sqlid . " )");
    }
    // echo "SELECT $fields FROM $tableName WHERE (" . $ssql . " " . $sqlid . " )";exit;
    //echo "SELECT $fields FROM $tableName WHERE ((vEmail = '" . $email . "' ".$sqlid.") OR " . $ssql . " ".$sqlid." )";exit;
    //$data = $obj->MySQLSelect("SELECT $fields FROM $tableName WHERE (vEmail = '" . $email . "' OR " . $ssql . ")");
    //echo "SELECT $fields FROM $tableName WHERE ((vEmail = '" . $email . "' " . $sqlid . ") OR " . $ssql . " " . $sqlid . " )";die;
    // echo "<pre>"; print_r($data);exit;
    if ($_REQUEST['type'] == "signIn" && empty($data) && scount($data) == 0) {
        return array(
            'status'   => 0,
            'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
        );
    } else if ($_REQUEST['type'] != "signIn" && $_REQUEST['type'] != "passwordAuth" && !empty($countryCode) && scount($data) > 0) { // For Signup
        return array(
            'status'   => 0,
            'MSG_TYPE' => 'MULTI_ACC_FOUND'
        );
    } else if ($_REQUEST['type'] != "signIn" && !empty($countryCode) && scount($data) == 0) { // For Signup
        return array('status' => 1);
    } else if (empty($data) && scount($data) == 0) { //Check Sign In Process
        return array(
            'status'   => 0,
            'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
        );
    } else if (empty($data) && scount($data) == 0) {
        return array(
            'status'   => 0,
            'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
        );
    } else if ($_REQUEST['type'] == "signIn" && !empty($data) && scount($data) > 0) {
        return array('status' => 1);
    }
    /* if (!empty($countryCode) && scount($data) > 0) { // For Signup
        return array('status' => 0, 'MSG_TYPE' => 'MULTI_ACC_FOUND');
    }
    if (!empty($countryCode) && scount($data) == 0) { // For Signup
        return array('status' => 1);
    }
    if (empty($data) && scount($data) == 0) { //Check Sign In Process
        return array('status' => 0, 'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG');
    } */
    if (!empty($pass)) {
        // Step 2 - Match password
        $match_pass_data_arr = array();
        foreach ($data as $data_item) {
            if ($generalobj->check_password($pass, $data_item['vPassword'])) {
                $match_pass_data_arr[] = $data_item;
            }
        }
        if (scount($match_pass_data_arr) == 0) {
            return array(
                'status'   => 0,
                'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
            );
        } else if (scount($match_pass_data_arr) == 1) {
            return array(
                'status'    => 1,
                'USER_DATA' => $data[0]
            );
        }
    }
    if (empty($countryCode)) {
        // Step 3 - Get country from Ip address
        $ip_address = $generalobj->get_client_ip();
        $responseData = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $ip_address));
        if (isset($responseData->geoplugin_countryCode) && !empty($responseData->geoplugin_countryCode)) {
            $match_country_data_arr = array();
            foreach ($data as $data_item) {
                if ($responseData->geoplugin_countryCode == $data_item['vCountry']) {
                    $match_country_data_arr[] = $data_item;
                }
            }
            if (scount($match_country_data_arr) == 0 || scount($match_country_data_arr) > 1) {
                return array(
                    'status'   => 2,
                    'MSG_TYPE' => 'SHOW_MULTI_ACC_ERR_MSG'
                );
            } else {
                return array(
                    'status'    => 1,
                    'USER_DATA' => $match_country_data_arr[0]
                );
            }
        } else {
            return array(
                'status'   => 0,
                'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
            );
        }
    }
    return array(
        'status'   => 0,
        'MSG_TYPE' => 'SHOW_INVLID_ACC_DETAILS_MSG'
    );
}

/*------------------check member function-----------------*/
/*------------------country-----------------*/
$ForDeleteAccount = isset($_REQUEST["ForDeleteAccount"]) ? $_REQUEST["ForDeleteAccount"] : '';
$htmldropdown = '';
$isXThemeActive = "Yes";
if ($ForDeleteAccount == 1) {
// ================ DropDown HTML Start ==============
    $classadd = '';
    $DropDownName = $_REQUEST['DropDownName'];
    $placeId = $_REQUEST['placeId'];
    $sql = "SELECT c.vValue,co.vPhoneCode from configurations as c LEFT JOIN country as co on co.vCountryCode=c.vValue where vName = 'DEFAULT_COUNTRY_CODE_WEB'";
    $defaultcountryArry = $obj->MySQLSelect($sql);
    $DefaultvPhoneCode = $defaultcountryArry[0]['vPhoneCode'];
    $DefaultvValue = $defaultcountryArry[0]['vValue'];
    if ($langage_lbl['LBL_FEILD_EMAIL_ERROR'] != '') {
        $message = $langage_lbl['LBL_FEILD_EMAIL_ERROR'];
    } else {
        $message = "Please enter a valid email address.";
    }
    $sqlC = "SELECT vCountryCode,vPhoneCode,vCountry from country where eStatus='Active'";
    $AllcountryArry = $obj->MySQLSelect($sqlC);
    $SITEPATH = $tconfig['tsite_url'];
    $htmldropdown = '<div class="countryPhoneSelectWrapper countryPhoneSelectWrapper' . $placeId . '" style="display:none;">
      <select name="' . $DropDownName . '" id="' . $DropDownName . '" class="countryPhoneSelect form-control">';
    foreach ($AllcountryArry as $Rows) {
        $htmldropdown .= '<option ';
        if ($Rows['vCountryCode'] == $defaultcountryArry[0]['vValue']) {
            $htmldropdown .= 'selected="selected"';
        }
        $htmldropdown .= ' value="' . $Rows["vCountryCode"] . '" data-code="+' . $Rows["vPhoneCode"] . '" data-country="' . $Rows["vCountryCode"] . '">' . $Rows["vCountry"] . ' (+' . $Rows["vPhoneCode"] . ')
                    </option>';
    }
    $htmldropdown .= '</select>
      <div class="countryPhoneSelectChoice">
        <span class="countryCode countryCode' . $placeId . '">' . $DefaultvValue . '</span>
        <span class="phoneCode phoneCode' . $placeId . '">+' . $DefaultvPhoneCode . '</span>
      </div>
  </div>';
// $('#".$placeId."').append('<div id='ferrmsg".$placeId."'></div>');
    // $('#".$placeId."').append(".$placeId.");
// var ".$placeId." = $('<div>', '');
//               ".$placeId.".className = 'ferrmsg".$placeId."';
//               ".$placeId.".id = 'ferrmsg".$placeId."';
//                 $(".$placeId.").insertAfter('#".$placeId."');
    // $createDiv = '$(<div id="ferrmsg'.$placeId.'"></div>).insertAfter("#'.$placeId.'")';
    $htmldropdown .= '<input type="hidden" name="isEmail' . $placeId . '" class="isEmail' . $placeId . '" value="Yes">';
    if ($isXThemeActive == 'Yes') {
        $htmldropdown .= "<script>
             var css_link = $('<link>', {
                    rel: 'stylesheet',
                    type: 'text/css',
                    href: '" . $SITEPATH . "assets/css/add_countrycode_dropdown.css'
                });
                css_link.appendTo('head');";
    } else {
        $htmldropdown .= "<script>";
    }
    $htmldropdown .= "var " . $placeId . " =document.createElement('div');
            " . $placeId . ".setAttribute('id', 'ferrmsg" . $placeId . "');
            " . $placeId . ".setAttribute('class', 'help-block error');
            $(" . $placeId . ").insertAfter('#" . $placeId . "');";
    if ($isXThemeActive == 'Yes') {
        $htmldropdown .= "$('document').ready(function () {

          $('#" . $DropDownName . "').change(function() {
                var fruitCount = $(this).attr('data-code');
                var phonecode = $(this).find(':selected').attr('data-code');
                var phonecountry = $(this).find(':selected').attr('data-country');
                $('.countryCode" . $placeId . "').text(phonecountry);
                $('.phoneCode" . $placeId . "').text(phonecode);
              });


            var timeout" . $placeId . " = null;
             $('#" . $placeId . "').keyup(function() {
                var inputvalue = $('#" . $placeId . "').val();

                document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                $('#btn_submit').removeAttr('disabled');

                 if($.isNumeric(inputvalue) && inputvalue!= '') {
                   $('.countryPhoneSelectWrapper" . $placeId . "').show(400,'swing');
                   $('#" . $placeId . "').addClass('phoneinput');
                   $('#" . $placeId . "').removeClass('emailinput');
                   $('.isEmail" . $placeId . "').val('No');
                 }else {
                  $('.countryPhoneSelectWrapper" . $placeId . "').hide(500);
                  $('#" . $placeId . "').removeClass('phoneinput');
                  $('#" . $placeId . "').addClass('emailinput');
                  $('.isEmail" . $placeId . "').val('Yes');

                  if((!isEmail(inputvalue)) && (inputvalue != '')){
                      document.getElementById('ferrmsg" . $placeId . "').style.display = 'block';
                        clearTimeout('timeout" . $placeId . "');
                        timeout" . $placeId . " = setTimeout(function() {
                            document.getElementById('ferrmsg" . $placeId . "').innerHTML = '" . addslashes($message) . "';
                            $('#btn_submit').attr('disabled','disabled');
                      }, 2000);
                  } else {
                      $('#btn_submit').removeAttr('disabled');
                      document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                      document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                  }

                 }
              });


            var timeout" . $placeId . " = null;
               $('#" . $placeId . "').change(function() {
                var inputvalue = $('#" . $placeId . "').val();

                document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                $('#btn_submit').removeAttr('disabled');

                 if($.isNumeric(inputvalue) && inputvalue!= '') {
                   $('.countryPhoneSelectWrapper" . $placeId . "').show(400,'swing');
                   $('#" . $placeId . "').addClass('phoneinput');
                   $('#" . $placeId . "').removeClass('emailinput');
                   $('.isEmail" . $placeId . "').val('No');
                 }else {
                  $('.countryPhoneSelectWrapper" . $placeId . "').hide(500);
                  $('#" . $placeId . "').removeClass('phoneinput');
                  $('#" . $placeId . "').addClass('emailinput');
                  $('.isEmail" . $placeId . "').val('Yes');

                  if((!isEmail(inputvalue)) && (inputvalue != '')){
                      document.getElementById('ferrmsg" . $placeId . "').style.display = 'block';
                        clearTimeout('timeout" . $placeId . "');
                        timeout" . $placeId . " = setTimeout(function() {
                            document.getElementById('ferrmsg" . $placeId . "').innerHTML = '" . addslashes($message) . "';
                            $('#btn_submit').attr('disabled','disabled');
                      }, 2000);
                  } else {
                      $('#btn_submit').removeAttr('disabled');
                      document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                      document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                  }

                 }
              });

              $('.tab-switch li').on('click', function () {
                var dataId = $(this).attr('data-id');
                $('#" . $placeId . "').removeClass('phoneinput');

                $('#btn_submit').removeAttr('disabled');
                document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
              });
      });
            </script>";
    } else {
        $htmldropdown .= "$('document').ready(function () {
                    $('#" . $DropDownName . "' ).change(function() {
                        var fruitCount = $(this).attr('data-code');
                        var phonecode" . $placeId . " = $(this).find(':selected').attr('data-code');
                        var phonecountry = $(this).find(':selected').attr('data-country');
                        $('.countryCode" . $placeId . "').text(phonecountry);
                        $('.phoneCode" . $placeId . "').text(phonecode" . $placeId . ");
                  });

                $('#" . $placeId . "').keyup(function() {
                  var inputvalue = $('#" . $placeId . "').val();
                   if($.isNumeric(inputvalue) && inputvalue!= '') {
                     $('.countryPhoneSelectWrapper" . $placeId . "').show(400,'swing');
                     $('.phone-field').addClass('country-code');
                     $('#" . $placeId . "').removeClass('emailinput');
                     $('#" . $placeId . "').addClass('phoneinput');
                     $('.isEmail').val('No');
                   }
                   else {
                    $('.countryPhoneSelectWrapper" . $placeId . "').hide(500);
                    $('.phone-field').removeClass('country-code');
                    $('#" . $placeId . "').removeClass('phoneinput');
                    $('#" . $placeId . "').addClass('emailinput');
                    $('.isEmail').val('Yes');
                   }
                });

                $('.tab-switch li').on('click', function () {
                  var dataId = $(this).attr('data-id');
                  $('#" . $placeId . "').removeClass('phoneinput');
                });

              });
              </script>";
    }
    echo $htmldropdown;
    exit;
}
// ================ DropDown HTML END ==============
/*------------------country-----------------*/

/*------------------class-----------------*/

class Language_web
{
    public function __construct()
    {
    }

    public function FetchLanguageLabels($lCode = '', $directValue = "", $iServiceId = "")
    {
        global $obj, $APP_TYPE, $oCache;
        /* find default language of website set by admin */
        $defaultLangCodeApcKey = md5('system_default_lang_code');
        $getDefaultLangCodeCacheData = $oCache->getData($defaultLangCodeApcKey);
        if (!empty($getDefaultLangCodeCacheData) && scount($getDefaultLangCodeCacheData) > 0) {
            $default_label = $getDefaultLangCodeCacheData;
        } else {
            $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
            $default_label = $obj->MySQLSelect($sql);
            $oCache->setData($defaultLangCodeApcKey, $default_label);
        }
        if ($lCode == '') {
            $lCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : 'EN';
        }
        $LangLabelsApcKey = md5('language_label_union_other_' . $lCode);
        $getLangLabelsCacheData = $oCache->getData($LangLabelsApcKey);
        if (!empty($getLangLabelsCacheData) && scount($getLangLabelsCacheData) > 0) {
            $all_label = $getLangLabelsCacheData;
        } else {
            $sql = "SELECT  `vLabel` , `vValue`  FROM  `language_label` WHERE  `vCode` = '" . $lCode . "' UNION SELECT `vLabel` , `vValue`  FROM  `language_label_other` WHERE  `vCode` = '" . $lCode . "' ";
            $all_label = $obj->MySQLSelect($sql);
            $oCache->setData($LangLabelsApcKey, $all_label);
        }
        $x = array();
        for ($i = 0; $i < scount($all_label); $i++) {
            $vLabel = $all_label[$i]['vLabel'];
            $vValue = $all_label[$i]['vValue'];
            $x[$vLabel] = $vValue;
        }
        $LangLabelsENApcKey = md5('language_label_union_other_EN');
        $getLangLabelsENCacheData = $oCache->getData($LangLabelsENApcKey);
        if (!empty($getLangLabelsENCacheData) && scount($getLangLabelsENCacheData) > 0) {
            $all_label_en = $getLangLabelsENCacheData;
        } else {
            $sql_en = "SELECT  `vLabel` , `vValue`  FROM  `language_label` WHERE  `vCode` = 'EN' UNION SELECT `vLabel` , `vValue`  FROM  `language_label_other` WHERE  `vCode` = 'EN'";
            $all_label_en = $obj->MySQLSelect($sql_en);
            $oCache->setData($LangLabelsENApcKey, $all_label_en);
        }
        if (scount($all_label_en) > 0) {
            for ($i = 0; $i < scount($all_label_en); $i++) {
                $vLabel_tmp = $all_label_en[$i]['vLabel'];
                $vValue_tmp = $all_label_en[$i]['vValue'];
                if (isset($x[$vLabel_tmp]) || array_key_exists($vLabel_tmp, $x)) {
                    if ($x[$vLabel_tmp] == "") {
                        $x[$vLabel_tmp] = $vValue_tmp;
                    }
                } else {
                    $x[$vLabel_tmp] = $vValue_tmp;
                }
            }
        }
        $x['vCode'] = $lCode;
        if ($directValue == "") {
            $returnArr['Action'] = "1";
            $returnArr['LanguageLabels'] = $x;
            return $returnArr;
        } else {
            return $x;
        }
    }
}

$LANG_OBJ_WEB = new Language_web;

class Delete_account_Web
{
    function __construct()
    {
        /*ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
        error_reporting(E_ALL);*/
    }

    public function accountDelete($iMemberId, $UserType)
    {
        global $tconfig;
        if ($UserType == 'Passenger') {
            $data = $this->checkAccountDelete_User($iMemberId);
        } else if ($UserType == 'Driver') {
            $data = $this->checkAccountDelete_Driver($iMemberId);
        } else if ($UserType == 'Company') {
            $data = $this->checkAccoutDelete_Store($iMemberId);
        } else if ($UserType == 'Tracking') {
            $data = $this->checkAccoutDelete_Tracking($iMemberId);
        }
        if (!empty($data) && scount($data) > 0) {
            $returnArr['Action'] = $data['status'];
            $returnArr['message'] = $data['message'];
            if ($data['status'] == 1) {
                $returnArr['Url'] = $tconfig['tsite_url'] . 'account_delete_process.php?GeneralMemberId=' . $iMemberId . '&GeneralUserType=' . $UserType;
            }
        } else {
            $returnArr['Action'] = 0;
            $returnArr['message'] = '--';
        }
        setDataResponse($returnArr);
    }

    private function checkAccountDelete_User($iMemberId)
    {
        global $obj, $MODULES_OBJ;
        $OutstandingAmount = GetPassengerOutstandingAmount($iMemberId);
        $register_user = $obj->MySQLSelect("SELECT iUserId, vTripStatus FROM `register_user` WHERE iUserId = '" . $iMemberId . "'");
        $trips = $obj->MySQLSelect("SELECT iTripId,iActive FROM `trips` WHERE iUserId = '" . $iMemberId . "' AND iActive IN ('Active' , 'On Going Trip','Arrived') ");
        $cab_request_now = $obj->MySQLSelect("SELECT iTripId,eStatus FROM `cab_request_now` WHERE iUserId = '" . $iMemberId . "'  AND iTripId != '' AND eStatus IN ('Pending')");
        $cab_booking = $obj->MySQLSelect("SELECT iTripId,eStatus FROM `cab_booking` WHERE iUserId = '" . $iMemberId . "'   AND eStatus IN ('Pending','Assign','Accepted')");
        $orders = array();
        if ($MODULES_OBJ->isDeliverAllFeatureAvailable()) {
            $orders = $obj->MySQLSelect("SELECT iOrderId,iStatusCode  FROM `orders` WHERE `iUserId` = '" . $iMemberId . "' AND iStatusCode IN (1,2,4,5)");
        }
        $Arr_Return['status'] = 0;
        if ($OutstandingAmount == 0 && scount($trips) == 0 && scount($cab_request_now) == 0 && scount($cab_booking) == 0 && scount($orders) == 0) {
            $Arr_Return['status'] = 1;
            $Arr_Return['message'] = 'LBL_CONTINUE_DELETE_ACCOUNT';
        } else {
            if ($OutstandingAmount > 0) {
                $LBL_MES = 'LBL_DELETE_ACCOUNT_PENDING_OUTSTANDING_AMOUNT';
            } else if (scount($trips) > 0 || scount($cab_request_now) > 0 || scount($cab_booking) > 0 || scount($orders) > 0) {
                $LBL_MES = 'LBL_DELETE_ACCOUNT_PENDING_TRIP_ORDER_JOB';
            }
            $Arr_Return['message'] = $LBL_MES;
        }
        return $Arr_Return;
    }

    private function checkAccountDelete_Driver($iMemberId)
    {
        global $obj;
        $vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
        $serverTimeZone = date_default_timezone_get();
        $date_ = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")));
        $TimeZoneOffset = converToTz($date_, $serverTimeZone, $vTimeZone, "P");
        $subSqlBook = " AND dBooking_date > ((CONVERT_TZ(NOW(), 'SYSTEM', '" . $TimeZoneOffset . "')) - INTERVAL 30 MINUTE)";
        $trips = $obj->MySQLSelect("SELECT iTripId,iDriverId,iActive,eDriverPaymentStatus FROM `trips` WHERE iDriverId = '" . $iMemberId . "' AND iActive != 'Canceled' AND eDriverPaymentStatus = 'Unsettelled' ");
        $cab_booking = $obj->MySQLSelect("SELECT iTripId,eStatus FROM `cab_booking` WHERE iDriverId = '" . $iMemberId . "' AND eStatus IN ('Pending','Assign','Accepted') $subSqlBook");
        $Arr_Return['status'] = 0;
        if (scount($trips) == 0 && scount($cab_booking) == 0) {
            $Arr_Return['status'] = 1;
            $Arr_Return['message'] = 'LBL_CONTINUE_DELETE_ACCOUNT';
        } else {
            if (scount($trips) > 0 || scount($cab_booking) > 0) {
                $LBL_MES = 'LBL_DELETE_ACCOUNT_PENDING_TRIP_ORDER_JOB';
            }
            $Arr_Return['message'] = $LBL_MES;
        }
        return $Arr_Return;
    }

    private function checkAccoutDelete_Store($iMemberId)
    {
        global $obj;
        $statusCode = "1,2,4,5";
        $orders = $obj->MySQLSelect("SELECT iOrderId FROM orders WHERE  iCompanyId='" . $iMemberId . "' AND iStatusCode IN ($statusCode)");
        $Arr_Return['status'] = 0;
        if (scount($orders) == 0) {
            $Arr_Return['status'] = 1;
            $Arr_Return['message'] = 'LBL_CONTINUE_DELETE_ACCOUNT';
        } else {
            if (scount($orders) > 0) {
                $LBL_MES = 'LBL_DELETE_ACCOUNT_PENDING_TRIP_ORDER_JOB';
            }
            $Arr_Return['message'] = $LBL_MES;
        }
        return $Arr_Return;
    }

    private function checkAccoutDelete_Tracking($iMemberId)
    {
        global $obj;
        $Tracking_user = $obj->MySQLSelect("SELECT iTrackServiceUserId FROM track_service_users WHERE  iTrackServiceUserId='" . $iMemberId . "'");
        $Arr_Return['status'] = 0;
        if (scount($Tracking_user) > 0) {
            $Arr_Return['status'] = 1;
            $Arr_Return['message'] = 'LBL_CONTINUE_DELETE_ACCOUNT';
        } else {
            $Arr_Return['message'] = '--';
        }
        return $Arr_Return;
    }

    public function signIn($iMemberId, $UserType, $mobileNo, $vPhoneCode, $email)
    {
        global $obj, $SIGN_IN_OPTION, $generalobj;
        if (function_exists("checkMemberDataInfo")) {
        $checkValid = checkMemberDataInfo($mobileNo, '', $UserType, $vPhoneCode, $iMemberId, '', 1);
        } else {
            $_REQUEST['type'] = "signIn";
            $checkValid = checkMemberDataInfoForDeleteAccount($mobileNo, '', $UserType, $vPhoneCode, '', '');
        }
        if ($UserType == "Driver") {
            $getData = $this->getDriverDetails($iMemberId, $mobileNo);
        } else if ($UserType == "Company") {
            $getData = $this->getCompanyDetails($iMemberId, $mobileNo);
        } else if ($UserType == 'Tracking') {
            $getData = $this->getTrackingUserDetails($iMemberId);
        } else if ($UserType == 'Admin') {
            $checkValid = $this->getHotelDetails($iMemberId);
        } else {
            $getData = $this->getUserDetails($iMemberId, $mobileNo);
        }
        $returnArr['Action'] = '1';
        if ($checkValid['status'] == 1) {
            $returnArr['showEnterPassword'] = 'No';
            $returnArr['showEnterOTP'] = 'No';
            if ($SIGN_IN_OPTION == 'Password') {
                $returnArr['showEnterPassword'] = 'Yes';
            } else if ($SIGN_IN_OPTION == 'OTP') {
                $returnArr['showEnterOTP'] = 'Yes';
                $this->sendOtp($iMemberId, $UserType, $vPhoneCode, $mobileNo);
        } else {
                $returnArr['showEnterPassword'] = 'Yes';
        }
            } else {
            $returnArr['message'] = 'LBL_INVALID_PHONE_NUMBER';
            $returnArr['Action'] = '0';
        }
        return $returnArr;
    }

    private function getDriverDetails($iMemberId, $phoneNo = '')
    {
        global $obj, $tconfig;
        $sql = '';
        if ($phoneNo != '') {
            $sql .= "AND vPhone = '$phoneNo'";
        }
        $query_1 = "SELECT vName,vLastName,vCurrencyDriver,vImage,vPassword,eStatus  FROM  register_driver  WHERE 1 = 1 AND iDriverId = " . $iMemberId . " $sql";
        $register_driver = $obj->MySQLSelect($query_1);
        $return_arr['vPassword'] = $register_driver[0]['vPassword'];
        $return_arr['eStatus'] = $register_driver[0]['eStatus'];
        $return_arr['userName'] = $register_driver[0]['vName'] . ' ' . $register_driver[0]['vLastName'];
        $return_arr['userImage'] = '';
        if (isset($register_driver[0]['vImage']) && !empty($register_driver[0]['vImage'])) {
            $return_arr['userImage'] = $tconfig["tsite_upload_images_driver"] . '/' . $iMemberId . '/3_' . $register_driver[0]['vImage'];
        } else {
            $return_arr['userImage'] = 'assets/img/profile-user-img.png';
        }
        return $return_arr;
    }

    private function getCompanyDetails($iCompanyId, $phoneNo = '')
    {
        global $obj, $tconfig;
        $sql = '';
        if ($phoneNo != '') {
            $sql .= "AND vPhone = '$phoneNo'";
        }
        $query_1 = "SELECT vCompany,vImage,vPassword,eStatus  FROM  company  WHERE 1 = 1 AND iCompanyId = " . $iCompanyId . " $sql";
        $company = $obj->MySQLSelect($query_1);
        $return_arr['userName'] = $company[0]['vCompany'];
        $return_arr['vPassword'] = $company[0]['vPassword'];
        $return_arr['eStatus'] = $company[0]['eStatus'];
        if (isset($company[0]['vImage']) && !empty($company[0]['vImage'])) {
            $return_arr['userImage'] = $tconfig['tsite_upload_images_compnay'] . '/' . $iCompanyId . '/3_' . $company[0]['vImage'];
            // $tconfig["tsite_upload_images_driver"] . '/' . $iMemberId . '/3_' . $register_driver[0]['vImage'];
        } else {
            $return_arr['userImage'] = 'assets/img/profile-user-img.png';
        }
        return $return_arr;
    }

    private function getTrackingUserDetails($iMemberId, $phoneNo = '')
    {
        global $obj, $tconfig;
        $sql = '';
        if ($phoneNo != '') {
            $sql .= "AND vPhone = '$phoneNo'";
        }
        //echo "SELECT CONCAT(vName, ' ', vLastName) as userName, vImgName,vPassword,eStatus FROM register_user WHERE iUserId = '" . $iMemberId . "'  $sql";
        $userData = $obj->MySQLSelect("SELECT CONCAT(vName, ' ', vLastName) as userName, vImage,vPassword,eStatus FROM track_service_users WHERE iTrackServiceUserId = '" . $iMemberId . "'  $sql");
        $return_arr['userName'] = $userData[0]['userName'];
        $return_arr['vPassword'] = $userData[0]['vPassword'];
        $return_arr['eStatus'] = $userData[0]['eStatus'];
        $return_arr['userImage'] = '';
        if (isset($userData[0]['vImage']) && !empty($userData[0]['vImage'])) {
            $return_arr['userImage'] = $tconfig['tsite_upload_images_track_company_user'] . "/" . $iMemberId . "/" . $userData[0]['vImage'];
        } else {
            $return_arr['userImage'] = 'assets/img/profile-user-img.png';
        }
        return $return_arr;
    }

    private function getHotelDetails($iHotelId)
    {
        global $obj, $tconfig;
        $sql = '';
        if ($phoneNo != '') {
            $sql .= "AND vPhone = '$phoneNo'";
        }
        $query_1 = "SELECT vFirstName,vLastName,vPassword,eStatus  FROM  administrators  WHERE 1 = 1 AND iGroupId = 4 AND eStatus != 'Deleted' AND iAdminId = " . $iHotelId . "";
        $hotel = $obj->MySQLSelect($query_1);
        $sql1 = "SELECT * FROM hotel WHERE iAdminId = '" . $iHotelId . "'";
        $hotel_details = $obj->MySQLSelect($sql1);
        $return_arr['userName'] = $hotel[0]['vFirstName'] . ' ' . $hotel[0]['vLastName'];
        $return_arr['vPassword'] = $hotel[0]['vPassword'];
        $return_arr['eStatus'] = $hotel[0]['eStatus'];
        $hotel_details_iHotelId = $hotel_details[0]['iHotelId'];
        if (isset($hotel_details[0]['vImgName']) && !empty($hotel_details[0]['vImgName'])) {
            $return_arr['userImage'] = $tconfig['tsite_url'] . 'resizeImg.php?w=250&src=' . $tconfig['tsite_upload_images_hotel_passenger'] . '/' . $hotel_details_iHotelId . '/' . $hotel_details[0]['vImgName'];
        } else {
            $return_arr['userImage'] = 'assets/img/profile-user-img.png';
        }
        if (scount($hotel) > 0) {
            $return_arr['status'] = 1;
        }
        return $return_arr;
    }

    private function getUserDetails($iMemberId, $phoneNo = '')
    {
        global $obj, $tconfig;
        $sql = '';
        if ($phoneNo != '') {
            $sql .= "AND vPhone = '$phoneNo'";
        }
        //echo "SELECT CONCAT(vName, ' ', vLastName) as userName, vImgName,vPassword,eStatus FROM register_user WHERE iUserId = '" . $iMemberId . "'  $sql";
        $userData = $obj->MySQLSelect("SELECT CONCAT(vName, ' ', vLastName) as userName, vImgName,vPassword,eStatus FROM register_user WHERE iUserId = '" . $iMemberId . "'  $sql");
        $return_arr['userName'] = $userData[0]['userName'];
        $return_arr['vPassword'] = $userData[0]['vPassword'];
        $return_arr['eStatus'] = $userData[0]['eStatus'];
        $return_arr['userImage'] = '';
        if (isset($userData[0]['vImgName']) && !empty($userData[0]['vImgName'])) {
            $return_arr['userImage'] = $tconfig['tsite_upload_images_passenger'] . "/" . $iMemberId . "/" . $userData[0]['vImgName'];
        } else {
            $return_arr['userImage'] = 'assets/img/profile-user-img.png';
        }
        return $return_arr;
    }

    private function sendOtp($iMemberId, $UserType, $vPhoneCode, $mobileNo)
    {
        global $LANG_OBJ_WEB, $obj, $MOBILE_NO_VERIFICATION_METHOD, $COMM_MEDIA_OBJ, $SITE_NAME;
        $otp = mt_rand(1000, 9999);
        $Data_update_member['iOTP'] = $otp;
        $Data_update_member_c['vOTP'] = $otp;
        $vLangCode = $LANG_OBJ_WEB->FetchDefaultLangData("vCode");
        if (isset($languageLabelDataArr['language_label_1_' . $vLangCode])) {
            $languageLabelsArr = $languageLabelDataArr['language_label_1_' . $vLangCode];
        } else {
            $languageLabelsArr = $LANG_OBJ_WEB->FetchLanguageLabels($vLangCode, "1", "");
            $languageLabelDataArr['language_label_1_' . $vLangCode] = $languageLabelsArr;
        }
        $str = "SELECT * from send_message_templates where vEmail_Code = 'AUTH_OTP'";
        $res = $obj->MySQLSelect($str);
        $prefix = $res[0]['vBody_' . $vLangCode];
        $message = str_replace([
            "#OTP#",
            "#SITE_NAME#"
        ], [
            $otp,
            $SITE_NAME
        ], $prefix);
        $toMobileNum = "+" . $vPhoneCode . $mobileNo;
        /********************** Firebase SMS Verfication **********************************/
        $returnArr['MOBILE_NO_VERIFICATION_METHOD'] = $MOBILE_NO_VERIFICATION_METHOD;
        /********************** Firebase SMS Verfication **********************************/
        if (strtoupper($MOBILE_NO_VERIFICATION_METHOD) != "FIREBASE") {
            $result = $COMM_MEDIA_OBJ->SendSystemSMS($toMobileNum, $mobileNo, $message);
        } else {
            $result = 1;
        }
        if ($result == 0) {
            if ($UserType == "Driver") {
                $where = " iDriverId = '$iMemberId' ";
                $obj->MySQLQueryPerform('register_driver', $Data_update_member, 'update', $where);
            } else if ($UserType == "Company") {
                $where = " iCompanyId  = '$iMemberId' ";
                $obj->MySQLQueryPerform('company', $Data_update_member_c, 'update', $where);
            } else {
                $where = " iUserId = '$iMemberId' ";
                $obj->MySQLQueryPerform('register_user', $Data_update_member, 'update', $where);
            }
        }
        return $result;
    }

    public function AuthenticateMember($iMemberId, $UserType, $mobileNo, $vPhoneCode, $email, $password)
    {
        global $obj, $SIGN_IN_OPTION, $AUTH_OBJ, $generalobj;



        if ($UserType == 'Admin') {
            if (function_exists("checkMemberDataInfo")) {
            } else {
                $_REQUEST['type'] = "signIn";
                $SIGN_IN_OPTION = "password";
            }
            $checkValid = $this->getHotelDetails($iMemberId);

        } else {
            if (function_exists("checkMemberDataInfo")) {
                $checkValid = checkMemberDataInfo($mobileNo, '', $UserType, $vPhoneCode, $iMemberId, '', 1);
            } else {
                $_REQUEST['type'] = "signIn";
                $SIGN_IN_OPTION = "password";
                $checkValid = checkMemberDataInfoForDeleteAccount($mobileNo, '', $UserType, $vPhoneCode, '', '');
            }
        }

        if ($checkValid['status'] == 1) {
            if ($UserType == "Driver") {
                $returnArr['Details'] = $this->getDriverDetails($iMemberId);
            } else if ($UserType == "Company") {
                $returnArr['Details'] = $this->getCompanyDetails($iMemberId);
            } else if ($UserType == "Tracking") {
                $returnArr['Details'] = $this->getTrackingUserDetails($iMemberId);
            } else if ($UserType == 'Admin') {
                $returnArr['Details'] = $this->getHotelDetails($iMemberId);
            } else {
                $returnArr['Details'] = $this->getUserDetails($iMemberId);
            }
            if (strtoupper($SIGN_IN_OPTION) == "PASSWORD") {

                $hash = $returnArr['Details']['vPassword'];
                if ($_REQUEST['type'] == "signIn") {
                    $_REQUEST['type'] = "passwordAuth";
                    $checkValidP = checkMemberDataInfoForDeleteAccount($mobileNo, $password, $UserType, $vPhoneCode, '', '');
                    if ($checkValidP['status'] == 0) {
                        $checkValidPass = 0;
                    } else {
                        $checkValidPass = 1;
                    }
                } else {
                    $checkValidPass = $AUTH_OBJ->VerifyPassword($password, $hash);
                }
                if ($checkValidPass == 0) {
                    $returnArr['Action'] = "0";
                    $returnArr['message'] = "LBL_WRONG_DETAIL";
                } else {
                    $returnArr['Action'] = '1';
                }
            }
        } else {
            $returnArr['Action'] = '0';
            $returnArr['message'] = 'LBL_NOT_FIND';
            if ($_REQUEST['type'] == "passwordAuth") {
                $returnArr['message'] = "LBL_WRONG_DETAIL";
            }
        }

        return $returnArr;
    }

    public function AuthenticateMemberWithOtp($iMemberId, $UserType, $mobileNo, $vPhoneCode, $otp, $isOtpVerifyDone)
    {
        global $obj;
        //$checkValid = checkMemberDataInfo($mobileNo, '', $UserType, $vPhoneCode, $iMemberId, '');
        if (function_exists("checkMemberDataInfo")) {
            $checkValid = checkMemberDataInfo($mobileNo, '', $UserType, $vPhoneCode, $iMemberId, '', 1);
        } else {
            $checkValid = $generalobj->checkMemberDataInfo($mobileNo, '', $UserType, $vPhoneCode, $iMemberId, '');
        }
        if ($checkValid['status'] == 1) {
            if (empty($otp) && $isOtpVerifyDone == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_VERIFICATION_CODE_INVALID";
                return $returnArr;
        }
            if ($UserType == "Driver") {
                $query_1 = "SELECT iDriverId  FROM  register_driver  WHERE 1 = 1 AND iDriverId = " . $iMemberId . " AND iOTP = " . $otp;
            } elseif ($UserType == "Company") {
                $query_1 = "SELECT iCompanyId  FROM  company  WHERE 1 = 1 AND iCompanyId = " . $iMemberId . " AND vOTP = " . $otp;
            } else {
                $query_1 = "SELECT iUserId FROM register_user WHERE iUserId = '" . $iMemberId . "' AND iOTP =" . $otp;
            }
            $data = $obj->MySQLSelect($query_1);
            if (scount($data) == 0 && $isOtpVerifyDone == 0) {
                $returnArr['Action'] = "0";
                $returnArr['message'] = "LBL_VERIFICATION_CODE_INVALID";
            } else {
                if ($UserType == "Driver") {
                    $returnArr['Details'] = $this->getDriverDetails($iMemberId);
                } elseif ($UserType == "Company") {
                    $returnArr['Details'] = $this->getCompanyDetails($iMemberId);
                } else {
                    $returnArr['Details'] = $this->getUserDetails($iMemberId);
                }
                $returnArr['Action'] = '1';
            }
        } else {
            $returnArr['Action'] = '0';
            $returnArr['message'] = 'LBL_NOT_FIND';
        }
        return $returnArr;
    }

    public function updateAdmin($iMemberId)
    {
        global $obj;
        $where = " iAdminId = '$iMemberId' ";
        $Data_update_member['eStatus'] = 'Deleted';
        $register_user = $obj->MySQLQueryPerform('administrators', $Data_update_member, 'update', $where);
    }

    public function updateUser($iMemberId)
    {
        global $obj, $generalobj;
        if (function_exists("get_value")) {
        $vPhone = get_value('register_user', 'vPhone', 'iUserId', $iMemberId, '', 'true');
        } else {
            $vPhone = $generalobj->get_value('register_user', 'vPhone', 'iUserId', $iMemberId, '', 'true');
        }
        $where = " iUserId = '$iMemberId' ";
        $Data_update_member['eStatus'] = 'Deleted';
        $Data_update_member['vPhone'] = $vPhone . '(Deleted)';
        $register_user = $obj->MySQLQueryPerform('register_user', $Data_update_member, 'update', $where);
    }

    public function updateDriver($iMemberId)
    {
        global $obj, $generalobj;
        if (function_exists("get_value")) {
        $vPhone = get_value('register_driver', 'vPhone', 'iDriverId', $iMemberId, '', 'true');
        } else {
            $vPhone = $generalobj->get_value('register_driver', 'vPhone', 'iDriverId', $iMemberId, '', 'true');
        }
        $where = " iDriverId = '$iMemberId' ";
        $Data_update_member['vPhone'] = $vPhone . '(Deleted)';
        $Data_update_member['eStatus'] = 'Deleted';
        $obj->MySQLQueryPerform('register_driver', $Data_update_member, 'update', $where);
    }

    public function updateCompany($iMemberId)
    {
        global $obj, $generalobj;
        if (function_exists("get_value")) {
        $vPhone = get_value('company', 'vPhone', 'iCompanyId', $iMemberId, '', 'true');
        } else {
            $vPhone = $generalobj->get_value('company', 'vPhone', 'iCompanyId', $iMemberId, '', 'true');
        }
        $where = " iCompanyId = '$iMemberId' ";
        $Data_update_member['vPhone'] = $vPhone . '(Deleted)';
        $Data_update_member['eStatus'] = 'Deleted';
        $obj->MySQLQueryPerform('company', $Data_update_member, 'update', $where);
    }

    public function updateTrackingUser($iMemberId)
    {
        global $obj;
        $vPhone = get_value('track_service_users', 'vPhone', 'iTrackServiceUserId', $iMemberId, '', 'true');
        $where = " iTrackServiceUserId = '$iMemberId' ";
        $Data_update_member['vPhone'] = $vPhone . '(Deleted)';
        $Data_update_member['eStatus'] = 'Deleted';
        $obj->MySQLQueryPerform('track_service_users', $Data_update_member, 'update', $where);
    }
}

$DELETE_ACCOUNT_OBJ = new Delete_account_Web;
/*------------------class-----------------*/
/*------------------AJAX-----------------*/
$MEMBER_DELETE = isset($_REQUEST["MEMBER_DELETE"]) ? $_REQUEST["MEMBER_DELETE"] : '0';
if ($MEMBER_DELETE) {
    $GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
    $GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : '';
    $screen = isset($_REQUEST["screen"]) ? $_REQUEST["screen"] : 'mainSignIn';
    $signIn = isset($_REQUEST["signIn"]) ? $_REQUEST["signIn"] : '';
    $AuthenticateMember = isset($_REQUEST["AuthenticateMember"]) ? $_REQUEST["AuthenticateMember"] : '';
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    $CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
    $otpVerification = isset($_REQUEST["otpVerification"]) ? $_REQUEST["otpVerification"] : '';
    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
    $emailError = '';
    $pagename = 'accountdeleteprocess.php';
    $ajaxpagename = 'ajax_account_delete_process.php';
    /*------------------Delete Account Link For Play Store-----------------*/
    if ((empty($GeneralMemberId) || $GeneralMemberId == 0) && $MEMBER_DELETE == 1 && $email != '') {
        if (strtoupper($GeneralUserType) == "PASSENGER") {
            $Data = $obj->MySQLSelect("SELECT iUserId as GeneralMemberId FROM register_user WHERE (vPhone = '$email'  AND  vCountry = '" . $CountryCode . "' )  ");
        } elseif (strtoupper($GeneralUserType) == "DRIVER") {
            $Data = $obj->MySQLSelect("SELECT iDriverId  as GeneralMemberId  FROM register_driver WHERE (vPhone = '$email'  AND  vCountry = '" . $CountryCode . "' )  ");
        } elseif (strtoupper($GeneralUserType) == "COMPANY") {
            $Data = $obj->MySQLSelect("SELECT iCompanyId as GeneralMemberId   FROM company WHERE ( vPhone = '$email'  AND  vCountry = '" . $CountryCode . "' )");
        }elseif (strtoupper($GeneralUserType) == "TRACKING") {
            $Data = $obj->MySQLSelect("SELECT iTrackServiceUserId as GeneralMemberId   FROM track_service_users WHERE ( vPhone = '$email'  AND  vCountry = '" . $CountryCode . "' )");
        } elseif (strtoupper($GeneralUserType) == "ADMIN") {
            $Data = $obj->MySQLSelect("SELECT iAdminId as GeneralMemberId   FROM administrators WHERE vEmail = '" . $email . "' AND iGroupId = 4 ");
        }
        if (isset($Data) && !empty($Data)) {
            $GeneralMemberId = $Data[0]['GeneralMemberId'];

        } else {
            $GeneralMemberId = 0;
        }
    }
    /*------------------Delete Account Link For Play Store-----------------*/
    if (strtoupper($GeneralUserType) == "PASSENGER") {
        $memberData = $obj->MySQLSelect("SELECT iUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM register_user WHERE iUserId = '$GeneralMemberId'");
    } elseif (strtoupper($GeneralUserType) == "DRIVER") {
        $memberData = $obj->MySQLSelect("SELECT iDriverId, vLang, vPhone, vCode, vEmail, vPassword FROM register_driver WHERE iDriverId = '$GeneralMemberId'");
    } elseif (strtoupper($GeneralUserType) == "COMPANY") {
        $memberData = $obj->MySQLSelect("SELECT iCompanyId, vLang, vPhone, vCode, vEmail, vPassword FROM company WHERE iCompanyId = '$GeneralMemberId'");
    } elseif (strtoupper($GeneralUserType) == "COMPANY") {
        $memberData = $obj->MySQLSelect("SELECT iCompanyId, vLang, vPhone, vCode, vEmail, vPassword FROM company WHERE iCompanyId = '$GeneralMemberId'");
    } elseif (strtoupper($GeneralUserType) == "ADMIN") {
        $memberData = $obj->MySQLSelect("SELECT iAdminId, vEmail   FROM administrators WHERE iAdminId = '$GeneralMemberId')");
    }
    $vPhoneC = '';
    if (isset($CountryCode) && !empty($CountryCode)) {
        $vPhoneC_ = $obj->MySQLSelect("SELECT vPhoneCode FROM `country` WHERE vCountryCode = '$CountryCode'");
        $vPhoneC = $vPhoneC_[0]['vPhoneCode'];
    }
    $vLang = $memberData[0]['vLang'];
    $vLang = $_SESSION['sess_lang'];
    if (isset($languageLabelDataArr['language_label_1_' . $vLang])) {
        $languageLabelsArr = $languageLabelDataArr['language_label_1_' . $vLang];
    } else {
    $languageLabelsArr = $LANG_OBJ_WEB->FetchLanguageLabels($vLang, "1", $iServiceId);
        $languageLabelDataArr['language_label_1_' . $vLang] = $languageLabelsArr;
    }
    if (isset($signIn) && !empty($signIn)) {
        $vPhoneCode = '';
        $phone = '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $phone = $email;
            $email = '';
        }
        if (!empty($phone) || !empty($email)) {
            $data = $DELETE_ACCOUNT_OBJ->signIn($GeneralMemberId, $GeneralUserType, $phone, $CountryCode, $email);
            if ($data['Action'] == 1 && $data['showEnterPassword'] == 'Yes') {
                $screen = 'Password';
            } else if ($data['Action'] == 1 && $data['showEnterOTP'] == 'Yes') {
                $screen = 'OTP';
            } else {
                $emailError = $languageLabelsArr[$data['message']];
                if ($GeneralUserType == 'Admin') {
                    $emailError = 'Invalid Email';
                }
            }
        } else {
            $screen = 'mainSignIn';
            $emailError = $languageLabelsArr['LBL_FEILD_REQUIRD'];
        }
    }
    if (isset($AuthenticateMember) && !empty($AuthenticateMember)) {
        $vPhoneCode = '';
        $password = isset($_REQUEST["password"]) ? $_REQUEST["password"] : '';
        $phone = '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $phone = $email;
            $email = '';
        }
        $data = $DELETE_ACCOUNT_OBJ->AuthenticateMember($GeneralMemberId, $GeneralUserType, $phone, $CountryCode, $email, $password);
        if ($data['Action'] == 1) {
            $screen = 'deleteAccountConform';
            $Details = $data['Details'];
        } else {
            $screen = 'Password';
            $emailError = $languageLabelsArr[$data['message']];
            if (!isset($languageLabelsArr[$data['message']])) {
                $emailError = "It seems that you have entered wrong account details.";
            }
        }
        if ($password == '' && $emailError == '') {
            $emailError = $languageLabelsArr['LBL_NOT_FOUND'];
        }
    }
    if (isset($otpVerification) && !empty($otpVerification)) {
        $otp = isset($_REQUEST["otp"]) ? $_REQUEST["otp"] : '';
        $data = $DELETE_ACCOUNT_OBJ->AuthenticateMemberWithOtp($GeneralMemberId, $GeneralUserType, $email, $CountryCode, $otp);
        if ($data['Action'] == 1) {
            $screen = 'deleteAccountConform';
            $Details = $data['Details'];
        } else {
            $screen = 'OTP';
            $emailError = $languageLabelsArr[$data['message']];
        }
    }
    if (isset($action) && !empty($action) && $action == 'Continue') {
        if ($GeneralUserType == "Driver") {
            $DELETE_ACCOUNT_OBJ->updateDriver($GeneralMemberId);
        }
        if ($GeneralUserType == "Company") {
            $DELETE_ACCOUNT_OBJ->updateCompany($GeneralMemberId);
        }
        if ($GeneralUserType == "Tracking") {
            $DELETE_ACCOUNT_OBJ->updateTrackingUser($GeneralMemberId);
        }
        if ($GeneralUserType == "Admin") {
            $DELETE_ACCOUNT_OBJ->updateAdmin($GeneralMemberId);
        } else {
            $DELETE_ACCOUNT_OBJ->updateUser($GeneralMemberId);
        }
        $screen = 'DeleteSuccess';
        if ($MEMBER_DELETE == 0) {
            echo 1;
            exit;
        }
        // header('Location: '.$tconfig['tsite_url'].'/success.php?success=1&account_deleted=Yes');
        // exit;
    }
    $email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
    if ($screen == 'mainSignIn') {
        ?>

        <div class="account-delete-confirmation-from" id="signin-section">
            <form id="_signin-section" name="signin-section">
                <strong><?= $languageLabelsArr['LBL_ACC_INFO'] ?></strong>
                <div class="form-group">
                    <?php if (strtoupper($GeneralUserType) == "ADMIN") { ?>
                        <label><?= $languageLabelsArr['LBL_ENTER_EMAIL_TXT'] ?></label>
                    <?php } else { ?>
                        <label><?= $languageLabelsArr['LBL_ENTER_MOBILE_NO'] ?></label>
                    <?php } ?>
                    <div class="phone-input">

                        <?php if (strtoupper($GeneralUserType) == "ADMIN") { ?>
                            <input type="text" name="email" id="email" class="form-control">
                        <?php } else { ?>
                            <input type="text" name="email" id="email" class="form-control" pattern="^[0-9]*$"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                                   maxlength="15">
                        <?php } ?>

                        <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                        <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                        <input name="signIn" type="hidden" value="1">
                    </div>
                    <?php if (strtoupper($GeneralUserType) != "ADMIN") { ?>
                        <div class="mobile-info"
                             style="margin: -8px 0 20px 0; font-size: 11px;"><?= $languageLabelsArr['LBL_SIGN_IN_MOBILE_EMAIL_HELPER'] ?>
                        </div>

                    <?php } ?>

                </div>
                <span class="error-message"> <?php echo $emailError ?> </span>
                <a onclick="formsubmit('_signin-section');"
                   class="gen-button"><?= $languageLabelsArr['LBL_BTN_NEXT_TXT'] ?> <span><img
                                src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                                alt=""></span></a>
            </form>
        </div>

    <?php }
    if ($screen == 'Password') { ?>
        <div class="account-delete-confirmation-from" id="password-section">
            <form id="_password-section" name="password-section">

                    <?php if (strtoupper($GeneralUserType) == "ADMIN") { ?>

                        <p class="email-text"><?= $languageLabelsArr['LBL_EMAIL'] ?>
                        :  <?php echo $email; ?></p>
                    <?php } else { ?>
                    <p class="email-text"><?php if (!isset($languageLabelsArr['LBL_MOB_NO'])) {
                            echo "Mobile No.	";
                        } else {
                            echo $languageLabelsArr['LBL_MOB_NO'];
                        } ?>
                    : <?php echo '+' . $vPhoneC; ?><?php echo $email; ?>  </p>
                    <?php } ?>

                <div class="form-group">
                    <label><?= $languageLabelsArr['LBL_ENTER_PASSWORD_TXT'] ?></label>
                    <div class="phone-input">
                        <input type="password" name="password" id="password" class="form-control">
                        <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                        <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                        <input name="email" type="hidden" value="<?php echo $email; ?>">
                        <input name="CountryCode" type="hidden" value="<?php echo $CountryCode; ?>">
                        <input name="AuthenticateMember" type="hidden" value="1">
                        <span class="error-message" > <?php echo $emailError ?> </span>
                    </div>
                </div>

                <a onclick="formsubmit('_password-section');"
                   class="gen-button"><?= $languageLabelsArr['LBL_BTN_NEXT_TXT'] ?> <span><img
                                src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                                alt=""></span></a>
            </form>
        </div>
    <?php }
    if ($screen == 'OTP') { ?>
        <div class="account-delete-confirmation-from" id="verification-section">
            <form id="_verification-section" name="verification-section">
                <strong><?= $languageLabelsArr['LBL_TWO_STEP_VERIFICATION_TXT'] ?></strong>
                <div class="form-group">
                    <label><?= $languageLabelsArr['LBL_ENTER_OTP_NOTE'] ?><?php echo $email; ?> </label>
                    <div class="phone-input">
                        <input type="text" name="otp" id="otp" class="form-control" pattern="^[0-9]*$"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');"
                               maxlength="15">
                        <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                        <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                        <input name="email" type="hidden" value="<?php echo $email; ?>">
                        <input name="CountryCode" type="hidden" value="<?php echo $CountryCode; ?>">
                        <input name="otpVerification" type="hidden" value="1">
                        <span class="error-message" > <?php echo $emailError ?> </span>
                    </div>
                </div>
                <a onclick="formsubmit('_verification-section');"
                   class="gen-button"><?= $languageLabelsArr['LBL_BTN_VERIFY_TXT'] ?> <span><img
                                src="<?= $tconfig['tsite_url'] . "assets/img/apptype/" . $template . "/arrow.svg" ?>"
                                alt=""></span></a>
            </form>
        </div>
    <?php }
    if ($screen == 'deleteAccountConform') { ?>
        <div class="account-delete-confirmation-from" id="comfirm-delete-section">
            <form id="_comfirm-delete-section" name="comfirm-delete-section">
                <p class="sitename-text"><?= $SITE_NAME ?></p>
                <div class="profile-section">
                    <img src="<?php echo $Details['userImage'] ?>">
                    <div class="profile-info">
                        <strong>

                            <?php
                            if (isset($languageLabelsArr['LBL_PROFILE_NAME_TXT'])) {
                                echo $languageLabelsArr['LBL_PROFILE_NAME_TXT'];
                            } else {
                                echo "Profile Name";
                            }
                            ?>

                        </strong>
                        <span>(<?php echo $Details['userName'] ?> )</span>
                    </div>
                    <input name="GeneralUserType" type="hidden" value="<?php echo $GeneralUserType; ?>">
                    <input name="GeneralMemberId" type="hidden" value="<?php echo $GeneralMemberId; ?>">
                </div>
                <p>

                    <?php
                    if (isset($languageLabelsArr['LBL_ACCOUNT_DELETE_DESC'])) {
                        echo str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", $languageLabelsArr['LBL_ACCOUNT_DELETE_DESC']);
                    } else {
                        echo str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", "When you delete your account , your #APP_NAME# profile will be deactivated immediately and deleted permanently.");
                    }
                    ?>

                </p>
                <br>

                <p>

                    <?php
                    if (isset($languageLabelsArr['LBL_ACCOUNT_DELETE_DESC'])) {
                        echo str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", $languageLabelsArr['LBL_ACCOUNT_DELETE_RETAIN_INFO']);
                    } else {
                        echo str_replace("#APP_NAME#", "<b>" . $SITE_NAME . "</b>", "#APP_NAME# will retain certain information after account deletion as required or permitted by law.	");
                    }
                    ?>
                </p>

                <a onclick="formsubmit('_comfirm-delete-section','Continue');" style="color:white"
                   class="gen-button justify-center"><?= $languageLabelsArr['LBL_CONTINUE_BTN'] ?></a>
                <a onclick="formsubmit('_comfirm-delete-section','cancel');"
                   class="gen-button-white gen-button-negative justify-center"><?= $languageLabelsArr['LBL_BTN_CANCEL_TXT'] ?>
                    <span></a>
            </form>
        </div>

    <?php }
    if ($screen == 'DeleteSuccess') { ?>
        <div class="account-delete-confirmation-from" id="delete-success">
            <style>
                #delete-success {
                    padding: 20px;
                }

                .message-box {
                    padding: 20px;
                    border: 2px solid #333;
                    border-radius: 8px;
                    text-align: center;
                }
            </style>
            <div class="message-box">
                <p> Your account has been deleted. </p>
            </div>
        </div>
    <?php }
    exit;
} ?>

<?php
$GeneralMemberId = isset($_REQUEST["GeneralMemberId"]) ? $_REQUEST["GeneralMemberId"] : '';
$GeneralUserType = isset($_REQUEST["GeneralUserType"]) ? $_REQUEST["GeneralUserType"] : '';
$MEMBER_DELETE = 1;
/*------------------Delete Account Link For Play Store-----------------*/
if (strtoupper($GeneralUserType) == "USER") {
    $GeneralUserType = "Passenger";
}
if (strtoupper($GeneralUserType) == "PROVIDER") {
    $GeneralUserType = "Driver";
}

if (strtoupper($GeneralUserType) == "STORE") {
    $GeneralUserType = "Company";
}
if (strtoupper($GeneralUserType) == "FOODKIOSK") {
    $GeneralUserType = "Company";
}
if (strtoupper($GeneralUserType) == "KIOSK") {
    $GeneralUserType = "Admin";
}

if (strtoupper($GeneralUserType) == "TRACKING") {
    $GeneralUserType = "Tracking";
}
/*------------------Delete Account Link For Play Store-----------------*/
$screen = isset($_REQUEST["screen"]) ? $_REQUEST["screen"] : 'mainSignIn';
$signIn = isset($_REQUEST["signIn"]) ? $_REQUEST["signIn"] : '';
$AuthenticateMember = isset($_REQUEST["AuthenticateMember"]) ? $_REQUEST["AuthenticateMember"] : '';
$email = isset($_REQUEST["email"]) ? $_REQUEST["email"] : '';
$CountryCode = isset($_REQUEST["CountryCode"]) ? $_REQUEST["CountryCode"] : '';
$otpVerification = isset($_REQUEST["otpVerification"]) ? $_REQUEST["otpVerification"] : '';
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : '';
$emailError = '';
$pagename = 'accountdeleteprocess.php';
if (strtoupper($GeneralUserType) == "PASSENGER") {
    $memberData = $obj->MySQLSelect("SELECT iUserId, vLang, vPhone, vPhoneCode, vEmail, vPassword FROM register_user WHERE iUserId = '$GeneralMemberId'");
} elseif (strtoupper($GeneralUserType) == "DRIVER") {
    $memberData = $obj->MySQLSelect("SELECT iDriverId, vLang, vPhone, vCode, vEmail, vPassword FROM register_driver WHERE iDriverId = '$GeneralMemberId'");
} elseif (strtoupper($GeneralUserType) == "COMPANY") {
    $memberData = $obj->MySQLSelect("SELECT iCompanyId, vLang, vPhone, vCode, vEmail, vPassword FROM company WHERE iCompanyId = '$GeneralMemberId'");
}
$vLang = $memberData[0]['vLang'];
/*------------------Delete Account Link For Play Store-----------------*/
$vLang = $_SESSION['sess_lang'];
/*------------------Delete Account Link For Play Store-----------------*/
if (isset($languageLabelDataArr['language_label_1_' . $vLang])) {
    $languageLabelsArr = $languageLabelDataArr['language_label_1_' . $vLang];
} else {
$languageLabelsArr = $LANG_OBJ_WEB->FetchLanguageLabels($vLang, "1", $iServiceId);
    $languageLabelDataArr['language_label_1_' . $vLang] = $languageLabelsArr;
}
?>

<!DOCTYPE html>

<html lang="en"
      dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <?php include_once("top/top_script.php"); ?>

    <title><?= $languageLabelsArr['LBL_DELETE_ACCOUNT_TXT'] ?></title>
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,400,500,600,700,800,900&display=swap"
          rel="stylesheet"/>

    <script>
        document.write('<style type="text/css">body{display:none}</style>');
        jQuery(function ($) {
            $('body').css('display', 'block');
        });
    </script>

    <style>

        /*-----------------add_countrycode_dropdown----------------*/
        .countryPhoneSelectWrapper {
            height: 100%;
            width: 90px;
            position: absolute;
            z-index: 0;
            right: 0;
            direction: ltr;
        }

        .countryPhoneSelectChoice {
            position: absolute;
            top: 14px;
            left: 6px;
            height: auto;
            display: inline-table;
            width: 93%;
            border-radius: 5px;
            font-size: 12px;
            background: #fff;
        }

        .payment-block-inner .countryPhoneSelectChoice {
            top: 10px;
            background-color: transparent;
        }

        .accessAid {
            position: absolute !important;
            clip: rect(1px 1px 1px 1px);
            clip: rect(1px, 1px, 1px, 1px);
            padding: 0 !important;
            border: 0 !important;
            height: 1px !important;
            width: 1px !important;
            overflow: hidden;
        }

        .countryPhoneSelect {
            position: relative;
            height: 44px;
            width: 100px;
            border: 0;
            background: 0 0;
            opacity: 0;
            z-index: 1;
            direction: ltr;
        }

        .countryPhoneSelectWrapper .countryCode {
            position: relative;
            text-align: center;
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            padding: 5px;

        }

        .countryPhoneSelectWrapper .phoneCode {
            padding-left: 7px;
            width: 62%;
        }

        .countryPhoneSelectWrapper .countryCode, .countryPhoneSelectWrapper .phoneCode {
            display: table-cell;
            vertical-align: middle;
            font-size: 14px;
        }

        .phoneinput {
            padding-top: 0;
            width: 100%;
            box-sizing: border-box;
        }

        .countryPhoneSelectWrapper .countryCode {
            background-color: #239707;
        }

        [dir='rtl'] .countryPhoneSelectWrapper {
            left: 80%:
            border-right: transparent;
            right: 0;
        }

        [dir='rtl'] .form-group .form-control {
            /*padding-right: 100px;*/
            direction: rtl;
        }

        [dir='rtl'] .phoneinput {
            padding-right: 95px !important;
        }

        /*-----------------add_countrycode_dropdown----------------*/
        /*-----------------account delete process----------------*/
        * {
            box-sizing: border-box;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        }

        input, textarea, select {
            -webkit-touch-callout: default !important;
            -webkit-user-select: text !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'poppins';
            overscroll-behavior: none !important;
        }

        a {
            text-decoration: none;
        }

        .container {
            max-width: 767px;
            margin: auto;
            height: 100vh;
            background-color: #F0F0F0;
        }

        form {
            padding: 20px;
        }

        form strong {
            font-size: 24px;
        }

        .form-group .form-control {
            width: 100% !important;
            display: block;
            box-sizing: border-box;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 16px;
            border: 1px solid #e0e0e0;
            margin-bottom: 15px;
            outline: none !important;
        }

        .form-group label {
            display: block;
            font-size: 15px;
            font-weight: 500;
            display: block;
            font-size: 15px;
            font-weight: 500;
            margin: 10px 0 5px 0;
        }

        .gen-button, .gen-button-white {
            display: flex;
            justify-content: space-between;
            border: none;
            background-color: #333;
            color: #fff !important;
            padding: 10px 15px;
            width: 100%;
            align-items: center;
            font-size: 15px;
            text-transform: uppercase;
            margin-top: 20px;
            cursor: pointer;
        }

        .gen-button span {
            width: 20px;
        }

        .forgotpass-row {
            text-align: right;
        }

        .resendotp-row {
            text-align: left;
        }

        .forgotpass-row a, .resendotp-row {
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }

        .phone-input {
            position: relative;
            display: block;
        }



        .countryPhoneSelectWrapper .countryCode {
            font-weight: 600 !important;
            padding: 3px !important;
        }

        .email-text, .sitename-text {
            text-align: center;
            font-size: 18px;
            margin: 0 0 20px 0;
        }

        .sitename-text {
            font-size: 24px;
            font-weight: 600;
        }

        .profile-section {
            text-align: center;
        }

        .profile-section img {
            width: 115px;
            border: 2px solid #000000;
            border-radius: 50%;
        }

        .profile-info {
            display: block;
            margin-top: 10px;
        }

        .profile-info strong {
            display: block;
            font-size: 16px;
        }

        .gen-button-white {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #000000;
        }

        .gen-button-negative {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #000000;
            color: #00a9b7 !important;
        }

        .justify-center {
            justify-content: center;
        }

        .del-info {
            text-align: center;
            margin: 50px 0;
        }

        /*-----------------account delete process----------------*/


        #wrapper {
            min-height: initial;
        }

        .account_delete_confirmation .container {
            max-width: 1310px;
            margin: auto;
            min-height: calc(100vh - 433px);
            min-height: -o-calc(100vh - 433px);
            min-height: -moz-calc(100vh - 433px);
            min-height: -webkit-calc(100vh - 433px);
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            height: auto;
        }

        .account-delete-confirmation-from {
            width: 500px;
            max-width: 100%;
        }

        .account-delete-confirmation-from label {
            display: block;
            margin: 10px 0 15px 0;
            font-weight: 500;
        }

        .account-delete-confirmation-from
        strong {
            display: block;
            margin: 10px 0 15px 0;
            font-weight: 600;
        }
        .error-message{
            color: red;
        }

    </style>

</head>

<body class="account_delete_confirmation">
<?php if ($template != 'taxishark'){ ?>
<div id="main-uber-page">
    <?php } ?>
    <?php include_once("top/left_menu.php"); ?>
    <?php include_once("top/header_topbar.php"); ?>
    <?php include_once("top/header.php"); ?>

    <div class="container">

    </div>

    <?php include_once('footer/footer_home.php'); ?>
    <div style="clear:both;"></div>
    <?php if ($template != 'taxishark'){ ?>

</div>

<?php } ?>
<?php include_once('top/footer_script.php'); ?>

<!--
<script src="<?php /*= $tconfig['tsite_url'] . "/templates/" . $template . "/assets/js/less.min.js" */ ?>"></script>-->
<script>
    less = {
        env: 'development'
    };
</script>
<script type="text/javascript">
    ajaxpagename = 'account_delete_web.php';
    var tsite_url = '<?php echo $tconfig['tsite_url']; ?>';
    var pagename = '<?php echo $pagename; ?>';
    var screen = '<?php echo $screen; ?>';
    var GeneralMemberId = '<?php echo $GeneralMemberId; ?>';
    var GeneralUserType = '<?php echo $GeneralUserType; ?>';
    var MEMBER_DELETE = '<?php echo $MEMBER_DELETE; ?>';
    reloadPage(screen, GeneralMemberId, GeneralUserType);

    function reloadPage(screen, GeneralMemberId, GeneralUserType) {
        var data = {
            data: 1,
            screen: screen,
            GeneralMemberId: GeneralMemberId,
            GeneralUserType: GeneralUserType,
            MEMBER_DELETE: MEMBER_DELETE,
        };
        $.ajax({
            url: tsite_url + ajaxpagename,
            data: data,
            method: "POST",
            headers: {'Authorization': 'Bearer ' + SOrToKnDoE()},
            success: function (result) {
                if(GeneralUserType != 'Admin') {
                    getPhoneCodeInTextBox('email', 'CountryCode');
                }
                $('.container').html(result);
            }
        });
    }

    function formsubmit(formName, action = '') {
        var formdata = $("#" + formName).serialize();
        formdata = formdata + '&MEMBER_DELETE=' + MEMBER_DELETE;
        if (action != '') {
            formdata = formdata + '&action=' + action;
        }
        $.ajax({
            url: tsite_url + ajaxpagename,
            data: formdata,
            method: "POST",
            headers: {'Authorization': 'Bearer ' + SOrToKnDoE()},

            success: function (result) {

                if(GeneralUserType != 'Admin') {
                    getPhoneCodeInTextBox('email', 'CountryCode');
                }

                $('.container').html(result);
                if (result == 1) {
                    redirectSuccess();
                }
            }
        });
        return false;
    }

    function redirectSuccess() {
        var url = "<?php echo $tconfig['tsite_url'] ?>success.php?success=1&account_deleted=Yes";
        window.location.href = url;
    }

    /*------------------country-----------------*/
    function getPhoneCodeInTextBox(placeId, DropDownName) {
        $.ajax({
            type: "POST",
            url: 'account_delete_web.php',
            data: 'DropDownName=' + DropDownName + '&placeId=' + placeId + '&ForDeleteAccount=1',
            headers: {'Authorization': 'Bearer ' + SOrToKnDoE()},
            success: function (data) {
                $('#' + placeId).before(data);
            }
        });
    }

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }


    /*------------------country-----------------*/

    $(document).on('keyup keypress', 'form input[type="text"]', function(e) { if(e. keyCode == 13) { e. preventDefault(); return false; } });

    $(document).on('keyup keypress', 'form input[type="password"]', function(e) { if(e. keyCode == 13) { e. preventDefault(); return false; } });

</script>
</body>

</html>