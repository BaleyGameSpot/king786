<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Administrator extends Model {
    
	protected $primaryKey  = "iAdminId";

	protected $table  = "administrators";

	protected $hidden  = ["vPassword"];

	public $timestamps = false;

	protected $fillable = [
		'iGroupId', 'vFirstName', 'vLastName', 'vEmail', 'vContactNo', 'vCode', 'vPassword', 'vCountry', 'vState', 'vCity', 'vAddress', 'vAddressLat', 'vAddressLong', 'fHotelServiceCharge', 'vPaymentEmail', 'vBankAccountHolderName', 'vAccountNumber', 'vBankName', 'vBankLocation', 'vBIC_SWIFT_Code', 'eStatus', 'eDefault'
	];

	function roles(){
		return $this->belongsTo(AdminGroup::class, 'iGroupId', 'iGroupId');
	}

	function locations(){
		return $this->belongsToMany(LocationMaster::class, 'admin_locations', 'admin_id', 'location_id');
	}
	
	public static function encrypt_decrypt($action, $string, $secret_key, $secret_iv) {
		$output = false;

		$encrypt_method = "AES-256-CBC";

		$key = hash('sha256', $secret_key);
		
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		}
		else if( $action == 'decrypt' ){
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}
	  
}