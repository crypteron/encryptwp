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

	public function generate_iv() {
		return random_bytes(Constants::IV_SIZE_BYTES);
	}

	/**
	 * Encrypts parameters with AES-GCM
	 *
	 * @param EncryptParameters $parameters - parameters to encrypt
	 * @param bool $base64 - base64 encode result
	 *
	 * @return string - Encrypted data including header and tag
	 */
	public function encryptWithParameters($parameters, $base64) {
		$ciphertext_with_tag = AESGCM::encryptAndAppendTag($parameters->key, $parameters->iv, $parameters->plaintext, $parameters->aad, Constants::TAG_SIZE_BITS);
		
		$header = new CipherCore_Header();
		$header->IV = $parameters->iv;
		$header->AAD = $parameters->aad;
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
		$encryptParameters = new EncryptParameters();
		$encryptParameters->plaintext = $clear_text;
		// TODO - assemble key request object
		$encryptParameters->key = $this->key_server_client->read_sec_part_key(null);
		$encryptParameters->iv = $this->generate_iv();

		$encrypted_record = $this->encryptWithParameters($encryptParameters, $base64);
		return $encrypted_record;
	}

	/**
	 * Decrypts parameters with AES-GCM
	 *
	 * @param DecryptParameters $parameters - parameters to decrypt
	 * @param bool $base64 - base64 encode result
	 *
	 * @return string - Decrypted plaintext
	 */
	public function decryptWithParameters($parameters, $base64) {
		if($base64) {
			$parameters->ciphertext = base64_decode($parameters->ciphertext);
		}
		$header_container = $this->serializer->deserialize($parameters->ciphertext);
		$iv = $header_container->header->IV;
		$aad = $header_container->header->AAD;
		$ciphertext = substr($parameters->ciphertext, $header_container->bytesRead);

		$plaintext = AESGCM::decryptWithAppendedTag($parameters->key, $iv, $ciphertext, $aad, Constants::TAG_SIZE_BITS);
		return $plaintext;
	}

	/**
	 * Decrypts binary text and authenticates additional data
	 *
	 * @param string $encrypted_record - Encrypted record. Binary.
	 * @param string | null $aad - Additional authenticated data
	 *
	 * @return string - Decrypted plaintext
	 */
	public function decrypt($encrypted_record, $base64 = true) {
		$decryptParameters = new DecryptParameters();
		$decryptParameters->ciphertext = $encrypted_record;
		// TODO - assemble key request object
		$decryptParameters->key = $this->key_server_client->read_sec_part_key(null);

		$clear_text = $this->decryptWithParameters($decryptParameters, $base64);
		return $clear_text;
	}

	/**
	 * Determines if encrypted or not. If it is, it returns the decrypted text. If not, it returns false.
	 * @param $text
	 *
	 * @return bool|string
	 * @throws \Exception
	 */
	public function try_decrypt($text){
		try{
			$clear_text = $this->decrypt($text);

		} catch(\AvroException $e){
			return false;
		} catch(CipherCore_Deserialize_Exception $e){
			return false;
		} catch(\Exception $e){
			// TODO log this
			throw $e;
		}

		return $clear_text;

	}

}
