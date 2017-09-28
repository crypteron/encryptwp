<?php
class EncryptWP_Bulk_User_Result {
	/**
	 * @var string[]
	 */
	public $users_success;

	/**
	 * @var string[]
	 */
	public $users_error;

	public function __construct($users_success = array(), $users_error = array()) {
		$this->users_success = $users_success;
		$this->users_error = $users_error;
	}
}