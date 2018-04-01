<?php
namespace CipherCore\v1;

Interface IKeyServerClient {
	public function read_sec_part_key($key_request);

	public function generate_key($AES_256);
}
