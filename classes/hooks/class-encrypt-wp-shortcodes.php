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
		add_shortcode('EncryptWP_GenerateKey', array($this, 'generate_key'));
	}

	public function encrypt($args, $content){
		return $this->encryptor->encrypt($content);
	}

	public function decrypt($args, $content){
		return $this->encryptor->decrypt($content );
	}

	public function generate_key($args){
		return $this->encryptor->key_server_client->generate_key();
	}
}