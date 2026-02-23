<?php
/* ****************** NOTE FOR THIS SCRIPT START ****************** */
// When $ALL_DATABASE_MAIN this is not set then all dbs taken from bbcsproducts.net which starts with bbcsprod_
// When $ALL_TABLES_MAIN is set or not it will taken tables which starts with language_label and not language_label_other, but when this is not set then all tables chk in loop
// If $HOST_ARRAY set then it will chk the tables only which starts with webpro31_cubejekdev, if taken other host or other table then need to add 'OR' condition in code
// existence of lables which chk in all tables which starts with language_label only.in _other it will be consider also
/* ****************** NOTE FOR THIS SCRIPT END ****************** */

/* ****************** DATABASES From Main Server start ****************** */
define("TSITE_SERVER", "localhost");
define("TSITE_DB", "bbcsprod_development");
define("TSITE_USERNAME", "root");
define("TSITE_PASS", $_POST['development_db']);
/* ****************** DATABASES From Main Server end ****************** */

include_once('../common.php');


 
$script = 'language_label';
$tbl_update_name = "language_label";

$CURRENT_FILE_NAME = basename($_SERVER['SCRIPT_FILENAME']);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$selectedlanguage = isset($_REQUEST['selectedlanguage']) ? $_REQUEST['selectedlanguage'] : '1';
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$DeleteLabel = isset($_REQUEST['DeleteLabel']) ? $_REQUEST['DeleteLabel'] : 'No';
$vDeleteLabel = isset($_REQUEST['vDeleteLabel']) ? $_REQUEST['vDeleteLabel'] : 'No';
$action = ($id != '') ? 'Edit' : 'Add';

/* ****************** Host, Db, Tables Details which will be taken for proceed start ****************** */

//if want all dbs from bbcsproducts.net, then only remove following array>>$ALL_DATABASE_MAIN
//$ALL_DATABASE_MAIN = array('bbcsprod_development',',''','bbcsprod_cubex20','bbcsprod_cubex2020','bbcsprod_deliveryX','bbcsprod_delvX','bbcsprod_foodx','bbcsprod_groceryx','bbcsprod_pharmacyx','bbcsprod_ridedeliveryX','bbcsprod_ridex','bbcsprod_servicex');
//$ALL_DATABASE_MAIN = array('bbcsprod_development','bbcsprod_cubejek20');

//if want all tables then set $ALL_TABLES_MAIN_VAR this to 1..so it will not consider this $ALL_TABLES_MAIN
//$ALL_TABLES_MAIN = array('language_label','language_label_1','language_label_deliverall','language_label_delivery','language_label_food','language_label_ride','language_label_ride_delivery','language_label_uberx');
////$ALL_TABLES_MAIN = array('language_label','language_label_food');
//$ALL_TABLES_MAIN_VAR = 0;

//arrays set to host in which data is to be inserted, here entered host,db,user, pwd in sequence
$HOST_ARRAY = array(
    array("78.129.252.33","kingx_pro_production", "king_wild_dev", $_POST['project_setup_db']),
);

$total_table = 10;
$tbl_name_food = "language_label_1";
/* ****************** Host, Db, Tables Details which will be taken for proceed end ****************** */

$tbl_name = 'language_label';
//$total_table = 10;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';


// set all variables with either post (when submit) either blank (when insert)
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : $id;
$lPage_id = isset($_POST['lPage_id']) ? $_POST['lPage_id'] : '';
$eAppType = isset($_POST['eAppType']) ? $_POST['eAppType'] : '';

$vValue_food = isset($_POST['vValue_food']) ? $_POST['vValue_food'] : '';
$vValue_other = isset($_POST['vValue_other']) ? $_POST['vValue_other'] : '';


function checkTableExistsDatabaseLang($table_name, $db_name, $hostName = TSITE_SERVER, $to_db_name_item = TSITE_DB, $dbusername = TSITE_USERNAME, $dbPassword = TSITE_PASS) {
    global $obj;
    $TABLES_OF_DATABASE_THEME = array();
    if ($table_name != '') {
        //echo $table_name."=======".$db_name."*****";
        //if($db_name=="webpro31_cubejekdev_prod") {
        //    $obj = new DBConnection("webprojectsdemo.com", "webpro31_cubejekdev_prod", "systemuser", "IAA7mjyQuVXtFheY");
        //}
        $obj = new DBConnection($hostName, $to_db_name_item, $dbusername, $dbPassword);
        if (empty($TABLES_OF_DATABASE_THEME)) {
            $data = $obj->MySQLSelect("SHOW TABLES");
            foreach ($data as $data_tmp) {
                $TABLES_OF_DATABASE_THEME[] = $data_tmp['Tables_in_' . $db_name];
            }
        }
        if (in_array($table_name, $TABLES_OF_DATABASE_THEME)) {
            return true;
        }
    }
    return false;
}

