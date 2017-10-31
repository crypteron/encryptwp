<?php
/**
 *
 * @link              http://trestian.com
 * @since             1.0.0
 * @package           trestian-core
 *
 */
namespace TrestianCore\v1;

if (class_exists('TrestianCore', false)){
	return;
}

// Dice - Dependancy injection. Only load if not loaded elsewhere
if(!class_exists('\\Dice\\Dice', false)) {
	require_once 'libs/Dice.php';
}
// Kint
if(WP_DEBUG){
	require_once 'libs/kint/Kint.class.php';
}

// Interfaces
require_once 'interfaces/interface-page.php';
require_once 'interfaces/interface-options-manager.php';

// Managers
require_once 'managers/class-ajax-manager.php';
require_once 'managers/class-page-manager.php';
require_once 'managers/class-template-manager.php';
require_once 'managers/class-acf-manager.php';
require_once 'managers/class-cmb2-manager.php';
require_once 'managers/class-admin-notice-manager.php';

// Models
require_once 'models/class-page.php';
require_once 'models/class-page-container.php';
require_once 'models/class-plugin-settings.php';
require_once 'models/class-constants.php';
require_once 'models/class-options.php';
require_once 'models/class-admin-notice.php';

class TrestianCore {
	/**
	 * Current version number
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';


	/**
	 * Setup Trestian Core, configure dependency injection, and return a Dice container.
	 *
	 * @param $plugin_name
	 * @param $version
	 * @param $plugin_url
	 * @param $plugin_path
	 * @param $prefix
	 * @param string $custom_fields
	 * @param \Dice\Dice|null $dice
	 * @param array $options
	 *
	 * @return \Dice\Dice
	 */
	public static function setup($plugin_name, $version, $plugin_url, $plugin_path, $prefix, $custom_fields = 'ACF', \Dice\Dice $dice = null, $options = array()){
		// Register script handles to be enqueued later
		wp_register_script('TrestianCore', plugin_dir_url(__FILE__) . 'assets/js/trestian-core.js', array('jquery', 'jquery-form'), self::VERSION);
		wp_localize_script('TrestianCore', 'TrestianCoreArgs', array('ajaxurl' => admin_url('admin-ajax.php')));
		wp_register_style('TrestianCore', plugin_dir_url(__FILE__) . 'assets/css/trestian-core.css', self::VERSION);


		if(is_null($dice)){
			$dice = new \Dice\Dice;
		}

		// Parse optios and defaults
		$options = wp_parse_args($options, [
			'cmb2_options_key' => $prefix . '_' . Constants::CMB2_OPTIONS_KEY
		]);

		// Set up options object
		$dice->addRule('TrestianCore\\v1\\Options', [
			'shared'=>true,
			'constructParams' => [$options['cmb2_options_key']]
		]);

		// Configure plugin settings
		$dice->addRule( 'TrestianCore\\v1\\Plugin_Settings', [
			'shared' => true,
			'constructParams' => [$plugin_name, $version, $plugin_url, $plugin_path, $prefix]
		]);

		// Determine Options Manager
		if($custom_fields == 'ACF') {
			$options_manager = 'Trestian_Acf_Manager';
		} else if($custom_fields == 'CMB2') {
			$options_manager = 'Trestian_Cmb2_Manager';
		} else {
			throw new Exception('Invalid custom fields manager provided. Only ACF or CMB2 are supported.');
		}

		// Set Options Manager
		$dice->addRule('*', ['substitutions' => [
			'TrestianCore\\v1\\IOptions_Manager' => [
				'instance'=>"TrestianCore\\v1\\$options_manager"
			]
		]]);

		// Set all Trestian Core Managers as shared instances
		$managers = [
			'TrestianCore\\v1\\Acf_Manager',
			'TrestianCore\\v1\\Cmb2_Manager',
			'TrestianCore\\v1\\Ajax_Manager',
			'TrestianCore\\v1\\Page_Manager',
			'TrestianCore\\v1\\Template_Manager'
		];

		foreach ($managers as $manager){
			$dice->addRule($manager, ['shared'=>true]);
		}

		return $dice;

	}


}