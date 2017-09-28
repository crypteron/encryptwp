<?php
use CipherCore\v1\Encryptor;

class EncryptWP_User_Email {
	/**
	 * @var EncryptWP_Options_Manager
	 */
	protected $options;

	/**
	 * @var EncryptWP_Error_Manager
	 */
	protected $error_manager;

	/**
	 * @var EncryptWP_Email_Search_Manager
	 */
	protected $email_search_manager;

	/**
	 * EncryptWP_UserEmail constructor.
	 *
	 * @param EncryptWP_Options_Manager $options
	 * @param EncryptWP_Error_Manager $error_manager
	 */
	public function __construct(EncryptWP_Options_Manager $options, EncryptWP_Error_Manager $error_manager, EncryptWP_Email_Search_Manager $email_search_manager) {
		$this->options = $options;
		$this->error_manager = $error_manager;
		$this->email_search_manager = $email_search_manager;
	}

	/**
	 * Register hooks
	 */
	public function load_hooks(){
		// Don't load hooks if email encryption is turned off. TODO: move setting to database
		if(!$this->options->encrypt_email){
			return;
		}

		// Secure email after profile is updated
		$this->register_profile_update_hook();

		// Encrypt email after a user registers
		add_action('user_register', array($this, 'encrypt_email'), 100, 1);

		// Decrypt email when fetched
		add_filter('user_email', array($this, 'decrypt_email'), 1, 2);

		// Decrypt email when fetched for editing
		add_filter('edit_user_email', array($this, 'decrypt_email'), 1, 2);

		add_action('pre_user_query', array($this, 'search_email'), 100, 1);
	}

	/**
	 * Encrypt a user's email within wp_user_meta and obfuscates original within wp_users.
	 * @param int $user_id
	 */
	public function encrypt_email($user_id){
		// Fetch user email and convert to lowercase
		$user = get_user_by('id', $user_id);
		$user_email = strtolower($user->user_email);

		// Store it in user meta. Note, EncryptWP_UserMeta will automatically encrypt it
		update_user_meta($user_id, EncryptWP_Constants::EMAIL_META_KEY, $user_email);

		// Obfuscate original email
		$user->user_email = sprintf(EncryptWP_Constants::OBFUSCATE_EMAIL_PATTERN, $user_id);

		// Remove profile update hook to avoid infinite loop
		remove_action('profile_update', array($this, 'encrypt_email'), 100);

		// Remove change email notification
		add_filter('send_email_change_email', array($this, 'disable_email_change_notification'), 100, 3);

		// Update user
		wp_update_user($user);

		// Re-add profile update hook
		$this->register_profile_update_hook();

		// Re-enable change email notification
		remove_filter('send_email_change_email', array($this, 'disable_email_change_notification'), 100);
	}

	public function decrypt_email($value, $user_id){
		// See if email is obfuscated or not
		if($value != sprintf(EncryptWP_Constants::OBFUSCATE_EMAIL_PATTERN, $user_id)){
			// If email is not obfuscated either return it or trigger error in secure mode.
			if($this->options->strict_mode){
				$this->error_manager->cleartext_found($value, 'users', 'user_email');
			} else {
				return $value;
			}
		}

		// Fetch encrypted email from user meta
		$user_email = get_user_meta($user_id, EncryptWP_Constants::EMAIL_META_KEY, true);

		if($user_email === false){
			$this->error_manager->decrypt_failure($value, new EncryptWP_Exception("No encrypted email address found in wp_user_meta for user ID: $user_id"), 'user_meta', 'secure_user_email');
		}

		return $user_email;
	}

	private function register_profile_update_hook(){
		add_action('profile_update', array($this, 'encrypt_email'), 100, 1);
	}

	public function disable_email_change_notification($send, $user, $userdata){
		return false;
	}

	/**
	 * @param $query WP_User_Query
	 */
	public function search_email($query){
		$search = $query->get('search');
		if (!$search) {
			return;
		}

		$query = $this->email_search_manager->update_query_for_email_search($query);
	}

	/**
	 * Copied from WP_User Query. Used to find the search SQL to replace and inject with email search string. Hackish. Ideally there would be
	 * a filter for search sql. Oh well.
	 *
	 * @access protected
     *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $string
	 * @param array  $cols
	 * @param bool   $wild   Whether to allow wildcard searches. Default is false for Network Admin, true for single site.
	 *                       Single site allows leading and trailing wildcards, Network Admin only trailing.
	 * @return string
	 */
	protected function get_search_sql( $string, $cols, $wild = false ) {
		global $wpdb;

		$searches = array();
		$leading_wild = ( 'leading' == $wild || 'both' == $wild ) ? '%' : '';
		$trailing_wild = ( 'trailing' == $wild || 'both' == $wild ) ? '%' : '';
		$like = $leading_wild . $wpdb->esc_like( $string ) . $trailing_wild;

		foreach ( $cols as $col ) {
			if ( 'ID' == $col ) {
				$searches[] = $wpdb->prepare( "$col = %s", $string );
			} else {
				$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
			}
		}

		return ' AND (' . implode(' OR ', $searches) . ')';
	}


}