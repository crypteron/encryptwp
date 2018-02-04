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
	 * @var EncryptWP_Options
	 */
	protected $options;

	/**
	 * Which priority to add and remove meta filters for reading
	 * @var int
	 */
	const READ_PRIORITY = 1;

	/**
	 * Which priority to add and remove meta filters for writing
	 * @var int
	 */
	const WRITE_PRIORITY = 500;

	/**
	 * User meta fields to secure and whether or not they are searchable. TODO: store these fields in database with
	 * admin page.
	 * @var array
	 */
	public static $secure_meta_keys = array(
		'billing_phone' => false,
		'phone_number' => false,
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
		EncryptWP_Constants::EMAIL_META_KEY => true,
		"pmpro_bfirstname" => false,
		"pmpro_blastname" => true,
		"pmpro_baddress1" => false,
		"pmpro_baddress2" => false,
		"pmpro_bphone" => false,
		"pmpro_bemail" => true
	);

	/**
	 * EncryptWP_UserMeta constructor.
	 *
	 * @param EncryptWP_Encryption_Manager $encryptor
	 */
	public function __construct(EncryptWP_Encryption_Manager $encryptor, EncryptWP_Meta_Query_Manager $meta_query_manager, EncryptWP_Options_Manager $options_manager) {
		$this->encryption_manager = $encryptor;
		$this->meta_query_manager = $meta_query_manager;
		$this->options = $options_manager->get_options();
	}

	/**
	 * Register hooks
	 */
	public function load_hooks(){

		// Intercept calls to update user meta data
		$this->register_update_user_meta_filter();

		// Intercept calls to add user meta data
		$this->register_add_user_meta_filter();


		// Intercept calls to get user metadata
		$this->register_get_user_meta_filter();

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
	public function update_meta_value($null, $user_id, $meta_key, $meta_value, $prev_value){
		// Disregard if encryption disabled or non-secure fields
		if(!$this->options->encrypt_enabled || !isset(self::$secure_meta_keys[$meta_key])){
			return $null;
		}

		// Serialize objects / arrays before encrypting
		$meta_value = maybe_serialize( $meta_value );

		$searchable = self::$secure_meta_keys[$meta_key];

		// If value is already encrypted, do nothing
		if( $this->encryption_manager->is_encrypted($meta_value)){
			return $null;
		}

		// Encrypt text
		$encrypted_value = $this->encryption_manager->encrypt($meta_value, null, $searchable);

		// Remove this save meta filter so we can avoid an infinite loop
		remove_filter('update_user_metadata', array($this, 'update_meta_value' ), self::WRITE_PRIORITY);

		// Save the encrypted record
		update_user_meta($user_id, $meta_key, $encrypted_value, $prev_value);

		// Re-add the save meta filter for future requests
		$this->register_update_user_meta_filter();

		// Return true to prevent the original meta from being updated while indicating to user that update was successful
		return true;

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
	public function add_meta_value($null, $user_id, $meta_key, $meta_value, $unique){
		// Disregard if encryption disabled or non-secure fields
		if(!$this->options->encrypt_enabled || !isset(self::$secure_meta_keys[$meta_key])){
			return $null;
		}

		// Serialize objects / arrays before encrypting
		$meta_value = maybe_serialize( $meta_value );

		$searchable = self::$secure_meta_keys[$meta_key];

		// If value is already encrypted, do nothing
		if( $this->encryption_manager->is_encrypted($meta_value)){
			return $null;
		}

		// Encrypt text
		$encrypted_value = $this->encryption_manager->encrypt($meta_value, null, $searchable);

		// Remove this save meta filter so we can avoid an infinite loop
		remove_filter('add_user_metadata', array($this, 'add_meta_value' ), self::WRITE_PRIORITY);

		// Save the encrypted record
		add_user_meta($user_id, $meta_key, $encrypted_value, $unique);

		// Re-add the save meta filter for future requests
		$this->register_add_user_meta_filter();

		// Return true to prevent the original meta from being added while indicating to user that update was successful
		return true;

	}

	/**
	 * Decrypt meta values for sensitive meta keys
	 * @param null $null - Always null
	 * @param int $user_id
	 * @param string $meta_key
	 * @param bool $single
	 *
	 * @return bool|array|string
	 */
	public function get_meta_value($null, $user_id, $meta_key, $single){
		// Disregard non-secure fields
		if(!isset(self::$secure_meta_keys[$meta_key]) && $meta_key){
			return $null;
		}

		// Turn off filter to fetch meta data through normal channels
		remove_filter('get_user_metadata', array($this, 'get_meta_value' ), self::READ_PRIORITY);

		// Fetch encrypted meta normally
		$value = get_user_meta($user_id, $meta_key, $single);

		// Re-Add the filter for future requests
		$this->register_get_user_meta_filter();

		// No meta key was specified. Loop through each item in the array of meta keys and see if any of them are secure
		if(!$meta_key){
			foreach($value as $key => $item){
				if(isset(self::$secure_meta_keys[$key])){
					foreach($item as $sub_key => $sub_item){
						$sub_item = $this->encryption_manager->decrypt($sub_item, null, 'user_meta', $meta_key);
						$value[$key][$sub_key] = maybe_unserialize($sub_item);
					}

				}
			}

			return $value;
		}

		// Decrypt value
		// We have to handle deserialization in a special manner since the previous call to get_user_meta will have been unable
		// to derialized encrypted text.
		if($single){
			// User wants the first result found in DB. However this first result, now decrypted, may be a serialized array. To prevent the caller,
			// get_metadata, from discarding all but the first element in the deserialized array on line 489 of meta.php, we wrap the result in another array.
			$value = $this->encryption_manager->decrypt($value, null, 'user meta', $meta_key);
			$value = array(maybe_unserialize($value));

			return $value;
		}

		// User wants the full array of results found. However these results may contain serialized arrays. Try to deserialize each
		// result and return the array of results.
		foreach($value as $key => $item){
			$item = $this->encryption_manager->decrypt($item, null, 'user meta', $meta_key);
			$value[$key] = maybe_unserialize($item);
		}

		return $value;
	}

	/**
	 * @param $query WP_User_Query
	 */
	public function transform_meta_query($query){
		$query->query_vars = $this->meta_query_manager->parse_query_vars($query->query_vars, self::$secure_meta_keys);
		return;
	}

	private function register_add_user_meta_filter(){
		add_filter('add_user_metadata', array($this, 'add_meta_value' ), self::WRITE_PRIORITY, 5);
	}

	private function register_update_user_meta_filter(){
		add_filter('update_user_metadata', array($this, 'update_meta_value' ), self::WRITE_PRIORITY, 5);
	}

	private function register_get_user_meta_filter(){
		add_filter('get_user_metadata', array($this, 'get_meta_value' ), self::READ_PRIORITY, 4);
	}
}