function removeDuplicatesFromLngTable($tableName, $cus_obj) {
    global $obj;
    if (empty($cus_obj)) {
        $cus_obj = $obj;
    }
    $cus_obj->sql_query("DELETE FROM " . $tableName . " WHERE `vLabel`=''");
    $sql_find_duplicates = "SELECT vLabel, COUNT( * ) as totalCount FROM " . $tableName . " WHERE vCode =  'EN' GROUP BY vLabel, vCode HAVING COUNT( * ) >1 ORDER BY vLabel";
    $duplicatesData = $cus_obj->MysqlSelect($sql_find_duplicates);
    foreach ($duplicatesData as $duplicatesData_item) {
        $limitOfLabel = $duplicatesData_item['totalCount'] - 1;
        $cus_obj->sql_query("DELETE FROM " . $tableName . " WHERE `vLabel`='" . $duplicatesData_item['vLabel'] . "' AND `vCode` = 'EN' LIMIT " . $limitOfLabel);
    }
}
################### ADD LABLES START ###################
if (isset($_POST['submit']) && $action == "Add") {
    $oCache->flushData();

    /* ****************** Check LBL STARTS With 'LBL_' and all word capital START ****************** */
    if (startsWith($vLabel, "LBL_") == false) {
        $var_msg = "Lable must be start with 'LBL_'";
        header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
        exit;
    }

    if (!preg_match('/^[A-Z_]+$/', "LBL_TMP_CHK")) {
        $var_msg = "Only Capital Letters and Underscores are allowed.";
        header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
        exit;
    }

    /* if(preg_match('/\\d/', $vLabel) > 0){
      $var_msg = "Label must not contains digits.";
      header("Location:".$CURRENT_FILE_NAME."?var_msg=" . $var_msg . '&success=0');
      exit;
      } */
    
    /* ****************** Check LBL STARTS With 'LBL_' and all word capital END ****************** */

    /* ****************** DATABASES From Main Server - bbcsproducts.net START ****************** */

    if(empty($ALL_DATABASE_MAIN)) {
        $all_databse_data = $obj->MySQLSelect("SHOW DATABASES");

        $all_databse_data_arr = array();
        foreach ($all_databse_data as $all_databse_data_item) {
            if(startsWith($all_databse_data_item['Database'], "bbcsprod_development") || startsWith($all_databse_data_item['Database'], "prod24_")) {
                $all_databse_data_arr[] = $all_databse_data_item['Database'];
            }
        }
    } else {
        $all_databse_data_arr = $ALL_DATABASE_MAIN;
    }

    /* ****************** DATABASES From Main Server - bbcsproducts.net END ****************** */

    /* ****************** DATABASES From opposite Server START ****************** */
    
    $all_databse_data_opposite_arr = array();
    if(!empty($HOST_ARRAY)) {
        $position_count = 0;
        foreach($HOST_ARRAY as $key=>$value) {
            $OPPOSITE_HOST = $USER_OPPOSITE_HOST = $PASSWORD_OPPOSITE_HOST = "";
            $OPPOSITE_HOST = $value[0];
            $DB_OPPOSITE_HOST = $value[1];
            $USER_OPPOSITE_HOST = $value[2];
            $PASSWORD_OPPOSITE_HOST = $value[3];
            $obj_opposite = new DBConnection($OPPOSITE_HOST, $DB_OPPOSITE_HOST, $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
        
            if (!empty($obj_opposite)) {
                //$all_databse_data_opposite_arr = array();
                $all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
                //$position_count = 0;
                foreach ($all_databse_data as $all_databse_data_item) {
                    if(/*startsWith($all_databse_data_item['Database'], "webpro31_cubejekdev") ||*/ startsWith($all_databse_data_item['Database'], "webpro31_kingx") || startsWith($all_databse_data_item['Database'], "prod_") || startsWith($all_databse_data_item['Database'], "kingx_pro_production")) {
                        $all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
                        $all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
                        $all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
                        $all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
                        $position_count++;
                    }
                }
        
                $obj_opposite->MySQLClose();
            }
        }
    }
    
    /* ****************** DATABASES From opposite Server END ****************** */
    
    /* ****************** Removing Duplicates & Check For existence of lables START ****************** */
    foreach ($all_databse_data_arr as $all_databse_data_arr_item) {
        $obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);

        if (checkTableExistsDatabaseLang($tbl_update_name, $all_databse_data_arr_item)) {
        removeDuplicatesFromLngTable($tbl_update_name, $obj_current_connection);
        }
        
        //         * ****************** Check For existence of lables  *******************
        
        /*if (checkTableExistsDatabaseLang('language_label', $all_databse_data_arr_item,TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS)) {
        $sql = "SELECT * FROM `language_label` WHERE vLabel = '" . $vLabel . "'";
        $db_label_check = $obj_current_connection->MySQLSelect($sql);
        if (scount($db_label_check) > 0) {
            $var_msg = "Language label already exists in general label in ".$all_databse_data_arr_item;
            header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
            exit;
        }
        }*/

        if (checkTableExistsDatabaseLang('language_label_other', $all_databse_data_arr_item,TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS)) {
            $sql = "SELECT * FROM `language_label_other` WHERE vLabel = '" . $vLabel . "'";
            $db_label_check_ride = $obj_current_connection->MySQLSelect($sql);
            if (scount($db_label_check_ride) > 0) {
                $var_msg = "Language label already exists in ride label";
                header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
                exit;
            }
        }
        for($i=1;$i<=$total_table;$i++) {
            $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
            if($num_rows->num_rows==1) {
                $sql = "SELECT * FROM `language_label_".$i."` WHERE vLabel = '" . $vLabel . "'";
                $db_label_check = $obj_current_connection->MySQLSelect($sql);
                if (scount($db_label_check) > 0) {
                    $var_msg = "Language Label Already Exists In Food Label or Other Label";
                    header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
                    exit;
                }
            }
        }

        //         * ****************** Check For existence of lables  ******************* 

        $obj_current_connection->MySQLClose();
    }

    if (!empty($all_databse_data_opposite_arr) && scount($all_databse_data_opposite_arr) > 0) {
        for ($ik = 0; $ik < scount($all_databse_data_opposite_arr); $ik++) {
            $all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];
            $obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);

            removeDuplicatesFromLngTable($tbl_update_name, $obj_opposite_connection);

            /*             * ****************** Check For existence of lables  ******************* */
            $sql = "SELECT * FROM `language_label` WHERE vLabel = '" . $vLabel . "'";
            $db_label_check = $obj_opposite_connection->MySQLSelect($sql);
            /*if (scount($db_label_check) > 0) {
                $var_msg = "Language label already exists in general label";
                header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
                exit;
            }*/
    
            $sql = "SELECT * FROM `language_label_other` WHERE vLabel = '" . $vLabel . "'";
            $db_label_check_ride = $obj_opposite_connection->MySQLSelect($sql);
            if (scount($db_label_check_ride) > 0) {
                $var_msg = "Language label already exists in ride label";
                header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
                exit;
            }
    
            for($i=1;$i<=$total_table;$i++) {
                $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
                if($num_rows->num_rows==1) {
                    $sql = "SELECT * FROM `language_label_".$i."` WHERE vLabel = '" . $vLabel . "'";
                    $db_label_check = $obj_opposite_connection->MySQLSelect($sql);
                    if (scount($db_label_check) > 0) {
                        $var_msg = "Language Label Already Exists In Food Label or Other Label";
                        header("Location:" . $CURRENT_FILE_NAME . "?var_msg=" . $var_msg . '&success=0');
                        exit;
                    }
                }
            }

            /*             * ****************** Check For existence of lables  ******************* */

            $obj_opposite_connection->MySQLClose();
        }
    }
    /* ****************** Removing Duplicates & Check For existence of lables END ****************** */


    /* ****************** Insert Label to multiple DB START ****************** */

    if (!empty($all_databse_data_opposite_arr) && scount($all_databse_data_opposite_arr) > 0) {

        for ($ik = 0; $ik < scount($all_databse_data_opposite_arr); $ik++) {
            $all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];

            $obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);

            $data_all_codes = $obj_opposite_connection->MySQLSelect("SELECT vCode FROM `language_master`");

            if (empty($data_all_codes) || scount($data_all_codes) == 0) {
                // $data_all_codes = array("vCode" => "EN");
                $data_all_codes = array();
                $data_all_codes[0]['vCode'] = "EN";
            }
            
            $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
            if($num_rows->num_rows==1) {
                foreach ($data_all_codes as $data_all_codes_item) {
                    $lbl_ins_arr = array();
                    $lbl_ins_arr['lPage_id'] = "0";
                    $lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
                    $lbl_ins_arr['vLabel'] = $vLabel;
                    $lbl_ins_arr['vValue'] = $vValue_food;
                    $lbl_ins_arr['eAppType'] = $eAppType;
                    $obj_opposite_connection->MySQLQueryPerform($tbl_name_food, $lbl_ins_arr, 'insert');
                }
            }
        
            for($i=2;$i<=$total_table;$i++) {
                $table_name_tmp = 'language_label_'.$i;
                $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
                if($num_rows->num_rows==1) {
                    foreach ($data_all_codes as $data_all_codes_item) {
                        $lbl_ins_arr = array();
                        $lbl_ins_arr['lPage_id'] = "0";
                        $lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
                        $lbl_ins_arr['vLabel'] = $vLabel;
                        $lbl_ins_arr['vValue'] = $vValue_other;
                        $lbl_ins_arr['eAppType'] = $eAppType;
                        $obj_opposite_connection->MySQLQueryPerform($table_name_tmp, $lbl_ins_arr, 'insert');
                    }
                }
            }
            $obj_opposite_connection->MySQLClose();
        }
    }

    foreach ($all_databse_data_arr as $all_databse_data_arr_item) {

        $obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);

        $data_all_codes = $obj_current_connection->MySQLSelect("SELECT vCode FROM `language_master`");

        if (empty($data_all_codes) || scount($data_all_codes) == 0) {
            // $data_all_codes = array("vCode" => "EN");
            $data_all_codes = array();
            $data_all_codes[0]['vCode'] = "EN";
        }

        $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
        if($num_rows->num_rows==1) {
            foreach ($data_all_codes as $data_all_codes_item) {
                $lbl_ins_arr = array();
                $lbl_ins_arr['lPage_id'] = "0";
                $lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
                $lbl_ins_arr['vLabel'] = $vLabel;
                $lbl_ins_arr['vValue'] = $vValue_food;
                $lbl_ins_arr['eAppType'] = $eAppType;
                $obj_current_connection->MySQLQueryPerform($tbl_name_food, $lbl_ins_arr, 'insert');
            }
        }
    
        for($i=2;$i<=$total_table;$i++) {
            $table_name_tmp = 'language_label_'.$i;
            $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
            if($num_rows->num_rows==1) {
                foreach ($data_all_codes as $data_all_codes_item) {
                    $lbl_ins_arr = array();
                    $lbl_ins_arr['lPage_id'] = "0";
                    $lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
                    $lbl_ins_arr['vLabel'] = $vLabel;
                    $lbl_ins_arr['vValue'] = $vValue_other;
                    $lbl_ins_arr['eAppType'] = $eAppType;
                    $obj_current_connection->MySQLQueryPerform($table_name_tmp, $lbl_ins_arr, 'insert');
                }
            }
        }
        $obj_current_connection->MySQLClose();
    }
    /* ****************** Insert Label to multiple DB END ****************** */
    $GCS_OBJ->updateGCSData();

    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been inserted successfully.';
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been updated successfully.';
    }

    header("location:" . $backlink);
    exit;
}

