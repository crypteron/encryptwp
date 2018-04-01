<?php

use TrestianCore\v1\Ajax_Manager;
use TrestianCore\v1\Template_Manager;
use TrestianCore\v1\Plugin_Settings;

class EncryptWP_Admin_Settings {


	/**
	 * @var Template_Manager
	 */
	protected $template_manager;

	/**
	 * @var EncryptWP_Options_Manager
	 */
	protected $options_manager;

	/**
	 * @var Plugin_Settings
	 */
	protected $settings;

	/**
	 * @var Ajax_Manager
	 */
	protected $ajax_manager;

	/**
	 * @var EncryptWP_Bulk_Encrypt_Manager
	 */
	protected $bulk_encrypt_manager;

	/**
	 * @var EncryptWP_Key_Manager
	 */
	protected $key_manager;

	/**
	 * @var string
	 */
	const ACTION = 'encryptwp_admin_settings';

	/**
	 * EncryptWP_Admin_Settings constructor.
	 *
	 * @param Template_Manager $template_manager
	 * @param EncryptWP_Options_Manager $options_manager
	 * @param Plugin_Settings $settings
	 * @param Ajax_Manager $ajax_manager
	 */
	public function __construct(
		Template_Manager $template_manager,
		EncryptWP_Options_Manager $options_manager,
		Plugin_Settings $settings,
		Ajax_Manager $ajax_manager,
		EncryptWP_Bulk_Encrypt_Manager $bulk_encrypt_manager,
		EncryptWP_Key_Manager $key_manager

	) {
		$this->template_manager = $template_manager;
		$this->options_manager          = $options_manager;
		$this->settings         = $settings;
		$this->ajax_manager     = $ajax_manager;
		$this->bulk_encrypt_manager = $bulk_encrypt_manager;
		$this->key_manager = $key_manager;
	}

	public function load_hooks(){
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('update_option_' . EncryptWP_Constants::OPTION_NAME, array($this, 'refresh_options'), 10, 3);
		add_action('admin_enqueue_scripts', array($this, 'load_styles'));
		add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
		add_action('wp_ajax_' . self::ACTION, array($this, 'update_settings'));
	}

	/**
	 * If the options are ever updated manually, refresh the options manager
	 * @param $old_value
	 * @param $value
	 * @param $option
	 */
	public function refresh_options($old_value, $value, $option){
		//$this->options_manager->refresh_options();
	}

	/**
	 * Register admin menu
	 */
	public function admin_menu(){
		add_options_page(
			'Crypteron EncryptWP Settings',
			'EncryptWP',
			'manage_options',
			EncryptWP_Constants::OPTION_GROUP,
			array(
				$this,
				'display_settings_page'
			));
	}

	/**
	 * Display settings page
	 */
	public function display_settings_page(){
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$key_defined = defined('CIPHER_CORE_KEY') && defined('CIPHER_CORE_TOKEN_KEY');

		$this->template_manager->load_template('templates/content-encrypt-wp-admin-settings.php', array(
			'prefix' => $this->settings->get_prefix(),
			'option_group' => EncryptWP_Constants::OPTION_GROUP,
			'options' => $this->options_manager->get_options(),
			'action' => self::ACTION,
			'nonce' => wp_create_nonce(self::ACTION),
			'key_defined' => $key_defined,
			'key_new' => $key_defined ? false : $this->key_manager->generate_key(),
			'token_key_new' => $key_defined ? false : $this->key_manager->generate_key()
		));
	}

	public function load_styles($hook){
		if($hook != 'settings_page_encryptwp'){
			return;
		}
		wp_enqueue_style($this->settings->get_prefix() . '_redux', $this->settings->get_plugin_url() . '/assets/css/redux-admin.css', array(), $this->settings->get_version());
		wp_enqueue_style($this->settings->get_prefix() . '_admin_settings', $this->settings->get_plugin_url() . '/assets/css/admin-settings.css', array('TrestianCore'), $this->settings->get_version());
		wp_enqueue_style($this->settings->get_prefix() . '_bootstrap', $this->settings->get_plugin_url() . '/assets/css/bootstrap-slim.css', array(), $this->settings->get_version());
		wp_enqueue_style($this->settings->get_prefix() . '_jquery_ui', $this->settings->get_plugin_url() . 'assets/css/jquery-ui-1.10.0.custom.css', array(), $this->settings->get_version());
	}

