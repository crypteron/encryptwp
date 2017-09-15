<?php
class EncryptWP_Options_Manager{

	public $strict_mode;

	public $user_meta_fields;

	public $user_fields;

	public $frontend_error_placeholder;

	public function __construct() {
		$options = get_option(EncryptWP_Constants::OPTION_NAME);
		$this->set_from_option_array($options);
	}

	public function set_from_option_array($options){
		$options = wp_parse_args($options, array(
			'strict_mode' => false,
			'frontend_error_placeholder' => 'HIDDEN',
			'user_meta_fields' => array(
				'billing_phone' => 0,
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
				'birthday' => true,
				EncryptWP_Constants::EMAIL_META_KEY => true
			),
			'user_fields' => array(
				'display_name' => false
			)
		));

		$this->strict_mode = (bool)$options['strict_mode'];
		$this->frontend_error_placeholder = $options['frontend_error_placeholder'];
		$this->user_meta_fields = $this->convert_boolean($options['user_meta_fields']);
		$this->user_fields = $this->convert_boolean($options['user_fields']);
	}

	public function get_option_array(){
		return array(
			'strict_mode' => $this->strict_mode,
			'frontend_error_placeholder' => $this->frontend_error_placeholder,
			'user_meta_fields' => $this->user_meta_fields,
			'user_fields' => $this->user_fields
		);
	}

	public function save(){
		$options = $this->get_option_array();
		update_option(EncryptWP_Constants::OPTION_NAME, $options);
	}

	public function convert_boolean($options){
		foreach($options as $key => $value){
			$options[$key] = (bool) $value;
		}

		return $options;
	}
}