<?php
namespace TrestianCore\v1;

class Cmb2_Manager implements IOptions_Manager {

	/**
	 * @var Plugin_Settings
	 */
	protected $settings;

	/**
	 * @var Options
	 */
	protected $options;

	const REGISTER_ACTION = 'cmb2_admin_init';


	public function __construct(Plugin_Settings $settings, Options $options) {
		$this->settings = $settings;
		$this->options = $options;
	}

	/**
	 * @param $key string
	 *
	 * @return mixed
	 */
	public function get_option_value( $key, $default = null) {
		if ( function_exists( 'cmb2_get_option' ) ) {
			// Use cmb2_get_option as it passes through some key filters.
			return cmb2_get_option( $this->options->get_cmb2_options_key(), $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( $this->options->get_cmb2_options_key() );
		if($opts === false){
			return $default;
		}

		$val = $default;

		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}


	/**
	 * Register an option field for a page in ACF
	 * @param Page_Container $page_container
	 *
	 * @return void
	 */
	public function register_page_options( IPage $page) {
		$cmb = new_cmb2_box( array(
			'id'         => $page->get_option_group_key(),
			'hookup'     => false,
			'cmb_styles' => false,
			'show_on'    => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->options->get_cmb2_options_key())
			),
		) );

		// Set our CMB2 fields
		$field = $cmb->add_field( array(
			'name' => $page->get_option_field_label(),
			'desc' => 'Select the page',
			'id'   => $page->get_option_field_name(),
			'type' => 'post_search_text',
			'post_type'   => 'page',
			'select_type' => 'radio',
			'select_behavior' => 'replace'
		) );
	}


	/**
	 * Get the action used to register the page options
	 * @return string
	 */
	public function get_register_action() {
		return self::REGISTER_ACTION;
	}
}