<?php
function cardOfCategory($parent = 0,$sub = 0,$vcat = [],$serviceType = '')
{
    global $tconfig,$iLanguageMasId,$booking_ids,$THEME_OBJ;
    $disabled = $checkbooking = "";
    $disabled = 'disabled';
    if ($serviceType == "Bidding"){
        $vcat['vCatName'] = $vcat['vTitle'];
        $img = $tconfig["tsite_url"].'resizeImg.php?h=75&src='.$tconfig["tsite_upload_images_bidding"].'/'.$vcat['vImage'];
        $vehicle_category_page = $tconfig["tsite_url_main_admin"]."bidding_master_category_action.php?id=".$vcat['iBiddingId']."&homepage=1";
        $adminurl = $tconfig["tsite_url_main_admin"].'servicebid_content_action.php?iVehicleCategoryId='.$vcat['iBiddingId'].'&id=1';
    }else{
        if (in_array($vcat['iVehicleCategoryId'],$booking_ids)){
            $checkbooking = "checked";
        }
        $img = $tconfig["tsite_url"].'resizeImg.php?h=75&src='.$tconfig["tsite_upload_home_page_service_images"].'/'.$vcat['vHomepageLogoOurServices'];
        $vehicle_category_page = $tconfig["tsite_url_main_admin"]."vehicle_category_action.php?id=".$vcat['iVehicleCategoryId']."&homepage=1";
        $adminurl = $tconfig["tsite_url_main_admin"].$vcat['adminurl']."&id=".$iLanguageMasId;
        if(isset($vcat['adminvideoconsulturl'])){
            $videoConsultUrl = $tconfig["tsite_url_main_admin"].$vcat['adminvideoconsulturl']."&id=".$iLanguageMasId;
        }
    }
    $isShowEditVideoInnerPageBtn = $isShowEditServiceInnerPageBtn = 0;
    if ($THEME_OBJ->isProSPThemeActive() == "Yes"){
        if ($vcat['eSubVideoConsultEnable'] == 'Yes' && $serviceType == "VideoConsulting"){
            $isShowEditVideoInnerPageBtn = 1;
        }else{
            $isShowEditServiceInnerPageBtn = 1;
        }
    }else{
        $isShowEditServiceInnerPageBtn = 1;
        if ($vcat['eSubVideoConsultEnable'] == 'Yes'){
            $isShowEditVideoInnerPageBtn = 1;
        }
    }
    $data = '';
    $data .= ' <li>
        <div class="toggle-list-inner">
            <div class="toggle-combo">
                <label>
                    <div align="center">
                        <img src="'.$img.'" >
                    </div>
                    <div style="margin: 0 0 0 15px;">
                        <td>'.$vcat['vCatName'].'</td>
                    </div>
                </label>
                   <span style="display: none" onclick="showAlert()" class="toggle-switch">
                        <input style="z-index: -1;" type="checkbox"                                             
                            id="statusbutton"
                            class="chk"
                            name="statusbutton"
                            value="1" '.$checkbooking.' '.$disabled.'>
                        <span class="toggle-base"></span>
                   </span>
            </div>
            <div class="check-combo">
                <label id="defaultText_246">
                    <ul>
                        <li class="entypo-twitter"
                            data-network="twitter">
                            <a target="_blank" href="'.$vehicle_category_page.'"
                               data-toggle="tooltip"
                               title="Edit">
                                <img src="img/edit-new.png"
                                     alt="Edit">
                            </a>
                        </li>';
    if ($isShowEditServiceInnerPageBtn == 1){
        $data .= '<li class="entypo-twitter"
                            data-network="twitter">
                            <a target="_blank" href="'.$adminurl.'"
                               data-toggle="tooltip"
                               title="Edit Inner Page">
                                <img src="img/edit-doc.png"
                                     alt="Edit">
                            </a>
                        </li>';
    }
    if ($isShowEditVideoInnerPageBtn == 1){
        $data .= '<li class="entypo-twitter"
                        data-network="twitter">
                        <a  target="_blank" href="'.$videoConsultUrl.'"
                           data-toggle="tooltip"
                           title="Edit Video Consult Page">
                            <img src="img/live-line.png"
                                 alt="Edit">
                                

                        </a>
                    </li>';
    }
    $data .= '</ul>
                    <div class="medical-service-note">
                    </div>
                </label>
            </div>
        </div>
      </li>';
    return $data;
}

