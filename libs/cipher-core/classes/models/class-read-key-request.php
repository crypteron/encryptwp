<?php
namespace CipherCore\v1;

/**
 * Class ReadKeyRequest
 * @package CipherCore\v1
 */
class ReadKeyRequest {

	/**
	 * @var string
	 */
	 public $SecPartId = '';

	/**
	 * @var int
	 */
	public $SecPartVer = SecPartVer::Latest;

	/**
	 * @var string
	 */
	 public $ForRoleId = '';

	 /**
	 * @var int
	 */
	public $Intents = 0;
}
