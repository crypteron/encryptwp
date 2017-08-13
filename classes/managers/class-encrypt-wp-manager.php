<?php
use AESGCM\AESGCM;

class EncryptWP_Manager {

	public function generate_iv(){
		return random_bytes(EncryptWP_Constants::IV_SIZE_BYTES);
	}

	/**
	 * Fetch the binary encryption key
	 *
	 * @return bool|string
	 * @throws EncryptWP_Exception
	 */
	public function get_key(){
		// TODO: get key from Crypteron
		if(!defined('ENCRYPT_WP_KEY')){
			throw new EncryptWP_Exception('No key defined. Define ENCRYPT_WP_KEY within wp-config.php. Generate one with EncryptWP_Manager->generate_key()');
		}

		return base64_decode(ENCRYPT_WP_KEY);
	}

	/**
	 * Generate a 256 or 128 bit encryption key in hexadecimal
	 * @param bool $AES_256
	 *
	 * @return string|void
	 */
	public function generate_key($AES_256 = true){
		if($AES_256){
			$bytes = EncryptWP_Constants::AES_256_KEY_SIZE_BYTES;
		} else {
			$bytes = EncryptWP_Constants::AES_128_KEY_SIZE_BYTES;
		}
		$key_binary = random_bytes($bytes);

		return base64_encode($key_binary);
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
		$key = $this->get_key();
		$iv = $this->generate_iv();

		$ciphertext_with_tag = AESGCM::encryptAndAppendTag($key, $iv, $clear_text, $aad, EncryptWP_Constants::TAG_SIZE_BITS);

		$header = new CipherCoreHeader();
		$header->IV = $iv;
		$encrypted_record =$header->serialize() . $ciphertext_with_tag;
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
		$header_container = CipherCoreHeader::deserialize($encrypted_record);
		$iv = $header_container->header->IV;
		$cipher_text = substr($encrypted_record, $header_container->bytesRead );

		$clear_text = AESGCM::decryptWithAppendedTag($this->get_key(), $iv, $cipher_text, $aad, EncryptWP_Constants::TAG_SIZE_BITS);
		return $clear_text;

	}


}
