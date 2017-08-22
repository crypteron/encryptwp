<?php
use \CipherCore\v1\Encryptor;

class EncryptWP_UserMeta{
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	/**
	 * User meta fields to secure and whether or not they are searchable. TODO: store these fields in database with
	 * admin page.
	 * @var array
	 */
	public static $secure_meta_keys = array(
		'billing_phone' => false,
		'phone_number' => false,
		'pmpro_bphone' => false,
		'first_name' => false,
		'last_name' => true,
		'billing_email' => true,
		'billing_first_name' => false,
		'billing_last_name' => true,
		'billing_address_1' => false,
		'billing_address_2' => false,
		'shipping_address_1' => false,
		'shipping_address_2' => false,
		'shipping_first_name' => false,
		'shipping_last_name' => true,
		'nickname' => false,
		'birthday' => true

	);

	const STRICT = false;


	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function load_hooks(){
		add_filter('update_user_metadata', array($this, 'save_metadata' ), 500, 5);
		add_filter('get_user_metadata', array($this, 'get_metadata'), 1, 4);
	}

	/**
	 * Filter calls to save user meta data. If encryption is required, bypasses filter and updates field itself
	 * and returns true to prevent another update.
	 * @param $null
	 * @param $user_id
	 * @param $meta_key
	 * @param $meta_value
	 * @param $prev_value
	 *
	 * @return bool
	 */
	public function save_metadata($null, $user_id, $meta_key, $meta_value, $prev_value){
		// Disregard non-secure fields
		if(!isset(self::$secure_meta_keys[$meta_key])){
			return $null;
		}

		$searchable = self::$secure_meta_keys[$meta_key];

		// If value is already encrypted, do nothing
		if($this->encryptor->try_decrypt($meta_value) !== false){
			return $null;
		}

		// Encrypt text
		// TODO: handle exceptions
		$encrypted_value = $this->encryptor->encrypt($meta_value, $searchable);

		// Remove this save meta filter so we can avoid an infinite loop
		remove_filter('update_user_metadata', array($this, 'save_metadata' ), 100);

		// Save the encrypted record
		update_user_meta($user_id, $meta_key, $encrypted_value);

		// Re-add the save meta filter for future requests
		add_filter('update_user_metadata', array($this, 'save_metadata' ), 100, 5);

		// Return true to prevent the original meta from being saved while indicating to user that update was successful
		return true;

	}

	public function get_metadata($null, $user_id, $meta_key, $single){
		// Disregard non-secure fields
		if(!isset(self::$secure_meta_keys[$meta_key])){
			return $null;
		}

		// Turn off filter to fetch meta data through normal channels
		remove_filter('get_user_metadata', array($this,'get_metadata'), 1);

		// Fetch meta dnormally
		$value = get_user_meta($user_id, $meta_key, $single);

		// Re-Add the filter for future requestsata
		add_filter('get_user_metadata', array($this, 'get_metadata'), 1, 4);

		if(self::STRICT){
			// Strict mode is on. Return the decrypted value, triggering an error if it is not already encrypted
			// TODO: handle exceptions
			return $this->encryptor->decrypt($value);

		} else {
			// Strict mode is off. Try to decrypt the value.
			// TODO: handle exceptions
			$result = $this->encryptor->try_decrypt($value);

			if($result === false){
				// Text is not encrypted. Just return the original.
				return $value;
			} else {
				// Text was encrypted. Return decrypted result.
				return $result;
			}
		}
	}
}
