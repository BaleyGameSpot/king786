<?php 
include_once('../common.php');
$fileURL = $tconfig["tsite_upload_files_db_backup"];
$filePATH = $tconfig["tsite_upload_files_db_backup_path"];
$file_name = $_REQUEST['file'];

$tmp = explode(".", $file_name);
for ($i = 0; $i < scount($tmp) - 1; $i++) {
    $tmp1[] = $tmp[$i];
}
$file = implode("_", $tmp1);
$ext = $tmp[scount($tmp) - 1];
$vaildExt = "sql";
$vaildExt_arr = explode(",", strtoupper($vaildExt));

if (in_array(strtoupper($ext), $vaildExt_arr)) {
	$file_url = $filePATH.$file_name;
	$filesize = filesize($filePATH.$file_name);
	header('Content-Description: File Transfer');
	header('Content-Type: application/download');
	header('Content-Disposition: attachment; filename='.basename($file_url));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . $filesize);
	ob_clean();
	ob_end_flush(); 
	flush();
	readfile($file_url);
	exit;
}
else {
	$_SESSION['success'] = 3;
	$_SESSION['var_msg'] = "Unable to download file. File is invalid.";
	header('Location: ' . $tconfig['tsite_url_main_admin'] . 'backup.php');
	exit;
}

?>