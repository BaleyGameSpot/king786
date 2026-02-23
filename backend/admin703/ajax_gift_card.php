<?php
include_once('../common.php');
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$iGiftCardId = isset($_REQUEST['iGiftCardId']) ? $_REQUEST['iGiftCardId'] : '';
$vLangCode = $LANG_OBJ->FetchDefaultLangData("vCode");
if ($type == "sendInfo") {
    $db_str = $obj->MySQLSelect("SELECT * FROM gift_cards WHERE iGiftCardId ='" . $iGiftCardId . "'");
    $db_str = $db_str[0];
    $tReceiverDetails = json_decode($db_str['tReceiverDetails'], true);

    if ($db_str['eCreatedBy'] == 'User') {
        $db_str['eCreatedBy'] = 'Passenger';
    }
    if (in_array($db_str['eUserType'], ['DriverSpecific', 'UserSpecific'])) {
        $tReceiverDetailsDB = $GIFT_CARD_OBJ->getUserData($db_str['iMemberId']);
        $tReceiverDetails['tReceiverName'] = $tReceiverDetailsDB['userName'];
        $tReceiverDetails['tReceiverEmail'] = $tReceiverDetailsDB['vEmail'];
        $tReceiverDetails['tReceiverMessage'] = '';
        $tReceiverDetails['tReceiverName'] = $tReceiverDetailsDB['userName'];
        $tReceiverDetails['vReceiverPhone'] = $tReceiverDetailsDB['vPhone'];
        $tReceiverDetails['vReceiverPhoneCode'] = $tReceiverDetailsDB['vPhoneCode'];
    }

    $MemberData = $GIFT_CARD_OBJ->getUserData($db_str['iCreatedById']);

    $_REQUEST['GeneralMemberId'] = $db_str['iCreatedById'];
    $_REQUEST['GeneralUserType'] = $db_str['eCreatedBy'];
    $_REQUEST['tReceiverName'] = $tReceiverDetails['tReceiverName'];
    $_REQUEST['tReceiverMessage'] = $tReceiverDetails['tReceiverMessage'];
    $_REQUEST['tReceiverEmail'] = $tReceiverDetails['tReceiverEmail'];
    $_REQUEST['fAmount'] = $db_str['fAmount'];
    $_REQUEST['iGiftCardImageId'] = $db_str['iGiftCardImageId'];

    $vGiftCardCode = $db_str['vGiftCardCode'];
    if (!empty($_REQUEST['tReceiverEmail'])) {
        $data = $COMM_MEDIA_OBJ->giftcardemaildataRecipt($_REQUEST, $vGiftCardCode);
        $arr['mail'] = 1;
        $arr['mailSend'] = $_REQUEST['tReceiverEmail'];
    }

    $vPhone = $tReceiverDetails['vReceiverPhone'];
    $vPhoneCode = $tReceiverDetails['vReceiverPhoneCode'];

    $dataArraySMSNew['RECEIVER_NAME'] = $_REQUEST['tReceiverName'];
    $dataArraySMSNew['GIFT_CARD_CODE'] = $vGiftCardCode;
    $dataArraySMSNew['SENDER_NAME'] = $MemberData['userName'];
    $dataArraySMSNew['AMOUNT'] = formateNumAsPerCurrency($db_str['fAmount'], $MemberData['vCurrency']);;
    $message = $COMM_MEDIA_OBJ->GetSMSTemplate('GIFT_CARD_RECEIVED', $dataArraySMSNew, "", $vLangCode);


    $result = $COMM_MEDIA_OBJ->SendSystemSMS($vPhone, $vPhoneCode, $message);
    
    if($result == 1) {
        $arr['sms'] = 1;
        $arr['smsSend'] = '+' . $vPhoneCode . ' ' . $vPhone;
    }
    echo json_encode($arr);
    /*--------------------- send mail to RECEIVER  ------------------*/
}

?>