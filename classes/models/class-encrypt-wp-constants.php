<?php
class EncryptWP_Constants {
	/**
	 * Whether or not insecure values found in the database should trigger an error when fetched.
	 * Only enable if you're sure the database is secured. Disabling this feature severely limits
	 * tamper protection since a malicious user can replace an encrypted value with cleartext.
	 * TODO: Put in database options with admin dashboard control
	 */
	const STRICT_MODE  = false;

	/**
	 * User meta key used to store encrypted email
	 */
	const EMAIL_META_KEY = 'secure_user_email';

	/**
	 * Replacement pattern for obfuscating emails in wp_users.
	 */
	const OBFUSCATE_EMAIL_PATTERN = "secure_%d@hifoo.com";

}
