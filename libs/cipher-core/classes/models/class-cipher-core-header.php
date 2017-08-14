<?php
namespace CipherCore\v1;

/**
 * Class CipherCore_Header
 * @package CipherCore\v1
 * Deserialized CipherCore header information
 */
class CipherCore_Header {



	/**
	 * Used for creating prefixes for searchable encryption
	 * @var string | null
	 */
	public $Token = null;

	/**
	 * Security Partition ID
	 * @var string
	 */
	public $SecPartId = "";

	/**
	 * Security Partition Version
	 * @var int
	 */
	public $SecPartVer = 1;

	/**
	 * CipherSuite Used
	 * @var int
	 */
	public $CipherSuite = CipherSuite::AESGCM;

	/**
	 * Required, use a different crypto random value each time
	 * @var string
	 */
	public $IV;

	/**
	 * Additional Authenticated Data. Useful for row binding.
	 * @var string | null
	 */
	public $AAD = null;

	/**
	 * For extra features such as compression
	 * @var int
	 */
	public $CellAttributes = CellAttribute::NONE;

	/**
	 * Optional
	 * @var int
	 */
	public $DekCipherSuite = 0;

	/**
	 * @var null
	 */
	public $DekIV = null;

	/**
	 * @var null
	 */
	public $DekEnc = null;

}
