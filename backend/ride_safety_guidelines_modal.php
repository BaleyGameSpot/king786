<?php 
    $rideSafetyGuidelines = $obj->MySQLSelect("SELECT tPageDesc_".$_SESSION['sess_lang']." as tPageDesc FROM `pages` WHERE `iPageId` = 55");
    //$rideSafetyGuidelines = str_replace('src="../assets/img/safety.png"', 'src="'.$tconfig['tsite_url'].'assets/img/safety.png"', $rideSafetyGuidelines);
    $searchStr = "assets\/img\/safety.png";
    $replaceStr = $tconfig['tsite_url']."assets/img/safety.png";
    
    $rideSafetyGuidelinesDesc = preg_replace("/\b" . $searchStr . "\b/i", $replaceStr, $rideSafetyGuidelines[0]['tPageDesc']);
    
    $rideSafetyGuidelinesDesc = str_replace('<img src="../', '<img src="', $rideSafetyGuidelinesDesc);
    $rideSafetyGuidelines[0]['tPageDesc'] = $rideSafetyGuidelinesDesc;
?>

<div class="custom-modal-main in" id="ride_safety_guidelines_modal" style="max-width: 172800px; max-height: 84330px;" aria-hidden="false">
    <div class="custom-modal">
        <div class="model-body">
            <div class="delivery-pref-modal-content">
                <i class="icon-close" data-dismiss="modal"></i>
                <div class="ride-safety-desc">
                    <?= html_entity_decode($rideSafetyGuidelines[0]['tPageDesc']); ?>
                </div>

                <?php if($MODULES_OBJ->isEnableRestrictPassengerLimit()) { ?>
                <div class="passenger-limit-note">
                    <?= str_replace('#PERSON_LIMIT#', '<span id="person_limit"></span>', $langage_lbl['LBL_CURRENT_PERSON_LIMIT']) ?>
                </div>
                <?php } ?>
                <div class="delivery-pref-button">
                    <button type="button" id="delivery_tip_success_btn" data-dismiss="modal" onclick="checkSubmitForm()"><?= $langage_lbl['LBL_AGREE'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>