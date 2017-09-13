<?php
use CipherCore\v1\Encryptor;

class EncryptWP_User_Email {
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	/**
	 * EncryptWP_UserEmail constructor.
	 *
	 * @param Encryptor $encryptor
	 */
	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	/**
	 * Register hooks
	 */
	public function load_hooks(){
		// Don't load hooks if email encryption is turned off. TODO: move setting to database
		if(!EncryptWP_Constants::ENCRYPT_EMAIL){
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
			if(EncryptWP_Constants::STRICT_MODE){
				throw new EncryptWP_Exception("Insecure email address found in wp_users table: $value for user ID: $user_id");
			} else {
				return $value;
			}
		}

		// Fetch encrypted email from user meta
		$user_email = get_user_meta($user_id, EncryptWP_Constants::EMAIL_META_KEY, true);

		if($user_email === false){
			throw new EncryptWP_Exception("No encrypted email address found in wp_user_meta for user ID: $user_id");
		}

		return $user_email;
	}

	private function register_profile_update_hook(){
		add_action('profile_update', array($this, 'encrypt_email'), 100, 1);
	}

	public function disable_email_change_notification($send, $user, $userdata){
		return false;
	}


}