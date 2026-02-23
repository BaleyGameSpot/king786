<?php
include_once('../common.php');
$baseURL = $tconfig["tsite_url"];
$sql_vehicle_category_table_name = getVehicleCategoryTblName();

$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
$order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : "";
$select_cat = isset($_REQUEST['selectcategory']) ? $_REQUEST['selectcategory'] : "";
$eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
$startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : "";
$endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : "";
$type = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : '';

$ssql = "";
// require('fpdf/fpdf.php');
// require('TCPDF-master/tcpdf.php'); // Added By Hasmukh
$date = new DateTime();
$timestamp_filename = $date->getTimestamp();
$default_lang = $LANG_OBJ->FetchSystemDefaultLang();
function change_key($array, $old_key, $new_key)
{
    if (!array_key_exists($old_key, $array)) {
        return $array;
    }
    $keys = array_keys($array);
    $keys[array_search($old_key, $keys)] = $new_key;
    return array_combine($keys, $array);
}

function cleanData(&$str)
{
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"')) {
        $str = '"' . str_replace('"', '""', $str) . '"';
    }
}

if ($section == "map_api") {
    $checkedvalues = $_REQUEST['checkedvalues'];
    $DbName = TSITE_DB;
    $TableName = "auth_master_accounts_places";
    $TableName_Accounts = "auth_accounts_places";
    $TableName_usage_report = "auth_report_accounts_places";
    $siteUrl = $tconfig['tsite_url'];
    $data_drv['servicedata'] = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName);
    $data_drv['auth_accounts_places'] = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName_Accounts);
    $data_drv['usage_report'] = $obj->fetchAllCollectionFromMongoDB($DbName, $TableName_usage_report);
    // $time = time();
    $date = date('d_m_Y_h_i_s');
    $file = 'map_api_export_' . $date . '.json';
    file_put_contents($file, json_encode($data_drv));
    header("Content-type: application/json");
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($file));
    // echo json_encode($data_drv) ."\t";
    echo json_encode($data_drv, JSON_PRETTY_PRINT) . "\t";
}
if ($section == 'blocked_driver') {
    $cancel_for_hours = $CANCEL_DECLINE_TRIPS_IN_HOURS;
    $c_date = date("Y-m-d H:i:s");
    $s_date = date("Y-m-d H:i:s", strtotime('-' . $cancel_for_hours . ' hours'));
    $ord = ' ORDER BY  `Total Cancelled Trips (Till now)` DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY rd.vName ASC";
        } else {
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY rd.vEmail ASC";
        } else {
            $ord = " ORDER BY rd.vEmail DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` ASC";
        } else {
            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY `Total Declined Trips (In " . $cancel_for_hours . " hours)` ASC";
        } else {
            $ord = " ORDER BY `Total Declined Trips (In " . $cancel_for_hours . " hours)` DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY `Total Cancelled Trips (Till now)` ASC";
        } else {
            $ord = " ORDER BY `Total Cancelled Trips (Till now)` DESC";
        }
    }
    if ($sortby == 6) {
        if ($order == 0) {
            $ord = " ORDER BY `Total Declined Trips (Till now)` ASC";
        } else {
            $ord = " ORDER BY `Total Declined Trips (Till now)` DESC";
        }
    }
    if ($sortby == 7) {
        if ($order == 0) {
            $ord = " ORDER BY `eIsBlocked` ASC";
        } else {
            $ord = " ORDER BY `eIsBlocked` DESC";
        }
    }
    if ($sortby == 8) {
        if ($order == 0) {
            $ord = " ORDER BY tBlockeddate ASC";
        } else {
            $ord = " ORDER BY tBlockeddate DESC";
        }
    }
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'DriverName') {
                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
            }
            if ($eIsBlocked != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND rd.eIsBlocked = '" . clean($eIsBlocked) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if (ONLYDELIVERALL == 'Yes') {
                if ($eIsBlocked != '') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%') AND rd.eIsBlocked = '" . clean($eIsBlocked) . "'";
                } else {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%')";
                }
            } else {
                if ($eIsBlocked != '') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%') AND rd.eIsBlocked = '" . clean($eIsBlocked) . "'";
                } else {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%')";
                }
            }
        }
    } else if ($eIsBlocked != '' && $keyword == '') {
        $ssql .= " AND rd.eIsBlocked = '" . clean($eIsBlocked) . "'";
    }
    // End Search Parameters
    $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '')";
    $sql = "SELECT  CONCAT(rd.vName,' ',rd.vLastName) AS Name, rd.vEmail as Email , COALESCE( m.cnt , 0 ) AS `Total Cancelled Trips (In " . $cancel_for_hours . " hours)`,  COALESCE( d.cnt, 0 ) AS `Total Declined Trips (In " . $cancel_for_hours . " hours)`,  COALESCE( mAll.cntAll, 0 ) AS   `Total Cancelled Trips (Till now)`, COALESCE( dAll.cntAll, 0 ) AS  `Total Declined Trips (Till now)` ,rd.eIsBlocked as `Block driver`,rd.tBlockeddate as `Block date`,rd.vTimeZone FROM  register_driver rd LEFT JOIN (SELECT  iDriverId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate FROM trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND  iActive =  'Canceled' AND eCancelledBy	='Driver' GROUP BY tr.iDriverId ) m ON rd.iDriverId = m.iDriverId LEFT JOIN (SELECT  iDriverId,COUNT( trAll.iTripId ) AS cntAll,iActive FROM trips trAll where  iActive =  'Canceled' AND eCancelledBy	='Driver' GROUP BY trAll.iDriverId ) mAll ON rd.iDriverId = mAll.iDriverId LEFT JOIN (SELECT  iDriverId,COUNT( dr.iDriverRequestId ) AS cnt,dAddedDate,eStatus FROM driver_request dr where  dr.dAddedDate BETWEEN  '" . $s_date . "'  AND  '" . $c_date . "'	AND dr.eStatus =  'Decline' GROUP BY  dr.iDriverId ) d ON rd.iDriverId = d.iDriverId LEFT JOIN (SELECT  iDriverId,COUNT( drAll.iDriverRequestId ) AS cntAll,dAddedDate,eStatus FROM driver_request drAll where  drAll.eStatus =  'Decline' GROUP BY  drAll.iDriverId ) dAll ON rd.iDriverId = dAll.iDriverId  where (mAll.cntAll >'0' $ssql $ssql1) OR  (dAll.cntAll >'0' $ssql $ssql1)  $ord";
    //ini_set("display_errors", 1);
    //error_reporting(E_ALL);
    $result = $obj->MySQLSelect($sql) or die('Query Failed!');
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $filename = "decline_alert_for_service_providers_".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1',  'Total Cancelled Trips/Jobs (In 24 hours)');
        $sheet->setCellValue('D1', 'Total Declined Trips/Jobs (In 24 hours)');
        $sheet->setCellValue('E1', 'Total Cancelled Trips/Jobs (Till now)');
        $sheet->setCellValue('F1', 'Total Declined Trips/Jobs (Till now)');
        $sheet->setCellValue('G1', 'Block Service Provider Status');
        $sheet->setCellValue('H1', 'Block Date');
        $i = 2;

        
        $timeZone = $result[0]['vTimeZone'];
        unset($result[0]['vTimeZone']);
        //echo implode("\t", array_keys($result[0])) . "\r\n";
        $result[0]['vTimeZone'] = $timeZone;
        foreach ($result as $value) {
          
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $value['Block date'];
            $get_Block_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['Block date'] = $get_Block_date_format['tDisplayDate'];//DateTime($val);
            
            $sheet->setCellValue('A' . $i, $value['Name']);
            $sheet->setCellValue('B' . $i, $value['Email']);
            $sheet->setCellValue('C' . $i, $value['Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)']);
            $sheet->setCellValue('D' . $i, $value['Total Declined Trips (In ' . $cancel_for_hours . ' hours)']);
            $sheet->setCellValue('E' . $i, $value['Total Cancelled Trips (Till now)']);
            $sheet->setCellValue('F' . $i, $value['Total Declined Trips (Till now)']);
            $sheet->setCellValue('G' . $i, $value['Block driver']);
            $sheet->setCellValue('H' . $i, $value['Block date']);
            $i++;            
        }
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else if ($type == 'PDF') {
        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 Start
        $heading = array(
            'Provider Name',
            'Email',
            'A',
            'B',
            'C',
            'D',
            'Block driver',
            'Block Date'
        );
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");
        //echo "<pre>";
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = "blocked_driver_" . $configPdf['pdfName'];
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Decline / Canceled Trip / Jobs Alert For Drivers");
        $pdf->Ln();
        $aTxt = 'A-Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)';
        $bTxt = 'B-Total Declined Trips (In ' . $cancel_for_hours . ' hours)';
        $cTxt = 'C-Total Cancelled Trips (Till now)';
        $dTxt = 'D-Total Declined Trips (Till now)';
        $pdf->SetFont($language, 'b', 9);
        $pdf->Cell(100, 5, $aTxt);
        $pdf->Ln();
        $pdf->Cell(100, 5, $bTxt);
        $pdf->Ln();
        $pdf->Cell(100, 5, $cTxt);
        $pdf->Ln();
        $pdf->Cell(100, 5, $dTxt);
        $pdf->Ln();
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Provider Name' || $column_heading == 'Email') {
                $pdf->Cell(60, 10, $column_heading, 1);
            } else if ($column_heading == 'Block Date') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == 'Block driver') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if($column == "vTimeZone")
                {
                    continue;
                }
                $values = $key;
                if ($column == 'Name') {
                    $values = clearName($key);
                }
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Name' || $column == 'Email') {
                    $pdf->Cell(60, 10, $values, 1);
                } else if ($column == 'Block date') {
                    $date_format_data_array = array(
                        'langCode' => $default_lang,
                        'DateFormatForWeb' => 1
                    );
                    $date_format_data_array['tdate'] = (!empty($row['vTimeZone']) && $values != "0000-00-00 00:00:00") ? converToTz($values,$row['vTimeZone'],$serverTimeZone) : $values;
                    $get_Block_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $val = $get_Block_date_format['tDisplayDate'];//DateTime($val);
                    $pdf->Cell(40, 10, $val, 1);
                } else if ($column == 'Block driver') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(20, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 End
    }
}
if ($section == 'blocked_rider') {
    $cancel_for_hours = $CANCEL_DECLINE_TRIPS_IN_HOURS;
    $c_date = date("Y-m-d H:i:s");
    $s_date = date("Y-m-d H:i:s", strtotime('-' . $cancel_for_hours . ' hours'));
    $ord = ' ORDER BY `Total Cancelled Trips (Till now)` DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vName ASC";
        } else {
            $ord = " ORDER BY vName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY rd.vEmail ASC";
        } else {
            $ord = " ORDER BY rd.vEmail DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` ASC";
        } else {
            $ord = " ORDER BY `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY `Total Cancelled Trips (Till now)` ASC";
        } else {
            $ord = " ORDER BY `Total Cancelled Trips (Till now)` DESC";
        }
    }
    if ($sortby == 7) {
        if ($order == 0) {
            $ord = " ORDER BY eIsBlocked ASC";
        } else {
            $ord = " ORDER BY eIsBlocked DESC";
        }
    }
    if ($sortby == 8) {
        if ($order == 0) {
            $ord = " ORDER BY tBlockeddate ASC";
        } else {
            $ord = " ORDER BY tBlockeddate DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'RiderName') {
                $option_new = "CONCAT(vName,' ',vLastName)";
            }
            if ($eIsBlocked != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND eIsBlocked = '" . clean($eIsBlocked) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if ($eIsBlocked != '') {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%') AND eIsBlocked = '" . clean($eIsBlocked) . "'";
            } else {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%')";
            }
        }
    } else if ($eIsBlocked != '' && $keyword == '') {
        $ssql .= " AND rd.eIsBlocked = '" . clean($eIsBlocked) . "'";
    }
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    $cmp_ssql = "";
    if ($eStatus != '') {
        $estatusquery = "";
    } else {
        $estatusquery = " AND eStatus != 'Deleted'";
    }
    $ssql1 = "AND (vEmail != '' OR vPhone != '')";
    $sql = "SELECT  CONCAT(rd.vName,' ',rd.vLastName) AS Name,rd.vEmail as Email,  COALESCE( m.cnt , 0 ) AS `Total Cancelled Trips (In " . $cancel_for_hours . " hours)` ,  COALESCE( mAll.cnt , 0 ) AS `Total Cancelled Trips (Till now)`,rd.eIsBlocked as `Block Rider`,rd.tBlockeddate as `Block Date`,rd.vTimeZone FROM  register_user rd LEFT JOIN (SELECT  iUserId,COUNT( tr.iTripId ) AS cnt,iActive,tEndDate   FROM trips tr where tEndDate BETWEEN  '" . $s_date . "' AND  '" . $c_date . "'  AND   tr.iActive =  'Canceled' AND tr.eCancelledBy ='Passenger' GROUP BY tr.iUserId ) m ON rd.iUserId = m.iUserId LEFT JOIN (SELECT  iUserId,COUNT( trAll.iTripId ) AS cnt,iActive FROM trips trAll where trAll.iActive =  'Canceled' AND trAll.eCancelledBy ='Passenger' GROUP BY trAll.iUserId ) mAll ON rd.iUserId = mAll.iUserId where (mAll.cnt >'0') $ssql $ssql1 $ord";
    $result = $obj->MySQLSelect($sql) or die('Query Failed!');
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $filename ="cancelled_trips_alert_for_user_".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1',  'Total Cancelled Trips/Jobs (In 24 hours)');
        $sheet->setCellValue('D1', 'Total Cancelled Trips/Jobs (Till now)');       
        $sheet->setCellValue('E1', 'Block User Status');
        $sheet->setCellValue('F1', 'Block Date');
        $i = 2;
		
        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
            $result[0] = change_key($result[0], 'Total Drivers', 'Total Providers');
        }
        $TimeZone = $result[0]['vTimeZone'];
        unset($result[0]['vTimeZone']);
        //echo implode("\t", array_keys($result[0])) . "\r\n";
        $result[0]['vTimeZone'] = $TimeZone;
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if($key == "vTimeZone")
                {
                    continue;
                }
                if ($key == 'Name') {
                    $val = clearCmpName($val);
                }
                if ($key == 'Email') {
                    $val = clearEmail($val);
                }
                if ($key == 'Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)') {
                    $val = ($val);
                }
                if ($key == 'Total Cancelled Trips (Till now)') {
                    $val = ($val);
                }
                if ($key == 'Block Rider') {
                    $val = ($val);
                }
                if ($key == 'Block Date') {
                    $date_format_data_array = array(
                        'langCode' => $default_lang,
                        'DateFormatForWeb' => 1
                    );
                    $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $val != "0000-00-00 00:00:00") ? converToTz($val,$value['vTimeZone'],$serverTimeZone) : $val;
                    $get_Block_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $val = $get_Block_date_format['tDisplayDate'];//DateTime($val);
                }
                //echo $val . "\t";
            }
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $value['Block Date'];
            $get_Block_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['Block Date'] = $get_Block_date_format['tDisplayDate'];//DateTime($val);
            
            $sheet->setCellValue('A' . $i, $value['Name']);
            $sheet->setCellValue('B' . $i, $value['Email']);
            $sheet->setCellValue('C' . $i, $value['Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)']);            
            $sheet->setCellValue('D' . $i, $value['Total Cancelled Trips (Till now)']);            
            $sheet->setCellValue('E' . $i, $value['Block Rider']);
            $sheet->setCellValue('F' . $i, $value['Block Date']);
            $i++; 
            //echo "\r\n";
        }
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
    else if ($type == 'PDF') {
        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 Start
        $heading = array(
            'User Name',
            'Email',
            'A',
            'B',
            'Block Driver',
            'Block Date'
        );
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        //echo "<pre>";
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = "blocked_rider_" . $configPdf['pdfName'];
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Cancelled Trip/Jobs Alert For " . $langage_lbl_admin['LBL_RIDERS_ADMIN']);
        $pdf->Ln();
        $aTxt = 'A-Total Cancelled Trips (In ' . $cancel_for_hours . ' hours)';
        $bTxt = 'B-Total Cancelled Trips (Till now)';
        $pdf->SetFont($language, 'b', 9);
        $pdf->Cell(100, 5, $aTxt);
        $pdf->Ln();
        $pdf->Cell(100, 5, $bTxt);
        $pdf->Ln();
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'User Name') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Email') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Block Date') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == 'Block Driver') {
                $pdf->Cell(23, 10, $column_heading, 1);
            } else {
                $pdf->Cell(15, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if($column == "vTimeZone")
                {
                    continue;
                }
                $values = $key;
                if ($column == 'Name') {
                    $values = clearName($key);
                }
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Name') {
                    $pdf->Cell(50, 10, $values, 1);
                } else if ($column == 'Email') {
                    $pdf->Cell(55, 10, $values, 1);
                } else if ($column == 'Block Date') {
                    $date_format_data_array = array(
                        'langCode' => $default_lang,
                        'DateFormatForWeb' => 1
                    );
                    $date_format_data_array['tdate'] = (!empty($row['vTimeZone']) && $values != "0000-00-00 00:00:00") ? converToTz($values,$row['vTimeZone'],$serverTimeZone) : $values;
                    $get_Block_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $values = $get_Block_date_format['tDisplayDate'];//DateTime($val);
                    $pdf->Cell(40, 10, $values, 1);
                } else if ($column == 'Block Rider') {
                    $pdf->Cell(23, 10, $values, 1);
                } else {
                    $pdf->Cell(15, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
        //Added By HJ On 18-01-2019 For Solved Client Bug - 6720 End
    }
}
if ($section == 'admin') {
    $query = Models\Administrator::with([
        'roles',
        'locations'
    ]);
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) && $_REQUEST['order'] == 1 ? 'ASC' : 'DESC';
    switch ($sortby) {
        case 1:
            $query->orderBy('vFirstName', $order);
            break;
        case 2:
            $query->orderBy('vEmail', $order);
            break;
        case 3:
            break;
        case 4:
            $query->orderBy('eStatus', $order);
            break;
        default:
            break;
    }
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    if (!empty($keyword)) {
        if (!empty($option)) {
            if ($option == 'eStatus') {
                $query->where('eStatus', $keyword);
            }
        } else {
            $query->where(function ($q) use ($keyword) {
                $q->where(DB::raw('concat(`vFirstName`," ",`vLastName`)'), "LIKE", "%{$keyword}%");
                $q->orWhere('vEmail', "LIKE", "%{$keyword}%");
                $q->orwhere('vContactNo', "LIKE", "%{$keyword}%");
                $q->orwhere('eStatus', "LIKE", "%{$keyword}%");
            });
        }
    }
    if (!$userObj->hasRole(1)) {
        $query->where('iGroupId', $userObj->role_id);
    }
    if ($option != 'eStatus') {
        $query->where('eStatus', '!=', "Deleted");
    }
    $start = 0;
    $data_drv = $query->get();
    //echo "<pre>";
    $result = array();
    foreach ($data_drv as $key => $row) {
        $data = array();
        $data['Name'] = clearName($row['vFirstName'] . ' ' . $row['vLastName']);
        $data['Email'] = clearEmail($row['vEmail']);
        $data['Admin Roles'] = $row->roles->vGroup;
        $data['Status'] = $row['eStatus'];
        $result[] = $data;
    }
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        echo implode("\t", array_keys($result[0])) . "\r\n";
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'Name') {
                    $val = clearName($val);
                }
                if ($key == 'Email') {
                    $val = clearEmail($val);
                }
                echo $val . "\t";
            }
            echo "\r\n";
        }
    }  else {
        $heading = array(
            'Name',
            'Email',
            'Admin Roles',
            'Status'
        );
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Admin " . $langage_lbl_admin['LBL_RIDERS_ADMIN']);
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(10, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Name') {
                    $values = clearName($key);
                }
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Id') {
                    $pdf->Cell(10, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
if ($section == 'company') {
    $ord = ' ORDER BY c.iCompanyId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCompany ASC";
        } else {
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY c.vEmail ASC";
        } else {
            $ord = " ORDER BY c.vEmail DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY c.eStatus ASC";
        } else {
            $ord = " ORDER BY c.eStatus DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(c.vCode,'',c.vPhone)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND c.eStatus = '" . clean($eStatus) . "'";
    }
    $cmp_ssql = "";
    if ($eStatus != '') {
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND c.eStatus != 'Deleted'";
    }
    $eSystem = " AND  c.eSystem ='General'";
    $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(rd.iDriverId) FROM register_driver AS rd WHERE rd.iCompanyId=c.iCompanyId) AS `Total Drivers`, CONCAT(c.vCode,' ',c.vPhone) AS Mobile,c.eStatus AS Status FROM company AS c WHERE 1 = 1 $eSystem $eStatus_sql $ssql $cmp_ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
            $result[0] = change_key($result[0], 'Total Drivers', 'Total Providers');
        }
        echo implode("\t", array_keys($result[0])) . "\r\n";
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'Email') {
                    $val = clearEmail($val);
                }
                if ($key == 'Mobile') {
                    $val = clearPhone($val);
                }
                if ($key == 'Name') {
                    $val = clearCmpName($val);
                }
                echo $val . "\t";
            }
            echo "\r\n";
        }
    } else {
        if ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') {
            $heading = array(
                'Name',
                'Email',
                'Total Providers',
                'Mobile',
                'Status'
            );
        } else {
            $heading = array(
                'Name',
                'Email',
                'Total Drivers',
                'Mobile',
                'Status'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Companies");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Total Drivers') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Total Providers') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(55, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Mobile') {
                    $values = clearPhone($key);
                }
                if ($column == 'Name') {
                    $values = clearCmpName($key);
                }
                if ($column == 'Total Drivers') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Total Providers') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(55, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
if ($section == 'store') {
    $ord = ' ORDER BY c.iCompanyId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCompany ASC";
        } else {
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY c.vEmail ASC";
        } else {
            $ord = " ORDER BY c.vEmail DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY c.eStatus ASC";
        } else {
            $ord = " ORDER BY c.eStatus DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(c.vCode,'',c.vPhone)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
            }
            if ($select_cat != "") {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND sc.iServiceId = '" . clean($select_cat) . "' ";
            }
            if ($select_cat != "" && $eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if ($eStatus == '' && $select_cat != "" && $keyword_new != "") {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else if ($eStatus != '' && $select_cat != "") {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else if ($eStatus != '') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "'";
            } else if ($select_cat != "") {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
            }
        }
    } else if ($eStatus != '' && $select_cat != "" && $keyword == '') {
        $ssql .= " AND c.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
    } else if ($eStatus != '' && $keyword == '' && $select_cat == "") {
        $ssql .= " AND c.eStatus = '" . clean($eStatus) . "'";
    } else if ($eStatus == '' && $keyword == '' && $select_cat != "") {
        $ssql .= " AND sc.iServiceId = '" . clean($select_cat) . "'";
    }
    $cmp_ssql = "";
    if ($eStatus != '') {
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND c.eStatus != 'Deleted'";
    }
    $eSystem = " AND  c.eSystem ='DeliverAll'";
    $ssql .= " AND sc.iServiceId IN(" . $enablesevicescategory . ")";
    if (!$MODULES_OBJ->isSingleStoreSelection()) {
        $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as `Item Categories`, c.vCode,c.vPhone, c.tRegistrationDate `Registration Date`,c.eStatus AS Status,c.vTimeZone,sc.iServiceId,(SELECT COUNT(rd.iDriverId) FROM register_driver as rd WHERE rd.iCompanyId = c.iCompanyId AND rd.eStatus != 'Deleted' ) as driver_count  FROM company AS c left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $cmp_ssql $ord";
    } else {
        $sql = "SELECT c.vCompany AS Name, c.vEmail AS Email,(SELECT count(iFoodMenuId) FROM food_menu WHERE iCompanyId = c.iCompanyId AND eStatus != 'Deleted') as `Item Categories`,  c.vCode,c.vPhone, c.tRegistrationDate `Registration Date`,c.eStatus AS Status,c.vTimeZone,sc.iServiceId,(SELECT COUNT(rd.iDriverId) FROM register_driver as rd WHERE rd.iCompanyId = c.iCompanyId AND rd.eStatus != 'Deleted' ) as driver_count  FROM company AS c left join service_categories as sc on c.iServiceId = sc.iServiceId WHERE 1 = 1 and sc.eStatus='Active' $eSystem $eStatus_sql $ssql $cmp_ssql GROUP BY sc.iServiceId  $ord";
    }
    // echo $sql;die; 
    //added by SP on 28-06-2019
    //$catdata = serviceCategories;
    //$service_cat_data = json_decode($catdata, true);
    $serverTimeZone = date_default_timezone_get();
    $catdata = ServiceData;
    $service_cat_data = json_decode($catdata, true);

    if ($type == 'XLS') {
        $filename = "store_".$timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN']." ".$langage_lbl_admin['LBL_NAME_TXT']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_EMAIL_TEXT']);
		
		if (scount($service_cat_data) > 1) { 
            $sheet->setCellValue('C1', "Service Type");
			$sheet->setCellValue('D1',  "Item Categories");
			$sheet->setCellValue('E1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
			$sheet->setCellValue('F1', "Registration Date");
			$sheet->setCellValue('G1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
			$sheet->setCellValue('H1', $langage_lbl_admin['LBL_Status']);			
        }else{
			$sheet->setCellValue('C1',  "Item Categories");
			$sheet->setCellValue('D1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
			$sheet->setCellValue('E1', "Registration Date");
			$sheet->setCellValue('F1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']);
			$sheet->setCellValue('G1', $langage_lbl_admin['LBL_Status']);			
		}
        
        $i = 2;
        foreach ($result as $value) {
            if(empty($value['vTimeZone']))
            {
                $timeZone_sql = "SELECT vTimeZone FROM country WHERE vCountryCode='".$value['vCountry']."' ";
                $get_timezone_data = $obj->MySQLSelect($timeZone_sql);
                $value['vTimeZone'] =  $get_timezone_data[0]['vTimeZone'];
            }
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value["Registration Date"],$value['vTimeZone'],$serverTimeZone) : $value["Registration Date"];
            $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $get_utc_time = DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']);
            $time_zone_difference_text = (!empty($get_utc_time)) ? " (UTC:".$get_utc_time.")" : " (UTC:00:00)";
            $value["Registration Date"] = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

            $sheet->setCellValue('A' . $i, $value["Name"]);
            $sheet->setCellValue('B' . $i, clearEmail($value["Email"]));                 
            if (scount($service_cat_data) > 1) { 
                $service_type = "";
                foreach ($service_cat_data as $servicedata) { 
                    if ($servicedata['iServiceId'] == $value['iServiceId'] && $select_cat == "") { 
                        $service_type = !empty($servicedata['vServiceName']) ? $servicedata['vServiceName'] : '';
                    } elseif ($servicedata['iServiceId'] == $select_cat) { 
                        $service_type = !empty($servicedata['vServiceName']) ? $servicedata['vServiceName'] : '';
                    }
                }
                $sheet->setCellValue('C' . $i, $service_type);
				$sheet->setCellValue('D' . $i, $value["Item Categories"]);
				$sheet->setCellValue('E' . $i, "(+". ($value["vCode"]).") ". clearPhone($value["vPhone"]));
				$sheet->setCellValue('F' . $i, $value["Registration Date"]);
				$sheet->setCellValue('G' . $i, $value["driver_count"]);
				$sheet->setCellValue('H' . $i, $value["Status"]);    								
            } else {
				$sheet->setCellValue('C' . $i, $value["Item Categories"]);
				$sheet->setCellValue('D' . $i, "(+". ($value["vCode"]).") ". clearPhone($value["vPhone"]));
				$sheet->setCellValue('E' . $i, $value["Registration Date"]);
				$sheet->setCellValue('F' . $i, $value["driver_count"]);
				$sheet->setCellValue('G' . $i, $value["Status"]);   
				
			}     
            $i++;
        }
        
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        $heading = array(
            'Name',
            'Email',
            'Item Categories',
            'Mobile',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Store");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Item Categories') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(55, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if($column == "vTimeZone")
                {
                    continue;
                }
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Mobile') {
                    $values = clearPhone($key);
                }
                if ($column == 'Name') {
                    $values = clearCmpName($key);
                }
                if ($column == 'Item Categories') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(55, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
if ($section == 'organization') {
    $ord = ' ORDER BY c.iOrganizationId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCompany ASC";
        } else {
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY c.vEmail ASC";
        } else {
            $ord = " ORDER BY c.vEmail DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY c.eStatus ASC";
        } else {
            $ord = " ORDER BY c.eStatus DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(c.vCode,'',c.vPhone)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
             $getProfileId = "SELECT iUserProfileMasterId FROM `user_profile_master` WHERE eStatus !='Deleted' AND vProfileName LIKE '%" . clean($keyword_new) . "%'";
            $data_profile = $obj->MySQLSelect($getProfileId);
            $iUserProfileMasterIdIn = '';
            if(scount($data_profile) > 0){
                $inArr = array();
                for($p=0;$p<scount($data_profile);$p++){
                    $inArr[] = $data_profile[$p]['iUserProfileMasterId'];
                }
                if(scount($inArr) > 0){
                     $iUserProfileMasterIdIn = " OR c.iUserProfileMasterId IN (".implode(',',$inArr).")";
                }
            }
            if ($eStatus != '') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%') ".$iUserProfileMasterIdIn.") AND c.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%') ".$iUserProfileMasterIdIn.")";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND c.eStatus = '" . clean($eStatus) . "'";
    }
    $cmp_ssql = "";
    if ($eStatus != '') {
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND c.eStatus != 'Deleted'";
    }
    $sql = "SELECT c.vCompany AS 'Organization Name', c.iUserProfileMasterId AS 'Organization Type',c.ePaymentBy AS 'Payment Method', c.vEmail AS Email,c.vCode,c.vPhone, c.eStatus AS Status FROM organization AS c WHERE 1 = 1 $eStatus_sql $ssql $cmp_ssql $ord";
    $orgTypeArr = array();
    $orgType_sql = "SELECT vProfileName,iUserProfileMasterId FROM user_profile_master ORDER BY iUserProfileMasterId ASC";
    $orgProfileData = $obj->MySQLSelect($orgType_sql);
    $default_lang = $_SESSION['sess_lang'];
    for ($p = 0; $p < scount($orgProfileData); $p++) {
        $profileName = (array)json_decode($orgProfileData[$p]['vProfileName']);
        $orgTypeArr[$orgProfileData[$p]['iUserProfileMasterId']] = $profileName['vProfileName_' . $default_lang];
    }
    //echo "<pre>";
    //print_r($orgTypeArr);die;
    // filename for download
    if ($type == 'XLS') {
        $filename = "organization_".$timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_ORGANIZATION_NAME_WEB']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_ORGANIZATION_TYPE_WEB']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_PAYMENT_METHOD_WEB']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_EMAIL_TEXT']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
        foreach ($result as $value) {
            $orgType = $value['Organization Type'];
            if (isset($orgTypeArr[$value['Organization Type']])) {
                $orgType = $orgTypeArr[$value['Organization Type']];
            }
            $orgType = $orgType;

            $payByName = $value['Payment Method'];
            if ($value['Payment Method'] == "" || $value['Payment Method'] == "Passenger") {
                $payByName = $langage_lbl_admin['LBL_RIDER'];
            }
            $value['Payment Method'] = "Pay By " . $payByName;

            $sheet->setCellValue('A' . $i, clearCmpName($value["Organization Name"]));
            $sheet->setCellValue('B' . $i, $orgType);
            $sheet->setCellValue('C' . $i, $value['Payment Method']);
            $sheet->setCellValue('D' . $i, !empty(clearEmail($value['Email'])) ? clearEmail($value['Email']) : '-' );
            $sheet->setCellValue('E' . $i, !empty(clearPhone($value["vPhone"])) ?  "(+". ($value["vCode"]).") ". clearPhone($value["vPhone"]) : "");
            $sheet->setCellValue('F' . $i, $value["Status"]);            
            $i++;
        }
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        $heading = array(
            'Name',
            'Email',
            'Mobile',
            'Status',
            'Type',
            'Payment'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Organizations");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Mobile' || $column_heading == 'Type') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Payment') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Type') {
                    $orgType = "";
                    if (isset($orgTypeArr[$key])) {
                        $orgType = $orgTypeArr[$key];
                    }
                    $values = $orgType;
                }
                if ($column == 'Payment') {
                    $payByName = $key;
                    if ($payByName == "") {
                        $payByName = $langage_lbl_admin['LBL_RIDER'];
                    }
                    $values = "Pay By " . $payByName;
                }
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Mobile') {
                    $values = clearPhone($key);
                }
                if ($column == 'Name') {
                    $values = clearCmpName($key);
                }
                if ($column == 'Mobile' || $column == 'Type') {
                    $pdf->Cell(55, 10, $values, 1);
                } else if ($column == 'Payment') {
                    $pdf->Cell(45, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(30, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
if ($section == 'rider') {
    $ord = ' ORDER BY iUserId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vName ASC";
        } else {
            $ord = " ORDER BY vName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vEmail ASC";
        } else {
            $ord = " ORDER BY vEmail DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY tRegistrationDate ASC";
        } else {
            $ord = " ORDER BY tRegistrationDate DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'RiderName') {
                $option_new = "CONCAT(vName,' ',vLastName)";
            }
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(vPhoneCode,'',vPhone)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%" . clean($keyword_new) . "%')) AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%" . clean($keyword_new) . "%'))";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    }
    $ssql1 = " AND (vEmail != '' OR vPhone != '') AND eHail='No'";
    if ($eStatus != '') {
        $estatusquery = "";
    } else {
        $estatusquery = " AND eStatus != 'Deleted'";
    }

    $sql = "SELECT CONCAT(vName,' ',vLastName) as `User Name`,vEmail as Email,tRegistrationDate as `Signup Date`,CONCAT(vPhoneCode,' ',vPhone) AS Mobile,vPhoneCode,vPhone,iUserId AS `Wallet Balance`,eStatus as Status ,vTimeZone,vCountry FROM register_user WHERE 1=1 $estatusquery  $ssql $ssql1 $rdr_ssql $ord";
    $wallet_data = $obj->MySQLSelect("SELECT iUserId, SUM(COALESCE(CASE WHEN eType = 'Credit' THEN iBalance END,0)) - SUM(COALESCE(CASE WHEN eType = 'Debit' THEN iBalance END,0)) as balance FROM user_wallet WHERE eUserType = 'Rider' GROUP BY iUserId");
    $walletDataArr = array();
    foreach ($wallet_data as $wallet_balance) {
        $walletDataArr[$wallet_balance['iUserId']] = $wallet_balance['balance'];
    }
    $serverTimeZone = date_default_timezone_get();
    // filename for download
    if(empty($SPREADSHEET_OBJ) || empty($SPREADSHEET_WRITER_OBJ)){
        return;
    }
    
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $filename = "Users_".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_TRACK_SERVICE_COMPANY_USER_NAME_TXT']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_EMAIL_TEXT']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_SIGNUP_DATE_ADMIN']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_WALLET_BALANCE']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
        foreach ($result as $value) {
            $user_available_balance = 0;
            if (isset($walletDataArr[$value['Wallet Balance']])) {
                $user_available_balance = $walletDataArr[$value['Wallet Balance']];
            }
            $value['Wallet Balance'] = formateNumAsPerCurrency($user_available_balance, '');

            if(empty($value['vTimeZone']))
            {
                $timeZone_sql = "SELECT vTimeZone FROM country WHERE vCountryCode='".$value['vCountry']."' ";
                $get_timezone_data = $obj->MySQLSelect($timeZone_sql);
                $value['vTimeZone'] =  $get_timezone_data[0]['vTimeZone'];
            }
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value["Signup Date"],$value['vTimeZone'],$serverTimeZone) : $value["Signup Date"];
            $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
            $value["Signup Date"] = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

            $sheet->setCellValue('A' . $i, $value["User Name"]);
            $sheet->setCellValue('B' . $i, clearEmail($value["Email"]));
            $sheet->setCellValue('C' . $i, $value["Signup Date"]);
            $sheet->setCellValue('D' . $i, "(+". ($value["vPhoneCode"]).") ". clearPhone($value["vPhone"]));
            $sheet->setCellValue('E' . $i, $value["Wallet Balance"]);
            $sheet->setCellValue('F' . $i, $value["Status"]);            
            $i++;
        }
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

    } else {
        $heading = array(
            'User Name',
            'Email',
            'Signup Date',
            'Mobile',
            'Wallet Balance',
            'Status'
        );

        while ($row = mysqli_fetch_assoc($result)) {
            // $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($row['Wallet Balance'], "Rider");
            $user_available_balance = 0;
            if (isset($walletDataArr[$row['Wallet Balance']])) {
                $user_available_balance = $walletDataArr[$row['Wallet Balance']];
            }
            $row['Wallet Balance'] = formateNumAsPerCurrency($user_available_balance, '');
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Riders");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Email') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Signup Date') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Wallet Balance') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else {
                $pdf->Cell(50, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column != 'vTimeZone') { 
                    $values = $key;
                    if ($column == 'User Name') {
                        $values = clearName($key);
                    }
                    if ($column == 'Email') {
                        $values = clearEmail($key);
                    }
                    if ($column == 'Mobile') {
                        $values = clearPhone($key);
                    }
                    if ($column == 'Email') {
                        $pdf->Cell(55, 10, $values, 1);
                    } else if ($column == 'Mobile') {
                        $pdf->Cell(45, 10, $values, 1);
                    } else if ($column == 'Status') {
                        $pdf->Cell(25, 10, $values, 1);
                    } else if ($column == 'Signup Date') {
                        $date_format_data_array = array(
                            'langCode' => $default_lang,
                            'DateFormatForWeb' => 1
                        );
                        $date_format_data_array['tdate'] = (!empty($row['vTimeZone'])) ? converToTz($key,$row['vTimeZone'],$serverTimeZone) : $key;
                        $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($row['vTimeZone'],$date_format_data_array['tdate']).")";
                        $values = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($key);
                        $pdf->Cell(55, 10, $values, 1);
                    } else if ($column == 'Wallet Balance') {
                        $pdf->Cell(40, 10, $values, 1);
                    } else {
                        $pdf->Cell(50, 10, $values, 1);
                    }
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//make
if ($section == 'make') {
    $ord = ' ORDER BY vMake ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vMake ASC";
        } else {
            $ord = " ORDER BY vMake DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vMake LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . ($keyword) . "%')";
        }
    }
    if ($option == "eStatus") {
        $eStatussql = " AND eStatus = '" . ($keyword) . "'";
    } else {
        $eStatussql = " AND eStatus != 'Deleted'";
    }
    $sql = "SELECT vMake as Make, eStatus as Status FROM make where 1=1 $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Make',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Make");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(70, 10, $column_heading, 1);
            } else {
                $pdf->Cell(80, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(70, 10, $key, 1);
                } else {
                    $pdf->Cell(80, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//make
////////// Package Start //////////////
if ($section == 'package_type') {
    $ord = ' ORDER BY vName ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vName ASC";
        } else {
            $ord = " ORDER BY vName DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vName LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . ($keyword) . "%')";
        }
    }
    if ($option == "eStatus") {
        $eStatussql = " AND eStatus = '" . ($keyword) . "'";
    } else {
        $eStatussql = " AND eStatus != 'Deleted'";
    }
    $sql = "SELECT vName as Name, eStatus as Status FROM package_type where 1=1 $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Name',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Package Type");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(70, 10, $column_heading, 1);
            } else {
                $pdf->Cell(80, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(70, 10, $key, 1);
                } else {
                    $pdf->Cell(80, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
////////// Package End //////////////
//model
if ($section == 'model') {
    $ord = ' ORDER BY mo.vTitle ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY mo.vTitle ASC";
        } else {
            $ord = " ORDER BY mo.vTitle DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY mk.vMake ASC";
        } else {
            $ord = " ORDER BY mk.vMake DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY mo.eStatus ASC";
        } else {
            $ord = " ORDER BY mo.eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (mo.vTitle LIKE '%" . $keyword . "%' OR mo.eStatus LIKE '%" . $keyword . "%' OR mk.vMake LIKE '%" . $keyword . "%')";
        }
    }
    if ($option == "eStatus") {
        $eStatussql = " AND mo.eStatus = '" . ucfirst($keyword) . "'";
    } else {
        $eStatussql = " AND mo.eStatus != 'Deleted'";
    }
    $sql = "SELECT mo.vTitle AS Title, mk.vMake AS Make, mo.eStatus AS Status FROM model  AS mo LEFT JOIN make AS mk ON mk.iMakeId = mo.iMakeId WHERE 1=1 $eStatussql $ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Title',
            'Make',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Model");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(60, 10, $column_heading, 1);
            } else {
                $pdf->Cell(70, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Id') {
                    $pdf->Cell(45, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(60, 10, $key, 1);
                } else {
                    $pdf->Cell(70, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//model
//country
if ($section == 'country') {
    $ord = ' ORDER BY vCountry ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vCountry ASC";
        } else {
            $ord = " ORDER BY vCountry DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vPhoneCode ASC";
        } else {
            $ord = " ORDER BY vPhoneCode DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY eUnit ASC";
        } else {
            $ord = " ORDER BY eUnit DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (vCountry LIKE '%" . stripslashes($keyword) . "%' OR vPhoneCode LIKE '%" . stripslashes($keyword) . "%' OR vCountryCodeISO_3 LIKE '%" . stripslashes($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (vCountry LIKE '%" . stripslashes($keyword) . "%' OR vPhoneCode LIKE '%" . stripslashes($keyword) . "%' OR vCountryCodeISO_3 LIKE '%" . stripslashes($keyword) . "%')";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    }
    if ($eStatus != '') {
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND eStatus != 'Deleted'";
    }
    $sql = "SELECT vCountry as Country,vPhoneCode as PhoneCode, eUnit as Unit, eStatus as Status FROM country where 1 = 1 $eStatus_sql $ssql";
    // filename for download
    if ($type == 'XLS') {
        $filename = "country_".$timestamp_filename . ".xls";
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_COUNTRY_TXT']);
        $sheet->setCellValue('B1', "PhoneCode");
        $sheet->setCellValue('C1', "Unit");
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
        while ($row = mysqli_fetch_assoc($result)) { 
            $sheet->setCellValue('A' . $i, $row["Country"]);
            $sheet->setCellValue('B' . $i, $row["PhoneCode"]);
            $sheet->setCellValue('C' . $i, $row["Unit"]);
            $sheet->setCellValue('D' . $i, $row["Status"]);  
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
    } else {
        $heading = array(
            'Country',
            'PhoneCode',
            'Unit',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Country");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(44, 10, $column_heading, 1);
            } else {
                $pdf->Cell(44, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(44, 10, $key, 1);
                } else {
                    $pdf->Cell(44, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//State
if ($section == 'state') {
    $ord = ' ORDER BY s.vState ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCountry ASC";
        } else {
            $ord = " ORDER BY c.vCountry DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY s.vState ASC";
        } else {
            $ord = " ORDER BY s.vState DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY s.vStateCode ASC";
        } else {
            $ord = " ORDER BY s.vStateCode DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY s.eStatus ASC";
        } else {
            $ord = " ORDER BY s.eStatus DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 's.eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR s.vState LIKE '%" . $keyword . "%' OR s.vStateCode LIKE '%" . $keyword . "%' OR s.eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT s.vState AS State,s.vStateCode AS `State Code`,c.vCountry AS Country,s.eStatus as Status FROM state AS s INNER JOIN country AS c ON c.iCountryId = s.iCountryId WHERE s.eStatus !=  'Deleted' $ssql $ord";
    if ($type == 'XLS') {
        $filename = "state_".$timestamp_filename . ".xls";
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_STATE_TXT']);
        $sheet->setCellValue('B1', "State Code");
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_COUNTRY_TXT']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
        while ($row = mysqli_fetch_assoc($result)) {
            $sheet->setCellValue('A' . $i, $row["State"]);
            $sheet->setCellValue('B' . $i, $row["State Code"]);
            $sheet->setCellValue('C' . $i, $row["Country"]);
            $sheet->setCellValue('D' . $i, $row["Status"]);  
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        $heading = array(
            'State',
            'State Code',
            'Country',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "State");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else {
                $pdf->Cell(40, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(40, 10, $key, 1);
                } else {
                    $pdf->Cell(40, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//State
if ($section == 'city') {
    $ord = ' ORDER BY vCity ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY st.vState ASC";
        } else {
            $ord = " ORDER BY st.vState DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY ct.vCity ASC";
        } else {
            $ord = " ORDER BY ct.vCity DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCountry ASC";
        } else {
            $ord = " ORDER BY c.vCountry DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY ct.eStatus ASC";
        } else {
            $ord = " ORDER BY ct.eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (ct.vCity LIKE '%" . $keyword . "%' OR st.vState LIKE '%" . $keyword . "%' OR c.vCountry LIKE '%" . $keyword . "%' OR ct.eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT ct.vCity AS City,st.vState AS State,c.vCountry AS Country, ct.eStatus AS Status FROM city AS ct INNER JOIN country AS c ON c.iCountryId =ct.iCountryId INNER JOIN state AS st ON st.iStateId=ct.iStateId WHERE  ct.eStatus != 'Deleted' $ssql $ord";
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'City',
            'State',
            'Country',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "City");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else {
                $pdf->Cell(35, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(35, 10, $key, 1);
                } else {
                    $pdf->Cell(35, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//city
//faq
if ($section == 'faq') {
    $ord = ' ORDER BY f.vTitle_' . $default_lang . ' ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY f.vTitle_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY f.vTitle_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY fc.vTitle ASC";
        } else {
            $ord = " ORDER BY fc.vTitle DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY f.iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY f.iDisplayOrder DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY f.eStatus ASC";
        } else {
            $ord = " ORDER BY f.eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (f.vTitle_" . $default_lang . " LIKE '%" . $keyword . "%' OR fc.vTitle LIKE '%" . $keyword . "%' OR f.iDisplayOrder LIKE '%" . $keyword . "%' OR f.eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $tbl_name = 'faqs';
    $sql = "SELECT f.vTitle_" . $default_lang . " as `Title`, fc.vTitle as `Category` ,f.iDisplayOrder as `DisplayOrder` ,f.eStatus  as `Status` FROM " . $tbl_name . " f, faq_categories fc WHERE f.iFaqcategoryId = fc.iUniqueId AND fc.vCode = '" . $default_lang . "' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Title',
            'Category',
            'Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "FAQ");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else if ($column_heading == 'Category') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Order') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else {
                $pdf->Cell(28, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Title') {
                    $pdf->Cell(80, 10, $key, 1);
                } else if ($column == 'Category') {
                    $pdf->Cell(45, 10, $key, 1);
                } else if ($column == 'Order') {
                    $pdf->Cell(28, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(28, 10, $key, 1);
                } else {
                    $pdf->Cell(28, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//faq
// help Detail
if ($section == 'help_detail') {
    $ord = ' ORDER BY f.vTitle_' . $default_lang . ' ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY f.vTitle_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY f.vTitle_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY fc.vTitle ASC";
        } else {
            $ord = " ORDER BY fc.vTitle DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY f.iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY f.iDisplayOrder DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY f.eStatus ASC";
        } else {
            $ord = " ORDER BY f.eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (f.vTitle_" . $default_lang . " LIKE '%" . $keyword . "%' OR fc.vTitle LIKE '%" . $keyword . "%' OR f.iDisplayOrder LIKE '%" . $keyword . "%' OR f.eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $tbl_name = 'help_detail';
    $sql = "SELECT f.vTitle_" . $default_lang . " as `Title`, fc.vTitle as `Category` ,f.iDisplayOrder as `DisplayOrder` ,f.eStatus  as `Status` FROM " . $tbl_name . " f, help_detail_categories fc WHERE f.iHelpDetailCategoryId = fc.iUniqueId AND fc.vCode = '" . $default_lang . "' $ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Title',
            'Category',
            'Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        //print_r($result);die;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Help Detail");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else if ($column_heading == 'Category') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Order') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else {
                $pdf->Cell(28, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Title') {
                    $pdf->Cell(80, 10, $key, 1);
                } else if ($column == 'Category') {
                    $pdf->Cell(45, 10, $key, 1);
                } else if ($column == 'Order') {
                    $pdf->Cell(28, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(28, 10, $key, 1);
                } else {
                    $pdf->Cell(28, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//help detail end
//faq category
if ($section == 'faq_category') {
    $ord = ' ORDER BY vTitle ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vImage ASC";
        } else {
            $ord = " ORDER BY vImage DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vTitle ASC";
        } else {
            $ord = " ORDER BY vTitle DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY iDisplayOrder DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vTitle LIKE '%" . $keyword . "%' OR iDisplayOrder LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT vTitle as `Title`, iDisplayOrder as `Order`, eStatus as `Status` FROM faq_categories where vCode = '" . $default_lang . "' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Title',
            'Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "FAQ Category");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(44, 10, $column_heading, 1);
            } else {
                $pdf->Cell(44, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(44, 10, $key, 1);
                } else {
                    $pdf->Cell(44, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//faq category
//Help Detail category
if ($section == 'help_detail_category') {
    $ord = ' ORDER BY vTitle ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vImage ASC";
        } else {
            $ord = " ORDER BY vImage DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vTitle ASC";
        } else {
            $ord = " ORDER BY vTitle DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY iDisplayOrder DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vTitle LIKE '%" . $keyword . "%' OR iDisplayOrder LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT vTitle as `Title`, iDisplayOrder as `Order`, eStatus as `Status` FROM help_detail_categories where vCode = '" . $default_lang . "' $ssql $ord";
    // die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Title',
            'Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Help Detail Category");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(44, 10, $column_heading, 1);
            } else {
                $pdf->Cell(60, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(44, 10, $key, 1);
                } else {
                    $pdf->Cell(60, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//Help Detail category
//pages
if ($section == 'page') {
    $ord = ' ORDER BY vPageName ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vPageName ASC";
        } else {
            $ord = " ORDER BY vPageName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vPageTitle_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vPageTitle_" . $default_lang . " DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vPageName LIKE '%" . $keyword . "%' OR vPageTitle_" . $default_lang . " LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT vPageName as `Name`, vPageTitle_" . $default_lang . " as `PageTitle` FROM pages where ipageId NOT IN('5','20','21','20') AND eStatus != 'Deleted' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Name',
            'PageTitle'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Pages");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Name') {
                $pdf->Cell(57, 10, $column_heading, 1);
            } else if ($column_heading == 'PageTitle') {
                $pdf->Cell(100, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Name') {
                    $pdf->Cell(57, 10, $key, 1);
                } else if ($column == 'PageTitle') {
                    $pdf->Cell(100, 10, $key, 1);
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//pages
//languages
if ($section == 'languages') {
    $checktext = isset($_REQUEST['checktext']) ? stripslashes($_REQUEST['checktext']) : "";
    $selectedlanguage = isset($_REQUEST['selectedlanguage']) ? stripslashes($_REQUEST['selectedlanguage']) : '';
    if (!empty($selectedlanguage)) {
        $tbl_name = 'language_label_' . $selectedlanguage;
    } else {
        $tbl_name = 'language_label';
    }
    $ord = ' ORDER BY vValue ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vLabel ASC";
        } else {
            $ord = " ORDER BY vLabel DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vValue ASC";
        } else {
            $ord = " ORDER BY vValue DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . addslashes($option) . " LIKE '" . addslashes($keyword) . "'";
            } else {
                if ($checktext == 'Yes' && $option == 'vValue') {
                    $ssql .= " AND " . addslashes($option) . " LIKE '" . addslashes($keyword) . "'";
                } else {
                    $ssql .= " AND " . addslashes($option) . " LIKE '%" . addslashes($keyword) . "%'";
                }
            }
        } else {
            $ssql .= " AND (vLabel  LIKE '%" . addslashes($keyword) . "%' OR vValue  LIKE '%" . addslashes($keyword) . "%') ";
        }
    }
    $sql = "SELECT vLabel as `Code`,vValue as `Value in English Language`  FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Code',
            'Value in English Language'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Languages");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Code') {
                $pdf->Cell(88, 10, $column_heading, 1);
            } else {
                $pdf->Cell(185, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Code') {
                    $pdf->Cell(88, 10, $key, 1);
                } else {
                    $pdf->Cell(185, 10, $key, 1);
                    /*$html = 'sadasdasd<br>dsdfsdfsdf<br> dfsdsdf dfdsfsdf fsad <br>sdfsdfdsf';

                    $pdf->writeHTML($html, true, 0, false, false);

                    /*$parts = str_split($key, 120);

                    $final = implode("<br>", $parts);

                    $strText = str_replace("\n", "<br>", $final);

                    $pdf->MultiCell(185, 10, $strText, 1, 'J', 0, 1, '', '', true, null, true);*/
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//language label other
if ($section == 'language_label_other') {
    $checktext = isset($_REQUEST['checktext']) ? stripslashes($_REQUEST['checktext']) : "";
    $ord = ' ORDER BY vValue ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vLabel ASC";
        } else {
            $ord = " ORDER BY vLabel DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vValue ASC";
        } else {
            $ord = " ORDER BY vValue DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                if ($checktext == 'Yes' && $option == 'vValue') {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
                } else {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
                }
            }
        } else {
            $ssql .= " AND (vLabel LIKE '%" . $keyword . "%' OR vValue LIKE '%" . $keyword . "%')";
        }
    }
    $tbl_name = 'language_label_other';
    $sql = "SELECT vLabel as `Code`,vValue as `Value in English Language`  FROM " . $tbl_name . " WHERE vCode = '" . $default_lang . "' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Code',
            'Value in English Language'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Admin Language Label");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(88, 10, $column_heading, 1);
            } else {
                $pdf->Cell(88, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(88, 10, $key, 1);
                } else {
                    $pdf->Cell(88, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//language label other
//vehicle_type
if ($section == 'vehicle_type') {
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";
    $eType = isset($_REQUEST['eType']) ? ($_REQUEST['eType']) : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $iLocationid = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : "";
    $ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vt.fPricePerKM ASC";
        } else {
            $ord = " ORDER BY vt.fPricePerKM DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY vt.fPricePerMin ASC";
        } else {
            $ord = " ORDER BY vt.fPricePerMin DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY vt.iPersonSize ASC";
        } else {
            $ord = " ORDER BY vt.iPersonSize DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY vt.eStatus ASC";
        } else {
            $ord = " ORDER BY vt.eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                if ($iVehicleCategoryId != '') {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'  AND vt.eStatus = '" . $eStatus . "'";
                } else {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";
                }
            } else {
                if ($iVehicleCategoryId != '') {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
                } else {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
                }
            }
        } else {
            if ($eStatus != '') {
                if ($iVehicleCategoryId != '') {
                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";
                } else {
                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.eStatus = '" . $eStatus . "'";
                }
            } else {
                if ($iVehicleCategoryId != '') {
                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
                } else {
                    $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";
                }
            }
        }
    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus != '') {
        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . clean($eStatus) . "'";
    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus == '') {
        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
    } else if ($eType != '' && $keyword == '' && $eStatus != '') {
        $ssql .= " AND vt.eType = '" . $eType . "' AND vt.eStatus = '" . clean($eStatus) . "'";
    } else if ($eType != '' && $keyword == '' && $eStatus == '') {
        $ssql .= " AND vt.eType = '" . $eType . "'";
    } else if ($iLocationid != '' && $keyword == '' && $eStatus != '') {
        $ssql .= " AND vt.iLocationid = '" . $iLocationid . "' AND vt.eStatus = '" . clean($eStatus) . "'";
    } else if ($iLocationid != '' && $keyword == '' && $eStatus == '') {
        $ssql .= " AND vt.iLocationid = '" . $iLocationid . "'";
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND vt.eStatus = '" . clean($eStatus) . "'";
    }
    if ($eStatus != '') {
        $eStatussql = "";
    } else {
        $eStatussql = " AND vt.eStatus != 'Deleted'";
    }
    if ($APP_TYPE == 'Delivery') {
        $Vehicle_type_name = 'Deliver';
    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {
        $Vehicle_type_name = 'Ride-Delivery';
    } else {
        $Vehicle_type_name = $APP_TYPE;
    }
    if ($Vehicle_type_name == "Ride-Delivery") {
        if (empty($eType)) {
            $ssql .= "AND (vt.eType ='Ride' or vt.eType ='Deliver')";
        }
        $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fPricePerKM as PricePer" . $DEFAULT_DISTANCE_UNIT . ",vt.fPricePerMin as PricePerMin,vt.iBaseFare as BaseFare,vt.fCommision as Commision,vt.iPersonSize as PersonSize,vt.eType as `Service Type`, vt.eStatus as Status, lm.vLocationName as location, vt.iLocationid as locationId from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1=1  $eStatussql $ssql $ord";
    } else {
        if ($APP_TYPE == 'UberX') {
            $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vc.vCategory_" . $default_lang . " as Subcategory, vt.eStatus as Status, lm.vLocationName as location,vt.iLocationid as locationId from vehicle_type as vt  left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' $eStatussql $ssql $ord";
        } else if ($APP_TYPE == 'Ride-Delivery-UberX') {
            $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fPricePerKM as PricePer" . $DEFAULT_DISTANCE_UNIT . ",vt.fPricePerMin as PricePerMin,vt.iBaseFare as BaseFare,vt.fCommision as Commision,vt.iPersonSize as PersonSize, vt.eStatus as Status ,lm.vLocationName as location,vt.iLocationid as locationId from vehicle_type as vt left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid  where 1=1 $eStatussql $ssql $ord";
        } else {
            $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fPricePerKM as PricePer" . $DEFAULT_DISTANCE_UNIT . ",vt.fPricePerMin as PricePerMin,vt.iBaseFare as BaseFare,vt.fCommision as Commision,vt.iPersonSize as PersonSize, vt.eStatus as Status, lm.vLocationName as location,vt.iLocationid as locationId  from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "'  $eStatussql $ssql $ord";
        }
    }
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $data = array_keys($result[0]);
        $arr = array_diff($data, array("locationId"));
        echo implode("\t", $arr) . "\r\n";
        $i = 0;
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'locationId') {
                    $val = "";
                }
                if ($key == 'location' && $value['locationId'] == '-1') {
                    $val = "All Location";
                }
                echo $val . "\t";
            }
            echo "\r\n";
            $i++;
        }
    } else {
        if ($APP_TYPE == 'UberX') {
            $heading = array(
                'Type',
                'Subcategory',
                'Location Name'
            );
        } else {
            if ($Vehicle_type_name == "Ride-Delivery") {
                $heading = array(
                    'Type',
                    'PricePer' . $DEFAULT_DISTANCE_UNIT,
                    'PricePerMin',
                    'BaseFare',
                    'Commision',
                    'PersonSize',
                    $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT'],
                    'Status',
                    'Location Name'
                );
            } else {
                $heading = array(
                    'Type',
                    'PricePer' . $DEFAULT_DISTANCE_UNIT,
                    'PricePerMin',
                    'BaseFare',
                    'Commision',
                    'PersonSize',
                    'Status',
                    'Location Name'
                );
            }
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']);
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Type' && $APP_TYPE == 'UberX') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else if ($column_heading == 'Type' && $APP_TYPE != 'UberX') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == $langage_lbl_admin['LBL_VEHICLE_TYPE_SMALL_TXT']) {
                $pdf->Cell(22, 10, $column_heading, 1);
            } else if ($column_heading == 'PricePerKM') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'BaseFare') {
                $pdf->Cell(18, 10, $column_heading, 1);
            } else if ($column_heading == 'Commision') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'PersonSize') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(15, 10, $column_heading, 1);
            } else if ($column_heading == 'Location Name') {
                $pdf->Cell(26, 10, $column_heading, 1);
            } else if ($column_heading == 'Subcategory') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else {
                $pdf->Cell(26, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Type' && $APP_TYPE == 'UberX') {
                    $pdf->Cell(80, 10, $key, 1);
                } else if ($column == 'Type' && $APP_TYPE != 'UberX') {
                    $pdf->Cell(30, 10, $key, 1);
                } else if ($column == 'Service Type') {
                    $pdf->Cell(22, 10, $key, 1);
                } else if ($column == 'PricePerKM') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'BaseFare') {
                    $pdf->Cell(18, 10, $key, 1);
                } else if ($column == 'Commision') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'PersonSize') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(15, 10, $key, 1);
                } else if ($column == 'location' && $row['locationId'] == "-1") {
                    $pdf->Cell(26, 10, 'All Location', 1);
                } else if ($column == 'locationId') {
                    $pdf->Cell(2, 10, '', 0);
                } else if ($column == 'Subcategory') {
                    $pdf->Cell(50, 10, $key, 1);
                } else {
                    $pdf->Cell(26, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//service_type
if ($section == 'service_type') {
    $iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : "";
    $eType = isset($_REQUEST['eType']) ? ($_REQUEST['eType']) : "";
    $eStatus = isset($_REQUEST['eStatus']) ? ($_REQUEST['eStatus']) : "";
    $ord = ' ORDER BY vt.iVehicleCategoryId ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY vt.eStatus ASC";
        } else {
            $ord = " ORDER BY vt.eStatus DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY vt.iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY vt.iDisplayOrder DESC";
        }
    }
    if ($parent_ufx_catid > 0) {
        $getSubCat = $obj->MySQLSelect("SELECT GROUP_CONCAT(DISTINCT CONCAT('''',iVehicleCategoryId, '''')) SUB_CAT FROM " . $sql_vehicle_category_table_name . " WHERE iParentId='" . $parent_ufx_catid . "'");
        if (scount($getSubCat) > 0) {
            $ssql .= " AND vt.iVehicleCategoryId IN (" . $getSubCat[0]['SUB_CAT'] . ")";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fPricePerKM LIKE '%" . $keyword . "%' OR vt.fPricePerMin LIKE '%" . $keyword . "%' OR vt.iPersonSize  LIKE '%" . $keyword . "%') AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
            }
        }
    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus != '') {
        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "' AND vt.eStatus='" . $eStatus . "'";
    } else if ($iVehicleCategoryId != '' && $keyword == '' && $eStatus == '') {
        $ssql .= " AND vt.iVehicleCategoryId = '" . $iVehicleCategoryId . "'";
    } else if ($iVehicleCategoryId == '' && $keyword == '' && $eStatus != '') {
        $ssql .= " AND vt.eStatus='" . $eStatus . "'";
    }
    // $Vehicle_type_name = ($APP_TYPE == 'Delivery')? 'Deliver':$APP_TYPE ;
    if ($APP_TYPE == 'Delivery') {
        $Vehicle_type_name = 'Deliver';
    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {
        $Vehicle_type_name = 'UberX';
    } else {
        $Vehicle_type_name = $APP_TYPE;
    }
    if ($eStatus != '') {
        $eStatussql = "";
    } else {
        $eStatussql = " AND vt.eStatus != 'Deleted'";
    }
    $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vc.vCategory_" . $default_lang . " as Subcategory,vt.iDisplayOrder as `Display Order`,lm.vLocationName as location,vt.iLocationid as locationId from vehicle_type as vt  left join " . $sql_vehicle_category_table_name . " as vc on vt.iVehicleCategoryId = vc.iVehicleCategoryId left join country as c ON c.iCountryId = vt.iCountryId left join state as st ON st.iStateId = vt.iStateId left join city as ct ON ct.iCityId = vt.iCityId left join location_master as lm ON lm.iLocationId = vt.iLocationid where vt.eType='" . $Vehicle_type_name . "' $ssql $eStatussql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $data = array_keys($result[0]);
        $arr = array_diff($data, array("locationId"));
        echo implode("\t", $arr) . "\r\n";
        $i = 0;
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'locationId') {
                    $val = "";
                }
                if ($key == 'location' && $value['locationId'] == '-1') {
                    $val = "All Location";
                }
                echo $val . "\t";
            }
            echo "\r\n";
            $i++;
        }
    } else {
        if ($Vehicle_type_name == 'UberX') {
            $heading = array(
                'Type',
                'Subcategory',
                'Display Order',
                'Location Name'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Service Type");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Type' && $Vehicle_type_name == 'UberX') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else if ($column_heading == 'Location Name') {
                $pdf->Cell(26, 10, $column_heading, 1);
            } else if ($column_heading == 'Subcategory') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(26, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Type' && $Vehicle_type_name == 'UberX') {
                    $pdf->Cell(80, 10, $key, 1);
                } else if ($column == 'location' && $row['locationId'] == "-1") {
                    $pdf->Cell(26, 10, 'All Location', 1);
                } else if ($column == 'locationId') {
                    $pdf->Cell(2, 10, '', 0);
                } else if ($column == 'Subcategory') {
                    $pdf->Cell(50, 10, $key, 1);
                } else if ($column == 'Display Order') {
                    $pdf->Cell(25, 10, $key, 1);
                } else {
                    $pdf->Cell(26, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//service_type
//coupon
if ($section == 'coupon') {
    $sql = "select vSymbol from  currency where eDefault='Yes'";
    $db_currency = $obj->MySQLSelect($sql);
    $ord = ' ORDER BY iCouponId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vCouponCode ASC";
        } else {
            $ord = " ORDER BY vCouponCode DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY dActiveDate ASC";
        } else {
            $ord = " ORDER BY dActiveDate DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY dExpiryDate ASC";
        } else {
            $ord = " ORDER BY dExpiryDate DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY eValidityType ASC";
        } else {
            $ord = " ORDER BY eValidityType DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($sortby == 6) {
        if ($order == 0) {
            $ord = " ORDER BY iUsageLimit ASC";
        } else {
            $ord = " ORDER BY iUsageLimit DESC";
        }
    }
    if ($sortby == 7) {
        if ($order == 0) {
            $ord = " ORDER BY iUsed ASC";
        } else {
            $ord = " ORDER BY iUsed DESC";
        }
    }
    if ($sortby == 9) {
        if ($order == 0) {
            $ord = " ORDER BY vPromocodeType ASC";
        } else {
            $ord = " ORDER BY vPromocodeType DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vCouponCode LIKE '%" . $keyword . "%'  OR eValidityType LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    //added by SP for date changes and estatus on 28-06-2019
    if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    } else if ($eStatus != '') {
        $ssql .= " AND eStatus = '" . $eStatus . "'";
    } else {
        $ssql .= " AND eStatus != 'Deleted'";
    }
    $ufxEnable = $MODULES_OBJ->isUberXFeatureAvailable() ? "Yes" : "No"; //add function to modules availibility
    $rideEnable = $MODULES_OBJ->isRideFeatureAvailable() ? "Yes" : "No";
    $deliveryEnable = $MODULES_OBJ->isDeliveryFeatureAvailable() ? "Yes" : "No";
    $deliverallEnable = $MODULES_OBJ->isDeliverAllFeatureAvailable() ? "Yes" : "No";
    if ($ufxEnable != "Yes") {
        $ssql .= " AND eSystemType != 'UberX'";
    }
    if (!$MODULES_OBJ->isAirFlightModuleAvailable()) {
        $ssql .= " AND eFly = '0'";
    }
    if ($rideEnable != "Yes") {
        $ssql .= " AND eSystemType != 'Ride'";
    }
    if ($deliveryEnable != "Yes") {
        $ssql .= " AND eSystemType != 'Delivery'";
    }
    if ($deliverallEnable != "Yes") {
        $ssql .= " AND eSystemType != 'DeliverAll'";
    }
    $field = '';
    if (($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') && ONLYDELIVERALL == "No") {
        $field = ',eSystemType as `System Type`';
    }
    /* $sql = "SELECT vCouponCode as 'GiftCertificate Code', (CASE WHEN eType = 'percentage' THEN CONCAT(fDiscount,'%') ELSE CONCAT( '" . $db_currency[0]['vSymbol'] . "', fDiscount) END ) as `Discount`,eValidityType as `Validity`,vPromocodeType as `PromoCode Type`,

            CASE WHEN (DATE_FORMAT(dActiveDate,'%d/%m/%Y')='00/00/0000') THEN '-'

            ELSE DATE_FORMAT(dActiveDate,'%d/%m/%Y')

            END AS `Activation Date`,

            CASE WHEN (DATE_FORMAT(dExpiryDate,'%d/%m/%Y')='00/00/0000') THEN '-'

            ELSE DATE_FORMAT(dExpiryDate,'%d/%m/%Y')

            END AS `Expiry Date`,

            iUsageLimit as `Usage Limit`,iUsed as `Used`,iUsed as `UsedInScheduleBooking`,eStatus as `Status`$field FROM coupon WHERE 1 $ssql $ord"; */
			
			$sql = "SELECT vCouponCode as 'GiftCertificate Code', (CASE WHEN eType = 'percentage' THEN CONCAT(fDiscount,'%') ELSE CONCAT( '" . $db_currency[0]['vSymbol'] . "', fDiscount) END ) as `Discount`,eValidityType as `Validity`,vPromocodeType as `PromoCode Type`,dActiveDate AS `Activation Date`,dExpiryDate AS `Expiry Date`,

            iUsageLimit as `Usage Limit`,iUsed as `Used`,iUsed as `UsedInScheduleBooking`,eStatus as `Status`$field FROM coupon WHERE 1 $ssql $ord";
    //$serverTimeZone = date_default_timezone_get();
    // filename for download
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');

        $filename ="promocode_".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', 'Promo Code');
        $sheet->setCellValue('B1', 'Discount');
        $sheet->setCellValue('C1', 'Validity');
        $sheet->setCellValue('D1', 'PromoCode Type');
        $sheet->setCellValue('E1', 'Activation Date');
        $sheet->setCellValue('F1', 'Expiry Date');
        $sheet->setCellValue('G1', 'Usage Limit');
        $sheet->setCellValue('H1', 'Used');
        $sheet->setCellValue('I1', 'Used In Schedule Booking');
        $sheet->setCellValue('J1', 'System Type');
        $sheet->setCellValue('K1', 'Status');
        
        
        $k = 2;
        $getCouponDataArray = $obj->MySQLSelect($sql) or die('Query failed!');
        $couponArray = array();
        if (scount($getCouponDataArray) > 0 && !empty($getCouponDataArray)) {
            for ($i = 0; $i < scount($getCouponDataArray); $i++) {
                array_push($couponArray, $getCouponDataArray[$i]['GiftCertificate Code']);
            }
            $couponString = "'" . implode("','", $couponArray) . "'";
            $couponData = getUnUsedPromocode($couponString);
        }
        $test_array= [];
        while ($row = mysqli_fetch_assoc($result)) {
            if($row['Activation Date'] != "-")
            {
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                //$date_format_data_array['tdate'] = (!empty($row['vTimeZone'])) ? converToTz($row['Activation Date'],$row['vTimeZone'],$serverTimeZone) : $row['Activation Date'];
                $date_format_data_array['tdate'] = $row['Activation Date'];
                $get_activation_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $row['Activation Date'] = $get_activation_date_format['tDisplayDate'];
            }
            //$row['Expiry Date'] = "22/02/2023";
            if(!empty($row['Expiry Date']) && $row['Expiry Date'] != "-")
            {
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                //$date_format_data_array['tdate'] = (!empty($row['vTimeZone'])) ? converToTz($row['Activation Date'],$row['vTimeZone'],$serverTimeZone) : $row['Activation Date'];
                $date_format_data_array['tdate'] = $row['Expiry Date'];
                $get_expiry_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $row['Expiry Date'] = $get_expiry_date_format['tDisplayDate'];
            }
			 
            if (array_key_exists($row['GiftCertificate Code'], $couponData)) {
                $row['UsedInScheduleBooking'] = $couponData[$row['GiftCertificate Code']];
            } else {
                $row['UsedInScheduleBooking'] = 0;
            }
            if ($row['Validity'] == "Defined") {
                $row['Validity'] = "Custom";
            }
            if (!$flag) {
                if (ONLYDELIVERALL == "Yes") {
                    unset($row['UsedInScheduleBooking']);
                }
                // display field/column names as first row
                //echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            if (ONLYDELIVERALL == "Yes") {
                unset($row['UsedInScheduleBooking']);
            }
            //echo implode("\t", array_values($row)) . "\r\n";            
            $sheet->setCellValue('A' . $k, $row['GiftCertificate Code']);
            $sheet->setCellValue('B' . $k, $row["Discount"]);
            $sheet->setCellValue('C' . $k, $row["Validity"]);
            $sheet->setCellValue('D' . $k, $row["PromoCode Type"]);
            $sheet->setCellValue('E' . $k, $row["Activation Date"]);
            $sheet->setCellValue('F' . $k, $row["Expiry Date"]); 
            $sheet->setCellValue('G' . $k, $row["Usage Limit"]);
            $sheet->setCellValue('H' . $k, $row["Used"]);
            $sheet->setCellValue('I' . $k, $row["UsedInScheduleBooking"]);
            $sheet->setCellValue('J' . $k, $row["System Type"]);      
            $sheet->setCellValue('K' . $k, $row["Status"]);     
            $test_array[] = $row;
            $k++;
        }
        // echo '<pre>';
        // print_r($test_array);
        // exit;
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        $heading = array(
            'Gift Certificate',
            'Discount',
            'ValidityType',
            'PromoCode Type',
            'Active Date',
            'ExpiryDate',
            'Usage Limit',
            'Used',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Coupon");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Gift Certificate') {
                $pdf->Cell(42, 10, $column_heading, 1);
            } else if ($column_heading == 'Discount') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Validity Type') {
                $pdf->Cell(26, 10, $column_heading, 1);
            } else if ($column_heading == 'PromoCode Type') {
                $pdf->Cell(26, 10, $column_heading, 1);
            } else if ($column_heading == 'Active Date') {
                $pdf->Cell(28, 10, $column_heading, 1);
            } else if ($column_heading == 'ExpiryDate') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Usage Limit') {
                $pdf->Cell(24, 10, $column_heading, 1);
            } else if ($column_heading == 'Used') {
                $pdf->Cell(12, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(17, 10, $column_heading, 1);
            } else {
                $pdf->Cell(25, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            //echo "<pre>";
            $symbol = '$';
            if ($row['eType'] == 'percentage') {
                $symbol = '%';
            }
            unset($row['eType']);
            //if($result[])
            foreach ($row as $column => $key) {
                if ($column == 'Gift Certificate') {
                    $pdf->Cell(42, 10, $key, 1);
                } else if ($column == 'Discount') {
                    $key = $key . ' ' . $symbol;
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'ValidityType') {
                    if ($key == 'Defined') {
                        $key = 'Custom';
                        $pdf->Cell(25, 10, $key, 1);
                    } else {
                        $pdf->Cell(25, 10, $key, 1);
                    }
                } else if ($column == 'PromoCode Type') {
                    $pdf->Cell(17, 10, $key, 1);
                } else if ($column == 'Active Date') {
                    if($key != '-')
                    {
                        $date_format_data_array = array(
                            'langCode' => $default_lang,
                            'DateFormatForWeb' => 1
                        );
                        //$date_format_data_array['tdate'] = (!empty($row['vTimeZone'])) ? converToTz($row['Activation Date'],$row['vTimeZone'],$serverTimeZone) : $row['Activation Date'];
                        $date_format_data_array['tdate'] = $row['Active Date'];
                        $get_activation_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $key = $get_activation_date_format['tDisplayDate'];
                    }
                    $pdf->Cell(28, 10, $key, 1);
                } else if ($column == 'ExpiryDate') {
                    if($key != '-')
                    {
                        $date_format_data_array = array(
                            'langCode' => $default_lang,
                            'DateFormatForWeb' => 1
                        );
                        //$date_format_data_array['tdate'] = (!empty($row['vTimeZone'])) ? converToTz($row['Activation Date'],$row['vTimeZone'],$serverTimeZone) : $row['Activation Date'];
                        $date_format_data_array['tdate'] = $row['Expiry Date'];
                        $get_expiry_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $key = $get_expiry_date_format['tDisplayDate'];
                    }
                    $pdf->Cell(25, 10, $key, 1);
                } else if ($column == 'Usage Limit') {
                    $pdf->Cell(24, 10, $key, 1);
                } else if ($column == 'Used') {
                    $pdf->Cell(12, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(17, 10, $key, 1);
                } else {
                    $pdf->Cell(25, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//coupon
//driver
if ($section == 'driver') {

    $ufxEnable = $MODULES_OBJ->isUfxFeatureAvailable();


    $ord = ' ORDER BY rd.iDriverId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY rd.vName ASC";
        } else {
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCompany ASC";
        } else {
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY rd.vEmail ASC";
        } else {
            $ord = " ORDER BY rd.vEmail DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY rd.tRegistrationDate ASC";
        } else {
            $ord = " ORDER BY rd.tRegistrationDate DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY rd.eStatus ASC";
        } else {
            $ord = " ORDER BY rd.eStatus DESC";
        }
    }
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(rd.vCode,'',rd.vPhone)";
            }
            if ($option == 'DriverName') {
                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND rd.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if (ONLYDELIVERALL == 'Yes') {
                if ($eStatus != '') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND rd.eStatus = '" . clean($eStatus) . "'";
                } else {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
                }
            } else {
                if ($eStatus != '') {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR c.vCompany LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND rd.eStatus = '" . clean($eStatus) . "'";
                } else {
                    $ssql .= " AND (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%' OR c.vCompany LIKE '%" . clean($keyword_new) . "%' OR rd.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(rd.vCode,'',rd.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
                }
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND rd.eStatus = '" . clean($eStatus) . "'";
    }
    $dri_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
    }
    if ($eStatus != '') {
        $eStatus_sql = " AND rd.eStatus = '" . clean($eStatus) . "' ";
    } else {
        $eStatus_sql = " AND rd.eStatus != 'Deleted'";
    }
    $IsFeaturedEnable = "No";
    if (ONLYDELIVERALL == 'No' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxEnable == "Yes") {
        $IsFeaturedEnable = "Yes";
    }
    $ssql1 = "AND (rd.vEmail != '' OR rd.vPhone != '') AND rd.iTrackServiceCompanyId = 0 ";
    if (ONLYDELIVERALL == 'Yes') {
        $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS `Driver Name`,rd.vEmail as `Email`, rd.tRegistrationDate as `Signup Date`,CONCAT(rd.vCode,' ',rd.vPhone) as `Mobile`,rd.vCode as vPhoneCode , rd.vPhone as vPhone ,rd.iDriverId AS `Wallet Balance`,rd.eStatus as `Status`,rd.eIsFeatured AS IsFeatured, rd.eIsPremium  AS IsPremium, rd.vTimeZone,(SELECT count(dv.iDriverVehicleId) FROM driver_vehicle AS dv WHERE dv.iDriverId=rd.iDriverId AND dv.eStatus != 'Deleted' AND dv.iMakeId != 0 AND dv.iModelId != 0 AND dv.eType != 'UberX') AS `count`,rd.vCountry,rd.iCompanyId FROM register_driver rd  WHERE 1 = 1  $eStatus_sql $ssql $ssql1 $dri_ssql $ord";
    } else {
        $sql = "SELECT CONCAT(rd.vName,' ',rd.vLastName) AS `Driver Name`,c.vCompany as `Company Name`,rd.vEmail as `Email`, rd.tRegistrationDate as `Signup Date`,CONCAT(rd.vCode,' ',rd.vPhone) as `Mobile` ,rd.vCode as vPhoneCode , rd.vPhone as vPhone , rd.iDriverId AS `Wallet Balance`,rd.eStatus as `Status`,rd.eIsFeatured AS IsFeatured, rd.eIsPremium  AS IsPremium, rd.vTimeZone,(SELECT count(dv.iDriverVehicleId) FROM driver_vehicle AS dv WHERE dv.iDriverId=rd.iDriverId AND dv.eStatus != 'Deleted' AND dv.iMakeId != 0 AND dv.iModelId != 0 AND dv.eType != 'UberX') AS `count`,rd.vCountry,rd.iCompanyId FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId WHERE 1 = 1  $eStatus_sql $ssql $ssql1 $dri_ssql $ord";
    }
	
    $wallet_data = $obj->MySQLSelect("SELECT iUserId, SUM(COALESCE(CASE WHEN eType = 'Credit' THEN iBalance END,0)) - SUM(COALESCE(CASE WHEN eType = 'Debit' THEN iBalance END,0)) as balance FROM user_wallet WHERE eUserType = 'Driver' GROUP BY iUserId");
    $walletDataArr = array();
    $serverTimeZone = date_default_timezone_get();
    foreach ($wallet_data as $wallet_balance) {
        $walletDataArr[$wallet_balance['iUserId']] = $wallet_balance['balance'];
    }
    // filename for download
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $filename ="service_providers".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']);
        $sheet->setCellValue('B1', 'Company/Store Name');
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_EMAIL_TEXT']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_Vehicle']." Count");
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_SIGNUP_DATE_ADMIN']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
        $sheet->setCellValue('G1', $langage_lbl_admin['LBL_WALLET_BALANCE']);
        $sheet->setCellValue('H1', $langage_lbl_admin['LBL_Status']);
        if (ONLYDELIVERALL == 'No' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxEnable == "Yes"){
            $sheet->setCellValue('I1','IsFeatured');
            $sheet->setCellValue('J1','IsPremium');
        }
        $i = 2; 
        
        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
            // $result[0] = change_key($result[0], 'Driver Name', 'Provider Name');
            $result[0] = change_key($result[0], 'Driver Name', 'Driver Name');
        }
        if ($MODULES_OBJ->isStorePersonalDriverAvailable() > 0) {
            $result[0] = change_key($result[0], 'Company Name', 'Company Name');
        }
        //echo "<pre>";
        
        $result_timezone = $result[0]['vTimeZone'];
        $result_vTimeZone = $result[0]['vCountry'];
        unset($result[0]['vTimeZone']);
        unset($result[0]['vCountry']);
        //unset($result[0]['vTimeZone']);
        //echo implode("\t", array_keys($result[0])) . "\r\n";
        $result[0]['vTimeZone'] = $result_timezone;
        $result[0]['vCountry'] = $result_vTimeZone;

        $getCompanyData = $obj->MySQLSelect("SELECT eSystem,iCompanyId FROM company WHERE 1=1");
        $eSystemArr = array();
        for ($g = 0; $g < scount($getCompanyData); $g++) {
            $eSystemArr[$getCompanyData[$g]['iCompanyId']] = $getCompanyData[$g]['eSystem'];
        }

        foreach ($result as $value) {			
            $hideUfxColumn = 1;
            if (isset($eSystemArr[$value['iCompanyId']]) && strtoupper($eSystemArr[$value['iCompanyId']]) == "DELIVERALL") {
                $hideUfxColumn = 0;
            }
           
            $user_available_balance = 0;
            if (isset($walletDataArr[$value['Wallet Balance']])) {
                $user_available_balance = $walletDataArr[$value['Wallet Balance']];
            }
            $value['Wallet Balance'] = formateNumAsPerCurrency($user_available_balance, '');

            if(empty($value['vTimeZone']))
            {
                $timeZone_sql = "SELECT vTimeZone FROM country WHERE vCountryCode='".$value['vCountry']."' ";
                $get_timezone_data = $obj->MySQLSelect($timeZone_sql);
                $value['vTimeZone'] =  $get_timezone_data[0]['vTimeZone'];
            }
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value["Signup Date"],$value['vTimeZone'],$serverTimeZone) : $value["Signup Date"];
            $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $get_utc_time = DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']);
            $time_zone_difference_text = (!empty($get_utc_time)) ? " (UTC:".$get_utc_time.")" : "(UTC:00:00)";
            $value["Signup Date"] = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

            $sheet->setCellValue('A' . $i, $value["Driver Name"]);
            $sheet->setCellValue('B' . $i, clearEmail($value["Company Name"]));
            $sheet->setCellValue('C' . $i, clearEmail($value["Email"]));
            $sheet->setCellValue('D' . $i, clearEmail($value["count"]));
            $sheet->setCellValue('E' . $i, $value["Signup Date"]);
            $sheet->setCellValue('F' . $i, (!empty($value["vPhone"])) ? "(+". ($value["vPhoneCode"]).") ". clearPhone($value["vPhone"]) : "");
            $sheet->setCellValue('G' . $i, $value["Wallet Balance"]);
            $sheet->setCellValue('H' . $i, $value["Status"]);
            if (ONLYDELIVERALL == 'No' && ($APP_TYPE == 'UberX' || $APP_TYPE == 'Ride-Delivery-UberX') && $ufxEnable == "Yes"){
                $sheet->setCellValue('I'.$i,($hideUfxColumn > 0)?$value["IsFeatured"]:'-');
                $sheet->setCellValue('J'.$i,$value["IsPremium"]);
            }

            $i++;
        }		
        // Auto-size columns
        
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        if (ONLYDELIVERALL == 'Yes') {
            $heading = array(
                $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT'],
                'Email',
                'Signup Date',
                'Mobile',
                'Wallet Balance',
                'Status',
                'IsFeatured'
            );
        } else {
            $heading = array(
                $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT'],
                'Company Name',
                'Email',
                'Signup Date',
                'Mobile',
                'Wallet Balance',
                'Status',
                'IsFeatured'
            );
        }
        //echo "<pre>";
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            // $user_available_balance = $WALLET_OBJ->FetchMemberWalletBalance($row['Wallet Balance'], "Driver");
            $user_available_balance = 0;
            if (isset($walletDataArr[$row['Wallet Balance']])) {
                $user_available_balance = $walletDataArr[$row['Wallet Balance']];
            }
            $row['Wallet Balance'] = formateNumAsPerCurrency($user_available_balance, '');
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO, "L", "A4");
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']);
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            //echo $column_heading;
            if ($column_heading == $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']) {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Company Name' || $column_heading == 'Wallet Balance') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Email') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Signup Date') {
                $pdf->Cell(37, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Status' || $column_heading == 'IsFeatured') {
                $pdf->Cell(22, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column != 'vTimeZone') { 
                    $values = $key;
                    //echo $column."<br>";
                    if ($column == 'Driver Name') {
                        $values = clearName($key);
                    }
                    if ($column == 'Email') {
                        $values = clearEmail($key);
                    }
                    if ($column == 'Mobile') {
                        $values = clearPhone($key);
                    }
                    if ($column == 'Company Name') {
                        $values = clearCmpName($key);
                    }
                    if ($column == 'Driver Name') {
                        $pdf->Cell(35, 10, $values, 1, 0, "1");
                    } else if ($column == 'Company Name' || $column == 'Wallet Balance') {
                        $pdf->Cell(35, 10, $values, 1);
                    } else if ($column == 'Email') {
                        $pdf->Cell(50, 10, $values, 1);
                    } else if ($column == 'Signup Date') {
                        $date_format_data_array = array(
                            'langCode' => $default_lang,
                            'DateFormatForWeb' => 1
                        );
                        $date_format_data_array['tdate'] = (!empty($row['vTimeZone'])) ? converToTz($values,$row['vTimeZone'],$serverTimeZone) : $values;
                        $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                        $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($row['vTimeZone'],$date_format_data_array['tdate']).")";
                        $values = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;

                        $pdf->Cell(50, 10, $values, 1);
                    } else if ($column == 'Mobile') {
                        $pdf->Cell(30, 10, $values, 1);
                    } else if ($column == 'Status' || $column == 'IsFeatured') {
                        $pdf->Cell(22, 10, $values, 1);
                    } else {
                        $pdf->Cell(20, 10, $key, 1);
                    }
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//driver
//vehicles
if ($section == 'vehicles') {
    $eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : "";
    $ord = ' ORDER BY dv.iDriverVehicleId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY m.vMake ASC";
        } else {
            $ord = " ORDER BY m.vMake DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCompany ASC";
        } else {
            $ord = " ORDER BY c.vCompany DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY rd.vName ASC";
        } else {
            $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY dv.eType ASC";
        } else {
            $ord = " ORDER BY dv.eType DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY dv.eStatus ASC";
        } else {
            $ord = " ORDER BY dv.eStatus DESC";
        }
    }
    //End Sorting
    $dri_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $dri_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
    }
    // Start Search Parameters
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : "";
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dv.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if (ONLYDELIVERALL != 'Yes') {
                if ($eStatus != '') {
                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR c.vCompany LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')  AND dv.eStatus = '" . clean($eStatus) . "'";
                } else {
                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR c.vCompany LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')";
                }
            } else {
                if ($eStatus != '') {
                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')  AND dv.eStatus = '" . clean($eStatus) . "'";
                } else {
                    $ssql .= " AND (m.vMake LIKE '%" . $keyword . "%' OR CONCAT(rd.vName,' ',rd.vLastName) LIKE '%" . $keyword . "%')";
                }
            }
        }
    } else if ($eStatus != '' && $keyword == '' && $eType == '') {
        $ssql .= " AND dv.eStatus = '" . clean($eStatus) . "'";
    } else if ($eType != '' && $keyword == '' && $eStatus == '') {
        $ssql .= " AND dv.eType = '" . clean($eType) . "'";
    } else if ($eType != '' && $keyword == '' && $eStatus != '') {
        $ssql .= " AND dv.eStatus = '" . clean($eStatus) . "' AND dv.eType = '" . clean($eType) . "'";
    }
    // End Search Parameters
    if ($iDriverId != "") {
        $query1 = "SELECT COUNT(iDriverVehicleId) as total FROM driver_vehicle where iDriverId ='" . $iDriverId . "'";
        $totalData = $obj->MySQLSelect($query1);
        $total_vehicle = $totalData[0]['total'];
        if ($total_vehicle > 1) {
            $ssql .= " AND dv.iDriverId='" . $iDriverId . "'";
        }
    }
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    if ($eStatus != '') {
        $eStatus_sql = "";
    } else {
        $eStatus_sql = " AND dv.eStatus != 'Deleted' AND dv.eType != 'UberX'";
    }
    if (ONLYDELIVERALL != 'Yes') {
        if ($APP_TYPE == 'UberX') {
            $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total  FROM driver_vehicle AS dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId" . $eStatus_sql . $ssql . $dri_ssql;
        } else {
            $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId" . $eStatus_sql . $ssql . $dri_ssql;
        }
    } else {
        $sql = "SELECT COUNT(dv.iDriverVehicleId) AS Total FROM driver_vehicle AS dv, register_driver rd, make m, model md WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId" . $eStatus_sql . $ssql . $dri_ssql;
    }
    $totalData = $obj->MySQLSelect($sql);
    $total_results = $totalData[0]['Total'];
    $total_pages = ceil($total_results / $per_page); //total pages we going to have
    $show_page = 1;
    //-------------if page is setcheck------------------//
    $start = 0;
    $end = $per_page;
    if (isset($_GET['page'])) {
        $show_page = $_GET['page'];             //it will telles the current page
        if ($show_page > 0 && $show_page <= $total_pages) {
            $start = ($show_page - 1) * $per_page;
            $end = $start + $per_page;
        }
    }
    // display pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $tpages = $total_pages;
    if ($page <= 0) {
        $page = 1;
    }
    //Pagination End
    if (ONLYDELIVERALL != 'Yes') {
        if ($APP_TYPE == 'UberX') {
            $sql = "SELECT dv.iDriverVehicleId,dv.eStatus,CONCAT(rd.vName,' ',rd.vLastName) AS driverName,dv.vLicencePlate, c.vCompany FROM driver_vehicle dv, register_driver rd,company c

        WHERE 1 = 1   AND dv.iDriverId = rd.iDriverId  AND dv.iCompanyId = c.iCompanyId $eStatus_sql $ssql $dri_ssql";
        } else {
            if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
                $sql = "SELECT  CONCAT(m.vMake,' ', md.vTitle) AS Taxis, c.vCompany AS Company, CONCAT(rd.vName,' ',rd.vLastName) AS Driver,dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eStatus_sql $ssql $dri_ssql $ord ";
            } else {
                $sql = "SELECT  CONCAT(m.vMake,' ', md.vTitle) AS Taxis, c.vCompany AS Company, CONCAT(rd.vName,' ',rd.vLastName) AS Driver ,dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md, company c WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iCompanyId = c.iCompanyId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eStatus_sql $ssql $dri_ssql $ord ";
            }
        }
    } else {
        $sql = "SELECT  CONCAT(m.vMake,' ', md.vTitle) AS Taxis, CONCAT(rd.vName,' ',rd.vLastName) AS Driver ,dv.eStatus as Status FROM driver_vehicle dv, register_driver rd, make m, model md WHERE 1 = 1 AND dv.iDriverId = rd.iDriverId AND dv.iModelId = md.iModelId AND dv.iMakeId = m.iMakeId $eStatus_sql $ssql $dri_ssql $ord ";
    }
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'UberX') {
            $result[0] = change_key($result[0], 'Driver', 'Provider');
        }
        echo implode("\t", array_keys($result[0])) . "\r\n";
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'Taxis') {
                    $val;
                }
                if ($key == 'Company') {
                    $val = clearCmpName($val);
                }
                if ($key == 'Driver') {
                    $val = clearName($val);
                }
                if ($key == 'Status') {
                    $val;
                }
                echo $val . "\t";
            }
            echo "\r\n";
        }
    } else {
        if (ONLYDELIVERALL == 'Yes') {
            $heading = array(
                'Taxis',
                $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                'Status'
            );
        } else if ($APP_TYPE == 'Ride-Delivery-UberX' || $APP_TYPE == 'Ride-Delivery') {
            $heading = array(
                'Taxis',
                'Company',
                $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                'Status'
            );
        } else {
            $heading = array(
                'Taxis',
                'Company',
                $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'],
                'Status'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Taxis");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Taxis') {
                $pdf->Cell(65, 10, $column_heading, 1);
            } else if ($column_heading == 'Company') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN']) {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Taxis') {
                    $pdf->Cell(65, 10, $key, 1);
                } else if ($column == 'Company') {
                    $pdf->Cell(40, 10, clearCmpName($key), 1);
                } else if ($column == 'Driver') {
                    $pdf->Cell(40, 10, clearName($key), 1); //}
                } /* else if ($column == 'Sevice Type') {

                  $pdf->Cell(25, 10, $key, 1);

                  } */ else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $key, 1);
                } else {
                    $pdf->Cell(45, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//vehicles
//email_template
if ($section == 'email_template') {
    $ord = ' ORDER BY vSubject_' . $default_lang . ' ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vSubject_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vSubject_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vPurpose ASC";
        } else {
            $ord = " ORDER BY vPurpose DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (vSubject_" . $default_lang . " LIKE '%" . $keyword . "%' OR vPurpose LIKE '%" . $keyword . "%')";
        }
    }
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $tbl_name = 'email_templates';
    $sql = "SELECT vSubject_" . $default_lang . " as `Email Subject`, vPurpose as `Purpose` FROM " . $tbl_name . " WHERE eStatus = 'Active' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Email Subject',
            'Purpose'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Email Templates");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Email Subject') {
                $pdf->Cell(98, 10, $column_heading, 1);
            } else if ($column_heading == 'Purpose') {
                $pdf->Cell(98, 10, $column_heading, 1);
            } else {
                $pdf->Cell(8, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Email Subject') {
                    $pdf->Cell(98, 10, $key, 1);
                } else if ($column == 'Purpose') {
                    $pdf->Cell(98, 10, $key, 1);
                } else {
                    $pdf->Cell(8, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//email_template
//Restricted Area
if ($section == 'restrict_area') {
    $ord = ' ORDER BY lm.vLocationName ASC';
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY lm.vLocationName ASC";
        } else {
            $ord = " ORDER BY lm.vLocationName DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY ra.eRestrictType ASC";
        } else {
            $ord = " ORDER BY ra.eRestrictType DESC";
        }
    }
    if ($sortby == 6) {
        if ($order == 0) {
            $ord = " ORDER BY ra.eStatus ASC";
        } else {
            $ord = " ORDER BY ra.eStatus DESC";
        }
    }
    if ($sortby == 7) {
        if ($order == 0) {
            $ord = " ORDER BY ra.eType ASC";
        } else {
            $ord = " ORDER BY ra.eType DESC";
        }
    }
    //End Sorting
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'ra.eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes(clean($keyword)) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes(clean($keyword)) . "%'";
            }
        } else {
            $ssql .= " AND (lm.vLocationName LIKE '%" . clean($keyword) . "%' OR ra.eStatus LIKE '%" . clean($keyword) . "%' OR ra.eRestrictType LIKE '%" . clean($keyword) . "%' OR ra.eType LIKE '%" . clean($keyword) . "%')";
        }
    }
    $sql = "SELECT lm.vLocationName as Address, ra.eRestrictType AS Area, ra.eType AS Type, ra.eStatus AS Status FROM restricted_negative_area AS ra LEFT JOIN location_master AS lm ON lm.iLocationId=ra.iLocationId WHERE 1=1 $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Address',
            'Area',
            'Type',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Address");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Area') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == 'Address') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else {
                $pdf->Cell(40, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Area') {
                    $pdf->Cell(40, 10, $key, 1);
                } else if ($column == 'Address') {
                    $pdf->Cell(80, 10, $key, 1);
                } else {
                    $pdf->Cell(40, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//visit location
if ($section == 'visitlocation') {
    $ord = ' ORDER BY iVisitId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY tDestLocationName ASC";
        } else {
            $ord = " ORDER BY tDestLocationName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY tDestAddress ASC";
        } else {
            $ord = " ORDER BY tDestAddress DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (tDestLocationName LIKE '%" . $keyword . "%' OR tDestAddress LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT vSourceAddresss as SourceAddress, tDestAddress as DestAddress,eStatus as Status FROM visit_address where eStatus != 'Deleted' $ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
        $heading = array(
            'SourceAddress',
            'DestAddress',
            'Status'
        );
    } else {
        $heading = array(
            'SourceAddress',
            'DestAddress',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Visit Location");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'SourceAddress') {
                $pdf->Cell(75, 10, $column_heading, 1);
            } else if ($column_heading == 'DestAddress') {
                $pdf->Cell(75, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'SourceAddress') {
                    $pdf->Cell(75, 10, clearCmpName($key), 1);
                } else if ($column == 'DestAddress') {
                    $pdf->Cell(75, 10, clearName($key), 1); //}
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $key, 1);
                } else {
                    $pdf->Cell(45, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//hotel rider
if ($section == 'hotel_rider') {
    $ord = ' ORDER BY vName ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vName ASC";
        } else {
            $ord = " ORDER BY vName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vEmail ASC";
        } else {
            $ord = " ORDER BY vEmail DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY tRegistrationDate ASC";
        } else {
            $ord = " ORDER BY tRegistrationDate DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . $keyword . "%' OR vEmail LIKE '%" . $keyword . "%' OR vPhone LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT  CONCAT(vName,' ',vLastName) as Name,vEmail as Email,CONCAT(vPhoneCode,' ',vPhone) AS Mobile,eStatus as Status FROM hotel WHERE eStatus != 'Deleted' $ssql $rdr_ssql $ord";
    //die;
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'Name') {
                    $val = clearName($val);
                }
                if ($key == 'Email') {
                    $val = clearEmail($val);
                }
                if ($key == 'Mobile') {
                    $val = clearPhone($val);
                }
                echo $val . "\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array(
            'Name',
            'Email',
            'Mobile',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Hotel Riders");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Email') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Mobile') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Name') {
                    $values = clearName($key);
                }
                if ($column == 'Email') {
                    $values = clearEmail($key);
                }
                if ($column == 'Mobile') {
                    $values = clearPhone($key);
                }
                if ($column == 'Email') {
                    $pdf->Cell(55, 10, $values, 1);
                } else if ($column == 'Mobile') {
                    $pdf->Cell(45, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
if ($section == 'sub_service_category') {
    global $tconfig;
    $sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';
    $ord = ' ORDER BY iDisplayOrder ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vCategory_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vCategory_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY Servicetypes ASC";
        } else {
            $ord = " ORDER BY Servicetypes DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY iDisplayOrder DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'  AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    }else{
        $ssql .= " AND eStatus != 'Deleted'";
    }
    if ($parent_ufx_catid != "0") {
        $sql = "SELECT vCategory_" . $default_lang . " as SubCategory, (SELECT vCategory_" . $default_lang . " FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId='" . $sub_cid . "') as Category, (select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = " . $sql_vehicle_category_table_name . ".iVehicleCategoryId) as `Service Types`, iDisplayOrder as `Display Order`, eStatus as Status FROM " . $sql_vehicle_category_table_name . " WHERE (iParentId='" . $sub_cid . "' || iVehicleCategoryId='" . $parent_ufx_catid . "') AND  1 = 1 $ssql $ord";
    } else {
        $sql = "SELECT vCategory_" . $default_lang . " as SubCategory, (SELECT vCategory_" . $default_lang . " FROM " . $sql_vehicle_category_table_name . " WHERE iVehicleCategoryId='" . $sub_cid . "') as Category,(select count(iVehicleTypeId) from vehicle_type where vehicle_type.iVehicleCategoryId = " . $sql_vehicle_category_table_name . ".iVehicleCategoryId) as `Service Types`, iDisplayOrder as `Display Order`,eStatus as Status FROM " . $sql_vehicle_category_table_name . " WHERE (iParentId='" . $sub_cid . "' || iVehicleCategoryId='" . $parent_ufx_catid . "') $ssql $ord";
    }
    // filename for download
    if ($type == 'XLS') {
        $filename = $section . "_" . date('Ymd') . ".xls";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "SubCategory");
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_CATEGORY_TXT']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_WASHING_SERVICE_TYPES_TXT']);
        $sheet->setCellValue('D1', "Display Order");
        $sheet->setCellValue('E1',$langage_lbl_admin['LBL_Status']);
        
         $i = 2;
        foreach ($result as $value) {
            $sheet->setCellValue('A' . $i, clearName($value["SubCategory"]));
            $sheet->setCellValue('B' . $i, clearName($value["Category"]));
            $sheet->setCellValue('C' . $i, $value["Service Types"]);
            $sheet->setCellValue('D' . $i, $value["Display Order"]);
            $sheet->setCellValue('E' . $i, $value["Status"]);            
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        $heading = array(
            'SubCategory',
            'Category',
            'Service Types',
            'Display Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Sub Category");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Service Types') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                $id = "";
                if ($column == 'iVehicleCategoryId') {
                    $id2 = $key;
                }
                if ($column == 'SubCategory') {
                    $values = clearName($key);
                }
                if ($column == 'Display Order') {
                    $values = clearName($key);
                }
                if ($column == 'Status') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Service Types') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Display Order') {
                    $pdf->Cell(20, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
if ($section == 'service_category') {
    global $tconfig;
    $sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';
    $ord = ' ORDER BY iDisplayOrder ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vc.vCategory_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vc.vCategory_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vc.eStatus ASC";
        } else {
            $ord = " ORDER BY vc.eStatus DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY SubCategories ASC";
        } else {
            $ord = " ORDER BY SubCategories DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY iDisplayOrder ASC";
        } else {
            $ord = " ORDER BY iDisplayOrder DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vc.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND vc.(vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND vc.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND vc.(vCategory_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND vc.eStatus = '" . clean($eStatus) . "'";
    }
    $sql = "SELECT vc.vCategory_" . $default_lang . " as Category ,(select count(iVehicleCategoryId) from " . $sql_vehicle_category_table_name . " where iParentId=vc.iVehicleCategoryId) as SubCategories,vc.iDisplayOrder as `Display Order`,vc.eStatus as Status FROM " . $sql_vehicle_category_table_name . " as vc WHERE  vc.iParentId='0' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'Category') {
                    $val = clearName($val);
                }
                echo $val . "\t";
            }
            echo "\r\n";
        }
    } else {
        $heading = array(
            'Category',
            'SubCategories',
            'Display Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Category");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Category') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else if ($column_heading == 'Total') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Category') {
                    $values = clearName($key);
                }
                if ($column == 'Total') {
                    $values = $key;
                }
                if ($column == 'Category') {
                    $pdf->Cell(55, 10, $values, 1);
                } else if ($column == 'Total') {
                    $pdf->Cell(45, 10, $values, 1);
                } else if ($column == 'Display Order') {
                    $pdf->Cell(45, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//mask_number
if ($section == 'mask_number') {
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (mask_number LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT masknum_id as `Id`, mask_number as `Masking Number`,adding_date as `Added Date`, eStatus as `Status` FROM masking_numbers where 1 = 1 $ssql";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        // echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Id',
            'Masking Number',
            'Added Date',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Masking Numbers");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Id') {
                $pdf->Cell(18, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else {
                $pdf->Cell(55, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Id') {
                    $pdf->Cell(18, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(55, 10, $key, 1);
                } else {
                    $pdf->Cell(55, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//mask_number
//document master
//driver
if ($section == 'Document_Master') {
    $eType_value = isset($_REQUEST['eType_value']) ? stripslashes($_REQUEST['eType_value']) : "";
    $doc_userTypeValue = isset($_REQUEST['doc_userTypeValue']) ? stripslashes($_REQUEST['doc_userTypeValue']) : "";
    $ord = ' ORDER BY dm.doc_name ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY c.vCountry ASC";
        } else {
            $ord = " ORDER BY c.vCountry DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY dm.doc_usertype ASC";
        } else {
            $ord = " ORDER BY dm.doc_usertype DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY dm.doc_name ASC";
        } else {
            $ord = " ORDER BY dm.doc_name DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY dm.status ASC";
        } else {
            $ord = " ORDER BY dm.status DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY dm.eType ASC";
        } else {
            $ord = " ORDER BY dm.eType DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eType_value != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dm.eType = '" . clean($eType_value) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
            if ($doc_userTypeValue != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND dm.doc_usertype = '" . clean($doc_userTypeValue) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if ($eType_value != '') {
                $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%') AND dm.eType = '" . clean($eType_value) . "'";
            } else {
                $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%')";
            }
            if ($doc_userTypeValue != '') {
                $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%') AND dm.eType = '" . clean($eType_value) . "' AND dm.doc_usertype = '" . clean($doc_userTypeValue) . "'";
            } else {
                $ssql .= " AND (c.vCountry LIKE '%" . $keyword . "%' OR dm.doc_usertype LIKE '%" . $keyword . "%' OR dm.doc_name LIKE '%" . $keyword . "%' OR dm.status LIKE '%" . $keyword . "%')";
            }
        }
    } else if ($eType_value != '' && $keyword == '') {
        $ssql .= " AND dm.eType = '" . clean($eType_value) . "'";
    } else if ($doc_userTypeValue != '' && $keyword == '') {
        $ssql .= " AND dm.doc_usertype = '" . clean($doc_userTypeValue) . "'";
    }
    if ($eType_value != '') {
        $ssql .= " AND dm.doc_usertype != 'company'";
    }
    if ($option == "dm.status") {
        $eStatussql = " AND dm.status = '$keyword'";
    } else {
        $eStatussql = " AND dm.status != 'Deleted'";
    }
    $dri_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $dri_ssql = " And dm.doc_instime > '" . WEEK_DATE . "'";
    }
    /*if ($APP_TYPE == 'Ride-Delivery') {

        $eTypeQuery = " AND (dm.eType='Ride' OR dm.eType='Delivery')";

    } else if ($APP_TYPE == 'Ride-Delivery-UberX') {

        $eTypeQuery = " AND (dm.eType='Ride' OR dm.eType='Delivery' OR dm.eType='UberX')";

    } else {

        $eTypeQuery = " AND dm.eType='" . $APP_TYPE . "'";

    }*/
    if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
        $sql = "SELECT if(c.vCountry IS NULL,'All',c.vCountry) as Country,dm.doc_name as `Document Name`,dm.doc_usertype as `Document For`, dm.status as Status FROM `document_master` AS dm

 LEFT JOIN `country` AS c ON c.vCountryCode=dm.country

 WHERE 1=1 $eStatussql $eTypeQuery $ssql $dri_ssql $ord";
    } else {
        $sql = "SELECT if(c.vCountry IS NULL,'All',c.vCountry) as Country,dm.doc_name as `Document Name`,dm.doc_usertype as `Document For`, dm.status as Status FROM `document_master` AS dm

  LEFT JOIN `country` AS c ON c.vCountryCode=dm.country

  WHERE 1=1 $eStatussql $eTypeQuery $ssql $dri_ssql $ord";
    }
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        // echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        echo implode("\t", array_keys($result[0])) . "\r\n";
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($val == 'UberX') {
                    $val = 'Other Services';
                }
                echo $val . "\t";
            }
            echo "\r\n";
        }
    } else {
        if ($APP_TYPE == 'Ride-Delivery' || $APP_TYPE == 'Ride-Delivery-UberX') {
            $heading = array(
                'Country',
                'Document Name',
                'Document For',
                'Status'
            );
        } else {
            $heading = array(
                'Country',
                'Document Name',
                'Document For',
                'Status'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Documents");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Country') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Document For') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Document Name') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Service Type') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Country') {
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Document For') {
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Document Name') {
                    $pdf->Cell(50, 10, $values, 1);
                } else if ($column == 'Service Type') {
                    if ($values == 'UberX') {
                        $values = 'Other Services';
                    }
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(35, 10, $values, 1);
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//document master
// review page
if ($section == 'review') {
    $reviewtype = isset($_REQUEST['reviewtype']) ? $_REQUEST['reviewtype'] : 'Driver';
    $adm_ssql = "";
    if (SITE_TYPE == 'Demo') {
        if ($reviewtype == "Driver") {
            $adm_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        } else {
            $adm_ssql = " And ru.tRegistrationDate > '" . WEEK_DATE . "'";
        }
    }
    $type = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Driver';
    $reviewtype = $type;
//Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY iRatingId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY t.vRideNo ASC";
        } else {
            $ord = " ORDER BY t.vRideNo DESC";
        }
    }
    if ($sortby == 2) {
        if ($reviewtype == 'Driver') {
            if ($order == 0) {
                $ord = " ORDER BY rd.vName ASC";
            } else {
                $ord = " ORDER BY rd.vName DESC";
            }
        } else {
            if ($order == 0) {
                $ord = " ORDER BY ru.vName ASC";
            } else {
                $ord = " ORDER BY ru.vName DESC";
            }
        }
    }
    if ($sortby == 6) {
        if ($reviewtype == 'Driver') {
            if ($order == 0) {
                $ord = " ORDER BY ru.vName ASC";
            } else {
                $ord = " ORDER BY ru.vName DESC";
            }
        } else {
            if ($order == 0) {
                $ord = " ORDER BY rd.vName ASC";
            } else {
                $ord = " ORDER BY rd.vName DESC";
            }
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY r.vRating1 ASC";
        } else {
            $ord = " ORDER BY r.vRating1 DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY r.tDate ASC";
        } else {
            $ord = " ORDER BY r.tDate DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY r.vMessage ASC";
        } else {
            $ord = " ORDER BY r.vMessage DESC";
        }
    }
//End Sorting
    $adm_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $adm_ssql = " And ru.tRegistrationDate > '" . WEEK_DATE . "'";
    }
// Start Search Parameters
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'r.eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . clean($keyword) . "'";
            } else {
                $option_new = $option;
                if ($option == 'drivername') {
                    $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
                }
                if ($option == 'ridername') {
                    $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";
                }
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (t.vRideNo LIKE '%" . clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . clean($keyword) . "%' OR r.vRating1 LIKE '%" . clean($keyword) . "%')";
        }
    }
// End Search Parameters
//Pagination Start
    $chkusertype = "";
    if ($type == "Driver") {
        $chkusertype = "Passenger";
    } else {
        $chkusertype = "Driver";
    }
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    $sql = "SELECT count(r.iRatingId) as Total FROM ratings_user_driver as r LEFT JOIN trips as t ON r.iTripId=t.iTripId LEFT JOIN register_driver as rd ON rd.iDriverId=t.iDriverId 	LEFT JOIN register_user as ru ON ru.iUserId=t.iUserId WHERE eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' AND t.eSystem = 'General' $ssql $adm_ssql";
    $totalData = $obj->MySQLSelect($sql);
    $total_results = $totalData[0]['Total'];
    $total_pages = ceil($total_results / $per_page); //total pages we going to have
    $show_page = 1;
//-------------if page is setcheck------------------//
    if (isset($_GET['page'])) {
        $show_page = $_GET['page'];             //it will telles the current page
        if ($show_page > 0 && $show_page <= $total_pages) {
            $start = ($show_page - 1) * $per_page;
            $end = $start + $per_page;
        } else {
            // error - show first set of results
            $start = 0;
            $end = $per_page;
        }
    } else {
        // if page isn't set, show first set of results
        $start = 0;
        $end = $per_page;
    }
// display pagination
    $page = isset($_GET['page']) ? intval($_GET['page']) : 0;
    $tpages = $total_pages;
    if ($page <= 0) {
        $page = 1;
    }
//Pagination End
    $chkusertype = "";
    if ($type == "Driver") {
        $chkusertype = "Passenger";
    } else {
        $chkusertype = "Driver";
    }
    $number_txt = $langage_lbl_admin['LBL_RIDE_TXT_ADMIN'] . ' Number';
    $driver_txt = $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'] . ' Name';
    $user_txt = $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN'] . ' Name';
    $sql = "SELECT t.vRideNo as '" . $number_txt . "',CONCAT(rd.vName,' ',rd.vLastName) as '" . $driver_txt . "',CONCAT(ru.vName,' ',ru.vLastName) as '" . $user_txt . "',r.vRating1 as Rate,r.tDate as Date,r.vMessage as Comment,rd.vTimeZone FROM ratings_user_driver as r LEFT JOIN trips as t ON r.iTripId=t.iTripId LEFT JOIN register_driver as rd ON rd.iDriverId=t.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=t.iUserId WHERE 1=1 AND r.eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' AND t.eSystem = 'General' $ssql $adm_ssql $ord";
    $type = 'XLS';
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
        $filename = "trips_review_".$timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        // echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Ride/Job Number");
        if($reviewtype=="Passenger")
        {
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_USER_NAME_LBL_TXT']);
            $sheet->setCellValue('C1', $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']);
        }
        else
        {
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']);
            $sheet->setCellValue('C1', $langage_lbl_admin['LBL_USER_NAME_LBL_TXT']);
        }
        
        
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_RATE']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_DATE_TXT']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_COMMENT_TXT']);

        $i = 2;
        foreach ($result as $value) {
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['Date'],$value['vTimeZone'],$serverTimeZone) : $value['Date'];
            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['Date'] = $get_date_format['tDisplayDate'];//DateTime($val);
            $sheet->setCellValue('A' . $i, $value[$number_txt]);
            if($reviewtype=="Passenger")
            {
                $sheet->setCellValue('B' . $i, $value[$user_txt]);
                $sheet->setCellValue('C' . $i, $value[$driver_txt]);
            }
            else
            {
                $sheet->setCellValue('B' . $i, $value[$driver_txt]);
                $sheet->setCellValue('C' . $i, $value[$user_txt]);
            }

            
            $sheet->setCellValue('D' . $i, $value["Rate"]);  
            $sheet->setCellValue('E' . $i, $value["Date"]); 
            $sheet->setCellValue('F' . $i, $value["Comment"]); 
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        // $timeZone = $result[0]['vTimeZone'];
        // unset($result[0]['vTimeZone']);
        // echo implode("\t", array_keys($result[0])) . "\r\n";
        // $result[0]['vTimeZone'] = $timeZone;
        // foreach ($result as $value) {
        //     foreach ($value as $key => $val) {
        //         if ($key == 'vTimeZone') {
        //             continue;
        //         }
        //         if ($key == 'RiderNumber') {
        //             $val = $val;
        //         }
        //         if ($key == 'DriverName') {
        //             $val = clearName($val);
        //         }
        //         if ($key == 'RiderName') {
        //             $val = clearName($val);
        //         }
        //         if ($reviewtype == "Driver") {
        //             if ($key == 'DriverName') {
        //                 $val = $val;
        //             }
        //         } else {
        //             if ($key == 'RiderName') {
        //                 $val = $val;
        //             }
        //         }
        //         if ($key == 'AverageRate') {
        //             $val = $val;
        //         }
        //         if ($reviewtype == "Driver") {
        //             if ($key == 'RiderName') {
        //                 $val = $val;
        //             }
        //         } else {
        //             if ($key == 'DriverName') {
        //                 $val = $val;
        //             }
        //         }
        //         if ($key == 'Rate') {
        //             $val = $val;
        //         }
        //         if ($key == 'Date') {
        //             $date_format_data_array = array(
        //                 'langCode' => $default_lang,
        //                 'DateFormatForWeb' => 1
        //             );
        //             $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($val,$value['vTimeZone'],$serverTimeZone) : $val;
        //             $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        //             $val = $get_date_format['tDisplayDate'];//DateTime($val);
        //         }
        //         if ($key == 'Comment') {
        //             $val = $val;
        //         }
        //         echo $val . "\t";
        //     }
        //     echo "\r\n";
        // }
    } else {
        if ($reviewtype == "Driver") {
            $heading = array(
                'RiderNumber',
                'DriverName',
                'AverageRate',
                'RiderName',
                'Rate',
                'Date',
                'Comment'
            );
        } else {
            $heading = array(
                'RiderNumber',
                'RiderName',
                'AverageRate',
                'DriverName',
                'Rate',
                'Date',
                'Comment'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Review");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'RiderNumber') {
                $pdf->Cell(22, 10, $column_heading, 1);
            } else if ($column_heading == 'DriverName') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == 'AverageRate') {
                $pdf->Cell(21, 10, $column_heading, 1);
            } else if ($column_heading == 'RiderName') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Rate') {
                $pdf->Cell(10, 10, $column_heading, 1);
            } else if ($column_heading == 'Date') {
                $pdf->Cell(42, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'RiderNumber') {
                    $values = clearPhone($key);
                }
                if ($column == 'DriverName') {
                    $values = clearName($key);
                }
                if ($column == 'RiderName') {
                    $values = clearName($key);
                }
                if ($column == 'Date') {
                    $values = DateTime($key);
                }
                DateTime($val);
                if ($column == 'RiderNumber') {
                    $pdf->Cell(22, 10, $values, 1);
                } else if ($column == 'DriverName') {
                    $pdf->Cell(40, 10, $values, 1);
                } else if ($column == 'AverageRate') {
                    $pdf->Cell(21, 10, $values, 1);
                } else if ($column == 'RiderName') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Rate') {
                    $pdf->Cell(10, 10, $values, 1);
                } else if ($column == 'Date') {
                    $date_format_data_array = array(
                        'langCode' => $default_lang,
                        'DateFormatForWeb' => 1
                    );
                    $date_format_data_array['tdate'] = (!empty($result['vTimeZone'])) ? converToTz($values,$result['vTimeZone'],$serverTimeZone) : $values;
                    $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $values = $get_date_format['tDisplayDate'];//DateTime($val);
                    $pdf->Cell(42, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//sms_template
if ($section == 'sms_template') {
    $ord = " ORDER BY vEmail_Code ASC";
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vEmail_Code ASC";
        } else {
            $ord = " ORDER BY vEmail_Code DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY vSubject_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vSubject_" . $default_lang . " DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND vEmail_Code LIKE '%" . $keyword . "%' OR vSubject_" . $default_lang . " LIKE '%" . $keyword . "%'";
        }
    }
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $tbl_name = 'send_message_templates';
    $sql = "SELECT vSubject_" . $default_lang . " as `SMS Title`,vEmail_Code as `SMS Code` FROM " . $tbl_name . " WHERE eStatus = 'Active' $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        // echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'SMS Title',
            'SMS Code'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "SMS Templates");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'SMS Title') {
                $pdf->Cell(82, 10, $column_heading, 1);
            } else if ($column_heading == 'SMS Code') {
                $pdf->Cell(82, 10, $column_heading, 1);
            } else {
                $pdf->Cell(82, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'SMS Title') {
                    $pdf->Cell(82, 10, $key, 1);
                } else if ($column == 'SMS Code') {
                    $pdf->Cell(82, 10, $key, 1);
                } else {
                    $pdf->Cell(82, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
// locationwise fare
if ($section == 'airportsurcharge_fare') {
    $ord = ' ORDER BY ls.iLocatioId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY ls.iLocationIds ASC";
        } else {
            $ord = " ORDER BY ls.iLocationIds DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY ls.fpickupsurchargefare ASC";
        } else {
            $ord = " ORDER BY ls.fpickupsurchargefare DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY ls.fdropoffsurchargefare ASC";
        } else {
            $ord = " ORDER BY ls.fdropoffsurchargefare DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY ls.eStatus ASC";
        } else {
            $ord = " ORDER BY ls.eStatus DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY vt.vVehicleType ASC";
        } else {
            $ord = " ORDER BY vt.vVehicleType DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND lm2.vLocationName LIKE '%" . $keyword . "%' OR ls.eStatus LIKE '%" . $keyword . "%' OR vt.vVehicleType LIKE '%" . $keyword . "%'";
        }
    }
    if ($option == "eStatus") {
        $eStatussql = " AND ls.eStatus = '" . ucfirst($keyword) . "'";
    } else {
        $eStatussql = " AND ls.eStatus != 'Deleted'";
    }
    $sql = "SELECT lm2.vLocationName as `Location Name`, ls.fpickupsurchargefare as `Pickup Surcharge Fare`,ls.fpickupsurchargefare as `Dropoff Surcharge Fare`,vt.vVehicleType  as `Vehicle Type`,ls.eStatus as `Status` FROM `airportsurcharge_fare` ls left join location_master lm2 on ls.iLocationIds = lm2.iLocationId left join vehicle_type as vt on vt.iVehicleTypeId=ls.iVehicleTypeId WHERE 1 = 1 $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Location Name',
            'Pickup Surcharge Fare',
            'Dropoff Surcharge Fare',
            'Vehicle Type',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Airport surcharge Fare");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Location Name') {
                $pdf->Cell(65, 10, $column_heading, 1);
            } else if ($column_heading == 'Pickup Surcharge Fare') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Dropoff Surcharge Fare') {
                $pdf->Cell(45, 10, $column_heading, 1);
            } else if ($column_heading == 'Vehicle Type') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(30, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Location Name') {
                    $pdf->Cell(65, 10, $key, 1);
                } else if ($column == 'Pickup Surcharge Fare') {
                    $pdf->Cell(45, 10, $key, 1);
                } else if ($column == 'Dropoff Surcharge Fare') {
                    $pdf->Cell(45, 10, $key, 1);
                } else if ($column == 'Vehicle Type') {
                    $pdf->Cell(25, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(20, 10, $key, 1);
                } else {
                    $pdf->Cell(30, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
// locationwise fare
if ($section == 'locationwise_fare') {
    $ord = ' ORDER BY ls.iLocatioId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY lm1.vLocationName ASC";
        } else {
            $ord = " ORDER BY lm1.vLocationName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY lm2.vLocationName ASC";
        } else {
            $ord = " ORDER BY lm2.vLocationName DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY ls.fFlatfare ASC";
        } else {
            $ord = " ORDER BY ls.fFlatfare DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY ls.eStatus ASC";
        } else {
            $ord = " ORDER BY ls.eStatus DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY vt.vVehicleType ASC";
        } else {
            $ord = " ORDER BY vt.vVehicleType DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND lm1.vLocationName LIKE '%" . $keyword . "%' OR lm2.vLocationName LIKE '%" . $keyword . "%' OR ls.fFlatfare LIKE '%" . $keyword . "%' OR ls.eStatus LIKE '%" . $keyword . "%' OR vt.vVehicleType LIKE '%" . $keyword . "%'";
        }
    }
    if ($option == "eStatus") {
        $eStatussql = " AND ls.eStatus = '" . ucfirst($keyword) . "'";
    } else {
        $eStatussql = " AND ls.eStatus != 'Deleted'";
    }
    $sql = "SELECT lm2.vLocationName as `Source LocationName`,lm1.vLocationName as `Destination LocationName`,ls.fFlatfare as `Flat Fare`,vt.vVehicleType as `Vehicle Type`,ls.eStatus as `Status` FROM `location_wise_fare` ls left join location_master lm1 on ls.iToLocationId = lm1.iLocationId left join location_master lm2 on ls.iFromLocationId = lm2.iLocationId left join vehicle_type as vt on vt.iVehicleTypeId=ls.iVehicleTypeId  WHERE 1 = 1 $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Source LocationName',
            'Destination LocationName',
            'Flat Fare',
            'Vehicle Type',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Locationwise Fare");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Source LocationName') {
                $pdf->Cell(65, 10, $column_heading, 1);
            } else if ($column_heading == 'Destination LocationName') {
                $pdf->Cell(65, 10, $column_heading, 1);
            } else if ($column_heading == 'Flat Fare') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(30, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Source LocationName') {
                    $pdf->Cell(65, 10, $key, 1);
                } else if ($column == 'Destination LocationName') {
                    $pdf->Cell(65, 10, $key, 1);
                } else if ($column == 'Flat Fare') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(20, 10, $key, 1);
                } else {
                    $pdf->Cell(30, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
// locationwise fare
//FoodMenu
if ($section == 'FoodMenu') {
    global $MODULES_OBJ;

    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY f.iFoodMenuId DESC';
    if ($sortby == 1) {
        if ($order == 0) $ord = " ORDER BY f.vMenu_" . $default_lang . " ASC"; else
            $ord = " ORDER BY f.vMenu_" . $default_lang . " DESC";
    }
    if ($sortby == 2) {
        if ($order == 0) $ord = " ORDER BY c.vCompany ASC"; else
            $ord = " ORDER BY c.vCompany DESC";
    }
    if ($sortby == 3) {
        if ($order == 0) $ord = " ORDER BY f.iDisplayOrder ASC"; else
            $ord = " ORDER BY f.iDisplayOrder DESC";
    }
    if ($sortby == 4) {
        if ($order == 0) $ord = " ORDER BY MenuItems ASC"; else
            $ord = " ORDER BY MenuItems DESC";
    }
    if ($sortby == 5) {
        if ($order == 0) $ord = " ORDER BY f.eStatus ASC"; else
            $ord = " ORDER BY f.eStatus DESC";
    }
    //End Sorting

    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
    $select_cat = isset($_REQUEST['selectcategory']) ? stripslashes($_REQUEST['selectcategory']) : "";
    $ssql = '';
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%' AND f.eStatus = '" . clean($eStatus) . "'";
            }
            if ($select_cat != "") {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                }
            }
            if ($select_cat != "" && $eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND f.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND f.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                }
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%'";
            }
        } else {
            if ($eStatus == '' && $select_cat != "" && $keyword_new != "") {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else if ($eStatus != '' && $select_cat != "") {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND f.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else if ($eStatus != '') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND f.eStatus = '" . clean($eStatus) . "'";
            } else if ($select_cat != "") {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND f.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword) . "%' OR f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
            }
        }
    } else if ($eStatus != '' && $select_cat != "" && $keyword == '') {
        $ssql .= " AND f.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
    } else if ($eStatus != '' && $keyword == '' && $select_cat == "") {
        $ssql .= " AND f.eStatus = '" . clean($eStatus) . "'";
    } else if ($eStatus == '' && $keyword == '' && $select_cat != "") {
        $ssql .= " AND sc.iServiceId = '" . clean($select_cat) . "'";
    }
    // End Search Parameters
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    if ($eStatus != '') {
        $eStatussql = "";
    } else {
        $eStatussql = " AND f.eStatus != 'Deleted'";
    }
    //$sql = "SELECT COUNT(f.iFoodMenuId) AS Total FROM food_menu as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId WHERE 1 = 1  $eStatussql $ssql $dri_ssql";

    if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
        $ssql .= " AND (c.iServiceId IN (" . $enablesevicescategory . ")";
        $enablesevicescategory = str_replace(",", "|", $enablesevicescategory);
        $ssql .= " OR c.iServiceIdMulti REGEXP '(^|,)(" . $enablesevicescategory . ")(,|$)') ";
        $fsql = " IF(f.iServiceId != 0, f.iServiceId, c.iServiceId) as iServiceId ";
        $joinsql = " FIND_IN_SET(sc.iServiceId, c.iServiceId) OR FIND_IN_SET(sc.iServiceId, c.iServiceIdMulti) ";
        if (!empty($select_cat)) {
            $ssql .= " AND f.iServiceId = '$select_cat' ";
        }
    } else {
        $ssql .= " AND c.iServiceId IN (" . $enablesevicescategory . ")";
        $fsql = " c.iServiceId ";
        $joinsql = " sc.iServiceId = c.iServiceId ";
    }

    if (!empty($eStatus)) {
        $eQuery = "";
    } else {
        $eQuery = " AND f.eStatus != 'Deleted'";
    }

    $getMenuItemCount = $obj->MySQLSelect("SELECT iFoodMenuId,count(iMenuItemId) as menuItemCnt FROM menu_items WHERE eStatus != 'Deleted' GROUP BY iFoodMenuId");
    $menuItemCountArr = array();
    for ($mi = 0; $mi < scount($getMenuItemCount); $mi++) {
        $menuItemCountArr[$getMenuItemCount[$mi]['iFoodMenuId']] = $getMenuItemCount[$mi]['menuItemCnt'];
    }
    $sql = "SELECT f.vMenu_" . $default_lang . " as Title,c.vCompany as Store,f.iDisplayOrder as `Display Order`,(select count(iMenuItemId) from menu_items where iFoodMenuId = f.iFoodMenuId AND eStatus !='Deleted') as MenuItems, f.eStatus as Status FROM  `food_menu` as f LEFT JOIN company c ON f.iCompanyId = c.iCompanyId left join service_categories as sc on $joinsql WHERE 1=1 AND f.eBuyAnyService = 'No' $eQuery $ssql GROUP BY f.iFoodMenuId $ord";
	
    // filename for download
    if ($type == 'XLS') {
        $filename = "Item_categories.xls";
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_TITLE_TXT_ADMIN']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_STORE']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_ITEMS']);
        $sheet->setCellValue('D1', "Display Order");
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
		
        foreach ($result as $value) {
			$menuItemCount = 0;
                if(isset($menuItemCountArr[$data_drv[$i]['iFoodMenuId']])){
                $menuItemCount = $menuItemCountArr[$data_drv[$i]['iFoodMenuId']];
            }
            $data_drv['MenuItems'] = $menuItemCount;			
			//print_r($menuItemCountArr); die;
            $sheet->setCellValue('A' . $i, clearName($value["Title"]));
            $sheet->setCellValue('B' . $i, clearName($value["Store"]));
            $sheet->setCellValue('C' . $i, $value["MenuItems"]);
            $sheet->setCellValue('D' . $i, $value["Display Order"]);
            $sheet->setCellValue('E' . $i, $value["Status"]);            
            $i++;
        }
		
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

    } else {
        $heading = array(
            'Title',
            'Store',
            'Display Order',
            'Items',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Item Categories");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Store') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Items') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Title') {
                    $values = clearName($key);
                }
                if ($column == 'Store') {
                    $values = clearEmail($key);
                }
                if ($column == 'Display Order') {
                    $values = clearPhone($key);
                }
                if ($column == 'Status') {
                    $values = clearCmpName($key);
                }
                if ($column == 'Title') {
                    $pdf->Cell(50, 10, $values, 1, 0, "1");
                } else if ($column == 'Store') {
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Display Order') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Items') {
                    $pdf->Cell(30, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(35, 10, $values, 1);
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//FoodMenu
//MenuItems
if ($section == 'MenuItems') {

    $menu_itemid = isset($_REQUEST['menu_itemid']) ? $_REQUEST['menu_itemid'] : "";
    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY mi.iMenuItemId DESC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY mi.vItemType_" . $default_lang . " ASC";
        else
            $ord = " ORDER BY mi.vItemType_" . $default_lang . " DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY c.vCompany ASC";
        else
            $ord = " ORDER BY c.vCompany DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY f.vMenu_" . $default_lang . " ASC";
        else
            $ord = " ORDER BY f.vMenu_" . $default_lang . " DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY mi.iDisplayOrder ASC";
        else
            $ord = " ORDER BY mi.iDisplayOrder DESC";
    }
    if ($sortby == 5) {
        if ($order == 0)
            $ord = " ORDER BY mi.eStatus ASC";
        else
            $ord = " ORDER BY mi.eStatus DESC";
    }
    //End Sorting
    // Start Search Parameters
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $select_cat = isset($_REQUEST['selectcategory']) ? stripslashes($_REQUEST['selectcategory']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
    $ssql = '';
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND mi.eStatus = '" . clean($eStatus) . "'";
            }
            if ($select_cat != "") {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                }
            }
            if ($select_cat != "" && $eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND mi.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND mi.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "' ";
                }
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";
            }
        } else {
            if ($eStatus == '' && $select_cat != "" && $keyword_new != "") {
                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else if ($eStatus != '' && $select_cat != "") {
                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND mi.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else if ($eStatus != '') {
                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND mi.eStatus = '" . clean($eStatus) . "'";
            } else if ($select_cat != "") {
                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . clean($keyword) . "%') AND mi.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
            } else {
                $ssql .= " AND (f.vMenu_" . $default_lang . " LIKE '%" . clean($keyword) . "%' OR c.vCompany LIKE '%" . clean($keyword) . "%' OR mi.vItemType_" . $default_lang . " LIKE '%" . clean($keyword) . "%')";
            }
        }
    } else if ($eStatus != '' && $select_cat != "" && $keyword == '') {
        $ssql .= " AND mi.eStatus = '" . clean($eStatus) . "' AND sc.iServiceId = '" . clean($select_cat) . "'";
    } else if ($eStatus != '' && $keyword == '' && $select_cat == "") {
        $ssql .= " AND mi.eStatus = '" . clean($eStatus) . "'";
    } else if ($eStatus == '' && $keyword == '' && $select_cat != "") {
        $ssql .= " AND sc.iServiceId = '" . clean($select_cat) . "'";
    }
    // End Search Parameters
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    if ($eStatus != '') {
        //$eStatussql = " AND c.eStatus != 'Deleted' AND f.eStatus != 'Deleted'";
        $eStatussql = " AND f.eStatus != 'Deleted'";
    } else {
       // $eStatussql = " AND c.eStatus != 'Deleted' AND f.eStatus != 'Deleted' AND mi.eStatus != 'Deleted'";
       $eStatussql = "  AND f.eStatus != 'Deleted' AND mi.eStatus != 'Deleted'";
    }
    if (!empty($menu_itemid)) {
        $ssql .= " AND f.iFoodMenuId = '" . $menu_itemid . "'";
    }
    $cmp_name = "";
    if ($menu_itemid != "") {
        $ssql .= " AND f.iFoodMenuId='" . $menu_itemid . "'";
        $sql = "select vMenu_" . $default_lang . " from food_menu where iFoodMenuId = '" . $menu_itemid . "'";
        $data_cmp1 = $obj->MySQLSelect($sql);
        $cmp_name = $data_cmp1[0]['vMenu_' . $default_lang];
        $keyword = $cmp_name;
    }
    if ($MODULES_OBJ->isEnableStoreMultiServiceCategories()) {
        $ssql .= " AND (c.iServiceId IN (" . $enablesevicescategory . ")";
        $enablesevicescategory = str_replace(",", "|", $enablesevicescategory);
        $ssql .= " OR c.iServiceIdMulti REGEXP '(^|,)(" . $enablesevicescategory . ")(,|$)') ";
        $fsql = " IF(f.iServiceId != 0, f.iServiceId, c.iServiceId) as iServiceId ";
        $joinsql = " FIND_IN_SET(sc.iServiceId, c.iServiceId) OR FIND_IN_SET(sc.iServiceId, c.iServiceIdMulti) ";
        if (!empty($select_cat)) {
            $ssql .= " AND f.iServiceId = '$select_cat' ";
        }
    } else {
        $ssql .= " AND c.iServiceId IN (" . $enablesevicescategory . ")";
        $fsql = " c.iServiceId ";
        $joinsql = " sc.iServiceId = c.iServiceId ";
    }

    $sql = "SELECT mi.vItemType_" . $default_lang . " as Item, f.vMenu_" . $default_lang . " as Category, c.vCompany as Store, mi.iDisplayOrder as `Display Order`,mi.eStatus as Status FROM  `menu_items` as mi INNER JOIN food_menu f ON f.iFoodMenuId = mi.iFoodMenuId INNER JOIN company as c on c.iCompanyId=f.iCompanyId left join service_categories as sc on $joinsql WHERE 1=1 AND f.eBuyAnyService = 'No' $eStatussql $ssql $dri_ssql GROUP BY mi.iMenuItemId $ord ";

    // filename for download
    if ($type == 'XLS') {
        $filename = "items_".$timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_ITEM']);
        $sheet->setCellValue('B1', "Item ".$langage_lbl_admin['LBL_CATEGORY_TXT']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_STORE_NAME_FOR_GENIE']);
        $sheet->setCellValue('D1', "Display Order");
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_Status']);

        $i = 2;
        foreach ($result as $value) {
            $sheet->setCellValue('A' . $i, clearName($value["Item"]));
            $sheet->setCellValue('B' . $i, clearName($value["Category"]));
            $sheet->setCellValue('C' . $i, clearName($value["Store"]));
            $sheet->setCellValue('D' . $i, clearPhone($value["Display Order"]));
            $sheet->setCellValue('E' . $i, $value["Status"]);            
            $i++;
        }
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    } else {
        $heading = array(
            'Item',
            'Category',
            'Store',
            'Display Order',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Items");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Item') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Category') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else if ($column_heading == 'Store') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else {
                $pdf->Cell(20, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Item') {
                    $values = clearName($key);
                }
                if ($column == 'Category') {
                    $values = clearName($key);
                }
                if ($column == 'Store') {
                    $values = clearEmail($key);
                }
                if ($column == 'Display Order') {
                    $values = clearPhone($key);
                }
                if ($column == 'Status') {
                    $values = clearCmpName($key);
                }
                if ($column == 'Item') {
                    $pdf->Cell(50, 10, $values, 1);
                } else if ($column == 'Category') {
                    $pdf->Cell(50, 10, $values, 1);
                } else if ($column == 'Store') {
                    $pdf->Cell(35, 10, $values, 1);
                } else if ($column == 'Display Order') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(20, 10, $values, 1);
                } else {
                    $pdf->Cell(20, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//MenuItems
//cuisine
if ($section == 'cuisine') {
    $ord = ' ORDER BY c.cuisineName_' . $default_lang . ' ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY c.cuisineName_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY c.cuisineName_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY c.eStatus ASC";
        } else {
            $ord = " ORDER BY c.eStatus DESC";
        }
    }
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (c.cuisineName_" . $default_lang . " LIKE '%" . $keyword . "%' OR sc.vServiceName_" . $default_lang . " LIKE '%" . $keyword . "%') AND c.eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (c.cuisineName_" . $default_lang . " LIKE '%" . $keyword . "%' OR sc.vServiceName_" . $default_lang . " LIKE '%" . $keyword . "%') ";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND c.eStatus = '" . clean($eStatus) . "'";
    }
    if ($eStatus != '') {
        $eStatussql = "";
    } else {
        $eStatussql = " AND c.eStatus != 'Deleted'";
    }
    if (ONLYDELIVERALL == 'Yes') {
        $sql = "SELECT c.cuisineName_" . $default_lang . " as `Item Type`, c.eStatus as Status FROM cuisine as c LEFT JOIN service_categories as sc on sc.iServiceId=c.iServiceId where 1=1 $eStatussql $ssql $ord";
    } else {
        $sql = "SELECT c.cuisineName_" . $default_lang . " as `Item Type`,sc.vServiceName_" . $default_lang . " as `DeliveryAll Service Category`, c.eStatus as Status FROM cuisine as c LEFT JOIN service_categories as sc on sc.iServiceId=c.iServiceId where 1=1 $eStatussql $ssql $ord";
    }
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        if (ONLYDELIVERALL == 'Yes') {
            $heading = array(
                'Item Type',
                'Status'
            );
        } else {
            $heading = array(
                'Item Type',
                'DeliveryAll Service Category',
                'Status'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Service Categories");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Status') {
                $pdf->Cell(50, 10, $column_heading, 1);
            } else {
                $pdf->Cell(70, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Status') {
                    $pdf->Cell(50, 10, $key, 1);
                } else {
                    $pdf->Cell(70, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//Cuisine
//vehicle_type
if ($section == 'store_vehicle_type') {
    $eSystem = " AND eType = 'DeliverAll' ";
    $ord = ' ORDER BY vt.vVehicleType_' . $default_lang . ' ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " ASC";
        } else {
            $ord = " ORDER BY vt.vVehicleType_" . $default_lang . " DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vt.fDeliveryCharge ASC";
        } else {
            $ord = " ORDER BY vt.fDeliveryCharge DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY vt.fRadius ASC";
        } else {
            $ord = " ORDER BY vt.fRadius DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fDeliveryCharge LIKE '%" . $keyword . "%' OR vt.fDeliveryChargeCancelOrder LIKE '%" . $keyword . "%' OR vt.fRadius LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%') AND vt.eStatus = '" . $eStatus . "'";
            } else {
                $ssql .= " AND (vt.vVehicleType_" . $default_lang . " LIKE '%" . $keyword . "%' OR vt.fDeliveryCharge LIKE '%" . $keyword . "%' OR vt.fDeliveryChargeCancelOrder LIKE '%" . $keyword . "%' OR vt.fRadius LIKE '%" . $keyword . "%' OR vt.iPersonSize   LIKE '%" . $keyword . "%')";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND vt.eStatus = '" . $eStatus . "'";
    }
    if (scount($userObj->locations) > 0) {
        $locations = implode(', ', $userObj->locations);
        $ssql .= " AND vt.iLocationid IN(-1, {$locations})";
    }
    if ($eStatus != '') {
        $eStatussql = "";
    } else {
        $eStatussql = " AND vt.eStatus != 'Deleted'";
    }
    $sql = "SELECT vt.vVehicleType_" . $default_lang . " as Type,vt.fDeliveryCharge as `Delivery Fees Completed Orders`,vt.fDeliveryChargeCancelOrder as `Delivery Fees Cancelled Orders`,vt.fRadius as Radius,vt.eStatus as Status, lm.vLocationName as location,vt.iLocationid as locationId  from  vehicle_type as vt left join location_master as lm ON lm.iLocationId = vt.iLocationid where 1 = 1 $eSystem $eStatussql $ssql $ord";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $data = array_keys($result[0]);
        $arr = array_diff($data, array("locationId"));
        echo implode("\t", $arr) . "\r\n";
        $i = 0;
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if ($key == 'locationId') {
                    $val = "";
                }
                if ($key == 'location' && $value['locationId'] == '-1') {
                    $val = "All Location";
                }
                echo $val . "\t";
            }
            echo "\r\n";
            $i++;
        }
    } else {
        if ($APP_TYPE == 'UberX') {
            $heading = array(
                'Type',
                'Subcategory',
                'Location Name'
            );
        } else {
            $heading = array(
                'Type',
                'Delivery Fees Completed Orders',
                'Delivery Fees Cancelled Orders',
                'Radius',
                'Status',
                'Location Name'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Vehicle Type");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Type' && $APP_TYPE == 'UberX') {
                $pdf->Cell(80, 10, $column_heading, 1);
            } else if ($column_heading == 'Type' && $APP_TYPE != 'UberX') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Delivery Fees Completed Orders') {
                $pdf->Cell(54, 10, $column_heading, 1);
            } else if ($column_heading == 'Delivery Fees Cancelled Orders') {
                $pdf->Cell(54, 10, $column_heading, 1);
            } else if ($column_heading == 'Radius') {
                $pdf->Cell(15, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Location Name') {
                $pdf->Cell(35, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Type' && $APP_TYPE == 'UberX') {
                    $pdf->Cell(80, 10, $key, 1);
                } else if ($column == 'Type' && $APP_TYPE != 'UberX') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'Delivery Fees Completed Orders') {
                    $pdf->Cell(54, 10, $key, 1);
                } else if ($column == 'Delivery Fees Cancelled Orders') {
                    $pdf->Cell(54, 10, $key, 1);
                } else if ($column == 'Radius') {
                    $pdf->Cell(15, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(20, 10, $key, 1);
                } else if ($column == 'location' && $row['locationId'] == "-1") {
                    $pdf->Cell(35, 10, 'All Location', 1);
                } else if ($column == 'location') {
                    $pdf->Cell(35, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//vehicle_type
// review page
if ($section == 'store_review') {
    $reviewtype = isset($_REQUEST['reviewtype']) ? $_REQUEST['reviewtype'] : 'Driver';
    $adm_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $adm_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    $ord = ' ORDER BY iRatingId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY o.vOrderNo ASC";
        } else {
            $ord = " ORDER BY o.vOrderNo DESC";
        }
    }
    if ($sortby == 2) {
        if ($reviewtype == 'Driver') {
            if ($order == 0) {
                $ord = " ORDER BY rd.vName ASC";
            } else {
                $ord = " ORDER BY rd.vName DESC";
            }
        } else if ($reviewtype == 'Company') {
            if ($order == 0) {
                $ord = " ORDER BY c.vCompany ASC";
            } else {
                $ord = " ORDER BY c.vCompany DESC";
            }
        } else {
            if ($order == 0) {
                $ord = " ORDER BY ru.vName ASC";
            } else {
                $ord = " ORDER BY ru.vName DESC";
            }
        }
    }
    if ($sortby == 6) {
        if ($reviewtype == 'Driver') {
            if ($order == 0) {
                $ord = " ORDER BY ru.vName ASC";
            } else {
                $ord = " ORDER BY ru.vName DESC";
            }
        } else if ($reviewtype == 'Company') {
            if ($order == 0) {
                $ord = " ORDER BY ru.vName ASC";
            } else {
                $ord = " ORDER BY ru.vName DESC";
            }
        } else {
            if ($order == 0) {
                $ord = " ORDER BY rd.vName ASC";
            } else {
                $ord = " ORDER BY rd.vName DESC";
            }
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY r.vRating1 ASC";
        } else {
            $ord = " ORDER BY r.vRating1 DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY r.tDate ASC";
        } else {
            $ord = " ORDER BY r.tDate DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY r.vMessage ASC";
        } else {
            $ord = " ORDER BY r.vMessage DESC";
        }
    }
    //End Sorting
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'r.eStatus') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . clean($keyword) . "'";
            } else {
                $option_new = $option;
                if ($option == 'drivername') {
                    $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
                }
                if ($option == 'ridername') {
                    $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";
                }
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%'";
            }
        } else {
            if ($reviewtype == 'Driver') {
                $ssql .= " AND (o.vOrderNo LIKE '%" . clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . clean($keyword) . "%' OR r.vRating1 LIKE '%" . clean($keyword) . "%')";
            } else if ($reviewtype == 'Company') {
                $ssql .= " AND (o.vOrderNo LIKE '%" . clean($keyword) . "%' OR  c.vCompany LIKE '%" . clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . clean($keyword) . "%' OR r.vRating1 LIKE '%" . clean($keyword) . "%')";
            } else {
                $ssql .= " AND (o.vOrderNo LIKE '%" . clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . clean($keyword) . "%' OR r.vRating1 LIKE '%" . clean($keyword) . "%')";
            }
        }
    }
// End Search Parameters
    $chkusertype = "";
    if ($reviewtype == "Driver") {
        $chkusertype = "Driver";
    } else if ($reviewtype == "Company") {
        $chkusertype = "Company";
    } else {
        $chkusertype = "Passenger";
    }
    if ($reviewtype == "Driver") {
        $sql = "SELECT o.vOrderNo as `Order Number`, CONCAT(ru.vName,' ',ru.vLastName) as `From User Name`,CONCAT(rd.vName,' ',rd.vLastName) as `To Driver Name` ,rd.vAvgRating as AverageRate,r.vRating1 as Rate,r.tDate as `Date`,r.vMessage as Comment,o.vTimeZone FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='" . $chkusertype . "' And ru.eStatus!='Deleted' $ssql $adm_ssql $ord";
    } else if ($reviewtype == "Company") {
        $store_txt = $langage_lbl_admin['LBL_RESTAURANT_TXT_ADMIN'];
        $sql = "SELECT o.vOrderNo as `Order Number`,CONCAT(ru.vName,' ',ru.vLastName) as `From User Name`,c.vCompany as `To $store_txt Name`,r.vRating1 as Rate,r.tDate as `Date`,c.vAvgRating as AverageRate,r.vMessage as Comment,o.vTimeZone FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='" . $chkusertype . "' AND ru.eStatus!='Deleted' $ssql $adm_ssql $ord";
    } else {
        $sql = "SELECT o.vOrderNo as `Order Number`,CONCAT(rd.vName,' ',rd.vLastName) as `From Delivery Driver Name`,CONCAT(ru.vName,' ',ru.vLastName) as `To User Name`,ru.vAvgRating as AverageRate,vRating1 as Rate,r.tDate as `Date`,r.vMessage as Comment,o.vTimeZone FROM ratings_user_driver as r LEFT JOIN orders as o ON r.iOrderId=o.iOrderId LEFT JOIN company as c ON c.iCompanyId=o.iCompanyId LEFT JOIN register_driver as rd ON rd.iDriverId=o.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=o.iUserId WHERE 1=1 AND r.eToUserType='" . $chkusertype . "' And ru.eStatus!='Deleted'  $ssql $adm_ssql $ord";
    }
    $serverTimeZone = date_default_timezone_get();
    // filename for download
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        if ($reviewtype == 'Company')
        {
            $file_name = "store_review_";
        } 
        if ($reviewtype == 'Driver')
        {
            $file_name = "service_provider_review_";
        } 
        if ($reviewtype == 'Passenger')
        {
            $file_name = "users_review_";
        } 
          
        $filename =$file_name.$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        if ($reviewtype == "Driver") {
            $label_name = "To Driver Name";
            $to_label_name = "From User Name";
            $from_cell_label = "Service Provider Name";
            $to_cell_label = "Rating by( User Name )";
        }else if ($reviewtype == "Company") {
            $label_name = "To $store_txt Name";
            $to_label_name = "From User Name";
            $from_cell_label = "Store Name";
            $to_cell_label = "Rating by( User Name)";
        }else{
            $label_name = "To User Name";
            $to_label_name = "From Delivery Driver Name";
            $from_cell_label = "User Name";
            $to_cell_label = "Rating by(Delivery Service Provider Name)";
        }
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Order Number");
        $sheet->setCellValue('B1', $from_cell_label);
        $sheet->setCellValue('C1', $to_cell_label);
        $sheet->setCellValue('D1', 'Rate');
        $sheet->setCellValue('E1', 'Date');
        $sheet->setCellValue('F1', "Comment");
        $i = 2;

        $timeZone = $result[0]['vTimeZone'];
        unset($result[0]['vTimeZone']);
        //echo implode("\t", array_keys($result[0])) . "\r\n";
        $result[0]['vTimeZone'] = $timeZone;
        foreach ($result as $value) {
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $value['Date']; 
            $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['Date'] = $get_tDate_format['tDisplayDate'];
            
            $sheet->setCellValue('A' . $i, $value["Order Number"]);
            $sheet->setCellValue('B' . $i, $value[$label_name]);
            $sheet->setCellValue('C' . $i, $value[$to_label_name]);
            $sheet->setCellValue('D' . $i, (!empty($value["Rate"])) ? $value["Rate"] : 0);
            $sheet->setCellValue('E' . $i, $value["Date"]);
            $sheet->setCellValue('F' . $i, $value["Comment"]);            
            $i++;
        }
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

    } else {
        if ($reviewtype == "Driver") {
            $heading = array(
                'RiderNumber',
                'DriverName',
                'AverageRate',
                'RiderName',
                'Rate',
                'Date',
                'Comment'
            );
        } else {
            $heading = array(
                'RiderNumber',
                'RiderName',
                'AverageRate',
                'DriverName',
                'Rate',
                'Date',
                'Comment'
            );
        }
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Review");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'RiderNumber') {
                $pdf->Cell(22, 10, $column_heading, 1);
            } else if ($column_heading == 'DriverName') {
                $pdf->Cell(40, 10, $column_heading, 1);
            } else if ($column_heading == 'AverageRate') {
                $pdf->Cell(21, 10, $column_heading, 1);
            } else if ($column_heading == 'RiderName') {
                $pdf->Cell(25, 10, $column_heading, 1);
            } else if ($column_heading == 'Rate') {
                $pdf->Cell(10, 10, $column_heading, 1);
            } else if ($column_heading == 'Date') {
                $pdf->Cell(42, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'DriverName') {
                    $values = clearName($key);
                }
                if ($column == 'Date') {
                    $values = DateTime($key);
                }
                DateTime($val);
                if ($column == 'RiderNumber') {
                    $pdf->Cell(22, 10, $values, 1);
                } else if ($column == 'DriverName') {
                    $pdf->Cell(40, 10, $values, 1);
                } else if ($column == 'AverageRate') {
                    $pdf->Cell(21, 10, $values, 1);
                } else if ($column == 'RiderName') {
                    $pdf->Cell(25, 10, $values, 1);
                } else if ($column == 'Rate') {
                    $pdf->Cell(10, 10, $values, 1);
                } else if ($column == 'Date') {
                    $date_format_data_array = array(
                        'langCode' => $default_lang,
                        'DateFormatForWeb' => 1
                    );
                    $date_format_data_array['tdate'] = (!empty($result['vTimeZone'])) ? converToTz($values,$result['vTimeZone'],$serverTimeZone) : $values;
                    $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $values = $get_date_format['tDisplayDate'];//DateTime($val);
                    $pdf->Cell(42, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//Cancel Reason
if ($section == 'cancel_reason') {
    $eType = isset($_REQUEST['eType']) ? stripslashes($_REQUEST['eType']) : "";
    $ord = ' ORDER BY vTitle ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vTitle ASC";
        } else {
            $ord = " ORDER BY vTitle DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'eStatus') !== false) {
                if ($eType != '') {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "' AND eType = '" . $eType . "' ";
                } else {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
                }
            } else {
                if ($eType != '') {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%' AND eType = '" . $eType . "' ";
                } else {
                    $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
                }
            }
        } else {
            if ($eType != '') {
                $ssql .= " AND ( vTitle_" . $default_lang . " LIKE '%" . $keyword . "%') AND  eType='" . $eType . "'";
            } else {
                $ssql .= " AND ( vTitle_" . $default_lang . " LIKE '%" . $keyword . "%')";
            }
        }
    } else if ($eType != '' && $keyword == '') {
        $ssql .= " AND eType = '" . clean($eType) . "'";
    }
    if ($option == "eStatus") {
        $eStatussql = " AND eStatus = '" . ($keyword) . "'";
    } else {
        $eStatussql = " AND eStatus != 'Deleted'";
    }
    $sql = "SELECT vTitle_EN as Title, eType as `Service Type` ,eStatus as Status FROM cancel_reason where 1=1 $eStatussql $ssql";
    // filename for download
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        // echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->ExecuteQuery($sql) or die('Query failed!');
        while ($row = mysqli_fetch_assoc($result)) {
            if (!$flag) {
                // display field/column names as first row
                echo implode("\t", array_keys($row)) . "\r\n";
                $flag = true;
            }
            array_walk($row, __NAMESPACE__ . '\cleanData');
            echo implode("\t", array_values($row)) . "\r\n";
        }
    } else {
        $heading = array(
            'Title',
            'Service Type',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Cancel Reason");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Title') {
                $pdf->Cell(100, 10, $column_heading, 1);
            } else if ($column_heading == 'Status') {
                $pdf->Cell(30, 10, $column_heading, 1);
            } else {
                $pdf->Cell(30, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == 'Title') {
                    $pdf->Cell(100, 10, $key, 1);
                } else if ($column == 'Status') {
                    $pdf->Cell(30, 10, $key, 1);
                } else {
                    $pdf->Cell(30, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
//Cancel Reason
//Added By Hasmukh On 1-11-2018 For Set Common PDF Configuration Start
function manage_tcpdf($pageOrientation, $unit, $imagePath, $imageName, $pageType = "P", $pageSize = "A4")
{
    $pdf = new TCPDF($pageOrientation, $unit, "Letter", true, 'UTF-8', false);
    $image_file = $imagePath . $imageName;
    //print_r($image_file);die;
    $pdf->AddPage($pageType, $pageSize);
    $pdf->Image($image_file, 90, 6, 30);
    $lg = array();
    $lg['a_meta_charset'] = 'UTF-8';
    $lg['a_meta_language'] = 'ar';
    // set some language-dependent strings (optional)
    $pdf->setLanguageArray($lg);
    $language = "dejavusans";
    //$language = "Arial";
    $pdfName = time() . ".pdf";
    $result = array(
        "pdf" => $pdf,
        "language" => $language,
        "pdfName" => $pdfName
    );
    return $result;
}

//Added By Hasmukh On 1-11-2018 For Set Common PDF Configuration End
// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 1 Start
if ($section == 'movement_report_before') {
    $ssql = "";
    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "";
    if ($searchDriver != "") {
        $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";
    }
    if ($startDate != '') {
        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";
    }
    $sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime,concat(rd.vName,' ',rd.vLastName) as Driver FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive = 'Active' $ssql ORDER BY iTripId DESC, iTripLocationId";
    $db_movement = $obj->MySQLSelect($sql);
    if ($type == 'XLS') {
        $filename = $section . "_" . date('Ymd') . ".xls";
        $flag = false;
        $header .= "Driver" . "\t";
        $header .= "Trip No." . "\t";
        $header .= "Distance (Mile)" . "\t";
        $header .= "Type" . "\t";
        $header .= "Date" . "\t";
        $header .= "Total Time" . "\t";
        $header .= "Location" . "\t";
        for ($i = 0; $i < scount($db_movement); $i++) {
            $tPlatitudes = explode(",", $db_movement[$i]['tPlatitudes']);
            $tPlongitudes = explode(",", $db_movement[$i]['tPlongitudes']);
            $lat = $tPlatitudes[0];
            $lng = $tPlongitudes[0];
            $address = getaddress($lat, $lng);
            if ($db_movement[$i]['fDistance'] > 0.1) {
                $fDistance = $db_movement[$i]['fDistance'];
            } else {
                $fDistance = round($db_movement[$i]['fDistance']);
            }
            $fDistance = getUnitToMiles($db_movement[$i]['fDistance'], 'Miles');
            $data_movement .= $db_movement[$i]['Driver'] . "\t";
            $data_movement .= $db_movement[$i]['vRideNo'] . "\t";
            $data_movement .= $fDistance . "\t";
            $data_movement .= "Period 1 \t";
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $db_movement[$i]['tDate']; 
            $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $data_movement .= $get_tDate_format['tDisplayDate']. "\t";//DateTime($val);
            //$data_movement .= $db_movement[$i]['tDate'] . "\t";
            $time = TimeDifference($db_movement[$i]['dStartTime'], $db_movement[$i]['dEndTime']);
            $data_movement .= $time . "\t";
            if ($address) {
                $data_movement .= $address;
            } else {
                $data_movement .= '--';
            }
            $data_movement .= "\n";
        }
        $data_movement = str_replace("\r", "", $data_movement);
        //echo "<pre>";print_r($header);die;
        ob_clean();
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        print "$header\n$data_movement";
        exit;
    }
}
// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 1 End
// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 2 Start
if ($section == 'movement_report_arriving') {
    $ssql = "";
    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "";
    if ($searchDriver != "") {
        $ssql .= " AND t.iDriverId ='" . $searchDriver . "'";
    }
    if ($startDate != '') {
        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";
    }
    $sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime,concat(rd.vName,' ',rd.vLastName) as Driver FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive = 'Arrived' $ssql ORDER BY iTripId DESC, iTripLocationId";
    $db_movement = $obj->MySQLSelect($sql);
    if ($type == 'XLS') {
        $filename = $section . "_" . date('Ymd') . ".xls";
        $flag = false;
        $header .= "Driver" . "\t";
        $header .= "Trip No." . "\t";
        $header .= "Distance (Mile)" . "\t";
        $header .= "Type" . "\t";
        $header .= "Date" . "\t";
        $header .= "Total Time" . "\t";
        $header .= "Location" . "\t";
        for ($i = 0; $i < scount($db_movement); $i++) {
            $tPlatitudes = explode(",", $db_movement[$i]['tPlatitudes']);
            $tPlongitudes = explode(",", $db_movement[$i]['tPlongitudes']);
            $lat = $tPlatitudes[0];
            $lng = $tPlongitudes[0];
            $address = getaddress($lat, $lng);
            if ($db_movement[$i]['fDistance'] > 0.1) {
                $fDistance = $db_movement[$i]['fDistance'];
            } else {
                $fDistance = round($db_movement[$i]['fDistance']);
            }
            $fDistance = getUnitToMiles($db_movement[$i]['fDistance'], 'Miles');
            $data_movement .= $db_movement[$i]['Driver'] . "\t";
            $data_movement .= $db_movement[$i]['vRideNo'] . "\t";
            $data_movement .= $fDistance . "\t";
            $data_movement .= "Period 2 \t";
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $db_movement[$i]['tDate']; 
            $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $data_movement .= $get_tDate_format['tDisplayDate']. "\t";
            //$data_movement .= $db_movement[$i]['tDate'] . "\t";
            $time = TimeDifference($db_movement[$i]['dStartTime'], $db_movement[$i]['dEndTime']);
            $data_movement .= $time . "\t";
            if ($address) {
                $data_movement .= $address;
            } else {
                $data_movement .= '--';
            }
            $data_movement .= "\n";
        }
        $data_movement = str_replace("\r", "", $data_movement);
        //echo "<pre>";print_r($data_movement);die;
        ob_clean();
        header("Content-type: application/octet-stream");
        // header('Content-Type: text/html; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        header("Pragma: no-cache");
        header("Expires: 0");
        print "$header\n$data_movement";
        exit;
    }
}
// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 2 End
// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 3 Start
if ($section == 'movement_report_ontrip') {
    $ssql = "";
    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : "";
    if ($searchDriver != "") {
        $ssql .= " AND tl.iDriverId ='" . $searchDriver . "'";
    }
    if ($startDate != '') {
        $ssql .= " AND Date(tDate) >='" . $startDate . " 00:00:00'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(tDate) <='" . $endDate . " 23:59:59'";
    }
    $sql = "SELECT tl.*,rd.vName, rd.vLastName, t.vRideNo,t.iDriverId,t.fDistance,t.tStartDate AS dStartTime,t.tEndDate AS dEndTime,concat(rd.vName,' ',rd.vLastName) as Driver FROM trips_locations tl, register_driver as rd, trips as t WHERE  t.iDriverId = rd.iDriverId AND tl.iTripId = t.iTripId AND t.iActive = 'On Going Trip' $ssql ORDER BY iTripId DESC, iTripLocationId";
    $db_movement = $obj->MySQLSelect($sql);
    if ($type == 'XLS') {
        $filename = $section . "_" . date('Ymd') . ".xls";
        $flag = false;
        $header .= "Driver" . "\t";
        $header .= "Trip No." . "\t";
        $header .= "Distance (Mile)" . "\t";
        $header .= "Type" . "\t";
        $header .= "Date" . "\t";
        $header .= "Total Time" . "\t";
        $header .= "Location" . "\t";
        for ($i = 0; $i < scount($db_movement); $i++) {
            $tPlatitudes = explode(",", $db_movement[$i]['tPlatitudes']);
            $tPlongitudes = explode(",", $db_movement[$i]['tPlongitudes']);
            $lat = $tPlatitudes[0];
            $lng = $tPlongitudes[0];
            $address = getaddress($lat, $lng);
            if ($db_movement[$i]['fDistance'] > 0.1) {
                $fDistance = $db_movement[$i]['fDistance'];
            } else {
                $fDistance = round($db_movement[$i]['fDistance']);
            }
            $fDistance = getUnitToMiles($db_movement[$i]['fDistance'], 'Miles');
            $data_movement .= $db_movement[$i]['Driver'] . "\t";
            $data_movement .= $db_movement[$i]['vRideNo'] . "\t";
            $data_movement .= $fDistance . "\t";
            $data_movement .= "Period 3 \t";
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $db_movement[$i]['tDate']; 
            $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $data_movement .= $get_tDate_format['tDisplayDate']. "\t";

            //$data_movement .= $db_movement[$i]['tDate'] . "\t";
            $time = TimeDifference($db_movement[$i]['dStartTime'], $db_movement[$i]['dEndTime']);
            $data_movement .= $time . "\t";
            if ($address) {
                $data_movement .= $address;
            } else {
                $data_movement .= '--';
            }
            $data_movement .= "\n";
        }
        $data_movement = str_replace("\r", "", $data_movement);
        //echo "<pre>";print_r($data_movement);die;
        ob_clean();
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        print "$header\n$data_movement";
        exit;
    }
}
// Added By Hasmukh On 11-12-2018 For Export Data of Movement Report For Period 3 End
// Added By Hasmukh On 12-12-2018 For Export Data of Advertisement Banners Start
if ($section == 'advertise_banners') {
    global $tconfig;
    $sub_cid = isset($_REQUEST['sub_cid']) ? $_REQUEST['sub_cid'] : '';
    $ord = ' ORDER BY iDispOrder ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vBannerTitle ASC";
        } else {
            $ord = " ORDER BY vBannerTitle DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY ePosition ASC";
        } else {
            $ord = " ORDER BY ePosition DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY iDispOrder ASC";
        } else {
            $ord = " ORDER BY iDispOrder DESC";
        }
    }
    if ($keyword != '') {
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%' AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND vBannerTitle LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND vBannerTitle LIKE '%" . clean($keyword) . "%')";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    }
    $sql = "SELECT iAdvertBannerId AS SrNo,vBannerTitle AS Name,ePosition AS Position,iDispOrder AS DisplayOrder,concat(dStartDate,' To ',dExpiryDate) as TimePeriod,dAddedDate AS AddedDate,iImpression AS TotalImpression,eImpression AS UsedImpression,eStatus AS Status FROM advertise_banners as vc WHERE eStatus != 'Deleted' $ssql $ord";
    // filename for download
    $getUserCount = $obj->MySQLSelect("SELECT * FROM banner_impression WHERE iAdvertBannerId > 0");
//echo "<pre>";
    $usedCountArr = array();
    for ($c = 0; $c < scount($getUserCount); $c++) {
        $bannerId = $getUserCount[$c]['iAdvertBannerId'];
        if (isset($usedCountArr[$bannerId]) && $usedCountArr[$bannerId] > 0) {
            $usedCountArr[$bannerId] += 1;
        } else {
            $usedCountArr[$bannerId] = 1;
        }
    }
    echo "<pre>";
    //print_r($usedCountArr);die;
    if ($type == 'XLS') {
        $filename = $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        //print_r($result);die;
        echo implode("\t", array_keys($result[0])) . "\r\n";
        $sr = 1;
        foreach ($result as $value) {
            $bannerUsedCount = "-----";
            $impressionCount = "Unlimited";
            if (isset($usedCountArr[$value['SrNo']]) && $usedCountArr[$value['SrNo']] > 0 && $value['UsedImpression'] == "Limited") {
                $bannerUsedCount = $usedCountArr[$value['SrNo']];
                $impressionCount = $value['TotalImpression'];
            }
            $value['UsedImpression'] = $bannerUsedCount;
            $value['TotalImpression'] = $impressionCount;
            $value['SrNo'] = $sr;
            //print_r($value);die;
            foreach ($value as $key => $val) {
                if ($key == 'Category') {
                    $val = clearName($val);
                }
                echo $val . "\t";
            }
            echo "\r\n";
            $sr++;
        }
    } else {
        $heading = array(
            'SrNo#',
            'Name',
            'Position',
            'Display Order',
            'Time Period',
            'Added Date',
            'Total Impression',
            'Used Impression',
            'Status'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Name");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == 'Position' || $column_heading == 'Status') {
                $pdf->Cell(20, 10, $column_heading, 1);
            } else if ($column_heading == 'Display Order' || $column_heading == 'Name' || $column_heading == 'Added Date') {
                $pdf->Cell(35, 10, $column_heading, 1);
            } else {
                $pdf->Cell(45, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        //echo "<pre>";
        //print_r($heading);die;
        $sr = 1;
        foreach ($result as $row) {
            $pdf->Ln();
            unset($row['URL']);
            $bannerUsedCount = "-----";
            $impressionCount = "Unlimited";
            if (isset($usedCountArr[$row['SrNo']]) && $usedCountArr[$row['SrNo']] > 0 && $row['UsedImpression'] == "Limited") {
                $bannerUsedCount = $usedCountArr[$row['SrNo']];
                $impressionCount = $row['TotalImpression'];
            }
            $row['UsedImpression'] = $bannerUsedCount;
            $row['TotalImpression'] = $impressionCount;
            $row['SrNo'] = $sr;
            foreach ($row as $column => $key) {
                $values = $key;
                if ($column == 'Name') {
                    $values = clearName($key);
                }
                if ($column == 'Position' || $column == 'Status') {
                    $pdf->Cell(20, 10, $values, 1);
                } else if ($column == 'DisplayOrder' || $column == 'Name' || $column == 'AddedDate') {
                    $pdf->Cell(35, 10, $values, 1);
                } else {
                    $pdf->Cell(45, 10, $values, 1);
                }
            }
            $sr++;
        }
        //print_r($pdf);die;
        $pdf->Output($pdfFileName, 'D');
    }
}
// Added By Hasmukh On 12-12-2018 For Export Data of Advertisement Banners End
// Added By Hasmukh On 14-12-2018 For Export Data of Newsletter Start
if ($section == 'newsletter') {
    $tbl_name = 'newsletter';
    $ord = ' ORDER BY iNewsLetterId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vName ASC";
        } else {
            $ord = " ORDER BY vName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vEmail ASC";
        } else {
            $ord = " ORDER BY vEmail DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY tDate ASC";
        } else {
            $ord = " ORDER BY tDate DESC";
        }
    }
    $ssql = " WHERE eStatus != 'Deleted'";
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword_new) . "%' AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (vName LIKE '%" . $keyword_new . "%'  OR vEmail LIKE '%" . clean($keyword_new) . "%') AND eStatus = '" . clean($eStatus) . "'";
            } else {
                $ssql .= " AND (vName LIKE '%" . $keyword_new . "%'  OR vEmail LIKE '%" . clean($keyword_new) . "%')";
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    }
    //added by SP for status on 28-06-2019
    $sql = "SELECT vName AS Name,vEmail AS Email,eStatus as Status,tDate AS Date,vIP AS IP FROM " . $tbl_name . " $ssql $ord";
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $filename ="newsletter_subscribers_".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_EMAIL_TEXT']);
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_Status']);
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'IP Address');
        $i = 2;

        //echo implode("\t", array_keys($result[0])) . "\r\n";
        foreach ($result as $value) {           
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $value['Date'];
            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);                    
            $value['Date'] = $get_date_format['tDisplayDate'];//DateTime($val);
               
            $sheet->setCellValue('A' . $i, $value['Name']);
            $sheet->setCellValue('B' . $i, $value['Email']);
            $sheet->setCellValue('C' . $i, $value['Status']);
            $sheet->setCellValue('D' . $i, $value['Date']);
            $sheet->setCellValue('E' . $i, $value['IP']);
            $i++;
            //echo "\r\n";
        }
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

    } else {
        $heading = array(
            $langage_lbl_admin['LBL_USER_NAME_HEADER_SLIDE_TXT'],
            $langage_lbl_admin['LBL_EMAIL_LBL_TXT'],
            $langage_lbl_admin['LBL_DATE_SIGNUP'],
            'IP'
        );
        $result = $obj->ExecuteQuery($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }
        $result = $resultset;
        $configPdf = manage_tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, K_PATH_IMAGES, PDF_HEADER_LOGO);
        $pdf = $configPdf['pdf'];
        $language = $configPdf['language'];
        $pdfFileName = $file . $configPdf['pdfName'];
        //$pdf = new FPDF('P', 'mm', 'Letter');
        //$pdf->AddPage();
        $pdf->SetFillColor(36, 96, 84);
        $pdf->SetFont($language, 'b', 15);
        $pdf->Cell(100, 16, "Newsletter");
        $pdf->Ln();
        $pdf->SetFont($language, 'b', 9);
        $pdf->Ln();
        foreach ($heading as $column_heading) {
            if ($column_heading == $langage_lbl_admin['LBL_DATE_SIGNUP'] || $column_heading == $langage_lbl_admin['LBL_EMAIL_LBL_TXT']) {
                $pdf->Cell(55, 10, $column_heading, 1);
            } else {
                $pdf->Cell(40, 10, $column_heading, 1);
            }
        }
        $pdf->SetFont($language, '', 9);
        foreach ($result as $row) {
            $pdf->Ln();
            foreach ($row as $column => $key) {
                if ($column == $langage_lbl_admin['LBL_DATE_SIGNUP']) {
                     $date_format_data_array = array(
                        'langCode' => $default_lang,
                        'DateFormatForWeb' => 1
                    );
                    $date_format_data_array['tdate'] = $key;
                    $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                    $key = $get_date_format['tDisplayDate'];
                    //$key = DateTime($key);
                    $pdf->Cell(55, 10, $key, 1);
                }
                if ($column == $langage_lbl_admin['LBL_EMAIL_LBL_TXT']) {
                    $key = clearEmail($key);
                    $pdf->Cell(55, 10, $key, 1);
                }
                if ($column == $langage_lbl_admin['LBL_USER_NAME_HEADER_SLIDE_TXT']) {
                    $key = clearName($key);
                    $pdf->Cell(40, 10, $key, 1);
                } else {
                    $pdf->Cell(40, 10, $key, 1);
                }
            }
        }
        $pdf->Output($pdfFileName, 'D');
    }
}
// Added By Hasmukh On 14-12-2018 For Export Data of Newsletter End
if ($section == 'driversubscription') {
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY iDriverSubscriptionPlanId DESC';
    $option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDriver = isset($_REQUEST['searchDriver']) ? stripslashes($_REQUEST['searchDriver']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $defaultDetails = $obj->MySQLSelect("SELECT * FROM `language_master` WHERE `eDefault` ='Yes' AND eStatus = 'Active'");
    //$currencySymbol = $obj->MySQLSelect("SELECT vSymbol FROM currency WHERE eDefault = 'Yes'")[0]['vSymbol'];
    $vcode = $defaultDetails[0]['vCode'];
    $currencySymbol = $defaultDetails[0]['vCurrencySymbol'];
    $ssql = '';
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option_new == 'providerName') {
                $ssql .= " AND (rd.vName LIKE '%" . clean($keyword_new) . "%' OR rd.vLastName LIKE '%" . clean($keyword_new) . "%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%" . clean($keyword_new) . "%' )";
            } else {
                $ssql .= " AND d." . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
            }
        } else {
            $ssql .= " AND (d.vPlanName LIKE '%" . clean($keyword_new) . "%' OR d.ePlanValidity LIKE '%" . clean($keyword_new) . "%')";
            $ssql .= " OR (rd.vName LIKE '%" . clean($keyword_new) . "%' OR rd.vLastName LIKE '%" . clean($keyword_new) . "%' OR CONCAT( vName,  ' ', vLastName ) LIKE  '%" . clean($keyword_new) . "%')";
        }
    }
    if ($searchDriver != '') {
        $ssql .= " AND rd.iDriverId = $searchDriver";
    }
    // End Search Parameters
    //Pagination Start
    $per_page = $DISPLAY_RECORD_NUMBER; // number of results to show per page
    $tblPlan = 'driver_subscription_plan';
    $tblDetails = 'driver_subscription_details';
    //$getField = "eSubscriptionStatus, p.vPlanName, p.vPlanDescription,p.vPlanPeriod,p.ePlanValidity,CONCAT('$currencySymbol',p.fPrice) as fPlanPrice,d.tSubscribeDate,d.tExpiryDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name";
    $getField = "d.eSubscriptionStatus, d.vPlanName, d.vPlanDescription,d.vPlanPeriod,d.ePlanValidity,d.fPrice as fPlanPrice,d.tSubscribeDate,d.tExpiryDate,d.tClosedDate,IFNULL(DATEDIFF(d.tExpiryDate,CURDATE()),'0') AS planLeftDays,d.tSubscribeDate, d.iDriverId,CONCAT(rd.vName,' ',rd.vLastName) as name,rd.vTimeZone";
    //$sql = "SELECT $getField FROM $tblDetails d INNER JOIN $tblPlan p ON d.iDriverSubscriptionPlanId = p.iDriverSubscriptionPlanId  LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";
    $sql = "SELECT $getField FROM $tblDetails d LEFT JOIN register_driver rd ON rd.iDriverId=d.iDriverId WHERE 1 $ssql ORDER BY d.tSubscribeDate DESC, d.tExpiryDate DESC";
    if ($type == 'XLS') {
        $filename = $tblDetails . "_" . $timestamp_filename . ".xls";
        // header("Content-Disposition: attachment; filename=\"$filename\"");
        // header("Content-Type: application/vnd.ms-excel");
        //echo "\xEF\xBB\xBF";
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'].' Name');
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_NAME']);
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_TYPE']);
        $sheet->setCellValue('D1',$langage_lbl_admin['LBL_SUBSCRIPTION_PLAN_PRICE']);
        $sheet->setCellValue('E1',  $langage_lbl_admin['LBL_DRIVER_SUBSCRIBE_DATE']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_DRIVER_EXPIRE_DATE']);
        $sheet->setCellValue('G1', $langage_lbl_admin['LBL_DRIVER_CANCEL_DATE']);
		$sheet->setCellValue('H1', $langage_lbl_admin['LBL_Status']);
        $i = 2; 
        $serverTimeZone = date_default_timezone_get();

        foreach ($result as $value) { 
		
            if ($value['ePlanValidity'] == 'Weekly') {
                $vPlanPeriod = $langage_lbl_admin['LBL_SUB_WEEKS'];
            }
            if ($value['ePlanValidity'] == 'Monthly') {
                $vPlanPeriod = $langage_lbl_admin['LBL_SUB_MONTH'];
            }
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $get_date_format = array();
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tSubscribeDate'],$value['vTimeZone'],$serverTimeZone) : $value['tSubscribeDate'];
            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $tSubscribeDate = $get_date_format['tDisplayDate'];

            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tExpiryDate'],$value['vTimeZone'],$serverTimeZone) : $value['tExpiryDate'];
            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $tExpiryDate = $get_date_format['tDisplayDate'];

            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tClosedDate'],$value['vTimeZone'],$serverTimeZone) : $value['tClosedDate'];
            $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $tClosedDate = ($value['eSubscriptionStatus']=='Cancelled') ? $get_date_format['tDisplayDate'] : "-";

            $sheet->setCellValue('A' . $i, $value["name"]);
            $sheet->setCellValue('B' . $i, $value["vPlanName"]);
            $sheet->setCellValue('C' . $i, $vPlanPeriod);
            $sheet->setCellValue('D' . $i, formateNumAsPerCurrency($value['fPlanPrice'],''));
            $sheet->setCellValue('E' . $i, $tSubscribeDate);
            $sheet->setCellValue('F' . $i, $tExpiryDate);
            $sheet->setCellValue('G' . $i, $tClosedDate);
			$sheet->setCellValue('H' . $i, $value['eSubscriptionStatus']);
            $i++;
        } 
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        //echo implode("\t", array_keys($result[0])) . "\r\n";
        // foreach ($result[0] as $key => $val) {
        //     if ($key == 'planLeftDays' || $key == 'iDriverId' || $key == 'vTimeZone') { //$key == 'eSubscriptionStatus' ||
        //         continue;
        //     }
        //     echo $key . "\t";
        // }
        // echo "\r\n";
        // $serverTimeZone = date_default_timezone_get();
        // foreach ($result as $value) {
        //     foreach ($value as $key => $val) {
        //         if ($key == 'planLeftDays' || $key == 'iDriverId' || $key == 'vTimeZone') { // $key == 'eSubscriptionStatus'
        //             continue;
        //         }
        //         if ($key == 'vPlanDescription') {
        //             $val = str_replace(",", "|", $val);
        //         }
        //         if ($key == 'vPlanName') {
        //             $val = str_replace(",", "|", $val);
        //         }
        //         if ($key == 'name') {
        //             $val = clearName(" " . $val);
        //         }
        //         if ($key == 'fPlanPrice') {
        //             $val = formateNumAsPerCurrency($val, '');
        //         }
        //         if ($key == 'vPlanPeriod') {
        //             if ($val == 'Weekly') {
        //                 $val = $langage_lbl_admin['LBL_SUB_WEEKS'];
        //             }
        //             if ($val == 'Monthly') {
        //                 $val = $langage_lbl_admin['LBL_SUB_MONTH'];
        //             }
        //         }
        //         if($key == "tSubscribeDate" || $key == "tExpiryDate" || $key == "tClosedDate")
        //         {
        //             $date_format_data_array = array(
        //                 'langCode' => $default_lang,
        //                 'DateFormatForWeb' => 1
        //             );
        //             $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($val,$value['vTimeZone'],$serverTimeZone) : $val;
        //             $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        //             $val = $get_date_format['tDisplayDate'];//DateTime($val);
        //         }
        //         /*if ($key == 'Subscribed Date') {

        //             $val = DateTime($val);

        //         }*/
        //         echo $val . "\t";
        //     }
        //     echo "\r\n";
        // }
    }
}
if($section == 'company_export')
{
    $eSystem = " AND  c.eSystem ='General'";
    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY c.iCompanyId DESC';
    if ($sortby == 1) {
        if ($order == 0) $ord = " ORDER BY c.vCompany ASC"; else
            $ord = " ORDER BY c.vCompany DESC";
    }
    if ($sortby == 2) {
        if ($order == 0) $ord = " ORDER BY c.vEmail ASC"; else
            $ord = " ORDER BY c.vEmail DESC";
    }
    if ($sortby == 3) {
        if ($order == 0) $ord = " ORDER BY `count` ASC"; else
            $ord = " ORDER BY `Service Providers` DESC";
    }
    if ($sortby == 4) {
        if ($order == 0) $ord = " ORDER BY c.eStatus ASC"; else
            $ord = " ORDER BY c.eStatus DESC";
    }
    // Start Search Parameters
    $option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $ssql = '';
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(c.vCode,'',c.vPhone)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND c.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
                }
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%')) AND c.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "')) AND c.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (c.vCompany LIKE '%" . clean($keyword_new) . "%' OR c.vEmail LIKE '%" . clean($keyword_new) . "%' OR (concat(c.vCode,'',c.vPhone) = '" . clean($keyword_new) . "'))";
                }
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND c.eStatus = '" . clean($eStatus) . "'";
    }
    // End Search Parameters
    if (!empty($eStatus)) {
        $equery = "";
    } else {
        $equery = " AND  c.eStatus != 'Deleted'";
    }
    $sql = "SELECT c.vCompany AS `Company Name`, (SELECT count(rd.iDriverId) FROM register_driver AS rd WHERE rd.iCompanyId=c.iCompanyId AND rd.eStatus != 'Deleted' $dri_ssql) AS `Service Providers`,c.vEmail AS `Email`,c.vCode, c.vPhone,c.eStatus AS `Status`  FROM company AS c WHERE 1 = 1 AND c.eBuyAnyService = 'No' $eSystem $equery $ssql $ord";
    if ($type == 'XLS') {
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $filename = "Company_" . $timestamp_filename . ".xls";
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_PROFILE_Company_name']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_DRIVERS_NAME_ADMIN']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_EMAIL_TEXT']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_Status']);

         $i = 2;
        foreach ($result as $value) {
            $user_available_balance = 0;
            if (isset($walletDataArr[$value['Wallet Balance']])) {
                $user_available_balance = $walletDataArr[$value['Wallet Balance']];
            }
            $value['Wallet Balance'] = formateNumAsPerCurrency($user_available_balance, '');

            if(empty($value['vTimeZone']))
            {
                $timeZone_sql = "SELECT vTimeZone FROM country WHERE vCountryCode='".$value['vCountry']."' ";
                $get_timezone_data = $obj->MySQLSelect($timeZone_sql);
                $value['vTimeZone'] =  $get_timezone_data[0]['vTimeZone'];
            }
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value["Signup Date"],$value['vTimeZone'],$serverTimeZone) : $value["Signup Date"];
            $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
            $value["Signup Date"] = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

            $sheet->setCellValue('A' . $i, clearName($value["Company Name"]));
            $sheet->setCellValue('B' . $i, $value["Service Providers"]);
            $sheet->setCellValue('C' . $i, clearEmail($value["Email"]));
            $sheet->setCellValue('D' . $i, "(+". ($value["vCode"]).") ". clearPhone($value["vPhone"]));
            $sheet->setCellValue('E' . $i, $value["Status"]); 
            $i++;
        }
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
}
if($section == 'track_any_service_user')
{
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
    $searchRider = (isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '');
    $ord = ' ORDER BY tsu.iTrackServiceUserId DESC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY tsu.vName ASC";
        else

            $ord = " ORDER BY tsu.vName DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY tsu.vEmail ASC";
        else

            $ord = " ORDER BY tsu.vEmail DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY tsu.eStatus ASC";
        else

            $ord = " ORDER BY tsu.eStatus DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY tsu.dAddedDate ASC";
        else

            $ord = " ORDER BY tsu.dAddedDate DESC";
    }
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
    }
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $ssql = '';
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array("(", "+", ")");
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        }
        else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'RiderName') {
                $option_new = "CONCAT(tsu.vName,' ',tsu.vLastName)";
            }
            if ($option == 'MobileNumber') {
                $option_new = "CONCAT(tsu.vPhoneCode,'',tsu.vPhone)";
            }
            if ($option == 'vEmail') {
                $option_new = "tsu.vEmail";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND eStatus = '" . clean($eStatus) . "'";
                }
            }
            else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
                }
            }
        }
        else {
            if ($eStatus != '') {
                $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) LIKE '%" . clean($keyword_new) . "%')) AND eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(vPhoneCode,'',vPhone) = '" . clean($keyword_new) . "')) AND eStatus = '" . clean($eStatus) . "'";
                }
            }
            else {
                $ssql .= " AND (concat(tsu.vName,' ',tsu.vLastName) LIKE '%" . clean($keyword_new) . "%' OR tsu.vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(tsu.vPhoneCode,'',tsu.vPhone) LIKE '%" . clean($keyword_new) . "%'))";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(tsu.vName,' ',tsu.vLastName) LIKE '%" . clean($keyword_new) . "%' OR tsu.vEmail LIKE '%" . clean($keyword_new) . "%' OR (CONCAT(tsu.vPhoneCode,'',tsu.vPhone) = '" . clean($keyword_new) . "'))";
                }
            }
        }
    }
    else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND tsu.eStatus = '" . clean($eStatus) . "'";
    }
    $ssql1 = "AND (tsu.vEmail != '' OR tsu.vPhone != '')";
    $per_page = $DISPLAY_RECORD_NUMBER;
    if ($eStatus != '') {
        $estatusquery = "";
    }
    else {
        $estatusquery = " AND tsu.eStatus != 'Deleted'";
    }


    if($searchRider != ''){
        $ssql .= " AND tsu.iUserId = '" . clean($searchRider) . "'";
    }
    if (!empty($eStatus)) {
        $esql = "";
    }
    else {
        $esql = " AND tsu.eStatus != 'Deleted'";
    }
    $sql = "SELECT CONCAT(tsu.vName,' ',tsu.vLastName) AS name,tsu.vEmail,(SELECT COUNT(ru.iUserId) FROM register_user as ru WHERE iUserId IN (tsu.tUserIds)) as LinkedMembers,tsu.tRegistrationDate,CONCAT('(+',tsu.vPhoneCode,')  ',tsu.vPhone) AS Mobile,tsu.eStatus,(SELECT ru1.vTimeZone FROM register_user as ru1 WHERE iUserId = tsu.tUserIds) as vTimeZone FROM track_service_users as tsu WHERE 1=1 $esql $ssql $ssql1 $rdr_ssql $ord";
    if ($type == 'XLS') {
        $filename = "Track_any_service_user_" . $timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $serverTimeZone = date_default_timezone_get();
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_USER_NAME_LBL_TXT']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_EMAIL_TEXT']);
        $sheet->setCellValue('C1', "Linked Members");
        $sheet->setCellValue('D1', "Registration Date");
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_MOBILE_NUMBER_HEADER_TXT']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_Status']);

        $i = 2;
        foreach ($result as $value) {
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tRegistrationDate'],$value['vTimeZone'],$serverTimeZone) : $value['tRegistrationDate'];
            $get_tRegistrationDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['tRegistrationDate'] = $get_tRegistrationDate_format['tDisplayDate'];//DateTime($val);

            $sheet->setCellValue('A' . $i, clearName($value["name"]));
            $sheet->setCellValue('B' . $i, (!empty($value["vEmail"])) ? clearEmail($value["vEmail"]) : "--");
            $sheet->setCellValue('C' . $i, $value["LinkedMembers"]);
            $sheet->setCellValue('D' . $i, $value["tRegistrationDate"]);  
            $sheet->setCellValue('E' . $i, $value["Mobile"]); 
            $sheet->setCellValue('F' . $i, $value["eStatus"]); 
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

    }
}
if($section == 'Reward_Report')
{
    $getActiveCampaign = $DRIVER_REWARD_OBJ->getActiveCampaign();
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY dr.iDriverReward DESC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY rd.vName ASC";
        else
            $ord = " ORDER BY rd.vName DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY dr.iAcceptanceRate ASC";
        else
            $ord = " ORDER BY dr.iAcceptanceRate DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY rs.vLevel ASC";
        else
            $ord = " ORDER BY rs.vLevel DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY dr.tDate ASC";
        else
            $ord = " ORDER BY dr.tDate DESC";
    }
    if ($sortby == 5) {
        if ($order == 0)
            $ord = " ORDER BY dr.fRatings ASC";
        else
            $ord = " ORDER BY dr.fRatings DESC";
    }
    $cmp_ssql = "";
    $dri_ssql = "";
    $option = isset($_REQUEST['option']) ? $_REQUEST['option'] : "";

    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";

    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";

    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $iCampaignId = isset($_REQUEST['iCampaignId']) ? $_REQUEST['iCampaignId'] : 0;
    $ssql = '';
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array("(", "+", ")");
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
           $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'drivername') {
                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
            }
            if ($option == 'level') {
                $option_new = "rs.vLevel";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND c.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND c.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
                }
            }
        } else {
            $ssql .= " AND ( (concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword_new) . "%') OR (rs.vLevel LIKE '%" . clean($keyword_new) . "%')  )";
            if (SITE_TYPE == 'Demo') {
                $ssql .= " AND ((concat(rd.vName,' ',rd.vLastName) = '" . clean($keyword_new) . "') OR (rs.vLevel LIKE '%" . clean($keyword_new) . "%'))";
            }
        }
        if (!empty($eStatus)) {
            $equery = "";
        } else {
            $equery = " AND  c.eStatus != 'Deleted'";
        }
    } 
    if ($iCampaignId) {
        $ssql .= " AND dr.iCampaignId = " . $iCampaignId . "";
    }else if(scount($getActiveCampaign) > 0){
        $ssql .= " AND dr.iCampaignId = " .$getActiveCampaign[0]['iCampaignId'] . "";
    }

    $sql ="SELECT concat(rd.vName,' ',rd.vLastName) AS name,rc.vTitle,JSON_UNQUOTE(JSON_VALUE(rs.vLevel, '$.vLevel_".$default_lang."')) as vLevel,dr.vMinimumTrips,dr.iAcceptanceRate,dr.iCancellationRate,dr.fRatings,dr.tDate,rd.vTimeZone
        FROM `driver_reward` as dr JOIN reward_settings as rs ON dr.iRewardId = rs.iRewardId 
        JOIN register_driver as rd ON rd.iDriverId = dr.iDriverId 
        JOIN reward_campaign as rc ON rc.iCampaignId = dr.iCampaignId WHERE 1 = 1 $ssql $ord";
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
            $filename = "Reward_Report_" . $timestamp_filename . ".xls";
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $flag = false;
            $result = $obj->MySQLSelect($sql) or die('Query Failed!');
            $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1', $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']);
            $sheet->setCellValue('B1', "Campaign");
            $sheet->setCellValue('C1',  "Level");
            $sheet->setCellValue('D1', $langage_lbl_admin['LBL_TRIP_TXT']);
            $sheet->setCellValue('E1', $langage_lbl_admin['LBL_ACCEPTANCE_RATE']);
            $sheet->setCellValue('F1', $langage_lbl_admin['LBL_CANCELLATION_RATE_INFO']);
            $sheet->setCellValue('G1', $langage_lbl_admin['LBL_RATINGS_REWARD']);
            $sheet->setCellValue('H1', $langage_lbl_admin['LBL_DATE_TXT']);
            $i = 2;
            foreach ($result as $value) {
               
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tDate'],$value['vTimeZone'],$serverTimeZone) : $value['tDate'];
                $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $value["tdate"] = $get_tDate_format['tDisplayDate'];
                $sheet->setCellValue('A' . $i, clearName($value["name"]));
                $sheet->setCellValue('B' . $i, clearName($value["vTitle"]));
                $sheet->setCellValue('C' . $i,clearName($value["vLevel"]));
                $sheet->setCellValue('D' . $i,clearName($value["vMinimumTrips"]));
                $sheet->setCellValue('E' . $i, $value["iAcceptanceRate"]);
                $sheet->setCellValue('F' . $i, $value["iCancellationRate"]);
                $sheet->setCellValue('G' . $i, $value["fRatings"]);   
                $sheet->setCellValue('H' . $i, $value["tdate"]);           
                $i++;
            }
            // Auto-size columns
            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            // Create a writer object
           $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    }
}
if($section == 'alloders')
{
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $order_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
    $script = $order_type == 'processing' ? "Processing Orders" : "All Orders";
    $os_ssql = "";
    if (!$MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
        $os_ssql .= " AND eBuyAnyService = 'No'";
    }
    if (!$MODULES_OBJ->isTakeAwayEnable()) {
        $os_ssql .= " AND eTakeaway != 'Yes'";
    }
    $processing_status_array = array('1', '2', '4', '5');
    $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12');
    if ($MODULES_OBJ->isEnableAnywhereDeliveryFeature()) {
        $processing_status_array = array('1', '2', '4', '5', '13', '14');
        $all_status_array = array('1', '2', '4', '5', '6', '7', '8', '9', '11', '12', '13', '14');
    }

    if (isset($_REQUEST['iStatusCode']) && $_REQUEST['iStatusCode'] != '') {
        $all_status_array = array($_REQUEST['iStatusCode']);
    }
    if ($order_type == 'processing') {
        $iStatusCode = '(' . implode(',', $processing_status_array) . ')';
    } else {
        $iStatusCode = '(' . implode(',', $all_status_array) . ')';
        $langage_lbl_admin['LBL_PROCESSING_ORDERS'] = $langage_lbl_admin['LBL_ALL_ORDER'];
    }

    $os_ssql .= " AND iStatusCode IN $iStatusCode ";
    $orderStatus = $obj->MySQLSelect("select iOrderStatusId,vStatus,iStatusCode from order_status WHERE 1 = 1 $os_ssql GROUP BY iStatusCode");

    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $promocode = isset($_REQUEST['promocode']) ? $_REQUEST['promocode'] : '';
    $ord = ' ORDER BY o.iOrderId DESC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY o.tOrderRequestDate ASC";
        else
            $ord = " ORDER BY o.tOrderRequestDate DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY riderName ASC";
        else
            $ord = " ORDER BY riderName DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY c.vCompany ASC";
        else
            $ord = " ORDER BY c.vCompany DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY driverName ASC";
        else
            $ord = " ORDER BY driverName DESC";
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $searchStore = isset($_REQUEST['searchStore']) ? $_REQUEST['searchStore'] : '';
    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
    $searchUser = isset($_REQUEST['searchUser']) ? $_REQUEST['searchUser'] : '';
    $searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
    $searchOrderNo = isset($_REQUEST['searchOrderNo']) ? $_REQUEST['searchOrderNo'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $searchOrderStatus = isset($_REQUEST['searchOrderStatus']) ? $_REQUEST['searchOrderStatus'] : '';
    if (isset($_REQUEST['iStatusCode']) && $_REQUEST['iStatusCode'] != '') {
        $searchOrderStatus = $_REQUEST['iStatusCode'];
    }
    if ($startDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
    }
    if ($searchOrderNo != '') {
        $ssql .= " AND o.vOrderNo ='" . $searchOrderNo . "'";
    }
    if ($searchStore != '') {
        $ssql .= " AND c.iCompanyId ='" . $searchStore . "'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND d.iDriverId ='" . $searchDriver . "'";
    }
    if ($searchUser != '') {
        $ssql .= " AND o.iUserId ='" . $searchUser . "'";
    }
    if ($searchServiceType != '' && !in_array($searchServiceType, ['Genie', 'Runner', 'Anywhere'])) {
        $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "' AND o.eBuyAnyService ='No'";
    }
    if ($searchServiceType == "Genie") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
    }
    if ($searchServiceType == "Runner") {
        $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
    }
    if ($searchOrderStatus != '') {
        $ssql .= " AND o.iStatusCode ='" . $searchOrderStatus . "'";
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
    }
    if (!empty($promocode) && isset($promocode)) {
        $ssql .= " AND o.vCouponCode LIKE '" . $promocode . "' AND o.iStatusCode=6";
    }
    //$ssql .= " AND (c.iServiceId IN(" . $enablesevicescategory . ") OR c.eBuyAnyService = 'Yes') ";
   $sql = "SELECT sc.vServiceName_" . $default_lang . " as vServiceName,o.vOrderNo,o.tOrderRequestDate,CONCAT(u.vName,' ',u.vLastName,',',u.vPhoneCode,' ',u.vPhone) AS riderName,CONCAT(c.vCompany,',',c.vCode,' ',c.vPhone) as stroe_name,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.fTotalGenerateFare,os.vStatus,o.ePaymentOption,o.iOrderId, o.fSubTotal,o.iServiceId,o.fOffersDiscount,o.fCommision,o.fDeliveryCharge,o.iStatusCode,o.vTimeZone,o.iUserId,o.iUserAddressId,u.vCountry,o.dDeliveryDate,o.tOrderRequestDate,o.ePayWallet,o.fNetTotal,o.fWalletDebit,o.iDriverId,o.iCompanyId,c.eAutoaccept,o.eTakeaway,o.fTipAmount,o.eBuyAnyService,o.eForPickDropGenie,o.eCancelledbyDriver,o.vCancelReasonDriver, o.eCancelledBy, o.eAskCodeToUser, o.vRandomCode, o.eOrderplaced_by, o.vName as KioskUserName, o.tKioskUserDetails, c.vCode,o.vTimeZone FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceId = o.iServiceId WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND IF(o.eBuyAnyService = 'Yes' && os.iStatusCode IN (1,4,13,14), os.eBuyAnyService = 'Yes', os.eBuyAnyService = 'No') AND o.iStatusCode IN $iStatusCode $ssql $trp_ssql $ord";

   /* $sql = "SELECT sc.vServiceName_" . $default_lang . " as vServiceName,o.vOrderNo,o.tOrderRequestDate,CONCAT(u.vName,' ',u.vLastName) AS riderName,CONCAT('<b>Phone: </b> +',u.vPhoneCode,' ',u.vPhone)  as user_phone,c.vCompany,CONCAT('<b>Phone: </b> +',c.vCode,' ',c.vPhone) as resturant_phone,CONCAT(d.vName,' ',d.vLastName) AS driverName,o.fTotalGenerateFare,os.vStatus,o.ePaymentOption,os.eBuyAnyService,o.eForPickDropGenie FROM orders o LEFT JOIN register_driver d ON d.iDriverId = o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceId = o.iServiceId WHERE IF(o.eTakeaway = 'Yes' && os.iStatusCode = 6, os.eTakeaway='Yes', os.eTakeaway != 'Yes') AND IF(o.eBuyAnyService = 'Yes' && os.iStatusCode IN (1,4,13,14), os.eBuyAnyService = 'Yes', os.eBuyAnyService = 'No') AND o.iStatusCode IN $iStatusCode $ssql $trp_ssql $ord";*/
    if ($type == 'XLS') {
            if($order_type == "processing")
            {
                $filename = "Processing_" . $timestamp_filename . ".xls";
            }
            else
            {
                $filename = "All_Orders_" . $timestamp_filename . ".xls";
            }
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $flag = false;
            $result = $obj->MySQLSelect($sql) or die('Query Failed!');
             $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1', "Serivce Type");
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_ORDER_NO_TXT']);
            $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_ORDER_DATE_TXT']);
            $sheet->setCellValue('D1', $langage_lbl_admin['LBL_TRACK_SERVICE_COMPANY_USER_NAME_TXT']);
            $sheet->setCellValue('E1', $langage_lbl_admin['LBL_STORE_NAME_FOR_GENIE']);
            $sheet->setCellValue('F1', "Delivery Service Provider");
            $sheet->setCellValue('G1', "Order Total");
            $sheet->setCellValue('H1', "Order Status");
            $sheet->setCellValue('I1', $langage_lbl_admin['LBL_PAYMENT_MODE_TXT']);
            $i = 2;
            $serverTimeZone = date_default_timezone_get();
            foreach ($result as $value) {
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tOrderRequestDate'],$value['vTimeZone'],$serverTimeZone) : $value['tOrderRequestDate'];
                $get_tOrderRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                $value['tOrderRequestDate'] = $get_tOrderRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

                if ($value['eBuyAnyService'] == "Yes") {
                    $value['vServiceName'] = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
                    if ($value['eForPickDropGenie'] == "Yes") {
                        $value['vServiceName'] = $langage_lbl_admin['LBL_RUNNER'];
                    }
                }
                $sheet->setCellValue('A' . $i, $value["vServiceName"]);
                $sheet->setCellValue('B' . $i, $value["vOrderNo"]);  
                $sheet->setCellValue('C' . $i, $value['tOrderRequestDate']);  
                $sheet->setCellValue('D' . $i, clearName($value['riderName']));  
                $sheet->setCellValue('E' . $i, $value['stroe_name']);  
                $sheet->setCellValue('F' . $i, clearName($value['driverName']));  
                $sheet->setCellValue('G' . $i, formateNumAsPerCurrency($value['fTotalGenerateFare'], ''));  
                $sheet->setCellValue('H' . $i, $value['vStatus']);  
                $sheet->setCellValue('I' . $i, $value['ePaymentOption']);  
                $i++;
            }
            // Auto-size columns

            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
                //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
                
            }
            
            $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    }
}
if($section == "bidding_report")
{
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And bid.dBiddingDate > '" . WEEK_DATE . "'";
    }
    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY bid.iBiddingPostId DESC';
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY bid.dBiddingDate ASC";
        } else {
            $ord = " ORDER BY bid.dBiddingDate DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY d.vName ASC";
        } else {
            $ord = " ORDER BY d.vName DESC";
        }
    }
    if ($sortby == 5) {
        if ($order == 0) {
            $ord = " ORDER BY ru.vName ASC";
        } else {
            $ord = " ORDER BY ru.vName DESC";
        }
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $searchCompany = isset($_REQUEST['searchCompany']) ? $_REQUEST['searchCompany'] : '';
    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $vBiddingPostNo = isset($_REQUEST['vBiddingPostNo']) ? $_REQUEST['vBiddingPostNo'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : '';
    $eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
    $iBiddingPostId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';


    if ($startDate != '') {
        $ssql .= " AND Date(bid.dBiddingDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(bid.dBiddingDate) <='" . $endDate . "'";
    }
    if ($vBiddingPostNo != '') {
        $ssql .= " AND bid.vBiddingPostNo ='" . $vBiddingPostNo . "'";
    }
    if ($searchCompany != '') {
        $ssql .= " AND bid.iCompanyId ='" . $searchCompany . "'";
    }
    if ($searchDriver != '') {
        $ssql .= " AND bid.iDriverId ='" . $searchDriver . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND bid.iUserId ='" . $searchRider . "'";
    }
    if ($eStatus == "Accepted") {
        $ssql .= " AND (bid.eStatus = 'Accepted')";
    } else if ($eStatus == "Pending") {
        $ssql .= " AND (bid.eStatus = 'Pending')";
    } else if ($eStatus == "Cancelled") {
        $ssql .= " AND (bid.eStatus = 'Cancelled')";
    } else if ($eStatus == "Completed") {
        $ssql .= " AND bid.eStatus = 'Completed'";
    } else if ($eStatus == "Expired") {
        $ssql .= " AND bid.eStatus  IN ('Pending')  AND bid.dBiddingDate < (NOW()) - INTERVAL 30 MINUTE";
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And bid.dBiddingDate > '" . WEEK_DATE . "'";
    }
    $TimeZoneOffset = date("P");
    $sql = "SELECT CASE WHEN bid.eStatus ='Pending' THEN bid.dBiddingDate < ((CONVERT_TZ(NOW(), 'SYSTEM', '" . $TimeZoneOffset . "')) - INTERVAL 30 MINUTE) ELSE  '0' END  as isExpired, bid.vBiddingPostNo,JSON_UNQUOTE(JSON_VALUE(bs.vTitle, '$.vTitle_" . $default_lang . "')) as vTitle,ua.vServiceAddress,bid.dBiddingDate,CONCAT(d.vName,' ',d.vLastName) AS driverName,CONCAT(ru.vName,' ',ru.vLastName) AS riderName,bid.iBiddingPostId,bid.eStatus,ua.vAddressType,bid.vTimeZone FROM bidding_post as bid LEFT JOIN register_user as ru ON ru.iUserId = bid.iUserId LEFT JOIN user_address as ua ON ua.iUserAddressId = bid.iAddressId LEFT JOIN register_driver d ON d.iDriverId = bid.iDriverId LEFT JOIN bidding_service as bs ON bs.iBiddingId = bid.iBiddingId WHERE 1=1 {$ssql} {$trp_ssql} {$ord}";
    $serverTimeZone = date_default_timezone_get();   
    if ($type == 'XLS') {
            $filename = "Bidding_Report_" . $timestamp_filename . ".xls";
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $result = $obj->MySQLSelect($sql) or die('Query Failed!');
            $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1', $langage_lbl_admin['LBL_TRIP_NO_ADMIN']);
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_BALANCE_TYPE']);
            $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_PROFILE_ADDRESS']);
            $sheet->setCellValue('D1', "Bids Date");
            $sheet->setCellValue('E1', $langage_lbl_admin['LBL_DRIVER_NAME_ADMIN']);
            $sheet->setCellValue('F1', $langage_lbl_admin['LBL_USER']);
            $sheet->setCellValue('G1', $langage_lbl_admin['LBL_FARE_TXT']);
            $sheet->setCellValue('H1', $langage_lbl_admin['LBL_Status']);
            $i = 2;
            foreach ($result as $value) {
                $value['vServiceAddress'] = (!empty($value['vAddressType'])) ? $value['vAddressType'] ." ". $value['vServiceAddress'] : $value['vServiceAddress'];
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['dBiddingDate'],$value['vTimeZone'],$serverTimeZone) : $value['dBiddingDate'];
                $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text =" (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                $value['dBiddingDate'] = $get_Signup_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

                $getBiddingPost = $BIDDING_OBJ->getBiddingPost('webservice',$value['iBiddingPostId']);
                $sql = "SELECT amount FROM  `bidding_offer`  WHERE 1 = 1 AND eStatus = 'Accepted' AND iBiddingPostId = " . $value['iBiddingPostId'] . "   ORDER BY `IOfferId` DESC LIMIT 1";
                $bidding_final_offer = $obj->MySQLSelect($sql);
                if (empty($bidding_final_offer)) {
                    $bidding_final_offer[0]['amount'] = $getBiddingPost[0]['fBiddingAmount'];
                }
                $fOutStandingAmount = round($getBiddingPost[0]['fOutStandingAmount'], 2);
                $fWalletDebit = round($getBiddingPost[0]['fWalletDebit'], 2);

                $total_Fare = $bidding_final_offer[0]['amount'] + $getBiddingPost[0]['fTax1'] + $getBiddingPost[0]['fTax2'];
                $total_Fare = round($total_Fare, 2);
            
                $value['iBiddingPostId'] = $total_Fare - $fWalletDebit + $fOutStandingAmount;
                $value['iBiddingPostId'] = formateNumAsPerCurrency($value['iBiddingPostId'], '');

                if ($value['isExpired'] == 1) {
                    $value["eStatus"] = $langage_lbl_admin["LBL_EXPIRED_TXT"];
                } 
                $sheet->setCellValue('A' . $i, $value["vBiddingPostNo"]);
                $sheet->setCellValue('B' . $i, $value["vTitle"]);
                $sheet->setCellValue('C' . $i, $value['vServiceAddress']);
                $sheet->setCellValue('D' . $i, $value['dBiddingDate']);
                $sheet->setCellValue('E' . $i, clearName($value["driverName"]));
                $sheet->setCellValue('F' . $i, clearName($value["riderName"]));   
                $sheet->setCellValue('G' . $i, $value["iBiddingPostId"]);   
                $sheet->setCellValue('H' . $i, $value["eStatus"]);            
                $i++;
            }
             // Auto-size columns
            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    }   
}
if($section == "cancelled_orders")
{
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY o.iOrderId DESC';
    if ($sortby == 1) {
        if ($order == 0) $ord = " ORDER BY o.tOrderRequestDate ASC";
        else
            $ord = " ORDER BY o.tOrderRequestDate DESC";
    }
    if ($sortby == 2) {
        if ($order == 0) $ord = " ORDER BY o.eCancelledBy ASC";
        else
            $ord = " ORDER BY o.eCancelledBy DESC";
    }
    if ($sortby == 3) {
        if ($order == 0) $ord = " ORDER BY o.vCancelReason ASC";
        else
            $ord = " ORDER BY o.vCancelReason DESC";
    }
    if ($sortby == 4) {
        if ($order == 0) $ord = " ORDER BY d.vName ASC";
        else
            $ord = " ORDER BY d.vName DESC";
    }
    //End Sorting
    // Start Search Parameters
    $ssql = '';
    $iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $searchOrderNo = isset($_REQUEST['searchOrderNo']) ? $_REQUEST['searchOrderNo'] : '';
    $searchServiceType = isset($_REQUEST['searchServiceType']) ? $_REQUEST['searchServiceType'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $action =  isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    if ($action == 'search') {
        if ($startDate != '') {
            $ssql .= " AND Date(o.tOrderRequestDate) >='" . $startDate . "'";
        }
        if ($endDate != '') {
            $ssql .= " AND Date(o.tOrderRequestDate) <='" . $endDate . "'";
        }
        if ($iDriverId != '') {
            $ssql .= " AND o.iDriverId ='" . $iDriverId . "'";
        }
        if ($searchOrderNo != '') {
            $ssql .= " AND o.vOrderNo ='" . $searchOrderNo . "'";
        }
        if ($searchServiceType != '' && !in_array($searchServiceType, [
                'Genie',
                'Runner',
                'Anywhere'
            ])
        ) {
            $ssql .= " AND sc.iServiceId ='" . $searchServiceType . "' AND o.eBuyAnyService ='No'";
        }
        if ($searchServiceType == "Genie") {
            $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'No' ";
        }
        if ($searchServiceType == "Runner") {
            $ssql .= " AND o.eBuyAnyService ='Yes' AND o.eForPickDropGenie = 'Yes' ";
        }
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And o.tOrderRequestDate > '" . WEEK_DATE . "'";
    }
    $ssql .= " AND sc.iServiceId IN(" . $enablesevicescategory . ")";
    $sql = "select vName,vSymbol from currency where eStatus='Active' AND eDefault='Yes'";
    $db_currency = $obj->MySQLSelect($sql);
    $default_currency = $db_currency[0]['vName'];
    $vSymbol = $db_currency[0]['vSymbol']; 
    $sql = "SELECT sc.vServiceName_" . $default_lang . " as vServiceName,o.vOrderNo,o.tOrderRequestDate,CONCAT(u.vName,' ',u.vLastName,',',u.vPhoneCode,' ',u.vPhone) AS UserName,CONCAT(c.vCompany,',',c.vCode,' ',c.vPhone) AS Store_name,CONCAT(d.vName,' ',d.vLastName,' ',d.vCode,' ',d.vPhone) as service_provider_name,o.fNetTotal,os.vStatus,o.ePaymentOption,o.eBuyAnyService,o.eForPickDropGenie,o.vTimeZone FROM orders o LEFT JOIN register_driver d ON d.iDriverId =o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('9','8') $ssql $trp_ssql $ord";
   
    // $sql = "SELECT o.iOrderId,o.iUserId, o.iStatusCode,o.iCompanyId, sc.vServiceName_" . $default_lang . " as vServiceName, o.tOrderRequestDate,o.fOutStandingAmount ,o.fTotalGenerateFare ,o.fNetTotal ,o.fCancellationCharge ,o.dDeliveryDate,o.iCancelledById, o.iReasonId, o.vCancelReason, d.iDriverId, o.vOrderNo,o.ePayWallet, o.ePaymentOption, o.fRefundAmount, o.eCancelledBy, o.fWalletDebit,o.eBuyAnyService, o.fDeliveryChargeCancelled, o.eForPickDropGenie, os.vStatus, o.fRatio_" . $default_currency . " as fRatio ,CONCAT(d.vName,' ',d.vLastName) AS dName,CONCAT(u.vName,' ',u.vLastName) AS UserName,c.vCompany,CONCAT(u.vPhoneCode,' ',u.vPhone)  as user_phone,CONCAT(d.vCode,' ',d.vPhone) as driver_phone,CONCAT(c.vCode,' ',c.vPhone) as resturant_phone, o.vTimeZone FROM orders o LEFT JOIN register_driver d ON d.iDriverId =o.iDriverId LEFT JOIN  register_user u ON u.iUserId = o.iUserId LEFT JOIN company c ON c.iCompanyId = o.iCompanyId LEFT JOIN order_status as os on os.iStatusCode = o.iStatusCode LEFT JOIN service_categories as sc on sc.iServiceid = o.iServiceid WHERE 1=1 AND o.iStatusCode IN ('9','8') $ssql $trp_ssql $ord";
    $serverTimeZone = date_default_timezone_get();

    if ($type == 'XLS') {
            $filename = "cancelled_orders_" . $timestamp_filename . ".xls";
            $result = $obj->MySQLSelect($sql) or die('Query Failed!');
            $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1', "Order Service Type");
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_ORDER_NO_TXT']);
            $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_ORDER_DATE_TXT']);
            $sheet->setCellValue('D1', $langage_lbl_admin['LBL_TRACK_SERVICE_COMPANY_USER_NAME_TXT']);
            $sheet->setCellValue('E1', $langage_lbl_admin['LBL_STORE_NAME_FOR_GENIE']);
            $sheet->setCellValue('F1', $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']);
            $sheet->setCellValue('G1', "Order Total");
            $sheet->setCellValue('H1', "Order Status");
            $sheet->setCellValue('I1', $langage_lbl_admin['LBL_PYMENT_MODE']);
            $i = 2;
            foreach ($result as $value) {
                if ($value['eBuyAnyService'] == "Yes") {
                        $value['vServiceName'] = $langage_lbl_admin['LBL_OTHER_DELIVERY'];
                        if ($value['eForPickDropGenie'] == "Yes") {
                            $value['vServiceName'] = $langage_lbl_admin['LBL_RUNNER'];
                        }
                }
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tOrderRequestDate'],$value['vTimeZone'],$serverTimeZone) : $value['tOrderRequestDate'];
                $get_tOrderRequestDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                $value['tOrderRequestDate'] = $get_tOrderRequestDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

                $sheet->setCellValue('A' . $i, $value['vServiceName']);
                $sheet->setCellValue('B' . $i, $value['vOrderNo']);
                $sheet->setCellValue('C' . $i, $value['tOrderRequestDate']);
                $sheet->setCellValue('D' . $i, $value['UserName']);
                $sheet->setCellValue('E' . $i, $value['Store_name']);    
                $sheet->setCellValue('F' . $i, $value['service_provider_name']); 
                $sheet->setCellValue('G' . $i, formateNumAsPerCurrency($value['fNetTotal'],'')); 
                $sheet->setCellValue('H' . $i, $value['vStatus']); 
                $sheet->setCellValue('I' . $i, $value['ePaymentOption']);
                $i++;
             }
            // Auto-size columns

            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    }
}
if($section == "payment_report")
{
    $eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
    $script = $eMasterType.'Report';
    $iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $rdr_ssql = "";

    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And dRentItemPostDate > '" . WEEK_DATE . "'";
    }

    //data for select fields
    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $iRentItemId = isset($_REQUEST['iRentItemId']) ? $_REQUEST['iRentItemId'] : '';
    $ord = ' ORDER BY r.iRentItemPostId  DESC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY riderName ASC";
        else
            $ord = " ORDER BY riderName DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY vPlanName ASC";
        else
            $ord = " ORDER BY vPlanName DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY vTitleCat ASC";
        else

           $ord = " ORDER BY vTitleCat DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY r.dRentItemPostDate ASC";
        else

           $ord = " ORDER BY r.dRentItemPostDate DESC";
    }
    if ($sortby == 5) {
        if ($order == 0)
            $ord = " ORDER BY r.fAmount ASC";
        else

           $ord = " ORDER BY r.fAmount DESC";
    }
    if ($sortby == 6) {
        if ($order == 0)
            $ord = " ORDER BY r.eStatus ASC";
        else

           $ord = " ORDER BY r.eStatus DESC";
    }
    if ($sortby == 7) {
        if ($order == 0)
            $ord = " ORDER BY r.ePaid ASC";
        else
           $ord = " ORDER BY r.ePaid DESC";
    }
    if ($sortby == 8) {
        if ($order == 0)
            $ord = " ORDER BY rc.iMasterServiceCategoryId ASC";
        else
           $ord = " ORDER BY rc.iMasterServiceCategoryId DESC";
    } 
    // Start Search Parameters

    $ssql = '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $searchPaymentPlan = isset($_REQUEST['searchPaymentPlan']) ? $_REQUEST['searchPaymentPlan'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    if ($startDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) <='" . $endDate . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND r.iUserId ='" . $searchRider . "'";
    }
    if($searchPaymentPlan != ""){
        $ssql .= " AND r.iPaymentPlanId ='" . $searchPaymentPlan . "'";
    }
    if ($iRentItemId != '') {
        $ssql .= " AND r.iItemCategoryId ='" . $iRentItemId . "'";
    }
    $trp_ssql = "";

    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";
    }
    $eTypesql = "";
    if($iMasterServiceCategoryId != ""){
        $eTypesql  = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
    }
    $sql = "SELECT r.vRentItemPostNo,CONCAT(u.vName,' ',u.vLastName) AS riderName,r.iRentItemPostId,JSON_UNQUOTE(JSON_VALUE(rc.vTitle, '$.vTitle_" . $default_lang . "')) AS vTitleCat,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,r.dRentItemPostDate,rp.fAmount as planamount,r.eStatus,r.tFieldsArr,rp.eFreePlan,rp.iTotalPost,r.iUserId,r.vTimeZone FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql} {$ssql} {$trp_ssql}  {$ord}";

        // $sql = "SELECT r.vRentItemPostNo,r.iItemSubCategoryId,r.iItemCategoryId,r.iUserId,r.tFieldsArr,r.iRentItemPostId,r.dRentItemPostDate,r.eStatus,r.eUserPayment,r.ePaid,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,rp.eFreePlan,rp.iTotalPost,rp.fAmount as planamount,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(rc.vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql} {$ssql} {$trp_ssql}  {$ord} LIMIT {$start}, {$per_page}";
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
            $filename = $script.".xls";
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/vnd.ms-excel");
            $flag = false;
            $result = $obj->MySQLSelect($sql) or die('Query Failed!');
            $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1',"Post Number");
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_USER']);
            $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_RENT_LISTING_TYPE']);
            $sheet->setCellValue('D1', $langage_lbl_admin['LBL_CATEGORY_TXT']);
            $sheet->setCellValue('E1', $langage_lbl_admin['LBL_RENT_PAYMENT_PLAN']);
            $sheet->setCellValue('F1', $langage_lbl_admin['LBL_RENT_DATE_POSTED']);
            $sheet->setCellValue('G1', $langage_lbl_admin['LBL_AMOUNT']);
            $sheet->setCellValue('H1', $langage_lbl_admin['LBL_Status']);
                
            $i = 2;
			$TotalAmount = 0;
            foreach ($result as $value) {
                $getRentItemPostData = $RENTITEM_OBJ->getFieldsDataArray($value['tFieldsArr'],$value['iRentItemPostId'],$default_lang);
                if(!empty($getRentItemPostData[$value['iRentItemPostId']])){
                    $listing_type = $getRentItemPostData[$value['iRentItemPostId']]['eListingTypeWeb'];
                }
                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['dRentItemPostDate'],$value['vTimeZone'],$serverTimeZone) : $value['dRentItemPostDate'];
                $get_dRentItemPostDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                $dRentItemPostDate = $get_dRentItemPostDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

                $pSql = "";
                if($iMasterServiceCategoryId != ""){
                    $pSql  = " And rp.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
                }
                $getlog =  "SELECT rl.iRentItemPostId,rl.iPaymentPlanId, MAX(rl.iTotalPost) AS MaxTotalPost,r.tPaymentPlanDetails FROM rentitem_payment_log as rl LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=rl.iPaymentPlanId LEFT JOIN  rentitem_post as r on r.iRentItemPostId=rl.iRentItemPostId WHERE 1=1 AND rl.iUserId= '".$value['iUserId']."' $pSql ORDER BY MaxTotalPost DESC";
                $userlogData = $obj->MySQLSelect($getlog);
                $userlogData = $userlogData[0];

                if($value['eFreePlan'] == "Yes"){ 
                    $planamount = "-"; 
                } else if ($value['iTotalPost'] == "0") {
                    $planamount = formateNumAsPerCurrency($value['planamount'],""); 
					$TotalAmount += $value['planamount'];
                } else {
                    $planvalue = "";
                    if($userlogData['iRentItemPostId'] == $value['iRentItemPostId']){      
                        if(!empty($userlogData['tPaymentPlanDetails'])){
                            $tPaymentPlanDetails = json_decode($userlogData['tPaymentPlanDetails'], true);
                            $planvalue = formateNumAsPerCurrency($tPaymentPlanDetails['fAmount'],"");
							$TotalAmount += $tPaymentPlanDetails['fAmount'];
                        } else {
                            $planvalue = formateNumAsPerCurrency($value['planamount'],""); 
							$TotalAmount += $value['planamount'];
                        }
                    }
                    if($planvalue == ""){
                        $planamount = '-';
                    } else {
                        $planamount =$planvalue;
                    }
                }

                $sheet->setCellValue('A' . $i, $value["vRentItemPostNo"]);
                $sheet->setCellValue('B' . $i, clearName($value["riderName"]));
                $sheet->setCellValue('C' . $i, $listing_type);
                $sheet->setCellValue('D' . $i, $value['vTitleCat']);
                $sheet->setCellValue('E' . $i, $value["vPlanName"]);
                $sheet->setCellValue('F' . $i, $dRentItemPostDate);
                $sheet->setCellValue('G' . $i, $planamount);
                $sheet->setCellValue('H' . $i, $value["eStatus"]);            
                $i++;
            }
			
			$i +=1;
			$Summary_array = array(					
				"Total Amount " => formateNumAsPerCurrency($TotalAmount, '')   
			);

			foreach ($Summary_array as $key => $value) {
				$sheet->setCellValue('G' . $i, $key);
				$sheet->setCellValue('H' . $i, $value);
				$i++;
			}
	
            // Auto-size columns
            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            // foreach ($result[0] as $key => $val) {
            //     if(!in_array($key,array('tFieldsArr','eFreePlan','iTotalPost','iUserId','vTimeZone')))
            //     {
            //         if($key == "vRentItemPostNo")
            //         {
            //             $key = "Post Number";
            //         }
            //         if($key == "riderName")
            //         {
            //             $key = "User";
            //         }
            //         if($key == "iRentItemPostId")
            //         {
            //             $key = "Listing Type";
            //         }
            //         if($key == "vTitleCat")
            //         {
            //             $key = "Category";
            //         }
            //         if($key == "vPlanName")
            //         {
            //             $key = "Payment Plan";
            //         }
            //         if($key == "dRentItemPostDate")
            //         {
            //             $key = "Date of Posted";
            //         }
            //         if($key == "planamount")
            //         {
            //             $key = "Amount";
            //         }
            //         if($key == "eStatus")
            //         {
            //             $key = "Status";
            //         }
            //         echo $key . "\t"; 
            //     }
            // }
            // echo "\r\n";
            // foreach ($result as $value) {
            //     foreach ($value as $key => $val) {
            //         if(!in_array($key,array('tFieldsArr','eFreePlan','iTotalPost','iUserId','vTimeZone')))
            //         {
            //             if($key == "planamount")
            //             {
            //                 $pSql = "";
            //                 if($iMasterServiceCategoryId != ""){
            //                     $pSql  = " And rp.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
            //                 }
            //                 $getlog =  "SELECT rl.iRentItemPostId,rl.iPaymentPlanId, MAX(rl.iTotalPost) AS MaxTotalPost,r.tPaymentPlanDetails FROM rentitem_payment_log as rl LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=rl.iPaymentPlanId LEFT JOIN  rentitem_post as r on r.iRentItemPostId=rl.iRentItemPostId WHERE 1=1 AND rl.iUserId= '".$value['iUserId']."' $pSql ORDER BY MaxTotalPost DESC";
            //                 $userlogData = $obj->MySQLSelect($getlog);
            //                 $userlogData = $userlogData[0];

            //                 if($value['eFreePlan'] == "Yes"){ 
            //                     $val = "-"; 
            //                 } else if ($value['iTotalPost'] == "0") {
            //                     $val = formateNumAsPerCurrency($val,""); 
            //                 } else {
            //                     $planvalue = "";
            //                     if($userlogData['iRentItemPostId'] == $value['iRentItemPostId']){      
            //                         if(!empty($userlogData['tPaymentPlanDetails'])){
            //                             $tPaymentPlanDetails = json_decode($userlogData['tPaymentPlanDetails'], true);
            //                             $planvalue = formateNumAsPerCurrency($tPaymentPlanDetails['fAmount'],"");
            //                         } else {
            //                             $planvalue = formateNumAsPerCurrency($value['planamount'],""); 
            //                         }
            //                     }
            //                     if($planvalue == ""){
            //                         $val = '-';
            //                     } else {
            //                         $val =$planvalue;
            //                     }
            //                 }
            //             }
            //             if($key == "dRentItemPostDate")
            //             {
            //                 $date_format_data_array = array(
            //                     'langCode' => $default_lang,
            //                     'DateFormatForWeb' => 1
            //                 );
            //                 $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($val,$value['vTimeZone'],$serverTimeZone) : $val;
            //                 $get_dRentItemPostDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            //                 $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
            //                 $val = $get_dRentItemPostDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);
            //             }
            //             if($key == "iRentItemPostId")
            //             {
            //                 $getRentItemPostData = $RENTITEM_OBJ->getFieldsDataArray($value['tFieldsArr'],$value['iRentItemPostId'],$default_lang);
            //                 $val = $getRentItemPostData[$value['iRentItemPostId']]['eListingTypeWeb'];
            //             }
            //             if($key == "driverName")
            //             {
            //                 $val = clearName($val);
            //             }
            //             echo $val. "\t"; 
            //         }
            //     }
            //     echo "\r\n";
            // }
    }   
}
if($section == "all_properties")
{
    $eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "";
    $iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
    $script = 'All'.$eMasterType;
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And dRentItemPostDate > '" . WEEK_DATE . "'";
    }
    //data for select fields
    //Start Sorting
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $iRentItemId = isset($_REQUEST['iRentItemId']) ? $_REQUEST['iRentItemId'] : '';
    $iItemSubCategoryId = isset($_REQUEST['iItemSubCategoryId']) ? $_REQUEST['iItemSubCategoryId'] : '';

    $ord = ' ORDER BY r.iRentItemPostId  DESC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY riderName ASC";
        else
            $ord = " ORDER BY riderName DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY vPlanName ASC";
        else
            $ord = " ORDER BY vPlanName DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY vTitleCat ASC";
        else
           $ord = " ORDER BY vTitleCat DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY r.dRentItemPostDate ASC";
        else
           $ord = " ORDER BY r.dRentItemPostDate DESC";
    }
    if ($sortby == 5) {
        if ($order == 0)
            $ord = " ORDER BY r.dApprovedDate ASC";
        else
           $ord = " ORDER BY r.dApprovedDate DESC";
    }
    if ($sortby == 6) {
        if ($order == 0)
            $ord = " ORDER BY r.eStatus ASC";
        else
           $ord = " ORDER BY r.eStatus DESC";
    }
    if ($sortby == 7) {
        if ($order == 0)
            $ord = " ORDER BY r.dRenewDate ASC";
        else
           $ord = " ORDER BY r.dRenewDate DESC";
    }
    //End Sorting
    $ssql = '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $searchPaymentPlan = isset($_REQUEST['searchPaymentPlan']) ? $_REQUEST['searchPaymentPlan'] : '';
    $searchStatus = isset($_REQUEST['searchStatus']) ? $_REQUEST['searchStatus'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
    $iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';

    if ($startDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) <='" . $endDate . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND r.iUserId ='" . $searchRider . "'";
    }
    if($searchPaymentPlan != ""){
        $ssql .= " AND r.iPaymentPlanId ='" . $searchPaymentPlan . "'";
    }
    if($searchStatus != ""){
        $ssql .= " AND r.eStatus ='" . $searchStatus . "'";
    }
    if ($iRentItemId != '') {
        $ssql .= " AND r.iItemCategoryId ='" . $iRentItemId . "'";
    }
    if($iItemSubCategoryId != ""){
        $ssql .= " AND r.iItemSubCategoryId ='" . $iItemSubCategoryId . "'";
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";
    }
    $eTypesql = "";
    if($iMasterServiceCategoryId != ""){
        $eTypesql  = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
    }
    $sql = "SELECT r.vRentItemPostNo,CONCAT(u.vName,' ',u.vLastName) AS riderName,r.iRentItemPostId,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanNamer,r.dRentItemPostDate,r.dApprovedDate,r.dRenewDate,r.eStatus,r.vTimeZone FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 {$eTypesql} {$ssql} {$trp_ssql}  {$ord}";
    $serverTimeZone = date_default_timezone_get(); 
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        // header("Content-Disposition: attachment; filename=\"$filename\"");
        // header("Content-Type: application/vnd.ms-excel");
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1',"Post Number");
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_USER']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_RENT_LISTING_TYPE']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_CATEGORY_TXT']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_RENT_PAYMENT_PLAN']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_RENT_DATE_POSTED']);
        $sheet->setCellValue('G1', $langage_lbl_admin['LBL_RENT_APPROVED_AT']);
        $sheet->setCellValue('H1', $langage_lbl_admin['LBL_RENT_RENEWAL_DATE']);
        $sheet->setCellValue('I1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
        foreach ($result as $value) {
            $reqArr = array('vCatName','eListingTypeWeb');        
            $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value['iRentItemPostId'], "" , $default_lang,"","","",$reqArr);
            $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
            $listing_type = $getRentItemPostData['eListingTypeWeb'];
            $dRentItemPostDate = $dApprovedDate = $dRenewDate = " ";
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            if($value['dRentItemPostDate'] != "0000-00-00 00:00:00"){
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['dRentItemPostDate'],$value['vTimeZone'],$serverTimeZone) : $value['dRentItemPostDate'];
                $get_Postdate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
                $dRentItemPostDate = $get_Postdate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);
            }
            if($value['dApprovedDate'] != "0000-00-00 00:00:00"){
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['dApprovedDate'],$value['vTimeZone'],$serverTimeZone) : $value['dApprovedDate'];
                $get_Approveddate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $dApprovedDate = $get_Approveddate_format['tDisplayDateTime'];//DateTime($val);
            }
            if($value['dRenewDate'] != "0000-00-00 00:00:00"){
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['dRenewDate'],$value['vTimeZone'],$serverTimeZone) : $value['dRenewDate'];
                $get_Renewdate_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $dRenewDate= $get_Renewdate_format['tDisplayDateTime'];//DateTime($val);
            }
            $sheet->setCellValue('A' . $i, $value["vRentItemPostNo"]);
            $sheet->setCellValue('B' . $i, clearName($value["riderName"]));
            $sheet->setCellValue('C' . $i, $listing_type);
            $sheet->setCellValue('D' . $i, $value["vTitleCat"]);
            $sheet->setCellValue('E' . $i, $value["vPlanNamer"]);
            $sheet->setCellValue('F' . $i, $dRentItemPostDate);    
            $sheet->setCellValue('G' . $i, $dApprovedDate);  
            $sheet->setCellValue('H' . $i, $dRenewDate);     
            $sheet->setCellValue('I' . $i, $value["eStatus"]);          
            $i++;
        }
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
}
if($section == "pending_item")
{
    $eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
    $iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $script = 'Pending' . $eMasterType;
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $iRentItemId = isset($_REQUEST['iRentItemId']) ? $_REQUEST['iRentItemId'] : '';
    $ord = ' ORDER BY r.iRentItemPostId DESC';
    if ($sortby == 1) {
        if ($order == 0) $ord = " ORDER BY riderName ASC"; else
            $ord = " ORDER BY riderName DESC";
    }
    if ($sortby == 2) {
        if ($order == 0) $ord = " ORDER BY vPlanName ASC"; else
            $ord = " ORDER BY vPlanName DESC";
    }
    if ($sortby == 3) {
        if ($order == 0) $ord = " ORDER BY vTitleCat ASC"; else
            $ord = " ORDER BY vTitleCat DESC";
    }
    if ($sortby == 4) {
        if ($order == 0) $ord = " ORDER BY r.dRentItemPostDate ASC"; else
            $ord = " ORDER BY r.dRentItemPostDate DESC";
    }
    if ($sortby == 5) {
        if ($order == 0) $ord = " ORDER BY r.eStatus ASC"; else
            $ord = " ORDER BY r.eStatus DESC";
    }
    $ssql = '';
    $searchPaymentPlan = isset($_REQUEST['searchPaymentPlan']) ? $_REQUEST['searchPaymentPlan'] : '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
    $method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
    $iItemSubCategoryId = isset($_REQUEST['iItemSubCategoryId']) ? $_REQUEST['iItemSubCategoryId'] : '';
    if ($startDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) <='" . $endDate . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND r.iUserId ='" . $searchRider . "'";
    }
    if ($searchPaymentPlan != "") {
        $ssql .= " AND r.iPaymentPlanId ='" . $searchPaymentPlan . "'";
    }
    if ($iRentItemId != '') {
        $ssql .= " AND r.iItemCategoryId ='" . $iRentItemId . "'";
    }
    if ($iItemSubCategoryId != "") {
        $ssql .= " AND r.iItemSubCategoryId ='" . $iItemSubCategoryId . "'";
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";
    }
    $eTypesql = "";
    if ($iMasterServiceCategoryId != "") {
        $eTypesql = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
    }
    $sql = "SELECT r.vRentItemPostNo,CONCAT(u.vName,' ',u.vLastName) AS riderName,r.iRentItemPostId,JSON_UNQUOTE(JSON_VALUE(rc.vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,r.dRentItemPostDate,r.vTimeZone FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 AND r.eStatus = 'Pending' {$eTypesql} {$ssql} {$trp_ssql}  {$ord}";
       
    // $sql = "SELECT r.*,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,CONCAT(u.vName,' ',u.vLastName) AS riderName,JSON_UNQUOTE(JSON_VALUE(rc.vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,rc.iMasterServiceCategoryId FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 AND r.eStatus = 'Pending' {$eTypesql} {$ssql} {$trp_ssql}  {$ord} LIMIT {$start}, {$per_page}";
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Post Number");
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_USER']);
        $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_RENT_LISTING_TYPE']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_CATEGORY_TXT']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_RENT_PAYMENT_PLAN']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_RENT_DATE_POSTED']);
        $i = 2;
        foreach ($result as $value) {
            $reqArr = array('vCatName','eListingTypeWeb');        
            $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value['iRentItemPostId'], "" , $default_lang,"","","",$reqArr);
            $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
            $value["iRentItemPostId"] = $getRentItemPostData['eListingTypeWeb'];
            $value['vTitleCat'] = (!empty(trim($categoryDataArray[1]))) ? $categoryDataArray[0]."(".$categoryDataArray[1].")" : $categoryDataArray[0];

            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value["dRentItemPostDate"],$value['vTimeZone'],$serverTimeZone) : $value["dRentItemPostDate"];
            $get_dRentItemPostDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $time_zone_difference_text = " (UTC:".DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']).")";
            $value["dRentItemPostDate"] = $get_dRentItemPostDate_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

            $sheet->setCellValue('A' . $i, $value["vRentItemPostNo"]);
            $sheet->setCellValue('B' . $i, clearName($value["riderName"]));
            $sheet->setCellValue('C' . $i, $value["iRentItemPostId"]);
            $sheet->setCellValue('D' . $i, $value['vTitleCat']);
            $sheet->setCellValue('E' . $i, $value["vPlanName"]);
            $sheet->setCellValue('F' . $i, $value["dRentItemPostDate"]);            
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
}
if($section == "item_approved")
{
    $eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $eMasterType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
    $iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eMasterType, '', 'true');
    $script = 'Approved' . $eMasterType;
    $rdr_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $rdr_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";
    }
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $iRentItemId = isset($_REQUEST['iRentItemId']) ? $_REQUEST['iRentItemId'] : '';
    $ord = ' ORDER BY r.iRentItemPostId  DESC';
    $iItemSubCategoryId = isset($_REQUEST['iItemSubCategoryId']) ? $_REQUEST['iItemSubCategoryId'] : '';
    if ($sortby == 1) {
        if ($order == 0) $ord = " ORDER BY riderName ASC"; else
            $ord = " ORDER BY riderName DESC";
    }
    if ($sortby == 2) {
        if ($order == 0) $ord = " ORDER BY vPlanName ASC"; else
            $ord = " ORDER BY vPlanName DESC";
    }
    if ($sortby == 3) {
        if ($order == 0) $ord = " ORDER BY vTitleCat ASC"; else

            $ord = " ORDER BY vTitleCat DESC";
    }
    if ($sortby == 4) {
        if ($order == 0) $ord = " ORDER BY r.dRentItemPostDate ASC"; else

            $ord = " ORDER BY r.dRentItemPostDate DESC";
    }
    if ($sortby == 5) {
        if ($order == 0) $ord = " ORDER BY r.dApprovedDate ASC"; else

            $ord = " ORDER BY r.dApprovedDate DESC";
    }
    if ($sortby == 6) {
        if ($order == 0) $ord = " ORDER BY r.eStatus ASC"; else

            $ord = " ORDER BY r.eStatus DESC";
    }
    if ($sortby == 7) {
        if ($order == 0) $ord = " ORDER BY r.dRenewDate ASC"; else

            $ord = " ORDER BY r.dRenewDate DESC";
    }
    $ssql = '';
    $searchPaymentPlan = isset($_REQUEST['searchPaymentPlan']) ? $_REQUEST['searchPaymentPlan'] : '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $serachTripNo = isset($_REQUEST['serachTripNo']) ? $_REQUEST['serachTripNo'] : '';
    $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : '';
    $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : '';
    $vStatus = isset($_REQUEST['vStatus']) ? $_REQUEST['vStatus'] : '';
    $iTripId = isset($_REQUEST['iTripId']) ? $_REQUEST['iTripId'] : '';
    if ($startDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) >='" . $startDate . "'";
    }
    if ($endDate != '') {
        $ssql .= " AND Date(r.dRentItemPostDate) <='" . $endDate . "'";
    }
    if ($searchRider != '') {
        $ssql .= " AND r.iUserId ='" . $searchRider . "'";
    }
    if ($searchPaymentPlan != "") {
        $ssql .= " AND r.iPaymentPlanId ='" . $searchPaymentPlan . "'";
    }
    if ($iRentItemId != '') {
        $ssql .= " AND r.iItemCategoryId ='" . $iRentItemId . "'";
    }
    if ($iItemSubCategoryId != "") {
        $ssql .= " AND r.iItemSubCategoryId ='" . $iItemSubCategoryId . "'";
    }
    $trp_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $trp_ssql = " And r.dRentItemPostDate > '" . WEEK_DATE . "'";
    }
    $eTypesql = "";
    if ($iMasterServiceCategoryId != "") {
        $eTypesql = " And rc.iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
    }
    $sql = "SELECT r.vRentItemPostNo,CONCAT(u.vName,' ',u.vLastName) AS riderName,r.iRentItemPostId,JSON_UNQUOTE(JSON_VALUE(vTitle, '$.vTitle_" . $default_lang . "')) as vTitleCat,JSON_UNQUOTE(JSON_VALUE(vPlanName, '$.vPlanName_" . $default_lang . "')) as vPlanName,r.dRentItemPostDate,r.dApprovedDate,r.dRenewDate,r.vTimeZone FROM rentitem_post r LEFT JOIN rent_item_payment_plan as rp on rp.iPaymentPlanId=r.iPaymentPlanId LEFT JOIN register_user as u on u.iUserId=r.iUserId LEFT JOIN rent_items_category as rc on rc.iRentItemId = r.iItemCategoryId WHERE 1=1 AND r.eStatus = 'Approved' AND r.eStatus!='Deleted' {$eTypesql} {$ssql} {$trp_ssql}  {$ord}";
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {

            $filename = $script."_" . $timestamp_filename . ".xls";
            $flag = false;
            $result = $obj->MySQLSelect($sql) or die('Query Failed!');
            $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1',"Post Number");
            $sheet->setCellValue('B1', $langage_lbl_admin['LBL_USER']);
            $sheet->setCellValue('C1',  $langage_lbl_admin['LBL_RENT_LISTING_TYPE']);
            $sheet->setCellValue('D1', $langage_lbl_admin['LBL_CATEGORY_TXT']);
            $sheet->setCellValue('E1', $langage_lbl_admin['LBL_RENT_PAYMENT_PLAN']);
            $sheet->setCellValue('F1', $langage_lbl_admin['LBL_RENT_DATE_POSTED']);
            $sheet->setCellValue('G1', $langage_lbl_admin['LBL_RENT_APPROVED_AT']);
            $sheet->setCellValue('H1', $langage_lbl_admin['LBL_RENT_RENEWAL_DATE']);
            $i = 2;
            foreach ($result as $value) {
                $reqArr = array('vCatName','eListingTypeWeb');        
                $getRentItemPostData = $RENTITEM_OBJ->getRentItemPostFinal("Web", $value['iRentItemPostId'], "" , $default_lang,"","","",$reqArr);
               
                $categoryDataArray = explode("-", $getRentItemPostData['vCatName']);
                $value['iRentItemPostId']  = $getRentItemPostData['eListingTypeWeb'];
                $value['vTitleCat'] = (!empty(trim($categoryDataArray[1]))) ? $categoryDataArray[0]."(".$categoryDataArray[1].")" : $categoryDataArray[0];

                $date_format_data_array = array(
                    'langCode' => $default_lang,
                    'DateFormatForWeb' => 1
                );
                $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $value['dRentItemPostDate'] != "0000-00-00 00:00:00") ? converToTz($value['dRentItemPostDate'],$value['vTimeZone'],$serverTimeZone) : $value['dRentItemPostDate'];
                $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $get_utc_time = DateformatCls::getUTCDiff($value['vTimeZone'],$date_format_data_array['tdate']);
                $time_zone_difference_text = (!empty($get_utc_time)) ? " (UTC:".$get_utc_time.")" : "";
                $value['dRentItemPostDate'] = $get_date_format['tDisplayDateTime'].$time_zone_difference_text;//DateTime($val);

                $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $value['dApprovedDate'] != "0000-00-00 00:00:00") ? converToTz($value['dApprovedDate'],$value['vTimeZone'],$serverTimeZone) : $value['dApprovedDate'];
                $get_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
                $value['dApprovedDate'] = $get_date_format['tDisplayDateTime'];//DateTime($val);

                $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $value['dRenewDate'] != "0000-00-00 00:00:00") ? converToTz($value['dRenewDate'],$value['vTimeZone'],$serverTimeZone) : $value['dRenewDate'];
                $value['dRenewDate'] = $get_date_format['tDisplayDateTime'];//DateTime($val);

                $sheet->setCellValue('A' . $i, $value["vRentItemPostNo"]);
                $sheet->setCellValue('B' . $i, clearName($value["riderName"]));
                $sheet->setCellValue('C' . $i, $value["iRentItemPostId"]);
                $sheet->setCellValue('D' . $i, $value["vTitleCat"]);
                $sheet->setCellValue('E' . $i, $value["vPlanName"]);
                $sheet->setCellValue('F' . $i, $value["dRentItemPostDate"]);    
                $sheet->setCellValue('G' . $i, $value["dApprovedDate"]);  
                $sheet->setCellValue('H' . $i, $value["dRenewDate"]);         
                $i++;
            }
            // Auto-size columns

            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
                //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
                
            }
            
            $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    } 
}
if($section == "bsr_master_category")
{
    $eType = isset($_REQUEST['eType']) ? geteTypeForBSR($_REQUEST['eType']) : "RentItem";
    if (!empty($eType)) {
        $iMasterServiceCategoryId = get_value($master_service_category_tbl, 'iMasterServiceCategoryId', 'eType', $eType, '', 'true');
        $catid = base64_encode(base64_encode($iMasterServiceCategoryId));
        $iMasterServiceCategoryId = base64_decode(base64_decode($catid));
        $eMasterType = $eType;
    }
    $script = $eMasterType;
    $lang = $LANG_OBJ->FetchDefaultLangData("vCode");

    $iRentItemId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
    $parentId = isset($_REQUEST['parent_id']) ? $_REQUEST['parent_id'] : 0;
    $sub = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 0;
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $queryString = $parentId > 0 ? '?parentid=' . $parentId : '';
    $ssql = '';
    if ($keyword != '') {
        if ($eStatus != '') {
            $ssql .= " AND (vTitle LIKE '%" . clean($keyword) . "%') AND eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND (vTitle LIKE '%" . clean($keyword) . "%')";
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND eStatus = '" . clean($eStatus) . "'";
    }
    if (empty($eStatus)) {
        $ssql .= 'AND ( estatus = "Active" || estatus = "Inactive" )';
    }
    $ord = " ORDER BY iDisplayOrder ASC";
    if ($sortby == 1) {
        $d = " SUBSTRING_INDEX(SUBSTRING_INDEX(vTitle,'vTitle_EN\":\"',-1),'\"',1)";
        if ($order == 0) $ord = " ORDER BY $d ASC"; else

            $ord = " ORDER BY $d DESC";
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY eStatus ASC";
        } else {
            $ord = " ORDER BY eStatus DESC";
        }
    }
    if ($iMasterServiceCategoryId != "") {
        $ssql .= " AND iMasterServiceCategoryId = '" . $iMasterServiceCategoryId . "'";
    } else {
        $ssql .= " AND iMasterServiceCategoryId = '0'";
    }

    if ($parentId > 0) {
        $master_service_categories = $RENTITEM_OBJ->getRentItemSubCategory('admin', $parentId, $ssql, '', '', $lang, $ord);
        $getrentitem = $RENTITEM_OBJ->getrentitem('admin', $parentId);
    } else {
        $master_service_categories = $RENTITEM_OBJ->getRentItemMaster('admin', $ssql, '0', '0', $lang, $ord);
    }
    foreach ($master_service_categories as $key => $value) {
        $query = $RENTITEM_OBJ->getRentItemSubCategory('admin', $value['iRentItemId']);
        $master_service_categories[$key]['SubCategories'] = scount($query);
    }

    if ($type == 'XLS') { 
        if (!empty($master_service_categories) && scount($master_service_categories) > 0) {
            $filename = $script."_" . $timestamp_filename . ".xls";
            $SPREADSHEET_OBJ->setActiveSheetIndex(0);
            // Get the active sheet
            $sheet = $SPREADSHEET_OBJ->getActiveSheet();
            $sheet->setCellValue('A1', $langage_lbl_admin['LBL_TITLE_TXT_ADMIN']);
            $sheet->setCellValue('B1', "Display Order");
            $sheet->setCellValue('C1', $langage_lbl_admin['LBL_Status']);
            $i = 2;
            foreach ($master_service_categories as $value) {
                $sheet->setCellValue('A' . $i, $value["vTitle"]);
                $sheet->setCellValue('B' . $i, $value["iDisplayOrder"]);
                $sheet->setCellValue('C' . $i, $value["eStatus"]);           
                $i++;
            }
            // Auto-size columns
            foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
                $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            $SPREADSHEET_WRITER_OBJ->save('php://output');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
        }

    }
}
if ($section =="order_status") 
{
    $script = 'order_status';
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = 'ORDER BY iOrderStatusId DESC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = "ORDER BY vStatus ASC";
        } else {
            $ord = "ORDER BY vStatus DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = "ORDER BY vStatus ASC";
        } else {
            $ord = "ORDER BY vStatus DESC";
        }
    }
    $adm_ssql = "";
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'vStatus') !== false) {
                $ssql .= "AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'"; // changed by me
            } else if (strpos($option, 'vStatus_Track') !== false) {
                $ssql .= "AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND vStatus LIKE '%" . $keyword . "%'";
        }
    }

    if(strtoupper(ONLY_MEDICAL_SERVICE) == "YES"){
        $ssql .= " AND eBuyAnyService = 'No' ";
    }
    $sql = "SELECT * FROM order_status where 1=1 $ssql $ord";
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Status Title");
        $sheet->setCellValue('B1', "Status Description");
        $i = 2;
        foreach ($result as $value) {
            if ($value['eBuyAnyService'] == "Yes") {
                $value["vStatus_".$default_lang] .= " (Genie Order)";
            }  
            if ($value[$i]['eTakeaway'] == "Yes") {
                $value["vStatus_".$default_lang]  .= " (Takeaway Order)";
            }
            $sheet->setCellValue('A' . $i, $value["vStatus_".$default_lang]);
            $sheet->setCellValue('B' . $i, clearName($value["vStatus_Track"]));     
            $i++;
        }
        // Auto-size columns

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
}
if($section == "rating_feedback_ques")
{
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $script = "RatingFeedbackQuestions";
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY iDisplayOrder';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY tQuestion ASC";
        else
            $ord = " ORDER BY tQuestion DESC";
    }

    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY iDisplayOrder ASC";
        else
            $ord = " ORDER BY iDisplayOrder DESC";
    }

    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY eStatus ASC";
        else
            $ord = " ORDER BY eStatus DESC";
    }
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            if ($option == "eStatus") {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql .= " AND (tQuestion LIKE '%" . $keyword . "%' OR eStatus LIKE '%" . $keyword . "%')";
        }
    }
    $sql = "SELECT JSON_UNQUOTE(JSON_VALUE(tQuestion, '$.tQuestion_".$default_lang."')) as tQuestion, iDisplayOrder,eStatus FROM rating_feedback_questions WHERE eStatus != 'Deleted' $ssql $ord";
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Feedback Questions");
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_ORDER']);
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_Status']);
        $i = 2;
        foreach ($result as $value) {
            $sheet->setCellValue('A' . $i, $value["tQuestion"]);
            $sheet->setCellValue('B' . $i, $value["iDisplayOrder"]);
            $sheet->setCellValue('C' . $i, $value["eStatus"]);    
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
}
if($section == "near_by_places")
{
    $script = 'nearbyPlaces';
    $lang = $LANG_OBJ->FetchDefaultLangData("vCode");
    $iNearByPlacesId = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : "";
    $sub = isset($_REQUEST['sub']) ? $_REQUEST['sub'] : 0;
    $iNearByCategoryId = isset($_REQUEST['iNearByCategoryId']) ? $_REQUEST['iNearByCategoryId'] : "";
    $ssql = '';
    if ($keyword != '') {
        if ($eStatus != '') {
            $ssql .= " AND (np.vTitle LIKE '%" . clean($keyword) . "%') AND np.eStatus = '" . clean($eStatus) . "'";
        } else {
            $ssql .= " AND (np.vTitle LIKE '%" . clean($keyword) . "%')";
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND np.eStatus = '" . clean($eStatus) . "'";
    }
    if (isset($iNearByCategoryId) && !empty($iNearByCategoryId)) {
        $ssql .= " AND np.iNearByCategoryId = '" . clean($iNearByCategoryId) . "'";
    }
    if(empty($eStatus)){
         $ssql .= 'AND ( np.estatus = "Active" || np.estatus = "Inactive" )';
    }
    $ord = "ORDER BY np.iNearByPlacesId DESC";
    $NearByPlaces = $NEARBY_OBJ->getNearByPlaces('admin', $ssql, 0, 0 , $lang, $ord);
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Place Name");
        $sheet->setCellValue('B1', "Place Category");
        $sheet->setCellValue('C1', "Address");
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_Status']);

        $i = 2;
        foreach ($NearByPlaces as $value) {
            $categoryStatus = '';
            if($value['categoryStatus'] == "Inactive") {
                $categoryStatus = ' (Inactive)';
            }
            $sheet->setCellValue('A' . $i, $value["vTitle"]);
            $sheet->setCellValue('B' . $i, clearEmail($value["categoryName"]).$categoryStatus);
            $sheet->setCellValue('C' . $i, $value["vAddress"]);
            $sheet->setCellValue('D' . $i, $value["eStatus"]);  
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    }
}
if($section == "ride_share_reviews")
{
    $script = 'ride-share-review';
    $FromUserType = $reviewtype = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Passenger';
    $ord = ' ORDER BY iRideShareRatingId DESC';
    $keyword = (isset($_REQUEST['keyword']) && $_REQUEST['keyword'] != '') ? $_REQUEST['keyword'] : '';
    $searchRider = isset($_REQUEST['searchRider']) ? $_REQUEST['searchRider'] : '';
    $searchDriver = isset($_REQUEST['searchDriver']) ? $_REQUEST['searchDriver'] : '';
    $isRideShare = isset($_REQUEST['rideShare']) ? $_REQUEST['rideShare'] : '';
    $ssql = '';
    if ($keyword != '') {
        $ssql .= " AND (pr.vPublishedRideNo LIKE '%" . clean($keyword) . "%' OR rsb.vBookingNo LIKE '%" . clean($keyword) . "%') ";
    }

    if ($searchRider != '') {
        $ssql .= " AND rsb.iUserId = {$searchRider} ";
    }
    if ($searchDriver != '') {
        $ssql .= " AND pr.iUserId = {$searchDriver} ";
    }
    $sql = "SELECT rsb.vBookingNo,pr.vPublishedRideNo,CONCAT(ru.vName,' ',ru.vLastName) AS UserName,CONCAT(rd.vName,' ',rd.vLastName) AS DriverName,rsr.fRating,rsr.tMessage,rsr.tDate,rd.vTimeZone FROM `ride_share_ratings` as rsr LEFT JOIN published_rides as pr ON pr.iPublishedRideId=rsr.iPublishedRideId LEFT JOIN ride_share_bookings as rsb ON rsb.iBookingId=rsr.iBookingId LEFT JOIN register_user as rd ON rd.iUserId=pr.iUserId LEFT JOIN register_user as ru ON ru.iUserId=rsb.iUserId WHERE rsr.eFromUserType =  '" . $FromUserType . "' $ssql";
    $serverTimeZone = date_default_timezone_get();
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Booking No");
        $sheet->setCellValue('B1', "Publish No");
        $sheet->setCellValue('C1', "User Name (Booked By)");
        $sheet->setCellValue('D1', "Rating By (User Name - Published By)");
        $sheet->setCellValue('E1', "Rating");
        $sheet->setCellValue('F1', "Comment");
        $sheet->setCellValue('G1', "Date");
        $i = 2;
        foreach ($result as $value) {
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone']) && $value["tDate"] != "0000-00-00 00:00:00") ? converToTz($value["tDate"],$value['vTimeZone'],$serverTimeZone) : $value["tDate"];
            $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value["tDate"] = $get_tDate_format['tDisplayDate'];//DateTime($val);
            $sheet->setCellValue('A' . $i, $value["vBookingNo"]);
            $sheet->setCellValue('B' . $i, $value["vPublishedRideNo"]);
            $sheet->setCellValue('C' . $i, $value["UserName"]);
            $sheet->setCellValue('D' . $i, $value["DriverName"]);  
            $sheet->setCellValue('E' . $i, $value["fRating"]);  
            $sheet->setCellValue('F' . $i, $value["tMessage"]);  
            $sheet->setCellValue('G' . $i, $value["tDate"]);  
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // foreach ($result[0] as $key => $val) {
        //     if($key == "vTimeZone")
        //     {
        //         continue;
        //     }
        //     if($key == "vBookingNo")
        //     {
        //         $key = "Booking No.";
        //     }
        //     if($key == "vPublishedRideNo")
        //     {
        //         $key = "Publish No.";
        //     }
        //     if($key == "UserName")
        //     {
        //         $key = "User Name (Booked By)";
        //     }
        //     if($key == "DriverName")
        //     {
        //         $key = "Rating By (User Name - Published By)";
        //     }
        //     if($key == "fRating")
        //     {
        //         $key = "Rating";
        //     }
        //     if($key == "tMessage")
        //     {
        //         $key = "Comment";
        //     }
        //     if($key == "tDate")
        //     {
        //         $key = "Date";
        //     }
        //     echo $key . "\t";
            
        // }
        // echo "\r\n";
        // foreach ($result as $value) {
        //     foreach ($value as $key => $val) {
        //         if($key == "vTimeZone")
        //         {
        //             continue;
        //         }
        //         if($key == "UserName" || $key == "DriverName" || $key == "tMessage")
        //         {
        //            $val = clearName($val);
        //         }
        //         if($key == "tDate")
        //         {
        //             $date_format_data_array = array(
        //                 'langCode' => $default_lang,
        //                 'DateFormatForWeb' => 1
        //             );
        //             $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($val,$value['vTimeZone'],$serverTimeZone) : $val;
        //             $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        //             $val = $get_tDate_format['tDisplayDate'];//DateTime($val);
        //         }
        //         echo $val. "\t"; 
        //     }
        //     echo "\r\n";
        // }
    }
}
if($section == "bidding_review") 
{
    $script = 'BidReviews';
    $type = (isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] != '') ? $_REQUEST['reviewtype'] : 'Driver';
    $reviewtype = $type;
    $exportType = (isset($_REQUEST['exportType'])) ? $_REQUEST['exportType'] : ''; 
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY iRatingId DESC';
    if ($sortby == 1) {
        if ($order == 0) $ord = " ORDER BY p.vBiddingPostNo ASC";
        else
            $ord = " ORDER BY p.vBiddingPostNo DESC";
    }
    if ($sortby == 2) {
        if ($reviewtype == 'Driver') {
            if ($order == 0) $ord = " ORDER BY rd.vName ASC";
            else
                $ord = " ORDER BY rd.vName DESC";
        }
        else {
            if ($order == 0) $ord = " ORDER BY ru.vName ASC";
            else
                $ord = " ORDER BY ru.vName DESC";
        }
    }
    if ($sortby == 6) {
        if ($reviewtype == 'Driver') {
            if ($order == 0) $ord = " ORDER BY ru.vName ASC";
            else
                $ord = " ORDER BY ru.vName DESC";
        }
        else {
            if ($order == 0) $ord = " ORDER BY rd.vName ASC";
            else
                $ord = " ORDER BY rd.vName DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) $ord = " ORDER BY r.fRating ASC";
        else
            $ord = " ORDER BY r.fRating DESC";
    }
    if ($sortby == 4) {
        if ($order == 0) $ord = " ORDER BY r.tDate ASC";
        else
            $ord = " ORDER BY r.tDate DESC";
    }
    if ($sortby == 5) {
        if ($order == 0) $ord = " ORDER BY r.tMessage ASC";
        else
            $ord = " ORDER BY r.tMessage DESC";
    }
    $adm_ssql = "";
    if (SITE_TYPE == 'Demo') {
        $adm_ssql = " And ru.tRegistrationDate > '" . WEEK_DATE . "'";
    }
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $ssql = '';
    if ($keyword != '') {
        if ($option != '') {
            $option_new = $option;
            if ($option == 'drivername') {
                $option_new = "CONCAT(rd.vName,' ',rd.vLastName)";
            }
            if ($option == 'ridername') {
                $option_new = "CONCAT(ru.vName,' ',ru.vLastName)";
            }
            if ($eStatus != "") {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%' AND r.eStatus = '" . clean($eStatus) . "'";
            }
            else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword) . "%'";
            }
        }
        else {
            $ssql .= " AND (p.vBiddingPostNo LIKE '%" . clean($keyword) . "%' OR  concat(rd.vName,' ',rd.vLastName) LIKE '%" . clean($keyword) . "%' OR concat(ru.vName,' ',ru.vLastName) LIKE '%" . clean($keyword) . "%' OR r.fRating LIKE '%" . clean($keyword) . "%' )";
        }
    }
    else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND r.eStatus = '" . clean($eStatus) . "'";
    }
    if ($eStatus != '') {
        $estatusquery = "";
    }
    else {
        $estatusquery = " AND r.eStatus != 'Deleted'";
    }
    $chkusertype = "";
    if ($type == "Driver") {
        $chkusertype = "Passenger";
    }
    else {
        $chkusertype = "Driver";
    }
    $sql = "SELECT p.vBiddingPostNo,CONCAT(rd.vName,' ',rd.vLastName) as driverName,CONCAT(ru.vName,' ',ru.vLastName) as passangerName,r.fRating,r.tDate,r.tMessage,ru.vTimeZone FROM bidding_service_ratings as r LEFT JOIN bidding_post as p ON p.iBiddingPostId=r.iBiddingPostId LEFT JOIN register_driver as rd ON rd.iDriverId=p.iDriverId LEFT JOIN register_user as ru ON ru.iUserId=p.iUserId WHERE 1=1 AND r.eUserType='" . $chkusertype . "' And ru.eStatus!='Deleted'  AND r.fRating != '' $estatusquery $ssql $adm_ssql $ord";
    $serverTimeZone = date_default_timezone_get();
    if ($exportType == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', "Bidding Number");
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_DRIVER_NAME_EXPORT']);
        $sheet->setCellValue('C1', $langage_lbl_admin['LBL_USER_NAME_LBL_TXT']);
        $sheet->setCellValue('D1', $langage_lbl_admin['LBL_RATE']);
        $sheet->setCellValue('E1', $langage_lbl_admin['LBL_DATE_TXT']);
        $sheet->setCellValue('F1', $langage_lbl_admin['LBL_COMMENT_TXT']);
        $i = 2;
        foreach ($result as $value) {
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($value['tDate'],$value['vTimeZone'],$serverTimeZone) : $value['tDate'];
            $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['tDate'] = $get_tDate_format['tDisplayDate'];//DateTime($val);
            $sheet->setCellValue('A' . $i, $value["vBiddingPostNo"]);
            $sheet->setCellValue('B' . $i, clearName($value["driverName"]));
            $sheet->setCellValue('C' . $i, $value["passangerName"]);
            $sheet->setCellValue('D' . $i, $value["fRating"]);  
            $sheet->setCellValue('E' . $i, $value["tDate"]);  
            $sheet->setCellValue('F' . $i, $value["tMessage"]);  
            $i++;
        }
        // Auto-size columns
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        // header("Content-Disposition: attachment; filename=\"$filename\"");
        // header("Content-Type: application/vnd.ms-excel");
        // $result = $obj->MySQLSelect($sql) or die('Query Failed!');
       
        // foreach ($result[0] as $key => $val) {
        //     if($key == "vTimeZone")
        //     {
        //         continue;
        //     }
        //     if($key == "vBiddingPostNo")
        //     {
        //         $key = "Bidding Number";
        //     }
        //     if($key == "driverName")
        //     {
        //         $key = "Service Provider Name";
        //     }
        //     if($key == "passangerName")
        //     {
        //         $key = "User Name";
        //     }
        //     if($key == "fRating")
        //     {
        //         $key = "Rate";
        //     }
        //     if($key == "tDate")
        //     {
        //         $key = "Date";
        //     }
        //     if($key == "tMessage")
        //     {
        //         $key = "Comment";
        //     }
        //     echo $key . "\t";
            
        // }
        // echo "\r\n";
        // foreach ($result as $value) {
        //     foreach ($value as $key => $val) {
        //         if($key == "vTimeZone")
        //         {
        //             continue;
        //         }
        //         if($key == "driverName" || $key == "passangerName" || $key == "tMessage")
        //         {
        //            $val = clearName($val);
        //         }
        //         if($key == "tDate")
        //         {
        //             $date_format_data_array = array(
        //                 'langCode' => $default_lang,
        //                 'DateFormatForWeb' => 1
        //             );
        //             $date_format_data_array['tdate'] = (!empty($value['vTimeZone'])) ? converToTz($val,$value['vTimeZone'],$serverTimeZone) : $val;
        //             $get_tDate_format = DateformatCls::getNewDateFormat($date_format_data_array);
        //             $val = $get_tDate_format['tDisplayDate'];//DateTime($val);
        //         }
        //         echo $val. "\t"; 
        //     }
        //     echo "\r\n";
        // }
    }
}
if($section == "org_outstanding_amount")
{
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ssql = '';
    $ssqlsearchSettle = '';
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $searchOrganization = isset($_REQUEST['searchOrganization']) ? $_REQUEST['searchOrganization'] : '';
    $eType = isset($_REQUEST['eType']) ? $_REQUEST['eType'] : '';
    $searchSettleUnsettle = isset($_REQUEST['searchSettleUnsettle']) ? $_REQUEST['searchSettleUnsettle'] : '1';
    $searchPaidby = 'org';
    $searchSettleUnsettlePagination = $searchSettleUnsettle;
    $ssql = '';
    if($searchPaidby=='org') {
        if($searchSettleUnsettle == '1'){
            //$ssql1 = " AND toa1.ePaidByOrganization ='No' ";
            $ssql1 = "AND (toa1.eBillGenerated ='No' AND toa1.ePaidByOrganization ='No')";
        } else if($searchSettleUnsettle == '0'){
            //$ssql1 = " AND toa1.ePaidByOrganization ='Yes' ";
            $ssql1 = "AND (toa1.eBillGenerated ='Yes' OR toa1.ePaidByOrganization ='Yes')";
        } else if($searchSettleUnsettle == '-1'){
            //$ssql1 = " AND toa1.ePaidByOrganization ='Yes' ";
            $ssql1 = "AND (toa1.eBillGenerated ='Yes' OR toa1.ePaidByOrganization ='Yes') ";
        }
    } else {
        if($searchSettleUnsettle == '1'){
            $ssql1 = "AND toa1.ePaidByPassenger ='No' ";
        } else if($searchSettleUnsettle == '0'){
            $ssql1 = "AND toa1.ePaidByPassenger ='Yes' ";
        } else if($searchSettleUnsettle == '-1'){
            $ssql1 = "AND toa1.ePaidByPassenger ='Yes' ";
        } 
    }
    if ($searchOrganization != '') {
        $ssql .= "AND toa.iOrganizationId ='" . $searchOrganization . "'";
    }
    $sqlPaidby = $sqlPaidbysub = '';
    if($searchPaidby=='org') {
        $sqlPaidbysub = "AND toa1.vTripPaymentMode = 'Organization'";
        $sqlPaidby = "AND toa.vTripPaymentMode = 'Organization'";
    } else {
        $sqlPaidbysub = " AND toa1.vTripPaymentMode != 'Organization'";
        $sqlPaidby = "AND toa.vTripPaymentMode != 'Organization'";
    }
    $trp_ssql = "ORDER BY org.vCompany ASC";
    if($searchSettleUnsettle == '1'){
        $sql = "SELECT CONCAT('(+',org.vCode,') ',org.vPhone) AS Mobile,SUM(toa.fPendingAmount) as allSum,(SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '') as Remaining from trip_outstanding_amount AS toa LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId HAVING remaining>0 $trp_ssql";
        $sqlAll=$sql;
    }else if ($searchSettleUnsettle == '0'){
        $sql = "SELECT CONCAT(org.vCode,' ',org.vPhone) AS Mobile,SUM(toa.fPendingAmount) AS allSum,(SUM(toa.fPendingAmount)-(SELECT SUM(toa1.fPendingAmount) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != ''))as Remaining from trip_outstanding_amount AS toa LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId HAVING allSum=PaidData $trp_ssql";
        $sqlAll=$sql;
    }else{
        $sql = "SELECT CONCAT('(+',org.vCode,') ',org.vPhone) AS Mobile,SUM(toa.fPendingAmount) AS allSum,(SUM(toa.fPendingAmount)-(SELECT (CASE WHEN ISNULL(SUM(toa1.fPendingAmount)) THEN 0 ELSE SUM(toa1.fPendingAmount) END) FROM trip_outstanding_amount as toa1 WHERE toa1.iOrganizationId=toa.iOrganizationId $sqlPaidbysub $ssql1 AND toa1.iOrganizationId != '')) as Remaining from trip_outstanding_amount AS toa LEFT JOIN trips AS tr ON tr.iTripId = toa.iTripId LEFT JOIN organization org ON org.iOrganizationId = toa.iOrganizationId WHERE toa.iOrganizationId > 0 AND toa.iOrganizationId != '' $sqlPaidby $ssql GROUP BY toa.iOrganizationId $trp_ssql";
        $sqlAll=$sql;
    }
    $sqlAll = $sqlAll; 
    if ($type == 'XLS') {
        $filename = $script."_" . $timestamp_filename . ".xls";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        foreach ($result[0] as $key => $val) {
            if(in_array($key,array('Mobile','Remaining')))
            {
                if($key == "Mobile")
                {
                    $key = "Organization Contact Details";
                }
                if($key == "Remaining")
                {
                    $key = "Outstanding Amount";
                }
                echo $key . "\t";
            }
            
        }
        echo "\r\n";
        foreach ($result as $value) {
            foreach ($value as $key => $val) {
                if(in_array($key,array('Mobile','Remaining')))
                {
                    if($key == "Remaining")
                    {                   
                        $remainingPendingAmount = $value['Remaining'];
                        $val = formateNumAsPerCurrency($remainingPendingAmount,""); 
                    }
                    if($key == "Mobile")
                    {
                         $val = clearPhone($val);
                    }
                    echo $val. "\t"; 
                }
            }
            echo "\r\n";
        }
    }
}
if($section == "expired_documents")
{
    $default_lang = $LANG_OBJ->FetchSystemDefaultLang();
    $script = 'Expired_Documents';  
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $ord = ' ORDER BY dm.doc_usertype ASC';
    if ($sortby == 1) {
        if ($order == 0)
            $ord = " ORDER BY dm.doc_usertype ASC";
        else
            $ord = " ORDER BY dm.doc_usertype DESC";
    }
    if ($sortby == 2) {
        if ($order == 0)
            $ord = " ORDER BY dm.country ASC";
        else
            $ord = " ORDER BY dm.country DESC";
    }
    if ($sortby == 3) {
        if ($order == 0)
            $ord = " ORDER BY dm.doc_name_" . $default_lang . " ASC";
        else
            $ord = " ORDER BY dm.doc_name_" . $default_lang . " DESC";
    }
    if ($sortby == 4) {
        if ($order == 0)
            $ord = " ORDER BY dl.ex_date ASC";
        else
            $ord = " ORDER BY dl.ex_date DESC";
    }

    if ($sortby == 5) {
        if ($order == 0)
            $ord = " ORDER BY doc_username ASC";
        else
            $ord = " ORDER BY doc_username DESC";
    }

    if ($sortby == 6) {
        if ($order == 0)
            $ord = " ORDER BY doc_useremail ASC";
        else
            $ord = " ORDER BY doc_useremail DESC";
    }
    if ($sortby == 7) {
        if ($order == 0)
            $ord = " ORDER BY doc_userphone ASC";
        else
            $ord = " ORDER BY doc_userphone DESC";
    }
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $ssql = '';
    $ssql1 = '';
    if ($keyword != '') {
        if ($option != '') {
            if (strpos($option, 'ex_date') !== false) {
                $ssql .= " AND " . stripslashes($option) . " LIKE '" . stripslashes($keyword) . "'";
            }else if (strpos($option, 'doc_username') !== false) {
                $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }else if (strpos($option, 'doc_useremail') !== false) {
                $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }else if (strpos($option, 'doc_userphone') !== false) {
                $ssql1 .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            } else {
                $ssql .= " AND " . stripslashes($option) . " LIKE '%" . stripslashes($keyword) . "%'";
            }
        } else {
            $ssql1 .= " AND (doc_username LIKE '%" . $keyword . "%' OR doc_useremail LIKE '%" . $keyword . "%' OR doc_userphone LIKE '%" . $keyword . "%' OR doc_usertype LIKE '%" . $keyword . "%' OR country LIKE '%" . $keyword . "%' OR doc_name_" . $default_lang . " LIKE '%" . $keyword . "%' OR ex_date LIKE '" . $keyword . "')";
        }
    }
    $sql = "SELECT * from (SELECT dl.doc_masterid,dl.doc_userid,dm.doc_name_". $default_lang . ",dm.country,dm.doc_usertype,dl.ex_date,dl.req_date,CASE dm.doc_usertype
    WHEN 'company' THEN (select vCompany from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select CONCAT(vName,' ', vLastName) from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select CONCAT(mk.vMake,' ',model.vTitle)  from driver_vehicle dv INNER JOIN make mk ON dv.iMakeId = mk.iMakeId INNER JOIN model model ON dv.iModelId=model.iModelId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vCompany from company where iCompanyId =dl.doc_userid)
    END AS doc_username,
    CASE dm.doc_usertype
    WHEN 'company' THEN ''
    WHEN 'driver' THEN ''
    WHEN 'car' THEN (select mk.vMake from driver_vehicle dv INNER JOIN make mk ON dv.iMakeId = mk.iMakeId INNER JOIN model model ON dv.iModelId=model.iModelId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN ''
    END AS vehicle,
    CASE dm.doc_usertype
    WHEN 'company' THEN (select vEmail from company where iCompanyId = dl.doc_userid)
    WHEN 'driver' THEN (select vEmail from register_driver where iDriverId = dl.doc_userid)
    WHEN 'car' THEN (select vEmail  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = dl.doc_userid)
    WHEN 'store' THEN (select vEmail from company where iCompanyId =dl.doc_userid)
    END AS doc_useremail,
     CASE dm.doc_usertype

    WHEN 'company' THEN (select vPhone from company where iCompanyId = dl.doc_userid)

    WHEN 'driver' THEN (select vPhone from register_driver where iDriverId = dl.doc_userid)

    WHEN 'car' THEN (select vPhone  from driver_vehicle dv INNER JOIN register_driver rd ON dv.iDriverId = rd.iDriverId where dv.iDriverVehicleId = dl.doc_userid)

    WHEN 'store' THEN (select vPhone from company where iCompanyId =dl.doc_userid)

    END AS doc_userphone

    FROM document_list dl INNER JOIN document_master dm ON dl.doc_masterid = dm.doc_masterid  where dm.ex_status = 'yes' AND dl.ex_date!='0000-00-00' AND dl.ex_date < CURDATE() AND dm.status !='Deleted'  $ssql $ord) AS documentlist WHERE doc_username IS NOT NULL $ssql1";
    if ($type == 'XLS') {
        $flag = false;
        $result = $obj->MySQLSelect($sql) or die('Query failed!');
        $filename =$script."_".$timestamp_filename.'.xls';
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', 'Document For');
        $sheet->setCellValue('B1', 'Country');
        $sheet->setCellValue('C1', 'Document Name');
        $sheet->setCellValue('D1', 'Expire Date');
        $sheet->setCellValue('E1', 'Document User Name');
        $sheet->setCellValue('F1', 'Email');
        $sheet->setCellValue('G1', 'Phone');
        
        $i = 2;
        
        //echo "\r\n";
        foreach ($result as $value) {
           // $value = array_replace(array_flip($KeyOrder), $value);           
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $value['ex_date'];
            $get_ex_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $value['ex_date'] = $get_ex_date_format['tDisplayDate'];

            

            $document_for = '';
            if ($value['doc_usertype'] == 'company') {
                $document_for ='Company';
            }else if($value['doc_usertype'] == 'driver'){
                $document_for ='Provider';
            }else if($value['doc_usertype'] == 'car'){
                $document_for ='Car';
            }else if($value['doc_usertype'] == 'store'){
                $document_for ='Store';
            }

            $sheet->setCellValue('A' . $i, $document_for);
            $sheet->setCellValue('B' . $i, $value['country']); 
            $sheet->setCellValue('C' . $i, $value['doc_name_'.$default_lang]);
            $sheet->setCellValue('D' . $i, $value['ex_date']); 
            $sheet->setCellValue('E' . $i, $value['doc_username']);
            $sheet->setCellValue('F' . $i, (!empty($value['doc_useremail'])) ? $value['doc_useremail'] : "-");   
            $sheet->setCellValueExplicit('G' . $i,  clearPhone(" " . $value['doc_userphone']),\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);     
            $i++;  
        }

        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
    
    }
}
if($section == "hotels")
{
    $sortby = isset($_REQUEST['sortby']) ? $_REQUEST['sortby'] : 0;
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : '';
    $action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
    $ord = ' ORDER BY a.iAdminId ASC';
    if ($sortby == 1) {
        if ($order == 0) {
            $ord = " ORDER BY vFirstName ASC";
        } else {
            $ord = " ORDER BY vFirstName DESC";
        }
    }
    if ($sortby == 2) {
        if ($order == 0) {
            $ord = " ORDER BY vEmail ASC";
        } else {
            $ord = " ORDER BY vEmail DESC";
        }
    }
    if ($sortby == 3) {
        if ($order == 0) {
            $ord = " ORDER BY a.iGroupId ASC";
        } else {
            $ord = " ORDER BY a.iGroupId DESC";
        }
    }
    if ($sortby == 4) {
        if ($order == 0) {
            $ord = " ORDER BY a.eStatus ASC";
        } else {
            $ord = " ORDER BY a.eStatus DESC";
        }
    }
    if ($sortby == 6) {
        if ($order == 0) {
            $ord = " ORDER BY h.tRegistrationDate ASC";
        } else {
            $ord = " ORDER BY h.tRegistrationDate DESC";
        }
    }
    $hotelPanel = ($MODULES_OBJ->isEnableHotelPanel('Yes')) ? "Yes" : "No";
    $kioskPanel = ($MODULES_OBJ->isEnableKioskPanel('Yes')) ? "Yes" : "No";
    $ssql = '';
    if (ONLYDELIVERALL == 'Yes' || $THEME_OBJ->isRideCXThemeActive() == 'Yes' || $THEME_OBJ->isRideDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXThemeActive() == 'Yes' || $THEME_OBJ->isDeliveryXv2ThemeActive() == 'Yes' || $THEME_OBJ->isServiceXThemeActive() == 'Yes' || $THEME_OBJ->isServiceXv2ThemeActive() == 'Yes' || $THEME_OBJ->isRideCXv2ThemeActive() == 'Yes' || $hotelPanel == 'No') {
        $ssql .= " AND a.iGroupId != 4";
    }
    $ssql .= " AND a.iGroupId = 4";
    $role_sql = "select * from admin_groups a where eStatus = 'Active'" . $ssql;
    $role_sql_data = $obj->MySQLSelect($role_sql);
    $option = isset($_REQUEST['option']) ? stripslashes($_REQUEST['option']) : "";
    $keyword = isset($_REQUEST['keyword']) ? stripslashes($_REQUEST['keyword']) : "";
    $searchDate = isset($_REQUEST['searchDate']) ? $_REQUEST['searchDate'] : "";
    $eStatus = isset($_REQUEST['eStatus']) ? $_REQUEST['eStatus'] : "";
    $eRole = isset($_REQUEST['eRole']) ? stripslashes($_REQUEST['eRole']) : "";
    if ($keyword != '') {
        $keyword_new = $keyword;
        $chracters = array(
            "(",
            "+",
            ")"
        );
        $removespacekeyword = preg_replace('/\s+/', '', $keyword);
        $keyword_new = trim(str_replace($chracters, "", $removespacekeyword));
        if (is_numeric($keyword_new)) {
            $keyword_new = $keyword_new;
        } else {
            $keyword_new = $keyword;
        }
        if ($option != '') {
            $option_new = $option;
            if ($option == 'Name') {
                $option_new = "CONCAT(vFirstName,' ',vLastName)";
            }
            if ($option == 'vEmail') {
                $option_new = "vEmail";
            }
            if ($option == 'vGroup') {
                $option_new = "vGroup";
            }
            if ($option == 'vContactNo') {
                $option_new = "CONCAT(vCode,' ',vContactNo)";
            }
            if ($eStatus != '') {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%' AND a.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "' AND a.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND " . stripslashes($option_new) . " LIKE '%" . clean($keyword_new) . "%'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND " . stripslashes($option_new) . " = '" . clean($keyword_new) . "'";
                }
            }
        } else {
            if ($eStatus != '') {
                $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%') AND a.eStatus = '" . clean($eStatus) . "'";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%') AND a.eStatus = '" . clean($eStatus) . "'";
                }
            } else {
                $ssql .= " AND (concat(vFirstName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%')";
                if (SITE_TYPE == 'Demo') {
                    $ssql .= " AND (concat(vName,' ',vLastName) LIKE '%" . clean($keyword_new) . "%' OR vEmail LIKE '%" . clean($keyword_new) . "%' OR vContactNo LIKE '%" . clean($keyword_new) . "%')";
                }
            }
        }
    } else if ($eStatus != '' && $keyword == '') {
        $ssql .= " AND a.eStatus = '" . clean($eStatus) . "'";
    }
     
    if ($eRole != '' ) {
        $ssql .= " AND a.iGroupId = '" . clean($eRole) . "'";
    }
    if (!empty($eStatus)) {
        $eQuery = "";
    } else {
        $eQuery = " AND a.eStatus != 'Deleted'";
    }
    $sql = "SELECT CONCAT(a.vFirstName,' ',a.vLastName) as name,a.vEmail,CONCAT(vCode,' ',vContactNo) as mobile,vCode , vContactNo , tRegistrationDate,a.eStatus FROM administrators a LEFT JOIN admin_groups ag ON a.iGroupId = ag.iGroupId LEFT JOIN hotel h ON a.iAdminId = h.iAdminId  WHERE ag.eStatus='Active' $eQuery $ssql $ssql1 $dri_ssql $ord";
	
    if ($type == 'XLS') {
        $filename = "Hotel_" . $timestamp_filename . ".xls";
        
        $result = $obj->MySQLSelect($sql) or die('Query Failed!');
        $serverTimeZone = date_default_timezone_get();
        $SPREADSHEET_OBJ->setActiveSheetIndex(0);
        // Get the active sheet
        $sheet = $SPREADSHEET_OBJ->getActiveSheet();
        $sheet->setCellValue('A1', $langage_lbl_admin['LBL_NAME_TXT']);
        $sheet->setCellValue('B1', $langage_lbl_admin['LBL_EMAIL']);
        $sheet->setCellValue('C1', "Mobile No");
        $sheet->setCellValue('D1',"Registration Date");
        $sheet->setCellValue('E1',  $langage_lbl_admin['LBL_Status']);
        $i = 2; 
        foreach ($result as $value) { 
		
            $date_format_data_array = array(
                'langCode' => $default_lang,
                'DateFormatForWeb' => 1
            );
            $date_format_data_array['tdate'] = $value['tRegistrationDate'];
            $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
            $tRegistrationDate = $get_Signup_date_format['tDisplayDateTime'];

            $sheet->setCellValue('A' . $i, clearName($value["name"]));
            $sheet->setCellValue('B' . $i, clearEmail($value["vEmail"]));
			$sheet->setCellValue('c' . $i, (!empty($value["vContactNo"])) ? "(+". ($value["vCode"]).") ". clearPhone($value["vContactNo"]) : "");
            $sheet->setCellValue('D' . $i, $tRegistrationDate);
            $sheet->setCellValue('E' . $i, $value['eStatus']);
            $i++;
        }
        foreach (range('A', $sheet->getHighestDataColumn()) as $columnID) {
            $sheet->getStyle($columnID.'1')->applyFromArray($styleArrayForHeader);
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
            //$sheet->getStyle( $columnID.'1' )->getFont()->setBold( true );
            
        }
        
        $SPREADSHEET_WRITER_OBJ->save('php://output');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        // foreach ($result[0] as $key => $val) {
        //     if($key == "name")
        //     {
        //         $key = "Name";
        //     }
        //     if($key == "vEmail")
        //     {
        //         $key = "Email";
        //     }
        //     if($key == "mobile")
        //     {
        //         $key = "Mobile No.";
        //     }
        //     if($key == "tRegistrationDate")
        //     {
        //         $key = "Registration Date";
        //     }
        //     if($key == "eStatus")
        //     {
        //         $key = "Status";
        //     }
        //     echo $key . "\t";
        // }
        // echo "\r\n";
        // foreach ($result as $value) {
        //     foreach ($value as $key => $val) {
        //         if($key == "name")
        //         {
        //            $val = clearName($val); 
        //         }
        //         if($key == "vEmail")
        //         {
        //            $val = clearEmail($val); 
        //         }
        //         if($key == "mobile")
        //         {
        //            $val = clearPhone($val);
        //         }
        //         if($key == "tRegistrationDate")
        //         {
        //             $date_format_data_array = array(
        //                 'langCode' => $default_lang,
        //                 'DateFormatForWeb' => 1
        //             );
        //             $date_format_data_array['tdate'] = $val;
        //             $get_Signup_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
        //             $val = $get_Signup_date_format['tDisplayDateTime'];
        //           // $val = DateTime($val, 'No');
        //         }
        //         echo $val. "\t"; 
        //     }
        //     echo "\r\n";
        // }
    }
  
}
?>