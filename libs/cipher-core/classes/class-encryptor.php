<?php
namespace CipherCore\v1;
use AESGCM\AESGCM;

class Encryptor {

	/**
	 * @var IKeyServerClient
	 */
	private $key_server_client;

	/**
	 * @var Serializer
	 */
	private $serializer;

	public function __construct() {
		$this->key_server_client = new Key_Server_Client();
		$this->serializer = new Serializer();
	}

	public function generate_iv(){
		return random_bytes(Constants::IV_SIZE_BYTES);
	}


	/**
	 * Encrypts text and additional data with AES-GCM
	 *
	 * @param string|null $clear_text - Text to encrypt
	 * @param string|null $aad - Additional Authenticated Data
	 *
	 * @return string - Encrypted binary data including header and tag
	 */
	public function encrypt($clear_text = null, $aad = null, $base64 = true){
		// TODO - assemble key request object
		$key = $this->key_server_client->read_sec_part_key(null);
		$iv = $this->generate_iv();

		$ciphertext_with_tag = AESGCM::encryptAndAppendTag($key, $iv, $clear_text, $aad, Constants::TAG_SIZE_BITS);

		$header = new CipherCore_Header();
		$header->IV = $iv;
		$serialized_header = $this->serializer->serialize($header);
		$encrypted_record =  $serialized_header. $ciphertext_with_tag;

		if($base64){
			$encrypted_record = base64_encode($encrypted_record);
		}
		return $encrypted_record;
	}

	/**
	 * Decrypts binary text and authenticates additional data
	 *
	 * @param string $encrypted_record - Encrypted record. Binary.
	 * @param string | null $aad - Additional authenticated data
	 *
	 * @return string - Decrypted clear text
	 */
	public function decrypt($encrypted_record, $aad = null, $base64 = true){
		if($base64){
			$encrypted_record = base64_decode($encrypted_record);
		}
		$header_container = $this->serializer->deserialize($encrypted_record);
		$iv = $header_container->header->IV;
		$cipher_text = substr($encrypted_record, $header_container->bytesRead );
		$key = $this->key_server_client->read_sec_part_key(null);

		$clear_text = AESGCM::decryptWithAppendedTag($key, $iv, $cipher_text, $aad, Constants::TAG_SIZE_BITS);
		return $clear_text;

	}


}
