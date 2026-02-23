<?php
$action_from = isset($_REQUEST['action_from']) ? $_REQUEST['action_from'] : '';
$iBiddingPostId = isset($_REQUEST['iBiddingPostId']) ? $_REQUEST['iBiddingPostId'] : '';

if ($action_from != '' && $iBiddingPostId != '') {
    include_once('common.php');
    if($_SESSION['sess_iAdminUserId'] != ""){
        $return = sendBiddingServiceReceipt($iBiddingPostId);
    }
    
    $invocieFile = "invoice_bids.php";

    if($return == "1" || $return == 1){
        header("location:" . $tconfig['tsite_url_main_admin'] . $invocieFile . '?iBiddingPostId=' . $iBiddingPostId . '&success=1');
        exit;
    }

    if($return == "3" || $return == 3){
        header("location:" . $tconfig['tsite_url_main_admin'] . $invocieFile . '?iBiddingPostId=' . $iBiddingPostId . '&fail=0');
        exit;
    }

    header("location:" . $tconfig['tsite_url_main_admin'] . $invocieFile . '?iBiddingPostId=' . $iBiddingPostId . '&success=1');
    exit;
}
function remote_file_exists($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if( $httpCode == 200 ){return true;}
    return false;
}
####################### FUNCTIONS:for email receipt ##########################  

