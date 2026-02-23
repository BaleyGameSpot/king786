<?php 
    if(!isset($languageLabelsArr))
    {
        $languageLabelsArr = $langage_lbl;
    }
?>

<div class="custom-modal-main in custom-common-modal" id="delivery_tip_modal" style="max-width: 172800px; max-height: 84330px;" aria-hidden="false">
    <div class="custom-modal">
        <div class="model-body">
            <div class="lock-img">
                <img src="<?= $tconfig['tsite_url'].'assets/img/save-money-new.svg' ?>">
            </div>
            <div class="delivery-pref-modal-content">
                <div class="delivery-pref-title"><?= $languageLabelsArr['LBL_DELIVERY_TIP_TXT']; ?></div>
                <div class="delivery-pref-desc">
                    <?= $languageLabelsArr['LBL_DELIVERY_TIP_DESC'] ?>
                </div>
                <div class="delivery-pref-button">
                    <button type="button" id="delivery_pref_btn" data-dismiss="modal"><?= $languageLabelsArr['LBL_BTN_OK_TXT'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="custom-modal-main in custom-common-modal" id="deliveryyourdoor_modal" aria-hidden="false">
    <div class="custom-modal">
        <div class="model-body">
            <div class="lock-img">
                <img src="<?= $tconfig['tsite_url'].'assets/img/ic_delivertodoor.svg' ?>">
            </div>
            <div class="delivery-pref-modal-content">
                <div class="delivery-pref-title"><?= $languageLabelsArr['LBL_DELIVER_TO_YOUR_DOORS']; ?></div>
                <div class="delivery-pref-desc">
                    <?= $languageLabelsArr['LBL_NOTE_DELIVER_TO_DOOR'] ?>
                </div>
                <div class="delivery-pref-button">
                    <button type="button" id="delivery_pref_btn" data-dismiss="modal"><?= $languageLabelsArr['LBL_BTN_OK_TXT'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="custom-modal-main in custom-common-modal" id="takeaway_modal" aria-hidden="false">
    <div class="custom-modal">
        <div class="model-body">
            <div class="lock-img">
                <img src="<?= $tconfig['tsite_url'].'assets/img/ic_takeaway.svg' ?>">
            </div>
            <div class="delivery-pref-modal-content">
                <div class="delivery-pref-title"><?= $languageLabelsArr['LBL_TAKE_AWAY']; ?></div>
                <div class="delivery-pref-desc">
                    <?= $languageLabelsArr['LBL_NOTE_TAKE_AWAY'] ?>
                </div>
                <div class="delivery-pref-button">
                    <button type="button" id="delivery_pref_btn" data-dismiss="modal"><?= $languageLabelsArr['LBL_BTN_OK_TXT'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>