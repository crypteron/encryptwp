<?php
namespace CipherCore\v1;

class Settings {
	/**
	 *
	 * Whether or not to trigger an error when decrypting cleartext.
	 * Only set to true if you're sure that all sensitive data is encrypted. Disabling this feature severely limits
	 * tamper protection since a malicious user can replace an encrypted value with cleartext.
	 * @var bool
	 */
	protected $strict;

	public function __construct($strict) {
		$this->strict = $strict;
	}

	public function get_strict(){
		return $this->strict;
	}

}