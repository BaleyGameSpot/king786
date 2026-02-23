<?php
	include_once('common.php');
	
	$sql1 = "SELECT iCabBookingId,iCronStage,eAssigned,dBooking_date FROM cab_booking WHERE eStatus='Assign' AND dBooking_date LIKE '%$ToDate%' AND eAutoAssign = 'Yes' AND iCronStage != '3' AND eAssigned='No'";
	$data_bks = $obj->MySQLSelect($sql1);
	// echo "<pre>"; print_r($data_bks); die;
	// echo $CRON_TIME; die;
	for($i=0;$i<scount($data_bks);$i++){
		$FromDate = date('2017-06-06 13:38:36');
		
		$ToDate = $data_bks[$i]['dBooking_date'];
		$datetime1 = strtotime($FromDate);
		$datetime2 = strtotime($ToDate);
		$interval  = abs($datetime2 - $datetime1);
		
		$minutes   = round($interval / 60);
		
		if($data_bks[$i]['iCronStage'] == 0) {
			if($minutes <= 12 && $minutes >= 8) {
				sendRequest($data_bks[$i]['iCabBookingId']);
			}
		}
		
		if($data_bks[$i]['iCronStage'] == 1) {
			if($minutes <= 8 && $minutes >= 4) {
				sendRequest($data_bks[$i]['iCabBookingId']);
			}
		}
		
		if($data_bks[$i]['iCronStage'] == 2) {
			if($minutes <= 4 && $minutes >= 0) {
				sendRequest($data_bks[$i]['iCabBookingId']);
			}
		}
	}
	
	
	function sendRequest($cabId){
		global $obj, $EVENT_MSG_OBJ;
		$sql = "SELECT cb.*, CONCAT(ru.vName,' ', ru.vLastName) as passengerName,ru.vFbId,ru.vImgName,ru.vAvgRating,ru.vPhoneCode,ru.vPhone FROM cab_booking as cb
		LEFT JOIN register_user as ru ON ru.iUserId = cb.iUserId
		WHERE cb.iCabBookingId='".$cabId."'";
		
		$data_booking = $obj->MySQLSelect($sql);
		

		if(scount($data_booking) > 0) {
		
			$deviceTokens_arr_ios = array();
			$registation_ids_new = array();
			
			$vSourceLatitude= $data_booking[0]['vSourceLatitude'];
			$vSourceLongitude= $data_booking[0]['vSourceLongitude'];
			$vDestLatitude= $data_booking[0]['vDestLatitude'];
			$vDestLongitude= $data_booking[0]['vDestLongitude'];
			$eType = $data_booking[0]['eType'];
			$passengerId = $data_booking[0]['iUserId'];
			$passengerName = $data_booking[0]['passengerName'];
			$PPicName = $data_booking[0]['vImgName'];
			$vFbId = $data_booking[0]['vFbId'];
			$vAvgRating = $data_booking[0]['vAvgRating'];
			$vPhone = $data_booking[0]['vPhone'];
			$vPhoneCode = $data_booking[0]['vPhoneCode'];
			$iCronStage = $data_booking[0]['iCronStage'];
			
			$messageArr['Message'] = "CabRequested";
			$messageArr['iBookingId']= $data_booking[0]['iCabBookingId'];
			$messageArr['sourceLatitude'] = strval($vSourceLatitude);
			$messageArr['sourceLongitude'] = strval($vSourceLongitude);
			$messageArr['PassengerId'] = strval($passengerId);
			$messageArr['PName'] = $passengerName;
			$messageArr['PPicName'] = $PPicName;
			$messageArr['PFId'] = $vFbId;
			$messageArr['PRating'] = $vAvgRating;
			$messageArr['PPhone'] = $vPhone;
			$messageArr['PPhoneC'] = $vPhoneCode;
			$messageArr['REQUEST_TYPE'] = $eType;
			$messageArr['PACKAGE_TYPE'] = $eType == "Deliver"?get_value('package_type', 'vName', 'iPackageTypeId',$iPackageTypeId,'','true'):'';
			$messageArr['destLatitude'] = strval($vDestLatitude);
			$messageArr['destLongitude'] = strval($vDestLongitude);
			$messageArr['MsgCode'] = strval(mt_rand(1000, 9999));
			
			$where_cabid = " iCabBookingId = '".$data_booking[0]['iCabBookingId']."'";
			$Data_update['iCronStage']= $iCronStage+1;
			// $id = $obj->MySQLQueryPerform("cab_booking",$Data_update,'update',$where_cabid);
			$message = json_encode($messageArr);
			$msg_encode  = json_encode($messageArr,JSON_UNESCAPED_UNICODE);
			$Data = array();
			$Data = FetchAvailableDrivers($vSourceLatitude,$vSourceLongitude);

			if(scount($Data) > 0){

				$generalDataArr = array();
                foreach ($Data as $item) {
                    $generalDataArr[] = array(
                        'eDeviceType'       => $item['eDeviceType'],
                        'deviceToken'       => $item['iGcmRegId'],
                        'alertMsg'          => $alertMsg,
                        'eAppTerminate'     => $item['eAppTerminate'],
                        'eDebugMode'     	=> $item['eDebugMode'],
                        'message'           => $messageArr,
                        'channelName'       => "CAB_REQUEST_DRIVER_" . $item['iDriverId'],
                        'addRequestSentArr' => array(
                            'iUserId'       => $passengerId,
                            'iDriverId'     => $item['iDriverId'],
                            'tMessage'      => $messageArr,
                            'iMsgCode'      => $messageArr['MsgCode'],
                            'vStartLatlong' => "",
                            'vEndLatlong'   => "",
                            'tStartAddress' => "",
                            'tEndAddress'   => ""
                        )
                    );
                }

                $EVENT_MSG_OBJ->send(array('GENERAL_DATA' => $generalDataArr), RN_PROVIDER);  
			}else{
				//Email to admin for Not assigned Driver
				$message = array();
				$message['details'] = '<p>Dear Administrator,</p>
							<p>Driver is not available for the following manual booking in stage '.$iCronStage.'</p>
							<p>Name: '.$passengerName.',</p>
							<p>Contact Number: +'.$vPhoneCode.$vPhone.'</p>';
				$mail = $COMM_MEDIA_OBJ->SendMailToMember('CRON_BOOKING_EMAIL',$message);
				//Email to admin for Not assigned Driver
			}
		}
	}
?>
</body>
</html>