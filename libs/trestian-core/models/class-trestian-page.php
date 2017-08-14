<?php
/**
 * Created by PhpStorm.
 * User: yaronguez
 * Date: 3/16/17
 * Time: 4:33 PM
 */
namespace TrestianCore\v1;

abstract class Trestian_Page implements ITrestian_Page{
	/**
	 * @var string
	 */
	protected $option_field_name;

	/**
	 * @var string
	 */
	protected $option_field_label;

	/**
	 * @var int
	 */
	protected $page_id;

	/**
	 * @var string
	 */
	protected $option_group_key;

	/**
	 * @var array - View Model
	 */
	protected $vm;

	/**
	 * @var Trestian_Template_Manager
	 */
	protected $template_manager;


	protected $option_field_order;

	/**
	 * @var string
	 */
	protected $template;

	public function __construct(Trestian_Template_Manager $template_manager) {
		$this->template_manager = $template_manager;
		$this->vm = array(
			'success'=>null,
			'error'=>null
		);
	}

	/**
	 * Passes view model variables to the page template and fetches the content string. Typically called within display_content once view model is parsed.
	 * @return string
	 */
	protected function get_content(){
		return $this->template_manager->load_template($this->template, $this->vm, true);
	}

	/**
	 * Optionally restrict access to page
	 */
	public function restrict_page(){
		// Default is to allow all access to page
	}

	/***
	 * Optionally override content of page
	 * @param string $content
	 *
	 * @return string
	 */
	public function display_content( $content ){
		// Default is to use original content
		return $content;
	}

	/**
	 * Optionally load CSS files on page
	 */
	public function load_styles(){

	}

	/**
	 * Optionally load JS files on page
	 */
	public function load_scripts() {
		// Default is to load no JS files
	}

	/**
	 * Option Group Key getter
	 * @return string
	 */
	public function get_option_group_key() {
		return $this->option_group_key;
	}

	/**
	 * Option Field Name getter
	 * @return string
	 */
	public function get_option_field_name() {
		return $this->option_field_name;
	}

	/**
	 * Option Field Label getter
	 * @return string
	 */
	public function  get_option_field_label(){
		return $this->option_field_label;
	}

	/**
	 * Page ID setter
	 * @param $page_id
	 */
	public function set_page_id($page_id) {
		$this->page_id = $page_id;
	}

	/**
	 * Page ID getter
	 * @return int
	 */
	public function get_page_id() {
		return $this->page_id;
	}


}