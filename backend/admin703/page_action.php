<?php
include_once('../common.php');

require_once(TPATH_CLASS . "Imagecrop.class.php");

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$cubexthemeon = 'No';
if($THEME_OBJ->isXThemeActive() == 'Yes') {
    $cubexthemeon = 'Yes';
}

if($cubexthemeon == 'Yes'){
    if ($id == 1 && !isset($_POST['submit'])){
      header("Location:page_action.php?id=52");
      exit();
    }
}

$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'pages';
$script = 'page';


$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';

// fetch all lang from language_master table
$sql = "SELECT * FROM `language_master` ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = scount($db_master);

// set all variables with either post (when submit) either blank (when insert)
$iPageId = isset($_POST['iPageId']) ? $_POST['iPageId'] : $id;
$vPageName = isset($_REQUEST['vPageName']) ? $_REQUEST['vPageName'] : '';
$vTitle = isset($_REQUEST['vTitle']) ? $_REQUEST['vTitle'] : '';
$tMetaKeyword = isset($_REQUEST['tMetaKeyword']) ? $_REQUEST['tMetaKeyword'] : '';
$tMetaDescription = isset($_REQUEST['tMetaDescription']) ? $_REQUEST['tMetaDescription'] : '';
$vImage = isset($_POST['vImage']) ? $_POST['vImage'] : '';
$vImage1 = isset($_POST['vImage1']) ? $_POST['vImage1'] : '';
$vImage2 = isset($_POST['vImage2']) ? $_POST['vImage2'] : '';
$iOrderBy = isset($_POST['iOrderBy']) ? $_POST['iOrderBy'] : ''; //added by SP for pages orderby,active/inactive functionality
$thumb = new thumbnail();

$vUserImage = isset($_POST['vUserImage']) ? $_POST['vUserImage'] : '';
$vDriverImage = isset($_POST['vDriverImage']) ? $_POST['vDriverImage'] : '';
$vCompanyImage = isset($_POST['vCompanyImage']) ? $_POST['vCompanyImage'] : '';
$vStoreImage = isset($_POST['vStoreImage']) ? $_POST['vStoreImage'] : '';
$vOrgImage = isset($_POST['vOrgImage']) ? $_POST['vOrgImage'] : '';
$vTrackingImage = isset($_POST['vTrackingImage']) ? $_POST['vTrackingImage'] : '';

$pageArray = array('48','50');
$pageidCubexImage = array('49');//'48','49','50'
$pageArrImg = array('48','49','50');
$update_array = [];

if($cubexthemeon == 'Yes' && $iPageId==1){
    $vPageName = isset($_REQUEST['vPageName_1']) ? $_REQUEST['vPageName_1'] : '';
    $vTitle = isset($_REQUEST['vTitle_1']) ? $_REQUEST['vTitle_1'] : '';
    $tMetaKeyword = isset($_REQUEST['tMetaKeyword_1']) ? $_REQUEST['tMetaKeyword_1'] : '';
    $tMetaDescription = isset($_REQUEST['tMetaDescription_1']) ? $_REQUEST['tMetaDescription_1'] : '';
    $iOrderBy = isset($_POST['iOrderBy_1']) ? $_POST['iOrderBy_1'] : '';
}

 if($cubexthemeon == 'Yes' && $iPageId != 53 && !in_array($iPageId,$pageArrImg)) {
    $Photo_Gallery_folder = $tconfig["tsite_upload_apptype_images_panel"] . '/'. $template .'/' ;
    $images = $tconfig['tsite_upload_apptype_images'].$template .'/';
 } else if (in_array($iPageId,$pageArrImg)) {
    $Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
    $images = $tconfig['tsite_upload_page_images'];
 }else {
    $Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
    $images = $tconfig['tsite_upload_page_images'];
 }

if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
        $$vPageTitle = isset($_POST[$vPageTitle]) ? $_POST[$vPageTitle] : '';
        $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
        $$tPageDesc = isset($_POST[$tPageDesc]) ? $_POST[$tPageDesc] : '';

        if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {

            $vPageSubTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
            $$vPageSubTitle = isset($_POST[$vPageSubTitle]) ? $_POST[$vPageSubTitle] : '';

            $vUserPageTitle = 'vUserPageTitle_' . $db_master[$i]['vCode'];
            $$vUserPageTitle = isset($_POST[$vUserPageTitle]) ? $_POST[$vUserPageTitle] : '';
            $tUserPageDesc = 'tUserPageDesc_' . $db_master[$i]['vCode'];
            $$tUserPageDesc = isset($_POST[$tUserPageDesc]) ? $_POST[$tUserPageDesc] : '';

            $vProviderPageTitle = 'vProviderPageTitle_' . $db_master[$i]['vCode'];
            $$vProviderPageTitle = isset($_POST[$vProviderPageTitle]) ? $_POST[$vProviderPageTitle] : '';
            $tProviderPageDesc = 'tProviderPageDesc_' . $db_master[$i]['vCode'];
            $$tProviderPageDesc = isset($_POST[$tProviderPageDesc]) ? $_POST[$tProviderPageDesc] : '';

            $vCompanyPageTitle = 'vCompanyPageTitle_' . $db_master[$i]['vCode'];
            $$vCompanyPageTitle = isset($_POST[$vCompanyPageTitle]) ? $_POST[$vCompanyPageTitle] : '';
            $tCompanyPageDesc = 'tCompanyPageDesc_' . $db_master[$i]['vCode'];
            $$tCompanyPageDesc = isset($_POST[$tCompanyPageDesc]) ? $_POST[$tCompanyPageDesc] : '';

            $vRestaurantPageTitle = 'vRestaurantPageTitle_' . $db_master[$i]['vCode'];
            $$vRestaurantPageTitle = isset($_POST[$vRestaurantPageTitle]) ? $_POST[$vRestaurantPageTitle] : '';
            $tRestaurantPageDesc = 'tRestaurantPageDesc_' . $db_master[$i]['vCode'];
            $$tRestaurantPageDesc = isset($_POST[$tRestaurantPageDesc]) ? $_POST[$tRestaurantPageDesc] : '';

            $vOrgPageTitle = 'vOrgPageTitle_' . $db_master[$i]['vCode'];
            $$vOrgPageTitle = isset($_POST[$vOrgPageTitle]) ? $_POST[$vOrgPageTitle] : '';
            $tOrgPageDesc = 'tOrgPageDesc_' . $db_master[$i]['vCode'];
            $$tOrgPageDesc = isset($_POST[$tOrgPageDesc]) ? $_POST[$tOrgPageDesc] : '';

            $vTrackServicePageTitle = 'vTrackServicePageTitle_' . $db_master[$i]['vCode'];
            $$vTrackServicePageTitle = isset($_POST[$vTrackServicePageTitle]) ? $_POST[$vTrackServicePageTitle] : '';
            $tTrackServicePageDesc = 'tTrackServicePageDesc_' . $db_master[$i]['vCode'];
            $$tTrackServicePageDesc = isset($_POST[$tTrackServicePageDesc]) ? $_POST[$tOrgPageDesc] : '';

            $vHotelPageTitle = 'vHotelPageTitle_' . $db_master[$i]['vCode'];
            $$vHotelPageTitle = isset($_POST[$vHotelPageTitle]) ? $_POST[$vHotelPageTitle] : '';
            $tHotelPageDesc = 'tHotelPageDesc_' . $db_master[$i]['vCode'];
            $$tHotelPageDesc = isset($_POST[$tHotelPageDesc]) ? $_POST[$tHotelPageDesc] : '';

            $vPageTitle = $vPageTitle;
            $$vPageTitle = getJsonFromAnArrWithoutClean(array("user_pages"=>$$vUserPageTitle,"provider_pages"=>$$vProviderPageTitle,"company_pages"=>$$vCompanyPageTitle,"restaurant_pages"=>$$vRestaurantPageTitle,"org_pages"=>$$vOrgPageTitle,"trackservice_pages"=>$$vTrackServicePageTitle,"hotel_pages"=>$$vHotelPageTitle));

            $tPageDesc = $tPageDesc;
            $$tPageDesc = getJsonFromAnArrWithoutClean(array("user_pages"=>$$tUserPageDesc,"provider_pages"=>$$tProviderPageDesc,"company_pages"=>$$tCompanyPageDesc,"restaurant_pages"=>$$tRestaurantPageDesc,"org_pages"=>$$tOrgPageDesc,"trackservice_pages"=>$$tTrackServicePageDesc,"hotel_pages"=>$$tHotelPageDesc));
        }

        if($cubexthemeon == 'Yes' && $iPageId==52) {
            $vPageSubTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
            $$vPageSubTitle = isset($_POST[$vPageSubTitle]) ? $_POST[$vPageSubTitle] : '';

            $tPageSecDesc = 'tPageSecDesc_' . $db_master[$i]['vCode'];
            $$tPageSecDesc = isset($_POST[$tPageSecDesc]) ? $_POST[$tPageSecDesc] : '';
            $tPageThirdDesc = 'tPageThirdDesc_' . $db_master[$i]['vCode'];
            $$tPageThirdDesc = isset($_POST[$tPageThirdDesc]) ? $_POST[$tPageThirdDesc] : '';
        }

        if($cubexthemeon == 'Yes' && $iPageId==1){
            $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'].'_1';
            $$vPageTitle = isset($_POST[$vPageTitle]) ? $_POST[$vPageTitle] : '';
            $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'].'_1';
            $$tPageDesc = isset($_POST[$tPageDesc]) ? $_POST[$tPageDesc] : '';
        }
    }
}

