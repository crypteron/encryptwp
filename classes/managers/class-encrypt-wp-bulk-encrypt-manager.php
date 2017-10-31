<?php

class EncryptWP_Bulk_Encrypt_Manager {

	/**
	 * @var EncryptWP_Encryption_Manager
	 */
	protected $encryption_manager;

	public function __construct(EncryptWP_Encryption_Manager $encryption_manager) {
		$this->encryption_manager = $encryption_manager;
	}

	/**
	 * @return EncryptWP_Bulk_User_Result
	 */
	public function encrypt_all_users(){
		/** @var WP_User[] $users */
		$users = get_users();
		$result = new EncryptWP_Bulk_User_Result();

		foreach($users as $user){
			$user->filter = 'edit';
			$update_result = wp_update_user($user);

			if(is_wp_error($update_result)){
				$result->users_error[] = sprintf('%s (%d) - %s', $user->display_name, $user->ID, $update_result->get_error_message());
			} else {
				$result->users_success[] = sprintf('%s (%d)', $user->display_name, $user->ID);
			}
		}

		return $result;
	}

	/**
	 * @return EncryptWP_Bulk_User_Result
	 */
	public function decrypt_all_users(){
		/** @var WP_User[] $users */
		$users = get_users();
		$result = new EncryptWP_Bulk_User_Result();

		foreach($users as $user){
			$user->filter = 'edit';
			$update_result = wp_update_user($user);

			if(is_wp_error($update_result)){
				$result->users_error[] = sprintf('%s (%d) - %s', $user->display_name, $user->ID, $update_result->get_error_message());
			} else {
				$result->users_success[] = sprintf('%s (%d)', $user->display_name, $user->ID);
			}
		}

		return $result;

	}
}