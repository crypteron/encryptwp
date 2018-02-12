<?php
class EncryptWP_Options_Manager{
	/**
	 * @var EncryptWP_Options
	 */
	protected $options;


	public function __construct() {
		$this->refresh_options();
	}

	public function refresh_options(){
		$this->options = $this->load_options();
	}

	private function load_options(){

		$options = get_option(EncryptWP_Constants::OPTION_NAME);
		if($options === false)
			$options = new EncryptWP_Options();

		$options = $this->set_defaults($options);
		$options = $this->index_fields($options);
		$options = $this->set_required_fields($options);

		return $options;
	}

	private function set_required_fields(EncryptWP_Options $options){
		$options->user_meta_fields[EncryptWP_Constants::EMAIL_META_KEY] = new EncryptWP_Field(null, EncryptWP_Field_State::ENCRYPTED_SEARCHABLE, EncryptWP_Constants::EMAIL_META_KEY);
		return $options;
	}

	private function index_fields(EncryptWP_Options $options){
		$user_fields = array();
		foreach($options->user_fields as $field)
			$user_fields[$field->slug] = $field;
		$options->user_fields = $user_fields;

		$meta_fields = array();
		foreach($options->user_meta_fields as $field)
			$meta_fields[$field->slug] = $field;
		$options->user_meta_fields = $meta_fields;

		return $options;
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