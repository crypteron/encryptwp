<?php
class EncryptWP_Init{

	public $plugin_name;

	public $version;

	public $plugin_path;

	public $plugin_url;

	public $prefix;

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
		require_once $this->plugin_path . 'libs/crypteron-php-labs/avro-header/ciphercore-header.php';
		require_once $this->plugin_path . 'libs/trestian-wp-managers/trestian-wp-managers.php';

		// Composer
		require_once $this->plugin_path . 'vendor/autload.php';

		// Setup classes
		require_once $this->plugin_path . 'classes/class-encrypt-wp-hooks.php';

		// Managers
		require_once $this->plugin_path . 'classes/managers/class-encrypt-wp-manager.php';

		// Models
		require_once $this->plugin_path . 'classes/models/class-encrypt-wp-exception.php';



	}

	private function setup_dependency_injection(){
		$this->dice = twpm_setup_dice($this->plugin_name, $this->version, $this->plugin_url, $this->plugin_path, $this->prefix);

		// Set all objects to be created as shared instance
		$this->dice->addRule('*', ['shared'=>true]);

	}

}