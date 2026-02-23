<?php
 // ini_set('display_errors', 1);
 //  ini_set('display_startup_errors', 1);
 //  error_reporting(E_ALL);

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
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$DeleteLabel = isset($_REQUEST['DeleteLabel']) ? $_REQUEST['DeleteLabel'] : 'No';
$vDeleteLabel = isset($_REQUEST['vDeleteLabel']) ? $_REQUEST['vDeleteLabel'] : 'No';
$action = ($id != '') ? 'Edit' : 'Add';

/* ****************** Host, Db, Tables Details which will be taken for proceed start ****************** */

//if want all dbs from bbcsproducts.net, then only remove following array>>$ALL_DATABASE_MAIN
//$ALL_DATABASE_MAIN = array('bbcsprod_development','bbcsprod_cubejek20','bbcsprod_cubex20','bbcsprod_cubex2020','bbcsprod_deliveryX','bbcsprod_delvX','bbcsprod_foodx','bbcsprod_groceryx','bbcsprod_pharmacyx','bbcsprod_ridedeliveryX','bbcsprod_ridex','bbcsprod_servicex');
//$ALL_DATABASE_MAIN = array('bbcsprod_development','bbcsprod_cubejek20');

//if want all tables then set $ALL_TABLES_MAIN_VAR this to 1..so it will not consider this $ALL_TABLES_MAIN
$ALL_TABLES_MAIN = array('language_label','language_label_deliverall','language_label_delivery','language_label_food','language_label_ride','language_label_ride_delivery','language_label_uberx','language_label_deliveryking','language_label_uberx_1','language_label_uberx_2','language_label_uberx_3','language_label_uberx_4','language_label_uberx_5','language_label_uberx_6', 'language_label_uberx_7', 'language_label_uberx_8', 'language_label_medicalservice');
//$ALL_TABLES_MAIN = array('language_label','language_label_food');
$ALL_TABLES_MAIN_VAR = 0;

//arrays set to host in which data is to be inserted, here entered host,db,user, pwd in sequence
$HOST_ARRAY = array(
    array("78.129.252.33","kingx_pro_production", "king_wild_dev", $_POST['project_setup_db']),
);

/* ****************** Host, Db, Tables Details which will be taken for proceed end ****************** */

$tbl_name = 'language_label';
$total_table = 10;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';


// set all variables with either post (when submit) either blank (when insert)
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : $id;
$lPage_id = isset($_POST['lPage_id']) ? $_POST['lPage_id'] : '';
$eAppType = isset($_POST['eAppType']) ? $_POST['eAppType'] : '';

$vValue_cubejek = isset($_POST['vValue_cubejek']) ? $_POST['vValue_cubejek'] : '';
$vValue_ride = isset($_POST['vValue_ride']) ? $_POST['vValue_ride'] : '';
$vValue_delivery = isset($_POST['vValue_delivery']) ? $_POST['vValue_delivery'] : '';
$vValue_uberx = isset($_POST['vValue_uberx']) ? $_POST['vValue_uberx'] : '';
$vValue_ride_delivery = isset($_POST['vValue_ride_delivery']) ? $_POST['vValue_ride_delivery'] : '';
$vValue_food = isset($_POST['vValue_food']) ? $_POST['vValue_food'] : '';
$vValue_deliverall = isset($_POST['vValue_deliverall']) ? $_POST['vValue_deliverall'] : '';
$vValue_deliveryking = isset($_POST['vValue_deliveryking']) ? $_POST['vValue_deliveryking'] : '';
$vValue_carwash = isset($_POST['vValue_carwash']) ? $_POST['vValue_carwash'] : '';
$vValue_homeclean = isset($_POST['vValue_homeclean']) ? $_POST['vValue_homeclean'] : '';
$vValue_beauty = isset($_POST['vValue_beauty']) ? $_POST['vValue_beauty'] : '';
$vValue_towtruck = isset($_POST['vValue_towtruck']) ? $_POST['vValue_towtruck'] : '';
$vValue_massage = isset($_POST['vValue_massage']) ? $_POST['vValue_massage'] : '';
$vValue_sanitization = isset($_POST['vValue_sanitization']) ? $_POST['vValue_sanitization'] : '';
$vValue_cubedocx = isset($_POST['vValue_cubedocx']) ? $_POST['vValue_cubedocx'] : '';
$vValue_medicalservice = isset($_POST['vValue_medicalservice']) ? $_POST['vValue_medicalservice'] : '';
$vValue_maid = isset($_POST['vValue_maid']) ? $_POST['vValue_maid'] : '';


################### FUNCTIONS START ###################
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

function isDeliverAllLanguageTables($lang_table) {
    global $DELIVERALL_LNG_TABLES;
    if (empty($DELIVERALL_LNG_TABLES)) {
        $DELIVERALL_LNG_TABLES = array();

        for ($i = 0; $i < 500; $i++) {
            $DELIVERALL_LNG_TABLES[] = "language_label_" . $i;
        }
    }

    return in_array($lang_table, $DELIVERALL_LNG_TABLES);
}

