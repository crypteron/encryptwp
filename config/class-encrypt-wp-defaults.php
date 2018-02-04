<?php
class EncryptWP_Defaults {

	private static $USER_FIELDS = [
		'display_name' => [
			'label' => 'Display Name',
			'state' => EncryptWP_Field_State::ENCRYPTED
		]
	];

	private static $USER_META_FIELDS = [
		'billing_phone' => [
			'label' => 'Billing Phone',
			'state'=>EncryptWP_Field_State::ENCRYPTED
		],
		'phone_number' => [
			'label' => 'Phone Number',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'pmpro_bphone' => [
			'label' => 'Paid Memberships Pro - Business Phone',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'first_name' => [
			'label' => 'First Name',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'last_name' => [
			'label' => 'Last Name',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE
		],
		'nickname' => [
			'label' => 'Nickname',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'billing_email' => [
			'label' => 'Billing Email',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE
		],
		'billing_first_name' => [
			'label' => 'Billing First Name',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'billing_last_name' => [
			'label' => 'Billing Last Name',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'billing_address_1' => [
			'label' => 'Billing Address',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'billing_address_2' => [
			'label' => 'Billing Address 2',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'shipping_address_1' => [
			'label' => 'Shipping Address',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'shipping_address_2' => [
			'label' => 'Shipping Address 2',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'shipping_first_name' => [
			'label' => 'Shipping First Name',
			'state' => EncryptWP_Field_State::ENCRYPTED
		],
		'shipping_last_name' => [
			'label' => 'Shipping Last Name',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE
		],
		'birthday' => [
			'label' => 'Birthday',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE
		],
		EncryptWP_Constants::EMAIL_META_KEY => [
			'label' => null,
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE
		]
	];

	/**
	 * @return EncryptWP_Field[]
	 */
	public static function get_user_fields(){
		return self::convert_field_arrays_to_objects(self::$USER_FIELDS);
	}

	/**
	 * @return EncryptWP_Field[]
	 */
	public static function get_user_meta_fields(){
		return self::convert_field_arrays_to_objects(self::$USER_META_FIELDS);
	}

	/**
	 * @return EncryptWP_Field[]
	 */
	private static function convert_field_arrays_to_objects($field_arrays){
		$fields = [];
		foreach($field_arrays as $field_array){
			$fields[] = new EncryptWP_Field($field_array['label'], $field_array['state']);
		}
		return $fields;
	}

}