function sendBiddingServiceReceipt($iBiddingPostId) {
    global $obj, $tconfig, $APP_TYPE,$languageLabelDataArr,$APP_DELIVERY_MODE,$COPYRIGHT_TEXT,$userDetailsArr,$ENABLE_PHONE_LOGIN_VIA_COUNTRY_SELECTION_METHOD, $CONFIG_OBJ, $COMM_MEDIA_OBJ,$BIDDING_OBJ;
    
    $Data = array();
    //Added By HJ On 25-06-2020 For Optimize trips Table Query Start
    $biddingData = $BIDDING_OBJ->getBiddingPost('webservice', $iBiddingPostId);
    $iMemberId = $biddingData[0]['iUserId'];
    $UserType = 'Passenger';
    $db_trip =  $BIDDING_OBJ->getFareDetails($iBiddingPostId, $iMemberId, $UserType , "mail");

    $userData = $obj->MySQLSelect("SELECT CONCAT(vName, ' ', vLastName) as userName FROM register_user WHERE iUserId = '$iMemberId' ");

    if($db_trip['userEmail'] == ""){
         return 3;
         exit;
    }
    

    $Data[0]['slocation'] = $db_trip['tSaddress'];
    $Data[0]['driver'] = $db_trip['driverName'];
    $Data[0]['vLang'] = $db_trip['userlang'];
    $Data[0]['userEmail'] = $db_trip['userEmail'];
    $Data[0]['EMAIL_NAME'] = $userData['userName'];
    /* ############### language code################ */
    $user_lang_code = $Data[0]['vLang'];
    if ($user_lang_code == "") {
        $user_lang_code = "EN";
        //Added By HJ On 25-06-2020 For Optimize language_master Table Query Start
        if (!empty($vSystemDefaultLangCode)) {
            $user_lang_code = $vSystemDefaultLangCode;
        } else {
            $user_lang_code = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
        }
        //Added By HJ On 25-06-2020 For Optimize language_master Table Query End
    }
    
    $vLabel_user_mail = array();
    //Added BY HJ On 25-06-2020 For Optimize language_label Table Query Start
    if(isset($languageLabelDataArr['language_label_'.$user_lang_code])){
        $db_lbl = $languageLabelDataArr['language_label_'.$user_lang_code];
    }else{
        $db_lbl=$obj->MySQLSelect("select * from language_label where vCode='".$user_lang_code."'");
        $languageLabelDataArr['language_label_'.$user_lang_code] = $db_lbl;
    }
    //Added BY HJ On 25-06-2020 For Optimize language_label Table Query End
    
    foreach ($db_lbl as $key => $value) {
        $vLabel_user_mail[$value['vLabel']] = $value['vValue'];
    }

    /* Language Label Other */
    //Added BY HJ On 25-06-2020 For Optimize language_label_other Table Query Start
    if(isset($languageLabelDataArr['language_label_other_'.$user_lang_code])){
        $db_lbl = $languageLabelDataArr['language_label_other_'.$user_lang_code];
    }else{
        $db_lbl=$obj->MySQLSelect("select * from language_label_other where vCode='".$user_lang_code."'");
        $languageLabelDataArr['language_label_other_'.$user_lang_code] = $db_lbl;
    }
    //Added BY HJ On 25-06-2020 For Optimize language_label_other Table Query End
   
    foreach ($db_lbl as $key => $value) {
        $vLabel_user_mail[$value['vLabel']] = $value['vValue'];
    }


    $border_tbl = "border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;";
 
    $Data[0]['vRating'] = $db_trip['driverAvgRating'];
    if (remote_file_exists($db_trip['driverImage'])) {
        $img = $db_trip['driverImage'];
    } else {
        $img = $tconfig["tsite_url"] . "webimages/icons/help/driver.png";
    }

    
    $car = $db_trip['vServiceDetailTitle'];
    $ridenum = $db_trip['vBiddingPostNo'];
    $Data[0]['CurrencySymbol'] = $db_trip['CurrencySymbol'];
    $Data[0]['ProjectName'] = $CONFIG_OBJ->getConfigurations("configurations", "SITE_NAME");
    $Data[0]['ProjectName1'] = '<img class="logo" src="' . $tconfig["tsite_home_images"] . 'logo.png" alt="">';
    $Data[0]['car'] = $car;
    $eIconType = $vLabel_user_mail['LBL_VIEW_TASK_BIDDING'];
    $Data[0]['ridenum'] = $ridenum;
    $Data[0]['total_amt'] = $db_trip['FareSubTotal'];
    $Data[0]['copyright'] = $COPYRIGHT_TEXT;

    $systemTimeZone = date_default_timezone_get();
    if (!empty($biddingData[0]['vTimeZone'])) {
        $starttime = converToTz($db_trip['dBiddingDate'], $biddingData[0]['vTimeZone'], $systemTimeZone);
    } else {
        $starttime = $db_trip['dBiddingDate'];
    }
    $start_time = date('h:i A', strtotime($starttime));
    $Data[0]['start_time'] = $start_time;

    $email_con_location = '<tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left"><td rowspan="2" style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:left;display:table-cell;width:17px!important;padding:3px 10px 10px 17px" align="left" valign="top">
                <img src="' . $tconfig["tsite_url"] . 'webimages/icons/help/green-lolo.png" style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left"  width="13" class="CToWUd">
            </td>           
        </tr>
        <tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">

            <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:left;display:table-cell;width:279px;line-height:16px;height:auto;padding:0 0px 0 0" align="left" valign="top">
                <span style="font-size:15px;font-weight:500;color:#000000!important">
                    <span class="aBn" data-term="goog_43159641" tabindex="0">

                        <span class="aQJ"><a href="#" style="font-size:15px;font-weight:600;color:#000000!important;text-decoration:none;">' . $Data[0]['start_time'] . '</a></span>
                    </span>
                </span><br>
                <span><a href="#" style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">' . $Data[0]['slocation'] . '</a></span>
            </td>
        </tr>';

    $invoice = '<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:100%!important;padding:0">
            <tbody>
            <tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">
                <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:left;display:table-cell;padding:12px 0 5px" align="left" valign="middle">
                    <p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:left;line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
                    </p>
                </td>
                <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:center;display:table-cell;width:120px!important;font-size:11px;white-space:pre-wrap;padding:12px 10px 5px" align="center" valign="middle">' . $vLabel_user_mail['LBL_FARE_BREAKDOWN'] . '</td>
                <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:left;display:table-cell;padding:12px 0 5px" align="left" valign="middle">
                    <p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:left;line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
                    </p>
                </td>
            </tr>
            </tbody>
            </table><table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;margin-top:15px;width:auto;padding:0"><tbody>';
    
    foreach ($db_trip['FareDetailsNewArr'] as $key => $value) {
        foreach ($value as $k => $val) {
            if ($k == $vLabel_user_mail['LBL_SUBTOTAL_TXT']) { 
                continue; 
            } else if ($k == "eDisplaySeperator") {
                $invoice .= '<tr><td colspan="2"><div style="border-top:1px dashed #d1d1d1"></div></td></tr>';
            } else {
                $invoice .= '<tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left"><td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:left;display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">' . $k . '</td><td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:right;display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">' . $val . '</td></tr>';
            }
        }   
    }


    $invoice .= '<tr style="vertical-align:top;text-align:left;font-weight:bold;width:100%;padding:0;vertical-align:top;text-align:left;border-top:1px;border-top-width:1px;border-top-color:#f0f0f0;border-top-style:solid;" align="left">
    <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:left;display:table-cell;width:300px;color:#111125;padding:5px 4px 4px" align="left" valign="top">' . $vLabel_user_mail['LBL_SUBTOTAL_TXT'] . '</td>
    <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:right;display:table-cell;width:90px;white-space:nowrap;padding:5px 4px 4px" align="right" valign="top">' . $db_trip['FareSubTotal'] . '</td>
    </tr>';
    

    if($db_trip['vBiddingPaymentMode'] == 'Wallet'){
        $paymentMode = $vLabel_user_mail['LBL_WALLET_TXT'];
    } else if($db_trip['vBiddingPaymentMode'] == 'Card') {
        $paymentMode = $vLabel_user_mail['LBL_VIA_CARD_TXT'];
    } else {
        $paymentMode = $vLabel_user_mail['LBL_VIA_CASH_TXT'];
    }
    
  
    $subtotal = $db_trip['FareSubTotal'];
    
    $invoice .= '<tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">
                        <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:left;display:table-cell;width:300px;color:#808080;line-height:18px;padding:5px 4px" align="left" valign="top">
                            <span style="font-size:9px;line-height:7px">' . $vLabel_user_mail['LBL_CHARGED_TXT'] . '</span>
                            <br>
                            <img src="' . getInvoicePaymentImg($db_trip['vBiddingPaymentMode']) . '" style="outline:none;text-decoration:none;float:left;clear:both;display:block;width:40px!important;min-height:25px;margin-right:5px;margin-top:3px" align="left" height="12" width="17" >
                            <span style="font-size:13px">
                                ' . $paymentMode . '
                            </span>
                        </td>
                        <td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:right;display:table-cell;width:90px;white-space:nowrap;font-size:19px;font-weight:bold;line-height:30px;padding:20px 4px 5px" align="right" valign="top">
                            ' . $subtotal . '
                        </td>
                    </tr></tbody></table>';

    # User Email below code
    $mailcont_member = '<div style="width:730px;!important;color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:left;line-height:19px;font-size:14px;margin:0;padding:0">
<table  style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:100%;color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;line-height:19px;font-size:14px;margin:0;padding:0"><tbody><tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left"><td style="border-collapse:collapse!important;vertical-align:top;text-align:center;padding:0" align="center" valign="top">
    <center style="width:100%;min-width:580px">
        <table style="border-color:#e3e3e3;border-style:solid;border-width:1px 1px 1px 1px;vertical-align:top;text-align:inherit;width:660px;margin:0 auto;padding:0"><tbody><tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left"><td style="border-collapse:collapse!important;vertical-align:top;text-align:left;padding:0" align="left" valign="top">
        
            <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:640px;max-width:640px;border-radius:2px;background-color:#ffffff;margin:0 10px;padding:0" bgcolor="#ffffff">
                <tbody>
                    <tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">
                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:inline-block;width:100%;padding:0" align="left" valign="top">
                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#e3e3e3;border-bottom-style:solid;padding:0">
                                <tbody>
                                    <tr style="vertical-align:top;text-align:left;width:100%;background-color:rgb(250,250,250);padding:0" align="left" bgcolor="rgb(250,250,250)">
                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:inline-block;width:299px;border-radius:3px 0 0 0;background-color:#fafafa;padding:26px 10px 20px" align="left" bgcolor="#FAFAFA" valign="top">
                                            <span style="font-weight:bold;font-size:32px;color:#000;line-height:30px;padding-left:15px">
                                                ' . $Data[0]['total_amt'] . '
                                            </span>
                                        </td>
                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:right;display:inline-block;width:290px;border-radius:0 3px 0 0;background-color:#fafafa;padding:26px 10px 20px" align="right" bgcolor="#FAFAFA" valign="top">
                                        <span style="vertical-align:top;text-align:right;font-size:11px;color:#999999;text-transform:uppercase;padding-right:10px">' . $vLabel_user_mail['LBL_TRIP_DATE_TXT_DRDL'] . ' :' . @date('d M Y', @strtotime($starttime)) . '</span><br/>
                                            <span style="vertical-align:top;text-align:right;font-size:11px;color:#999999;text-transform:uppercase;padding-right:10px">' . $vLabel_user_mail['LBL_BIDDING_TXT']. " " . $vLabel_user_mail['LBL_NUMBER_TXT']. ' :' . $Data[0]['ridenum'] . '</span> <BR/>
                                            <span style="font-size:12px;font-weight:normal;color:#b2b2b2">' . $vLabel_user_mail['LBL_THANKS_FOR_CHOOSING_TXT_ADMIN'] . ' ' . $Data[0]['ProjectName'] . '</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>';


    $mailcont_member .= '<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:100%;max-width:640px;' . $border_tbl . 'padding:0">
                                <tbody>
                                    <tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">
                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:inline-block;width:300px;padding:25px 10px 25px 5px" align="left" valign="top">
                                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;margin-left:19px;padding:0">
                                                <tbody>
                                                    <tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">
                                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:inline-block;width:300px;padding:0" align="left" valign="top">
                                                            
                                                            <div class="a6S" dir="ltr" style="opacity: 0.01; left: 432.922px; top: 670px;">
                                                                <div id=":n0" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Download attachment map_32aba2dc-7679-4c0e-bea3-7f4d8e8f934a" data-tooltip-class="a1V" data-tooltip="Download">
                                                                    <div class="aSK J-J5-Ji aYr"></div>
                                                                </div>
                                                                <div id=":n1" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Save attachment to Drive map_32aba2dc-7679-4c0e-bea3-7f4d8e8f934a" data-tooltip-class="a1V" data-tooltip="Save to Drive">
                                                                    <div class="wtScjd J-J5-Ji aYr aQu">
                                                                        <div class="T-aT4" style="display: none;">
                                                                            <div></div>
                                                                            <div class="T-aT4-JX"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr style="vertical-align:top;text-align:left;width:279px;display:block;background-color:#fafafa;padding:20px 0;border-color:#e3e3e3;border-style:solid;border-width:1px 1px 0px" align="left" bgcolor="#FAFAFA">
                                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:inline-block;width:279px;padding:0" align="left" valign="top">
                                                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:auto;padding:0">
                                                                <tbody>
                                                                    ' . $email_con_location . '
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr style="vertical-align:top;text-align:left;width:279px;display:block;background-color:#fafafa;padding:0;border:1px solid #e3e3e3" align="left" bgcolor="#FAFAFA">
                                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:table-cell!important;width:279px!important;padding:0" align="left" valign="top">
                                                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:left;width:100%;color:#959595;line-height:14px;padding:0">
                                                                <tbody>
                                                                    <tr style="vertical-align:top;text-align:left;width:100%;padding:0" align="left">
                                                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
                                                                            <span style="font-size:9px;text-transform:uppercase">' . $eIconType . '</span><br>
                                                                            <span style="font-size:13px;color:#111125;font-weight:normal">' . $Data[0]['car'] . '</span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td style="border-collapse:collapse!important;vertical-align:top;text-align:left;display:inline-block;width:300px;padding:10px" align="left" valign="top">
                                            <span style="display:block;padding:0px 8px 0 10px">
                                            ' . $invoice . '
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                
                            
                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:left;width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;padding:0">
                                <tbody>
                                    <tr style="vertical-align:middle;text-align:left;width:100%;padding:0" align="left">
                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:inline-block;width:50%;padding:0px" align="left" valign="middle">
                                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:left;width:100%;max-width:640px;display:inline-block;padding:0">
                                                <tbody>
                                                    <tr style="vertical-align:middle;text-align:left;width:100%;padding:0" align="left">
                                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:inline-block;width:100%!important;line-height:15px;padding:0px 0px 0px 10px" align="left" valign="middle">
                                                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:left;width:auto;padding:0">
                                                                <tbody>
                                                                    <tr style="vertical-align:middle;text-align:left;width:100%;padding:0" align="left">
                                                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:table-cell;width:45px;padding:5px 0px 5px" align="left" valign="middle">
                                                                            <img src="' . $img . '" style="outline:none;text-decoration:none;float:left;clear:both;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;margin-left:15px;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7" align="left" height="45" width="45" class="CToWUd">
                                                                        </td>
                                                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:table-cell;width:300px;padding:5px 5px 5px" align="left" valign="middle">
                                                                            <span style="padding-bottom:5px;display:inline-block;font-size:15px;">' . $vLabel_user_mail['LBL_You_ride_with'] . ' ' . $Data[0]['driver'] . '</span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:inline-block;width: 50%;height:100%;padding:0px 0px 0px" align="left" valign="middle">
                                            <table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:left;width:100%;max-width:640px;display:block;">
                                                <tbody style="width:100%;display:block">
                                                    <tr style="vertical-align:middle;text-align:right;width:100%;display:block;padding:0px" align="right">
                                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:inline;font-size:12px;color:#808080;text-transform:uppercase;padding:0px 5px 0px 0px" align="left" valign="middle">
                                                            <span>' . $vLabel_user_mail['LBL_TRIP_RATING_TXT'] . '</span>
                                                        </td>
                                                        <td style="border-collapse:collapse!important;vertical-align:middle;text-align:left;display:inline-block" align="left" valign="middle">
                                                            <b style="font-size:10px;display:inline-block!important;padding:0px 2px">' . getInvoiceRating($Data[0]['vRating']) . '</b>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
        </tr>
        </tbody>
        </table>
    </div>';
    
    $maildata_member['vRideNo'] = $ridenum;
    $maildata_member['details'] = $mailcont_member;
    $maildata_member['email'] = $Data[0]['userEmail'];
    
    
    // echo "<pre>"; print_R($maildata_member); exit;
    return $COMM_MEDIA_OBJ->SendMailToMember("RIDER_INVOICE", $maildata_member);
}

####################### for email receipt end ##########################
?>
