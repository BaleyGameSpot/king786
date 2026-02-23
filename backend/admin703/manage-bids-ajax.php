<?php
include_once('../common.php');
$iDriverId = isset($_REQUEST['iDriverId']) ? $_REQUEST['iDriverId'] : '';

$sql = "select iBiddingDriverServiceId from bidding_driver_service where iDriverId = '" . $iDriverId . "'";
$db_drv_veh = $obj->MySQLSelect($sql);

$id = isset($_POST['id']) ? $_POST['id'] : $db_drv_veh[0]['iBiddingDriverServiceId'];
$action = ($id != '') ? 'Edit' : 'Add';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$redirectToDocumentPage = isset($_POST['redirectToDocumentPage']) ? $_POST['redirectToDocumentPage'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$search_keyword = isset($_REQUEST['SearchBids']) ? $_REQUEST['SearchBids'] : 0;
$isAjax = isset($_REQUEST['isAjax']) ? $_REQUEST['isAjax'] : 'No';

if ($action == 'Edit') {
	$biddingdriverservice = $BIDDING_OBJ->biddingDriverService('webservice', $iDriverId);
    $selectedbiddingdriverservice = explode(',', $biddingdriverservice[0]['vBiddingId']);
}

$lang = $_SESSION['sess_lang'];
if ($lang == "" || $lang == NULL) {
    $lang = $LANG_OBJ->FetchDefaultLangData("vCode");
}
$reqArr = ['vCategory', 'iBiddingId'];
$biddingServices = $BIDDING_OBJ->getBiddingMaster('webservice', '', '', '', $lang, '', $reqArr);

if (scount($biddingServices) > 0) {
    $reqArr = ['vTitle', 'iBiddingId'];
    for ($i = 0; $i < scount($biddingServices); $i++) {
        if ($biddingServices[$i]['iBiddingId'] != $BIDDING_OBJ->other_id) {
            $SubCategory = $BIDDING_OBJ->getBiddingSubCategory('webservice', $biddingServices[$i]['iBiddingId'], '', '', '', $lang, '', $reqArr);
            $biddingServices[$i]['SubCategory'] = $SubCategory;
        }
    }
}

/* --------------------------------- search --------------------------------- */
if (!empty($search_keyword)) {
    foreach ($biddingServices as $key => $value) {
        $main_cat = $subcat = 0;
        if (stripos($value['vCategory'], $search_keyword) !== false) {
            $main_cat = 1;
        }

        if (isset($value['SubCategory']) && $main_cat == 0) {
            foreach ($value['SubCategory'] as $skey => $sCategory) {
                if (stripos($sCategory['vTitle'], $search_keyword) !== false) {
                    $subcat = 1;
                } else {
                    unset($biddingServices[$key]['SubCategory'][$skey]);
                }
            }

            if (!empty($biddingServices[$key]['SubCategory'])) {
                $biddingServices[$key]['SubCategory'] = array_values($biddingServices[$key]['SubCategory']);
            }
        }

        if (($main_cat == 0 && $subcat == 0) || empty($biddingServices[$key]['SubCategory'])) {
            unset($biddingServices[$key]);
        }
    }
}
/* --------------------------------- search --------------------------------- */
$biddingServices = array_values($biddingServices);

?>

    <ul style="padding-left: 0;">
    <?php foreach ($biddingServices as $key => $value) { ?>
        <div class="main-cat">
            <?php echo $value['vCategory']; ?>
        </div>
    
        <fieldset>
        <?php foreach ($value['SubCategory'] as $SubCategoryval) { ?>
            <li style="list-style: outside none none;">
                <b><?php echo $SubCategoryval['vTitle']; ?></b>
                <div class="make-switch" data-on="success" data-off="warning">
                    <input type="checkbox" class="chk vCarTypeClass" name="selectedbiddingdriverservice[]" id="selectedbiddingdriverservice<?= $SubCategoryval['iBiddingId'] ?>"  <?php if (in_array($SubCategoryval['iBiddingId'], $selectedbiddingdriverservice)) { ?>checked<?php } ?> value="<?= $SubCategoryval['iBiddingId'] ?>" />
                </div>
            </li>
        <? } ?>
        </fieldset>
    <?php } ?>
    </ul>

<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>