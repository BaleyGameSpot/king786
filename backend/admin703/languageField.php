<?php
function generateLanguageFieldTextInputs($Id,$DataArr,$editFunctionName,$saveFunctionName,$modelName,$text,$FieldName)
{
    global $EN_available,$languageList,$default_lang;
    $countLanguages = scount($languageList);
    $defaultLanguageCode = $EN_available?'EN':$default_lang;
    if ($countLanguages > 1){
        ?>
        <div class="row">
            <div class="col-lg-12">
                <label><?=$text?><span class="red"> *</span></label>
            </div>
            <div class="col-md-6 col-sm-6">
                <input type="text" class="form-control <?=empty($Id)?'readonly-custom':''?>" name="<?=$FieldName?>Default" id="<?=$FieldName?>Default" value="<?=$DataArr[$FieldName.$default_lang]??'';?>" data-originalvalue="<?=$DataArr[$FieldName.$default_lang]??'';?>" readonly="readonly" required <?=empty($Id)?'onclick="'.$editFunctionName.'(\'Add\')"':'';?> placeholder="Enter <?=$text?>">
            </div>
            <?php if (!empty($Id)){ ?>
                <div class="col-lg-2">
                    <button type="button" class="btn btn-info" data-toggle="tooltip" data-original-title="Edit" onclick="<?=$editFunctionName?>(<?="'Edit'"?>)">
                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                </div>
            <?php } ?>
        </div>

        <div class="modal fade" id="<?=$modelName?>" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content nimot-class">
                    <div class="modal-header">
                        <h4>
                            <span id="modal_action"></span> <?=$text?>
                            <button type="button" class="close" data-dismiss="modal" onclick="resetToOriginalValue(this, '<?=$FieldName?>')">x</button>
                        </h4>
                    </div>

                    <div class="modal-body">
                        <?php foreach ($languageList as $language){
                            $vCode = $language['vCode'];
                            $vTitle = $language['vTitle'];
                            $eDefault = $language['eDefault'];
                            $vValue = $FieldName.$vCode;
                            $required = $eDefault === 'Yes'?'required':'';
                            $required_msg = $eDefault === 'Yes'?'<span class="red"> *</span>':'';
                            $pageTitleClass = ($vCode === 'EN' || $vCode === $default_lang)?'col-md-9 col-sm-9':'col-lg-12';
                            ?>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label><?=$text?> (<?=$vTitle?>) <?=$required_msg?></label>
                                </div>
                                <div class="<?=$pageTitleClass?>">
                                    <input type="text" class="form-control" name="<?=$vValue?>" id="<?=$vValue?>" value="<?=$DataArr[$vValue]??''?>" placeholder="<?=$vTitle?> Value" data-originalvalue="<?=$DataArr[$vValue]??''?>">
                                    <div class="text-danger" id="<?=$vValue.'_error'?>" style="display: none;">Required</div>
                                </div>
                                <?php if ($vCode === 'EN' || $vCode === $default_lang){ ?>
                                    <div class="col-md-3 col-sm-3">

                                        <button type ="button" name="allLanguage" id="allLanguage" class="btn btn-primary" onClick="getAllLanguageCode('vPackageName_', '<?= $default_lang ?>');">Convert To All Language</button>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="modal-footer" style="margin-top: 0">
                        <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;">
                            <strong>Note: </strong> Please make sure to save main page below after saving and closing this popup window.
                        </h5>
                        <div class="nimot-class-but" style="margin-bottom: 0">
                            <button type="button" class="save" style="margin-left: 0 !important" onclick="<?=$saveFunctionName?>()">Save</button>
                            <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal" onclick="resetToOriginalValue(this, '<?=$FieldName?>')">Cancel</button>
                        </div>
                    </div>

                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>

        <script>

            function <?=$editFunctionName; ?>(action) {
                $('#modal_action').html(action);
                $('#<?=$modelName?>').modal('show');
            }

            function <?=$saveFunctionName; ?>() {
                if ($('#<?=$FieldName ?><?= $default_lang ?>').val() == "") {
                    $('#<?=$FieldName ?><?= $default_lang ?>_error').show();
                    $('#<?=$FieldName ?><?= $default_lang ?>').focus();
                    clearInterval(langVar);
                    langVar = setTimeout(function () {
                        $('#<?=$FieldName ?><?= $default_lang ?>_error').hide();
                    }, 5000);
                    return false;
                }
                $('#<?=$FieldName ?>Default').val($('#<?=$FieldName ?><?= $default_lang ?>').val());
                $('#<?=$FieldName ?>Default').closest('.row').removeClass('has-error');
                $('#<?=$FieldName ?>Default-error').remove();
                $('#<?=$modelName?>').modal('hide');
                var <?=$FieldName ?>Default_field = $('#<?=$FieldName ?>Default');
                var form = $(<?=$FieldName ?>Default_field).closest('form');
                form.validate().element(<?=$FieldName ?>Default_field);
            }

        </script>

    <?php }else{ ?>
        <div class="row">
            <div class="col-lg-12">
                <label><?=$text?> <span class="red"> *</span></label>
            </div>
            <div class="col-md-6 col-sm-6">
                <input type="text" class="form-control" name="<?=$FieldName?><?=$default_lang?>" id="<?=$FieldName?><?=$default_lang?>" value="<?=$DataArr[$FieldName.$default_lang]??''?>" required>
            </div>
        </div>
    <?php }
}

?>
