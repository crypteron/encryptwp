<?php
use CipherCore\v1\Encryptor;

class EncryptWP_Shortcodes {
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	public function __construct(Encryptor $encryptor) {
		$this->encryptor = $encryptor;
	}

	public function load_hooks(){
		add_shortcode('EncryptWP_Encrypt', array($this, 'encrypt'));
		add_shortcode('EncryptWP_Decrypt', array($this, 'decrypt'));
		add_shortcode('EncryptWP_GenerateKey', array($this, 'generate_key'));
		add_shortcode('EncryptWP_Search', array($this, 'search'));
	}

	public function encrypt($args, $content){
		return encrypt_wp()->encryptor()->encrypt($content, null, false);
	}

	public function decrypt($args, $content){
		return encrypt_wp()->encryptor()->decrypt($content, null, 'shortcode' );
	}

	public function generate_key($args){
		return $this->encryptor->key_server_client->generate_key();
	}

	public function search($atts){
		$atts = shortcode_atts(array(
			'key' => 'first_name',
			'value' => 'Yaron'
		), $atts, 'EncryptWP_Search');

		$args = array(
			'meta_key' => $atts['key'],
			'meta_value' => $atts['value'],
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'secure_user_email',
					'value' => 'Yaron@trestian.com',
					'compare' => 'LIKE'
				)
			)
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