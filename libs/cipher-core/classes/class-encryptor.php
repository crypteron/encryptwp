<?php
namespace CipherCore\v1;
use AESGCM\AESGCM;

class Encryptor {
	// Search tokens will only universally work when there is no padding in the Base64 encoded version.
  // Since Base64 is encoded in groups of 6 bits and bytes are 8 bits, they overlap every 24 bits or 3 bytes.
	const PREFIX_DIVISIBLE_BY = 3;

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

	/**
	 * Generates a search token for the given encryption parameters.
	 *
	 * @param EncryptParameters $parameters - parameters to tokenize
	 *
	 * @return string - binary token value
	 */
	public function tokenForParameters($parameters) {
		return hash_hmac(Constants::HASH_ALGORITHM, $parameters->plaintext, $parameters->tokenKey, true);
	}

	public function searchPrefixForParameters($parameters, $base64) {
		$magicBlockLength = mb_strlen(Constants::MAGIC_BLOCK, '8bit');
		$searchPrefixSize = $magicBlockLength + Constants::TOKEN_SIZE_BYTES;
		$searchPrefixSize -= $searchPrefixSize % self::PREFIX_DIVISIBLE_BY;
		$header = new CipherCore_Header();
		$header->IV = '';
		$header->Token = $this->tokenForParameters($parameters);
		$headerBytes = $this->serializer->serialize($header);
		$searchPrefix = substr($headerBytes, 0, $searchPrefixSize);
		if($base64) {
			$searchPrefix = base64_encode($searchPrefix);
		}
		return $searchPrefix;
	}

	public function searchPrefix($plaintext, $base64 = true) {
		$parameters = new EncryptParameters();
		$parameters->plaintext = $plaintext;
		// TODO - assemble key request object
		$parameters->tokenKey = $this->key_server_client->read_sec_part_key(null);
		$searchPrefix = $this->searchPrefixForParameters($parameters, $base64);
		return $searchPrefix;
	}

	/**
	 * Encrypts parameters with AES-GCM
	 *
	 * @param EncryptParameters $parameters - parameters to encrypt
	 * @param bool $base64 - Base64 encode result
	 *
	 * @return string - Encrypted binary/Base64 data including header and tag
	 */
	public function encryptWithParameters($parameters, $base64) {
		$ciphertext_with_tag = AESGCM::encryptAndAppendTag($parameters->key, $parameters->iv, $parameters->plaintext, $parameters->aad, Constants::TAG_SIZE_BITS);
		
		$header = new CipherCore_Header();
		$header->IV = $parameters->iv;
		$header->AAD = $parameters->aad;
		if($parameters->searchable) {
			$header->CipherSuite = CipherSuite::AESGCM_TOKENHMACSHA256;
			$header->Token = $this->tokenForParameters($parameters);
		}
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
	 * @param bool $searchable - include token for searchable encryption
	 * @param bool $base64 - Base64 encode result
	 *
	 * @return string - Encrypted binary/Base64 data including header and tag
	 */
	public function encrypt($clear_text = null, $aad = null, $searchable = false, $base64 = true) {
		$encryptParameters = new EncryptParameters();
		$encryptParameters->plaintext = $clear_text;
		$encryptParameters->searchable = $searchable;
		// TODO - assemble key request object
		$encryptParameters->key = $this->key_server_client->read_sec_part_key(null);
		if($searchable) {
			// TODO - assemble key request object
			$encryptParameters->tokenKey = $this->key_server_client->read_sec_part_key(null);
		}

		$encrypted_record = $this->encryptWithParameters($encryptParameters, $base64);
		return $encrypted_record;
	}

	/**
	 * Decrypts parameters with AES-GCM
	 *
	 * @param DecryptParameters $parameters - parameters to decrypt
	 * @param bool $base64 - Base64 decode ciphertext
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
	 * @param string $encrypted_record - Encrypted record. Binary or Base64 encoded.
	 * @param bool $base64 - Base64 decode ciphertext
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
