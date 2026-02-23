<?php
include_once('../common.php');
$doc_type = isset($_REQUEST['doc_type']) ? $_REQUEST['doc_type'] : '';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';

$cmpss = "";
if($doc_type != "") {
	$cmpss = " AND doc_usertype = '".$doc_type."' ";
	$sql = "SELECT COUNT(doc_masterid) AS Total FROM document_master WHERE 1=1 $cmpss";
	$db_count = $obj->MySQLSelect($sql);
	$count = $db_count[0]['Total'];
} 

$newCnt = $count+1;
$totalVal = $count+1;
		
if($id != "") {
	$sql = "SELECT iDisplayOrder FROM document_master WHERE 1=1 AND doc_masterid = '".$id."' $cmpss";
	$db_old = $obj->MySQLSelect($sql);
	if(!empty($db_old)) {
		$newCnt = $count;
		$iDisplayOrder_db = $db_old[0]['iDisplayOrder'];
	}
}
		
?>
<input type="hidden" name="temp_order" id="temp_order" value="<?= ($action == 'Edit') ? $totalVal : '1'; ?>">
<?php  $display_numbers = $totalVal; ?>
<select name="iDisplayOrder" class="form-control">
    <?php for ($i = 1; $i <= $display_numbers; $i++) { ?>
        <option value="<?= $i ?>" <?php
        if ($i == $iDisplayOrder_db) {
            echo "selected";
        }
        ?>> -- <?= $i ?> --</option>
            <?php } ?>
</select> 

<?php exit();