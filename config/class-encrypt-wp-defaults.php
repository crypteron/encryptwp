<?php
class EncryptWP_Defaults {

	private static $USER_FIELDS = [
		[
			'label' => 'Display Name',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'  =>'display_name'
		]
	];

	private static $USER_META_FIELDS = [
		[
			'label' => 'Billing Phone',
			'state'=>EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'billing_phone'
		],
		[
			'label' => 'Phone Number',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'phone_number'
		],
		[
			'label' => 'Paid Memberships Pro - Business Phone',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'pmpro_bphone'
		],
		[
			'label' => 'First Name',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'first_name'
		],
		[
			'label' => 'Last Name',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE,
			'slug'=>'last_name'
		],
		[
			'label' => 'Nickname',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'nickname'
		],
		[
			'label' => 'Billing Email',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE,
			'slug'=>'billing_email'
		],
		[
			'label' => 'Billing First Name',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'billing_first_name'
		],
		[
			'label' => 'Billing Last Name',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'billing_last_name'
		],
		[
			'label' => 'Billing Address',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'billing_address_1'
		],
		[
			'label' => 'Billing Address 2',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'billing_address_2'
		],
		[
			'label' => 'Shipping Address',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'shipping_address_1'
		],
		[
			'label' => 'Shipping Address 2',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'shipping_address_2'
		],
		[
			'label' => 'Shipping First Name',
			'state' => EncryptWP_Field_State::ENCRYPTED,
			'slug'=>'shipping_first_name'
		],
		[
			'label' => 'Shipping Last Name',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE,
			'slug'=>'shipping_last_name'
		],
		[
			'label' => 'Birthday',
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE,
			'slug'=>'birthday'
		],
		[
			'label' => null,
			'state' => EncryptWP_Field_State::ENCRYPTED_SEARCHABLE,
			'slug'=>EncryptWP_Constants::EMAIL_META_KEY
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
			$slug = $field_array['slug'];
			$fields[$slug] = new EncryptWP_Field($field_array['label'], $field_array['state'], $slug);
		}
		return $fields;
	}

}