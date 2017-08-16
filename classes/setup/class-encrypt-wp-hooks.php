<?php

class EncryptWP_Hooks{
	protected $shortcodes;

	protected $user_meta;

	public function __construct(EncryptWP_Shortcodes $shortcodes, EncryptWP_UserMeta $user_meta) {
		$this->shortcodes = $shortcodes;
		$this->user_meta = $user_meta;
	}

	public function load_hooks(){
		$this->shortcodes->load_hooks();
		$this->user_meta->load_hooks();
	}

}