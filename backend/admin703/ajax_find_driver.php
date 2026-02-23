<?

include_once("../common.php");




$iCompanyId = isset($_REQUEST['company']) ? $_REQUEST['company'] : '';
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';
$selected = "selected";
$cont = '';
$cont .= '<select class="validate[required] form-control" id="driverNo" name="iDriverId">';
$cont .= '<option value="">CHOOSE DRIVER </option>';
if ($iCompanyId != '') {
    $sql = "select iDriverId,vName,vLastName,vEmail from register_driver where iCompanyId = '" . $iCompanyId . "' and eStatus != 'Deleted' order by vName ASC";
    $db_model = $obj->MySQLSelect($sql);

    for ($i = 0; $i < scount($db_model); $i++) {
        if ($db_model[$i]['iDriverId'] == $iDriverId){
            $cont .= '<option value="' . $db_model[$i]['iDriverId'] . '"  ' . $selected . '>' . clearName($db_model[$i]['vName'] . ' ' . $db_model[$i]['vLastName']) . ' ('. clearEmail($db_model[$i]['vEmail']) .')</option>';
        } else {
            if($db_model[$i]['vEmail'] != ''){
                $concatemail = ' ('. clearEmail($db_model[$i]['vEmail']) .')';
            } else {
                $concatemail = '';
            }
            $cont .= '<option value="' . $db_model[$i]['iDriverId'] . '">' . clearName($db_model[$i]['vName'] . ' ' . $db_model[$i]['vLastName']) . $concatemail .'</option>';
        }
    }
}
$cont .= '</select>';
echo $cont;
exit;
?>