function updateLblValues($database, $table, $obj) {
    global $vValue_ride, $vValue_ride_delivery, $vValue_delivery, $vValue_uberx, $vValue_deliverall, $vValue_food, $vValue_cubejek, $vValue_deliveryking, $vValue_carwash, $vValue_homeclean, $vValue_beauty, $vValue_towtruck,$vValue_massage,$vValue_sanitization,$vValue_cubedocx,$vValue_medicalservice, $vLabel, $tbl_name, $eAppType, $lPage_id;

    $taxi_db = array("master_taxi", "master_taxi_old");
    $taxi_tables = array("language_label_ride", "language_label_taxi");

    $taxi_delivery_db = array("master_taxi_delivery", "master_ride_delivery");
    $taxi_delivery_tables = array("language_label_ride_delivery", "language_label_taxi_delivery");

    $delivery_db = array("master_delivery", "master_deliver");
    $delivery_tables = array("language_label_delivery", "language_label_deliver");

    $ufx_db = array("master_ufx");
    $ufx_tables = array("language_label_uberx");

    $deliverall_db = array("master_DeliverAll");
    $deliverall_tables = array("language_label_deliverall");

    $deliverall_food_db = array("master_food");
    $deliverall_food_tables = array("language_label_food");

    $cubejek_db = array("master_cubejek", "master_cubejekdevshark");
    $cubejek_tables = array("language_label");
    
    $deliveryking_tables = array("language_label_deliveryking");
    $carwash_tables = array("language_label_uberx_1");
    $homeclean_tables = array("language_label_uberx_2");
    $beauty_tables = array("language_label_uberx_3");
    $towtruck_tables = array("language_label_uberx_4");
    $massage_tables = array("language_label_uberx_5");
    $sanitization_tables = array("language_label_uberx_6");
    $cubedocx_tables = array("language_label_uberx_7");
    $maid_tables = array("language_label_uberx_8");
    $medicalservice_tables = array("language_label_medicalservice");


    if (in_array($table, $taxi_tables)) {
        if (!empty($vValue_ride)) {

            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_ride);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $taxi_delivery_tables)) {
        if (!empty($vValue_ride_delivery)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_ride_delivery);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $delivery_tables)) {
        if (!empty($vValue_delivery)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_delivery);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $ufx_tables)) {
        if (!empty($vValue_uberx)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_uberx);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $deliverall_tables)) {
        if (!empty($vValue_deliverall)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_deliverall);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $deliverall_food_tables)) {
        if (!empty($vValue_food)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_food);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $deliveryking_tables)) {
        if (!empty($vValue_deliveryking)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_deliveryking);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $carwash_tables)) {
        if (!empty($vValue_carwash)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_carwash);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $homeclean_tables)) {
        if (!empty($vValue_homeclean)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_homeclean);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $beauty_tables)) {
        if (!empty($vValue_beauty)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_beauty);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $towtruck_tables)) {
        if (!empty($vValue_towtruck)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_towtruck);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $massage_tables)) {
        if (!empty($vValue_massage)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_massage);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
            
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            if(scount($db_data)>0) {
                    $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
            } else {
            $data_label_value_update['vLabel'] = $vLabel;
            $data_label_value_update['vCode'] = 'EN';
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'insert');
            }
        }
        return true;
    } else if (in_array($table, $sanitization_tables)) {
        if (!empty($vValue_sanitization)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_sanitization);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
            
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            if(scount($db_data)>0) {
                    $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
            } else {
            $data_label_value_update['vLabel'] = $vLabel;
            $data_label_value_update['vCode'] = 'EN';
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'insert');
            }
            //$obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $cubedocx_tables)) {
        if (!empty($vValue_cubedocx)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_cubedocx);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
            
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            if(scount($db_data)>0) {
                    $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
            } else {
            $data_label_value_update['vLabel'] = $vLabel;
            $data_label_value_update['vCode'] = 'EN';
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'insert');
            }
            //$obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $medicalservice_tables)) {
        if (!empty($vValue_medicalservice)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_medicalservice);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
            
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            if(scount($db_data)>0) {
                    $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
            } else {
            $data_label_value_update['vLabel'] = $vLabel;
            $data_label_value_update['vCode'] = 'EN';
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'insert');
            }
            //$obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($table, $maid_tables)) {
        if (!empty($vValue_maid)) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_maid);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
            
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            if(scount($db_data)>0) {
                $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
            } else {
                $data_label_value_update['vLabel'] = $vLabel;
                $data_label_value_update['vCode'] = 'EN';
                $obj->MySQLQueryPerform($table, $data_label_value_update, 'insert');
            }
        }
        return true;
    }
    

    if (in_array($database, $taxi_db)) {
        if (!empty($vValue_ride) && (in_array($table, $taxi_tables) || $table == "language_label")) {

            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_ride);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($database, $taxi_delivery_db)) {
        if (!empty($vValue_ride_delivery) && (in_array($table, $taxi_delivery_tables) || $table == "language_label")) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_ride_delivery);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($database, $delivery_db)) {
        if (!empty($vValue_delivery) && (in_array($table, $delivery_tables) || $table == "language_label")) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_delivery);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($database, $ufx_db)) {
        if (!empty($vValue_uberx) && (in_array($table, $ufx_tables) || $table == "language_label")) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_uberx);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($database, $deliverall_db)) {
        if (!empty($vValue_deliverall) && (in_array($table, $deliverall_tables) || $table == "language_label")) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_deliverall);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    } else if (in_array($database, $deliverall_food_db)) {
        if (!empty($vValue_food) && (in_array($table, $deliverall_food_tables) || $table == "language_label")) {
            $where = " vLabel LIKE '" . $vLabel . "' ";

            $data_label_value_update = array();
            $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_food);
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;

            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
        }
        return true;
    }

    if (!empty($vValue_cubejek)) {
        $where = " vLabel LIKE '" . $vLabel . "' ";

        $data_label_value_update = array();
        $data_label_value_update['vValue'] = getProperDataValueWithoutClean($vValue_cubejek);
        $data_label_value_update['eAppType'] = $eAppType;
        $data_label_value_update['lPage_id'] = $lPage_id;

        /* echo "<PRE>";
          print_r($data_label_value_update);exit; */

        $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
    }
    return true;
}

