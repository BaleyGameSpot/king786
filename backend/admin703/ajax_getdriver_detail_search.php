<?php

include_once("../common.php");

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
$usertype = isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : 'Driver';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '';
$iServiceId = isset($_REQUEST['selectedserviceId']) ? $_REQUEST['selectedserviceId'] : '';
$searchDriverHotel = isset($_REQUEST['searchDriverHotel']) ? $_REQUEST['searchDriverHotel'] : '';
$searchRiderHotel = isset($_REQUEST['searchRiderHotel']) ? $_REQUEST['searchRiderHotel'] : '';
$trackingCompany = isset($_REQUEST['trackingCompany']) ? $_REQUEST['trackingCompany'] : '';
if (isset($trackingCompany) && !empty($trackingCompany) && $trackingCompany == 1) {
    if ($usertype == 'Rider') {
        $usertype = 'TrackServiceRider';
    }
    if ($usertype == 'Driver') {
        $usertype = 'TrackServiceDriver';
    }
    if ($usertype == 'Kiosk') {
        $usertype = 'TrackServiceKiosk';
    }
}

$resultCount = 10;
$end = ($page - 1) * $resultCount;
$start = $end + $resultCount;
$rdr_ssql = $cSql = $driveridarr = $useridarr = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}
if ($company_id != '') {
    $cSql = " AND iCompanyId = '" . $company_id . "'";
}
if ($iServiceId != '') {
    $srSql = " AND iServiceId = '" . $iServiceId . "'";
}
if ($usertype == 'Driver') {
    if ($searchDriverHotel != '') {
        $driveridarr = " AND iDriverId IN($searchDriverHotel)";
    }
    if ($id != '') {
        $sql = "SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND iDriverId = '" . $id . "' $cSql $rdr_ssql $driveridarr order by vName";
        $db_drivers = $obj->MySQLSelect($sql);
        if (!empty($db_drivers)) {
            $db_drivers[0]['fullName'] = clearName($db_drivers[0]['fullName']);
            $db_drivers[0]['vEmail'] = clearEmail($db_drivers[0]['vEmail']);
            $db_drivers[0]['Phoneno'] = clearPhone($db_drivers[0]['Phoneno']);
            echo json_encode($db_drivers);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND iTrackServiceCompanyId = 0 AND (CONCAT(vName,' ',vLastName) LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%') $cSql $rdr_ssql $driveridarr order by vName LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND iTrackServiceCompanyId = 0 $cSql $rdr_ssql $driveridarr order by vName LIMIT {$end},{$start}";
        }
        $db_drivers = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND iTrackServiceCompanyId = 0 AND (CONCAT(vName,' ',vLastName) LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%') $cSql $rdr_ssql $driveridarr");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND iTrackServiceCompanyId = 0 $cSql $rdr_ssql $driveridarr");
        }
        foreach ($db_drivers as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
                    $db_drivers[$key][$k] = clearName($val);
                }
                if ($k == 'vEmail' && $val != '') {
                    $db_drivers[$key][$k] = clearEmail($val);
                }
                if ($k == 'Phoneno' && $val != '') {
                    $db_drivers[$key][$k] = clearPhone($val);
                }
                if ($k == 'Phoneno' && $val == '-') {
                    $db_drivers[$key][$k] = '';
                }
                $db_drivers[$key]['total_count'] = scount($countdata);
            }
        }
        if (!empty($db_drivers)) {
            //print_r($db_drivers);die;
            echo json_encode($db_drivers);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else if ($usertype == 'Company') {
    if ($id != '') {
        $sql = "SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND eSystem = 'General' AND iCompanyId = '" . $id . "' AND eBuyAnyService = 'No' order by vCompany";
        $db_company = $obj->MySQLSelect($sql);
        if (!empty($db_company)) {
            $db_company[0]['fullName'] = clearName($db_company[0]['fullName']);
            $db_company[0]['vEmail'] = clearEmail($db_company[0]['vEmail']);
            $db_company[0]['Phoneno'] = clearPhone($db_company[0]['Phoneno']);
            echo json_encode($db_company);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND eSystem = 'General' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' ) AND eBuyAnyService = 'No' order by vCompany LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND eSystem = 'General' AND eBuyAnyService = 'No' order by vCompany LIMIT {$end},{$start}";
        }
        $db_company = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND eSystem = 'General' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' ) AND eBuyAnyService = 'No'");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND eSystem = 'General' AND eBuyAnyService = 'No'");
        }
        foreach ($db_company as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
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
        if (!empty($db_company)) {
            echo json_encode($db_company);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else if ($usertype == 'Store') {
    $eSystem = " AND eSystem = 'DeliverAll'";
    $ssqlsc = " AND iServiceId IN(" . $enablesevicescategory . ")";
    if ($id != '') {
        $sql = "SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND iCompanyId = '" . $id . "' $eSystem $ssqlsc $srSql AND eBuyAnyService = 'No' order by vCompany";
        $db_company = $obj->MySQLSelect($sql);
        if (!empty($db_company)) {
            echo json_encode($db_company);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' )  $eSystem $ssqlsc $srSql AND eBuyAnyService = 'No' order by vCompany LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' $eSystem $ssqlsc $srSql AND eBuyAnyService = 'No' order by vCompany LIMIT {$end},{$start}";
        }
        $db_company = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' )  $eSystem $ssqlsc $srSql AND eBuyAnyService = 'No'");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from company WHERE eStatus != 'Deleted' $eSystem $ssqlsc $srSql AND eBuyAnyService = 'No' order by vCompany ");
        }
        foreach ($db_company as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
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
        if (!empty($db_company)) {
            echo json_encode($db_company);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else if ($usertype == 'TrackServiceRider') {
    if ($id != '') {
        $sql = "SELECT iTrackServiceUserId as id,CONCAT(vName,'- ',vLastName) AS fullName,vEmail,CONCAT(vPhoneCode,'- ',vPhone) AS Phoneno FROM track_service_users WHERE eStatus != 'Deleted' AND iTrackServiceUserId = '" . $id . "' ORDER BY vName";
        $db_company = $obj->MySQLSelect($sql);

        if (!empty($db_company)) {
            $db_company[0]['fullName'] = clearName($db_company[0]['fullName']);
            $db_company[0]['vEmail'] = clearEmail($db_company[0]['vEmail']);
            $db_company[0]['Phoneno'] = clearPhone($db_company[0]['Phoneno']);
            echo json_encode($db_company);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iTrackServiceUserId as id,CONCAT(vName,'- ',vLastName) AS fullName,vEmail,CONCAT(vPhoneCode,'- ',vPhone) AS Phoneno FROM track_service_users WHERE eStatus != 'Deleted' AND (vName LIKE '%" . $term . "%' OR vLastName LIKE '%" . $term . "%' OR CONCAT(vPhoneCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vPhoneCode,'-',vPhone) LIKE '%" . $term . "%' ) ORDER BY vName LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iTrackServiceUserId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vPhoneCode,'- ',vPhone) AS Phoneno FROM track_service_users WHERE eStatus != 'Deleted' ORDER BY vName LIMIT {$end},{$start}";
        }
        $db_company = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iTrackServiceUserId as id,vName AS fullName,vEmail,CONCAT(vPhoneCode,'- ',vPhone) AS Phoneno FROM track_service_users WHERE eStatus != 'Deleted'  AND (vName LIKE '%" . $term . "%' OR vLastName LIKE '%" . $term . "%' OR CONCAT(vPhoneCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vPhoneCode,'-',vPhone) LIKE '%" . $term . "%' ) ");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iTrackServiceUserId as id,vName AS fullName,vEmail,CONCAT(vPhoneCode,'- ',vPhone) AS Phoneno FROM track_service_users WHERE eStatus != 'Deleted' ");
        }
        foreach ($db_company as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
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
        if (!empty($db_company)) {
            echo json_encode($db_company);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else if ($usertype == 'TrackServiceCompany') {
    if ($id != '') {
        $sql = "SELECT iTrackServiceCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno FROM track_service_company WHERE eStatus != 'Deleted' AND iTrackServiceCompanyId = '" . $id . "' ORDER BY vCompany";
        $db_company = $obj->MySQLSelect($sql);
        if (!empty($db_company)) {
            $db_company[0]['fullName'] = clearName($db_company[0]['fullName']);
            $db_company[0]['vEmail'] = clearEmail($db_company[0]['vEmail']);
            $db_company[0]['Phoneno'] = clearPhone($db_company[0]['Phoneno']);
            echo json_encode($db_company);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iTrackServiceCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno FROM track_service_company WHERE eStatus != 'Deleted' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' ) ORDER BY vCompany LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iTrackServiceCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno FROM track_service_company WHERE eStatus != 'Deleted' ORDER BY vCompany LIMIT {$end},{$start}";
        }
        $db_company = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iTrackServiceCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno FROM track_service_company WHERE eStatus != 'Deleted' AND eSystem = 'General' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' ) ");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iTrackServiceCompanyId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno FROM track_service_company WHERE eStatus != 'Deleted' ");
        }
        foreach ($db_company as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
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
        if (!empty($db_company)) {
            echo json_encode($db_company);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else if ($usertype == 'TrackServiceDriver') {
    if ($searchDriverHotel != '') {
        $driveridarr = " AND iDriverId IN($searchDriverHotel)";
    }
    $cSql = " AND iTrackServiceCompanyId > 0 ";
    if ($company_id != '') {
        $cSql .= " AND iTrackServiceCompanyId = '" . $company_id . "'";
    }
    if ($id != '') {
        $sql = "SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND iDriverId = '" . $id . "' $cSql $rdr_ssql $driveridarr order by vName";
        $db_drivers = $obj->MySQLSelect($sql);
        if (!empty($db_drivers)) {
            $db_drivers[0]['fullName'] = clearName($db_drivers[0]['fullName']);
            $db_drivers[0]['vEmail'] = clearEmail($db_drivers[0]['vEmail']);
            $db_drivers[0]['Phoneno'] = clearPhone($db_drivers[0]['Phoneno']);
            echo json_encode($db_drivers);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND (CONCAT(vName,' ',vLastName) LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%') $cSql $rdr_ssql $driveridarr order by vName LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' $cSql $rdr_ssql $driveridarr order by vName LIMIT {$end},{$start}";
        }
        $db_drivers = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' AND (CONCAT(vName,' ',vLastName) LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%') $cSql $rdr_ssql $driveridarr");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iDriverId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vPhone) AS Phoneno from register_driver WHERE eStatus != 'Deleted' $cSql $rdr_ssql $driveridarr");
        }
        foreach ($db_drivers as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
                    $db_drivers[$key][$k] = clearName($val);
                }
                if ($k == 'vEmail' && $val != '') {
                    $db_drivers[$key][$k] = clearEmail($val);
                }
                if ($k == 'Phoneno' && $val != '') {
                    $db_drivers[$key][$k] = clearPhone($val);
                }
                if ($k == 'Phoneno' && $val == '-') {
                    $db_drivers[$key][$k] = '';
                }
                $db_drivers[$key]['total_count'] = scount($countdata);
            }
        }
        if (!empty($db_drivers)) {
            echo json_encode($db_drivers);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else if ($usertype == 'Organization') {
    if ($id != '') {
        $sql = "SELECT iOrganizationId  as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from organization WHERE eStatus != 'Deleted' AND iOrganizationId  = '" . $id . "' order by vCompany";
        $db_organization = $obj->MySQLSelect($sql);
        if (!empty($db_organization)) {
            $db_organization[0]['fullName'] = clearName($db_organization[0]['fullName']);
            $db_organization[0]['vEmail'] = clearEmail($db_organization[0]['vEmail']);
            $db_organization[0]['Phoneno'] = clearPhone($db_organization[0]['Phoneno']);
            echo json_encode($db_organization);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iOrganizationId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from organization WHERE eStatus != 'Deleted' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' )  order by vCompany LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iOrganizationId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from organization WHERE eStatus != 'Deleted' order by vCompany LIMIT {$end},{$start}";
        }
        $db_organization = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iOrganizationId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from organization WHERE eStatus != 'Deleted' AND (vCompany LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vCode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vCode,'-',vPhone) LIKE '%" . $term . "%' )");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iOrganizationId as id,vCompany AS fullName,vEmail,CONCAT(vCode,'- ',vPhone) AS Phoneno from organization WHERE eStatus != 'Deleted'");
        }
        foreach ($db_organization as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
                    $db_organization[$key][$k] = clearCmpName($val);
                }
                if ($k == 'vEmail' && $val != '') {
                    $db_organization[$key][$k] = clearEmail($val);
                }
                if ($k == 'Phoneno' && $val != '') {
                    $db_organization[$key][$k] = clearPhone($val);
                }
                if ($k == 'Phoneno' && $val == '-') {
                    $db_organization[$key][$k] = '';
                }
                $db_organization[$key]['total_count'] = scount($countdata);
            }
        }
        if (!empty($db_organization)) {
            echo json_encode($db_organization);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
} else if ($usertype == 'Kiosk') {

    if ($id != '') {
        $sql = "SELECT h.iHotelId as id,CONCAT(a.vFirstName,' ',a.vLastName) AS fullName,a.vEmail,CONCAT(a.vCode,'-',a.vContactNo) AS Phoneno from administrators as a LEFT JOIN hotel as h ON a.iAdminId = h.iAdminId WHERE a.eStatus != 'Deleted' AND h.iHotelId = '" . $id . "' $cSql $rdr_ssql $driveridarr order by vFirstName";
        $db_drivers = $obj->MySQLSelect($sql);
        if (!empty($db_drivers)) {
            $db_drivers[0]['fullName'] = clearName($db_drivers[0]['fullName']);
            $db_drivers[0]['vEmail'] = clearEmail($db_drivers[0]['vEmail']);
            $db_drivers[0]['Phoneno'] = clearPhone($db_drivers[0]['Phoneno']);
            echo json_encode($db_drivers);
            exit;
        }
    }
    else {
         if ($term != '') {
            $sql = "SELECT h.iHotelId as id,CONCAT(a.vFirstName,' ',a.vLastName) AS fullName,a.vEmail,CONCAT(a.vCode,'-',a.vContactNo) AS Phoneno from administrators as a LEFT JOIN hotel as h ON a.iAdminId = h.iAdminId WHERE a.eStatus != 'Deleted'  AND (CONCAT(a.vFirstName,' ',a.vLastName) LIKE '%" . $term . "%' OR a.vEmail LIKE '%" . $term . "%' OR CONCAT(a.vCode,'',a.vContactNo) LIKE '%" . $term . "%' OR CONCAT(a.vCode,'-',a.vContactNo) LIKE '%" . $term . "%') $cSql $rdr_ssql $driveridarr order by vFirstName LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT h.iHotelId as id,CONCAT(a.vFirstName,' ',a.vLastName) AS fullName,a.vEmail,CONCAT(a.vCode,'-',a.vContactNo) AS Phoneno from administrators as a LEFT JOIN hotel as h ON a.iAdminId = h.iAdminId WHERE a.eStatus != 'Deleted'  $cSql $rdr_ssql $driveridarr order by vFirstName LIMIT {$end},{$start}";
        }
        $db_kiosk = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT h.iHotelId as id,CONCAT(a.vFirstName,' ',a.vLastName) AS fullName,a.vEmail,CONCAT(a.vCode,'-',a.vContactNo) AS Phoneno from administrators as a LEFT JOIN hotel as h ON a.iAdminId = h.iAdminId WHERE a.eStatus != 'Deleted'  AND (CONCAT(a.vFirstName,' ',a.vLastName) LIKE '%" . $term . "%' OR a.vEmail LIKE '%" . $term . "%' OR CONCAT(a.vCode,'',a.vContactNo) LIKE '%" . $term . "%' OR CONCAT(a.vCode,'-',a.vContactNo) LIKE '%" . $term . "%') $cSql $rdr_ssql $driveridarr");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT h.iHotelId as id,CONCAT(a.vFirstName,' ',a.vLastName) AS fullName,a.vEmail,CONCAT(a.vCode,'-',a.vContactNo) AS Phoneno from administrators as a LEFT JOIN hotel as h ON a.iAdminId = h.iAdminId WHERE a.eStatus != 'Deleted'  $cSql $rdr_ssql $driveridarr");
        }
        foreach ($db_kiosk as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
                    $db_kiosk[$key][$k] = clearName($val);
                }
                if ($k == 'vEmail' && $val != '') {
                    $db_kiosk[$key][$k] = clearEmail($val);
                }
                if ($k == 'Phoneno' && $val != '') {
                    $db_kiosk[$key][$k] = clearPhone($val);
                }
                if ($k == 'Phoneno' && $val == '-') {
                    $db_kiosk[$key][$k] = '';
                }
                $db_kiosk[$key]['total_count'] = scount($countdata);
            }
        }
        if (!empty($db_kiosk)) {
            //print_r($db_kiosk);die;
            echo json_encode($db_kiosk);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
else {
    if ($searchRiderHotel != '') {
        $useridarr = " AND iUserId IN($searchRiderHotel)";
    }
    if ($id != '') {
        $sql = "SELECT iUserId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vPhonecode,'- ',vPhone) AS Phoneno from register_user WHERE eStatus != 'Deleted' AND (vEmail != '' OR vPhone != '')  AND eHail= 'No' AND iUserId = '" . $id . "' $rdr_ssql $useridarr order by vName";
        $db_rider = $obj->MySQLSelect($sql);
        if (!empty($db_rider)) {
            echo json_encode($db_rider);
            exit;
        }
    }
    else {
        if ($term != '') {
            $sql = "SELECT iUserId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vPhonecode,'- ',vPhone) AS Phoneno from register_user WHERE eStatus != 'Deleted' AND (vEmail != '' OR vPhone != '')  AND eHail= 'No' AND (CONCAT(vName,' ',vLastName) LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vPhonecode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vPhonecode,'-',vPhone) LIKE '%" . $term . "%' ) $rdr_ssql $useridarr order by vName LIMIT {$end},{$start}";
        }
        else {
            $sql = "SELECT iUserId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vPhonecode,'- ',vPhone) AS Phoneno from register_user WHERE eStatus != 'Deleted' AND (vEmail != '' OR vPhone != '')  AND eHail= 'No' $rdr_ssql $useridarr order by vName LIMIT {$end},{$start}";
        }
        $db_rider = $obj->MySQLSelect($sql);
        if ($term != '') {
            $countdata = $obj->MySQLSelect("SELECT iUserId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vPhonecode,'- ',vPhone) AS Phoneno from register_user WHERE eStatus != 'Deleted' AND (vEmail != '' OR vPhone != '')  AND eHail= 'No' AND (CONCAT(vName,' ',vLastName) LIKE '%" . $term . "%' OR vEmail LIKE '%" . $term . "%' OR CONCAT(vPhonecode,'',vPhone) LIKE '%" . $term . "%' OR CONCAT(vPhonecode,'-',vPhone) LIKE '%" . $term . "%' ) $rdr_ssql $useridarr order by vName ");
        }
        else {
            $countdata = $obj->MySQLSelect("SELECT iUserId as id,CONCAT(vName,' ',vLastName) AS fullName,vEmail,CONCAT(vPhonecode,'- ',vPhone) AS Phoneno from register_user WHERE eStatus != 'Deleted' AND (vEmail != '' OR vPhone != '')  AND eHail= 'No' $rdr_ssql $useridarr");
        }
        foreach ($db_rider as $key => $value) {
            if ($value && SITE_TYPE == "Demo") {
                $value = array_map('utf8_encode', $value);
            }
            foreach ($value as $k => $val) {
                if ($k == 'fullName' && $val != '') {
                    $db_rider[$key][$k] = clearName($val);
                }
                if ($k == 'vEmail' && $val != '') {
                    $db_rider[$key][$k] = clearEmail($val);
                }
                if ($k == 'Phoneno' && $val != '') {
                    $db_rider[$key][$k] = clearPhone($val);
                }
                if ($k == 'Phoneno' && $val == '-') {
                    $db_rider[$key][$k] = '';
                }
                $db_rider[$key]['total_count'] = scount($countdata);
            }
        }
        if (!empty($db_rider)) {
            echo json_encode($db_rider);
            exit;
        }
        else {
            $emptydata[0]['Phoneno'] = '';
            $emptydata[0]['fullName'] = '';
            $emptydata[0]['id'] = '';
            $emptydata[0]['total_count'] = '';
            $emptydata[0]['vEmail'] = '';
            $emptydata[0]['total_count'] = '';
            echo json_encode($emptydata);
            exit;
        }
    }
}
?>