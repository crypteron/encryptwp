<?php
use \CipherCore\v1\Encryptor;

class EncryptWP_UserMeta{
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	protected $meta_fields = array(
		'billing_phone',
		'phone_number',
		'pmpro_bphone');


	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function load_hooks(){
		add_filter('update_user_metadata', array($this, 'save_meta'));
	}

	public function save_meta($null, $user_id, $meta_key, $meta_value, $prev_value){
		// Disregard user meta fields that aren't sensitive
		if(!in_array($meta_key, $this->meta_fields)){
			return $null;
		}

		// Ensure meta value is not already encrypted


	}


}