function setLblValues($database, $table, $obj) {
    global $vValue_ride, $vValue_ride_delivery, $vValue_delivery, $vValue_uberx, $vValue_deliverall, $vValue_food, $vValue_cubejek, $vValue_deliveryking, $vValue_carwash, $vValue_homeclean, $vValue_beauty, $vValue_towtruck,$vValue_massage,$vValue_sanitization,$vValue_cubedocx,$vValue_medicalservice, $vLabel, $tbl_name;

    $taxi_tables = array("language_label_ride", "language_label_taxi");
    $taxi_delivery_tables = array("language_label_ride_delivery", "language_label_taxi_delivery");
    $delivery_tables = array("language_label_delivery", "language_label_deliver");
    $ufx_tables = array("language_label_uberx");
    $deliverall_tables = array("language_label_deliverall");
    $deliverall_food_tables = array("language_label_food");
    $cubejek_tables = array("language_label");
    $deliveryking_tables = array("language_label_deliveryking");
    $carwash_tables = array("language_label_uberx_1");
    $homeclean_tables = array("language_label_uberx_2");
    $beauty_tables = array("language_label_uberx_3");
    $towtruck_tables = array("language_label_uberx_4");
    $massage_tables = array("language_label_uberx_5");
    $sanitization_tables = array("language_label_uberx_6");
    $cubedocx_tables = array("language_label_uberx_7");
    $maid_tables = array("language_label_uberx_8");
    $medicalservice_tables = array("language_label_medicalservice");

    if (in_array($table, $taxi_tables)) {
        if (empty($vValue_ride)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_ride = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $taxi_delivery_tables)) {
        if (empty($vValue_ride_delivery)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_ride_delivery = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $delivery_tables)) {
        if (empty($vValue_delivery)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_delivery = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $ufx_tables)) {
        if (empty($vValue_uberx)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_uberx = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $deliverall_tables)) {
        if (empty($vValue_deliverall)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_deliverall = $db_data[0]['vValue'];
            return true;
        }
        return true;
    } else if (in_array($table, $deliverall_food_tables)) {
        if (empty($vValue_food)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_food = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $deliveryking_tables)) {
        if (empty($vValue_deliveryking)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_deliveryking = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $carwash_tables)) {
        if (empty($vValue_carwash)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_carwash = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $homeclean_tables)) {
        if (empty($vValue_homeclean)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_homeclean = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $beauty_tables)) {
        if (empty($vValue_beauty)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_beauty = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $towtruck_tables)) {
        if (empty($vValue_towtruck)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_towtruck = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $massage_tables)) {
        if (empty($vValue_massage)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_massage = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $sanitization_tables)) {
        if (empty($vValue_sanitization)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_sanitization = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $cubedocx_tables)) {
        if (empty($vValue_cubedocx)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_cubedocx = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $medicalservice_tables)) {
        if (empty($vValue_medicalservice)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_medicalservice = $db_data[0]['vValue'];
        }
        return true;
    } else if (in_array($table, $maid_tables)) {
        if (empty($vValue_maid)) {
            $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
            $vValue_maid = $db_data[0]['vValue'];
        }
        return true;
    }

    if (empty($vValue_cubejek)) {
        $db_data = $obj->MySQLSelect("SELECT vValue FROM " . $tbl_name . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
        $vValue_cubejek = $db_data[0]['vValue'];
    }
    return true;
}

function getLabelValue($database, $table) {
    global $vValue_ride, $vValue_ride_delivery, $vValue_delivery, $vValue_uberx, $vValue_deliverall, $vValue_food, $vValue_cubejek, $vValue_deliveryking, $vValue_carwash, $vValue_homeclean, $vValue_beauty, $vValue_towtruck,$vValue_massage,$vValue_sanitization,$vValue_cubedocx,$vValue_medicalservice,$vValue_maid;

    $taxi_db = array("master_taxi", "master_taxi_old");
    $taxi_tables = array("language_label_ride", "language_label_taxi");

    $taxi_delivery_db = array("master_taxi_delivery", "master_ride_delivery");
    $taxi_delivery_tables = array("language_label_ride_delivery", "language_label_taxi_delivery");

    $delivery_db = array("master_delivery", "master_deliver");
    $delivery_tables = array("language_label_delivery", "language_label_deliver");

    $ufx_db = array("master_ufx");
    $ufx_tables = array("language_label_uberx");

    $deliverall_db = array("master_DeliverAll");
    $deliverall_tables = array("language_label_deliverall");

    $deliverall_food_db = array("master_food");
    $deliverall_food_tables = array("language_label_food");

    $cubejek_db = array("master_cubejek", "master_cubejekdevshark");
    $cubejek_tables = array("language_label");

    $deliveryking_tables = array("language_label_deliveryking");
    $carwash_tables = array("language_label_uberx_1");
    $homeclean_tables = array("language_label_uberx_2");
    $beauty_tables = array("language_label_uberx_3");
    $towtruck_tables = array("language_label_uberx_4");
    $massage_tables = array("language_label_uberx_5");
    $sanitization_tables = array("language_label_uberx_6");
    $cubedocx_tables = array("language_label_uberx_7");
    $maid_tables = array("language_label_uberx_8");
    $medicalservice_tables = array("language_label_medicalservice");

    if (in_array($database, $taxi_db)) {
        if (in_array($table, $taxi_tables) || $table == "language_label") {
            return getProperDataValue($vValue_ride);
        } else {
            return "";
        }
    } else if (in_array($database, $taxi_delivery_db)) {
        if (in_array($table, $taxi_delivery_tables) || $table == "language_label") {
            return getProperDataValue($vValue_ride_delivery);
        } else {
            return "";
        }
    } else if (in_array($database, $delivery_db)) {
        if (in_array($table, $delivery_tables) || $table == "language_label") {
            return getProperDataValue($vValue_delivery);
        } else {
            return "";
        }
    } else if (in_array($database, $ufx_db)) {
        if (in_array($table, $ufx_tables) || $table == "language_label") {
            return getProperDataValue($vValue_uberx);
        } else {
            return "";
        }
    } else if (in_array($database, $deliverall_db)) {
        if (in_array($table, $deliverall_tables) || $table == "language_label") {
            return getProperDataValue($vValue_deliverall);
        } else {
            return "";
        }
    } else if (in_array($database, $deliverall_food_db)) {
        if (in_array($table, $deliverall_food_tables) || $table == "language_label") {
            return getProperDataValue($vValue_food);
        } else {
            return "";
        }
    } else if (in_array($database, $cubejek_db)) {
        if (in_array($table, $cubejek_tables) || $table == "language_label") {
            return getProperDataValue($vValue_cubejek);
        } else {
            return "";
        }
    }

    if (in_array($table, $taxi_tables)) {
        return getProperDataValue($vValue_ride);
    } else if (in_array($table, $taxi_delivery_tables)) {
        return getProperDataValue($vValue_ride_delivery);
    } else if (in_array($table, $delivery_tables)) {
        return getProperDataValue($vValue_delivery);
    } else if (in_array($table, $ufx_tables)) {
        return getProperDataValue($vValue_uberx);
    } else if (in_array($table, $deliverall_tables)) {
        return getProperDataValue($vValue_deliverall);
    } else if (in_array($table, $deliverall_food_tables)) {
        return getProperDataValue($vValue_food);
    } else if (in_array($table, $deliveryking_tables)) {
        return getProperDataValue($vValue_deliveryking);
    } else if (in_array($table, $carwash_tables)) {
        return getProperDataValue($vValue_carwash);
    } else if (in_array($table, $homeclean_tables)) {
        return getProperDataValue($vValue_homeclean);
    } else if (in_array($table, $beauty_tables)) {
        return getProperDataValue($vValue_beauty);
    } else if (in_array($table, $towtruck_tables)) {
        return getProperDataValue($vValue_towtruck);
    } else if (in_array($table, $massage_tables)) {
        return getProperDataValue($vValue_massage);
    } else if (in_array($table, $sanitization_tables)) {
        return getProperDataValue($vValue_sanitization);
    } else if (in_array($table, $cubedocx_tables)) {
        return getProperDataValue($vValue_cubedocx);
    } else if (in_array($table, $cubejek_tables)) {
        return getProperDataValue($vValue_cubejek);
    } else if (in_array($table, $medicalservice_tables)) {
        return getProperDataValue($vValue_medicalservice);
    } else if (in_array($table, $maid_tables)) {
        return getProperDataValue($vValue_maid);
    } else {
        return getProperDataValue($vValue_cubejek);
    }
}

function deleteLblValues($database, $table, $obj){
	global $vLabel, $tbl_name;
	
	$taxi_db = array("master_taxi","master_taxi_old");
	$taxi_tables = array("language_label_ride","language_label_taxi");
	
	$taxi_delivery_db = array("master_taxi_delivery","master_ride_delivery");
	$taxi_delivery_tables = array("language_label_ride_delivery","language_label_taxi_delivery");
	
	$delivery_db = array("master_delivery","master_deliver");
	$delivery_tables = array("language_label_delivery","language_label_deliver");
	
	$ufx_db = array("master_ufx");
	$ufx_tables = array("language_label_uberx");
	
	$deliverall_db = array("master_DeliverAll");
	$deliverall_tables = array("language_label_deliverall");
	
	$deliverall_food_db = array("master_food");
	$deliverall_food_tables = array("language_label_food");
	
	$cubejek_db = array("master_cubejek","master_cubejekdevshark");
	$cubejek_tables = array("language_label");
	
    $deliveryking_tables = array("language_label_deliveryking");
    $carwash_tables = array("language_label_uberx_1");
    $homeclean_tables = array("language_label_uberx_2");
    $beauty_tables = array("language_label_uberx_3");
    $towtruck_tables = array("language_label_uberx_4");
    $massage_tables = array("language_label_uberx_5");
    $sanitization_tables = array("language_label_uberx_6");
    $cubedocx_tables = array("language_label_uberx_7");
    $maid_tables = array("language_label_uberx_8");
    $medicalservice_tables = array("language_label_medicalservice");

	
	if(in_array($table,$taxi_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$taxi_delivery_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$delivery_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$ufx_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$deliverall_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$deliverall_food_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$deliveryking_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$carwash_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$homeclean_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$beauty_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$towtruck_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$massage_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$sanitization_tables)){
		$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		return true;
	}else if(in_array($table,$medicalservice_tables)){
        $obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
        return true;
    } else if(in_array($table,$maid_tables)){
        $obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
        return true;
    }
	
	if(in_array($database,$taxi_db)){
		if((in_array($table,$taxi_tables) || $table == "language_label")){
			$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		}
		return true;
	}else if(in_array($database,$taxi_delivery_db)){
		if((in_array($table,$taxi_delivery_tables) || $table == "language_label")){
			$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		}
		return true;
	}else if(in_array($database,$delivery_db)){
		if((in_array($table,$delivery_tables) || $table == "language_label")){
			$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		}
		return true;
	}else if(in_array($database,$ufx_db)){
		if((in_array($table,$ufx_tables) || $table == "language_label")){
			$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		}
		return true;
	}else if(in_array($database,$deliverall_db)){
		if((in_array($table,$deliverall_tables) || $table == "language_label")){
			$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		}
		return true;
	}else if(in_array($database,$deliverall_food_db)){
		if((in_array($table,$deliverall_food_tables) || $table == "language_label")){
			$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
		}
		return true;
	}
	
	$obj->sql_query("DELETE FROM `".$table."` WHERE `vLabel` LIKE '".$vLabel."'");
	
	return true;
}


################### FUNCTIONS END ###################

################### ADD LABLES START ###################
if (isset($_POST['submit']) && $action == "Add") {
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
                    if(startsWith($all_databse_data_item['Database'], "prod_") || startsWith($all_databse_data_item['Database'], "kingx_pro_production")) {
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
        $all_tables_lng_arr = array();
        $all_tables_arr = array();
        $all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
        foreach ($all_tables as $all_tables_item) {
            $item = $all_tables_item["Tables_in_" . $all_databse_data_arr_item];
            $all_tables_arr[] = $item;
            if (startsWith($item, "language_label") == true) {
                $all_tables_lng_arr[] = $item;
            }
        }

        for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
            $table_name_tmp = $all_tables_lng_arr[$i];
            if (in_array($table_name_tmp, $all_tables_arr)) {
                $db_label_check = $obj_current_connection->MySQLSelect("SELECT vLabel FROM `" . $table_name_tmp . "` WHERE vLabel = '" . $vLabel . "'");
                if (!empty($db_label_check) && scount($db_label_check) > 0) {
                    $var_msg = "Language label already exists ".$all_databse_data_arr_item . " === " . $table_name_tmp;
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

            if (checkTableExistsDatabaseLang($tbl_update_name, $all_databse_data_opposite_arr_item['DB'],$all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD'])) {
                removeDuplicatesFromLngTable($tbl_update_name, $obj_opposite_connection);
            }

            /*             * ****************** Check For existence of lables  ******************* */
            $all_tables_lng_arr = array();
            $all_tables_arr = array();
            $all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
            foreach ($all_tables as $all_tables_item) {
                $item = $all_tables_item["Tables_in_" . $all_databse_data_opposite_arr_item];
                $all_tables_arr[] = $item;
                if (startsWith($item, "language_label") == true) {
                    $all_tables_lng_arr[] = $item;
                }
            }

            for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
                $table_name_tmp = $all_tables_lng_arr[$i];
                if (in_array($table_name_tmp, $all_tables_arr)) {
                    $db_label_check = $obj_opposite_connection->MySQLSelect("SELECT vLabel FROM `" . $table_name_tmp . "` WHERE vLabel = '" . $vLabel . "'");
                    if (!empty($db_label_check) && scount($db_label_check) > 0) {
                        $var_msg = "Language label already exists ".$all_databse_data_opposite_arr_item['DB'] . " === " . $table_name_tmp;
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

            $all_tables_lng_arr = array();
            $all_tables_arr = array();
            $all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
            foreach ($all_tables as $all_tables_item) {
                $item = $all_tables_item["Tables_in_" . $all_databse_data_opposite_arr_item['DB']];
                $all_tables_arr[] = $item;
                if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
                    $all_tables_lng_arr[] = $item;
                }
            }

            for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
                $table_name_tmp = $all_tables_lng_arr[$i];

                if (!isDeliverAllLanguageTables($table_name_tmp)) {
                    $vValue_tmp = getLabelValue($all_databse_data_opposite_arr_item['DB'], $table_name_tmp);
                    if (!empty($vValue_tmp)) {

                        foreach ($data_all_codes as $data_all_codes_item) {

                            $lbl_ins_arr = array();
                            $lbl_ins_arr['lPage_id'] = "0";
                            $lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
                            $lbl_ins_arr['vLabel'] = $vLabel;
                            $lbl_ins_arr['vValue'] = $vValue_tmp;
                            $lbl_ins_arr['eAppType'] = $eAppType;

                            $obj_opposite_connection->MySQLQueryPerform($table_name_tmp, $lbl_ins_arr, 'insert');
                        }
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

        $all_tables_lng_arr = array();
        $all_tables_arr = array();
        $all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
        foreach ($all_tables as $all_tables_item) {
            $item = $all_tables_item["Tables_in_" . $all_databse_data_arr_item];
            $all_tables_arr[] = $item;
            if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
                $all_tables_lng_arr[] = $item;
            }
        }

        for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
            $table_name_tmp = $all_tables_lng_arr[$i];

            if (isDeliverAllLanguageTables($table_name_tmp) == false) {
                $vValue_tmp = getLabelValue($all_databse_data_arr_item, $table_name_tmp);
                if (!empty($vValue_tmp)) {

                    foreach ($data_all_codes as $data_all_codes_item) {
                        $lbl_ins_arr = array();
                        $lbl_ins_arr['lPage_id'] = "0";
                        $lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
                        $lbl_ins_arr['vLabel'] = $vLabel;
                        $lbl_ins_arr['vValue'] = $vValue_tmp;
                        $lbl_ins_arr['eAppType'] = $eAppType;
                        
                        $obj_current_connection->MySQLQueryPerform($table_name_tmp, $lbl_ins_arr, 'insert');
                    }
                }
            }
        }
        $obj_current_connection->MySQLClose();
    }
    /* ****************** Insert Label to multiple DB END ****************** */
    $oCache->flushData();
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
if (!isset($_POST['submit']) && $action == "Edit") {

    $sql = "SELECT vLabel, eAppType, lPage_id FROM " . $tbl_update_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $eAppType = $db_data[0]['eAppType'];

    $vLabel = $db_data[0]['vLabel'];
    $lPage_id = $db_data[0]['lPage_id'];
    
    $all_tables_lng_arr = array();
    $all_tables_arr = array();
    $all_tables = $obj->MySQLSelect("SHOW TABLES");
    foreach ($all_tables as $all_tables_item) {
        $item = $all_tables_item["Tables_in_" . TSITE_DB];
        $all_tables_arr[] = $item;
        if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
            $all_tables_lng_arr[] = $item;
        }
    }

    for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
        $table_name_tmp = $all_tables_lng_arr[$i];

        if (isDeliverAllLanguageTables($table_name_tmp) == false) {
            setLblValues(TSITE_DB, $table_name_tmp, $obj);
        }
    }


    /* ****************** DATABASES From Main Server - bbcsproducts.net START ****************** */
    //if(empty($ALL_DATABASE_MAIN)) {
    //    $all_databse_data = $obj->MySQLSelect("SHOW DATABASES");
    //
    //    $all_databse_data_arr = array();
    //    foreach ($all_databse_data as $all_databse_data_item) {
    //        if(startsWith($all_databse_data_item['Database'], "bbcsprod_")) {
    //            $all_databse_data_arr[] = $all_databse_data_item['Database'];
    //        }
    //    }
    //} else {
    //    $all_databse_data_arr = $ALL_DATABASE_MAIN;
    //}
    
    /* ****************** DATABASES From Main Server - bbcsproducts.net END ****************** */
    
    /* ****************** DATABASES From opposite Server START ****************** */

    //if(!empty($HOST_ARRAY)) {
    //    $all_databse_data_opposite_arr = array();
    //    $position_count = 0;
    //    foreach($HOST_ARRAY as $key=>$value) {
    //        $OPPOSITE_HOST = $USER_OPPOSITE_HOST = $PASSWORD_OPPOSITE_HOST = "";
    //        $OPPOSITE_HOST = $value[0];
    //        $DB_OPPOSITE_HOST = $value[1];
    //        $USER_OPPOSITE_HOST = $value[2];
    //        $PASSWORD_OPPOSITE_HOST = $value[3];
    //        $obj_opposite = new DBConnection($OPPOSITE_HOST, $DB_OPPOSITE_HOST, $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
    //    
    //        if (!empty($obj_opposite)) {
    //            //$all_databse_data_opposite_arr = array();
    //            $all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
    //            //$position_count = 0;
    //            foreach ($all_databse_data as $all_databse_data_item) {
    //                if(startsWith($all_databse_data_item['Database'], "webpro31_cubejekdev")) {
    //                    $all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
    //                    $all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
    //                    $all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
    //                    $all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
    //                    $position_count++;
    //                }
    //            }
    //    
    //            $obj_opposite->MySQLClose();
    //        }
    //    }
    //}
    /* ****************** DATABASES From opposite Server END ****************** */


    /* ****************** Retrieve And Set Label to multiple DB START ****************** */

    //foreach ($all_databse_data_arr as $all_databse_data_arr_item) {
    //
    //    $obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);
    //
    //
    //    $all_tables_lng_arr = array();
    //    $all_tables_arr = array();
    //    $all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
    //    foreach ($all_tables as $all_tables_item) {
    //        $item = $all_tables_item["Tables_in_" . $all_databse_data_arr_item];
    //        $all_tables_arr[] = $item;
    //        if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
    //            $all_tables_lng_arr[] = $item;
    //        }
    //    }
    //
    //    for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
    //        $table_name_tmp = $all_tables_lng_arr[$i];
    //
    //        if (isDeliverAllLanguageTables($table_name_tmp) == false) {
    //            setLblValues($all_databse_data_arr_item, $table_name_tmp, $obj_current_connection);
    //        }
    //    }
    //    $obj_current_connection->MySQLClose();
    //}
    //
    //if (!empty($all_databse_data_opposite_arr) && scount($all_databse_data_opposite_arr) > 0) {
    //
    //    for ($ik = 0; $ik < scount($all_databse_data_opposite_arr); $ik++) {
    //        $all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];
    //
    //        $obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);
    //
    //
    //        $all_tables_lng_arr = array();
    //        $all_tables_arr = array();
    //        $all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
    //        foreach ($all_tables as $all_tables_item) {
    //            $item = $all_tables_item["Tables_in_" . $all_databse_data_opposite_arr_item['DB']];
    //            $all_tables_arr[] = $item;
    //            if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
    //                $all_tables_lng_arr[] = $item;
    //            }
    //        }
    //
    //        for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
    //            $table_name_tmp = $all_tables_lng_arr[$i];
    //
    //            if (!isDeliverAllLanguageTables($table_name_tmp)) {
    //                setLblValues($all_databse_data_opposite_arr_item['DB'], $table_name_tmp, $obj_opposite_connection);
    //            }
    //        }
    //        $obj_opposite_connection->MySQLClose();
    //    }
    //}


    /* ****************** Retrieve And Set Label to multiple DB END ****************** */

     //echo "<BR/>";
     // echo "<hr>";
     // echo "vValueRide=".$vValue_ride;
     // echo "<hr>";
     // echo "vValue_ride_delivery=".$vValue_ride_delivery;
     // echo "<hr>";
     // echo "vValue_delivery=".$vValue_delivery;
     // echo "<hr>";
     // echo "vValue_uberx=".$vValue_uberx;
     // echo "<hr>";
     // echo "vValue_deliverall=".$vValue_deliverall;
     // echo "<hr>";
     // echo "vValue_food=".$vValue_food;
     // echo "<hr>";
     // echo "vValue_cubejek=".$vValue_cubejek;
     // echo "<hr>";
     // echo "EDITCalled::1";exit; 
}

################### GET EDIT LABLES END ###################
################### EDIT/DELETE LABLES START ###################
if ((isset($_POST['submit']) && $action == "Edit") || (!empty($DeleteLabel) && $DeleteLabel=='Yes')) {    
    $sql = "SELECT vLabel, eAppType, lPage_id FROM " . $tbl_update_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    //$eAppType = $db_data[0]['eAppType'];

    $vLabel = $db_data[0]['vLabel'];
    $lPage_id = $db_data[0]['lPage_id'];

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
                    if(startsWith($all_databse_data_item['Database'], "prod_") || startsWith($all_databse_data_item['Database'], "kingx_pro_production")) {
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


        $all_tables_lng_arr = array();
        $all_tables_arr = array();
        $all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
        foreach ($all_tables as $all_tables_item) {
            $item = $all_tables_item["Tables_in_" . $all_databse_data_arr_item];
            $all_tables_arr[] = $item;
            if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
                $all_tables_lng_arr[] = $item;
            }
        }

        for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
            $table_name_tmp = $all_tables_lng_arr[$i];

            if (isDeliverAllLanguageTables($table_name_tmp) == false) {
                if(!empty($DeleteLabel) && $DeleteLabel=='Yes') {
                    $vLabel = $vDeleteLabel;
                    deleteLblValues($all_databse_data_arr_item, $table_name_tmp, $obj_current_connection);
                } else {
                    updateLblValues($all_databse_data_arr_item, $table_name_tmp, $obj_current_connection);
                }
            }
        }

        /*if (in_array("register_driver", $all_tables_arr)) {
            $obj_current_connection->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
        }

        if (in_array("register_user", $all_tables_arr)) {
            $obj_current_connection->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
        }

        if (in_array("company", $all_tables_arr)) {
            $obj_current_connection->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
        }*/


        $obj_current_connection->MySQLClose();
    }

    if (!empty($all_databse_data_opposite_arr) && scount($all_databse_data_opposite_arr) > 0) {

        for ($ik = 0; $ik < scount($all_databse_data_opposite_arr); $ik++) {
            $all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];

            $obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);

            $all_tables_lng_arr = array();
            $all_tables_arr = array();
            $all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
            foreach ($all_tables as $all_tables_item) {
                $item = $all_tables_item["Tables_in_" . $all_databse_data_opposite_arr_item['DB']];
                $all_tables_arr[] = $item;
                if (startsWith($item, "language_label") == true && startsWith($item, "language_label_other") == false && (in_array($item, $ALL_TABLES_MAIN) || $ALL_TABLES_MAIN_VAR==1)) {
                    $all_tables_lng_arr[] = $item;
                }
            }

            for ($i = 0; $i < scount($all_tables_lng_arr); $i++) {
                $table_name_tmp = $all_tables_lng_arr[$i];

                if (!isDeliverAllLanguageTables($table_name_tmp)) {
                    if(!empty($DeleteLabel) && $DeleteLabel=='Yes') {
                        $vLabel = $vDeleteLabel;
                        deleteLblValues($all_databse_data_opposite_arr_item['DB'], $table_name_tmp, $obj_opposite_connection);
                    } else {
                        updateLblValues($all_databse_data_opposite_arr_item['DB'], $table_name_tmp, $obj_opposite_connection);
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

    $oCache->flushData();
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
    <body class="padTop53">

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
                                <?php /*<input type="hidden" name="prod_kingx" value="<?= $_POST['prod_kingx'] ?>"> */?>

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
                                        <label>Label value for cubejek (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_cubejek" id="vValue_cubejek" value="<?php echo htmlspecialchars($vValue_cubejek, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for cubejek (English)" required>
                                        <h6>Note: Use <b>"Service Provider"</b> word instead of "Driver","Provider" or "Delivery Driver"</h6>
                                    </div>
                                    <div class="col-lg-6">
                                        <button type="button" class="btn btn-primary" id="copyToAll">Copy to All</button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Ride (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_ride" id="vValue_ride" value="<?php echo htmlspecialchars($vValue_ride, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Ride (English)" required>
                                        <h6>Note: Use <b>"Driver"</b> word instead of "Service Provider","Provider" or "Delivery Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Delivery (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_delivery" id="vValue_delivery" value="<?php echo htmlspecialchars($vValue_delivery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Delivery (English)" required>
                                        <h6>Note: Use <b>"Delivery Driver"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for UberX (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_uberx" id="vValue_uberx" value="<?php echo htmlspecialchars($vValue_uberx, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for UberX (English)" required>
                                        <h6>Note: Use <b>"Service Provider"</b> word instead of "Driver","Provider" or "Delivery Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Ride-Delivery (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_ride_delivery" id="vValue_ride_delivery" value="<?php echo htmlspecialchars($vValue_ride_delivery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Ride-Delivery (English)" required>
                                        <h6>Note: Use <b>"Delivery Driver"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for food(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_food" id="vValue_food" value="<?php echo htmlspecialchars($vValue_food, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Food(English)" required>
                                        <h6>Note: Use <b>"Delivery Driver"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for deliverall(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_deliverall" id="vValue_deliverall" value="<?php echo htmlspecialchars($vValue_deliverall, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for DeliverAll(English)" required>
                                        <h6>Note: Use <b>"Delivery Driver"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for deliveryking(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_deliveryking" id="vValue_deliveryking" value="<?php echo htmlspecialchars($vValue_deliveryking, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for DeliveryKing(English)" required>
                                        <h6>Note: Use <b>"Delivery Driver"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for carwash(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_carwash" id="vValue_carwash" value="<?php echo htmlspecialchars($vValue_carwash, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Carwash(English)" required>
                                        <h6>Note: Use <b>"Car Washer"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Homeclean(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_homeclean" id="vValue_homeclean" value="<?php echo htmlspecialchars($vValue_homeclean, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for HomeClean(English)" required>
                                        <h6>Note: Use <b>"Home Cleaner"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Beauty(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_beauty" id="vValue_beauty" value="<?php echo htmlspecialchars($vValue_beauty, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Beauty(English)" required>
                                        <h6>Note: Use <b>"Beautician"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for TowTruck(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_towtruck" id="vValue_towtruck" value="<?php echo htmlspecialchars($vValue_towtruck, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for TowTruck(English)" required>
                                        <h6>Note: Use <b>"Tow Truck Driver"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Massage(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_massage" id="vValue_massage" value="<?php echo htmlspecialchars($vValue_massage, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Massage(English)" required>
                                        <h6>Note: Use <b>"Masseur"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Sanitization(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_sanitization" id="vValue_sanitization" value="<?php echo htmlspecialchars($vValue_sanitization, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Sanitization(English)" required>
                                        <h6>Note: Use <b>"Sanitarian"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for CubeDocX(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_cubedocx" id="vValue_cubedocx" value="<?php echo htmlspecialchars($vValue_cubedocx, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Sanitization(English)" required>
                                        <h6>Note: Use <b>"Medical Expert"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for All in 1 Medical(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_medicalservice" id="vValue_medicalservice" value="<?php echo htmlspecialchars($vValue_medicalservice, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for All in 1 Medical(English)" required>
                                        <h6>Note: Use <b>"Medical Expert"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Maid (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control label-value" name="vValue_maid" id="vValue_maid" value="<?php echo htmlspecialchars($vValue_maid, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Maid (English)" required>
                                        <h6>Note: Use <b>"Maid"</b> word instead of "Service Provider","Provider" or "Driver"</h6>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label For<span class="red"> *</span></label>
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
                    if (vLabel === vLabel.toUpperCase()) {
                        var res_vLabel = vLabel.split("_");
                        for (i = 0; i < res_vLabel.length; i++) {
                            if (res_vLabel[i] == '') {
                                alert("Please add language label in proper format like 'LBL_LABEL_NAME', Don't merge more than one underscore");
                                return false;
                            }
                        }
                        var alphaExp = /[0-9]/;
                        if (vLabel.match(alphaExp)) {
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
            //alert(referrer);		
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            // referrer = "languages_action_multisystem_ks.php";
            referrer = <?= $CURRENT_FILE_NAME; ?>;
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });

    $('#copyToAll').click(function() {
        $('.label-value').val($('#vValue_cubejek').val());
    });
</script>