<div class="row">
    <div class="col-md-12 col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row" style="padding-bottom:0; ">
                    <div class="col-lg-6">
                        <h5><b><?php echo $langage_lbl_admin['LBL_MANAGE_OPTIONS_ADDON_TOPPINGS_TXT'] ?></b></h5>
                    </div>
                    <div class="col-lg-6 text-right"><button class="btn btn-success" type="button" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_ADD_OPTIONS_ADDON_TOPPINGS_TXT'] ?>" onclick="add_multi_options_category();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button></div>
                </div>
            </div>
            <div class="panel-body" style="padding: 25px; overflow-y: auto;">
                <input type="hidden" name="DeleteMultiOptionsCategoryId" id="DeleteMultiOptionsCategoryId">
                <div id="multi_options_category">
                    <?php if($action == 'Edit' && !empty($multi_options_cat_data) && scount($multi_options_cat_data) > 0) { ?>
                        <?php 
                            $mCatDataCount = 1; 
                            foreach ($multi_options_cat_data as $mCatData) { ?>
                            <?php 
                                $tCategoryName = !empty($mCatData['tCategoryName']) ? json_decode($mCatData['tCategoryName'], true)['tCategoryName_'.$default_lang] : "";
                            ?>
                            <div id="multi_options_category_fields<?= $mCatData['iOptionsCategoryId'] ?>">
                                <div class="row pb-0">
                                    <label class="col-md-12"><?= $langage_lbl_admin['LBL_OPTIONS_ADDON_TOPPINGS_TITLE_TXT'] ?></label>
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="MultiOptionsCategory[]" value="<?= $tCategoryName ?>" readonly="">
                                        </div>
                                        <input type="hidden" name="MultiOptionsCategoryId[]" value="<?= $mCatData['iOptionsCategoryId'] ?>">
                                        <input type="hidden" name="MultiOptionsCategoryIdTmp[]" value="0">
                                        <textarea name="MultiOptionsCategoryAll[]" style="display: none"><?= $mCatData['tCategoryName'] ?></textarea>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-btn">
                                                    <span>
                                                        <button class="btn btn-info" type="button" onclick="toggleOptionsToppings(<?= $mCatData['iOptionsCategoryId'] ?>);" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_VIEW'] ?>" style="margin-right: 20px">
                                                            <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
                                                        </button>
                                                    </span>
                                                    <span>
                                                        <button class="btn btn-info" type="button" onclick="edit_multi_options_category(<?= $mCatData['iOptionsCategoryId'] ?>);" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_EDIT'] ?>" style="margin-right: 20px">
                                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                        </button>
                                                    </span>
                                                    <span>
                                                        <button class="btn btn-danger" type="button" onclick="multi_options_category_remove(<?= $mCatData['iOptionsCategoryId'] ?>);" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_REMOVE_TEXT'] ?>" style="margin-right: 20px">
                                                            <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                                                        </button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>

                                <?php
                                    $tOptionTitleVal = !empty($mCatData['BaseOptions'][0]['tOptionAddonTitle']) ? json_decode($mCatData['BaseOptions'][0]['tOptionAddonTitle'], true) : "";
                                    $tOptionTitle = !empty($tOptionTitleVal) ? $tOptionTitleVal['tOptionAddonTitle_' . $default_lang] : "";
                                ?>
                                <div id="option_toppings<?= $mCatData['iOptionsCategoryId'] ?>" style="display:none">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <div class="row options_title" style="padding-bottom:0;">
                                                <div class="col-lg-6">
                                                    <h5><b><?= $langage_lbl_admin['LBL_OPTIONS_MENU_ITEM'] ?></b> <i class="icon-question-sign" id="helptxtchange" data-placement="top" data-toggle="tooltip" data-original-title="This feature can be used when you want to provide different options for the same product. The price would be added to the base price.For E.G.: Regular Pizza, Double Cheese Pizza etc."></i>
                                                    </h5>
                                                </div>
                                                <div class="col-lg-6 text-right">
                                                    <button class="btn btn-success" type="button" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_ADD_EDIT_OPTIONS_TITLE'] ?>" onclick="options_title(<?= $mCatData['iOptionsCategoryId'] ?>);" style="margin-right:10px"> <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> </button>
                                                    <button class="btn btn-success" type="button" data-toggle="tooltip" data-original-title="Add Option" onclick="options_fields(<?= $mCatData['iOptionsCategoryId'] ?>);">
                                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 
                                                    </button>
                                                </div>
                                                <?php if(!empty($tOptionTitleVal)) { ?>
                                                    <div class="col-lg-12 options_title_value" style="float: left;">
                                                        <input type="text" class="form-control w-50" disabled value="<?= $tOptionTitle ?>">
                                                    </div>
                                                    <textarea name="tOptionTitle[]" style="display:none"><?= trim($mCatData['BaseOptions'][0]['tOptionAddonTitle'], '"') ?></textarea>
                                                <?php } else { ?>
                                                    <div class="col-lg-12 options_title_value" style="float: left;">
                                                        <input type="text" class="form-control w-50" disabled placeholder="<?= $langage_lbl_admin['LBL_OPTIONS_TITLE'] ?>">
                                                    </div>
                                                    <textarea name="tOptionTitle[]" style="display:none"></textarea>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 25px; overflow-y: auto;">
                                            <div id="options_fields<?= $mCatData['iOptionsCategoryId'] ?>">
                                                <?php if(!empty($mCatData['BaseOptions']) && scount($mCatData['BaseOptions']) > 0) { ?>
                                                    <?php foreach ($mCatData['BaseOptions'] as $option) { ?>
                                                        <?php if ($option['eDefault'] == 'Yes') { ?>
                                                            <div class="form-group row eDefault pb-0 mb-0">
                                                                <div class="col-sm-5">
                                                                    <div class="form-group"> 
                                                                        <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-5">
                                                                    <div class="form-group"> 
                                                                        <input type="text" class="form-control" id="OptPrice" name="OptPrice[]"  value="<?= $option['fPrice'] ?>" placeholder="Price" readonly required="required">
                                                                        <input type="hidden" name="optType[]" value="Options" />
                                                                        <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>" /><input type="hidden" name="eDefault[]" value="Yes"/>
                                                                        <input type="hidden" name="OptionsCategoryId[]" value="<?= $option['iOptionsCategoryId'] ?>"/>
                                                                        <textarea name="options_lang_all[]" style="display: none;"><?= preg_replace('/"+/', '"', $option['tOptionNameLang']) ?></textarea>
                                                                        <input type="hidden" name="vMenuItemOptionImage[]" value="">
                                                                        <input type="hidden" name="vMenuItemOptionImgName" value="<?= $option['vImage'] ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <div class="form-group">
                                                                        <div class="input-group">
                                                                            <div class="input-group-btn">
                                                                                <span>
                                                                                    <button class="btn btn-info" type="button" onclick="edit_options_fields(0, 1 ,<?= $mCatData['iOptionsCategoryId'] ?>);" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="clear"></div>
                                                            </div>
                                                            <?php } else { ?>
                                                            <div class="form-group row removeclass<?= $option['iOptionId'] ?> mb-0 pb-0">
                                                                <div class="col-sm-5">
                                                                    <div class="form-group"> 
                                                                        <input type="text" class="form-control" id="BaseOptions" name="BaseOptions[]" required="required" value="<?= $option['vOptionName'] ?>" placeholder="Option Name" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-5">
                                                                    <div class="form-group"> 
                                                                        <input type="text" class="form-control" id="OptPrice" name="OptPrice[]" required="required" value="<?= $option['fPrice'] ?>" placeholder="Price" readonly>
                                                                        <input type="hidden" name="optType[]" value="Options" />
                                                                        <input type="hidden" name="OptionId[]" value="<?= $option['iOptionId'] ?>" /><input type="hidden" name="eDefault[]" value="No"/>
                                                                        <input type="hidden" name="OptionsCategoryId[]" value="<?= $option['iOptionsCategoryId'] ?>"/>
                                                                        <textarea name="options_lang_all[]" style="display: none;"><?= trim($option['tOptionNameLang'], '"') ?></textarea>
                                                                        <input type="hidden" name="vMenuItemOptionImage[]" value="">
                                                                        <input type="hidden" name="vMenuItemOptionImgName" value="<?= $option['vImage'] ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <div class="form-group">
                                                                        <div class="input-group">
                                                                            <div class="input-group-btn">
                                                                                <span>
                                                                                    <button class="btn btn-info" type="button" onclick="edit_options_fields(<?= $option['iOptionId'] ?>, 0, <?= $mCatData['iOptionsCategoryId'] ?>);" data-toggle="tooltip" data-original-title="Edit" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                                                </span>
                                                                                <span>
                                                                                    <button class="btn btn-danger" type="button" onclick="remove_options_fields(<?= $option['iOptionId'] ?>);" data-toggle="tooltip" data-original-title="Remove" style="margin-right: 20px"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="clear"></div>
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                        $tAddonTitleVal = !empty($mCatData['AddonOptions'][0]['tOptionAddonTitle']) ? json_decode($mCatData['AddonOptions'][0]['tOptionAddonTitle'], true) : "";
                                        $tAddonTitle = !empty($tAddonTitleVal) ? $tAddonTitleVal['tOptionAddonTitle_' . $default_lang] : "";
                                    ?>
                                    <div class="panel panel-default servicecatresponsive">
                                        <div class="panel-heading">
                                            <div class="row addon_title" style="padding-bottom:0;">
                                                <div class="col-lg-6">
                                                    <h5><b><?= $langage_lbl_admin['LBL_ADDON_FRONT'] ?> <i class="icon-question-sign" data-placement="top" data-toggle="tooltip" data-original-title="Addon/Topping Price will be additional amount which will added in base price"></i></b>
                                                    </h5>
                                                </div>
                                                <div class="col-lg-6 text-right">
                                                    <button class="btn btn-success" type="button" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_ADD_EDIT_ADDON_TITLE'] ?>" onclick="addon_title(<?= $mCatData['iOptionsCategoryId'] ?>);" style="margin-right:10px"> <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> </button>
                                                    <button class="btn btn-success" type="button" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_ADD_ADDON_TOPPING'] ?>" onclick="addon_fields(<?= $mCatData['iOptionsCategoryId'] ?>);"> 
                                                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 
                                                    </button>
                                                </div>
                                                <?php if(!empty($tAddonTitleVal)) { ?>
                                                    <div class="col-lg-12 addon_title_value" style="float: left;">
                                                        <input type="text" class="form-control w-50" disabled value="<?= $tAddonTitle ?>">
                                                    </div>
                                                    <textarea name="tAddonTitle[]" style="display:none"><?= trim($mCatData['AddonOptions'][0]['tOptionAddonTitle'], '"') ?></textarea>
                                                <?php } else { ?>
                                                    <div class="col-lg-12 addon_title_value" style="float: left;">
                                                        <input type="text" class="form-control w-50" disabled placeholder="<?= $langage_lbl_admin['LBL_ADDON_TOPPING_TITLE'] ?>">
                                                    </div>
                                                    <textarea name="tAddonTitle[]" style="display:none"></textarea>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="panel-body" style="padding: 25px; overflow-y: auto;">
                                            <div id="addon_fields<?= $mCatData['iOptionsCategoryId'] ?>">
                                                <?php if(!empty($mCatData['AddonOptions']) && scount($mCatData['AddonOptions']) > 0) { ?>
                                                    <?php foreach ($mCatData['AddonOptions'] as $addon) { ?>
                                                        <div class="form-group row removeclassaddon<?= $addon['iOptionId'] ?> pb-0 mb-0">
                                                            <div class="col-sm-5">
                                                                <div class="form-group"> 
                                                                    <input type="text" class="form-control" id="AddonOptions" name="AddonOptions[]" value="<?= $addon['vOptionName'] ?>" placeholder="Topping Name" required readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <div class="form-group"> 
                                                                    <input type="text" class="form-control" id="AddonPrice" name="AddonPrice[]" value="<?= $addon['fPrice'] ?>" placeholder="Price" required readonly>
                                                                    <input type="hidden" name="optTypeaddon[]" value="Addon" />
                                                                    <input type="hidden" name="addonId[]" value="<?= $addon['iOptionId'] ?>" />
                                                                    <input type="hidden" name="AddonsCategoryId[]" value="<?= $addon['iOptionsCategoryId'] ?>"/>
                                                                    <textarea name="addons_lang_all[]" style="display: none;"><?= trim($addon['tOptionNameLang'], '"') ?></textarea>
                                                                    <input type="hidden" name="vMenuItemAddonImage[]" value="">
                                                                    <input type="hidden" name="vMenuItemOptionImgName" value="<?= $addon['vImage'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-2">
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-btn">
                                                                            <span>
                                                                                <button class="btn btn-info" type="button" onclick="edit_addon_fields(<?= $addon['iOptionId'] ?>, <?= $mCatData['iOptionsCategoryId'] ?>);" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_EDIT'] ?>" style="margin-right: 20px"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>
                                                                            </span>
                                                                            <span>
                                                                                <button class="btn btn-danger" type="button" onclick="remove_addon_fields(<?= $addon['iOptionId'] ?>);" data-toggle="tooltip" data-original-title="<?= $langage_lbl_admin['LBL_REMOVE_TEXT'] ?>" style="margin-right: 20px"> <span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if($mCatDataCount < scount($multi_options_cat_data)) { ?>
                            <hr>
                            <?php } ?>
                        <?php $mCatDataCount++; } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="multi_options_category_Modal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" >
        <div class="modal-content nimot-class">
            <div class="modal-header">
                <h4>
                    <span id="multi_options_category_title"></span>
                    <button type="button" class="close" data-dismiss="modal">x</button>
                </h4>
            </div>
            
            <div class="modal-body">
                <input type="hidden" name="multi_options_category_action" id="multi_options_category_action">
                <input type="hidden" name="multi_options_category_id" id="multi_options_category_id">
                <?php
                    if(scount($db_master) > 1)
                    {
                        for ($i = 0; $i < $count_all; $i++) 
                        {
                            $vCode = $db_master[$i]['vCode'];
                            $vTitle = $db_master[$i]['vTitle'];
                            $eDefault = $db_master[$i]['eDefault'];
                    
                            $vValue = 'tCategoryName_' . $vCode;
                        ?>
                        <div class="row">
                            <div class="col-md-12">
                                <label><span><?= $langage_lbl_admin['LBL_OPTIONS_ADDON_TOPPINGS_TITLE_TXT'] ?></span> (<?= $vTitle ?>)</label>
                                <input type="text" class="form-control" name="<?= $vValue; ?>" id="<?= $vValue; ?>" placeholder="<?= $vTitle; ?> Value">
                                <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                            </div>
                        </div>
                        <?php
                            if (scount($db_master) > 1) {
                                if($EN_available) {
                                    if($vCode == "EN") { ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" onclick="getAllLanguageCode('tCategoryName_', 'EN');">Convert To All Language</button>
                                        </div>
                                    </div>
                                    <?php 
                                    }
                                } else { 
                                    if($vCode == $defaultLang) { ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" onclick="getAllLanguageCode('tCategoryName_', '<?= $default_lang ?>');">Convert To All Language</button>
                                        </div>
                                    </div>
                                    <?php 
                                    }
                                }
                            }
                        ?>
                        <?php 
                        }
                    } else { ?>
                        <div class="row">
                            <div class="col-md-6">
                                <label><?= $langage_lbl_admin['LBL_MULTI_OPTIONS_CATEGORY_NAME'] ?> (<?= $db_master[0]['vTitle'] ?>)</label>
                                <input type="text" class="form-control" name="tCategoryName_<?= $default_lang; ?>" id="tCategoryName_<?= $default_lang; ?>" placeholder="<?= $db_master[0]['vTitle']; ?> Value">
                                <div class="text-danger" id="<?= $vValue.'_error'; ?>" style="display: none;"><?= $langage_lbl_admin['LBL_REQUIRED'] ?></div>
                            </div>
                        </div>
                        <?php 
                    }
                ?>
            </div>
            <div class="modal-footer" style="margin-top: 0">
                <h5 class="text-left" style="margin-bottom: 15px; margin-top: 0;"><strong><?= $langage_lbl['LBL_NOTE']; ?>: </strong><?= $langage_lbl['LBL_SAVE_INFO']; ?></h5>
                <div class="nimot-class-but" style="margin-bottom: 0">
                    <button type="button" class="save" id="add_multi_options_category_btn"  style="margin-left: 0 !important"><?= $langage_lbl['LBL_ADD']; ?></button>
                    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Cancel</button>
                </div>
            </div>
            
            <div style="clear:both;"></div>
        </div>
    </div>
</div>