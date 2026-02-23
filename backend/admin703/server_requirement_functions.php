<?php
function safeExec($command, &$output = null, &$return_var = null)
{
    if (!function_exists('exec')) {
        return null;
    }

    // Check if exec is in disabled functions
    $disabled = explode(',', str_replace(' ', '', ini_get('disable_functions')));
    if (in_array('exec', $disabled)) {
        return null;
    }

    return exec($command, $output, $return_var);
}

function checkModSecurity() {
    ob_start();
    phpinfo(INFO_MODULES);
    $contents = ob_get_clean();
    $modSecurity = strpos($contents, 'mod_security');
    return $modSecurity;
}

function checkPostMaxSize() {
    $post_max_size = ini_get('post_max_size');
    $post_max_size = str_replace('M', '', $post_max_size);    
    return $post_max_size;
}

function checkUploadMaxFileSize() {
    $upload_max_filesize = ini_get('upload_max_filesize');
    $upload_max_filesize = str_replace('M', '', $upload_max_filesize);
    return $upload_max_filesize;
}

function checkSqlMode() {
    global $obj;
    $sql_mode = $obj->MySQLSelect("SELECT @@sql_mode");
    return $sql_mode[0]['@@sql_mode'];
}

function checkSqlCharset() {
    global $obj;
    $sql_mode = $obj->MySQLSelect("SELECT @@character_set_database, @@collation_database");
    $default_charset = mysqli_character_set_name($obj->GetConnection());
    if($default_charset == 'utf8') {
        return $default_charset;    
    } else {
        if($sql_mode[0]['@@character_set_database'] == 'utf8')
        {
            return $sql_mode[0]['@@character_set_database'];
        }   
    }
    return $default_charset;
}

function checkCurlExtension() {
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'https://www.google.com/']);
    $curl_resp = "";
    if (curl_exec($curl)) {
        $curl_resp = curl_exec($curl);
    }
    curl_close($curl);

    if(!empty($curl_resp)) {
        return true;
    }
    return false;
}

function checkPHPandMySqlTimeZone() {
    global $obj;
    $php_time_zone = date_default_timezone_get();
    $mysql_time_zone = $obj->MySQLSelect("SELECT @@system_time_zone");
    $mysql_time_zone = $mysql_time_zone[0]['@@system_time_zone'];

    if($php_time_zone == $mysql_time_zone) {
        return true;
    } else{
        return false;
    }
}

function getTimezoneOffset() {
    $phpTime = date('Y-m-d H:i:s');
    $timezone = new DateTimeZone(date_default_timezone_get());
    $offset = $timezone->getOffset(new DateTime($phpTime));
    $offsetHours = round(abs($offset)/3600);
    if($offsetHours >= 0) {
        $str_offset = "+0$offsetHours:00";    
    } else {
        $str_offset = "-0$offsetHours:00";
    }
    return $str_offset;
}

function checkSocketCluster() {
    global $tconfig;
    $sc_host = $tconfig["tsite_sc_host"];
    $sc_port = $tconfig["tsite_host_sc_port"];
    $sc_connection = @fsockopen($sc_host, $sc_port, $errno, $errstr, 5);
    return $sc_connection;
}

function checkOpenPort($host, $port) {
    $port = (int)$port;
    $connection = @fsockopen($host, $port, $errno, $errstr, 5);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    } else{
        return false;
    }
}

function check_innodb_file_per_table() {
    global $obj;
    $innodb_file_per_table = $obj->MySQLSelect("SELECT @@innodb_file_per_table");
    return $innodb_file_per_table[0]['@@innodb_file_per_table'];
}

function check_query_cache_type() {
    global $obj;
    $query_cache_type = $obj->MySQLSelect("SELECT @@query_cache_type");
    return $query_cache_type[0]['@@query_cache_type'];
}

function check_open_files_limit() {
    global $obj;
    $open_files_limit = $obj->MySQLSelect("SELECT @@open_files_limit");
    return $open_files_limit[0]['@@open_files_limit'];
}

function check_max_allowed_packet() {
    global $obj;
    $max_allowed_packet = $obj->MySQLSelect("SELECT @@max_allowed_packet");
    return $max_allowed_packet[0]['@@max_allowed_packet'];
}

function check_max_connections() {
    global $obj;
    $max_connections = $obj->MySQLSelect("SELECT @@max_connections");
    return $max_connections[0]['@@max_connections'];
}

function check_max_user_connections() {
    global $obj;
    $max_user_connections = $obj->MySQLSelect("SELECT @@max_user_connections");
    return $max_user_connections[0]['@@max_user_connections'];
}

function check_innodb_buffer_pool_size() {
    global $obj;
    $innodb_buffer_pool_size = $obj->MySQLSelect("SELECT @@innodb_buffer_pool_size/1024 as innodb_buffer_pool_size");
    return $innodb_buffer_pool_size[0]['innodb_buffer_pool_size'];
}

