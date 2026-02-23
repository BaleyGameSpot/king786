<?php
include_once("common.php");
$htmldropdown = '';
if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'rider') {
    $sess_iUserId = $_SESSION['sess_iUserId'];
    $rider_query = "SELECT vPhoneCode,vCountry from register_user where iUserId = $sess_iUserId";
    $rider_data = $obj->MySQLSelect($rider_query);
}

if (isset($_SESSION['sess_user']) && $_SESSION['sess_user'] == 'driver') {
    $sess_iUserId = $_SESSION['sess_iUserId'];
    $driver_query = "SELECT vCode,vCountry from register_driver where iDriverId = $sess_iUserId";
    $driver_data = $obj->MySQLSelect($driver_query);
}


if ($ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD == 'Yes') {
// ================ DropDown HTML Start ==============
    $classadd = '';
    $DropDownName = $_REQUEST['DropDownName'];
    $placeId = $_REQUEST['placeId'];
    $sql = "SELECT c.vValue,co.vPhoneCode from configurations as c LEFT JOIN country as co on co.vCountryCode=c.vValue where vName = 'DEFAULT_COUNTRY_CODE_WEB'";
    $defaultcountryArry = $obj->MySQLSelect($sql);
    $DefaultvPhoneCode = $defaultcountryArry[0]['vPhoneCode'];
    $DefaultvValue = $defaultcountryArry[0]['vValue'];
    $sqlC = "SELECT vCountryCode,vPhoneCode,vCountry from country where eStatus='Active' ORDER BY vCountry ASC";
    $AllcountryArry = $obj->MySQLSelect($sqlC);
    $SITEPATH = $tconfig['tsite_url'];
    $styleDisplyNone = 'style="display:none;" ';
    $htmldropdown = '<div class="countryPhoneSelectWrapper countryPhoneSelectWrapper' . $placeId . '" ' . $styleDisplyNone . '>
      <select name="' . $DropDownName . '" id="' . $DropDownName . '" class="countryPhoneSelect form-control">';
    foreach ($AllcountryArry as $Rows) {
        $htmldropdown .= '<option ';
        if ($Rows['vCountryCode'] == $defaultcountryArry[0]['vValue']) {
            $htmldropdown .= 'selected="selected"';
        }
        if (($Rows['vCountryCode'] == $rider_data[0]['vCountry']) && ($_SESSION['sess_user'] == 'rider')) {
            $htmldropdown .= 'selected="selected"';
            $DefaultvPhoneCode = $rider_data[0]['vPhoneCode'];
            $DefaultvValue = $rider_data[0]['vCountry'];
        }
        if (($Rows['vCountryCode'] == $driver_data[0]['vCountry']) && ($_SESSION['sess_user'] == 'driver')) {
            $htmldropdown .= 'selected="selected"';
            $DefaultvPhoneCode = $driver_data[0]['vCode'];
            $DefaultvValue = $driver_data[0]['vCountry'];
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
    $htmldropdown .= '<input type="hidden" name="isEmail' . $placeId . '" class="isEmail' . $placeId . '" value="Yes">';
    if ($THEME_OBJ->isXThemeActive() == 'Yes') {
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
    $htmldropdown .= "var " . $placeId . " = document.createElement('div');
            " . $placeId . ".setAttribute('id', 'ferrmsg" . $placeId . "');
            " . $placeId . ".setAttribute('class', 'help-block error');
            $(" . $placeId . ").insertAfter('#" . $placeId . "');";
    if ($THEME_OBJ->isXThemeActive() == 'Yes') {
        $htmldropdown .= "$('document').ready(function () {
          var SIGN_IN_OPTION = '" . $SIGN_IN_OPTION . "';
          $('#" . $DropDownName . " , .countryPhoneSelect ').change(function() {
                var fruitCount = $(this).attr('data-code');
                var phonecode = $(this).find(':selected').attr('data-code');
                var phonecountry = $(this).find(':selected').attr('data-country');
                $('.countryCode" . $placeId . "').text(phonecountry);
                $('.phoneCode" . $placeId . "').text(phonecode);
              });

              var selTabType = $('#type_usr').val();
              var CompSystem = $('.CompSystem').val();
              var myarray = ['Rider', 'Driver'];

            var timeout" . $placeId . " = null;
             $('#" . $placeId . "').keyup(function() {
                var inputvalue = $('#" . $placeId . "').val();

                document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                $('#btn_submit').removeAttr('disabled');

                 if($.isNumeric(inputvalue) && inputvalue!= '') {
                   $('.countryPhoneSelectWrapper" . $placeId . "').show(0,'swing');
                   $('#" . $placeId . "').addClass('phoneinput');
                   $('#" . $placeId . "').removeClass('emailinput');
                   $('.isEmail" . $placeId . "').val('No');
                 }else {


                  var GOON = 1;
                    GOON = floatingStatus();
                    if(GOON==1){
                     $('.countryPhoneSelectWrapper" . $placeId . "').hide(0);
                  $('#" . $placeId . "').removeClass('phoneinput');
                  }
                  $('#" . $placeId . "').addClass('emailinput');
                  $('.isEmail" . $placeId . "').val('Yes');

                  if(($('.CompSystem').val() == 'DeliverAll' && $('#type_usr').val() == 'Company' && SIGN_IN_OPTION == 'OTP') || (SIGN_IN_OPTION == 'OTP' && jQuery.inArray($('#type_usr').val(), myarray) !== -1)){
                    if((!validatePhone(inputvalue)) && (inputvalue != '')){
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'block';
                          clearTimeout('timeout" . $placeId . "');
                          timeout" . $placeId . " = setTimeout(function() {
                              document.getElementById('ferrmsg" . $placeId . "').innerHTML = '" . addslashes($langage_lbl['LBL_PHONE_VALID_MSG']) . "';
                              $('#btn_submit').attr('disabled','disabled');
                        }, 2000);
                    } else {
                        $('#btn_submit').removeAttr('disabled');
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                        document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                    }
                  } else {

                    if((!isEmail(inputvalue)) && (inputvalue != '')){
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'block';
                          clearTimeout('timeout" . $placeId . "');
                          timeout" . $placeId . " = setTimeout(function() {
                              document.getElementById('ferrmsg" . $placeId . "').innerHTML = '" . addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR']) . "';
                              $('#btn_submit').attr('disabled','disabled');
                        }, 2000);
                    } else {
                        $('#btn_submit').removeAttr('disabled');
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                        document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                    }

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

                  var GOON = 1;
                    GOON = floatingStatus();
                    if(GOON==1){
                    $('.countryPhoneSelectWrapper" . $placeId . "').hide(500);
                    $('#" . $placeId . "').removeClass('phoneinput');
                    }
                  $('#" . $placeId . "').addClass('emailinput');
                  $('.isEmail" . $placeId . "').val('Yes');

                if(($('.CompSystem').val() == 'DeliverAll' && $('#type_usr').val() == 'Company' && SIGN_IN_OPTION == 'OTP') || (SIGN_IN_OPTION == 'OTP' && jQuery.inArray($('#type_usr').val(), myarray) !== -1)){

                    if((!validatePhone(inputvalue)) && (inputvalue != '')){
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'block';
                          clearTimeout('timeout" . $placeId . "');
                          timeout" . $placeId . " = setTimeout(function() {
                              document.getElementById('ferrmsg" . $placeId . "').innerHTML = '" . addslashes($langage_lbl['LBL_PHONE_VALID_MSG']) . "';
                              $('#btn_submit').attr('disabled','disabled');
                        }, 2000);
                    } else {
                        $('#btn_submit').removeAttr('disabled');
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                        document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                    }

                } else {
                    if((!isEmail(inputvalue)) && (inputvalue != '')){
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'block';
                          clearTimeout('timeout" . $placeId . "');
                          timeout" . $placeId . " = setTimeout(function() {
                              document.getElementById('ferrmsg" . $placeId . "').innerHTML = '" . addslashes($langage_lbl['LBL_FEILD_EMAIL_ERROR']) . "';
                              $('#btn_submit').attr('disabled','disabled');
                        }, 2000);
                    } else {
                        $('#btn_submit').removeAttr('disabled');
                        document.getElementById('ferrmsg" . $placeId . "').style.display = 'none';
                        document.getElementById('ferrmsg" . $placeId . "').innerHTML = '';
                    }
                  }

                 }
              });

              $('.tab-switch li').on('click', function () {
                var dataId = $(this).attr('data-id');
                    var GOON = 1;
                    GOON = floatingStatus();
                    if(GOON==1){
                        $('#" . $placeId . "').removeClass('phoneinput');
                    }
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

                    $('.phone-field').removeClass('country-code');
                    var GOON = 1;
                    GOON = floatingStatus();
                    if(GOON==1){
                    $('.countryPhoneSelectWrapper" . $placeId . "').hide(500);
                    $('#" . $placeId . "').removeClass('phoneinput');
                    }
                    $('#" . $placeId . "').addClass('emailinput');
                    $('.isEmail').val('Yes');
                   }
                });

                $('.tab-switch li').on('click', function () {
                  var dataId = $(this).attr('data-id');

                    var GOON = 1;
                    GOON = floatingStatus();
                    if(GOON==1){
                      $('#" . $placeId . "').removeClass('phoneinput');
                    }
                });

              });
              </script>";
    }
}
// ================ DropDown HTML END ==============
echo $htmldropdown;
exit;
?>