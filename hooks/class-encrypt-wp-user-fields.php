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

	const PREFIX_SAVE = 'save_field_';

	const PREFIX_GET = 'get_field_';

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
			add_filter('pre_user_' . $field->slug, array($this, self::PREFIX_SAVE . $field->slug), 500, 1);


			// Setup filters for reading
			add_filter('edit_user_' . $field->slug, array($this, self::PREFIX_GET . $field->slug), 1, 2);
			add_filter('user_' . $field->slug, array($this, self::PREFIX_GET . $field->slug), 1, 2);
			add_filter('the_author', array($this, 'decrypt_author'), 500, 1);
			add_filter('wp_dropdown_users', array($this, 'decrypt_dropdown_users'), 500, 1);
		}
	}

	/**
	 * Magic method to determine slug from function call, e.g. edit_user_display_name or get_user_display_name
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 */
	public function __call($name, $arguments){
		if(strpos($name, self::PREFIX_SAVE) === 0)
			return $this->save_generic( $name, $arguments );

		if(strpos($name, self::PREFIX_GET) === 0)
			return $this->get_generic( $name, $arguments );



		$this->invalid_method($name);

		$prefix_save = 'save_field_';
		$prefix_get = 'get_field_';

		if(strpos($name, $prefix_save) !== 0 && strpos($name, $prefix_get) !== 0)
			$this->invalid_method($name);


	}

	private function get_and_validate_slug($prefix, $name){
		$slug = substr($name, strlen($prefix));
		if(!isset($this->options->user_fields[$slug]))
			$this->invalid_method($name);

		return $slug;
	}
	private function save_generic($name, $arguments){
		$slug = $this->get_and_validate_slug(self::PREFIX_SAVE, $name);

		if(!isset($arguments[0]))
			$this->invalid_method_arg($name);

		return $this->save_field($arguments[0], $slug);
	}

	private function get_generic($name, $arguments){
		$slug = $this->get_and_validate_slug(self::PREFIX_GET, $name);

		if(count($arguments) != 2)
			$this->invalid_method_arg($name);

		return $this->get_field($arguments[0], $arguments[1], $slug);

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
	public function get_field($value, $user_id, $slug){
		return $this->encryption_manager->decrypt( $value, null, 'user', $slug);
	}

	public function decrypt_author($author){
		if(!$this->options->encrypt_enabled || !isset($this->options->user_fields['display_name']) || $this->options->user_fields['display_name']->state == EncryptWP_Field_State::PLAINTEXT)
			return $author;

		return $this->encryption_manager->decrypt($author, null, 'user', 'display_name');
	}

	public function decrypt_dropdown_users($output){
		if(!$this->options->encrypt_enabled || !isset($this->options->user_fields['display_name']) ||  $this->options->user_fields['display_name']->state == EncryptWP_Field_State::PLAINTEXT)
			return $output;


		$output = preg_replace_callback("/(<option value='\d+'(?: selected='selected')*>)(.+)( \([[A-Za-z0-9 _.\-@]+\)<\/option>)/", function( $matches){
			if(count($matches) != 4)
				return $matches[0];
			$display_name = $this->encryption_manager->decrypt($matches[2], null, 'user', 'display_name');
			return $matches[1] . $display_name . $matches[3];
		}, $output);

		return $output;
	}
}

