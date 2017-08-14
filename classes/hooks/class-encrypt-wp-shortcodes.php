<?php

class EncryptWP_Shortcodes {
	/**
	 * @var \CipherCore\v1\Encryptor
	 */
	protected $encryptor;

	public function __construct(\CipherCore\v1\Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function load_hooks(){
		add_shortcode('EncryptWP_Encrypt', array($this, 'encrypt'));
		add_shortcode('EncryptWP_Decrypt', array($this, 'decrypt'));
	}

	public function encrypt($args, $content){
		return $this->encryptor->encrypt($content);
	}

	public function decrypt($args, $content){
		return $this->encryptor->decrypt($content );
	}
}