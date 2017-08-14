<?php
/**
 * Created by PhpStorm.
 * User: yaronguez
 * Date: 4/3/17
 * Time: 5:52 PM
 */
namespace TrestianCore\v1;

interface ITrestian_Options_Manager {
	/**
	 * @param $key string
	 *
	 * @return mixed
	 */
	public function get_option_value($key, $default);

	/**
	 * @param ITrestian_Page $page;
	 *
	 * @return void
	 */
	public function register_page_options(ITrestian_Page $page);

	/**
	 * Get the action used to register the page options
	 * @return string
	 */
	public function get_register_action();

}