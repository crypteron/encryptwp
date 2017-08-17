<?php
use CipherCore\v1\Encryptor;

class EncryptWP_UserFields {
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	protected $secure_fields = array(
		'email',
		'display_name'/**,
		'nicename'**/
	);

	// TODO: figure out solution for nicename which only has 50 characters allocated

	// TODO: put in site wide settings
	const STRICT  = false;

	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function load_hooks(){
		foreach($this->secure_fields as $field){
			add_filter('pre_user_' . $field, array($this, 'save_field'), 100, 1);
			add_filter('edit_user_' . $field, array($this, 'get_field'), 1, 2);
			add_filter('user_' . $field, array($this, 'get_field'), 1, 2);
		}
	}

	public function save_field($value){
		// If value is already encrypted, do nothing
		if($this->encryptor->try_decrypt($value) !== false){
			return $value;
		}

		// Encrypt value
		// TODO: add error handling
		$encrypted_value = $this->encryptor->encrypt($value);

		return $encrypted_value;
	}

	public function get_field($value, $user_id){
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