if (isset($_POST['submit'])) {
    if ($action == "Add" && !$userObj->hasPermission('create-pages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to create page.';
        header("Location:page.php");
        exit;
    }
    if ($action == "Edit" && !$userObj->hasPermission('edit-pages')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to update page.';
        header("Location:page.php");
        exit;
    }

    if (SITE_TYPE == "Demo") {
        header("Location:page_action.php?id=" . $iPageId . '&success=2');
        exit;
    }
    $vPageSubTitleArr = array();
    if (scount($db_master) > 0) {
        $str = '';
        for ($i = 0; $i < scount($db_master); $i++) {

            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {

                $vPageSubTitleArr["pageSubtitle_".$db_master[$i]['vCode']] = $_REQUEST['vPageSubTitle'][$db_master[$i]['vCode']];

                $vUserPageTitle = 'vUserPageTitle_' . $db_master[$i]['vCode'];
                $$vUserPageTitle = $_REQUEST[$vUserPageTitle];
                $tUserPageDesc = 'tUserPageDesc_' . $db_master[$i]['vCode'];
                $$tUserPageDesc = $_REQUEST[$tUserPageDesc];

                $vProviderPageTitle = 'vProviderPageTitle_' . $db_master[$i]['vCode'];
                $$vProviderPageTitle = $_REQUEST[$vProviderPageTitle];
                $tProviderPageDesc = 'tProviderPageDesc_' . $db_master[$i]['vCode'];
                $$tProviderPageDesc = $_REQUEST[$tProviderPageDesc];

                $vCompanyPageTitle = 'vCompanyPageTitle_' . $db_master[$i]['vCode'];
                $$vCompanyPageTitle = $_REQUEST[$vCompanyPageTitle];
                $tCompanyPageDesc = 'tCompanyPageDesc_' . $db_master[$i]['vCode'];
                $$tCompanyPageDesc = $_REQUEST[$tCompanyPageDesc];

                $vRestaurantPageTitle = 'vRestaurantPageTitle_' . $db_master[$i]['vCode'];
                $$vRestaurantPageTitle = $_REQUEST[$vRestaurantPageTitle];
                $tRestaurantPageDesc = 'tRestaurantPageDesc_' . $db_master[$i]['vCode'];
                $$tRestaurantPageDesc = $_REQUEST[$tRestaurantPageDesc];

                $vOrgPageTitle = 'vOrgPageTitle_' . $db_master[$i]['vCode'];
                $$vOrgPageTitle = $_REQUEST[$vOrgPageTitle];
                $tOrgPageDesc = 'tOrgPageDesc_' . $db_master[$i]['vCode'];
                $$tOrgPageDesc = $_REQUEST[$tOrgPageDesc];

                $vTrackServicePageTitle = 'vTrackServicePageTitle_' . $db_master[$i]['vCode'];
                $$vTrackServicePageTitle = $_REQUEST[$vTrackServicePageTitle];
                $tTrackServicePageDesc = 'tTrackServicePageDesc_' . $db_master[$i]['vCode'];
                $$tTrackServicePageDesc = $_REQUEST[$tTrackServicePageDesc];

                $vHotelPageTitle = 'vHotelPageTitle_' . $db_master[$i]['vCode'];
                $$vHotelPageTitle = $_REQUEST[$vHotelPageTitle];
                $tHotelPageDesc = 'tHotelPageDesc_' . $db_master[$i]['vCode'];
                $$tHotelPageDesc = $_REQUEST[$tHotelPageDesc];

                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                $$vPageTitle = getJsonFromAnArrWithoutClean(array("user_pages"=>$$vUserPageTitle,"provider_pages"=>$$vProviderPageTitle,"company_pages"=>$$vCompanyPageTitle,"restaurant_pages"=>$$vRestaurantPageTitle,"org_pages"=>$$vOrgPageTitle,"trackservice_pages"=>$$vTrackServicePageTitle,"hotel_pages"=>$$vHotelPageTitle));

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = getJsonFromAnArrWithoutClean(array("user_pages"=>$$tUserPageDesc,"provider_pages"=>$$tProviderPageDesc,"company_pages"=>$$tCompanyPageDesc,"restaurant_pages"=>$$tRestaurantPageDesc,"org_pages"=>$$tOrgPageDesc,"trackservice_pages"=>$$tTrackServicePageDesc,"hotel_pages"=>$$tHotelPageDesc));

                $str .= " " . $vPageTitle . " = '" . $$vPageTitle . "', " . $tPageDesc . " = '" . $$tPageDesc . "', ";

                $update_array[$vPageTitle] =  $$vPageTitle;
                $update_array[$tPageDesc] =  $$tPageDesc;

            } else if($cubexthemeon == 'Yes' && $iPageId == 52) {
                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                $$vPageTitle = $_REQUEST[$vPageTitle];

                $vPageSubTitleArr['pageSubtitle_'.$db_master[$i]['vCode']] = $_REQUEST['vPageSubTitle'][$db_master[$i]['vCode']];

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $_REQUEST[$tPageDesc];
                $tPageSecDesc = 'tPageSecDesc_' . $db_master[$i]['vCode'];
                $$tPageSecDesc = $_REQUEST[$tPageSecDesc];
                $tPageThirdDesc = 'tPageThirdDesc_' . $db_master[$i]['vCode'];
                $$tPageThirdDesc = $_REQUEST[$tPageThirdDesc];

                $$tPageDesc = getJsonFromAnArrWithoutClean(array("FirstDesc"=>$$tPageDesc,"SecDesc"=>$$tPageSecDesc,"ThirdDesc"=>$$tPageThirdDesc));

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $str .= " " . $vPageTitle . " = '" . $$vPageTitle . "', " . $tPageDesc . " = '" . $$tPageDesc . "', ";
                $update_array[$vPageTitle] =  $$vPageTitle;
                $update_array[$tPageDesc] =  $$tPageDesc;
            } else if($cubexthemeon == 'Yes' && $iPageId==1){

                $vPageTitlekey = 'vPageTitle_' . $db_master[$i]['vCode'];
                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'].'_1';
                $$vPageTitle = isset($_REQUEST[$vPageTitle]) ? $_REQUEST[$vPageTitle] : '';
                $tPageDesckey = 'tPageDesc_' . $db_master[$i]['vCode'];
                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'].'_1';
                $$tPageDesc = isset($_REQUEST[$tPageDesc]) ? $_REQUEST[$tPageDesc] : '';
                $str .= " " . $vPageTitlekey . " = '" . $$vPageTitle . "', " . $tPageDesckey . " = '" . $$tPageDesc . "', ";
                $update_array[$vPageTitlekey] =  $$vPageTitle;
                $update_array[$tPageDesckey] =  $$tPageDesc;
            } else {

                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];

                $$vPageTitle = $_REQUEST[$vPageTitle];

                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $_REQUEST[$tPageDesc];

                $str .= " " . $vPageTitle . " = '" . $$vPageTitle . "', " . $tPageDesc . " = '" . $$tPageDesc . "', ";

                $update_array[$vPageTitle] =  $$vPageTitle;
                $update_array[$tPageDesc] =  $$tPageDesc;
            }

        }
    }

    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
        $vPageSubTitle = getJsonFromAnArrWithoutClean($vPageSubTitleArr);
        $str .= " pageSubtitle = '". $vPageSubTitle."', ";

        $update_array['pageSubtitle'] =  $vPageSubTitle;
    }
    if($cubexthemeon == 'Yes' && $iPageId==52) {
        $vPageSubTitle = getJsonFromAnArrWithoutClean($vPageSubTitleArr);
        $str .= " pageSubtitle = '". $vPageSubTitle."', ";

        $update_array['pageSubtitle'] =  $vPageSubTitle;

    }

    $image_object = $_FILES['vImage']['tmp_name'];
    $image_name = $_FILES['vImage']['name'];
    $image_name = str_replace(' ', '', $image_name);

    if ($image_name != "") {
        // $filecheck = basename($_FILES['vImage']['name']);
        // $fileextarr = explode(".", $filecheck);
        // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
        // $flag_error = 0;
        // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
        //     $flag_error = 1;
        //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
        // }
        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
        if($error){
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = $error;
            header("location:page.php");
            exit;
        }

        if ($_FILES['vImage']['size'] > 2097152) {
            $flag_error = 1;
            $var_msg = "Image size is too large";
        }
        if ($flag_error == 1) {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = $var_msg;
                header("location:" . $backlink);
            //getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
            exit;
        } else {
            //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

            $vImage = $img[0];
        }
    }

    $image_object1 = $_FILES['vImage1']['tmp_name'];
    $image_name1 = $_FILES['vImage1']['name'];
    $image_name1 = str_replace(' ', '', $image_name1);
    if ($image_name1 != "") {
        // $filecheck1 = basename($_FILES['vImage1']['name']);
        // $fileextarr1 = explode(".", $filecheck1);
        // $ext1 = strtolower($fileextarr1[scount($fileextarr1) - 1]);
        // $flag_error1 = 0;
        // if ($ext1 != "jpg" && $ext1 != "gif" && $ext1 != "png" && $ext1 != "jpeg" && $ext1 != "bmp" && $ext1 != "svg") {
        //     $flag_error1 = 1;
        //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
        // }

        require_once("library/validation.class.php");
        $validobj = new validation();
        $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
        $error = $validobj->validateFileType($_FILES['vImage1'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
        if($error){
            $_SESSION['success'] = 3;
            $_SESSION['var_msg'] = $error;
            header("location:page.php");
            exit;
        }

        if ($_FILES['vImage1']['size'] > 2097152) {
            $flag_error1 = 1;
            $var_msg = "Image size is too large";
        }
        if ($flag_error1 == 1) {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = $var_msg;
                header("location:" . $backlink);
            //getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
            exit;
        } else {
            //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
            if (!is_dir($Photo_Gallery_folder)) {
                mkdir($Photo_Gallery_folder, 0777);
            }
            $img1 = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object1, $image_name1, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload
            $vImage1 = $img1[0];
        }
    }

   // if(!empty($vImage2)) {
        $image_object2 = $_FILES['vImage2']['tmp_name'];
        $image_name2 = $_FILES['vImage2']['name'];
        $image_name2 = str_replace(' ', '', $image_name2);
        if ($image_name2 != "") {
            // $filecheck2 = basename($_FILES['vImage2']['name']);
            // $fileextarr2 = explode(".", $filecheck2);
            // $ext2 = strtolower($fileextarr2[scount($fileextarr2) - 1]);
            // $flag_error2 = 0;
            // if ($ext2 != "jpg" && $ext2 != "gif" && $ext2 != "png" && $ext2 != "jpeg" && $ext2 != "bmp" && $ext2 != "svg") {
            //     $flag_error2= 1;
            //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
            // }

            require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vImage2'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
                if($error){
                    $_SESSION['success'] = 3;
                    $_SESSION['var_msg'] = $error;
                    header("location:page.php");
                    exit;
                }

            if ($_FILES['vImage2']['size'] > 2097152) {
                $flag_error2 = 1;
                $var_msg = "Image size is too large";
            }
            if ($flag_error2 == 1) {
                $_SESSION['success'] = 3;
                $_SESSION['var_msg'] = $var_msg;
                header("location:" . $backlink);
                //getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
                exit;
            } else {
                //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
                if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
                }
                $img2 = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object2, $image_name2, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload
                $vImage2 = $img2[0];
            }
        }

		if($iPageId == 50 || $iPageId == 48){
			$Signimage_object1 = $_FILES['vSignImage1']['tmp_name'];
			$Signimage_name1 = $_FILES['vSignImage1']['name'];
			$Signimage_name1 = str_replace(' ', '', $Signimage_name1);
			if ($Signimage_name1 != "") {
				// $Signfilecheck1 = basename($_FILES['vSignImage1']['name']);
				// $Signfileextarr1 = explode(".", $Signfilecheck1);
				// $ext2 = strtolower($Signfileextarr1[scount($Signfileextarr1) - 1]);
				// $flag_error2 = 0;
				// if ($ext2 != "jpg" && $ext2 != "gif" && $ext2 != "png" && $ext2 != "jpeg" && $ext2 != "bmp" && $ext2 != "svg") {
				// 	$flag_error2= 1;
				// 	$var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
				// }

                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vSignImage1'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
                if($error){
                    $_SESSION['success'] = 3;
                    $_SESSION['var_msg'] = $error;
                    header("location:page.php");
                    exit;
                }

				if ($_FILES['vSignImage1']['size'] > 2097152) {
					$flag_error2 = 1;
					$var_msg = "Image size is too large";
				}
				if ($flag_error2 == 1) {
					$_SESSION['success'] = 3;
					$_SESSION['var_msg'] = $var_msg;
					header("location:" . $backlink);
					exit;
				} else {
					if (!is_dir($Photo_Gallery_folder)) {
						mkdir($Photo_Gallery_folder, 0777);
					}
					// $fileextension = strtolower($Signfileextarr1[scount($Signfileextarr1) - 1]);
					// $firstlane = mt_rand(1111, 9999);
					// $filename = mt_rand(11111, 99999);
					// $secondlane = mt_rand(111, 999);
					// $Signimage_name1=$firstlane."_".$filename."_".$secondlane.".".$fileextension;
					$img3 = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $Signimage_object1, $Signimage_name1, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload
					$vSignImage1 = $img3[0];
				}
			}

			$Signimage_object2 = $_FILES['vSignImage2']['tmp_name'];
			$Signimage_name2 = $_FILES['vSignImage2']['name'];
			$Signimage_name2 = str_replace(' ', '', $Signimage_name2);
			if ($Signimage_name2 != "") {
				// $Signfilecheck2 = basename($_FILES['vSignImage2']['name']);
				// $Signfileextarr2 = explode(".", $Signfilecheck2);
				// $ext2 = strtolower($Signfileextarr2[scount($Signfileextarr2) - 1]);
				// $flag_error2 = 0;
				// if ($ext2 != "jpg" && $ext2 != "gif" && $ext2 != "png" && $ext2 != "jpeg" && $ext2 != "bmp" && $ext2 != "svg") {
				// 	$flag_error2= 1;
				// 	$var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
				// }
                 require_once("library/validation.class.php");
                    $validobj = new validation();
                    $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                    $error = $validobj->validateFileType($_FILES['vSignImage2'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);
                    if($error){
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                        exit;
                    }
				if ($_FILES['vSignImage2']['size'] > 2097152) {
					$flag_error2 = 1;
					$var_msg = "Image size is too large";
				}
				if ($flag_error2 == 1) {
					$_SESSION['success'] = 3;
					$_SESSION['var_msg'] = $var_msg;
					header("location:" . $backlink);
					//getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
					exit;
				} else {
					//$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
					if (!is_dir($Photo_Gallery_folder)) {
						mkdir($Photo_Gallery_folder, 0777);
					}
					// $fileextension = strtolower($Signfileextarr2[scount($Signfileextarr2) - 1]);
					// $firstlane = mt_rand(1111, 9999);
					// $filename = mt_rand(11111, 99999);
					// $secondlane = mt_rand(111, 999);
					// $Signimage_name2=$firstlane."_".$filename."_".$secondlane.".".$fileextension;
					$img4 = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $Signimage_object2, $Signimage_name2, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload
					$vSignImage2 = $img4[0];
				}
			}


            $user_image_object = $_FILES['vUserImage']['tmp_name'];
            $user_image_name = $_FILES['vUserImage']['name'];
            $user_image_name = str_replace(' ', '', $user_image_name);

            if ($user_image_name != "") {
                // $filecheck = basename($_FILES['vUserImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }
                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vUserImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }

                if ($_FILES['vUserImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    exit;
                } else {
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $vUserImage = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $user_image_object, $user_image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vUserImage = $vUserImage[0];
                }
            }


            $driver_image_object = $_FILES['vDriverImage']['tmp_name'];
            $driver_image_name = $_FILES['vDriverImage']['name'];
            $driver_image_name = str_replace(' ', '', $driver_image_name);

            if ($driver_image_name != "") {
                // $filecheck = basename($_FILES['vDriverImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }

                 require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vDriverImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }

                if ($_FILES['vDriverImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    exit;
                } else {
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $vDriverImage = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $driver_image_object, $driver_image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vDriverImage = $vDriverImage[0];
                }
            }


            $company_image_object = $_FILES['vCompanyImage']['tmp_name'];
            $company_image_name = $_FILES['vCompanyImage']['name'];
            $company_image_name = str_replace(' ', '', $company_image_name);

            if ($company_image_name != "") {
                // $filecheck = basename($_FILES['vCompanyImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }

                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vCompanyImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }
                if ($_FILES['vCompanyImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    exit;
                } else {
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $vCompanyImage = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $company_image_object, $company_image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vCompanyImage = $vCompanyImage[0];
                }
            }


            $store_image_object = $_FILES['vStoreImage']['tmp_name'];
            $store_image_name = $_FILES['vStoreImage']['name'];
            $store_image_name = str_replace(' ', '', $store_image_name);

            if ($store_image_name != "") {
                // $filecheck = basename($_FILES['vStoreImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }
                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vStoreImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }
                if ($_FILES['vStoreImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    exit;
                } else {
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $vStoreImage = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $store_image_object, $store_image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vStoreImage = $vStoreImage[0];
                }
            }


            $org_image_object = $_FILES['vOrgImage']['tmp_name'];
            $org_image_name = $_FILES['vOrgImage']['name'];
            $org_image_name = str_replace(' ', '', $org_image_name);

            if ($org_image_name != "") {
                // $filecheck = basename($_FILES['vOrgImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }
                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vOrgImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }
                if ($_FILES['vOrgImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    exit;
                } else {
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $org_image_name = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $org_image_object, $org_image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vOrgImage = $org_image_name[0];
                }
            }


            $tracking_image_object = $_FILES['vTrackingImage']['tmp_name'];
            $tracking_image_name = $_FILES['vTrackingImage']['name'];
            $tracking_image_name = str_replace(' ', '', $tracking_image_name);

            if ($tracking_image_name != "") {
                // $filecheck = basename($_FILES['vTrackingImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }
                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vTrackingImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }
                if ($_FILES['vTrackingImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    exit;
                } else {
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $tracking_image_object, $tracking_image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vTrackingImage = $img[0];
                }
            }


            $image_object = $_FILES['vHotelImage']['tmp_name'];
            $image_name = $_FILES['vHotelImage']['name'];
            $image_name = str_replace(' ', '', $image_name);

            if ($image_name != "") {
                // $filecheck = basename($_FILES['vHotelImage']['name']);
                // $fileextarr = explode(".", $filecheck);
                // $ext = strtolower($fileextarr[scount($fileextarr) - 1]);
                // $flag_error = 0;
                // if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext1 != "svg") {
                //     $flag_error = 1;
                //     $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png, .svg";
                // }
                require_once("library/validation.class.php");
                $validobj = new validation();
                $imgUploadingExtenstionMsg = str_replace("####",$tconfig["tsite_upload_image_file_extensions_validation"],$langage_lbl_admin['LBL_FILE_UPLOADING_EXTENSION_MSG']);
                $error = $validobj->validateFileType($_FILES['vHotelImage'], $tconfig["tsite_upload_image_file_extensions"], $imgUploadingExtenstionMsg);

                 if ($error) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $error;
                        header("location:page.php");
                    exit;
                }
                if ($_FILES['vHotelImage']['size'] > 2097152) {
                    $flag_error = 1;
                    $var_msg = "Image size is too large";
                }
                if ($flag_error == 1) {
                        $_SESSION['success'] = 3;
                        $_SESSION['var_msg'] = $var_msg;
                        header("location:" . $backlink);
                    //getPostForm($_POST, $var_msg, $tconfig['tsite_url_main_admin'] . "page_action.php?id=".$id."&success=3");
                    exit;
                } else {
                    //$Photo_Gallery_folder = $tconfig["tsite_upload_page_images_panel"] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                        mkdir($Photo_Gallery_folder, 0777);
                    }
                    $image_name = $UPLOAD_OBJ->UploadImage($Photo_Gallery_folder, $image_object, $image_name, '', $tconfig["tsite_upload_image_file_extensions"]); //fileupload

                    $vImage = $image_name[0];
                }
            }


	   }

    /* ------------------------------ update query ------------------------------ */

    $update_array['vTitle'] = $vTitle;
    $update_array['tMetaKeyword'] = $tMetaKeyword;
    $update_array['tMetaDescription'] = $tMetaDescription;
    $update_array['iOrderBy'] = $iOrderBy;
    if ($action == "Add") {
        $update_array['vPageName'] = $vPageName;
    }
    if ($image_name != '') {
        $update_array['vImage'] = $vImage;
    }
    if ($image_name1 != '') {
        $update_array['vImage1'] = $vImage1;
    }
     if ($image_name2 != '') {
        $update_array['vImage2'] = $vImage2;
    }
     if ($Signimage_name1 != '') {
        $update_array['vSignImage1'] = $vSignImage1;
    }
     if ($Signimage_name2 != '') {
        $update_array['vSignImage2'] = $vSignImage2;
    }

    if ($user_image_name != '') {
        $update_array['vUserImage'] = $vUserImage;
    }
    if ($driver_image_name != '') {
        $update_array['vDriverImage'] = $vDriverImage;
    }
    if ($company_image_name != '') {
        $update_array['vCompanyImage'] = $vCompanyImage;
    }
    if ($store_image_name != '') {
        $update_array['vStoreImage'] = $vStoreImage;
    }
    if ($org_image_name != '') {
        $update_array['vOrgImage'] = $vOrgImage;
    }

    if ($tracking_image_name != '') {
        $update_array['vTrackingImage'] = $vTrackingImage;
    }

    $where = "`iPageId` = '" . $iPageId . "'";


    $id = $obj->MySQLQueryPerform($tbl_name, $update_array, 'update', $where);

    /* ------------------------------ update query ------------------------------ */


    if ($action == 'Add') {
        $iPageId = $obj->GetInsertId();
    }

    //header("Location:page_action.php?id=".$iPageId.'&success=1');
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
    }
    $oCache->flushData();
    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $page_ids = explode(",", $OPTIMIZE_DATA_OBJ->page_ids);
        if(in_array($iPageId, $page_ids)) {
            $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticPages');
        }
    }
    updateSystemData();

    header("location:" . $backlink);
    exit;
}

