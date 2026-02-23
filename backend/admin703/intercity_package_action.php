<?php
include_once('../common.php');
include_once('languageField.php');
require_once("library/validation.class.php");

$script = 'InterCity Rental Package';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
if(isset($_REQUEST['iVehicleTypeId']) && !empty($_REQUEST['iVehicleTypeId']))
{
    $iVehicleTypeId = $_REQUEST['iVehicleTypeId'];
}
else{
    header("Location:rental_vehicle_list.php");
}

$backUrl = "intercity_package.php?id=".$iVehicleTypeId;
/*------------------get lang-----------------*/
$languageList = $obj->MySQLSelect("SELECT * FROM `language_master` WHERE eStatus = 'Active' ORDER BY `iDispOrder`");
$count_all = scount($languageList);
$languageList = $LANG_OBJ->getLangDataDefaultFirst($languageList);

$sql = "SELECT vName,vSymbol FROM currency WHERE eDefault = 'Yes'";
$db_currency = $obj->MySQLSelect($sql);
/*------------------get lang-----------------*/
/*------------------Submit Function-----------------*/
$iRentalPackageId = isset($_REQUEST['iRentalPackageId'])?$_REQUEST['iRentalPackageId']:'';

$action = "Add";
if(isset($iRentalPackageId) && !empty($iRentalPackageId))
{
    $action = "Edit";
}

if (isset($_POST['btnsubmitnew']))
{

    if(SITE_TYPE =='Demo') {
        $_SESSION['success'] = 2;
        header("Location:intercity_package.php?id=".$iVehicleTypeId);exit;
    }


    $validobj = new validation();
    $validobj->add_fields($_POST['iVehicleTypeId'], 'req', 'iVehicleTypeId is required.');
    $validobj->add_fields($_POST['iRentalPackageId'], 'req', 'iRentalPackageId is required.');
    $validobj->add_fields($_POST['vPackageName_Default'], 'req', 'PackageName is required.');
    $validobj->add_fields($_POST['fPrice'], 'req', 'Price is required.');
    $validobj->add_fields($_POST['fKiloMeter'], 'req', 'fKiloMeter is required.');
    $validobj->add_fields($_POST['fHour'], 'req', 'fHour is required.');
    $validobj->add_fields($_POST['fPricePerKM'], 'req', 'fPricePerKM is required.');
    $validobj->add_fields($_POST['fPricePerHour'], 'req', 'fPricePerHour is required.');
    $error = $validobj->validate();
    $error = [];
    if ($error) {
        $success = 3;
        $newError = $error;
    }else{

        $packageData = [];
        if(isset($languageList) && !empty($languageList)){
            foreach ($languageList as $lang)
            {
                $PackageNameLang = 'vPackageName_'.$lang['vCode'];
                $packageData[$PackageNameLang] = $_REQUEST[$PackageNameLang];
            }
        }

        $packageData['iVehicleTypeId'] = $_POST['iVehicleTypeId'];
        $packageData['fPrice'] = $_POST['fPrice'];
        $packageData['fKiloMeter'] = $_POST['fKiloMeter'];
        $packageData['fHour'] = $_POST['fHour'];
        $packageData['fPricePerKM'] = $_POST['fPricePerKM'];
        $packageData['fPricePerHour'] = $_POST['fPricePerHour'];

        if($iRentalPackageId != '')
        {
            $where = " iRentalPackageId = '" . $iRentalPackageId . "'";
            $obj->MySQLQueryPerform("rental_package", $packageData, 'update', $where);
        }else{
            $obj->MySQLQueryPerform("rental_package", $packageData, 'insert');
        }

        $iRentalPackageId = ($iRentalPackageId != '') ? $iRentalPackageId : $obj->GetInsertId();

        if($action == "Add")
        {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INSERT_MSG'];
            $_SESSION['success'] = "1";
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_Record_Updated_successfully'];
            $_SESSION['success'] = "1";
        }

        header("Location:".$backUrl);
        exit;
    }
}