function checkHtaccess() {
    global $tconfig;
    stream_context_set_default( [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ]);
    $url = $tconfig['tsite_url']."sign-in";
    $headers = get_headers($url);
    if(isset($headers) && strpos($headers[0], '200') !== false) {
        return true;
    } else {
        return false;
    }
}

function checkForceHttps() {
    global $tconfig;
    $url = $tconfig['tsite_url_main_admin'].'server_details.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    if($result['REQUEST_SCHEME'] == "http") {
        return true;
    } else {
        return false;
    }
}

function checkMapAPIreplacementAvailable() {
    global $tconfig;
    $host = $tconfig["tsite_gmap_replacement_host"];
    $port = $tconfig["tsite_host_gmap_replacement_port"];
    $connection = @fsockopen($host, $port);
    return $connection;
}

function checkMapAPIService() {
    global $obj;

    $db_con = $obj->MySQLSelect("SELECT cn.vCountryCode,cn.vCountry,cn.tLatitude,cn.tLongitude from country cn inner join configurations c on c.vValue=cn.vCountryCode where c.vName='DEFAULT_COUNTRY_CODE_WEB'");
    $vCountry = $db_con[0]['vCountryCode'];
    $tLatitude = $db_con[0]['tLatitude'];
    $tLongitude = $db_con[0]['tLongitude'];
    $session_token = "Passenger_4_7899765332757";
    $search_address = $db_con[0]['vCountry']; // Country Name

    $returnValue = false;
    $language_code = $_SESSION['sess_lang'];
    // =========autocomplete
    $search_address = str_replace(' ', '+', $search_address);
    $params_autocomp = "?language_code=" . $language_code . "&search_query=" . $search_address . "&latitude=" . $tLatitude . "&longitude=" . $tLongitude . "&TSITE_DB=" . TSITE_DB . "&session_token=" . $session_token . "";
    $url_autocomplete = GOOGLE_API_REPLACEMENT_URL . "autocomplete" . $params_autocomp;
    // $response = json_encode(file_get_contents($url));
    $response_autocomp = curlCall($url_autocomplete);
    
    //$response_autocomp = json_decode(file_get_contents($url_autocomplete));
    $response_count_auto = scount($response_autocomp['data']);
    // =========geocode
    $params_geo_code = "?language_code=" . $language_code . "&latitude=" . $tLatitude . "&longitude=" . $tLongitude . "&TSITE_DB=" . TSITE_DB . "&session_token=" . $session_token . "";
    $url_geo_code = GOOGLE_API_REPLACEMENT_URL . "reversegeocode" . $params_geo_code;
    //$response_geo_code = json_decode(file_get_contents($url_geo_code));
    $response_geo_code = curlCall($url_geo_code);
    if (!empty($response_geo_code['address'])){
        $response_count_geo_code = 1;
    }
    // =========direction
    $waypoint0 = $tLatitude . "," . $tLongitude;
    $waypoint1 = $tLatitude . "," . $tLongitude;
    $params_direction = "?language_code=" . $language_code . "&source_latitude=" . $tLatitude . "&source_longitude=" . $tLongitude . "&dest_latitude=" . $tLatitude . "&dest_longitude=" . $tLongitude . "&TSITE_DB=" . TSITE_DB . "&session_token=" . $session_token . "&waypoint0=" . $waypoint0 . "&waypoint1=" . $waypoint1 . "";
    $url_direction = GOOGLE_API_REPLACEMENT_URL . "direction" . $params_direction;
    //$response_direction = json_decode(file_get_contents($url_direction));
    $response_direction = curlCall($url_direction);
    // echo "<pre>"; print_r($response_direction); exit;
    $response_count_direction = scount($response_direction['data']);
    // =========check in all condition

    if ($response_count_auto > 0 && $response_count_geo_code > 0 /*&& $response_count_direction > 0*/) {
        $returnValue = true;
    }

    return $returnValue;
}

function getSystemMemInfo() {       
    $data = explode("\n", file_get_contents("/proc/meminfo"));
    $data = array_filter($data);
    $meminfo = array();
    foreach ($data as $line) {
        list($key, $val) = explode(":", $line);
        $meminfo[$key] = trim($val);
    }
    return $meminfo;
}

function getDirectoriesList($directories) {
    global $tconfig;
    $all_directories = array();

    foreach ($directories as $directory) {
        if(stripos($directory, "domain_cert_files") === false) {
            $permission = substr(sprintf('%o', fileperms($tconfig['tpanel_path'].$directory)), -4);
            $all_directories['main_dirs'][] = array('path'=> '/'.$directory,'permission'=> $permission);
            $all_directories['sub_dirs'][] = array();

            $dir_path = $tconfig['tpanel_path'].$directory.'/*';
            $all_sub_directories = getSubDirectories($dir_path);

            foreach ($all_sub_directories as $sub_directory) {
                $permission = substr(sprintf('%o', fileperms($sub_directory)), -4);
                $all_directories['sub_dirs'][] = array('path'=> '/'.str_replace($tconfig['tpanel_path'], "", $sub_directory),'permission'    => $permission);
            }
        }
    }

    return $all_directories;
}

