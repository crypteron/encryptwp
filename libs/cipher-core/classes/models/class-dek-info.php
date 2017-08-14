<?php
namespace CipherCore\v1;

/**
 * Class DekInfo
 * @package CipherCore\v1
 */
class DekInfo {

	/**
	 * @var int
	 */
	public $DekCipherSuite = 0;

	/**
	 * @var string | null
	 */
	public $DekIV = null;

	/**
	 * @var string | null
	 */
	public $DekEnc = null;
}