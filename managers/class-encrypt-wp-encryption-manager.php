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
	 * @var EncryptWP_Error_Manager
	 */
	protected $error_manager;

	/**
	 * EncryptWP_Encryption_Manager constructor.
	 *
	 * @param Encryptor $encryptor
	 * @param Plugin_Settings $settings
	 * @param Admin_Notice_Manager $admin_notice_manager
	 * @param EncryptWP_Error_Manager $error_manager
	 */
	public function __construct(
		Encryptor $encryptor,
		Plugin_Settings $settings,
		Admin_Notice_Manager $admin_notice_manager,
		EncryptWP_Error_Manager $error_manager
	) {
		$this->encryptor = $encryptor;
		$this->settings = $settings;
		$this->admin_notice_manager = $admin_notice_manager;;
		$this->error_manager = $error_manager;
	}

	/**
	 * Encrypt and sign text
	 *
	 * @param $clear_text string - the text to encrypt
	 * @param $aad string - any additional authenticated data
	 * @param $searchable - whether or not the encrypted text should be searchable
	 * @param $context string - what type of data is this, e.g. user or user meta
	 * @param $field string - what field is this, e.g. first_name
	 * @return string
	 */
	public function encrypt($clear_text, $aad, $searchable, $context = null, $field = null){
		try {
			return $this->encryptor->encrypt( $clear_text, $aad, $searchable );
		} catch (Exception $e){
			return $this->error_manager->encrypt_failure($clear_text, $e, $context, $field);
		}
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
	public function decrypt($encrypted_record, $aad = null, $context = null, $field = null){
		try {
			return $this->encryptor->decrypt( $encrypted_record, $aad );
		}
		catch(AvroException $e){
			return $this->error_manager->cleartext_found($encrypted_record, $context, $field);
		} catch(CipherCore_Deserialize_Exception $e){
			return $this->error_manager->cleartext_found($encrypted_record, $context, $field);
		} catch(Exception $e){
			return $this->error_manager->decrypt_failure($encrypted_record, $e, $context, $field);
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
	 * Gets a search prefix for a given string. Exact match only, case sensitive.
	 *
	 * @param $string
	 *
	 * @return string
	 */
	public function get_search_prefix($string){
		return $this->encryptor->getSearchPrefix($string);
	}

	/**
	 * Gets a regular expression string to use in a query for encrypted text
	 *
	 * @param $string - string to search for
	 *
	 * @return string
	 */
	public function get_search_regex($string) {
		$prefix = $this->get_search_prefix($string);
		return '^' . preg_quote($prefix);
	}

	/**
	 * @param $strict_mode
	 */
	public function set_strict_mode($strict_mode){
		$this->encryptor->set_strict_mode($strict_mode);
	}
}