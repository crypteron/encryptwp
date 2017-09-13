<?php

class EncryptWP_Hooks{
	/**
	 * @var EncryptWP_Shortcodes
	 */
	protected $shortcodes;

	/**
	 * @var EncryptWP_User_Meta
	 */
	protected $user_meta;

	/**
	 * @var EncryptWP_User_Fields
	 */
	protected $user_fields;

	/**
	 * @var EncryptWP_User_Email
	 */
	protected $user_email;

	/**
	 * EncryptWP_Hooks constructor. Inject hook objects
	 *
	 * @param EncryptWP_Shortcodes $shortcodes
	 * @param EncryptWP_User_Meta $user_meta
	 * @param EncryptWP_User_Fields $user_fields
	 */
	public function __construct(
		EncryptWP_Shortcodes $shortcodes,
		EncryptWP_User_Meta $user_meta,
		EncryptWP_User_Fields $user_fields,
		EncryptWP_User_Email $user_email
	) {
		$this->shortcodes = $shortcodes;
		$this->user_meta = $user_meta;
		$this->user_fields = $user_fields;
		$this->user_email = $user_email;
	}

	/**
	 * Load all hooks
	 */
	public function load_hooks(){
		$this->shortcodes->load_hooks();
		$this->user_meta->load_hooks();
		$this->user_fields->load_hooks();
		$this->user_email->load_hooks();

	}

}