<?php
use CipherCore\v1\Encryptor;

class EncryptWP_User_Fields {
	/**
	 * @var Encryptor
	 */
	protected $encryptor;


	// TODO: store these fields in database and configure with settings
	/**
	 * @var array - User fields to encrypt and whether or not they are searchable. NOTE: user_login and user_nicename are
	 * stored in DB with only 50 characters and cannot be encrypted. Encourage users not to use personal information
	 * in usernames or generate a random username during registration. Login with email instead. Note, email is treated
	 * differently due to size of field.
	 * TODO: store these fields in database with admin page.
	 */
	public static $secure_fields = array(
		'display_name' => false
	);

	// TODO: put in site wide settings
	const STRICT  = false;

	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function load_hooks(){
		// Setup encrypted fields
		foreach(self::$secure_fields as $field => $searchable){
			if($searchable){
				add_filter('pre_user_' . $field, array($this, 'save_field_searchable'), 500, 1);
			} else {
				add_filter('pre_user_' . $field, array($this, 'save_field'), 500, 1);
			}

			// Filters for editing and displaying user fields and
			add_filter('edit_user_' . $field, array($this, 'get_field'), 1, 2);
			add_filter('user_' . $field, array($this, 'get_field'), 1, 2);
			add_filter('the_author', array($this, 'decrypt_author'), 500, 1);
			add_filter('wp_dropdown_users', array($this, 'decrypt_dropdown_users'), 500, 1);
		}
	}

	/**
	 * Internal method for encrypting a potentially searchable field, if it's not already encrypted
	 * @param string $value - clear text
	 * @param bool $searchable - whether text is searchable or not
	 *
	 * @return string - Encrypted record
	 */
	private function save_field_internal($value, $searchable = false){
		// If value is already encrypted, do nothing
		if($this->encryptor->try_decrypt($value) !== false){
			return $value;
		}

		// Encrypt value
		// TODO: add error handling
		$encrypted_value = $this->encryptor->encrypt($value, null, $searchable);

		return $encrypted_value;
	}

	/**
	 * Save a non searchable secure field
	 * @param string $value - cleartext
	 */
	public function save_field($value){
		return $this->save_field_internal($value);
	}

	/**
	 * Save a searchable secure field
	 * @param string $value - cleartext
	 */
	public function save_field_searchable($value){
		return $this->save_field_internal($value, true);
	}

	/**
	 * Fetches and decrypts a potentially encrypted field
	 * @param $value - ciphertext
	 * @param $user_id
	 *
	 * @return string - cleartext
	 */
	public function get_field($value, $user_id){
		return $this->encryptor->decrypt($value);
	}

	public function decrypt_author($author){
		return $this->encryptor->decrypt($author);
	}

	public function decrypt_dropdown_users($output){
		// TODO: regEx match <option value="XX" selected="selected">ENCRYPTED_DISPLAY_NAME (UserLogin)</option>
		return $output;
	}
}