// for Edit

if ($action == 'Edit') {
    $sql = "SELECT * FROM " . $tbl_name . " WHERE iPageId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);
    $vLabel = $id;


    if (scount($db_data) > 0) {
        for ($i = 0; $i < scount($db_master); $i++) {
            foreach ($db_data as $key => $value) {

                $vPageTitle = 'vPageTitle_' . $db_master[$i]['vCode'];
                $$vPageTitle = $value[$vPageTitle];
                $tPageDesc = 'tPageDesc_' . $db_master[$i]['vCode'];
                $$tPageDesc = $value[$tPageDesc];

                if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                    $pageSubtitle = $value['pageSubtitle'];
                    $pageSubtitleArr = json_decode($pageSubtitle, true);
                }
                if($cubexthemeon == 'Yes' && $iPageId==52) {
                    $pageSubtitle = $value['pageSubtitle'];
                    $pageSubtitleArr = json_decode($pageSubtitle, true);
                }

                $vPageName = $value['vPageName'];
                $vTitle = $value['vTitle'];
                $tMetaKeyword = $value['tMetaKeyword'];
                $tMetaDescription = $value['tMetaDescription'];
                $vImage = $value['vImage'];
                $vImage1 = $value['vImage1'];
                $vImage2 = $value['vImage2'];
                $vSignImage1 = $value['vSignImage1'];
                $vSignImage2 = $value['vSignImage2'];
                $iOrderBy = $value['iOrderBy']; //added by SP for pages orderby,active/inactive functionality

                $vUserImage = $value['vUserImage'];
                $vDriverImage = $value['vDriverImage'];
                $vCompanyImage = $value['vCompanyImage'];
                $vStoreImage = $value['vStoreImage'];
                $vOrgImage = $value['vOrgImage'];
                $vTrackingImage = $value['vTrackingImage'];
            }
        }
    }
}

