<?php

include_once 'common.php';


if (isset($_POST['vNamenewsletter'])) {
    if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);

        if ($valiedRecaptch) {
            $vNamenewsletter = trim($_REQUEST['vNamenewsletter']);
            $vEmailnewsletter = trim($_REQUEST['vEmailnewsletter']);
            $eStatus = trim($_REQUEST['eStatus']);
            $remoteIp = $_SERVER['REMOTE_ADDR'];
            $dateTime = date("Y-m-d H:i:s");

            $chkUser = "SELECT * FROM `newsletter` WHERE vEmail = '" . $vEmailnewsletter . "' ";
            $chkUserCnt = $obj->MySQLSelect($chkUser);
            $fetchStatus = $chkUserCnt[0]['eStatus'];

            if (scount($chkUserCnt) > 0) {

                if (($fetchStatus == "Unsubscribe") && ($eStatus == "Unsubscribe")) {
                    header("Location:thank-you.php?action=Alreadyunsubscribe");
                    exit;
                } if (($fetchStatus == "Subscribe") && ($eStatus == "Subscribe")) {
                    header("Location:thank-you.php?action=Alreadysubscribe");
                    exit;
                }

                $insert_query = "UPDATE newsletter SET vLang='" . $_SESSION['sess_lang'] . "', vName='" . $vNamenewsletter . "', vIP='" . $remoteIp . "',tDate='" . $dateTime . "', eStatus = '" . $eStatus . "' WHERE vEmail='" . $vEmailnewsletter . "'";
                $obj->sql_query($insert_query);


                if (($fetchStatus == "Subscribe") && ($eStatus == "Unsubscribe")) {
                    $maildata['EMAIL'] = $vEmailnewsletter;
                    $maildata['NAME'] = $vNamenewsletter;
                    $maildata['EMAIL_NAME'] = $vNamenewsletter;
                    $maildata['EMAILID'] = $SUPPORT_MAIL;
                    $maildata['PHONENO'] = $SUPPORT_PHONE;
                    $Data_Insert['vLang'] = $_SESSION['sess_lang'];
                    $COMM_MEDIA_OBJ->SendMailToMember("MEMBER_NEWS_UNSUBSCRIBE_USER", $maildata);
                }
                if (($fetchStatus == "Unsubscribe") && ($eStatus == "Subscribe")) {
                    $maildata['EMAIL'] = $vEmailnewsletter;
                    $maildata['NAME'] = $vNamenewsletter;
                    $maildata['EMAIL_NAME'] = $vNamenewsletter;
                    $maildata['EMAILID'] = $SUPPORT_MAIL;
                    $maildata['PHONENO'] = $SUPPORT_PHONE;

                    $COMM_MEDIA_OBJ->SendMailToMember("MEMBER_NEWS_SUBSCRIBE_USER", $maildata);
                }

            } else {

                if ((scount($chkUserCnt) == 0) && $eStatus == 'Unsubscribe') {
                    header("Location:thank-you.php?action=Notsubscribe");
                    exit;
                }

                $Data_Insert['vName'] = $vNamenewsletter;
                $Data_Insert['vEmail'] = $vEmailnewsletter;
                $Data_Insert['vIP'] = $remoteIp;
                $Data_Insert['tDate'] = $dateTime;
                $Data_Insert['eStatus'] = $eStatus;
                $Data_Insert['vLang'] = $_SESSION['sess_lang'];

                $obj->MySQLQueryPerform("newsletter", $Data_Insert, 'insert');

                if ($eStatus == 'Subscribe') {
                    $maildata['EMAIL'] = $vEmailnewsletter;
                    $maildata['NAME'] = $vNamenewsletter;
                    $maildata['EMAIL_NAME'] = $vNamenewsletter;
                    $maildata['EMAILID'] = $SUPPORT_MAIL;
                    $maildata['PHONENO'] = $SUPPORT_PHONE;

                    $COMM_MEDIA_OBJ->SendMailToMember("MEMBER_NEWS_SUBSCRIBE_USER", $maildata);
                }


            }

            header("Location: thank-you.php?action=$eStatus");
            exit;
        } else {
            header("Location: thank-you.php?action=Recaptchafail");
            exit;
        }
    } else {
        //$obj->sql_query($insert_query);
        header("Location: thank-you.php?action=Recaptchafail");
        exit;
    }
}
?>