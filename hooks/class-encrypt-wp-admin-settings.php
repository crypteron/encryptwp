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
	 * @var string
	 */
	const ENCRYPT_ALL_ACTION = 'encryptwp_encrypt_all';

	const DECRYPT_ALL_ACTION = 'encryptwp_decrypt_all';

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
		EncryptWP_Bulk_Encrypt_Manager $bulk_encrypt_manager
	) {
		$this->template_manager = $template_manager;
		$this->options_manager          = $options_manager;
		$this->settings         = $settings;
		$this->ajax_manager     = $ajax_manager;
		$this->bulk_encrypt_manager = $bulk_encrypt_manager;
	}

	public function load_hooks(){
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('update_option_' . EncryptWP_Constants::OPTION_NAME, array($this, 'refresh_options'), 10, 3);
		add_action('admin_enqueue_scripts', array($this, 'load_styles'));
		add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
		add_action('wp_ajax_' . self::ENCRYPT_ALL_ACTION, array($this, 'encrypt_all'));
	}

	/**
	 * If the options are ever updated manually, refresh the options manager
	 * @param $old_value
	 * @param $value
	 * @param $option
	 */
	public function refresh_options($old_value, $value, $option){
		//$this->options_manager->load_options();
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

		$this->template_manager->load_template('templates/content-encrypt-wp-admin-settings.php', array(
			'prefix' => $this->settings->get_prefix(),
			'option_group' => EncryptWP_Constants::OPTION_GROUP,
			'options' => $this->options_manager->get_options()
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
			'encrypt_all_nonce' => wp_create_nonce(self::ENCRYPT_ALL_ACTION),
			'encrypt_all_action' => self::ENCRYPT_ALL_ACTION,
			'decrypt_all_nonce' => wp_create_nonce(self::DECRYPT_ALL_ACTION),
			'decrypt_all_action' => self::DECRYPT_ALL_ACTION,

			));
		wp_enqueue_script($this->settings->get_prefix() . '_admin_settings');
	}

	public function encrypt_all(){
		$this->ajax_manager->validate_nonce();
		try {
			$bulk_response = $this->bulk_encrypt_manager->encrypt_all_users();
			$result = '';

			if(!empty($bulk_response->users_error)){
				$result .= '<strong>The following users failed to encrypt:</strong><br/>';
				$result .= implode('<br/>', $bulk_response->users_error);
			}

			if(!empty($bulk_response->users_error) && !empty($bulk_response->users_success)){
				$result .= '<br/>';
			}
			if(!empty($bulk_response->users_success)){
				$result .= '<strong>The following users were successfully encrypted:</strong><br/>';
				$result .= implode('<br/>', $bulk_response->users_success);
			}


			$this->ajax_manager->return_success($result);

		} catch(EncryptWP_Exception $e){
			$this->ajax_manager->return_error($e->getMessage());
		}
	}

	public function decrypt_all(){
		$this->ajax_manager->validate_nonce();
		try {
			$bulk_response = $this->bulk_encrypt_manager->decrypt_all_user();
			$result = '';

			if(!empty($bulk_response->users_error)){
				$result .= '<strong>The following users failed to decrypt:</strong><br/>';
				$result .= implode('<br/>', $bulk_response->users_error);
			}

			if(!empty($bulk_response->users_error) && !empty($bulk_response->users_success)){
				$result .= '<br/>';
			}
			if(!empty($bulk_response->users_success)){
				$result .= '<strong>The following users were successfully decrypted:</strong><br/>';
				$result .= implode('<br/>', $bulk_response->users_success);
			}


			$this->ajax_manager->return_success($result);

		} catch(EncryptWP_Exception $e){
			$this->ajax_manager->return_error($e->getMessage());
		}
	}
}