function masterService()
{
    global $parentCat,$booking_ids,$morebooking_ids;
    if (isset($parentCat) && !empty($parentCat)){
        $medicalServiceHtml = "";
        foreach ($parentCat as $cat){
            $display_order = $cat['iDisplayOrderHomepage'];
            $checked = $checked1 = '';
            if (in_array($cat['iVehicleCategoryId'],$booking_ids)){
                $checked = "checked";
            }
            if (in_array($cat['iVehicleCategoryId'],$morebooking_ids)){
                $checked1 = "checked";
            }
            $select_options = "";
            for ($i = 1;$i <= scount($parentCat);$i++){
                $select_options .= '<option value="'.$cat['iVehicleCategoryId'].'-'.$i.'" '.($i == $display_order?'selected':'').'>'.$i.'</option>';
            }
            $medicalServiceHtml .= '<tr><td>'.$cat['vCatName'].'</td><td><select class="form-control  " name="ms_display_order[]" >'.$select_options.'</select></td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryId[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked.' /></div></div></td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryIdMore[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked1.' /></div></div></td>';
        }
        echo $medicalServiceHtml;
        exit;
    }
}

function subService()
{
    global $subCat,$booking_ids,$morebooking_ids;
    if (isset($subCat) && !empty($subCat)){
        $medicalServiceHtml = "";
        foreach ($subCat as $cat){
            $display_order = $cat['iDisplayOrderHomepage'];
            $checked = $checked1 = '';
            if (in_array($cat['iVehicleCategoryId'],$booking_ids)){
                $checked = "checked";
            }
            if (in_array($cat['iVehicleCategoryId'],$morebooking_ids)){
                $checked1 = "checked";
            }
            $select_options = "";
            for ($i = 1;$i <= scount($subCat);$i++){
                $select_options .= '<option value="'.$cat['iVehicleCategoryId'].'-'.$i.'" '.($i == $display_order?'selected':'').'>'.$i.'</option>';
            }
            $medicalServiceHtml .= '<tr><td>'.$cat['vCatName'].'</td><td><select class="form-control display-order" name="ms_display_order[]" >'.$select_options.'</select></td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryId[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked.' /></div></div></td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryIdMore[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked1.' /></div></div></td>';
        }
        echo $medicalServiceHtml;
        exit;
    }
}

function AllService()
{
    global $parentCat,$subCat,$booking_ids,$morebooking_ids;

    $medicalServiceHtml = "";
    // Combine $parentCat and $subCat into $combinedCat
    if (isset($parentCat) && !empty($parentCat) && isset($subCat) && !empty($subCat)){
        $combinedCat = array_merge($parentCat,$subCat);
    }elseif (isset($parentCat) && !empty($parentCat)){
        $combinedCat = $parentCat;
    }elseif (isset($subCat) && !empty($subCat)){
        $combinedCat = $subCat;
    }
    if (!empty($combinedCat)){

        $j = 1;
        $js = 1;
        foreach ($combinedCat as $cat){
            //$display_order = $cat['iDisplayOrderHomepage'];
            $display_order = $j;
            $display_order_sub = $js;
            $checked = '';
            if (in_array($cat['iVehicleCategoryId'],$booking_ids)){
                $checked = "checked";
            }
            if (in_array($cat['iVehicleCategoryId'],$morebooking_ids)){
                $checked1 = "checked";
            }

            $select_options = '';
            for ($i = 1;$i <= scount($combinedCat);$i++){
                $select_options .= '<option value="'.$cat['iVehicleCategoryId'].'-'.$i.'" '.($i == $display_order?'selected':'').'>'.$i.'</option>';
            }
            $orderDisplay = '<select onfocus="capturePrevValue(this)" onchange = "change_category_order(this,'.$display_order.')"  class="form-control" name="ms_display_order[]" >'.$select_options.'</select>';

            $j++;

            /* $select_options = "";$select_options_forSubCategory = "";
             if($checked == "checked"){
                 for ($i = 1;$i <= scount($parentCat);$i++){
                     $select_options .= '<option value="'.$cat['iVehicleCategoryId'].'-'.$i.'" '.($i == $display_order?'selected':'').'>'.$i.'</option>';
                 }
                 $orderDisplay = '<select onchange = "change_category_order(this,'.$display_order.')"  class="form-control" name="ms_display_order[]" >'.$select_options.'</select>';

                 $j++;


             }else{

                 for ($is = 1;$is <= scount($subCat);$is++){
                     $select_options_forSubCategory .= '<option value="'.$cat['iVehicleCategoryId'].'-'.$is.'" '.($is == $display_order_sub?'selected':'').'>'.$is.'</option>';
                 }

                 $orderDisplay = '<select onchange = "change_category_order(this,'.$display_order_sub.')"  class="form-control" name="ms_display_order[]" >'.$select_options_forSubCategory.'</select>';
                 $js++;
             }*/

            $medicalServiceHtml .= '<tr><td>'.$cat['vCatName'].'</td><td style = "width:20%" >'.$orderDisplay.'
                </td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryId[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked.' /></div></div></td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryIdMore[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked1.' /></div></div></td>';

        }
    }
    echo $medicalServiceHtml;
    exit;
}

function vehicleCategoryDisplayTOHomePage()
{

    global $tbl_name,$obj,$vCode,$booking_ids,$morebooking_ids;
    $sql_vehicle_category_table_name = getVehicleCategoryTblName();
    $iVehicleCategoryIdArr = $_POST['iVehicleCategoryIdArr']??'';
    $iVehicleCategoryIdRemoveArr = $_POST['iVehicleCategoryIdRemoveArr']??'';
    $iVehicleCategoryIdMoreArr = $_POST['iVehicleCategoryIdMoreArr']??'';
    $iVehicleCategoryIdMoreRemoveArr = $_POST['iVehicleCategoryIdMoreRemoveArr']??'';
    $iDisplayOrderArr = $_POST['iDisplayOrderArr']??'';
    if (isset($iVehicleCategoryIdRemoveArr) && !empty($iVehicleCategoryIdRemoveArr)){
        $iVehicleCategoryIdRemoveArr = explode(',',$iVehicleCategoryIdRemoveArr);
        $booking_ids = array_diff($booking_ids,$iVehicleCategoryIdRemoveArr);
    }
    if (!empty($iVehicleCategoryIdArr)){
        $iVehicleCategoryIdArr = explode(',',$iVehicleCategoryIdArr);
        $booking_ids = array_unique(array_merge($booking_ids,$iVehicleCategoryIdArr),SORT_REGULAR);
    }
    $booking_ids = implode(',',array_filter($booking_ids));
    if (isset($iVehicleCategoryIdMoreRemoveArr) && !empty($iVehicleCategoryIdMoreRemoveArr)){
        $iVehicleCategoryIdMoreRemoveArr = explode(',',$iVehicleCategoryIdMoreRemoveArr);
        $morebooking_ids = array_diff($morebooking_ids,$iVehicleCategoryIdMoreRemoveArr);
    }
    if (!empty($iVehicleCategoryIdMoreArr)){
        $iVehicleCategoryIdMoreArr = explode(',',$iVehicleCategoryIdMoreArr);
        $morebooking_ids = array_unique(array_merge($morebooking_ids,$iVehicleCategoryIdMoreArr),SORT_REGULAR);
    }
    $morebooking_ids = implode(',',array_filter($morebooking_ids));
    $where = " vCode = '".$vCode."'";
    $Update['booking_ids'] = $booking_ids;
    $Update['morebooking_ids'] = $morebooking_ids;
    $obj->MySQLQueryPerform($tbl_name,$Update,'update',$where);
    if (!empty($iDisplayOrderArr)){
        $iDisplayOrderArr = explode(',',$iDisplayOrderArr);
        $query = "UPDATE $sql_vehicle_category_table_name SET iDisplayOrderHomepage = (CASE iVehicleCategoryId  ";
        $ids = [];
        foreach ($iDisplayOrderArr as $iDisplayOrder){
            $data = explode('-',$iDisplayOrder);
            $id = $data[0];
            $w = $data[1];
            if (isset($id) && !empty($id)){
                $query .= "WHEN $id THEN $w ";
                $ids[] = $id;
            }
        }
        $ids = implode(',',$ids);
        $query .= "END) WHERE iVehicleCategoryId  IN({$ids});";
        $obj->sql_query($query);
    }
    $arrReturn['active'] = '1';
    echo json_encode($arrReturn);
    exit();
}

function AllTaxiService()
{
    global $TaxiServiceArr,$obj;
    $table_name = getContentCMSHomeTable();
    $TaxiServiceHtml = "";
    if (!empty($TaxiServiceArr)){
        foreach ($TaxiServiceArr as $cat){
            $checked = '';
            $sql1 = "SELECT eShowHomePage FROM $table_name WHERE iVehicleCategoryId = '" . $cat['iVehicleCategoryId'] . "' AND eStatus = 'Active'";
            $db_data = $obj->MySQLSelect($sql1);
            if ($db_data[0]['eShowHomePage'] == 'Yes'){
                $checked = "checked";
            }
            $TaxiServiceHtml .= '<tr><td>'.$cat['vCatName'].'</td><td><div class="meds-action"><div class="make-switch" data-on="success" data-off="warning"><input type="checkbox" name="iVehicleCategoryId[]" value="'.$cat['iVehicleCategoryId'].'" '.$checked.' /></div></div></td></tr>';
        }
    }
    echo $TaxiServiceHtml;
    exit;
}

function TaxiServicesDisplayToHomePageForCubex()
{

    global $obj;
    $table_name = getContentCMSHomeTable();
    $iVehicleCategoryIdArr = $_POST['iVehicleCategoryIdArr']??'';
    $iVehicleCategoryIdRemoveArr = $_POST['iVehicleCategoryIdRemoveArr']??'';
    if (isset($iVehicleCategoryIdRemoveArr) && !empty($iVehicleCategoryIdRemoveArr)){
        //$iVehicleCategoryIdRemoveArr = explode(',',$iVehicleCategoryIdRemoveArr);
        $Update1 = array();
        $where1 = " iVehicleCategoryId IN (".$iVehicleCategoryIdRemoveArr.")";
        $Update1['eShowHomePage'] = 'No';
        $obj->MySQLQueryPerform($table_name,$Update1,'update',$where1);
    }
    if (!empty($iVehicleCategoryIdArr)){
        //$iVehicleCategoryIdArr = explode(',',$iVehicleCategoryIdArr);
        $Update = array();
        $where = " iVehicleCategoryId IN (".$iVehicleCategoryIdArr.")";
        $Update['eShowHomePage'] = 'Yes';
        $obj->MySQLQueryPerform($table_name,$Update,'update',$where);
    }
   
    $arrReturn['active'] = '1';
    echo json_encode($arrReturn);
    exit();
}


?>