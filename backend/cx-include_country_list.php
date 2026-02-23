<?php
include_once("common.php");
$sqlC = "SELECT vCountryCode,vPhoneCode,vCountry from country where eStatus='Active'";
$AllcountryArry = $obj->MySQLSelect($sqlC);
?>
<select name="vCode" id="vCode" class=" form-control">
    <?php foreach ($AllcountryArry as $Rows) { ?>
        <option
           <!-- --><?php /*if ($Rows['vCountryCode'] == $defaultcountryArry[0]['vValue']) {
                $htmldropdown .= 'selected="selected"';
            } */?>
                value="<?php echo $Rows["vCountryCode"]; ?>" data-code="<?php echo $Rows["vPhoneCode"]; ?>" data-country="<?php echo $Rows["vCountryCode"]; ?>"><?php echo $Rows["vCountry"]; ?>
            (<?php echo $Rows["vPhoneCode"]; ?>)
        </option>
    <?php } ?>
</select>
