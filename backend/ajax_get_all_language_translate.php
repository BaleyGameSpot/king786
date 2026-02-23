<?php
include_once("common.php"); 

$englishText = isset($_POST['englishText']) ? $_POST['englishText'] : '';
//added by SP on 28-01-2021, default_lang taken from js file becoz when en is not available at that time client default lang is taken here..
$default_lang = isset($_POST['default_lang']) ? strtolower($_POST['default_lang']) : 'en';

use Google\Cloud\Translate\TranslateClient;

$GCSConfig = $GCS_OBJ->getGCSConfig();

$translate = new TranslateClient([
    'projectId' => $GCSConfig['project_id'],
    'key' => $GCSConfig['API_KEY']
]);

function checkHashValue($string) {
    $value_replaceChar= "#";
    preg_match_all("/(?<!\.w)" . $value_replaceChar . "\S*+/", $string, $matches);
    
    if(scount($matches[0]) > 0) {
        return true;
    }
    return false;
}
// fetch all lang from language_master table
$db_master = $obj->MySQLSelect("SELECT vCode,vLangCode FROM `language_master` where vCode!='" . $default_lang . "'  ORDER BY `iDispOrder` ");
$count_all = scount($db_master);

$checkHashValue = checkHashValue($englishText);

//$data = $obj->MySQLSelect("SELECT vLangCode FROM language_master where eStatus='Active' AND eDefault = 'Yes'");
//$vGMapLangCode = isset($data[0]["vLangCode"]) ? $data[0]["vLangCode"] : 'en';
$vGMapLangCode = $default_lang;

if ($count_all > 0) {
    for ($i = 0; $i < $count_all; $i++) {
        $vCode = $db_master[$i]['vCode'];

        $vGmapCode = $db_master[$i]['vLangCode'];
        //$def_lang = strtolower($default_lang);
        $vValue = 'vValue_' . $vCode;
        
        //added by SP on 28-01-2021, when following lang is there in source or destination it converts to other becoz it is not available, it is used for some prj only...
        $vGmapCodeChange = $vGmapCode;
        if($vGmapCode=='ZHCN' || $vGmapCode=='ZHTW' || $vGmapCode=='ZHSG' || $vGmapCode=='ZHHK') {
            $vGmapCodeChange = 'ZH';    
        }
        if($vGmapCode=='ptpt' || $vGmapCode=='ptbr') {
            $vGmapCodeChange = 'pt';    
        }
        if($vGmapCode=='SMI' || $vGmapCode=='ENUS' || $vGmapCode=='ENUK') {
            $vGmapCodeChange = 'EN';    
        }
        if($vGMapLangCode=='ZHCN' || $vGMapLangCode=='ZHTW' || $vGMapLangCode=='ZHSG' || $vGMapLangCode=='ZHHK') {
            $vGmapCodeChange = 'ZH';    
        }
        if($vGMapLangCode=='ptpt' || $vGMapLangCode=='ptbr') {
            $vGmapCodeChange = 'pt';    
        }
        if($vGMapLangCode=='SMI' || $vGMapLangCode=='ENUS' || $vGMapLangCode=='ENUK') {
            $vGMapLangCode = 'EN';    
        }

        $englishTextOrig = $englishText;
        // $englishText = replaceWordGT($englishText, $SITE_NAME, 'projectName');
        // $englishText = getTextForTranslationGT($englishText);
        if($checkHashValue) {
            try {
                $translation = $translate->translate(getTextForTranslationGT($englishText), [
                    'source' => 'en',
                    'target' => strtolower($vGmapCodeChange) == "zh" ? "zh-CN" : strtolower($vGmapCodeChange)
                ]);    
                $translatedText = getTranslatedTextGT($englishText, $translation);
            } catch (Exception $e) {
                $translatedText = $englishLabelValue;
            }            
        } else {
            $url = 'http://api.mymemory.translated.net/get?q=' . urlencode($englishText) . '&de=harshilmehta1982@gmail.com&langpair=' . $vGMapLangCode . '|' . $vGmapCodeChange;
        //echo $url;die;
            $result = file_get_contents($url);
            $finalResult = json_decode($result);
            $getText = $finalResult->responseData;
            $responseStatus = $finalResult->responseStatus;
            if ($responseStatus != "200") {
                $translatedText = $englishLabelValue;
            } else {
                $translatedText = $getText->translatedText;
                // $translatedText = array('text' => $translatedText);
                // $translatedText = getTranslatedTextGT($englishTextOrig, $translatedText);
                $translatedText = getProperTextGT($translatedText);
            }
        }

        $data['result'][] = array($vValue => $translatedText);
    }
}

$output = array();
foreach ($data['result'] as $Result) {
    /* $output[key($Result)] = current($Result); */
    if (current($Result) != "") {
        $output[key($Result)] = current($Result);
    } else {
        $output[key($Result)] = $englishText;
    }
}

$output = str_replace("\\", "", $output);
echo json_encode($output);
exit;
?>