<?php 
	include_once('common.php');
	$id = isset($_REQUEST['id'])?$_REQUEST['id']:'';
    $sql = "SELECT * FROM register_driver where iDriverId='$id'";
    $data_drv = $obj->MySQLSelect($sql);
	
?>

<span><b>Email: </b><?= clearEmail($data_drv[0]['vEmail']);?></span>
<br>
<span><b>Phone Number: </b>(<?= $data_drv[0]['vCode'];?>) <?= clearPhone($data_drv[0]['vPhone']);?></span>

