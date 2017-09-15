<?php
use \CipherCore\v1\Encryptor;

class EncryptWP_User_Meta{
	/**
	 * @var EncryptWP_Encryption_Manager
	 */
	protected $encryption_manager;

	/**
	 * @var EncryptWP_Meta_Query_Manager
	 */
	protected $meta_query_manager;

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
		'birthday' => true,
		EncryptWP_Constants::EMAIL_META_KEY => true
	);

	/**
	 * EncryptWP_UserMeta constructor.
	 *
	 * @param EncryptWP_Encryption_Manager $encryptor
	 */
	public function __construct(EncryptWP_Encryption_Manager $encryptor, EncryptWP_Meta_Query_Manager $meta_query_manager) {
		$this->encryption_manager = $encryptor;
		$this->meta_query_manager = $meta_query_manager;
	}

	/**
	 * Register hooks
	 */
	public function load_hooks(){
		// Intercept calls to update user meta data
		add_filter('update_user_metadata', array($this, 'encrypt_meta_value' ), 500, 5);

		// Intercept calls to get user metadata
		add_filter('get_user_metadata', array($this, 'decrypt_meta_value' ), 1, 4);

		add_action('pre_get_users', array($this, 'transform_meta_query'), 500, 1);
	}

	/**
	 * Encrypt meta values for sensitive meta keys
	 * @param null $null - Always null.
	 * @param int $user_id
	 * @param string $meta_key
	 * @param string $meta_value
	 * @param string $prev_value
	 *
	 * @return bool
	 */
	public function encrypt_meta_value($null, $user_id, $meta_key, $meta_value, $prev_value){
		// Disregard non-secure fields
		if(!isset(self::$secure_meta_keys[$meta_key])){
			return $null;
		}

		$searchable = self::$secure_meta_keys[$meta_key];

		// If value is already encrypted, do nothing
		if( $this->encryption_manager->is_encrypted($meta_value)){
			return $null;
		}

		// Encrypt text
		// TODO: handle exceptions
		$encrypted_value = $this->encryption_manager->encrypt($meta_value, null, $searchable);

		// Remove this save meta filter so we can avoid an infinite loop
		remove_filter('update_user_metadata', array($this, 'encrypt_meta_value' ), 100);

		// Save the encrypted record
		update_user_meta($user_id, $meta_key, $encrypted_value);

		// Re-add the save meta filter for future requests
		add_filter('update_user_metadata', array($this, 'encrypt_meta_value' ), 100, 5);

		// Return true to prevent the original meta from being saved while indicating to user that update was successful
		return true;

	}

	/**
	 * Decrypt meta values for sensitive meta keys
	 * @param null $null - Always null
	 * @param int $user_id
	 * @param string $meta_key
	 * @param bool $single
	 *
	 * @return bool|mixed|string
	 */
	public function decrypt_meta_value($null, $user_id, $meta_key, $single){
		// Disregard non-secure fields
		if(!isset(self::$secure_meta_keys[$meta_key])){
			return $null;
		}

		// Turn off filter to fetch meta data through normal channels
		remove_filter('get_user_metadata', array($this, 'decrypt_meta_value' ), 1);

		// Fetch meta normally
		$value = get_user_meta($user_id, $meta_key, $single);

		// Re-Add the filter for future requestsata
		add_filter('get_user_metadata', array($this, 'decrypt_meta_value' ), 1, 4);

		return $this->encryption_manager->decrypt($value, null, 'user meta', $meta_key);
	}

	/**
	 * @param $query WP_User_Query
	 */
	public function transform_meta_query($query){
		$query->query_vars = $this->meta_query_manager->parse_query_vars($query->query_vars, self::$secure_meta_keys);
		return;
	}
}
