<?php
include_once("common.php");

include_once(TPATH_CLASS.'configuration.php');


$sql = "select * from language_label where vCode='EN' GROUP BY vLabel limit 21,30";
$data_LanguageLabel = $obj->MySQLSelect($sql);

if(scount($data_LanguageLabel)>0){
    
   for($i=0;$i<scount($data_LanguageLabel);$i++){
   
      $englishLabelValue = $data_LanguageLabel[$i]['vValue'];  
      
      $sql1 = "select * from language_label where vCode!='EN' AND vLabel='".$data_LanguageLabel[$i]['vLabel']."'";
      $data_OtherLanguageValue = $obj->MySQLSelect($sql1);
      
      if(scount($data_OtherLanguageValue)>0){
          for($j=0;$j<scount($data_OtherLanguageValue);$j++){
          
                $sql3 = "select * from language_master where vCode='".$data_OtherLanguageValue[$j]['vCode']."'";
                $data_LanguageMaster = $obj->MySQLSelect($sql3);
                
                $vCode = trim($data_LanguageMaster[0]['vLangCode']);
                
                $url = 'http://api.mymemory.translated.net/get?q='.urlencode($englishLabelValue).'&langpair=en|'.$vCode;
            
                $result = file_get_contents($url);
      
                $finalResult = json_decode($result);
                
                $getText = $finalResult->responseData;
                
                
                
                /*$where = " LanguageLabelId = '".$data_OtherLanguageValue[$j]['LanguageLabelId']."'";
                $Data_UpdateLabel['vValue'] =  $getText;
                $Data_UpdateLabel['eScript'] =  'Yes';*/
				$text = $obj->SqlEscapeString($getText->translatedText);
                
                $updateQuery = "UPDATE language_label SET vValue ='".$text."', eScript = 'Yes' WHERE LanguageLabelId=".$data_OtherLanguageValue[$j]['LanguageLabelId'];
                                      
                $db_cab = $obj->sql_query($updateQuery);
                
                //$id = $obj->MySQLQueryPerform('language_label',$Data_UpdateLabel,'update',$where);
              
          }
      }
   } 
    
}

?>