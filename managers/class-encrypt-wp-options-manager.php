<?php
class EncryptWP_Options_Manager{
	/**
	 * @var EncryptWP_Options
	 */
	protected $options;


	public function __construct() {
		$this->load_options();
	}

	public function load_options(){

		$options = get_option(EncryptWP_Constants::OPTION_NAME);
		if($options === false)
			$options = new EncryptWP_Options();

		$options = $this->set_defaults($options);

		$this->options = $options;
	}


	private function set_defaults(EncryptWP_Options $options){
		if(is_null($options->user_fields)){
			$options->user_fields = EncryptWP_Defaults::get_user_fields();
		}

		if(is_null($options->user_meta_fields)){
			$options->user_meta_fields = EncryptWP_Defaults::get_user_meta_fields();
		}

		if(is_null($options->admin_notify)){
			$options->admin_notify = [get_bloginfo('admin_email')];
		}

		return $options;
	}

	public function get_options(){
		return $this->options;
	}

	public function update_options($options){
		$this->options = $options;
		update_option(EncryptWP_Constants::OPTION_NAME, $this->options);
	}

}