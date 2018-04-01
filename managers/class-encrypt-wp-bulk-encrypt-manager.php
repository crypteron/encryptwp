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
	 * Encrypt or decrypt all users based on options.
	 * TODO: only handle the fields and meta fields that have changed
	 * @return EncryptWP_Bulk_User_Result
	 */
	public function update_all_users(){
		/** @var WP_User[] $users */
		$users = get_users();
		$result = new EncryptWP_Bulk_User_Result();
		$this->encryption_manager->set_strict_mode(false);

		foreach($users as $user){

			$user->filter = 'edit';
			foreach ($this->options->user_fields as $field){
				// If we're transitioning to plaintext, the auto decrypt filter will be turned off. Decrypt manually
				if(!$this->options->encrypt_enabled || $field->state === EncryptWP_Field_State::PLAINTEXT)
					$user->{$field->slug} = $this->encryption_manager->decrypt($user->{$field->slug}, null, 'user', $field->slug);
				else {
					// The user object magic method will decrypt the field automatically
					$user->{$field->slug} = $user->{$field->slug};
				}
			}

			// Remove any secure user meta from fields updated within wp_update_user so we can handle their logic below instead
			add_filter('insert_user_meta', array($this, 'remove_user_meta'), 10, 3);

			// Update the user fields
			$update_result = wp_update_user($user);

			// Reset the filter
			remove_filter('insert_user_meta', array($this, 'remove_user_meta'), 10);

			// Ensure nothing went wrong
			if(is_wp_error($update_result)) {
				$result->users_error[] = sprintf( '%s (%d) - %s', $user->display_name, $user->ID, $update_result->get_error_message() );
				continue;
			}

			// Fetch all existing user meta fields
			$meta_fields = get_user_meta($user->ID);

			// Update all sensitive meta fields
			foreach($this->options->user_meta_fields as $meta_field){

				// Skip fields that don't exist
				if(!isset($meta_fields[$meta_field->slug]))
					continue;

				$value = $meta_fields[$meta_field->slug][0];

				// If we're transitioning to plaintext, the call to get_user_meta above will not have automatically decrypted it
				if(!$this->options->encrypt_enabled || $meta_field->state === EncryptWP_Field_State::PLAINTEXT)
					$value = $this->encryption_manager->decrypt($value, null, 'user_meta', $meta_field->slug);

				// Let the automatic encryption logic do its thing
				update_user_meta($user->ID, $meta_field->slug, $value);

			}

			$result->users_success[] = sprintf('%s (%d)', $user->display_name, $user->ID);

		}

		// Turn strict mode back on (if it was)
		$this->encryption_manager->set_strict_mode($this->options->strict_mode);
		return $result;
	}

	public function remove_user_meta($meta, $user, $update) {
		foreach ($this->options->user_meta_fields as $meta_field){
			unset($meta[$meta_field->slug]);
		}

		return $meta;
	}
}