/*------------------Submit Function-----------------*/

$sql = "SELECT * from rental_package where iRentalPackageId='".$iRentalPackageId."'";
$Rental_Package_Data = $obj->MySQLSelect($sql);
$Rental_Package_Data = $Rental_Package_Data[0];

$FeatureDataArr = [];
if(isset($languageList) && !empty($languageList))
{
    foreach ($languageList as $lang)
    {
        $PackageNameLang = 'vPackageName_'.$lang['vCode'];
        $FeatureDataArr[$PackageNameLang] = $Rental_Package_Data[$PackageNameLang];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<!-- BEGIN HEAD-->
<head>
    <meta charset="UTF-8"/>
    <title><?= $SITE_NAME ?> | <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN']; ?>  <?= $action; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <?php include_once('global_files.php'); ?>
    <!-- On OFF switch -->
    <link href="../assets/css/jquery-ui.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css"/>
</head>
<!-- END HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">
<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header.php'); ?>
    <?php include_once('left_menu.php'); ?>
    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?php echo $action; ?> Rental Package</h2>
                    <a class="back_link" href="<?php echo $backUrl; ?>">
                        <input type="button" value="Back to Listing" class="add-btn">
                    </a>
                </div>
            </div>
            <hr/>
            <div class="body-div">
                <div class="form-group">
                    <?php if (isset($success) && $success == 3) { ?>
                        <div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                            <?php print_r($error); ?>
                        </div><br/>
                    <?php } ?>
                    <form id="rental_package" name="rental_package" method="post" action="" enctype="multipart/form-data" >
                        <input type="hidden" name="iVehicleTypeId" value="<?php echo $iVehicleTypeId; ?>">
                        <input type="hidden" id= "iRentalPackageId" name="iRentalPackageId" value="<?php echo $iRentalPackageId; ?>">
                        <?php generateLanguageFieldTextInputs( $iRentalPackageId, $FeatureDataArr, 'editPackageName','savePackageName','Package_Name_Modal','Package Name','vPackageName_' ); ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Rental Total Price  (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="fPrice"  id="fPrice" value="<?php echo $Rental_Package_Data['fPrice'] ?>" onkeypress="return isNumberKey(event)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Rental <em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?></em><span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="fKiloMeter"  id="fKiloMeter" value="<?php echo $Rental_Package_Data['fKiloMeter'] ?>" onkeypress="return isNumberKey(event)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Rental Hour<span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" class="form-control" name="fHour"  id="fHour" value="<?php echo $Rental_Package_Data['fHour'] ?>" min="1"  step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57" >

                               <!-- <span>Note : Enter minimum package duration to be 1 hour.</span>-->
                            </div>


                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Additional Price Per <em class="change_eUnit" style="font-style: normal"><?=$DEFAULT_DISTANCE_UNIT;?></em> (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" name="fPricePerKM"  id="fPricePerKM" value="<?php echo $Rental_Package_Data['fPricePerKM'] ?>" onkeypress="return isNumberKey(event)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <label>Additional Price Per Min (In <?=$db_currency[0]['vName']?>) <span class="red"> *</span></label>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" value="<?php echo $Rental_Package_Data['fPricePerHour'] ?>" name="fPricePerHour"  id="fPricePerHour" value="" onkeypress="return isNumberKey(event)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if($userObj->hasPermission(['create-rental-packages', 'edit-rental-packages'])){ ?>
                                    <input type="submit" class="save btn-info" name="btnsubmitnew" id="btnsubmit"  value="<?php echo $action; ?> Rental Package" >
                                <?php  } ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row loding-action" id="loaderIcon" style="display:none;">
    <div align="center">
        <img src="default.gif">
        <span>Language Translation is in Process. Please Wait...</span>
    </div>
</div>
<?php include_once('footer.php'); ?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>

</script>
</body>
<!-- END BODY-->
</html>
