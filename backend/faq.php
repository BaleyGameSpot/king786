<?php
include_once("common.php");


$script = "Faq";
$defaultLang = "EN";
if (isset($_SESSION['sess_lang']) && trim($_SESSION['sess_lang']) != "") {
    $defaultLang = $_SESSION['sess_lang'];
}
$meta = $STATIC_PAGE_OBJ->FetchStaticPage(1, $defaultLang);
$meta_arr = $STATIC_PAGE_OBJ->FetchSeoSetting(3);
$iFaqcategoryId = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$Type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

//Added By HJ On 17-10-2019 For Solved Isssue 418 Start
$db_faqs = $obj->MySQLSelect("SELECT FQ.iFaqId,FQ.iFaqcategoryId,FQ.iDisplayOrder,FQ.vTitle_" . $defaultLang . " as Que ,FQ.tAnswer_" . $defaultLang . " as Ans FROM faqs FQ INNER JOIN faq_categories FC ON FQ.iFaqcategoryId=FC.iUniqueId WHERE FQ.eStatus='Active' AND FC.eStatus='Active' GROUP BY FQ.iFaqId ORDER BY FQ.iDisplayOrder");

$faqDataArr = $db_faq_categories = $faqCatIdArr = $finalFaqDataArr = array();
$faqCatId = $faqCatIds = "";
for ($f = 0; $f < scount($db_faqs); $f++) {
    if ($iFaqcategoryId == "") {
        if ($faqCatId == "" || $faqCatId == $db_faqs[$f]['iFaqcategoryId']) {
            $faqCatId = $db_faqs[$f]['iFaqcategoryId'];
            $faqDataArr[] = $db_faqs[$f];
        }
    } else {
        if ($iFaqcategoryId == $db_faqs[$f]['iFaqcategoryId']) {
            if ($faqCatId == "" || $faqCatId == $db_faqs[$f]['iFaqcategoryId']) {
                $faqCatId = $db_faqs[$f]['iFaqcategoryId'];
                $faqDataArr[] = $db_faqs[$f];
            }
        }
    }
    $faqCatIds .= ",'" . $db_faqs[$f]['iFaqcategoryId'] . "'";
}

$whereUniqueId = "";
if ($faqCatIds != "") {
    $faqCatIds = trim($faqCatIds, ",");

    $whereUniqueId .= "AND iUniqueId IN ($faqCatIds)";
    if ($iFaqcategoryId == "") {
        //$whereUniqueId .= "AND iUniqueId IN ($faqCatIds)";
    }
    $db_faq_categories = $obj->MySQLSelect("SELECT * FROM faq_categories WHERE vCode='" . $defaultLang . "' $whereUniqueId AND eStatus='Active' order by iDisplayOrder");
    for ($f = 0; $f < scount($db_faq_categories); $f++) {
        $faqCatIdArr[] = $db_faq_categories[$f]['iUniqueId'];
    }
}
for ($p = 0; $p < scount($faqDataArr); $p++) {
    //echo $faqDataArr[$p]['iFaqcategoryId'];die;
    //echo "<pre>";print_r($faqDataArr);die;
    if (in_array($faqDataArr[$p]['iFaqcategoryId'], $faqCatIdArr)) {
        $finalFaqDataArr[] = $faqDataArr[$p];
    }
}

if($iFaqcategoryId == "") {
    $faqDataArr = array();
    for ($f = 0; $f < scount($db_faqs); $f++) {
        if ($db_faq_categories[0]['iUniqueId'] == $db_faqs[$f]['iFaqcategoryId']) {
            $faqDataArr[] = $db_faqs[$f];
        }
    }
}
//Commented By HJ On 06-11-2019 For Solved Issue - 441 of Sheet Start
/*if (!in_array($faqCatIdArr, $iFaqcategoryId) && $iFaqcategoryId != "") {
    header("Location:faq");
    exit;
}*/
//Commented By HJ On 06-11-2019 For Solved Issue - 441 of Sheet End
//Added By HJ On 17-10-2019 For Solved Isssue 418 End

//echo "<pre>";print_r("SELECT * FROM faq_categories WHERE vCode='" . $defaultLang . "' $whereUniqueId AND eStatus='Active' order by iDisplayOrder");die;

