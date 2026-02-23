<?php
include_once('../common.php');
$tblname = 'multi_level_referral_master';
$sql = "SELECT * FROM $tblname WHERE eStatus = 'Active' ORDER BY iLevel ASC LIMIT $REFERRAL_LEVEL";
$multi_level_referral_master_data = $obj->MySQLSelect($sql);
$SERVICE_AMOUNT = 100;

$data = $Level_amount = [];
$Level_User_Name = [];
$Level_percentage = [];
$Level_User_Name[0] = 'ABC Member';
$userName = 1;
foreach ($multi_level_referral_master_data as $MD){
    $Level_amount[$MD['iLevel']] = $MD['iAmount'];
    $Level_User_Name[$MD['iLevel']] = 'Member '.$userName;
    $Level_percentage[$MD['iLevel']] = number_format($MD['iAmount']).'%';
    $Level_Amount[$MD['iLevel']] = formateNumAsPerCurrency(($SERVICE_AMOUNT * $MD['iAmount']) / 100 , '');

    $userName++;
}

function earningMessage($name_level,$str = '',$amount_level = 1)
{
    global $SERVICE_AMOUNT,$Level_amount,$Level_User_Name;
    if ($name_level < 0){
        return '';
    }
    $iAmount = ($SERVICE_AMOUNT * $Level_amount[$amount_level]) / 100;
    $iAmount = formateNumAsPerCurrency($iAmount,'');
    $str = $Level_User_Name[$name_level]." Earned  ".$iAmount." from the Level ".$amount_level."<br>";
    $str .= earningMessage(($name_level - 1),$str,($amount_level + 1));
    return $str;
}

function earningMessage1($name_level,$str = '',$amount_level = 1)
{
    global $SERVICE_AMOUNT,$Level_amount,$Level_User_Name;
    if ($name_level < 0){
        return '';
    }
    $iAmount = ($SERVICE_AMOUNT * $Level_amount[$amount_level]) / 100;
    $iAmount = formateNumAsPerCurrency($iAmount,'');
    $str = '<li>'.$Level_User_Name[$name_level]." Earned  ".$iAmount." from the Level ".$amount_level."</li>";
    $str .= earningMessage1(($name_level - 1),$str,($amount_level + 1));
    return $str;
}
function generateNetworkArray($levels)
{
    global $level,$Level_amount,$Level_User_Name;
    if ($levels <= 0){
        return '';
    }
    $levels_1 = (($level + 1) - $levels);
    $array = array("title"       => $Level_User_Name[$levels_1],
                   "referred_by" => '(Referred by '.$Level_User_Name[($levels_1 - 1)].') <br>',
                   "Earned"      => earningMessage(($levels_1 - 1)),
                   "Earned1"      => earningMessage1(($levels_1 - 1)),

                   "children"    => array(generateNetworkArray($levels - 1),));
    return $array;
}

$level = 3;
$data[] = array("title"    => $Level_User_Name[0],
                "Earned"   => "",
                "children" => array(generateNetworkArray($level),));

?>
<body>

<p>Let's simplify how the Referral feature works as explained below.</p>
<!--<p> Notes:</p>
<ul>
    <li><?php /*echo $Level_User_Name[1] */?> is referred by an <?php /*echo $Level_User_Name[0] */?>.</li>
    <li><?php /*echo $Level_User_Name[2] */?> is referred by <?php /*echo $Level_User_Name[3] */?>.</li>
    <li><?php /*echo $Level_User_Name[3] */?> is referred by <?php /*echo $Level_User_Name[2] */?>.</li>
</ul>-->
<ul>
    <li> Job amount is <?php echo formateNumAsPerCurrency($SERVICE_AMOUNT,''); ?> </li>


    <li> Referral Amount for Level 1: <?php echo $Level_Amount[1] ?> ( As per Referral Percentage <?php echo $Level_percentage[1] ?> )</li>
    <li> Referral Amount for Level 2: <?php echo $Level_Amount[2] ?> ( As per Referral Percentage <?php echo $Level_percentage[2] ?> )</li>
    <li> Referral Amount for Level 3: <?php echo $Level_Amount[3] ?> ( As per Referral Percentage <?php echo $Level_percentage[3] ?> )</li>
</ul>

<figure>
    <?php
    // Function to recursively generate HTML from the array
    function generateHTML($data,$first = 0)
    {
        if (isset($data[0]['title']) && !empty($data[0]['title'])){
            if ($first == 1){
                $html = '<ul class="tree" >';
            }else{
                // $html = '<ul>';
                $html = '';
            }
            if (isset($data) && !empty($data)){
                foreach ($data as $item){
                    if (isset($item['title']) && !empty($item['title'])){
                        if(isset($item['referred_by'])){
                            $html .= '<li><span>'.$item['title'].' <p class="referred">'.$item['referred_by'].'</p>  <p>'.$item['Earned'].'</p></span>';
                        } else {
                            $html .= '<li><span>'.$item['title'].'<p>'.$item['Earned'].'</p></span>';
                        }
                        
                        if (isset($item['children']) && !empty($item['children'])){
                            $html .= generateHTML($item['children']);
                        }
                        $html .= '</li>';
                    }
                }
            }
            if ($first == 1){
                $html .= '</ul>';
            }
            return $html;
        }
    }
    echo $html = generateHTML($data,1);
    ?>
</figure>


<?php
// Function to recursively generate HTML from the array
function generateHTML1($data,$first = 0)
{
    if (isset($data[0]['title']) && !empty($data[0]['title'])){
        if ($first == 1){
            $html = '<ol class = "treeOl" >';
        }else{
            $html = '';
        }
        if (isset($data) && !empty($data)){
            foreach ($data as $item){
                if (isset($item['title']) && !empty($item['title'])){

                    $html .= '<li> Job accomplished by  '.$item['title'].'<br>';
                    $html .= '<ul>';
                    if (isset($item['Earned1']) && !empty($item['Earned1'])){
                        $html .=  $item['Earned1'];
                    }
                    $html .= '</ul>';
                    $html .= '</li><br>';

                    $html .= generateHTML1($item['children']);
                }
            }
        }
        if ($first == 1){
            $html .= '</ol>';
        }else{

        }
        return $html;
    }
}

// Generate HTML from the array

echo $html = generateHTML1($data[0]['children'],1);
?>

