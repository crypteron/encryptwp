<?php
use CipherCore\v1\Encryptor;
use CipherCore\v1\IKeyServerClient;

class EncryptWP_Shortcodes {
	/**
	 * @var Encryptor
	 */
	protected $encryption_manager;

	/**
	 * @var IKeyServerClient
	 */
	protected $key_manager;

	public function __construct(EncryptWP_Encryption_Manager $encryption_manager, EncryptWP_Key_Manager $key_manager ) {
		$this->encryption_manager = $encryption_manager;
		$this->key_manager        = $key_manager;
	}

	public function load_hooks(){
		add_shortcode('EncryptWP_Encrypt', array($this, 'encrypt'));
		add_shortcode('EncryptWP_Decrypt', array($this, 'decrypt'));
		add_shortcode('EncryptWP_GenerateKey', array($this, 'generate_key'));
		add_shortcode('EncryptWP_Search', array($this, 'search'));
	}

	public function encrypt($args, $content){
		return $this->encryption_manager->encrypt($content, null, false);
	}

	public function decrypt($args, $content){
		return $this->encryption_manager->decrypt($content, null, 'shortcode' );
	}

	public function generate_key($args){
		return $this->key_manager->generate_key();
	}

	public function search($atts){
		$atts = shortcode_atts(array(
			'key' => 'last_name',
			'value' => 'Guez'
		), $atts, 'EncryptWP_Search');

		$args = array(
			'meta_key' => $atts['key'],
			'meta_value' => $atts['value']
		);

		$query = new WP_User_Query($args);

		$result = '';
		/**
		 * @var $query->results WP_User[]
		 */
		if(! empty($query->results)){
			foreach($query->results as $user){
				/**
				 * @var $user WP_User
				 */
				$user->filter = 'display';
				$result .= "$user->first_name $user->last_name ($user->user_email)";
			}
		} else {
			$result = 'No user found';
		}

		return $result;
	}
}