<?php 
include_once('common.php');
if(isset($_POST['filename'])){
    $filePATH = $tconfig["tsite_libraries_v"] . "datasetfiles/";
    $file_name = $_POST['filename'];

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
        header('Content-Type: application/octet-stream');
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
} else {
    header("location:Page-Not-Found");
    exit();
}
?>