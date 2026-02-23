<?php
include_once('common.php');

$AUTH_OBJ->checkMemberAuthentication();
$abc = 'driver,company';
$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
setRole($abc, $url);

$iDriverId = isset($_REQUEST['iDriverId']) ? (trim($_REQUEST['iDriverId'])) : '';

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

$biddingdriverservice = $BIDDING_OBJ->biddingDriverService('webservice', $iDriverId);
$selectedbiddingdriverservice = explode(',', $biddingdriverservice[0]['vBiddingId']);

$biddingdriverrequest = $BIDDING_OBJ->biddingdriverrequest('webservice', $iDriverId);
$biddingdriverrequest = $BIDDING_OBJ->multiToSingle($biddingdriverrequest, 'iBiddingId');


// For Bidding Service
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
?>
                  
<div class="add-car-services-hatch add-services-hatch add-services-taxi">
    <input type="hidden" name="iDriverIdNew" value="<?= $iDriverId ?>" />
    <div class="card-block">
        <?php
        $emptySubCatData = '0';
        foreach ($biddingServices as $value1) {
            if (scount($value1['SubCategory']) > 0) { 
                $emptySubCatData = empty($value1['SubCategory']) ? '0' : '1 ';
            }
        ?>
            <div class="main-cat">
                <span><?= $value1['vCategory']; ?></span>
            </div>
            <div class="partation">
                <ul class="setings-list">
                    <?php foreach ($value1['SubCategory'] as $SubCategoryval) { 
                    $disStat = '';
                    if (in_array($SubCategoryval['iBiddingId'], $biddingdriverrequest)) {
                        $disStat = 'disabled';
                    } ?>
                        <li>
                            <div class="toggle-list-inner">
                                <div class="toggle-combo">
                                    <label><?php echo $SubCategoryval['vTitle']; ?>
                                    </label>
                                    <span class="toggle-switch">
                                        <input type="checkbox" id="selectedbiddingdriverservice<?= $SubCategoryval['iBiddingId'] ?>" class="chk vCarTypeClass" name="selectedbiddingdriverservice[]" <?php if (in_array($SubCategoryval['iBiddingId'], $selectedbiddingdriverservice)) { ?>checked<?php } ?> value="<?= $SubCategoryval['iBiddingId'] ?>" <?= $disStat ?> />
                                        <span class="toggle-base"></span>
                                    </span>

                                </div>
                                <div class="check-combo">
                                    <?php if (!empty($disStat) && $ENABLE_DRIVER_SERVICE_REQUEST_MODULE == 'Yes') { ?>
                                        <br><br>
                                        <small><?= $langage_lbl['LBL_SERVICE_REQUEST_PENDING']; ?></small><br>
                                    <?php } ?>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
        <?php if ($emptySubCatData !== '0') { ?>
        <div class="button-block justify-left">
            <input type="submit" class="save-vehicle gen-btn" name="submitbid" id="submitbid" value="<?= $langage_lbl['LBL_SUBMIT_BUTTON_TXT']; ?>" > 
        </div>
        <?php } ?>
        <?php if ($emptySubCatData == '0') { ?>
            <div> <?= $langage_lbl['LBL_NO_SERVICE_AVAIL_WEB']; ?></div>
        <?php } ?>
    </div>
</div>