################### ADD LABLES END ###################
################### GET EDIT LABLES START ###################
if (isset($_POST['submit']) == false && $action == "Edit") {
    
    $table_name_tmp = 'language_label_'.$selectedlanguage;
    $num_rows = $obj->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
    if($num_rows->num_rows==1) {
        $sql = "SELECT vLabel, eAppType, lPage_id, vValue FROM " . $table_name_tmp . " WHERE LanguageLabelId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        $eAppType = $db_data[0]['eAppType'];
        $vLabel = $db_data[0]['vLabel'];
        $lPage_id = $db_data[0]['lPage_id'];
        $vValue_other = $db_data[0]['vValue'];
    }    
    
    /* ****************** DATABASES From Main Server - bbcsproducts.net START ****************** */
    if(empty($ALL_DATABASE_MAIN)) {
        $all_databse_data = $obj->MySQLSelect("SHOW DATABASES");
    
        $all_databse_data_arr = array();
        foreach ($all_databse_data as $all_databse_data_item) {
            if(startsWith($all_databse_data_item['Database'], "bbcsprod_development") || startsWith($all_databse_data_item['Database'], "prod24_")) {
                $all_databse_data_arr[] = $all_databse_data_item['Database'];
            }
        }
    } else {
        $all_databse_data_arr = $ALL_DATABASE_MAIN;
    }
    
    /* ****************** DATABASES From Main Server - bbcsproducts.net END ****************** */
    
    /* ****************** DATABASES From opposite Server START ****************** */

    if(!empty($HOST_ARRAY)) {
        $all_databse_data_opposite_arr = array();
        $position_count = 0;
        foreach($HOST_ARRAY as $key=>$value) {
            $OPPOSITE_HOST = $USER_OPPOSITE_HOST = $PASSWORD_OPPOSITE_HOST = "";
            $OPPOSITE_HOST = $value[0];
            $DB_OPPOSITE_HOST = $value[1];
            $USER_OPPOSITE_HOST = $value[2];
            $PASSWORD_OPPOSITE_HOST = $value[3];
            $obj_opposite = new DBConnection($OPPOSITE_HOST, $DB_OPPOSITE_HOST, $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
        
            if (!empty($obj_opposite)) {
                //$all_databse_data_opposite_arr = array();
                $all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
                //$position_count = 0;
                foreach ($all_databse_data as $all_databse_data_item) {
                    if(/*startsWith($all_databse_data_item['Database'], "webpro31_cubejekdev") ||*/ startsWith($all_databse_data_item['Database'], "webpro31_kingx") || startsWith($all_databse_data_item['Database'], "prod_") || startsWith($all_databse_data_item['Database'], "kingx_pro_production")) {
                        $all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
                        $all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
                        $all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
                        $all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
                        $position_count++;
                    }
                }
        
                $obj_opposite->MySQLClose();
            }
        }
    }
    /* ****************** DATABASES From opposite Server END ****************** */


    /* ****************** Retrieve And Set Label to multiple DB START ****************** */

    foreach ($all_databse_data_arr as $all_databse_data_arr_item) {

        $obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);

        $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
        if($num_rows->num_rows==1) {
            $sql = "SELECT vLabel, eAppType, lPage_id, vValue FROM " . $tbl_name_food . " WHERE vLabel = '" . $vLabel . "' AND vCode = 'EN'";
            $db_data = $obj_current_connection->MySQLSelect($sql);
            if(!empty($db_data)) {
                $eAppType = $db_data[0]['eAppType'];
                $vLabel = $db_data[0]['vLabel'];
                $lPage_id = $db_data[0]['lPage_id'];
                $vValue_food = $db_data[0]['vValue'];
            }
        }
        
        for($i=2;$i<=$total_table;$i++) {
            $table_name_tmp = 'language_label_'.$i;
            $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
            if($num_rows->num_rows==1) {
                $sql = "SELECT vLabel, eAppType, lPage_id, vValue FROM " . $table_name_tmp . " WHERE vLabel = '" . $vLabel . "' AND vCode = 'EN'";
                $db_data = $obj_current_connection->MySQLSelect($sql);
                if(!empty($db_data)) {
                    $eAppType = $db_data[0]['eAppType'];
                    $vLabel = $db_data[0]['vLabel'];
                    $lPage_id = $db_data[0]['lPage_id'];
                    $vValue_other = $db_data[0]['vValue'];
                }
            }    
        }
        $obj_current_connection->MySQLClose();
    }
    if (!empty($all_databse_data_opposite_arr) && scount($all_databse_data_opposite_arr) > 0) {

        for ($ik = 0; $ik < scount($all_databse_data_opposite_arr); $ik++) {
            $all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];

            $obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);

            $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
            if($num_rows->num_rows==1) {
                $sql = "SELECT vLabel, eAppType, lPage_id, vValue FROM " . $tbl_name_food . " WHERE vLabel = '" . $vLabel . "' AND vCode = 'EN'";
                $db_data = $obj_opposite_connection->MySQLSelect($sql);
                if(!empty($db_data)) {
                    $eAppType = $db_data[0]['eAppType'];
                    $vLabel = $db_data[0]['vLabel'];
                    $lPage_id = $db_data[0]['lPage_id'];
                    $vValue_food = $db_data[0]['vValue'];
                }
            }
            
            for($i=2;$i<=$total_table;$i++) {
                $table_name_tmp = 'language_label_'.$i;
                $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
                if($num_rows->num_rows==1) {
                    $sql = "SELECT vLabel, eAppType, lPage_id, vValue FROM " . $table_name_tmp . " WHERE vLabel = '" . $vLabel . "' AND vCode = 'EN'";
                    $db_data = $obj_opposite_connection->MySQLSelect($sql);
                    if(!empty($db_data)) {
                        $eAppType = $db_data[0]['eAppType'];
                        $vLabel = $db_data[0]['vLabel'];
                        $lPage_id = $db_data[0]['lPage_id'];
                        $vValue_other = $db_data[0]['vValue'];
                    }
                }    
            }
            $obj_opposite_connection->MySQLClose();
        }
    }
    /* ****************** Retrieve And Set Label to multiple DB END ****************** */
}

