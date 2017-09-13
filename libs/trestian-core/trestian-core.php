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
require_once 'interfaces/interface-trestian-page.php';
require_once 'interfaces/interface-trestian-options-manager.php';

// Managers
require_once 'managers/class-trestian-ajax-manager.php';
require_once 'managers/class-trestian-page-manager.php';
require_once 'managers/class-trestian-template-manager.php';
require_once 'managers/class-trestian-acf-manager.php';
require_once 'managers/class-trestian-cmb2-manager.php';

// Models
require_once 'models/class-trestian-page.php';
require_once 'models/class-trestian-page-container.php';
require_once 'models/class-trestian-plugin-settings.php';
require_once 'models/class-trestian-constants.php';
require_once 'models/class-trestian-options.php';


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
		wp_register_style('TrestianCore', plugin_dir_url(__FILE__) . 'assets/css/trestian-core.css', self::VERSION);


		if(is_null($dice)){
			$dice = new \Dice\Dice;
		}

		// Parse optios and defaults
		$options = wp_parse_args($options, [
			'cmb2_options_key' => $prefix . '_' . Trestian_Constants::CMB2_OPTIONS_KEY
		]);

		// Set up options object
		$dice->addRule('Trestian_Options', [
			'shared'=>true,
			'constructParams' => [$options['cmb2_options_key']]
		]);

		// Configure plugin settings
		$dice->addRule( 'TrestianCore\\v1\\Trestian_Plugin_Settings', [
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
			'ITrestian_Options_Manager' => [
				'instance'=>$options_manager
			]
		]]);

		// Set all Trestian WP Managers as shared instances
		$managers = [
			'Trestian_Acf_Manager',
			'Trestian_Cmb2_Manager',
			'Trestian_Ajax_Manager',
			'Trestian_Page_Manager',
			'Trestian_Template_Manager'
		];

		foreach ($managers as $manager){
			$dice->addRule($manager, ['shared'=>true]);
		}

		return $dice;

	}


}