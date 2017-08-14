<?php
namespace TrestianCore\v1;

/**
 * Configure new pages
 *
 *
 * @package    TrestianWPManagers
 * @subpackage TrestianWPManagers/managers
 * @author     Yaron Guez <yaron@trestian.com>
 */
class Trestian_Page_Manager{
	/**
	 * @var ITrestian_Page[]
	 */
	public $pages;

	/**
	 * @var Trestian_Page_Container[]
	 */
	public $page_containers;

	/**
	 * @var Trestian_Plugin_Settings
	 */
	protected $settings;

	/**
	 * @var ITrestian_Options_Manager
	 */
	protected $options_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param $options_prefix
	 */
    public function __construct(Trestian_Plugin_Settings $settings, ITrestian_Options_Manager $options_manager ) {
		$this->pages = array();
		$this->page_containers = array();
		$this->settings = $settings;
		$this->options_manager = $options_manager;
    }

	/**
	 * Add page to be setup on plugin load
	 *
	 * @param ITrestian_Page $page
	 */
    public function add_page( ITrestian_Page $page){
	    // Append to list of pages indexed by option field name
    	$this->pages[$page->get_option_field_name()] = $page;
    }

	/**
	 * Load all pages
	 */
    public function load(){
	    foreach ($this->pages as $page){
		    $this->page_containers[] = $this->setup_page($page);
	    }
	}

	/**
	 * Given a class matching the Trestian page interface, and a page field, configure it for restricting access and displaying content
	 *
	 * @param ITrestian_Page $page
	 * @param $page_field
	 *
	 * @return Trestian_Page_Container
	 */
    public function setup_page( ITrestian_Page $page){
	    // Set Page ID
	    $page_id = $this->options_manager->get_option_value($page->get_option_field_name(), -1);
	    $page->set_page_id($page_id);

		// Create page container for page specific hooks
    	$page_container = new Trestian_Page_Container($page, $this->options_manager);

    	// Register all page hooks
	    add_action($this->options_manager->get_register_action(), array($page_container, 'register_page_options'));
	    add_action('template_redirect', array($page_container, 'restrict_page' ));
	    add_action('the_content', array($page_container, 'display_content'));
	    add_action('wp_enqueue_scripts', array($page_container, 'load_scripts'));
	    add_action('wp_enqueue_scripts', array($page_container, 'load_styles'));

	    return $page_container;
	}

	/**
	 * Get the Page ID for a given option name field
	 *
	 * @param $option_field_name
	 *
	 * @return int
	 */
	public function get_page_id($option_field_name) {
    	if(!isset($this->pages[$option_field_name])){
    		return -1;
	    } else {
    		return $this->pages[$option_field_name]->get_page_id();
	    }
	}

	/**
	 * Get the permalink for a page ID, page option field name, or ITrestian_Page object
	 * @param $page_identifier int | string | ITrestian_Page
	 *
	 * @return false|string
	 */
	public function get_page_url($page_identifier){
		if(is_numeric($page_identifier)){
			$page_id = $page_identifier;
		} else if($page_identifier instanceof ITrestian_Page){
			$page_id = $page_identifier->get_page_id();
		} else {
			$page_id = $this->get_page_id($page_identifier);
		}

		return get_permalink($page_id);
	}
}