	public function load_scripts($hook){
		if($hook != 'settings_page_encryptwp'){
			return;
		}

		wp_register_script($this->settings->get_prefix() . '_admin_settings', $this->settings->get_plugin_url() . '/assets/js/admin-settings.js', array('jquery', 'TrestianCore', 'jquery-ui-core',  'jquery-ui-button'), $this->settings->get_version());
		$path = get_admin_url() . '?' . EncryptWP_Email_Pluggable_Manager::EMAIL_PLUGGABLE . '=1';
		wp_localize_script($this->settings->get_prefix() . '_admin_settings', 'ENCRYPT_WP_ADMIN', array(
			'options' => $this->options_manager->get_options(),
			'encrypt_email_path'=>$path,
			));
		wp_enqueue_script($this->settings->get_prefix() . '_admin_settings');
	}

	public function update_settings(){
		try {


			$this->ajax_manager->validate_nonce();
			$options         = $this->options_manager->get_options();
			$encrypt_enabled = $this->ajax_manager->check_missing_data( 'encrypt_enabled', 'Encrypt User Fields toggle is required' );
			$encrypt_enabled = $encrypt_enabled === '1';

			// Encryption still disabled
			if ( ! $encrypt_enabled && ! $options->encrypt_enabled ) {
				$this->ajax_manager->return_success( 'No changes made.' );
			}

			// Encryption now disabled. Ignore the rest of settings and update all users.
			if ( ! $encrypt_enabled && $options->encrypt_enabled ) {
				$options->encrypt_enabled = false;
				$this->options_manager->update_options( $options );
				$this->update_all_users();
			}

			// Assume we don't have to re-encrypt everything
			$update_all_users = false;

			// Encryption wasn't previously enabled. Bulk encryption required.
			if ( ! $options->encrypt_enabled ) {
				$options->encrypt_enabled = true;
				$update_all_users = true;
			}

			// Validate required inputs
			$user_fields      = $this->ajax_manager->check_missing_data_array( 'user_fields', 'Missing which user fields to encrypt' );
			$user_meta_fields = $this->ajax_manager->check_missing_data_array( 'user_meta_fields', 'Missing which user meta fields to encrypt' );
			$encrypt_email    = $this->ajax_manager->check_missing_data( 'encrypt_email', 'Missing encrypt email setting' );
			$admin_notify     = $this->ajax_manager->check_missing_data( 'admin_notify', 'Missing encrypt admin email addresses' );
			$strict_mode      = $this->ajax_manager->check_missing_data( 'strict_mode', 'Missing admin notification on insecure data setting' );

			// Update user fields and determine if bulk encryption is required
			$result = $this->options_manager->update_fields_from_array($options->user_fields, $user_fields);
			$options->user_fields = $result['fields'];
			$update_all_users = $update_all_users || $result['updated'];

			// Update user meta fields and determine if bulk encryption is required
			$result  = $this->options_manager->update_fields_from_array($options->user_meta_fields, $user_meta_fields);
			$options->user_meta_fields = $result['fields'];
			$update_all_users = $update_all_users || $result['updated'];

			// Determine if encrypt email was changed. If so bulk encryption is required.
			$encrypt_email = $encrypt_email === '1';
			if($options->encrypt_email != $encrypt_email){
				$options->encrypt_email = $encrypt_email;
				$update_all_users = true;
			}

			// Update strict mode and admin notification. Neither will require bulk encryption.
			$options->strict_mode = $strict_mode === '1';
			$options->admin_notify = array_map('trim', explode(',', $admin_notify));

			// Update the options
			$this->options_manager->update_options($options);

			// No user update needed. Return successful
			if ( !$update_all_users)
				$this->ajax_manager->return_success( 'Settings updated.' );


			// Bulk update all users
			$this->update_all_users();


		} catch (Exception $e){
			$this->ajax_manager->return_error($e->getMessage());
		}
	}



	public function update_all_users(){
		$this->ajax_manager->validate_nonce();
		try {
			$bulk_response = $this->bulk_encrypt_manager->update_all_users();
			$result = '';

			if(!empty($bulk_response->users_error)){
				$result .= '<strong>The following users failed to update:</strong><br/>';
				$result .= implode('<br/>', $bulk_response->users_error);
			}

			if(!empty($bulk_response->users_error) && !empty($bulk_response->users_success)){
				$result .= '<br/>';
			}
			if(!empty($bulk_response->users_success)){
				$result .= '<strong>The following users were successfully updated:</strong><br/>';
				$result .= implode('<br/>', $bulk_response->users_success);
			}


			$this->ajax_manager->return_success($result);

		} catch(EncryptWP_Exception $e){
			$this->ajax_manager->return_error($e->getMessage());
		}
	}
}