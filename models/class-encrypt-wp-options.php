<?php
class EncryptWP_Options {
	/**
	 * @var bool - Whether EncryptWP should encrypt data. Encrypted data will decrypt by default.
	 */
	public $encrypt_enabled = false;

	/**
	 * @var bool - Whether an error should occur when clear text is found in a secure field
	 */
	public $strict_mode = false;

	/**
	 * @var array[] - The user meta fields to encrypt and their settings
	 */
	public $user_meta_fields = null;

	/**
	 * @var array[] - The user fields to encrypt
	 */
	public $user_fields = null;

	/**
	 * @var string[] - A list of the plugins that were activated since email activated was successfully turned on
	 */
	public $incompatible_plugins = [];

	/**
	 * @var bool - Whether or not to encrypt email addresses
	 */
	public $encrypt_email = false;

	/**
	 * @var string[] - Admin email addresses to notify on error
	 */
	public $admin_notify = null;
}