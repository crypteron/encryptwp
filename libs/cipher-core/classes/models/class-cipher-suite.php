<?php
namespace CipherCore\v1;

/**
 * Class CipherSuite
 * @package CipherCore\v1
 * Constants for different CipherSuites
 */
class CipherSuite {

	/**
	 * Basic and Legacy
	 */
	const AESGCM = 0;

	/**
	 * RESERVED for historical purposes was SecVer
	 */
	const RESERVED_SECVER_V1 = 1;

	/**
	 * RESERVED for historical purposes was SecVer
	 */
	const RESERVED_SECVER_V2 = 2;
	const AESECB = 3;

	/**
	 * Encryption + HMACs
	 * This is AES GCM with an HMAC of SHA256
	 */
	const AESGCM_HMACSHA256 = 10;

	/**
	 * Tokenization + Encryption
	 * This is AES GCM with an HMAC of SHA256
	 */
	const AESGCM_TOKENHMACSHA256 = 20;

	/**
	 * Tokenization
	 */
	const TOKENHMACSHA256 = 30;
}
