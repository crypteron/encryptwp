<?php

use TrestianCore\v1\Admin_Notice_Manager;
use TrestianCore\v1\Plugin_Settings;

class EncryptWP_Error_Manager {
	/**
	 * @var Plugin_Settings
	 */
	protected $settings;

	/**
	 * @var Admin_Notice_Manager
	 */
	protected $admin_notice_manager;

	/**
	 * @var EncryptWP_Options
	 */
	protected $options;

	/**
	 * How long to rate limit errors for the same piece of data. 24 hours.
	 */
	const RATE_LIMIT = 24*60*60;

	const ERROR_KEY = 'encryptwp_errors';

	/**
	 * @var EncryptWP_Error[]
	 */
	protected $errors;

	/**
	 * EncryptWP_Encryption_Manager constructor.
	 *
	 * @param Plugin_Settings $settings
	 * @param Admin_Notice_Manager $admin_notice_manager
	 * @param EncryptWP_Options_Manager $options_manager
	 */
	public function __construct(
		Plugin_Settings $settings,
		Admin_Notice_Manager $admin_notice_manager,
		EncryptWP_Options_Manager $options_manager
	) {
		$this->settings = $settings;
		$this->admin_notice_manager = $admin_notice_manager;;
		$this->options = $options_manager->get_options();
		$this->errors = get_option(self::ERROR_KEY, array());
	}


	/**
	 * Helper function to generate a context string for errors based on context and field name.
	 *
	 * @param $context string
	 * @param $field string
	 *
	 * @return string
	 */
	private function get_context_string($context, $field){
		$context = is_null($context) ? __('sensitive', $this->settings->get_prefix()) : $context;
		$context = is_null($field) ? sprintf(__('a %s field', $this->settings->get_prefix()),$context ) : sprintf(__('the %s field %s', $this->settings->get_prefix()), $context, $field);

		return $context;
	}

	/**
	 * Handle clear text being found in a secured field. Log the error, notify the admin and return the cleartext.
	 *
	 *
	 * @param $record string - the clear text that was found
	 * @param $context - where the data was stored
	 * @param $field - the data field
	 *
	 * @return string - the text after being handled
	 */
	public function cleartext_found($record, $context, $field){
		$context = $this->get_context_string($context, $field);
		$error = sprintf(__( "DANGER! Clear text has been found in %s. ", $this->settings->get_prefix() ), $context);
		$error .= sprintf(__("The text: '%s' should have been encrypted and may have been tampered with. ", $this->settings->get_prefix()), $record);
		$error .= __( "If your database is not fully encrypted yet, turn off 'Strict Mode' within EncryptWP settings.", $this->settings->get_prefix() );
		$cache_key = md5($record . $context . $field . 'cleartext');
		$this->log_error($error, $cache_key);

		return $record;

	}

	/**
	 * Handle decryption failures. Log the error, notify the admin, return the record as is.
	 *
	 * @param $record string - The encrypted record that failed to decrypt
	 * @param $exception Exception - The exception that occurred
	 * @param $context string - The type of data
	 * @param $field string - The field being decrypted
	 *
	 * @return string - The encrypted text that failed to decrypt
	 */
	public function decrypt_failure($record, $exception, $context, $field){

		$context = $this->get_context_string($context, $field);
		$error = sprintf(__('DANGER! Decryption failed on %s. Unable to decrypt the text: %s. The following exception occurred: %s', $this->settings->get_prefix()), $context, $record, $exception->getMessage());

		$cache_key = md5($record . $context . $field . $exception->getCode() );
		$this->log_error($error, $cache_key );

		return $record;

	}


	/**
	 * Handle encryption failures. Log the error, notify the admin, return the original cleartext.
	 *
	 * @param $cleartext string - The cleartext that failed to encrypt
	 * @param $exception Exception - The exception that occurred
	 * @param $context string - The type of data
	 * @param $field string - The field being decrypted
	 *
	 * @return string - The cleartext
	 */
	public function encrypt_failure($cleartext, $exception, $context, $field){

		$context = $this->get_context_string($context, $field);
		$error = sprintf(__('DANGER! Encryption failed on %s. Unable to encrypt the text: %s. The following exception occurred: %s', $this->settings->get_prefix()), $context, $cleartext, $exception->getMessage());

		$cache_key = md5($cleartext . $context . $field . $exception->getCode());
		$this->log_error($error, $cache_key);

		return $cleartext;
	}

	public function log_error($message, $cache_key){
		if(wp_cache_get($cache_key, EncryptWP_Constants::CACHE_GROUP)){
			return;
		}


		$to = $this->options->admin_notify;
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$subject = __('EncryptWP Error', $this->settings->get_prefix());

		$result = wp_mail($to,$subject, $message, $headers);

		$this->admin_notice_manager->add_notice($message, 'error');

		wp_cache_add($cache_key, true, EncryptWP_Constants::CACHE_GROUP, self::RATE_LIMIT);


	}


}