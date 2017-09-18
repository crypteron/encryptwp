<?php

class EncryptWP_Constants {

	/**
	 * User meta key used to store encrypted email
	 */
	const EMAIL_META_KEY = 'secure_user_email';

	/**
	 * Replacement pattern for obfuscating emails in wp_users.
	 */
	const OBFUSCATE_EMAIL_PATTERN = "secure_%d@hifoo.com";

	const OPTION_GROUP = 'encryptwp';

	const OPTION_NAME = 'encryptwp_options';

}
