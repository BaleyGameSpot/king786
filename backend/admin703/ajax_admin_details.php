<?php
include_once("../common.php");

$iAdminId = isset($_REQUEST['iAdminId']) ? $_REQUEST['iAdminId'] : ''; 

$sql = "SELECT a.iAdminId,a.iGroupId,a.vFirstName,a.vLastName,a.vEmail,a.vAddress,vGroup,CONCAT('(+',vCode,') ',vContactNo) as mobile,a.eStatus,h.iHotelId,h.vImgName,tRegistrationDate,h.vZip,cn.vCountry as country,ct.vCity as city,st.vState as statem,cn.vTimeZone FROM administrators a LEFT JOIN admin_groups ag ON a.iGroupId = ag.iGroupId LEFT JOIN hotel h ON a.iAdminId = h.iAdminId left join country cn on cn.vCountryCode = a.vCountry left join city ct on ct.iCityId = a.vCity left join state st on st.iStateId = a.vState WHERE ag.eStatus='Active' and a.iAdminId = '$iAdminId'";
$data_admin = $obj->MySQLSelect($sql);

$URL="admin_action.php?id=".$iAdminId;
if ($data_admin[0]['iGroupId'] == "4") {
	$URL="admin_action.php?id=".$iAdminId."&admin=hotels";
}

if ($data_admin[0]['vImgName'] != "" && file_exists($tconfig["tsite_upload_images_hotel_passenger_path"] . '/' . $data_admin[0]['iHotelId'] . '/' . $data_admin[0]['vImgName']))
    $image_path = $tconfig["tsite_upload_images_hotel_passenger"] . '/' . $data_admin[0]['iHotelId'] . '/' . $data_admin[0]['vImgName'];
else {
    $image_path = "../assets/img/profile-user-img.png";
}
$systemTimeZone = date_default_timezone_get();
$date_format_data_array = array(
    'tdate' => (!empty($data_admin[0]['vTimeZone'])) ? converToTz($data_admin[0]['tRegistrationDate'],$data_admin[0]['vTimeZone'], $systemTimeZone) : $data_admin[0]['tRegistrationDate'],
    'langCode' => $default_lang,
    'DateFormatForWeb' => 1
);
$get_registration_date_format = DateformatCls::getNewDateFormat($date_format_data_array);
?>
<style>
    .text_design{
        font-size: 12px;
        font-weight: bold;
        font-family: verdana;
    }
    .border_table{
        border:1px solid #dddddd;
    }
    .no-cursor{
        cursor: text;
    }
</style>

<table border="1" class="table table-bordered" width="100%" align="center" cellspacing="5" cellpadding="10px">
    <tbody>
        <tr>
            <?php if($data_admin[0]['iGroupId'] == "4") { ?>
                <td rowspan="3" height="150px" width="150px" ><img width="150px" src="<?= $image_path ?>"></td>
            <?php } ?>
            <td>
                <table border="0" width="100%" height="150px" cellspacing="5" cellpadding="5px">
                    <tr>
                        <td width="140px" class="text_design"><?php if ($data_admin[0]['iGroupId'] == "4") { ?>Hotel<?php } else { ?>Admin<?php } ?> Name</td>
                        <td><?= clearName($data_admin[0]['vFirstName'])." ".clearName($data_admin[0]['vLastName']); ?></td>
                    </tr>
                    <tr>
                        <td class="text_design">Email</td>
                        <td><?= clearEmail($data_admin[0]['vEmail']) ?></td>
                    </tr>
					<?php if ($data_admin[0]['iGroupId'] == "4") { ?>
                    <tr>
                        <td class="text_design">Phone Number</td>
                        <td>
                            <?php
								echo clearPhoneNo($data_admin[0]['mobile']);
                            ?>
                        </td>
                    </tr> 
					<?php } ?>
                    <?php if ($data_admin[0]['iGroupId'] != "4") { ?>
                        <tr>
                            <td class="text_design">Roles</td>
                            <td>
                               <?= clearEmail($data_admin[0]['vGroup']); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td class="text_design">Status</td>
                        <td>
                            <?php
                            $class = "";
                            if ($data_admin[0]['eStatus'] == "Active") {
                                $class = "btn-success";
                            } else if ($data_admin[0]['eStatus'] == "Inactive") {
                                $class = "btn";
                            } else if ($data_admin[0]['eStatus'] == "Delete") {
                                $class = "btn-info";
                            } else {
                                $class = "btn-danger";
                            }
                            ?>
                            <button class="btn <?= $class ?> no-cursor"><?= ucfirst($data_admin[0]['eStatus']) ?></button>
                        </td>
                    </tr>

                </table>
            </td>
        </tr><tr></tr><tr></tr><tr></tr>

          <?php if ($data_admin[0]['iGroupId'] == "4") { ?>
			<?php if ($data_admin[0]['country'] != "") { ?>
				<tr>
					<td class="text_design">Country</td>
					<td>
						<?= $data_admin[0]['country']; ?>
					</td>
				</tr>
			<?php } ?>
		  <tr>
            <td class="text_design">Address</td>
            <td>
                <?= $data_admin[0]['vAddress']; ?>	
            </td>
        </tr> 
        <tr>
                <td class="text_design">Registration Date</td>
            <td><?= $get_registration_date_format['tDisplayDate'];// DateTime($data_admin[0]['tRegistrationDate'], 7); ?></td>
        </tr>
        <?php } ?> 
	</tbody>
</table>
<div class="modal-footer"> 

	<a href="<?= $URL; ?>" class="btn btn-primary btn-ok" target="blank">Edit <?php if ($data_admin[0]['iGroupId'] == "4") { echo 'Hotel'; } else{ echo 'Admin'; } ?></a>
    
    <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Close</button>
</div>
