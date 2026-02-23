<?php
include_once('../common.php');
require_once("library/validation.class.php");

if (SITE_TYPE == 'Demo') {
    $returnArr['Action'] = '0';
    $returnArr['message'] = '"Edit Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.';
    echo json_encode($returnArr);
    exit;
}

if (!$userObj->hasPermission('edit-app-home-screen-view')) {
	$returnArr['Action'] = '0';
    $returnArr['message'] = 'You do not have permission to update App Home Screen View.';
    echo json_encode($returnArr);
    exit;
}

$table_name = "app_home_screen_view";

$ViewType = isset($_REQUEST['ViewType']) ? $_REQUEST['ViewType'] : '';
$ServiceType = isset($_REQUEST['ServiceType']) ? $_REQUEST['ServiceType'] : '';

if($ViewType == "TitleView" || in_array($ViewType, ['GridIconView', 'ListView'])) {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$vBtnTxtArr = isset($_REQUEST['vBtnTxtArr']) ? $_REQUEST['vBtnTxtArr'] : '';
	$ServiceId = isset($_REQUEST['ServiceId']) ? $_REQUEST['ServiceId'] : '0';
	$vLangLabel = isset($_REQUEST['vLangLabel']) ? $_REQUEST['vLangLabel'] : '';

	if(in_array($ServiceType, ['GeneralLabel']) || (strtoupper(ONLY_MEDICAL_SERVICE) == "YES" && $ServiceType == "Other") || (strtoupper($APP_TYPE) == 'UBERX' && in_array($ServiceType, ['VideoConsult','Bidding'])) || (strtoupper($APP_TYPE) == 'RIDE-DELIVERY' && in_array($ServiceType, ['Deliver']))) {
		//VideoConsult,'Bidding' commented casue it come from the app_home_screen_tabel
	    foreach ($vTitleArr as $k => $vTitle) {
	    	$vCode = explode('_', $k)[1];
	    	$Data_update_lbl = array();
	    	$Data_update_lbl['vValue'] = $vTitle;

	    	if($ServiceType == "Deliver") {
	    		$where = " vCode = '$vCode' AND vLabel = 'LBL_PARCEL_DELIVERY_HOME_SCREEN_TXT' ";

	    	} elseif ($ServiceType == "VideoConsult") {
	    		$where = " vCode = '$vCode' AND vLabel = 'LBL_VIDEO_CONSULTATION_TXT' ";

	    	} elseif ($ServiceType == "Bidding") {
	    		$where = " vCode = '$vCode' AND vLabel = 'LBL_BIDDING_POST_TASK_TITLE' ";
	    		
	    	} elseif ($ServiceType == "Other" && strtoupper(ONLY_MEDICAL_SERVICE) == "YES") {
	    		$where = " vCode = '$vCode' AND vLabel = 'LBL_MEDICAL_MORE_SERVICES_TITLE' ";
	    		
	    	} elseif ($ServiceType == "GeneralLabel") {
	    		$where = " vCode = '$vCode' AND vLabel = '$vLangLabel' ";
	    	}
	    	
	    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
	    }
	} else {
		$Data_update = array();
		$Data_update['vTitle'] = getJsonFromAnArrWithoutClean($vTitleArr);
		if(!empty($vSubTitleArr)) {
			$Data_update['vSubtitle'] = getJsonFromAnArrWithoutClean($vSubTitleArr);
		}

		if(in_array($ServiceType, ["Ride", "MedicalServices", "Other", "DeliveryServices", "UberX", "NearBy", "BuySellRent", "TrackAnyService", "SearchBar","GeneralBanner","VideoConsult","Bidding","Deliver"])) {
			$where = " eServiceType = '$ServiceType' ";
			if($ServiceType == "SearchBar") {
				$where = " eViewType = '$ServiceType' ";
			}
		} elseif (in_array($ViewType, ['GridIconView', 'ListView'])) {
			if(!empty($vBtnTxtArr)) {
				$Data_update['vBtnTxt'] = getJsonFromAnArrWithoutClean($vBtnTxtArr);
			}
			$where = " eViewType = '$ViewType' ";

		} elseif (in_array($ServiceType, ['DeliverAllNearby', 'DeliverAllTopRated', 'DeliverAllItems'])) {
			$where = " eServiceType = '$ServiceType' AND JSON_UNQUOTE(JSON_EXTRACT(tServiceDetails, '$.iServiceId')) = '$ServiceId' ";
		} else {
			$where = " eViewType = 'TitleView' ";
		}

		$obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);
	}
	

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "TextBannerView") {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
	$vImageOld = isset($_REQUEST['vImageOld']) ? $_REQUEST['vImageOld'] : '';
	$vBtnTxtArr = isset($_REQUEST['vBtnTxtArr']) ? $_REQUEST['vBtnTxtArr'] : '';
	$vTxtTitleColor = isset($_REQUEST['vTxtTitleColor']) ? $_REQUEST['vTxtTitleColor'] : '';
	$vTxtSubTitleColor = isset($_REQUEST['vTxtSubTitleColor']) ? $_REQUEST['vTxtSubTitleColor'] : '';
	$vBgColor = isset($_REQUEST['vBgColor']) ? $_REQUEST['vBgColor'] : '';
	$ServiceTypeOther = isset($_REQUEST['ServiceTypeOther']) ? $_REQUEST['ServiceTypeOther'] : '';
	$iServiceId = isset($_REQUEST['iServiceId']) ? $_REQUEST['iServiceId'] : '';

	$db_data_service = $obj->MySQLSelect("SELECT tLayoutDetails FROM $table_name WHERE eServiceType = '$ServiceType' ");
	$tLayoutDetails = json_decode($db_data_service[0]['tLayoutDetails'], true);

	if(!empty($vTxtTitleColor)) {
		$tLayoutDetails['vTxtTitleColor'] = $vTxtTitleColor;
	}
	if(!empty($vTxtSubTitleColor)) {
		$tLayoutDetails['vTxtSubTitleColor'] = $vTxtSubTitleColor;
	}
	if(!empty($vBgColor)) {
		$tLayoutDetails['vBgColor'] = $vBgColor;
	}

	$tLayoutDetails = json_encode($tLayoutDetails);

	$Data_update = array();

	if(in_array($ServiceType, ['Ride', 'Deliver', 'DeliverAll', 'RentEstate', 'RentCars', 'RentItem']) && empty($ServiceTypeOther)) {
		$table_name = $master_service_category_tbl;

		$Data_update['vCategoryName'] = $vTitleArr;
		if(!empty($vSubTitleArr)) {
			$Data_update['vCategoryDesc'] = $vSubTitleArr;
		}

		if(in_array($ServiceType, ['RentEstate', 'RentCars', 'RentItem'])) {
			$Data_update['vTextColor'] = $vTxtTitleColor;
			$Data_update['vBgColor'] = $vBgColor;
		}

		if($image_name != ""){

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

	        if($error){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
	        } else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
	            if(!is_dir($Photo_Gallery_folder)){
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }  
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '',$tconfig["tsite_upload_image_file_extensions"]);
	            $vImage = $img[0];

	            if(!empty($vImageOld) && file_exists($Photo_Gallery_folder . $vImageOld) && SITE_TYPE != 'Demo') {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }

	            $Data_update['vIconImage1'] = $vImage;
	        }
	    }

	    $where = " eType = '$ServiceType' ";
	    $obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	} else if ($ServiceType == 'RideShareInfo') {

		$vPublishTitleArr = isset($_REQUEST['vPublishTitleArr']) ? $_REQUEST['vPublishTitleArr'] : '';
		$vBookTitleArr = isset($_REQUEST['vBookTitleArr']) ? $_REQUEST['vBookTitleArr'] : '';
		$vMyRideTitleArr = isset($_REQUEST['vMyRideTitleArr']) ? $_REQUEST['vMyRideTitleArr'] : '';

		$vPublishTitleArr = json_decode(stripslashes($vPublishTitleArr));
		foreach ($vPublishTitleArr as $k => $vTitle) {
	    	$vCode = explode('_', $k)[1];
	    	$Data_update_lbl_publish = array();
	    	$Data_update_lbl_publish['vValue'] = $vTitle;
	    	$where = " vCode = '$vCode' AND vLabel = 'LBL_RIDE_SHARE_PUBLISH_TXT' ";
	    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl_publish, 'update', $where);
	    }

	    $vBookTitleArr = json_decode(stripslashes($vBookTitleArr));
	    foreach ($vBookTitleArr as $k => $vTitle) {
	    	$vCode = explode('_', $k)[1];
	    	$Data_update_lbl_book = array();
	    	$Data_update_lbl_book['vValue'] = $vTitle;
	    	$where = " vCode = '$vCode' AND vLabel = 'LBL_RIDE_SHARE_BOOK_TXT' ";
	    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl_book, 'update', $where);
	    }

	    $vMyRideTitleArr = json_decode(stripslashes($vMyRideTitleArr));
	    foreach ($vMyRideTitleArr as $k => $vTitle) {
	    	$vCode = explode('_', $k)[1];
	    	$Data_update_lbl_myride = array();
	    	$Data_update_lbl_myride['vValue'] = $vTitle;
	    	$where = " vCode = '$vCode' AND vLabel = 'LBL_RIDE_SHARE_MY_RIDES_TXT'";
	    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl_myride, 'update', $where);
	    }


		$vImagePublish_name = $vImagePublish = isset($_FILES['vImagePublish']['name']) ? $_FILES['vImagePublish']['name'] : '';
		$vImagePublish_object = isset($_FILES['vImagePublish']['tmp_name']) ? $_FILES['vImagePublish']['tmp_name'] : '';
		$vImageOldPublish = isset($_REQUEST['vImageOldPublish']) ? $_REQUEST['vImageOldPublish'] : '';

		$vImageBook_name = $vImageBook = isset($_FILES['vImageBook']['name']) ? $_FILES['vImageBook']['name'] : '';
		$vImageBook_object = isset($_FILES['vImageBook']['tmp_name']) ? $_FILES['vImageBook']['tmp_name'] : '';
		$vImageOldBook = isset($_REQUEST['vImageOldBook']) ? $_REQUEST['vImageOldBook'] : '';

		$vImageMyRides_name = $vImageMyRides = isset($_FILES['vImageMyRides']['name']) ? $_FILES['vImageMyRides']['name'] : '';
		$vImageMyRides_object = isset($_FILES['vImageMyRides']['tmp_name']) ? $_FILES['vImageMyRides']['tmp_name'] : '';
		$vImageOldMyRides = isset($_REQUEST['vImageOldMyRides']) ? $_REQUEST['vImageOldMyRides'] : '';

		$rideshare_service_details = $obj->MySQLSelect("SELECT tCategoryDetails FROM $master_service_category_tbl WHERE eType = 'RideShare' ");
    	$tCategoryDetails = $rideshare_service_details[0]['tCategoryDetails'];

    	if ($vImagePublish_name != "") {
	        $Data_Update_Category = array();

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImagePublish'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

	        $image_info = getimagesize($_FILES["vImagePublish"]["tmp_name"]);
	        $image_width = $image_info[0];
	        $image_height = $image_info[1];
	        if($error){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
			} else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
	            if (!is_dir($Photo_Gallery_folder)) {
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $vImagePublish_object, $vImagePublish_name, '', $tconfig["tsite_upload_image_file_extensions"]);
	            $vImagePublish = $img[0];
	            if (!empty($vImageOldPublish) && file_exists($Photo_Gallery_folder . $vImageOldPublish)) {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }
	        }
	    } else {
	        $vImagePublish = $vImageOldPublish;
	    }

	    if ($vImageBook_name != "") {
	        $Data_Update_Category = array();

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImageBook'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);


	        $image_info = getimagesize($_FILES["vImageBook"]["tmp_name"]);
	        $image_width = $image_info[0];
	        $image_height = $image_info[1];
	        if($error){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
			}else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
	            if (!is_dir($Photo_Gallery_folder)) {
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $vImageBook_object, $vImageBook_name, '', $tconfig["tsite_upload_image_file_extensions"]);
	            $vImageBook = $img[0];
	            if (!empty($vImageOldBook) && file_exists($Photo_Gallery_folder . $vImageOldBook)) {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }
	        }
	    } else {
	        $vImageBook = $vImageOldBook;
	    }


	    if ($vImageMyRides_name != "") {
	        $Data_Update_Category = array();

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImageBook'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

	        $image_info = getimagesize($_FILES["vImageMyRides"]["tmp_name"]);
	        $image_width = $image_info[0];
	        $image_height = $image_info[1];
	        if($error){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
			} else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
	            if (!is_dir($Photo_Gallery_folder)) {
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $vImageMyRides_object, $vImageMyRides_name, '', $tconfig["tsite_upload_image_file_extensions"]);
	            $vImageMyRides = $img[0];
	            if (!empty($vImageOldMyRides) && file_exists($Photo_Gallery_folder . $vImageOldMyRides)) {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }
	        }
	    } else {
	        $vImageMyRides = $vImageOldMyRides;
	    }

	    if (!empty($tCategoryDetails)) {
	        $tCategoryDetails = json_decode($tCategoryDetails, true);
	    } else {
	        $tCategoryDetails = array();
	    }

        if (!empty($vImagePublish)) {
            $tCategoryDetails['RideSharePublish']['vImage'] = $vImagePublish;
        }


        if (!empty($vImageBook)) {
            $tCategoryDetails['RideShareBook']['vImage'] = $vImageBook;
        }

        if (!empty($vImageMyRides)) {
            $tCategoryDetails['RideShareMyRides']['vImage'] = $vImageMyRides;
        }
	   
	    $Data_Update_Category['tCategoryDetails'] = json_encode($tCategoryDetails, JSON_UNESCAPED_UNICODE);

	    $where = " eType = 'RideShare' ";
	    $obj->MySQLQueryPerform($master_service_category_tbl, $Data_Update_Category, 'update', $where);

	} elseif (in_array($ServiceType, ['TrackFamilyMember', 'TrackEmployeeMember'])) {
		$table_name = "track_service_category";

		$Data_update['vCategoryName'] = str_replace('vTitle_', 'vCategoryName_', $vTitleArr);
		$Data_update['vCategoryDesc'] = str_replace('vSubtitle_', 'vCategoryDesc_', $vSubTitleArr);
		$Data_update['vTextColor'] = $vTxtTitleColor;
		$Data_update['vBgColor'] = $vBgColor;

		if($image_name != ""){

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

	        if($error){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
	        } else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
	            if(!is_dir($Photo_Gallery_folder)){
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }  
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '',$tconfig["tsite_upload_image_file_extensions"]);
	            $vImage = $img[0];

	            if(!empty($vImageOld) && file_exists($Photo_Gallery_folder . $vImageOld) && SITE_TYPE != 'Demo') {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }

	            $Data_update['vImage'] = $vImage;
	        }
	    }

	    $where = " eMemberType = '$ServiceTypeOther' ";
	    $obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	} else {
		$Data_update['vTitle'] = $vTitleArr;
		if(!empty($vSubTitleArr)) {
			$Data_update['vSubtitle'] = $vSubTitleArr;
		}
		if(!empty($vBtnTxtArr)) {
			$Data_update['vBtnTxt'] = $vBtnTxtArr;
		}

		$Data_update['tLayoutDetails'] = $tLayoutDetails;

		if($image_name != ""){

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

	        if($error){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
	        } else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
	            if(!is_dir($Photo_Gallery_folder)){
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }  
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '',$tconfig["tsite_upload_image_file_extensions"]);
	            $vImage = $img[0];

	            if(!empty($vImageOld) && file_exists($Photo_Gallery_folder . $vImageOld) && SITE_TYPE != 'Demo') {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }

	            $Data_update['vImage'] = $vImage;
	        }
	    }

	    $where = " eServiceType = '$ServiceType' ";
	    if(in_array($ServiceTypeOther, ['Delivery', 'MultipleDelivery'])) {
	    	$where .= " AND json_extract(tServiceDetails, '$.eCatType') = '$ServiceTypeOther' ";

	    } elseif ($ServiceTypeOther == "DeliverAll") {
	    	$where .= " AND json_extract(tServiceDetails, '$.iServiceId') = '$iServiceId' ";
	    }
	    $obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);
	}	

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "GridView") {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$ServiceTypeOther = isset($_REQUEST['ServiceTypeOther']) ? $_REQUEST['ServiceTypeOther'] : '';

	$Data_update = array();
	if(empty($ServiceTypeOther)) {
		$Data_update['vTitle'] = $vTitleArr;
		if(!empty($vSubTitleArr)) {
			$Data_update['vSubtitle'] = $vSubTitleArr;
		}
	}	

	if($ServiceType == "UberX") {
		if ($_REQUEST['saveOnDemandDisplay'] == "Yes") {
	        $iVehicleCategoryIdArr = $_POST['iVehicleCategoryId'];
	        $iVehicleCategoryIdValArr = $_POST['iVehicleCategoryIdVal'];
	        $iDisplayOrderOnDemandServiceArr = $_POST['iDisplayOrderOnDemandServiceArr'];
	        $vImageOldArr = $_POST['vImageOld'];
	        $db_data_ufx = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = 'UberX' ");
	        $tServiceDetails = array();
	        if (!empty($db_data_ufx[0]['tServiceDetails'])) {
	            $tServiceDetails = json_decode($db_data_ufx[0]['tServiceDetails'], true);
	            foreach ($tServiceDetails as $serviceDetail) {
	                if (!in_array($serviceDetail['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
	                    $tServiceDetails['iVehicleCategoryId_' . $serviceDetail['iVehicleCategoryId']]['eStatus'] = "Inactive";
	                }
	            }
	        }
	        foreach ($iVehicleCategoryIdArr as $iVehicleCategoryId) {

	            $orderKey = array_search($iVehicleCategoryId, $_POST['iVehicleCategoryIdVal']);
	            $iDisplayOrderService = $iDisplayOrderOnDemandServiceArr[$orderKey];
	            $vImage = "";
	            $image_object = $_FILES['vImage']['tmp_name'][$orderKey];
	            $image_name = $_FILES['vImage']['name'][$orderKey];
	            if ($image_name != "") {

					$validobj = new validation();
					$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
					$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

			        if($error){
			            $returnArr['Action'] = '0';
					    $returnArr['message'] = $error;
					    echo json_encode($returnArr);
					    exit;
			        }else {
	                    $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
	                    if (!is_dir($Photo_Gallery_folder)) {
	                        mkdir($Photo_Gallery_folder, 0777);
	                        chmod($Photo_Gallery_folder, 0777);
	                    }
	                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
	                    $vImage = $img[0];
	                    if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey]) && SITE_TYPE != 'Demo') {
	                        // unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
	                    }
	                }
	            }
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['iVehicleCategoryId'] = $iVehicleCategoryId;
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Active';
	            if (!empty($vImage)) {
	                $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImage;
	            } else {
	                $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImageOldArr[$orderKey];
	            }
	        }

	        $Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);
	    }

	} elseif ($ServiceType == "NearBy") {

		if ($_POST['saveNearbyServices'] == "Yes") {
            $iCategoryIdArr = $_POST['iCategoryId'];
            $iCategoryIdValArr = $_POST['iCategoryIdVal'];
            $iDisplayOrderNearbyArr = $_POST['iDisplayOrderNearbyArr'];
            $vImageOldArr = $_POST['vImageOld'];
            $db_data_nearby = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = 'NearBy' ");
            $tServiceDetails = array();
            if (!empty($db_data_nearby[0]['tServiceDetails'])) {
                $tServiceDetails = json_decode($db_data_nearby[0]['tServiceDetails'], true);
                foreach ($tServiceDetails as $serviceDetail) {
                    if (!in_array($serviceDetail['iCategoryId'], $iCategoryIdArr)) {
                        $tServiceDetails['iCategoryId_' . $serviceDetail['iCategoryId']]['eStatus'] = "Inactive";
                    }
                }
            }
            foreach ($iCategoryIdArr as $iCategoryId) {
                $orderKey = array_search($iCategoryId, $_POST['iCategoryIdVal']);
                $iDisplayOrderService = $iDisplayOrderNearbyArr[$orderKey];
                $vImage = "";
                $image_object = $_FILES['vImage']['tmp_name'][$orderKey];
                $image_name = $_FILES['vImage']['name'][$orderKey];
                if ($image_name != "") {

					$validobj = new validation();
					$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
					$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

			        if($error){
			            $returnArr['Action'] = '0';
					    $returnArr['message'] = $error;
					    echo json_encode($returnArr);
					    exit;
			        } else {
                        $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
                        if (!is_dir($Photo_Gallery_folder)) {
                            mkdir($Photo_Gallery_folder, 0777);
                            chmod($Photo_Gallery_folder, 0777);
                        }
                        $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
                        $vImage = $img[0];
                        if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey])) {
                            unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
                        }
                    }
                }
                $tServiceDetails['iCategoryId_' . $iCategoryId]['iCategoryId'] = $iCategoryId;
                $tServiceDetails['iCategoryId_' . $iCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
                $tServiceDetails['iCategoryId_' . $iCategoryId]['eStatus'] = 'Active';
                if (!empty($vImage)) {
                    $tServiceDetails['iCategoryId_' . $iCategoryId]['vImage'] = $vImage;
                } else {
                    $tServiceDetails['iCategoryId_' . $iCategoryId]['vImage'] = $vImageOldArr[$orderKey];
                }
            }

            $Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);
        }

	} elseif ($ServiceType == "Ride") {
		if ($_POST['saveRideServiceDisplay'] == "Yes") {
	        $iVehicleCategoryIdArr = $_POST['iVehicleCategoryId'];
	        $iVehicleCategoryIdValArr = $_POST['iVehicleCategoryIdVal'];
	        $iDisplayOrderRideArr = $_POST['iDisplayOrderRideArr'];
	        $vImageOldArr = $_POST['vImageOld'];
	        $db_data_ride = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = 'Ride' ");
	        $tServiceDetails = array();
	        if (!empty($db_data_ride[0]['tServiceDetails'])) {
	            $tServiceDetails = json_decode($db_data_ride[0]['tServiceDetails'], true);
	            foreach ($tServiceDetails as $serviceDetail) {
	                if (!in_array($serviceDetail['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
	                    $tServiceDetails['iVehicleCategoryId_' . $serviceDetail['iVehicleCategoryId']]['eStatus'] = "Inactive";
	                }
	            }
	        }
	        foreach ($iVehicleCategoryIdArr as $iVehicleCategoryId) {
	            $orderKey = array_search($iVehicleCategoryId, $_POST['iVehicleCategoryIdVal']);
	            $iDisplayOrderService = $iDisplayOrderRideArr[$orderKey];
	            $vImage = "";
	            $image_object = $_FILES['vImage']['tmp_name'][$orderKey];
	            $image_name = $_FILES['vImage']['name'][$orderKey];
	            if ($image_name != "") {

					$validobj = new validation();
					$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
					$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

			        if($error){
			            $returnArr['Action'] = '0';
					    $returnArr['message'] = $error;
					    echo json_encode($returnArr);
					    exit;
			        }else {
	                    $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
	                    if (!is_dir($Photo_Gallery_folder)) {
	                        mkdir($Photo_Gallery_folder, 0777);
	                        chmod($Photo_Gallery_folder, 0777);
	                    }
	                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
	                    $vImage = $img[0];
	                    if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey])) {
	                        // unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
	                    }
	                }
	            }
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['iVehicleCategoryId'] = $iVehicleCategoryId;
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Active';
	            if (!empty($vImage)) {
	                $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImage;
	            } else {
	                $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImageOldArr[$orderKey];
	            }
	        }
	        
	        $Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);
	    }

	} elseif ($ServiceType == "Other" || $ServiceType == "TaxiBid") {
		$image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
	    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
		$vImageOld = isset($_REQUEST['vImageOld']) ? $_REQUEST['vImageOld'] : '';

		$db_data_other = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = '$ServiceType' ");
	    $tServiceDetails = $db_data_other[0]['tServiceDetails'];

		if ($image_name != "") {
		    $Data_Update_Category = array();

			$validobj = new validation();
			$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
			$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

			$image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
    		$image_width = $image_info[0];
    		$image_height = $image_info[1];
	        
	        if($error) {
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $error;
			    echo json_encode($returnArr);
			    exit;
			} else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
	            if(in_array($ServiceTypeOther, ['AddStopInfo', 'TaxiPoolInfo', 'ShareRideInfo', 'TaxiBidInfo'])) {
	            	$Photo_Gallery_folder .= 'AppHomeScreen/';
	            }
	            if (!is_dir($Photo_Gallery_folder)) {
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
	            $vImage = $img[0];
	            if (!empty($vImageOld) && file_exists($Photo_Gallery_folder . $vImageOld)) {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }
	        }
	    } else {
	    	$vImage = $vImageOld;
	    }

	    if (!empty($tServiceDetails)) {
	        $tServiceDetails = json_decode($tServiceDetails, true);
	    } else {
	        $tServiceDetails = array();
	    }

	    if ($ServiceTypeOther == "AddStop") {
	        if (!empty($vImage)) {
	            $tServiceDetails['AddStop']['vImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "TaxiPool") {
	        if (!empty($vImage)) {
	            $tServiceDetails['TaxiPool']['vImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "ShareRide") {
	        if (!empty($vImage)) {
	            $tServiceDetails['ShareRide']['vImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "TaxiBid") {
	        if (!empty($vImage)) {
	            $tServiceDetails['TaxiBid']['vImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "AddStopInfo") {
	        if (!empty($vImage)) {
	            $tServiceDetails['AddStop']['vInfoImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "TaxiPoolInfo") {
	        if (!empty($vImage)) {
	            $tServiceDetails['TaxiPool']['vInfoImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "ShareRideInfo") {
	        if (!empty($vImage)) {
	            $tServiceDetails['ShareRide']['vInfoImage'] = $vImage;
	        }

	    } elseif ($ServiceTypeOther == "TaxiBidInfo") {
	        if (!empty($vImage)) {
	            $tServiceDetails['TaxiBid']['vInfoImage'] = $vImage;
	        }
	    }
	    $Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);

	    if ($ServiceTypeOther == "AddStop") {
	    	$vTitleLBL = 'LBL_TAXI_ADD_A_STOP';
	    	$vSubTitleLBL = 'LBL_TAXI_ADD_A_STOP_DESC';

	    } elseif ($ServiceTypeOther == "AddStopInfo") {
	    	$vTitleLBL = 'LBL_TAXI_ADD_A_STOP_PAGE_TITLE';
	    	$vSubTitleLBL = 'LBL_TAXI_ADD_A_STOP_PAGE_DESC';

	    } elseif ($ServiceTypeOther == "TaxiPool") {
	    	$vTitleLBL = 'LBL_TAXI_POOL_TITLE';
	    	$vSubTitleLBL = 'LBL_TAXI_POOL_DESC';

	    } elseif ($ServiceTypeOther == "TaxiPoolInfo") {
	    	$vTitleLBL = 'LBL_TAXI_POOL_PAGE_TITLE';
	    	$vSubTitleLBL = 'LBL_TAXI_POOL_PAGE_DESC';

	    } elseif ($ServiceTypeOther == "ShareRide") {
	    	$vTitleLBL = 'LBL_SHARE_YOUR_RIDE_TITLE';
	    	$vSubTitleLBL = 'LBL_SHARE_YOUR_RIDE_DESC';

	    } elseif ($ServiceTypeOther == "ShareRideInfo") {
	    	$vTitleLBL = 'LBL_SHARE_YOUR_RIDE_PAGE_TITLE';
	    	$vSubTitleLBL = 'LBL_SHARE_YOUR_RIDE_PAGE_DESC';

	    } elseif ($ServiceTypeOther == "TaxiBid") {
	    	$vTitleLBL = 'LBL_TAXI_BID_TITLE';
	    	$vSubTitleLBL = 'LBL_TAXI_BID_DESC';

	    } elseif ($ServiceTypeOther == "TaxiBidInfo") {
	    	$vTitleLBL = 'LBL_TAXI_BID_PAGE_TITLE';
	    	$vSubTitleLBL = 'LBL_TAXI_BID_PAGE_DESC';
	    }


	    foreach ($vTitleArr as $k => $vTitle) {
	    	$vCode = explode('_', $k)[1];
	    	$Data_update_lbl = array();
	    	$Data_update_lbl['vValue'] = $vTitle;
	    	$where = " vCode = '$vCode' AND vLabel = '$vTitleLBL' ";
	    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
	    }

	    foreach ($vSubTitleArr as $k => $vSubTitle) {
	    	$vCode = explode('_', $k)[1];
	    	$Data_update_lbl = array();
	    	$Data_update_lbl['vValue'] = $vSubTitle;
	    	$where = " vCode = '$vCode' AND vLabel = '$vSubTitleLBL' ";
	    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
	    }

	} elseif ($ServiceType == "DeliverAll") {
		if ($_REQUEST['saveServiceDisplay'] == "Yes") {
	        $iVehicleCategoryIdArr = $_POST['iVehicleCategoryId'];
	        $iVehicleCategoryIdValArr = $_POST['iVehicleCategoryIdVal'];
	        $iDisplayOrderServiceArr = $_POST['iDisplayOrderServiceArr'];
	        $db_data_ufx = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = 'DeliverAll' ");
	        $tServiceDetails = array();
	        if (!empty($db_data_ufx[0]['tServiceDetails'])) {
	            $tServiceDetails = json_decode($db_data_ufx[0]['tServiceDetails'], true);
	            foreach ($tServiceDetails as $serviceDetail) {
	                if (!in_array($serviceDetail['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
	                    $tServiceDetails['iVehicleCategoryId_' . $serviceDetail['iVehicleCategoryId']]['eStatus'] = "Inactive";
	                }
	            }
	        }

	        foreach ($iVehicleCategoryIdValArr as $iVehicleCategoryId) {
	            $orderKey = array_search($iVehicleCategoryId, $_POST['iVehicleCategoryIdVal']);
	            $iDisplayOrderService = $iDisplayOrderServiceArr[$orderKey];
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['iVehicleCategoryId'] = $iVehicleCategoryId;
	            $tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
	            if(in_array($iVehicleCategoryId, $iVehicleCategoryIdArr)) {
	            	$tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Active';
	            } else {
	            	$tServiceDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Inactive';
	            }
	        }

	        $Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);

	        $Data_update_alt = array();
	        $Data_update_alt['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);

	        $where_alt = " eViewType = 'ListView' ";
			$obj->MySQLQueryPerform($table_name, $Data_update_alt, "update", $where_alt);

	    } elseif ($_REQUEST['saveDeliverAllServiceDisplay'] == "Yes") {
	        $iServiceIdValValArr = $_POST['iServiceIdVal'];
	        $db_data_delvAll = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = 'DeliverAll' ");
	        $tServiceDetails = json_decode($db_data_delvAll[0]['tServiceDetails'], true);

	        foreach ($iServiceIdValValArr as $iServiceIdVal) {
                $orderKey = array_search($iServiceIdVal, $_POST['iServiceIdVal']);

                $vImage = "";
                $image_object = $_FILES['vImage']['tmp_name'][$orderKey];
                $image_name = $_FILES['vImage']['name'][$orderKey];

                if ($image_name != "") {

					$validobj = new validation();
					$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
					$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

			        if($error){
			            $returnArr['Action'] = '0';
					    $returnArr['message'] = $error;
					    echo json_encode($returnArr);
					    exit;
			        } else {
                        $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
                        if (!is_dir($Photo_Gallery_folder)) {
                            mkdir($Photo_Gallery_folder, 0777);
                            chmod($Photo_Gallery_folder, 0777);
                        }
                        $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]);
                        $vImage = $img[0];

                        if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey])) {
                            unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
                        }
                    }
                }

                if (!empty($vImage)) {
                    $tServiceDetails['iServiceId_' . $iServiceIdVal] = $vImage;
                }
            }
            $Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);
	    }
	}

	$where = " eServiceType = '$ServiceType' ";
	$obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	$oCache->flushData();

	if ($ServiceTypeOther == "TaxiBidInfo") {
    	updateSystemData();
    }

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "TextBannerGridView") {

	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
	$vImageOld = isset($_REQUEST['vImageOld']) ? $_REQUEST['vImageOld'] : '';
	$ServiceTypeMS = isset($_REQUEST['ServiceTypeMS']) ? $_REQUEST['ServiceTypeMS'] : '';
	$vTxtTitleColor = isset($_REQUEST['vTxtTitleColor']) ? $_REQUEST['vTxtTitleColor'] : '';
	$vBgColor = isset($_REQUEST['vBgColor']) ? $_REQUEST['vBgColor'] : '';

	$medical_service_details = $obj->MySQLSelect("SELECT tCategoryDetails FROM $master_service_category_tbl WHERE eType = 'MedicalServices' ");
	$tCategoryDetails = $medical_service_details[0]['tCategoryDetails'];

	if ($image_name != "") {
	    $Data_Update_Category = array();

        $filecheck = basename($_FILES['vImage']['name']);
        $fileextarr = explode(".", $filecheck);
        $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        $flag_error = 0;
        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
            $flag_error = 1;
            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
        }
        $image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
        $image_width = $image_info[0];
        $image_height = $image_info[1];
        if ($flag_error == 1) {
            $returnArr['Action'] = '0';
		    $returnArr['message'] = $var_msg;
		    echo json_encode($returnArr);
		    exit;
        } else {
            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"];
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
            $vImage = $img[0];
            if (!empty($vImageOld) && file_exists($Photo_Gallery_folder . $vImageOld)) {
                // unlink($Photo_Gallery_folder . $vImageOld);
            }
        }
    } else {
    	$vImage = $vImageOld;
    }

    if (!empty($tCategoryDetails)) {
        $tCategoryDetails = json_decode($tCategoryDetails, true);
    } else {
        $tCategoryDetails = array();
    }

    if ($ServiceTypeMS == "BookService") {
        if (!empty($vImage)) {
            $tCategoryDetails['BookService']['vImage'] = $vImage;
        }
        $tCategoryDetails['BookService']['vTextColor'] = $vTxtTitleColor;
        $tCategoryDetails['BookService']['vBgColor'] = $vBgColor;

    } elseif ($ServiceTypeMS == "VideoConsult") {
        if (!empty($vImage)) {
            $tCategoryDetails['VideoConsult']['vImage'] = $vImage;
        }
        $tCategoryDetails['VideoConsult']['vTextColor'] = $vTxtTitleColor;
        $tCategoryDetails['VideoConsult']['vBgColor'] = $vBgColor;
    } else {
        if (!empty($vImage)) {
            $tCategoryDetails['MoreService']['vImage'] = $vImage;
        }
        $tCategoryDetails['MoreService']['vTextColor'] = $vTxtTitleColor;
        $tCategoryDetails['MoreService']['vBgColor'] = $vBgColor;
    }
    $Data_Update_Category['tCategoryDetails'] = json_encode($tCategoryDetails, JSON_UNESCAPED_UNICODE);

    $where = " eType = 'MedicalServices' ";
	$obj->MySQLQueryPerform($master_service_category_tbl, $Data_Update_Category, 'update', $where);

    if ($ServiceTypeMS == "BookService") {
    	$vTitleLBL = 'LBL_ON_DEMAND_MEDICAL_SERVICES_TITLE';
    	$vSubTitleLBL = 'LBL_ON_DEMAND_MEDICAL_SERVICES_DESC';
    } elseif ($ServiceTypeMS == "VideoConsult") {
    	$vTitleLBL = 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_TITLE';
    	$vSubTitleLBL = 'LBL_VIDEO_CONSULT_MEDICAL_SERVICES_DESC';
    } else {
    	$vTitleLBL = 'LBL_MEDICAL_MORE_SERVICES_TITLE';
    	$vSubTitleLBL = 'LBL_MEDICAL_MORE_SERVICES_DESC';
    }

    //$vTitleArr = json_decode(stripslashes($vTitleArr), true);

    foreach ($vTitleArr as $k => $vTitle) {
    	$vCode = explode('_', $k)[1];
    	$Data_update_lbl = array();
    	$Data_update_lbl['vValue'] = $vTitle;
    	$where = " vCode = '$vCode' AND vLabel = '$vTitleLBL' ";
    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
    }

    //$vSubTitleArr = json_decode(stripslashes($vSubTitleArr), true);
    foreach ($vSubTitleArr as $k => $vSubTitle) {
    	$vCode = explode('_', $k)[1];
    	$Data_update_lbl = array();
    	$Data_update_lbl['vValue'] = $vSubTitle;
    	$where = " vCode = '$vCode' AND vLabel = '$vSubTitleLBL' ";
    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
    }

	
	$db_data_ms = $obj->MySQLSelect("SELECT tServiceDetails FROM $table_name WHERE eServiceType = 'MedicalServices' ");
    $tServiceDetails = array();
    if (!empty($db_data_ms[0]['tServiceDetails'])) {
        $tServiceDetails = json_decode($db_data_ms[0]['tServiceDetails'], true);
    }

    $Data_update = array();
	if ($_POST['saveBookServiceMS'] == "Yes") {
        $iVehicleCategoryIdArr = $_POST['iVehicleCategoryIdBS'];
        $iVehicleCategoryIdValArr = $_POST['iVehicleCategoryIdValBS'];
        $iDisplayOrderBookServiceArr = $_POST['iDisplayOrderBookServiceMSArr'];
        $vImageOldArr = $_POST['vImageOldBS'];
        if (isset($tServiceDetails['BookService'])) {
            foreach ($tServiceDetails['BookService'] as $serviceDetail) {
                if (!in_array($serviceDetail['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
                    $tServiceDetails['BookService']['iVehicleCategoryId_' . $serviceDetail['iVehicleCategoryId']]['eStatus'] = "Inactive";
                }
            }
        }
        foreach ($iVehicleCategoryIdArr as $iVehicleCategoryId) {
            $orderKey = array_search($iVehicleCategoryId, $_POST['iVehicleCategoryIdValBS']);
            $iDisplayOrderService = $iDisplayOrderBookServiceArr[$orderKey];
            $vImage = "";
            $image_object = $_FILES['vImageBS']['tmp_name'][$orderKey];
            $image_name = $_FILES['vImageBS']['name'][$orderKey];
            if ($image_name != "") {
                $filecheck = basename($_FILES['vImageBS']['name'][$orderKey]);
                $fileextarr = explode(".", $filecheck);
                $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                $flag_error = 0;
                if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $flag_error = 1;
                    $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
                }
                if ($flag_error == 1) {
                    $returnArr['Action'] = '0';
				    $returnArr['message'] = $var_msg;
				    echo json_encode($returnArr);
				    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                        chmod($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
                    $vImage = $img[0];
                    if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey])) {
                        unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
                    }
                }
            }
            $tServiceDetails['BookService']['iVehicleCategoryId_' . $iVehicleCategoryId]['iVehicleCategoryId'] = $iVehicleCategoryId;
            $tServiceDetails['BookService']['iVehicleCategoryId_' . $iVehicleCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
            $tServiceDetails['BookService']['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Active';
            if (!empty($vImage)) {
                $tServiceDetails['BookService']['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImage;
            } else {
                $tServiceDetails['BookService']['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImageOldArr[$orderKey];
            }
        }
    }

    if ($_POST['saveVideoConsultMS'] == "Yes") {
        $iVehicleCategoryIdArr = $_POST['iVehicleCategoryIdVC'];
        $iVehicleCategoryIdValArr = $_POST['iVehicleCategoryIdValVC'];
        $iDisplayOrderVideoConsultArr = $_POST['iDisplayOrderVideoConsultMSArr'];
        $vImageOldArr = $_POST['vImageOldVC'];
        if (isset($tServiceDetails['VideoConsult'])) {
            foreach ($tServiceDetails['VideoConsult'] as $serviceDetail) {
                if (!in_array($serviceDetail['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
                    $tServiceDetails['VideoConsult']['iVehicleCategoryId_' . $serviceDetail['iVehicleCategoryId']]['eStatus'] = "Inactive";
                }
            }
        }
        foreach ($iVehicleCategoryIdArr as $iVehicleCategoryId) {
            $orderKey = array_search($iVehicleCategoryId, $_POST['iVehicleCategoryIdValVC']);
            $iDisplayOrderService = $iDisplayOrderVideoConsultArr[$orderKey];
            $vImage = "";
            $image_object = $_FILES['vImageVC']['tmp_name'][$orderKey];
            $image_name = $_FILES['vImageVC']['name'][$orderKey];
            if ($image_name != "") {
                $filecheck = basename($_FILES['vImageVC']['name'][$orderKey]);
                $fileextarr = explode(".", $filecheck);
                $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                $flag_error = 0;
                if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $flag_error = 1;
                    $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
                }
                if ($flag_error == 1) {
                    $returnArr['Action'] = '0';
				    $returnArr['message'] = $var_msg;
				    echo json_encode($returnArr);
				    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                        chmod($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
                    $vImage = $img[0];
                    if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey])) {
                        unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
                    }
                }
            }
            $tServiceDetails['VideoConsult']['iVehicleCategoryId_' . $iVehicleCategoryId]['iVehicleCategoryId'] = $iVehicleCategoryId;
            $tServiceDetails['VideoConsult']['iVehicleCategoryId_' . $iVehicleCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
            $tServiceDetails['VideoConsult']['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Active';
            if (!empty($vImage)) {
                $tServiceDetails['VideoConsult']['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImage;
            } else {
                $tServiceDetails['VideoConsult']['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImageOldArr[$orderKey];
            }
        }
    }

    if ($_POST['saveMoreServiceMS'] == "Yes") {
        $iVehicleCategoryIdArr = $_POST['iVehicleCategoryIdMS'];
        $iVehicleCategoryIdValArr = $_POST['iVehicleCategoryIdValMS'];
        $iDisplayOrderMoreServiceArr = $_POST['iDisplayOrderMoreServiceMSArr'];
        $vImageOldArr = $_POST['vImageOldMS'];
        if (isset($tServiceDetails['MoreService'])) {
            foreach ($tServiceDetails['MoreService'] as $serviceDetail) {
                if (!in_array($serviceDetail['iVehicleCategoryId'], $iVehicleCategoryIdArr)) {
                    $tServiceDetails['MoreService']['iVehicleCategoryId_' . $serviceDetail['iVehicleCategoryId']]['eStatus'] = "Inactive";
                }
            }
        }
        foreach ($iVehicleCategoryIdArr as $iVehicleCategoryId) {
            $orderKey = array_search($iVehicleCategoryId, $_POST['iVehicleCategoryIdValMS']);
            $iDisplayOrderService = $iDisplayOrderMoreServiceArr[$orderKey];
            $vImage = "";
            $image_object = $_FILES['vImageMS']['tmp_name'][$orderKey];
            $image_name = $_FILES['vImageMS']['name'][$orderKey];
            if ($image_name != "") {
                $filecheck = basename($_FILES['vImageMS']['name'][$orderKey]);
                $fileextarr = explode(".", $filecheck);
                $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                $flag_error = 0;
                if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $flag_error = 1;
                    $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
                }
                if ($flag_error == 1) {
                    $returnArr['Action'] = '0';
				    $returnArr['message'] = $var_msg;
				    echo json_encode($returnArr);
				    exit;
                } else {
                    $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                        chmod($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder, $image_object, $image_name, '', 'jpg,png,gif,jpeg,bmp');
                    $vImage = $img[0];
                    if (!empty($vImageOldArr[$orderKey]) && file_exists($Photo_Gallery_folder . $vImageOldArr[$orderKey])) {
                        unlink($Photo_Gallery_folder . $vImageOldArr[$orderKey]);
                    }
                }
            }
            $tServiceDetails['MoreService']['iVehicleCategoryId_' . $iVehicleCategoryId]['iVehicleCategoryId'] = $iVehicleCategoryId;
            $tServiceDetails['MoreService']['iVehicleCategoryId_' . $iVehicleCategoryId]['iDisplayOrder'] = $iDisplayOrderService;
            $tServiceDetails['MoreService']['iVehicleCategoryId_' . $iVehicleCategoryId]['eStatus'] = 'Active';
            if (!empty($vImage)) {
                $tServiceDetails['MoreService']['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImage;
            } else {
                $tServiceDetails['MoreService']['iVehicleCategoryId_' . $iVehicleCategoryId]['vImage'] = $vImageOldArr[$orderKey];
            }
        }
    }	
	
	$Data_update['tServiceDetails'] = json_encode($tServiceDetails, JSON_UNESCAPED_UNICODE);
	$where = " eServiceType = '$ServiceType' ";
	$obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "NewListingView") {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$vBtnTxtArr = isset($_REQUEST['vBtnTxtArr']) ? $_REQUEST['vBtnTxtArr'] : '';
	
	$Data_update = array();
	$Data_update['vTitle'] = $vTitleArr;
	$Data_update['vBtnTxt'] = $vBtnTxtArr;

	$where = " eViewType = 'NewListingView'";
	$obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} else if ($ViewType == "TextBannerViewTrack") {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vTitleArr = json_decode(stripslashes($_REQUEST['vTitleArr']),true);
	$vImageOldTrackService = $_REQUEST['vImageOldTrackService'];
	$Data_update = array();
	$where = "";
	foreach ($vTitleArr as $Tkey => $Tvalue) {
		$iTrackServiceCategoryId = $Tvalue['iTrackServiceCategoryId'];

		$image_name = $vImageTrackService = isset($_FILES['vImageTrackService']['name'][$Tkey]) ? $_FILES['vImageTrackService']['name'][$Tkey] : '';
    	$image_object = isset($_FILES['vImageTrackService']['tmp_name'][$Tkey]) ? $_FILES['vImageTrackService']['tmp_name'][$Tkey] : '';
		$vImageOld = $vImageOldTrackService[$Tkey];

		if($image_name != ""){
	        $filecheck = basename($image_name);
	        $fileextarr = explode(".", $filecheck);
	        $ext = strtolower($fileextarr[scount($fileextarr)-1]);
	        $flag_error = 0;
	        if($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp"){
	            $flag_error = 1;
	            $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .bmp";
	        }

	        if($flag_error == 1){
	            $returnArr['Action'] = '0';
			    $returnArr['message'] = $var_msg;
			    echo json_encode($returnArr);
			    exit;
	        } else {
	            $Photo_Gallery_folder = $tconfig["tsite_upload_app_home_screen_images_path"] . 'AppHomeScreen/';
	            if(!is_dir($Photo_Gallery_folder)){
	                mkdir($Photo_Gallery_folder, 0777);
	                chmod($Photo_Gallery_folder, 0777);
	            }  
	            $img = $UPLOAD_OBJ->GeneralFileUpload($Photo_Gallery_folder,$image_object,$image_name, '','jpg,png,gif,jpeg,bmp');
	            $vImage = $img[0];

	            if(!empty($vImageOld) && file_exists($Photo_Gallery_folder . $vImageOld) && SITE_TYPE != 'Demo') {
	                // unlink($Photo_Gallery_folder . $vImageOld);
	            }

	            $Data_update['vImage'] = $vImage;
	        }
	    }

		$Data_update['vCategoryName'] = json_encode($Tvalue['vTitleArr']);
		$where = " iTrackServiceCategoryId = '$iTrackServiceCategoryId' ";
		$obj->MySQLQueryPerform('track_service_category', $Data_update, "update", $where);
	}

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "IconTextView") {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
	$vImageOld = isset($_REQUEST['vImageOld']) ? $_REQUEST['vImageOld'] : '';
	$vTxtTitleColor = isset($_REQUEST['vTxtTitleColor']) ? $_REQUEST['vTxtTitleColor'] : '';
	$vTxtSubTitleColor = isset($_REQUEST['vTxtSubTitleColor']) ? $_REQUEST['vTxtSubTitleColor'] : '';
	$vBgColor = isset($_REQUEST['vBgColor']) ? $_REQUEST['vBgColor'] : '';
	$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';

	$db_data_service = $obj->MySQLSelect("SELECT tLayoutDetails FROM $table_name WHERE eServiceType = '$ServiceType' ");
	$tLayoutDetails = json_decode($db_data_service[0]['tLayoutDetails'], true);

	if(!empty($vTxtTitleColor)) {
		$tLayoutDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vTxtTitleColor'] = $vTxtTitleColor;
	}
	if(!empty($vTxtSubTitleColor)) {
		$tLayoutDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vTxtSubTitleColor'] = $vTxtSubTitleColor;
	}
	if(!empty($vBgColor)) {
		$tLayoutDetails['iVehicleCategoryId_' . $iVehicleCategoryId]['vBgColor'] = $vBgColor;
	}

	$tLayoutDetails = json_encode($tLayoutDetails);
	$Data_update = array();
	$Data_update['tLayoutDetails'] = $tLayoutDetails;

	$where = " eServiceType = '$ServiceType' ";
    $obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	$table_name = "vehicle_category";

	$Data_update = array();
	foreach ($vTitleArr as $vKey => $vTitle) {
		$Data_update[$vKey] = $vTitle;
	}

	foreach ($vSubTitleArr as $vKey => $vSubTitle) {
		$Data_update[$vKey] = $vSubTitle;
	}

	if($image_name != "") {
        
		$validobj = new validation();
		$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
		$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

        if($error){
            $returnArr['Action'] = '0';
		    $returnArr['message'] = $error;
		    echo json_encode($returnArr);
		    exit;
        } else {
            $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];
            $Photo_Gallery_folder = $img_path . '/' . $iVehicleCategoryId . '/';
            $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';
            $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }
            if (!is_dir($Photo_Gallery_folder_android)) {
                mkdir($Photo_Gallery_folder_android, 0777);
                chmod($Photo_Gallery_folder_android, 0777);
            }
            if (!is_dir($Photo_Gallery_folder_ios)) {
                mkdir($Photo_Gallery_folder_ios, 0777);
                chmod($Photo_Gallery_folder_ios, 0777);
            }
            $vVehicleType1 = $default_lang;
            $img = $UPLOAD_OBJ->GeneralImageUploadVehicleCategoryAndroid($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_category_size1_android"], $tconfig["tsite_upload_images_vehicle_category_size2_android"], $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_both"], $Photo_Gallery_folder_android, $vVehicleType1, NULL);
            $img1 = $UPLOAD_OBJ->GeneralImageUploadVehicleCategoryIOS($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_ios"], $Photo_Gallery_folder_ios, $vVehicleType1, NULL);
            $img_time = explode("_", $img);
            $filecheck = basename($_FILES['vImage']['name']);
            $fileextarr = explode(".", $filecheck);
            $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
            $time_val = $img_time[0];
            $vImage = "ic_car_" . $vVehicleType1 . "_" . $time_val . "." . $ext;
            
            $Data_update['vLogo2'] = $vImage;
        }
    }

    $where = " iVehicleCategoryId = '$iVehicleCategoryId' ";
    $obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "CardIconTextView") {
	$vTitleArr = isset($_REQUEST['vTitleArr']) ? $_REQUEST['vTitleArr'] : '';
	$vSubTitleArr = isset($_REQUEST['vSubTitleArr']) ? $_REQUEST['vSubTitleArr'] : '';
	$vTxtTitleColor = isset($_REQUEST['vTxtTitleColor']) ? $_REQUEST['vTxtTitleColor'] : '';
	$vTxtSubTitleColor = isset($_REQUEST['vTxtSubTitleColor']) ? $_REQUEST['vTxtSubTitleColor'] : '';
	$vBgColor = isset($_REQUEST['vBgColor']) ? $_REQUEST['vBgColor'] : '';
	$ServiceTypeOther = isset($_REQUEST['ServiceTypeOther']) ? $_REQUEST['ServiceTypeOther'] : '';

	$Data_update = array();

	$image_name = $vImage = isset($_FILES['vImage']['name']) ? $_FILES['vImage']['name'] : '';
    $image_object = isset($_FILES['vImage']['tmp_name']) ? $_FILES['vImage']['tmp_name'] : '';
	$vImageOld = isset($_REQUEST['vImageOld']) ? $_REQUEST['vImageOld'] : '';

	$db_data_other = $obj->MySQLSelect("SELECT tLayoutDetails FROM $table_name WHERE eViewType = '$ViewType' ");
    $tLayoutDetails = json_decode($db_data_other[0]['tLayoutDetails'], true);

	if ($image_name != "") {
	    $Data_Update_Category = array();

		$validobj = new validation();
		$imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
		$error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

		$image_info = getimagesize($_FILES["vImage"]["tmp_name"]);
		$image_width = $image_info[0];
		$image_height = $image_info[1];
        
        if($error) {
            $returnArr['Action'] = '0';
		    $returnArr['message'] = $error;
		    echo json_encode($returnArr);
		    exit;
		} else {
            $Photo_Gallery_folder = $tconfig['tsite_url'] . 'webimages/icons/DefaultImg/';

            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
                chmod($Photo_Gallery_folder, 0777);
            }

            if ($ServiceTypeOther == "Wallet") {
		    	$img_name = getMoreServicesIconName('ic_wallet_topup.png');

		    } elseif ($ServiceTypeOther == "GiftCard") {
		    	$img_name = getMoreServicesIconName('ic_gift_card.png');

		    } elseif ($ServiceTypeOther == "Cart") {
		    	$img_name = getMoreServicesIconName('ic_cart.png');

		    }

            $ext = pathinfo($img_name, PATHINFO_EXTENSION);
	        $image_name = pathinfo($img_name, PATHINFO_FILENAME) . '_' . date("YmdHis") . substr(rand(),0,3) . '.' . $ext;

			//checking if file exsists
			if(file_exists("$Photo_Gallery_folder/$image_name") && SITE_TYPE != "Demo") {
	            unlink("$Photo_Gallery_folder/$image_name");
	        }
			
			if(move_uploaded_file($image_object, "$Photo_Gallery_folder/$image_name")) {
	            //echo "Success";
	        } else {
	            //echo "Failed";
	        }
        }
    } else {
    	$vImage = $vImageOld;
    }

    $tLayoutDetails[$ServiceTypeOther]['vTxtTitleColor'] = $vTxtTitleColor;
    $tLayoutDetails[$ServiceTypeOther]['vTxtSubTitleColor'] = $vTxtSubTitleColor;
    $tLayoutDetails[$ServiceTypeOther]['vBgColor'] = $vBgColor;
    $Data_update['tLayoutDetails'] = json_encode($tLayoutDetails, JSON_UNESCAPED_UNICODE);

    if ($ServiceTypeOther == "Wallet") {
    	$vTitleLBL = 'LBL_WALLET_TITLE_HOME_SCREEN_TXT';
    	$vSubTitleLBL = 'LBL_WALLET_SUBTITLE_HOME_SCREEN_TXT';

    } elseif ($ServiceTypeOther == "GiftCard") {
    	$vTitleLBL = 'LBL_GIFT_CARD_TITLE_HOME_SCREEN_TXT';
    	$vSubTitleLBL = 'LBL_GIFT_CARD_SUBTITLE_HOME_SCREEN_TXT';

    } elseif ($ServiceTypeOther == "Cart") {
    	$vTitleLBL = 'LBL_CART_TITLE_HOME_SCREEN_TXT';
    	$vSubTitleLBL = 'LBL_CART_SUBTITLE_HOME_SCREEN_TXT';

    }


    foreach ($vTitleArr as $k => $vTitle) {
    	$vCode = explode('_', $k)[1];
    	$Data_update_lbl = array();
    	$Data_update_lbl['vValue'] = $vTitle;
    	$where = " vCode = '$vCode' AND vLabel = '$vTitleLBL' ";
    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
    }

    foreach ($vSubTitleArr as $k => $vSubTitle) {
    	$vCode = explode('_', $k)[1];
    	$Data_update_lbl = array();
    	$Data_update_lbl['vValue'] = $vSubTitle;
    	$where = " vCode = '$vCode' AND vLabel = '$vSubTitleLBL' ";
    	$obj->MySQLQueryPerform('language_label', $Data_update_lbl, 'update', $where);
    }

	$where = " eViewType = '$ViewType' ";
	$obj->MySQLQueryPerform($table_name, $Data_update, "update", $where);

	$oCache->flushData();

	if ($ServiceTypeOther == "TaxiBidInfo") {
    	updateSystemData();
    }

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;

} elseif ($ViewType == "ServiceDisplayOrder") {
	$iDisplayOrderArr = $_POST['iDisplayOrderArr'];

	foreach ($iDisplayOrderArr as $key => $iDisplayOrderVal) {
		if($ServiceType == "Bidding") {
			$obj->sql_query("UPDATE bidding_service SET iDisplayOrder = '$iDisplayOrderVal' WHERE iBiddingId = '$key'");	
		} elseif ($ServiceType == "NearBy") {
			$obj->sql_query("UPDATE nearby_category SET iDisplayOrder = '$iDisplayOrderVal' WHERE iNearByCategoryId = '$key'");

		} elseif ($ServiceType == "VideoConsult") {
			$obj->sql_query("UPDATE vehicle_category SET iDisplayOrderVC = '$iDisplayOrderVal' WHERE iVehicleCategoryId = '$key'");	
		} else {
			$obj->sql_query("UPDATE vehicle_category SET iDisplayOrder = '$iDisplayOrderVal' WHERE iVehicleCategoryId = '$key'");	
		}		
	}

	$oCache->flushData();

	$returnArr['Action'] = '1';
	echo json_encode($returnArr);
    exit;
}
?>