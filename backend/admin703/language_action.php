<?php
include_once('../common.php');

unset($_POST['dataTables-example_length']);

unset($_POST['submit']);


$iLanguageMasId = $_REQUEST['iLanguageMasId'];

$eStatus = $_REQUEST['status'];

$iDispOrder = $_REQUEST['iDispOrder'];

if (SITE_TYPE == 'Demo') {

    header("location:language.php?success=2");

    exit;

} else {

    $str = "UPDATE language_master SET eStatus = '" . $eStatus . "' WHERE iLanguageMasId='" . $iLanguageMasId."'";
    $db_update = $obj->sql_query($str);

    $query = "UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1";

    $obj->sql_query($query);



    $query1 = "UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1";

    $obj->sql_query($query1);



    $query1 = "UPDATE company SET eChangeLang = 'Yes' WHERE 1=1";

    $obj->sql_query($query1);
    
    if(!empty($OPTIMIZE_DATA_OBJ)) {
        $OPTIMIZE_DATA_OBJ->ExecuteMethod('loadStaticInfo');
    }

    updateSystemData();
    

    $siteUrl = $tconfig['tsite_url']."".SITE_ADMIN_URL."/language.php?success=1&reload";

    ?>

    <script>window.location.replace("<?php echo $siteUrl; ?>");</script>

<?php } ?>