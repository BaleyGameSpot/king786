<?php
include_once('../common.php');
$id = $_REQUEST['id'];
$type = $_REQUEST['eUserType'];
function getMemberReferUser($id,$type,$REFER_MEMBER = [])
{

    $MEMBER_MLM_ARR = [];
    global $obj;
    if (empty($REFER_MEMBER)){

        $REFER_MEMBER = getMemberReferUsersub($id,$type);
    }
    if (isset($REFER_MEMBER) && !empty($REFER_MEMBER)){

        foreach ($REFER_MEMBER as &$MEMBER){

            $REFER_MEMBER_A = $MEMBER['referralUser'] = getMemberReferUsersub($MEMBER['UserId'],$MEMBER['MemberType']);
            if (isset($REFER_MEMBER_A) && !empty($REFER_MEMBER_A)){

                $MEMBER['referralUser'] = getMemberReferUser('','',$REFER_MEMBER_A);
            }
        }
    }
    return $REFER_MEMBER;
}

$data = getMemberReferUser($id,$type);
if ($type == 'Driver'){

    $tablename = 'register_driver';
    $iUserId = "iDriverId";
}else{

    $tablename = 'register_user';
    $iUserId = 'iUserId';
}
$query = "SELECT concat(vName, ' ' ,vLastName) as OrgName , '".$id."' as UserId FROM ".$tablename." WHERE ".$iUserId." = '".$id."' ";
$MLM_MEMBER_ARR = $obj->MySQLSelect($query);
$MLM_MEMBER_ARR[0]['referralUser'] = $data;
$MLM_MEMBER_ARR[0]['MemberType'] = $type;
?>


<?php
function generateHTMLForTreeView($MLM_MEMBER_ARR,$first = 0)
{

    if (isset($MLM_MEMBER_ARR) && !empty($MLM_MEMBER_ARR)){

        if ($first == 1){

            $html = '<ul class="mlm_tree" >';
        }else{

            $html = '<ul>';
        }
        //echo generateHTMLForTreeView($MLM_MEMBER_ARR , 0);
        foreach ($MLM_MEMBER_ARR as $MEMBER){

            if (isset($MEMBER['OrgName']) && !empty($MEMBER['OrgName'])){

                $html .= '<li>   

                 ';
                if (isset($MEMBER['referralUser']) && !empty($MEMBER['referralUser'])){
                    if($MEMBER['UserId'] == $_REQUEST['id'] && $MEMBER['MemberType'] == $_REQUEST['eUserType']){
                        $html .= ' <span>'.$MEMBER['OrgName'].' <p>'.$MEMBER['MemberType'].'</p></span>';
                    } else {
                        $html .= ' <span><a href="multi_level_referrer_action.php?id='.$MEMBER['UserId'].'&eUserType='.$MEMBER['MemberType'].'" data-toggle="tooltip" title="" target="_blank" data-original-title="View Details"> '.$MEMBER['OrgName'].' <p>'.$MEMBER['MemberType'].'</p> </a></span>';
                    }

                    $html .= generateHTMLForTreeView($MEMBER['referralUser']);
                }else{

                    $html .= '<span>'.$MEMBER['OrgName'].' <p>'.$MEMBER['MemberType'].'</p> </span>';
                }
                $html .= '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }
}

if (isset($MLM_MEMBER_ARR) && !empty($MLM_MEMBER_ARR)){

    echo generateHTMLForTreeView($MLM_MEMBER_ARR,1);
}
?>