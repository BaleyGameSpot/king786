<?php
$default_lang 	= $LANG_OBJ->FetchSystemDefaultLang();
$def_lang_name = $LANG_OBJ->get_default_lang_name();
if(!isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] == ""){
    $_SESSION['eDirectionCode'] = $vSystemDefaultLangDirection;
}

function get_langcode($lang) {
    global $obj, $Data_ALL_langArr;
    if(!empty($Data_ALL_langArr) && scount($Data_ALL_langArr) > 0){
        foreach($Data_ALL_langArr as $language_item){
            if(strtoupper($language_item['vCode']) == strtoupper($lang)){
                $vLangCode = $language_item['vLangCode'];
            }
        }
    }
    if(!empty($vLangCode)){
        return $vLangCode;
    }
    $result = $obj->MySQLSelect("SELECT vLangCode FROM language_master WHERE vCode = '".$lang."'");
    return $result[0]['vLangCode'];
}
?>