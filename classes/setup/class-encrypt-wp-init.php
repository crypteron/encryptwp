<?php
use TrestianCore\v1\TrestianCore;

class EncryptWP_Init{

	/**
	 * @var string
	 */
	public $plugin_name;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var string
	 */
	public $plugin_path;

	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * @var string
	 */
	public $prefix;

	/**
	 * @var
	 */
	public $hooks;

	/**
	 * @var \Dice\Dice
	 */
	public $dice;

	public function __construct($plugin_file) {
		$this->plugin_name = 'encrypt-wp';
		$this->prefix = 'encrypt-wp';
		$this->version = '1.0';
		$this->plugin_path = plugin_dir_path($plugin_file);
		$this->plugin_url = plugin_dir_url($plugin_file);

		$this->load_dependencies();
		$this->setup_dependency_injection();

		$this->hooks = $this->dice->create('EncryptWP_Hooks');

	}

	private function load_dependencies(){
		// Libraries
		require_once $this->plugin_path . 'libs/cipher-core/cipher-core.php';

		// Load V1 of Trestian Core if not already loaded
		if(!class_exists('\TrestianCore\v1\TrestianCore')){
			require_once $this->plugin_path  . 'libs/trestian-core/trestian-core.php';
		}

		// Setup classes
		require_once $this->plugin_path . 'classes/setup/class-encrypt-wp-hooks.php';

		// Models
		require_once $this->plugin_path . 'classes/models/class-encrypt-wp-exception.php';
		require_once $this->plugin_path . 'classes/models/class-encrypt-wp-constants.php';

		// Hooks
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-shortcodes.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-user-meta.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-user-fields.php';
		require_once $this->plugin_path . 'classes/hooks/class-encrypt-wp-user-email.php';

	}

	private function setup_dependency_injection(){
		$this->dice = TrestianCore::setup($this->plugin_name, $this->version, $this->plugin_url, $this->plugin_path, $this->prefix);

		// Set all objects to be created as shared instance
		$this->dice->addRule('*', ['shared'=>true]);

	}

	public function run(){
		$this->hooks->load_hooks();
	}

}