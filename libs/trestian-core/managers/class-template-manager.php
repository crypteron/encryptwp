<?php
namespace TrestianCore\v1;

/**
 * Template loading functionality
 *
 * @since      1.0.0
 * @package    TrestianWPManagers
 * @subpackage TrestianWPManagers/managers
 * @author     Yaron Guez <yaron@trestian.com>
 */
class Template_Manager {

	/**
	 * @var Plugin_Settings
	 */
	private $settings;

	protected $message_template;

	/**
	 * @var string
	 */
	protected $template_location;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param $settings Plugin_Settings
	 */
	public function __construct( Plugin_Settings $settings, $message_template = null, $template_path = null) {
		$this->settings          = $settings;
		$this->message_template  = is_null($message_template) ? 'templates/public/content-trestian-messages.php' : $message_template;
		$this->template_location = is_null($template_path) ? 'templates/public/' : trailingslashit($template_path);
	}

	/**
	 * Gets the plugin file path
	 * @param $path
	 *
	 * @return string
	 */
	public function get_path($path){
		return $this->settings->get_plugin_path(). $path;
	}

	public function get_template_path($slug, $name=''){
		$template = '';

		// Look in yourtheme/slug-name.php
		if ( $name) {
			$template = locate_template( array( "{$slug}-{$name}.php") );
		}

		// If not yet found, look in plugin's slug-name.php
		$template_location = $this->template_location;
		if ( ! $template && $name && file_exists($this->get_path("{$template_location}{$slug}-{$name}.php" ) )) {
			$template = $this->get_path("{$template_location}{$slug}-{$name}.php");
		}

		// If not yet found, look in yourtheme/slug.php
		if ( ! $template) {
			$template = locate_template( array( "{$slug}.php") );
		}

		// If not yet found, look in plugin's slug.php
		if ( ! $template && file_exists($this->get_path("{$template_location}{$slug}.php" ) )) {
			$template = $this->get_path("{$template_location}{$slug}.php");
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'trestian_get_template_part', $template, $name );

		return $template;
	}


	/**
	 * Load a template part while allowing theme and developers to override it
	 * Modeled off of WooCommerce
	 *
	 * @access public
	 * @param string $name (default: '')
	 */
	public function get_template_part( $slug, $name = '', $data=array()) {
		$template = $this->get_template_path($slug, $name);

		if ( !$template ) {
			return;
		}

		// Load globals to be accessible in template
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		// Load any query variables to be accessible in template
		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		// If a search variable was loaded from the query vars, escape its contents
		if ( isset( $s ) ) {
			$s = esc_attr( $s );
		}

		// Load any data passed in as array
		extract($data);

		// Expose manager to template
		$htm = $this;

		// Launch the template!
		require( $template );
	}

	/**
	 * Load a template including any data passed in along with an instance of the template manager
	 * @param $path
	 * @param array $data
	 * @param bool $return - whether to return the data or output it
	 * @return void|string
	 */
	public function load_template($path, $data=array(), $return=false){
		// Extract data to be available in template
		extract($data);

		// Expose the template manager to template
		$htm = $this;

		// If return is true, capture the output and return it
		if($return){
			ob_start();
		}

		require($this->get_path($path));

		if($return){
			return ob_get_clean();
		}

	}

	public function messages($success = null, $error=null){
		$this->load_template($this->message_template, array(
			'success'=>$success,
			'error'=>$error
		));
	}

	public function parse_content($content){
		global $wp_embed;
		$content = $wp_embed->autoembed( $content );
		$content = $wp_embed->run_shortcode( $content );
		$content = wpautop( $content );
		$content = do_shortcode( $content );
		return $content;
	}

}
