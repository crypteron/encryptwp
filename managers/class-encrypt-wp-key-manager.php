<?php
use CipherCore\v1\IKeyServerClient;

class EncryptWP_Key_Manager {
	/**
	 * @var IKeyServerClient
	 */
	protected $key_server_client;

	/**
	 * EncryptWP_Key_Manager constructor.
	 *
	 * @param IKeyServerClient $key_server_client
	 */
	public function __construct(IKeyServerClient $key_server_client) {
		$this->key_server_client = $key_server_client;
	}

	public function generate_key($AES_256 = true){
		return $this->key_server_client->generate_key($AES_256);
	}
}