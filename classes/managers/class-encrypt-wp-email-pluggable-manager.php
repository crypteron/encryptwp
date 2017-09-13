<?php

class EncryptWP_Email_Pluggable_Manager {
	/**
	 * GET request variable to trigger pluggable check
	 */
	const EMAIL_PLUGGABLE = 'encrypt_wp_email_pluggable_check';

	const function_name = 'get_user_by';

	/**
	 * @var Trestian_Plugin_Settings
	 */
	protected $settings;

	public function __construct(TrestianCore\v1\Trestian_Plugin_Settings $settings) {
		$this->settings = $settings;
	}

	public function init(){
		$this->check_pluggable();
		$this->load_pluggable();
	}

	protected function check_pluggable(){
		if(isset($_GET[self::EMAIL_PLUGGABLE]) && $_GET[self::EMAIL_PLUGGABLE]){
			@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
			@header( 'X-Robots-Tag: noindex' );

			send_nosniff_header();
			nocache_headers();

			if(!is_admin()){
				echo 'not allowed';
				wp_die();
			}

			if(function_exists(self::function_name)){
				die(0);
			} else {
				die(1);
			}
		}
	}

	protected function load_pluggable(){
		if(!EncryptWP_Constants::ENCRYPT_EMAIL) {
			return;
		}
		// Throw error if get_user_by is already defined.
		// TODO: when ENCRYPT_EMAIL setting is moved to database, perform check on whether or not this function exists before updating setting from admin dashboard
		if(function_exists('get_user_by')){
			// TODO: supply more meaningful error message
			throw new EncryptWP_Exception('get_user_by is already overloaded by another plugin.');
		}

		require_once $this->settings->get_plugin_path() . 'functions/encrypt-wp-email-pluggable.php';
	}

}