<?php
include_once('common.php');
$AUTH_OBJ->checkMemberAuthentication();
$abc = 'company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$menu_itemid = isset($_REQUEST['menu_itemid']) ? $_REQUEST['menu_itemid'] : "";
$iCompanyId = $_SESSION['sess_iUserId'];
$ssql1 = $ssql = "";
if(!empty($menu_itemid)){
  $ssql1 .= " AND f.iFoodMenuId = '".$menu_itemid."'"; 
}

if(!empty($_POST["search"]["value"])){
	$ssql1 .= 'AND (mi.vItemType_'.$default_lang.' LIKE "%'.$_POST["search"]["value"].'%" ';
	$ssql1 .= ' OR f.vMenu_'.$default_lang.' LIKE "%'.$_POST["search"]["value"].'%")';			
}

if($_POST["length"] != -1){
	$ssql .= ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
}

$sql = "SELECT mi.*,f.vMenu_".$default_lang.",c.vCompany FROM  `menu_items` as mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId  WHERE 1=1 AND f.iCompanyId = '" . $iCompanyId . "' AND mi.eStatus != 'Deleted' $ssql1 $ssql";
$data_drv = $obj->MySQLSelect($sql);

$sql1= "SELECT mi.*,f.vMenu_".$default_lang.",c.vCompany FROM  `menu_items` as mi LEFT JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId LEFT JOIN company as c on c.iCompanyId=f.iCompanyId  WHERE 1=1 AND f.iCompanyId = '" . $iCompanyId . "' AND mi.eStatus != 'Deleted' $ssql1";
$data_drv1 = $obj->MySQLSelect($sql1);
$allRecords = scount($data_drv1);

$records = array();		
for ($i = 0; $i < scount($data_drv); $i++) { 
	$rows = array();
	$rows[] = $data_drv[$i]['vItemType_'.$default_lang];
	$rows[] = $data_drv[$i]['vMenu_'.$default_lang];

	if ($ENABLE_ITEM_MULTIPLE_IMAGE_VIDEO_UPLOAD == 'Yes') {
		$multipleImage = $MENU_ITEM_MEDIA_OBJ->getImageVideo($data_drv[$i]['iMenuItemId']);
		if(!empty($multipleImage)){
			foreach ($multipleImage as $images) {
				$image = $images['vImage'];
				$fileextarr = explode(".", $image);
				$ext = strtolower($fileextarr[scount($fileextarr) - 1]);
				if (in_array($ext, ['mp4', 'mov', 'wmv', 'avi', 'flv', 'mkv', 'webm'])) {
					$rows[] = '';
				} else {

					$imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $image;
					$imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $image;

					if ($image != "" && file_exists($imgpth)) {
						$rows[] = '<div style="width: 100px;" class=" shine setImageRatioJS"> <img onerror="this.onerror=null;" attr-image-src="'.$imgUrl.'" alt="Image preview" class="thumbnail resize_image imageVisibilityHidden resizeImageWithJs" > </div>';
					 	break; 
					} else {
						$rows[] = '';
						break; 
					}

				}
			}
		}else {
			$rows[] = '';
		}
	} else {
		$imgpth = $tconfig["tsite_upload_images_menu_item_path"] . '/' . $data_drv[$i]['vImage'];
	    $imgUrl = $tconfig["tsite_upload_images_menu_item"] . '/' . $data_drv[$i]['vImage'];
		if ($data_drv[$i]['vImage'] != "" && file_exists($imgpth)) {
			$rows[] = '<img src="'.$imgUrl.'" alt="Image preview" class="thumbnail" style="max-width: 100px; max-height: 100px;margin:0;display: initial;">';
		} else {
			$rows[] = '';
		}
	}

	$rows[] = $data_drv[$i]['iDisplayOrder'];
	$eStatus = ($data_drv[$i]['eStatus'] == "Active") ? 'Inactive' : 'Active';

	if(strtolower($data_drv[$i]['eStatus']) == "active"){
        $statusLabel = $langage_lbl['LBL_ACTIVE'];
	} else { 
 		$statusLabel = $langage_lbl['LBL_INACTIVE'];
	} 

	$rows[] = '<a href="menuitems.php?iMenuItemId='.$data_drv[$i]['iMenuItemId'].'&menu_itemid='.$menu_itemid.'&Status='.$eStatus.'" class="gen-btn small-btn">'.$statusLabel.'</a>';

	$rows[] = '<a href="menu_item_action.php?id='.$data_drv[$i]['iMenuItemId'].'&menu_itemid='.$menu_itemid.'&action=edit" class="gen-btn small-btn">'.$langage_lbl['LBL_DRIVER_EDIT'].'</a>';

	$rows[] = '<form name="delete_form_'.$data_drv[$i]['iMenuItemId'].'" id="delete_form_'.$data_drv[$i]['iMenuItemId'].'" method="post" action="" class="margin0"><input type="hidden" name="hdn_del_id" id="hdn_del_id" value="'.$data_drv[$i]['iMenuItemId'].'"><input type="hidden" name="menu_itemid" id="menu_itemid" value="'.$menu_itemid.'"><input type="hidden" name="action" id="action" value="delete"><button type="button" class="gen-btn small-btn" onClick="confirm_delete('.$data_drv[$i]['iMenuItemId'].');"><i class="icon-remove icon-white"></i> '.$langage_lbl['LBL_DRIVER_DELETE'].'</button></form>';
	
	$records[] = $rows;
}


$output = array(
	"draw"	=>	intval($_POST["draw"]),			
	"iTotalRecords"	=> 	$allRecords,
	"iTotalDisplayRecords"	=>  $allRecords,
	"data"	=> 	$records
);

echo json_encode($output);
die;
?>