function getSubDirectories($dir) {
    $subDir = array();
    $directories = array_filter(glob($dir), 'is_dir');
    $subDir = array_merge($subDir, $directories);
    foreach ($directories as $directory) {
        if(stripos($directory, "domain_cert_files") === false) {
            $permission = substr(sprintf('%o', fileperms($directory)), -4);
            $subDir = array_merge($subDir, getSubDirectories($directory.'/*'));  
        }
    } 
    return $subDir;
}

function checkLanguageSetup($field) {
    global $obj,$oCache,$getSetupCacheData;
    //Added By HJ On 21-09-2020 For Store setup_info Data into Cache Start
    if(empty($getSetupCacheData) || scount($getSetupCacheData) == 0) {
        $setupInfoApcKey = md5("setup_info");
        $getSetupCacheData = $oCache->getData($setupInfoApcKey);
        if(!empty($getSetupCacheData) && scount($getSetupCacheData) > 0){
           $setup_info_data= $getSetupCacheData;
        }else{
            $setup_info_data= $obj->MySQLSelect("SELECT * FROM setup_info LIMIT 0,1");
            $setSetupCacheData = $oCache->setData($setupInfoApcKey, $setup_info_data);
        }
    } else {
        $setup_info_data= $getSetupCacheData;
    }
    //echo "<pre>";print_r($setup_info_data);die;
    //Added By HJ On 21-09-2020 For Store setup_info Data into Cache End
    //$setup_info_data = $obj->MySQLSelect("SELECT * FROM setup_info");
    $eLanguageFieldsSetup = $setup_info_data[0]['eLanguageFieldsSetup'];
    $eCurrencyFieldsSetup = $setup_info_data[0]['eCurrencyFieldsSetup'];
    $eLanguageLabelConversion = $setup_info_data[0]['eLanguageLabelConversion'];
    $eOtherTableValueConversion = $setup_info_data[0]['eOtherTableValueConversion'];

    if($field == "eLanguageFieldsSetup") {
        return ($eLanguageFieldsSetup == "Yes") ? true : false;
    } else if ($field == "eCurrencyFieldsSetup") {
        return ($eCurrencyFieldsSetup == "Yes") ? true : false;
    } else if ($field == "eLanguageLabelConversion") {
        return ($eLanguageLabelConversion == "Yes" && $eOtherTableValueConversion == "Yes") ? true : false;
    }
}

function checkSystemTypeCongiguration() {
    global $APP_TYPE, $parent_ufx_catid;
    if($APP_TYPE == "Ride-Delivery-UberX") {
        return ($parent_ufx_catid == 0) ? true : false;
    }
    return true;
}

function checkGhostScript() {
    $min_gs_version = 1.0;
    $retval = "";
    if(!function_exists('system')) {
        return false;
    }
    
    if ( $retval == 0 || shell_exec("gs --version") >= $min_gs_version) {
        return true;
    }
    return false;
}

function checkCurlVersion() {
    $curl_version = $http2 = false;
    if(checkCurlExtension()) {
        if(curl_version()['version_number'] >= 477952) {
            $curl_version = true;
        }
    }
    $curl_version = true;

    $output = array();
    $result = safeExec("curl -sI https://curl.se -o/dev/null -w '%{http_version}'", $output);

    // If exec is not available, assume http2 is supported (safe default)
    if($result === null) {
        $http2 = 1;
    } elseif(!empty($output) && $output[0] == 2) {
        $http2 = 1;
    }

    if($curl_version == false || $http2 == false) {
        return false;
    }
    return true;
}

function checkPhpPearPackage() {
    include_once 'System.php';
    return class_exists('System');
}

function checkffmpeg() {
    $output = array();
    $result = safeExec("ffmpeg -version", $output);

    // If exec is not available, return false (ffmpeg cannot be verified)
    if($result === null) {
        return false;
    }

    if(!empty($output)) {
        return true;
    }
    return false;
}

function checkIniFiles() {
    global $tconfig;
    $files = scandir($tconfig['tpanel_path']);
    $fileListArr = array();
    foreach ($files as $file) {
        if(!is_dir($file) && !in_array($file, ['.', '..'])) {
            $file_temp = explode(".", $file);
            $ext = $file_temp[scount($file_temp) - 1];
            if(strtolower($ext) == "ini") {
                $fileListArr[] = $file;
            }
        }
    }

    return $fileListArr;
}

function checkImagickHeicSupport() {
    ob_start();
    phpinfo(INFO_MODULES);
    $phpinfo = ob_get_clean();

    $imagick_support = false;
    if (extension_loaded('imagick')) {
        $imagick = new Imagick();
        $formats = $imagick->queryFormats();
        $imagick_support = in_array('HEIC', $formats);
    }

    if ($imagick_support) {
        return true;
    }

    return false;
}
?>