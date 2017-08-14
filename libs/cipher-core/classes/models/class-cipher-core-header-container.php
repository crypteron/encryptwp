<?php
namespace CipherCore\v1;

/**
 * Class CipherCore_Header_Container
 * @package CipherCore\v1
 * Container class for returning a deserialized CipherCore_Header along with the number of bytes read from the serialized header string
 */
class CipherCore_Header_Container {
	/**
	 * @var CipherCore_Header
	 */
	public $header;

	/**
	 * @var int
	 */
	public $bytesRead;
}
