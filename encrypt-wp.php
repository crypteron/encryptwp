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
 * Version:           1.0.1
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
	 * @var \Dice\Dice
	 */
	protected $dice;

	public function __construct() {
		$this->plugin_name = 'encrypt-wp';
		$this->prefix = 'encrypt-wp';
		$this->version = '1.0';
		$this->plugin_path = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);

		$this->load_dependencies();
		$this->setup_dependency_injection();

		$this->hooks = $this->dice->create('EncryptWP_Hooks');
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

		// Models
		require_once $this->plugin_path . 'classes/models/class-encrypt-wp-exception.php';
		require_once $this->plugin_path . 'classes/models/class-encrypt-wp-constants.php';

		// Hooks
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-hooks.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-shortcodes.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-user-meta.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-user-fields.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-user-email.php';

	}

	/**
	 * Setup dependency injection rules
	 */
	private function setup_dependency_injection(){
		$this->dice = TrestianCore::setup($this->plugin_name, $this->version, $this->plugin_url, $this->plugin_path, $this->prefix);

		// Set all objects to be created as shared instance
		$this->dice->addRule('*', ['shared'=>true]);

		// Enable or disable strict mode.
		// TODO: move this
		$this->dice->addRule('CipherCore\\v1\\Settings', ['constructParams'=>[EncryptWP_Constants::STRICT_MODE]]);

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
	 * @return \CipherCore\v1\Encryptor
	 */
	public function encryptor(){
		return $this->dice->create('CipherCore\\v1\\Encryptor');
	}

}

/**
 * Return the main instance of EncryptWP
 * @return EncryptWP
 */
function encrypt_wp(){
	return EncryptWP::instance();
}

function run_encrypt_wp(){
	// Instantiate the plugin class
	$plugin = encrypt_wp();

	// Run the plugin
	$plugin->run();
}

// Run after the latest version of Trestian WP Managers has been loaded
add_action('plugins_loaded', 'run_encrypt_wp');