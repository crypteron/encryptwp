<?php
class EncryptWP_Shortcodes {
	protected $encrypt_manager;

	public function __construct(EncryptWP_Manager $encrypt_manager) {
		$this->encrypt_manager = $encrypt_manager;
	}

	public function load_hooks(){
		add_shortcode('EncryptWP_Key', array($this, 'generate_key'));
		add_shortcode('EncryptWP_Encrypt', array($this, 'encrypt'));
		add_shortcode('EncryptWP_Decrypt', array($this, 'decrypt'));
	}

	public function generate_key(){
		$key = $this->encrypt_manager->generate_key();
		return $key;
	}

	public function encrypt($args, $content){
		return $this->encrypt_manager->encrypt($content);
	}

	public function decrypt($args, $content){
		return $this->encrypt_manager->decrypt($content );
	}
}