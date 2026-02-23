<?php
//Added By HJ On 02-07-2019 For Check Project Language Conversion Process Done Or Not Start
$dbAllTablesArr = getAllTableArray(); // For Get Current Db's All Table Arr
$checkTable = checkTableExists("setup_info", $dbAllTablesArr);
$setupMessage = "";
if ($checkTable == 1) {
    //echo "<pre>";
    $data_info = $obj->MysqlSelect("select * from setup_info where 1=1");
    $eLanguageLabelConversion = $eOtherTableValueConversion = $eCurrencyFieldsSetup = $eLanguageFieldsSetup = "No";
    if (isset($data_info[0]['eLanguageLabelConversion']) && $data_info[0]['eLanguageLabelConversion'] != "") {
        $eLanguageLabelConversion = $data_info[0]['eLanguageLabelConversion'];
    }
    if (isset($data_info[0]['eOtherTableValueConversion']) && $data_info[0]['eOtherTableValueConversion'] != "") {
        $eOtherTableValueConversion = $data_info[0]['eOtherTableValueConversion'];
    }
    if (isset($data_info[0]['eCurrencyFieldsSetup']) && $data_info[0]['eCurrencyFieldsSetup'] != "") {
        $eCurrencyFieldsSetup = $data_info[0]['eCurrencyFieldsSetup'];
    }
    if (isset($data_info[0]['eLanguageFieldsSetup']) && $data_info[0]['eLanguageFieldsSetup'] != "") {
        $eLanguageFieldsSetup = $data_info[0]['eLanguageFieldsSetup'];
    }
    if ($eLanguageLabelConversion != "Yes" || $eOtherTableValueConversion != "Yes" || $eCurrencyFieldsSetup != "Yes" || $eLanguageFieldsSetup != "Yes") {
        if ($eCurrencyFieldsSetup != "Yes") {
            $setupMessage .= "Currency ratio wise field setup";
        }
        if ($eLanguageFieldsSetup != "Yes") {
            if ($setupMessage != "") {
                $setupMessage .= ", ";
            }
            $setupMessage .= "Language wise field setup";
        }
        if ($eLanguageLabelConversion != "Yes") {
            if ($setupMessage != "") {
                $setupMessage .= ", ";
            }
            $setupMessage .= "Language label table's";
        }
        if ($eOtherTableValueConversion != "Yes") {
            if ($setupMessage != "") {
                $setupMessage .= " And";
            }
            $setupMessage .= " Other all table's";
        }
        $setupMessage .= " Conversion Pending.";
    }
}
//Added By HJ On 02-07-2019 For Check Project Language Conversion Process Done Or Not End
?>
<h1 style="height: 0;margin: 0;padding: 0;pointer-events: none;visibility: hidden; font-size: 0;">
    8CAnbE
</h1>

<style>
   /* .menu>ul>li[data-parentname-search]:before {
        content: attr(data-parentname-search);
        font-size: 14px;
        font-style: normal;
        font-weight: 600;
        padding: 10px 0;
        margin-left: var(--menu-margin-left);
        margin-right: var(--menu-margin-right);
        display: block;
        text-transform: uppercase;
    }*/
</style>
<script>
    var _system_script = '<?php echo $script; ?>';
    //Added BY HJ On 05-06-2019 For Auto Hide Message Section Start
    $(document).ready(function () {
        if ($('.alert').html() != '') {
            setTimeout(function () {
                $('.alert').fadeOut();
            }, 10000);
        }

        $('#footer').appendTo('#content');
    });

    function hideSetupMessage() {
        $("#footer-new-cube").hide(2000);
    }

    //Added BY HJ On 05-06-2019 For Auto Hide Message Section End
    <?php if($MODULES_OBJ->isEnableAdminPanelV2()) { ?>
    $('.sidebar, .requirements-modal .modal-body, .system-diagnostic-modal .modal-body').mCustomScrollbar({

        theme: "minimal-dark",
        scrollInertia: 600,
        mouseWheel: {
            scrollAmount: 250
        }
    });

    setInterval(() => {
        var minHeight = $('.sidebar_hide').length > 0 ? $('#wrap').innerHeight() : 'auto';
        $('.main-sidebar').css('min-height', minHeight);
    }, 500);


    $(".table-responsive").mCustomScrollbar({
        axis: "x",
        theme: "minimal-dark",
        scrollInertia: 200,
        setWidth: false
    });
    $('[data-toggle="tooltip"]').tooltip();
    <?php } ?>

    $(document).ready(function () {
        $('.table-responsive .table tbody tr').length <= 2 ? $('.table-responsive').addClass('less-child') : $('.table-responsive').removeClass('less-child');
    })

    $(window).on('load', function () {
        leftMenuScrollTo();
       /* $("#search-menu").trigger("input");*/
    });

   var sidebar_height =  $('.sidebar').height();

    $('li.treeview').click(function () {
        var position = $(this).offset();
        var height = $(this).height();
        var totalHeight = 0;

        $(this).children().each(function(){
            totalHeight = totalHeight + $(this).outerHeight(true);
        });

        // console.log('position ' + position.top + 'height ' +totalHeight+ ' document' + sidebar_height);

        if(sidebar_height < (position.top + totalHeight)) {
            setTimeout(function () {
                leftMenuScrollTo();
            }, 350);
        }

    });

    function leftMenuScrollTo() {

        $('.sidebar').mCustomScrollbar("scrollTo", ".sidebar-menu .active");
    }

    $("input[type=text].form-control,textarea.form-control1").keypress(function(e) {
       if (e.which === 32 && $.trim($(this).val()).length == 0) {
           e.preventDefault();
           $(this).val('');
       }

   });






</script>
<script type="text/javascript" src="js/validation/jquery.validate.min.js"></script>
<script type="text/javascript" src="js/validation/additional-methods.min.js"></script>
<script type="text/javascript" src="js/form-validation.js"></script>
<script src="<?= $siteUrl ?><?php echo $templatePath; ?>assets/js/less.min.js"></script>
<!-- <script>less = { env: 'development'};</script> -->
<div style="clear:both;"></div>
<?php if ($setupMessage != "" && strtoupper($SITE_TYPE) == "LIVE") { ?>
    <div id="footer-new-cube">
        <div class="cancle-cube-cl">
            <img onclick="hideSetupMessage();" src="images/cancel.svg" width="40px" height="40px"/>
        </div>
        <div class="text-cube-cl"><?= $setupMessage; ?></div>
    </div>
<?php } ?>
<div id="footer">
    <?= str_replace("#YEAR#", date('Y'), $COPYRIGHT_TEXT_ADMIN); ?>
</div>