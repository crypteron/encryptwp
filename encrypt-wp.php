<?php
/**
 *
 * @link              http://trestian.com
 * @since             1.0.0
 * @package           encryptwp
 *
 * @wordpress-plugin
 * Plugin Name:       EncryptWP
 * Plugin URI:        https://bitbucket.org/Crypteron/cipherwp
 * Description:       Adds military grade encryption and tamper protection to WordPress
 * Version:           1.0.0
 * Author:            Crypteron
 * Author URI:        https://crypteron.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       encryptwp
 * Domain Path:       /languages
 * BitBucket Plugin URI: https://bitbucket.org/Crypteron/cipherwp
 * BitBucket Branch: development
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load Trestian WP Managers
require_once  plugin_dir_path( __FILE__ ) . 'libs/trestian-wp-managers/trestian-wp-managers.php';


function run_encrypt_wp(){
	/**
	 * The core plugin class
	 */
	require plugin_dir_path( __FILE__ ) . 'classes/setup/class-encrypt-wp-init.php';
	$plugin = new EncryptWP_Init(__FILE__);
	$plugin->run();
}

// Run after the latest version of Trestian WP Managers has been loaded
add_action('trestian_wp_managers_loaded', 'run_encrypt_wp');