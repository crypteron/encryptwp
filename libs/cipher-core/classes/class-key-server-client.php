<?php
namespace CipherCore\v1;

class Key_Server_Client implements IKeyServerClient {

	/**
	 * @param ReadKeyRequest $key_request
	 *
	 * @return bool|string
	 */
	public function read_sec_part_key($key_request) {
		$keyBytesEncoded = CIPHER_CORE_KEY;
		if($key_request->SecPartVer === SecPartVer::Tokenization) {
			$keyBytesEncoded = CIPHER_CORE_TOKEN_KEY;
		}
		// TO DO - Implement key server call and response
		return base64_decode($keyBytesEncoded);
	}

	/**
	 * Generate a 256 or 128 bit encryption key in Base64
	 * @param bool $AES_256
	 *
	 * @return string|void
	 */
	public function generate_key($AES_256 = true){
		if($AES_256){
			$bytes = Constants::AES_256_KEY_SIZE_BYTES;
		} else {
			$bytes = Constants::AES_128_KEY_SIZE_BYTES;
		}
		$key_binary = random_bytes($bytes);

		return base64_encode($key_binary);
	}
}
