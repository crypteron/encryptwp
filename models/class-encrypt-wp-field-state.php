<?php
class EncryptWP_Field_State {
	const PLAINTEXT = -1;
	const ENCRYPTED = 0;
	const ENCRYPTED_SEARCHABLE = 1;

	public static function is_valid($state){
		return in_array($state, [self::PLAINTEXT, self::ENCRYPTED, self::ENCRYPTED_SEARCHABLE]);
	}
}