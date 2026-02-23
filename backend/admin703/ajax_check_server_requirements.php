<?php 
include_once('../common.php');
include_once('server_requirement_functions.php');


$AUTH_OBJ->checkMemberAuthentication();

session_write_close();
$tProjectData = $setupData = $obj->MySQLSelect("SELECT * FROM setup_info");
$lAddOnConfiguration_obj = json_decode($tProjectData[0]['lAddOnConfiguration'], true);
$tProjectPortData = GetFileData($tconfig['tsite_script_file_path'] . 'port_data/services_config.json');
$tProjectPortData = json_decode($tProjectPortData, true);

$SHOW_ALL_MISSING = (isset($_POST['SHOW_ALL_MISSING']) && $_POST['SHOW_ALL_MISSING'] == "Yes") ? "Yes" : "No";

if(isset($_POST['server_requirement']) && $_POST['server_requirement'] != "")
{
	$server_requirement = $_POST['server_requirement'];

	if($server_requirement == "server_settings")
	{
		$server_settings = array(
	        'PHP Version = 7.1'                 => (version_compare(PHP_VERSION, '7.1', '>=')) ? 1 : 0,    
	        'Mod Security (Must be "Off")'      => (checkModSecurity() == false) ? 1 : 0,
	        '.htaccess Support'                 => (is_readable($tconfig['tpanel_path'].'.htaccess') && checkHtaccess()) ? 1 : 0,
	        'MYSQL localhost server connection' => (stripos(TSITE_SERVER, 'localhost') !== false) ? 1 : 0,
	        'Nginx (Must be "Disabled")'        => (stripos($_SERVER["SERVER_SOFTWARE"], 'nginx') == false) ? 1 : 0,
	        'Force HTTPS (Must be "Disabled")'  => (checkForceHttps()) ? 1 : 0,
	        'Ghostscript'                    	=> (checkGhostScript()) ? 1 : 0,
	    );

	    $server_settings['PHP-Pear Package'] = checkPhpPearPackage() ? 1 : 0;

		$server_settings_status = 1;
	    foreach ($server_settings as $srkey => $server_setting) 
	    {
	    	if($SHOW_ALL_MISSING == "Yes")
	        {
	            $server_settings[$srkey] = 0;       
	        }

	        if($server_setting == 0 || $SHOW_ALL_MISSING == "Yes")
	        {
	            $server_settings_status = 0;
	        }
	    }

	    $server_requirement_status = $server_settings_status;
	}

	else if ($server_requirement == "phpini_settings") {
		$php_ini_settings = array(
	        'zlib.output_compression (Must be "On")'    => (ini_get('zlib.output_compression') == "On") ? 1 : 0,
	        'post_max_size >= 900MB'                    => (checkPostMaxSize() >= 900) ? 1 : 0,
	        'upload_max_filesize >= 900MB'              => (checkUploadMaxFileSize() >= 900) ? 1 : 0,
	        'max_execution_time = 0'                 	=> (ini_get('max_execution_time') <= 0) ? 1 : 0,
	        'max_input_time = 0'                     	=> (ini_get('max_input_time') <= 0) ? 1 : 0,
	        'memory_limit = -1'                         => (ini_get('memory_limit') == -1) ? 1 : 0,
	        'allow_url_fopen (Must be "On")'            => (ini_get('allow_url_fopen') == "On") ? 1 : 0,
	        'max_file_uploads >= 20'                    => (ini_get('max_file_uploads') >= 20) ? 1 : 0,
	        'short_open_tag (Must be "On")'             => (ini_get('short_open_tag') == "On") ? 1 : 0,
	        'zend.enable_gc (Must be "On")'             => (ini_get('zend.enable_gc') == "On") ? 1 : 0,
	        'max_input_vars >= 10000'                   => (ini_get('max_input_vars') >= 10000) ? 1 : 0,
	        'default_charset = UTF-8'                   => (ini_get('default_charset') == "UTF-8") ? 1 : 0,
	    );

	    $phpini_settings_status = 1;
	    foreach ($php_ini_settings as $pskey => $ini_setting) 
	    {
	    	if($SHOW_ALL_MISSING == "Yes")
	        {
	            $php_ini_settings[$pskey] = 0;       
	        }

	        if($ini_setting == 0 || $SHOW_ALL_MISSING == "Yes")
	        {
	            $phpini_settings_status = 0;
	        }
	    }
	    $server_requirement_status = $phpini_settings_status;
	}
	else if ($server_requirement == "php_modules") {
		$extensions = get_loaded_extensions();
	    $php_extensions = array(
	        'exif'              => (in_array('exif', $extensions)) ? 1 : 0,
	        'mbstring'          => (in_array('mbstring', $extensions)) ? 1 : 0,
	        'curl'  			=> (in_array('curl', $extensions) && checkCurlVersion()) ? 1 : 0,
	        'gd'                => (in_array('gd', $extensions)) ? 1 : 0, 
	        'ionCube Loader'    => (in_array('ionCube Loader', $extensions)) ? 1 : 0, 
	        'mysqli'            => (in_array('mysqli', $extensions)) ? 1 : 0, 
	        'dom'               => (in_array('dom', $extensions)) ? 1 : 0,
	        'fileinfo'          => (in_array('fileinfo', $extensions)) ? 1 : 0,
	        'ctype'             => (in_array('ctype', $extensions)) ? 1 : 0, 
	        'gettext'           => (in_array('gettext', $extensions)) ? 1 : 0, 
	        'hash'              => (in_array('hash', $extensions)) ? 1 : 0, 
	        'json'              => (in_array('json', $extensions)) ? 1 : 0, 
	        'libxml'            => (in_array('libxml', $extensions)) ? 1 : 0, 
	        'mcrypt'            => (in_array('mcrypt', $extensions) ? 1 : (version_compare(PHP_VERSION, '7.2', '>=') ? 1 : 0)),
	        'mysqlnd'           => (in_array('mysqlnd', $extensions)) ? 1 : 0, 
	        'openssl'           => (in_array('openssl', $extensions)) ? 1 : 0, 
	        'sockets'           => (in_array('sockets', $extensions)) ? 1 : 0, 
	        'zlib'              => (in_array('zlib', $extensions)) ? 1 : 0, 
	        'soap'              => (in_array('soap', $extensions)) ? 1 : 0, 
	        'memcache-4.0.5.2'  => (in_array('memcache', $extensions)) ? 1 : 0, 
	        'mongodb'           => (in_array('mongodb', $extensions)) ? 1 : 0, 
	        'imagick'           => (in_array('imagick', $extensions)) ? 1 : 0, 
	        'ffmpeg'            => (checkffmpeg()) ? 1 : 0, 
	        // 'apcu'              => (in_array('apcu', $extensions)) ? 1 : 0, 
	    );

	    $php_extensions_status = 1;
	    foreach ($php_extensions as $pekey => $extension) 
	    {
	    	if($SHOW_ALL_MISSING == "Yes")
	        {
	            $php_extensions[$pekey] = 0;       
	        }

	        if($extension == 0 || $SHOW_ALL_MISSING == "Yes")
	        {
	            $php_extensions_status = 0;
	        }
	    }
	    $server_requirement_status = $php_extensions_status;
	}
	else if ($server_requirement == "mysql_settings") {
		$mysql_settings = array(
			'default_charset = UTF-8'				=> (checkSqlCharset() == "utf8") ? 1 : 0,
	        'sql_mode = NO_ENGINE_SUBSTITUTION'     => (stripos(checkSqlMode(), "NO_ENGINE_SUBSTITUTION") !== false) ? 1 : 0,
	        'mysql strict mode (Must be "Off")'     => (stripos(checkSqlMode(), "STRICT")  !== false) ? 0 : 1,
	        'innodb_file_per_table (Must be "On")'  => (check_innodb_file_per_table() == 1) ? 1 : 0,
	        'query_cache_type = 0'                  => (check_query_cache_type() == 0 || check_query_cache_type() == "OFF") ? 1 : 0,
	        'open_files_limit >= 10000'             => (check_open_files_limit() >= 10000) ? 1 : 0,
	        'max_allowed_packet >= 256MB'           => (check_max_allowed_packet() >= 268435456) ? 1 : 0,
	        'max_user_connections >= 250'           => (check_max_user_connections() >= 250 || check_max_user_connections() == 0) ? 1 : 0,
	    );

	    $mysql_settings_status = 1;
	    foreach ($mysql_settings as $mskey => $mysql_setting) 
	    {
	    	if($SHOW_ALL_MISSING == "Yes")
	        {
	            $mysql_settings[$mskey] = 0;       
	        }

	        if($mysql_setting == 0 || $SHOW_ALL_MISSING == "Yes")
	        {
	            $mysql_settings_status = 0;
	        }
	    }
	    $server_requirement_status = $mysql_settings_status;
	}
	else if ($server_requirement == "server_ports") {
		$pStatus = 0;
	    $ports = array(2195);
		$ports_list = array();
		foreach ($ports as $port) {
		    $host = $_SERVER['HTTP_HOST'];
		    if($port == 2195) {
		        $host = 'gateway.push.apple.com';
		    }
		    $ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);
		}

		$port_data_key = rtrim($_SERVER['HTTP_HOST'] . $tconfig['tsite_folder'], "/");
		$port_data = $tProjectPortData[$port_data_key];

		$socket_cluster_status_html = '<br><a href="' . $tconfig['tsite_url_main_admin'] . 'sc_diagnostics.php?time='. time() . '" target="_blank">Click here to confirm that socket cluster is working.</a>';
		if(isset($port_data['SOCKET_CLUSTER_PORT']) && $port_data['SOCKET_CLUSTER_PORT'] != "") {
		    $host = API_SERVICE_DOMAIN;
		    $port = $port_data['SOCKET_CLUSTER_PORT'];
		    $port_html = '<span>' . $port . $socket_cluster_status_html .'</span>';
		} else {
		    $host = API_SERVICE_DOMAIN;
		    $port = $tconfig['tsite_host_sc_port'];
		    $port_html = '<span>' . $port . $socket_cluster_status_html .'</span>';
		}
		$ports_list[$port_html] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);

		if(isset($port_data['SOCKET_PHP_CLIENT_PORT']) && $port_data['SOCKET_PHP_CLIENT_PORT'] != "") {
		    $host = API_SERVICE_DOMAIN;
		    $port = $port_data['SOCKET_PHP_CLIENT_PORT'];
		} else {
		    $host = API_SERVICE_DOMAIN;
		    $port = $tconfig['tsite_host_sc_php_port'];
		}
		$ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);

		if(isset($port_data['APP_SERVICE_PORT']) && $port_data['APP_SERVICE_PORT'] != "") {
		    $host = API_SERVICE_DOMAIN;
		    $port = $port_data['APP_SERVICE_PORT'];
		} else {
		    $host = API_SERVICE_DOMAIN;
		    $port = $tconfig['tsite_host_app_service_port'];
		}
		$ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0) ;

		$lAddOnConfiguration_obj = json_decode($setupData[0]['lAddOnConfiguration'], true);
		if(!empty($lAddOnConfiguration_obj['GOOGLE_PLAN'])) {
		    if(isset($port_data['ADMIN_MONGO_PORT']) && $port_data['ADMIN_MONGO_PORT'] != "") {
		        $host = API_SERVICE_DOMAIN;
		        $port = $port_data['ADMIN_MONGO_PORT'];
		    } else {
		        $host = API_SERVICE_DOMAIN;
		        $port = $tconfig['tmongodb_port'];
		    }
		    $ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);

		    if(isset($port_data['MAPS_API_SERVICE_PORT']) && $port_data['MAPS_API_SERVICE_PORT'] != "") {
		        $host = API_SERVICE_DOMAIN;
		        $port = $port_data['MAPS_API_SERVICE_PORT'];
		    } else {
		        $host = API_SERVICE_DOMAIN;
		        $port = $tconfig['tsite_host_gmap_replacement_port'];
		    }
		    $ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);
		}

		if(isset($port_data['WRTC_PORT']) && $port_data['WRTC_PORT'] != "") {
	        $host = API_SERVICE_DOMAIN;
	        $port = $port_data['WRTC_PORT'];
	    } else {
	        $host = API_SERVICE_DOMAIN;
	        $port = $tconfig['tsite_webrtc_port'];
	    }
	    $ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);

		if(isset($tconfig['tsite_webrtc_stun_port']) && $tconfig['tsite_webrtc_stun_port'] != "") {
			$host = $tconfig["tsite_webrtc_stun_host"];
		    $port = $tconfig['tsite_webrtc_stun_port'];
		    $port_status = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);
		    $ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);
		}

		if(isset($tconfig['tsite_webrtc_turn_port']) && $tconfig['tsite_webrtc_turn_port'] != "" && $tconfig['tsite_webrtc_turn_port'] != $tconfig['tsite_webrtc_stun_port']) {
			$host = $tconfig["tsite_webrtc_turn_host"];
		    $port = $tconfig['tsite_webrtc_turn_port'];
		    $port_status = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);
		    $ports_list[$port] = $pStatus ? 1 : (checkOpenPort($host, $port) ? 1 : 0);
		}

		$server_requirement_status = 1;
	    $ports_content_html = "";
	    $all_ports_content_html = "";
	    foreach ($ports_list as $plkey => $port1) 
	    {
	        $ports_status = $ports_list[$plkey];
	        if($ports_status == 0 || $SHOW_ALL_MISSING == "Yes") {
	        	if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	        		$ports_content_html .= '<li class="list-group-item d-flex justify-content-between align-items-center">' . $plkey . '<span class="status-icon-danger"><i class="fa fa-times"></i></span></li>';		
	        	}

	        	$server_requirement_status = 0;
	        }

	        $ports_content_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>' . $plkey . '</span>' . ($ports_status == 0 ? '<span class="icon server-status-icon pending-color"><i class="ri-alert-line"></i></span>' : '<span class="icon server-status-icon success-color"><i class="ri-check-line"></i></span>') . '</li>';	

	        $all_ports_content_html .= '<li class="list-group-item d-flex justify-content-between align-items-center">' . $plkey;
	        if($ports_status == 0 || $SHOW_ALL_MISSING == "Yes") {
	        	if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	        		$all_ports_content_html .= '<span class="status-icon-danger"><i class="fa fa-times"></i></span>';
	        	} else {
	        		$all_ports_content_html .= '<span class="icon server-status-icon pending-color"><i class="ri-alert-line"></i></span>';
	        	}
	        }
	        else {
	        	if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	        		$all_ports_content_html .= '<span class="status-icon-success"><i class="fa fa-check"></i></span>';
	        	} else {
	        		$all_ports_content_html .= '<span class="icon server-status-icon success-color"><i class="ri-check-line"></i></span>';
	        	}
	        }
	        $all_ports_content_html .= '</li>';
	    }
	    
	    $returnArr['server_requirement_html'] = $ports_content_html;
	    $returnArr['all_ports_html'] = $all_ports_content_html;
	}
	else if ($server_requirement == "cron_jobs_status") {
		$cron_last_executed = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_jobs_last_executed.txt');

		$cron_status = GetFileData($tconfig['tsite_script_file_path'] . 'system_cron_jobs_status.txt');
		$server_requirement_status = 1;
		if(round(((strtotime(date('Y-m-d H:i:s')) - strtotime($cron_last_executed)) / 60), 2) >= 5 || $cron_status == "error")
		{
			$server_requirement_status = 0;
		}

		if($SHOW_ALL_MISSING == "Yes")
        {
            $server_requirement_status = 0;  
        }
	}
	else if ($server_requirement == "mysql_suggestions") {
		$memory_info = getSystemMemInfo();
	    $MemTotal = trim(str_replace(["kb", "kB", "Kb", "KB"], "", $memory_info['MemTotal']));
	    
	    $other_params1 = 200;
	    $other_params2 = 5;
	    $innodb_buffer_pool_size_value1 = (0.4 * $MemTotal);
	    $innodb_buffer_pool_size_value2 = (0.5 * $MemTotal);

	    $memtotal1 = (0.6 * $MemTotal) / 1024;
	    $memtotal2 = (0.65 * $MemTotal) / 1024;

	    $max_connections1 = ($memtotal1 - ($other_params1 + ($innodb_buffer_pool_size_value2 / 1024))) / $other_params2;
	    $max_connections1 = round($max_connections1);
	    $max_connections2 = ($memtotal2 - ($other_params1 + ($innodb_buffer_pool_size_value2 / 1024))) / $other_params2;
	    $max_connections2 = round($max_connections2);

	    $server_requirement_status = 0;
	    if((check_innodb_buffer_pool_size() >= $innodb_buffer_pool_size_value1 && check_innodb_buffer_pool_size() <= $innodb_buffer_pool_size_value2) || (check_innodb_buffer_pool_size() >= $innodb_buffer_pool_size_value2) && $SHOW_ALL_MISSING != 'Yes')
	    {
	        $server_requirement_status_alt1 = 1;
	    }
	    else {
	        $server_requirement_status_alt1 = 0;
	    }

	    if(((check_max_connections() >= $max_connections1 && check_max_connections() <= $max_connections2) || check_max_connections() >= $max_connections2) && $SHOW_ALL_MISSING != 'Yes')
	    {
	       $server_requirement_status_alt2 = 1; 
	    }
	    else {
	    	$server_requirement_status_alt2 = 0;	
	    }

	    if($server_requirement_status_alt1 == 0 || $server_requirement_status_alt2 == 0 || $SHOW_ALL_MISSING == 'Yes')
	    {
	    	$server_requirement_status = 0;
	    }
	    else {
	    	$server_requirement_status = 1;
	    }
	}
	else if ($server_requirement == "folder_permissions") {
		$directories = array('webimages', 'assets/img');
	    $all_directories = getDirectoriesList($directories);

	    $directory_permissions = array();
	    $server_requirement_status = 1;
	    foreach ($all_directories as $dkey => $directories) 
	    {
	    	foreach ($directories as $directory) 
	    	{
	    		if($directory['permission'] != '0777' && $directory['permission'] != '0755')
		        {
		            $server_requirement_status = 0;
		            if($dkey == "sub_dirs") {
		                $dir_path = explode('/', $directory['path']);
		                array_pop($dir_path);
		                $dir_path = implode('/', $dir_path);
		                if(!isset($directory_permissions[$dir_path]))
		                {
		                    // $base_path = preg_replace('~/+~', '/', $tconfig['tpanel_path'].$dir_path);
		                    $base_path = preg_replace('~/+~', '/', $tconfig['tpanel_path'].$dir_path);
		                    $directory_permissions['sub_dirs'][$dir_path] = $directory['permission'];
		                }
		            } else {
		                $directory_permissions['main_dirs'][] = $directory;
		            }
		        }
	    	}
	    }

	    if($SHOW_ALL_MISSING == 'Yes')
	    {
	    	$server_requirement_status = 0;
	    }
	    $folder_permissions_html = "";
	    if(scount($directory_permissions) > 0) 
	    {
		    foreach ($directory_permissions['main_dirs'] as $dir_permission_main) 
		    {
		    	if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	            	$folder_permissions_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>Path: ' . $dir_permission_main['path'] . '<br><span style="color: #999999;">Current Permission: ' . $dir_permission_main['permission'] . '</span></span><span class="status-icon-danger"><i class="fa fa-times"></i></span></li>';
	            } else {
	            	$folder_permissions_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>Path: ' . $dir_permission_main['path'] . '<br><span style="color: #999999;">Current Permission: ' . $dir_permission_main['permission'] . '</span></span><span class="icon server-status-icon pending-color"><i class="ri-alert-line"></i></span></li>';
	            }
	        }
	        
	        $directory_permissions['sub_dirs'] = array_filter($directory_permissions['sub_dirs']);
	        if(scount($directory_permissions['sub_dirs']) > 0) 
	        {
	        	if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	        		$folder_permissions_html .= '<li class="list-group-item"><span class="w-100 pull-left" style="margin-bottom: 5px"><strong>Subfolder permissions missing </strong><span class="status-icon-danger pull-right"><i class="fa fa-times"></i></span></span><span class="w-100">';
	        	} else {
	        		$folder_permissions_html .= '<li class="list-group-item"><span class="w-100 pull-left" style="margin-bottom: 5px"><strong>Subfolder permissions missing </strong><span class="icon server-status-icon pending-color"><i class="ri-alert-line"></i></span></span><span class="w-100">';
	        	}

	        	foreach ($directory_permissions['sub_dirs'] as $subdirkey => $dir_permission_sub) 
	        	{
	                $folder_permissions_html .= '<hr class="w-100 pull-left"><span><span style="word-break: break-all;">Path: ' . $subdirkey . '</span><br><span style="color: #999999;">Current Permission: ' . $dir_permission_sub . '</span></span><br>';
	            }
	            
	            $folder_permissions_html .= '</span></li>';
	        }
	    }
	    else {
	    	if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	    		$folder_permissions_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>All set correctly</span><span class="status-icon-success"><i class="fa fa-check"></i></span></li>';
	    	} else {
	    		$folder_permissions_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>All set correctly</span><span class="icon server-status-icon success-color"><i class="ri-check-line"></i></span></li>';
	    	}
	    }
		
		if(empty($folder_permissions_html)) {
			$server_requirement_status = 1;

			if(!$MODULES_OBJ->isEnableAdminPanelV2()) {
	    		$folder_permissions_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>All set correctly</span><span class="status-icon-success"><i class="fa fa-check"></i></span></li>';
	    	} else {
	    		$folder_permissions_html .= '<li class="list-group-item d-flex justify-content-between align-items-center"><span>All set correctly</span><span class="icon server-status-icon success-color"><i class="ri-check-line"></i></span></li>';
	    	}
			
		}
        $returnArr['server_requirement_html'] = $folder_permissions_html;
	}
	else if($server_requirement == "system_settings")
	{
		$system_settings = array(
	        'Language Set Up'       		=> checkLanguageSetup('eLanguageFieldsSetup') ? 1 : 0,
	        'Language Conversion'   		=> checkLanguageSetup('eLanguageLabelConversion') ? 1 : 0,
	        'Currency Setup'   				=> checkLanguageSetup('eCurrencyFieldsSetup') ? 1 : 0,
	        'System Type configurations'    => checkSystemTypeCongiguration() ? 1 : 0
	    );

	    $system_settings_status = 1;
	    foreach ($system_settings as $sskey => $sys_setting) 
	    {
	    	if($SHOW_ALL_MISSING == "Yes")
	        {
	            $system_settings[$sskey] = 0;       
	        }

	        if($sys_setting == 0 || $SHOW_ALL_MISSING == "Yes")
	        {
	            $system_settings_status = 0;
	        }
	    }
	    $server_requirement_status = $system_settings_status;
	}

	$returnArr['Action'] = $server_requirement_status;
	echo json_encode($returnArr);
	exit();
}

else{
	return; // Added By NM on 25/8 after confirm with Hemant
	exit();
}
?>