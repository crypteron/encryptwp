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

	public function encryptWithParameters($plaintext, $key, $iv, $aad, $base64) {
		$ciphertext_with_tag = AESGCM::encryptAndAppendTag($key, $iv, $plaintext, $aad, Constants::TAG_SIZE_BITS);
		
		$header = new CipherCore_Header();
		$header->IV = $iv;
		$header->AAD = $aad;
		$serialized_header = $this->serializer->serialize($header);

		$encrypted_record =  $serialized_header . $ciphertext_with_tag;
		if($base64) {
			$encrypted_record = base64_encode($encrypted_record);
		}
		return $encrypted_record;
	}

	/**
	 * Encrypts text and additional data with AES-GCM
	 *
	 * @param string|null $clear_text - Text to encrypt
	 * @param string|null $aad - Additional Authenticated Data
	 *
	 * @return string - Encrypted binary data including header and tag
	 */
	public function encrypt($clear_text = null, $aad = null, $base64 = true) {
		// TODO - assemble key request object
		$key = $this->key_server_client->read_sec_part_key(null);
		$iv = $this->generate_iv();

		$encrypted_record = $this->encryptWithParameters($clear_text, $key, $iv, $aad, $base64);
		return $encrypted_record;
	}

	public function decryptWithParameters($encrypted, $key, $base64) {
		if($base64) {
			$encrypted = base64_decode($encrypted);
		}
		$header_container = $this->serializer->deserialize($encrypted);
		$iv = $header_container->header->IV;
		$aad = $header_container->header->AAD;
		$ciphertext = substr($encrypted, $header_container->bytesRead);

		$plaintext = AESGCM::decryptWithAppendedTag($key, $iv, $ciphertext, $aad, Constants::TAG_SIZE_BITS);
		return $plaintext;
	}

	/**
	 * Decrypts binary text and authenticates additional data
	 *
	 * @param string $encrypted_record - Encrypted record. Binary.
	 * @param string | null $aad - Additional authenticated data
	 *
	 * @return string - Decrypted clear text
	 */
	public function decrypt($encrypted_record, $base64 = true) {
		// TODO - assemble key request object
		$key = $this->key_server_client->read_sec_part_key(null);

		$clear_text = $this->decryptWithParameters($encrypted_record, $key, $base64);
		return $clear_text;
	}

}
