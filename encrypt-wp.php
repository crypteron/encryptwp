<?php
/**
 *
 * @link              https://crypteron.com
 * @since             1.0.0
 * @package           encryptwp
 *
 * @wordpress-plugin
 * Plugin Name:       EncryptWP
 * Plugin URI:        https://bitbucket.org/Crypteron/cipherwp
 * Description:       Adds military grade encryption and tamper protection to WordPress
 * Version:           1.0.10
 * Author:            Crypteron
 * Author URI:        https://crypteron.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       encryptwp
 * Domain Path:       /languages
 * BitBucket Plugin URI: https://bitbucket.org/Crypteron/encryptwp
 * BitBucket Branch: master
 */
use TrestianCore\v1\TrestianCore;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EncryptWP{

	/**
	 * The single instance of the class.
	 *
	 * @var EncryptWP
	 */
	protected static $instance = null;

	/**
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $plugin_path;

	/**
	 * @var string
	 */
	protected $plugin_url;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var
	 */
	protected $hooks;

	/**
	 * @var EncryptWP_Email_Pluggable_Manager
	 */
	protected $email_pluggable;

	/**
	 * @var EncryptWP_Options
	 */
	protected $options;

	/**
	 * @var \Dice\Dice
	 */
	protected $dice;

	public function __construct() {
		$this->plugin_name = 'encrypt-wp';
		$this->prefix = 'encrypt-wp';
		$this->version = '1.0.8';
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);

		$this->load_dependencies();
		$this->dice = $this->setup_dependency_injection();

		$this->hooks = $this->dice->create('EncryptWP_Hooks');

		// Special case handling for pluggables. As of now there's only one for email handling
		// If more are needed in the future, abstract this into a pluggable parent class
		$this->email_pluggable = $this->dice->create( 'EncryptWP_Email_Pluggable_Manager' );
		$this->email_pluggable->init();

		// Default the current user filter to display. This action must be added here since it must
		// be defined before plugins_loaded
		add_action('set_current_user', array($this, 'set_current_user_filter'));
	}

	/**
	 * Main EncryptWP Instance
	 *
	 * Ensures only one instance of EncryptWP is loaded or can be loaded.
	 *
	 * @static
	 * @see EncryptWP()
	 * @return EncryptWP
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load dependency files
	 */
	private function load_dependencies(){
		// Libraries
		require_once $this->plugin_path . 'libs/cipher-core/cipher-core.php';


		// Load V1 of Trestian Core if not already loaded
		if(!class_exists('\TrestianCore\v1\TrestianCore')){
			require_once $this->plugin_path  . 'libs/trestian-core/trestian-core.php';
		}

		// Managers
		require_once $this->plugin_path . 'managers/class-encrypt-wp-email-pluggable-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-meta-query-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-options-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-encryption-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-error-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-bulk-encrypt-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-key-manager.php';
		require_once $this->plugin_path . 'managers/class-encrypt-wp-email-search-manager.php';

		// Config
		require_once $this->plugin_path . 'config/class-encrypt-wp-constants.php';
		require_once $this->plugin_path . 'config/class-encrypt-wp-defaults.php';

		// Models
		require_once $this->plugin_path . 'models/class-encrypt-wp-exception.php';
		require_once $this->plugin_path . 'models/class-encrypt-wp-error.php';
		require_once $this->plugin_path . 'models/class-encrypt-wp-bulk-user-result.php';
		require_once $this->plugin_path . 'models/class-encrypt-wp-field-state.php';
		require_once $this->plugin_path . 'models/class-encrypt-wp-options.php';
		require_once $this->plugin_path . 'models/class-encrypt-wp-field.php';

		// Hooks
		require_once $this->plugin_path . 'hooks/class-encrypt-wp-hooks.php';
		require_once $this->plugin_path . 'hooks/class-encrypt-wp-shortcodes.php';
		require_once $this->plugin_path . 'hooks/class-encrypt-wp-user-meta.php';
		require_once $this->plugin_path . 'hooks/class-encrypt-wp-user-fields.php';
		require_once $this->plugin_path . 'hooks/class-encrypt-wp-user-email.php';
		require_once $this->plugin_path . 'hooks/class-encrypt-wp-admin-settings.php';

	}

	/**
	 * Setup dependency injection rules
	 */
	private function setup_dependency_injection(){
		$dice = TrestianCore::setup($this->plugin_name, $this->version, $this->plugin_url, $this->plugin_path, $this->prefix);

		// Set all objects to be created as shared instance and Key Server Client
		$dice->addRule('*', [
			'shared'=>true,
			'substitutions' => [
				'CipherCore\\v1\\IKeyServerClient' => [
					'instance'=>'CipherCore\\v1\\Key_Server_Client'
				]
			]
		]);

		// Fetch the plugin options
		/**
		 * @var $options_manager EncryptWP_Options_Manager
		 */
		$options_manager = $dice->create('EncryptWP_Options_Manager');
		$this->options = $options_manager->get_options();


		// Enable or disable strict mode.
		$dice->addRule('CipherCore\\v1\\Settings', ['constructParams'=>[$this->options->strict_mode]]);

		return $dice;
	}

	public function get_plugin_name(){
		return $this->plugin_name();
	}

	public function get_version(){
		return $this->version();
	}

	public function run(){
		$this->hooks->load_hooks();
	}

	/**
	 * Get or create an instance of the encryptor class
	 * @return EncryptWP_Encryption_Manager
	 */
	public function encryptor(){
		return $this->dice->create('EncryptWP_Encryption_Manager');
	}

	/**
	 * Public getter for encryption enabled option. Used by pluggable hack.
	 * @return bool
	 */
	public function encrypt_enabled(){
		return $this->options->encrypt_enabled;
	}

	/**
	 * Public getter for encrypt email option. Used by pluggable hack.
	 * @return bool
	 */
	public function encrypt_email_enabled(){
		return $this->options->encrypt_email;
	}

	public function set_current_user_filter(){
		global $current_user;
		$current_user->filter = 'display';
	}

}

/**
 * Return the main instance of EncryptWP
 * @return EncryptWP
 */
function encrypt_wp(){
	return EncryptWP::instance();
}

// Instantiate plugin for sake of pluggables
encrypt_wp();

function run_encrypt_wp(){
	// Get instance of plugin class
	$plugin = encrypt_wp();

	// Run the plugin
	$plugin->run();
}

// Run after the latest version of Trestian WP Managers has been loaded
 add_action('plugins_loaded', 'run_encrypt_wp');