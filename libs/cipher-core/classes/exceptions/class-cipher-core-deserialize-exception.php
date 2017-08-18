<?php
namespace CipherCore\v1;
use Throwable;

class CipherCore_Deserialize_Exception extends CipherCore_Exception {
	public function __construct( $message = "", $code = 0, Throwable $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}
}
