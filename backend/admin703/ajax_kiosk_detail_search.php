<?php

include_once("../common.php");

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';
$usertype = isset($_REQUEST['usertype']) ? $_REQUEST['usertype'] : 'Kiosk';
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
}

$resultCount = 10;
$end = ($page - 1) * $resultCount;
$start = $end + $resultCount;
$rdr_ssql = $cSql = $driveridarr = $useridarr = "";
if (SITE_TYPE == 'Demo') {
    $rdr_ssql = " And a.tRegistrationDate > '" . WEEK_DATE . "'";
}
$rdr_ssql .= " And a.iGroupId = '4'";
// if ($company_id != '') {
//     $cSql = " AND iCompanyId = '" . $company_id . "'";
// }
// if ($iServiceId != '') {
//     $srSql = " AND iServiceId = '" . $iServiceId . "'";
// }
if ($usertype == 'Kiosk') {
    // if ($searchDriverHotel != '') {
    //     $driveridarr = " AND iDriverId IN($searchDriverHotel)";
    // }
    if ($id != '') {
        $sql = "SELECT iAdminId as id,CONCAT(vFirstName,' ',vLastName) AS fullName,vEmail,CONCAT(vCode,'-',vContactNo) AS Phoneno from administrators WHERE eStatus != 'Deleted' AND iAdminId = '" . $id . "' $cSql $rdr_ssql $driveridarr order by vName";
        $db_kiosk = $obj->MySQLSelect($sql);
        if (!empty($db_kiosk)) {
            $db_kiosk[0]['fullName'] = clearName($db_kiosk[0]['fullName']);
            $db_kiosk[0]['vEmail'] = clearEmail($db_kiosk[0]['vEmail']);
            $db_kiosk[0]['Phoneno'] = clearPhone($db_kiosk[0]['Phoneno']);
            echo json_encode($db_kiosk);
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
} else {
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