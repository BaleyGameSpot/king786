<?php

include_once("common.php");

$user_type1 = $_REQUEST['type'] = $_POST['user_type'];
if($user_type1=='driver') {
    $user_type1 = $_REQUEST['type'] = 'provider';
}
if($THEME_OBJ->isXThemeActive() == 'Yes') 
    //$url = "cx-sign-up.php?type=".$user_type1."&";
    $url = "sign-up.php?type=".$user_type1."&";
else
    $url = "sign-up.php?";
    
if(SITE_TYPE=='Demo') {
    header("Location:" . $tconfig["tsite_url"] . $url ."error=1");
    exit;
}

if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
    $valiedRecaptch = isRecaptchaValid($GOOGLE_CAPTCHA_SECRET_KEY, $_POST['g-recaptcha-response']);
    if ($valiedRecaptch) {
        if ($_POST) {
			$user_type = $_POST['user_type'];
			
			if((!empty($_POST['vEmailc']) || !empty($_POST['vEmaild'])) && ($THEME_OBJ->isXThemeActive() == 'Yes')) {
                if($user_type=='driver') $_POST['vEmail'] = $_POST['vEmaild'];
                else $_POST['vEmail'] = $_POST['vEmailc'];
            } 
			
			if ($user_type == 'driver') {
				$check = validateformfields();
				if (isset($check['vName']) || isset($check['vLastName']) || isset($check['vPhone']) || isset($check['vCountry']) || isset($check['vPhoneCode']) || isset($check['vCode']) || isset($check['vEmail'])) {
					$_SESSION['postDetail'] = $_REQUEST;
					$_SESSION['pnameErr'] = $check['vFirstName'];
					$_SESSION['plastnameErr'] = $check['vLastName'];
					$_SESSION['pphoneErr'] = $check['vPhone'];	
					$_SESSION['pemailErr'] = $check['vEmail'];	
					$_SESSION['pcountryErr'] = $check['vCountry'];
					$_SESSION['pphonecodeErr'] = $check['vCode'];					
					header("Location:" . $tconfig["tsite_url"] . "sign-up.php?type=provider");
					exit;
				}
			} else {
				$check = validateformfields();
				if (isset($check['vName']) || isset($check['vLastName']) || isset($check['vPhone']) || isset($check['vCountry']) || isset($check['vPhoneCode']) || isset($check['vCode']) || isset($check['vEmail'])) {
					$_SESSION['postDetail'] = $_REQUEST;
					$_SESSION['cnameErr'] = $check['vCompany'];
					$_SESSION['cphoneErr'] = $check['vPhone'];
					$_SESSION['cemailErr'] = $check['vEmail'];
					$_SESSION['ccountryErr'] = $check['vCountry'];
					$_SESSION['cphonecodeErr'] = $check['vCode'];					
					header("Location:" . $tconfig["tsite_url"] . "sign-up.php?type=company");
					exit;
				}
			}
            
            
            $eSystem = "";
            
            if ($user_type == 'driver') {
                if($_POST['vEmail'] != ""){
                $table_name = "register_driver";
                $msg = checkDuplicateFront('vEmail', 'register_driver', Array('vEmaild'), $tconfig["tsite_url"] . $url ."error=1&var_msg=Email already Exists", "Email already Exists", "", "");
                }
            } else if ($user_type == 'organization') { /* Use For Organization Module */ 
                $table_name = "company";
                $msg = checkDuplicateFront('vEmail', 'company', Array('vEmailc'), $tconfig["tsite_url"] . $url. "error=1&var_msg=Email already Exists", "Email already Exists", "", "");
            }

            else if ($user_type == 'donation_organization') { /* Use For Organization Module */
                $table_name = "donation";
                $msg = checkDuplicateFront('vEmail', 'donation', Array('vEmailc'), $tconfig["tsite_url"] . $url. "error=1&var_msg=Email already Exists", "Email already Exists", "", "");
            }
            else { /* Use For Organization Module */
                if($_POST['vEmailc'] != ""){
                    $eSystem = "General";
                    $table_name = "company";
                    $msg = checkDuplicateFront('vEmail', 'company', Array('vEmailc'), $tconfig["tsite_url"] . $url ."error=1&var_msg=Email already Exists", "Email already Exists", "", "");
                }
            }

            $checPhoneExist = checkMemberDataInfo($_POST['vPhone'],"",$user_type,$_POST['vCountry'],"",$eSystem);
            //echo "<pre>";
            //print_r($checPhoneExist);exit;
            if($checPhoneExist['status'] == 0){
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . $url."error=1&var_msg=".$langage_lbl['LBL_PHONE_EXIST_MSG']);
                exit;
            } else if($checPhoneExist['status'] == 2){
                header("Location:" . $tconfig["tsite_url"] . $url."error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }

            if(!empty($_POST['vCountry'])){
                $_SESSION['postDetail'] = $_REQUEST;
                $checkCountryExist = checkCountryExistOrNot($_POST['vCountry']);
                if($checkCountryExist == 'No'){
                    header("Location:" . $tconfig["tsite_url"] . $url."error=1&var_msg=".$langage_lbl['LBL_NO_COUNTRY_AVAIL_MSG']);
                    exit;
                }
            }

            if($user_type == 'driver' && !empty($_POST['vCurrencyDriver'])){
                $checkCurrencyExist = checkCurrencyExistOrNot($_POST['vCurrencyDriver']);
                if($checkCurrencyExist == 'No'){
                    header("Location:" . $tconfig["tsite_url"] . $url."error=1&var_msg=".$langage_lbl['LBL_NO_CURRENCY_AVAIL_MSG']);
                    exit;
                }
            }

            if ($user_type == 'driver') {
                $eReftype = "Driver";
                $Data['vRefCode'] = $REFERRAL_OBJ->GenerateReferralCode($eReftype);
                $Data['iRefUserId'] = $_POST['iRefUserId'];
                $Data['eRefType'] = $_POST['eRefType'];
                $Data['dRefDate'] = Date('Y-m-d H:i:s');
            }

            $Data['vName'] = $_POST['vFirstName'];
            $Data['vLastName'] = $_POST['vLastName'];
            $Data['vLang'] = $_SESSION['sess_lang'];
            $Data['vPassword'] = encrypt_bycrypt($_REQUEST['vPassword']);
            $Data['vEmail'] = $_POST['vEmail'];
            //$Data['dBirthDate'] = $_POST['vYear'].'-'.$_POST['vMonth'].'-'.$_POST['vDay'];
            $Data['vPhone'] = $_POST['vPhone'];
            $Data['vCaddress'] = $_POST['vCaddress'];
            $Data['vCadress2'] = $_POST['vCadress2'];
            $Data['vCity'] = $_POST['vCity'];
            $Data['vCountry'] = $_POST['vCountry'];
            $Data['vState'] = $_POST['vState'];
            $Data['vZip'] = $_POST['vZip'];
           // $Data['vCode'] = $_POST['vCode'];
            $Data['vBackCheck'] = $_POST['vBackCheck'];
            $Data['vInviteCode'] = $_POST['vInviteCode'];
            $Data['vFathersName'] = $_POST['vFather'];
            $Data['vCompany'] = $_POST['vCompany'];
            $Data['tRegistrationDate'] = Date('Y-m-d H:i:s');

            

            $csql = "SELECT eZeroAllowed,vCountryCode,vPhoneCode,vTimeZone FROM `country` WHERE vCountryCode = '" . $_POST['vCountry'] . "'";
            $CountryData = $obj->MySQLSelect($csql);
            //$eZeroAllowed = $CountryData[0]['eZeroAllowed'];
            
            $Data['vCode'] = $CountryData[0]['vPhoneCode'];
            $Data['vTimeZone'] = $CountryData[0]['vTimeZone'];
            /*if ($eZeroAllowed == 'Yes') {
                $Data['vPhone'] = $Data['vPhone'];
            } else {
                $first = substr($Data['vPhone'], 0, 1);

                if ($first == "0") {
                    $Data['vPhone'] = substr($Data['vPhone'], 1);
                }
            }*/
            if (SITE_TYPE == 'Demo') {
                $Data['eStatus'] = 'Active';
                //Added By HJ On 17-07-2019 For Auto Verify Email and Phone Driver When Register Start
                $Data['eEmailVerified'] = $Data['ePhoneVerified'] = 'Yes';
                //Added By HJ On 17-07-2019 For Auto Verify Email and Phone Driver When Register End
                //Added By HJ On 31-07-2019 For Enable Service At Location Feature Start
                if (strtolower($user_type) == 'driver') {
                    $Data['eEnableServiceAtLocation'] = 'Yes';
                }
                //Added By HJ On 31-07-2019 For Enable Service At Location Feature End
            }

            if ($user_type == 'driver') {
                $Data['eDestinationMode'] = 'No';
                $Data['iDestinationCount'] = 0;
                $Data['tDestinationModifiedDate'] = date('Y-m-d H:i:s');
                $table = 'register_driver';
                $Data['vCurrencyDriver'] = $_POST['vCurrencyDriver'];
                $Data['eGender'] = $_POST['eGender'];
                $user_type = 'driver';
                $Data['iCompanyId'] = 1;
            } else {
                $table = 'company';
                //$Data['eSystem'] = ($user_type == 'organization') ? 'Organization' : 'General';  /* Use For Company & Organization */
                if($user_type=='organization') $Data['eSystem'] = 'Organization';
                else if($user_type=='store') $Data['eSystem'] = 'DeliverAll';
                else $Data['eSystem'] = 'General'; 
                //$user_type='company';
                $Data['vVat'] = $_POST['vVat'];
            }

            if($ENABLE_EMAIL_OPTIONAL != "Yes") {
            $checkValid = checkMemberDataInfo($_POST['vEmail'], "", $user_type,$_POST['vCountry'],"",$eSystem);
            } else {
                $checkValid = checkMemberDataInfo($_POST['vPhone'],"",$user_type,$_POST['vCountry'],"",$eSystem);
            }

            if ($checkValid['status'] == 1) {
                $id = $obj->MySQLQueryPerform($table, $Data, 'insert');
                //User Signup log added by Rs start
                if($user_type == 'driver')
                    createUserLog('Driver', 'No', $id, 'Web','WebLogin','SignUp');
                else if($user_type == 'organization')
                    createUserLog('Organization', 'No', $id, 'Web','WebLogin','SignUp');
                else
                    createUserLog('Company', 'No', $id, 'Web','WebLogin','SignUp');
                //User Signup log added by Rs End

                /* Multi Level Referral */
                if ($user_type == 'driver') 
                {
                    if($_POST['iRefUserId'] != "" && $_POST['iRefUserId'] > 0)
                    {
                        $refSql = "SELECT * FROM user_referrer_transaction WHERE iMemberId = ".$_POST['iRefUserId']." AND eUserType = '".$_POST['eRefType']."'";
                        $refData = $obj->MySQLSelect($refSql);
                        
                        if($refData[0]['tReferrerInfo'] == "")
                        {
                            $referrerInfo[] = array('Position of Referrer' => 1, 'iMemberId' => $_POST['iRefUserId'], 'eUserType' => $_POST['eRefType']);
                        }
                        else {
                            $referrerInfo = json_decode($refData[0]['tReferrerInfo'], true);
                            $referrerInfo[] = array('Position of Referrer' => (scount($referrerInfo)+1), 'iMemberId' => $_POST['iRefUserId'], 'eUserType' => $_POST['eRefType']);
                        }
                        $Data_Referrer['tReferrerInfo'] = json_encode($referrerInfo);
                    }

                    $Data_Referrer['iMemberId'] = $id;
                    $Data_Referrer['eUserType'] = 'Driver';
                    $obj->MySQLQueryPerform("user_referrer_transaction", $Data_Referrer, 'insert');

                }
                /* Multi Level Referral End */

                //Added By HJ On 27-07-2019 For Add Money Into Wallet When Register Driver In Demo Mode Start - Discuss With CD and KS Sir
                if ($user_type == 'driver' && SITE_TYPE == 'Demo') {
                    $tDescription = '#LBL_AMOUNT_CREDIT#';
                    $ePaymentStatus = 'Unsettelled';
                    $dDate = Date('Y-m-d H:i:s');
                    $WALLET_OBJ->PerformWalletTransaction($id, "Driver", 500, 'Credit', 0, "Deposit", $tDescription, $ePaymentStatus, $dDate);
                }
                //Added By HJ On 27-07-2019 For Add Money Into Wallet When Register Driver In Demo Mode End - Discuss With CD and KS Sir
                if ($SITE_VERSION == "v5" && $user_type == 'driver') {
                    $set_driver_pref = Insert_Default_Preferences($id);
                }

                // user_wallet table insert data
                //      $eFor = "Referrer";
                //      $tDescription = "Referal amount credit ".$REFERRAL_AMOUNT." into your account";
                //      $dDate = Date('Y-m-d H:i:s');
                //      $ePaymentStatus = "Unsettelled";
                //      $REFERRAL_AMOUNT; 
                //
    //      if($user_type=='driver'){
                //
    //          if($_POST['vRefCode'] != "" && !empty($_POST['vRefCode'])){
                //              $WALLET_OBJ->PerformWalletTransaction($_POST['iRefUserId'],$_POST['eRefType'],$REFERRAL_AMOUNT,'Credit',0,$eFor,$tDescription,$ePaymentStatus,$dDate);
                //          }   
                //      }   

                if (($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && ONLYDELIVERALL == "No") {
                    if ($user_type == 'driver') {
                        $query = "SELECT GROUP_CONCAT(iVehicleTypeId) as countId FROM `vehicle_type` WHERE `eType` = 'UberX'";
                        $result = $obj->MySQLSelect($query);

                        $Drive_vehicle['iDriverId'] = $id;
                        $Drive_vehicle['iCompanyId'] = "1";
                        $Drive_vehicle['iMakeId'] = "3";
                        $Drive_vehicle['iModelId'] = "1";
                        $Drive_vehicle['iYear'] = Date('Y');
                        $Drive_vehicle['vLicencePlate'] = "My Services";
                        $Drive_vehicle['eStatus'] = "Active";
                        $Drive_vehicle['eType'] = "UberX";
                        $Drive_vehicle['eCarX'] = "Yes";
                        $Drive_vehicle['eCarGo'] = "Yes";
                        if (SITE_TYPE == 'Demo') {
                            $Drive_vehicle['vCarType'] = $result[0]['countId'];
                        } else {
                            $Drive_vehicle['vCarType'] = "";
                        }
                        //$Drive_vehicle['vCarType'] = $result[0]['countId'];
                        $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');

                        if ($APP_TYPE == 'UberX') {
                            $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                            $obj->sql_query($sql);
                        }

                        /* if($ALLOW_SERVICE_PROVIDER_AMOUNT == "Yes"){
                          $sql="select iVehicleTypeId,iVehicleCategoryId,eFareType,fFixedFare,fPricePerHour from vehicle_type where 1=1";
                          $data_vehicles = $obj->MySQLSelect($sql);
                          //echo "<pre>";print_r($data_vehicles);exit;

                          if($data_vehicles[$i]['eFareType'] != "Regular")
                          {
                          for($i=0 ; $i < scount($data_vehicles); $i++){
                          $Data_service['iVehicleTypeId'] = $data_vehicles[$i]['iVehicleTypeId'];
                          $Data_service['iDriverVehicleId'] = $iDriver_VehicleId;

                          if($data_vehicles[$i]['eFareType'] == "Fixed"){
                          $Data_service['fAmount'] = $data_vehicles[$i]['fFixedFare'];
                          }
                          else if($data_vehicles[$i]['eFareType'] == "Hourly"){
                          $Data_service['fAmount'] = $data_vehicles[$i]['fPricePerHour'];
                          }
                          $data_service_amount = $obj->MySQLQueryPerform('service_pro_amount',$Data_service,'insert');
                          }
                          }
                          } */
                        if ($APP_TYPE == 'Ride-Delivery-UberX') {
                            if (SITE_TYPE == 'Demo') {
                                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";
                                $result_ride = $obj->MySQLSelect($query);

                                $query1 = "SELECT GROUP_CONCAT(iVehicleTypeId)as countRentalId FROM `vehicle_type` WHERE `eType` = 'Ride'";
                                $resultrental = $obj->MySQLSelect($query1);
                                /*added by sunita*/
                                $getDeliverAll = $obj->MySQLSelect("SELECT iVehicleTypeId FROM `vehicle_type` WHERE eType = 'DeliverAll' ORDER BY iVehicleTypeId ASC LIMIT 0,1");

                                if (isset($getDeliverAll[0]['iVehicleTypeId']) && $getDeliverAll[0]['iVehicleTypeId'] > 0) {
                                    $deliverAll = $getDeliverAll[0]['iVehicleTypeId'];
                                    $result_ride[0]['countId'] .= "," . $deliverAll;
                                }
                                /*added by sunita*/
                                $Drive_vehicle_Ride['iDriverId'] = $id;
                                $Drive_vehicle_Ride['iCompanyId'] = "1";
                                $Drive_vehicle_Ride['iMakeId'] = "1";
                                $Drive_vehicle_Ride['iModelId'] = "50";
                                $Drive_vehicle_Ride['iYear'] = "2014";
                                $Drive_vehicle_Ride['vLicencePlate'] = "CK201";
                                $Drive_vehicle_Ride['eStatus'] = "Active";
                                $Drive_vehicle_Ride['eCarX'] = "Yes";
                                $Drive_vehicle_Ride['eCarGo'] = "Yes";
                                $Drive_vehicle_Ride['eType'] = "Ride";
                                $Drive_vehicle_Ride['vCarType'] = $result_ride[0]['countId'];
                                if (ENABLE_RENTAL_OPTION == 'Yes') {
                                    $Drive_vehicle_Ride['vRentalCarType'] = $resultrental[0]['countRentalId'];
                                }
                                $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_Ride, 'insert');

                                $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                                $obj->sql_query($sql);

                                //$query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')"; //Commented By HJ For Add New Condition In Below Query
                                $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE eType = 'Ride' OR eType = 'Deliver'"; //OR eType = 'DeliverAll'
                                //Added By HJ For Add New Condition (OR eType = 'DeliverAll') In Query Discuss With KS Sir
                                $result_delivery = $obj->MySQLSelect($query);
                                /*added by sunita*/
                                if (isset($getDeliverAll[0]['iVehicleTypeId']) && $getDeliverAll[0]['iVehicleTypeId'] > 0) {
                                    $deliverAll = $getDeliverAll[0]['iVehicleTypeId'];
                                    $result_delivery[0]['countId'] .= "," . $deliverAll;
                                }
                                /*added by sunita*/
                                $Drive_vehicle_Deliver['iDriverId'] = $id;
                                $Drive_vehicle_Deliver['iCompanyId'] = "1";
                                $Drive_vehicle_Deliver['iMakeId'] = "5";
                                $Drive_vehicle_Deliver['iModelId'] = "18";
                                $Drive_vehicle_Deliver['iYear'] = "2014";
                                $Drive_vehicle_Deliver['vLicencePlate'] = "CK201";
                                $Drive_vehicle_Deliver['eStatus'] = "Active";
                                $Drive_vehicle_Deliver['eCarX'] = "Yes";
                                $Drive_vehicle_Deliver['eCarGo'] = "Yes";
                                $Drive_vehicle_Deliver['eType'] = "Delivery";
                                $Drive_vehicle_Deliver['vCarType'] = $result_delivery[0]['countId'];
                                $Drive_vehicle_Deliver['vRentalCarType'] = $result_delivery[0]['countId'];
                                $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle_Deliver, 'insert');
                            }
                        }
                    }
                } else {
                    if (SITE_TYPE == 'Demo') {
                        if ($APP_TYPE == 'Delivery') {
                            $app_type = 'Deliver';
                        } else {
                            $app_type = $APP_TYPE;
                        }
                        if ($app_type == 'Ride-Delivery') {
                            $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type` WHERE (`eType` = 'Ride' OR `eType` = 'Deliver')";

                            $query1 = "SELECT GROUP_CONCAT(iVehicleTypeId)as RentalcountId FROM `vehicle_type` WHERE (`eType` = 'Ride')";
                            $resultReantal = $obj->MySQLSelect($query1);
                        } else {
                            $query = "SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`  WHERE `eType` = '" . $app_type . "'";
                        }
                        $result = $obj->MySQLSelect($query);
                        /*  $query ="SELECT GROUP_CONCAT(iVehicleTypeId)as countId FROM `vehicle_type`  WHERE `eType` = '".$app_type ."'";
                          $result = $obj->MySQLSelect($query); */
                        $Drive_vehicle['iDriverId'] = $id;
                        $Drive_vehicle['iCompanyId'] = "1";
                        $Drive_vehicle['iMakeId'] = "5";
                        $Drive_vehicle['iModelId'] = "18";
                        $Drive_vehicle['iYear'] = "2014";
                        $Drive_vehicle['vLicencePlate'] = "CK201";
                        $Drive_vehicle['eStatus'] = "Active";
                        $Drive_vehicle['eCarX'] = "Yes";
                        $Drive_vehicle['eCarGo'] = "Yes";
                        $Drive_vehicle['eType'] = $app_type;
                        $Drive_vehicle['vCarType'] = $result[0]['countId'];

                        if (($app_type == 'Ride' || $app_type == 'Ride-Delivery') && ENABLE_RENTAL_OPTION == 'Yes') {
                            $Drive_vehicle['vRentalCarType'] = $resultReantal[0]['RentalcountId'];
                        }

                        $iDriver_VehicleId = $obj->MySQLQueryPerform('driver_vehicle', $Drive_vehicle, 'insert');
                        $sql = "UPDATE register_driver set iDriverVehicleId='" . $iDriver_VehicleId . "' WHERE iDriverId='" . $id . "'";
                        $obj->sql_query($sql);
                    }
                }

                if ($id != "") {
                    //Added By HJ On 21-02-2020 For Solved 141 Mantis Bug #3675 Start
                    $_SESSION['postDetail'] = $_REQUEST;
                    $_SESSION['sess_eSystem'] = $Data['eSystem'];
                    //Added By HJ On 21-02-2020 For Solved 141 Mantis Bug #3675 End
                    $_SESSION['sess_iUserId'] = $id;
                    if ($user_type == 'driver') {
                        $_SESSION['sess_iCompanyId'] = 1;
                        $_SESSION["sess_ProfileName"] = $_SESSION["sess_vName"] = $Data['vName'] . ' ' . $Data['vLastName'];
                        $_SESSION["sess_currency"] = $_SESSION["sess_vCurrency"] = $Data['vCurrencyDriver'];
                    } else {
                        $_SESSION['sess_iCompanyId'] = $id;
                        $_SESSION["sess_ProfileName"] = $_SESSION["sess_vName"] = $Data['vCompany'];
                        $_SESSION["eSystem"] = $Data['eSystem'];
                        $_SESSION['postDetail']['user_type'] = "company";
                    }

                    $_SESSION["sess_company"] = $Data['vCompany'];
                    $_SESSION["sess_vEmail"] = $Data['vEmail'];
                    $_SESSION["sess_user"] = $user_type;
                    $_SESSION["sess_new"] = 1;

                    $_SESSION["sess_vImage"] = getProfileImage('', $tconfig["tsite_url"],'','');
                    $maildata['NAME'] = $_SESSION["sess_vName"];
                    $maildata['EMAIL_NAME'] = $_SESSION["sess_vName"];
                    $maildata['EMAILORG'] = $_SESSION["sess_vEmail"];
                    $emailtxt = $langage_lbl['LBL_EMAIL_TEXT'] . " : " . $_SESSION["sess_vEmail"];
                    $maildata['EMAIL'] = !empty($_SESSION["sess_vEmail"]) ? $emailtxt : '';
                    $phonetxt = $langage_lbl['LBL_PHONE'] . " : + " .  $Data['vCode'] . $Data['vPhone'];
                    $maildata['PHONE'] = !empty($Data['vPhone']) ? $phonetxt : '';
                    //$maildata['PASSWORD'] = $langage_lbl['LBL_PASSWORD'].": ". $_REQUEST['vPassword']; //Commented By HJ On 11-01-2019 For Hide Password As Per Discuss With QA BM
                    $maildata['SOCIALNOTES'] = '';
                    //$COMM_MEDIA_OBJ->SendMailToMember("MEMBER_REGISTRATION_USER",$maildata);
                    if ($user_type == 'driver') {
                        $COMM_MEDIA_OBJ->SendMailToMember("DRIVER_REGISTRATION_ADMIN", $maildata);
                        $COMM_MEDIA_OBJ->SendMailToMember("DRIVER_REGISTRATION_USER", $maildata);
                    } else {
                        $COMM_MEDIA_OBJ->SendMailToMember("COMPANY_REGISTRATION_ADMIN", $maildata);
                        $COMM_MEDIA_OBJ->SendMailToMember("COMPANY_REGISTRATION_USER", $maildata);
                    }
                    #header("Location:profile.php?first=yes");
                    //User login log added by Rs start
                    if($user_type == 'driver')

                        createUserLog('Driver', 'Yes', $id, 'Web');
                    else if($user_type == 'organization')

                        createUserLog('Organization', 'Yes', $id, 'Web');
                    else
                        createUserLog('Company', 'Yes', $id, 'Web');
                    //User login log added by Rs End
                    
                    if ($APP_TYPE == 'UberX' && $user_type == 'driver') {
                        header("Location:add_services.php?iDriverId=" . base64_encode(base64_encode($_SESSION['sess_iUserId'])));
                        exit;
                    } else {
                        if($THEME_OBJ->isXThemeActive() == 'Yes') {
                            if($user_type == 'driver') {
                                 header("Location:profile?first=yes");
                                 exit;
                            } else {
                                header("Location:profile?first=yes");
                                 exit;
                            }
                        } else {
                            header("Location:profile.php?first=yes");
                            exit;
                        }
                    }
                }
            } else if($checkValid['status'] == 2) {
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . $url . "error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }
            else
            {
                $_SESSION['postDetail'] = $_REQUEST;
                header("Location:" . $tconfig["tsite_url"] . $url . "error=1&var_msg=".$langage_lbl['LBL_INVALID_MEMBER_USER_COUNTRY_EMAIL_TXT']);
                exit;
            }
        }
    } else {
        $_SESSION['postDetail'] = $_REQUEST;
        header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=".$langage_lbl['LBL_CAPTCHA_MATCH_MSG']);
        exit;
    }
} else {
    $_SESSION['postDetail'] = $_REQUEST;
    header("Location:" . $tconfig["tsite_url"] . $url ."error=1&var_msg=Please check reCAPTCHA box.");
    exit;
}
/* } else {
  $_SESSION['postDetail'] = $_REQUEST;
  header("Location:" . $tconfig["tsite_url"] . "sign-up.php?error=1&var_msg=Captcha did not match.");
  exit;
  } */
?>
