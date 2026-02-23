<?php

include 'common.php';

$defaultCurrency = '';
$id = isset($_REQUEST['id']) ? $obj->Escape($_REQUEST['id']) : '';
$userType = isset($_REQUEST['user']) && $_REQUEST['user'] === 'driver' ? 'driver' : 'passenger';

if (!empty($id)) {
    $sql = "SELECT vCurrency FROM country WHERE vCountryCode = '$id'";
    $db_data = $obj->MySQLSelect($sql);

    if (!empty($db_data)) {
        $currencyCode = $db_data[0]['vCurrency'];
        $sql = "SELECT * FROM currency WHERE vName = '$currencyCode' AND eStatus = 'Active'";
        $edit_data = $obj->sql_query($sql);

        if (!empty($edit_data)) {
            $defaultCurrency = $currencyCode;
        }
    }
}

// Fallback para default se não tiver definido
if (empty($defaultCurrency)) {
    $sqldef = "SELECT * FROM currency WHERE eStatus = 'Active' AND eDefault = 'Yes' ORDER BY iDispOrder ASC";
    $db_defcurrency = $obj->MySQLSelect($sqldef);
    $defaultCurrency = !empty($db_defcurrency) ? $db_defcurrency[0]['vName'] : '';
}

$sql = "SELECT * FROM currency WHERE eStatus = 'Active' ORDER BY iDispOrder ASC";
$db_currency = $obj->MySQLSelect($sql);

$selectFieldName = $userType === 'driver' ? 'vCurrencyDriver' : 'vCurrencyPassenger';

?>
<label><?= htmlspecialchars($langage_lbl['LBL_SELECT_CURRENCY_SIGNUP']); ?></label>
<select name="<?= $selectFieldName; ?>" required>
    <?php foreach ($db_currency as $currency) { ?>
        <option value="<?= htmlspecialchars($currency['vName']); ?>"
            <?= ($defaultCurrency === $currency['vName']) ? 'selected' : ''; ?>>
            <?= htmlspecialchars($currency['vName']); ?>
        </option>
    <?php } ?>
</select>