?>
<!DOCTYPE html>
<html lang="en" dir="<?= (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "") ? $_SESSION['eDirectionCode'] : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densityDpi=device-dpi">
    <title><?= $meta_arr['meta_title']; ?></title>
    <meta name="keywords" value="<?= $meta_arr['meta_keyword']; ?>"/>
    <meta name="description" value="<?= $meta_arr['meta_desc']; ?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php"); ?>
    <?php include_once("top/validation.php"); ?>
    <script type="text/javascript" src="assets/js/script.js"></script>
</head>
<body>
    <div id="main-uber-page">
        <!-- Left Menu -->
        <?php include_once("top/left_menu.php"); ?>
        <?php include_once("top/header_topbar.php"); ?>
        <?php if($THEME_OBJ->isXThemeActive() == 'Yes') { ?>
           <div class="gen-cms-page">
              <div class="gen-cms-page-inner">
              <?php } else { ?>
                <div class="page-contant">
                    <div class="page-contant-inner"><?php } ?>
                    <h2 class="header-page"><?= $langage_lbl['LBL_FAQ_TEXT']; ?></h2>
                    <div class="faq-page">
                        <div class="faq-top-part">
                            <ul>
                                <?php
                                if (scount($db_faq_categories) > 0) {
                                    for ($i = 0; $i < scount($db_faq_categories); $i++) {
                                        
                                        ?>
                                        <li id="faqCat_<?= $db_faq_categories[$i]['iUniqueId']; ?>" <?php if (trim($Type) == trim($db_faq_categories[$i]['vTitle'])) { ?>class="Active" <?php } ?>>
                                            <a href="javascript:void(0);" onClick="getFaqs('<?= $db_faq_categories[$i]['vTitle']; ?>',<?= $db_faq_categories[$i]['iUniqueId']; ?>)"><?= $db_faq_categories[$i]['vTitle']; ?></a>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="faq-bottom-part" id='cssmenu'>
                            <ul>
                                <?php
                                for ($i = 0; $i < scount($faqDataArr); $i++) {
                             
                                    $selTabId = $faqDataArr[$i]['iFaqcategoryId'];
                                    ?>
                                    <li class='has-sub <?php if($i == 0) { ?>open<?php } ?>'>
                                        <a href="#" class="faq-q">
                                            <span>
                                                <span class="bullet-faq"><?= $langage_lbl['LBL_Q']; ?></span>
                                                <h3><?= $faqDataArr[$i]['Que']; ?></h3>
                                            </span>
                                        </a>
                                        <ul class="faq-ans" <?php if($i > 0) { ?> style="display:none" <?php } ?> >
                                            <li id="faq_<?= $faqDataArr[$i]['iFaqId'] ?>">
                                                <span>  <?= $faqDataArr[$i]['Ans']; ?></span>
                                            </li>
                                        </ul>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
                <form name="faq" id="faq" action="">
                    <input type="hidden" name="id" id="iUniqueId"  value="">
                    <input type="hidden" name="type" id="CatName"  value="">
                </form>
            </div>
            <?php include_once('footer/footer_home.php'); ?>
            <div style="clear:both;"></div>
        </div>
        <?php include_once('top/footer_script.php'); ?>
        <script type="text/javascript">
            <?php if ($iFaqcategoryId == "") { ?>
            var selTabId = '<?= $db_faq_categories[0]['iUniqueId'] ?>';
            <?php } else { ?>
            var selTabId = '<?= $selTabId; ?>';
            <?php } ?>
            //console.log(selTabId);
            $("#faqCat_" + selTabId).addClass("Active");
            function FacdeQuestion(id)
            {
                if ($("#faq_" + id).is(":visible")) {
                    $("#faq_" + id).slideToggle("slow");
                } else {
                    $("#faq_" + id).slideToggle("slow");
                }
            }
            function getFaqs(cat, id)
            {
                $("#iUniqueId").val(id);
                $("#CatName").val(cat);
                document.faq.submit();
            }
        </script>
        <!-- End: Footer Script -->
    </body>
    </html>
