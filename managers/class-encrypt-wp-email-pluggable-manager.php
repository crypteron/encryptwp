<?php
use TrestianCore\v1\Plugin_Settings;
use TrestianCore\v1\Admin_Notice_Manager;

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

	/**
	 * @var Admin_Notice_Manager
	 */
	protected $admin_notice_manager;

	/**
	 * @var EncryptWP_Options_Manager
	 */
	protected $options_manager;

	public function __construct(
		Plugin_Settings $settings,
		EncryptWP_Options_Manager $options_manager,
		Admin_Notice_Manager $admin_notice_manager
	) {
		$this->settings = $settings;
		$this->options_manager = $options_manager;
		$this->admin_notice_manager = $admin_notice_manager;
	}

	public function init(){
		$this->check_pluggable();
		$this->load_pluggable();
		add_action('activate_plugin', array($this, 'store_last_activated_plugins'));
		add_action('deactivate_plugin', array($this, 'cleanup_inactive_plugins'));
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
				echo 0;
				die;
			} else {
				echo 1;
				die;
			}
		}
	}

	protected function load_pluggable(){
		$options = $this->options_manager->get_options();
		if(!$options->encrypt_email) {
			return;
		}

		// If the function is already defined, then the last plugin activated is incompatible
		if(function_exists(self::function_name)){
			if(!empty($options->incompatible_plugins)){

				$plugins = implode(', ', $options->incompatible_plugins);
				$error = sprintf(__('WARNING: One of these recently activated plugins is incompatible with EncryptWP\'s email encryption feature: %s. ', $this->settings->get_prefix()), $plugins);
				$error .= __('Please deactivate them one at a time until this error goes away, or disable the email encryption feature in EncryptWP settings', $this->settings->get_prefix());
				$this->admin_notice_manager->add_notice($error, 'error', false, 'admin');
			} else {

				$this->admin_notice_manager->add_notice('One of the plugins is no good', 'error', false, 'admin');
			}

		} else {
			if(!empty($options->incompatible_plugins)){
				$options->incompatible_plugins = array();
				$this->options_manager->update_options($options);
			}
			require_once $this->settings->get_plugin_path() . 'config/encrypt-wp-pluggables.php';
		}
	}

	public function store_last_activated_plugins($plugin){
		$options = $this->options_manager->get_options();
		$plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
		$options->incompatible_plugins[$plugin] = $plugin_data['Name'];
		$this->options_manager->update_options($options);
	}

	public function cleanup_inactive_plugins($plugin){
		$options = $this->options_manager->get_options();
		if(isset($options->incompatible_plugins[$plugin])){
			unset($options->incompatible_plugins[$plugin]);
			$this->options_manager->update_options($options);
		}
	}

}