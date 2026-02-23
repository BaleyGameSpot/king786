<?php
include_once("../common.php");

$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
$approveBtn = isset($_REQUEST['approveBtn']) ? $_REQUEST['approveBtn'] : 'Yes';

$documents = $obj->MySQLSelect("SELECT rdl.eApproveDoc , rdl.ex_date,rdl.doc_file,rdl.doc_id,(SELECT CASE WHEN COUNT(*) > 0 THEN 'Yes' ELSE 'No' END  as 'isUploaded' FROM `ride_share_document_list` WHERE doc_masterid = dm.doc_masterid AND doc_userid = {$iUserId}) as isUploaded,dm.doc_masterid, dm.ex_status, dm.doc_name_EN as doc_name FROM document_master as dm LEFT JOIN ride_share_document_list rdl ON (rdl.doc_masterid = dm.doc_masterid AND doc_userid = {$iUserId} ) WHERE  dm.doc_usertype = 'user' AND dm.status = 'Active' ORDER BY iDisplayOrder ASC ");
$img_path = $tconfig["tsite_upload_ride_share_documents"];

$Photo_Gallery_folder = $img_path . '/' . $iUserId . '/';
if (!empty($documents)) {
    $i = 0;
    foreach ($documents as $document) {
        if (empty($document['doc_id'])) {
            $documents[$i]['doc_id'] = '';
        }
        if (empty($document['ex_date'])) {
            $documents[$i]['ex_date'] = '';
        }
        if (empty($document['doc_file'])) {
            $documents[$i]['doc_file'] = '';
        }
        else {
            $documents[$i]['doc_file'] = $Photo_Gallery_folder . $document['doc_file'];
            $documents[$i]['is_doc'] = "No";
            $doc_file_arr = explode(".", $document['doc_file']);
            $doc_file_ext = strtolower($doc_file_arr[scount($doc_file_arr) - 1]);
            $images_ext_arr = explode(",", $tconfig["tsite_upload_image_file_extensions"]);
            if (!in_array($doc_file_ext, $images_ext_arr)) {
                $documents[$i]['is_doc'] = "Yes";
            }
        }
        $i++;
    }
}
?>
<div class="row user-document-action ">
    <?php
    $eApproveDoc = "Yes";
    if (isset($documents) && !empty($documents)){
    foreach ($documents as $document) {

        if ($document['eApproveDoc'] == "No") {
            $eApproveDoc = "No";
        }
        ?>


        <div class="col-lg-6">
            <div class="panel panel-default upload-clicking">
                <div class="panel-heading">
                    <div><?php echo $document['doc_name']; ?></div>
                </div>
                <div class="panel-body">
                    <?php if (isset($document['doc_file']) && !empty($document['doc_file'])) { ?>
                        <img src="<?php echo $tconfig['tsite_url'] . 'resizeImg.php?w=200&src=' . $document['doc_file']; ?>"
                             alt="" style="cursor:pointer;" alt="YOUR DRIVING LICENCE">
                    <?php }
                    else {
                        echo "No Document  Found.";
                    } ?>
                </div>
            </div>
        </div>


    <?php } ?>
</div>

<?php


?>
<?php if ($eApproveDoc == "No" && $approveBtn != "No") { ?>
    <div class="row user-document-action ">
        <div class="col-lg-12">
            <form id="approvedDoc" action="#">
                <input type="hidden" name="userid" value="<?php echo $iUserId; ?>">
                <input type="hidden" name="docApproved" value="1">
                <button class="btn btn-info" type="submit"> Approve</button>
            </form>

        </div>
    </div>
<?php } ?>

<?php }
else {
    echo "No Document  Found.";
} ?>

<style>
    .user-document-action .upload-clicking .panel-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
</style>