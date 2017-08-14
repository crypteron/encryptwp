<?php
/**
 * Created by PhpStorm.
 * User: yaronguez
 * Date: 3/16/17
 * Time: 3:52 PM
 */
namespace TrestianCore\v1;

interface ITrestian_Page {

	/**
	 *
	 */
	public function restrict_page();

	/**
	 * @param $content string
	 *
	 * @return string
	 */
	public function display_content($content);

	/**
	 * Load JS files on page
	 */
	public function load_scripts();

	/**
	 * Load CSS files on page
	 */
	public function load_styles();

	/**
	 * @return string
	 */
	public function get_option_group_key();

	/**
	 * @return string
	 */
	public function get_option_field_name();

	/**
	 * @return string
	 */
	public function get_option_field_label();

	/**
	 * @param $page_id int
	 */
	public function set_page_id($page_id);

	/**
	 * @return int
	 */
	public function get_page_id();

}