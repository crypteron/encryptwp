<?php
namespace CipherCore\v1;

class Constants {
	const BITS_PER_BYTE = 8;
	const AES_256_KEY_SIZE_BITS = 256;
	const AES_256_KEY_SIZE_BYTES = self::AES_256_KEY_SIZE_BITS / self::BITS_PER_BYTE;

	const AES_128_KEY_SIZE_BITS = self::AES_256_KEY_SIZE_BITS / 2;
	const AES_128_KEY_SIZE_BYTES = self::AES_256_KEY_SIZE_BYTES / 2;

	const IV_SIZE_BITS = 96;
	const IV_SIZE_BYTES = self::IV_SIZE_BITS / self::BITS_PER_BYTE;

	const TAG_SIZE_BITS = 128;
	const TAG_SIZE_BYTES = self::TAG_SIZE_BITS / self::BITS_PER_BYTE;

	const TOKEN_SIZE_BITS = 256;
	const TOKEN_SIZE_BYTES = self::TOKEN_SIZE_BITS / self::BITS_PER_BYTE;

	const MAGIC_BLOCK = "\xCD\xB3";
	const HASH_ALGORITHM = "sha256";
}
