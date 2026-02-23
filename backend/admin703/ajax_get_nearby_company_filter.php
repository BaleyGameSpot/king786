<?php
include_once('../common.php');
$iServiceId = isset($_REQUEST['iServiceIdNew']) ? $_REQUEST['iServiceIdNew'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$module = isset($_REQUEST['module']) ? $_REQUEST['module'] : '';
$selected_company_id = isset($_REQUEST['selected_company_id']) ? $_REQUEST['selected_company_id'] : ''; // for the nearby places
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : ''; // for the nearby places
$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : ''; // for the nearby places
if (!empty($selected_company_id)) {
    $selected_company_id = explode(',', $selected_company_id);
}
$ssql_LIKE = '';
if ($term != '') {
    $ssql_LIKE .= "  AND (c.vCompany LIKE '%" . $term . "%' OR c.vEmail LIKE '%" . $term . "%' OR CONCAT(c.vCode,'',c.vPhone) LIKE '%" . $term . "%' OR CONCAT(c.vCode,'-',c.vPhone) LIKE '%" . $term . "%' )";
}
$ssql = " FIND_IN_SET('" . $iServiceId . "', c.iServiceId)";
if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
    $ssql = " ( FIND_IN_SET('" . $iServiceId . "', c.iServiceId) OR (FIND_IN_SET('" . $iServiceId . "', c.iServiceIdMulti) AND c.iServiceIdMulti != '')) ";
}
if ($module == 'NearBy') {
    $ssql .= " AND c.eStatus != 'Inactive'";
}
if ($type == 'getCompanyDetails') {
    $ssql = "  c.iCompanyId = '" . $iCompanyId . "'";
}
$store_option_html = '';
if (!empty($iServiceId)) {
    $sql = "SELECT 
            c.vCompany AS fullName,CONCAT(c.vCode,'- ',c.vPhone) AS Phoneno,c.iCompanyId as id,
            c.vMonFromSlot1, c.vMonToSlot1, c.vTueFromSlot1, c.vTueToSlot1, c.vWedFromSlot1, c.vWedToSlot1, c.vThuFromSlot1, c.vThuToSlot1, c.vFriFromSlot1, c.vFriToSlot1, c.vSatFromSlot1, c.vSatToSlot1, c.vSunFromSlot1, c.vSunToSlot1,
            c.vMonFromSlot2, c.vMonToSlot2, c.vTueFromSlot2, c.vTueToSlot2, c.vWedFromSlot2, c.vWedToSlot2, c.vThuFromSlot2, c.vThuToSlot2, c.vFriFromSlot2, c.vFriToSlot2, c.vSatFromSlot2, c.vSatToSlot2, c.vSunFromSlot2, c.vSunToSlot2,c.vFromMonFriTimeSlot1,c.vToMonFriTimeSlot1,c.vFromMonFriTimeSlot2,c.vToMonFriTimeSlot2,c.vFromSatSunTimeSlot1,c.vToSatSunTimeSlot1,c.vFromSatSunTimeSlot2,c.vToSatSunTimeSlot2,c.iCompanyId,c.vCompany,c.iServiceId,c.vEmail FROM `company` AS c LEFT JOIN food_menu AS f ON c.iCompanyId = f.iCompanyId WHERE  c.vPhone != '' $ssql_LIKE AND  $ssql  AND c.eStatus!='Deleted' $ssql1 GROUP BY c.iCompanyId ORDER BY `vCompany`";
    $db_company = $obj->MySQLSelect($sql);
    $store_option_html .= "<option value=''>Select " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . "</option>";
    if (scount($db_company) > 0) {
        for ($i = 0; $i < scount($db_company); $i++) {
            $selected = '';
            if ($db_company[$i]['iCompanyId'] == $iCompanyId) {
                $selected = "selected=selected";
            }
            $disabled = '';
            if (in_array($db_company[$i]['iCompanyId'], $selected_company_id)) {
                $disabled = 'disabled';
            }
            if ($db_company[$i]['vEmail'] != '') {
                $store_option_html .= "<option value=" . $db_company[$i]['iCompanyId'] . " " . $disabled . " " . $selected . ">" . clearName($db_company[$i]['vCompany']) . " - ( " . clearEmail($db_company[$i]['vEmail']) . ") </option>";
            } else {
                $store_option_html .= "<option value=" . $db_company[$i]['iCompanyId'] . " " . $disabled . " " . $selected . ">" . clearName($db_company[$i]['vCompany']) . " </option>";
            }
        }
    }
    
    if (scount($db_company) > 0 && !empty($db_company)) {
        foreach ($db_company as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && clearCmpName($val) != '') {
                    $db_company[$key][$k] = clearCmpName($val);
                }
                if ($k == 'vEmail' && $val != '') {
                    $db_company[$key][$k] = clearEmail($val);
                }
                if ($k == 'Phoneno' && $val != '') {
                    $db_company[$key][$k] = clearPhone($val);
                }
                if ($k == 'Phoneno' && $val == '-') {
                    $db_company[$key][$k] = '';
                }
                $db_company[$key]['total_count'] = scount($countdata);
            }
        }

        $arr['db_company'] = $db_company;
    } else {


        $emptydata[0]['Phoneno'] = '';
        $emptydata[0]['fullName'] = '';
        $emptydata[0]['id'] = '';
        $emptydata[0]['total_count'] = '';
        $emptydata[0]['vEmail'] = '';
        $emptydata[0]['total_count'] = '';
        $arr['db_company'] = $emptydata;
    }
} else {
    $store_option_html .= "<option value=''>Select " . $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'] . "</option>";
    exit;
}
$arr['store_option_html'] = $store_option_html;
/*---------------------  ------------------*/
unset($db_company[0]['iCompanyId'], $db_company[0]['iServiceId']);
$compnaydata = [];
foreach ($db_company[0] as $key => $value) {
    $compnaydata[$key] = date("h:i A", strtotime($value));
}
$arr['company_data'] = $compnaydata;


echo json_encode($arr);
exit;
?>
