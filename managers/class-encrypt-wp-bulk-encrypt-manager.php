<?php

class EncryptWP_Bulk_Encrypt_Manager {

	/**
	 * @var EncryptWP_Encryption_Manager
	 */
	protected $encryption_manager;

	public function __construct(EncryptWP_Encryption_Manager $encryption_manager, EncryptWP_Options_Manager $options_manager) {
		$this->encryption_manager = $encryption_manager;
		$this->options = $options_manager->get_options();
	}

	/**
	 * Updates all users, effectively encrypting or decrypting them according to options
	 * @return EncryptWP_Bulk_User_Result
	 */
	public function update_all_users(){
		/** @var WP_User[] $users */
		$users = get_users();
		$result = new EncryptWP_Bulk_User_Result();

		foreach($users as $user){
			$user->filter = 'edit';

			foreach ($this->options->user_fields as $field){
				$user->{$field->slug} = $user->{$field->slug};
			}

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