$serviceArray = $serviceIdArray = array();
$serviceArray = json_decode(serviceCategories, true);
$serviceIdArray = array_column($serviceArray, 'iServiceId');

$become_restaurant = '';
if(strtoupper(DELIVERALL) == "YES") {
    if (scount($serviceIdArray) == 1 && $serviceIdArray[0]==1) {
        $become_restaurant = $langage_lbl_admin['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl_admin['LBL_STORE'];
    }
}
$activetab = 'usertab';
$hotelPanel = $MODULES_OBJ->isEnableHotelPanel('Yes');

$EN_available = $LANG_OBJ->checkLanguageExist();
$db_master = $LANG_OBJ->getLangDataDefaultFirst($db_master);

$onlyRideShareEnable = !empty($MODULES_OBJ->isOnlyEnableRideSharingPro()) ? 'Yes' : 'No';
$onlyBSREnable = !empty($MODULES_OBJ->isOnlyEnableBuySellRentPro()) ? 'Yes' : 'No';
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Static Page <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

        <?php include_once('global_files.php'); ?>
        <!-- PAGE LEVEL STYLES -->
        <link rel="stylesheet" href="../assets/plugins/Font-Awesome/css/font-awesome.css" />
        <link rel="stylesheet" href="../assets/plugins/wysihtml5/dist/bootstrap-wysihtml5-0.0.2.css" />
        <style>
            ul.wysihtml5-toolbar > li {
                position: relative;
            }
            .modal{
                overflow:inherit;
            }
        </style>

    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <?php include_once('header.php'); ?>
            <?php include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Static Page</h2>
                            <a href="page.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <?php include('valid_msg.php'); ?>
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_Record_Updated_successfully']; ?>
                                </div><br/>
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?php echo $langage_lbl_admin['LBL_EDIT_DELETE_RECORD']; ?>
                                </div><br/>
                            <?php } ?>
                            <?php if($cubexthemeon == 'Yes' && in_array($iPageId,array('1','52'))) {
                             include_once('aboutus.php');
                            } else { ?>
                                <form method="post" action="" name="_page_form" id="_page_form"  enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?= $id; ?>"/>
                                    <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                    <input type="hidden" name="backlink" id="backlink" value="page.php"/>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label>Page/Section</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="text" class="form-control" name="vPageName"  id="vPageName" value="<?= htmlspecialchars($vPageName); ?>" placeholder="Page Name" <?= ($action == "Edit") ? 'readonly disabled' : ''; ?> >
                                        </div>
                                    </div>
                                    <?php if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {

                                    if($iPageId == 48) { ?>

                                        <?php if(scount($db_master) > 1) { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Page Title <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vPageSubTitle_Default" value="<?= $pageSubtitleArr['pageSubtitle_'.$default_lang]; ?>" data-originalvalue="<?= $pageSubtitleArr['pageSubtitle_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editPageSubTitle('Add')" <?php } ?>>
                                            </div>
                                            <?php if($id != "") { ?>
                                            <div class="col-lg-2">
                                                <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editPageSubTitle('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <div  class="modal fade" id="tPageSubTitle_Modal"  role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                            <div class="modal-dialog" >
                                                <div class="modal-content nimot-class">
                                                    <div class="modal-header">
                                                        <h4>
                                                            <span id="modal_action"></span> Page Title
                                                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPageSubTitle_')">x</button>
                                                        </h4>
                                                    </div>

                                                    <div class="modal-body">
                                                        <?php

                                                            for ($i = 0; $i < $count_all; $i++)
                                                            {
                                                                $vCode = $db_master[$i]['vCode'];
                                                                $vLTitle = $db_master[$i]['vTitle'];
                                                                $eDefault = $db_master[$i]['eDefault'];

                                                                $vPageSubTitleS = "vPageSubTitle_$vCode";

                                                                $vPageSubTitle = "vPageSubTitle[$vCode]";

                                                                $pageSubtitle = 'pageSubtitle_' . $vCode;
                                                                $$pageSubtitle = $pageSubtitleArr[$pageSubtitle];

                                                                $required = ($eDefault == 'Yes') ? 'required' : '';
                                                                $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                        ?>
                                                                <div class="row">
                                                                    <div class="col-lg-12">
                                                                        <label>Page Title (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>

                                                                    </div>
                                                                    <div class="col-lg-12">
                                                                        <input type="text" class="form-control" name="<?= $vPageSubTitle; ?>" id="<?= $vPageSubTitleS; ?>" value="<?= $$pageSubtitle; ?>" data-originalvalue="<?= $$pageSubtitle; ?>" placeholder="<?= $vLTitle; ?> Value">
                                                                        <div class="text-danger" id="<?= $pageSubtitle.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                    </div>

                                                                    <?php
                                                                    if (scount($db_master) > 1) {
                                                                        if($EN_available) {
                                                                            if($vCode == "EN") { ?>
                                                                            <div class="col-lg-12">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPageSubTitle_', 'EN');" style="margin-top: 10px">Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        } else {
                                                                            if($vCode == $default_lang) { ?>
                                                                            <div class="col-lg-12">
                                                                                <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPageSubTitle_', '<?= $default_lang ?>');" style="margin-top: 10px">Convert To All Language</button>
                                                                            </div>
                                                                        <?php }
                                                                        }
                                                                    }
                                                                    ?>
                                                                </div>
                                                            <?php
                                                            }
                                                        ?>
                                                    </div>
                                                    <div class="modal-footer" style="margin-top: 0">
                                                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                        <div class="nimot-class-but" style="margin-bottom: 0">
                                                            <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="savePageSubTitle()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPageSubTitle_')">Cancel</button>
                                                        </div>
                                                    </div>

                                                    <div style="clear:both;"></div>
                                                </div>
                                            </div>

                                        </div>
                                        <?php } else { ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <label>Page Title <span class="red"> *</span></label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" id="vPageSubTitle_<?= $default_lang ?>" name="vPageSubTitle[<?= $default_lang ?>]" value="<?= $pageSubtitleArr['pageSubtitle_'.$default_lang]; ?>" required>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    <?php }
                                    ?>
                                    <input type="hidden" class="form-control" name="vTitle"  id="vTitle" value="<?= $vTitle; ?>" placeholder="Meta Title">



                                    <ul class="nav nav-tabs">
                                        <li class="<?php if($activetab=='usertab') { ?> active <?php }  ?>">
                                            <a data-toggle="tab" href="#usertab"><?=$langage_lbl_admin['LBL_RIDER'];?></a>
                                        </li>
                                        <?php if ($onlyRideShareEnable != 'Yes' && $onlyBSREnable != 'Yes') { ?>
                                            <li class="<?php if($activetab=='drivertab') { ?> active <?php }  ?>">
                                                <a data-toggle="tab" href="#drivertab"><?=$langage_lbl_admin['LBL_SIGNIN_DRIVER'];?></a>
                                            </li>
                                            <?php if(strtoupper(ONLYDELIVERALL) != "YES") { ?><li class="<?php if($activetab=='companytab') { ?> active <?php }  ?>">
                                                <a data-toggle="tab" href="#companytab"><?=$langage_lbl_admin['LBL_COMPANY_SIGNIN'];?></a>
                                            </li>
                                            <?php } if (!empty($become_restaurant)) { ?><li class="<?php if($activetab=='restauranttab') { ?> active <?php }  ?>">
                                                <a data-toggle="tab" href="#restauranttab"><?=$become_restaurant;?></a>
                                            </li>
                                            <?php } if(strtoupper($ENABLE_CORPORATE_PROFILE)=='YES') { ?><li class="<?php if($activetab=='organizationtab') { ?> active <?php }  ?>">
                                                <a data-toggle="tab" href="#organizationtab"><?=$langage_lbl_admin['LBL_ORGANIZATION'];?></a>
                                            </li>
                                            <?php } if($MODULES_OBJ->isEnableTrackServiceFeature()) { ?><li class="<?php if($activetab=='trackservicetab') { ?> active <?php }  ?>">
                                                <a data-toggle="tab" href="#trackservicetab">Tracking Company</a>
                                            </li>
                                            <?php } if($iPageId==48) { ?>
                                            <?php if($hotelPanel > 0) { ?><li class="<?php if($activetab=='hoteltab') { ?> active <?php }  ?>">
                                                <a data-toggle="tab" href="#hoteltab"><?=$langage_lbl_admin['LBL_HOTEL_LOGIN'];?></a>
                                            </li>
                                        <?php } } } ?>
                                    </ul>
                                    <div class="tab-content mb-20">
                                        <div id="usertab" class="tab-pane <?php if($activetab=='usertab') { ?> active <?php }  ?>">

                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>

                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_User = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_User = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_User],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_User],true);
                                                $titleval = $pagetitlearr['user_pages'];
                                                $descval = $pagedescarr['user_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_User];
                                                $descval = $db_data[0][$vPageDesc_Default_User];
                                            }
                                            if(scount($db_master) > 1) {
                                             if(!in_array($iPageId, [48,50])) {//bcoz no need it in signup page
                                             ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Page Sub Description</label>
                                                    </div>
                                                    <div class="col-md-6 col-sm-6">
                                                        <textarea class="form-control" name="vUserPageTitle_Default"  id="vUserPageTitle_Default" readonly="readonly" <?php if($id == "") { ?> onclick="editUserPage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                    </div>
                                                    <?php if($id != "") { ?>
                                                    <div class="col-lg-2">
                                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editUserPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" name="tUserPageDesc_Default"  id="tUserPageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'UserDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div  class="modal fade" id="tPageUserDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action_user"></span> <?=$langage_lbl_admin['LBL_RIDER'];?> Page Sub Description
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vUserPageTitle_')">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php
                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitleU = 'vUserPageTitle_' . $vCode;
                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                                        $titleval = $pagetitlearr['user_pages'];
                                                                    } else {
                                                                        $titleval = $$vPageTitle;
                                                                    }
                                                            ?>
                                                            <div class="row">
                                                                <div class="col-lg-12">
                                                                    <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                </div>
                                                                <?php
                                                                $page_title_class = 'col-lg-12';
                                                                if (scount($db_master) > 1) {
                                                                    if($EN_available) {
                                                                        if($vCode == "EN") {
                                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                                        }
                                                                    } else {
                                                                        if($vCode == $default_lang) {
                                                                            $page_title_class = 'col-md-9 col-sm-9';
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                <div class="<?= $page_title_class ?>">
                                                                    <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                    <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                </div>

                                                                <?php
                                                                if (scount($db_master) > 1) {
                                                                    if($EN_available) {
                                                                        if($vCode == "EN") { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vUserPageTitle_', 'EN');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                    } else {
                                                                        if($vCode == $default_lang) { ?>
                                                                        <div class="col-md-3 col-sm-3">
                                                                            <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vUserPageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                        </div>
                                                                    <?php }
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                            <?php } ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveUserPage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vUserPageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div  class="modal fade" id="UserDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> <?=$langage_lbl_admin['LBL_RIDER'];?> Page Description
                                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDescU = 'tUserPageDesc_' . $vCode;
                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                                        $descval = $pagedescarr['user_pages'];
                                                                    } else {
                                                                        $descval = $$tPageDesc;
                                                                    }
                                                            ?>

                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle ?>)</label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tUserPageDesc_', 'UserDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php } else { ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Page Sub Description <span class="red"> *</span></label>
                                                    </div>
                                                    <div class="col-md-6 col-sm-6">
                                                        <textarea class="form-control" name="vUserPageTitle_<?= $default_lang ?>"  id="vUserPageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Page Description </label>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control ckeditor" rows="10" name="tUserPageDesc_<?= $default_lang ?>"  id="tUserPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vUserImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vUserImage; ?>"><img src="<?= $images . $vUserImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vUserImage" id="vUserImage" />
                                                    <span></span>
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="drivertab" class="tab-pane <?php if($activetab=='drivertab') { ?> active <?php }  ?>">

                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>
                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_Driver = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_Driver = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_Driver],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_Driver],true);
                                                $titleval = $pagetitlearr['provider_pages'];
                                                $descval = $pagedescarr['provider_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_Driver];
                                                $descval = $db_data[0][$vPageDesc_Default_Driver];
                                            }
                                            if(scount($db_master) > 1) {
                                             if(!in_array($iPageId, [48,50])) {//bcoz no need it in signup page ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vProviderPageTitle_Default"  id="vProviderPageTitle_Default" readonly="readonly" <?php if($id == "") { ?> onclick="editProviderPage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editProviderPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" name="tProviderPageDesc_Default"  id="tProviderPageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'ProDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div class="modal fade" id="tPageProviderDesc_Modal"  role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action_provider"></span> <?=$langage_lbl_admin['LBL_SIGNIN_DRIVER'];?> Page Sub Description
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vProviderPageTitle_')">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitleU = 'vProviderPageTitle_' . $vCode;
                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                                        $titleval = $pagetitlearr['provider_pages'];
                                                                    } else {
                                                                        $titleval = $$vPageTitle;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        </div>
                                                                        <?php
                                                                        $page_title_class = 'col-lg-12';
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            } else {
                                                                                if($vCode == $default_lang) {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="<?= $page_title_class ?>">
                                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>

                                                                        <?php
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vProviderPageTitle_', 'EN');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            } else {
                                                                                if($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vProviderPageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveProviderPage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vProviderPageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="ProDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> <?=$langage_lbl_admin['LBL_SIGNIN_DRIVER'];?> Page Description
                                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDescU = 'tProviderPageDesc_' . $vCode;
                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                                        $descval = $pagedescarr['provider_pages'];
                                                                    } else {
                                                                        $descval = $$tPageDesc;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle ?>)</label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tProviderPageDesc_', 'ProDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vProviderPageTitle_<?= $default_lang ?>"  id="vProviderPageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="tProviderPageDesc_<?= $default_lang ?>"  id="tProviderPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vDriverImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vDriverImage; ?>"><img src="<?= $images . $vDriverImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vDriverImage" id="vDriverImage" />
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="companytab" class="tab-pane <?php if($activetab=='companytab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>
                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_Company = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_Company = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_Company],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_Company],true);
                                                $titleval = $pagetitlearr['company_pages'];
                                                $descval = $pagedescarr['company_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_Company];
                                                $descval = $db_data[0][$vPageDesc_Default_Company];
                                            }
                                            if(scount($db_master) > 1) { ?>
                                                <?php if(!in_array($iPageId, [48])) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vCompanyPageTitle_Default"  id="vCompanyPageTitle_Default" rows="3" readonly="readonly" <?php if($id == "") { ?> onclick="editCompanyPage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editCompanyPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" name="tCompanyPageDesc_Default"  id="tCompanyPageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'CompDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div class="modal fade" id="tPageCompanyDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action_company"></span> <?=$langage_lbl_admin['LBL_COMPANY_SIGNIN'];?> Page Sub Description
                                                                <button type="button" class="close" data-dismiss="modal" >x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitleU = 'vCompanyPageTitle_' . $vCode;
                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                                        $titleval = $pagetitlearr['company_pages'];
                                                                    } else {
                                                                        $titleval = $$vPageTitle;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        </div>
                                                                        <?php
                                                                        $page_title_class = 'col-lg-12';
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            } else {
                                                                                if($vCode == $default_lang) {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="<?= $page_title_class ?>">
                                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>

                                                                        <?php
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCompanyPageTitle_', 'EN');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            } else {
                                                                                if($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vCompanyPageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveCompanyPage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vCompanyPageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="CompDesc_Modal"  role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> <?=$langage_lbl_admin['LBL_COMPANY_SIGNIN'];?> Page Description
                                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDescU = 'tCompanyPageDesc_' . $vCode;
                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                                        $descval = $pagedescarr['company_pages'];
                                                                    } else {
                                                                        $descval = $$tPageDesc;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle ?>)</label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tCompanyPageDesc_', 'CompDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vCompanyPageTitle_<?= $default_lang ?>"  id="vCompanyPageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="tCompanyPageDesc_<?= $default_lang ?>"  id="tCompanyPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } ?>
    											<?php  if (in_array($iPageId, array('48', '50'))) {?>
    												<div class="row" style="<?= $style_vimage1 ?>">
    													<div class="col-lg-12">
    														<label>Image</label>
    													</div>
    													<div class="col-md-6 col-sm-6">
    														<?php if ($vSignImage1 != '') { ?>
    															<a target="_blank" href="<?= $images . $vSignImage1; ?>"><img src="<?= $images . $vSignImage1; ?>" style="width:100px;height:100px;"></a>
    														<?php } ?>
    														<input type="file" class="form-control" name="vSignImage1" id="vSignImage1" />
    														<span class="notes">[Note: For Better Resolution Upload only image size of 512px * 512px.]</span>
    													</div>
    												</div>
    											<?php } ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Image (Left side shown)</label>
                                                    </div>
                                                    <div class="col-md-6 col-sm-6">
                                                        <?php if ($vCompanyImage != '') { ?>
                                                            <a target="_blank" href="<?= $images . $vCompanyImage; ?>"><img src="<?= $images . $vCompanyImage; ?>" style="width:200px;"></a>
                                                        <?php } ?>
                                                        <input type="file" class="form-control" name="vCompanyImage" id="vCompanyImage" />
                                                        <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                    </div>
                                                </div>
                                        </div>
                                        <div id="restauranttab" class="tab-pane <?php if($activetab=='restauranttab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>
                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_Restaurant = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_Restaurant = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_Restaurant],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_Restaurant],true);
                                                $titleval = $pagetitlearr['restaurant_pages'];
                                                $descval = $pagedescarr['restaurant_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_Restaurant];
                                                $descval = $db_data[0][$vPageDesc_Default_Restaurant];
                                            }
                                            if(scount($db_master) > 1) { ?>
                                            <?php if(!in_array($iPageId, [48])) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vRestaurantPageTitle_Default"  id="vRestaurantPageTitle_Default" rows="3" readonly="readonly" <?php if($id == "") { ?> onclick="editRestaurantPage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editRestaurantPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" name="tRestaurantPageDesc_Default"  id="tRestaurantPageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>



                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'RestDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div class="modal fade" id="tRestaurantPageDesc_Modal"  role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action_store"></span> <?=$langage_lbl_admin['LBL_RESTAURANT_TXT'];?> Page Sub Description
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRestaurantPageTitle_')">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitleU = 'vRestaurantPageTitle_' . $vCode;
                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                                        $titleval = $pagetitlearr['restaurant_pages'];
                                                                    } else {
                                                                        $titleval = $$vPageTitle;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        </div>
                                                                        <?php
                                                                        $page_title_class = 'col-lg-12';
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            } else {
                                                                                if($vCode == $default_lang) {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="<?= $page_title_class ?>">
                                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>

                                                                        <?php
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vRestaurantPageTitle_', 'EN');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            } else {
                                                                                if($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vRestaurantPageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveRestaurantPage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vRestaurantPageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="RestDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> <?=$langage_lbl_admin['LBL_RESTAURANT_TXT'];?> Page Description
                                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDescU = 'tRestaurantPageDesc_' . $vCode;
                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                                        $descval = $pagedescarr['restaurant_pages'];
                                                                    } else {
                                                                        $descval = $$tPageDesc;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle ?>)</label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tRestaurantPageDesc_', 'RestDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vRestaurantPageTitle_<?= $default_lang ?>"  id="vRestaurantPageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="tRestaurantPageDesc_<?= $default_lang ?>"  id="tRestaurantPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vStoreImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vStoreImage; ?>"><img src="<?= $images . $vStoreImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vStoreImage" id="vStoreImage" />
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="organizationtab" class="tab-pane <?php if($activetab=='organizationtab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>
                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_Org = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_Org = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_Org],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_Org],true);
                                                $titleval = $pagetitlearr['org_pages'];
                                                $descval = $pagedescarr['org_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_Org];
                                                $descval = $db_data[0][$vPageDesc_Default_Org];
                                            }
                                            if(scount($db_master) > 1) { ?>
                                                <?php if(!in_array($iPageId, [48])) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vOrgPageTitle_Default"  id="vOrgPageTitle_Default" rows="3" readonly="readonly" <?php if($id == "") { ?> onclick="editOrgPage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editOrgPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <label>Page Description </label>
                                                    </div>
                                                    <div class="col-md-6 col-sm-6">
                                                        <textarea class="form-control ckeditor" rows="10" name="tOrgPageDesc_Default"  id="tOrgPageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>
                                                    </div>
                                                    <?php if($id != "") { ?>
                                                    <div class="col-lg-1">
                                                        <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'OrgDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                    </div>
                                                    <?php } ?>
                                                </div>

                                                <div class="modal fade" id="tOrgPageDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                    <div class="modal-dialog modal-lg" >
                                                        <div class="modal-content nimot-class">
                                                            <div class="modal-header">
                                                                <h4>
                                                                    <span id="modal_action_org"></span> <?=$langage_lbl_admin['LBL_ORGANIZATION'];?> Page Sub Description
                                                                    <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vOrgPageTitle_')">x</button>
                                                                </h4>
                                                            </div>

                                                            <div class="modal-body">
                                                                <?php

                                                                    for ($i = 0; $i < $count_all; $i++)
                                                                    {
                                                                        $vCode = $db_master[$i]['vCode'];
                                                                        $vLTitle = $db_master[$i]['vTitle'];
                                                                        $eDefault = $db_master[$i]['eDefault'];

                                                                        $vPageTitleU = 'vOrgPageTitle_' . $vCode;
                                                                        $vPageTitle = 'vPageTitle_' . $vCode;

                                                                        if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                            $pagetitlearr = json_decode($$vPageTitle,true);
                                                                            $titleval = $pagetitlearr['org_pages'];
                                                                        } else {
                                                                            $titleval = $$vPageTitle;
                                                                        }
                                                                ?>
                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                            </div>
                                                                            <?php
                                                                            $page_title_class = 'col-lg-12';
                                                                            if (scount($db_master) > 1) {
                                                                                if($EN_available) {
                                                                                    if($vCode == "EN") {
                                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                                    }
                                                                                } else {
                                                                                    if($vCode == $default_lang) {
                                                                                        $page_title_class = 'col-md-9 col-sm-9';
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                            <div class="<?= $page_title_class ?>">
                                                                                <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                                <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                            </div>

                                                                            <?php
                                                                            if (scount($db_master) > 1) {
                                                                                if($EN_available) {
                                                                                    if($vCode == "EN") { ?>
                                                                                    <div class="col-md-3 col-sm-3">
                                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vOrgPageTitle_', 'EN');">Convert To All Language</button>
                                                                                    </div>
                                                                                <?php }
                                                                                } else {
                                                                                    if($vCode == $default_lang) { ?>
                                                                                    <div class="col-md-3 col-sm-3">
                                                                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vOrgPageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                                    </div>
                                                                                <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                    <?php
                                                                    }
                                                                ?>
                                                            </div>
                                                            <div class="modal-footer" style="margin-top: 0">
                                                                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                                    <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveOrgPage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vOrgPageTitle_')">Cancel</button>
                                                                </div>
                                                            </div>

                                                            <div style="clear:both;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal fade" id="OrgDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                    <div class="modal-dialog modal-lg" >
                                                        <div class="modal-content nimot-class">
                                                            <div class="modal-header">
                                                                <h4>
                                                                    <span id="modal_action"></span> <?=$langage_lbl_admin['LBL_ORGANIZATION'];?> Page Description
                                                                    <button type="button" class="close" data-dismiss="modal">x</button>
                                                                </h4>
                                                            </div>

                                                            <div class="modal-body">
                                                                <?php

                                                                    for ($i = 0; $i < $count_all; $i++)
                                                                    {
                                                                        $vCode = $db_master[$i]['vCode'];
                                                                        $vLTitle = $db_master[$i]['vTitle'];
                                                                        $eDefault = $db_master[$i]['eDefault'];

                                                                        $tPageDescU = 'tOrgPageDesc_' . $vCode;
                                                                        $tPageDesc = 'tPageDesc_' . $vCode;

                                                                        if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                            $pagedescarr = json_decode($$tPageDesc,true);
                                                                            $descval = $pagedescarr['org_pages'];
                                                                        } else {
                                                                            $descval = $$tPageDesc;
                                                                        }
                                                                ?>

                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <label>Page Description (<?= $vLTitle ?>)</label>

                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                                <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                            </div>
                                                                        </div>
                                                                    <?php
                                                                    }
                                                                ?>
                                                            </div>
                                                            <div class="modal-footer" style="margin-top: 0">
                                                                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                                <div class="nimot-class-but" style="margin-bottom: 0">
                                                                    <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tOrgPageDesc_', 'OrgDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                                </div>
                                                            </div>

                                                            <div style="clear:both;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vOrgPageTitle_<?= $default_lang ?>"  id="vOrgPageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="tOrgPageDesc_<?= $default_lang ?>"  id="tOrgPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } ?>
											<?php  if (in_array($iPageId, array('48', '50'))) {?>
												<div class="row" style="<?= $style_vimage1 ?>">
													<div class="col-lg-12">
														<label>Image</label>
													</div>
													<div class="col-md-6 col-sm-6">
														<?php if ($vSignImage2 != '') { ?>
															<a target="_blank" href="<?= $images . $vSignImage2; ?>"><img src="<?= $images . $vSignImage2; ?>" style="width:100px;height:100px;"></a>
														<?php } ?>
														<input type="file" class="form-control" name="vSignImage2" id="vSignImage2" />
														<span class="notes">[Note: For Better Resolution Upload only image size of 512px * 512px.]</span>
													</div>
												</div>
											<?php } ?>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vOrgImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vOrgImage; ?>"><img src="<?= $images . $vOrgImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vOrgImage" id="vOrgImage" />
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="trackservicetab" class="tab-pane <?php if($activetab=='trackservicetab') { ?> active <?php }  ?>">
                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>
                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_TrackService = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_TrackService = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_TrackService],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_TrackService],true);
                                                $titleval = $pagetitlearr['trackservice_pages'];
                                                $descval = $pagedescarr['trackservice_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_TrackService];
                                                $descval = $db_data[0][$vPageDesc_Default_TrackService];
                                            }
                                            if(scount($db_master) > 1) {
                                             ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vTrackServicePageTitle_Default" rows="3" id="vTrackServicePageTitle_Default" readonly="readonly" <?php if($id == "") { ?> onclick="editTrackServicePage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editTrackServicePage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" name="tTrackServicePageDesc_Default"  id="tTrackServicePageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'TrackServiceDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>

                                            </div>

                                            <div  class="modal fade" id="tTrackServicePageDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action_TrackService"></span> Tracking Company Page Sub Description
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTrackServicePageTitle_')">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitleU = 'vTrackServicePageTitle_' . $vCode;
                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                                        $titleval = $pagetitlearr['trackservice_pages'];
                                                                    } else {
                                                                        $titleval = $$vPageTitle;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        </div>
                                                                        <?php
                                                                        $page_title_class = 'col-lg-12';
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            } else {
                                                                                if($vCode == $default_lang) {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="<?= $page_title_class ?>">
                                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>

                                                                        <?php
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTrackServicePageTitle_', 'EN');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            } else {
                                                                                if($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vTrackServicePageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveTrackServicePage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vTrackServicePageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div  class="modal fade" id="TrackServiceDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> Tracking Company Page Description
                                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDescU = 'tTrackServicePageDesc_' . $vCode;
                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                                        $descval = $pagedescarr['trackservice_pages'];
                                                                    } else {
                                                                        $descval = $$tPageDesc;
                                                                    }
                                                            ?>

                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle ?>)</label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tTrackServicePageDesc_', 'TrackServiceDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vTrackServicePageTitle_<?= $default_lang ?>"  id="vTrackServicePageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="tTrackServicePageDesc_<?= $default_lang ?>"  id="tOrgPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } ?>


                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vTrackingImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vTrackingImage; ?>"><img src="<?= $images . $vTrackingImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vTrackingImage" id="vTrackingImage" />
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="hoteltab" class="tab-pane <?php if($activetab=='hoteltab') { ?> active <?php }  ?>">

                                            <?php $style_v = "";
                                            if (in_array($iPageId, array('29', '30'))) {
                                                $style_v = "style = 'display:none;'";
                                            }
                                            ?>
                                            <?php
                                            if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                $vPageTitle_Default_Hotel = 'vPageTitle_' . $default_lang;
                                                $vPageDesc_Default_Hotel = 'tPageDesc_' . $default_lang;
                                                $pagetitlearr = json_decode($db_data[0][$vPageTitle_Default_Hotel],true);
                                                $pagedescarr = json_decode($db_data[0][$vPageDesc_Default_Hotel],true);
                                                $titleval = $pagetitlearr['hotel_pages'];
                                                $descval = $pagedescarr['hotel_pages'];
                                            } else {
                                                $titleval = $db_data[0][$vPageTitle_Default_Hotel];
                                                $descval = $db_data[0][$vPageDesc_Default_Hotel];
                                            }
                                            if(scount($db_master) > 1) { ?>
                                             <?php if(!in_array($iPageId, [48])) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vHotelPageTitle_Default"  id="vHotelPageTitle_Default" readonly="readonly" <?php if($id == "") { ?> onclick="editHotelPage('Add')" <?php } ?> data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editHotelPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" name="tHotelPageDesc_Default"  id="tHotelPageDesc_Default" readonly="readonly"> <?= $descval; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'HotelDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div class="modal fade" id="tHotelPageDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action_hotel"></span> <?= $langage_lbl_admin['LBL_HOTEL_LOGIN'] ?> Page Sub Description
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vHotelPageTitle_')">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitleU = 'vHotelPageTitle_' . $vCode;
                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagetitlearr = json_decode($$vPageTitle,true);
                                                                        $titleval = $pagetitlearr['hotel_pages'];
                                                                    } else {
                                                                        $titleval = $$vPageTitle;
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Sub Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        </div>
                                                                        <?php
                                                                        $page_title_class = 'col-lg-12';
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            } else {
                                                                                if($vCode == $default_lang) {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="<?= $page_title_class ?>">
                                                                            <textarea class="form-control" name="<?= $vPageTitleU; ?>"  id="<?= $vPageTitleU; ?>" placeholder="<?= $vLTitle; ?> Value" data-originalvalue="<?= $titleval; ?>"><?= $titleval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $vPageTitleU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>

                                                                        <?php
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vHotelPageTitle_', 'EN');" >Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            } else {
                                                                                if($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vHotelPageTitle_', '<?= $default_lang ?>');">Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>

                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveHotelPage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vHotelPageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal fade" id="HotelDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> <?= $langage_lbl_admin['LBL_HOTEL_LOGIN'] ?> Page Description
                                                                <button type="button" class="close" data-dismiss="modal">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDescU = 'tHotelPageDesc_' . $vCode;
                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($cubexthemeon == 'Yes' && in_array($iPageId,$pageArray)) {
                                                                        $pagedescarr = json_decode($$tPageDesc,true);
                                                                        $descval = $pagedescarr['hotel_pages'];
                                                                    } else {
                                                                        $descval = $$tPageDesc;
                                                                    }
                                                            ?>

                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle ?>)</label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDescU; ?>"  id="<?= $tPageDescU; ?>"  placeholder="<?= $tPageDesc; ?> Value"> <?= $descval; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDescU.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tHotelPageDesc_', 'HotelDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Sub Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control" name="vHotelPageTitle_<?= $default_lang ?>"  id="vHotelPageTitle_<?= $default_lang ?>"><?= $titleval; ?></textarea>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description </label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" name="tHotelPageDesc_<?= $default_lang ?>"  id="tHotelPageDesc_<?= $default_lang ?>"> <?= $descval; ?></textarea>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vHotelImage" id="vHotelImage" />
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } else { ?>

                                        <!--<textarea class="form-control ckeditor" rows="10" name="aaa"  id="editortest"  placeholder="aa Value"></textarea>-->
                                        <?php
                                        $style_v = "";
                                        if (in_array($iPageId, array('29', '30','53'))) {
                                            $style_v = "style = 'display:none;'";
                                        }
                                        if (scount($db_master) > 1) {
                                            if (!in_array($iPageId, array('55', '56'))) {
                                            ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Title <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <input type="text" class="form-control <?= ($id == "") ?  'readonly-custom' : '' ?>" id="vPageTitle_Default" value="<?= $db_data[0]['vPageTitle_'.$default_lang]; ?>" data-originalvalue="<?= $db_data[0]['vPageTitle_'.$default_lang]; ?>" readonly="readonly" <?php if($id == "") { ?> onclick="editPage('Add')" <?php } ?>>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editPage('Edit')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <?php if (!in_array($iPageId, array('53'))) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <textarea class="form-control ckeditor" rows="10" id="tPageDesc_Default" readonly="readonly"><?= $db_data[0]['tPageDesc_'.$default_lang]; ?></textarea>
                                                </div>
                                                <?php if($id != "") { ?>
                                                <div class="col-lg-2">
                                                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="editDescWeb('Edit', 'PageDesc_Modal')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                </div>
                                                <?php } ?>
                                            </div>

                                            <div  class="modal fade" id="tPageDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> Page Title
                                                                <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPageTitle_')">x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $vPageTitle = 'vPageTitle_' . $vCode;

                                                                    if($style_v=='') {
                                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                                    }
                                                            ?>
                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Title (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>
                                                                        </div>
                                                                        <?php
                                                                        $page_title_class = 'col-lg-12';
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            } else {
                                                                                if($vCode == $default_lang) {
                                                                                    $page_title_class = 'col-md-9 col-sm-9';
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                        <div class="<?= $page_title_class ?>">
                                                                            <input type="text" class="form-control" name="<?= $vPageTitle; ?>" id="<?= $vPageTitle; ?>" value="<?= $$vPageTitle; ?>" data-originalvalue="<?= $$vPageTitle; ?>" placeholder="<?= $vLTitle; ?> Value">
                                                                            <div class="text-danger" id="<?= $vPageTitle.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>

                                                                        <?php
                                                                        if (scount($db_master) > 1) {
                                                                            if($EN_available) {
                                                                                if($vCode == "EN") { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPageTitle_', 'EN');" >Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            } else {
                                                                                if($vCode == $default_lang) { ?>
                                                                                <div class="col-md-3 col-sm-3">
                                                                                    <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPageTitle_', '<?= $default_lang ?>');" >Convert To All Language</button>
                                                                                </div>
                                                                            <?php }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="savePage()"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, 'vPageTitle_')">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>

                                            </div>
                                            <?php } ?>
                                            <div  class="modal fade" id="PageDesc_Modal" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                                                <div class="modal-dialog modal-lg" >
                                                    <div class="modal-content nimot-class">
                                                        <div class="modal-header">
                                                            <h4>
                                                                <span id="modal_action"></span> Page Description
                                                                <button type="button" class="close" data-dismiss="modal" >x</button>
                                                            </h4>
                                                        </div>

                                                        <div class="modal-body">
                                                            <?php

                                                                for ($i = 0; $i < $count_all; $i++)
                                                                {
                                                                    $vCode = $db_master[$i]['vCode'];
                                                                    $vLTitle = $db_master[$i]['vTitle'];
                                                                    $eDefault = $db_master[$i]['eDefault'];

                                                                    $tPageDesc = 'tPageDesc_' . $vCode;

                                                                    if($style_v=='') {
                                                                        $required = ($eDefault == 'Yes') ? 'required' : '';
                                                                        $required_msg = ($eDefault == 'Yes') ? '<span class="red"> *</span>' : '';
                                                                    }
                                                            ?>

                                                                    <div class="row">
                                                                        <div class="col-lg-12">
                                                                            <label>Page Description (<?= $vLTitle; ?>) <?php echo $required_msg; ?></label>

                                                                        </div>
                                                                        <div class="col-lg-12">
                                                                            <textarea class="form-control ckeditor" rows="10" name="<?= $tPageDesc; ?>"  id="<?= $tPageDesc; ?>"  placeholder="<?= $vLTitle; ?> Value"> <?= $$tPageDesc; ?></textarea>
                                                                            <div class="text-danger" id="<?= $tPageDesc.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                }
                                                            ?>
                                                        </div>
                                                        <div class="modal-footer" style="margin-top: 0">
                                                            <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                                                            <div class="nimot-class-but" style="margin-bottom: 0">
                                                                <button type="button" class="save" id="tPageDesc_btn"  style="margin-left: 0 !important" onclick="saveDescWeb('tPageDesc_', 'PageDesc_Modal')"><?= $langage_lbl['LBL_Save']; ?></button>
                                                                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>

                                                        <div style="clear:both;"></div>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php } else { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Title <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <input type="text" class="form-control" id="vPageTitle_<?= $default_lang ?>" name="vPageTitle_<?= $default_lang ?>" value="<?= $db_data[0]['vPageTitle_'.$default_lang]; ?>">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Page Description <span class="red"> *</span></label>
                                                </div>
                                                <div class="col-lg-12">
                                                    <textarea class="form-control ckeditor" rows="10" id="tPageDesc_<?= $default_lang ?>" name="tPageDesc_<?= $default_lang ?>"><?= $db_data[0]['tPageDesc_'.$default_lang]; ?></textarea>
                                                </div>
                                            </div>
                                        <?php } ?>
                                            <?php

                                    }
                                    if (!in_array($iPageId, array('23', '24', '25', '26', '27', '46', '48','49','50', '54', '55','56','57', '59'))) {
                                        ?>
                                        <div class="row" <?= $style_v ?>>
                                            <div class="col-lg-12">
                                                <label>Meta Title</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" name="vTitle"  id="vTitle" value="<?= htmlspecialchars($vTitle); ?>" placeholder="Meta Title">
                                            </div>
                                        </div>
                                        <div class="row" <?= $style_v ?>>
                                            <div class="col-lg-12">
                                                <label>Meta Keyword</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <input type="text" class="form-control" name="tMetaKeyword"  id="tMetaKeyword" value="<?= htmlspecialchars($tMetaKeyword); ?>" placeholder="Meta Keyword">
                                            </div>
                                        </div>

                                        <div class="row" <?= $style_v ?>>
                                            <div class="col-lg-12">
                                                <label>Meta Description</label>
                                            </div>
                                            <div class="col-md-6 col-sm-6">
                                                <textarea class="form-control" rows="10" name="tMetaDescription"  id="<?= $tMetaDescription; ?>"  placeholder="<?= $tMetaDescription; ?> Value" <?= $required; ?>> <?= $tMetaDescription; ?></textarea>
                                            </div>
                                        </div>

                                        <?php
                                    } if (!in_array($iPageId, array('1', '2', '7', '4', '3', '6', '23', '27', '33','44','55','56','57'))) {
                                        ?>
                                        <?php
                                        $style_vimage = "";
                                        if ($cubexthemeon == 'Yes' && in_array($iPageId, $pageidCubexImage)) {
                                            ?>
                                            <br><br>
                                            <?php if($iPageId!=50) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage" id="vImage" />
                                                    <span class="notes">[Note: For Better Resolution Upload only image size of 330px * 500px.]</span>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <?php if(!in_array($iPageId, [48,49])) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <?php if($iPageId == 50) { ?>
                                                    <label>Image (Left side shown)</label>
                                                    <?php } else { ?>
                                                    <label>Background Image</label>
                                                    <?php } ?>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage1 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage1" id="vImage1" />
                                                     <span class="notes">[Note: For Better Resolution Upload only image size of 943px * 1920px.]</span>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        <?php } else if($cubexthemeon == 'Yes' && $iPageId==52) { ?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>First Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage" id="vImageaaa2" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Second Image (Right side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage1 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage1" id="vImagea1" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <label>Third Image (Left side shown)</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage2 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage2; ?>"><img src="<?= $images . $vImage2; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage2" id="vImagea2" />
                                                </div>
                                            </div>
                                        <?php } else if($cubexthemeon == 'Yes' && $iPageId==22) { ?>
                                        <div class="row" style="<?= $style_vimage ?>">
                                                <div class="col-lg-12">
                                                    <label>Image</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage1 != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage1; ?>"><img src="<?= $images . $vImage1; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage1" id="vImagen1" />
                                                     <span class="notes">[Note: For Better Resolution Upload only image size of 190px * 190px.]</span>
                                                </div>
                                            </div>
                                        <?php } else {
                                            
                                            if (!in_array($iPageId, array('53'))) {
                                                $style_vimage = "display:none;";
                                            }
                                            ?>
                                            <div class="row" style="<?= $style_vimage ?>">
                                                <div class="col-lg-12">
                                                    <label>Image</label>
                                                </div>
                                                <div class="col-md-6 col-sm-6">
                                                    <?php if ($vImage != '') { ?>
                                                        <a target="_blank" href="<?= $images . $vImage; ?>"><img src="<?= $images . $vImage; ?>" style="width:200px;"></a>
                                                    <?php } ?>
                                                    <input type="file" class="form-control" name="vImage" id="vImage" />
                                                     <span class="notes">[Note: For Better Resolution Upload only image size of 1903px * 626px.]</span>
                                                </div>
                                            </div>
                                        <?php } ?>



                                    <?php } if(!in_array($iPageId, [2, 22, 44, 46, 48, 49, 54, 50, 55, 56, 57, 59])) { ?>
                                    <!--                                added by SP for pages orderby,active/inactive functionality  -->
                                    <div class="row" <?= $style_v ?>>
                                        <div class="col-lg-12">
                                            <label>Display Order</label>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <input type="number" class="form-control" name="iOrderBy" id="iOrderBy" value="<?= $iOrderBy; ?>" placeholder="Page displayed according to this number" min="0">
                                        </div>
                                    </div>
                                    <?php } ?>

                                    <div class="row">
                                        <div class="col-lg-12">
                                            <?php if (($action == 'Edit' && $userObj->hasPermission('edit-pages')) || ($action == 'Add' && $userObj->hasPermission('create-pages'))) { ?>
                                                <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Static Page">
                                                <input type="reset" value="Reset" class="btn btn-default">
                                            <?php } ?>
                                            <!-- <a href="javascript:void(0);" onclick="reset_form('_page_form');" class="btn btn-default">Reset</a> -->
                                            <a href="page.php" class="btn btn-default back_link">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <div class="row loding-action" id="loaderIcon" style="display:none; z-index: 99999">
            <div align="center">
                <img src="default.gif">
                <span>Language Translation is in Process. Please Wait...</span>
            </div>
        </div>
        <?php include_once('footer.php'); ?>

        <!-- PAGE LEVEL SCRIPTS -->
        <script src="../assets/plugins/ckeditor/ckeditor.js"></script>
        <script src="../assets/plugins/ckeditor/config.js"></script>

        <script>
            /* CKEDITOR.replace( 'ckeditor',{
             allowedContent : {
             i:{
             classes:'fa*'
             },
             span: true
             }
             } ); */
        </script>
        <script>
            var myVar;
            $(document).ready(function () {
                var referrer;
                if ($("#previousLink").val() == "") {
                    referrer = document.referrer;
                } else {
                    referrer = $("#previousLink").val();
                }
                if (referrer == "") {
                    referrer = "page.php";
                } else {
                    $("#backlink").val(referrer);
                }
                $(".back_link").attr('href', referrer);
            });
            /**
             * This will reset the CKEDITOR using the input[type=reset] clicks.
             */
            $(function () {
                if (typeof CKEDITOR != 'undefined') {
                    $('form').on('reset', function (e) {
                        if ($(CKEDITOR.instances).length) {
                            for (var key in CKEDITOR.instances) {
                                var instance = CKEDITOR.instances[key];
                                if ($(instance.element.$).closest('form').attr('name') == $(e.target).attr('name')) {
                                    instance.setData(instance.element.$.defaultValue);
                                }
                            }
                        }
                    });
                }
            });

            function editPage(action)
            {
                $('#modal_action').html(action);
                $('#tPageDesc_Modal').modal('show');
            }

            function savePage()
            {
                var tPageDescLength = CKEDITOR.instances['tPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vPageTitle_<?= $default_lang ?>_error').show();
                    $('#vPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vPageTitle_Default').val($('#vPageTitle_<?= $default_lang ?>').val());
                $('#vPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vPageTitle_Default-error').remove();
                var tPageDescHTML = CKEDITOR.instances['tPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tPageDesc_Default'].setData(tPageDescHTML)
                $('#tPageDesc_Modal').modal('hide');
            }

            function editPageSubTitle(action)
            {
                $('#modal_action').html(action);
                $('#tPageSubTitle_Modal').modal('show');
            }

            function savePageSubTitle()
            {
                if($('#vPageSubTitle_<?= $default_lang ?>').val() == "") {
                    $('#vPageSubTitle_<?= $default_lang ?>_error').show();
                    $('#vPageSubTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vPageSubTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }

                $('#vPageSubTitle_Default').val($('#vPageSubTitle_<?= $default_lang ?>').val());
                $('#vPageSubTitle_Default').closest('.row').removeClass('has-error');
                $('#vPageSubTitle_Default-error').remove();
                $('#tPageSubTitle_Modal').modal('hide');
            }

            $(document).on("keypress keyup blur paste keydown", '#tPageDesc_Default, #vPageTitle_Default',function (event) {
                event.preventDefault();
            });

            function editUserPage(action)
            {
                $('#modal_action_user').html(action);
                $('#tPageUserDesc_Modal').modal('show');
            }

            function saveUserPage()
            {
                //var tPageDescULength = CKEDITOR.instances['tUserPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vUserPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vUserPageTitle_<?= $default_lang ?>_error').show();
                    $('#vUserPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vUserPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vUserPageTitle_Default').val($('#vUserPageTitle_<?= $default_lang ?>').val());
                $('#vUserPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vUserPageTitle_Default-error').remove();
                var tPageDescUHTML = CKEDITOR.instances['tUserPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tUserPageDesc_Default'].setData(tPageDescUHTML)
                $('#tPageUserDesc_Modal').modal('hide');
            }

            function editProviderPage(action)
            {
                $('#modal_action_provider').html(action);
                $('#tPageProviderDesc_Modal').modal('show');
            }

            function saveProviderPage()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vProviderPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vProviderPageTitle_<?= $default_lang ?>_error').show();
                    $('#vProviderPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vProviderPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vProviderPageTitle_Default').val($('#vProviderPageTitle_<?= $default_lang ?>').val());
                $('#vProviderPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vProviderPageTitle_Default-error').remove();
                var tPageDescPHTML = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tProviderPageDesc_Default'].setData(tPageDescPHTML)
                $('#tPageProviderDesc_Modal').modal('hide');
            }


            function editCompanyPage(action)
            {
                $('#modal_action_company').html(action);
                $('#tPageCompanyDesc_Modal').modal('show');
            }

            function saveCompanyPage()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vCompanyPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vCompanyPageTitle_<?= $default_lang ?>_error').show();
                    $('#vCompanyPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vCompanyPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vCompanyPageTitle_Default').val($('#vCompanyPageTitle_<?= $default_lang ?>').val());
                $('#vCompanyPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vCompanyPageTitle_Default-error').remove();
                var tPageDescCHTML = CKEDITOR.instances['tCompanyPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tCompanyPageDesc_Default'].setData(tPageDescCHTML)
                $('#tPageCompanyDesc_Modal').modal('hide');
            }

            function editRestaurantPage(action)
            {
                $('#modal_action_store').html(action);
                $('#tRestaurantPageDesc_Modal').modal('show');
            }

            function saveRestaurantPage()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vRestaurantPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vRestaurantPageTitle_<?= $default_lang ?>_error').show();
                    $('#vRestaurantPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vRestaurantPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vRestaurantPageTitle_Default').val($('#vRestaurantPageTitle_<?= $default_lang ?>').val());
                $('#vRestaurantPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vRestaurantPageTitle_Default-error').remove();
                var tPageDescRHTML = CKEDITOR.instances['tRestaurantPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tRestaurantPageDesc_Default'].setData(tPageDescRHTML)
                $('#tRestaurantPageDesc_Modal').modal('hide');
            }

            function editOrgPage(action)
            {
                $('#modal_action_org').html(action);
                $('#tOrgPageDesc_Modal').modal('show');
            }

            function saveOrgPage()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vOrgPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vOrgPageTitle_<?= $default_lang ?>_error').show();
                    $('#vOrgPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vOrgPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vOrgPageTitle_Default').val($('#vOrgPageTitle_<?= $default_lang ?>').val());
                $('#vOrgPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vOrgPageTitle_Default-error').remove();
                var tPageDescOHTML = CKEDITOR.instances['tOrgPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tOrgPageDesc_Default'].setData(tPageDescOHTML)
                $('#tOrgPageDesc_Modal').modal('hide');
            }

            function editTrackServicePage(action)
            {
                $('#modal_action_trackservice').html(action);
                $('#tTrackServicePageDesc_Modal').modal('show');
            }

            function saveTrackServicePage()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vTrackServicePageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vTrackServicePageTitle_<?= $default_lang ?>_error').show();
                    $('#vTrackServicePageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vTrackServicePageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vTrackServicePageTitle_Default').val($('#vTrackServicePageTitle_<?= $default_lang ?>').val());
                $('#vTrackServicePageTitle_Default').closest('.row').removeClass('has-error');
                $('#vTrackServicePageTitle_Default-error').remove();
                var tPageDescOHTML = CKEDITOR.instances['tTrackServicePageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tTrackServicePageDesc_Default'].setData(tPageDescOHTML)
                $('#tTrackServicePageDesc_Modal').modal('hide');
            }

            function editHotelPage(action)
            {
                $('#modal_action_hotel').html(action);
                $('#tHotelPageDesc_Modal').modal('show');
            }

            function saveHotelPage()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vHotelPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vHotelPageTitle_<?= $default_lang ?>_error').show();
                    $('#vHotelPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vHotelPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vHotelPageTitle_Default').val($('#vHotelPageTitle_<?= $default_lang ?>').val());
                $('#vHotelPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vHotelPageTitle_Default-error').remove();
                var tPageDescRHTML = CKEDITOR.instances['tHotelPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tHotelPageDesc_Default'].setData(tPageDescRHTML)
                $('#tHotelPageDesc_Modal').modal('hide');
            }

            function editAboutUsWeb(action)
            {
                $('#modal_action').html(action);
                $('#aboutUsWeb_Modal').modal('show');
            }

            function saveAboutUsWeb()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vPageTitle_<?= $default_lang ?>').val() == "") {
                    $('#vPageTitle_<?= $default_lang ?>_error').show();
                    $('#vPageTitle_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vPageTitle_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vPageTitle_Default').val($('#vPageTitle_<?= $default_lang ?>').val());
                $('#vPageTitle_Default').closest('.row').removeClass('has-error');
                $('#vPageTitle_Default-error').remove();
                /*var vPageSubTitleHTML = CKEDITOR.instances['vPageSubTitle_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['vPageSubTitle_Default'].setData(vPageSubTitleHTML);

                var tPageDescHTML = CKEDITOR.instances['tPageDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tPageDesc_Default'].setData(tPageDescHTML);

                var tPageSecDescHTML = CKEDITOR.instances['tPageSecDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tPageSecDesc_Default'].setData(tPageSecDescHTML);

                var tPageThirdDescHTML = CKEDITOR.instances['tPageThirdDesc_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tPageThirdDesc_Default'].setData(tPageThirdDescHTML);*/
                $('#aboutUsWeb_Modal').modal('hide');
            }

            function editAboutUsApp(action)
            {
                $('#modal_action').html(action);
                $('#aboutUsApp_Modal').modal('show');
            }

            function saveAboutUsApp()
            {
                //var tPageDescULength = CKEDITOR.instances['tProviderPageDesc_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if($('#vPageTitle_1_<?= $default_lang ?>').val() == "") {
                    $('#vPageTitle_1_<?= $default_lang ?>_error').show();
                    $('#vPageTitle_1_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#vPageTitle_1_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }/*
                else if(!tPageDescLength) {
                    $('#tPageDesc_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }*/

                $('#vPageTitle_1_Default').val($('#vPageTitle_1_<?= $default_lang ?>').val());
                $('#vPageTitle_1_Default').closest('.row').removeClass('has-error');
                $('#vPageTitle_1_Default-error').remove();

                var tPageDescHTML = CKEDITOR.instances['tPageDesc_1_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tPageDesc_1_Default'].setData(tPageDescHTML);

                $('#aboutUsApp_Modal').modal('hide');
            }

            function editDescApp(action)
            {
                $('#DescApp_Modal').find('#modal_action').html(action);
                $('#DescApp_Modal').modal('show');
            }

            function saveDescApp()
            {
                var DescAppLength = CKEDITOR.instances['tPageDesc_1_<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;

                if(!DescAppLength) {
                    $('#tPageDesc_1_<?= $default_lang ?>_error').show();
                    $('#tPageDesc_1_<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#tPageDesc_1_<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }

                var tPageDescHTML = CKEDITOR.instances['tPageDesc_1_<?= $default_lang ?>'].getData();
                CKEDITOR.instances['tPageDesc_1_Default'].setData(tPageDescHTML);

                $('#DescApp_Modal').modal('hide');
            }

            function editDescWeb(action, modal_id)
            {
                $('#'+modal_id).find('#modal_action').html(action);
                $('#'+modal_id).modal('show');
            }

            function saveDescWeb(input_id, modal_id)
            {
                var DescLength = CKEDITOR.instances[input_id+'<?= $default_lang ?>'].getData().replace(/<[^>]*>/gi, '').length;
                if(!DescLength) {
                    $('#'+input_id+'<?= $default_lang ?>_error').show();
                    $('#'+input_id+'<?= $default_lang ?>').focus();
                    clearInterval(myVar);
                    myVar = setTimeout(function() {
                        $('#'+input_id+'<?= $default_lang ?>_error').hide();
                    }, 5000);
                    e.preventDefault();
                    return false;
                }

                var DescHTML = CKEDITOR.instances[input_id + '<?= $default_lang ?>'].getData();
                CKEDITOR.instances[input_id+'Default'].setData(DescHTML);
                $('#'+modal_id).modal('hide');
            }
        </script>

    </body>
    <!-- END BODY-->
</html>