################### GET EDIT LABLES END ###################
################### EDIT/DELETE LABLES START ###################
if ((isset($_POST['submit']) && $action == "Edit") || (!empty($DeleteLabel) && $DeleteLabel=='Yes')) {
    $oCache->flushData();

    $table_name_tmp = 'language_label_'.$selectedlanguage;
    $num_rows = $obj->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
    if($num_rows->num_rows==1) {
        $sql = "SELECT vLabel, eAppType, lPage_id, vValue FROM " . $table_name_tmp . " WHERE LanguageLabelId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);
        //$eAppType = $db_data[0]['eAppType'];
        $vLabel = $db_data[0]['vLabel'];
        $lPage_id = $db_data[0]['lPage_id'];
    }

    /* ****************** DATABASES From Main Server - bbcsproducts.net START ****************** */
    if(empty($ALL_DATABASE_MAIN)) {
        $all_databse_data = $obj->MySQLSelect("SHOW DATABASES");
    
        $all_databse_data_arr = array();
        foreach ($all_databse_data as $all_databse_data_item) {
            if(startsWith($all_databse_data_item['Database'], "bbcsprod_development") || startsWith($all_databse_data_item['Database'], "prod24_")) {
                $all_databse_data_arr[] = $all_databse_data_item['Database'];
            }
        }
    } else {
        $all_databse_data_arr = $ALL_DATABASE_MAIN;
    }
    /* ****************** DATABASES From Main Server - bbcsproducts.net END ****************** */
    
    /* ****************** DATABASES From opposite Server START ****************** */
    if(!empty($HOST_ARRAY)) {
        $all_databse_data_opposite_arr = array();
        $position_count = 0;
        foreach($HOST_ARRAY as $key=>$value) {
            $OPPOSITE_HOST = $USER_OPPOSITE_HOST = $PASSWORD_OPPOSITE_HOST = "";
            $OPPOSITE_HOST = $value[0];
            $DB_OPPOSITE_HOST = $value[1];
            $USER_OPPOSITE_HOST = $value[2];
            $PASSWORD_OPPOSITE_HOST = $value[3];
            $obj_opposite = new DBConnection($OPPOSITE_HOST, $DB_OPPOSITE_HOST, $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
        
            if (!empty($obj_opposite)) {
                //$all_databse_data_opposite_arr = array();
                $all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
                //$position_count = 0;
                foreach ($all_databse_data as $all_databse_data_item) {
                    if(/*startsWith($all_databse_data_item['Database'], "webpro31_cubejekdev") ||*/ startsWith($all_databse_data_item['Database'], "webpro31_kingx") || startsWith($all_databse_data_item['Database'], "prod_") || startsWith($all_databse_data_item['Database'], "kingx_pro_production")) {
                        $all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
                        $all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
                        $all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
                        $all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
                        $position_count++;
                    }
                }
        
                $obj_opposite->MySQLClose();
            }
        }
    }
    /* ****************** DATABASES From opposite Server END ****************** */

    /* ****************** Retrieve And Set Label to multiple DB START ****************** */

    foreach ($all_databse_data_arr as $all_databse_data_arr_item) {

        $obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);

        if(!empty($DeleteLabel) && $DeleteLabel=='Yes') {
            $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
            if($num_rows->num_rows==1) {
                $obj_current_connection->sql_query("DELETE FROM `".$tbl_name_food."` WHERE `vLabel` LIKE '".$vDeleteLabel."'");
            }
            for($i=2;$i<=$total_table;$i++) {
                $table_name_tmp = 'language_label_'.$i;
                $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
                if($num_rows->num_rows==1) {
                    $obj_current_connection->sql_query("DELETE FROM `".$table_name_tmp."` WHERE `vLabel` LIKE '".$vDeleteLabel."'");
                }
            }
        } else {
            $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
            if($num_rows->num_rows==1) {
                $where = " vLabel LIKE '" . $vLabel . "' ";
                $data_label_value_update = array();
                $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_food);
                $data_label_value_update['eAppType'] = $eAppType;
                $data_label_value_update['lPage_id'] = $lPage_id;
                $obj_current_connection->MySQLQueryPerform($tbl_name_food, $data_label_value_update, 'update', $where);
            }
            
            for($i=2;$i<=$total_table;$i++) {
                $table_name_tmp = 'language_label_'.$i;
                $num_rows = $obj_current_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
                if($num_rows->num_rows==1) {
                    $where = " vLabel LIKE '" . $vLabel . "' ";
                    $data_label_value_update = array();
                    $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_other);
                    $data_label_value_update['eAppType'] = $eAppType;
                    $data_label_value_update['lPage_id'] = $lPage_id;    
                    $obj_current_connection->MySQLQueryPerform($table_name_tmp, $data_label_value_update, 'update', $where);
                }
            }
        }
        
        if (in_array("register_driver", $all_tables_arr)) {
            $obj_current_connection->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
        }

        if (in_array("register_user", $all_tables_arr)) {
            $obj_current_connection->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
        }

        if (in_array("company", $all_tables_arr)) {
            $obj_current_connection->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
        }

        $obj_current_connection->MySQLClose();
    }

    if (!empty($all_databse_data_opposite_arr) && scount($all_databse_data_opposite_arr) > 0) {

        for ($ik = 0; $ik < scount($all_databse_data_opposite_arr); $ik++) {
            $all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];

            $obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);
            
            if(!empty($DeleteLabel) && $DeleteLabel=='Yes') {
                $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
                if($num_rows->num_rows==1) {
                    $obj_opposite_connection->sql_query("DELETE FROM `".$tbl_name_food."` WHERE `vLabel` LIKE '".$vDeleteLabel."'");
                }
                for($i=2;$i<=$total_table;$i++) {
                    $table_name_tmp = 'language_label_'.$i;
                    $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
                    if($num_rows->num_rows==1) {
                        $obj_opposite_connection->sql_query("DELETE FROM `".$table_name_tmp."` WHERE `vLabel` LIKE '".$vDeleteLabel."'");
                    }
                }
            } else {
                $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_food'");
                if($num_rows->num_rows==1) {
                    $where = " vLabel LIKE '" . $vLabel . "' ";
                    $data_label_value_update = array();
                    $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_food);
                    $data_label_value_update['eAppType'] = $eAppType;
                    $data_label_value_update['lPage_id'] = $lPage_id;
                    $obj_opposite_connection->MySQLQueryPerform($tbl_name_food, $data_label_value_update, 'update', $where);
                }
                
                for($i=2;$i<=$total_table;$i++) {
                    $table_name_tmp = 'language_label_'.$i;
                    $num_rows = $obj_opposite_connection->ExecuteQuery("SHOW TABLES LIKE '$table_name_tmp'");
                    if($num_rows->num_rows==1) {
                        $where = " vLabel LIKE '" . $vLabel . "' ";
                        $data_label_value_update = array();
                        $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_other);
                        $data_label_value_update['eAppType'] = $eAppType;
                        $data_label_value_update['lPage_id'] = $lPage_id;
                        $obj_opposite_connection->MySQLQueryPerform($table_name_tmp, $data_label_value_update, 'update', $where);
                    }
                }
            }

            if (in_array("register_driver", $all_tables_arr)) {
                $obj_opposite_connection->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
            }

            if (in_array("register_user", $all_tables_arr)) {
                $obj_opposite_connection->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
            }

            if (in_array("company", $all_tables_arr)) {
                $obj_opposite_connection->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
            }

            $obj_opposite_connection->MySQLClose();
        }
    }

    $GCS_OBJ->updateGCSData();
    /* ****************** Retrieve And Set Label to multiple DB END ****************** */
    if(!empty($DeleteLabel) && $DeleteLabel=='Yes') {
        $returnArr = array();
        $returnArr['Action'] = "1";
        $returnArr['message'] = "Label is deleted successfully";
        echo json_encode($returnArr);exit;
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been updated successfully.';
        header("location:" . $backlink);
    }
}
################### EDIT/DELETE LABLES END ###################
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Language <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <?php include_once('global_files.php'); ?>
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
                            <h2><?= $action; ?> Language Label</h2>
                            <a href="languages.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <?php if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record Updated successfully.
                                </div><br/>
                            <?php } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                                </div><br/>
                            <?php } elseif ($success == 0 && $var_msg != '') { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $var_msg; ?>
                                </div><br/>
                            <?php } ?>
                            <form method="post" name="_languages_form" id="_languages_form" action="">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="languages.php"/>
                                <input type="hidden" name="development_db" value="<?= $_POST['development_db'] ?>">
                                <input type="hidden" name="project_setup_db" value="<?= $_POST['project_setup_db'] ?>">
                                <?php/*<input type="hidden" name="prod_kingx" value="<?= $_POST['prod_kingx'] ?>">*/?>
                                
                                <div class="row">
                                    <div class="col-lg-12" id="errorMessage">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Language Label <?= ($id != '') ? '' : '<span class="red"> *</span>'; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vLabel"  id="vLabel" value="<?= $vLabel; ?>" placeholder="Language Label" <?= ($id != '') ? 'disabled' : 'required'; ?>>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Food (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_food" id="vValue_food" value="<?php echo htmlspecialchars($vValue_food, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Food (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Other (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_other" id="vValue_other" value="<?php echo htmlspecialchars($vValue_other, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Other (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Lable For<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="eAppType" id="eAppType" class="form-control" required="required">
                                            <option value="General" <?= ($eAppType == 'General') ? 'selected' : ''; ?> >General</option>
                                            <option value="Ride" <?= ($eAppType == 'Ride') ? 'selected' : ''; ?> >Ride</option>
                                            <option value="Delivery" <?= ($eAppType == 'Delivery') ? 'selected' : ''; ?> >Delivery</option>
                                            <option value="Ride-Delivery" <?= ($eAppType == 'Ride-Delivery') ? 'selected' : ''; ?> >Ride-Delivery</option>
                                            <option value="UberX" <?= ($eAppType == 'UberX') ? 'selected' : ''; ?> >UberX</option>
                                            <option value="Multi-Delivery" <?= ($eAppType == 'Multi-Delivery') ? 'selected' : ''; ?> >Multi-Delivery</option>
                                            <option value="DeliverAll" <?= ($eAppType == 'DeliverAll') ? 'selected' : ''; ?> >DeliverAll</option>
                                            <option value="Kiosk" <?= ($eAppType == 'Kiosk') ? 'selected' : ''; ?> >Kiosk</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Label">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <a href="languages.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <div class="row loding-action" id="imageIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>


        <?php include_once('footer.php'); ?>
    </body>
    <!-- END BODY-->
</html>
<script type="text/javascript" language="javascript">
    $(document).ready(function () {

        $('#imageIcon').hide();

        $("form[name='_languages_form']").submit(function () {
            var idvalue = $("input[name=id]").val();
            var vLabel = $("input[name=vLabel]").val();

            if (idvalue == '') {
                if (vLabel.match("^LBL_")) {
                    if(vLabel === vLabel.toUpperCase()) {
                        var res_vLabel = vLabel.split("_");
                        for (i = 0; i < res_vLabel.length; i++) {
                            if(res_vLabel[i]=='') {
                                alert("Please add language label in proper format like 'LBL_LABEL_NAME', Don't merge more than one underscore");
                                return false;
                            }
                        }
                        var alphaExp = /[0-9]/;
                        if(vLabel.match(alphaExp)) {
                            alert("Numeric should not be allowed in language label");
                            return false;
                        }
                        return true;
                    } else {
                        alert('Please add language label in uppercase.');
                        return false;
                    }
                } else {
                    alert('Please add language label start with \"LBL_\".');
                    return false;
                }

            } else {
                return true;
            }
        });

    });

    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "languages_action_multisystem_food_other.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
</script>



