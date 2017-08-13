<?php

class EncryptWP_Hooks{
	protected $shortcodes;

	public function __construct(EncryptWP_Shortcodes $shortcodes) {
		$this->shortcodes = $shortcodes;
	}

	public function load_hooks(){
		$this->shortcodes->load_hooks();
	}

}