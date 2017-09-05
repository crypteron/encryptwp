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
	public $key_server_client;

	/**
	 * @var Serializer
	 */
	protected $serializer;

	/**
	 * @var Settings
	 */
	protected $settings;

	public function __construct(Settings $settings) {
		$this->key_server_client = new Key_Server_Client();
		$this->serializer = new Serializer();
		$this->settings = $settings;
	}

	/**
	 * Generates a search token for the given encryption parameters.
	 *
	 * @param EncryptParameters $parameters - parameters to tokenize
	 *
	 * @return string - binary token value
	 */
	public function getTokenForParameters($parameters) {
		return hash_hmac(Constants::HASH_ALGORITHM, $parameters->plaintext, $parameters->tokenKey, true);
	}

	/**
	 * Generates a search prefix for a set of parameters
	 *
	 * @param EncryptParameters $parameters - Object containing encryption parameters
	 * @param bool $base64 - Whether base64 encode binary result
	 *
	 * @return string - search prefix in binary or base64
	 */
	public function getSearchPrefixForParameters($parameters, $base64) {
		// Determine search prefix size by combining sizes of magic block and token
		// and accounting for overlap with base64 encoding
		$magicBlockLength = mb_strlen(Constants::MAGIC_BLOCK, '8bit');
		$searchPrefixSize = $magicBlockLength + Constants::TOKEN_SIZE_BYTES;
		$searchPrefixSize -= $searchPrefixSize % self::PREFIX_DIVISIBLE_BY;

		// Assemble dummy header to extract prefix
		$header = new CipherCore_Header();
		$header->IV = '';
		$header->Token = $this->getTokenForParameters($parameters);
		$headerBytes = $this->serializer->serialize($header);

		// Extract search prefix from dummy header
		$searchPrefix = substr($headerBytes, 0, $searchPrefixSize);

		// Optionally base64 encode result
		if($base64) {
			$searchPrefix = base64_encode($searchPrefix);
		}
		return $searchPrefix;
	}

	/**
	 * Generates a search prefix for an exact plain text search
	 *
	 * @param string $plaintext - Exact, case sensitive text to search for
	 * @param bool $base64 - Whether to base64 encode binary result
	 *
	 * @return string - search prefix in binary or base64
	 */
	public function getSearchPrefix($plaintext, $base64 = true) {
		// Assemble parameters object
		$parameters = new EncryptParameters();
		$parameters->plaintext = $plaintext;

		// Fetch search encryption key
		$keyRequest = new ReadKeyRequest();
		$keyRequest->SecPartId = Constants::SYSTEM_RESERVED_ID;
		$keyRequest->SecPartVer = SecPartVer::Tokenization;
		$keyRequest->ForRoleId = Constants::SYSTEM_RESERVED_ID;
		$parameters->tokenKey = $this->key_server_client->read_sec_part_key($keyRequest);

		// Fetch and return search prefix
		$searchPrefix = $this->getSearchPrefixForParameters($parameters, $base64);
		return $searchPrefix;
	}

	/**
	 * Encrypts parameter object with AES-GCM
	 *
	 * @param EncryptParameters $parameters - parameters to encrypt
	 * @param bool $base64 - Base64 encode result
	 *
	 * @return string - Encrypted binary/Base64 data including header and tag
	 */
	public function encryptWithParameters($parameters, $base64) {
		// Encrypt clear text with appended tag
		$ciphertext_with_tag = AESGCM::encryptAndAppendTag($parameters->key, $parameters->iv, $parameters->plaintext, $parameters->aad, Constants::TAG_SIZE_BITS);

		// Assemble header with meta information
		$header = new CipherCore_Header();
		$header->IV = $parameters->iv;
		$header->AAD = $parameters->aad;

		// If clear text should be searchable, include a hashed search token in header
		if($parameters->searchable) {
			$header->CipherSuite = CipherSuite::AESGCM_TOKENHMACSHA256;
			$header->Token = $this->getTokenForParameters($parameters);
		}

		// Serialize header and prepend it to cipher text
		$serialized_header = $this->serializer->serialize($header);
		$encrypted_record =  $serialized_header . $ciphertext_with_tag;

		// Optionally base64 encode the result
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
	 * @param bool $searchable - Whether result should include a token for searchable encryption
	 * @param bool $base64 - Base64 encode result
	 *
	 * @return string - Encrypted binary/Base64 data including header and tag
	 */
	public function encrypt($clear_text = null, $aad = null, $searchable = false, $base64 = true) {
		// Assemble encryption parameters object
		$encryptParameters = new EncryptParameters();
		$encryptParameters->plaintext = $clear_text;
		$encryptParameters->searchable = $searchable;

		// Fetch encryption key
		$keyRequest = new ReadKeyRequest();
		$encryptParameters->key = $this->key_server_client->read_sec_part_key($keyRequest);

		// If result should be searchable, fetch search token key
		if($searchable) {
			$tokenKeyRequest = new ReadKeyRequest();
			$tokenKeyRequest->SecPartId = Constants::SYSTEM_RESERVED_ID;
			$tokenKeyRequest->SecPartVer = SecPartVer::Tokenization;
			$tokenKeyRequest->ForRoleId = Constants::SYSTEM_RESERVED_ID;
			$encryptParameters->tokenKey = $this->key_server_client->read_sec_part_key($tokenKeyRequest);
		}

		// Encrypt results
		$encrypted_record = $this->encryptWithParameters($encryptParameters, $base64);
		return $encrypted_record;
	}

	/**
	 * Decrypts parameters object with AES-GCM
	 *
	 * @param DecryptParameters $parameters - parameters to decrypt
	 * @param bool $base64 - Base64 decode ciphertext
	 *
	 * @return string - Decrypted plaintext
	 */
	public function decryptWithParameters($parameters, $base64) {
		// If input is base64, convert it to binary first
		if($base64) {
			$parameters->ciphertext = base64_decode($parameters->ciphertext);
		}

		// Deserialize the header and its position within record
		$header_container = $this->serializer->deserialize($parameters->ciphertext);

		// Extract the IV and AAD from the header and the remaining cipher text within the record
		$iv = $header_container->header->IV;
		$aad = $header_container->header->AAD;
		$ciphertext = substr($parameters->ciphertext, $header_container->bytesRead);

		// Verify the AAD if passed
		if( $parameters->AAD != null && $parameters->AAD != $header_container->header->AAD){
			throw new CipherCore_AAD_Exception("Additional authenticated data (AAD) provided does not match what's in the encrypted record. The data may have been tampered with.");
		}

		// Decrypt the record
		$plaintext = AESGCM::decryptWithAppendedTag($parameters->key, $iv, $ciphertext, $aad, Constants::TAG_SIZE_BITS);
		return $plaintext;
	}

	public function decrypt($encrypted_record, $aad = null, $base64 = true){
		// If using strict mode, just call internal decrypt method
		if($this->settings->get_strict()){
			return $this->decrypt_internal($encrypted_record, $aad, $base64);
		} else {
			// If not using strict mode, see if the text is encrypted
			$results = $this->try_decrypt($encrypted_record, $aad, $base64);

			// If the text was successfully decrypted, return the end result
			if($results !== false){
				return $results;
			}

			// The text is not encrypted. Return the original.
			return $encrypted_record;
		}
	}

	/**
	 * Decrypts binary text and authenticates additional data
	 *
	 * @param string $encrypted_record - Encrypted record. Binary or Base64 encoded.
	 * @param bool $base64 - Base64 decode ciphertext
	 *
	 * @return string - Decrypted plaintext
	 */
	protected function decrypt_internal($encrypted_record, $aad = null, $base64 = true) {

		// Assemble decryption parameters object with cipher text and fetched encryption key
		$decryptParameters             = new DecryptParameters();
		$decryptParameters->ciphertext = $encrypted_record;
		$decryptParameters->AAD        = $aad;

		$keyRequest = new ReadKeyRequest();
		// TODO - assemble rest of key request object
		$decryptParameters->key = $this->key_server_client->read_sec_part_key($keyRequest);

		// Decrypt results
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
	public function try_decrypt($text, $aad = null, $base64 = true){
		try{
			$clear_text = $this->decrypt_internal($text, $aad, $base64);

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
