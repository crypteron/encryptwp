<?php
namespace TrestianCore\v1;
class Options {
	/**
	 * Options Key used to persist CMB2 options
	 * @var string
	 */
	protected $cmb2_options_key;

	/**
	 * Trestian_Options constructor.
	 *
	 * @param $cmb2_options_key string
	 */
	function __construct($cmb2_options_key) {
		$this->cmb2_options_key = $cmb2_options_key;
	}

	/**
	 * Get options key used to persist CMB2 options
	 * @return string
	 */
	public function get_cmb2_options_key(){
		return $this->cmb2_options_key;
	}
}