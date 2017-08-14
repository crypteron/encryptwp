<?php
namespace CipherCore\v1;

class CellAttribute {
	const NONE = 0;
	const COMPRESSED = 1 << 0;
	const SHARED = 1 << 7;
}
