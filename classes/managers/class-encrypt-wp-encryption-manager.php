<?php
use CipherCore\v1\Encryptor;
use CipherCore\v1\CipherCore_Deserialize_Exception;
use CipherCore\v1\CipherCore_AAD_Exception;
use TrestianCore\v1\Plugin_Settings;
use TrestianCore\v1\Admin_Notice_Manager;

class EncryptWP_Encryption_Manager {
	/**
	 * @var Encryptor
	 */
	protected $encryptor;

	/**
	 * @var Plugin_Settings
	 */
	protected $settings;

	/**
	 * @var Admin_Notice_Manager
	 */
	protected $admin_notice_manager;

	/**
	 * @var EncryptWP_Options_Manager
	 */
	protected $options;

	/**
	 * EncryptWP_Encryption_Manager constructor.
	 *
	 * @param Encryptor $encryptor
	 * @param Plugin_Settings $settings
	 * @param Admin_Notice_Manager $admin_notice_manager
	 * @param EncryptWP_Options_Manager $options
	 */
	public function __construct(
		Encryptor $encryptor,
		Plugin_Settings $settings,
		Admin_Notice_Manager $admin_notice_manager,
		EncryptWP_Options_Manager $options
	) {
		$this->encryptor = $encryptor;
		$this->settings = $settings;
		$this->admin_notice_manager = $admin_notice_manager;;
		$this->options = $options;
	}

	/**
	 * Encrypt and sign text
	 *
	 * @param $clear_text string - the text to encrypt
	 * @param $aad string - any additional authenticated data
	 * @param $searchable - whether or not the encrypted text should be searchable
	 *
	 * @return string
	 */
	public function encrypt($clear_text, $aad, $searchable){
		return $this->encryptor->encrypt($clear_text, $aad, $searchable);
	}

	/**
	 * Decrypt and authenticate text
	 *
	 * @param $encrypted_record string - the text to decrypt
	 * @param $aad string - any additional authenticated data
	 * @param $context string - what type of data is this, e.g. user or user meta
	 * @param $field string - what field is this, e.g. first_name
	 *
	 * @return bool|string
	 */
	public function decrypt($encrypted_record, $aad, $context = null, $field = null){
		try {
			return $this->encryptor->decrypt( $encrypted_record, $aad );
		}
		catch(AvroException $e){
			return $this->handle_cleartext($encrypted_record, $context, $field);
		} catch(CipherCore_Deserialize_Exception $e){
			return $this->handle_cleartext($encrypted_record, $context, $field);
		} catch(Exception $e){
			return $this->handle_decrypt_failure($encrypted_record, $e, $context, $field);
		}
	}

	/**
	 * Determines if text is encrypted or not
	 * @param $text
	 *
	 * @return bool
	 */
	public function is_encrypted($text){
		try {
			// Try decrypt returns decrypted text if true. We just want a true or false.
			return $this->encryptor->try_decrypt($text) !== false;
		} catch(CipherCore_AAD_Exception $e){

			// We're not passing in any AAD, so it's possible the AAD check fails after a successful decryption
			return true;
		}
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
	 * Helper function to handle clear text being found in a secured field while in strict mode.
	 * Displays error in admin and obfuscates text on frontend.
	 *
	 * @param $record string - the clear text that was found
	 * @param $context - where the data was stored
	 * @param $field - the data field
	 *
	 * @return string - the clear text if in admin and the obfuscated text if on frontend
	 */
	private function handle_cleartext($record, $context, $field){

		if(is_admin()) {
			$context = $this->get_context_string($context, $field);
			$error = sprintf(__( "DANGER! Clear text has been found in %s. ", $this->settings->get_prefix() ), $context);
			$error .= sprintf(__("The text: '%s' should have been encrypted and may have been tampered with. ", $this->settings->get_prefix()), $record);
			$error .= __( "If your database is not fully encrypted yet, turn off 'Strict Mode' within EncryptWP settings.", $this->settings->get_prefix() );
			$this->admin_notice_manager->add_notice( $error, 'error' );

			return $record;
		} else {
			// TODO: log and email admin
			return $this->options->frontend_error_placeholder;
		}
	}

	/**
	 * Helper function to handle decryption failure. Displays an error with the relevant information on
	 * the admin dashboard and obfuscates the text on the frontend.
	 *
	 * @param $record string - The encrypted record that failed to decrypt
	 * @param $exception Exception - The exception that occurred
	 * @param $context string - The type of data
	 * @param $field string - The field being decrypted
	 *
	 * @return string - The encrypted text in admin and obfuscated text on the frontend.
	 */
	private function handle_decrypt_failure($record, $exception, $context, $field){
		if(is_admin()){
			$context = $this->get_context_string($context, $field);
			$error = sprintf(__('DANGER! Decryption failed on %s. Unable to decrypt the text: %s. The following exception occurred: %s', $this->settings->get_prefix()), $context, $record, $exception->getMessage());

			// TODO: email full stack trace to admin
			$this->admin_notice_manager->add_notice($error, 'error');

			return $record;
		} else {
			// TODO: log and email admin
			return $this->options->frontend_error_placeholder;
		}



	}

}