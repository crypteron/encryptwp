<?php
use CipherCore\v1\Encryptor;
use CipherCore\v1\CipherCore_Deserialize_Exception;
use TrestianCore\v1\Plugin_Settings;
use TrestianCore\v1\Admin_Notice_Manager;

class EncryptWP_User_Fields {
	/**
	 * @var EncryptWP_Encryption_Manager
	 */
	protected $encryption_manager;

	/**
	 * @var EncryptWP_Options
	 */
	protected $options;


	/**
	 * EncryptWP_User_Fields constructor.
	 *
	 * @param Encryptor $encryptor
	 * @param Plugin_Settings $settings
	 */
	public function __construct(EncryptWP_Encryption_Manager $encryption_manager, EncryptWP_Options_Manager $options_manager) {
		$this->encryption_manager   = $encryption_manager;
		$this->options = $options_manager->get_options();
	}

	public function load_hooks(){
		// Setup encrypted fields
		foreach($this->options->user_fields as $field){

			// Set up filter for saving
			add_filter('pre_user_' . $field->slug, array($this, 'save_field_' . $field->slug), 500, 1);


			// Setup filters for reading
			add_filter('edit_user_' . $field->slug, array($this, 'get_field'), 1, 2);
			add_filter('user_' . $field->slug, array($this, 'get_field'), 1, 2);
			add_filter('the_author', array($this, 'decrypt_author'), 500, 1);
			add_filter('wp_dropdown_users', array($this, 'decrypt_dropdown_users'), 500, 1);
		}
	}

	public function __call($name, $arguments){
		$prefix = 'save_field_';

		if(strpos($name, $prefix) !== 0)
			$this->invalid_method($name);

		$slug = substr($name, strlen($prefix));
		if(!isset($this->options->user_fields[$slug]))
			$this->invalid_method($name);

		if(!isset($arguments[0]))
			$this->invalid_method_arg($name);

		return $this->save_field($arguments[0], $slug);


	}

	private function save_field($value, $slug) {
		// Fetch field details
		$field = $this->options->user_fields[$slug];


		// Ensure encryption is enabled, field is not plain text, and value is not already encrypted
		if( !$this->options->encrypt_enabled || $field->state == EncryptWP_Field_State::PLAINTEXT || $this->encryption_manager->is_encrypted($value) !== false){
			return $value;
		}

		// Encrypt value
		$encrypted_value = $this->encryption_manager->encrypt($value, null, $field->state === EncryptWP_Field_State::ENCRYPTED_SEARCHABLE);

		return $encrypted_value;
	}

	private function invalid_method($name){
		trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
	}

	private function invalid_method_arg($name){
		trigger_error('Missing argument 1 in call to '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
	}

	/**
	 * Fetches and decrypts a potentially encrypted field
	 * @param $value - ciphertext
	 * @param $user_id
	 *
	 * @return string - cleartext
	 */
	public function get_field($value, $user_id){
		return $this->encryption_manager->decrypt( $value, null, 'user');
	}

	public function decrypt_author($author){
		return $this->encryption_manager->decrypt($author, null, 'user', 'display_name');
	}

	public function decrypt_dropdown_users($output){
		// TODO: regEx match <option value="XX" selected="selected">ENCRYPTED_DISPLAY_NAME (UserLogin)</option>
		return $output;
	}
}

