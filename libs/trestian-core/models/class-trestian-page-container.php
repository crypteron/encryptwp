<?php
namespace TrestianCore\v1;

/**
 * Page container class
 *
 * User: yaronguez
 * Date: 3/16/17
 * Time: 7:15 PM
 */
class Trestian_Page_Container {
	/**
	 * @var ITrestian_Page
	 */
	public $page;

	/**
	 * @var ITrestian_Options_Manager
	 */
	protected $options_manager;

	/**
	 * Trestian_Page_Container constructor.
	 *
	 * @param ITrestian_Page $page
	 * @param $prefix string
	 */
	public function __construct(ITrestian_Page $page, ITrestian_Options_Manager $options_manager) {
		$this->page = $page;
		$this->options_manager = $options_manager;
	}

	/**
	 * Restrict access to page based
	 */
	public function restrict_page(){
		if($this->is_page()) {
			$this->page->restrict_page();
		}
	}

	/**
	 * Display content on page
	 */
	public function display_content($content){
		if($this->is_page()){
			return $this->page->display_content($content);
		}

		return $content;
	}

	/**
	 * Load styles on page
	 */
	public function load_styles(){
		if($this->is_page()){
			$this->page->load_styles();
		}
	}

	/**
	 * Load scripts on page
	 */
	public function load_scripts(){
		if($this->is_page()){
			$this->page->load_scripts();
		}
	}

	/**
	 * Register page options for this container
	 */
	public function register_page_options(){
		$this->options_manager->register_page_options($this->page);
	}

	/**
	 * Helper function to determine if user is on page
	 * @return bool
	 */
	private function is_page(){
		return is_page($this->page->get_page_id());
	}

}